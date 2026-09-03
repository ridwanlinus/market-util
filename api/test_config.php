<?php
define('LARAVEL_START', microtime(true));
@mkdir('/tmp/views', 0755, true);
require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
header('Content-Type: application/json');
echo json_encode([
    'config_providers' => config('app.providers') ? 'SET' : 'NOT SET',
    'config_providers_count' => is_array(config('app.providers')) ? count(config('app.providers')) : 0,
]);
