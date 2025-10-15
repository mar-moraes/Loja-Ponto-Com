// Captura o <select> e o container de produtos
const select = document.querySelector("#sort");
const grid = document.querySelector(".grid");

// Ordenação dos produtos
select.addEventListener("change", (e) => {
  const valor = e.target.value;
  const cards = Array.from(grid.querySelectorAll(".card"));

  if (valor === "Menor preço") {
    cards.sort((a, b) => Number(a.dataset.price) - Number(b.dataset.price));
  } else if (valor === "Maior preço") {
    cards.sort((a, b) => Number(b.dataset.price) - Number(a.dataset.price));
  } else {
    return;
  }

  cards.forEach(card => grid.appendChild(card));
});

// ==========================
// Adicionar produtos ao carrinho
// ==========================
document.querySelectorAll(".card").forEach(card => {
  card.addEventListener("click", () => {
    const title = card.querySelector(".title").innerText;
    const price = card.querySelector(".price").innerText.replace("R$", "").trim();
    // Extrai a URL da imagem do backgroundImage (ex: url("..."))
    const bgImg = card.querySelector(".thumb").style.backgroundImage;
    const imgMatch = bgImg.match(/url\(["']?(.*?)["']?\)/);
    const img = imgMatch ? imgMatch[1] : "";

    let carrinho = JSON.parse(localStorage.getItem("carrinho")) || [];

    // Verifica se o produto já existe
    const existente = carrinho.find(p => p.title === title);

    if (existente) {
      if (typeof existente.quantidade !== "number") {
        existente.quantidade = 1;
      }
      existente.quantidade += 1;
    } else {
      carrinho.push({
        title,
        price: parseFloat(price),
        img,
        quantidade: 1
      });
    }

    // Salva o carrinho atualizado
    localStorage.setItem("carrinho", JSON.stringify(carrinho));

    // Redireciona para o carrinho
    window.location.href = "tela_carrinho.html";
  });
});
