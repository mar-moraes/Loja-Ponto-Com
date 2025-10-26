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

    <script>
  // --- 1. RECUPERAÇÃO DE DADOS E SELETORES ---
  
  // === INÍCIO DA MODIFICAÇÃO (CARREGAMENTO DO CARRINHO) ===
  // Tenta carregar o carrinho do PHP (se logado)
  let carrinho_bd = <?php echo json_encode($carrinho_bd); ?>;
  
  // Se o carrinho do BD não estiver vazio, use-o.
  // Se estiver vazio (não logado ou carrinho vazio), tente o localStorage.
  let carrinho = (carrinho_bd.length > 0) ? carrinho_bd : (JSON.parse(localStorage.getItem("carrinho")) || []);
  // === FIM DA MODIFICAÇÃO ===
  
  let salvos = JSON.parse(localStorage.getItem("salvos")) || []; 

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
  // (Suas funções renderCarrinho() e renderSalvos() estão corretas e podem permanecer)
  // ...
  function renderCarrinho() {
    if (carrinho.length === 0) {
      painelCarrinho.innerHTML = "<p style=\"display: flex; align-items: center; justify-content: left;\"><img src=\"imagens/carrinho.png\" alt=\"\" style=\"width: 30px; height: 30px; margin-right: 5px;\"> Nenhum produto no carrinho. </p>";    
      painelCarrinho.style.flexDirection = "initial"; 
      painelCarrinho.style.gap = "0";
    } else {
      painelCarrinho.style.display = "flex"; 
      painelCarrinho.style.flexDirection = "column";
      painelCarrinho.style.gap = "15px";
      painelCarrinho.innerHTML = carrinho.map((produto, index) => {
        // O PHP já nos dá o 'price' como número
        const price = typeof produto.price === "number" ? produto.price : 0;
        const quantidade = typeof produto.quantidade === "number" ? produto.quantidade : 1;
        // Garante que o ID do produto (vindo do BD) está no contador
        return `
            <div class="produto" ...>
              <img src="${produto.img || ''}" ...>
              <div ...>
                <div ...>
                  <p ...>${produto.title || ""}</p>
                  <div ...>
                    <div class="contador-container">
                      <button type="button" onclick="alterarContador(${index}, -1)">-</button>
                      <div id="contador${index}" ...>${quantidade}</div>
                      <button type="button" onclick="alterarContador(${index}, 1)">+</button>
                    </div>
                    <span id="preco${index}" ...>R$ ${(price * quantidade).toFixed(2)}</span>
                  </div>
                </div>
                <div ...>
                  <button type="button" onclick="salvarDepois(${index})" ...>Salvar para depois</button>
                  <button type="button" onclick="removerProduto(${index})" ...>Excluir</button>
                </div>
              </div>
            </div>
        `;
      }).join("");
    }
  }
  // (renderSalvos, atualizarTotal, atualizarContadoresTabs, alterarContador, etc... permanecem)
  // ...

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
</script> 

</body>
</html>