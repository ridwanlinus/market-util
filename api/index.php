<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

header('Content-Type: text/plain');

define('LARAVEL_START', microtime(true));
@mkdir('/tmp/views', 0755, true);

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';

$request = \Illuminate\Http\Request::capture();

try {
    $response = $app->handleRequest($request);
    $response->send();
} catch (\Throwable $e) {
    echo "CAUGHT: " . get_class($e) . "\n";
    echo "Message: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "Trace:\n" . substr($e->getTraceAsString(), 0, 2000) . "\n";
}
