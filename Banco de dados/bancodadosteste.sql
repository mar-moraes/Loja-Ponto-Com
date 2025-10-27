-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Oct 27, 2025 at 02:34 AM
-- Server version: 8.0.34
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `bancodadosteste`
--

-- --------------------------------------------------------

--
-- Table structure for table `avaliacoes`
--

CREATE TABLE `avaliacoes` (
  `id` int NOT NULL,
  `usuario_id` int NOT NULL,
  `produto_id` int NOT NULL,
  `nota` int NOT NULL,
  `comentario` text,
  `data_avaliacao` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `carrinho`
--

CREATE TABLE `carrinho` (
  `id` int NOT NULL,
  `usuario_id` int NOT NULL,
  `data_atualizacao` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `carrinho`
--

INSERT INTO `carrinho` (`id`, `usuario_id`, `data_atualizacao`) VALUES
(3, 1, '2025-10-26 22:31:08');

-- --------------------------------------------------------

--
-- Table structure for table `carrinho_itens`
--

CREATE TABLE `carrinho_itens` (
  `id` int NOT NULL,
  `carrinho_id` int NOT NULL,
  `produto_id` int NOT NULL,
  `quantidade` int NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `carrinho_itens`
--

INSERT INTO `carrinho_itens` (`id`, `carrinho_id`, `produto_id`, `quantidade`) VALUES
(1, 3, 1, 1),
(2, 3, 5, 7);

-- --------------------------------------------------------

--
-- Table structure for table `categorias`
--

CREATE TABLE `categorias` (
  `id` int NOT NULL,
  `nome` varchar(100) NOT NULL,
  `descricao` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `categorias`
--

INSERT INTO `categorias` (`id`, `nome`, `descricao`) VALUES
(1, 'Brinquedos', 'Brinquedos de montar, bonecos e figuras de ação.'),
(2, 'TVs', 'Televisores, Smart TVs e acessórios de vídeo.'),
(3, 'Ferramentas', 'Ferramentas manuais, elétricas e equipamentos.'),
(4, 'Eletrônicos', 'Celulares, notebooks, computadores, áudio e acessórios.'),
(5, 'Robótica', 'Drones, Robôs quadrúpedes, Assistentes inteligentes, Entretenimento high-tech, Robôs inteligentes e Robôs autônomos.');

-- --------------------------------------------------------

--
-- Table structure for table `enderecos`
--

CREATE TABLE `enderecos` (
  `id` int NOT NULL,
  `usuario_id` int NOT NULL,
  `cep` varchar(10) NOT NULL,
  `rua` varchar(255) NOT NULL,
  `numero` varchar(50) NOT NULL,
  `complemento` varchar(100) DEFAULT NULL,
  `bairro` varchar(100) NOT NULL,
  `cidade` varchar(100) NOT NULL,
  `estado` varchar(2) NOT NULL,
  `pais` varchar(50) NOT NULL DEFAULT 'Brasil'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `entregas`
--

CREATE TABLE `entregas` (
  `id` int NOT NULL,
  `pedido_id` int NOT NULL,
  `codigo_rastreio` varchar(100) DEFAULT NULL,
  `transportadora` varchar(100) DEFAULT NULL,
  `data_envio` datetime DEFAULT NULL,
  `status_entrega` varchar(50) NOT NULL DEFAULT 'nao_enviado'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pagamentos`
--

CREATE TABLE `pagamentos` (
  `id` int NOT NULL,
  `pedido_id` int NOT NULL,
  `metodo` varchar(50) NOT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'pendente',
  `data_pagamento` datetime DEFAULT NULL,
  `valor_pago` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pedidos`
--

CREATE TABLE `pedidos` (
  `id` int NOT NULL,
  `usuario_id` int NOT NULL,
  `endereco_id` int NOT NULL,
  `data_pedido` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `status` varchar(50) NOT NULL DEFAULT 'pendente',
  `valor_total` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pedido_itens`
--

CREATE TABLE `pedido_itens` (
  `id` int NOT NULL,
  `pedido_id` int NOT NULL,
  `produto_id` int NOT NULL,
  `quantidade` int NOT NULL,
  `preco_unitario` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `produtos`
--

CREATE TABLE `produtos` (
  `id` int NOT NULL,
  `nome` varchar(255) NOT NULL,
  `descricao` text,
  `preco` decimal(10,2) NOT NULL,
  `categoria_id` int NOT NULL,
  `estoque` int NOT NULL DEFAULT '0',
  `imagem_url` varchar(1024) DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'ativo'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `produtos`
--

INSERT INTO `produtos` (`id`, `nome`, `descricao`, `preco`, `categoria_id`, `estoque`, `imagem_url`, `status`) VALUES
(1, 'Minifigura LEGO Ninjago - Zane', 'Minifigura original LEGO do personagem Zane...', 149.90, 1, 50, 'imagens/Produtos/Lego/Zane lego - 1.jpg', 'ativo'),
(4, 'Samsung Vision Ai Tv 65 Oled 4k S85f 2025', 'Processador Com Ai, Controle Por Gestos, Modo Ai, Painel 120hz, 7 Anos De Atualização', 7999.90, 2, 15, 'imagens/Produtos/Televisão/principal.webp', 'ativo'),
(5, 'Parafusadeira E Furadeira Impacto', 'Parafusadeira E Furadeira Impacto The Black Tools Tb-21pw 3/8 Cor Amarelo Frequência 50/60 Hz', 189.90, 3, 50, 'imagens/Produtos/Furadeira/principal.webp', 'ativo'),
(6, 'Notebook Gamer Msi Katana ', 'Notebook GAMER TOP MSI KATANA 15HX - Intel Core i7 14° Geração 14650HX 5.2Ghz - RTX 5070 8Gb DDR7 ( FULL POWER GPU ) - 64GB de memória DDR5 - 1 Tera NVME - Tela 15\" 165Hz - QHD+ - DCIP3 100%', 15900.00, 4, 50, 'imagens/Produtos/Notebook/principal.jpg', 'ativo'),
(7, 'Kit Mini Antena Starlink Internet via Satélite', 'O Kit Mini Antena Starlink oferece internet via satélite com velocidade de até 100 Mbps, ideal para uso em locais remotos. Compacto e leve (3,05 kg), funciona com alimentação 12V e é compatível com computadores, notebooks, smartphones e tablets. Conta com modo Access Point e conexão Wi-Fi no padrão 802.11ac. Inclui peças completas para montagem e garante conexão estável com tecnologia avançada da Starlink.', 799.00, 4, 15, 'imagens/Produtos/Antena/principal.jpg', 'ativo'),
(8, 'Unitree Go2 Robot Dog ', 'O Unitree Go2 Robot Dog é um robô quadrúpede inteligente projetado para mobilidade avançada, exploração e interação autônoma. Equipado com inteligência artificial, sensores de alta precisão e capacidade de navegação em diversos terrenos, ele oferece desempenho ágil e estável. Ideal para pesquisa, entretenimento, uso educacional e demonstrações tecnológicas. Representa uma das soluções mais modernas em robótica móvel.', 2518.52, 5, 200, 'imagens/Produtos/Robô/principal.webp', 'ativo');

-- --------------------------------------------------------

--
-- Table structure for table `produto_imagens`
--

CREATE TABLE `produto_imagens` (
  `id` int NOT NULL,
  `produto_id` int NOT NULL,
  `url_imagem` varchar(1024) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `produto_imagens`
--

INSERT INTO `produto_imagens` (`id`, `produto_id`, `url_imagem`) VALUES
(3, 4, 'imagens/Produtos/Televisão/1.webp'),
(4, 4, 'imagens/Produtos/Televisão/2.webp'),
(5, 4, 'imagens/Produtos/Televisão/3.webp'),
(6, 4, 'imagens/Produtos/Televisão/4.webp'),
(7, 5, 'imagens/Produtos/Furadeira/1.webp'),
(8, 5, 'imagens/Produtos/Furadeira/2.webp'),
(9, 5, 'imagens/Produtos/Furadeira/3.webp'),
(10, 5, 'imagens/Produtos/Furadeira/4.webp'),
(11, 6, 'imagens/Produtos/Notebook/1.jpg'),
(12, 6, 'imagens/Produtos/Notebook/2.jpg'),
(13, 6, 'imagens/Produtos/Notebook/3.jpg'),
(14, 6, 'imagens/Produtos/Notebook/4.jpg'),
(19, 8, 'imagens/Produtos/Robô/1.webp'),
(20, 8, 'imagens/Produtos/Robô/2.webp'),
(21, 8, 'imagens/Produtos/Robô/3.webp'),
(23, 1, 'imagens/Produtos/Lego/Zane lego - 2.jpg'),
(24, 1, 'imagens/Produtos/Lego/Zane lego.jpg'),
(25, 7, 'imagens/Produtos/Antena/1.jpg'),
(26, 7, 'imagens/Produtos/Antena/2.jpg'),
(27, 7, 'imagens/Produtos/Antena/3.jpg'),
(28, 7, 'imagens/Produtos/Antena/4.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int NOT NULL,
  `nome` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `cpf` varchar(14) NOT NULL,
  `senha` varchar(255) NOT NULL,
  `telefone` varchar(20) DEFAULT NULL,
  `data_cadastro` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `tipo` varchar(20) NOT NULL DEFAULT 'cliente'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `usuarios`
--

INSERT INTO `usuarios` (`id`, `nome`, `email`, `cpf`, `senha`, `telefone`, `data_cadastro`, `tipo`) VALUES
(1, 'Chris Brown', 'chrisbrown@gmail.com', '123456478910', '$2y$10$a54PQT1ifql6Im7K1O1KnuyWZ9nMUhZjbP4.AH3H9m6Se8Ql9MThC', '9912345678', '2025-10-25 21:14:47', 'cliente');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `avaliacoes`
--
ALTER TABLE `avaliacoes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `usuario_produto_UNIQUE` (`usuario_id`,`produto_id`),
  ADD KEY `fk_AVALIACOES_USUARIOS_idx` (`usuario_id`),
  ADD KEY `fk_AVALIACOES_PRODUTOS_idx` (`produto_id`);

--
-- Indexes for table `carrinho`
--
ALTER TABLE `carrinho`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `usuario_id_UNIQUE` (`usuario_id`);

--
-- Indexes for table `carrinho_itens`
--
ALTER TABLE `carrinho_itens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `carrinho_produto_UNIQUE` (`carrinho_id`,`produto_id`),
  ADD KEY `fk_CARRINHO_ITENS_CARRINHO_idx` (`carrinho_id`),
  ADD KEY `fk_CARRINHO_ITENS_PRODUTOS_idx` (`produto_id`);

--
-- Indexes for table `categorias`
--
ALTER TABLE `categorias`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nome_UNIQUE` (`nome`);

--
-- Indexes for table `enderecos`
--
ALTER TABLE `enderecos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_ENDERECOS_USUARIOS_idx` (`usuario_id`);

--
-- Indexes for table `entregas`
--
ALTER TABLE `entregas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `pedido_id_UNIQUE` (`pedido_id`);

--
-- Indexes for table `pagamentos`
--
ALTER TABLE `pagamentos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_PAGAMENTOS_PEDIDOS_idx` (`pedido_id`);

--
-- Indexes for table `pedidos`
--
ALTER TABLE `pedidos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_PEDIDOS_USUARIOS_idx` (`usuario_id`),
  ADD KEY `fk_PEDIDOS_ENDERECOS_idx` (`endereco_id`);

--
-- Indexes for table `pedido_itens`
--
ALTER TABLE `pedido_itens`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_PEDIDO_ITENS_PEDIDOS_idx` (`pedido_id`),
  ADD KEY `fk_PEDIDO_ITENS_PRODUTOS_idx` (`produto_id`);

--
-- Indexes for table `produtos`
--
ALTER TABLE `produtos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_PRODUTOS_CATEGORIAS_idx` (`categoria_id`);

--
-- Indexes for table `produto_imagens`
--
ALTER TABLE `produto_imagens`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_IMAGENS_PRODUTOS_idx` (`produto_id`);

--
-- Indexes for table `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email_UNIQUE` (`email`),
  ADD UNIQUE KEY `cpf_UNIQUE` (`cpf`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `avaliacoes`
--
ALTER TABLE `avaliacoes`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `carrinho`
--
ALTER TABLE `carrinho`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `carrinho_itens`
--
ALTER TABLE `carrinho_itens`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `categorias`
--
ALTER TABLE `categorias`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `enderecos`
--
ALTER TABLE `enderecos`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `entregas`
--
ALTER TABLE `entregas`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pagamentos`
--
ALTER TABLE `pagamentos`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pedidos`
--
ALTER TABLE `pedidos`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pedido_itens`
--
ALTER TABLE `pedido_itens`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `produtos`
--
ALTER TABLE `produtos`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `produto_imagens`
--
ALTER TABLE `produto_imagens`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `avaliacoes`
--
ALTER TABLE `avaliacoes`
  ADD CONSTRAINT `fk_AVALIACOES_PRODUTOS` FOREIGN KEY (`produto_id`) REFERENCES `produtos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_AVALIACOES_USUARIOS` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `carrinho`
--
ALTER TABLE `carrinho`
  ADD CONSTRAINT `fk_CARRINHO_USUARIOS` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `carrinho_itens`
--
ALTER TABLE `carrinho_itens`
  ADD CONSTRAINT `fk_CARRINHO_ITENS_CARRINHO` FOREIGN KEY (`carrinho_id`) REFERENCES `carrinho` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_CARRINHO_ITENS_PRODUTOS` FOREIGN KEY (`produto_id`) REFERENCES `produtos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `enderecos`
--
ALTER TABLE `enderecos`
  ADD CONSTRAINT `fk_ENDERECOS_USUARIOS` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `entregas`
--
ALTER TABLE `entregas`
  ADD CONSTRAINT `fk_ENTREGAS_PEDIDOS` FOREIGN KEY (`pedido_id`) REFERENCES `pedidos` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE;

--
-- Constraints for table `pagamentos`
--
ALTER TABLE `pagamentos`
  ADD CONSTRAINT `fk_PAGAMENTOS_PEDIDOS` FOREIGN KEY (`pedido_id`) REFERENCES `pedidos` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE;

--
-- Constraints for table `pedidos`
--
ALTER TABLE `pedidos`
  ADD CONSTRAINT `fk_PEDIDOS_ENDERECOS` FOREIGN KEY (`endereco_id`) REFERENCES `enderecos` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_PEDIDOS_USUARIOS` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE;

--
-- Constraints for table `pedido_itens`
--
ALTER TABLE `pedido_itens`
  ADD CONSTRAINT `fk_PEDIDO_ITENS_PEDIDOS` FOREIGN KEY (`pedido_id`) REFERENCES `pedidos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_PEDIDO_ITENS_PRODUTOS` FOREIGN KEY (`produto_id`) REFERENCES `produtos` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE;

--
-- Constraints for table `produtos`
--
ALTER TABLE `produtos`
  ADD CONSTRAINT `fk_PRODUTOS_CATEGORIAS` FOREIGN KEY (`categoria_id`) REFERENCES `categorias` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE;

--
-- Constraints for table `produto_imagens`
--
ALTER TABLE `produto_imagens`
  ADD CONSTRAINT `fk_IMAGENS_PRODUTOS` FOREIGN KEY (`produto_id`) REFERENCES `produtos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
