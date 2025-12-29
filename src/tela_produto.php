<?php
session_start();
require '../Banco de dados/conexao.php';

// 1. Validar e pegar o ID da URL
if (!isset($_GET['id']) || !filter_var($_GET['id'], FILTER_VALIDATE_INT)) {
  header('Location: index.php');
  exit();
}
$produto_id = $_GET['id'];

// 2. Buscar o produto principal
try {
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

// 4. Lógica de usuário logado (para o header E avaliações)
$usuario_logado = isset($_SESSION['usuario_id']);
$usuario_id = $_SESSION['usuario_id'] ?? null;
$nome_usuario = $usuario_logado ? explode(' ', $_SESSION['usuario_nome'])[0] : '';


// 5. Parsear a descrição (lógica anterior)
$descricao_completa = $produto['descricao'] ?? '';
$caracteristicas_array = [];
$especificacoes_array = [];
$descricao_texto = '';

$inicio_caract = strpos($descricao_completa, "--- CARACTERÍSTICAS ---");
$inicio_espec = strpos($descricao_completa, "--- ESPECIFICAÇÕES ---");
$inicio_desc = strpos($descricao_completa, "--- DESCRIÇÃO ---");

if ($inicio_desc !== false) {
  $descricao_texto = trim(substr($descricao_completa, $inicio_desc + strlen("--- DESCRIÇÃO ---")));
} else {
  if ($inicio_caract === false && $inicio_espec === false) {
    $descricao_texto = $descricao_completa;
  }
}

if ($inicio_caract !== false) {
  $fim_caract = $inicio_espec !== false ? $inicio_espec : $inicio_desc;
  if ($fim_caract === false) $fim_caract = strlen($descricao_completa);

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

if ($inicio_espec !== false) {
  $fim_espec = $inicio_desc !== false ? $inicio_desc : strlen($descricao_completa);

  $bloco_espec = substr(
    $descricao_completa,
    $inicio_espec + strlen("--- ESPECIFICAÇÕES ---"),
    $fim_espec - ($inicio_espec + strlen("--- ESPECIFICAÇÕES ---"))
  );

  $linhas = explode("\n", trim($bloco_espec));
  foreach ($linhas as $linha) {
    if (!empty(trim($linha))) {
      $especificacoes_array[] = trim($linha);
    }
  }
}

// 6. Lógica de Desconto (lógica anterior)
$preco_original = (float)$produto['preco'];
$desconto_percent = (int)$produto['desconto'];
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

// =======================================================
// --- (NOVO) 7. BUSCAR AVALIAÇÕES (COMENTÁRIOS E NOTAS) ---
// =======================================================
$minha_avaliacao = null;
$outras_avaliacoes = [];

try {
  // Busca todas as avaliações JUNTANDO com o nome do usuário
  $stmt_aval = $pdo->prepare(
    "SELECT a.*, u.nome as usuario_nome 
         FROM avaliacoes a
         JOIN usuarios u ON a.usuario_id = u.id
         WHERE a.produto_id = ?
         ORDER BY a.data_avaliacao DESC"
  );
  $stmt_aval->execute([$produto_id]);
  $todas_avaliacoes = $stmt_aval->fetchAll(PDO::FETCH_ASSOC);

  // Separa a avaliação do usuário logado das demais
  foreach ($todas_avaliacoes as $avaliacao) {
    if ($usuario_id && $avaliacao['usuario_id'] == $usuario_id) {
      $minha_avaliacao = $avaliacao;
    } else {
      $outras_avaliacoes[] = $avaliacao;
    }
  }
} catch (PDOException $e) {
  error_log("Erro ao buscar avaliações: " . $e->getMessage());
  // Continua mesmo se der erro, mas as listas estarão vazias
}

// Passa a nota e comentário do usuário para o JavaScript
$minha_nota_js = $minha_avaliacao ? $minha_avaliacao['nota'] : 0;

// Isso evita erros de sintaxe se o comentário tiver aspas, '\' ou <script>
$meu_comentario_js = json_encode($minha_avaliacao ? $minha_avaliacao['comentario'] : '');
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
  <meta charset="UTF-8">
  <title>Página do Produto</title>
  <link rel="stylesheet" href="../assets/estilos/style.css" />
  <link rel="stylesheet" href="../assets/estilos/notifications.css">
  <link rel="stylesheet" href="../assets/estilos/estilo_carrinho.css">
  <link rel="stylesheet" href="../assets/estilos/estilo_produto.css">

  <style>
    .opiniao-item {
      border-bottom: 1px solid #eee;
      padding-bottom: 15px;
      margin-bottom: 15px;
    }

    .opiniao-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 5px;
    }

    .opiniao-data {
      font-size: 0.85rem;
      color: #777;
    }

    .opiniao-estrelas .star {
      font-size: 1.1rem;
      /* Estrelas das avaliações listadas */
      color: #ddd;
      letter-spacing: 1px;
    }

    .opiniao-estrelas .star.filled {
      color: #f5c518;
      /* Amarelo da estrela preenchida */
    }

    .opiniao-item p {
      margin: 10px 0 5px;
      color: #333;
      line-height: 1.5;
    }

    .btn-link-excluir {
      background: none;
      border: none;
      color: #D9534F;
      /* Vermelho para excluir */
      cursor: pointer;
      padding: 0;
      font-size: 0.9rem;
      font-weight: 500;
    }

    .btn-link-excluir:hover {
      text-decoration: underline;
    }

    .opiniao-form-container strong {
      font-size: 1.2rem;
      color: #333;
    }

    .opiniao-form-container p {
      font-size: 0.9rem;
      color: #555;
      margin: 5px 0 15px;
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

        <?php if ($usuario_logado): ?>
          <!-- Notification System -->
          <div id="notification-bell" class="notification-container">
            <svg class="notification-bell-icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
              <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
            </svg>
            <span id="notification-badge" class="notification-badge"></span>
            <div id="notification-dropdown" class="notification-dropdown">
              <div class="notification-header">
                <span>Notificações</span>
                <span id="mark-all-read" class="mark-all-read">Marcar todas como lidas</span>
              </div>
              <div id="notification-list"></div>
            </div>
          </div>
        <?php endif; ?>
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
            if (empty($caracteristicas_array) && empty($especificacoes_array)) {
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
        <?php echo nl2br(htmlspecialchars($descricao_texto)); ?>
      </p>
    </section>

    <section id="opinioes" class="detalhe-bloco">
      <h2>Opiniões do produto</h2>

      <?php if ($usuario_logado): ?>
        <div class="opiniao-form-container" id="opiniao-form-container">

          <div class="opiniao-form">
            <textarea id="opiniao-textarea" placeholder="Escreva sua opinião aqui..."></textarea>
            <button type="button" class="btn-acao btn-comprar" id="btn-enviar-opiniao">Enviar Comentário</button>
          </div>
        </div>
      <?php else: ?>
        <p>Você precisa <a href="tela_login.html" style="color: #007bff; text-decoration: none;">fazer login</a> para avaliar este produto.</p>
      <?php endif; ?>

      <hr style="margin: 30px 0; border: 0; border-top: 1px solid #eee;">

      <strong>Últimas avaliações</strong>

      <?php if (empty($outras_avaliacoes) && empty($minha_avaliacao)): ?>
        <p style="margin-top: 10px;">Este produto ainda não tem avaliações.</p>
      <?php endif; ?>

      <?php if ($minha_avaliacao): ?>
        <div class="opiniao-item" id="minha-opiniao-<?php echo $minha_avaliacao['id']; ?>">
          <div class="opiniao-header">
            <strong><?php echo htmlspecialchars($minha_avaliacao['usuario_nome']); ?> (Sua avaliação)</strong>
            <span class="opiniao-data"><?php echo date('d/m/Y', strtotime($minha_avaliacao['data_avaliacao'])); ?></span>
          </div>
          <div class="opiniao-estrelas">
            <?php for ($i = 1; $i <= 5; $i++): // Loop para desenhar as estrelas 
            ?>
              <span class="star <?php echo ($i <= $minha_avaliacao['nota']) ? 'filled' : ''; ?>">&#9733;</span>
            <?php endfor; ?>
          </div>

          <?php if (!empty($minha_avaliacao['comentario'])): ?>
            <p><?php echo nl2br(htmlspecialchars($minha_avaliacao['comentario'])); ?></p>
          <?php endif; ?>

          <button type="button" class="btn-link-excluir" onclick="excluirAvaliacao(<?php echo $minha_avaliacao['id']; ?>)">
            Excluir avaliação
          </button>
        </div>
      <?php endif; ?>

      <?php foreach ($outras_avaliacoes as $avaliacao): ?>
        <div class="opiniao-item" id="opiniao-<?php echo $avaliacao['id']; ?>">
          <div class="opiniao-header">
            <strong><?php echo htmlspecialchars($avaliacao['usuario_nome']); ?></strong>
            <span class="opiniao-data"><?php echo date('d/m/Y', strtotime($avaliacao['data_avaliacao'])); ?></span>
          </div>
          <div class="opiniao-estrelas">
            <?php for ($i = 1; $i <= 5; $i++): ?>
              <span class="star <?php echo ($i <= $avaliacao['nota']) ? 'filled' : ''; ?>">&#9733;</span>
            <?php endfor; ?>
          </div>
          <?php if (!empty($avaliacao['comentario'])): ?>
            <p><?php echo nl2br(htmlspecialchars($avaliacao['comentario'])); ?></p>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
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

    // --- 3. LÓGICA DO CONTADOR DE QUANTIDADE ---
    function alterarContador(valor) {
      let novaQuantidade = quantidade + valor;
      if (novaQuantidade < 1) novaQuantidade = 1;
      if (novaQuantidade > estoqueMaximo) novaQuantidade = estoqueMaximo;
      quantidade = novaQuantidade;
      contadorNumero.innerText = quantidade;
    }

    // --- 4. FUNÇÃO AUXILIAR PARA PEGAR DADOS DO PRODUTO (para carrinho/compra) ---
    function getDadosProduto() {
      return {
        id: container.dataset.id,
        title: container.dataset.title,
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

      // (NOVA ADIÇÃO) Salva ESTE item como o carrinho ATUAL
      // Isso sobrescreve qualquer carrinho existente, 
      // que é o comportamento esperado de "Comprar Agora".
      localStorage.setItem("carrinho", JSON.stringify([produto]));

      // (EXISTENTE) Calcula o total e salva
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

    // =======================================================
    // --- (NOVO) 6. LÓGICA DE AVALIAÇÃO (ESTRELAS E COMENTÁRIOS) ---
    // =======================================================

    // Pega os dados do PHP
    const produtoId = container.dataset.id;
    const usuarioLogado = <?php echo $usuario_logado ? 'true' : 'false'; ?>;

    // Pega os elementos do formulário de opinião
    const btnEnviarOpiniao = document.getElementById("btn-enviar-opiniao");
    const opiniaoTextarea = document.getElementById("opiniao-textarea");

    // Pega os elementos das estrelas
    const stars = document.querySelectorAll(".produto-avaliacao .star");
    const starContainer = document.querySelector(".produto-avaliacao");

    // (MODIFICADO) Inicializa a nota com o valor vindo do banco (ou 0 se não houver)
    let currentRating = <?php echo $minha_nota_js; ?>;

    // Preenche o textarea com o comentário seguro (vindo do json_encode)
    if (opiniaoTextarea) {
      opiniaoTextarea.value = <?php echo $meu_comentario_js; ?>;
    }

    // Função para desenhar as estrelas (preenchidas ou vazias)
    function drawStars(rating) {
      stars.forEach(star => {
        const starValue = parseInt(star.dataset.value);
        if (starValue <= rating) {
          star.classList.add("filled");
          star.innerHTML = "&#9733;"; // Cheia
        } else {
          star.classList.remove("filled");
          star.innerHTML = "&#9734;"; // Vazia
        }
      });
    }

    // (NOVO) Função para salvar a avaliação (nota E comentário) no banco
    async function salvarAvaliacao() {
      // Esta função só é chamada por cliques (estrelas/botão), 
      // que já verificam se o usuário está logado.
      if (!usuarioLogado) return;

      const comentario = opiniaoTextarea ? opiniaoTextarea.value : '';
      const nota = currentRating;

      // Impede o envio se a nota for 0 (caso o usuário clique em "Enviar" sem ter clicado em uma estrela)
      if (nota === 0) {
        alert("Por favor, selecione uma nota (clicando nas estrelas) antes de enviar.");
        return;
      }

      // Desabilita o botão para evitar cliques duplos
      if (btnEnviarOpiniao) btnEnviarOpiniao.disabled = true;

      try {
        const response = await fetch('../Banco de dados/processa_avaliacao.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json'
          },
          body: JSON.stringify({
            produto_id: produtoId,
            nota: nota,
            comentario: comentario
          })
        });

        const data = await response.json();

        if (data.sucesso) {
          // Recarrega a página para mostrar a avaliação salva na lista
          window.location.reload();
        } else {
          alert('Erro ao salvar avaliação: ' + data.mensagem);
          if (btnEnviarOpiniao) btnEnviarOpiniao.disabled = false;
        }
      } catch (error) {
        console.error('Fetch error:', error);
        alert('Erro de conexão ao salvar avaliação.');
        if (btnEnviarOpiniao) btnEnviarOpiniao.disabled = false;
      }
    }

    // (NOVO) Função para excluir a avaliação
    // Precisa estar no escopo global (window.) para ser chamada pelo 'onclick' do HTML
    window.excluirAvaliacao = async function(avaliacaoId) {
      if (!confirm('Tem certeza que deseja excluir sua avaliação? Esta ação não pode ser desfeita.')) {
        return;
      }

      try {
        const response = await fetch('../Banco de dados/excluir_avaliacao.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
          },
          // Envia como form data, pois o PHP está esperando $_POST['id']
          body: `id=${avaliacaoId}`
        });

        const data = await response.json();

        if (data.sucesso) {
          // Recarrega a página para remover a avaliação da lista
          window.location.reload();
        } else {
          alert('Erro ao excluir avaliação: ' + data.mensagem);
        }
      } catch (error) {
        console.error('Fetch error:', error);
        alert('Erro de conexão ao excluir avaliação.');
      }
    }

    // --- 7. INICIALIZAÇÃO DOS LISTENERS DE AVALIAÇÃO ---

    // Adiciona listeners nas estrelas
    stars.forEach(star => {
      star.addEventListener("mouseover", () => {
        // Só mostra o "hover" se estiver logado
        if (usuarioLogado) {
          const rating = parseInt(star.dataset.value);
          drawStars(rating);
        }
      });

      star.addEventListener("click", () => {
        if (!usuarioLogado) {
          // Se não está logado, redireciona para o login ao clicar
          alert("Você precisa estar logado para avaliar.");
          window.location.href = 'tela_login.html?redirecionar=' + window.location.pathname + window.location.search;
          return;
        }
        // Define a nova nota
        currentRating = parseInt(star.dataset.value);
        drawStars(currentRating);
        // (MODIFICADO) Salva a avaliação (nota) imediatamente no banco
        salvarAvaliacao();
      });
    });

    // Listener para quando o mouse sai do container de estrelas
    starContainer.addEventListener("mouseleave", () => {
      // Retorna para a nota que está salva (currentRating)
      drawStars(currentRating);
    });

    // Adiciona listener no botão "Enviar Comentário"
    if (btnEnviarOpiniao) {
      btnEnviarOpiniao.addEventListener("click", () => {
        // A nota (currentRating) já foi definida pelo clique na estrela
        // A função salvarAvaliacao() vai pegar a nota e o texto do textarea
        salvarAvaliacao();
      });
    }

    // (MODIFICADO) Desenha as estrelas com a nota salva assim que a página carrega
    drawStars(currentRating);
  </script>
  <script src="../assets/js/notifications.js"></script>
</body>

</html>