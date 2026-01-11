<?php
session_start();
require 'conexao.php';

if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] !== 'fornecedor') {
    header("Location: ../src/index.php");
    exit();
}

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $usuario_id = $_SESSION['usuario_id'];

    try {
        // Garante que só deleta se pertencer ao usuário logado
        $stmt = $pdo->prepare("DELETE FROM cupons WHERE id = ? AND usuario_id = ?");
        $stmt->execute([$id, $usuario_id]);

        header("Location: ../src/tela_minha_conta.php?msg=cupom_excluido");
    } catch (PDOException $e) {
        error_log("Erro ao excluir cupom: " . $e->getMessage());
        header("Location: ../src/tela_minha_conta.php?erro=erro_excluir");
    }
}
