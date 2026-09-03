<?php
header('Content-Type: application/json');
echo json_encode([
    'status' => 'ok',
    'php' => PHP_VERSION,
    'sapi' => php_sapi_name(),
    'laravel' => class_exists('Illuminate\Foundation\Application') ? 'loaded' : 'missing',
    'env' => [
        'APP_KEY' => env('APP_KEY') ? 'set' : 'missing',
        'DB_CONNECTION' => env('DB_CONNECTION', 'not set'),
        'APP_DEBUG' => env('APP_DEBUG', 'not set'),
    ],
    'vendor' => file_exists(__DIR__.'/../vendor/autoload.php') ? 'exists' : 'missing',
]);
