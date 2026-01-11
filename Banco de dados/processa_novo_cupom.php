<?php
session_start();
require 'conexao.php';

if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] !== 'fornecedor') {
    header("Location: ../src/index.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $codigo = strtoupper(trim($_POST['codigo']));
    $tipo = $_POST['tipo_desconto']; // porcentagem | fixo
    $valor = floatval($_POST['valor_desconto']);
    $minimo = floatval($_POST['valor_minimo']);
    $limite = !empty($_POST['limite_uso']) ? intval($_POST['limite_uso']) : null;
    $data_fim = !empty($_POST['data_fim']) ? $_POST['data_fim'] . ' 23:59:59' : null;
    $usuario_id = $_SESSION['usuario_id'];

    if (empty($codigo) || $valor <= 0) {
        // Erro básico validation
        header("Location: ../src/tela_cupom_form.php?erro=dados_invalidos");
        exit();
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO cupons (codigo, descricao, tipo_desconto, valor_desconto, valor_minimo, data_inicio, data_fim, limite_uso, ativo, usuario_id) VALUES (?, 'Cupom de Fornecedor', ?, ?, ?, NOW(), ?, ?, 1, ?)");
        $stmt->execute([$codigo, $tipo, $valor, $minimo, $data_fim, $limite, $usuario_id]);

        header("Location: ../src/tela_minha_conta.php?msg=cupom_criado");
        exit();
    } catch (PDOException $e) {
        if ($e->errorInfo[1] == 1062) {
            header("Location: ../src/tela_cupom_form.php?erro=codigo_duplicado");
        } else {
            error_log("Erro ao criar cupom: " . $e->getMessage());
            header("Location: ../src/tela_cupom_form.php?erro=erro_interno");
        }
    }
}
