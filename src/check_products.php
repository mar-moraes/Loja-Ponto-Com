<?php
require __DIR__ . '/../Banco de dados/conexao.php';

try {
    $stmt = $pdo->query("SELECT id, nome, status, usuario_id, data_cadastro FROM produtos ORDER BY id DESC LIMIT 5");
    $produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "--- Últimos 5 Produtos Cadastrados ---\n";
    foreach ($produtos as $p) {
        // data_cadastro might not exist in the table based on previous knowledge, but let's check
        // The schema dump didn't show data_cadastro for products, only for users.
        // Let's stick to fields we know or just dump relevant ones.
        echo "ID: {$p['id']} | Nome: {$p['nome']} | Status: {$p['status']} | Dono: {$p['usuario_id']}\n";
    }
} catch (PDOException $e) {
    echo "Erro: " . $e->getMessage() . "\n";
}
