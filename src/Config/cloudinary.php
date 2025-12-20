<?php

use Cloudinary\Configuration\Configuration;

if (file_exists(__DIR__ . '/../../.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../../');
    $dotenv->safeLoad();
}

// Configura a URL do Cloudinary a partir da variável de ambiente
Configuration::instance($_ENV['CLOUDINARY_URL'] ?? '');
