<?php
// src/test_client_credentials.php

$config = require 'magalu_config.php';

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $config['token_endpoint']);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);
curl_setopt($ch, CURLOPT_USERPWD, $config['client_id'] . ":" . $config['client_secret']);

// Try Client Credentials Flow
$postData = http_build_query([
    'grant_type' => 'client_credentials',
    'scope' => $config['scopes']
]);

curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);

echo "<h2>Tentando Grant Type: Client Credentials...</h2>";
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Code: " . $httpCode . "<br>";
echo "Response: " . htmlspecialchars($response) . "<br><br>";

$data = json_decode($response, true);

if (isset($data['access_token'])) {
    echo "<h3>Sucesso! Token obtido sem login manual.</h3>";
    file_put_contents('magalu_token.json', json_encode($data));
    echo "Token salvo em magalu_token.json. <a href='seed_magalu.php'>Pode rodar o seed agora.</a>";
} else {
    echo "Falha no Client Credentials. Parece que precisaremos do Authorization Code (Login do Usuário) mesmo.<br>";
    echo "Tente ver se o erro indica escopos inválidos ou falta de permissão.";
}
