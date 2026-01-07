<?php
$config = require 'magalu_config.php';

$params = [
    'client_id' => $config['client_id'],
    'response_type' => 'code',
    'redirect_uri' => $config['redirect_uri'],
    'scope' => $config['scopes']
];

$authUrl = $config['auth_endpoint'] . '?' . http_build_query($params);

echo "<h1>Integração Magalu</h1>";
echo "<p>Clique no link abaixo para autorizar o aplicativo e gerar o token de acesso:</p>";
echo "<a href='$authUrl' style='font-size: 20px; font-weight: bold;'>Autorizar Magalu</a>";
