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

require __DIR__.'/../vendor/autoload.php';

$app = require_once __DIR__.'/../bootstrap/app.php';

$response = $app->handleRequest(
    \Illuminate\Http\Request::capture()
);

$response->send();
