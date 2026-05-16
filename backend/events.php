<?php

require_once 'config.php';
session_start();

header('Content-Type: application/json');

if (empty($_SESSION['user_id'])) {
    json_response(false, 'Unauthorized.');
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $db   = get_db();
    $rows = $db->query(
        'SELECT id, title, description, event_date, location FROM events ORDER BY event_date DESC'
    )->fetchAll();

    json_response(true, 'OK', ['events' => $rows]);
}

if ($method === 'POST') {
    if ($_SESSION['user_role'] !== 'manager') {
        json_response(false, 'Unauthorized.');
    }

    $action = trim($_POST['action'] ?? '');

    if ($action === 'add') {
        $title       = trim($_POST['title'] ?? '');
        $event_date  = trim($_POST['event_date'] ?? '');
        $location    = trim($_POST['location'] ?? '');
        $description = trim($_POST['description'] ?? '');

        if ($title === '' || $event_date === '') {
            json_response(false, 'Title and date are required.');
        }

        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $event_date)) {
            json_response(false, 'Invalid date format.');
        }

        $db   = get_db();
        $stmt = $db->prepare(
            'INSERT INTO events (title, description, event_date, location, created_by) VALUES (?, ?, ?, ?, ?)'
        );
        $stmt->execute([$title, $description, $event_date, $location, $_SESSION['user_id']]);

        json_response(true, 'Event added.');
    }

    if ($action === 'edit') {
        $id          = (int) ($_POST['id'] ?? 0);
        $title       = trim($_POST['title'] ?? '');
        $event_date  = trim($_POST['event_date'] ?? '');
        $location    = trim($_POST['location'] ?? '');
        $description = trim($_POST['description'] ?? '');

        if ($id <= 0) {
            json_response(false, 'Invalid event ID.');
        }

        if ($title === '' || $event_date === '') {
            json_response(false, 'Title and date are required.');
        }

        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $event_date)) {
            json_response(false, 'Invalid date format.');
        }

        $db   = get_db();
        $stmt = $db->prepare(
            'UPDATE events SET title=?, description=?, event_date=?, location=? WHERE id=?'
        );
        $stmt->execute([$title, $description, $event_date, $location, $id]);

        json_response(true, 'Event updated.');
    }

    if ($action === 'delete') {
        $id = (int) ($_POST['id'] ?? 0);

        if ($id <= 0) {
            json_response(false, 'Invalid event ID.');
        }

        $db   = get_db();
        $stmt = $db->prepare('DELETE FROM events WHERE id = ?');
        $stmt->execute([$id]);

        json_response(true, 'Event deleted.');
    }

    json_response(false, 'Unknown action.');
}

json_response(false, 'Method not allowed.');
