<?php
require __DIR__ . '/../Banco de dados/conexao.php';
require __DIR__ . '/../vendor/autoload.php';

use Services\NotificationService;

$service = new NotificationService($pdo);

echo "--- Debug Profundo Notificações ---\n";

// 1. Verificar estrutura da tabela
$stmt = $pdo->query("DESCRIBE notificacoes");
$columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
echo "Colunas na tabela: " . implode(", ", $columns) . "\n";

// 2. Verificar ultimo ID inserido e timestamps
$stmt = $pdo->query("SELECT id, usuario_id, mensagem, data_criacao FROM notificacoes ORDER BY id DESC LIMIT 1");
$last = $stmt->fetch(PDO::FETCH_ASSOC);
if ($last) {
    echo "Última notificação: ID {$last['id']} para User {$last['usuario_id']} em {$last['data_criacao']}\n";
} else {
    echo "Tabela vazia.\n";
}

// 3. Teste de inserção normal
try {
    // Pegar ID 6 (Fornecedor Sandra) e ID 3 (Comprador)
    // Vamos criar notif para o ID 3 (que deve ser o usuário logado se for o dev testando)
    // Ou melhor, criar para os usuarios 1, 2, 3, etc. pra garantir.

    $users = [3, 6];
    foreach ($users as $uid) {
        $check = $pdo->query("SELECT id FROM usuarios WHERE id = $uid")->fetch();
        if ($check) {
            $service->create($uid, "Teste Notificação DEBUG " . time(), "info", "#");
            echo "Criada para user $uid.\n";
        }
    }
} catch (Exception $e) {
    echo "Erro creating via Service: " . $e->getMessage() . "\n";
}

echo "--- Fim Debug ---\n";
