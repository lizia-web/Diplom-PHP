<?php include "session.php"; ?>

<?php
global $user_id;

$dsn = "pgsql:host=localhost;dbname=postgres;port=5432";
$db_username = "postgres";
$db_password = "1131";

try {
    $pdo = new PDO($dsn, $db_username, $db_password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

    $stmt = $pdo->prepare("SELECT id, name FROM collections WHERE user_id = :user_id ORDER BY created_at ASC ");
    $stmt->execute(['user_id' => $user_id]);
    $collections = $stmt->fetchAll();

} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage();
    exit;
}
?>

<!doctype html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <title>Мої збірки</title>
    <link rel="stylesheet" href="../css/base.css">
    <link rel="stylesheet" href="../css/my_collections.css">
    <link rel="stylesheet" href="../css/footer.css">
</head>
<script src="../js/my_collections.js"></script>
<body>
<div class="container">

    <?php include "menu.php" ?>

    <div class="content">
        <h2>Мої збірки</h2>
        <div class="collections">
            <?php if (empty($collections)): ?>
                <p style="color:white; padding: 15px; text-align: center; font-size: 20px;">У вас ще немає збірок.</p>
            <?php else: ?>
                <ul class="collection-list">
                    <?php foreach ($collections as $collection): ?>
                        <li>
                            <a href="collection_works.php?collection_id=<?= $collection['id'] ?>">
                                <?= htmlspecialchars($collection['name']) ?>
                            </a>
                            <?php
                            $name_lc = mb_strtolower($collection['name'], 'UTF-8');
                            if (!in_array($name_lc, ['прочитано', 'улюблене'])): ?>
                                <span class="delete-btn" onclick="confirmDelete(<?= $collection['id'] ?>)">✖</span>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>

            <?php endif; ?>
        </div>

        <?php include "footer.php" ?>
    </div>

</div>


</body>
</html>
