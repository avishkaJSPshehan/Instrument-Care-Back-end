<?php
namespace App\Controllers;

use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use PDO;

final class AdminController{

    private PDO $pdo;

    public function __construct(private Database $db)
    {
        $this->pdo = $db->pdo();
    }

    public function Get_Admin_Dashboard_Status(Request $req, array $params): void
    {
        try {
            // Prepare query to count users with user_type_id = 8
            $stmt = $this->pdo->prepare('SELECT COUNT(*) AS user_count FROM users WHERE user_type_id = :user_type_id');
            $stmt->execute(['user_type_id' => 8]);
            
            $row = $stmt->fetch(); // fetch the single row
            
            if (!$row) {
                Response::json(['error' => 'No users found'], 404);
                return;
            }

            // Return count
            Response::json(['user_count' => $row['user_count']]);
        } catch (\PDOException $e) { // <-- add backslash here
            Response::json(['error' => 'Database error: ' . $e->getMessage()], 500);
        }
    }


}
