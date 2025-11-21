<?php
session_start();
header('Content-Type: application/json');
require 'conexao.php'; // Inclui a conexão

// 1. Verificar se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['sucesso' => false, 'mensagem' => 'Usuário não logado.']);
    exit();
}
$usuario_id = $_SESSION['usuario_id'];

// 2. Coletar dados enviados pelo JavaScript
$dados = json_decode(file_get_contents('php://input'), true);
$cart = $dados['cart'] ?? [];
$endereco_id = $dados['endereco_id'] ?? null; // $endereco_id será null se for 'agência'
$valor_total = (float)($dados['valor_total'] ?? 0);

// 3. Validar dados (MODIFICADO: verificação de $endereco_id removida)
if (empty($cart)) {
    echo json_encode(['sucesso' => false, 'mensagem' => 'O carrinho está vazio.']);
    exit();
}
// if (empty($endereco_id)) { // <-- REMOVIDO
//     echo json_encode(['sucesso' => false, 'mensagem' => 'Endereço de entrega inválido.']);
//     exit();
// }
if ($valor_total <= 0) {
    echo json_encode(['sucesso' => false, 'mensagem' => 'Valor total inválido.']);
    exit();
}

try {
    // 4. Iniciar Transação
    $pdo->beginTransaction();

    // 5. Criar o registro na tabela PEDIDOS (MODIFICADO para usar bind)
    $stmt_pedido = $pdo->prepare(
        "INSERT INTO PEDIDOS (usuario_id, endereco_id, valor_total, status) 
         VALUES (?, ?, ?, 'processando')"
    );
    
    // Bind do usuario_id (int)
    $stmt_pedido->bindParam(1, $usuario_id, PDO::PARAM_INT);
    
    // Bind do endereco_id (pode ser int ou null)
    if (empty($endereco_id)) {
        $stmt_pedido->bindValue(2, null, PDO::PARAM_NULL);
    } else {
        $stmt_pedido->bindParam(2, $endereco_id, PDO::PARAM_INT);
    }
    
    // Bind do valor_total (string/float)
    $stmt_pedido->bindParam(3, $valor_total);

    // Executa o statement preparado
    $stmt_pedido->execute();
    $pedido_id = $pdo->lastInsertId();


    // 6. Preparar queries para itens e estoque (sem alteração)
    $stmt_item = $pdo->prepare(
        "INSERT INTO PEDIDO_ITENS (pedido_id, produto_id, quantidade, preco_unitario) 
         VALUES (?, ?, ?, ?)"
    );
    
    $stmt_stock = $pdo->prepare(
        "UPDATE PRODUTOS SET estoque = estoque - ? WHERE id = ? AND estoque >= ?"
    );

    // 7. Loop pelos itens do carrinho (sem alteração)
    foreach ($cart as $item) {
        $produto_id = $item['id'];
        $quantidade = $item['quantidade'];
        $preco = $item['price'];
        
        $stmt_item->execute([$pedido_id, $produto_id, $quantidade, $preco]);
        $stmt_stock->execute([$quantidade, $produto_id, $quantidade]);
        
        if ($stmt_stock->rowCount() == 0) {
             // ==================
             // --- CORREÇÃO ---
             // ==================
             throw new PDOException("Estoque insuficiente para o produto: " . $item['title']);
        }
    }

    // 8. Limpar o CARRINHO_ITENS do usuário no banco (sem alteração)
    $stmt_get_cart = $pdo->prepare("SELECT id FROM CARRINHO WHERE usuario_id = ?");
    $stmt_get_cart->execute([$usuario_id]);
    $carrinho_row = $stmt_get_cart->fetch();
    
    if ($carrinho_row) {
        $carrinho_id = $carrinho_row['id'];
        $stmt_clear_cart = $pdo->prepare("DELETE FROM CARRINHO_ITENS WHERE carrinho_id = ?");
        $stmt_clear_cart->execute([$carrinho_id]);
    }

    // 9. Confirmar a Transação
    $pdo->commit();
    echo json_encode(['sucesso' => true, 'pedido_id' => $pedido_id, 'mensagem' => 'Pedido processado com sucesso.']);

} catch (PDOException $e) {
    // 10. Reverter em caso de erro
    $pdo->rollBack();
    echo json_encode(['sucesso' => false, 'mensagem' => 'Erro de Banco de Dados: ' . $e->getMessage()]);
}
?>