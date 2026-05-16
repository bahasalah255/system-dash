<?php

session_start();
require_once 'config.php';

header('Content-Type: application/json');

if (empty($_SESSION['user_id'])) {
    json_response(false, 'Not authenticated.');
}

json_response(true, 'OK', [
    'id'    => $_SESSION['user_id'],
    'name'  => $_SESSION['user_name'],
    'email' => $_SESSION['user_email'],
    'role'  => $_SESSION['user_role'],
]);
