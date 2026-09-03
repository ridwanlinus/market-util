<?php
header('Content-Type: application/json');
echo json_encode([
    'APP_DEBUG' => getenv('APP_DEBUG') ?: 'NOT SET',
    'APP_KEY' => getenv('APP_KEY') ? 'SET (length: ' . strlen(getenv('APP_KEY')) . ')' : 'NOT SET',
    'DB_CONNECTION' => getenv('DB_CONNECTION') ?: 'NOT SET',
    'VIEW_COMPILED_PATH' => getenv('VIEW_COMPILED_PATH') ?: 'NOT SET',
    'LOG_CHANNEL' => getenv('LOG_CHANNEL') ?: 'NOT SET',
    'all_env_count' => count($_ENV),
    'server_vars' => array_key_exists('REQUEST_METHOD', $_SERVER) ? 'yes' : 'no',
]);
