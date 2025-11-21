<?php
session_start();

// 1. Verifica se o usuário está logado E se é um fornecedor
$usuario_logado = isset($_SESSION['usuario_id']);
// Usamos a session 'usuario_tipo' definida no login
$is_fornecedor = (isset($_SESSION['usuario_tipo']) && $_SESSION['usuario_tipo'] == 'fornecedor');

if (!$usuario_logado || !$is_fornecedor) {
  // Se não estiver logado ou não for fornecedor, redireciona
  header('Location: tela_login.html?erro=acesso_negado');
  exit();
}

// 2. Pega o nome do usuário para o cabeçalho
$nome_usuario = explode(' ', $_SESSION['usuario_nome'] ?? 'Usuário')[0];
?>
<!DOCTYPE html>

<html lang="pt-BR">

<head>
  <meta charset="UTF-8">
  <title>Novo Produto</title>

  <link rel="stylesheet" href="estilos/style.css" />
  <link rel="stylesheet" href="estilos/estilo_carrinho.css">
  <link rel="stylesheet" href="estilos/estilo_produto.css">

  <style>
    /* Estiliza os inputs de NOME da característica */
    .caracteristica-item input[name="caracteristica_nome[]"] {
      font-weight: 500;
      color: #333;
      background-color: transparent;
      /* Tira o fundo do input */
      border: none;
      /* Tira a borda */
      padding: 12px 16px;
      /* Mantém o padding original */
      font-size: 1em;
      /* Mantém o tamanho da fonte */
      font-family: inherit;
      /* Mantém a fonte */
      width: 100%;
      /* Ocupa o espaço */
      box-sizing: border-box;
    }

    /* --- INÍCIO DA MODIFICAÇÃO CSS --- */

    /* Estiliza os inputs de VALOR da característica */
    .caracteristica-item input[name="caracteristica_valor[]"] {
      border: none;
      background-color: transparent;
      padding: 12px 16px;
      font-size: 1em;
      font-family: inherit;
      width: 100%;
      box-sizing: border-box;
      /* Adiciona espaço à direita para o ícone não sobrepor o texto */
      padding-right: 40px;
    }

    /* Adiciona foco para saber onde está digitando */
    .caracteristica-item input:focus {
      outline: 1px solid #2968C8;
      /* Borda azul ao focar */
      background-color: #fff;
    }

    /* Define o item como 'relative' para posicionar o ícone dentro dele */
    .caracteristica-item {
      position: relative;
    }

    /* Estilo do ícone de lixeira */
    .btn-delete-caracteristica {
      position: absolute;
      right: 12px;
      /* Alinha com o padding do input */
      top: 50%;
      transform: translateY(-50%);
      display: none;
      /* Escondido por padrão */
      cursor: pointer;
      font-size: 1.2em;
      opacity: 0.6;
      user-select: none;
      /* Impede de selecionar o emoji */
    }

    /* Mostra o ícone quando o mouse passa sobre o item */
    .caracteristica-item:hover .btn-delete-caracteristica {
      display: block;
    }

    .btn-delete-caracteristica:hover {
      opacity: 1;
    }

    /* --- FIM DA MODIFICAÇÃO CSS --- */


    /* --- INÍCIO CSS ESPECIFICAÇÕES RÁPIDAS (NOVO) --- */
    #especificacoes-rapidas-lista .espec-item {
      position: relative;
      border-bottom: 1px solid #eee;
    }

    #especificacoes-rapidas-lista input[name="especificacao_rapida[]"] {
      border: none;
      background-color: transparent;
      padding: 12px 16px;
      font-size: 1em;
      font-family: inherit;
      width: 100%;
      box-sizing: border-box;
      padding-right: 40px;
      /* Espaço para lixeira */
    }

    #especificacoes-rapidas-lista input:focus {
      outline: 1px solid #2968C8;
      background-color: #fff;
    }

    #especificacoes-rapidas-lista .btn-delete-espec {
      position: absolute;
      right: 12px;
      top: 50%;
      transform: translateY(-50%);
      display: none;
      cursor: pointer;
      font-size: 1.2em;
      opacity: 0.6;
      user-select: none;
    }

    #especificacoes-rapidas-lista .espec-item:hover .btn-delete-espec {
      display: block;
    }

    #especificacoes-rapidas-lista .btn-delete-espec:hover {
      opacity: 1;
    }

    /* --- FIM CSS ESPECIFICAÇÕES RÁPIDAS (NOVO) --- */
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

        <a href="tela_minha_conta.php">Olá, <?php echo htmlspecialchars($nome_usuario); ?></a>
        <a href="Banco de dados/logout.php">Sair</a>
        <a href="tela_carrinho.php" style="display: flex; align-items: center; gap: 5px;"> Carrinho
          <img src="imagens/carrinho invertido.png" alt="" style="width: 20px; height: 20px;">
        </a>
      </div>
    </nav>
  </header>

  <form id="form-produto">
    <!-- Campos ocultos para ID e Imagem Atual -->
    <input type="hidden" id="produto-id" name="produto_id" value="">
    <input type="hidden" id="imagem-atual" name="imagem_atual" value="">

    <main class="produto-container"
      data-title=""
      data-price=""
      data-img="">

      <div class="coluna-galeria" style="flex: 1.2;">

        <input type="file" id="produto-imagens" multiple accept="image/*" style="display: none;">

        <div style="display: flex; gap: 15px; width: 100%; height: 100%;">

          <div class="thumbnails" id="thumbnails-container">
          </div>

          <div class="imagem-principal-container" style="display: flex; flex-direction: column; flex: 1;">

            <label for="produto-imagens"
              id="imagem-placeholder"
              style="background-color: #f0f0f0; border: 1px dashed #ccc; height: 100%; display: flex; align-items: center; justify-content: center; width: 100%; cursor: pointer; color: #555; text-align: center; box-sizing: border-box;">
              Clique aqui para selecionar as imagens
            </label>

            <img alt="Imagem principal do produto"
              id="imagem-principal"
              style="border: 1px solid #eee; height: 100%; display: none; width: 100%; object-fit: contain; box-sizing: border-box; max-height: none;">

          </div>
        </div>

      </div>

      <div class="coluna-info" style="flex: 1;">

        <label for="produto-titulo" style="font-size: 14px; font-weight: 600; color: #333;">Título do Produto:</label>
        <input type="text" id="produto-titulo" name="titulo" required style="width: 100%; font-size: 22px; font-weight: 600; padding: 5px; margin-bottom: 20px; box-sizing: border-box;">

        <div style="margin-bottom: 20px;">
          <label for="produto-categoria" style="display: block; margin-bottom: 5px;">Categoria:</label>
          <select id="produto-categoria" name="categoria" required style="width: 100%; padding: 10px; font-size: 16px; box-sizing: border-box; border: 1px solid #ddd; border-radius: 6px; background: #fff;">
            <option value="">Carregando categorias...</option>
          </select>
        </div>
        <div class="produto-preco" style="display: flex; flex-wrap: wrap; gap: 15px; margin-bottom: 15px;">

          <div style="flex: 1; min-width: 150px;">
            <label for="produto-preco" style="display: block; margin-bottom: 5px;">Preço:</label>
            <input type="number" class="price" id="produto-preco" name="preco" step="0.01" min="0" required placeholder="Ex: 199.90" style="width: 100%; padding: 10px; font-size: 16px; box-sizing: border-box; border: 1px solid #ddd; border-radius: 6px;">
          </div>

          <div style="flex: 1; min-width: 150px;">
            <label for="produto-badge" style="display: block; margin-bottom: 5px;">Desconto (%):</label>
            <input type="number" class="badge" id="produto-badge" name="desconto" min="0" max="100" placeholder="Opcional" style="width: 100%; padding: 10px; font-size: 16px; box-sizing: border-box; border: 1px solid #ddd; border-radius: 6px;">
          </div>

          <div style="width: 100%; margin-top: 10px;">
            <button type="button" onclick="calcular()" style="padding: 10px 15px; cursor: pointer; border-radius: 6px; border: none; background-color: rgba(41, 104, 200, 0.1); color: #2968C8; font-weight: 600;">
              Calcular desconto
            </button>
          </div>

          <div style="width: 100%; margin-top: 10px; min-height: 40px;">
            <span class="old" id="produto-preco-antigo"></span>
            <span class="price" id="produto-preco-novo"></span>
            <span class="badge" id="produto-badge-display"></span>
          </div>

        </div>
        <div style="margin-bottom: 20px;">
          <label class="label-quantidade" for="produto-quantidade" style="display: block; margin-bottom: 5px;">Quantidade disponível:</label>
          <input type="number" id="produto-quantidade" name="quantidade" min="0" placeholder="Ex: 50" required style="width: 100%; padding: 10px; font-size: 16px; box-sizing: border-box; border: 1px solid #ddd; border-radius: 6px;">
        </div>


        <script>
          // Script de calcular o desconto
          function calcular() {
            let preco = parseFloat(document.getElementById("produto-preco").value);
            let desconto = parseFloat(document.getElementById("produto-badge").value);

            if (isNaN(preco) || preco < 0) {
              // Não alerta se estiver vazio (rascunho)
              return;
            }

            if (isNaN(desconto)) desconto = 0;
            if (desconto < 0) desconto = 0;
            if (desconto > 100) desconto = 100;

            let precoFinal = preco * (1 - desconto / 100);

            const formatar = (valor) =>
              valor.toLocaleString("pt-BR", {
                style: "currency",
                currency: "BRL"
              });

            const precoAntigoEl = document.getElementById("produto-preco-antigo");
            const precoNovoEl = document.getElementById("produto-preco-novo");
            const badgeEl = document.getElementById("produto-badge-display");

            if (desconto > 0) {
              precoAntigoEl.textContent = formatar(preco);
              precoAntigoEl.style.display = "inline";
              precoNovoEl.textContent = formatar(precoFinal);
              badgeEl.textContent = `${desconto}% OFF`;
              badgeEl.style.display = "inline";
            } else {
              precoAntigoEl.textContent = "";
              precoAntigoEl.style.display = "none";
              badgeEl.textContent = "";
              badgeEl.style.display = "none";
              precoNovoEl.textContent = formatar(preco);
            }
          }
        </script>

        <div style="margin-top: auto; display: flex; gap: 10px; padding-top: 20px; border-top: 1px solid #eee;">
          <button type="submit" style="flex: 1; background:#2968C8;color:white;padding:10px 15px;border:none;border-radius:6px;cursor:pointer; font-size: 16px; font-weight: 600;">
            📤 Enviar Produto
          </button>

          <button type="button" id="btn-salvar-rascunho" style="flex: 1; background: rgba(41, 104, 200, 0.1); color: #2968C8; padding: 10px 15px;border:none;border-radius:6px;cursor:pointer; font-size: 16px; font-weight: 600;">
            💾 Salvar como Rascunho
          </button>
        </div>
      </div>
    </main>

    <div class="detalhes-produto-container">

      <section id="caracteristicas" class="detalhe-bloco">
        <h2>Características principais</h2>

        <div class="caracteristicas-tabela">
          <!-- Carregado dinamicamente -->
        </div>
        <button type="button" id="btn-add-caracteristica" style="background-color: #2968C8; color: #FFFFFF; border: none; border-radius: 6px; padding: 10px 15px; font-weight: 600; cursor: pointer; margin-top: 15px;">
          Adicionar Característica
        </button>

      </section>

      <section id="especificacoes-rapidas" class="detalhe-bloco">
        <h2>O que você precisa saber sobre este produto</h2>

        <div id="especificacoes-rapidas-lista">
          <!-- Carregado dinamicamente -->
        </div>

        <button type="button" id="btn-add-especificacao" style="background-color: #2968C8; color: #FFFFFF; border: none; border-radius: 6px; padding: 10px 15px; font-weight: 600; cursor: pointer; margin-top: 15px;">
          Adicionar Item
        </button>
      </section>
      <section id="descricao" class="detalhe-bloco">
        <h2>Descrição do produto</h2>
        <textarea name="descricao" id="produto-descricao" required style="min-height: 150px; width: 100%;"></textarea>
      </section>

    </div>

  </form>
  <script>
    // Variável global para armazenar os arquivos selecionados
    let selectedFiles = [];

    document.addEventListener('DOMContentLoaded', async () => {

      // --- 1. Carregar Categorias ---
      const selectCategoria = document.getElementById('produto-categoria');
      async function carregarCategorias() {
        try {
          const response = await fetch('Banco de dados/buscar_categorias.php');
          const data = await response.json();
          if (data.sucesso && data.categorias.length > 0) {
            selectCategoria.innerHTML = '<option value="">Selecione uma categoria</option>';
            data.categorias.forEach(categoria => {
              const option = document.createElement('option');
              option.value = categoria.id;
              option.textContent = categoria.nome;
              selectCategoria.appendChild(option);
            });
          }
        } catch (error) {
          console.error('Erro ao buscar categorias:', error);
        }
      }
      await carregarCategorias(); // Aguarda para poder selecionar a categoria correta depois

      // --- 2. Verificar se é Edição (via URL) ---
      const urlParams = new URLSearchParams(window.location.search);
      const produtoId = urlParams.get('id');

      if (produtoId) {
        carregarDadosProduto(produtoId);
      } else {
        // Adiciona campos vazios padrão se for novo
        adicionarCaracteristica('Marca', '');
        adicionarCaracteristica('Modelo', '');
        adicionarEspecificacao('');
      }

      async function carregarDadosProduto(id) {
        try {
          const response = await fetch(`Banco de dados/buscar_produto.php?id=${id}`);
          const data = await response.json();

          if (data.sucesso) {
            const p = data.produto;

            // Preenche campos básicos
            document.getElementById('produto-id').value = p.id;
            document.getElementById('produto-titulo').value = p.titulo;
            document.getElementById('produto-preco').value = p.preco;
            document.getElementById('produto-badge').value = p.desconto;
            document.getElementById('produto-quantidade').value = p.quantidade;
            document.getElementById('produto-descricao').value = p.descricao;

            if (p.categoria_id) {
              selectCategoria.value = p.categoria_id;
            }

            // Imagem Principal
            if (p.imagem_url) {
              document.getElementById('imagem-atual').value = p.imagem_url;
              const mainImage = document.getElementById('imagem-principal');
              const placeholder = document.getElementById('imagem-placeholder');

              mainImage.src = p.imagem_url;
              mainImage.style.display = 'block';
              placeholder.style.display = 'none';
            }

            // Características
            const containerCarac = document.querySelector('.caracteristicas-tabela');
            containerCarac.innerHTML = ''; // Limpa
            if (p.caracteristicas && p.caracteristicas.length > 0) {
              p.caracteristicas.forEach(c => adicionarCaracteristica(c.nome, c.valor));
            } else {
              adicionarCaracteristica('Marca', '');
            }

            // Especificações
            const containerEspec = document.getElementById('especificacoes-rapidas-lista');
            containerEspec.innerHTML = ''; // Limpa
            if (p.especificacoes && p.especificacoes.length > 0) {
              p.especificacoes.forEach(e => adicionarEspecificacao(e));
            } else {
              adicionarEspecificacao('');
            }

            // Recalcula desconto visualmente
            calcular();

            // Atualiza botão de rascunho
            const btnRascunho = document.getElementById('btn-salvar-rascunho');
            btnRascunho.textContent = "💾 Atualizar Rascunho";

          } else {
            alert('Erro ao carregar produto: ' + data.mensagem);
          }
        } catch (error) {
          console.error('Erro ao buscar produto:', error);
        }
      }

      // --- Funções Auxiliares de UI ---
      function adicionarCaracteristica(nome, valor) {
        const container = document.querySelector('.caracteristicas-tabela');
        const novoItem = document.createElement('div');
        novoItem.className = 'caracteristica-item';
        novoItem.innerHTML = `
            <input type="text" name="caracteristica_nome[]" value="${nome}" placeholder="Nome" required>
            <input type="text" name="caracteristica_valor[]" value="${valor}" placeholder="Valor" required>
            <span class="btn-delete-caracteristica">🗑️</span>
        `;
        container.appendChild(novoItem);
      }

      function adicionarEspecificacao(texto) {
        const container = document.getElementById('especificacoes-rapidas-lista');
        const novoItem = document.createElement('div');
        novoItem.className = 'espec-item';
        novoItem.innerHTML = `
            <input type="text" name="especificacao_rapida[]" value="${texto}" placeholder="Especificação" required>
            <span class="btn-delete-espec">🗑️</span>
        `;
        container.appendChild(novoItem);
      }

      // Event Listeners para Adicionar/Remover
      document.getElementById('btn-add-caracteristica').addEventListener('click', () => adicionarCaracteristica('', ''));
      document.querySelector('.caracteristicas-tabela').addEventListener('click', (e) => {
        if (e.target.classList.contains('btn-delete-caracteristica')) e.target.closest('.caracteristica-item').remove();
      });

      document.getElementById('btn-add-especificacao').addEventListener('click', () => adicionarEspecificacao(''));
      document.getElementById('especificacoes-rapidas-lista').addEventListener('click', (e) => {
        if (e.target.classList.contains('btn-delete-espec')) e.target.closest('.espec-item').remove();
      });


      // --- Upload de Imagem ---
      const fileInput = document.getElementById('produto-imagens');
      const mainImage = document.getElementById('imagem-principal');
      const imagePlaceholder = document.getElementById('imagem-placeholder');
      const thumbnailsContainer = document.getElementById('thumbnails-container');

      fileInput.addEventListener('change', (e) => {
        const files = e.target.files;
        if (files.length === 0) return;

        selectedFiles = Array.from(files);

        // Preview Imagem Principal
        const readerMain = new FileReader();
        readerMain.onload = (ev) => {
          mainImage.src = ev.target.result;
          mainImage.style.display = 'block';
          imagePlaceholder.style.display = 'none';
        };
        readerMain.readAsDataURL(selectedFiles[0]);

        // Preview Thumbnails
        thumbnailsContainer.innerHTML = '';
        selectedFiles.forEach((file, index) => {
          const readerThumb = new FileReader();
          const thumbImg = document.createElement('img');
          thumbImg.classList.add('thumb-img');
          if (index === 0) thumbImg.classList.add('active');

          readerThumb.onload = (ev) => {
            thumbImg.src = ev.target.result;
          };
          thumbImg.addEventListener('click', () => {
            mainImage.src = thumbImg.src;
            thumbnailsContainer.querySelectorAll('.thumb-img').forEach(t => t.classList.remove('active'));
            thumbImg.classList.add('active');
          });
          thumbnailsContainer.appendChild(thumbImg);
          readerThumb.readAsDataURL(file);
        });
      });


      // --- Envio do Formulário (PUBLICAR) ---
      const formProduto = document.getElementById('form-produto');
      formProduto.addEventListener('submit', async (e) => {
        e.preventDefault();

        // Validação de Imagem
        const imagemAtual = document.getElementById('imagem-atual').value;
        if (selectedFiles.length === 0 && !imagemAtual) {
          alert("A imagem principal é obrigatória para publicar.");
          return;
        }

        enviarDados('Banco de dados/processa_novo_produto.php', 'Enviando...', 'index.php');
      });

      // --- Salvar Rascunho ---
      document.getElementById('btn-salvar-rascunho').addEventListener('click', () => {
        enviarDados('Banco de dados/salvar_rascunho.php', 'Salvando...', 'tela_minha_conta.php');
      });

      async function enviarDados(url, textoBotao, redirectUrl) {
        const submitButton = formProduto.querySelector('button[type="submit"]');
        const originalText = submitButton.textContent;
        submitButton.disabled = true;
        submitButton.textContent = textoBotao;

        const formData = new FormData(formProduto);

        if (selectedFiles.length > 0) {
          formData.append('imagem_principal', selectedFiles[0]);
          for (let i = 1; i < selectedFiles.length; i++) {
            formData.append('thumbnails[]', selectedFiles[i]);
          }
        }

        try {
          const response = await fetch(url, {
            method: 'POST',
            body: formData
          });
          const resultado = await response.json();

          if (resultado.sucesso) {
            alert('✅ ' + resultado.mensagem);
            window.location.href = redirectUrl;
          } else {
            alert('❌ Erro: ' + resultado.mensagem);
          }
        } catch (error) {
          console.error('Erro:', error);
          alert('❌ Erro de conexão.');
        } finally {
          submitButton.disabled = false;
          submitButton.textContent = originalText;
        }
      }

    });
  </script>
</body>

</html>