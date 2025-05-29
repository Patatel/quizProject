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
} elseif ($route === 'add_questions' && $method === 'POST') {
    require_once __DIR__ . '/../api/controllers/QuizController.php';
    createQuestionsForQuiz();
} elseif ($route === 'update_questions' && $method === 'PUT') {
    require_once __DIR__ . '/../api/controllers/QuizController.php';
    updateQuestions();
} elseif ($route === 'all_quizzes' && $method === 'GET') {
    require_once __DIR__ . '/../api/controllers/QuizController.php';
    getQuizzes();
} elseif ($route === 'get_quiz' && $method === 'GET') {
    require_once __DIR__ . '/../api/controllers/QuizController.php';
    getQuizById();
} elseif ($route === 'delete_quiz' && $method === 'GET') {
    http_response_code(405);
    echo json_encode(["error" => "Méthode non autorisée, utilisez DELETE"]);
} elseif ($route === 'delete_quiz' && $method === 'DELETE') {
    require_once __DIR__ . '/../api/controllers/QuizController.php';
    deleteQuiz();
} elseif ($route === 'update_quiz' && $method === 'PUT') {
    require_once __DIR__ . '/../api/controllers/QuizController.php';
    updateQuiz();
} elseif ($route === 'update_user' && $method === 'POST') {
    require_once __DIR__ . '/../api/controllers/UserController.php';
    updateUser();
} elseif ($route === 'get-user-results' && $method === 'GET') {
    require_once __DIR__ . '/../api/controllers/ResultController.php';
    getUserResults();
} elseif ($route === 'get_questions' && $method === 'GET') {
    require_once __DIR__ . '/../api/controllers/QuizController.php';
    getQuestionsByQuizId();
} elseif ($route === 'submit_answers' && $method === 'POST') {
    require_once __DIR__ . '/../api/controllers/AnswerController.php';
    submitAnswers();
} elseif ($route === 'get_answer_details' && $method === 'GET') {
    require_once __DIR__ . '/../api/controllers/AnswerController.php';
    getAnswerDetails();
} elseif ($route === 'delete_question' && $method === 'POST') {
    require_once __DIR__ . '/../api/controllers/QuizController.php';
    deleteQuestion();
} elseif ($route === 'update_question' && $method === 'POST') {
    require_once __DIR__ . '/../api/controllers/QuizController.php';
    updateQuestion();
} elseif ($route === 'get_quiz_questions' && $method === 'GET') {
    require_once __DIR__ . '/../api/controllers/QuizController.php';
    getQuizQuestions();
} elseif ($route === 'user_submit_answers' && $method === 'POST') {
    require_once __DIR__ . '/../api/controllers/QuizController.php';
    userSubmitAnswers();
} elseif ($route === 'get_user_results' && $method === 'GET') {
    require_once __DIR__ . '/../api/controllers/ResultController.php';
    getUserResults();
} else {
    http_response_code(404);
    echo json_encode(["error" => "Route non trouvée"]);
}
