<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Adjust path as needed. Script is in src/
require '../Banco de dados/conexao.php';

header('Content-Type: text/plain');

echo "=== DATABASE DIAGNOSTIC ===\n";

try {
    // 1. Check DB Name
    $dbName = $pdo->query("SELECT DATABASE()")->fetchColumn();
    echo "Connected Database: " . $dbName . "\n";

    // 2. Check Products
    $prodCount = $pdo->query("SELECT COUNT(*) FROM produtos")->fetchColumn();
    echo "Total Products: $prodCount\n";

    if ($prodCount > 0) {
        $last = $pdo->query("SELECT id, nome, status FROM produtos ORDER BY id DESC LIMIT 1")->fetch(PDO::FETCH_ASSOC);
        echo "Last Product: ID {$last['id']} - {$last['nome']} (Status: {$last['status']})\n";
    }

    // 3. Check Notifications
    $notifCount = $pdo->query("SELECT COUNT(*) FROM notificacoes")->fetchColumn();
    echo "Total Notifications: $notifCount\n";

    if ($notifCount > 0) {
        $lastN = $pdo->query("SELECT id, usuario_id, message_preview FROM (SELECT id, usuario_id, LEFT(mensagem, 20) as message_preview, data_criacao FROM notificacoes) as sub ORDER BY data_criacao DESC LIMIT 1")->fetch(PDO::FETCH_ASSOC);
        echo "Last Notification: ID {$lastN['id']} for User {$lastN['usuario_id']} - msg: {$lastN['message_preview']}...\n";
    }
} catch (PDOException $e) {
    echo "DB Error: " . $e->getMessage() . "\n";
}
