<?php
require_once __DIR__ . '/../config/database.php';

class QuizModel
{
    private $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function createQuiz($title, $description, $userId)
    {
        try {
            $stmt = $this->pdo->prepare("INSERT INTO quiz (title, description, user_id) VALUES (?, ?, ?)");
            $stmt->execute([$title, $description, $userId]);
            return $this->pdo->lastInsertId();
        } catch (PDOException $e) {
            error_log("Erreur PDO dans createQuiz: " . $e->getMessage());
            return false;
        }
    }

    public function getQuizzes()
    {
        $stmt = $this->pdo->prepare("SELECT * FROM quiz");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getUserQuizzes($userId)
    {
        $stmt = $this->pdo->prepare("SELECT id, title, description, created_at FROM quiz WHERE user_id = ? ORDER BY created_at DESC");
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getQuizById($id)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM quiz WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function deleteQuiz($quizId)
    {
        $stmt = $this->pdo->prepare("DELETE FROM quiz WHERE id = ?");
        return $stmt->execute([$quizId]);
    }

    public function updateQuiz($quizId, $title, $description)
    {
        try {
            $stmt = $this->pdo->prepare("UPDATE quiz SET title = ?, description = ? WHERE id = ?");
            return $stmt->execute([$title, $description, $quizId]);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function createQuestionsForQuiz($quizId, $questions)
    {
        try {
            $this->pdo->beginTransaction();

            foreach ($questions as $q) {
                $stmt = $this->pdo->prepare("INSERT INTO questions (text) VALUES (?)");
                $stmt->execute([$q['text']]);
                $questionId = $this->pdo->lastInsertId();

                $linkStmt = $this->pdo->prepare("INSERT INTO questionquiz (question_id, quiz_id) VALUES (?, ?)");
                $linkStmt->execute([$questionId, $quizId]);

                foreach ($q['answers'] as $i => $answerText) {
                    if (trim($answerText) !== '') {
                        $isCorrect = ($i === $q['correctAnswerIndex']) ? 1 : 0;
                        $stmtAns = $this->pdo->prepare("INSERT INTO answers (question_id, text, is_correct) VALUES (?, ?, ?)");
                        $stmtAns->execute([$questionId, $answerText, $isCorrect]);
                    }
                }
            }

            $this->pdo->commit();
            return true;
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            return false;
        }
    }

    public function updateQuestions($quizId, $questions)
    {
        try {
            $this->pdo->beginTransaction();

            $stmt = $this->pdo->prepare("SELECT question_id FROM questionquiz WHERE quiz_id = ?");
            $stmt->execute([$quizId]);
            $questionIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

            if ($questionIds) {
                $inQuery = implode(',', array_fill(0, count($questionIds), '?'));
                $delAnswersStmt = $this->pdo->prepare("DELETE FROM answers WHERE question_id IN ($inQuery)");
                $delAnswersStmt->execute($questionIds);

                $delLinkStmt = $this->pdo->prepare("DELETE FROM questionquiz WHERE quiz_id = ?");
                $delLinkStmt->execute([$quizId]);

                $delQuestionsStmt = $this->pdo->prepare("DELETE FROM questions WHERE id IN ($inQuery)");
                $delQuestionsStmt->execute($questionIds);
            }

            foreach ($questions as $q) {
                $cleanText = htmlspecialchars(trim($q['text']));
                if ($cleanText === '') continue;

                $stmtInsertQ = $this->pdo->prepare("INSERT INTO questions (text) VALUES (?)");
                $stmtInsertQ->execute([$cleanText]);
                $questionId = $this->pdo->lastInsertId();

                $stmtLink = $this->pdo->prepare("INSERT INTO questionquiz (question_id, quiz_id) VALUES (?, ?)");
                $stmtLink->execute([$questionId, $quizId]);

                foreach ($q['answers'] as $i => $answerText) {
                    if (trim($answerText) !== '') {
                        $isCorrect = ($i === $q['correctAnswerIndex']) ? 1 : 0;
                        $stmtAns = $this->pdo->prepare("INSERT INTO answers (question_id, text, is_correct) VALUES (?, ?, ?)");
                        $stmtAns->execute([$questionId, $answerText, $isCorrect]);
                    }
                }
            }

            $this->pdo->commit();
            return true;
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            return false;
        }
    }

    public function getQuestionsByQuizId($quizId)
    {
        $stmt = $this->pdo->prepare("
            SELECT q.id, q.text
            FROM questions q
            JOIN questionquiz qq ON q.id = qq.question_id
            WHERE qq.quiz_id = ?
            ORDER BY q.id ASC
        ");
        $stmt->execute([$quizId]);
        $questions = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($questions as &$q) {
            $stmtAns = $this->pdo->prepare("SELECT id, text, is_correct FROM answers WHERE question_id = ?");
            $stmtAns->execute([$q['id']]);
            $answers = $stmtAns->fetchAll(PDO::FETCH_ASSOC);
            $q['answers'] = array_map(function ($a) {
                return $a['text'];
            }, $answers);
            foreach ($answers as $idx => $a) {
                if ($a['is_correct']) {
                    $q['correctAnswerIndex'] = $idx;
                    break;
                }
            }
        }

        return $questions;
    }
    public function getQuizQuestions($quizId)
    {
        $stmt = $this->pdo->prepare("
            SELECT q.id, q.text, a.text as answer, a.is_correct
            FROM questions q
            JOIN answers a ON q.id = a.question_id
            WHERE q.id IN (SELECT question_id FROM questionquiz WHERE quiz_id = ?)
            ORDER BY q.id, a.id
        ");
        $stmt->execute([$quizId]);
        $questions = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $organizedQuestions = [];
        foreach ($questions as $q) {
            if (!isset($organizedQuestions[$q['id']])) {
                $organizedQuestions[$q['id']] = [
                    'id' => $q['id'],
                    'text' => $q['text'],
                    'answers' => []
                ];
            }
            $organizedQuestions[$q['id']]['answers'][] = [
                'text' => $q['answer'],
                'is_correct' => $q['is_correct']
            ];
        }

        return array_values($organizedQuestions);
    }

    public function userSubmitAnswers($userId, $quizId, $answers)
    {
        try {
            $this->pdo->beginTransaction();

            $score = 0;
            $totalQuestions = count($answers);

            foreach ($answers as $answer) {
                $questionId = $answer['question_id'];
                $userAnswer = $answer['user_answer'];

                // Récupère la bonne réponse depuis la base
                $stmt = $this->pdo->prepare("
                SELECT text FROM answers 
                WHERE question_id = ? AND is_correct = 1 LIMIT 1
            ");
                $stmt->execute([$questionId]);
                $correctAnswer = $stmt->fetchColumn();

                $isCorrect = $userAnswer === $correctAnswer ? 1 : 0;
                if ($isCorrect) {
                    $score++;
                }

                // Enregistre la réponse utilisateur
                $stmt = $this->pdo->prepare("
                INSERT INTO useranswers (user_id, quiz_id, question_id, user_answer, is_correct)
                VALUES (?, ?, ?, ?, ?)
            ");
                $stmt->execute([
                    $userId,
                    $quizId,
                    $questionId,
                    $userAnswer,
                    $isCorrect
                ]);
            }

            $percentage = ($score / $totalQuestions) * 100;

            $stmt = $this->pdo->prepare("
            INSERT INTO results (user_id, quiz_id, score, total_questions, percentage, date_passed)
            VALUES (?, ?, ?, ?, ?, NOW())
        ");
            $stmt->execute([
                $userId,
                $quizId,
                $score,
                $totalQuestions,
                $percentage
            ]);

            $this->pdo->commit();

            return [
                "success" => true,
                "score" => $score,
                "total_questions" => $totalQuestions,
                "percentage" => $percentage
            ];
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }
}
