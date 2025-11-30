<?php
session_start();

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
            $_SESSION['success'] = "Вхід успішний!";

            // Перенаправлення на головну сторінку
            header("Location: new_main.php");
            exit;
        } else {
            $error = "Невірний логін або пароль!";
        }
    }
} catch (PDOException $e) {
    die("Помилка підключення: " . $e->getMessage());
}
?>
