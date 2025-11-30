<?php include "session.php"?>

<?php
global $user_id;

// Підключення до бази даних
$dsn = "pgsql:host=localhost;dbname=postgres;port=5432";
$db_username = "postgres";
$db_password = "1131";

try {
    $pdo = new PDO($dsn, $db_username, $db_password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

    // Отримуємо список улюблених авторів поточного користувача
    $stmt = $pdo->prepare("
        SELECT u.id, u.name, u.profile_photo
        FROM favourite_authors fa
        JOIN users u ON fa.author_id = u.id
        WHERE fa.user_id = :user_id
    ");
    $stmt->execute(['user_id' => $user_id]);
    $favourite_authors = $stmt->fetchAll();

} catch (PDOException $e) {
    echo "<p>Помилка бази даних: " . $e->getMessage() . "</p>";
    exit;
}
?>

<!doctype html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <title>Улюблені автори</title>
    <link rel="stylesheet" href="../css/base.css">
    <link rel="stylesheet" href="../css/favourite_authors.css">
</head>
<body>

<div class="container">

    <?php include "menu.php"?>

    <div class="content">
        <h1>Улюблені автори</h1>

        <?php if (empty($favourite_authors)): ?>
            <p class="content-p">У вас ще немає улюблених авторів.</p>
        <?php else: ?>
            <div class="authors-list">
                <?php foreach ($favourite_authors as $author): ?>
                    <div class="author-card">
                        <a href="guest_profile.php?id=<?= $author['id'] ?>">
                            <?php if (!empty($author['profile_photo'])): ?>
                                <img class="author-photo" src="data:image/png;base64,<?= base64_encode(stream_get_contents($author['profile_photo'])) ?>" alt="Фото профілю">
                            <?php else: ?>
                                <img class="author-photo" src="https://via.placeholder.com/80" alt="Фото профілю">
                            <?php endif; ?>
                            <p class="name"><?= htmlspecialchars($author['name']) ?></p>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php include "footer.php"?>
    </div>

</div>
</body>
</html>
