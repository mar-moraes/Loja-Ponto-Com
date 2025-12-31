<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Services\CacheService;

try {
    echo "Testing CacheService from " . __DIR__ . "\n";
    $cache = new CacheService();

    $key = 'test_cache_key_' . time();
    $value = 'test_value';

    echo "Setting key '$key'...\n";
    $cache->set($key, $value, 60);

    $retrieved = $cache->get($key);
    echo "Retrieved value: " . ($retrieved === $value ? 'MATCH' : 'MISMATCH') . "\n";

    echo "Forgetting key '$key'...\n";
    $cache->forget($key);

    $retrievedAfter = $cache->get($key);
    echo "Retrieved after forget: " . ($retrievedAfter === null ? 'NULL (Correct)' : 'STILL EXISTS (Error)') . "\n";

    echo "Testing home keys clearing...\n";
    $cache->set('home_produtos_destaque', 'dummy', 60);
    $cache->forget('home_produtos_destaque');
    if ($cache->get('home_produtos_destaque') === null) {
        echo "home_produtos_destaque cleared successfully.\n";
    } else {
        echo "Failed to clear home_produtos_destaque.\n";
    }
} catch (Exception $e) {
    echo "Exception: " . $e->getMessage() . "\n";
}
