<?php
session_start();
require 'Banco de dados/conexao.php'; // Inclui a conexão

// 1. OBTER E VALIDAR O TERMO DE BUSCA
$query_string = '';
$produtos = [];

if (isset($_GET['q']) && !empty(trim($_GET['q']))) {
    $query_string = trim($_GET['q']);
    
    // 2. BUSCAR NO BANCO DE DADOS
    try {
        // Prepara o termo para a consulta LIKE
        $search_term = '%' . $query_string . '%';
        
        // Busca onde o nome OU a descrição correspondem
        $stmt = $pdo->prepare("SELECT * FROM produtos WHERE (nome LIKE ? OR descricao LIKE ?) AND status = 'ativo' LIMIT 20");
        $stmt->execute([$search_term, $search_term]);
        $produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    } catch (PDOException $e) {
        $produtos = []; // Array vazio em caso de erro
        error_log("Erro ao buscar produtos: " . $e->getMessage());
    }

}
// else: Se 'q' estiver vazio ou não definido, $produtos continuará sendo um array vazio.

// 3. VERIFICAR LOGIN (para o cabeçalho)
$usuario_logado = isset($_SESSION['usuario_nome']);
$nome_usuario = $usuario_logado ? explode(' ', $_SESSION['usuario_nome'])[0] : '';
?>
<!doctype html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Resultados para "<?php echo htmlspecialchars($query_string); ?>"</title>
  <link rel="stylesheet" href="estilos/style.css">

  <style>
      .search-title-container {
        width: 100%;          /* Garante que a seção ocupe toda a largura */
        text-align: center;   /* Centraliza o h1 dentro dela */
        margin-top: 25px;     /* Adiciona um espaço acima */
        margin-bottom: 25px;  /* Adiciona um espaço abaixo */
      }

      .search-title-container h1 {
        font-size: 1.75rem; /* Tamanho da fonte diminuído (ajuste se precisar) */
        font-weight: 600;   /* Peso da fonte (opcional) */
        color: #333;       /* Cor (opcional) */
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
          <input type="search" id="pesquisa" name="q" value="<?php echo htmlspecialchars($query_string); ?>" placeholder="Digite sua pesquisa..." style="font-size: 16px; width: 100%; height: 40px; padding-left: 15px; padding-right: 45px; border-radius: 6px; border: none; box-sizing: border-box;">
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

  <section class="search-title-container">
    <?php if (!empty($query_string)): ?>
        <h1>Resultados para "<?php echo htmlspecialchars($query_string); ?>"</h1>
    <?php else: ?>
        <h1>Nenhum termo de busca fornecido</h1>
    <?php endif; ?>
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
            // Copiada de index.php
            $preco_original = (float)$produto['preco'];
            $desconto_percent = (int)($produto['desconto'] ?? 0); 
            $tem_desconto = $desconto_percent > 0;

            $preco_final = $preco_original;
            $preco_antigo_formatado = '';
            $preco_final_formatado = '';
            $badge_texto = '';

            if ($tem_desconto) {
                $preco_final = $preco_original * (1 - ($desconto_percent / 100));
                $preco_antigo_formatado = 'R$ ' . number_format($preco_original, 2, ',', '.');
                $preco_final_formatado = 'R$ ' . number_format($preco_final, 2, ',', '.');
                $badge_texto = $desconto_percent . '% OFF';
            } else {
                $preco_final_formatado = 'R$ ' . number_format($preco_original, 2, ',', '.');
            }
            // --- (FIM - LÓGICA DE DESCONTO PARA O CARD) ---
            ?>
            
            <a href="tela_produto.php?id=<?php echo $produto['id']; ?>" class="card-link">
                <article class="card" data-price="<?php echo htmlspecialchars($preco_final); ?>">
                    <div class="thumb" style="background-image:url('<?php echo htmlspecialchars($produto['imagem_url'] ?? 'imagens/placeholder.png'); ?>')"></div>
                    <div class="title"><?php echo htmlspecialchars($produto['nome']); ?></div>
                    <div class="card-avaliacao"></div> 
                    
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
        
        <?php if (empty($produtos) && !empty($query_string)): ?>
            <p>Nenhum produto encontrado para "<?php echo htmlspecialchars($query_string); ?>".</p>
        <?php elseif (empty($produtos) && empty($query_string)): ?>
            <p>Por favor, digite um termo na barra de pesquisa.</p>
        <?php endif; ?>
    </section>
  </main>

  <script>
    // SCRIPT DE ORDENAÇÃO (copiado de index.php)
    document.addEventListener('DOMContentLoaded', function () {
      
      const sortSelect = document.getElementById('sort');
      const productGrid = document.getElementById('product-grid');
      const products = Array.from(productGrid.querySelectorAll('.card-link')); 
      const originalProducts = [...products]; 

      sortSelect.addEventListener('change', function() {
          const sortBy = this.value; 

          let sortedProducts = [];

          if (sortBy === 'menor-preco') {
              sortedProducts = products.sort((a, b) => {
                  const priceA = parseFloat(a.querySelector('.card').dataset.price);
                  const priceB = parseFloat(b.querySelector('.card').dataset.price);
                  return priceA - priceB;
              });
          } else if (sortBy === 'maior-preco') {
              sortedProducts = products.sort((a, b) => {
                  const priceA = parseFloat(a.querySelector('.card').dataset.price);
                  const priceB = parseFloat(b.querySelector('.card').dataset.price);
                  return priceB - priceA;
              });
          } else {
              sortedProducts = originalProducts;
          }

          productGrid.innerHTML = ''; 
          sortedProducts.forEach(product => {
              productGrid.appendChild(product);
          });
      });

    });
  </script>
</body>
</html>