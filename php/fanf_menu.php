<?php
global $user_id;
// Підключення до БД
$dsn = "pgsql:host=localhost;dbname=postgres;port=5432";
$db_username = "postgres";
$db_password = "1131";

try {
    $pdo = new PDO($dsn, $db_username, $db_password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
// Отримуємо всі collection_id поточного користувача
    $stmt = $pdo->prepare("
    SELECT c.id, c.name, cw.work_id
    FROM collections c
    JOIN collection_works cw ON c.id = cw.collection_id
    WHERE c.user_id = :user_id AND c.name IN ('Прочитано', 'Улюблене')
");
    $stmt->execute(['user_id' => $user_id]);
    $in_collections = $stmt->fetchAll();

    $works_in = [
        'прочитано' => [],
        'улюблене' => []
    ];
    foreach ($in_collections as $row) {
        $name = mb_strtolower($row['name'], 'UTF-8');
        $works_in[$name][] = $row['work_id'];
    }

} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage();
    exit;
} ?>
