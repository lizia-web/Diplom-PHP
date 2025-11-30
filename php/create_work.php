<?php include "session.php"?>

<?php

$dsn = "pgsql:host=localhost;dbname=postgres;port=5432";
$db_username = "postgres";
$db_password = "1131";

header('Content-Type: text/html; charset=UTF-8');

try {
    $pdo = new PDO($dsn, $db_username, $db_password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

    // Завантаження опцій
    $fandoms = $pdo->query("SELECT * FROM fandoms ORDER BY name")->fetchAll();
    $characters = $pdo->query("SELECT * FROM characters ORDER BY name")->fetchAll();
    $directions = $pdo->query("SELECT * FROM directions ORDER BY name")->fetchAll();
    $genres = $pdo->query("SELECT * FROM genres ORDER BY name")->fetchAll();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $fandom_id = !empty($_POST['fandom_id']) ? (int)$_POST['fandom_id'] : null;
        $character_ids = !empty($_POST['characters']) ? explode(',', $_POST['characters']) : [];
        $pairing_ids = !empty($_POST['pairing']) ? explode(',', $_POST['pairing']) : [];
        $direction_id = (int)($_POST['direction_id'] ?? 0);
        $genre_id = (int)($_POST['genre_id'] ?? 0);

        // Назви фандому, напрямку, жанру
        $fandom_name = 'Оріджин';
        if ($fandom_id) {
            $stmt = $pdo->prepare("SELECT name FROM fandoms WHERE id = :id");
            $stmt->execute(['id' => $fandom_id]);
            $fandom_name = $stmt->fetchColumn() ?: $fandom_name;
        }

        $direction_name = '';
        if ($direction_id) {
            $stmt = $pdo->prepare("SELECT name FROM directions WHERE id = :id");
            $stmt->execute(['id' => $direction_id]);
            $direction_name = $stmt->fetchColumn();
        }

        $genre_name = '';
        if ($genre_id) {
            $stmt = $pdo->prepare("SELECT name FROM genres WHERE id = :id");
            $stmt->execute(['id' => $genre_id]);
            $genre_name = $stmt->fetchColumn();
        }

        // Формуємо пейринги
        $pairing_str = '-';
        if (!empty($pairing_ids)) {
            $stmt = $pdo->prepare("SELECT name FROM characters WHERE id = ?");
            $pairs = [];
            foreach ($pairing_ids as $pair) {
                if (strpos($pair, '/') !== false) {
                    [$id1, $id2] = explode('/', $pair);
                    $stmt->execute([$id1]);
                    $name1 = $stmt->fetchColumn();
                    $stmt->execute([$id2]);
                    $name2 = $stmt->fetchColumn();
                    if ($name1 && $name2) {
                        $pairs[] = "$name1/$name2";
                    }
                }
            }
            $pairing_str = implode(', ', $pairs) ?: '-';
        }

        // Додавання твору
        $stmt = $pdo->prepare("INSERT INTO works (user_id, title, fandom, pairing, characters, direction, genre, description, created_at)
                           VALUES (:user_id, :title, :fandom, :pairing, '', :direction, :genre, :description, now())");

        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':title', $title);
        $stmt->bindParam(':fandom', $fandom_name);
        $stmt->bindParam(':pairing', $pairing_str);
        $stmt->bindParam(':direction', $direction_name);
        $stmt->bindParam(':genre', $genre_name);
        $stmt->bindParam(':description', $description);
        $stmt->execute();

        // Отримуємо ID нового твору
        $work_id = $pdo->lastInsertId("works_id_seq");

        // Додаємо персонажів до зв’язкової таблиці
        if (!empty($character_ids)) {
            $insertCharacter = $pdo->prepare("INSERT INTO work_characters (work_id, character_id) VALUES (:work_id, :character_id)");
            foreach ($character_ids as $char_id) {
                $insertCharacter->execute([
                    'work_id' => $work_id,
                    'character_id' => (int)$char_id
                ]);
            }
        }

        header("Location: update_work.php?id=" . $work_id);
        exit;
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
    <link rel="stylesheet" href="../css/footer.css">
    <title>Створити твір</title>
</head>
<body>

<div class="container">

    <?php include "menu.php"?>

    <div class="content">

        <div class="forms">

            <h2>Додати новий твір</h2>

            <form method="post" enctype="multipart/form-data">
                <label>Назва:</label>
                <input type="text" name="title" MAXLENGTH="255" required>

                <label>Опис:</label>
                <textarea name="description" MAXLENGTH="500" required></textarea>
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
                <select name="direction_id" required>
                    <option value="">Оберіть напрямок</option>
                    <?php foreach ($directions as $d): ?>
                        <option value="<?= $d['id'] ?>"><?= htmlspecialchars($d['name']) ?></option>
                    <?php endforeach; ?>
                </select>

                <label>Жанр:</label>
                <select name="genre_id" required>
                    <option value="">Оберіть жанр</option>
                    <?php foreach ($genres as $g): ?>
                        <option value="<?= $g['id'] ?>"><?= htmlspecialchars($g['name']) ?></option>
                    <?php endforeach; ?>
                </select>

                <button type="submit">Створити</button>
            </form>

        </div>


        <?php include "footer.php"?>
    </div>


</div>
<script>
    const allCharacters = <?= json_encode($characters, JSON_UNESCAPED_UNICODE) ?>;
</script>

<script src="../js/create_work.js"></script>

</body>
</html>
