<?php include "session.php" ?>

<?php
global $user_id;

$work_id = $_POST['work_id'] ?? null;
$collection_name = $_POST['collection_name'] ?? '';

if (!$work_id || !$collection_name) {
    echo json_encode(['success' => false, 'error' => 'Некоректні дані']);
    exit;
}

$dsn = "pgsql:host=localhost;dbname=postgres;port=5432";
$db_username = "postgres";
$db_password = "1131";

try {
    $pdo = new PDO($dsn, $db_username, $db_password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

    // Отримати id збірки з таким ім’ям для користувача
    $stmt = $pdo->prepare("SELECT id FROM collections WHERE user_id = :user_id AND name = :name");
    $stmt->execute([
        'user_id' => $user_id,
        'name' => $collection_name
    ]);
    $collection = $stmt->fetch();

    if (!$collection) {
        echo json_encode(['success' => false, 'error' => 'Збірка не знайдена']) ;
        exit;
    }

    $collection_id = $collection['id'];

    // Перевірити, чи вже додано
    $stmt = $pdo->prepare("SELECT id FROM collection_works WHERE collection_id = :collection_id AND work_id = :work_id");
    $stmt->execute([
        'collection_id' => $collection_id,
        'work_id' => $work_id
    ]);
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'error' => 'Робота вже у збірці']);
        exit;
    }

    // Додати до collection_works
    $stmt = $pdo->prepare("INSERT INTO collection_works (collection_id, work_id) VALUES (:collection_id, :work_id)");
    $stmt->execute([
        'collection_id' => $collection_id,
        'work_id' => $work_id
    ]);

    echo json_encode(['success' => true]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Помилка БД: ' . $e->getMessage()]);
}

?>
