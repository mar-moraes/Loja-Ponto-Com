<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require 'vendor/autoload.php';

echo "Autoload loaded.\n";

if (class_exists('Predis\Client')) {
    echo "Predis\Client exists.\n";
} else {
    echo "Predis\Client DOES NOT exist.\n";
}

try {
    $client = new Predis\Client();
    echo "Client created.\n";
} catch (Throwable $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
