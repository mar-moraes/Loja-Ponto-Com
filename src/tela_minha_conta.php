<?php
session_start();
require '../Banco de dados/conexao.php'; // Inclui a conexão

// 1. Verifica se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: tela_login.html'); // Redireciona para o login se não estiver logado
    exit();
}

$usuario_id = $_SESSION['usuario_id'];

// 1.1 Verifica se o usuário é um fornecedor (definido no login)
$is_fornecedor = (isset($_SESSION['usuario_tipo']) && $_SESSION['usuario_tipo'] == 'fornecedor');


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

// ==========================================================
// --- HISTÓRICO DE COMPRAS ---
// ==========================================================
try {
    // 4. Busca o histórico de pedidos e seus itens
    $sql_pedidos = "
        SELECT 
            p.id as pedido_id,
            p.data_pedido,
            p.status as pedido_status,
            pi.quantidade,
            prod.nome as produto_nome,
            prod.imagem_url as produto_imagem
        FROM PEDIDOS p
        JOIN PEDIDO_ITENS pi ON p.id = pi.pedido_id
        JOIN PRODUTOS prod ON pi.produto_id = prod.id
        WHERE p.usuario_id = ?
        ORDER BY p.data_pedido DESC, p.id DESC, prod.nome ASC
    ";
    $stmt_pedidos = $pdo->prepare($sql_pedidos);
    $stmt_pedidos->execute([$usuario_id]);
    $itens_de_pedidos = $stmt_pedidos->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $itens_de_pedidos = [];
    error_log("Erro ao buscar histórico de pedidos: " . $e->getMessage());
}

// ==========================================================
// --- RASCUNHOS (Se for fornecedor) ---
// ==========================================================
$rascunhos = [];
if ($is_fornecedor) {
    try {
        $stmt_rascunhos = $pdo->prepare("SELECT * FROM PRODUTOS WHERE usuario_id = ? AND status = 'rascunho' ORDER BY id DESC");
        $stmt_rascunhos->execute([$usuario_id]);
        $rascunhos = $stmt_rascunhos->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Erro ao buscar rascunhos: " . $e->getMessage());
    }
}
// ==========================================================
// --- FIM DA LÓGICA ---
// ==========================================================


// Pega o primeiro nome para o header
$nome_usuario = explode(' ', $usuario['nome'])[0];

// Configura o fuso horário e local para formatar datas em português
date_default_timezone_set('America/Sao_Paulo');
setlocale(LC_TIME, 'pt_BR', 'pt_BR.utf-8', 'portuguese');
?>
<!doctype html>
<html lang="pt-BR">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Minha Conta - Loja Ponto Com</title>

    <link rel="stylesheet" href="../assets/estilos/style.css">

    <style>
        /* Estilos copiados de tela_gerenciar_produtos.html */
        #lista-produtos {
            display: flex;
            flex-wrap: wrap;
            justify-content: flex-start;
            gap: 20px;
            /* Adicionado gap para espaçamento */
        }

        #lista-produtos .card {
            flex-grow: 0;
            flex-shrink: 0;
        }

        /* --- INÍCIO DA MODIFICAÇÃO --- */
        /* Estilo para o novo botão de adicionar produto */
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
                <a href="tela_minha_conta.php">Olá, <?php echo htmlspecialchars($nome_usuario); ?></a>
                <a href="../Banco de dados/logout.php">Sair</a>
                <a href="tela_carrinho.php" style="display: flex; align-items: center; gap: 5px;">
                    Carrinho
                    <img src="../assets/imagens/carrinho invertido.png" alt="" style="width: 20px; height: 20px;">
                </a>
            </div>
        </nav>
    </header>

    <main class="container">
        <h1>Minha Conta</h1>

        <div class="tabs-container">
            <button class="tab-button active" data-tab="painel-conta">Minha Conta</button>
            <button class="tab-button" data-tab="painel-compras">Compras feitas</button>

            <?php if ($is_fornecedor): ?>
                <button class="tab-button" data-tab="painel-produtos">Meus produtos</button>
            <?php endif; ?>
        </div>

        <div id="painel-conta" class="tab-painel active">
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
                            <a href="../Banco de dados/processa_excluir_endereco.php?id=<?php echo $endereco['id']; ?>"
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
        </div>

        <div id="painel-compras" class="tab-painel">

            <?php if (empty($itens_de_pedidos)): ?>
                <section class="conta-secao">
                    <h2>Minhas Compras</h2>
                    <p>Você ainda não fez nenhuma compra.</p>
                </section>
            <?php else: ?>
                <?php
                $data_atual_grupo = ""; // Para controlar o cabeçalho de data

                foreach ($itens_de_pedidos as $item):
                    $data_pedido = new DateTime($item['data_pedido']);
                    // Formata a data (ex: "19 de agosto de 2024")
                    $data_formatada_cabecalho = strftime('%d de %B de %Y', $data_pedido->getTimestamp());
                    // Formata a data (ex: "20 de agosto")
                    $data_formatada_item = strftime('%d de %B', $data_pedido->getTimestamp());

                    // Se a data mudou, imprime um novo cabeçalho de data
                    if ($data_formatada_cabecalho != $data_atual_grupo):
                        $data_atual_grupo = $data_formatada_cabecalho;
                ?>
                        <h3 class="data-grupo-compras"><?php echo htmlspecialchars($data_formatada_cabecalho); ?></h3>
                    <?php
                    endif;

                    // --- Agora, renderiza o card do item (baseado na imagem) ---
                    ?>
                    <div class="compra-card">
                        <div class="compra-imagem">
                            <img src="<?php echo htmlspecialchars($item['produto_imagem'] ?? 'imagens/placeholder.png'); ?>" alt="Imagem do Produto">
                        </div>
                        <div class="compra-detalhes">
                            <span class="compra-status" style="color: #ff8c00;"> <?php echo htmlspecialchars(ucfirst($item['pedido_status'])); ?>
                            </span>
                            <span class="compra-data-entrega">
                                Pedido feito em <?php echo htmlspecialchars($data_formatada_item); ?>
                            </span>
                            <p class="compra-titulo"><?php echo htmlspecialchars($item['produto_nome']); ?></p>
                            <span class="compra-unidades"><?php echo htmlspecialchars($item['quantidade']); ?> unidade(s)</span>
                        </div>
                        <div class="compra-vendedor">
                            <span>Vendido por LOJA LTDA</span>
                        </div>
                        <div class="compra-acoes">
                            <a href="#" class="btn-compra-primario">Ver compra</a>
                            <a href="#" class="btn-compra-secundario" onclick="alert('Funcionalidade ainda não implementada'); return false;">Comprar novamente</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>

        </div>

        <?php if ($is_fornecedor): ?>
            <div id="painel-produtos" class="tab-painel">

                <section class="conta-secao">
                    <h2>Rascunhos</h2>

                    <div class="controls" style="margin-bottom: 20px;">
                        <label for="sort-produtos">Ordenar por</label>
                        <select id="sort-produtos" aria-label="Ordenar por">
                            <option>Mais relevantes</option>
                            <option>Menor preço</option>
                            <option>Maior preço</option>
                        </select>
                    </div>

                    <?php if (empty($rascunhos)): ?>
                        <p id="sem-produtos-aviso">Você ainda não adicionou nenhum rascunho.</p>
                    <?php else: ?>
                        <section class="grid" id="lista-produtos" style="padding: 0; border: none;">
                            <?php foreach ($rascunhos as $p):
                                $preco = $p['preco'] ?? 0;
                                $desconto = $p['desconto'] ?? 0;
                                $precoFinal = $preco * (1 - $desconto / 100);
                                $img = !empty($p['imagem_url']) ? $p['imagem_url'] : 'imagens/placeholder.png';
                            ?>
                                <article class="card" data-price="<?php echo $precoFinal; ?>">
                                    <div class="thumb" style="background-image:url('<?php echo htmlspecialchars($img); ?>')"></div>
                                    <div class="title"><?php echo htmlspecialchars($p['nome'] ?? 'Sem título'); ?></div>
                                    <div>
                                        <?php if ($desconto > 0): ?>
                                            <span class="old">R$ <?php echo number_format($preco, 2, ',', '.'); ?></span>
                                        <?php endif; ?>
                                        <span class="price">R$ <?php echo number_format($precoFinal, 2, ',', '.'); ?></span>
                                        <?php if ($desconto > 0): ?>
                                            <span class="badge"><?php echo $desconto; ?>% OFF</span>
                                        <?php endif; ?>
                                    </div>
                                    <a href="tela_produto_do_fornecedor.php?id=<?php echo $p['id']; ?>" class="editar-btn" style="text-decoration: none; text-align: center; display: inline-block; padding: 5px;">✏️ Editar</a>
                                    <a href="../Banco de dados/excluir_produto.php?id=<?php echo $p['id']; ?>" class="excluir-btn" style="text-decoration: none; text-align: center; display: inline-block; padding: 5px;" onclick="return confirm('Excluir este rascunho?');">🗑 Excluir</a>
                                </article>
                            <?php endforeach; ?>
                        </section>
                    <?php endif; ?>

                    <a href="tela_produto_do_fornecedor.php"
                        class="btn-adicionar-endereco">
                        <span>+</span> Adicionar novo produto
                    </a>
                </section>

            </div>
        <?php endif; ?>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Lógica original das abas
            const tabs = document.querySelectorAll('.tab-button');
            const panels = document.querySelectorAll('.tab-painel');

            tabs.forEach(tab => {
                tab.addEventListener('click', () => {
                    tabs.forEach(t => t.classList.remove('active'));
                    panels.forEach(p => p.classList.remove('active'));
                    tab.classList.add('active');
                    const targetPanelId = tab.getAttribute('data-tab');
                    document.getElementById(targetPanelId).classList.add('active');
                });
            });
        });
    </script>

</body>

</html>