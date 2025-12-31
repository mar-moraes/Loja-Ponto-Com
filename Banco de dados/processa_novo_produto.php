<?php
session_start();
header('Content-Type: application/json');
require 'conexao.php';

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['sucesso' => false, 'mensagem' => 'Usuário não logado.']);
    exit();
}
$usuario_id = $_SESSION['usuario_id'];

$uploadDir = '../assets/imagens/Produtos/';
// Diretorio local ainda pode ser usado para fallback ou removido se tudo for p/ nuvem
// if (!file_exists($uploadDir) && !mkdir($uploadDir, 0777, true)) { ... }

// Autoload composer
require_once __DIR__ . '/../vendor/autoload.php';

use Services\CloudinaryService;
use Services\CacheService;

try {
    $cloudinary = new CloudinaryService();
} catch (Exception $e) {
    echo json_encode(['sucesso' => false, 'mensagem' => 'Erro config CDN: ' . $e->getMessage()]);
    exit();
}

try {
    $cache = new CacheService();
} catch (Exception $e) {
    // Falha silenciosa ou log
}

try {
    $titulo = $_POST['titulo'] ?? 'Produto sem nome';
    $preco = (float)($_POST['preco'] ?? 0);
    $desconto = (int)($_POST['desconto'] ?? 0);
    $quantidade = (int)($_POST['quantidade'] ?? 0);
    $categoria_id = (int)($_POST['categoria'] ?? 1);
    $produto_id = !empty($_POST['produto_id']) ? (int)$_POST['produto_id'] : null;

    $descricao_html = $_POST['descricao'] ?? '';

    $caracteristicas_bloco = "--- CARACTERÍSTICAS ---\n";
    if (isset($_POST['caracteristica_nome']) && isset($_POST['caracteristica_valor'])) {
        $nomes = $_POST['caracteristica_nome'];
        $valores = $_POST['caracteristica_valor'];
        for ($i = 0; $i < count($nomes); $i++) {
            if (!empty($nomes[$i])) {
                $caracteristicas_bloco .= htmlspecialchars($nomes[$i]) . ": " . htmlspecialchars($valores[$i] ?? '') . "\n";
            }
        }
    }

    $especificacoes_bloco = "--- ESPECIFICAÇÕES ---\n";
    if (isset($_POST['especificacao_rapida'])) {
        foreach ($_POST['especificacao_rapida'] as $item) {
            if (!empty(trim($item))) {
                $especificacoes_bloco .= htmlspecialchars(trim($item)) . "\n";
            }
        }
    }

    $descricao_final = $caracteristicas_bloco . "\n" . $especificacoes_bloco . "\n--- DESCRIÇÃO ---\n" . $descricao_html;

    // Lógica da Imagem Principal
    $mainImageDbPath = null;

    // 1. Se enviou arquivo novo
    // 1. Se enviou arquivo novo
    if (isset($_FILES['imagem_principal']) && $_FILES['imagem_principal']['error'] == UPLOAD_ERR_OK) {
        try {
            $mainImageDbPath = $cloudinary->upload($_FILES['imagem_principal']);
        } catch (Exception $e) {
            echo json_encode(['sucesso' => false, 'mensagem' => $e->getMessage()]);
            exit();
        }
    }
    // 2. Se já existe imagem (rascunho ou edição)
    elseif (!empty($_POST['imagem_atual'])) {
        $mainImageDbPath = $_POST['imagem_atual'];
    }

    // Validação: Imagem é obrigatória para publicar
    if (!$mainImageDbPath) {
        echo json_encode(['sucesso' => false, 'mensagem' => 'A imagem principal é obrigatória para publicar o produto.']);
        exit();
    }

    $pdo->beginTransaction();

    if ($produto_id) {
        // UPDATE
        // Verifica permissão
        $stmtCheck = $pdo->prepare("SELECT id FROM PRODUTOS WHERE id = ? AND usuario_id = ?");
        $stmtCheck->execute([$produto_id, $usuario_id]);
        if (!$stmtCheck->fetch()) {
            echo json_encode(['sucesso' => false, 'mensagem' => 'Produto não encontrado ou permissão negada.']);
            exit();
        }

        $sql = "UPDATE PRODUTOS SET 
                nome = ?, preco = ?, desconto = ?, descricao = ?, estoque = ?, categoria_id = ?, status = 'ativo'";
        $params = [$titulo, $preco, $desconto, $descricao_final, $quantidade, $categoria_id];

        if ($mainImageDbPath) {
            $sql .= ", imagem_url = ?";
            $params[] = $mainImageDbPath;
        }

        $sql .= " WHERE id = ?";
        $params[] = $produto_id;

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        $id_final = $produto_id;
    } else {
        // INSERT
        $stmt = $pdo->prepare(
            "INSERT INTO PRODUTOS (nome, preco, desconto, descricao, estoque, categoria_id, imagem_url, status, usuario_id) 
             VALUES (?, ?, ?, ?, ?, ?, ?, 'ativo', ?)"
        );
        $stmt->execute([
            $titulo,
            $preco,
            $desconto,
            $descricao_final,
            $quantidade,
            $categoria_id,
            $mainImageDbPath,
            $usuario_id
        ]);
        $id_final = $pdo->lastInsertId();
    }

    // Processa Thumbnails
    if (isset($_FILES['thumbnails'])) {
        $stmt_thumb = $pdo->prepare("INSERT INTO PRODUTO_IMAGENS (produto_id, url_imagem) VALUES (?, ?)");

        // Re-organiza array de files se necessario, mas CloudinaryService::upload espera um item de $_FILES (com tmp_name etc)
        // O array $_FILES['thumbnails'] vem invertido (name => [0=>..., 1=>...]).

        $count = count($_FILES['thumbnails']['name']);
        for ($i = 0; $i < $count; $i++) {
            if ($_FILES['thumbnails']['error'][$i] == UPLOAD_ERR_OK) {
                // Monta array unico estilo $_FILES['file']
                $fileItem = [
                    'name'     => $_FILES['thumbnails']['name'][$i],
                    'type'     => $_FILES['thumbnails']['type'][$i],
                    'tmp_name' => $_FILES['thumbnails']['tmp_name'][$i],
                    'error'    => $_FILES['thumbnails']['error'][$i],
                    'size'     => $_FILES['thumbnails']['size'][$i],
                ];

                try {
                    $thumbUrl = $cloudinary->upload($fileItem);
                    $stmt_thumb->execute([$id_final, $thumbUrl]);
                } catch (Exception $e) {
                    // Ignora erro de uma imagem ou interrompe?
                    // Vamos ignorar para nao cancelar o produto todo, mas idealmente logar
                }
            }
        }
    }

    $pdo->commit();

    // Limpa o cache da home para mostrar o novo produto imediatamente
    if (isset($cache)) {
        $cache->forget('home_produtos_destaque');
        $cache->forget('home_produtos_carousel');
    }

    echo json_encode(['sucesso' => true, 'mensagem' => 'Produto publicado com sucesso!']);
} catch (PDOException $e) {
    $pdo->rollBack();
    echo json_encode(['sucesso' => false, 'mensagem' => 'Erro DB: ' . $e->getMessage()]);
} catch (Exception $e) {
    echo json_encode(['sucesso' => false, 'mensagem' => 'Erro Geral: ' . $e->getMessage()]);
}
