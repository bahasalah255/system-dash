<?php
session_start();

require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    json_response(false, 'Unauthorized.');
}

require_post();

$new_email = sanitize($_POST['new_email'] ?? '');
$password  = $_POST['password'] ?? '';

if (empty($new_email) || empty($password)) {
    json_response(false, 'All fields are required.');
}

if (!filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
    json_response(false, 'Invalid email address.');
}

$db = get_db();

$stmt = $db->prepare('SELECT password FROM users WHERE id = ?');
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

if (!$user || !password_verify($password, $user['password'])) {
    json_response(false, 'Incorrect password.');
}

$stmt = $db->prepare('SELECT id FROM users WHERE email = ? AND id != ?');
$stmt->execute([$new_email, $_SESSION['user_id']]);
if ($stmt->fetch()) {
    json_response(false, 'This email is already in use.');
}

$stmt = $db->prepare('UPDATE users SET email = ? WHERE id = ?');
$stmt->execute([$new_email, $_SESSION['user_id']]);

$_SESSION['user_email'] = $new_email;

json_response(true, 'Email updated successfully.');
