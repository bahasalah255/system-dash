<?php
session_start();

require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    json_response(false, 'Unauthorized.');
}

require_post();

$current_password = $_POST['current_password'] ?? '';
$new_password     = $_POST['new_password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';

if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
    json_response(false, 'All fields are required.');
}

if ($new_password !== $confirm_password) {
    json_response(false, 'New passwords do not match.');
}

if (strlen($new_password) < 8) {
    json_response(false, 'New password must be at least 8 characters.');
}

$db = get_db();

$stmt = $db->prepare('SELECT password FROM users WHERE id = ?');
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

if (!$user || !password_verify($current_password, $user['password'])) {
    json_response(false, 'Current password is incorrect.');
}

$hashed = password_hash($new_password, PASSWORD_DEFAULT);

$stmt = $db->prepare('UPDATE users SET password = ? WHERE id = ?');
$stmt->execute([$hashed, $_SESSION['user_id']]);

json_response(true, 'Password updated successfully.');
