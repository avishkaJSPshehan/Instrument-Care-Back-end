<?php
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
$profile = new ProfileController($db);

// List
$router->add('GET', '/api/items', [$items, 'index']);

// Get by id
$router->add('GET', '/api/items/{id}', [$items, 'show']);

// Create
$router->add('POST', '/api/items', [$items, 'store']);

// Update (PUT or PATCH)
$router->add('PUT',   '/api/items/{id}', [$items, 'update']);
$router->add('PATCH', '/api/items/{id}', [$items, 'update']);

// Delete
$router->add('DELETE', '/api/items/{id}', [$items, 'destroy']);


// Get by id
$router->add('GET', '/tech/profile/{id}', [$profile, 'Get_Technician_Profile_Details']);





// Login route
$login = new LoginController($db);
$router->add('POST', '/api/login', [$login, 'login']);


// Registration route
$register = new RegisterController($db);
$router->add('POST', '/api/register', [$register, 'register']);
