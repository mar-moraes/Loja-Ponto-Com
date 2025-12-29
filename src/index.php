<?php
session_start();
require '../Banco de dados/conexao.php'; // Inclui a conexão
require '../vendor/autoload.php';

use Services\CacheService;

$cache = new CacheService();

// === BLOCO MODIFICADO: Busca produtos com a média de avaliações ===
try {
  $produtos = $cache->remember('home_produtos_destaque', 300, function () use ($pdo) {
    // 1. A consulta SQL foi atualizada
    //    - LEFT JOIN junta com a tabela de avaliações
    //    - AVG(a.nota) calcula a média das notas
    //    - COUNT(a.nota) conta quantas avaliações existem
    //    - GROUP BY p.id agrupa os resultados por produto
    $stmt = $pdo->prepare(
      "SELECT
                p.*,
                AVG(a.nota) as media_avaliacoes,
                COUNT(a.nota) as total_avaliacoes
            FROM
                produtos p
            LEFT JOIN
                avaliacoes a ON p.id = a.produto_id
            WHERE
                p.status = 'ativo'
            GROUP BY
                p.id
            LIMIT 20"
    );
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  });
} catch (Exception $e) { // Catch broadly or keep PDOException if sure callback throws it
  $produtos = []; // Array vazio em caso de erro
  error_log("Erro ao buscar produtos: " . $e->getMessage());
}
// === FIM DO BLOCO MODIFICADO ===


// === NOVO BLOCO: Busca 3 produtos aleatórios para o carrossel ===
try {
  $produtos_carousel = $cache->remember('home_produtos_carousel', 300, function () use ($pdo) {
    // ORDER BY RAND() pega produtos aleatórios.
    // Garante que tenham imagem e estejam ativos.
    $stmt_carousel = $pdo->prepare("SELECT id, nome, imagem_url FROM produtos WHERE status = 'ativo' AND imagem_url IS NOT NULL ORDER BY RAND() LIMIT 3");
    $stmt_carousel->execute();
    return $stmt_carousel->fetchAll(PDO::FETCH_ASSOC);
  });
} catch (Exception $e) {
  $produtos_carousel = []; // Array vazio em caso de erro
  error_log("Erro ao buscar produtos do carrossel: " . $e->getMessage());
}
// === FIM DO NOVO BLOCO ===


// Verifica se o usuário está logado
$usuario_logado = isset($_SESSION['usuario_nome']);
$nome_usuario = $usuario_logado ? explode(' ', $_SESSION['usuario_nome'])[0] : '';
?>
<!doctype html>
<html lang="pt-BR">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Loja Ponto Com</title>
  <link rel="stylesheet" href="../assets/estilos/style.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
  <style>
    /* Ajuste para o slide do carrossel cobrir a área */
    .hero-swiper .swiper-slide {
      background-size: cover;
      background-position: center;
    }

    /* Estilo para o título no slide (opcional) */
    .hero-swiper .swiper-slide a {
      display: flex;
      width: 100%;
      height: 100%;
      align-items: flex-end;
      /* Coloca o texto no fim */
      justify-content: center;
      padding: 20px;
      text-decoration: none;
      color: white;
      font-size: 1.5rem;
      font-weight: bold;
      text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.7);
      box-sizing: border-box;
      /* Garante que o padding não quebre o layout */
    }
  </style>
</head>

<body>
  <header class="topbar">
    <nav class="actions">
      <div class="logo-container">
        <a href="index.php" style="display: flex; align-items: center;">
          <img src="../assets/imagens/exemplo-logo.png" alt="" style="width: 40px; height: 40px;">
        </a>
      </div>

      <form action="buscar.php" method="GET" style="position: relative; width: 600px; max-width: 100%;">
        <input type="search" id="pesquisa" name="q" placeholder="Digite sua pesquisa..." style="font-size: 16px; width: 100%; height: 40px; padding-left: 15px; padding-right: 45px; border-radius: 6px; border: none; box-sizing: border-box;">
        <button type="submit" style="position: absolute; right: 0; top: 0; height: 40px; width: 45px; border: none; background: transparent; cursor: pointer; display: flex; align-items: center; justify-content: center;">
          <img src="../assets/imagens/lupa.png" alt="lupa" style="width: 28px; height: 28px; opacity: 0.6;">
        </button>
      </form>

      <div style="display: flex; gap: 30px; align-items: center;">
        <?php if ($usuario_logado): ?>
          <a href="tela_minha_conta.php">Olá, <?php echo htmlspecialchars($nome_usuario); ?></a>
          <a href="../Banco de dados/logout.php">Sair</a>
        <?php else: ?>
          <a href="tela_cadastro.html">Crie a sua conta</a>
          <a href="tela_login.html">Entre</a>
        <?php endif; ?>
        <a href="tela_carrinho.php" style="display: flex; align-items: center; gap: 5px;">
          Carrinho
          <img src="../assets/imagens/carrinho invertido.png" alt="" style="width: 20px; height: 20px;">
        </a>
      </div>
    </nav>
  </header>

  <section class="hero">
    <div class="swiper hero-swiper">
      <div class="swiper-wrapper">

        <?php foreach ($produtos_carousel as $produto_slide): ?>
          <div class="swiper-slide" style="background-image:url('<?php echo htmlspecialchars($produto_slide['imagem_url']); ?>')">
            <a href="tela_produto.php?id=<?php echo $produto_slide['id']; ?>">
              <?php // echo htmlspecialchars($produto_slide['nome']); /* Descomente se quiser o nome no slide */ 
              ?>
            </a>
          </div>
        <?php endforeach; ?>

        <?php if (empty($produtos_carousel)): /* Caso não ache produtos */ ?>
          <div class="swiper-slide" style="background-image:url('../assets/imagens/exemplo-logo.png')"></div>
        <?php endif; ?>
      </div>
      <div class="swiper-button-prev"></div>
      <div class="swiper-button-next"></div>
      <div class="swiper-pagination"></div>
    </div>
  </section>

  <main class="container">

    <div class="controls">
      <label for="sort">Ordenar por</label>
      <select id="sort" aria-label="Ordenar por">
        <option value="relevantes">Mais relevantes</option>
        <option value="menor-preco">Menor preço</option>
        <option value="maior-preco">Maior preço</option>
      </select>
    </div>

    <section class="grid" id="product-grid">
      <?php foreach ($produtos as $produto): ?>
        <?php
        // --- (INÍCIO - LÓGICA DE DESCONTO PARA O CARD) ---
        // Copiada de tela_produto.php
        $preco_original = (float)$produto['preco'];
        // Usamos ?? 0 para garantir que funcione se a coluna 'desconto' for nula
        $desconto_percent = (int)($produto['desconto'] ?? 0);
        $tem_desconto = $desconto_percent > 0;

        $preco_final = $preco_original; // Preço para o data-price
        $preco_antigo_formatado = '';
        $preco_final_formatado = '';
        $badge_texto = '';

        if ($tem_desconto) {
          // Calcula o preço final com desconto
          $preco_final = $preco_original * (1 - ($desconto_percent / 100));

          // Formata os textos para exibição
          $preco_antigo_formatado = 'R$ ' . number_format($preco_original, 2, ',', '.');
          $preco_final_formatado = 'R$ ' . number_format($preco_final, 2, ',', '.');
          $badge_texto = $desconto_percent . '% OFF';
        } else {
          // Se não tem desconto, o preço final é o original
          $preco_final_formatado = 'R$ ' . number_format($preco_original, 2, ',', '.');
        }
        // --- (FIM - LÓGICA DE DESCONTO PARA O CARD) ---

        // --- (NOVO) 2. LÓGICA DE AVALIAÇÃO PARA O CARD ---
        $nota_para_estrelas = 0;
        // Verifica se 'total_avaliacoes' é maior que 0
        if ((int)$produto['total_avaliacoes'] > 0) {
          // Arredonda a média para o número inteiro mais próximo
          $nota_para_estrelas = round((float)$produto['media_avaliacoes']);
        }
        // --- (FIM - LÓGICA DE AVALIAÇÃO) ---
        ?>

        <a href="tela_produto.php?id=<?php echo $produto['id']; ?>" class="card-link">
          <article class="card" data-price="<?php echo htmlspecialchars($preco_final); ?>">
            <div class="thumb" style="background-image:url('<?php echo htmlspecialchars($produto['imagem_url'] ?? '../assets/imagens/placeholder.png'); ?>')"></div>
            <div class="title"><?php echo htmlspecialchars($produto['nome']); ?></div>

            <div class="card-avaliacao">
              <?php
              // Loop para desenhar as 5 estrelas
              for ($i = 1; $i <= 5; $i++) {
                // Adiciona a classe 'filled' se $i for menor ou igual à nota
                $classe = ($i <= $nota_para_estrelas) ? 'filled' : '';
                echo '<span class="star ' . $classe . '">&#9733;</span>';
              }
              ?>
            </div>

            <div>
              <?php if ($tem_desconto): ?>
                <span class="old"><?php echo $preco_antigo_formatado; ?></span>
              <?php endif; ?>

              <span class="price"><?php echo $preco_final_formatado; ?></span>

              <?php if ($tem_desconto): ?>
                <span class="badge"><?php echo $badge_texto; ?></span>
              <?php endif; ?>

              <span class="parcel">em 12x sem juros</span>
            </div>
          </article>
        </a>
      <?php endforeach; ?>

      <?php if (empty($produtos)): ?>
        <p>Nenhum produto encontrado no momento.</p>
      <?php endif; ?>
    </section>
  </main>

  <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
  <script>
    document.addEventListener('DOMContentLoaded', function() {

      // Script do Carrossel (Swiper)
      const swiper = new Swiper('.hero-swiper', {
        loop: true,
        autoplay: {
          delay: 3000,
          disableOnInteraction: false
        },
        pagination: {
          el: '.swiper-pagination',
          clickable: true
        },
        navigation: {
          nextEl: '.swiper-button-next',
          prevEl: '.swiper-button-prev'
        },
      });

      // === NOVO SCRIPT PARA ORDENAÇÃO ===
      const sortSelect = document.getElementById('sort');
      const productGrid = document.getElementById('product-grid');
      // Pega todos os 'a.card-link' que estão dentro do grid
      const products = Array.from(productGrid.querySelectorAll('.card-link'));
      // Salva a ordem original (para o "Mais Relevantes")
      const originalProducts = [...products];

      sortSelect.addEventListener('change', function() {
        const sortBy = this.value; // Pega o valor (ex: 'menor-preco')

        let sortedProducts = [];

        if (sortBy === 'menor-preco') {
          sortedProducts = products.sort((a, b) => {
            // Pega o 'data-price' de dentro do 'article' de cada 'a'
            const priceA = parseFloat(a.querySelector('.card').dataset.price);
            const priceB = parseFloat(b.querySelector('.card').dataset.price);
            return priceA - priceB; // Ordena do menor para o maior
          });
        } else if (sortBy === 'maior-preco') {
          sortedProducts = products.sort((a, b) => {
            const priceA = parseFloat(a.querySelector('.card').dataset.price);
            const priceB = parseFloat(b.querySelector('.card').dataset.price);
            return priceB - priceA; // Ordena do maior para o menor
          });
        } else {
          // 'relevantes' (volta à ordem original)
          sortedProducts = originalProducts;
        }

        // Limpa o grid e re-adiciona os produtos na ordem correta
        productGrid.innerHTML = ''; // Limpa o grid
        sortedProducts.forEach(product => {
          productGrid.appendChild(product); // Adiciona o produto (o <a>) de volta
        });
      });
      // === FIM DO NOVO SCRIPT ===

    });
  </script>
</body>

</html>