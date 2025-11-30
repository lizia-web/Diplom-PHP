<?php
include "session.php";
global $user_id;

$dsn = "pgsql:host=localhost;dbname=postgres;port=5432";
$db_username = "postgres";
$db_password = "1131";
$pdo = new PDO($dsn, $db_username, $db_password, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
]);

$stmt = $pdo->prepare("UPDATE users SET about = :about WHERE id = :id");
$stmt->execute([
    'about' => $_POST['about'],
    'id' => $user_id
]);

header("Location: account.php");

?>
