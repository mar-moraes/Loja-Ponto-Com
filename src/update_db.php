<?php
require '../Banco de dados/conexao.php';

try {
    echo "Iniciando atualização do banco de dados...\n";

    // 1. Adicionar usuario_id na tabela PRODUTOS se não existir
    try {
        $pdo->exec("ALTER TABLE PRODUTOS ADD COLUMN usuario_id INT");
        echo "Coluna 'usuario_id' adicionada com sucesso.\n";

        // Adicionar FK
        $pdo->exec("ALTER TABLE PRODUTOS ADD CONSTRAINT fk_PRODUTOS_USUARIOS FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE");
        echo "Foreign Key 'fk_PRODUTOS_USUARIOS' adicionada.\n";
    } catch (PDOException $e) {
        // Se der erro, provavelmente já existe
        echo "Nota: " . $e->getMessage() . "\n";
    }

    // 2. Tornar colunas NULLABLE para permitir rascunhos incompletos
    $columns = [
        'nome' => 'VARCHAR(255)',
        'preco' => 'DECIMAL(10,2)',
        'categoria_id' => 'INT',
        'estoque' => 'INT'
    ];

    foreach ($columns as $col => $type) {
        try {
            // MySQL syntax to modify column
            $sql = "ALTER TABLE PRODUTOS MODIFY COLUMN $col $type NULL";
            $pdo->exec($sql);
            echo "Coluna '$col' alterada para NULLABLE.\n";
        } catch (PDOException $e) {
            echo "Erro ao alterar coluna '$col': " . $e->getMessage() . "\n";
        }
    }

    // Ajustar default do estoque para 0 se for null (opcional, mas bom)
    $pdo->exec("ALTER TABLE PRODUTOS ALTER COLUMN estoque SET DEFAULT 0");

    echo "Atualização concluída!\n";
} catch (PDOException $e) {
    echo "Erro fatal: " . $e->getMessage();
}
