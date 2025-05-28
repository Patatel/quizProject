<?php
require_once __DIR__ . '/../config/database.php';

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

    try {
        $stmt = $pdo->prepare("INSERT INTO quiz (title, description, user_id) VALUES (?, ?, ?)");
        $stmt->execute([$title, $description, $userId]);

        http_response_code(201);
        $quizId = $pdo->lastInsertId();
        echo json_encode(["message" => "Quiz créé avec succès", "id" => $quizId]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(["error" => "Erreur serveur : " . $e->getMessage()]);
    }
}

function getQuizzes()
{
    global $pdo;

    $stmt = $pdo->prepare("SELECT * FROM quiz");
    $stmt->execute(); // Nécessaire pour exécuter la requête
    $quizzes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($quizzes);
}

function getUserQuizzes()
{
    global $pdo;
    $userId = $_GET['user_id'] ?? null;

    if (!$userId) {
        http_response_code(400);
        echo json_encode(["error" => "ID utilisateur requis"]);
        return;
    }

    $stmt = $pdo->prepare("SELECT * FROM quiz WHERE user_id = ?");
    $stmt->execute([$userId]);
    $quizzes = $stmt->fetchAll(PDO::FETCH_ASSOC);

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

    $stmt = $pdo->prepare("SELECT * FROM quiz WHERE id = ?");
    $stmt->execute([$id]);
    $quiz = $stmt->fetch(PDO::FETCH_ASSOC);

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

    $stmt = $pdo->prepare("DELETE FROM quiz WHERE id = ?");
    $stmt->execute([$quizId]);
    echo json_encode(["message" => "Quiz supprimé avec succès"]);
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

    try {
        $stmt = $pdo->prepare("UPDATE quiz SET title = ?, description = ? WHERE id = ?");
        $stmt->execute([$title, $description, $quizId]);

        echo json_encode(["message" => "Quiz mis à jour avec succès"]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(["error" => "Erreur serveur : " . $e->getMessage()]);
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

    try {
        $pdo->beginTransaction();

        foreach ($questions as $questionText) {
            $stmt = $pdo->prepare("INSERT INTO questions (text) VALUES (?)");
            $stmt->execute([$questionText]);
            $questionId = $pdo->lastInsertId();

            $linkStmt = $pdo->prepare("INSERT INTO questionquiz (question_id, quiz_id) VALUES (?, ?)");
            $linkStmt->execute([$questionId, $quizId]);
        }

        $pdo->commit();

        echo json_encode(["message" => "Questions ajoutées avec succès."]);
    } catch (PDOException $e) {
        $pdo->rollBack();
        http_response_code(500);
        echo json_encode(["error" => "Erreur serveur : " . $e->getMessage()]);
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

    try {
        $pdo->beginTransaction();

        // 1. Récupérer les IDs des questions associées au quiz pour suppression
        $stmt = $pdo->prepare("SELECT question_id FROM questionquiz WHERE quiz_id = ?");
        $stmt->execute([$quizId]);
        $questionIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

        if ($questionIds) {
            // Supprimer les liens dans questionquiz
            $inQuery = implode(',', array_fill(0, count($questionIds), '?'));
            $delLinkStmt = $pdo->prepare("DELETE FROM questionquiz WHERE quiz_id = ?");
            $delLinkStmt->execute([$quizId]);

            // Supprimer les questions en elles-mêmes
            $delQuestionsStmt = $pdo->prepare("DELETE FROM questions WHERE id IN ($inQuery)");
            $delQuestionsStmt->execute($questionIds);
        }

        // 2. Insérer les nouvelles questions et leurs liens
        foreach ($questions as $questionText) {
            $cleanText = htmlspecialchars(trim($questionText));
            if ($cleanText === '') continue; // Ignore les questions vides

            $stmtInsertQ = $pdo->prepare("INSERT INTO questions (text) VALUES (?)");
            $stmtInsertQ->execute([$cleanText]);
            $questionId = $pdo->lastInsertId();

            $stmtLink = $pdo->prepare("INSERT INTO questionquiz (question_id, quiz_id) VALUES (?, ?)");
            $stmtLink->execute([$questionId, $quizId]);
        }

        $pdo->commit();

        echo json_encode(["message" => "Questions mises à jour avec succès."]);
    } catch (PDOException $e) {
        $pdo->rollBack();
        http_response_code(500);
        echo json_encode(["error" => "Erreur serveur : " . $e->getMessage()]);
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

    try {
        $stmt = $pdo->prepare("
            SELECT q.id, q.text
            FROM questions q
            JOIN questionquiz qq ON q.id = qq.question_id
            WHERE qq.quiz_id = ?
            ORDER BY q.id ASC
        ");
        $stmt->execute([$quizId]);
        $questions = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode($questions);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(["error" => "Erreur serveur : " . $e->getMessage()]);
    }
}
