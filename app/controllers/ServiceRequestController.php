<?php
namespace App\Controllers;

use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use PDO;

final class ServiceRequestController
{
    private PDO $pdo;

    public function __construct(private Database $db)
    {
        $this->pdo = $db->pdo();
    }
    public function Create_Service_Request(Request $req): void
    {
        $data = $req->json(); // Get JSON data from frontend
        $fields = [];
        $placeholders = [];
        $values = [];

        // Mapping frontend keys to database columns
        $mapping = [
            'full_name' => 'full_name',
            'email' => 'email',
            'physical_address' => 'physical_address',
            'contact_number' => 'contact_number',
            'institute_name' => 'institute_name',
            'institute_address' => 'institute_address',
            'instrument_name' => 'instrument_name',
            'instrument_brand' => 'instrument_brand',
            'instrument_model' => 'instrument_model',
            'instrument_manufacturer' => 'instrument_manufacturer',
            'manufactured_year' => 'manufactured_year',
            'product_testing_type' => 'product_testing_type',
            'testing_parameter' => 'testing_parameter',
            'consumption_period' => 'consumption_period',
            'issue_description' => 'issue_description',
            'technician_id' => 'technician_id', 
            'user_id' => 'user_id'
        ];

        foreach ($mapping as $frontendKey => $dbColumn) {
            if (array_key_exists($frontendKey, $data)) {
                $fields[] = $dbColumn;
                $placeholders[] = '?';
                $values[] = trim((string)$data[$frontendKey]);
            }
        }

        if (!$fields) {
            Response::json(['error' => 'No data provided'], 400);
            return;
        }

        // Prepare and execute SQL
        $sql = 'INSERT INTO service_requests (' . implode(', ', $fields) . ') VALUES (' . implode(', ', $placeholders) . ')';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($values);

        $insertedId = $this->pdo->lastInsertId();
        Response::json([
            'message' => 'Service request created successfully',
            'service_request_id' => $insertedId
        ]);
    }

    public function Get_Technician_Service_Requests(Request $req, array $params): void
    {
        // Get technician ID from URL params
        $technicianId = (int)($params['id'] ?? 0);

        if ($technicianId <= 0) {
            Response::json(['error' => 'Invalid technician ID'], 400);
            return;
        }

        try {
            // Fetch all service requests for the given technician
            $stmt = $this->pdo->prepare('SELECT * FROM service_requests WHERE technician_id = ? ORDER BY created_at DESC');
            $stmt->execute([$technicianId]);

            $rows = $stmt->fetchAll();

            if (!$rows || count($rows) === 0) {
                // No records found
                Response::json(['message' => 'No service requests found for this technician', 'data' => []], 200);
                return;
            }

            // Return fetched records
            Response::json($rows);
        } catch (\PDOException $e) {
            // Handle any unexpected DB errors
            Response::json(['error' => 'Server error while fetching service requests'], 500);
        }
    }

    public function Get_Technician_Job_Counts(Request $req, array $params): void
    {
        // Get technician ID from URL params
        $technicianId = (int)($params['id'] ?? 0);

        if ($technicianId <= 0) {
            Response::json(['error' => 'Invalid technician ID'], 400);
            return;
        }

        try {
            // Query to get job counts grouped by status
            $stmt = $this->pdo->prepare(
                'SELECT status, COUNT(*) as count 
                FROM service_requests 
                WHERE technician_id = ? 
                GROUP BY status'
            );
            $stmt->execute([$technicianId]);

            $rows = $stmt->fetchAll();

            if (!$rows || count($rows) === 0) {
                Response::json([
                    'message' => 'No service requests found for this technician', 
                    'data' => []
                ], 200);
                return;
            }

            // Optional: convert to key-value format for easier usage
            $result = [];
            foreach ($rows as $row) {
                $result[$row['status']] = (int)$row['count'];
            }

            Response::json([
                'technician_id' => $technicianId,
                'job_counts' => $result
            ], 200);

        } catch (\PDOException $e) {
            Response::json(['error' => 'Server error while fetching job counts'], 500);
        }
    }

    public function Get_Technician_Service_Requests_By_User(Request $req, array $params): void
{
    // Get technician ID from URL params
    $technicianId = (int)($params['id'] ?? 0);

    // ✅ Get user_id from request body instead of token
    $body = $req->getBody(); 
    $userId = isset($body['user_id']) ? (int)$body['user_id'] : 0;

    if ($technicianId <= 0 || $userId <= 0) {
        Response::json(['error' => 'Invalid technician ID or user not provided'], 400);
        return;
    }

    try {
        // Fetch only service requests for this technician AND this user
        $stmt = $this->pdo->prepare(
            'SELECT * FROM service_requests 
             WHERE technician_id = ? AND user_id = ? 
             ORDER BY created_at DESC'
        );
        $stmt->execute([$technicianId, $userId]);

        $rows = $stmt->fetchAll();

        if (!$rows || count($rows) === 0) {
            Response::json([
                'message' => 'No service requests found for this technician by this user',
                'data' => []
            ], 200);
            return;
        }

        Response::json($rows);
    } catch (\PDOException $e) {
        Response::json(['error' => 'Server error while fetching service requests'], 500);
    }
}



}