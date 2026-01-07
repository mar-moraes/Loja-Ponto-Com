<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/Banco de dados/conexao.php';

try {
    echo "Connected to DB.\n";
    $count = $pdo->query("SELECT COUNT(*) FROM produtos")->fetchColumn();
    echo "Total products: " . $count . "\n";

    echo "Fetching last 3...\n";
    $stmt = $pdo->query("SELECT id, nome, imagem_url FROM produtos ORDER BY id DESC LIMIT 3");
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($products as $p) {
        echo "[ID: {$p['id']}] {$p['nome']} \n   -> {$p['imagem_url']}\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
