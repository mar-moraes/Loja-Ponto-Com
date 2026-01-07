<?php
require dirname(__DIR__) . '/vendor/autoload.php';

$tokenFile = 'magalu_token.json';
if (!file_exists($tokenFile)) exit;

$tokenData = json_decode(file_get_contents($tokenFile), true);
$accessToken = $tokenData['access_token'];
$config = require 'magalu_config.php';
$clientId = $config['client_id'];

$url = 'https://api.magalu.com/seller/v1/portfolios/skus';

$headersSets = [
    [
        "Authorization: Bearer $accessToken",
        "Accept: application/json"
    ],
    [
        "Authorization: Bearer $accessToken",
        "Accept: application/json",
        "X-Api-Key: $clientId"
    ],
    [
        "Authorization: Bearer $accessToken",
        "Accept: application/json",
        "X-Zw-Paula-Code: $clientId" // Sometimes used in older APIs? or 'X-Client-Id'?
    ]
];

$log = "Test Start: " . date('Y-m-d H:i:s') . "\n";

foreach ($headersSets as $index => $headers) {
    $log .= "Testing Header Set #$index\n";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $log .= "HTTP Code: $httpCode\n";
    $log .= "Response: " . substr($response, 0, 200) . "\n";
    $log .= "---------------------------------------------------\n";
}

file_put_contents('test_log.txt', $log);
