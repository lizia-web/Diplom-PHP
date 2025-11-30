<?php
include "session.php";

require_once __DIR__ . '/../libs/htmlpurifier-4.15.0/library/HTMLPurifier.auto.php';

// Отримуємо ID твору з параметру GET
$work_id = $_GET['id'] ?? null;

if (!$work_id) {
    header("Location: account.php");
    exit;
}

// Підключення до бази
$dsn = "pgsql:host=localhost;dbname=postgres;port=5432";
$db_username = "postgres";
$db_password = "1131";

try {
    $pdo = new PDO($dsn, $db_username, $db_password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

    // Отримуємо поточний контент
    $stmt = $pdo->prepare("SELECT content, title, draft FROM works WHERE id = :id");
    $stmt->execute(['id' => $work_id]);
    $work = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$work) {
        echo "Твір не знайдено.";
        exit;
    }

} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage();
    exit;
}

function purify_html($dirty_html) {
    static $purifier = null;

    if ($purifier === null) {
        $config = HTMLPurifier_Config::createDefault();
        $config->set('HTML.Allowed', 'p,b,i,u,div,span,br,strong,em,s,sub,sup,ul,ol,li,blockquote,hr,pre,code,style');
        $purifier = new HTMLPurifier($config);
    }

    return $purifier->purify($dirty_html);
}

// Збереження змін
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['content'])) {
    $new_content = $_POST['content'];
    $stmt = $pdo->prepare("UPDATE works SET content = :content WHERE id = :id");
    $stmt->execute([
        'content' => $new_content,
        'id' => $work_id
    ]);

    // Якщо це зміна чернетки, перенаправлення на ту ж сторінку
    if (isset($_POST['toggle_draft'])) {
        $new_draft_value = ($_POST['toggle_draft'] == '1') ? 1 : 0;
        $stmt = $pdo->prepare("UPDATE works SET draft = :draft WHERE id = :id");
        $stmt->execute([
            'draft' => $new_draft_value,
            'id' => $work_id
        ]);
        header("Location: update_work.php?id=$work_id");
        exit;
    }

    // Якщо це просто збереження
    header("Location: read.php?id=$work_id");
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
    <link rel="stylesheet" href="../css/update_work.css">
    <title>Редагувати твір</title>
</head>
<body>

<div class="container">

    <?php include "menu.php" ?>

    <div class="content">

        <h2>Редагування твору: "<?= $work['title'] ?>"</h2>
        <form method="post" id="editForm">

        <div class="update">
                <div class="toolbar">
                    <button type="button" onclick="format('bold')"><b>B</b></button>
                    <button type="button" onclick="format('italic')"><i>K</i></button>
                    <button type="button" onclick="format('underline')"><u>U</u></button>
                    <button type="button" onclick="format('strikeThrough')"><s>S</s></button>
                    <button type="button" onclick="format('justifyLeft')">Ліворуч</button>
                    <button type="button" onclick="format('justifyCenter')">По центру</button>
                    <button type="button" onclick="format('justifyRight')">Праворуч</button>
                </div>

                <div id="editor" contenteditable="true"><?= $work['content'] ?></div>

                <textarea name="content" id="content" hidden></textarea>
                <br>

                <div class="toolbar">
                    <button type="submit" name="save">Зберегти</button>
                    <?php if ($work['draft'] == 0): ?>
                        <button type="submit" name="toggle_draft" value="1">Опублікувати</button>
                    <?php else: ?>
                        <button type="submit" name="toggle_draft" value="0">Зробити чернеткою</button>
                    <?php endif; ?>
                    <a href="read.php?id=<?= $work_id ?>">
                        <button type="button">Перейти до твору</button>
                    </a>
                </div>
            </div>
        </form>


    </div>

</div>

<script src="../js/update_work.js"></script>

</body>
</html>
