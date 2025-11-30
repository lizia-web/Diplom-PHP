<?php
include "session.php";

global $user_id;
$dsn = "pgsql:host=localhost;dbname=postgres;port=5432";
$db_username = "postgres";
$db_password = "1131";

try {
    $pdo = new PDO($dsn, $db_username, $db_password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

    $stmt = $pdo->prepare("SELECT * FROM works WHERE user_id = :user_id ORDER BY created_at DESC");
    $stmt->execute(['user_id' => $user_id]);
    $all_works = $stmt->fetchAll();

    include "fanf_menu.php";


} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage();
    exit;
}
?>



<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="../css/base.css">
    <link rel="stylesheet" href="../css/my_works.css">
    <link rel="stylesheet" href="../css/fanfics.css">
    <title>Мої твори</title>
</head>
<body>

<div class="container">

    <?php include "menu.php" ?>

    <div class="content">

        <div class="work-tabs">
            <button class="tab-button" onclick="showTab('published')">Опубліковані</button>
            <button class="tab-button" onclick="showTab('drafts')">Чернетки</button>
        </div>

        <div id="published" class="tab-content">
            <?php
            $is_my_works = true;
            global $works;
            global $works_in;
            $published = array_filter($all_works, function($w) { return $w['draft'] == 1; });

            $works = $published;
            $block_title = "Опубліковані твори";
            include "fanfics.php";
            ?>
        </div>

        <div id="drafts" class="tab-content" style="display: none;">
            <?php
            global $works;
            global $works_in;
            $is_my_works = true;
            $drafts = array_filter($all_works, function($w) { return $w['draft'] == 0; });
            $works = $drafts;
            $block_title = "Чернетки";

            include "fanfics.php";
            ?>
        </div>

        <?php include "footer.php" ?>

    </div>
</div>
<script src="../js/my_works.js"></script>
</body>
</html>
