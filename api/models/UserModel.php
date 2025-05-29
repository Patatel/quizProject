<?php
require_once __DIR__ . '/../config/database.php';

class UserModel {
    private $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function createUser($name, $email, $password) {
        try {
            $stmt = $this->pdo->prepare("INSERT INTO Users (name, email, password) VALUES (?, ?, ?)");
            return $stmt->execute([$name, $email, $password]);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function findUserByEmail($email) {
        $stmt = $this->pdo->prepare("SELECT * FROM Users WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updateUser($id, $name, $email) {
        try {
            $stmt = $this->pdo->prepare("UPDATE Users SET name = ?, email = ? WHERE id = ?");
            return $stmt->execute([$name, $email, $id]);
        } catch (PDOException $e) {
            return false;
        }
    }
}