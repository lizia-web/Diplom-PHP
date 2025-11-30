<?php
include "session.php";
global $user_id;
header('Content-Type: application/json');

if (!$user_id) exit(json_encode([]));

$pdo = new PDO("pgsql:host=localhost;dbname=postgres;port=5432", "postgres", "1131", [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
]);

$stmt = $pdo->prepare("SELECT id, name FROM collections WHERE user_id = :user_id ORDER BY created_at DESC");
$stmt->execute(['user_id' => $user_id]);
echo json_encode($stmt->fetchAll());
