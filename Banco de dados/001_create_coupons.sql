-- Criação da tabela de Cupons
CREATE TABLE `cupons` (
  `id` int NOT NULL AUTO_INCREMENT,
  `codigo` varchar(50) NOT NULL,
  `descricao` text,
  `tipo_desconto` enum('porcentagem','fixo') NOT NULL DEFAULT 'porcentagem',
  `valor_desconto` decimal(10,2) NOT NULL,
  `valor_minimo` decimal(10,2) DEFAULT '0.00',
  `data_inicio` datetime DEFAULT NULL,
  `data_fim` datetime DEFAULT NULL,
  `limite_uso` int DEFAULT NULL,
  `ativo` tinyint(1) NOT NULL DEFAULT '1',
  `usuario_id` int DEFAULT NULL COMMENT 'ID do fornecedor, se for cupom específico',
  PRIMARY KEY (`id`),
  UNIQUE KEY `codigo_UNIQUE` (`codigo`),
  KEY `fk_CUPONS_USUARIOS_idx` (`usuario_id`),
  CONSTRAINT `fk_CUPONS_USUARIOS` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Criação da tabela de Uso de Cupons
CREATE TABLE `cupom_uso` (
  `id` int NOT NULL AUTO_INCREMENT,
  `cupom_id` int NOT NULL,
  `usuario_id` int NOT NULL,
  `pedido_id` int NOT NULL,
  `data_uso` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_CUPOM_USO_CUPONS_idx` (`cupom_id`),
  KEY `fk_CUPOM_USO_USUARIOS_idx` (`usuario_id`),
  KEY `fk_CUPOM_USO_PEDIDOS_idx` (`pedido_id`),
  CONSTRAINT `fk_CUPOM_USO_CUPONS` FOREIGN KEY (`cupom_id`) REFERENCES `cupons` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_CUPOM_USO_PEDIDOS` FOREIGN KEY (`pedido_id`) REFERENCES `pedidos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_CUPOM_USO_USUARIOS` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Alteração da tabela Pedidos para registrar o desconto
ALTER TABLE `pedidos` 
ADD COLUMN `cupom_id` int DEFAULT NULL AFTER `usuario_id`,
ADD COLUMN `valor_desconto` decimal(10,2) DEFAULT '0.00' AFTER `valor_total`,
ADD KEY `fk_PEDIDOS_CUPONS_idx` (`cupom_id`),
ADD CONSTRAINT `fk_PEDIDOS_CUPONS` FOREIGN KEY (`cupom_id`) REFERENCES `cupons` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;
