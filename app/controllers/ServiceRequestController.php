<?php
namespace App\Controllers;

use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use PDO;

// Include EmailController to access sendServiceRequestEmail function
require_once __DIR__ . '/EmailController.php';

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

        // Insert into database
        $sql = 'INSERT INTO service_requests (' . implode(', ', $fields) . ') VALUES (' . implode(', ', $placeholders) . ')';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($values);

        $insertedId = $this->pdo->lastInsertId();

        // Send Email Notification to Technician
        $emailSent = false;

        if (isset($data['technician_id'])) {
            $techId = $data['technician_id'];

            // Fetch technician details from technician_details table
            $techStmt = $this->pdo->prepare('SELECT full_name, email FROM technician_details WHERE id = ?');
            $techStmt->execute([$techId]);
            $technician = $techStmt->fetch(PDO::FETCH_ASSOC);

            if ($technician && !empty($technician['email'])) {
                $recipientEmail = $technician['email'];
                $technicianName = $technician['full_name'];
                $requestId = "SR-" . $insertedId;
                $customer = $data['full_name'] ?? 'N/A';
                $serviceType = $data['product_testing_type'] ?? 'N/A';
                $scheduledDate = $data['scheduled_date'] ?? date('d M Y');
                $location = $data['physical_address'] ?? 'N/A';

                // Call the email function from EmailController
                $emailSent = sendServiceRequestEmail(
                    $recipientEmail,
                    $technicianName,
                    $requestId,
                    $customer,
                    $serviceType,
                    $scheduledDate,
                    $location
                );
            }
        }

        Response::json([
            'message' => 'Service request created successfully',
            'service_request_id' => $insertedId,
            'email_sent' => $emailSent,
            'technician_email' => $technician['email'] ?? null
        ]);
    }


    public function Get_Technician_Service_Requests(Request $req, array $params): void
    {
        $technicianId = (int)($params['id'] ?? 0);

        if ($technicianId <= 0) {
            Response::json(['error' => 'Invalid technician ID'], 400);
            return;
        }

        try {
            $stmt = $this->pdo->prepare('SELECT * FROM service_requests WHERE technician_id = ? ORDER BY created_at DESC');
            $stmt->execute([$technicianId]);

            $rows = $stmt->fetchAll();

            if (!$rows || count($rows) === 0) {
                Response::json(['message' => 'No service requests found for this technician', 'data' => []], 200);
                return;
            }

            Response::json($rows);
        } catch (\PDOException $e) {
            Response::json(['error' => 'Server error while fetching service requests'], 500);
        }
    }

    public function Get_Technician_Job_Counts(Request $req, array $params): void
    {
        $technicianId = (int)($params['id'] ?? 0);

        if ($technicianId <= 0) {
            Response::json(['error' => 'Invalid technician ID'], 400);
            return;
        }

        try {
            $stmt = $this->pdo->prepare(
                'SELECT status, COUNT(*) as count 
                FROM service_requests 
                WHERE technician_id = ? 
                GROUP BY status'
            );
            $stmt->execute([$technicianId]);

            $rows = $stmt->fetchAll();
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
        $technicianId = (int)($params['id'] ?? 0);
        $body = json_decode(file_get_contents("php://input"), true);
        $userId = isset($body['user_id']) ? (int)$body['user_id'] : 0;

        if ($technicianId <= 0 || $userId <= 0) {
            Response::json(['error' => 'Invalid technician ID or user not provided'], 400);
            return;
        }

        try {
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

    public function Get_All_User_Service_Requests(Request $req, array $params): void
    {
        $body = json_decode(file_get_contents("php://input"), true);
        $userId = isset($body['user_id']) ? (int)$body['user_id'] : 0;

        if ($userId <= 0) {
            Response::json(['error' => 'Invalid or missing user ID'], 400);
            return;
        }

        try {
            $stmt = $this->pdo->prepare(
                'SELECT * FROM service_requests 
                WHERE user_id = ? 
                ORDER BY created_at DESC'
            );
            $stmt->execute([$userId]);

            $rows = $stmt->fetchAll();

            if (!$rows || count($rows) === 0) {
                Response::json([
                    'message' => 'No service requests found for this user',
                    'data' => []
                ], 200);
                return;
            }

            Response::json($rows);
        } catch (\PDOException $e) {
            Response::json(['error' => 'Server error while fetching service requests'], 500);
        }
    }

    public function Send_Service_Request_Email(Request $req): void
{
    // Decode incoming JSON body
    $body = json_decode(file_get_contents("php://input"), true);

    $ownerEmail = $body['owner_email'] ?? null;
    $subject = $body['subject'] ?? null;
    $message = $body['message'] ?? null;
    $requestId = $body['request_id'] ?? null;

    if (!$ownerEmail || !$subject || !$message || !$requestId) {
        Response::json(['error' => 'Missing required fields'], 400);
        return;
    }

    // Fetch request details for email content (optional but useful)
    try {
        $stmt = $this->pdo->prepare("
            SELECT sr.id, sr.customer_name, sr.instrument_name, sr.service_type, sr.scheduled_date, sr.location
            FROM service_requests sr
            WHERE sr.id = ?
        ");
        $stmt->execute([$requestId]);
        $serviceRequest = $stmt->fetch();

        if (!$serviceRequest) {
            Response::json(['error' => 'Service request not found'], 404);
            return;
        }
    } catch (\PDOException $e) {
        Response::json(['error' => 'Database error fetching service request'], 500);
        return;
    }

    // Send the email
    $emailSent = sendOwnerEmail(
        $ownerEmail,
        $subject,
        $message,
        $serviceRequest
    );

    if ($emailSent) {
        // Update service request status to IN_PROGRESS
        try {
            $updateStmt = $this->pdo->prepare("
                UPDATE service_requests
                SET status = 'IN_PROGRESS'
                WHERE id = ?
            ");
            $updateStmt->execute([$requestId]);
        } catch (\PDOException $e) {
            Response::json(['error' => 'Email sent, but failed to update status'], 500);
            return;
        }

        Response::json([
            'success' => true,
            'message' => 'Email sent successfully and status updated to IN_PROGRESS',
        ]);
    } else {
        Response::json(['error' => 'Failed to send email'], 500);
    }
}

}
