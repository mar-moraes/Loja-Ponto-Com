<?php
require '../Banco de dados/conexao.php';

header('Content-Type: text/plain');

echo "=== DATABASE CONNECTION INFO ===\n";
echo "Host: " . $dsn . "\n"; // DSN usually contains host/dbname
try {
    $db = $pdo->query("SELECT DATABASE()")->fetchColumn();
    echo "Actual Connected Database: [" . $db . "]\n\n";
} catch (Exception $e) {
    echo "Error getting DB name: " . $e->getMessage() . "\n";
}

echo "=== LAST 5 PRODUCTS (Application View) ===\n";
$stmt = $pdo->query("SELECT id, nome, status, usuario_id, imagem_url FROM produtos ORDER BY id DESC LIMIT 5");
$prods = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($prods as $p) {
    echo "[ID: {$p['id']}] Name: {$p['nome']} | Status: {$p['status']} | User: {$p['usuario_id']}\n";
    echo "       Img: {$p['imagem_url']}\n";
}

echo "\n=== LAST 5 NOTIFICATIONS (Application View) ===\n";
$stmt = $pdo->query("SELECT id, usuario_id, mensagem, lida, data_criacao FROM notificacoes ORDER BY id DESC LIMIT 5");
$notifs = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($notifs as $n) {
    echo "[ID: {$n['id']}] To User: {$n['usuario_id']} | Read: {$n['lida']} | Date: {$n['data_criacao']}\n";
    echo "       Msg: {$n['mensagem']}\n";
}

echo "\n=== LAST 5 MESSAGES (Application View) ===\n";
$stmt = $pdo->query("SELECT id, conversa_id, remetente_id, conteudo, data_envio FROM mensagens ORDER BY id DESC LIMIT 5");
$msgs = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($msgs as $m) {
    echo "[ID: {$m['id']}] Chat: {$m['conversa_id']} | From: {$m['remetente_id']} | Date: {$m['data_envio']}\n";
    echo "       Content: {$m['conteudo']}\n";
}
