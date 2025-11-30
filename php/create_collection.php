<?php
include "session.php";
global $user_id;
header('Content-Type: application/json');

$name = trim($_POST['name'] ?? '');

if (!$user_id || $name === '') {
    echo json_encode(['success' => false, 'error' => 'Невірні дані']);
    exit;
}

$pdo = new PDO("pgsql:host=localhost;dbname=postgres;port=5432", "postgres", "1131", [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
]);

// 🔍 Перевірка, чи вже існує збірка з такою назвою для цього користувача
$stmt = $pdo->prepare("SELECT id FROM collections WHERE user_id = :user_id AND LOWER(name) = LOWER(:name) LIMIT 1");
$stmt->execute([
    'user_id' => $user_id,
    'name' => $name
]);

if ($stmt->fetch()) {
    echo json_encode(['success' => false, 'error' => 'Збірка з такою назвою вже існує']);
    exit;
}

$stmt = $pdo->prepare("INSERT INTO collections (user_id, name, created_at) VALUES (:user_id, :name, now()) RETURNING id");
$stmt->execute(['user_id' => $user_id, 'name' => $name]);
$id = $stmt->fetchColumn();

echo json_encode(['success' => true, 'collection_id' => $id]);
