<?php
header('Content-Type: application/json');

// Include dependencies
require_once __DIR__ . '/../../Banco de dados/conexao.php';
require_once __DIR__ . '/../Services/CupomService.php';

use Services\CupomService;

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['codigo']) || !isset($data['total'])) {
    http_response_code(400);
    echo json_encode(['valid' => false, 'message' => 'Dados incompletos.']);
    exit;
}

$codigo = trim($data['codigo']);
$cartTotal = floatval($data['total']);
$usuarioId = isset($data['usuario_id']) ? intval($data['usuario_id']) : null;

try {
    $cupomService = new CupomService($pdo);
    $resultado = $cupomService->validarCupom($codigo, $cartTotal, $usuarioId);

    echo json_encode($resultado);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['valid' => false, 'message' => 'Erro interno ao validar cupom.']);
}
