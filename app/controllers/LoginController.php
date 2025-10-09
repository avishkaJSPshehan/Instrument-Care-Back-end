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
        $id = $user['id'];

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
            'id' => $id,
            'role' => $roleId
        ]);
    }


    public function sendPasswordReset(Request $req): void
    {
        $data = $req->json();

        if (empty($data['email'])) {
            Response::json(['error' => 'Email is required'], 422);
            return;
        }

        $recipientEmail = trim($data['email']);

        // Check if user exists with this email
        $stmt = $this->pdo->prepare('SELECT id, username FROM users WHERE username = ? OR email = ?');
        $stmt->execute([$recipientEmail, $recipientEmail]);
        $user = $stmt->fetch();

        if (!$user) {
            Response::json(['error' => 'No account found with this email'], 404);
            return;
        }

        // Generate a temporary token (for demo: base64 random + timestamp)
        $resetToken = base64_encode(random_bytes(32)) . '.' . time();

        // Call email sender
        if (sendPasswordResetEmail($recipientEmail, $resetToken)) {
            Response::json(['message' => 'Password reset link sent successfully']);
        } else {
            Response::json(['error' => 'Failed to send password reset email'], 500);
        }
    }


    public function resetPassword(Request $req): void
    {
        $data = $req->json();

        if (empty($data['email']) || empty($data['password'])) {
            Response::json(['error' => 'Email and new password are required'], 422);
            return;
        }

        $email = trim($data['email']);
        $newPassword = $data['password'];

        // Find the user by email
        $stmt = $this->pdo->prepare('SELECT id FROM users WHERE username = ?');
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if (!$user) {
            Response::json(['error' => 'User not found'], 404);
            return;
        }

        // Hash the new password
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

        // Update the user's password in the database
        $updateStmt = $this->pdo->prepare('UPDATE users SET password = ? WHERE email = ?');
        $success = $updateStmt->execute([$hashedPassword, $email]);

        if ($success) {
            Response::json(['message' => 'Password reset successful'], 200);
        } else {
            Response::json(['error' => 'Failed to reset password. Please try again.'], 500);
        }
    }


}
