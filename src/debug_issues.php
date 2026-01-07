<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require 'Banco de dados/conexao.php';

header('Content-Type: text/plain');

echo "=== START DEBUG ===\n";

try {
    echo "Checking connection...\n";
    $pdo->query("SELECT 1");
    echo "Connection OK.\n";
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage() . "\n");
}

echo "\n=== CHECKING CHAT SERVICE QUERY ===\n";
try {
    $conversaId = 1;
    $remetenteId = 3;

    // Verify if conversation 1 exists
    $check = $pdo->query("SELECT * FROM conversas WHERE id = 1")->fetch();
    if (!$check) {
        echo "Conversation 1 does not exist. Picking first available conversation.\n";
        $check = $pdo->query("SELECT * FROM conversas LIMIT 1")->fetch();
        if ($check) {
            $conversaId = $check['id'];
            $remetenteId = $check['comprador_id']; // Assert sender is buyer
            echo "Using Conversa ID: $conversaId, Remetente: $remetenteId\n";
        } else {
            die("No conversations found in DB.\n");
        }
    }

    $sql = "
        SELECT 
            c.comprador_id, 
            c.fornecedor_id,
            u_remetente.nome as nome_remetente,
            p.nome as nome_produto
        FROM conversas c
        JOIN usuarios u_remetente ON u_remetente.id = :remetenteId
        LEFT JOIN produtos p ON c.produto_id = p.id
        WHERE c.id = :conversaId
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([':conversaId' => $conversaId, ':remetenteId' => $remetenteId]);
    $res = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($res) {
        echo "Query Result:\n";
        print_r($res);
        $destinatarioId = ($res['comprador_id'] == $remetenteId) ? $res['fornecedor_id'] : $res['comprador_id'];
        echo "Calculated Destinatario ID: $destinatarioId\n";
    } else {
        echo "Query returned empty result for chat $conversaId / user $remetenteId\n";
        echo "Checking if user $remetenteId exists...\n";
        $u = $pdo->query("SELECT * FROM usuarios WHERE id = $remetenteId")->fetch();
        echo $u ? "User exists: " . $u['nome'] . "\n" : "User does not exist.\n";
    }
} catch (Exception $e) {
    echo "Error checking chat query: " . $e->getMessage() . "\n";
}

echo "\n=== TESTING PRODUCT INSERTION ===\n";
try {
    $sql = "INSERT INTO PRODUTOS (nome, preco, desconto, descricao, estoque, categoria_id, imagem_url, status, usuario_id) 
            VALUES (?, ?, ?, ?, ?, ?, ?, 'ativo', ?)";

    $stmt = $pdo->prepare($sql);

    // Get a valid user ID (supplier ideally)
    $userId = $pdo->query("SELECT id FROM usuarios LIMIT 1")->fetchColumn();

    $params = [
        'Produto Debug ' . time(),
        100.50,
        10,
        'Descricao teste debug',
        50,
        1,
        '../assets/imagens/placeholder.png',
        $userId
    ];

    echo "Attempting insert with User ID: $userId\n";
    $res = $stmt->execute($params);
    $newId = $pdo->lastInsertId();
    echo "Product Insert Result: " . ($res ? "SUCCESS" : "FAILURE") . " (ID: $newId)\n";
} catch (Exception $e) {
    echo "Error inserting product: " . $e->getMessage() . "\n";
}

echo "=== END DEBUG ===\n";
