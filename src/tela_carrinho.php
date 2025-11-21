<?php
session_start();
require 'Banco de dados/conexao.php'; // Inclui a conexão

$usuario_logado = isset($_SESSION['usuario_nome']);
$nome_usuario = $usuario_logado ? explode(' ', $_SESSION['usuario_nome'])[0] : '';
$carrinho_bd = []; // Array que guardará o carrinho vindo do BD
$endereco_padrao = null; // Garante que a variável exista para o HTML

// (O bloco 'try/catch' que estava aqui foi MOVIDO)

if ($usuario_logado) {
    // 1. DEFINA O ID DO USUÁRIO PRIMEIRO
    $usuario_id = $_SESSION['usuario_id'];
    
    // 2. AGORA BUSQUE O ENDEREÇO (BLOCO MOVIDO PARA CÁ)
    try {
        // Busca o último endereço cadastrado pelo usuário
        $stmt_addr = $pdo->prepare("SELECT * FROM ENDERECOS WHERE usuario_id = ? ORDER BY id DESC LIMIT 1");
        $stmt_addr->execute([$usuario_id]); // <-- Agora $usuario_id existe
        $endereco_padrao = $stmt_addr->fetch(PDO::FETCH_ASSOC);

        // Se um endereço foi encontrado, usa o nome do destinatário dele
        if ($endereco_padrao && !empty($endereco_padrao['destinatario_nome'])) {
            $nome_usuario = $endereco_padrao['destinatario_nome'];
        }

    } catch (PDOException $e) {
        error_log("Erro ao buscar endereço: " . $e->getMessage());
    }
    
    // 3. A BUSCA DE CARRINHO (QUE JÁ ESTAVA AQUI) CONTINUA ABAIXO
    try {
        // 1. Busca o ID do carrinho principal do usuário
        $stmt_cart = $pdo->prepare("SELECT id FROM CARRINHO WHERE usuario_id = ?");
        $stmt_cart->execute([$usuario_id]);
        $carrinho = $stmt_cart->fetch();

        if ($carrinho) {
            $carrinho_id = $carrinho['id'];
            
            // 2. Busca os itens desse carrinho E os dados dos produtos
            $sql_itens = "SELECT p.id, p.nome as title, p.preco as price, p.imagem_url as img, ci.quantidade
                          FROM CARRINHO_ITENS ci
                          JOIN PRODUTOS p ON ci.produto_id = p.id
                          WHERE ci.carrinho_id = ?";
            $stmt_itens = $pdo->prepare($sql_itens);
            $stmt_itens->execute([$carrinho_id]);
            $itens = $stmt_itens->fetchAll(PDO::FETCH_ASSOC);
            
            // 3. Formata o array para o JavaScript entender (igual ao formato do localStorage)
            foreach ($itens as $item) {
                $carrinho_bd[] = [
                    'id' => (int) $item['id'], // ID DO PRODUTO (Necessário para sincronizar_carrinho.php)
                    'title' => $item['title'],
                    'price' => (float) $item['price'],
                    'img' => $item['img'] ?? 'imagens/placeholder.png',
                    'quantidade' => (int) $item['quantidade']
                ];
            }
        }
    } catch (PDOException $e) {
        error_log("Erro ao buscar carrinho do BD: " . $e->getMessage());
    }
}
// Se não está logado, $carrinho_bd continuará sendo um array vazio.
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <title>Tela de Compras</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="estilos/style.css"/>
  <link rel="stylesheet" href="estilos/estilo_carrinho.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />

  <style>
    .carrinho-tabs {
      display: flex;
      gap: 10px;
      border-bottom: 1px solid #ddd;
    }
    .carrinho-tab {
      font-size: 16px;
      font-weight: 500;
      color: #666;
      background: none;
      border: none;
      padding: 10px 15px;
      cursor: pointer;
      border-bottom: 3px solid transparent;
    }
    .carrinho-tab.active {
      color: #2968C8; 
      border-bottom: 3px solid #2968C8;
    }

    /* O estilo .filtro-local-input foi REMOVIDO daqui */

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
        
        <div style="position: relative; width: 600px; max-width: 100%;">
          <input type="search" id="pesquisa" placeholder="Buscar no carrinho/salvos..." style="font-size: 16px; width: 100%; height: 40px; padding-left: 15px; padding-right: 45px; border-radius: 6px; border: none; box-sizing: border-box;">
          <button type="button" style="position: absolute; right: 0; top: 0; height: 40px; width: 45px; border: none; background: transparent; cursor: pointer; display: flex; align-items: center; justify-content: center;">
            <img src="imagens/lupa.png" alt="lupa" style="width: 28px; height: 28px; opacity: 0.6;">
          </button>
        </div>
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

    <div class="carrinho-layout-container">
    
      <div style="flex: 1; min-width: 400px;">
    
        <div class="carrinho-tabs">
          <button id="tab-carrinho" class="carrinho-tab active">Carrinho (0)</button>
          <button id="tab-salvos" class="carrinho-tab">Salvos (0)</button>
        </div>

        <div class="painel" id="painel-carrinho" style="margin-top: 20px;">
          </div>
        
        <div class="painel" id="painel-salvos" style="display: none; margin-top: 20px;">
          </div>
        
      </div> 
      
      <div class="resumo-container">
        <h3>Resumo da compra</h3>
        <div id="resumo-separador" class="resumo-divisor" style="display: none;"></div>
        <div id="total-container" class="resumo-linha total" style="display: none;">
            <span>Total</span>
            <span id="valor-total">R$ 0,00</span>
        </div>
        <button class="btn-continuar" style="display: none;">Continuar</button>
      </div>
    
    </div>

    <section class="recomendacoes-container">
        <h2>Produtos que podem interessar</h2>
        <div class="swiper recomendacoes-swiper">
            <div class="swiper-wrapper">
                </div>
            <div class="swiper-button-prev"></div>
            <div class="swiper-button-next"></div>
        </div>
    </section>

  
    <script>
  document.addEventListener("DOMContentLoaded", function() {
  // --- 1. RECUPERAÇÃO DE DADOS E SELETORES ---
  
  // === INÍCIO DA NOVA LÓGICA DE MERGE ===
  let carrinho_bd = <?php echo json_encode($carrinho_bd); ?>;
  let carrinho_ls = JSON.parse(localStorage.getItem("carrinho")) || [];
  let carrinho = carrinho_bd;

  if (carrinho_ls.length > 0) {
      const mapaCarrinhoBD = new Map();
      carrinho.forEach(item => mapaCarrinhoBD.set(item.id, item));

      carrinho_ls.forEach(item_ls => {
          if (mapaCarrinhoBD.has(item_ls.id)) {
              mapaCarrinhoBD.get(item_ls.id).quantidade = item_ls.quantidade;
          } else {
              carrinho.push(item_ls);
          }
      });
  }
  
  localStorage.setItem("carrinho", JSON.stringify(carrinho));
  let salvos = JSON.parse(localStorage.getItem("salvos")) || [];
  // === FIM DA NOVA LÓGICA DE MERGE ===

  // Seletores dos painéis e abas
  const painelCarrinho = document.getElementById("painel-carrinho");
  const painelSalvos = document.getElementById("painel-salvos"); 
  const tabCarrinho = document.getElementById("tab-carrinho"); 
  const tabSalvos = document.getElementById("tab-salvos"); 
  
  // === SELETORES DE FILTRO REMOVIDOS ===
  // const filtroCarrinhoContainer = ... (REMOVIDO)
  // const filtroSalvosContainer = ... (REMOVIDO)
  // const filtroCarrinhoInput = ... (REMOVIDO)
  // const filtroSalvosInput = ... (REMOVIDO)
  
  // === NOVO SELETOR (Filtro do Header) ===
  const headerSearchInput = document.getElementById('pesquisa');
  
  // Seletores do resumo
  const resumoContainer = document.querySelector(".resumo-container"); 
  const totalSpan = document.getElementById("valor-total");
  const separador = document.getElementById("resumo-separador");
  const totalContainer = document.getElementById("total-container");
  const btnContinuar = document.querySelector(".btn-continuar");

  // --- 2. FUNÇÕES DE RENDERIZAÇÃO ---
  
  // Renderiza os itens do CARRINHO
  function renderCarrinho() {
    // Linha de limpar filtro foi REMOVIDA
      
    if (carrinho.length === 0) {
      painelCarrinho.innerHTML = "<p style=\"display: flex; align-items: center; justify-content: left;\"><img src=\"imagens/carrinho.png\" alt=\"\" style=\"width: 30px; height: 30px; margin-right: 5px;\"> Nenhum produto no carrinho. </p>";    
      painelCarrinho.style.flexDirection = "initial"; 
      painelCarrinho.style.gap = "0";
    } else {
      painelCarrinho.style.display = "flex"; 
      painelCarrinho.style.flexDirection = "column";
      painelCarrinho.style.gap = "15px";

      painelCarrinho.innerHTML = carrinho.map((produto, index) => {
        // ... (código interno do .map() permanece o mesmo) ...
        const imgSrc = typeof produto.img === "string" ? produto.img.replace('url("', '').replace('")', '') : "";
        const quantidade = typeof produto.quantidade === "number" ? produto.quantidade : 1;
        const price = typeof produto.price === "number" ? produto.price : 0;
        return `
            <div class="produto" style="display: flex; align-items: center; gap: 10px; border-radius: 8px; border: 1px solid #e0e0e0; padding: 10px;">
              <img src="${imgSrc}" alt="Produto" style="width:80px; height:80px; object-fit:cover;">
              <div style="display: flex; flex-direction: column; gap: 6px; flex: 1;">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                  <p style="margin: 0;">${produto.title || ""}</p>
                  <div style="display: flex; align-items: center; gap: 10px;">
                    <div class="contador-container">
                      <button type="button" onclick="alterarContador(${index}, -1)">-</button>
                      <div id="contador${index}" class="contador-numero" style="color: black; font-weight:500; ">${quantidade}</div>
                      <button type="button" onclick="alterarContador(${index}, 1)">+</button>
                    </div>
                    <span id="preco${index}" style="color: black; white-space: nowrap; margin-right: 10px;">R$ ${(price * quantidade).toFixed(2)}</span>
                  </div>
                </div>
                <div style="display: flex; align-items: center; gap: 15px; margin-top: 4px;">
                  <button type="button" onclick="comprarAgora(${index})" style="color: blue; background: none; border: none; padding: 0; cursor: pointer; font-size: 12px;">Comprar agora</button>
                  <button type="button" onclick="salvarDepois(${index})" style="color: blue; background: none; border: none; border-left: 1px solid rgba(0,0,0,0.15); border-right: 1px solid rgba(0,0,0,0.15); padding: 0 8px; cursor: pointer; font-size: 12px; outline: none;">Salvar para depois</button>
                  <button type="button" onclick="removerProduto(${index})" style="color: blue; background: none; border: none; padding: 0; cursor: pointer; font-size: 12px;">Excluir</button>
                </div>
              </div>
            </div>
        `;
      }).join("");
    }
    
    aplicarFiltroLocal(); // <-- Re-aplica o filtro após renderizar
  }

  // Renderiza os itens SALVOS
  function renderSalvos() {
    // Linha de limpar filtro foi REMOVIDA
      
    if (salvos.length === 0) {
      painelSalvos.innerHTML = "<p>Nenhum item salvo para depois.</p>";
      painelSalvos.style.flexDirection = "initial";
      painelSalvos.style.gap = "0";
    } else {
      painelSalvos.style.display = "flex"; 
      painelSalvos.style.flexDirection = "column";
      painelSalvos.style.gap = "15px";

      painelSalvos.innerHTML = salvos.map((produto, index) => {
        // ... (código interno do .map() permanece o mesmo) ...
        const imgSrc = typeof produto.img === "string" ? produto.img.replace('url("', '').replace('")', '') : "";
        const price = typeof produto.price === "number" ? produto.price : 0;
        return `
          <div class="produto" style="display: flex; align-items: center; gap: 10px; border-radius: 8px; border: 1px solid #e0e0e0; padding: 10px;">
            <img src="${imgSrc}" alt="Produto" style="width:80px; height:80px; object-fit:cover;">
            <div style="display: flex; flex-direction: column; gap: 6px; flex: 1;">
              <div style="display: flex; justify-content: space-between; align-items: center;">
                <p style="margin: 0;">${produto.title || ""}</p>
                <span style="color: black; white-space: nowrap; margin-right: 10px;">R$ ${price.toFixed(2)}</span>
              </div>
              <div style="display: flex; align-items: center; gap: 15px; margin-top: 4px;">
                <button type="button" onclick="moverParaCarrinho(${index})" style="color: blue; background: none; border: none; padding: 0; cursor: pointer; font-size: 12px;">Mover para o carrinho</button>
                <button type="button" onclick="removerDosSalvos(${index})" style="color: blue; background: none; border: none; padding: 0 8px; border-left: 1px solid rgba(0,0,0,0.15); cursor: pointer; font-size: 12px;">Excluir</button>
              </div>
            </div>
          </div>
        `;
      }).join("");
    }
    
    aplicarFiltroLocal(); // <-- Re-aplica o filtro após renderizar
  }


  // --- 3. FUNÇÕES DE ATUALIZAÇÃO (Total e Contadores) ---
  
  // Atualiza o TOTAL (bloco de resumo)
  function atualizarTotal() {
    if (carrinho.length === 0) {
      separador.style.display = "none";
      totalContainer.style.display = "none";
      btnContinuar.style.display = "none";
    } else {
      const total = carrinho.reduce((acc, p) => acc + (p.price * p.quantidade), 0);
      separador.style.display = "block";
      totalContainer.style.display = "flex";
      btnContinuar.style.display = "block"; 
      totalSpan.innerText = "R$ " + total.toFixed(2).replace(".", ",");
    }
  }

  // Atualiza os contadores nas ABAS
  function atualizarContadoresTabs() {
    tabCarrinho.innerText = `Carrinho (${carrinho.length})`;
    tabSalvos.innerText = `Salvos (${salvos.length})`;
  }

  // --- 4. FUNÇÕES DE AÇÃO DOS PRODUTOS (Globais) ---
  
  window.alterarContador = function(index, valor) {
    // ... (função sem alteração) ...
    const item = carrinho[index];
    item.quantidade += valor;
    if (item.quantidade < 1) item.quantidade = 1;

    localStorage.setItem("carrinho", JSON.stringify(carrinho));

    document.getElementById(`contador${index}`).textContent = item.quantidade;
    document.getElementById(`preco${index}`).textContent = "R$ " + (item.price * item.quantidade).toFixed(2);

    atualizarTotal();
  }

  window.removerProduto = function(index) {
    // ... (função sem alteração) ...
    carrinho.splice(index, 1);
    localStorage.setItem("carrinho", JSON.stringify(carrinho));
    
    renderCarrinho(); 
    atualizarContadoresTabs(); 
    mostrarCarrinho(); 
  }

  window.removerDosSalvos = function(index) {
    // ... (função sem alteração) ...
    salvos.splice(index, 1);
    localStorage.setItem("salvos", JSON.stringify(salvos));
    
    renderSalvos(); 
    atualizarContadoresTabs(); 
    mostrarSalvos(); 
  }

  window.comprarAgora = function(index) {
    // ... (função sem alteração) ...
    const produto = carrinho[index];
    const totalDoProduto = produto.price * produto.quantidade;
    localStorage.setItem("totalCompra", totalDoProduto.toFixed(2));
    window.location.href = "tela_entrega.php"; 
  }

  window.salvarDepois = function(index) {
    // ... (função sem alteração) ...
    const item = carrinho[index];
    const existenteEmSalvos = salvos.find(p => p.title === item.title);

    if (!existenteEmSalvos) {
      salvos.push(item);
    }

    carrinho.splice(index, 1);
    
    localStorage.setItem("carrinho", JSON.stringify(carrinho));
    localStorage.setItem("salvos", JSON.stringify(salvos)); 
    
    renderCarrinho(); 
    renderSalvos();   
    atualizarContadoresTabs();
    mostrarCarrinho(); 
  }
  
  window.moverParaCarrinho = function(index) {
    // ... (função sem alteração) ...
    const item = salvos[index];
    
    salvos.splice(index, 1);
    
    const existente = carrinho.find(p => p.title === item.title);
    if (existente) {
      existente.quantidade += (item.quantidade || 1);
    } else {
      carrinho.push(item);
    }
    
    localStorage.setItem("carrinho", JSON.stringify(carrinho));
    localStorage.setItem("salvos", JSON.stringify(salvos));
    
    renderCarrinho();
    renderSalvos();
    atualizarContadoresTabs();
    mostrarSalvos(); 
  }
  
  // --- 5. LÓGICA DAS ABAS (Funções principais de visão) ---
  
  // FUNÇÃO ATUALIZADA para incluir os filtros
  function mostrarCarrinho() {
    painelCarrinho.style.display = "block"; // Mostra painel carrinho
    painelSalvos.style.display = "none";   // Esconde painel salvos
    
    // Linhas de mostrar/esconder filtros REMOVIDAS
    
    tabCarrinho.classList.add("active");
    tabSalvos.classList.remove("active");
    
    resumoContainer.style.display = "block"; // Mostra o resumo
    atualizarTotal(); // Atualiza o total (que pode ficar visível ou não)
    
    aplicarFiltroLocal(); // Aplica o filtro na aba correta
  }

  // FUNÇÃO ATUALIZADA para incluir os filtros
  function mostrarSalvos() {
    painelCarrinho.style.display = "none";  // Esconde painel carrinho
    painelSalvos.style.display = "block"; // Mostra painel salvos
    
    // Linhas de mostrar/esconder filtros REMOVIDAS
    
    tabCarrinho.classList.remove("active");
    tabSalvos.classList.add("active");
    
    resumoContainer.style.display = "none"; // Esconde o resumo
    
    aplicarFiltroLocal(); // Aplica o filtro na aba correta
  }

  tabCarrinho.addEventListener("click", mostrarCarrinho);
  tabSalvos.addEventListener("click", mostrarSalvos);


  // === 5.5. LÓGICA DO FILTRO LOCAL (HEADER) (BLOCO SUBSTITUÍDO) ===
  
  // Nova função unificada para aplicar o filtro
  function aplicarFiltroLocal() {
      // Pega o termo da barra de pesquisa do CABEÇALHO
      const termo = headerSearchInput.value.toLowerCase();
      
      // Descobre qual aba está ativa
      const isCarrinhoActive = tabCarrinho.classList.contains('active');
      
      // Define qual painel será filtrado
      let painelAlvo = isCarrinhoActive ? painelCarrinho : painelSalvos;
      
      // Seleciona todos os '.produto' dentro do painel ativo
      const itens = painelAlvo.querySelectorAll('.produto');
      
      itens.forEach(item => {
        // Pega o título dentro do item
        const titulo = item.querySelector('p').innerText.toLowerCase();
        if (titulo.includes(termo)) {
          item.style.display = 'flex'; // Mostra se bate
        } else {
          item.style.display = 'none'; // Esconde se não bate
        }
      });
  }

  // Adiciona o listener para a barra de pesquisa do CABEÇALHO
  headerSearchInput.addEventListener('input', aplicarFiltroLocal);
  
  // === FIM DA LÓGICA DOS FILTROS ===


  // --- 6. AÇÃO DO BOTÃO "CONTINUAR" ---
  btnContinuar.addEventListener("click", function() {
      // ... (função sem alteração) ...
      if (carrinho.length === 0) {
          alert("Seu carrinho está vazio.");
          return;
      }

      const total = carrinho.reduce((acc, p) => acc + (p.price * p.quantidade), 0);
      localStorage.setItem("totalCompra", total.toFixed(2));


      fetch('Banco de dados/sincronizar_carrinho.php', { 
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ carrinho: carrinho }) 
      })
      .then(response => response.json())
      .then(data => {
          if (data.sucesso) {
              window.location.href = "tela_entrega.php"; 
          } else {
              if (data.erro === 'nao_logado') { 
                   alert("Você precisa estar logado para continuar.");
                   sessionStorage.setItem('redirecionarPara', 'tela_carrinho.php');
                   window.location.href = "tela_login.html"; 
              } else {
                   alert("Erro ao salvar seu carrinho: " + data.mensagem);
              }
          }
      })
      .catch(error => {
          console.error("Erro no fetch:", error);
          alert("Erro de conexão. Tente novamente.");
      });
  });

  // --- 7. INICIALIZAÇÃO DA PÁGINA ---
  renderCarrinho();
  renderSalvos();
  atualizarContadoresTabs();
  mostrarCarrinho();
}); // Fim do DOMContentLoaded

</script> 

  <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>

  <script>
    // Script do Swiper e dos Cards de Recomendação
    // (ESTE BLOCO SERÁ MAJORITARIAMENTE SUBSTITUÍDO)
    
    document.addEventListener('DOMContentLoaded', function () {
      
      // --- A. INICIALIZA O SWIPER (MODIFICADO) ---
      // Mudei de 'const' para 'let' para que a função de fetch possa inicializá-lo
      let swiper; 

      function inicializarSwiper() {
          swiper = new Swiper('.recomendacoes-swiper', {
            loop: false,
            spaceBetween: 18, 
            slidesPerView: 2, 
            breakpoints: {
              600: { slidesPerView: 3, spaceBetween: 10 },
              900: { slidesPerView: 4, spaceBetween: 15 },
              1100: { slidesPerView: 5, spaceBetween: 15 } 
            },
            navigation: {
              nextEl: '.swiper-button-next',
              prevEl: '.swiper-button-prev',
            },
          });
      }

      // --- B. LÓGICA DE CLIQUE DO CARD (MOVIMOS PARA UMA FUNÇÃO) ---
      // Esta função será usada para adicionar listeners aos cards *depois* que eles forem carregados
      function adicionarListenersAosCards() {
          document.querySelectorAll(".recomendacoes-container a.card-link").forEach(link => {
            
            link.addEventListener("click", (event) => {
              
              const card = link.querySelector(".card");
              if (!card) return; 

              const title = card.querySelector(".title").innerText;
              const priceText = card.querySelector(".price").innerText;
              const priceValue = card.dataset.price; 
              const oldPrice = card.querySelector(".old")?.innerText || ""; 
              const badge = card.querySelector(".badge")?.innerText || ""; 
              
              const bgImg = card.querySelector(".thumb").style.backgroundImage;
              const imgMatch = bgImg.match(/url\(["']?(.*?)["']?\)/);
              const img = imgMatch ? imgMatch[1] : "";

              // Pegamos o ID do produto do data-id do card
              const id = card.dataset.id;

              const produtoSelecionado = {
                id: parseInt(id), // Adicionamos o ID
                title: title,
                price: parseFloat(priceValue), // Usamos o priceValue numérico
                priceValue: parseFloat(priceValue),
                oldPrice: oldPrice,
                badge: badge,
                img: img
              };
              
              localStorage.setItem("produtoSelecionado", JSON.stringify(produtoSelecionado));
            });
          });
          
          // --- C. LÓGICA DAS ESTRELAS (TAMBÉM MOVEMOS PARA A FUNÇÃO) ---
          const allCards = document.querySelectorAll(".recomendacoes-container .card");

          allCards.forEach(card => {
            const titleElement = card.querySelector(".title");
            const avaliacaoContainer = card.querySelector(".card-avaliacao");

            if (!titleElement || !avaliacaoContainer || !titleElement.innerText) {
              return; // Pula cards de modelo
            }
            
            const title = titleElement.innerText;
            const ratingKey = "rating_" + title; 
            const savedRating = localStorage.getItem(ratingKey);

            if (savedRating) {
              const rating = parseInt(savedRating);
              let starsHTML = "";
              for (let i = 1; i <= 5; i++) {
                if (i <= rating) {
                  starsHTML += '<span class="star filled">&#9733;</span>'; // Cheia
                } else {
                  starsHTML += '<span class="star">&#9734;</span>'; // Vazia
                }
              }
              avaliacaoContainer.innerHTML = starsHTML;
            }
          });
      } // Fim de adicionarListenersAosCards()


      // ==========================================================
      // --- INÍCIO DA NOVA LÓGICA (FETCH RECOMENDAÇÕES) ---
      // ==========================================================
      
      /**
       * Função para buscar recomendações e popular o Swiper
       */
      function carregarRecomendacoes() {
          const wrapper = document.querySelector('.recomendacoes-swiper .swiper-wrapper');
          if (!wrapper) return;
          
          // Coloca um 'loading' temporário
          wrapper.innerHTML = '<p style="text-align: center; width: 100%;">Carregando recomendações...</p>';

          // Chama o novo script PHP
          fetch('Banco de dados/buscar_recomendacoes.php')
              .then(response => response.json())
              .then(data => {
                  if (data.sucesso && data.produtos.length > 0) {
                      let slidesHTML = '';
                      
                      // Itera sobre os produtos retornados
                      data.produtos.forEach(produto => {
                          // Lógica de cálculo de preço (baseada na do index.php)
                          const preco = parseFloat(produto.preco);
                          const desconto = parseInt(produto.desconto);
                          let precoFinal = preco;
                          let precoAntigoHTML = '';
                          let badgeHTML = '';

                          if (desconto > 0) {
                              precoFinal = preco - (preco * (desconto / 100));
                              precoAntigoHTML = `<span class="old">R$ ${preco.toFixed(2).replace('.', ',')}</span>`;
                              badgeHTML = `<div class="badge">${desconto}% OFF</div>`;
                          }

                          const precoFinalFormatado = `R$ ${precoFinal.toFixed(2).replace('.', ',')}`;
                          const imgUrl = produto.imagem_url ? produto.imagem_url : 'imagens/placeholder.png';

                          // Cria o HTML do card
                          // ATENÇÃO: Adicionamos data-id="${produto.id}" ao .card
                          slidesHTML += `
                              <div class="swiper-slide">
                                  <a href="tela_produto.php?id=${produto.id}" class="card-link">
                                      <div class="card" data-id="${produto.id}" data-price="${precoFinal.toFixed(2)}">
                                          ${badgeHTML}
                                          <div class="thumb" style="background-image: url('${imgUrl}');"></div>
                                          <div class="details">
                                              <span class="title">${produto.nome}</span>
                                                <div class="card-avaliacao">
                                                    <?php
                                                    for ($i = 1; $i <= 5; $i++) {
                                                        $classe = ($i <= $nota_para_estrelas) ? 'filled' : '';
                                                        echo '<span class="star ' . $classe . '">&#9733;</span>';
                                                    }
                                                    ?>
                                                </div>
                                              
                                              <div class="price-container">
                                                  ${precoAntigoHTML}
                                                  <span class="price">${precoFinalFormatado}</span>
                                              </div>
                                              
                                          </div> </div> </a> </div>
                          `;
                      });
                      
                      // Insere o HTML no wrapper
                      wrapper.innerHTML = slidesHTML;
                      
                      // (Re)Inicializa o Swiper DEPOIS de adicionar o HTML
                      if (swiper) swiper.destroy(true, true); // Destrói instância anterior, se houver
                      inicializarSwiper(); 
                      
                      // Adiciona os listeners de clique e estrelas aos novos cards
                      adicionarListenersAosCards();
                      


                  } else {
                      wrapper.innerHTML = '<p style="text-align: center; width: 100%;">Não há recomendações no momento.</p>';
                  }
              })
              .catch(error => {
                  console.error("Erro ao buscar recomendações:", error);
                  wrapper.innerHTML = '<p style="text-align: center; width: 100%;">Erro ao carregar recomendações.</p>';
              });
      }

      /**
       * Função para adicionar listeners aos botões "Adicionar ao carrinho" (do carrossel)
       */
      function adicionarListenersBotoesCard() {
          const btnsAdd = document.querySelectorAll('.recomendacoes-container .btn-card-add');
          
          btnsAdd.forEach(btn => {
              btn.addEventListener('click', (e) => {
                  e.preventDefault(); // Impede o link <a> de navegar
                  e.stopPropagation(); // Impede o clique de subir para o card-link
                  
                  const id = parseInt(btn.dataset.id);
                  const title = btn.dataset.title;
                  const price = parseFloat(btn.dataset.price);
                  const img = btn.dataset.img;
                  
                  // Usa o 'carrinho' global do PRIMEIRO script (se existir)
                  // ou pega do localStorage
                  let carrinho;
                  if (typeof window.carrinho !== 'undefined') {
                      carrinho = window.carrinho;
                  } else {
                      carrinho = JSON.parse(localStorage.getItem("carrinho")) || [];
                  }

                  const produtoExistente = carrinho.find(p => p.id === id);

                  if (produtoExistente) {
                      produtoExistente.quantidade++;
                  } else {
                      carrinho.push({ id, title, price, img, quantidade: 1 });
                  }
                  
                  localStorage.setItem("carrinho", JSON.stringify(carrinho));
                  
                  // Tenta chamar as funções globais do PRIMEIRO script para atualizar
                  // a UI do carrinho imediatamente.
                  if (typeof window.atualizarContadoresTabs === 'function') {
                      window.atualizarContadoresTabs(); 
                      window.renderCarrinho(); 
                      window.mostrarCarrinho(); 
                  } else {
                      // Fallback se as funções não estiverem no escopo global
                      const tab = document.getElementById("tab-carrinho");
                      if(tab) tab.innerText = `Carrinho (${carrinho.length})`;
                  }
                  
                  // Animação simples no botão
                  btn.innerText = "Adicionado!";
                  setTimeout(() => { btn.innerText = "Adicionar ao carrinho"; }, 1500);
              });
          }); 
      }

      // --- CHAMA A NOVA FUNÇÃO AO CARREGAR A PÁGINA ---
      carregarRecomendacoes();
      
      // ==========================================================
      // --- FIM DA NOVA LÓGICA ---
      // ==========================================================

    });
  </script>



</body>
</html>