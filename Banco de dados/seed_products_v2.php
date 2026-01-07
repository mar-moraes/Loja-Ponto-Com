<?php
// Banco de dados/seed_products_v2.php

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/conexao.php';

use Services\CacheService;

$usuario_id = 6;
$limpar_produtos_anteriores = true;

function getRealImage($searchTerm)
{
    try {
        $url = "https://dummyjson.com/products/search?q=" . urlencode($searchTerm) . "&limit=1";
        $opts = [
            "http" => [
                "method" => "GET",
                "header" => "User-Agent: Mozilla/5.0\r\n",
                "timeout" => 5
            ]
        ];
        $context = stream_context_create($opts);
        $response = file_get_contents($url, false, $context);

        if ($response) {
            $data = json_decode($response, true);
            if (!empty($data['products'])) {
                return $data['products'][0]['thumbnail'] ?? $data['products'][0]['images'][0] ?? null;
            }
        }
    } catch (Exception $e) {
    }
    return "https://placehold.co/600x400?text=" . urlencode($searchTerm);
}

echo "Iniciando Seeding V2 (Texto PT + Imagens API)...\n";

try {
    // 1. Limpeza (Fora da transação)
    if ($limpar_produtos_anteriores) {
        $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
        $pdo->exec("TRUNCATE TABLE produto_imagens");
        $pdo->exec("TRUNCATE TABLE avaliacoes");
        $pdo->exec("DELETE FROM PRODUTOS");
        $pdo->exec("ALTER TABLE PRODUTOS AUTO_INCREMENT = 1");
        $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
        echo "Limpeza concluída.\n";
    }

    // 2. Transação
    $pdo->beginTransaction();

    $catalogo = [
        'Eletrônicos & Tech' => [
            ['term' => 'Galaxy', 'nome' => 'Samsung Galaxy S24 Ultra', 'preco' => 6999, 'desc' => 'Smartphone top de linha com IA.'],
            ['term' => 'Macbook', 'nome' => 'MacBook Pro M3', 'preco' => 12999, 'desc' => 'Potência profissional para criativos.'],
            ['term' => 'Headphones', 'nome' => 'Sony WH-1000XM5', 'preco' => 2200, 'desc' => 'Cancelamento de ruído líder de mercado.'],
            ['term' => 'Iphone', 'nome' => 'iPhone 15 Pro Max', 'preco' => 8500, 'desc' => 'Titânio, A17 Pro e Câmeras incríveis.'],
            ['term' => 'Monitor', 'nome' => 'Monitor Dell 4K', 'preco' => 2500, 'desc' => 'Cores precisas para design.'],
            ['term' => 'Keyboard', 'nome' => 'Teclado Keychron K2', 'preco' => 600, 'desc' => 'Mecânico, sem fio e compacto.'],
            ['term' => 'Mouse', 'nome' => 'Logitech MX Master 3S', 'preco' => 550, 'desc' => 'Produtividade máxima.'],
            ['term' => 'Tablet', 'nome' => 'iPad Air 5', 'preco' => 4500, 'desc' => 'Leveza e potência M1.'],
            ['term' => 'Watch', 'nome' => 'Apple Watch Series 9', 'preco' => 3200, 'desc' => 'Saúde e conectividade no pulso.']
        ],
        'Casa e Decoração' => [
            ['term' => 'Sofa', 'nome' => 'Sofá Retrátil 3 Lugares', 'preco' => 2500, 'desc' => 'Conforto para toda a família.'],
            ['term' => 'Lamp', 'nome' => 'Luminária de Piso', 'preco' => 350, 'desc' => 'Design moderno e luz acolhedora.'],
            ['term' => 'Chair', 'nome' => 'Cadeira Eames Wood', 'preco' => 180, 'desc' => 'Clássico do design.'],
            ['term' => 'Bed', 'nome' => 'Cama Box Queen', 'preco' => 1500, 'desc' => 'Noites de sono perfeitas.'],
            ['term' => 'Plant', 'nome' => 'Vaso com Planta Artificial', 'preco' => 120, 'desc' => 'Verde sem trabalho.'],
            ['term' => 'Rug', 'nome' => 'Tapete Geométrico', 'preco' => 400, 'desc' => 'Toque final para sua sala.'],
        ],
        'Cozinha & Gourmet' => [
            ['term' => 'Mixer', 'nome' => 'Batedeira Planetária', 'preco' => 600, 'desc' => 'Para bolos e massas pesadas.'],
            ['term' => 'Blender', 'nome' => 'Liquidificador Turbo', 'preco' => 200, 'desc' => 'Vitaminas e sucos em segundos.'],
            ['term' => 'Pan', 'nome' => 'Jogo de Panelas Antiaderente', 'preco' => 450, 'desc' => 'Cozinhe sem grudar nada.'],
            ['term' => 'Knife', 'nome' => 'Faca do Chef Inox', 'preco' => 150, 'desc' => 'Corte preciso.'],
            ['term' => 'Microwave', 'nome' => 'Micro-ondas 30L', 'preco' => 700, 'desc' => 'Agilidade na cozinha.'],
            ['term' => 'Coffee', 'nome' => 'Cafeteira Nespresso', 'preco' => 500, 'desc' => 'Café expresso cremoso.'],
        ],
        'Moda Masculina' => [
            ['term' => 'Shirt', 'nome' => 'Camisa Social Slim', 'preco' => 180, 'desc' => 'Elegância para o trabalho.'],
            ['term' => 'Jeans', 'nome' => 'Calça Jeans Premium', 'preco' => 220, 'desc' => 'Durabilidade e estilo.'],
            ['term' => 'Sneakers', 'nome' => 'Tênis Casual Branco', 'preco' => 300, 'desc' => 'Combina com tudo.'],
            ['term' => 'Jacket', 'nome' => 'Jaqueta de Couro', 'preco' => 450, 'desc' => 'Estilo atemporal.'],
            ['term' => 'Shoes', 'nome' => 'Sapato Oxford', 'preco' => 280, 'desc' => 'Para ocasiões formais.'],
            ['term' => 'Watch', 'nome' => 'Relógio Clássico', 'preco' => 350, 'desc' => 'Pontualidade e classe.'],
        ],
        'Esporte e Lazer' => [
            ['term' => 'Ball', 'nome' => 'Bola de Futebol Oficial', 'preco' => 150, 'desc' => 'Tecnologia profissional.'],
            ['term' => 'Dumbbell', 'nome' => 'Par de Halteres 5kg', 'preco' => 100, 'desc' => 'Treino em casa.'],
            ['term' => 'Bike', 'nome' => 'Bicicleta Mountain Bike', 'preco' => 1800, 'desc' => 'Aventura em qualquer terreno.'],
            ['term' => 'Bag', 'nome' => 'Mochila Esportiva', 'preco' => 120, 'desc' => 'Leve tudo o que precisa.'],
        ]
    ];

    $randomTerms = ['perfume', 'lipstick', 'skincare', 'drill', 'hammer', 'saw', 'toy', 'doll', 'car', 'book', 'pen', 'notebook'];

    // Processa Categorias
    foreach ($catalogo as $catName => $items) {
        $stmt_cat = $pdo->prepare("SELECT id FROM categorias WHERE nome = ?");
        $stmt_cat->execute([$catName]);
        $catId = $stmt_cat->fetchColumn();
        if (!$catId) {
            $pdo->prepare("INSERT INTO categorias (nome) VALUES (?)")->execute([$catName]);
            $catId = $pdo->lastInsertId();
        }

        foreach ($items as $item) {
            $img = getRealImage($item['term']);
            $descFinal = "--- CARACTERÍSTICAS ---\nMarca: Genérica\n--- ESPECIFICAÇÕES ---\nProduto original\n--- DESCRIÇÃO ---\n" . $item['desc'];

            $pdo->prepare("INSERT INTO PRODUTOS (nome, preco, desconto, descricao, estoque, categoria_id, imagem_url, status, usuario_id) VALUES (?, ?, 0, ?, 50, ?, ?, 'ativo', ?)")
                ->execute([$item['nome'], $item['preco'], $descFinal, $catId, $img, $usuario_id]);
        }
        echo "Cat $catName OK.\n";
    }

    // Processa Aleatórios para volume
    echo "Gerando extras...\n";
    $catIdMisc = 1; // Default
    foreach ($randomTerms as $term) {
        $img = getRealImage($term);
        $nome = ucfirst($term) . " Premium";
        $descFinal = "--- CARACTERÍSTICAS ---\nMarca: Importada\n--- ESPECIFICAÇÕES ---\nAlta qualidade\n--- DESCRIÇÃO ---\nProduto excelente.";

        $pdo->prepare("INSERT INTO PRODUTOS (nome, preco, desconto, descricao, estoque, categoria_id, imagem_url, status, usuario_id) VALUES (?, 99.90, 0, ?, 20, ?, ?, 'ativo', ?)")
            ->execute([$nome, $descFinal, $catIdMisc, $img, $usuario_id]);
    }

    $pdo->commit();
    echo "Sucesso Total.\n";

    // Limpa cache
    try {
        $cache = new CacheService();
        $cache->forget('home_produtos_destaque');
        $cache->forget('home_produtos_carousel');
        $cache->forget('categorias_lista');
    } catch (Exception $e) {
    }
} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    echo "Erro: " . $e->getMessage();
}
