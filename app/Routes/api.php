<?php
use App\Controllers\AdminController;
use App\Controllers\ServiceRequestController;
use App\Core\Request;
use App\Core\Response;
use App\Core\Database;
use App\Core\Router;
use App\Controllers\ItemsController;
use App\Controllers\LoginController;
use App\Controllers\RegisterController;
use App\Controllers\ProfileController;

/** @var Router $router */
/** @var Database $db */

// Health
$router->add('GET', '/api/health', function (Request $req) {
    return ['status' => 'ok', 'time' => date('c')];
});

//////////////////////////////////////////// Admin Routes ////////////////////////////////////////////////////////////////////////////////////////////////////////////////
$Admin = new AdminController($db);
$router->add('GET', '/admin/dashboard', [$Admin, 'Get_Admin_Dashboard_Status']);
$router->add('GET', '/admin/line-chart', [$Admin, 'Get_Service_Request_Line_Chart_Data']);
$router->add('GET', '/admin/technicians', [$Admin, 'Get_All_Technician_Details']);
$router->add('PUT', '/admin/technicians/{id}', [$Admin, 'Update_Technician_Profile']);
$router->add('DELETE', '/admin/technicians/{id}', [$Admin, 'Delete_Technician']);
$router->add('GET', '/admin/users', [$Admin, 'Get_All_User_Details']);
$router->add('PUT', '/admin/users/{id}', [$Admin, 'Update_User_Profile']);
$router->add('DELETE', '/admin/users/{id}', [$Admin, 'Delete_User_Profile']);
$router->add('GET', '/admin/instruments', [$Admin, 'Get_All_Instruments']);
$router->add('PUT', '/admin/instrument{id}', [$Admin, 'Update_Instrument']);
$router->add('DELETE', '/admin/instrument/{id}', [$Admin, 'Delete_Instrument']);
$router->add('GET', '/admin/service-requests', [$Admin, 'Get_All_Service_Requests']);

//////////////////////////////////////////// Service Request Routes ////////////////////////////////////////////////////////////////////////////////////////////////////////////////

// Create
$Service_Request = new ServiceRequestController($db);
$router->add('POST', '/user/service-request', [$Service_Request, 'Create_Service_Request']);

$router->add('GET', '/user/service-request/{id}', [$Service_Request, 'Get_Technician_Service_Requests']);
$router->add('GET', '/service-request/{id}/job-counts', [$Service_Request, 'Get_Technician_Job_Counts']);
$router->add('POST', '/user/service-request/{id}/my-requests', [$Service_Request, 'Get_Technician_Service_Requests_By_User']);
$router->add('POST', '/user/my-requests', [$Service_Request, 'Get_All_User_Service_Requests']);
$router->add('PUT', '/api/send-owner-email', [$Service_Request, 'Send_Service_Request_Email']);


/////////////////////////////////////////// Technician Profile Routes /////////////////////////////////////////////////////////////////////////////////////////////////////////////

// Get by id
$profile = new ProfileController($db);
$router->add('GET', '/tech/profile/{id}', [$profile, 'Get_Technician_Profile_Details']);


////////////////////////////////////////// User Routes ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Get All Technician Details
$router->add('GET', '/user/dashboard', [$profile, 'Get_All_Technician_Details']);
$router->add('GET', '/user/dashboard/{id}', [$profile, 'Get_Technician_Profile_Details_by_ID']);


// Update (PUT or PATCH)
$router->add('POST', '/tech/profile/{id}', [$profile, 'Update_Technician_Profile_Details']);
$router->add('PUT',   '/tech/profile/{id}', [$profile, 'Update_Technician_Profile_Details']);
$router->add('PATCH', '/tech/profile/{id}', [$profile, 'Update_Technician_Profile_Details']);

/////////////////////////////////////////// Auth Routs ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

// Login route
$login = new LoginController($db);
$router->add('POST', '/api/login', [$login, 'login']);
$router->add('POST', '/api/email-entry-forgot-password', [$login, 'sendPasswordReset']);
$router->add('PUT', '/api/reset-password', [$login, 'resetPassword']);


// Registration route
$register = new RegisterController($db);
$router->add('POST', '/api/register', [$register, 'register']);
$router->add('POST', '/api/verify-email', [$register, 'verifyEmail']);
