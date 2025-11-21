<?php
session_start();
header('Content-Type: application/json');
require 'conexao.php';

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['sucesso' => false, 'mensagem' => 'Acesso negado.']);
    exit();
}
$usuario_id = $_SESSION['usuario_id'];
$id = $_GET['id'] ?? 0;

try {
    $stmt = $pdo->prepare("SELECT * FROM PRODUTOS WHERE id = ? AND usuario_id = ?");
    $stmt->execute([$id, $usuario_id]);
    $produto = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($produto) {
        // Parse da descrição para separar características e especificações
        $desc = $produto['descricao'] ?? '';

        // Extrair Características
        $caracteristicas = [];
        if (preg_match('/--- CARACTERÍSTICAS ---\n(.*?)(?=\n--- |$)/s', $desc, $matches)) {
            $lines = explode("\n", trim($matches[1]));
            foreach ($lines as $line) {
                $parts = explode(":", $line, 2);
                if (count($parts) == 2) {
                    $caracteristicas[] = ['nome' => trim($parts[0]), 'valor' => trim($parts[1])];
                }
            }
        }

        // Extrair Especificações
        $especificacoes = [];
        if (preg_match('/--- ESPECIFICAÇÕES ---\n(.*?)(?=\n--- |$)/s', $desc, $matches)) {
            $lines = explode("\n", trim($matches[1]));
            foreach ($lines as $line) {
                if (trim($line)) $especificacoes[] = trim($line);
            }
        }

        // Extrair Descrição Texto
        $descricao_texto = '';
        if (preg_match('/--- DESCRIÇÃO ---\n(.*)/s', $desc, $matches)) {
            $descricao_texto = trim($matches[1]);
        }

        // Buscar Thumbnails
        $stmtThumb = $pdo->prepare("SELECT url_imagem FROM PRODUTO_IMAGENS WHERE produto_id = ?");
        $stmtThumb->execute([$id]);
        $thumbs = $stmtThumb->fetchAll(PDO::FETCH_COLUMN);

        echo json_encode([
            'sucesso' => true,
            'produto' => [
                'id' => $produto['id'],
                'titulo' => $produto['nome'],
                'preco' => $produto['preco'],
                'desconto' => $produto['desconto'],
                'quantidade' => $produto['estoque'],
                'categoria_id' => $produto['categoria_id'],
                'imagem_url' => $produto['imagem_url'],
                'descricao' => $descricao_texto,
                'caracteristicas' => $caracteristicas,
                'especificacoes' => $especificacoes,
                'thumbnails' => $thumbs
            ]
        ]);
    } else {
        echo json_encode(['sucesso' => false, 'mensagem' => 'Produto não encontrado.']);
    }
} catch (PDOException $e) {
    echo json_encode(['sucesso' => false, 'mensagem' => 'Erro DB: ' . $e->getMessage()]);
}
