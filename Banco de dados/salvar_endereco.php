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

// Coleta os dados (assumindo que você alterou a tabela)
$cep = $dados['cep'] ?? null;
$rua = $dados['rua'] ?? null;
$numero = $dados['numero'] ?? 'S/N'; // 'S/N' se "sem número" estiver marcado
$complemento = $dados['complemento'] ?? null;
$info_adicional = $dados['info_adicional'] ?? null; // Nota: Sua tabela ENDERECOS não tem este campo
$nome_destinatario = $dados['nome'] ?? null;
$telefone_destinatario = $dados['telefone'] ?? null;

// Busca Bairro, Cidade e Estado pelo CEP (ViaCEP)
// (Em um app real, o JS já teria feito isso, mas podemos fazer aqui como garantia)
$cep_limpo = preg_replace('/\D/', '', $cep);
$bairro = 'N/A';
$cidade = 'N/A';
$estado = 'N/A';

if (strlen($cep_limpo) == 8) {
    $viacep_url = "https://viacep.com.br/ws/{$cep_limpo}/json/";
    $viacep_data = @json_decode(@file_get_contents($viacep_url), true);
    if ($viacep_data && !isset($viacep_data['erro'])) {
        $bairro = $viacep_data['bairro'] ?? $bairro;
        $cidade = $viacep_data['localidade'] ?? $cidade;
        $estado = $viacep_data['uf'] ?? $estado;
        // Se a rua estiver vazia no formulário, usa a do ViaCEP
        if(empty($rua) && !empty($viacep_data['logradouro'])) {
            $rua = $viacep_data['logradouro'];
        }
    }
}

try {
    // Por simplicidade, este script sempre INSERE um novo endereço.
    // Uma versão avançada verificaria se um 'endereco_id' foi passado para ATUALIZAR (UPDATE).
    
    $sql = "INSERT INTO ENDERECOS (usuario_id, cep, rua, numero, complemento, bairro, cidade, estado, destinatario_nome, destinatario_telefone)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $usuario_id,
        $cep,
        $rua,
        $numero,
        $complemento,
        $bairro,
        $cidade,
        $estado,
        $nome_destinatario,
        $telefone_destinatario
    ]);
    
    $novo_endereco_id = $pdo->lastInsertId();

    // Retorna os dados completos para o JS atualizar a tela
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
            'estado' => $estado,
            'nome' => $nome_destinatario
        ]
    ]);

} catch (PDOException $e) {
    echo json_encode(['sucesso' => false, 'mensagem' => 'Erro ao salvar no banco: ' . $e->getMessage()]);
}
?>