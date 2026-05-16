<?php

session_start();
require_once 'config.php';

header('Content-Type: application/json');

if (empty($_SESSION['user_id']) || $_SESSION['user_role'] !== 'manager') {
    json_response(false, 'Unauthorized.');
}

$db = get_db();

$event_count   = (int) $db->query('SELECT COUNT(*) FROM events')->fetchColumn();
$student_count = (int) $db->query("SELECT COUNT(*) FROM users WHERE role = 'student'")->fetchColumn();

json_response(true, 'OK', [
    'events'   => $event_count,
    'students' => $student_count,
]);
