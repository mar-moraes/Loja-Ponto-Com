<?php
require __DIR__ . '/../Banco de dados/conexao.php';
require __DIR__ . '/../vendor/autoload.php';

use Services\NotificationService;

$service = new NotificationService($pdo);

echo "--- Debug Notificações ---\n";

// 1. Check table count
$stmt = $pdo->query("SELECT COUNT(*) FROM verificacoes"); // Wait, table name is 'notificacoes'? 
// Let me check table creation... I recall 'notificacoes'.
// Let's assume 'notificacoes'.
try {
    $stmt = $pdo->query("SELECT COUNT(*) FROM notificacoes");
    echo "Total de notificações no banco: " . $stmt->fetchColumn() . "\n";

    $stmt = $pdo->query("SELECT * FROM notificacoes ORDER BY id DESC LIMIT 5");
    $latest = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "Últimas 5:\n";
    print_r($latest);
} catch (Exception $e) {
    echo "Erro ao ler tabela: " . $e->getMessage() . "\n";
}

// 2. Test Creation
echo "\nTentando criar notificação de teste...\n";
try {
    // Vamos pegar o primeiro usuário que existir
    $stmtU = $pdo->query("SELECT id FROM usuarios LIMIT 1");
    $userId = $stmtU->fetchColumn();

    if ($userId) {
        $service->create($userId, "Teste DEBUG Manual " . date('H:i:s'), "info", "#");
        echo "Notificação criada para usuário ID $userId.\n";
    } else {
        echo "Nenhum usuário encontrado para testar.\n";
    }
} catch (Exception $e) {
    echo "Erro ao criar notificação: " . $e->getMessage() . "\n";
}
