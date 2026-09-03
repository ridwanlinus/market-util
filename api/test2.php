<?php
// Minimal Laravel boot test - focus on handleRequest()
define('LARAVEL_START', microtime(true));
@mkdir('/tmp/views', 0755, true);

header('Content-Type: application/json');
$results = [];

try {
    require __DIR__.'/../vendor/autoload.php';
    $app = require __DIR__.'/../bootstrap/app.php';
    $request = \Illuminate\Http\Request::capture();
    $response = $app->handleRequest($request);
    $results['status'] = $response->getStatusCode();
    $results['content_length'] = strlen($response->getContent());
    $results['content_preview'] = substr($response->getContent(), 0, 200);
} catch (\Throwable $e) {
    $results['error'] = get_class($e);
    $results['message'] = $e->getMessage();
    $results['file'] = $e->getFile() . ':' . $e->getLine();
}

echo json_encode($results, JSON_PRETTY_PRINT);
