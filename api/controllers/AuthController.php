<?php
require_once __DIR__ . '/../models/UserModel.php';

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

    $userModel = new UserModel($pdo);
    if ($userModel->createUser($name, $email, $password)) {
        echo json_encode(["message" => "Inscription réussie"]);
    } else {
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

    $userModel = new UserModel($pdo);
    $user = $userModel->findUserByEmail($data['email']);

    if ($user && password_verify($data['password'], $user['password'])) {
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