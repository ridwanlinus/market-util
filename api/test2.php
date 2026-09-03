<?php
header('Content-Type: application/json');
$results = [];

// Step 1: autoloader
$results['step1_autoload'] = 'checking...';
$autoloadFile = __DIR__.'/../vendor/autoload.php';
if (file_exists($autoloadFile)) {
    try {
        require $autoloadFile;
        $results['step1_autoload'] = 'OK - ' . count(get_declared_classes()) . ' classes loaded';
    } catch (\Throwable $e) {
        $results['step1_autoload'] = 'FAIL - ' . $e->getMessage();
    }
} else {
    $results['step1_autoload'] = 'FAIL - vendor/autoload.php not found at ' . $autoloadFile;
}

// Step 2: bootstrap app
$results['step2_bootstrap'] = 'checking...';
$bootstrapFile = __DIR__.'/../bootstrap/app.php';
if (file_exists($bootstrapFile)) {
    try {
        $app = require $bootstrapFile;
        $results['step2_bootstrap'] = 'OK - app class: ' . get_class($app);
    } catch (\Throwable $e) {
        $results['step2_bootstrap'] = 'FAIL - ' . get_class($e) . ': ' . $e->getMessage();
    }
} else {
    $results['step2_bootstrap'] = 'FAIL - bootstrap/app.php not found';
}

// Step 3: config
$results['step3_config'] = 'checking...';
try {
    $configPath = __DIR__.'/../config';
    $results['step3_config'] = 'OK - ' . count(scandir($configPath)) . ' config files';
} catch (\Throwable $e) {
    $results['step3_config'] = 'FAIL - ' . $e->getMessage();
}

// Step 4: handle request
$results['step4_request'] = 'checking...';
try {
    $request = \Illuminate\Http\Request::capture();
    $response = $app->handleRequest($request);
    $results['step4_request'] = 'OK - status: ' . $response->getStatusCode() . ', content-length: ' . strlen($response->getContent());
} catch (\Throwable $e) {
    $results['step4_request'] = 'FAIL - ' . get_class($e) . ': ' . $e->getMessage();
}

echo json_encode($results, JSON_PRETTY_PRINT);
