<?php
// Minimal Laravel boot test
define('LARAVEL_START', microtime(true));
@mkdir('/tmp/views', 0755, true);

header('Content-Type: application/json');
$results = [];

// Step 1: autoloader
try {
    require __DIR__.'/../vendor/autoload.php';
    $results['step1'] = 'OK - ' . count(get_declared_classes()) . ' classes';
} catch (\Throwable $e) {
    $results['step1'] = 'FAIL - ' . $e->getMessage();
    echo json_encode($results);
    exit;
}

// Step 2: bootstrap
try {
    $app = require __DIR__.'/../bootstrap/app.php';
    $results['step2'] = 'OK - ' . get_class($app);
} catch (\Throwable $e) {
    $results['step2'] = 'FAIL - ' . get_class($e) . ': ' . $e->getMessage();
    echo json_encode($results);
    exit;
}

// Step 3: config
try {
    $results['step3'] = 'OK - view.compiled = ' . config('view.compiled');
} catch (\Throwable $e) {
    $results['step3'] = 'FAIL - ' . $e->getMessage();
    echo json_encode($results);
    exit;
}

// Step 4: handle request
try {
    $request = \Illuminate\Http\Request::capture();
    $response = $app->handleRequest($request);
    $results['step4'] = 'OK - status: ' . $response->getStatusCode() . ', length: ' . strlen($response->getContent());
} catch (\Throwable $e) {
    $results['step4'] = 'FAIL - ' . get_class($e) . ': ' . $e->getMessage();
}

echo json_encode($results, JSON_PRETTY_PRINT);
