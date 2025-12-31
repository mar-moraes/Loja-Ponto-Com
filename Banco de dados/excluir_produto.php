<?php
session_start();
require 'conexao.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../src/tela_login.html');
    exit();
}

$id = $_GET['id'] ?? 0;
$usuario_id = $_SESSION['usuario_id'];

// Autoload para usar o CacheService
require_once __DIR__ . '/../vendor/autoload.php';

use Services\CacheService;

try {
    $stmt = $pdo->prepare("DELETE FROM PRODUTOS WHERE id = ? AND usuario_id = ?");
    $stmt->execute([$id, $usuario_id]);

    if ($stmt->rowCount() > 0) {
        // Se deletou algo, limpa o cache
        try {
            $cache = new CacheService();
            $cache->forget('home_produtos_destaque');
            $cache->forget('home_produtos_carousel');
        } catch (Exception $e) {
            // Se der erro no cache, apenas loga e segue a vida
            error_log("Erro ao limpar cache na exclusão: " . $e->getMessage());
        }
    }

    header('Location: ../src/tela_minha_conta.php?msg=produto_excluido');
} catch (PDOException $e) {
    die("Erro ao excluir: " . $e->getMessage());
}
