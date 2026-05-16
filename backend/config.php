<?php

// Load .env file from the same directory (optional)
$env_file = __DIR__ . '/.env';
if (file_exists($env_file)) {
    $lines = file($env_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0 || strpos($line, '=') === false) continue;
        [$key, $value] = explode('=', $line, 2);
        $_ENV[trim($key)] = trim($value);
    }
}

define('DB_HOST',    $_ENV['DB_HOST']    ?? 'localhost');
define('DB_NAME',    $_ENV['DB_NAME']    ?? 'academic_events');
define('DB_USER',    $_ENV['DB_USER']    ?? 'root');
define('DB_PASS',    $_ENV['DB_PASS']    ?? '');
define('DB_CHARSET', $_ENV['DB_CHARSET'] ?? 'utf8mb4');

function get_db() {
    static $pdo = null;

    if ($pdo === null) {
        $dsn     = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            json_response(false, 'Database connection failed.');
            exit;
        }
    }

    return $pdo;
}

// ---- Session setup (ensure consistent session storage and cookie params) ----
// Determine a writable session save path and set it so all scripts use the same location.
$currentSave = ini_get('session.save_path');
if ($currentSave) {
    if (strpos($currentSave, ';') !== false) {
        $parts = explode(';', $currentSave);
        $currentSave = end($parts);
    }
}
$currentSave = $currentSave ?: sys_get_temp_dir();
if (!is_dir($currentSave) || !is_writable($currentSave)) {
    $alt = sys_get_temp_dir();
    if (is_dir($alt) && is_writable($alt)) {
        session_save_path($alt);
    } else {
        $localTmp = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'tmp';
        if (!is_dir($localTmp)) {
            @mkdir($localTmp, 0777, true);
        }
        if (is_dir($localTmp) && is_writable($localTmp)) {
            session_save_path($localTmp);
        }
    }
}

// Configure session cookie params so cookies are consistent on localhost
if (defined('PHP_VERSION_ID') && PHP_VERSION_ID >= 70300) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'domain'   => '',
        'secure'   => false,
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
} else {
    session_set_cookie_params(0, '/; samesite=Lax', '', false, true);
}


function json_response($success, $message, $data = []) {
    header('Content-Type: application/json');
    $response = ['success' => $success, 'message' => $message];
    if (!empty($data)) {
        $response['data'] = $data;
    }
    echo json_encode($response);
    exit;
}

function require_post() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        json_response(false, 'Method not allowed. Use POST.');
    }
}

function sanitize($value) {
    return trim(htmlspecialchars($value, ENT_QUOTES, 'UTF-8'));
}
