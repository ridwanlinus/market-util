<?php

/**
 * Vercel Serverless entry — meneruskan semua request ke Laravel.
 */

define('LARAVEL_START', microtime(true));
@mkdir('/tmp/views', 0755, true);

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';

$response = $app->handleRequest(
    \Illuminate\Http\Request::capture()
);

$response->send();
