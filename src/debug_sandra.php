<?php
require '../Banco de dados/conexao.php';

try {
    // 1. Find User ID for Sandra
    $stmtUser = $pdo->prepare("SELECT id, nome, email FROM usuarios WHERE nome LIKE :nome");
    $stmtUser->execute([':nome' => '%Sandra%']);
    $users = $stmtUser->fetchAll(PDO::FETCH_ASSOC);

    echo "--- Users found for 'Sandra' ---\n";
    print_r($users);

    // 2. Check notifications for each found user
    foreach ($users as $u) {
        $stmtNotif = $pdo->prepare("SELECT * FROM notificacoes WHERE usuario_id = :id");
        $stmtNotif->execute([':id' => $u['id']]);
        $notifs = $stmtNotif->fetchAll(PDO::FETCH_ASSOC);

        echo "\n--- Notifications for User ID {$u['id']} ({$u['nome']}) ---\n";
        print_r($notifs);
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
