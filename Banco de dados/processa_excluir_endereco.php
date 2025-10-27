<?php
session_start();
require 'conexao.php'; // Conexão

// Verifica se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../tela_login.html');
    exit();
}

$usuario_id = $_SESSION['usuario_id'];
$endereco_id = $_GET['id'] ?? null; // Pega o ID da URL

if (!$endereco_id) {
    header('Location: ../tela_minha_conta.php');
    exit();
}

try {
    $stmt = $pdo->prepare(
        "DELETE FROM ENDERECOS WHERE id = ? AND usuario_id = ?" // Segurança: Só deleta se o ID e o usuario_id baterem
    );
    $stmt->execute([$endereco_id, $usuario_id]);
    
    // Redireciona de volta
    header("Location: ../tela_minha_conta.php?sucesso=del");
    exit();

} catch (PDOException $e) {
    die("Erro ao excluir endereço: " . $e->getMessage());
}
?>