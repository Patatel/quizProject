<?php
// Fichier : models/AnswerModel.php

require_once __DIR__ . '/../config/database.php';

function submitAnswers($pdo, $data) {
    try {
        $pdo->beginTransaction();

        // Récupérer les bonnes réponses pour le quiz
        $stmt = $pdo->prepare("
            SELECT id, correct_answer
            FROM Questions
            WHERE quiz_id = ?
        ");
        $stmt->execute([$data['quiz_id']]);
        $correctAnswers = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Calculer le score
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

        // Calculer le pourcentage
        $percentage = ($score / $totalQuestions) * 100;

        // Sauvegarder le résultat
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

        // Sauvegarder les réponses individuelles
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

        return [
            "success" => true,
            "score" => $score,
            "total_questions" => $totalQuestions,
            "percentage" => $percentage
        ];

    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
}

function getAnswerDetails($pdo, $userId, $quizId) {
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
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function deleteQuestion($pdo, $questionId) {
    try {
        $pdo->beginTransaction();

        // Supprimer les réponses associées à la question
        $stmt = $pdo->prepare("DELETE FROM UserAnswers WHERE question_id = ?");
        $stmt->execute([$questionId]);

        // Supprimer la question
        $stmt = $pdo->prepare("DELETE FROM Questions WHERE id = ?");
        $stmt->execute([$questionId]);

        $pdo->commit();

        return ["success" => true];
    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
}

function updateQuestion($pdo, $questionId, $questionText, $answers, $correctAnswerIndex) {
    try {
        $pdo->beginTransaction();

        // Mettre à jour la question
        $stmt = $pdo->prepare("UPDATE Questions SET question_text = ? WHERE id = ?");
        $stmt->execute([$questionText, $questionId]);

        // Mettre à jour les réponses
        foreach ($answers as $index => $answer) {
            $isCorrect = ($index == $correctAnswerIndex) ? 1 : 0;
            $stmt = $pdo->prepare("
                UPDATE Answers
                SET answer_text = ?, is_correct = ?
                WHERE question_id = ? AND id = ?
            ");
            $stmt->execute([$answer, $isCorrect, $questionId, $index + 1]);
        }

        $pdo->commit();

        return ["success" => true];
    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
}
?>