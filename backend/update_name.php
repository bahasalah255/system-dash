<?php
session_start();

require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    json_response(false, 'Unauthorized.');
}

require_post();

$full_name = sanitize($_POST['full_name'] ?? '');

if (empty($full_name)) {
    json_response(false, 'Full name is required.');
}

$db   = get_db();
$stmt = $db->prepare('UPDATE users SET full_name = ? WHERE id = ?');
$stmt->execute([$full_name, $_SESSION['user_id']]);

$_SESSION['user_name'] = $full_name;

json_response(true, 'Name updated successfully.');
