<?php
include "session.php";

global $user_id;

require_once __DIR__ . '/../libs/htmlpurifier-4.15.0/library/HTMLPurifier.auto.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "Невірний ID фанфіка.";
    exit;
}


$id = (int)$_GET['id'];

$dsn = "pgsql:host=localhost;dbname=postgres;port=5432";
$db_username = "postgres";
$db_password = "1131";

try {
    $pdo = new PDO($dsn, $db_username, $db_password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

    // Отримання фанфіка по id
    $stmt = $pdo->prepare("SELECT * FROM works WHERE id = :id");
    $stmt->execute(['id' => $id]);
    $work = $stmt->fetch();
    $author_id = $work['user_id'];

    if (!$work) {
        echo "Фанфік не знайдено.";
        exit;
    }

    if ($work['draft'] == 0 && $user_id != $work['user_id']) {
        echo "Ви не маєте доступу";
        exit;
    }

    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = :id");
    $stmt->execute(['id' => $author_id]);
    $author = $stmt->fetch();

} catch (PDOException $e) {
    echo "Помилка бази даних: " . $e->getMessage();
    exit;
}

function purify_html($dirty_html) {
    static $purifier = null;

    if ($purifier === null) {
        $config = HTMLPurifier_Config::createDefault();
        $config->set('HTML.Allowed', 'p,b,i,u,div,span,br,strong,em,s,sub,sup,ul,ol,li,blockquote,hr,pre,code');
        $config->set('CSS.AllowedProperties', ['text-align', 'color', 'font-weight', 'font-style', 'text-decoration']);
        $config->set('HTML.AllowedAttributes', 'style');
        $purifier = new HTMLPurifier($config);
    }

    return $purifier->purify($dirty_html);
}


// Отримати імена персонажів твору
$character_names = [];
$stmt = $pdo->prepare("
    SELECT c.name 
    FROM characters c 
    INNER JOIN work_characters wc ON c.id = wc.character_id 
    WHERE wc.work_id = ?
");
$stmt->execute([$work['id']]);
$character_names = $stmt->fetchAll(PDO::FETCH_COLUMN);
$characters_str = $character_names ? implode(', ', $character_names) : '-';

?>

<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($work['title']) ?></title>
    <link rel="stylesheet" href="../css/base.css">
    <link rel="stylesheet" href="../css/read.css">
</head>
<body>
<div class="container">
    <?php include "menu.php" ?>

    <div class="content">

        <h1>"<?= htmlspecialchars($work['title']) ?>"</h1>

        <div class="fanf">


            <p><a style="color: #ff7fa5; font-size: 19px; font-family: Lumberjack; width: auto"> Автор:</a> <a
                        href="guest_profile.php?id=<?= $work['user_id'] ?>"><?= htmlspecialchars($author['name']) ?> </a>
            </p>
            <p><a style="color: #ff7fa5; font-size: 19px; font-family: Lumberjack; width: auto"> Фандом:</a> <?= htmlspecialchars($work['fandom']) ?></p>
            <p><a style="color: #ff7fa5; font-size: 19px; font-family: Lumberjack; width: auto"> Пейринг:</a> <?= htmlspecialchars($work['pairing']) ?></p>
            <p><a style="color: #ff7fa5">Персонажі:</a> <?= htmlspecialchars($characters_str) ?></p>
            <p><a style="color: #ff7fa5; font-size: 19px; font-family: Lumberjack; width: auto"> Напрямок:</a> <?= htmlspecialchars($work['direction']) ?></p>
            <p><a style="color: #ff7fa5; font-size: 19px; font-family: Lumberjack; width: auto"> Жанр:</a> <?= htmlspecialchars($work['genre']) ?></p>
            <p><a style="color: #ff7fa5; font-size: 19px; font-family: Lumberjack; width: auto"> Опис:</a> <?= htmlspecialchars($work['description']) ?></p>
            <p><a style="color: #ff7fa5; font-size: 19px; font-family: Lumberjack; width: auto"> Дата публікації:</a> <?= date("d.m.Y", strtotime($work['created_at'])) ?></p>
            <?php if ($user_id == $work['user_id']): ?>
                <div class="change-button-header">
                    <a href="change_header_work.php?id=<?= $work['id'] ?>">
                        <button>Редагувати шапку твору</button>
                    </a>
                </div>
            <?php endif; ?>

        </div>
        <hr>
        <div class="content-text">
            <?= purify_html($work['content']) ?>
        </div>

        <?php if ($user_id == $work['user_id']): ?>
            <div class="change-button">
                <a href="update_work.php?id=<?= $work['id'] ?>">
                    <button>Редагувати</button>
                </a>
            </div>
        <?php endif; ?>


        <?php include "footer.php" ?>
    </div>


</div>
</body>
</html>
