<?php
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

// Items resource
$items = new ItemsController($db);


// List
$router->add('GET', '/api/items', [$items, 'index']);

// Get by id
// $router->add('GET', '/api/items/{id}', [$items, 'show']);

// Create
$router->add('POST', '/api/items', [$items, 'store']);

// Update (PUT or PATCH)
// $router->add('PUT',   '/api/items/{id}', [$items, 'update']);
// $router->add('PATCH', '/api/items/{id}', [$items, 'update']);

// Delete
$router->add('DELETE', '/api/items/{id}', [$items, 'destroy']);















//////////////////////////////////////////// Service Request Routes ////////////////////////////////////////////////////////////////////////////////////////////////////////////////

// Create
$Service_Request = new ServiceRequestController($db);
$router->add('POST', '/user/service-request', [$Service_Request, 'Create_Service_Request']);

$router->add('GET', '/user/service-request/{id}', [$Service_Request, 'Get_Technician_Service_Requests']);
$router->add('GET', '/service-request/{id}/job-counts', [$Service_Request, 'Get_Technician_Job_Counts']);


/////////////////////////////////////////// Technician Profile Routes /////////////////////////////////////////////////////////////////////////////////////////////////////////////

// Get by id
$profile = new ProfileController($db);
$router->add('GET', '/tech/profile/{id}', [$profile, 'Get_Technician_Profile_Details']);


////////////////////////////////////////// User Routes ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Get All Technician Details
$router->add('GET', '/user/dashboard', [$profile, 'Get_All_Technician_Details']);
$router->add('GET', '/user/dashboard/{id}', [$profile, 'Get_Technician_Profile_Details_by_ID']);


// Update (PUT or PATCH)
$router->add('PUT',   '/tech/profile/{id}', [$profile, 'Update_Technician_Profile_Details']);
$router->add('PATCH', '/tech/profile/{id}', [$profile, 'Update_Technician_Profile_Details']);

/////////////////////////////////////////// Auth Routs ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

// Login route
$login = new LoginController($db);
$router->add('POST', '/api/login', [$login, 'login']);


// Registration route
$register = new RegisterController($db);
$router->add('POST', '/api/register', [$register, 'register']);
