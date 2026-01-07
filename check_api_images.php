<?php
require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/Banco de dados/conexao.php';

$stmt = $pdo->query("SELECT nome, imagem_url FROM produtos ORDER BY id DESC LIMIT 5");
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Checking last 5 products images:\n";
foreach ($products as $p) {
    echo "Product: {$p['nome']}\n";
    echo "Image: {$p['imagem_url']}\n";
    echo "-------------------\n";
}

$count = $pdo->query("SELECT COUNT(*) FROM produtos")->fetchColumn();
echo "Total products: $count\n";
