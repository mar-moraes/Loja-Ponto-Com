<?php
require __DIR__ . '/../Banco de dados/conexao.php';

try {
    // Definir ID de um fornecedor válido (ex: 6 - Sandra)
    $defaultSupplierId = 6;

    // Verificar se o usuário 6 existe
    $stmtUser = $pdo->prepare("SELECT id FROM usuarios WHERE id = ?");
    $stmtUser->execute([$defaultSupplierId]);
    if (!$stmtUser->fetch()) {
        die("ERRO: O usuário ID $defaultSupplierId não existe no banco! Por favor, verifica a tabela 'usuarios'.");
    }

    // Atualizar produtos sem dono
    $sql = "UPDATE produtos SET usuario_id = :supplier WHERE usuario_id IS NULL OR usuario_id = 0";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':supplier' => $defaultSupplierId]);

    echo "Sucesso! Produtos órfãos foram atribuídos ao fornecedor ID $defaultSupplierId.\n";
    echo "Linhas afetadas: " . $stmt->rowCount() . "\n";
} catch (PDOException $e) {
    echo "Erro ao atualizar produtos: " . $e->getMessage() . "\n";
}
