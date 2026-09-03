<?php
// DEBUG: Output immediately to prove PHP is executing
header('X-Debug: PHP-executed');
error_reporting(E_ALL);
ini_set('display_errors', '1');

define('LARAVEL_START', microtime(true));
@mkdir('/tmp/views', 0755, true);

require __DIR__.'/../vendor/autoload.php';

$app = require_once __DIR__.'/../bootstrap/app.php';

$response = $app->handleRequest(
    \Illuminate\Http\Request::capture()
);

$response->send();
