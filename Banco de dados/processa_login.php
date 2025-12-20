<?php
// Inicia a sessão para armazenar os dados do usuário logado
session_start();

// 1. Inclui a conexão
// 1. Inclui a conexão
require 'conexao.php';

// Carregamento manual ou via Composer
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require __DIR__ . '/../vendor/autoload.php';
} else {
    require __DIR__ . '/../src/Services/AuthService.php';
}

use Services\AuthService;

// 2. Verifica se o formulário foi enviado (método POST)
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // 3. Coleta os dados
    $email = trim($_POST['email']);
    $senha_digitada = $_POST['senha'];

    try {
        $authService = new AuthService($pdo);
        $usuario = $authService->login($email, $senha_digitada);

        // 5. Verifica se o usuário existe E se a senha está correta
        if ($usuario) {

            // 6. Senha correta! Salva os dados na sessão
            $_SESSION['usuario_id'] = $usuario['id'];
            $_SESSION['usuario_nome'] = $usuario['nome'];

            // 7. Verifica se o e-mail é de um fornecedor
            $_SESSION['usuario_tipo'] = $authService->identifyUserType($email);


            // 8. Redireciona para a página principal (ou index.html)
            header("Location: ../src/index.php");
            exit();
        } else {
            // 9. Usuário ou senha incorretos
            header("Location: ../src/tela_login.html?erro=login_invalido");
            exit();
        }
    } catch (PDOException $e) {
        die("Erro ao fazer login: " . $e->getMessage());
    }
} else {
    // Se alguém tentar acessar o script diretamente, redireciona
    header("Location: tela_login.html");
    exit();
}
