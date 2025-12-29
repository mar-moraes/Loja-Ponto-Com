<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Services\CloudinaryService;

echo "--- Verifying CloudinaryService Class ---\n";

try {
    $service = new CloudinaryService();
    echo "Service instantiated successfully.\n";
    echo "Configuration loaded.\n";
} catch (Exception $e) {
    echo "FAILED: " . $e->getMessage() . "\n";
    exit(1);
}

echo "--- Verification Passed ---\n";
