<?php

namespace Services;

use Cloudinary\Configuration\Configuration;
use Cloudinary\Api\Upload\UploadApi;
use Dotenv\Dotenv;

class CloudinaryService
{
    public function __construct()
    {
        // Load .env if not already loaded and CLOUDINARY_URL is missing
        if (!getenv('CLOUDINARY_URL')) {
            // Adjust path to root directory assuming this file is in src/Services/
            $dotenvPath = __DIR__ . '/../../';
            if (file_exists($dotenvPath . '.env')) {
                $dotenv = Dotenv::createImmutable($dotenvPath);
                $dotenv->safeLoad();
            }
        }

        // Configure Cloudinary explicitly from environment variable
        // The SDK might auto-pick it up, but explicit init ensures it's set
        if (getenv('CLOUDINARY_URL')) {
            Configuration::instance(getenv('CLOUDINARY_URL'));
        }
    }

    public function upload($file)
    {
        if (!isset($file['tmp_name']) || empty($file['tmp_name'])) {
            throw new \Exception("Arquivo inválido para upload.");
        }

        try {
            $upload = new UploadApi();
            $result = $upload->upload($file['tmp_name'], [
                'folder' => 'loja_ponto_com/produtos',
                'resource_type' => 'auto'
            ]);

            return $result['secure_url'];
        } catch (\Exception $e) {
            // Re-throw to be handled by the controller
            throw new \Exception("Erro no upload para Cloudinary: " . $e->getMessage());
        }
    }
}
