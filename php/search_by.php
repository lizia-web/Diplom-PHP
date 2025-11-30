<?php
include "session.php";


$dsn = "pgsql:host=localhost;dbname=postgres;port=5432";
$db_username = "postgres";
$db_password = "1131";

header('Content-Type: text/html; charset=UTF-8');

try {
    $pdo = new PDO($dsn, $db_username, $db_password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

    $fandoms = $pdo->query("SELECT * FROM fandoms ORDER BY name")->fetchAll();
    $characters = $pdo->query("SELECT * FROM characters ORDER BY name")->fetchAll();
    $directions = $pdo->query("SELECT * FROM directions ORDER BY name")->fetchAll();
    $genres = $pdo->query("SELECT * FROM genres ORDER BY name")->fetchAll();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $title = isset($_POST['title']) ? mb_convert_encoding(trim($_POST['title']), 'UTF-8', 'auto') : '';
        $description = isset($_POST['description']) ? mb_convert_encoding(trim($_POST['description']), 'UTF-8', 'auto') : '';
        $fandom_id = isset($_POST['fandom_id']) ? (int)$_POST['fandom_id'] : null;

        $character_ids = !empty($_POST['characters']) ? explode(',', $_POST['characters']) : [];
        $pairing_ids = !empty($_POST['pairing']) ? explode(',', $_POST['pairing']) : [];

        $direction_id = isset($_POST['direction_id']) ? (int)$_POST['direction_id'] : null;
        $genre_id = isset($_POST['genre_id']) ? (int)$_POST['genre_id'] : null;


        // Отримуємо назви фандому, напрямку, жанру

        $fandom = $pdo->prepare("SELECT name FROM fandoms WHERE id = :id");
        $fandom->execute(['id' => $fandom_id]);
        $fandom_name = $fandom->fetchColumn();


        $direction = $pdo->prepare("SELECT name FROM directions WHERE id = :id");
        $direction->execute(['id' => $direction_id]);
        $direction_name = $direction->fetchColumn();

        $genre = $pdo->prepare("SELECT name FROM genres WHERE id = :id");
        $genre->execute(['id' => $genre_id]);
        $genre_name = $genre->fetchColumn();

        // Отримуємо імена персонажів
        if (!empty($character_ids)) {
            $in = str_repeat('?,', count($character_ids) - 1) . '?';
            $stmt = $pdo->prepare("SELECT name FROM characters WHERE id IN ($in)");
            $stmt->execute($character_ids);
            $character_names = $stmt->fetchAll(PDO::FETCH_COLUMN);
            $characters_str = implode(', ', $character_names);
        }

        $title = mb_convert_encoding($title, 'UTF-8', 'auto');
        $description = mb_convert_encoding($description, 'UTF-8', 'auto');

        // Формуємо пейринги
        $pairing_str = '';
        if (!empty($pairing_ids)) {
            $stmt = $pdo->prepare("SELECT name FROM characters WHERE id = ?");
            foreach ($pairing_ids as $pair) {
                if (strpos($pair, '/') !== false) {
                    [$id1, $id2] = explode('/', $pair);
                    $stmt->execute([$id1]);
                    $name1 = $stmt->fetchColumn();
                    $stmt->execute([$id2]);
                    $name2 = $stmt->fetchColumn();
                    if ($name1 && $name2) {
                        $pairing_str .= "$name1/$name2, ";
                    }
                }
            }
            $pairing_str = rtrim($pairing_str, ', ');
        }
    }

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
    <link rel="stylesheet" href="../css/create_work.css">
    <link rel="stylesheet" href="../css/fanfics.css">
    <title>Пошук за смаком</title>
</head>
<body>

<div class="container">
    <?php include "menu.php"; ?>

    <div class="content">

        <div class="forms">

            <h2>Пошук за смаком</h2>

            <form id="taste-form">

            <label>Назва:</label>
                <input type="text" name="title" MAXLENGTH="255" >

                <label>Опис:</label>
                <textarea name="description" MAXLENGTH="255" ></textarea>
                <br>

                <label>Фандом:</label>
                <select name="fandom_id" id="fandom-select" onchange="filterCharactersByFandom()">
                    <option value="">Оберіть фандом</option>
                    <?php foreach ($fandoms as $f): ?>
                        <option value="<?= $f['id'] ?>"><?= htmlspecialchars($f['name']) ?></option>
                    <?php endforeach; ?>
                </select>


                <label>Персонажі:</label>
                <select id="character-select"></select>
                <button type="button" onclick="addCharacter()">Додати</button>
                <div id="character-list"></div>
                <input type="hidden" name="characters" id="characters-hidden">

                <label>Пейринг:</label>
                <select id="pairing-select"></select>
                <button type="button" onclick="addPairing()">Додати</button>
                <div id="pairing-list"></div>
                <input type="hidden" name="pairing" id="pairing-hidden">


                <label>Напрямок:</label>
                <select name="direction_id" >
                    <option value="">Оберіть напрямок</option>
                    <?php foreach ($directions as $d): ?>
                        <option value="<?= $d['id'] ?>"><?= htmlspecialchars($d['name']) ?></option>
                    <?php endforeach; ?>
                </select>

                <label>Жанр:</label>
                <select name="genre_id" >
                    <option value="">Оберіть жанр</option>
                    <?php foreach ($genres as $g): ?>
                        <option value="<?= $g['id'] ?>"><?= htmlspecialchars($g['name']) ?></option>
                    <?php endforeach; ?>
                </select>

                <button type="submit">Пошук</button>
            </form>

        </div>
        <div id="search-results"></div>


        <?php include "footer.php"; ?>
    </div>

</div>

<script>
    const allCharacters = <?= json_encode($characters, JSON_UNESCAPED_UNICODE) ?>;
</script>
<script src="../js/create_work.js"></script>
<script src="../js/search_by.js"></script>
<!--<script src="../js/fanfics.js"></script>-->


</body>
</html>
