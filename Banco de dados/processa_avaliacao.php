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

// 2. Pega os dados enviados pelo JavaScript (fetch)
$dados = json_decode(file_get_contents('php://input'), true);

if (!$dados) {
    echo json_encode(['sucesso' => false, 'mensagem' => 'Nenhum dado recebido.']);
    exit();
}

// 3. Valida os dados
$produto_id = $dados['produto_id'] ?? null;
$nota = $dados['nota'] ?? null;
$comentario = $dados['comentario'] ?? ''; // Comentário pode ser vazio

if (!filter_var($produto_id, FILTER_VALIDATE_INT)) {
    echo json_encode(['sucesso' => false, 'mensagem' => 'ID de produto inválido.']);
    exit();
}

if (!filter_var($nota, FILTER_VALIDATE_INT) || $nota < 1 || $nota > 5) {
    echo json_encode(['sucesso' => false, 'mensagem' => 'Nota inválida. Deve ser entre 1 e 5.']);
    exit();
}

// 4. Salva no banco de dados
// A tabela 'avaliacoes' tem uma chave única (usuario_id, produto_id).
// Usamos "ON DUPLICATE KEY UPDATE" para criar ou atualizar a avaliação.
// Isso cumpre os requisitos de "postar" e "mudar a avaliação" em uma só query.
try {
    $sql = "INSERT INTO avaliacoes (usuario_id, produto_id, nota, comentario, data_avaliacao)
            VALUES (?, ?, ?, ?, NOW())
            ON DUPLICATE KEY UPDATE
                nota = VALUES(nota),
                comentario = VALUES(comentario),
                data_avaliacao = NOW()";
                
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $usuario_id,
        $produto_id,
        $nota,
        trim($comentario) // Salva o comentário
    ]);

    echo json_encode(['sucesso' => true, 'mensagem' => 'Avaliação salva.']);

} catch (PDOException $e) {
    error_log("Erro ao salvar avaliação: " . $e->getMessage());
    echo json_encode(['sucesso' => false, 'mensagem' => 'Erro de banco de dados: ' . $e->getMessage()]);
}
?>