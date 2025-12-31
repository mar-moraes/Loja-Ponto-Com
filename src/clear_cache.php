<?php
require __DIR__ . '/../Banco de dados/conexao.php';
require __DIR__ . '/../vendor/autoload.php';

use Services\CacheService;

$cache = new CacheService();

// Limpar chaves específicas da home
$cache->forget('home_produtos_destaque');
$cache->forget('home_produtos_carousel');

echo "Cache da home limpo com sucesso!\n";
