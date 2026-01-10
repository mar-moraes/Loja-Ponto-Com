<?php
session_start();
require '../Banco de dados/conexao.php';

// 1. Recebe os IDs da URL (ex: ?ids=1,2,3)
$ids_param = $_GET['ids'] ?? '';
$ids_array = array_filter(explode(',', $ids_param), 'is_numeric');

if (empty($ids_array)) {
    // Se não tiver IDs, redireciona ou mostra vazio
    header('Location: index.php');
    exit();
}

// 2. Busca os dados de TODOS os produtos
// Precisamos usar IN (?) e passar o array
$placeholders = str_repeat('?,', count($ids_array) - 1) . '?';
$sql = "SELECT * FROM produtos WHERE id IN ($placeholders) AND status = 'ativo'";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($ids_array);
    $produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Erro na comparação: " . $e->getMessage());
    $produtos = [];
}

// Ordena os produtos conforme a ordem dos IDs na URL (opcional)
$produtos_ordenados = [];
foreach ($ids_array as $id) {
    foreach ($produtos as $p) {
        if ($p['id'] == $id) {
            $produtos_ordenados[] = $p;
            break;
        }
    }
}
$produtos = $produtos_ordenados;

// Se não achou nenhum
if (empty($produtos)) {
    die("Produtos não encontrados para comparação.");
}

$nome_usuario = isset($_SESSION['usuario_nome']) ? explode(' ', $_SESSION['usuario_nome'])[0] : '';
$usuario_logado = isset($_SESSION['usuario_nome']);

// 3. Função auxiliar para extrair características
function parseCaracteristicas($descricao)
{
    $caracteristicas = [];
    $inicio = strpos($descricao, "--- CARACTERÍSTICAS ---");
    if ($inicio !== false) {
        $substr = substr($descricao, $inicio);
        $linhas = explode("\n", $substr);
        foreach ($linhas as $linha) {
            if (strpos($linha, ':') !== false) {
                $parts = explode(':', $linha, 2);
                $caracteristicas[trim($parts[0])] = trim($parts[1]);
            }
        }
    }
    return $caracteristicas;
}

$todas_chaves_caract = [];
$produtos_view = [];

foreach ($produtos as $p) {
    $caracts = parseCaracteristicas($p['descricao']);
    foreach (array_keys($caracts) as $k) {
        if (!in_array($k, $todas_chaves_caract)) {
            $todas_chaves_caract[] = $k;
        }
    }

    // Calcula Preço Final
    $preco = (float)$p['preco'];
    $desconto = (int)($p['desconto'] ?? 0);
    $preco_final = $preco * (1 - ($desconto / 100));

    $produtos_view[] = [
        'id' => $p['id'],
        'nome' => $p['nome'],
        'imagem_url' => $p['imagem_url'],
        'preco_final' => $preco_final,
        'caracteristicas' => $caracts,
        'descricao_curta' => mb_strimwidth($p['descricao'] ?? '', 0, 150, "...")
    ];
}
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comparação de Produtos</title>
    <link rel="stylesheet" href="../assets/estilos/style.css">
    <link rel="stylesheet" href="../assets/estilos/notifications.css">
</head>

<body>

    <header class="topbar">
        <nav class="actions">
            <div class="logo-container">
                <a href="index.php" style="display: flex; align-items: center;">
                    <img src="../assets/imagens/exemplo-logo.png" alt="" style="width: 40px; height: 40px;">
                </a>
            </div>

            <!-- Espaçador -->
            <div style="flex:1"></div>

            <div style="display: flex; gap: 30px; align-items: center;">
                <?php if ($usuario_logado): ?>
                    <a href="tela_minha_conta.php">Olá, <?php echo htmlspecialchars($nome_usuario); ?></a>
                <?php else: ?>
                    <a href="tela_login.html">Entre</a>
                <?php endif; ?>
                <a href="tela_carrinho.php">Carrinho</a>
            </div>
        </nav>
    </header>

    <main class="container comparison-container">
        <h1>Comparação de Produtos</h1>

        <div class="comparison-table-wrapper">
            <table class="comparison-table">
                <thead>
                    <tr>
                        <th><!-- Célula vazia labels --></th>
                        <?php foreach ($produtos_view as $p): ?>
                            <th>
                                <div class="comparison-product-card">
                                    <img src="<?php echo htmlspecialchars($p['imagem_url']); ?>" alt="">
                                    <h3><?php echo htmlspecialchars($p['nome']); ?></h3>
                                    <div class="price">R$ <?php echo number_format($p['preco_final'], 2, ',', '.'); ?></div>
                                    <a href="tela_produto.php?id=<?php echo $p['id']; ?>" class="btn-view">Ver Detalhes</a>
                                </div>
                            </th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <!-- Linha de Descrição -->
                    <tr>
                        <th>Descrição</th>
                        <?php foreach ($produtos_view as $p): ?>
                            <td><?php echo htmlspecialchars($p['descricao_curta']); ?></td>
                        <?php endforeach; ?>
                    </tr>

                    <!-- Linhas Dinâmicas de Características -->
                    <?php foreach ($todas_chaves_caract as $chave): ?>
                        <tr>
                            <th><?php echo htmlspecialchars($chave); ?></th>
                            <?php foreach ($produtos_view as $p): ?>
                                <td>
                                    <?php echo htmlspecialchars($p['caracteristicas'][$chave] ?? '-'); ?>
                                </td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div style="margin-top:20px; text-align:center;">
            <a href="index.php" style="text-decoration:none; color:#2968C8; font-weight:600;">&larr; Voltar para a Loja</a>
        </div>

    </main>

    <script src="../assets/js/notifications.js"></script>

</body>

</html>