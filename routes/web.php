<?php

require_once __DIR__ . '/../Controllers/HomeController.php';
require_once __DIR__ . '/../Controllers/UserController.php';
require_once __DIR__ . '/../Models/User.php';

$routes = [
    'GET' => [
        '/' => ['HomeController', 'index'],
        'users' => ['UserController', 'list'],          // HTML view
        'api/users' => ['UserController', 'apiList'],   // JSON API
    ],
    'POST' => [
        // add POST routes here later
    ]
];
