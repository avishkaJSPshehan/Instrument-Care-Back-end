<?php
namespace App\Controllers;

use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use PDO;

final class ProfileController{
    private PDO $pdo;

    public function __construct(private Database $db)
    {
        $this->pdo = $db->pdo();
    }

    public function Get_Technician_Profile_Details(Request $req, array $params): void
    {
        $id = (int)($params['id'] ?? 0); 
        $stmt = $this->pdo->prepare('SELECT * FROM technician_details WHERE user_id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        if (!$row) {
            Response::json(['error' => 'Item not found'], 404);
            return;
        }
        Response::json($row);
    }

}