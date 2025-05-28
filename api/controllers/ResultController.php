<?php
require_once __DIR__ . '/../config/database.php';

function getUserResults() {
    global $pdo;
    $userId = $_GET['user_id'] ?? null;

    if (!$userId) {
        http_response_code(400);
        echo json_encode(["error" => "Utilisateur non défini"]);
        return;
    }

    $stmt = $pdo->prepare("
        SELECT q.title, r.score, r.date_passed
        FROM Results r
        JOIN Quizzes q ON r.quiz_id = q.id
        WHERE r.user_id = ?
        ORDER BY r.date_passed DESC
    ");
    $stmt->execute([$userId]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($results);
}
