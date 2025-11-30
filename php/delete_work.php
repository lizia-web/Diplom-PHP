<?php
include "session.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: account.php");
    exit;
}

$work_id = $_POST['work_id'] ?? null;
$user_id = $_SESSION['user_id'] ?? null;

if (!$work_id || !$user_id) {
    echo "Недійсний запит.";
    exit;
}

$dsn = "pgsql:host=localhost;dbname=postgres;port=5432";
$db_username = "postgres";
$db_password = "1131";

try {
    $pdo = new PDO($dsn, $db_username, $db_password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

    // Перевірка, чи твір належить користувачу
    $stmt = $pdo->prepare("SELECT id FROM works WHERE id = :work_id AND user_id = :user_id");
    $stmt->execute([
        'work_id' => $work_id,
        'user_id' => $user_id
    ]);

    if (!$stmt->fetch()) {
        echo "Ви не маєте прав для видалення цього твору.";
        exit;
    }

    // Видалення твору
    $stmt = $pdo->prepare("DELETE FROM works WHERE id = :id");
    $stmt->execute(['id' => $work_id]);

    // (Необов'язково) також можна видалити записи з collection_works
    $stmt = $pdo->prepare("DELETE FROM collection_works WHERE work_id = :id");
    $stmt->execute(['id' => $work_id]);

    header("Location: my_works.php");
    exit;

} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage();
    exit;
}
?>
