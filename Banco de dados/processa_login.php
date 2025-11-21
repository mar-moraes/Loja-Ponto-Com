<?php
// Inicia a sessão para armazenar os dados do usuário logado
session_start();

// 1. Inclui a conexão
require 'conexao.php';

// 2. Verifica se o formulário foi enviado (método POST)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // 3. Coleta os dados
    $email = trim($_POST['email']);
    $senha_digitada = $_POST['senha'];

    try {
        // 4. Busca o usuário pelo email
        // (Nota: O ideal seria buscar a coluna 'tipo' do banco, 
        // mas seguindo a regra de verificar o e-mail no login)
        $stmt = $pdo->prepare("SELECT id, nome, senha FROM usuarios WHERE email = ?");
        $stmt->execute([$email]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        // 5. Verifica se o usuário existe E se a senha está correta
        if ($usuario && password_verify($senha_digitada, $usuario['senha'])) {
            
            // 6. Senha correta! Salva os dados na sessão
            $_SESSION['usuario_id'] = $usuario['id'];
            $_SESSION['usuario_nome'] = $usuario['nome'];

            // --- INÍCIO DA MODIFICAÇÃO ---
            // 7. Verifica se o e-mail é de um fornecedor
            if (strpos($email, "@LojaLTDA.com") !== false) {
                $_SESSION['usuario_tipo'] = 'fornecedor';
            } else {
                $_SESSION['usuario_tipo'] = 'cliente';
            }
            // --- FIM DA MODIFICAÇÃO ---


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
?>