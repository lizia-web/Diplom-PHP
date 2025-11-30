<?php
$dsn = "pgsql:host=localhost;dbname=postgres;port=5432";
$db_username = "postgres";
$db_password = "1131";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $user_id = $_POST['user_id'] ?? null;
    $user_type = $_POST['user_type'] ?? null;

    if (!$user_id || !in_array($user_type, ['user', 'admin'])) {
        http_response_code(400);
        echo "Некоректні дані";
        exit;
    }

    try {
        $pdo = new PDO($dsn, $db_username, $db_password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);

        $stmt = $pdo->prepare("UPDATE users SET user_type = :user_type WHERE id = :id");
        $stmt->execute([
            ':user_type' => $user_type,
            ':id' => $user_id
        ]);

        echo "OK";

    } catch (PDOException $e) {
        http_response_code(500);
        echo "Помилка БД: " . $e->getMessage();
    }
}
