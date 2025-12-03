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
            $stmtOwners = $this->pdo->prepare(
                'SELECT COUNT(*) AS count FROM users WHERE user_type_id = :user_type_id'
            );
            $stmtOwners->execute(['user_type_id' => 8]);
            $ownersRow = $stmtOwners->fetch();

            // Count Technicians (user_type_id = 10)
            $stmtTech = $this->pdo->prepare(
                'SELECT COUNT(*) AS count FROM users WHERE user_type_id = :user_type_id'
            );
            $stmtTech->execute(['user_type_id' => 10]);
            $techRow = $stmtTech->fetch();

            // ✅ Count Instruments
            $stmtInstrument = $this->pdo->prepare(
                'SELECT COUNT(*) AS count FROM instrument'
            );
            $stmtInstrument->execute();
            $instrumentRow = $stmtInstrument->fetch();

            // ✅ Count Service Requests
            $stmtServiceRequests = $this->pdo->prepare(
                'SELECT COUNT(*) AS count FROM service_requests'
            );
            $stmtServiceRequests->execute();
            $serviceRequestRow = $stmtServiceRequests->fetch();

            // ✅ Final Dashboard Response
            Response::json([
                'owner_count'          => $ownersRow['count'] ?? 0,
                'technician_count'     => $techRow['count'] ?? 0,
                'instrument_count'     => $instrumentRow['count'] ?? 0,
                'service_request_count'=> $serviceRequestRow['count'] ?? 0
            ]);

        } catch (\PDOException $e) {
            Response::json([
                'error' => 'Database error: ' . $e->getMessage()
            ], 500);
        }
    }

    public function Get_Service_Request_Line_Chart_Data(Request $req, array $params): void
    {
        try {
            // ✅ Get service request count grouped by date
            $stmt = $this->pdo->prepare("
                SELECT 
                    DATE(created_at) AS date, 
                    COUNT(*) AS count
                FROM service_requests
                GROUP BY DATE(created_at)
                ORDER BY DATE(created_at) ASC
            ");

            $stmt->execute();
            $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            // ✅ Return data in required format for line chart
            Response::json($rows);

        } catch (\PDOException $e) {
            Response::json([
                'error' => 'Database error: ' . $e->getMessage()
            ], 500);
        }
    }

    public function Get_All_Technician_Details(Request $req, array $params): void
    {
        try {
            // ✅ Fetch all technician details
            $stmt = $this->pdo->prepare("SELECT * FROM technician_details ORDER BY id ASC");
            $stmt->execute();
            $technicians = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            // ✅ Return response as JSON
            Response::json($technicians);

        } catch (\PDOException $e) {
            Response::json([
                'error' => 'Database error: ' . $e->getMessage()
            ], 500);
        }
    }



}
