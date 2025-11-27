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
            // Count Owners (user_type_id = 8)
            $stmtOwners = $this->pdo->prepare('SELECT COUNT(*) AS count FROM users WHERE user_type_id = :user_type_id');
            $stmtOwners->execute(['user_type_id' => 8]);
            $ownersRow = $stmtOwners->fetch();

            // Count Technicians (user_type_id = 10)
            $stmtTech = $this->pdo->prepare('SELECT COUNT(*) AS count FROM users WHERE user_type_id = :user_type_id');
            $stmtTech->execute(['user_type_id' => 10]);
            $techRow = $stmtTech->fetch();

            Response::json([
                'owner_count' => $ownersRow['count'] ?? 0,
                'technician_count' => $techRow['count'] ?? 0
            ]);
        } catch (\PDOException $e) {
            Response::json(['error' => 'Database error: ' . $e->getMessage()], 500);
        }
    }



}
