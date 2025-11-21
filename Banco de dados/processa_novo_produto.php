<?php
session_start();
header('Content-Type: application/json');
require 'conexao.php';

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['sucesso' => false, 'mensagem' => 'Usuário não logado.']);
    exit();
}
$usuario_id = $_SESSION['usuario_id'];

$uploadDir = '../imagens/Produtos/';
if (!file_exists($uploadDir) && !mkdir($uploadDir, 0777, true)) {
    echo json_encode(['sucesso' => false, 'mensagem' => 'Falha ao criar diretório de uploads.']);
    exit();
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
    if (isset($_FILES['imagem_principal']) && $_FILES['imagem_principal']['error'] == UPLOAD_ERR_OK) {
        $ext = pathinfo($_FILES['imagem_principal']['name'], PATHINFO_EXTENSION);
        $newName = uniqid() . '.' . $ext;
        if (move_uploaded_file($_FILES['imagem_principal']['tmp_name'], $uploadDir . $newName)) {
            $mainImageDbPath = 'imagens/Produtos/' . $newName;
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

    // Processa Thumbnails (Adiciona novas, não remove antigas por enquanto)
    if (isset($_FILES['thumbnails'])) {
        $stmt_thumb = $pdo->prepare("INSERT INTO PRODUTO_IMAGENS (produto_id, url_imagem) VALUES (?, ?)");

        for ($i = 0; $i < count($_FILES['thumbnails']['name']); $i++) {
            if ($_FILES['thumbnails']['error'][$i] == UPLOAD_ERR_OK) {
                $ext = pathinfo($_FILES['thumbnails']['name'][$i], PATHINFO_EXTENSION);
                $thumbName = uniqid() . '.' . $ext;
                if (move_uploaded_file($_FILES['thumbnails']['tmp_name'][$i], $uploadDir . $thumbName)) {
                    $thumbDbPath = 'imagens/Produtos/' . $thumbName;
                    $stmt_thumb->execute([$id_final, $thumbDbPath]);
                }
            }
        }
    }

    $pdo->commit();
    echo json_encode(['sucesso' => true, 'mensagem' => 'Produto publicado com sucesso!']);
} catch (PDOException $e) {
    $pdo->rollBack();
    echo json_encode(['sucesso' => false, 'mensagem' => 'Erro DB: ' . $e->getMessage()]);
} catch (Exception $e) {
    echo json_encode(['sucesso' => false, 'mensagem' => 'Erro Geral: ' . $e->getMessage()]);
}
