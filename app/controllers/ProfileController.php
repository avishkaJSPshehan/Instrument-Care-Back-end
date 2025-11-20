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

    public function Get_All_Technician_Details(Request $req, array $params): void
    {
        // Prepare query to fetch all technician details
        $stmt = $this->pdo->prepare('SELECT * FROM technician_details');
        $stmt->execute();
        
        $rows = $stmt->fetchAll(); // fetch all rows

        if (!$rows) {
            Response::json(['error' => 'No technicians found'], 404);
            return;
        }

        Response::json($rows);
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

    public function Get_Technician_Profile_Details_by_ID(Request $req, array $params): void
    {
        $id = (int)($params['id'] ?? 0); 

        // Change user_id to technician_id
        $stmt = $this->pdo->prepare('SELECT * FROM technician_details WHERE id = ?');
        $stmt->execute([$id]);
        
        $row = $stmt->fetch();
        if (!$row) {
            Response::json(['error' => 'Technician not found'], 404);
            return;
        }

        Response::json($row);
    }
public function Update_Technician_Profile_Details(Request $req, array $params = []): void
{
    $id = (int)($params['id'] ?? 0);
    if ($id <= 0) {
        Response::json(['error' => 'Invalid id'], 400);
        return;
    }

    // Since React sends FormData, read normal fields from $_POST
    $data = $_POST ?? [];
    if (isset($data['_method'])) {
        unset($data['_method']);
    }

    $fields = [];
    $values = [];

    $mapping = [
        'fullName' => 'full_name',
        'nic' => 'nic',
        'email' => 'email',
        'address' => 'address',
        'personalNumber' => 'personal_number',
        'bio' => 'bio',
        'current_designation' => 'current_designation',
        'institute_name' => 'institute_name',
        'laboratory_category' => 'laboratory_category',
        'instrument_category' => 'instrument_category',
        'supervisor_name' => 'supervisor_name',
        'supervisor_Designation' => 'supervisor_designation',
        'supervisor_Email' => 'supervisor_email',
        'supervisor_Contract_No' => 'supervisor_contract_no',
        'company_name' => 'company_name',
        'company_designation' => 'company_designation',
        'years_of_experience' => 'years_of_experience',
        'certificate_name' => 'certificate_name',
        'certificate_issued_year' => 'certificate_issued_year',
        'certificate_verification_code' => 'certificate_verification_code',
        'guarantee_for_service' => 'guarantee_for_service',
        'additional_comment' => 'additional_comment'
    ];

    foreach ($mapping as $frontendKey => $dbColumn) {
        if (isset($data[$frontendKey])) {
            $fields[] = "$dbColumn = ?";
            $values[] = trim((string)$data[$frontendKey]);
        }
    }

    // Handle profile image from FormData
    if (!empty($_FILES['profileImage']['tmp_name'])) {
        $maxImageSize = 2 * 1024 * 1024; // 2 MB limit to stay well below MySQL packet threshold
        $uploadedSize = (int)($_FILES['profileImage']['size'] ?? 0);

        if ($uploadedSize > $maxImageSize) {
            Response::json([
                'error' => 'Profile image is too large. Please upload a file under 2MB so it fits the server limit.'
            ], 413);
            return;
        }

        $imageData = file_get_contents($_FILES['profileImage']['tmp_name']);
        $fields[] = "profile_image = ?";
        $values[] = $imageData;
    }

    if (!$fields) {
        Response::json(['error' => 'No updatable fields provided'], 400);
        return;
    }

    $values[] = $id;

    $sql = 'UPDATE technician_details SET '
         . implode(', ', $fields)
         . ', updated_at = NOW() WHERE user_id = ?';

    $stmt = $this->pdo->prepare($sql);
    $stmt->execute($values);

    Response::json(['message' => 'Profile updated successfully']);
}




}