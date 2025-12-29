<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../Banco de dados/conexao.php';

echo "--- Debug Infra ---\n";

echo "Checking Database Config...\n";
if (isset($pdo)) {
    echo "PDO Object exists.\n";
    try {
        $stmt = $pdo->query("SELECT 1");
        if ($stmt) {
            echo "Database Connection: OK\n";
        } else {
            echo "Database Connection: Failed\n";
        }
    } catch (PDOException $e) {
        echo "Database Connection: ERROR (" . $e->getMessage() . ")\n";
    }
} else {
    echo "PDO Object missing!\n";
}

echo "\nChecking Cloudinary Config...\n";

use Dotenv\Dotenv;

try {
    $envPath = __DIR__ . '/../';
    if (file_exists($envPath . '.env')) {
        echo ".env file exists at $envPath.env\n";

        // Debug file content raw
        $content = file_get_contents($envPath . '.env');
        echo "Raw content length: " . strlen($content) . "\n";
        echo "First 20 chars: " . substr($content, 0, 20) . "\n";

        $dotenv = Dotenv::createImmutable($envPath);
        $dotenv->safeLoad();

        $cUrl = getenv('CLOUDINARY_URL');
        $serverUrl = $_SERVER['CLOUDINARY_URL'] ?? null;
        $envUrl = $_ENV['CLOUDINARY_URL'] ?? null;

        echo "getenv: " . ($cUrl ? "FOUND" : "MISSING") . "\n";
        echo "_SERVER: " . ($serverUrl ? "FOUND" : "MISSING") . "\n";
        echo "_ENV: " . ($envUrl ? "FOUND" : "MISSING") . "\n";

        if ($cUrl || $serverUrl || $envUrl) {
            echo "SUCCESS: CLOUDINARY_URL loaded.\n";
        } else {
            echo "FAILURE: CLOUDINARY_URL NOT loaded.\n";
        }
    } else {
        echo ".env file MISSING\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n--- End Debug ---\n";
