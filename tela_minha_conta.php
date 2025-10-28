<?php
session_start();
require 'Banco de dados/conexao.php'; // Inclui a conexão

// 1. Verifica se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: tela_login.html'); // Redireciona para o login se não estiver logado
    exit();
}

$usuario_id = $_SESSION['usuario_id'];

// 2. Busca os dados pessoais do usuário
try {
    $stmt_user = $pdo->prepare("SELECT nome, email, cpf, telefone FROM usuarios WHERE id = ?");
    $stmt_user->execute([$usuario_id]);
    $usuario = $stmt_user->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Erro ao buscar dados do usuário: " . $e->getMessage());
}

// 3. Busca os endereços do usuário
try {
    $stmt_enderecos = $pdo->prepare("SELECT * FROM enderecos WHERE usuario_id = ? ORDER BY id DESC");
    $stmt_enderecos->execute([$usuario_id]);
    $enderecos = $stmt_enderecos->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $enderecos = [];
    error_log("Erro ao buscar endereços: " . $e->getMessage());
}

// Pega o primeiro nome para o header
$nome_usuario = explode(' ', $usuario['nome'])[0];
?>
<!doctype html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Minha Conta - Loja Ponto Com</title>
  <link rel="stylesheet" href="estilos/style.css">
  
  <style>
    /* Estilos para a página "Minha Conta", inspirados na referência */
    .container {
        max-width: 900px; /* Mais estreito para uma página de conta */
        margin-top: 40px;
        margin-bottom: 40px;
    }
    
    .conta-secao {
        background-color: #ffffff;
        border-radius: 8px;
        padding: 24px;
        margin-bottom: 30px;
        /* box-shadow: 0 2px 4px rgba(0,0,0,0.05); */
    }

    .conta-secao h2 {
        margin-top: 0;
        padding-bottom: 10px;
        border-bottom: 1px solid #eee;
    }

    /* Card de Endereço */
    .endereco-card {
        border: 1px solid #ddd;
        border-radius: 8px;
        padding: 20px;
        margin-bottom: 15px;
        position: relative;
        line-height: 1.6;
    }
    
    .endereco-card p {
        margin: 0 0 5px 0;
        font-size: 0.95rem;
        color: #333;
    }
    
    .endereco-card .rua-principal {
        font-weight: bold;
        color: #000;
        font-size: 1.1rem;
    }
    
    .endereco-card .cep-cidade {
        font-size: 0.9rem;
        color: #555;
    }

    .endereco-card-opcoes {
        position: absolute;
        top: 20px;
        right: 20px;
    }
    
    .endereco-card-opcoes a {
        font-size: 0.9rem;
        margin-left: 15px;
        text-decoration: none;
        color: #007bff;
    }

    /* =================================== */
    /* BOTÃO ADICIONAR NOVO - MODIFICADO   */
    /* =================================== */
    .btn-adicionar-endereco {
        display: flex;
        align-items: center;
        justify-content: center; /* Centraliza o conteúdo */
        width: 100%;
        padding: 14px 20px; /* Padding padrão dos botões */
        border: none; /* Remove a borda */
        border-radius: 8px;
        text-decoration: none;
        color: #FFFFFF; /* Texto branco */
        font-size: 1rem; /* Tamanho padrão (16px) */
        font-weight: 600; /* Peso padrão */
        background-color: #2968C8; /* Cor azul padrão */
        transition: opacity 0.2s;
        box-sizing: border-box;
    }
    
    .btn-adicionar-endereco:hover {
        opacity: 0.9; /* Efeito hover padrão */
    }

    .btn-adicionar-endereco span {
        font-size: 1.5rem; /* '+' um pouco menor */
        font-weight: 300;
        margin-right: 10px; /* Espaço entre o '+' e o texto */
        line-height: 1;
    }
    
    /* Tabela de dados pessoais */
    .dados-pessoais {
        width: 100%;
    }
    .dados-pessoais td {
        padding: 8px 0;
        font-size: 1rem;
    }
    .dados-pessoais td:first-child {
        font-weight: 500;
        color: #555;
        width: 100px;
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
          <a href="tela_minha_conta.php">Olá, <?php echo htmlspecialchars($nome_usuario); ?></a>
          <a href="Banco de dados/logout.php">Sair</a>
          <a href="tela_carrinho.php" style="display: flex; align-items: center; gap: 5px;">
            Carrinho
            <img src="imagens/carrinho invertido.png" alt="" style="width: 20px; height: 20px;">
          </a>
        </div>
        </nav>
    </header>

  <main class="container">
    <h1>Minha Conta</h1>

    <section class="conta-secao">
        <h2>Dados Pessoais</h2>
        <table class="dados-pessoais">
            <tr>
                <td>Nome:</td>
                <td><?php echo htmlspecialchars($usuario['nome']); ?></td>
            </tr>
            <tr>
                <td>Email:</td>
                <td><?php echo htmlspecialchars($usuario['email']); ?></td>
            </tr>
            <tr>
                <td>CPF:</td>
                <td><?php echo htmlspecialchars(substr($usuario['cpf'], 0, 3) . '.***.***-' . substr($usuario['cpf'], -2)); ?></td>
            </tr>
            <tr>
                <td>Telefone:</td>
                <td><?php echo htmlspecialchars($usuario['telefone']); ?></td>
            </tr>
        </table>
        </section>

    <section class="conta-secao">
        <h2>Endereços</h2>

        <?php if (empty($enderecos)): ?>
            <p>Nenhum endereço cadastrado.</p>
        <?php endif; ?>

        <?php foreach ($enderecos as $endereco): ?>
            <div class="endereco-card">
                <div class="endereco-card-opcoes">
                    <a href="tela_editar_endereco.php?id=<?php echo $endereco['id']; ?>">Editar</a>
                    <a href="Banco de dados/processa_excluir_endereco.php?id=<?php echo $endereco['id']; ?>" 
                       onclick="return confirm('Tem certeza que deseja excluir este endereço?');">Excluir</a>
                </div>
                
                <p class="rua-principal">
                    <?php 
                        echo htmlspecialchars($endereco['rua']) . ', ' . htmlspecialchars($endereco['numero']); 
                        if (!empty($endereco['complemento'])) {
                            echo ' - ' . htmlspecialchars($endereco['complemento']);
                        }
                    ?>
                </p>
                <p class="cep-cidade">
                    CEP <?php echo htmlspecialchars($endereco['cep']); ?> - 
                    <?php echo htmlspecialchars($endereco['cidade']); ?> - 
                    <?php echo htmlspecialchars($endereco['estado']); ?>
                </p>
                <p><?php echo htmlspecialchars($usuario['nome']); ?></p>
            </div>
        <?php endforeach; ?>

        <a href="tela_novo_endereco.php" class="btn-adicionar-endereco">
            <span>+</span> Adicionar novo endereço
        </a>
    </section>
    
  </main>
  
</body>
</html>