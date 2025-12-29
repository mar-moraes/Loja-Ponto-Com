<?php
require 'vendor/autoload.php';

use Services\CacheService;

echo "Testing CacheService...\n";

try {
    $cache = new CacheService();
    echo "CacheService instantiated.\n";

    echo "Setting key 'test_key'...\n";
    $cache->set('test_key', ['foo' => 'bar'], 60);
    echo "Key set.\n";

    echo "Getting key 'test_key'...\n";
    $val = $cache->get('test_key');

    if (isset($val['foo']) && $val['foo'] === 'bar') {
        echo "SUCCESS: Cache retrieval works! Value: " . print_r($val, true) . "\n";
    } else {
        echo "FAILURE: Value mismatch.\n";
    }
} catch (Throwable $e) {
    echo "EXCEPTION: " . $e->getMessage() . "\n";
    echo "Ensure Redis is running on localhost:6379\n";
}
