<?php
// Salve este arquivo como: Banco de dados/buscar_categorias.php

header('Content-Type: application/json');
require 'conexao.php'; // Assume que conexao.php está no mesmo diretório

try {
    // Busca o ID e o Nome da tabela categorias, ordenando por nome
    $stmt = $pdo->prepare("SELECT id, nome FROM categorias ORDER BY nome ASC");
    $stmt->execute();
    $categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Retorna um JSON com sucesso e a lista de categorias
    echo json_encode(['sucesso' => true, 'categorias' => $categorias]);

} catch (PDOException $e) {
    // Em caso de erro, envia uma resposta JSON de falha
    http_response_code(500); // Internal Server Error
    echo json_encode(['sucesso' => false, 'mensagem' => 'Erro de Banco de Dados: ' . $e->getMessage()]);
}
?>