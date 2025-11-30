<?php
$dsn = "pgsql:host=localhost;dbname=postgres;port=5432";
$db_username = "postgres";
$db_password = "1131";

// Отримуємо пошуковий запит
$q = trim($_GET['q'] ?? '');

if (strlen($q) < 3) {
    exit;
}

try {
    $pdo = new PDO($dsn, $db_username, $db_password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    $query2 = "%$q%";
    $q = mb_convert_case($q, MB_CASE_TITLE, "UTF-8");
    $query = "%$q%";

    $results = [];

    $stmt = $pdo->prepare(
        "SELECT id, title FROM works 
                 WHERE (title ILIKE :q OR title ILIKE :q2) AND draft = 1
                 ORDER BY created_at DESC LIMIT 10");
    $stmt->execute([
        'q' => $query,
        'q2' => $query2]);
    $results['works'] = $stmt->fetchAll();

    //пошук у жанрах

    $stmt = $pdo->prepare(
        "SELECT id, name FROM genres 
                 WHERE (name ILIKE :q OR name ILIKE :q2)
                 LIMIT 10");
    $stmt->execute([
        'q' => $query,
        'q2' => $query2]);
    $results['genres'] = $stmt->fetchAll();

    $stmt = $pdo->prepare(
        "SELECT id, name FROM characters 
                 WHERE (name ILIKE :q OR name ILIKE :q2)
                 LIMIT 10");
    $stmt->execute([
        'q' => $query,
        'q2' => $query2]);
    $results['characters'] = $stmt->fetchAll();

//пошук у збірках
    $stmt = $pdo->prepare("
    SELECT id, name FROM collections 
    WHERE name ILIKE :q1 OR name ILIKE :q2
    ORDER BY created_at DESC
    LIMIT 10
");
    $stmt->execute([
        'q1' => $query,
        'q2' => $query2
    ]);
    $results['collections'] = $stmt->fetchAll();


    // Пошук у fandoms (за name)
    $stmt = $pdo->prepare(
        "SELECT id, name FROM fandoms 
                WHERE name ILIKE :q OR name ILIKE :q2
                ORDER BY name LIMIT 10");
    $stmt->execute([
        'q' => $query,
        'q2' => $query2]);
    $results['fandoms'] = $stmt->fetchAll();

    // Пошук у users (за name)
    $stmt = $pdo->prepare(
        "SELECT id, name FROM users 
                WHERE name ILIKE :q OR name ILIKE :q2
                ORDER BY name LIMIT 5");
    $stmt->execute([
        'q' => $query,
        'q2' => $query2]);
    $results['users'] = $stmt->fetchAll();

    // Виведення результатів
    foreach ($results as $table => $rows) {
        if (count($rows)) {
            echo "<div><strong>" . ucfirst($table) . ":</strong></div>";
            foreach ($rows as $row) {
                $label = htmlspecialchars($row['title'] ?? $row['name'] ?? $row['login']);
                echo "<div onclick=\"location.href='details.php?table=$table&id={$row['id']}'\">$label</div>";
            }
        }
    }

    if (empty(array_filter($results))) {
        echo "<div>Нічого не знайдено.</div>";
    }

} catch (PDOException $e) {
    echo "<div>Помилка підключення до бази.</div>";
}
?>
