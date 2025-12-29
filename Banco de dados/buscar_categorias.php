<?php
// Salve este arquivo como: Banco de dados/buscar_categorias.php

header('Content-Type: application/json');
require '../vendor/autoload.php'; // Autoload do Composer
require 'conexao.php'; // Assume que conexao.php está no mesmo diretório

use Services\CacheService;

$cache = new CacheService();

try {
    // Tenta buscar do cache
    $categorias = $cache->remember('categorias_lista', 600, function () use ($pdo) {
        // Busca o ID e o Nome da tabela categorias, ordenando por nome
        $stmt = $pdo->prepare("SELECT id, nome FROM categorias ORDER BY nome ASC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    });

    // Retorna um JSON com sucesso e a lista de categorias
    echo json_encode(['sucesso' => true, 'categorias' => $categorias]);
} catch (PDOException $e) {
    // Em caso de erro, envia uma resposta JSON de falha
    http_response_code(500); // Internal Server Error
    echo json_encode(['sucesso' => false, 'mensagem' => 'Erro de Banco de Dados: ' . $e->getMessage()]);
}
