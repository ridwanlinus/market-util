<?php
define('LARAVEL_START', microtime(true));
@mkdir('/tmp/views', 0755, true);
require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';

// Check what's in services.php BEFORE handleRequest
$servicesBefore = require __DIR__.'/../bootstrap/cache/services.php';

// Handle the request
$request = \Illuminate\Http\Request::capture();
$response = $app->handleRequest($request);

// Check services.php AFTER handleRequest
$servicesAfter = require __DIR__.'/../bootstrap/cache/services.php';

header('Content-Type: application/json');
echo json_encode([
    'before_providers_count' => count($servicesBefore['providers'] ?? []),
    'before_eager_count' => count($servicesBefore['eager'] ?? []),
    'after_providers_count' => count($servicesAfter['providers'] ?? []),
    'after_eager_count' => count($servicesAfter['eager'] ?? []),
    'after_has_view' => in_array('Illuminate\\View\\ViewServiceProvider', $servicesAfter['eager'] ?? []),
    'config_app_providers' => config('app.providers') ? 'SET ('.count(config('app.providers')).')' : 'NULL',
]);
