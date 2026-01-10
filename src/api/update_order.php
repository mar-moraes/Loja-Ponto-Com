<?php
session_start();
require '../../Banco de dados/conexao.php';

header('Content-Type: application/json');

// Verifica permissão (apenas fornecedor pode editar - ou admin se houver)
$is_fornecedor = (isset($_SESSION['usuario_tipo']) && $_SESSION['usuario_tipo'] == 'fornecedor');
// Se quiser ser mais restritivo, checar se modo_editor está on
if (!$is_fornecedor) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$order = $input['order'] ?? [];

if (empty($order) || !is_array($order)) {
    echo json_encode(['success' => false, 'error' => 'No order provided']);
    exit;
}

try {
    $pdo->beginTransaction();
    $stmt = $pdo->prepare("UPDATE produtos SET ordem_destaque = ? WHERE id = ?");

    foreach ($order as $index => $id) {
        if (is_numeric($id)) {
            $stmt->execute([$index, $id]);
        }
    }

    // Invalidar cache se necessário
    // require '../Services/CacheService.php';
    // $cache = new \Services\CacheService($pdo); // Adaptar conforme implementação
    // $cache->forget('home_produtos_destaque');
    // Como não sei passar o PDO para o serviço facilmente aqui sem autoload completo:
    // Vou fazer delete manual do cache do banco se for tabela

    // Invalidar cache
    try {
        require_once '../../vendor/autoload.php';
        $cache = new \Services\CacheService(); // Qualified name
        $cache->forget('home_produtos_destaque');
    } catch (Exception $e) {
        error_log("Erro ao limpar cache: " . $e->getMessage());
    }

    $pdo->commit();
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
