<?php
require_once __DIR__ . '/../models/QuizModel.php';

function createQuiz()
{
    global $pdo;
    $data = json_decode(file_get_contents("php://input"), true);

    if (!isset($data['title'], $data['description'], $data['user_id'])) {
        http_response_code(400);
        echo json_encode(["error" => "Champs manquants"]);
        return;
    }

    $title = htmlspecialchars($data['title']);
    $description = htmlspecialchars($data['description']);
    $userId = intval($data['user_id']);

    $quizModel = new QuizModel($pdo);
    $quizId = $quizModel->createQuiz($title, $description, $userId);

    if ($quizId) {
        http_response_code(201);
        echo json_encode(["message" => "Quiz créé avec succès", "id" => $quizId]);
    } else {
        http_response_code(500);
        echo json_encode(["error" => "Erreur serveur"]);
    }
}

function getQuizzes()
{
    global $pdo;
    $quizModel = new QuizModel($pdo);
    $quizzes = $quizModel->getQuizzes();
    echo json_encode($quizzes);
}

function getUserQuizzes()
{
    global $pdo;
    $userId = $_GET['user_id'] ?? null;

    if (!$userId || !is_numeric($userId)) {
        http_response_code(400);
        echo json_encode(["error" => "ID utilisateur invalide"]);
        return;
    }

    $quizModel = new QuizModel($pdo);
    $quizzes = $quizModel->getUserQuizzes($userId);
    echo json_encode($quizzes);
}

function getQuizById()
{
    global $pdo;
    $id = $_GET['id'] ?? null;

    if (!$id) {
        http_response_code(400);
        echo json_encode(["error" => "ID du quiz requis"]);
        return;
    }

    $quizModel = new QuizModel($pdo);
    $quiz = $quizModel->getQuizById($id);

    if ($quiz) {
        echo json_encode($quiz);
    } else {
        http_response_code(404);
        echo json_encode(["error" => "Quiz non trouvé"]);
    }
}

function deleteQuiz()
{
    global $pdo;
    parse_str(file_get_contents("php://input"), $data);
    $quizId = $data['id'] ?? null;

    if (!$quizId) {
        http_response_code(400);
        echo json_encode(["error" => "ID du quiz requis"]);
        return;
    }

    $quizModel = new QuizModel($pdo);
    if ($quizModel->deleteQuiz($quizId)) {
        echo json_encode(["message" => "Quiz supprimé avec succès"]);
    } else {
        http_response_code(500);
        echo json_encode(["error" => "Erreur serveur"]);
    }
}

function updateQuiz()
{
    global $pdo;
    $data = json_decode(file_get_contents("php://input"), true);

    $quizId = $data['id'] ?? null;
    $title = htmlspecialchars($data['title'] ?? '');
    $description = htmlspecialchars($data['description'] ?? '');

    if (!$quizId || !$title || !$description) {
        http_response_code(400);
        echo json_encode(["error" => "Champs manquants pour la mise à jour"]);
        return;
    }

    $quizModel = new QuizModel($pdo);
    if ($quizModel->updateQuiz($quizId, $title, $description)) {
        echo json_encode(["message" => "Quiz mis à jour avec succès"]);
    } else {
        http_response_code(500);
        echo json_encode(["error" => "Erreur serveur"]);
    }
}

function createQuestionsForQuiz()
{
    global $pdo;
    $data = json_decode(file_get_contents("php://input"), true);

    $quizId = $data['quiz_id'] ?? null;
    $questions = $data['questions'] ?? [];

    if (!$quizId || count($questions) < 1 || count($questions) > 10) {
        http_response_code(400);
        echo json_encode(["error" => "Le quiz doit contenir entre 1 et 10 questions."]);
        return;
    }

    foreach ($questions as $q) {
        if (!isset($q['answers']) || count($q['answers']) < 2 || trim($q['answers'][0]) === '' || trim($q['answers'][1]) === '') {
            http_response_code(400);
            echo json_encode(["error" => "Chaque question doit avoir au moins 2 réponses non vides."]);
            return;
        }
    }

    $quizModel = new QuizModel($pdo);
    if ($quizModel->createQuestionsForQuiz($quizId, $questions)) {
        echo json_encode(["message" => "Questions ajoutées avec succès."]);
    } else {
        http_response_code(500);
        echo json_encode(["error" => "Erreur serveur"]);
    }
}

function updateQuestions()
{
    global $pdo;
    $data = json_decode(file_get_contents("php://input"), true);

    $quizId = $data['quiz_id'] ?? null;
    $questions = $data['questions'] ?? [];

    if (!$quizId || count($questions) < 1 || count($questions) > 10) {
        http_response_code(400);
        echo json_encode(["error" => "Le quiz doit contenir entre 1 et 10 questions."]);
        return;
    }

    foreach ($questions as $q) {
        if (!isset($q['answers']) || count($q['answers']) < 2 || trim($q['answers'][0]) === '' || trim($q['answers'][1]) === '') {
            http_response_code(400);
            echo json_encode(["error" => "Chaque question doit avoir au moins 2 réponses non vides."]);
            return;
        }
    }

    $quizModel = new QuizModel($pdo);
    if ($quizModel->updateQuestions($quizId, $questions)) {
        echo json_encode(["message" => "Questions mises à jour avec succès."]);
    } else {
        http_response_code(500);
        echo json_encode(["error" => "Erreur serveur"]);
    }
}

function getQuestionsByQuizId()
{
    global $pdo;
    $quizId = $_GET['quiz_id'] ?? null;

    if (!$quizId) {
        http_response_code(400);
        echo json_encode(["error" => "ID du quiz requis"]);
        return;
    }

    $quizModel = new QuizModel($pdo);
    $questions = $quizModel->getQuestionsByQuizId($quizId);
    echo json_encode($questions);
}

function getQuizQuestions()
{
    global $pdo;
    $quizId = $_GET['quiz_id'] ?? null;

    header('Content-Type: application/json; charset=utf-8'); // ← important

    if (!$quizId) {
        http_response_code(400);
        echo json_encode(["error" => "ID du quiz manquant"]);
        return;
    }

    try {
        $quizModel = new QuizModel($pdo);
        $questions = $quizModel->getQuizQuestions($quizId);
        echo json_encode($questions);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            "error" => "Erreur lors de la récupération des questions",
            "details" => $e->getMessage()
        ]);
    }
}

function userSubmitAnswers()
{
    global $pdo;
    $data = json_decode(file_get_contents('php://input'), true);

    if (!$data || !isset($data['user_id']) || !isset($data['quiz_id']) || !isset($data['answers'])) {
        http_response_code(400);
        echo json_encode(["error" => "Données invalides"]);
        return;
    }

    try {
        $quizModel = new QuizModel($pdo);
        $result = $quizModel->userSubmitAnswers($data['user_id'], $data['quiz_id'], $data['answers']);
        echo json_encode($result);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            "error" => "Erreur lors de la soumission des réponses",
            "details" => $e->getMessage()
        ]);
    }
}