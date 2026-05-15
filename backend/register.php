<?php

require_once 'config.php';

header('Content-Type: application/json');

require_post();

$full_name = sanitize($_POST['full_name'] ?? '');
$email     = sanitize($_POST['email'] ?? '');
$password  = $_POST['password'] ?? '';
$role      = 'student';

if (empty($full_name) || empty($email) || empty($password)) {
    json_response(false, 'Full name, email, and password are required.');
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    json_response(false, 'Invalid email address.');
}

if (strlen($password) < 8) {
    json_response(false, 'Password must be at least 8 characters.');
}

$db = get_db();

$stmt = $db->prepare('SELECT id FROM users WHERE email = ?');
$stmt->execute([$email]);
if ($stmt->fetch()) {
    json_response(false, 'An account with this email already exists.');
}

$hashed_password = password_hash($password, PASSWORD_BCRYPT);

$stmt = $db->prepare(
    'INSERT INTO users (full_name, email, password, role, created_at) VALUES (?, ?, ?, ?, NOW())'
);
$stmt->execute([$full_name, $email, $hashed_password, $role]);

json_response(true, 'Registration successful.');
