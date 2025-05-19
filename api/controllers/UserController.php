<?php
require_once '../config/database.php';

function listUsers() {
    global $pdo;

    $stmt = $pdo->query("SELECT id, name, email FROM Users");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($users);
}
