<?php
require_once __DIR__ . '/../models/ResultModel.php';


function getUserResults() {
    global $pdo;
    $userId = $_GET['user_id'] ?? null;

    if (!$userId) {
        http_response_code(400);
        echo json_encode(["error" => "Utilisateur non défini"]);
        return;
    }

    $model = new ResultModel($pdo);
    $results = $model->getResultsByUser($userId);
    echo json_encode($results);
}
