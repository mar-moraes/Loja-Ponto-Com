<?php
require '../Banco de dados/conexao.php';
require '../vendor/autoload.php';

use Services\NotificationService;

$service = new NotificationService($pdo);
$userId = $argv[1] ?? 1; // Default to ID 1 if not provided

echo "Creating notification for User ID: $userId\n";
$service->create($userId, "Teste Manul para Usuário $userId", "primary", "#");
echo "Done.\n";
