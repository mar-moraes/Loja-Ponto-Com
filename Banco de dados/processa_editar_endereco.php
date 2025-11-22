<?php
session_start();
require 'conexao.php'; // Conexão

// Verifica se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../src/tela_login.html');
    exit();
}

// Verifica se o formulário foi enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $usuario_id = $_SESSION['usuario_id'];

    // Coleta dados do formulário
    $endereco_id = $_POST['endereco_id']; // ID do endereço a ser atualizado
    $cep = trim($_POST['cep']);
    $rua = trim($_POST['rua']);
    $numero = trim($_POST['numero']);
    $complemento = trim($_POST['complemento']) ?: null;
    $bairro = trim($_POST['bairro']);
    $cidade = trim($_POST['cidade']);
    $estado = trim($_POST['estado']);

    try {
        $stmt = $pdo->prepare(
            "UPDATE ENDERECOS SET 
             cep = ?, rua = ?, numero = ?, complemento = ?, bairro = ?, cidade = ?, estado = ? 
             WHERE id = ? AND usuario_id = ?" // Segurança: Só atualiza se o ID e o usuario_id baterem
        );
        $stmt->execute([$cep, $rua, $numero, $complemento, $bairro, $cidade, $estado, $endereco_id, $usuario_id]);

        // Redireciona de volta para a "Minha Conta"
        header("Location: ../src/tela_minha_conta.php?sucesso=edit");
        exit();
    } catch (PDOException $e) {
        die("Erro ao atualizar endereço: " . $e->getMessage());
    }
} else {
    header("Location: ../src/index.php");
    exit();
}
