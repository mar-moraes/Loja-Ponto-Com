<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: text/plain');

$dsn = 'mysql:host=127.0.0.1;dbname=bancodadosteste';
$user = 'root';
$pass = '1234';

echo "=== STANDALONE DB DEBUG ===\n";
echo "Attempting connection to '$dsn' with user '$user'...\n";

try {
    $pdo = new PDO($dsn, $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Connection SUCCESS.\n";

    $db = $pdo->query("SELECT DATABASE()")->fetchColumn();
    echo "Current Database: $db\n";

    $prods = $pdo->query("SELECT COUNT(*) FROM produtos")->fetchColumn();
    echo "Products Count: $prods\n";

    $notifs = $pdo->query("SELECT COUNT(*) FROM notificacoes")->fetchColumn();
    echo "Notifications Count: $notifs\n";

    if ($prods > 0) {
        $last = $pdo->query("SELECT id, nome, status FROM produtos ORDER BY id DESC LIMIT 1")->fetch(PDO::FETCH_ASSOC);
        echo "Last Product: [{$last['id']}] {$last['nome']}\n";
    }
} catch (PDOException $e) {
    echo "CONNECTION FAILED: " . $e->getMessage() . "\n";
}
echo "=== END ===\n";
