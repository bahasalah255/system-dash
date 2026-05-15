<?php

require_once 'config.php';

session_start();
session_unset();
session_destroy();

// If called via AJAX/fetch, return JSON; otherwise redirect
$is_ajax = !empty($_SERVER['HTTP_X_REQUESTED_WITH'])
    && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

if ($is_ajax || (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false)) {
    json_response(true, 'Logged out successfully.');
} else {
    header('Location: ../frontend/pages/login.html');
    exit;
}
