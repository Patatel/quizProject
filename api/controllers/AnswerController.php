<?php
require_once __DIR__ . '/../config/database.php';

function submitAnswers() {
    global $pdo;
    
    // Get JSON data from request body
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!$data) {
        http_response_code(400);
        echo json_encode(["error" => "Données invalides"]);
        return;
    }

    // Validate required fields
    $requiredFields = ['user_id', 'quiz_id', 'answers'];
    foreach ($requiredFields as $field) {
        if (!isset($data[$field])) {
            http_response_code(400);
            echo json_encode(["error" => "Champ manquant: $field"]);
            return;
        }
    }

    try {
        // Start transaction
        $pdo->beginTransaction();

        // Get correct answers for the quiz
        $stmt = $pdo->prepare("
            SELECT id, correct_answer 
            FROM Questions 
            WHERE quiz_id = ?
        ");
        $stmt->execute([$data['quiz_id']]);
        $correctAnswers = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Calculate score
        $score = 0;
        $totalQuestions = count($correctAnswers);
        
        foreach ($data['answers'] as $answer) {
            foreach ($correctAnswers as $correct) {
                if ($answer['question_id'] == $correct['id'] && 
                    $answer['answer'] == $correct['correct_answer']) {
                    $score++;
                    break;
                }
            }
        }

        // Calculate percentage
        $percentage = ($score / $totalQuestions) * 100;

        // Save result
        $stmt = $pdo->prepare("
            INSERT INTO Results (user_id, quiz_id, score, total_questions, percentage, date_passed)
            VALUES (?, ?, ?, ?, ?, NOW())
        ");
        $stmt->execute([
            $data['user_id'],
            $data['quiz_id'],
            $score,
            $totalQuestions,
            $percentage
        ]);

        // Save individual answers
        $stmt = $pdo->prepare("
            INSERT INTO UserAnswers (user_id, quiz_id, question_id, user_answer, is_correct)
            VALUES (?, ?, ?, ?, ?)
        ");

        foreach ($data['answers'] as $answer) {
            $isCorrect = false;
            foreach ($correctAnswers as $correct) {
                if ($answer['question_id'] == $correct['id'] && 
                    $answer['answer'] == $correct['correct_answer']) {
                    $isCorrect = true;
                    break;
                }
            }
            
            $stmt->execute([
                $data['user_id'],
                $data['quiz_id'],
                $answer['question_id'],
                $answer['answer'],
                $isCorrect
            ]);
        }

        $pdo->commit();

        // Return result
        echo json_encode([
            "success" => true,
            "score" => $score,
            "total_questions" => $totalQuestions,
            "percentage" => $percentage
        ]);

    } catch (Exception $e) {
        $pdo->rollBack();
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
        $stmt = $pdo->prepare("
            SELECT 
                q.question_text,
                qa.user_answer,
                qa.is_correct,
                q.correct_answer
            FROM UserAnswers qa
            JOIN Questions q ON qa.question_id = q.id
            WHERE qa.user_id = ? AND qa.quiz_id = ?
            ORDER BY q.id
        ");
        
        $stmt->execute([$userId, $quizId]);
        $answers = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode($answers);

    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            "error" => "Erreur lors de la récupération des réponses",
            "details" => $e->getMessage()
        ]);
    }
} 