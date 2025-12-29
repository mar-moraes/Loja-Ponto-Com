<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require 'vendor/autoload.php';

// Manual require to debug autoloading
require_once 'src/Services/CacheService.php';

use Services\CacheService;

echo "Testing CacheService...\n";

try {
    $cache = new CacheService();
    echo "CacheService instantiated.\n";

    echo "Setting key 'test_key'...\n";
    $cache->set('test_key', ['foo' => 'bar'], 60);
    echo "Key set.\n";

    $val = $cache->get('test_key');
    echo "Got value: " . print_r($val, true) . "\n";
} catch (Throwable $e) {
    echo "EXCEPTION: " . $e->getMessage() . "\n";
}
