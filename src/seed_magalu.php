<?php
// src/seed_magalu.php

use Services\CacheService;

require dirname(__DIR__) . '/vendor/autoload.php';

// Adjust path to connection file if needed.
// Assuming conexao.php is in 'Banco de dados' folder which is sibling to 'src'
$conexaoPath = dirname(__DIR__) . '/Banco de dados/conexao.php';
if (file_exists($conexaoPath)) {
    require $conexaoPath;
} else {
    die("Erro: Arquivo de conexão não encontrado em $conexaoPath");
}

$tokenFile = 'magalu_token.json';
if (!file_exists($tokenFile)) {
    die("Erro: Arquivo do token não encontrado. Por favor, faça a autenticação primeiro acessando magalu_auth.php.");
}

$tokenData = json_decode(file_get_contents($tokenFile), true);
$accessToken = $tokenData['access_token'];

echo "Iniciando importação do Magalu...\n";

// Endpoint definition - Using Portfolio API based on scopes
$apiUrl = 'https://api.magalu.com/open/portfolio-skus/v1/skus?_limit=100&_offset=0';

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Bearer $accessToken",
    "Accept: application/json"
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode !== 200) {
    echo "Falha ao buscar produtos na API Magalu. HTTP Code: $httpCode\n";
    echo "Resposta: " . $response . "\n";
    exit;
}

$products = json_decode($response, true);
// Adjust based on actual API response structure (could be inside 'data' or 'results')
// Common Magalu pattern: array of objects or wrapper
$items = $products['items'] ?? $products['skus'] ?? $products ?? [];

if (empty($items)) {
    echo "Nenhum produto encontrado na conta Magalu.\n";
    echo "Verifique se você possui produtos cadastrados no seu portfólio Magalu marketplace.\n";
    exit;
}

echo "Encontrados " . count($items) . " produtos.\n";

$usuario_id = 6; // Default user ID from previous seed logic
$catId = 1; // Default Category 'Geral' or logic to find/create

$pdo->beginTransaction();

try {
    $stmt = $pdo->prepare("INSERT INTO PRODUTOS (nome, preco, desconto, descricao, estoque, categoria_id, imagem_url, status, usuario_id) VALUES (?, ?, 0, ?, ?, ?, ?, 'ativo', ?)");

    foreach ($items as $item) {
        // Mapping implementation
        $nome = $item['name'] ?? $item['title'] ?? 'Produto sem nome';
        $preco = $item['price']['sellPrice'] ?? $item['price'] ?? 0.00;
        $estoque = $item['stockQuantity'] ?? $item['stock'] ?? 10;
        $descricao = $item['description'] ?? "Descrição importada do Magalu.";
        $imagem = 'https://placehold.co/600x400?text=Sem+Imagem';

        // Try to get image
        if (!empty($item['images']) && is_array($item['images'])) {
            // Assuming mapping or first item
            $imagem = $item['images']['default'] ?? $item['images'][0] ?? $imagem;
        }

        // Enforce 4-6 lines description if possible or format it
        $descFinal = "--- CARACTERÍSTICAS ---\nMarca: Magalu Seller\n--- DESCRIÇÃO ---\n" . substr($descricao, 0, 500);

        $stmt->execute([$nome, $preco, $descFinal, $estoque, $catId, $imagem, $usuario_id]);
        echo "Importado: $nome\n";
    }

    $pdo->commit();
    echo "Importação concluída com sucesso!\n";

    // Clear Cache
    try {
        $cache = new CacheService();
        $cache->forget('home_produtos_destaque');
        $cache->forget('home_produtos_carousel');
        $cache->forget('categorias_lista');
        echo "Cache limpo.\n";
    } catch (Exception $e) {
        echo "Aviso: Cache não pôde ser limpo (" . $e->getMessage() . ")\n";
    }
} catch (Exception $e) {
    $pdo->rollBack();
    echo "Erro ao inserir no banco: " . $e->getMessage();
}
