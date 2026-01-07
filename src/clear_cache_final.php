<?php
// Define o caminho base do projeto
$baseDir = dirname(__DIR__);
require $baseDir . '/vendor/autoload.php';

use Services\CacheService;

echo "=== LIMPEZA DE CACHE REDIS ===\n";
echo "Conectando ao Redis...\n";

try {
    // Tenta usar o serviço de cache do projeto
    $cache = new CacheService();

    // Lista de chaves conhecidas para remover
    $keys = [
        'home_produtos_destaque',
        'home_produtos_carousel'
    ];

    foreach ($keys as $key) {
        $cache->forget($key);
        echo "Chave removida: $key\n";
    }

    // Opcional: Se quiser limpar TUDO (use com cuidado)
    // $client = new Predis\Client([...]);
    // $client->flushall();

    echo "Limpeza concluída com sucesso!\n";
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage() . "\n";
    echo "Certifique-se de que o Redis está rodando.\n";
}
