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

$eventId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$target  = 'dashboard.php?section=events&mode=edit';

if ($eventId > 0) {
    $target .= '&id=' . $eventId;
}

header('Location: ' . $target);
exit;