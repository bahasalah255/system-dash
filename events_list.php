<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if (($_SESSION['user_role'] ?? '') !== 'manager') {
    header('Location: events.php');
    exit;
}

header('Location: dashboard.php?section=events');
exit;