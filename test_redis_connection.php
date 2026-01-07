<?php
require 'vendor/autoload.php';

use Predis\Client;

header('Content-Type: text/plain');

echo "=== TESTING REDIS CONNECTION ===\n";

try {
    $client = new Client([
        'scheme' => 'tcp',
        'host'   => '127.0.0.1',
        'port'   => 6379,
    ]);

    echo "Connecting to 127.0.0.1:6379...\n";
    $client->connect();
    echo "Connected.\n";

    echo "Ping... " . $client->ping() . "\n";

    $testKey = "debug_test_key_" . time();
    echo "Setting key '$testKey'...\n";
    $client->set($testKey, "working");

    echo "Getting key: " . $client->get($testKey) . "\n";

    // Check specific cache keys used in app
    echo "\n=== CHECKING APPLICATION KEYS ===\n";
    $keysToCheck = ['home_produtos_destaque', 'home_produtos_carousel'];
    foreach ($keysToCheck as $k) {
        $exists = $client->exists($k);
        echo "Key '$k': " . ($exists ? "EXISTS" : "NOT FOUND") . "\n";
        if ($exists) {
            echo "TTL: " . $client->ttl($k) . "s\n";
        }
    }
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
