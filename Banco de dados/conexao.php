<?php
// Configurações do banco de dados
$dsn = 'mysql:host=127.0.0.1;dbname=bancodadosteste'; // Altere "bancodadosteste" para o nome do seu banco de dados
$dbusername = 'root'; // Usuário padrão do XAMPP/MySQL, mas pode variar conforme sua configuração
$dbpassword = ''; // Galera, essa é senha do meu XAMPP/MySQL. Altere conforme sua configuração.

try {
    // Cria a conexão PDO
    $pdo = new PDO($dsn, $dbusername, $dbpassword);
    
    // Define o modo de erro para exceções, para podermos ver os erros
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Define o charset para utf8mb4, conforme seu script SQL
    $pdo->exec("SET NAMES 'utf8mb4'");
    
} catch (PDOException $e) {
    // Em caso de falha, exibe o erro. 
    // Em um site em produção, você deve logar este erro, não exibi-lo.
    die('Conexão falhou: ' . $e->getMessage());
}
?>
