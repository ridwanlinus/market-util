<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

header('Content-Type: text/plain');
echo "api/index.php executed! Time: " . date('Y-m-d H:i:s') . "\n";
echo "REQUEST_URI: " . ($_SERVER['REQUEST_URI'] ?? 'N/A') . "\n";
echo "PHP: " . PHP_VERSION . "\n";
