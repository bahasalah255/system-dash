<?php

require_once 'config.php';
session_start();

header('Content-Type: application/json');

if (empty($_SESSION['user_id']) || $_SESSION['user_role'] !== 'manager') {
    json_response(false, 'Unauthorized.');
}

$db   = get_db();
$rows = $db->query(
    "SELECT id, full_name, email, created_at FROM users WHERE role = 'student' ORDER BY created_at DESC"
)->fetchAll();

json_response(true, 'OK', ['users' => $rows]);
