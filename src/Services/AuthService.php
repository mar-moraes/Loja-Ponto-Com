<?php

namespace Services;

class AuthService
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function login(string $email, string $password): ?array
    {
        $stmt = $this->pdo->prepare("SELECT id, nome, senha FROM usuarios WHERE email = ?");
        $stmt->execute([$email]);
        $usuario = $stmt->fetch(\PDO::FETCH_ASSOC);

        if ($usuario && password_verify($password, $usuario['senha'])) {
            return $usuario;
        }

        return null;
    }

    public function identifyUserType(string $email): string
    {
        if (strpos($email, "@LojaLTDA.com") !== false) {
            return 'fornecedor';
        }
        return 'cliente';
    }
}
