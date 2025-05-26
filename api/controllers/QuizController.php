<?php
require_once __DIR__ . '/../config/database.php';

function createQuiz() {
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

    try {
        $stmt = $pdo->prepare("INSERT INTO Quizzes (title, description, user_id) VALUES (?, ?, ?)");
        $stmt->execute([$title, $description, $userId]);

        http_response_code(201);
        echo json_encode(["message" => "Quiz créé avec succès"]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(["error" => "Erreur serveur : " . $e->getMessage()]);
    }
}

function getUserQuizzes() {
    global $pdo;
    $userId = $_GET['user_id'] ?? null;

    if (!$userId) {
        http_response_code(400);
        echo json_encode(["error" => "ID utilisateur requis"]);
        return;
    }

    $stmt = $pdo->prepare("SELECT * FROM Quizzes WHERE user_id = ?");
    $stmt->execute([$userId]);
    $quizzes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($quizzes);
}

function deleteQuiz() {
    global $pdo;
    parse_str(file_get_contents("php://input"), $data);
    $quizId = $data['id'] ?? null;

    if (!$quizId) {
        http_response_code(400);
        echo json_encode(["error" => "ID du quiz requis"]);
        return;
    }

    $stmt = $pdo->prepare("DELETE FROM Quizzes WHERE id = ?");
    $stmt->execute([$quizId]);
    echo json_encode(["message" => "Quiz supprimé avec succès"]);
}
