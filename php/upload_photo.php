<?php
session_start();
header('Content-Type: application/json');

$user_id = $_SESSION['user_id'] ?? null;

if (!$user_id) {
    echo json_encode(['success' => false, 'error' => 'Користувач не авторизований']);
    exit;
}

if (!isset($_FILES['image']) || !isset($_POST['type'])) {
    echo json_encode(['success' => false, 'error' => 'Невірний запит']);
    exit;
}

$type = $_POST['type'];
$field = ($type === 'cover') ? 'cover_photo' : 'profile_photo';

// Зчитуємо зображення
$tmpName = $_FILES['image']['tmp_name'];
if (!is_uploaded_file($tmpName)) {
    echo json_encode(['success' => false, 'error' => 'Файл не завантажено через HTTP POST']);
    exit;
}

$img = file_get_contents($tmpName);
if (!$img) {
    echo json_encode(['success' => false, 'error' => 'Не вдалося зчитати зображення']);
    exit;
}

$dsn = "pgsql:host=localhost;dbname=postgres;port=5432";
$db_username = "postgres";
$db_password = "1131";

try {
    $pdo = new PDO($dsn, $db_username, $db_password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

    $sql = "UPDATE users SET $field = :img WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':img', $img, PDO::PARAM_LOB);
    $stmt->bindParam(':id', $user_id, PDO::PARAM_INT);
    $result = $stmt->execute();

    if (!$result || $stmt->rowCount() === 0) {
        echo json_encode(['success' => false, 'error' => 'UPDATE не виконався або не змінив рядок']);
        exit;
    }

    $img_base64 = 'data:image/png;base64,' . base64_encode($img);
    echo json_encode(['success' => true, 'image' => $img_base64]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'БД: ' . $e->getMessage()]);
}
