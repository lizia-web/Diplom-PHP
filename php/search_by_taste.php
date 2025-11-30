<?php
$pdo = new PDO("pgsql:host=localhost;dbname=postgres;port=5432", "postgres", "1131", [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
]);

include "fanf_menu.php";

$title = isset($_POST['title']) ? trim($_POST['title']) : '';
$description = isset($_POST['description']) ? trim($_POST['description']) : '';
$fandom_id = !empty($_POST['fandom_id']) ? (int)$_POST['fandom_id'] : null;
$direction_id = !empty($_POST['direction_id']) ? (int)$_POST['direction_id'] : null;
$genre_id = !empty($_POST['genre_id']) ? (int)$_POST['genre_id'] : null;

$query = "SELECT * FROM works WHERE 1=1";
$params = [];

if ($title) {
    $query .= " AND title ILIKE ?";
    $params[] = "%$title%";
}
if ($description) {
    $query .= " AND description ILIKE ?";
    $params[] = "%$description%";
}
if ($fandom_id) {
    $query .= " AND fandom_id = ?";
    $params[] = $fandom_id;
}
if ($direction_id) {
    $query .= " AND direction_id = ?";
    $params[] = $direction_id;
}
if ($genre_id) {
    $query .= " AND genre_id = ?";
    $params[] = $genre_id;
}

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$works = $stmt->fetchAll();

if (count($works) === 0) {
    echo "<p>Робіт не знайдено.</p>";
} else {
    include "fanfics.php";
}
