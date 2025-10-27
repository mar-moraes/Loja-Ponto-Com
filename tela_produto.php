<?php
session_start();
require 'Banco de dados/conexao.php'; // Inclui a conexão

// 1. Validar e pegar o ID da URL
if (!isset($_GET['id']) || !filter_var($_GET['id'], FILTER_VALIDATE_INT)) {
    // Se o ID não for válido, volta para a index
    header('Location: index.php');
    exit();
}
$produto_id = $_GET['id'];

// 2. Buscar o produto principal
try {
    $stmt = $pdo->prepare("SELECT * FROM PRODUTOS WHERE id = ? AND status = 'ativo'");
    $stmt->execute([$produto_id]);
    $produto = $stmt->fetch(PDO::FETCH_ASSOC);

    // Se o produto não for encontrado, volta para a index
    if (!$produto) {
        header('Location: index.php');
        exit();
    }
    
    // 3. Buscar as imagens secundárias (da tabela produto_imagens)
    $stmt_imgs = $pdo->prepare("SELECT url_imagem FROM PRODUTO_IMAGENS WHERE produto_id = ?");
    $stmt_imgs->execute([$produto_id]);
    $imagens_secundarias = $stmt_imgs->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    error_log("Erro ao buscar produto: " . $e->getMessage());
    // Em caso de erro de banco, volta para a index
    header('Location: index.php');
    exit();
}

// 4. Lógica de usuário logado (para o header)
$usuario_logado = isset($_SESSION['usuario_nome']);
$nome_usuario = $usuario_logado ? explode(' ', $_SESSION['usuario_nome'])[0] : '';
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
            data-id="<?php echo $produto['id']; ?>" data-title="<?php echo htmlspecialchars($produto['nome']); ?>" 
            data-price="<?php echo htmlspecialchars($produto['preco']); ?>" 
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
          <span class="old" id="produto-preco-antigo"></span> <span class="price" id="produto-preco">R$ <?php echo number_format($produto['preco'], 2, ',', '.'); ?></span>
          <span class="badge" id="produto-badge"></span> </div>

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
            <li>Compatível com outros conjuntos LEGO.</li>
            <li>Inclui 3 minifiguras.</li>
            <li>Recomendado para maiores de 8 anos.</li>
            <li>Material: Plástico ABS.</li>
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
          <div class="caracteristica-item">
            <span>Marca</span>
            <span>LEGO</span>
          </div>
          <div class="caracteristica-item">
            <span>Linha</span>
            <span>NINJAGO</span>
          </div>
          <div class="caracteristica-item">
            <span>Modelo</span>
            <span>71741</span>
          </div>
          <div class="caracteristica-item">
            <span>Tipo de alimentação</span>
            <span>N/A</span>
          </div>
          <div class="caracteristica-item">
            <span>Cor</span>
            <span>Colorido</span>
          </div>
        </div>
      </section>

      <section id="descricao" class="detalhe-bloco">
        <h2>Descrição do produto</h2>
        <p>
          <?php echo nl2br(htmlspecialchars($produto['descricao'])); // nl2br preserva quebras de linha ?>
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
  
  // ***** SUA CORREÇÃO ESTÁ AQUI (PASSO 1) *****
  // Buscamos o estoque máximo que o PHP já imprimiu na página.
  const estoqueMaximo = <?php echo $produto['estoque']; ?>;
  
  let quantidade = 1; // Variável global da quantidade

  const btnComprar = document.getElementById("btn-comprar");
  const btnAdicionar = document.getElementById("btn-adicionar-carrinho");
  const btnFavoritar = document.getElementById("btn-favoritar");

  // --- 2. LÓGICA DA GALERIA ---
  function mudarImagem(thumbElement) {
    // Define a imagem principal
    imagemPrincipal.src = thumbElement.src;
    
    // Remove a classe 'active' de todas as thumbnails
    thumbnails.forEach(thumb => thumb.classList.remove("active"));
    
    // Adiciona a classe 'active' apenas na clicada
    thumbElement.classList.add("active");
  }

  // ***** SUA CORREÇÃO ESTÁ AQUI (PASSO 2) *****
  // --- 3. LÓGICA DO CONTADOR DE QUANTIDADE (CORRIGIDA) ---
  // Esta função é a que o seu HTML chama no 'onclick'
  function alterarContador(valor) {
    // Calcula a nova quantidade ANTES de aplicar
    let novaQuantidade = quantidade + valor;

    // 1. Verifica o limite mínimo
    if (novaQuantidade < 1) {
      novaQuantidade = 1;
      
    // 2. Verifica o limite máximo (o estoque)
    } else if (novaQuantidade > estoqueMaximo) {
      novaQuantidade = estoqueMaximo; // Trava no máximo
      // alert("Quantidade máxima em estoque atingida."); // Avisa o usuário
    
    }
    
    // 3. Atualiza a variável global e o texto na tela
    quantidade = novaQuantidade;
    contadorNumero.innerText = quantidade;
  }

  // --- 4. FUNÇÃO AUXILIAR PARA PEGAR DADOS DO PRODUTO ---
  // Esta função agora lê os dados que o script (item 0) preencheu
  function getDadosProduto() {
    return {
      id: container.dataset.id, // <-- ADICIONE ESTA LINHA
      title: container.dataset.title,
      price: parseFloat(container.dataset.price),
      img: container.dataset.img,
      quantidade: quantidade // Pega a quantidade do contador
    };
  }

  // --- 5. LÓGICA DAS AÇÕES (CARRINHO E FAVORITOS) ---
  // (Esta parte permanece a mesma da resposta anterior)

  // Botão "Adicionar ao carrinho"
  btnAdicionar.addEventListener("click", () => {
    const produto = getDadosProduto();
    let carrinho = JSON.parse(localStorage.getItem("carrinho")) || [];

    // Verifica se o produto já existe
    const existente = carrinho.find(p => p.title === produto.title);

    if (existente) {
      existente.quantidade += produto.quantidade;
    } else {
      carrinho.push(produto);
    }

    localStorage.setItem("carrinho", JSON.stringify(carrinho));

    // Redireciona imediatamente para o carrinho
    window.location.href = "tela_carrinho.php"; // <-- Linha alterada
  });

  // Botão "Comprar agora" (Verifica login, Adiciona e Redireciona)
  btnComprar.addEventListener("click", () => {
    
    // 1. Pega os dados do produto atual (incluindo a quantidade)
    const produto = getDadosProduto();
    
    // 2. Calcula o total APENAS para este item
    const totalDaCompra = produto.price * produto.quantidade;

    // 3. Salva esse total no localStorage para a tela_entrega ler
    // (usa a mesma chave que o 'tela_carrinho.html' usa)
    localStorage.setItem("totalCompra", totalDaCompra.toFixed(2));
    
    // 4. Redireciona para a tela de entrega
      window.location.href = "tela_entrega.php";
  });

  // Botão "Favoritar" (Alterna Salvar/Remover)
  btnFavoritar.addEventListener("click", (e) => {
    const produto = getDadosProduto();
    // Define a quantidade padrão para favoritos
    produto.quantidade = 1; 
    
    let salvos = JSON.parse(localStorage.getItem("salvos")) || [];

    // Procura o ÍNDICE do produto na lista de salvos
    const indexExistente = salvos.findIndex(p => p.title === produto.title);

    if (indexExistente === -1) {
      // --- SE NÃO EXISTE: ADICIONA (FAVORITA) ---
      salvos.push(produto);
      
      // Altera o visual do botão
      btnFavoritar.classList.add("active");
      
    } else {
      // --- SE JÁ EXISTE: REMOVE (DESFAVORITA) ---
      salvos.splice(indexExistente, 1); // Remove o item pelo índice
      
      // Altera o visual do botão
      btnFavoritar.classList.remove("active");
    }
    
    // Salva o array atualizado (adicionado ou removido) no localStorage
    localStorage.setItem("salvos", JSON.stringify(salvos));
  });

  // --- 6. LÓGICA DE AVALIAÇÃO (ESTRELAS) ---
  const stars = document.querySelectorAll(".produto-avaliacao .star");
  const starContainer = document.querySelector(".produto-avaliacao");
  
  // Variável para guardar a nota que foi clicada
  let currentRating = 0; 

  /**
   * Função para desenhar as estrelas
   * @param {number} rating - A nota (1 a 5) a ser exibida
   */
  function drawStars(rating) {
    stars.forEach(star => {
      const starValue = parseInt(star.dataset.value);
      
      if (starValue <= rating) {
        star.classList.add("filled");
        star.innerHTML = "&#9733;"; // Ícone de estrela cheia
      } else {
        star.classList.remove("filled");
        star.innerHTML = "&#9734;"; // Ícone de estrela vazia
      }
    });
  }

  // Adiciona eventos de HOVER (mouse enter) para cada estrela
  stars.forEach(star => {
    star.addEventListener("mouseover", () => {
      const rating = parseInt(star.dataset.value);
      drawStars(rating); // Preenche as estrelas até a que está em hover
    });

    // Adiciona evento de CLIQUE para cada estrela
    star.addEventListener("click", () => {
      currentRating = parseInt(star.dataset.value); // Salva a nota
      
      // Salva a nota no localStorage
      localStorage.setItem("rating_" + container.dataset.title, currentRating);
      
      drawStars(currentRating); // Trava a nota clicada
    });
  });

  // Adiciona evento de MOUSE LEAVE (mouse out) no CONTAINER
  starContainer.addEventListener("mouseleave", () => {
    // Quando o mouse sai, volta para a nota que foi CLICADA (currentRating)
    drawStars(currentRating);
  });

  // O script "DOMContentLoaded" que eu te passei antes foi REMOVIDO
  // pois ele não era compatível com seu HTML. Esta versão está correta.

</script>
</body>
</html>