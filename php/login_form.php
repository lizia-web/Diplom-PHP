<?php session_start();

$success = $_SESSION['success'] ?? null;
unset($_SESSION['success']);

$dsn = "pgsql:host=localhost;dbname=postgres;port=5432";
$username = "postgres";
$password = "1131";

try {
    // Підключення до бази даних
    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $login = trim($_POST['login']);
        $password = trim($_POST['password']);

        // Запит на пошук користувача
        $stmt = $pdo->prepare("SELECT id, password, user_type FROM users WHERE login = :login");
        $stmt->execute(['login' => $login]);
        $user = $stmt->fetch();

        // Перевірка пароля (припускаємо, що він хешований bcrypt'ом)
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id']; // Збереження ID користувача в сесії
            $_SESSION['user_type'] = $user['user_type']; // Збереження ID користувача в сесії
            $_SESSION['success'] = "Вхід успішний!";

            // Перенаправлення на головну сторінку
            header("Location: main.php");
            exit;
        } else {
            $error = "Невірний логін або пароль!";
        }
    }
} catch (PDOException $e) {
    die("Помилка підключення: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="../css/login.css">
    <title>Авторизація</title>

</head>
<script src="../js/login_registr_navigate.js"></script>


<body>
<div class="container">
<!--    <div class="logo">-->
<!--        <img src="../img/icon.PNG">-->
<!--    </div>-->
    <div class="login-form">

        <form method="POST">

            <p class="title">Авторизація</p>

            <?php if ($success): ?>
                <div id="toast" class="toast success"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div id="toast" class="toast error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>


            <div class="form-group">
                <label>Логін</label>
                <input type="text" name="login" placeholder="Введіть логін" required><br>
            </div>

            <div class="form-group">
                <label>Пароль</label>
                <input type="password" name="password" placeholder="Введіть пароль" required><br>
            </div>

            <button>Увійти</button><br>

            <p class="ask" onclick="navigateTo('registr.php')">Ще не зареєстровані?</p>
        </form>
    </div>

</div>
</body>
</html>