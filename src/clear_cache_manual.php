<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Services\CacheService;

try {
    $cache = new CacheService();
    $cache->forget('home_produtos_destaque');
    $cache->forget('home_produtos_carousel');
    echo "Cache limpo com sucesso!\n";
} catch (Exception $e) {
    echo "Erro ao limpar cache: " . $e->getMessage() . "\n";
}
