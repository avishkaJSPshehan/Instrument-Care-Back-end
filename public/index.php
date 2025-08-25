<?php
declare(strict_types=1);

// 1) Basic bootstrap
error_reporting(E_ALL);
ini_set('display_errors', '1');
header('Content-Type: application/json; charset=utf-8');

// 2) Load config
$config = require __DIR__ . '/../config/config.php';

// 3) PSR-4 style autoloader for App\*
spl_autoload_register(function ($class) {
    $prefix   = 'App\\';
    $base_dir = __DIR__ . '/../app/';
    $len      = strlen($prefix);

    if (strncmp($prefix, $class, $len) !== 0) return;

    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
    if (is_file($file)) require $file;
});

// 4) Helpers
require __DIR__ . '/../app/Helpers/functions.php';

// 5) CORS (OPTIONS preflight support)
$corsOrigin = $config['app']['CORS_ORIGIN'] ?? '*';
header('Access-Control-Allow-Origin: ' . $corsOrigin);
header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
header('Access-Control-Max-Age: 86400');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

// 6) Build Request/Router/DB
$appBasePath = rtrim($config['app']['BASE_PATH'] ?? '', '/');

$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path = '/' . ltrim(substr($requestUri, strlen($appBasePath)), '/');

// Normalize trailing slash (no trailing slash)
if ($path !== '/' && str_ends_with($path, '/')) {
    $path = rtrim($path, '/');
}

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

$request = new App\Core\Request($method, $path, $_GET, $_POST, file_get_contents('php://input'));

$db = new App\Core\Database($config['db']);

// 7) Router & routes
$router = new App\Core\Router();
require __DIR__ . '/../app/Routes/api.php';

// 8) Dispatch
$matched = $router->dispatch($method, $path, $request);
if ($matched === false) {
    App\Core\Response::json(['error' => 'Not Found', 'path' => $path], 404);
}
