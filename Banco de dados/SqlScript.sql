-- -----------------------------------------------------
-- Tabela `USUARIOS`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `USUARIOS` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `nome` VARCHAR(255) NOT NULL,
  `email` VARCHAR(255) NOT NULL,
  `cpf` VARCHAR(14) NOT NULL,
  `senha` VARCHAR(255) NOT NULL,
  `telefone` VARCHAR(20) NULL,
  `data_cadastro` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `tipo` VARCHAR(20) NOT NULL DEFAULT 'cliente',
  PRIMARY KEY (`id`),
  UNIQUE INDEX `email_UNIQUE` (`email` ASC),
  UNIQUE INDEX `cpf_UNIQUE` (`cpf` ASC)
) 
ENGINE = InnoDB
DEFAULT CHARSET=utf8mb4;


-- -----------------------------------------------------
-- Tabela `CATEGORIAS`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `CATEGORIAS` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `nome` VARCHAR(100) NOT NULL,
  `descricao` TEXT NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `nome_UNIQUE` (`nome` ASC)
)
ENGINE = InnoDB
DEFAULT CHARSET=utf8mb4;


-- -----------------------------------------------------
-- Tabela `PRODUTOS`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `PRODUTOS` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `nome` VARCHAR(255) NOT NULL,
  `descricao` TEXT NULL,
  `preco` DECIMAL(10,2) NOT NULL,
  `categoria_id` INT NOT NULL,
  `estoque` INT NOT NULL DEFAULT 0,
  `imagem_url` VARCHAR(1024) NULL,
  `status` VARCHAR(20) NOT NULL DEFAULT 'ativo',
  PRIMARY KEY (`id`),
  INDEX `fk_PRODUTOS_CATEGORIAS_idx` (`categoria_id` ASC),
  CONSTRAINT `fk_PRODUTOS_CATEGORIAS`
    FOREIGN KEY (`categoria_id`)
    REFERENCES `CATEGORIAS` (`id`)
    ON DELETE RESTRICT
    ON UPDATE CASCADE
)
ENGINE = InnoDB
DEFAULT CHARSET=utf8mb4;


-- -----------------------------------------------------
-- Tabela `ENDERECOS`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `ENDERECOS` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `usuario_id` INT NOT NULL,
  `cep` VARCHAR(10) NOT NULL,
  `rua` VARCHAR(255) NOT NULL,
  `numero` VARCHAR(50) NOT NULL,
  `complemento` VARCHAR(100) NULL,
  `bairro` VARCHAR(100) NOT NULL,
  `cidade` VARCHAR(100) NOT NULL,
  `estado` VARCHAR(2) NOT NULL,
  `pais` VARCHAR(50) NOT NULL DEFAULT 'Brasil',
  PRIMARY KEY (`id`),
  INDEX `fk_ENDERECOS_USUARIOS_idx` (`usuario_id` ASC),
  CONSTRAINT `fk_ENDERECOS_USUARIOS`
    FOREIGN KEY (`usuario_id`)
    REFERENCES `USUARIOS` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE
)
ENGINE = InnoDB
DEFAULT CHARSET=utf8mb4;

-- Correção: O ALTER TABLE deve ficar fora do CREATE TABLE
ALTER TABLE `ENDERECOS` 
ADD COLUMN `destinatario_nome` VARCHAR(255) NULL AFTER `pais`,
ADD COLUMN `destinatario_telefone` VARCHAR(20) NULL AFTER `destinatario_nome`;


-- -----------------------------------------------------
-- Tabela `CARRINHO`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `CARRINHO` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `usuario_id` INT NOT NULL,
  `data_atualizacao` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `usuario_id_UNIQUE` (`usuario_id` ASC),
  CONSTRAINT `fk_CARRINHO_USUARIOS`
    FOREIGN KEY (`usuario_id`)
    REFERENCES `USUARIOS` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE
)
ENGINE = InnoDB
DEFAULT CHARSET=utf8mb4;


-- -----------------------------------------------------
-- Tabela `CARRINHO_ITENS`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `CARRINHO_ITENS` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `carrinho_id` INT NOT NULL,
  `produto_id` INT NOT NULL,
  `quantidade` INT NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  INDEX `fk_CARRINHO_ITENS_CARRINHO_idx` (`carrinho_id` ASC),
  INDEX `fk_CARRINHO_ITENS_PRODUTOS_idx` (`produto_id` ASC),
  UNIQUE INDEX `carrinho_produto_UNIQUE` (`carrinho_id` ASC, `produto_id` ASC),
  CONSTRAINT `fk_CARRINHO_ITENS_CARRINHO`
    FOREIGN KEY (`carrinho_id`)
    REFERENCES `CARRINHO` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_CARRINHO_ITENS_PRODUTOS`
    FOREIGN KEY (`produto_id`)
    REFERENCES `PRODUTOS` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE
)
ENGINE = InnoDB
DEFAULT CHARSET=utf8mb4;


-- -----------------------------------------------------
-- Tabela `PEDIDOS`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `PEDIDOS` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `usuario_id` INT NOT NULL,
  `endereco_id` INT NOT NULL,
  `data_pedido` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `status` VARCHAR(50) NOT NULL DEFAULT 'pendente',
  `valor_total` DECIMAL(10,2) NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_PEDIDOS_USUARIOS_idx` (`usuario_id` ASC),
  INDEX `fk_PEDIDOS_ENDERECOS_idx` (`endereco_id` ASC),
  CONSTRAINT `fk_PEDIDOS_USUARIOS`
    FOREIGN KEY (`usuario_id`)
    REFERENCES `USUARIOS` (`id`)
    ON DELETE RESTRICT
    ON UPDATE CASCADE,
  CONSTRAINT `fk_PEDIDOS_ENDERECOS`
    FOREIGN KEY (`endereco_id`)
    REFERENCES `ENDERECOS` (`id`)
    ON DELETE RESTRICT
    ON UPDATE CASCADE
)
ENGINE = InnoDB
DEFAULT CHARSET=utf8mb4;


-- -----------------------------------------------------
-- Tabela `PEDIDO_ITENS`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `PEDIDO_ITENS` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `pedido_id` INT NOT NULL,
  `produto_id` INT NOT NULL,
  `quantidade` INT NOT NULL,
  `preco_unitario` DECIMAL(10,2) NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_PEDIDO_ITENS_PEDIDOS_idx` (`pedido_id` ASC),
  INDEX `fk_PEDIDO_ITENS_PRODUTOS_idx` (`produto_id` ASC),
  CONSTRAINT `fk_PEDIDO_ITENS_PEDIDOS`
    FOREIGN KEY (`pedido_id`)
    REFERENCES `PEDIDOS` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_PEDIDO_ITENS_PRODUTOS`
    FOREIGN KEY (`produto_id`)
    REFERENCES `PRODUTOS` (`id`)
    ON DELETE RESTRICT
    ON UPDATE CASCADE
)
ENGINE = InnoDB
DEFAULT CHARSET=utf8mb4;


-- -----------------------------------------------------
-- Tabela `PAGAMENTOS`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `PAGAMENTOS` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `pedido_id` INT NOT NULL,
  `metodo` VARCHAR(50) NOT NULL,
  `status` VARCHAR(50) NOT NULL DEFAULT 'pendente',
  `data_pagamento` DATETIME NULL,
  `valor_pago` DECIMAL(10,2) NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_PAGAMENTOS_PEDIDOS_idx` (`pedido_id` ASC),
  CONSTRAINT `fk_PAGAMENTOS_PEDIDOS`
    FOREIGN KEY (`pedido_id`)
    REFERENCES `PEDIDOS` (`id`)
    ON DELETE RESTRICT
    ON UPDATE CASCADE
)
ENGINE = InnoDB
DEFAULT CHARSET=utf8mb4;


-- -----------------------------------------------------
-- Tabela `ENTREGAS`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `ENTREGAS` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `pedido_id` INT NOT NULL,
  `codigo_rastreio` VARCHAR(100) NULL,
  `transportadora` VARCHAR(100) NULL,
  `data_envio` DATETIME NULL,
  `status_entrega` VARCHAR(50) NOT NULL DEFAULT 'nao_enviado',
  PRIMARY KEY (`id`),
  UNIQUE INDEX `pedido_id_UNIQUE` (`pedido_id` ASC),
  CONSTRAINT `fk_ENTREGAS_PEDIDOS`
    FOREIGN KEY (`pedido_id`)
    REFERENCES `PEDIDOS` (`id`)
    ON DELETE RESTRICT
    ON UPDATE CASCADE
)
ENGINE = InnoDB
DEFAULT CHARSET=utf8mb4;


-- -----------------------------------------------------
-- Tabela `AVALIACOES`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `AVALIACOES` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `usuario_id` INT NOT NULL,
  `produto_id` INT NOT NULL,
  `nota` INT NOT NULL,
  `comentario` TEXT NULL,
  `data_avaliacao` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `fk_AVALIACOES_USUARIOS_idx` (`usuario_id` ASC),
  INDEX `fk_AVALIACOES_PRODUTOS_idx` (`produto_id` ASC),
  UNIQUE INDEX `usuario_produto_UNIQUE` (`usuario_id` ASC, `produto_id` ASC),
  CONSTRAINT `fk_AVALIACOES_USUARIOS`
    FOREIGN KEY (`usuario_id`)
    REFERENCES `USUARIOS` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_AVALIACOES_PRODUTOS`
    FOREIGN KEY (`produto_id`)
    REFERENCES `PRODUTOS` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE
)
ENGINE = InnoDB
DEFAULT CHARSET=utf8mb4;