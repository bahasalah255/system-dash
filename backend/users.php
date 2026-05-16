<?php

require_once 'config.php';
session_start();

header('Content-Type: application/json');

if (empty($_SESSION['user_id']) || $_SESSION['user_role'] !== 'manager') {
    json_response(false, 'Unauthorized.');
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $db   = get_db();
    $rows = $db->query(
        "SELECT id, full_name, email, created_at FROM users WHERE role = 'student' ORDER BY created_at DESC"
    )->fetchAll();
    json_response(true, 'OK', ['users' => $rows]);
}

if ($method === 'POST') {
    $action = trim($_POST['action'] ?? '');

    if ($action === 'add') {
        $full_name = sanitize($_POST['full_name'] ?? '');
        $email     = sanitize($_POST['email']     ?? '');
        $password  = $_POST['password']              ?? '';

        if (empty($full_name) || empty($email) || empty($password)) {
            json_response(false, 'Full name, email and password are required.');
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            json_response(false, 'Invalid email address.');
        }
        if (strlen($password) < 8) {
            json_response(false, 'Password must be at least 8 characters.');
        }

        $db   = get_db();
        $stmt = $db->prepare('SELECT id FROM users WHERE email = ?');
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            json_response(false, 'An account with this email already exists.');
        }

        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $stmt   = $db->prepare(
            'INSERT INTO users (full_name, email, password, role) VALUES (?, ?, ?, ?)'
        );
        $stmt->execute([$full_name, $email, $hashed, 'student']);
        json_response(true, 'Student added.');
    }

    if ($action === 'edit') {
        $id        = (int) ($_POST['id']        ?? 0);
        $full_name = sanitize($_POST['full_name'] ?? '');
        $email     = sanitize($_POST['email']     ?? '');
        $password  = $_POST['password']              ?? '';

        if ($id <= 0) {
            json_response(false, 'Invalid student ID.');
        }
        if (empty($full_name) || empty($email)) {
            json_response(false, 'Full name and email are required.');
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            json_response(false, 'Invalid email address.');
        }

        $db   = get_db();
        $stmt = $db->prepare('SELECT id FROM users WHERE email = ? AND id != ?');
        $stmt->execute([$email, $id]);
        if ($stmt->fetch()) {
            json_response(false, 'This email is already in use by another account.');
        }

        if ($password !== '') {
            if (strlen($password) < 8) {
                json_response(false, 'Password must be at least 8 characters.');
            }
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt   = $db->prepare('UPDATE users SET full_name=?, email=?, password=? WHERE id=? AND role=?');
            $stmt->execute([$full_name, $email, $hashed, $id, 'student']);
        } else {
            $stmt = $db->prepare('UPDATE users SET full_name=?, email=? WHERE id=? AND role=?');
            $stmt->execute([$full_name, $email, $id, 'student']);
        }

        json_response(true, 'Student updated.');
    }

    if ($action === 'delete') {
        $id = (int) ($_POST['id'] ?? 0);
        if ($id <= 0) {
            json_response(false, 'Invalid student ID.');
        }
        $db   = get_db();
        $stmt = $db->prepare("DELETE FROM users WHERE id=? AND role='student'");
        $stmt->execute([$id]);
        json_response(true, 'Student deleted.');
    }

    json_response(false, 'Unknown action.');
}

json_response(false, 'Method not allowed.');
