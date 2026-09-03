<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';

header('Content-Type: application/json');
echo json_encode([
    'view_compiled' => config('view.compiled'),
    'view_paths' => config('view.paths'),
    'tmp_views_exists' => is_dir('/tmp/views'),
    'tmp_views_writable' => is_writable('/tmp/views') || @mkdir('/tmp/views', 0755, true),
    'storage_views_writable' => is_writable(storage_path('framework/views')),
    'storage_path' => storage_path('framework/views'),
    'env_VIEW_COMPILED' => env('VIEW_COMPILED_PATH', 'NOT SET'),
]);
