<?php
session_start();
require 'Banco de dados/conexao.php'; 

// 1. Validar e pegar o ID da URL
if (!isset($_GET['id']) || !filter_var($_GET['id'], FILTER_VALIDATE_INT)) {
    header('Location: index.php');
    exit();
}
$produto_id = $_GET['id'];

// 2. Buscar o produto principal
try {
    // O SELECT * já vai pegar a nova coluna 'desconto'
    $stmt = $pdo->prepare("SELECT * FROM PRODUTOS WHERE id = ? AND status = 'ativo'");
    $stmt->execute([$produto_id]);
    $produto = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$produto) {
        header('Location: index.php');
        exit();
    }
    
    // 3. Buscar as imagens secundárias
    $stmt_imgs = $pdo->prepare("SELECT url_imagem FROM PRODUTO_IMAGENS WHERE produto_id = ?");
    $stmt_imgs->execute([$produto_id]);
    $imagens_secundarias = $stmt_imgs->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    error_log("Erro ao buscar produto: " . $e->getMessage());
    header('Location: index.php');
    exit();
}

// 4. Lógica de usuário logado (para o header)
$usuario_logado = isset($_SESSION['usuario_nome']);
$nome_usuario = $usuario_logado ? explode(' ', $_SESSION['usuario_nome'])[0] : '';


// 5. Parsear a descrição (lógica anterior)
// 5. Parsear a descrição (NOVA LÓGICA)
$descricao_completa = $produto['descricao'] ?? '';
$caracteristicas_array = [];
$especificacoes_array = []; // <-- NOVO ARRAY
$descricao_texto = '';

// Encontra os marcadores
$inicio_caract = strpos($descricao_completa, "--- CARACTERÍSTICAS ---");
$inicio_espec = strpos($descricao_completa, "--- ESPECIFICAÇÕES ---"); // <-- NOVO
$inicio_desc = strpos($descricao_completa, "--- DESCRIÇÃO ---");

// --- Processa Bloco de Descrição (o último) ---
if ($inicio_desc !== false) {
    $descricao_texto = trim(substr($descricao_completa, $inicio_desc + strlen("--- DESCRIÇÃO ---")));
} else {
    // Fallback se não houver marcadores (ou se for só descrição antiga)
    if ($inicio_caract === false && $inicio_espec === false) {
         $descricao_texto = $descricao_completa;
    }
}

// --- Processa Bloco de Características ---
if ($inicio_caract !== false) {
    // O bloco termina onde o próximo bloco (especificações ou descrição) começa
    $fim_caract = $inicio_espec !== false ? $inicio_espec : $inicio_desc;
    if ($fim_caract === false) $fim_caract = strlen($descricao_completa); // Se for o último

    $bloco_caract = substr(
        $descricao_completa, 
        $inicio_caract + strlen("--- CARACTERÍSTICAS ---"),
        $fim_caract - ($inicio_caract + strlen("--- CARACTERÍSTICAS ---"))
    );
    
    $linhas = explode("\n", trim($bloco_caract));
    foreach ($linhas as $linha) {
        $partes = explode(":", $linha, 2); 
        if (count($partes) == 2) {
            $caracteristicas_array[] = [
                'nome' => trim($partes[0]),
                'valor' => trim($partes[1])
            ];
        }
    }
}

// --- NOVO: Processa Bloco de Especificações ---
if ($inicio_espec !== false) {
    // O bloco termina onde a descrição começa
    $fim_espec = $inicio_desc !== false ? $inicio_desc : strlen($descricao_completa);

    $bloco_espec = substr(
        $descricao_completa, 
        $inicio_espec + strlen("--- ESPECIFICAÇÕES ---"),
        $fim_espec - ($inicio_espec + strlen("--- ESPECIFICAÇÕES ---"))
    );
    
    $linhas = explode("\n", trim($bloco_espec));
    foreach ($linhas as $linha) {
        if (!empty(trim($linha))) {
            $especificacoes_array[] = trim($linha); // Adiciona ao novo array
        }
    }
}

// =======================================================
// --- (NOVO BLOCO DE LÓGICA DE DESCONTO) ---
// =======================================================
$preco_original = (float)$produto['preco'];
// Pegamos a coluna 'desconto' que veio do banco
$desconto_percent = (int)$produto['desconto']; 
$tem_desconto = $desconto_percent > 0;

$preco_final = $preco_original; // Preço que vai para o data-price e JS
$preco_antigo_formatado = '';  // Ex: R$ 100,00 (com risco)
$preco_final_formatado = '';   // Ex: R$ 80,00 (preço principal)
$badge_texto = '';             // Ex: 20% OFF

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
// =======================================================
// --- (FIM DO NOVO BLOCO) ---
// =======================================================
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <title>Página do Produto</title>
  <link rel="stylesheet" href="estilos/style.css"/>
  <link rel="stylesheet" href="estilos/estilo_carrinho.css">
  <link rel="stylesheet" href="estilos/estilo_produto.css">
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

      <main class="produto-container" 
            data-id="<?php echo $produto['id']; ?>" 
            data-title="<?php echo htmlspecialchars($produto['nome']); ?>" 
            data-price="<?php echo htmlspecialchars($preco_final); ?>" 
            data-img="<?php echo htmlspecialchars($produto['imagem_url']); ?>">
      
      <div class="coluna-galeria">
        <div class="thumbnails">
          <img src="<?php echo htmlspecialchars($produto['imagem_url']); ?>" alt="Thumb 1" class="thumb-img active" onmouseover="mudarImagem(this)">
          <?php foreach ($imagens_secundarias as $img): ?>
            <img src="<?php echo htmlspecialchars($img['url_imagem']); ?>" alt="Thumb" class="thumb-img" onmouseover="mudarImagem(this)">
          <?php endforeach; ?>
        </div>
        <div class="imagem-principal-container">
          <img src="<?php echo htmlspecialchars($produto['imagem_url']); ?>" alt="Imagem Principal" id="imagem-principal">
        </div>
      </div>

    <div class="coluna-info">
      
        <button class="btn-favoritar" id="btn-favoritar">
          <svg width="24" height="24" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
          </svg>
        </button>

        <h1 class="produto-titulo" id="produto-titulo"><?php echo htmlspecialchars($produto['nome']); ?></h1>
        
        <div class="produto-preco">
          <?php if ($tem_desconto): ?>
            <span class="old" id="produto-preco-antigo"><?php echo $preco_antigo_formatado; ?></span>
          <?php endif; ?>
          
          <span class="price" id="produto-preco"><?php echo $preco_final_formatado; ?></span>
          
          <?php if ($tem_desconto): ?>
            <span class="badge" id="produto-badge"><?php echo $badge_texto; ?></span>
          <?php endif; ?>
        </div>
        <div class="especificacoes-rapidas">
          <div class="produto-avaliacao">
            <span class="star" data-value="1">&#9734;</span>
            <span class="star" data-value="2">&#9734;</span>
            <span class="star" data-value="3">&#9734;</span>
            <span class="star" data-value="4">&#9734;</span>
            <span class="star" data-value="5">&#9734;</span>
          </div>          
          <h3>O que você precisa saber sobre este produto:</h3>
          <ul>
            <?php if (!empty($especificacoes_array)): ?>
                <?php foreach ($especificacoes_array as $espec): ?>
                    <li><?php echo htmlspecialchars($espec); ?></li>
                <?php endforeach; ?>
            <?php else: ?>
                <?php
                  // Exibe os itens antigos apenas se a lista estiver vazia (para produtos não atualizados)
                  if(empty($caracteristicas_array) && empty($especificacoes_array)) {
                      echo '<li>Compatível com outros conjuntos LEGO.</li>';
                      echo '<li>Inclui 3 minifiguras.</li>';
                  }
                ?>
            <?php endif; ?>
          </ul>
          
        </div>

        <label class="label-quantidade">Quantidade:</label>
        <div class="seletor-quantidade-wrapper">
            <div class="contador-container" style="margin-right: 0; justify-content: flex-start;">
              <button type="button" onclick="alterarContador(-1)">-</button>
              <div id="contador-numero" class="contador-numero" style="color: black; font-weight:500;">1</div>
              <button type="button" onclick="alterarContador(1)">+</button>
            </div>
            <span class="qtd-disponivel">(<?php echo $produto['estoque']; ?> disponíveis)</span>
        </div>

        <div class="botoes-acao">
          <button class="btn-acao btn-comprar" id="btn-comprar">Comprar agora</button>
          <button class="btn-acao btn-adicionar" id="btn-adicionar-carrinho">Adicionar ao carrinho</button>
        </div>
    </div>
</main>
    <div class="detalhes-produto-container">

      <section id="caracteristicas" class="detalhe-bloco">
        <h2>Características principais</h2>
        
        <div class="caracteristicas-tabela">
          <?php if (!empty($caracteristicas_array)): ?>
            <?php foreach ($caracteristicas_array as $caract): ?>
              <div class="caracteristica-item">
                <span><?php echo htmlspecialchars($caract['nome']); ?></span>
                <span><?php echo htmlspecialchars($caract['valor']); ?></span>
              </div>
            <?php endforeach; ?>
          <?php else: ?>
            <p>Este produto não possui características principais detalhadas.</p>
          <?php endif; ?>
        </div>
      </section>

      <section id="descricao" class="detalhe-bloco">
        <h2>Descrição do produto</h2>
        <p>
          <?php echo nl2br(htmlspecialchars($descricao_texto)); // nl2br preserva quebras de linha ?>
        </p>
      </section>

      <section id="opinioes" class="detalhe-bloco">
        <h2>Opiniões do produto</h2>
        
        <div class="opiniao-form">
          <textarea placeholder="Escreva sua opinião aqui... (Esta é uma demonstração visual, o botão não salva os dados)"></textarea>
          <button type="button" class="btn-acao btn-comprar">Enviar</button>
        </div>
      </section>

    </div>

<script>
  // --- 1. SELETORES DOS ELEMENTOS ---
  const container = document.querySelector(".produto-container");
  const imagemPrincipal = document.getElementById("imagem-principal");
  const thumbnails = document.querySelectorAll(".thumb-img");
  
  const contadorNumero = document.getElementById("contador-numero");
  
  const estoqueMaximo = <?php echo $produto['estoque']; ?>;
  
  let quantidade = 1; 

  const btnComprar = document.getElementById("btn-comprar");
  const btnAdicionar = document.getElementById("btn-adicionar-carrinho");
  const btnFavoritar = document.getElementById("btn-favoritar");

  // --- 2. LÓGICA DA GALERIA ---
  function mudarImagem(thumbElement) {
    imagemPrincipal.src = thumbElement.src;
    thumbnails.forEach(thumb => thumb.classList.remove("active"));
    thumbElement.classList.add("active");
  }

  // --- 3. LÓGICA DO CONTADOR DE QUANTIDADE (CORRIGIDA) ---
  function alterarContador(valor) {
    let novaQuantidade = quantidade + valor;

    if (novaQuantidade < 1) {
      novaQuantidade = 1;
    } else if (novaQuantidade > estoqueMaximo) {
      novaQuantidade = estoqueMaximo; 
    }
    
    quantidade = novaQuantidade;
    contadorNumero.innerText = quantidade;
  }

  // --- 4. FUNÇÃO AUXILIAR PARA PEGAR DADOS DO PRODUTO ---
  function getDadosProduto() {
    return {
      id: container.dataset.id, 
      title: container.dataset.title,
      // O 'data-price' agora já vem com o desconto calculado pelo PHP
      price: parseFloat(container.dataset.price),
      img: container.dataset.img,
      quantidade: quantidade 
    };
  }

  // --- 5. LÓGICA DAS AÇÕES (CARRINHO E FAVORITOS) ---
  btnAdicionar.addEventListener("click", () => {
    const produto = getDadosProduto();
    let carrinho = JSON.parse(localStorage.getItem("carrinho")) || [];

    const existente = carrinho.find(p => p.title === produto.title);

    if (existente) {
      existente.quantidade += produto.quantidade;
    } else {
      carrinho.push(produto);
    }

    localStorage.setItem("carrinho", JSON.stringify(carrinho));
    window.location.href = "tela_carrinho.php"; 
  });

  btnComprar.addEventListener("click", () => {
    const produto = getDadosProduto();
    const totalDaCompra = produto.price * produto.quantidade;
    localStorage.setItem("totalCompra", totalDaCompra.toFixed(2));
    window.location.href = "tela_entrega.php";
  });

  btnFavoritar.addEventListener("click", (e) => {
    const produto = getDadosProduto();
    produto.quantidade = 1; 
    
    let salvos = JSON.parse(localStorage.getItem("salvos")) || [];
    const indexExistente = salvos.findIndex(p => p.title === produto.title);

    if (indexExistente === -1) {
      salvos.push(produto);
      btnFavoritar.classList.add("active");
    } else {
      salvos.splice(indexExistente, 1); 
      btnFavoritar.classList.remove("active");
    }
    localStorage.setItem("salvos", JSON.stringify(salvos));
  });

  // --- 6. LÓGICA DE AVALIAÇÃO (ESTRELAS) ---
  const stars = document.querySelectorAll(".produto-avaliacao .star");
  const starContainer = document.querySelector(".produto-avaliacao");
  
  let currentRating = 0; 

  function drawStars(rating) {
    stars.forEach(star => {
      const starValue = parseInt(star.dataset.value);
      
      if (starValue <= rating) {
        star.classList.add("filled");
        star.innerHTML = "&#9733;"; 
      } else {
        star.classList.remove("filled");
        star.innerHTML = "&#9734;"; 
      }
    });
  }

  stars.forEach(star => {
    star.addEventListener("mouseover", () => {
      const rating = parseInt(star.dataset.value);
      drawStars(rating); 
    });

    star.addEventListener("click", () => {
      currentRating = parseInt(star.dataset.value); 
      localStorage.setItem("rating_" + container.dataset.title, currentRating);
      drawStars(currentRating); 
    });
  });

  starContainer.addEventListener("mouseleave", () => {
    drawStars(currentRating);
  });
</script>
</body>
</html>