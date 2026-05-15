<?php

require_once 'config.php';

header('Content-Type: application/json');

require_post();

$action = sanitize($_POST['action'] ?? '');

if ($action === 'verify') {
    // Step 1: check email exists
    $email = sanitize($_POST['email'] ?? '');

    if (empty($email)) {
        json_response(false, 'Email is required.');
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        json_response(false, 'Invalid email address.');
    }

    $db = get_db();

    $stmt = $db->prepare('SELECT id FROM users WHERE email = ?');
    $stmt->execute([$email]);
    if (!$stmt->fetch()) {
        // Generic message to avoid user enumeration
        json_response(true, 'If this email exists, a reset token has been issued.');
    }

    $token      = bin2hex(random_bytes(32));
    $created_at = date('Y-m-d H:i:s');

    // Remove any existing token for this email before inserting
    $stmt = $db->prepare('DELETE FROM password_resets WHERE email = ?');
    $stmt->execute([$email]);

    $stmt = $db->prepare('INSERT INTO password_resets (email, token, created_at) VALUES (?, ?, ?)');
    $stmt->execute([$email, $token, $created_at]);

    // In production, send $token via email. Returned here for development/testing only.
    json_response(true, 'Reset token issued.', ['token' => $token]);

} elseif ($action === 'reset') {
    // Step 2: validate token and set new password
    $email        = sanitize($_POST['email'] ?? '');
    $token        = sanitize($_POST['token'] ?? '');
    $new_password = $_POST['new_password'] ?? '';

    if (empty($email) || empty($token) || empty($new_password)) {
        json_response(false, 'Email, token, and new password are required.');
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        json_response(false, 'Invalid email address.');
    }

    if (strlen($new_password) < 8) {
        json_response(false, 'Password must be at least 8 characters.');
    }

    $db = get_db();

    // Token expires after 1 hour
    $stmt = $db->prepare(
        'SELECT id FROM password_resets
         WHERE email = ? AND token = ? AND created_at >= NOW() - INTERVAL 1 HOUR'
    );
    $stmt->execute([$email, $token]);
    if (!$stmt->fetch()) {
        json_response(false, 'Invalid or expired reset token.');
    }

    $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);

    $stmt = $db->prepare('UPDATE users SET password = ? WHERE email = ?');
    $stmt->execute([$hashed_password, $email]);

    $stmt = $db->prepare('DELETE FROM password_resets WHERE email = ?');
    $stmt->execute([$email]);

    json_response(true, 'Password has been reset successfully.');

} else {
    json_response(false, 'Invalid action. Use verify or reset.');
}
