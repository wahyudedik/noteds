<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__ . '/../storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require __DIR__ . '/../vendor/autoload.php';

// Bootstrap Laravel and handle the request...
/** @var Application $app */
$app = require_once __DIR__ . '/../bootstrap/app.php';

// Set Vite dev server URL to HTTP before handling request
if (($_ENV['APP_ENV'] ?? 'production') === 'local') {
    $host = $_SERVER['HTTP_HOST'] ?? 'noteds.test';
    $viteDevServerUrl = "http://{$host}:5173";
    putenv("VITE_DEV_SERVER_URL={$viteDevServerUrl}");
    $_ENV['VITE_DEV_SERVER_URL'] = $viteDevServerUrl;
}

$isLocal = ($_ENV['APP_ENV'] ?? 'production') === 'local';

$response = $app->handleRequest(Request::capture());

// Fix Vite URLs in response object
if ($response && $isLocal) {
    $host = $_SERVER['HTTP_HOST'] ?? 'noteds.test';
    
    if (method_exists($response, 'getContent')) {
        $content = $response->getContent();
        if ($content && is_string($content) && str_contains($content, ':5173')) {
            $newContent = preg_replace(
                '#https://' . preg_quote($host, '#') . ':5173#',
                'http://' . $host . ':5173',
                $content
            );
            if ($newContent !== $content) {
                $response->setContent($newContent);
            }
        }
    }
}

// Send response - Laravel should always return a response
// If response is null, let Laravel's error handler deal with it
if ($response) {
    $response->send();
}
