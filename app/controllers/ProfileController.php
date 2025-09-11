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

    public function Update_Technician_Profile_Details(Request $req, array $params): void
    {
        $id = (int)($params['id'] ?? 0);
        print_r($id);
        if ($id <= 0) {
            Response::json(['error' => 'Invalid id'], 400);
            return;
        }

        $data = $req->json();
        $fields = [];
        $values = [];

        // Mapping front-end keys to database columns
        $mapping = [
            'fullName' => 'full_name',
            'nic' => 'nic',
            'email' => 'email',
            'address' => 'address',
            'personalNumber' => 'personal_number',
            'bio' => 'bio',
            'experiences' => 'experiences',
            'certificates' => 'certificates',
            'specialistInstrument' => 'specialist_instrument',
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
            if (array_key_exists($frontendKey, $data)) {
                $fields[] = "$dbColumn = ?";
                $values[] = trim((string)$data[$frontendKey]);
            }
        }

        if (!$fields) {
            Response::json(['error' => 'No updatable fields provided'], 400);
            return;
        }

        // Add the ID at the end for the WHERE clause
        $values[] = $id;

        $sql = 'UPDATE technician_details SET ' . implode(', ', $fields) . ', updated_at = NOW() WHERE user_id = ?';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($values);

        Response::json(['message' => 'Profile updated successfully']);
    }


}