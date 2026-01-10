<?php
require '../Banco de dados/conexao.php';

try {
    echo "Verificando coluna 'ordem_destaque' na tabela 'produtos'...\n";

    // Verifica se a coluna existe
    $stmt = $pdo->query("SHOW COLUMNS FROM produtos LIKE 'ordem_destaque'");
    $coluna = $stmt->fetch();

    if (!$coluna) {
        echo "Coluna nao encontrada. Adicionando...\n";
        $pdo->exec("ALTER TABLE produtos ADD COLUMN ordem_destaque INT DEFAULT 999999");
        echo "Coluna adicionada com sucesso!\n";

        // Inicializa com o valor do ID (para manter ordem de criação inicialmente ou algo lógico)
        // Na verdade, queremos ORDER BY ordem_destaque ASC.
        // Se definirmos ordem_destaque = -id, os mais novos (maior ID) ficam com menor ordem (aparecem antes).
        // A query original era ORDER BY id DESC.
        // id=100 -> ordem = -100. id=99 -> ordem = -99.
        // Ordenando por ordem ASC: -100, -99. (100 aparece antes). Correto.

        echo "Inicializando valores...\n";
        $pdo->exec("UPDATE produtos SET ordem_destaque = -id WHERE ordem_destaque = 999999");
        echo "Valores inicializados.\n";
    } else {
        echo "Coluna ja existe.\n";
    }
} catch (PDOException $e) {
    die("Erro: " . $e->getMessage() . "\n");
}
