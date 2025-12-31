<?php
require __DIR__ . '/../Banco de dados/conexao.php';

try {
    echo "--- Verificando Tabela notificacoes ---\n";
    $stmt = $pdo->query("SELECT * FROM notificacoes ORDER BY id DESC LIMIT 5");
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($items)) {
        echo "Nenhuma notificação encontrada no banco.\n";
    } else {
        foreach ($items as $n) {
            echo "ID: {$n['id']} | User: {$n['usuario_id']} | Msg: {$n['mensagem']} | Link: {$n['link']}\n";
        }
    }
} catch (Exception $e) {
    echo "Erro Fatal: " . $e->getMessage();
}
