<?php
session_start();
header('Content-Type: application/json');
require 'conexao.php';

// --- (Opcional) Verificação de Segurança ---
/*
if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['sucesso' => false, 'mensagem' => 'Usuário não logado.']);
    exit();
}
*/
// --- Fim da Verificação ---


// 1. Definições Iniciais
$uploadDir = '../imagens/Produtos/'; 
$resposta = ['sucesso' => false, 'mensagem' => 'Erro desconhecido.'];

if (!file_exists($uploadDir) && !mkdir($uploadDir, 0777, true)) {
    echo json_encode(['sucesso' => false, 'mensagem' => 'Falha ao criar diretório de uploads.']);
    exit();
}

try {
    // 2. Coleta de Dados do Formulário (via $_POST)
    $titulo = $_POST['titulo'] ?? 'Produto sem nome';
    $preco = (float)($_POST['preco'] ?? 0);
    $desconto = (int)($_POST['desconto'] ?? 0); // <--- VALOR JÁ ESTÁ SENDO PEGO
    $quantidade = (int)($_POST['quantidade'] ?? 0);
    $categoria_id = (int)($_POST['categoria'] ?? 1); 
    
    // 3. Monta a Descrição com as Características e Especificações
    $descricao_html = $_POST['descricao'] ?? '';
    
    // Bloco Características
    $caracteristicas_bloco = "--- CARACTERÍSTICAS ---\n";
    if (isset($_POST['caracteristica_nome']) && isset($_POST['caracteristica_valor'])) {
        $nomes = $_POST['caracteristica_nome'];
        $valores = $_POST['caracteristica_valor'];

        $count = count($nomes);
        for ($i = 0; $i < $count; $i++) {
            if (!empty($nomes[$i]) && isset($valores[$i])) {
                $nome = htmlspecialchars($nomes[$i]);
                $valor = htmlspecialchars($valores[$i]);
                $caracteristicas_bloco .= $nome . ": " . $valor . "\n";
            }
        }
    }
    
    // --- (NOVO BLOCO) ---
    // Monta as Especificações Rápidas
    $especificacoes_bloco = "--- ESPECIFICAÇÕES ---\n";
    if (isset($_POST['especificacao_rapida'])) {
        foreach ($_POST['especificacao_rapida'] as $item) {
            // Usamos trim() para ignorar campos que só têm espaços
            if (!empty(trim($item))) {
                $especificacoes_bloco .= htmlspecialchars(trim($item)) . "\n";
            }
        }
    }
    // --- (FIM DO NOVO BLOCO) ---


    // Combina tudo na descrição final
    $descricao_final = $caracteristicas_bloco . "\n" . $especificacoes_bloco . "\n--- DESCRIÇÃO ---\n" . $descricao_html;

    
    // 4. Processa a Imagem Principal
    if (!isset($_FILES['imagem_principal']) || $_FILES['imagem_principal']['error'] != UPLOAD_ERR_OK) {
        echo json_encode(['sucesso' => false, 'mensagem' => 'A imagem principal é obrigatória.']);
        exit();
    }

    $mainImage = $_FILES['imagem_principal'];
    $mainImageName = uniqid() . '-' . basename($mainImage['name']);
    $mainImagePath = $uploadDir . $mainImageName;
    $mainImageDbPath = 'imagens/Produtos/' . $mainImageName; 

    if (!move_uploaded_file($mainImage['tmp_name'], $mainImagePath)) {
        echo json_encode(['sucesso' => false, 'mensagem' => 'Falha ao mover imagem principal.']);
        exit();
    }

    // 5. Insere na Tabela PRODUTOS
    $pdo->beginTransaction();
    
    // =======================================================
    // --- (MODIFICAÇÃO AQUI) ---
    // Adicionamos 'desconto' ao INSERT
    $stmt = $pdo->prepare(
        "INSERT INTO PRODUTOS (nome, preco, desconto, descricao, estoque, categoria_id, imagem_url, status) 
         VALUES (?, ?, ?, ?, ?, ?, ?, 'ativo')"
    );
    
    // Adicionamos $desconto ao execute
    $stmt->execute([
        $titulo,
        $preco,
        $desconto, // <--- VALOR NOVO
        $descricao_final,
        $quantidade,
        $categoria_id,
        $mainImageDbPath
    ]);
    // --- (FIM DA MODIFICAÇÃO) ---
    // =======================================================
    
    $produto_id = $pdo->lastInsertId();

    // 6. Processa as Miniaturas (Thumbnails)
    if (isset($_FILES['thumbnails'])) {
        $stmt_thumb = $pdo->prepare("INSERT INTO PRODUTO_IMAGENS (produto_id, url_imagem) VALUES (?, ?)");
        
        for ($i = 0; $i < count($_FILES['thumbnails']['name']); $i++) {
            if ($_FILES['thumbnails']['error'][$i] == UPLOAD_ERR_OK) {
                
                $thumbName = uniqid() . '-' . basename($_FILES['thumbnails']['name'][$i]);
                $thumbPath = $uploadDir . $thumbName;
                $thumbDbPath = 'imagens/Produtos/' . $thumbName;

                if (move_uploaded_file($_FILES['thumbnails']['tmp_name'][$i], $thumbPath)) {
                    $stmt_thumb->execute([$produto_id, $thumbDbPath]);
                }
            }
        }
    }

    // 7. Finaliza a transação
    $pdo->commit();
    echo json_encode(['sucesso' => true, 'mensagem' => 'Produto cadastrado com sucesso!']);

} catch (PDOException $e) {
    $pdo->rollBack();
    echo json_encode(['sucesso' => false, 'mensagem' => 'Erro de Banco de Dados: ' . $e->getMessage()]);
} catch (Exception $e) {
    echo json_encode(['sucesso' => false, 'mensagem' => 'Erro Geral: ' . $e->getMessage()]);
}
?>