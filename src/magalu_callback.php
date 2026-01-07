<?php
$config = require 'magalu_config.php';

if (!isset($_GET['code'])) {
    die("Erro: Authorization Code não encontrado.");
}

$code = $_GET['code'];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $config['token_endpoint']);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);

// Using Basic Auth for client credentials
curl_setopt($ch, CURLOPT_USERPWD, $config['client_id'] . ":" . $config['client_secret']);

$postData = http_build_query([
    'grant_type' => 'authorization_code',
    'code' => $code,
    'redirect_uri' => $config['redirect_uri']
]);

curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode !== 200) {
    echo "Erro ao obter token. HTTP Code: $httpCode <br>";
    echo "Resposta: " . htmlspecialchars($response);
    exit;
}

$tokenData = json_decode($response, true);

if (isset($tokenData['access_token'])) {
    file_put_contents('magalu_token.json', json_encode($tokenData));
    echo "<h1>Token obtido com sucesso!</h1>";
    echo "<p>Access Token salvo em <code>magalu_token.json</code>.</p>";
    echo "<pre>" . print_r($tokenData, true) . "</pre>";
    echo "<br><a href='seed_magalu.php'>Ir para Importação de Produtos</a>";
} else {
    echo "Erro ao decodificar resposta do token.";
    var_dump($tokenData);
}
