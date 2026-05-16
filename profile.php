<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$role = $_SESSION['user_role'] ?? 'student';

if ($role === 'manager') {
    header('Location: dashboard.php?section=profile');
} else {
    header('Location: frontend/pages/dashboard-student.html?section=profile');
}

exit;