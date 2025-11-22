<?php
session_start();
require 'conexao.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../src/tela_login.html');
    exit();
}

$id = $_GET['id'] ?? 0;
$usuario_id = $_SESSION['usuario_id'];

try {
    $stmt = $pdo->prepare("DELETE FROM PRODUTOS WHERE id = ? AND usuario_id = ?");
    $stmt->execute([$id, $usuario_id]);

    header('Location: ../src/tela_minha_conta.php?msg=produto_excluido');
} catch (PDOException $e) {
    die("Erro ao excluir: " . $e->getMessage());
}
