<?php
require_once __DIR__ . '/../config/database.php';

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

    try {
        $stmt = $pdo->prepare("UPDATE Users SET name = ?, email = ? WHERE id = ?");
        $stmt->execute([$name, $email, $id]);
        echo json_encode(["message" => "Mise à jour réussie"]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(["error" => "Erreur lors de la mise à jour"]);
    }
}
