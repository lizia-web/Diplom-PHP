<?php
session_start();
$role = $_SESSION['user_type'] ?? 'guest';
$username = $_SESSION['username'] ?? null;
$user_id = $_SESSION['user_id'] ?? null;
?>
