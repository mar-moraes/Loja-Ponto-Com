// Captura o <select> e o container de produtos
const select = document.querySelector("#sort");
const grid = document.querySelector(".grid");

// Evento: quando o usuário muda a opção do select
select.addEventListener("change", (e) => {
  const valor = e.target.value;
  const cards = Array.from(grid.querySelectorAll(".card"));

  // Ordena de acordo com a escolha do usuário
  if (valor === "Menor preço") {
    cards.sort((a, b) => Number(a.dataset.price) - Number(b.dataset.price));
  } else if (valor === "Maior preço") {
    cards.sort((a, b) => Number(b.dataset.price) - Number(a.dataset.price));
  } else {
    alert("Ordenando por: " + valor);
    return; // Mantém a ordem atual
  }

  // Atualiza a ordem na tela
  cards.forEach(card => grid.appendChild(card));
});
