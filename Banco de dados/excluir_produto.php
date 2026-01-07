<?php
session_start();
require 'conexao.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../src/tela_login.html');
    exit();
}

$id = $_GET['id'] ?? 0;
$usuario_id = $_SESSION['usuario_id'];

// Autoload para usar o CacheService
require_once __DIR__ . '/../vendor/autoload.php';

use Services\CacheService;

try {
    // 1. Instanciar serviço
    $cloudinary = null;
    try {
        $cloudinary = new Services\CloudinaryService();
    } catch (Exception $e) {
        error_log("Erro ao instanciar CloudinaryService: " . $e->getMessage());
    }

    // 2. Coletar URLs para deletar (Principal + Galeria)
    $urlsToDelete = [];

    // Verificar produto e imagem principal
    $stmtSelect = $pdo->prepare("SELECT imagem_url FROM PRODUTOS WHERE id = ? AND usuario_id = ?");
    $stmtSelect->execute([$id, $usuario_id]);
    $produto = $stmtSelect->fetch(PDO::FETCH_ASSOC);

    if ($produto) {
        // Se tem imagem principal
        if (!empty($produto['imagem_url'])) {
            $urlsToDelete[] = $produto['imagem_url'];
        }

        // Buscar imagens da galeria
        try {
            $stmtGallery = $pdo->prepare("SELECT url_imagem FROM PRODUTO_IMAGENS WHERE produto_id = ?");
            $stmtGallery->execute([$id]);
            $galleryImages = $stmtGallery->fetchAll(PDO::FETCH_COLUMN);

            foreach ($galleryImages as $imgUrl) {
                if (!empty($imgUrl)) {
                    $urlsToDelete[] = $imgUrl;
                }
            }
        } catch (Exception $e) {
            error_log("Erro ao buscar imagens da galeria: " . $e->getMessage());
        }
    }

    // 3. Processar deleção no Cloudinary
    if ($cloudinary && !empty($urlsToDelete)) {
        foreach ($urlsToDelete as $url) {
            try {
                // Extrair Public ID
                $path = parse_url($url, PHP_URL_PATH);
                if ($path && preg_match('/upload\/(?:v\d+\/)?(.+)\.[a-zA-Z0-9]+$/', $path, $matches)) {
                    $publicId = $matches[1];
                    $cloudinary->delete($publicId);
                }
            } catch (Exception $e) {
                error_log("Erro ao deletar imagem do Cloudinary ($url): " . $e->getMessage());
            }
        }
    }

    $stmt = $pdo->prepare("DELETE FROM PRODUTOS WHERE id = ? AND usuario_id = ?");
    $stmt->execute([$id, $usuario_id]);

    if ($stmt->rowCount() > 0) {
        // Se deletou algo, limpa o cache
        try {
            $cache = new CacheService();
            $cache->forget('home_produtos_destaque');
            $cache->forget('home_produtos_carousel');
        } catch (Exception $e) {
            // Se der erro no cache, apenas loga e segue a vida
            error_log("Erro ao limpar cache na exclusão: " . $e->getMessage());
        }
    }

    header('Location: ../src/tela_minha_conta.php?msg=produto_excluido');
} catch (PDOException $e) {
    die("Erro ao excluir: " . $e->getMessage());
}
