<?php
session_start();
header('Content-Type: application/json');
require 'conexao.php'; // Inclui a conexão

$recomendacoes = [];
$usuario_id = $_SESSION['usuario_id'] ?? null;

// SQL de fallback: Se o usuário não tiver histórico ou não estiver logado,
// mostra produtos aleatórios.
$sql_fallback = "SELECT id, nome, preco, desconto, imagem_url 
                 FROM PRODUTOS 
                 WHERE status = 'ativo' 
                 ORDER BY RAND() 
                 LIMIT 10";

try {
    // Só tenta buscar por categoria se o usuário estiver logado
    if ($usuario_id) {
        
        // 1. Encontrar o ID do último pedido do usuário
        $stmt_last_order = $pdo->prepare(
            "SELECT id FROM PEDIDOS WHERE usuario_id = ? ORDER BY data_pedido DESC LIMIT 1"
        );
        $stmt_last_order->execute([$usuario_id]);
        $ultimo_pedido = $stmt_last_order->fetch();

        // Se o usuário tem um pedido...
        if ($ultimo_pedido) {
            $pedido_id = $ultimo_pedido['id'];

            // 2. Encontrar as categorias (distintas) dos produtos desse pedido
            $sql_categorias = "SELECT DISTINCT p.categoria_id 
                               FROM PRODUTOS p
                               JOIN PEDIDO_ITENS pi ON p.id = pi.produto_id
                               WHERE pi.pedido_id = ?";
            
            $stmt_categorias = $pdo->prepare($sql_categorias);
            $stmt_categorias->execute([$pedido_id]);
            $categorias_ids = $stmt_categorias->fetchAll(PDO::FETCH_COLUMN); // Pega só os IDs

            // Se encontrou categorias...
            if (!empty($categorias_ids)) {
                
                // 3. Buscar produtos dessas categorias (incluindo os já comprados)
                
                // Cria os placeholders (?) dinamicamente (ex: ?,?,?)
                $placeholders = implode(',', array_fill(0, count($categorias_ids), '?'));
                
                $sql_recomendacoes = "SELECT id, nome, preco, desconto, imagem_url 
                                    FROM PRODUTOS 
                                    WHERE categoria_id IN ($placeholders) 
                                    AND status = 'ativo'
                                    ORDER BY RAND() 
                                    LIMIT 10";
                                    
                $stmt_recomendacoes = $pdo->prepare($sql_recomendacoes);
                $stmt_recomendacoes->execute($categorias_ids);
                $recomendacoes = $stmt_recomendacoes->fetchAll(PDO::FETCH_ASSOC);
            }
        }
    }

    // 4. Fallback: Se $recomendacoes ainda estiver vazio (sem login, sem pedido, ou erro)
    if (empty($recomendacoes)) {
        $stmt_fallback = $pdo->prepare($sql_fallback);
        $stmt_fallback->execute();
        $recomendacoes = $stmt_fallback->fetchAll(PDO::FETCH_ASSOC);
    }

    echo json_encode(['sucesso' => true, 'produtos' => $recomendacoes]);

} catch (PDOException $e) {
    // Em caso de erro, tenta o fallback
    try {
        $stmt_fallback = $pdo->prepare($sql_fallback);
        $stmt_fallback->execute();
        $recomendacoes = $stmt_fallback->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['sucesso' => true, 'produtos' => $recomendacoes]);
    } catch (PDOException $e2) {
        http_response_code(500);
        echo json_encode(['sucesso' => false, 'mensagem' => 'Erro de Banco de Dados: ' . $e2->getMessage()]);
    }
}
?>