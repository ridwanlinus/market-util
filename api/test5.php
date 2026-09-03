<?php
define('LARAVEL_START', microtime(true));
@mkdir('/tmp/views', 0755, true);

header('Content-Type: application/json');

$servicesFile = __DIR__.'/../bootstrap/cache/services.php';
$services = file_exists($servicesFile) ? require $servicesFile : null;

$results = [
    'services_file_exists' => file_exists($servicesFile),
    'services_file_size' => file_exists($servicesFile) ? filesize($servicesFile) : 0,
    'providers_count' => isset($services['providers']) ? count($services['providers']) : 0,
    'has_view_provider' => isset($services['providers']) && in_array('Illuminate\\View\\ViewServiceProvider', $services['providers']),
    'all_providers' => isset($services['providers']) ? array_values($services['providers']) : [],
];

echo json_encode($results, JSON_PRETTY_PRINT);
