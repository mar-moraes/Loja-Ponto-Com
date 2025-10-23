<?php
// --- INÍCIO DO PHP ---
// Aqui você pode fazer conexão com o banco de dados, se necessário.
// Exemplo (futuro):
// include 'conexao.php';

// Verifica se há uma busca sendo feita
$termo_busca = isset($_GET['q']) ? htmlspecialchars($_GET['q']) : "";

// Exemplo simples de uso posterior:
// if ($termo_busca != "") {
//     $resultado = mysqli_query($con, "SELECT * FROM produtos WHERE nome LIKE '%$termo_busca%'");
// }
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <title>Título da Página</title>

  <!-- Referência ao arquivo CSS -->
  <link rel="stylesheet" href="estilo_tela_compras.css">

  <style>
    /* Estilo básico para o contador */
    .contador-container {
      display: flex;
      align-items: center;
      gap: 10px;
      margin-top: 10px;
      font-family: Arial, sans-serif;
      font-size: 20px;
    }

    .contador-container button {
      width: 40px;
      height: 40px;
      font-size: 24px;
      cursor: pointer;
      border: 1px solid #ccc;
      background-color: #f0f0f0;
      border-radius: 5px;
    }

    .contador-numero {
      min-width: 30px;
      text-align: center;
    }
  </style>
</head>
<body>
  <div class="painel">
    <!-- Formulário de busca -->
    <form action="" method="GET">
      <label for="pesquisa">Pesquisar:</label>
      <input type="search" id="pesquisa" name="q" placeholder="Digite sua busca..." value="<?= $termo_busca ?>">
      <button type="submit">Buscar</button>
    </form>

    <?php
    // Exemplo: exibir produtos (aqui está fixo, mas futuramente pode vir do banco)
    // Enquanto não há banco de dados, deixamos um produto de exemplo:
    ?>
    <div class="produto">
      <p>Descrição do produto: Teclado Gamer</p>
      <img src="exemplo01.png" alt="teclado">

      <!-- Contador de quantidade -->
      <div class="contador-container">
        <button type="button" onclick="alterarContador(-1)">-</button>
        <div id="contador" class="contador-numero">0</div>
        <button type="button" onclick="alterarContador(1)">+</button>
      </div>
    </div>

    <?php
    // Exemplo simples de retorno de busca:
    if ($termo_busca != "") {
        echo "<p>Você pesquisou por: <strong>$termo_busca</strong></p>";
    }
    ?>
  </div>

  <!-- Script do contador -->
  <script>
    let contador = 0;

    function alterarContador(valor) {
      contador += valor;
      if (contador < 0) contador = 0;
      document.getElementById('contador').innerText = contador;
    }
  </script>
</body>
</html>
