<?php

namespace Services;

class OrderService
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function validateOrder(array $cart, float $total): bool
    {
        if (empty($cart)) {
            return false;
        }
        if ($total <= 0) {
            return false;
        }
        return true;
    }

    public function checkStock(int $productId, int $quantity): bool
    {
        // This method effectively checks if we *can* deduct stock.
        // In a real scenario it might need to query the DB, but logic 
        // here is about validating the *request* against the *process*.
        // The actual DB stock check happens in the transaction update.
        // We could extract the specific query here if we want to do a pre-check.

        $stmt = $this->pdo->prepare("SELECT estoque FROM PRODUTOS WHERE id = ?");
        $stmt->execute([$productId]);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);

        if ($result && $result['estoque'] >= $quantity) {
            return true;
        }

        return false;
    }
}
