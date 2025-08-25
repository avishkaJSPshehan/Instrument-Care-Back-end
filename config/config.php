<?php
return [
    'app' => [
        // If your app lives at http://localhost/php-rest-api/public,
        // BASE_PATH must equal '/php-rest-api/public'
        'BASE_PATH' => '/instrument-care-back-end/public',
        'CORS_ORIGIN' => '*', // tighten this in production
    ],
    'db' => [
        'host' => '127.0.0.1',
        'name' => 'instrument',
        'user' => 'root',
        'pass' => '', // XAMPP default is empty
        'charset' => 'utf8mb4',
    ],
];
