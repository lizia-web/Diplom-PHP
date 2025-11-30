<?php
include "session.php";
global $user_id;
header('Content-Type: application/json');

$collection_id = $_POST['collection_id'] ?? null;
$work_id = $_POST['work_id'] ?? null;

if (!$user_id || !$collection_id || !$work_id) {
    echo json_encode(['success' => false, 'error' => 'Некоректні дані']);
    exit;
}

$pdo = new PDO("pgsql:host=localhost;dbname=postgres;port=5432", "postgres", "1131", [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
]);

// Перевіряємо, що ця збірка належить цьому користувачу
$stmt = $pdo->prepare("SELECT id FROM collections WHERE id = :id AND user_id = :user_id");
$stmt->execute(['id' => $collection_id, 'user_id' => $user_id]);

if (!$stmt->fetch()) {
    echo json_encode(['success' => false, 'error' => 'Збірка не знайдена або не належить вам']);
    exit;
}

// Чи вже додано?
$stmt = $pdo->prepare("SELECT id FROM collection_works WHERE collection_id = :collection_id AND work_id = :work_id");
$stmt->execute(['collection_id' => $collection_id, 'work_id' => $work_id]);

if ($stmt->fetch()) {
    echo json_encode(['success' => false, 'error' => 'Вже у збірці']);
    exit;
}

// Додаємо
$stmt = $pdo->prepare("INSERT INTO collection_works (collection_id, work_id) VALUES (:collection_id, :work_id)");
$stmt->execute(['collection_id' => $collection_id, 'work_id' => $work_id]);

echo json_encode(['success' => true]);
