<?php
session_start();
require '../Banco de dados/conexao.php';

// Configura o cabeçalho para retornar JSON
header('Content-Type: application/json');

// 1. Verifica se o usuário está logado e é fornecedor
if (!isset($_SESSION['usuario_id']) || !isset($_SESSION['usuario_tipo']) || $_SESSION['usuario_tipo'] !== 'fornecedor') {
    http_response_code(403);
    echo json_encode(['error' => 'Acesso negado. Apenas fornecedores podem acessar este recurso.']);
    exit();
}

$usuario_id = $_SESSION['usuario_id'];
$range = isset($_GET['range']) ? $_GET['range'] : '7days';

try {
    // Consulta base
    $sql = "
        SELECT 
            DATE(p.data_pedido) as date, 
            SUM(pi.quantidade * pi.preco_unitario) as total 
        FROM PEDIDOS p
        JOIN PEDIDO_ITENS pi ON p.id = pi.pedido_id
        JOIN PRODUTOS prod ON pi.produto_id = prod.id
        WHERE prod.usuario_id = ?
    ";

    // Filtros de data
    if ($range === '7days') {
        $sql .= " AND p.data_pedido >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
    } elseif ($range === '30days') {
        $sql .= " AND p.data_pedido >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
    }
    // Para 'all', não adicionamos filtro de data, pegando todo o histórico

    // Agrupamento e Ordenação
    $sql .= "
        GROUP BY DATE(p.data_pedido)
        ORDER BY date ASC
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$usuario_id]);
    $dados = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Formata os dados para garantir números corretos
    foreach ($dados as &$item) {
        $item['total'] = (float)$item['total'];
    }

    echo json_encode($dados);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Erro no banco de dados: ' . $e->getMessage()]);
}
