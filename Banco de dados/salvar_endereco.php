<?php
session_start();
header('Content-Type: application/json'); // Responde em JSON

// Verifica se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['sucesso' => false, 'mensagem' => 'Usuário não logado.']);
    exit();
}

// Inclui a conexão
require 'conexao.php';
$usuario_id = $_SESSION['usuario_id'];

// Pega os dados enviados via JSON (pelo fetch)
$dados = json_decode(file_get_contents('php://input'), true);

if (!$dados) {
    echo json_encode(['sucesso' => false, 'mensagem' => 'Nenhum dado recebido.']);
    exit();
}

// --- INÍCIO DA MODIFICAÇÃO ---

// Coleta os dados que o tela_entrega.php REALMENTE envia
$cep = $dados['cep'] ?? null;
$rua = $dados['rua'] ?? null;
$numero = $dados['numero'] ?? 'S/N';
$complemento = $dados['complemento'] ?? null; // Já vem combinado (Complemento + Info Adicional)
$bairro = $dados['bairro'] ?? null; // Já vem do ViaCEP/JS
$cidade = $dados['cidade'] ?? null; // Já vem do ViaCEP/JS
$estado = $dados['estado'] ?? null; // Já vem do ViaCEP/JS

// REMOVIDO: Campos que não existem mais
// $info_adicional = $dados['info_adicional'] ?? null; 
// $nome_destinatario = $dados['nome'] ?? null;
// $telefone_destinatario = $dados['telefone'] ?? null;

// REMOVIDO: Bloco de busca do ViaCEP.
// Isso não é mais necessário, pois o JavaScript já envia
// 'bairro', 'cidade' e 'estado'.

try {
    // CORREÇÃO: SQL ajustado para bater com a tabela ENDERECOS
    $sql = "INSERT INTO ENDERECOS (usuario_id, cep, rua, numero, complemento, bairro, cidade, estado)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $pdo->prepare($sql);
    
    // CORREÇÃO: Lista de execução ajustada
    $stmt->execute([
        $usuario_id,
        $cep,
        $rua,
        $numero,
        $complemento,
        $bairro,
        $cidade,
        $estado
        // REMOVIDO: $nome_destinatario, $telefone_destinatario
    ]);
    
    $novo_endereco_id = $pdo->lastInsertId();

    // Retorna os dados completos para o JS atualizar a tela
    // CORREÇÃO: Retorna os dados corretos (sem 'nome')
    echo json_encode([
        'sucesso' => true, 
        'mensagem' => 'Endereço salvo!',
        'endereco' => [
            'id' => $novo_endereco_id,
            'rua' => $rua,
            'numero' => $numero,
            'bairro' => $bairro,
            'cep' => $cep,
            'cidade' => $cidade,
            'estado' => $estado
            // REMOVIDO: 'nome' => $nome_destinatario
        ]
    ]);

} catch (PDOException $e) {
    // O erro que você está vendo é capturado aqui
    echo json_encode(['sucesso' => false, 'mensagem' => 'Erro ao salvar no banco: ' . $e->getMessage()]);
}

// --- FIM DA MODIFICAÇÃO ---
?>