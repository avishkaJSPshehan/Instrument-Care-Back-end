<?php
namespace App\Controllers;

use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use PDO;

final class LoginController
{
    private PDO $pdo;

    public function __construct(private Database $db)
    {
        $this->pdo = $db->pdo();
    }

    // POST /api/login
    public function login(Request $req): void
    {
        $data = $req->json();

        if (empty($data['username']) || empty($data['password'])) {
            Response::json(['error' => 'Username and password are required'], 422);
            return;
        }

        $username = trim($data['username']);
        $password = $data['password'];

        // find user and user type
        $stmt = $this->pdo->prepare('SELECT id, user_type_id, username, password FROM users WHERE username = ?');
        $stmt->execute([$username]);
        $user = $stmt->fetch();
        $roleId = $user['user_type_id'];

        if (!$user || !password_verify($password, $user['password'])) {
            Response::json(['error' => 'Invalid credentials'], 401);
            return;
        }

        // Fake JWT-like token (in production use real JWT lib)
        $payload = [
            'sub' => $user['id'],
            'username' => $user['username'],
            'iat' => time(),
            'exp' => time() + 3600 // 1 hour expiry
        ];
        $token = base64_encode(json_encode($payload));

        Response::json([
            'message' => 'Login successful',
            'token' => $token,
            'role' => $roleId
        ]);
    }
}
