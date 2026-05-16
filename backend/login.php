<?php

// Temporary error/exception handlers to return JSON for debugging (remove after fix)
set_exception_handler(function($e){
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Exception: ' . $e->getMessage()]);
    exit;
});

set_error_handler(function($errno, $errstr, $errfile, $errline){
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => "Error: $errstr in $errfile:$errline"]);
    exit;
});

// Hide PHP warnings/notices so the frontend always receives valid JSON
ini_set('display_errors', '0');
error_reporting(0);

require_once 'config.php';
session_start();

header('Content-Type: application/json');

require_post();

$email    = sanitize($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if (empty($email) || empty($password)) {
    json_response(false, 'Email and password are required.');
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    json_response(false, 'Invalid email address.');
}

try {
    $db = get_db();

    $stmt = $db->prepare('SELECT id, full_name, email, password, role FROM users WHERE email = ?');
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['password'] ?? '')) {
        json_response(false, 'Invalid email or password.');
    }

    session_regenerate_id(true);

    // Re-issue the session cookie with the same params to ensure the browser receives it
    if (function_exists('setcookie')) {
        if (defined('PHP_VERSION_ID') && PHP_VERSION_ID >= 70300) {
            setcookie(session_name(), session_id(), [
                'expires'  => 0,
                'path'     => '/',
                'domain'   => '',
                'secure'   => false,
                'httponly' => true,
                'samesite' => 'Lax'
            ]);
        } else {
            // Fallback for older PHP versions: append SameSite to path
            setcookie(session_name(), session_id(), 0, '/; samesite=Lax', '', false, true);
        }
    }

    $_SESSION['user_id']    = $user['id'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_role']  = $user['role'];
    $_SESSION['user_name']  = $user['full_name'];

    json_response(true, 'Login successful.', [
        'id'        => $user['id'],
        'full_name' => $user['full_name'],
        'email'     => $user['email'],
        'role'      => $user['role'],
    ]);

} catch (Exception $e) {
    // Return a generic error so frontend can parse JSON; log details server-side if needed.
    json_response(false, 'Server error. Please try again later.');
}
