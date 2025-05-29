<?php
require_once __DIR__ . '/../config/database.php';

class ResultModel {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function getResultsByUser($userId) {
        $stmt = $this->pdo->prepare("
            SELECT q.title, r.score, r.total_questions, r.percentage, r.date_passed
            FROM results r
            JOIN quiz q ON r.quiz_id = q.id
            WHERE r.user_id = ?
            ORDER BY r.date_passed DESC
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>