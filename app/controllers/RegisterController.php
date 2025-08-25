<?php
namespace App\Controllers;

use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use PDO;

final class RegisterController
{
    private PDO $pdo;

    public function __construct(private Database $db)
    {
        $this->pdo = $db->pdo();
    }

    // POST /api/register
    public function register(Request $req): void
    {
        $data = $req->json();

        // Validate required fields
        $required = ['first_name','last_name','mobile_number','username', 'password'];
        $missing = [];
        foreach ($required as $field) {
            if (empty($data[$field])) $missing[] = $field;
        }
        if ($missing) {
            Response::json(['error' => 'Missing fields', 'fields' => $missing], 422);
            return;
        }

        $first_name = trim($data['first_name']);
        $last_name = trim($data['last_name']);
        $mobile_number = trim($data['mobile_number']);
        $username = trim($data['username']);
        $password = $data['password'];

        // Check if username exists
        $stmt = $this->pdo->prepare('SELECT id FROM users WHERE username = ?');
        $stmt->execute([$username]);
        if ($stmt->fetch()) {
            Response::json(['error' => 'Username already exists'], 409);
            return;
        }

        // Hash password
        $passwordHash = password_hash($password, PASSWORD_BCRYPT);

        // Insert user
        $stmt = $this->pdo->prepare(
            'INSERT INTO users (first_name, last_name, mobile_number, user_type_id, username, password, createdBy) VALUES (?, ?, ?, 10, ?, ?, NOW())'
        );
        $stmt->execute([$first_name, $last_name, $mobile_number, $username, $passwordHash]);

        $userId = (int)$this->pdo->lastInsertId();

        Response::json([
            'message' => 'User registered successfully',
            'user_id' => $userId
        ], 201);
    }
}
