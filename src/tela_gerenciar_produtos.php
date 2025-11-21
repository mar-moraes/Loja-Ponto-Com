<!doctype html>
<html lang="pt-BR">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Tela Executiva</title>

  <link rel="stylesheet" href="../assets/estilos/style.css">

  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />

  <style>
    #lista-produtos {
      display: flex;
      /* Usa Flexbox em vez de Grid */
      flex-wrap: wrap;
      /* Permite que os cards quebrem a linha */
      justify-content: flex-start;
      /* Alinha os cards à esquerda */

      /* O 'gap' (espaçamento) provavelmente já vem da classe .grid, 
         mas se o espaçamento sumir, descomente a linha abaixo: */
      /* gap: 20px; */
    }

    /* Esta regra garante que o card não tente crescer */
    #lista-produtos .card {
      flex-grow: 0;
      flex-shrink: 0;
      /* O card já deve ter uma largura definida em 'style.css' 
         (ex: width: 250px), o que é ideal. */
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
        <a href="#">Crie a sua conta</a>
        <a href="#">Entre</a>
        <a href="tela_carrinho.html" style="display: flex; align-items: center; gap: 5px;">
          Carrinho
          <img src="../assets/imagens/carrinho invertido.png" alt="" style="width: 20px; height: 20px;">
        </a>
      </div>
    </nav>
  </header>


  <div class="controls">
    <label for="sort">Ordenar por</label>
    <select id="sort" aria-label="Ordenar por">
      <option>Mais relevantes</option>
      <option>Menor preço</option>
      <option>Maior preço</option>
    </select>
  </div>

  <section class="grid" id="lista-produtos"></section>

  <script>
    document.addEventListener("DOMContentLoaded", () => {
      const container = document.getElementById("lista-produtos");
      let produtos = JSON.parse(localStorage.getItem("produtosCadastrados")) || [];

      function atualizarTela() {
        container.innerHTML = ""; // Limpa a tela
        produtos.forEach((p, index) => {
          const card = document.createElement("article");
          card.classList.add("card");
          card.dataset.price = p.precoFinal;

          card.innerHTML = `
        <div class="thumb" style="background-image:url('${p.img}')"></div>
        <div class="title">${p.titulo}</div>
        <div>
          <span class="old">R$ ${p.preco}</span>
          <span class="price">R$ ${p.precoFinal}</span>
          <span class="badge">${p.desconto > 0 ? p.desconto + "% OFF" : ""}</span>
        </div>
        <button class="editar-btn" data-index="${index}">✏️ Editar</button>
        <button class="excluir-btn" data-index="${index}">🗑 Excluir</button>
      `;
          container.appendChild(card);
        });

        // Evento para excluir produto
        document.querySelectorAll(".excluir-btn").forEach(btn => {
          btn.addEventListener("click", e => {
            const i = e.target.dataset.index;
            produtos.splice(i, 1); // Remove do array
            localStorage.setItem("produtosCadastrados", JSON.stringify(produtos));
            atualizarTela(); // Atualiza a pagina
          });
        });

        // Evento para editar produto
        document.querySelectorAll(".editar-btn").forEach(btn => {
          btn.addEventListener("click", e => {
            const i = e.target.dataset.index;
            localStorage.setItem("editarProduto", JSON.stringify({
              index: i,
              dados: produtos[i]
            }));
            window.location.href = "tela_produto_do_fornecedor.html"; // leva para tela de edição
          });
        });
      }

      atualizarTela();
    });
  </script>




  <footer></footer>

  <script src="script.js"></script>
  </script>
</body>

</html>