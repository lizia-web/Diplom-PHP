<?php
session_start();
header("Content-Type: application/json");

$collection_id = $_POST['collection_id'] ?? null;
$user_id = $_SESSION['user_id'] ?? null;

if (!$collection_id || !$user_id || !is_numeric($collection_id)) {
    echo json_encode(['success' => false, 'error' => 'Невірні дані']);
    exit;
}

$dsn = "pgsql:host=localhost;dbname=postgres;port=5432";
$db_username = "postgres";
$db_password = "1131";

try {
    $pdo = new PDO($dsn, $db_username, $db_password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

    // Отримати збірку
    $stmt = $pdo->prepare("SELECT name FROM collections WHERE id = :id AND user_id = :user_id");
    $stmt->execute(['id' => $collection_id, 'user_id' => $user_id]);
    $collection = $stmt->fetch();

    if (!$collection) {
        echo json_encode(['success' => false, 'error' => 'Збірка не знайдена']);
        exit;
    }

    $name_lc = mb_strtolower($collection['name'], 'UTF-8');
    if (in_array($name_lc, ['прочитано', 'улюблене'])) {
        echo json_encode(['success' => false, 'error' => 'Цю збірку не можна видалити']);
        exit;
    }

    // Видалення робіт зі збірки
    $stmt = $pdo->prepare("DELETE FROM collection_works WHERE collection_id = :id");
    $stmt->execute(['id' => $collection_id]);

    // Видалення самої збірки
    $stmt = $pdo->prepare("DELETE FROM collections WHERE id = :id");
    $stmt->execute(['id' => $collection_id]);

    echo json_encode(['success' => true]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Помилка БД: ' . $e->getMessage()]);
}
