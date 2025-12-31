<?php
require __DIR__ . '/../Banco de dados/conexao.php';
require __DIR__ . '/../vendor/autoload.php';

use Services\NotificationService;

// Check if a user ID was passed as argument, otherwise default to 3
$userId = $argv[1] ?? 3;

echo "Initializing NotificationService...\n";
$service = new NotificationService($pdo);

echo "Creating test notification for user ID $userId...\n";

try {
    $success = $service->create(
        $userId,
        "Teste de notificação executado em " . date('d/m/Y H:i:s'),
        "success",
        "tela_minha_conta.php"
    );

    if ($success) {
        echo "Notification created successfully!\n";
        echo "Check the browser (logged in as user ID $userId) to see it.\n";
    } else {
        echo "Failed to create notification.\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
