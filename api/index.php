<?php

/**
 * Vercel Serverless entry — meneruskan semua request ke Laravel.
 * Builder: vercel-php (lihat vercel.json & docs/vercel-deploy.md).
 *
 * Catatan penting: environment serverless bersifat ephemeral.
 * WAJIB pakai database eksternal (MySQL/Postgres) dan storage objek (S3/R2)
 * agar data & gambar tidak hilang antar request.
 */

define('LARAVEL_START', microtime(true));

// Ensure writable directories exist on Vercel serverless
@mkdir('/tmp/views', 0755, true);

// Temporary error handler for Vercel debugging
set_error_handler(function($severity, $message, $file, $line) {
    if (!(error_reporting() & $severity)) return;
    header('Content-Type: text/plain');
    echo "ERROR [$severity]: $message in $file:$line\n";
    exit(1);
});
set_exception_handler(function($e) {
    header('Content-Type: text/plain');
    echo "EXCEPTION: " . get_class($e) . ": " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
});

require __DIR__.'/../vendor/autoload.php';

$app = require_once __DIR__.'/../bootstrap/app.php';

$response = $app->handleRequest(
    \Illuminate\Http\Request::capture()
);

$response->send();
