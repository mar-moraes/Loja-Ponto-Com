<?php
require __DIR__ . '/../Banco de dados/conexao.php';

try {
    echo "--- Verificando Tabela notificacoes ---\n";
    $stmt = $pdo->query("SELECT * FROM notificacoes ORDER BY id DESC LIMIT 5");

    if (!$stmt) {
        $err = $pdo->errorInfo();
        file_put_contents('debug_verify.log', "Erro na query: " . print_r($err, true) . "\n", FILE_APPEND);
        die();
    }

    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    file_put_contents('debug_verify.log', "Items found: " . count($items) . "\n", FILE_APPEND);

    if (empty($items)) {
        file_put_contents('debug_verify.log', "Nenhuma notificação encontrada no banco.\n", FILE_APPEND);
    } else {
        foreach ($items as $n) {
            $line = "ID: {$n['id']} | User: {$n['usuario_id']} | Msg: {$n['mensagem']} | Link: {$n['link']}\n";
            file_put_contents('debug_verify.log', $line, FILE_APPEND);
        }
    }
} catch (Exception $e) {
    file_put_contents('debug_verify.log', "Erro Fatal: " . $e->getMessage() . "\n", FILE_APPEND);
}
