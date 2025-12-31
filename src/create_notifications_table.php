<?php
require __DIR__ . '/../Banco de dados/conexao.php';

try {
    $sql = "
    CREATE TABLE IF NOT EXISTS `notificacoes` (
      `id` int NOT NULL AUTO_INCREMENT,
      `usuario_id` int NOT NULL,
      `mensagem` text,
      `lida` tinyint(1) NOT NULL DEFAULT '0',
      `tipo` varchar(50) DEFAULT 'primary',
      `link` varchar(255) DEFAULT NULL,
      `data_criacao` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
      PRIMARY KEY (`id`),
      KEY `fk_NOTIFICACOES_USUARIOS_idx` (`usuario_id`),
      CONSTRAINT `fk_NOTIFICACOES_USUARIOS` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
    ";

    $pdo->exec($sql);
    echo "Tabela 'notificacoes' criada com sucesso!" . PHP_EOL;
} catch (PDOException $e) {
    die("Erro ao criar tabela: " . $e->getMessage() . PHP_EOL);
}
