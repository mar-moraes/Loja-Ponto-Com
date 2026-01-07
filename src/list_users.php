<?php
require '../Banco de dados/conexao.php';
$stmt = $pdo->query("SELECT id, nome, email FROM usuarios");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
if (empty($users)) {
    echo "No users found.\n";
} else {
    foreach ($users as $u) {
        echo "[ID: " . $u['id'] . "] " . $u['nome'] . " (" . $u['email'] . ")\n";
    }
}
