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

  // 1. Carrega ambos os carrinhos
  let carrinho_bd = <?php echo json_encode($carrinho_bd); ?>;
  let carrinho_ls = JSON.parse(localStorage.getItem("carrinho")) || [];

  // 2. O 'carrinho' final começa sendo o do banco de dados (nossa base)
  let carrinho = carrinho_bd;

  // 3. Se o localStorage TEM itens, precisamos mesclá-los
  if (carrinho_ls.length > 0) {
      
      // Criamos um mapa do carrinho do BD para facilitar a busca (usando 'id')
      // [tela_produto.php] e [tela_carrinho.php] garantem que 'id' existe.
      const mapaCarrinhoBD = new Map();
      carrinho.forEach(item => mapaCarrinhoBD.set(item.id, item));

      // 4. Itera sobre o carrinho do localStorage
      carrinho_ls.forEach(item_ls => {
          
          if (mapaCarrinhoBD.has(item_ls.id)) {
              // Item JÁ EXISTE no carrinho do BD.
              // Atualiza a quantidade (o localStorage é mais recente)
              mapaCarrinhoBD.get(item_ls.id).quantidade = item_ls.quantidade;
          } else {
              // Item NÃO EXISTE no carrinho do BD.
              // Adiciona o item do localStorage ao 'carrinho' final.
              carrinho.push(item_ls);
          }
      });
  }
  
  // 5. Neste ponto, 'carrinho' contém a lista mesclada (BD + LS).
  // Salva a lista mesclada de volta no localStorage para manter a consistência.
  localStorage.setItem("carrinho", JSON.stringify(carrinho));
  
  // ===== CORREÇÃO =====
  // A variável 'salvos' deve ser declarada aqui, no escopo principal,
  // para que 'salvarDepois' e 'removerDosSalvos' possam usá-la.
  let salvos = JSON.parse(localStorage.getItem("salvos")) || [];
  // ===== FIM DA CORREÇÃO =====

  // === FIM DA NOVA LÓGICA DE MERGE ===

  // (Seletores ... painelCarrinho, tabCarrinho, etc. ... permanecem os mesmos)
  const painelCarrinho = document.getElementById("painel-carrinho");
  const painelSalvos = document.getElementById("painel-salvos"); 
  const tabCarrinho = document.getElementById("tab-carrinho"); 
  const tabSalvos = document.getElementById("tab-salvos"); 
  const resumoContainer = document.querySelector(".resumo-container"); 
  const totalSpan = document.getElementById("valor-total");
  const separador = document.getElementById("resumo-separador");
  const totalContainer = document.getElementById("total-container");
  const btnContinuar = document.querySelector(".btn-continuar");

  // --- 2. FUNÇÕES DE RENDERIZAÇÃO ---
  
  // ===== CORREÇÃO: Função 'renderCarrinho' duplicada removida =====
  // A versão incompleta foi apagada. Esta é a versão correta.

  // Renderiza os itens do CARRINHO (CORRIGIDA)
  function renderCarrinho() {
    if (carrinho.length === 0) {
      painelCarrinho.innerHTML = "<p style=\"display: flex; align-items: center; justify-content: left;\"><img src=\"imagens/carrinho.png\" alt=\"\" style=\"width: 30px; height: 30px; margin-right: 5px;\"> Nenhum produto no carrinho. </p>";    
      
      // Reseta os estilos que o 'else' aplica
      painelCarrinho.style.flexDirection = "initial"; 
      painelCarrinho.style.gap = "0";
    } else {
      // Adiciona 'gap' para espaçar os produtos
      painelCarrinho.style.display = "flex"; // Garante que é flex
      painelCarrinho.style.flexDirection = "column";
      painelCarrinho.style.gap = "15px";

      painelCarrinho.innerHTML = carrinho.map((produto, index) => {
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
  }

  // ===== CORREÇÃO: Função 'renderSalvos' duplicada removida =====
  // A versão incompleta foi apagada. Esta é a versão correta.

  // Renderiza os itens SALVOS (CORRIGIDA)
  function renderSalvos() {
    if (salvos.length === 0) {
      painelSalvos.innerHTML = "<p>Nenhum item salvo para depois.</p>";

      // Reseta os estilos que o 'else' aplica
      painelSalvos.style.flexDirection = "initial";
      painelSalvos.style.gap = "0";
    } else {
      // Adiciona 'gap' para espaçar os produtos
      painelSalvos.style.display = "flex"; // Garante que é flex
      painelSalvos.style.flexDirection = "column";
      painelSalvos.style.gap = "15px";

      painelSalvos.innerHTML = salvos.map((produto, index) => {
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
  }


  // --- 3. FUNÇÕES DE ATUALIZAÇÃO (Total e Contadores) ---
  // ... (funções atualizarTotal() e atualizarContadoresTabs() permanecem as mesmas) ...
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

  // --- 4. FUNÇÕES DE AÇÃO DOS PRODUTOS ---
  
  // ===== CORREÇÃO: Funções movidas para o escopo global (window) =====
  
  window.alterarContador = function(index, valor) {
    const item = carrinho[index];
    item.quantidade += valor;
    if (item.quantidade < 1) item.quantidade = 1;

    localStorage.setItem("carrinho", JSON.stringify(carrinho));

    document.getElementById(`contador${index}`).textContent = item.quantidade;
    document.getElementById(`preco${index}`).textContent = "R$ " + (item.price * item.quantidade).toFixed(2);

    atualizarTotal();
  }

  // Remove do Carrinho (ATUALIZADA)
  window.removerProduto = function(index) {
    carrinho.splice(index, 1);
    localStorage.setItem("carrinho", JSON.stringify(carrinho));
    
    renderCarrinho(); // Atualiza o conteúdo
    atualizarContadoresTabs(); // Atualiza a aba
    mostrarCarrinho(); // Garante que a visão (painel + resumo) está correta
  }

  // Remove dos Salvos (ATUALIZADA)
  window.removerDosSalvos = function(index) {
    salvos.splice(index, 1);
    localStorage.setItem("salvos", JSON.stringify(salvos));
    
    renderSalvos(); // Atualiza o conteúdo
    atualizarContadoresTabs(); // Atualiza a aba
    mostrarSalvos(); // Garante que a visão (painel) está correta
  }

  window.comprarAgora = function(index) {
    // 1. Pega o produto específico do carrinho
    const produto = carrinho[index];
    
    // 2. Calcula o total APENAS para este item (preço * quantidade)
    const totalDoProduto = produto.price * produto.quantidade;
    
    // 3. Salva esse total no localStorage (mesma chave que a tela_entrega usa)
    localStorage.setItem("totalCompra", totalDoProduto.toFixed(2));
    
    // 4. Redireciona para a tela de entrega
    // MODIFICADO: Apontando para o .php correto
    window.location.href = "tela_entrega.php"; 
  }

  // "Salvar para depois" (CORRIGIDA)
  window.salvarDepois = function(index) {
    const item = carrinho[index];
    
    // Agora usa a variável 'salvos' GLOBAL
    const existenteEmSalvos = salvos.find(p => p.title === item.title);

    if (!existenteEmSalvos) {
      salvos.push(item);
    }

    carrinho.splice(index, 1); // Remove do carrinho (array global 'carrinho')
    
    localStorage.setItem("carrinho", JSON.stringify(carrinho));
    localStorage.setItem("salvos", JSON.stringify(salvos)); // Salva o array GLOBAL 'salvos'
    
    renderCarrinho(); // Renderiza o 'carrinho' global (correto)
    renderSalvos();   // Renderiza o 'salvos' global (que acabamos de atualizar)
    atualizarContadoresTabs();
    mostrarCarrinho(); 
  }
  
  // Mover dos Salvos para o Carrinho (ATUALIZADA)
  window.moverParaCarrinho = function(index) {
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
    mostrarSalvos(); // Garante que a visão (painel) é atualizada
  }
  
  // ===== FIM DAS CORREÇÕES 'window.' =====

  // --- 5. LÓGICA DAS ABAS (Funções principais de visão) ---
  // ... (funções mostrarCarrinho() e mostrarSalvos() permanecem as mesmas) ...
  function mostrarCarrinho() {
    painelCarrinho.style.display = "block"; // Mostra painel carrinho
    painelSalvos.style.display = "none";   // Esconde painel salvos
    
    tabCarrinho.classList.add("active");
    tabSalvos.classList.remove("active");
    
    resumoContainer.style.display = "block"; // Mostra o resumo
    atualizarTotal(); // Atualiza o total (que pode ficar visível ou não)
  }

  function mostrarSalvos() {
    painelCarrinho.style.display = "none";  // Esconde painel carrinho
    painelSalvos.style.display = "block"; // Mostra painel salvos
    
    tabCarrinho.classList.remove("active");
    tabSalvos.classList.add("active");
    
    resumoContainer.style.display = "none"; // Esconde o resumo
  }

  tabCarrinho.addEventListener("click", mostrarCarrinho);
  tabSalvos.addEventListener("click", mostrarSalvos);


  // --- 6. AÇÃO DO BOTÃO "CONTINUAR" ---
  // O seu código para o btnContinuar já está 100% CORRETO.
  // Ele já usa o 'fetch' para 'sincronizar_carrinho.php'
  // e já redireciona para tela_login.html se não estiver logado.
  // A única diferença é que agora o array 'carrinho' (se logado)
  // contém o 'produto.id' vindo do PHP, o que faz o 'sincronizar_carrinho.php' funcionar.
  btnContinuar.addEventListener("click", function() {
      if (carrinho.length === 0) {
          alert("Seu carrinho está vazio.");
          return;
      }
      
      fetch('Banco de dados/sincronizar_carrinho.php', { //
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ carrinho: carrinho }) 
      })
      .then(response => response.json())
      .then(data => {
          if (data.sucesso) {
              window.location.href = "tela_entrega.php"; //
          } else {
              if (data.erro === 'nao_logado') { //
                   alert("Você precisa estar logado para continuar.");
                   sessionStorage.setItem('redirecionarPara', 'tela_carrinho.php');
                   window.location.href = "tela_login.html"; //
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
}); // Fim do DOMContentLoaded // <-- LINHA ADICIONADA

</script> 

  <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>

  <script>
    document.addEventListener('DOMContentLoaded', function () {
      
      // --- A. INICIALIZA O SWIPER DE RECOMENDAÇÕES ---
      const swiper = new Swiper('.recomendacoes-swiper', {
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

      // --- B. LÓGICA DE CLIQUE DO CARD (Copiado de script.js) ---
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

          const produtoSelecionado = {
            title: title,
            price: priceText,
            priceValue: parseFloat(priceValue),
            oldPrice: oldPrice,
            badge: badge,
            img: img
          };
          
          localStorage.setItem("produtoSelecionado", JSON.stringify(produtoSelecionado));
        });
      });
      
      // --- C. LÓGICA DAS ESTRELAS (Copiado de script.js) ---
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

    });
  </script>



</body>
</html>