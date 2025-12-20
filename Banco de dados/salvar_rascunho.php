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
// if (!file_exists($uploadDir) && !mkdir($uploadDir, 0777, true)) { ... }

// Autoload composer
require_once __DIR__ . '/../vendor/autoload.php';

use Services\CloudinaryService;

$cloudinary = new CloudinaryService();

try {
    // Coleta de dados (pode ser parcial)
    $titulo = $_POST['titulo'] ?? null;
    $preco = !empty($_POST['preco']) ? (float)$_POST['preco'] : null;
    $desconto = !empty($_POST['desconto']) ? (int)$_POST['desconto'] : 0;
    $quantidade = !empty($_POST['quantidade']) ? (int)$_POST['quantidade'] : 0;
    $categoria_id = !empty($_POST['categoria']) ? (int)$_POST['categoria'] : null;
    $produto_id = !empty($_POST['produto_id']) ? (int)$_POST['produto_id'] : null;

    // Descrição completa
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

    // Lógica da Imagem
    $imagem_db_path = null;

    // 1. Se enviou arquivo novo
    // 1. Se enviou arquivo novo
    if (isset($_FILES['imagem_principal']) && $_FILES['imagem_principal']['error'] == UPLOAD_ERR_OK) {
        try {
            $imagem_db_path = $cloudinary->upload($_FILES['imagem_principal']);
        } catch (Exception $e) {
            echo json_encode(['sucesso' => false, 'mensagem' => 'Erro CDN: ' . $e->getMessage()]);
            exit();
        }
    }
    // 2. Se não enviou arquivo, mas tem imagem_atual (url)
    elseif (!empty($_POST['imagem_atual'])) {
        $imagem_db_path = $_POST['imagem_atual'];
    }

    // Se produto_id existe, é UPDATE
    if ($produto_id) {
        // Verifica se pertence ao usuário
        $stmtCheck = $pdo->prepare("SELECT id FROM PRODUTOS WHERE id = ? AND usuario_id = ?");
        $stmtCheck->execute([$produto_id, $usuario_id]);
        if (!$stmtCheck->fetch()) {
            echo json_encode(['sucesso' => false, 'mensagem' => 'Produto não encontrado ou permissão negada.']);
            exit();
        }

        $sql = "UPDATE PRODUTOS SET 
                nome = ?, preco = ?, desconto = ?, descricao = ?, estoque = ?, categoria_id = ?, status = 'rascunho'";

        $params = [$titulo, $preco, $desconto, $descricao_final, $quantidade, $categoria_id];

        if ($imagem_db_path) {
            $sql .= ", imagem_url = ?";
            $params[] = $imagem_db_path;
        }

        $sql .= " WHERE id = ?";
        $params[] = $produto_id;

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        $msg = "Rascunho atualizado com sucesso!";
        $id_retorno = $produto_id;
    } else {
        // INSERT
        $sql = "INSERT INTO PRODUTOS (nome, preco, desconto, descricao, estoque, categoria_id, imagem_url, status, usuario_id) 
                VALUES (?, ?, ?, ?, ?, ?, ?, 'rascunho', ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$titulo, $preco, $desconto, $descricao_final, $quantidade, $categoria_id, $imagem_db_path, $usuario_id]);

        $msg = "Rascunho salvo com sucesso!";
        $id_retorno = $pdo->lastInsertId();
    }

    echo json_encode(['sucesso' => true, 'mensagem' => $msg, 'id' => $id_retorno]);
} catch (PDOException $e) {
    echo json_encode(['sucesso' => false, 'mensagem' => 'Erro DB: ' . $e->getMessage()]);
} catch (Exception $e) {
    echo json_encode(['sucesso' => false, 'mensagem' => 'Erro: ' . $e->getMessage()]);
}
