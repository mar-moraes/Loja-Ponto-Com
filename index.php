<?php
session_start();
require 'Banco de dados/conexao.php'; // Inclui a conexão

// Busca produtos do banco de dados (Grid Principal)
try {
    $stmt = $pdo->prepare("SELECT * FROM PRODUTOS WHERE status = 'ativo' LIMIT 20");
    $stmt->execute();
    $produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $produtos = []; // Array vazio em caso de erro
    error_log("Erro ao buscar produtos: " . $e->getMessage());
}

// === NOVO BLOCO: Busca 3 produtos aleatórios para o carrossel ===
try {
    // ORDER BY RAND() pega produtos aleatórios. 
    // Garante que tenham imagem e estejam ativos.
    $stmt_carousel = $pdo->prepare("SELECT id, nome, imagem_url FROM PRODUTOS WHERE status = 'ativo' AND imagem_url IS NOT NULL ORDER BY RAND() LIMIT 3");
    $stmt_carousel->execute();
    $produtos_carousel = $stmt_carousel->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
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
  <link rel="stylesheet" href="estilos/style.css">
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
        align-items: flex-end; /* Coloca o texto no fim */
        justify-content: center;
        padding: 20px;
        text-decoration: none;
        color: white;
        font-size: 1.5rem;
        font-weight: bold;
        text-shadow: 2px 2px 4px rgba(0,0,0,0.7);
        box-sizing: border-box; /* Garante que o padding não quebre o layout */
    }
  </style>
</head>
<body>
    <header class="topbar">
      <nav class="actions"> 
        <div class="logo-container"> 
            <a href="index.php" style="display: flex; align-items: center;">
              <img src="imagens/exemplo-logo.png" alt="" style="width: 40px; height: 40px;">
            </a>
          </div> 
        
        <form action="buscar.php" method="GET" style="position: relative; width: 600px; max-width: 100%;">
          <input type="search" id="pesquisa" name="q" placeholder="Digite sua pesquisa..." style="font-size: 16px; width: 100%; height: 40px; padding-left: 15px; padding-right: 45px; border-radius: 6px; border: none; box-sizing: border-box;">
          <button type="submit" style="position: absolute; right: 0; top: 0; height: 40px; width: 45px; border: none; background: transparent; cursor: pointer; display: flex; align-items: center; justify-content: center;">
            <img src="imagens/lupa.png" alt="lupa" style="width: 28px; height: 28px; opacity: 0.6;">
          </button>
        </form>
        
        <div style="display: flex; gap: 30px; align-items: center;">
          <?php if ($usuario_logado): ?>
            <a href="tela_minha_conta.php">Olá, <?php echo htmlspecialchars($nome_usuario); ?></a>
            <a href="Banco de dados/logout.php">Sair</a>
          <?php else: ?>
            <a href="tela_cadastro.html">Crie a sua conta</a>
            <a href="tela_login.html">Entre</a>
          <?php endif; ?>
          <a href="tela_carrinho.php" style="display: flex; align-items: center; gap: 5px;">
            Carrinho
            <img src="imagens/carrinho invertido.png" alt="" style="width: 20px; height: 20px;">
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
                    <?php // echo htmlspecialchars($produto_slide['nome']); /* Descomente se quiser o nome no slide */ ?>
                </a>
            </div>
        <?php endforeach; ?>

        <?php if (empty($produtos_carousel)): /* Caso não ache produtos */ ?>
            <div class="swiper-slide" style="background-image:url('imagens/exemplo-logo.png')"></div>
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
            <a href="tela_produto.php?id=<?php echo $produto['id']; ?>" class="card-link">
                <article class="card" data-price="<?php echo htmlspecialchars($produto['preco']); ?>">
                    <div class="thumb" style="background-image:url('<?php echo htmlspecialchars($produto['imagem_url'] ?? 'imagens/placeholder.png'); ?>')"></div>
                    <div class="title"><?php echo htmlspecialchars($produto['nome']); ?></div>
                    <div class="card-avaliacao"></div> <div>
                        <span class="price">R$ <?php echo number_format($produto['preco'], 2, ',', '.'); ?></span>
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
    document.addEventListener('DOMContentLoaded', function () {
      
      // Script do Carrossel (Swiper)
      const swiper = new Swiper('.hero-swiper', {
        loop: true,
        autoplay: { delay: 3000, disableOnInteraction: false },
        pagination: { el: '.swiper-pagination', clickable: true },
        navigation: { nextEl: '.swiper-button-next', prevEl: '.swiper-button-prev' },
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