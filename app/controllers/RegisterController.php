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
        $required = ['first_name','last_name','mobile_number', 'password'];
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
        $email = trim($data['email']);
        $password = $data['password'];

        // Check if username exists
        $stmt = $this->pdo->prepare('SELECT id FROM users WHERE email = ?');
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            Response::json(['error' => 'Username already exists'], 409);
            return;
        }

        // Hash password
        $passwordHash = password_hash($password, PASSWORD_BCRYPT);

        // Insert user
        $stmt = $this->pdo->prepare(
            'INSERT INTO users (first_name, last_name, mobile_number, email, username, user_type_id, password, createdBy) 
             VALUES (?, ?, ?, ?, ?, 10, ?, NOW())'
        );
        $stmt->execute([$first_name, $last_name, $mobile_number, $email, $email, $passwordHash]);

        $userId = (int)$this->pdo->lastInsertId();

        // ------------------ Insert into technician_details ------------------
        $stmtTech = $this->pdo->prepare(
            'INSERT INTO technician_details 
             (user_id, full_name, personal_number, email,created_at) 
             VALUES (?, ?, ?, ?, NOW())'
        );
        $stmtTech->execute([
            $userId,                         // foreign key to users.id
            $first_name . ' ' . $last_name,  // full_name from registration
            $mobile_number,                   // personal_number from registration
            $email                           // email
        ]);
        // ---------------------------------------------------------------------

        Response::json([
            'message' => 'User registered successfully',
            'user_id' => $userId
        ], 201);
    }
}
