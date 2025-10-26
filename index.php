<?php
session_start();
require 'Banco de dados/conexao.php'; // Inclui a conexão

// Busca produtos do banco de dados
try {
    $stmt = $pdo->prepare("SELECT * FROM PRODUTOS WHERE status = 'ativo' LIMIT 20");
    $stmt->execute();
    $produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $produtos = []; // Array vazio em caso de erro
    error_log("Erro ao buscar produtos: " . $e->getMessage());
}

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
            <a href="minha_conta.php">Olá, <?php echo htmlspecialchars($nome_usuario); ?></a>
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
        <div class="swiper-slide" style="background-image:url('imagens/Zane lego.jpg')"></div>
        <div class="swiper-slide" style="background-image:url('imagens/SEU_OUTRO_BANNER.jpg')"></div>
        <div class="swiper-slide" style="background-image:url('')"></div>
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
        <option>Mais relevantes</option>
        <option>Menor preço</option>
        <option>Maior preço</option>
      </select>
    </div>

    <section class="grid">
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
    // Script do Carrossel (Swiper)
    document.addEventListener('DOMContentLoaded', function () {
      const swiper = new Swiper('.hero-swiper', {
        loop: true,
        autoplay: { delay: 3000, disableOnInteraction: false },
        pagination: { el: '.swiper-pagination', clickable: true },
        navigation: { nextEl: '.swiper-button-next', prevEl: '.swiper-button-prev' },
      });
    });
  </script>
</body>
</html>