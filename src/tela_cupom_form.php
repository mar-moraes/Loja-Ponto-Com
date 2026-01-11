<?php
session_start();
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] !== 'fornecedor') {
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Criar Novo Cupom - Loja Ponto Com</title>
    <link rel="stylesheet" href="../assets/estilos/style.css">
    <style>
        .form-container {
            max-width: 600px;
            margin: 50px auto;
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }

        .btn-submit {
            width: 100%;
            padding: 12px;
            background: #2968C8;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            cursor: pointer;
        }

        .btn-submit:hover {
            background: #1c4e9e;
        }

        .error-msg {
            color: red;
            margin-bottom: 15px;
        }
    </style>
</head>

<body>
    <header class="topbar">
        <nav class="actions">
            <div class="logo-container">
                <a href="index.php"><img src="../assets/imagens/exemplo-logo.png" style="width: 40px;"></a>
            </div>
            <a href="tela_minha_conta.php">Voltar para Minha Conta</a>
        </nav>
    </header>

    <main class="form-container">
        <h2>Criar Novo Cupom</h2>

        <?php if (isset($_GET['erro'])): ?>
            <p class="error-msg">
                <?php
                if ($_GET['erro'] == 'codigo_duplicado') echo "Este código já existe.";
                elseif ($_GET['erro'] == 'dados_invalidos') echo "Dados inválidos. Verifique os campos.";
                else echo "Erro ao criar cupom.";
                ?>
            </p>
        <?php endif; ?>

        <form action="../Banco de dados/processa_novo_cupom.php" method="POST">
            <div class="form-group">
                <label for="codigo">Código do Cupom</label>
                <input type="text" name="codigo" id="codigo" placeholder="Ex: VERAO10" required style="text-transform: uppercase;">
            </div>

            <div class="form-group">
                <label for="tipo_desconto">Tipo de Desconto</label>
                <select name="tipo_desconto" id="tipo_desconto">
                    <option value="porcentagem">Porcentagem (%)</option>
                    <option value="fixo">Valor Fixo (R$)</option>
                </select>
            </div>

            <div class="form-group">
                <label for="valor_desconto">Valor do Desconto</label>
                <input type="number" name="valor_desconto" id="valor_desconto" step="0.01" min="0.01" required>
            </div>

            <div class="form-group">
                <label for="valor_minimo">Valor Mínimo do Pedido (R$)</label>
                <input type="number" name="valor_minimo" id="valor_minimo" step="0.01" min="0" value="0">
            </div>

            <div class="form-group">
                <label for="data_fim">Válido até</label>
                <input type="date" name="data_fim" id="data_fim">
            </div>

            <div class="form-group">
                <label for="limite_uso">Limite de Usos (Opcional)</label>
                <input type="number" name="limite_uso" id="limite_uso" min="1" placeholder="Ex: 50 (Deixe vazio para ilimitado)">
            </div>

            <button type="submit" class="btn-submit">Criar Cupom</button>
        </form>
    </main>
</body>

</html>