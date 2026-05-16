<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Academic Events</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f4f6f9;
            color: #333;
        }

        nav {
            background: #1a1a2e;
            padding: 0 32px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            height: 56px;
            border-bottom: 3px solid #e94560;
        }

        nav .brand {
            color: #fff;
            font-size: 1.1rem;
            font-weight: 700;
            letter-spacing: 0.5px;
            text-decoration: none;
        }

        nav ul {
            list-style: none;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        nav ul li a {
            color: #c9d1d9;
            text-decoration: none;
            padding: 8px 14px;
            border-radius: 4px;
            font-size: 0.9rem;
            transition: background 0.15s, color 0.15s;
        }

        nav ul li a:hover,
        nav ul li a.active {
            background: #e94560;
            color: #fff;
        }

        nav .user-info {
            color: #8b949e;
            font-size: 0.85rem;
        }

        .page-content {
            max-width: 960px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .alert {
            padding: 12px 16px;
            border-radius: 4px;
            margin-bottom: 20px;
            font-size: 0.9rem;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
    </style>
</head>
<body>
<nav>
    <a class="brand" href="events.php">Academic Events</a>
    <ul>
        <li><a href="events.php" <?= $current_page === 'events.php' ? 'class="active"' : '' ?>>Events</a></li>
        <li><a href="profile.php" <?= $current_page === 'profile.php' ? 'class="active"' : '' ?>>My Profile</a></li>
        <li><a href="backend/logout.php">Logout</a></li>
    </ul>
    <span class="user-info"><?= htmlspecialchars($_SESSION['user_name'], ENT_QUOTES, 'UTF-8') ?></span>
</nav>
<div class="page-content">
