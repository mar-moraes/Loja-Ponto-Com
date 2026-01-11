<?php

namespace Model;

use PDO;

class Cupom
{
    private $pdo;

    public $id;
    public $codigo;
    public $descricao;
    public $tipo_desconto;
    public $valor_desconto;
    public $valor_minimo;
    public $data_inicio;
    public $data_fim;
    public $limite_uso;
    public $ativo;
    public $usuario_id;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function findByCode($codigo)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM cupons WHERE codigo = :codigo AND ativo = 1");
        $stmt->bindParam(':codigo', $codigo);
        $stmt->execute();
        return $stmt->fetchObject(self::class, [$this->pdo]);
    }

    public function isValidForCart($cartTotal, $userId = null)
    {
        // Check dates
        $now = new \DateTime();
        if ($this->data_inicio && $now < new \DateTime($this->data_inicio)) {
            return ['valid' => false, 'message' => 'Cupom ainda não é válido.'];
        }
        if ($this->data_fim && $now > new \DateTime($this->data_fim)) {
            return ['valid' => false, 'message' => 'Cupom expirado.'];
        }

        // Check min value
        if ($cartTotal < $this->valor_minimo) {
            return ['valid' => false, 'message' => 'Valor mínimo não atingido para este cupom.'];
        }

        // Check global usage limit (if implemented in DB/Service query count)
        // This simple model check might not cover aggregate usage without a separate query.

        return ['valid' => true];
    }

    public function calculateDiscount($cartTotal)
    {
        if ($this->tipo_desconto === 'porcentagem') {
            return ($cartTotal * $this->valor_desconto) / 100;
        } else {
            return min($this->valor_desconto, $cartTotal); // Cannot discount more than total
        }
    }
}
