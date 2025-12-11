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
                'SELECT COUNT(*) AS count FROM technician_details'
            );
            $stmtTech->execute();
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

    public function Update_Technician_Profile(Request $req, array $params): void
    {
        try {
            // ✅ Get technician ID from URL parameters
            if (!isset($params['id'])) {
                Response::json(['error' => 'Technician ID is required'], 400);
                return;
            }
            $id = $params['id'];

            // ✅ Get input data from JSON body
            $data = json_decode(file_get_contents("php://input"), true);

            // ✅ Allowed columns to update (no skills column)
            $allowedColumns = [
                'full_name', 'current_designation', 'company_name', 'company_designation',
                'years_of_experience', 'email', 'personal_number', 'address',
                'nic', 'institute_name', 'supervisor_name', 'certificate_name',
                'certificate_issued_year', 'certificate_verification_code',
                'bio', 'guarantee_for_service', 'additional_comment'
            ];

            $fields = [];
            $values = [];

            foreach ($allowedColumns as $column) {
                if (isset($data[$column])) {
                    $fields[] = "$column = :$column";
                    $values[":$column"] = $data[$column];
                }
            }

            if (empty($fields)) {
                Response::json(['error' => 'No valid fields to update'], 400);
                return;
            }

            // ✅ Prepare and execute update query
            $sql = "UPDATE technician_details SET " . implode(", ", $fields) . " WHERE id = :id";
            $values[':id'] = $id;

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($values);

            Response::json([
                'success' => true,
                'message' => 'Technician profile updated successfully'
            ]);

        } catch (\PDOException $e) {
            Response::json([
                'error' => 'Database error: ' . $e->getMessage()
            ], 500);
        }
    }

    public function Delete_Technician(Request $req, array $params): void
    {
        try {
            // ✅ Get technician ID from URL parameters
            if (!isset($params['id'])) {
                Response::json(['error' => 'Technician ID is required'], 400);
                return;
            }
            $id = $params['id'];

            // ✅ Prepare and execute DELETE query
            $stmt = $this->pdo->prepare("DELETE FROM technician_details WHERE id = :id");
            $stmt->bindParam(':id', $id, \PDO::PARAM_INT);
            $stmt->execute();

            // ✅ Check if any row was deleted
            if ($stmt->rowCount() > 0) {
                Response::json([
                    'success' => true,
                    'message' => 'Technician deleted successfully'
                ]);
            } else {
                Response::json([
                    'error' => 'Technician not found or already deleted'
                ], 404);
            }

        } catch (\PDOException $e) {
            Response::json([
                'error' => 'Database error: ' . $e->getMessage()
            ], 500);
        }
    }

    public function Get_All_User_Details(Request $req, array $params): void
    {
        try {
            // ✅ Fetch all user details
            $stmt = $this->pdo->prepare("SELECT * FROM users WHERE user_type_id = :user_type_id ORDER BY id ASC");
            $stmt->execute(['user_type_id' => 8]);
            $users = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            // ✅ Return response as JSON
            Response::json($users);

        } catch (\PDOException $e) {
            Response::json([
                'error' => 'Database error: ' . $e->getMessage()
            ], 500);
        }
    }

    public function Update_User_Profile(Request $req, array $params): void
    {
        try {
            // ✅ Get user ID from URL parameters
            if (!isset($params['id'])) {
                Response::json(['error' => 'User ID is required'], 400);
                return;
            }
            $id = $params['id'];

            // ✅ Get input data from JSON body
            $data = json_decode(file_get_contents("php://input"), true);

            // ✅ Allowed columns to update
            $allowedColumns = [
                'first_name', 'last_name', 'email', 'mobile_number', 'phone_number', 
                'address', 'user_type_id', 'institute_id', 'designation', 
                'username', 'gender', 'other_institute_name', 'user_status'
            ];

            $fields = [];
            $values = [];

            foreach ($allowedColumns as $column) {
                if (isset($data[$column])) {
                    $fields[] = "$column = :$column";
                    $values[":$column"] = $data[$column];
                }
            }

            if (empty($fields)) {
                Response::json(['error' => 'No valid fields to update'], 400);
                return;
            }

            // ✅ Add updated timestamp
            $fields[] = "updatedDtm = NOW()";

            // ✅ Prepare and execute update query
            $sql = "UPDATE users SET " . implode(", ", $fields) . " WHERE id = :id";
            $values[':id'] = $id;

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($values);

            Response::json([
                'success' => true,
                'message' => 'User profile updated successfully'
            ]);

        } catch (\PDOException $e) {
            Response::json([
                'error' => 'Database error: ' . $e->getMessage()
            ], 500);
        }
    }


    public function Delete_User_Profile(Request $req, array $params): void
    {
        try {
            // ✅ Get user ID from URL parameters
            if (!isset($params['id'])) {
                Response::json(['error' => 'User ID is required'], 400);
                return;
            }
            $id = $params['id'];

            // ✅ Prepare and execute delete query
            $sql = "DELETE FROM users WHERE id = :id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':id' => $id]);

            // ✅ Check if any row was deleted
            if ($stmt->rowCount() > 0) {
                Response::json([
                    'success' => true,
                    'message' => 'User deleted successfully'
                ]);
            } else {
                Response::json([
                    'success' => false,
                    'message' => 'User not found or already deleted'
                ], 404);
            }

        } catch (\PDOException $e) {
            Response::json([
                'error' => 'Database error: ' . $e->getMessage()
            ], 500);
        }
    }

    public function Get_All_Instruments(): void
    {
        try {
            // ✅ Prepare SQL query to fetch all instruments with instrument_type
            $sql = "
                SELECT 
                    i.instrument_id,
                    i.instrument_name,
                    i.institute_id,
                    i.faculty_id,
                    i.department_id,
                    i.laboratory_id,
                    it.instrument_type AS instrument_type,
                    i.condition_id,
                    i.manufacturer,
                    i.model,
                    i.year_of_manufacture,
                    i.accessories,
                    i.inst_description,
                    i.catalog_link,
                    i.catalog_upload,
                    i.catalog_access,
                    i.price,
                    i.service_charge,
                    i.vendor_name,
                    i.vendor_contact,
                    i.vendor_url,
                    i.no_of_samples_per_cycle,
                    i.no_of_samples_per_day,
                    i.total_usage_hour_per_day,
                    i.specification,
                    i.availabiltiy_of_staff,
                    i.external_researchers,
                    i.funding_source,
                    i.date_commencement_operation,
                    i.record_status,
                    i.contact_person_name,
                    i.contact_person_email,
                    i.contact_person_phone_number,
                    i.contact_person_mobile_number,
                    i.inst_keywords,
                    i.p_categories,
                    i.image_upload1,
                    i.image_upload2,
                    i.image_upload3,
                    i.image_upload4,
                    i.isDeleted,
                    i.created_user_id,
                    i.created_date_time,
                    i.updated_user_id,
                    i.updated_date_time,
                    i.deletedBy,
                    i.deleted_date_time,
                    i.record_endDtm
                FROM instrument i
                LEFT JOIN instrument_types it ON i.instrument_type_id = it.instrument_type_id
                ORDER BY i.instrument_id DESC
            ";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();

            $instruments = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // ✅ Return the result as JSON
            Response::json([
                'success' => true,
                'data' => $instruments
            ]);

        } catch (\PDOException $e) {
            Response::json([
                'error' => 'Database error: ' . $e->getMessage()
            ], 500);
        }
    }

    public function Update_Instrument(Request $req, array $params): void
    {
        try {
            // ✅ Get instrument ID from URL parameters
            if (!isset($params['id'])) {
                Response::json(['error' => 'Instrument ID is required'], 400);
                return;
            }
            $id = $params['id'];

            // ✅ Get input data from JSON body
            $data = json_decode(file_get_contents("php://input"), true);

            // ✅ Allowed columns to update
            $allowedColumns = [
                'instrument_name', 'institute_id', 'faculty_id', 'department_id',
                'laboratory_id', 'condition_id', 'manufacturer',
                'model', 'year_of_manufacture', 'accessories', 'inst_description',
                'catalog_link', 'catalog_upload', 'catalog_access', 'price',
                'service_charge', 'vendor_name', 'vendor_contact', 'vendor_url',
                'no_of_samples_per_cycle', 'no_of_samples_per_day',
                'total_usage_hour_per_day', 'specification', 'availabiltiy_of_staff',
                'external_researchers', 'funding_source', 'date_commencement_operation',
                'record_status', 'contact_person_name', 'contact_person_email',
                'contact_person_phone_number', 'contact_person_mobile_number',
                'inst_keywords', 'p_categories', 'image_upload1', 'image_upload2',
                'image_upload3', 'image_upload4'
            ];

            $fields = [];
            $values = [];

            foreach ($allowedColumns as $column) {
                if (isset($data[$column])) {
                    $fields[] = "$column = :$column";
                    $values[":$column"] = $data[$column];
                }
            }

            if (empty($fields)) {
                Response::json(['error' => 'No valid fields to update'], 400);
                return;
            }

            // ✅ Add updated timestamp
            $fields[] = "updated_date_time = NOW()";

            // ✅ Prepare and execute update query
            $sql = "UPDATE instrument SET " . implode(", ", $fields) . " WHERE instrument_id = :id";
            $values[':id'] = $id;

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($values);

            Response::json([
                'success' => true,
                'message' => 'Instrument updated successfully'
            ]);

        } catch (\PDOException $e) {
            Response::json([
                'error' => 'Database error: ' . $e->getMessage()
            ], 500);
        }
    }


    public function Delete_Instrument(Request $req, array $params): void
    {
        try {
            // ✅ Get instrument ID from URL parameters
            if (!isset($params['id'])) {
                Response::json(['error' => 'Instrument ID is required'], 400);
                return;
            }
            $id = $params['id'];

            // ✅ Prepare and execute delete query
            $sql = "DELETE FROM instrument WHERE instrument_id = :id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':id' => $id]);

            // ✅ Check if any row was deleted
            if ($stmt->rowCount() > 0) {
                Response::json([
                    'success' => true,
                    'message' => 'Instrument deleted successfully'
                ]);
            } else {
                Response::json([
                    'success' => false,
                    'message' => 'Instrument not found or already deleted'
                ], 404);
            }

        } catch (\PDOException $e) {
            Response::json([
                'error' => 'Database error: ' . $e->getMessage()
            ], 500);
        }
    }

    public function Get_All_Service_Requests(Request $req, array $params): void
    {
        try {
            // ✅ Fetch all service request details
            $stmt = $this->pdo->prepare("SELECT * FROM service_requests ORDER BY id ASC");
            $stmt->execute();
            $serviceRequests = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            // ✅ Return response as JSON
            Response::json($serviceRequests);

        } catch (\PDOException $e) {
            Response::json([
                'error' => 'Database error: ' . $e->getMessage()
            ], 500);
        }
    }



}
