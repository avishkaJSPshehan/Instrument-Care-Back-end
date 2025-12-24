<?php
namespace App\Controllers;

use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use PDO;

// include email controller functions
require_once __DIR__ . "/EmailController.php";

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
        $required = ['first_name','last_name','mobile_number', 'password', 'email'];
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

        // Check if username/email exists
        $stmt = $this->pdo->prepare('SELECT id FROM users WHERE email = ?');
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            Response::json(['error' => 'Email already registered'], 409);
            return;
        }

        // Hash password
        $passwordHash = password_hash($password, PASSWORD_BCRYPT);

        // Insert into users
        $stmt = $this->pdo->prepare(
            'INSERT INTO users (first_name, last_name, mobile_number, email, username, user_type_id, password, createdBy) 
             VALUES (?, ?, ?, ?, ?, 10, ?, NOW())'
        );
        $stmt->execute([$first_name, $last_name, $mobile_number, $email, $email, $passwordHash]);

        $userId = (int)$this->pdo->lastInsertId();

        // ------------------ Insert into technician_details ------------------
        $stmtTech = $this->pdo->prepare(
            'INSERT INTO technician_details 
             (user_id, full_name, personal_number, email, created_at) 
             VALUES (?, ?, ?, ?, NOW())'
        );
        $stmtTech->execute([
            $userId,
            $first_name . ' ' . $last_name,
            $mobile_number,
            $email
        ]);
        // ---------------------------------------------------------------------

        // ------------------ Generate & Save Verification Code ----------------
        $verificationCode = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        $stmtOtp = $this->pdo->prepare(
            'INSERT INTO email_verification (user_id, code, expires_at, created_at) 
             VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 10 MINUTE), NOW())'
        );
        $stmtOtp->execute([$userId, $verificationCode]);
        // ---------------------------------------------------------------------

        // ------------------ Send Verification Email --------------------------
        $emailSent = sendEmailVerification($email, $verificationCode);
        // ---------------------------------------------------------------------

        Response::json([
            'message' => 'User registered successfully. Verification email sent.',
            'user_id' => $userId,
            'email_sent' => $emailSent
        ], 201);
    }

    // POST /api/verify-email
    public function verifyEmail(Request $req): void
    {
        $data = $req->json();
        $required = ['user_id','otp'];
        $missing = [];
        foreach ($required as $field) {
            if (empty($data[$field])) $missing[] = $field;
        }
        if ($missing) {
            Response::json(['error' => 'Missing fields', 'fields' => $missing], 422);
            return;
        }

        $userId = (int)$data['user_id'];
        $otp = trim($data['otp']);

        // Check OTP in email_verification table
        $stmt = $this->pdo->prepare(
            'SELECT * FROM email_verification WHERE user_id = ? AND code = ? AND expires_at > NOW()'
        );
        $stmt->execute([$userId, $otp]);
        $verification = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$verification) {
            Response::json(['message' => 'Invalid or expired OTP'], 400);
            return;
        }

        // Mark email as verified in users table
        $stmtUpdate = $this->pdo->prepare('UPDATE users SET is_email_verified = 1 WHERE id = ?');
        $stmtUpdate->execute([$userId]);

        // Delete the OTP record (optional)
        $stmtDelete = $this->pdo->prepare('DELETE FROM email_verification WHERE user_id = ?');
        $stmtDelete->execute([$userId]);

        Response::json(['message' => 'Verification successful'], 200);
    }

    public function Get_All_Institutes(Request $req, array $params): void
    {
        try {
            // ✅ Get all institute IDs and names
            $stmt = $this->pdo->prepare("
                SELECT 
                    institute_id,
                    name
                FROM institutes
                ORDER BY name ASC
            ");

            $stmt->execute();
            $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            // ✅ Return institute list
            Response::json($rows);

        } catch (\PDOException $e) {
            Response::json([
                'error' => 'Database error: ' . $e->getMessage()
            ], 500);
        }
    }

}
