<?php
require_once __DIR__ . '/../config/database.php';

function register() {
    global $pdo;
    $data = json_decode(file_get_contents("php://input"), true);

    if (!isset($data['name'], $data['email'], $data['password'])) {
        http_response_code(400);
        echo json_encode(["error" => "Champs manquants"]);
        return;
    }

    $name = htmlspecialchars($data['name']);
    $email = filter_var($data['email'], FILTER_VALIDATE_EMAIL);
    $password = password_hash($data['password'], PASSWORD_BCRYPT);

    if (!$email) {
        http_response_code(400);
        echo json_encode(["error" => "Email invalide"]);
        return;
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO Users (name, email, password) VALUES (?, ?, ?)");
        $stmt->execute([$name, $email, $password]);
        echo json_encode(["message" => "Inscription réussie"]);
    } catch (PDOException $e) {
        http_response_code(400);
        echo json_encode(["error" => "Email déjà utilisé"]);
    }
}


function login() {
    global $pdo;
    $data = json_decode(file_get_contents("php://input"), true);

    if (!isset($data['email'], $data['password'])) {
        http_response_code(400);
        echo json_encode(["error" => "Champs manquants"]);
        return;
    }

    $stmt = $pdo->prepare("SELECT * FROM Users WHERE email = ?");
    $stmt->execute([$data['email']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($data['password'], $user['password'])) {
        // (Tu peux générer un token ici si tu veux)
        echo json_encode([
            "message" => "Connexion réussie",
            "user" => [
                "id" => $user['id'],
                "name" => $user['name'],
                "email" => $user['email'],
                "role" => $user['role']
            ]
        ]);
    } else {
        http_response_code(401);
        echo json_encode(["error" => "Identifiants invalides"]);
    }
}
