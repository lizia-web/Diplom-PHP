<?php include "session.php"; ?>

<?php
global $user_id;
$guest_id = $_GET['id'] ?? null;

if ($user_id == $guest_id){
    header("Location: account.php");
}

if (!$guest_id || !is_numeric($guest_id)) {
    echo "<p>Невірний користувач.</p>";
    exit;
}

$is_favourite = false;


$dsn = "pgsql:host=localhost;dbname=postgres;port=5432";
$db_username = "postgres";
$db_password = "1131";

try {
    $pdo = new PDO($dsn, $db_username, $db_password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

    // Дані користувача
    $stmt = $pdo->prepare("SELECT name, profile_photo, cover_photo, created_at, about FROM users WHERE id = :id");
    $stmt->execute(['id' => $guest_id]);
    $user = $stmt->fetch();

    if (!$user) {
        echo "<p>Користувача не знайдено.</p>";
        exit;
    }

    if ($guest_id && $user_id && $guest_id != $user_id) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM favourite_authors WHERE user_id = :user_id AND author_id = :author_id");
        $stmt->execute(['user_id' => $user_id, 'author_id' => $guest_id]);
        $is_favourite = $stmt->fetchColumn() > 0;
    }


    $registration_date = $user['created_at'] ? date("d.m.Y", strtotime($user['created_at'])) : '01.01.2025';

    // Витягуємо роботи користувача
    $stmt = $pdo->prepare("SELECT * FROM works WHERE user_id = :user_id ORDER BY created_at DESC");
    $stmt->execute(['user_id' => $guest_id]);
    $works = $stmt->fetchAll();

} catch (PDOException $e) {
    echo "<p>Помилка бази даних: " . $e->getMessage() . "</p>";
    exit;
}

// Обробка додавання або видалення автора з улюблених
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_favourite_author']) && $_POST['author_id'] == $guest_id) {
        $stmt = $pdo->prepare("INSERT INTO favourite_authors (user_id, author_id) VALUES (:user_id, :author_id)");
        $stmt->execute(['user_id' => $user_id, 'author_id' => $guest_id]);
        $is_favourite = true;
    }

    if (isset($_POST['remove_favourite_author']) && $_POST['author_id'] == $guest_id) {
        $stmt = $pdo->prepare("DELETE FROM favourite_authors WHERE user_id = :user_id AND author_id = :author_id");
        $stmt->execute(['user_id' => $user_id, 'author_id' => $guest_id]);
        $is_favourite = false;
    }
}



?>

<!doctype html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <title>Профіль користувача</title>
    <link rel="stylesheet" href="../css/base.css">
    <link rel="stylesheet" href="../css/account.css">
    <link rel="stylesheet" href="../css/fanfics.css">
</head>
<body>
<div class="container">

    <?php include "menu.php"; ?>

    <div class="content">

        <div class="profile-header">
            <div class="background">
                <?php if (!empty($user['cover_photo'])): ?>
                    <img src="data:image/png;base64,<?= base64_encode(stream_get_contents($user['cover_photo'])) ?>" alt="Фон профілю">
                <?php else: ?>
                    <img src="https://via.placeholder.com/1200x200" alt="Фон профілю">
                <?php endif; ?>
            </div>

            <div class="profile-photo">
                <?php if (!empty($user['profile_photo'])): ?>
                    <img src="data:image/png;base64,<?= base64_encode(stream_get_contents($user['profile_photo'])) ?>" alt="Фото профілю">
                <?php else: ?>
                    <img src="https://via.placeholder.com/100" alt="Фото профілю">
                <?php endif; ?>
            </div>

            <div class="profile-info">
                <h1><?= htmlspecialchars($user['name']) ?></h1>
                <p>Дата реєстрації: <?= htmlspecialchars($registration_date) ?></p>
            </div>
        </div>

        <?php if ($guest_id != $user_id): ?>
            <div class="favourite-author">
                <?php if (!$is_favourite): ?>
                    <form method="post">
                        <input type="hidden" name="add_favourite_author" value="1">
                        <input type="hidden" name="author_id" value="<?= $guest_id ?>">
                        <button type="submit">Додати до улюблених авторів</button>
                    </form>
                <?php else: ?>
                    <form method="post">
                        <input type="hidden" name="remove_favourite_author" value="1">
                        <input type="hidden" name="author_id" value="<?= $guest_id ?>">
                        <button type="submit">Прибрати з улюблених авторів</button>
                    </form>
                <?php endif; ?>
            </div>
        <?php endif; ?>



        <div class="about-section">
            <p style="font-size: 20px; font-weight: bold;">Про себе:</p>
            <p><?= nl2br(htmlspecialchars($user['about'] ?? '')) ?></p>
        </div>

        <?php
        try {
            $pdo = new PDO($dsn, $db_username, $db_password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
            ]);

            // Витягуємо роботи користувача
            $stmt = $pdo->prepare("SELECT * FROM works WHERE user_id = :user_id ORDER BY created_at DESC");
            $stmt->execute(['user_id' => $guest_id]);
            $works = $stmt->fetchAll();

            include "fanf_menu.php";

        } catch (PDOException $e) {
            echo "<p>Помилка бази даних: " . $e->getMessage() . "</p>";
            exit;
        }

        global $works;
        global $works_in;
        $block_title = "Твори користувача";
        include "fanfics.php";
        ?>

        <?php include "footer.php"; ?>
    </div>

</div>

</body>
</html>
