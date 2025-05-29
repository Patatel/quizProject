<?php
require_once __DIR__ . '/../models/UserModel.php';

function updateUser() {
    global $pdo;
    $data = json_decode(file_get_contents("php://input"), true);

    if (!isset($data['id'], $data['name'], $data['email'])) {
        http_response_code(400);
        echo json_encode(["error" => "Champs manquants"]);
        return;
    }

    $id = intval($data['id']);
    $name = htmlspecialchars($data['name']);
    $email = filter_var($data['email'], FILTER_VALIDATE_EMAIL);

    if (!$email) {
        http_response_code(400);
        echo json_encode(["error" => "Email invalide"]);
        return;
    }

    $userModel = new UserModel($pdo);
    if ($userModel->updateUser($id, $name, $email)) {
        echo json_encode(["message" => "Mise à jour réussie"]);
    } else {
        http_response_code(500);
        echo json_encode(["error" => "Erreur lors de la mise à jour"]);
    }
}