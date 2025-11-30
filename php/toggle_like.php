<?php
include "session.php";
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
$work_id = $data['work_id'] ?? null;
$action = $data['action'] ?? null;


$dsn = "pgsql:host=localhost;dbname=postgres;port=5432";
$db_username = "postgres";
$db_password = "1131";

try {
    $pdo = new PDO($dsn, $db_username, $db_password, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    $user_id = $_SESSION['user_id'];

    if ($action === 'like') {
        $stmt = $pdo->prepare("INSERT INTO likes (user_id, work_id) VALUES (?, ?) ON CONFLICT DO NOTHING");
        $stmt->execute([$user_id, $work_id]);
    } elseif ($action === 'unlike') {
        $stmt = $pdo->prepare("DELETE FROM likes WHERE user_id = ? AND work_id = ?");
        $stmt->execute([$user_id, $work_id]);
    }

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM likes WHERE work_id = ?");
    $stmt->execute([$work_id]);
    $count = $stmt->fetchColumn();

    echo json_encode(['success' => true, 'new_count' => $count]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
