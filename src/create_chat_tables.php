<?php
require __DIR__ . '/../Banco de dados/conexao.php';

try {
    // Tabela de Conversas
    $sqlConversas = "
    CREATE TABLE IF NOT EXISTS conversas (
        id INT AUTO_INCREMENT PRIMARY KEY,
        comprador_id INT NOT NULL,
        fornecedor_id INT NOT NULL,
        pedido_id INT DEFAULT NULL,
        produto_id INT DEFAULT NULL,
        data_criacao DATETIME DEFAULT CURRENT_TIMESTAMP,
        data_atualizacao DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (comprador_id) REFERENCES usuarios(id) ON DELETE CASCADE,
        FOREIGN KEY (fornecedor_id) REFERENCES usuarios(id) ON DELETE CASCADE,
        FOREIGN KEY (pedido_id) REFERENCES pedidos(id) ON DELETE SET NULL,
        FOREIGN KEY (produto_id) REFERENCES produtos(id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
    ";

    $pdo->exec($sqlConversas);
    echo "Tabela 'conversas' verificada/criada com sucesso.\n";

    // Tabela de Mensagens
    $sqlMensagens = "
    CREATE TABLE IF NOT EXISTS mensagens (
        id INT AUTO_INCREMENT PRIMARY KEY,
        conversa_id INT NOT NULL,
        remetente_id INT NOT NULL,
        conteudo TEXT NOT NULL,
        lida BOOLEAN DEFAULT 0,
        data_envio DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (conversa_id) REFERENCES conversas(id) ON DELETE CASCADE,
        FOREIGN KEY (remetente_id) REFERENCES usuarios(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
    ";

    $pdo->exec($sqlMensagens);
    echo "Tabela 'mensagens' verificada/criada com sucesso.\n";
} catch (PDOException $e) {
    echo "Erro ao criar tabelas: " . $e->getMessage() . "\n";
}
