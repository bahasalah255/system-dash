<?php
require_once 'backend/config.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if (($_SESSION['user_role'] ?? '') !== 'manager') {
    header('Location: events.php');
    exit;
}

header('Location: frontend/pages/dashboard-manager.html?section=overview');
exit;