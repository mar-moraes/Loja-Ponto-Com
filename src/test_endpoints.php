<?php
require dirname(__DIR__) . '/vendor/autoload.php';

$tokenFile = 'magalu_token.json';
if (!file_exists($tokenFile)) {
    die("Token file not found.\n");
}

$tokenData = json_decode(file_get_contents($tokenFile), true);
$accessToken = $tokenData['access_token'];

$endpoints = [
    'https://api.magalu.com/seller/v1/portfolios/skus',
    'https://api.magalu.com/seller/v1/products',
    'https://api.magalu.com/seller/v1/skus',
    'https://api.magalu.com/open/portfolio-skus/v1/skus',
    'https://api.magalu.com/open/portfolio-skus-seller/v1/skus'
];

echo "Token Length: " . strlen($accessToken) . "\n";
echo "Testing endpoints...\n";

foreach ($endpoints as $url) {
    echo "---------------------------------------------------\n";
    echo "URL: $url\n";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer $accessToken",
        "Accept: application/json",
        // "X-Api-Key: ..." // Try without first
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    echo "HTTP Code: $httpCode\n";
    if ($httpCode >= 200 && $httpCode < 300) {
        echo "SUCCESS! Response: " . substr($response, 0, 200) . "\n";
    } else {
        echo "Error Response: " . substr($response, 0, 200) . "\n";
    }
}
echo "---------------------------------------------------\n";
