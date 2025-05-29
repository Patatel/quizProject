<?php
// Fichier : controllers/AnswerController.php

require_once __DIR__ . '/../models/AnswerModel.php';

function submitAnswers() {
    global $pdo;
    $data = json_decode(file_get_contents('php://input'), true);

    if (!$data) {
        http_response_code(400);
        echo json_encode(["error" => "Données invalides"]);
        return;
    }

    $requiredFields = ['user_id', 'quiz_id', 'answers'];
    foreach ($requiredFields as $field) {
        if (!isset($data[$field])) {
            http_response_code(400);
            echo json_encode(["error" => "Champ manquant: $field"]);
            return;
        }
    }

    try {
        $result = submitAnswers($pdo, $data);
        echo json_encode($result);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            "error" => "Erreur lors de la soumission des réponses",
            "details" => $e->getMessage()
        ]);
    }
}

function getAnswerDetails() {
    global $pdo;
    $userId = $_GET['user_id'] ?? null;
    $quizId = $_GET['quiz_id'] ?? null;

    if (!$userId || !$quizId) {
        http_response_code(400);
        echo json_encode(["error" => "ID utilisateur ou quiz manquant"]);
        return;
    }

    try {
        $answers = getAnswerDetails($pdo, $userId, $quizId);
        echo json_encode($answers);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            "error" => "Erreur lors de la récupération des réponses",
            "details" => $e->getMessage()
        ]);
    }
}

function deleteQuestion() {
    global $pdo;
    $data = json_decode(file_get_contents('php://input'), true);

    if (!$data || !isset($data['question_id'])) {
        http_response_code(400);
        echo json_encode(["error" => "ID de la question manquant"]);
        return;
    }

    try {
        $result = deleteQuestion($pdo, $data['question_id']);
        echo json_encode($result);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            "error" => "Erreur lors de la suppression de la question",
            "details" => $e->getMessage()
        ]);
    }
}

function updateQuestion() {
    global $pdo;
    $data = json_decode(file_get_contents('php://input'), true);

    if (!$data || !isset($data['question_id']) || !isset($data['question_text']) || !isset($data['answers']) || !isset($data['correct_answer_index'])) {
        http_response_code(400);
        echo json_encode(["error" => "Données manquantes pour la mise à jour de la question"]);
        return;
    }

    try {
        $result = updateQuestion($pdo, $data['question_id'], $data['question_text'], $data['answers'], $data['correct_answer_index']);
        echo json_encode($result);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            "error" => "Erreur lors de la mise à jour de la question",
            "details" => $e->getMessage()
        ]);
    }
}
?>