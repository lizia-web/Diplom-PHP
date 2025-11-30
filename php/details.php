<?php include "session.php"?>

<?php
$table = $_GET['table'] ?? '';
$id = $_GET['id'] ?? '';

if (!in_array($table, ['users', 'works', 'collections', 'fandoms', 'genres', 'characters']) || !is_numeric($id)) {
    echo "Невірні параметри.";
    exit;
}

$dsn = "pgsql:host=localhost;dbname=postgres;port=5432";
$db_username = "postgres";
$db_password = "1131";

try {
    $pdo = new PDO($dsn, $db_username, $db_password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

    if ($table === 'users') {
        header("Location: guest_profile.php?id=" . urlencode($id));
        exit;
    }

    if ($table === 'works') {
        $stmt = $pdo->prepare("SELECT * FROM works WHERE id = :id AND draft = 1");
        $stmt->execute(['id' => $id]);
        $work = $stmt->fetch();
        $works = $work ? [$work] : [];
        $block_title = "Твір: " . htmlspecialchars($work['title'] ?? '');
    }

    elseif ($table === 'collections') {
        $stmt = $pdo->prepare("
        SELECT w.* FROM works w WHERE draft = 1
        JOIN collection_works cw ON w.id = cw.work_id
        WHERE cw.collection_id = :id
        ORDER BY w.created_at DESC
    ");
        $stmt->execute(['id' => $id]);
        $works = $stmt->fetchAll();

        $f = $pdo->prepare("SELECT * FROM collections WHERE id = :id");
        $f->execute(['id' => $id]);
        $collection = $f->fetch();

        $block_title = "Збірка: " . htmlspecialchars($collection['name'] ?? '');
    }

    elseif ($table === 'fandoms') {
        $stmt = $pdo->prepare("SELECT * FROM works WHERE fandom ILIKE (SELECT name FROM fandoms WHERE id = :id) AND draft = 1 ORDER BY created_at DESC");
        $stmt->execute(['id' => $id]);
        $works = $stmt->fetchAll();

        $f = $pdo->prepare("SELECT name FROM fandoms WHERE id = :id");
        $f->execute(['id' => $id]);
        $fandom = $f->fetch();

        $block_title = "Фандом: " . htmlspecialchars($fandom['name'] ?? '');
    }

    elseif ($table === 'genres') {
        $stmt = $pdo->prepare("SELECT * FROM works WHERE genre ILIKE (SELECT name FROM genres WHERE id = :id) AND draft = 1 ORDER BY created_at DESC");
        $stmt->execute(['id' => $id]);
        $works = $stmt->fetchAll();

        $f = $pdo->prepare("SELECT name FROM genres WHERE id = :id");
        $f->execute(['id' => $id]);
        $genre = $f->fetch();

        $block_title = "Жанр: " . htmlspecialchars($genre['name'] ?? '');
    }

    elseif ($table === 'characters') {
        $stmt = $pdo->prepare("
        SELECT w.* FROM works w
        JOIN work_characters wc ON w.id = wc.work_id
        WHERE wc.character_id = :id AND w.draft = 1
        ORDER BY w.created_at DESC
    ");
        $stmt->execute(['id' => $id]);
        $works = $stmt->fetchAll();

        $f = $pdo->prepare("SELECT name FROM characters WHERE id = :id");
        $f->execute(['id' => $id]);
        $character = $f->fetch();

        $block_title = "Персонаж: " . htmlspecialchars($character['name'] ?? '');
    }

    if (!isset($works)) {
        $works = [];
    }

    $works_in = ['прочитано' => [], 'улюблене' => []];
    if (isset($_SESSION['user_id'])) {
        $stmt = $pdo->prepare("
            SELECT c.name, cw.work_id FROM collections c
            JOIN collection_works cw ON c.id = cw.collection_id
            WHERE c.user_id = :user_id AND c.name IN ('Прочитано', 'Улюблене')
        ");
        $stmt->execute(['user_id' => $_SESSION['user_id']]);
        while ($row = $stmt->fetch()) {
            $key = mb_strtolower($row['name'], 'UTF-8');
            $works_in[$key][] = $row['work_id'];
        }
    }

} catch (PDOException $e) {
    echo "Помилка БД: " . $e->getMessage();
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
    <link rel="stylesheet" href="../css/details.css">
    <link rel="stylesheet" href="../css/footer.css">
    <title>Результати пошуку</title>
</head>
<body>

<div class="container">

    <?php include "menu.php"?>

    <div class="content">

        <?php
        global $works, $works_in;
        include "fanfics.php";
        ?>


        <?php include "footer.php"?>
    </div>

</div>

</body>
</html>
