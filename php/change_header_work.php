<?php
include "session.php";

if (!isset($_GET['id'])) {
    echo "Не передано ID твору.";
    exit;
}
$work_id = $_GET['id'];

$dsn = "pgsql:host=localhost;dbname=postgres;port=5432";
$db_username = "postgres";
$db_password = "1131";

try {
    $pdo = new PDO($dsn, $db_username, $db_password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

    // Отримання всіх фандомів, жанрів, напрямків
    $fandoms = $pdo->query("SELECT * FROM fandoms")->fetchAll();
    $genres = $pdo->query("SELECT * FROM genres")->fetchAll();
    $directions = $pdo->query("SELECT * FROM directions")->fetchAll();
    $characters = $pdo->query("SELECT * FROM characters")->fetchAll();


// Завантаження даних про твір
    $stmt = $pdo->prepare("SELECT * FROM works WHERE id = :id");
    $stmt->execute(['id' => $work_id]);
    $work = $stmt->fetch();

    if (!$work) {
        echo "Твір не знайдено.";
        exit;
    }


} catch (PDOException $e) {
    echo "Помилка бази даних: " . $e->getMessage();
    exit;
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Отримання оновлених даних
    $title = $_POST['title'] ?? $work['title'];
    $description = $_POST['description'] ?? $work['description'];
    $fandom_id = $_POST['fandom_id'];
    $characters = $_POST['characters'] ?? $work['characters'];
    $pairing = $_POST['pairing'] ?? $work['pairing'];
    $direction_id = $_POST['direction_id'];
    $genre_id = $_POST['genre_id'];

    $stmt = $pdo->prepare("SELECT name FROM fandoms WHERE id = :id");
    $stmt->execute(['id' => $fandom_id]);
    $fandom = $stmt->fetch();

    $stmt = $pdo->prepare("SELECT name FROM directions WHERE id = :id");
    $stmt->execute(['id' => $direction_id]);
    $direction = $stmt->fetch();

    $stmt = $pdo->prepare("SELECT name FROM genres WHERE id = :id");
    $stmt->execute(['id' => $genre_id]);
    $genre = $stmt->fetch();

    // Оновлення твору
    $stmt = $pdo->prepare("UPDATE works 
        SET title = :title, description = :description,          
            direction = :direction, genre = :genre
        WHERE id = :id");

    $stmt->execute([
        'title' => $title,
        'description' => $description,
//        'fandom' => $fandom['name'],
//        'characters' => $characters,
//        'pairing' => $pairing,
        'direction' => $direction['name'],
        'genre' => $genre['name'],
        'id' => $work_id
    ]);

    header("Location: read.php?id=" . $work_id);
    exit;
}
?>

<!doctype html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <title>Редагувати шапку твору</title>
    <link rel="stylesheet" href="../css/base.css">
    <link rel="stylesheet" href="../css/create_work.css">
    <script src="../js/create_work.js"></script> <!-- Якщо у вас є JS для додавання персонажів -->
</head>
<body>
<div class="container">
    <?php include "menu.php"; ?>
    <div class="content">
        <div class="forms">
            <h2>Редагувати шапку твору</h2>

            <form method="post" enctype="multipart/form-data">
                <label>Назва:</label>
                <input type="text" name="title" value="<?= htmlspecialchars($work['title']) ?>" required>

                <label>Опис:</label>
                <textarea name="description" maxlength="255" required><?= htmlspecialchars($work['description']) ?></textarea>

                <label>Фандом:</label>
                <input type="text" name="fandoms" value="<?= htmlspecialchars($work['fandom']) ?>" disabled>


                <label>Персонажі:</label>
                <input type="text" name="characters" value="<?= htmlspecialchars($work['characters']) ?>" disabled>

                <label>Пейринг:</label>
                <input type="text" name="pairing" value="<?= htmlspecialchars($work['pairing']) ?>" disabled>


                <label>Напрямок:</label>
                <select name="direction_id">
                    <?php foreach ($directions as $d): ?>
                        <option value="<?= $d['id'] ?>" <?= $d['name'] == $work['direction'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($d['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <label>Жанр:</label>
                <select name="genre_id">
                    <?php foreach ($genres as $g): ?>
                        <option value="<?= $g['id'] ?>" <?= $g['name'] == $work['genre'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($g['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <button type="submit">Оновити</button>
            </form>
        </div>

        <?php include "footer.php"; ?>
    </div>
</div>
</body>
</html>
