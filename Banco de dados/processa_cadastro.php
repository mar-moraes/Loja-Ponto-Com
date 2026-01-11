<?php
session_start();
require 'conexao.php'; // Inclui a conexão

// Verifica se o formulário foi enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Coleta os dados do formulário
    $nome = trim($_POST['nome']);
    $email = trim($_POST['email']);
    $cpf = trim($_POST['cpf']);
    $telefone = trim($_POST['telefone']);
    $senha = $_POST['senha'];
    $confirma_senha = $_POST['confirma_senha'];

    // 1. Validar Senhas
    if ($senha !== $confirma_senha) {
        // Se as senhas não batem, volta ao cadastro com erro
        header("Location: ../src/tela_cadastro.html?erro=senhas_nao_conferem");
        exit();
    }

    // 2. Validar força da senha (mínimo 6 caracteres)
    if (strlen($senha) < 6) {
        header("Location: ../src/tela_cadastro.html?erro=senha_curta");
        exit();
    }

    // 3. Criptografar a senha (MUITO IMPORTANTE)
    $senha_hash = password_hash($senha, PASSWORD_DEFAULT);

    // --- INÍCIO DA MODIFICAÇÃO ---
    // 4. Determina o tipo de usuário com base no e-mail
    $tipo_usuario = 'cliente'; // Padrão
    if (strpos($email, "@LojaLTDA.com") !== false) {
        $tipo_usuario = 'fornecedor';
    }
    // --- FIM DA MODIFICAÇÃO ---

    try {
        // 5. Inserir no banco de dados (Query atualizada para incluir 'tipo')
        $stmt = $pdo->prepare("INSERT INTO usuarios (nome, email, cpf, telefone, senha, tipo) VALUES (?, ?, ?, ?, ?, ?)");
        // 'execute' atualizado para incluir $tipo_usuario
        $stmt->execute([$nome, $email, $cpf, $telefone, $senha_hash, $tipo_usuario]);

        // 6. Sucesso: Loga o usuário e redireciona para a index
        $_SESSION['usuario_id'] = $pdo->lastInsertId();
        $_SESSION['usuario_nome'] = $nome;
        $_SESSION['usuario_tipo'] = $tipo_usuario; // Salva o tipo na sessão

        // --- GERAÇÃO DE CUPOM DE BOAS VINDAS ---
        if ($tipo_usuario === 'cliente') {
            $novo_id = $_SESSION['usuario_id'];
            $codigo_cupom = 'BemVindo_' . $novo_id;

            // Cria um coupon de 10% válido por 7 dias
            $stmt_cupom = $pdo->prepare("INSERT INTO cupons (codigo, descricao, tipo_desconto, valor_desconto, valor_minimo, data_inicio, data_fim, limite_uso, ativo) VALUES (?, ?, ?, ?, ?, NOW(), DATE_ADD(NOW(), INTERVAL 7 DAY), 1, 1)");
            $stmt_cupom->execute([$codigo_cupom, 'Cupom de Boas Vindas', 'porcentagem', 10.00, 50.00]);
        }
        // --- FIM DA GERAÇÃO ---

        header("Location: ../src/index.php"); // Redireciona para a nova index.php
        exit();
    } catch (PDOException $e) {
        // 7. Erro (provavelmente email ou CPF duplicado)
        if ($e->errorInfo[1] == 1062) { // 1062 é o código de "Entrada duplicada"
            header("Location: ../src/tela_cadastro.html?erro=email_cpf_duplicado");
        } else {
            // Outro erro de banco
            header("Location: ../src/tela_cadastro.html?erro=db_error");
        }
        exit();
    }
} else {
    // Se acessou o script sem ser por POST, volta ao cadastro
    header("Location: ../src/tela_cadastro.html");
    exit();
}
