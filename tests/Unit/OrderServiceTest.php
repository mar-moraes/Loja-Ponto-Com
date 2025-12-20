<?php

use PHPUnit\Framework\TestCase;
use Services\OrderService;

class OrderServiceTest extends TestCase
{
    private $pdoMock;
    private $orderService;

    protected function setUp(): void
    {
        $this->pdoMock = $this->createMock(\PDO::class);
        $this->orderService = new OrderService($this->pdoMock);
    }

    public function testValidateOrderSuccess()
    {
        $cart = [['id' => 1, 'qty' => 1]];
        $total = 100.50;
        $this->assertTrue($this->orderService->validateOrder($cart, $total));
    }

    public function testValidateOrderEmptyCart()
    {
        $cart = [];
        $total = 100.50;
        $this->assertFalse($this->orderService->validateOrder($cart, $total));
    }

    public function testValidateOrderInvalidTotal()
    {
        $cart = [['id' => 1]];
        $total = 0;
        $this->assertFalse($this->orderService->validateOrder($cart, $total));
    }

    public function testCheckStockSuccess()
    {
        $stmtMock = $this->createMock(\PDOStatement::class);
        $stmtMock->method('fetch')->willReturn(['estoque' => 10]);
        $this->pdoMock->method('prepare')->willReturn($stmtMock);

        $this->assertTrue($this->orderService->checkStock(1, 5));
    }

    public function testCheckStockInsufficient()
    {
        $stmtMock = $this->createMock(\PDOStatement::class);
        $stmtMock->method('fetch')->willReturn(['estoque' => 2]);
        $this->pdoMock->method('prepare')->willReturn($stmtMock);

        $this->assertFalse($this->orderService->checkStock(1, 5));
    }
}
