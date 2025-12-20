<?php

use PHPUnit\Framework\TestCase;
use Services\AuthService;

class AuthServiceTest extends TestCase
{
    private $pdoMock;
    private $authService;

    protected function setUp(): void
    {
        // Mock PDO and Statement
        $this->pdoMock = $this->createMock(\PDO::class);
        $this->authService = new AuthService($this->pdoMock);
    }

    public function testIdentifyUserTypeFornecedor()
    {
        $type = $this->authService->identifyUserType('admin@LojaLTDA.com');
        $this->assertEquals('fornecedor', $type);
    }

    public function testIdentifyUserTypeCliente()
    {
        $type = $this->authService->identifyUserType('user@gmail.com');
        $this->assertEquals('cliente', $type);
    }

    public function testLoginSuccess()
    {
        $email = 'test@example.com';
        $password = 'password123';
        $hash = password_hash($password, PASSWORD_DEFAULT);

        $stmtMock = $this->createMock(\PDOStatement::class);
        $stmtMock->method('fetch')->willReturn([
            'id' => 1,
            'nome' => 'Test',
            'senha' => $hash
        ]);

        $this->pdoMock->method('prepare')->willReturn($stmtMock);

        $result = $this->authService->login($email, $password);
        $this->assertIsArray($result);
        $this->assertEquals(1, $result['id']);
    }

    public function testLoginFailure()
    {
        $email = 'test@example.com';
        $password = 'wrongpassword';
        $hash = password_hash('password123', PASSWORD_DEFAULT);

        $stmtMock = $this->createMock(\PDOStatement::class);
        $stmtMock->method('fetch')->willReturn([
            'id' => 1,
            'nome' => 'Test',
            'senha' => $hash
        ]);

        $this->pdoMock->method('prepare')->willReturn($stmtMock);

        $result = $this->authService->login($email, $password);
        $this->assertNull($result);
    }
}
