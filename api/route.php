<?php

$method = $_SERVER['REQUEST_METHOD'];
$route = $_GET['route'] ?? '';

if ($route === 'register' && $method === 'POST') {
    require_once __DIR__ . '/../api/controllers/AuthController.php';
    register();
} elseif ($route === 'login' && $method === 'POST') {
    require_once __DIR__ . '/../api/controllers/AuthController.php';
    login();
} else {
    http_response_code(404);
    echo json_encode(["error" => "Route non trouvée"]);
}
