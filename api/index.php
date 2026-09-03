<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');

set_exception_handler(function($e) {
    http_response_code(500);
    header('Content-Type: text/plain');
    echo "UNCAUGHT: " . get_class($e) . "\n";
    echo "Message: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "Trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
});

set_error_handler(function($severity, $message, $file, $line) {
    if (!(error_reporting() & $severity)) return;
    http_response_code(500);
    header('Content-Type: text/plain');
    echo "ERROR [$severity]: $message in $file:$line\n";
    exit(1);
});

define('LARAVEL_START', microtime(true));
@mkdir('/tmp/views', 0755, true);

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';

$response = $app->handleRequest(
    \Illuminate\Http\Request::capture()
);

$response->send();
