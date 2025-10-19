// Captura o <select> e o container de produtos
const select = document.querySelector("#sort");
const grid = document.querySelector(".grid");

// ==========================
// Ordenação dos produtos 
// ==========================
select.addEventListener("change", (e) => {
  const valor = e.target.value;

  // 1. Pega TODOS os filhos diretos do grid (sejam <a> ou <article>)
  const items = Array.from(grid.children);

  // 2. Filtra para garantir que estamos lidando apenas com cards ou links de card
  const cardsToSort = items.filter(item => 
      item.classList.contains('card') || item.classList.contains('card-link')
  );

  if (valor === "Menor preço" || valor === "Maior preço") {
      
      // 3. Helper para pegar o preço, não importa o tipo do elemento
      const getPrice = (element) => {
          let priceStr = "";
          if (element.classList.contains('card-link')) {
              // Se for <a>, pega o data-price do <article> DENTRO dele
              priceStr = element.querySelector('.card').dataset.price;
          } else {
              // Se for <article>, pega o data-price dele mesmo
              priceStr = element.dataset.price;
          }
          
          const price = Number(priceStr);
          
          // Se o data-price estiver vazio ("") ou for inválido (NaN),
          // retorna 'null' para tratarmos na ordenação.
          return (priceStr === "" || isNaN(price)) ? null : price;
      };

      // 4. Ordena a lista
      cardsToSort.sort((a, b) => {
          const priceA = getPrice(a);
          const priceB = getPrice(b);

          // Lógica para jogar preços vazios (null) para o FIM da lista,
          // tanto em "Menor preço" quanto em "Maior preço".
          if (priceA === null) return 1;  // 'a' vai para o fim
          if (priceB === null) return -1; // 'b' vai para o fim

          if (valor === "Menor preço") {
              return priceA - priceB;
          } else { // Maior preço
              return priceB - priceA;
          }
      });

      // 5. Re-anexa os itens ordenados de volta ao grid
      cardsToSort.forEach(card => grid.appendChild(card));

  } else {
    // "Mais relevantes" - não faz nada, deixa na ordem padrão.
    return;
  }
});


// ========================================================
// Clicar no Card salva os dados antes de navegar
// ========================================================
document.querySelectorAll("a.card-link").forEach(link => {
  link.addEventListener("click", (event) => {
    
    // Pega o .card que está DENTRO do link
    const card = link.querySelector(".card");
    if (!card) return; // Segurança

    // 1. Coletar todos os dados do card clicado
    const title = card.querySelector(".title").innerText;
    const priceText = card.querySelector(".price").innerText; // Ex: "R$ 159,79"
    const priceValue = card.dataset.price; // Ex: "159.79"
    const oldPrice = card.querySelector(".old").innerText;
    const badge = card.querySelector(".badge").innerText;
    
    const bgImg = card.querySelector(".thumb").style.backgroundImage;
    const imgMatch = bgImg.match(/url\(["']?(.*?)["']?\)/);
    const img = imgMatch ? imgMatch[1] : "";

    // 2. Criar um objeto com esses dados
    const produtoSelecionado = {
      title: title,
      price: priceText,
      priceValue: parseFloat(priceValue),
      oldPrice: oldPrice,
      badge: badge,
      img: img
    };

    // 3. Salvar este objeto no localStorage
    localStorage.setItem("produtoSelecionado", JSON.stringify(produtoSelecionado));

    // 4. O link <a> fará a navegação automaticamente.
  });
});
