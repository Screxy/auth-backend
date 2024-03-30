<?php

declare(strict_types=1);

use App\Controllers\UserController;
use Core\Logger;
use Core\Request;
use Core\Response;
use Core\Router;

require dirname(__DIR__) . '/vendor/autoload.php';

if (isset($_SERVER['HTTP_ORIGIN'])) {
    header('Access-Control-Allow-Origin: ' . $_SERVER['HTTP_ORIGIN']);
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Max-Age: 86400');    // cache for 1 day
}

// Access-Control headers are received during OPTIONS requests
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {

    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD'])) {
        header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PATCH, DELETE");
    }
    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS'])) {
        header('Access-Control-Allow-Headers: ' . $_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']);
    }
    exit(0);
}

$router = new Router();

$router->get('/feed', [UserController::class, 'feed']);
$router->get('/', [UserController::class, 'test']);
$router->post('/authorize', [UserController::class, 'authorize']);
$router->post('/register', [UserController::class, 'register']);

$request = new Request($_SERVER);

$router->addNotFoundHandler(function () {
    echo new Response(404, ['message' => 'Not found']);
});

try {
    $router->run($request);
} catch (\Throwable $exception) {
    http_response_code(500);
    echo 'Internal Server Error';
    Logger::error([$exception->getMessage(), $exception->getLine(), $exception->getTrace()]);
}
