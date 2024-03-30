<?php

declare(strict_types=1);


use App\Controllers\UserController;
use App\Core\Logger;
use App\Core\Response;
use App\Core\Router;

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
$router->get('/feed', UserController::class . '::feed');
$router->post('/authorize', UserController::class . '::authorize');
$router->post('/register', UserController::class . '::register');


$router->addNotFoundHandler(function () {
    echo new Response(404, ['message' => 'Not found']);
});
try {
    $router->run($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);
} catch (\Throwable $exception) {
    http_response_code(500);
    echo 'Internal Server Error';
    Logger::error([$exception->getMessage(), $exception->getLine(), $exception->getTrace()]);
}