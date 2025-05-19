<?php

$request = $_SERVER['REQUEST_URI'];
$request = explode('?', $request)[0];

switch ($request) {
    case '/api/users':
        require_once '../controllers/UserController.php';
        listUsers();
        break;

    default:
        http_response_code(404);
        echo json_encode(['error' => 'Route not found']);
        break;
}
