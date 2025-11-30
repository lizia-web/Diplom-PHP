<?php
session_start();

$success = $_SESSION['success'] ?? null;
unset($_SESSION['success']);

$error = $_SESSION['error'] ?? null;
unset($_SESSION['error']);

$dsn = "pgsql:host=localhost;dbname=postgres;port=5432";
$username = "postgres";
$password = "1131";

try {
    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $name = trim($_POST['name']);
        $login = trim($_POST['login']);
        $password = trim($_POST['password']);
        $password_confirm = trim($_POST['password_confirm']);

        // Перевірка на порожні значення
        if (empty($name) || empty($login) || empty($password) || empty($password_confirm)) {
            $_SESSION['error'] = "Будь ласка, заповніть усі поля.";
            header("Location: " . $_SERVER['PHP_SELF']);
            exit;
        } elseif ($password !== $password_confirm) {
            $_SESSION['error'] = "Паролі не співпадають.";
            header("Location: " . $_SERVER['PHP_SELF']);
            exit;

        } else {
            // Перевірка наявності користувача з таким логіном
            $stmt = $pdo->prepare("SELECT id FROM users WHERE login = :login");
            $stmt->execute(['login' => $login]);
            if ($stmt->fetch()) {
                $_SESSION['error'] = "Користувач з таким логіном вже існує.";
                header("Location: " . $_SERVER['PHP_SELF']);
                exit;

            } else {

                $now = date('d-m-Y H:i:s');
                // Хешування пароля
                $hashed_password = password_hash($password, PASSWORD_BCRYPT);

                // Додавання нового користувача
                $stmt = $pdo->prepare("INSERT INTO users (name, login, password, user_type, created_at) VALUES (:name, :login, :password, :user_type, :created_at)");
                $stmt->execute([
                    'name' => $name,
                    'login' => $login,
                    'password' => $hashed_password,
                    'user_type' => "user",
                    'created_at' => $now
                ]);

                // Отримання ID новоствореного користувача
                $user_id = $pdo->lastInsertId();

// Створення двох збірок: "прочитано" та "улюблене"

                $stmt = $pdo->prepare("INSERT INTO collections (user_id, name, created_at) VALUES (:user_id, :name, :created_at)");

                $stmt->execute([
                    'user_id' => $user_id,
                    'name' => 'Прочитано',
                    'created_at' => $now
                ]);

                $stmt->execute([
                    'user_id' => $user_id,
                    'name' => 'Улюблене',
                    'created_at' => $now
                ]);

                $_SESSION['success'] = "Реєстрація успішна!";
                header("Location: login_form.php");
                exit;
            }
        }
    }
} catch (PDOException $e) {
    die("Помилка підключення: " . $e->getMessage());
}
?>


<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="../css/registr.css">
    <title>Реєстрація</title>
</head>
<script src="../js/login_registr_navigate.js"></script>

<body>
<div class="container">
    <div class="registr-form">

        <form method="POST">

            <p class="title">Реєстрація</p>

            <?php if ($error): ?>
                <div id="toast" class="toast error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div id="toast" class="toast success"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>



            <div class="form-group">
            <label>Ім'я</label>
            <input type="text" name="name" placeholder="Введіть ім'я користувача" spellcheck="true" minlength="6" maxlength="25" required><br>
            </div>

            <div class="form-group">
            <label>Логін</label>
            <input type="text" name="login" placeholder="Введіть логін" spellcheck="true" minlength="6" maxlength="25" required><br>
            </div>

            <div class="form-group">
            <label>Пароль</label>
            <input type="password" name="password" placeholder="Введіть пароль" minlength="8" maxlength="25" required><br>
            </div>

            <div class="form-group">
            <label>Повторіть <br>пароль</label>
            <input type="password" name="password_confirm" placeholder="Введіть пароль повторно" minlength="8" maxlength="25" required><br>
            </div>

<!--            <div class="add-register">-->
<!--                <a href="#"><img src="../img/icon_google.png" ></a>-->
<!--                <a href="#"><img src="../img/icon_instagram.png" ></a>-->
<!--                <a href="#"><img src="../img/icon_x.png"></a>-->
<!--            </div> <br>-->

            <button type="submit">Зареєструватися</button><br>

            <p class="ask" onclick="navigateTo('login_form.php')">Вже маєте акаунт?</p>

        </form>
    </div>
</div>
</body>
</html>