<?php include "session.php" ?>

<?php
global $user_id;

$dsn = "pgsql:host=localhost;dbname=postgres;port=5432";
$db_username = "postgres";
$db_password = "1131";

try {
    $pdo = new PDO($dsn, $db_username, $db_password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

    // Витягуємо всі роботи користувача
    $stmt = $pdo->prepare("SELECT * FROM works WHERE user_id = :user_id ORDER BY created_at DESC");
    $stmt->execute(['user_id' => $user_id]);
    $works = $stmt->fetchAll();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!empty($_FILES['profile_photo']['tmp_name']) && is_uploaded_file($_FILES['profile_photo']['tmp_name'])) {
            $img = file_get_contents($_FILES['profile_photo']['tmp_name']);
            $stmt = $pdo->prepare("UPDATE users SET profile_photo = :img WHERE id = :id");
            $stmt->bindParam(':img', $img, PDO::PARAM_LOB);
            $stmt->bindParam(':id', $user_id, PDO::PARAM_INT);
            $stmt->execute();
        }

        if (!empty($_FILES['cover_photo']['tmp_name']) && is_uploaded_file($_FILES['cover_photo']['tmp_name'])) {
            $img = file_get_contents($_FILES['cover_photo']['tmp_name']);
            $stmt = $pdo->prepare("UPDATE users SET cover_photo = :img WHERE id = :id");
            $stmt->bindParam(':img', $img, PDO::PARAM_LOB);
            $stmt->bindParam(':id', $user_id, PDO::PARAM_INT);
            $stmt->execute();
        }

        header("Location: account.php");
        exit;
    }


// Отримати дані користувача
    $stmt = $pdo->prepare("SELECT name, profile_photo, cover_photo, created_at, about FROM users WHERE id = :id");
    $stmt->execute(['id' => $user_id]);
    $user = $stmt->fetch();

    $registration_date = $user['created_at'] ? date("d.m.Y", strtotime($user['created_at'])) : '01.01.2025';

} catch (PDOException $e) {
    echo "<p>Помилка бази даних: " . $e->getMessage() . "</p>";
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
    <link rel="stylesheet" href="../css/account.css">
    <link rel="stylesheet" href="../css/fanfics.css">
<!--    <link rel="stylesheet" href="../css/main.css">-->
    <title>Профіль</title>
</head>
<body>
<div class="container">

    <?php include "menu.php" ?>

    <div class="content">

        <div class="profile-header">

            <input type="file" id="photo-input" style="display: none;" accept="image/*">
            <input type="file" id="background-input" style="display: none;" accept="image/*">

            <div class="background" onclick="document.getElementById('background-input').click();">
                <?php if (!empty($user['cover_photo'])): ?>
                    <?php $coverImage = stream_get_contents($user['cover_photo']); ?>
                    <img id="background-img" src="data:image/png;base64,<?= base64_encode($coverImage) ?>"
                         alt="Фон профілю">
                <?php else: ?>
                    <img id="background-img" src="https://via.placeholder.com/1200x200" alt="Фон профілю">
                <?php endif; ?>
            </div>


            <div class="profile-photo" onclick="document.getElementById('photo-input').click();">
                <?php if (!empty($user['profile_photo'])): ?>
                    <?php $profileImage = stream_get_contents($user['profile_photo']); ?>
                    <img id="profile-img" src="data:image/png;base64,<?= base64_encode($profileImage) ?>"
                         alt="Фото профілю">
                <?php else: ?>
                    <img id="profile-img" src="https://via.placeholder.com/100" alt="Фото профілю">
                <?php endif; ?>
            </div>

            <div class="profile-info">
                <h1 id="nickname"><?= htmlspecialchars($user['name']) ?></h1>
                <p id="registration-date">Дата
                    реєстрації: <?= htmlspecialchars($registration_date ?? '01.01.2025') ?></p>
            </div>
        </div>

        <div class="about-section">
            <p style="font-size: 20px; font-weight: bold;">Про себе:</p>

            <?php if (($user_id ?? null) == ($_SESSION['user_id'] ?? null)): ?>
                <div id="about-display">
                    <p id="about-text"><?= nl2br(htmlspecialchars($user['about'] ?? '')) ?></p>
                    <button type="button" onclick="toggleAboutEdit()">Редагувати</button>
                </div>

                <form action="update_profile.php" method="post" id="about-form" style="display: none;">
                    <textarea name="about" rows="5"><?= htmlspecialchars($user['about'] ?? '') ?></textarea><br>
                    <button type="submit">Зберегти</button>
                    <button type="button" onclick="cancelAboutEdit()">Скасувати</button>
                </form>
            <?php else: ?>
                <p><?= nl2br(htmlspecialchars($user['about'] ?? '')) ?></p>
            <?php endif; ?>
        </div>

        <?php

        try {
        $pdo = new PDO($dsn, $db_username, $db_password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);

        // Витягуємо всі роботи користувача
        $stmt = $pdo->prepare("SELECT * FROM works WHERE user_id = :user_id ORDER BY created_at DESC");
        $stmt->execute(['user_id' => $user_id]);
        $works = $stmt->fetchAll();

        include  "fanf_menu.php";

        } catch (PDOException $e) {
            echo "<p>Помилка бази даних: " . $e->getMessage() . "</p>";
            exit;
        }
        global $works;
        global $works_in;
        $block_title = "Мої твори";
        include "fanfics.php"

        ?>


        <?php include "footer.php" ?>

    </div>

</div>

<script src="../js/account.js"></script>
<script src="../js/main.js"></script>
</body>
</html>
