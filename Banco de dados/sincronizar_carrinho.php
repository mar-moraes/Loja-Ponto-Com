<?php
session_start();
header('Content-Type: application/json');
require 'conexao.php'; //

// 1. Verificar se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['sucesso' => false, 'erro' => 'nao_logado', 'mensagem' => 'Usuário não logado.']);
    exit();
}

$usuario_id = $_SESSION['usuario_id'];
$dados = json_decode(file_get_contents('php://input'), true);
$carrinhoJS = $dados['carrinho'] ?? [];

// if (empty($carrinhoJS)) {
//     echo json_encode(['sucesso' => false, 'mensagem' => 'Carrinho vazio recebido.']);
//     exit();
// }

try {
    // Inicia uma transação para garantir consistência
    $pdo->beginTransaction();

    // 2. Encontrar ou criar o carrinho principal do usuário (Tabela CARRINHO)
    $stmt = $pdo->prepare("SELECT id FROM CARRINHO WHERE usuario_id = ?");
    $stmt->execute([$usuario_id]);
    $carrinhoBD = $stmt->fetch();

    $carrinho_id = null;
    if ($carrinhoBD) {
        $carrinho_id = $carrinhoBD['id'];
    } else {
        $stmt_cria = $pdo->prepare("INSERT INTO CARRINHO (usuario_id) VALUES (?)");
        $stmt_cria->execute([$usuario_id]);
        $carrinho_id = $pdo->lastInsertId();
    }

    // 3. Limpar itens antigos (Tabela CARRINHO_ITENS)
    $stmt_limpa = $pdo->prepare("DELETE FROM CARRINHO_ITENS WHERE carrinho_id = ?");
    $stmt_limpa->execute([$carrinho_id]);

    // 4. Inserir os novos itens
    $stmt_insere = $pdo->prepare("INSERT INTO CARRINHO_ITENS (carrinho_id, produto_id, quantidade) VALUES (?, ?, ?)");
    
    foreach ($carrinhoJS as $item) {
        // ATENÇÃO: Você precisa ter o ID do produto no seu JS.
        // Seu JS atual só tem o Título. Você precisará adicionar 'produto.id'
        // Vou assumir que 'item['id']' existe.
        
        $produto_id = $item['id']; // Você precisa adicionar isso ao seu JS
        $quantidade = $item['quantidade'];
        
        // Em um app real, você também buscaria o preço do PRODUTO no BD aqui
        // para evitar fraude, mas vamos manter simples por agora.
        
        $stmt_insere->execute([$carrinho_id, $produto_id, $quantidade]);
    }

    // Confirma a transação
    $pdo->commit();
    echo json_encode(['sucesso' => true]);

} catch (PDOException $e) {
    $pdo->rollBack();
    echo json_encode(['sucesso' => false, 'mensagem' => 'Erro de banco de dados: ' . $e->getMessage()]);
}
?>