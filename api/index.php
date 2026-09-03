<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

header('Content-Type: text/plain');

echo "Step 1: PHP OK\n";
define('LARAVEL_START', microtime(true));
@mkdir('/tmp/views', 0755, true);

echo "Step 2: Autoloader loading...\n";
require __DIR__.'/../vendor/autoload.php';
echo "Step 2: Done\n";

echo "Step 3: Bootstrap loading...\n";
$app = require_once __DIR__.'/../bootstrap/app.php';
echo "Step 3: Done - " . get_class($app) . "\n";

echo "Step 4: Creating request...\n";
$request = \Illuminate\Http\Request::capture();
echo "Step 4: Done\n";

echo "Step 5: handleRequest...\n";
$response = $app->handleRequest($request);
echo "Step 5: Done - status: " . $response->getStatusCode() . "\n";

echo "Step 6: Sending response...\n";
$response->send();
echo "Step 6: Done\n";
