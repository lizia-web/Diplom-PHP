<?php include "session.php"; ?>

<?php
$collection_id = $_GET['collection_id'] ?? null;

if (!$collection_id || !is_numeric($collection_id)) {
    echo "Невірна збірка.";
    exit;
}

$dsn = "pgsql:host=localhost;dbname=postgres;port=5432";
$db_username = "postgres";
$db_password = "1131";

try {
    $pdo = new PDO($dsn, $db_username, $db_password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

    // Назва збірки
    $stmt = $pdo->prepare("SELECT name FROM collections WHERE id = :id");
    $stmt->execute(['id' => $collection_id]);
    $collection = $stmt->fetch();

    if (!$collection) {
        echo "Збірка не знайдена.";
        exit;
    }

    // Роботи у збірці
    $stmt = $pdo->prepare("
        SELECT w.*
        FROM works w
        JOIN collection_works cw ON w.id = cw.work_id
        WHERE cw.collection_id = :collection_id
        ORDER BY w.created_at DESC
    ");
    $stmt->execute(['collection_id' => $collection_id]);
    $works = $stmt->fetchAll();

} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage();
    exit;
}
?>

<!doctype html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <title>Збірка: <?= htmlspecialchars($collection['name']) ?></title>
    <link rel="stylesheet" href="../css/base.css">
    <link rel="stylesheet" href="../css/fanfics.css">
    <link rel="stylesheet" href="../css/collection_works.css">
</head>
<body>
<div class="container">

    <?php include "menu.php" ?>

    <div class="content">

        <?php

        try {
            $pdo = new PDO($dsn, $db_username, $db_password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
            ]);

            // Роботи у збірці
            $stmt = $pdo->prepare("
        SELECT w.*
        FROM works w
        JOIN collection_works cw ON w.id = cw.work_id
        WHERE cw.collection_id = :collection_id
        ORDER BY w.created_at DESC
    ");
            $stmt->execute(['collection_id' => $collection_id]);
            $works = $stmt->fetchAll();

            include "fanf_menu.php";

        } catch (PDOException $e) {
            echo "Database error: " . $e->getMessage();
            exit;
        }

        global $works;
        global $works_in;
        $block_title = "Збірка: $collection[name]";
        $show_remove_button = true;
        $current_collection_name = $collection['name'];


        include "fanfics.php"
        ?>

        <?php include "footer.php"?>

    </div>
</div>
</body>
</html>
