<?php

namespace Services;

require_once __DIR__ . '/../Model/Cupom.php'; // Manual include since autoload might not cover Model
use Model\Cupom;
use PDO;

class CupomService
{
    private $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function validarCupom($codigo, $cartTotal, $usuarioId = null)
    {
        $cupomModel = new Cupom($this->pdo);
        $cupom = $cupomModel->findByCode($codigo);

        if (!$cupom) {
            return ['valid' => false, 'message' => 'Cupom inválido ou não encontrado.'];
        }

        // Validate basic rules
        $validation = $cupom->isValidForCart($cartTotal, $usuarioId);
        if (!$validation['valid']) {
            return $validation;
        }

        // Validate Usage Limits (Database check)
        if ($cupom->limite_uso > 0) {
            $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM cupom_uso WHERE cupom_id = :id");
            $stmt->execute([':id' => $cupom->id]);
            $count = $stmt->fetchColumn();

            if ($count >= $cupom->limite_uso) {
                return ['valid' => false, 'message' => 'Limite de uso deste cupom atingido.'];
            }
        }

        // Check if user already used this coupon (if one-use-per-user rule applies)
        if ($usuarioId) {
            $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM cupom_uso WHERE cupom_id = :id AND usuario_id = :uid");
            $stmt->execute([':id' => $cupom->id, ':uid' => $usuarioId]);
            $userUsage = $stmt->fetchColumn();
            if ($userUsage > 0) {
                // Assuming 1 use per user for now, or configurable. 
                // For MVP, let's say 1 use per user per coupon code generally.
                return ['valid' => false, 'message' => 'Você já utilizou este cupom.'];
            }
        }

        return [
            'valid' => true,
            'cupom' => $cupom,
            'desconto_calculado' => $cupom->calculateDiscount($cartTotal)
        ];
    }

    public function registrarUso($cupomId, $usuarioId, $pedidoId)
    {
        $stmt = $this->pdo->prepare("INSERT INTO cupom_uso (cupom_id, usuario_id, pedido_id, data_uso) VALUES (:cid, :uid, :pid, NOW())");
        return $stmt->execute([
            ':cid' => $cupomId,
            ':uid' => $usuarioId,
            ':pid' => $pedidoId
        ]);
    }
}
