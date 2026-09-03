<?php
header('Content-Type: text/plain');
echo "PHP works! Version: " . PHP_VERSION . "\n";
require __DIR__.'/../vendor/autoload.php';
echo "Autoloader loaded OK\n";
$app = require __DIR__.'/../bootstrap/app.php';
echo "App created: " . get_class($app) . "\n";
