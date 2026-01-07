<?php
require '../vendor/autoload.php';

use Services\CacheService;

echo "=== CLEARING CACHE ===\n";
try {
    $cache = new CacheService();
    $cache->forget('home_produtos_destaque');
    $cache->forget('home_produtos_carousel');
    echo "Keys 'home_produtos_destaque' and 'home_produtos_carousel' cleared.\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
