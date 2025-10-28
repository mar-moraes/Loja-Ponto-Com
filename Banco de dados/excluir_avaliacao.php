<?php
session_start();
header('Content-Type: application/json');
require 'conexao.php'; // Inclui a conexão

// 1. Verifica se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['sucesso' => false, 'mensagem' => 'Usuário não logado.']);
    exit();
}

$usuario_id = $_SESSION['usuario_id'];

// 2. Pega o ID da avaliação (enviado por POST)
$avaliacao_id = $_POST['id'] ?? null;

if (!filter_var($avaliacao_id, FILTER_VALIDATE_INT)) {
    echo json_encode(['sucesso' => false, 'mensagem' => 'ID de avaliação inválido.']);
    exit();
}

// 3. Deleta do banco
try {
    // A query SÓ deleta SE o 'id' da avaliação pertencer ao 'usuario_id' logado.
    // Isso impede que um usuário apague a avaliação de outro.
    $sql = "DELETE FROM avaliacoes WHERE id = ? AND usuario_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$avaliacao_id, $usuario_id]);
    
    $linhas_afetadas = $stmt->rowCount();

    if ($linhas_afetadas > 0) {
        echo json_encode(['sucesso' => true, 'mensagem' => 'Avaliação excluída.']);
    } else {
        echo json_encode(['sucesso' => false, 'mensagem' => 'Avaliação não encontrada ou não pertence a você.']);
    }

} catch (PDOException $e) {
    error_log("Erro ao excluir avaliação: " . $e->getMessage());
    echo json_encode(['sucesso' => false, 'mensagem' => 'Erro de banco de dados.']);
}
?>