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
        header("Location: ../tela_cadastro.html?erro=senhas_nao_conferem");
        exit();
    }

    // 2. Validar força da senha (mínimo 6 caracteres)
    if (strlen($senha) < 6) {
        header("Location: ../tela_cadastro.html?erro=senha_curta");
        exit();
    }

    // 3. Criptografar a senha (MUITO IMPORTANTE)
    $senha_hash = password_hash($senha, PASSWORD_DEFAULT);

    try {
        // 4. Inserir no banco de dados
        $stmt = $pdo->prepare("INSERT INTO USUARIOS (nome, email, cpf, telefone, senha) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$nome, $email, $cpf, $telefone, $senha_hash]);

        // 5. Sucesso: Loga o usuário e redireciona para a index
        $_SESSION['usuario_id'] = $pdo->lastInsertId();
        $_SESSION['usuario_nome'] = $nome;
        header("Location: .../index.php"); // Redireciona para a nova index.php
        exit();

    } catch (PDOException $e) {
        // 6. Erro (provavelmente email ou CPF duplicado)
        if ($e->errorInfo[1] == 1062) { // 1062 é o código de "Entrada duplicada"
            header("Location: ../tela_cadastro.html?erro=email_cpf_duplicado");
        } else {
            // Outro erro de banco
            header("Location: ../tela_cadastro.html?erro=db_error");
        }
        exit();
    }
} else {
    // Se acessou o script sem ser por POST, volta ao cadastro
    header("Location: ../tela_cadastro.html");
    exit();
}
?>