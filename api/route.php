<?php

$method = $_SERVER['REQUEST_METHOD'];
$route = $_GET['route'] ?? '';

if ($route === 'register' && $method === 'POST') {
    require_once __DIR__ . '/../api/controllers/AuthController.php';
    register();
} elseif ($route === 'login' && $method === 'POST') {
    require_once __DIR__ . '/../api/controllers/AuthController.php';
    login();
} elseif ($route === 'create_quiz' && $method === 'POST') {
    require_once __DIR__ . '/../api/controllers/QuizController.php';
    createQuiz();
} elseif ($route === 'my_quizzes' && $method === 'GET') {
    require_once __DIR__ . '/../api/controllers/QuizController.php';
    getUserQuizzes();
} elseif ($route === 'delete_quiz' && $method === 'DELETE') {
    require_once __DIR__ . '/../api/controllers/QuizController.php';
    deleteQuiz();
} else {
    http_response_code(404);
    echo json_encode(["error" => "Route non trouvée"]);
}
