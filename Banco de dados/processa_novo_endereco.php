<?php
session_start();
require 'conexao.php'; // Conexão com o banco

// Verifica se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../tela_login.html');
    exit();
}

// Verifica se o formulário foi enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $usuario_id = $_SESSION['usuario_id'];
    
    // Coleta dados do formulário
    $cep = trim($_POST['cep']);
    $rua = trim($_POST['rua']);
    $numero = trim($_POST['numero']);
    $complemento = trim($_POST['complemento']) ?: null; // Salva null se vazio
    $bairro = trim($_POST['bairro']);
    $cidade = trim($_POST['cidade']);
    $estado = trim($_POST['estado']);
    // 'pais' tem valor default no BD, então não precisamos enviar

    try {
        $stmt = $pdo->prepare(
            "INSERT INTO ENDERECOS (usuario_id, cep, rua, numero, complemento, bairro, cidade, estado) 
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
        );
        $stmt->execute([$usuario_id, $cep, $rua, $numero, $complemento, $bairro, $cidade, $estado]);
        
        // Redireciona de volta para a "Minha Conta"
        header("Location: ../tela_minha_conta.php?sucesso=add");
        exit();

    } catch (PDOException $e) {
        die("Erro ao salvar endereço: " . $e->getMessage());
    }

} else {
    // Se acessou sem ser por POST, volta para a index
    header("Location: ../index.php");
    exit();
}
?>