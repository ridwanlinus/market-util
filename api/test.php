<?php
// Minimal PHP test - no dependencies
header('Content-Type: application/json');
echo json_encode([
    'status' => 'ok',
    'php' => PHP_VERSION,
    'sapi' => php_sapi_name(),
    'extensions' => count(get_loaded_extensions()),
    'time' => date('Y-m-d H:i:s'),
    'dir' => __DIR__,
    'cwd' => getcwd(),
]);
