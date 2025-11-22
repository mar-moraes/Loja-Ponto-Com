-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Nov 22, 2025 at 02:26 AM
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

--
-- Dumping data for table `avaliacoes`
--

INSERT INTO `avaliacoes` (`id`, `usuario_id`, `produto_id`, `nota`, `comentario`, `data_avaliacao`) VALUES
(5, 4, 23, 5, 'Melhor motor da história !', '2025-10-28 19:49:04'),
(6, 3, 23, 3, 'Meh ! Não gostei', '2025-10-28 22:51:03'),
(11, 3, 27, 5, '', '2025-10-28 22:10:45'),
(16, 6, 30, 5, '', '2025-11-21 15:03:54'),
(19, 6, 38, 4, '', '2025-11-21 22:21:16');

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
(4, 3, '2025-10-27 20:48:01'),
(5, 4, '2025-10-28 16:23:10');

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
(5, 'Robótica', 'Drones, Robôs quadrúpedes, Assistentes inteligentes, Entretenimento high-tech, Robôs inteligentes e Robôs autônomos.'),
(6, 'Esporte e Fitness', 'Equipamentos de ginástica, bicicletas ergométricas, acessórios esportivos e de fitness.'),
(7, 'Eletrodomésticos', 'Aparelhos elétricos para uso doméstico, como cafeteiras, liquidificadores, batedeiras e outros.'),
(8, 'Energia Solar', 'Painéis solares, inversores, baterias e equipamentos para geração de energia solar.'),
(9, 'Motores e Equipamentos Agrícolas', 'Motores estacionários, geradores, motobombas e equipamentos para uso agrícola e industrial.'),
(10, 'Móveis e Escritório', 'Cadeiras de escritório, cadeiras gamer, mesas e outros móveis para casa e escritório.');

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

--
-- Dumping data for table `enderecos`
--

INSERT INTO `enderecos` (`id`, `usuario_id`, `cep`, `rua`, `numero`, `complemento`, `bairro`, `cidade`, `estado`, `pais`) VALUES
(6, 4, '09687-100', 'Rua General Izidoro Dias Lopes', '314', 'Casa', 'Paulicéia', 'São Bernardo do Campo', 'SP', 'Brasil'),
(7, 8, '13058-011', 'Rua Padre Josimo Moraes Tavares', 'S/N', 'casa', 'Conjunto Habitacional Parque Itajaí', 'Campinas', 'SP', 'Brasil'),
(11, 6, '13184000', 'Rua Sete de Setembro', '123', 'casa', 'Parque Ortolândia', 'Hortolândia', 'SP', 'Brasil');

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
  `endereco_id` int DEFAULT NULL,
  `data_pedido` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `status` varchar(50) NOT NULL DEFAULT 'pendente',
  `valor_total` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `pedidos`
--

INSERT INTO `pedidos` (`id`, `usuario_id`, `endereco_id`, `data_pedido`, `status`, `valor_total`) VALUES
(1, 4, 6, '2025-10-27 10:00:00', 'processando', 839.00),
(2, 4, 6, '2025-10-28 20:34:28', 'processando', 24999.99),
(3, 3, NULL, '2025-10-28 20:50:28', 'processando', 135.98),
(4, 3, NULL, '2025-10-28 20:54:21', 'processando', 75005.96),
(5, 3, NULL, '2025-10-28 22:08:20', 'processando', 194.99),
(6, 3, NULL, '2025-10-28 22:24:31', 'processando', 634.99),
(7, 6, NULL, '2025-11-21 15:03:10', 'processando', 25005.98),
(8, 8, NULL, '2025-11-21 19:30:35', 'processando', 1509.99),
(9, 8, 7, '2025-11-21 19:43:02', 'processando', 629.00);

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

--
-- Dumping data for table `pedido_itens`
--

INSERT INTO `pedido_itens` (`id`, `pedido_id`, `produto_id`, `quantidade`, `preco_unitario`) VALUES
(1, 1, 23, 1, 629.00),
(2, 1, 22, 1, 210.00),
(3, 2, 30, 1, 24999.99),
(4, 3, 32, 1, 129.99),
(5, 4, 30, 3, 24999.99),
(6, 5, 22, 1, 189.00),
(7, 6, 23, 1, 629.00),
(8, 7, 30, 1, 24999.99),
(9, 8, 24, 1, 1504.00),
(10, 9, 23, 1, 629.00);

-- --------------------------------------------------------

--
-- Table structure for table `produtos`
--

CREATE TABLE `produtos` (
  `id` int NOT NULL,
  `nome` varchar(255) DEFAULT NULL,
  `descricao` text,
  `preco` decimal(10,2) DEFAULT NULL,
  `desconto` int DEFAULT '0',
  `categoria_id` int DEFAULT NULL,
  `estoque` int DEFAULT '0',
  `imagem_url` varchar(1024) DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'ativo',
  `usuario_id` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `produtos`
--

INSERT INTO `produtos` (`id`, `nome`, `descricao`, `preco`, `desconto`, `categoria_id`, `estoque`, `imagem_url`, `status`, `usuario_id`) VALUES
(22, 'Parafusadeira E Furadeira Impacto', '--- CARACTERÍSTICAS ---\nÉ sem fio: Sim\nCom função percutor: Sim\nTamanho do mandril: 10 mm\n\n--- ESPECIFICAÇÕES ---\nCom função reversa.\nVem com maleta de transporte.\nSua frequência é de 60Hz.\nPossui função parafusadeira.\nInclui função martelete.\n\n--- DESCRIÇÃO ---\nA Parafusadeira/Furadeira de Impacto Profissional The Black Tools TB-21PW 21V é ideal para uso profissional e doméstico, oferecendo potência, durabilidade e praticidade. Com velocidade variável, função reversível e impacto, garante excelente desempenho em metal, madeira e plástico. Possui empunhadura Soft Grip e indicador de carga da bateria para maior conforto e controle. Acompanha maleta completa com brocas, soquetes, adaptadores e bits. Compacta e leve, é a escolha certa para quem busca eficiência e qualidade nas tarefas do dia a dia.\r\n', 210.00, 10, 3, 24, '../assets/imagens/Produtos/Furadeira/principal.webp', 'ativo', NULL),
(23, 'Motor Estacionário Gasolina', '--- CARACTERÍSTICAS ---\nTipo De Motor: Monocilíndrico 4T OHV\nRefrigeração: Ar\nCombustível: Gasolina\nCilindrada: 208 cc\nPotência Máxima: 7 Hp\nRotação Máxima: 3600 RPM\n\n--- ESPECIFICAÇÕES ---\nTipo de ignição: manual.\nDimensões: 39cm de largura x 39cm de comprimento x 39cm de altura\nPeso: 16g.\nTipo de combustível: Gasolina.\n\n--- DESCRIÇÃO ---\nO Motor Estacionário Kawashima 7HP é potente, econômico e ideal para diversas aplicações, como microtratores, geradores e motobombas. Com motor 4 tempos monocilíndrico a gasolina, oferece até 7HP de potência e 3600 RPM. Possui partida manual, sensor de óleo e refrigeração a ar, garantindo durabilidade e segurança. Compacto e robusto, é uma excelente escolha para trabalhos que exigem desempenho e confiabilidade.\r\n', 629.00, 0, 9, 23, '../assets/imagens/Produtos/Motor/principal.webp', 'ativo', NULL),
(24, 'Bicicleta Ergométrica Para Spinning', '--- CARACTERÍSTICAS ---\nPeso máximo suportado: 120 kg\nÉ dobrável: Não\nCor: Preto/Vermelho\nMarca: Odin Fit\nAltura: 1.2 m\n\n--- ESPECIFICAÇÕES ---\nSistema de resistência mecânica que é operado a partir do botão de ajuste.\nO guidão se adapta às necessidades do usuário.\nPara treinar sem impacto e melhorar a saúde integral.\n\n--- DESCRIÇÃO ---\nA Bicicleta Ergométrica Spinning PACE3000 Odin Fit é ideal para uso residencial, proporcionando exercícios aeróbicos que fortalecem a musculatura e melhoram o condicionamento cardiorrespiratório. Oferece benefícios como queima calórica, aumento da disposição e baixo impacto nas articulações, sendo indicada também para reabilitação. Possui ajustes no selim, guidão e pedais, além de roda de inércia de 8kg com resistência mecânica ajustável. Inclui monitor multifunções com sensor de pulso e suporte para tablet ou smartphone.\r\n', 1600.00, 6, 6, 99, '../assets/imagens/Produtos/Bicicleta Ergométrica/principal.webp', 'ativo', NULL),
(25, 'Cadeira De Escritório Ergonômica Giratória ', '--- CARACTERÍSTICAS ---\nÉ gamer: Sim\nCom apoio de braços ajustável: Sim\nÉ giratória: Sim\nMaterial Do Estofamento: Mesh Espuma Látex\n\n--- ESPECIFICAÇÕES ---\nIdeal para uso em escritório ou setup gamer, unindo estilo e funcionalidade.\nPossui design ergonômico com encosto reclinável e apoio de cabeça ajustável em dois sentidos\n\n--- DESCRIÇÃO ---\nCadeira De Escritório Ergonômica Com Apoio De Braços 3D Plus\r\n\r\nApresentamos a nossa excepcional Cadeira de Escritório Ergonômica 3D Plus, uma fusão perfeita de design elegante e funcionalidade excepcional. O revestimento em tecido Mesh oferece conforto respirável, enquanto o encosto de cabeça ajustável em dois sentidos proporciona suporte personalizado. Com encosto reclinável, altura ajustável e apoio de braço reversível, esta cadeira se adapta perfeitamente às suas preferências.', 599.00, 5, 9, 25, '../assets/imagens/Produtos/Cadeira/principal.webp', 'ativo', NULL),
(26, 'Antena Starlink Mini Branca', '--- CARACTERÍSTICAS ---\nMarca: Starlink\nModelo: Mini\nTipo de entrega: Omnidirecional\nCor: Branco\n\n--- ESPECIFICAÇÕES ---\nUnidades por kit: 1.\nFormato de venda: Unidade.\nÉ uma antena de internet via satélite.\n\n--- DESCRIÇÃO ---\nA Antena Starlink Mini Branco oferece conexão rápida e estável, mesmo em regiões remotas, com velocidade de até 150 MB e alcance de 40 metros. Seu cabo de 15 metros permite flexibilidade na instalação, garantindo o melhor sinal. Leve e prática, pesa apenas 1,1 kg e consome 60 W de potência. Fabricada pela renomada Starlink, é homologada pela Anatel, assegurando qualidade e segurança. Ideal para quem busca desempenho em streaming, jogos e videoconferências, a Starlink Mini combina eficiência, portabilidade e alta tecnologia em um único produto.', 696.00, 0, 4, 100, '../assets/imagens/Produtos/Antena/0.jpg', 'ativo', NULL),
(27, 'Cafeteira Elétrica Electrolux', '--- CARACTERÍSTICAS ---\nNome da marca: ‎Electrolux\nFabricante: Electrolux\nModelo: ‎ECM25\nPeças para montagem: ‎Filtro permanente, Jarra\nCor: ‎Granite Grey\n\n--- ESPECIFICAÇÕES ---\nFiltro permanente removível\nTimer programável de 24 horas\nDesign moderno em aço inox escovado\n\n--- DESCRIÇÃO ---\nA Cafeteira Elétrica Programável Experience Electrolux possui capacidade de 1,2L, permitindo preparar até 30 cafezinhos. Com timer de 24 horas, painel programável e função manter aquecido, garante praticidade e café quente a qualquer momento. Conta com sistema corta pingos, desligamento automático e placa de aquecimento antiaderente. Seu filtro permanente removível dispensa o uso de filtros de papel, sendo mais econômico e sustentável. Possui acabamento em aço inox escovado e indicador de nível de água para maior modernidade e facilidade no uso.\r\n', 259.00, 12, 7, 20, '../assets/imagens/Produtos/Cafeteira/0.jpg', 'ativo', NULL),
(28, 'Notebook Gamer Msi Katana', '--- CARACTERÍSTICAS ---\r\nMarca de placa gráfica dedicada: NVIDIA\r\nLinha de placa gráfica dedicada:  RTX 5070\r\nModelo de placa gráfica dedicada:  RTX5070 8Gb\r\n\r\n--- ESPECIFICAÇÕES ---\r\nProcessador: Intel Core i7 14650HX\r\nVersão do sistema operacional: 11\r\nEdição do sistema operacional: Home\r\nNome do sistema operacional: Windows\r\nResolução da tela: 2560 X 1440\r\n\r\n--- DESCRIÇÃO ---\r\nO Notebook Gamer MSI Katana combina potência e desempenho para jogadores e profissionais exigentes. Equipado com o processador Intel Core i7-14650HX e a placa NVIDIA GeForce RTX 5070, oferece alto rendimento em jogos e tarefas intensas. Sua tela de 15,6” QHD 165Hz garante imagens fluidas e detalhadas. Com 64GB de RAM e SSD ultrarrápido, proporciona carregamentos ágeis e multitarefa sem travamentos. Possui design robusto e moderno, ideal para uso prolongado. Uma máquina feita para quem busca desempenho e imersão máxima.\r\n', 15900.00, 0, 4, 50, '../assets/imagens/Produtos/Notebook/0.jpg', 'ativo', NULL),
(29, 'Placa Solar 550w Peimar Monocristalino', '--- CARACTERÍSTICAS ---\nMarca: Peimar\nModelo: 550w\nVoltagem de circuito aberto: 49.6V\nQuantidade de células: 144\nCor: Prateado\n\n--- ESPECIFICAÇÕES ---\nIdeal para sistemas residenciais, rurais e comerciais de pequeno e médio porte.\nProduto sustentável, reduz custos de energia e contribui para o meio ambiente.\nTecnologia monocristalina de 144 células, que garante alta eficiência na captação de energia solar\n\n--- DESCRIÇÃO ---\nPainel Solar Monocristalino Peimar OR10H550M de 550W, com tecnologia italiana Half Cell M10 | PERC, garantindo alta eficiência de 21,28% e excelente desempenho em projetos residenciais, comerciais e industriais. O kit inclui 3 unidades, cada uma com estrutura resistente em alumínio anodizado e vidro temperado antirreflexo. Suporta condições extremas, possui classe de proteção IP67 e certificações internacionais (IEC 61215 / 61730). Oferece 30 anos de garantia de performance e 25 anos de fabricação. Produto original com NF-e e seguro de responsabilidade civil incluso.\r\n', 2499.00, 3, 8, 500, '../assets/imagens/Produtos/Painel solar/0.webp', 'ativo', NULL),
(30, 'Unitree Go2', '--- CARACTERÍSTICAS ---\n Número do modelo: ‎Go2 Pro\nMontagem necessária: ‎Não\nCâmera grande angular HD: ‎Suportado\nLiDAR 3D super grande angular: Suportado\nCor: Prateado\n\n--- ESPECIFICAÇÕES ---\nEquipado com LiDAR 4D ultra-amplo (360° × 90°), oferecendo reconhecimento preciso do ambiente.\nMovimentos ágeis como correr, saltar, rolar e escalar, com torque de articulação de até 45 N·m.\nSistema de inteligência artificial aprimorado por GPT, permitindo interações dinâmicas.\nConectividade avançada com Wi-Fi 6, Bluetooth e 4G.\nAutonomia de 1 a 2 horas com bateria de 8.000 mAh.\n\n--- DESCRIÇÃO ---\nO Unitree Go2 é um robô quadrúpede avançado, projetado para interações dinâmicas e navegação inteligente. Equipado com LiDAR 4D ultra-amplo, oferece reconhecimento preciso de ambientes em 360° × 90°. Seu sistema de inteligência artificial, aprimorado por GPT, permite movimentos ágeis como correr, saltar e escalar. Com bateria de 8.000 mAh, proporciona autonomia de 1 a 2 horas. Possui conectividade Wi-Fi 6, Bluetooth e 4G, além de um sistema de seguimento inteligente ISS 2.0. Ideal para pesquisas, educação STEM e aplicações industriais.', 24999.99, 0, 5, 195, '../assets/imagens/Produtos/Robô/0.webp', 'ativo', NULL),
(31, 'Televisão Samsung Vision AI TV 55\" OLED 4K S85F 2025', '--- CARACTERÍSTICAS ---\nResolução: 4k\nÉ smart: ‎Sim\nTipo de tela: OLED\nAplicativos incorporados: Netflix\nQuantidade de portas HDM: 4\n\n--- ESPECIFICAÇÕES ---\nAlexa Embutido.\nEquipado com conexão USB.\nInclui controle remoto.\nConta com wi-fi e porto de rede.\n\n--- DESCRIÇÃO ---\nA Samsung Vision AI TV 55\" OLED 4K S85F 2025 oferece uma experiência de entretenimento imersiva com design sofisticado e tecnologia avançada. Seu painel OLED de 55\" garante cores vibrantes e pretos profundos, proporcionando contraste excepcional e imagens nítidas em 4K. A tecnologia Vision AI utiliza inteligência artificial para otimizar imagem e som em tempo real, adaptando-se às condições do ambiente. Conta com Tizen, oferecendo acesso rápido a apps de streaming e assistentes de voz integrados como Alexa e Google Assistant. O áudio é aprimorado pelo Dolby Atmos, proporcionando som envolvente e cinema em casa. Possui design ultra-fino elegante com base central estável e moderna. Além disso, oferece conectividade avançada com múltiplas portas HDMI, USB e suporte a Wi-Fi 6 para navegação ágil e contínua.', 5099.00, 0, 2, 100, '../assets/imagens/Produtos/Televisão/principal.webp', 'ativo', NULL),
(32, 'Tomada Inteligente Wifi', '--- CARACTERÍSTICAS ---\nMarca: Coibeu\nVoltagem: ‎110 Volts, 220 Volts\nTipo de fonte de energia: AC\nCertificação: ‎INMETRO:0124\nFabricante: ‎C &amp; B Global Importação e Exportação LTDA\n\n--- ESPECIFICAÇÕES ---\nTomada inteligente Wi-Fi com 3 tomadas AC, 2 portas USB e 1 porta Type-C, permitindo alimentar até 6 dispositivos simultaneamente.\nCarcaça resistente e materiais de alta condutividade, com proteção contra sobrecarga e raios.\nControle remoto via aplicativo, com funções de temporizador e integração com Alexa e Google Assistant.\n\n--- DESCRIÇÃO ---\nFiltro de linha multifuncional com 3 tomadas, 2 portas USB e 1 Type-C, permitindo alimentar até 6 dispositivos simultaneamente. Possui carcaça resistente, materiais de alta condutividade e proteção contra sobrecarga e raios. Conta com interruptor centralizado e indicadores luminosos para uso prático e seguro. Permite controle remoto via aplicativo, temporização e integração com Alexa e Google Assistant. Oferece estatísticas de consumo de energia no app (função paga).\r\n', 144.43, 10, 4, 49, '../assets/imagens/Produtos/Tomada/0.jpg', 'ativo', NULL),
(38, 'Mini figurinha Lego Ninjago', '--- CARACTERÍSTICAS ---\nPersonagem: Zane ZX (Ninja do Gelo)\nMarca: LEGO\nMaterial: Plástico ABS de alta qualidade\n\n--- ESPECIFICAÇÕES ---\nMini figura inspirada na temporada ZX, clássica para colecionadores.\nÓtima opção para fãs de Ninjago, coleções ou para complementar sets existentes.\nDetalhamento rico no torso e na face, incluindo impressão de armas e traje ninja.\nFabricada em material resistente e durável.\nÓtima opção para fãs de Ninjago, coleções ou para complementar sets existentes.\n\n--- DESCRIÇÃO ---\nA mini figurinha Zane ZX traz o icônico Ninja do Gelo em sua versão clássica usada na fase “ZX” da série LEGO Ninjago. Com armadura dourada, máscara ninja branca e detalhes impressos em alta qualidade, esta figura é perfeita para fãs da saga e colecionadores que buscam completar sua equipe de ninjas.\r\n\r\nO item acompanha armas e acessórios característicos do personagem, tornando a experiência mais autêntica e divertida. Ideal para exposição, presente ou uso em brincadeiras temáticas.', 210.00, 10, 1, 50, '../assets/imagens/Produtos/69210d2dd88af.jpg', 'ativo', 6);

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
(116, 29, '../assets/imagens/Produtos/Painel solar/1.webp'),
(117, 29, '../assets/imagens/Produtos/Painel solar/2.webp'),
(118, 29, '../assets/imagens/Produtos/Painel solar/3.webp'),
(119, 30, '../assets/imagens/Produtos/Robô/1.webp'),
(120, 30, '../assets/imagens/Produtos/Robô/2.webp'),
(121, 30, '../assets/imagens/Produtos/Robô/3.webp'),
(129, 22, '../assets/imagens/Produtos/Furadeira/1.webp'),
(130, 22, '../assets/imagens/Produtos/Furadeira/2.webp'),
(131, 22, '../assets/imagens/Produtos/Furadeira/3.webp'),
(132, 22, '../assets/imagens/Produtos/Furadeira/4.webp'),
(133, 23, '../assets/imagens/Produtos/Motor/1.webp'),
(134, 23, '../assets/imagens/Produtos/Motor/2.webp'),
(135, 23, '../assets/imagens/Produtos/Motor/3.webp'),
(136, 23, '../assets/imagens/Produtos/Motor/4.webp'),
(137, 23, '../assets/imagens/Produtos/Motor/5.webp'),
(138, 23, '../assets/imagens/Produtos/Motor/6.webp'),
(139, 24, '../assets/imagens/Produtos/Bicicleta Ergométrica/1.webp'),
(140, 24, '../assets/imagens/Produtos/Bicicleta Ergométrica/2.webp'),
(141, 24, '../assets/imagens/Produtos/Bicicleta Ergométrica/3.webp'),
(142, 25, '../assets/imagens/Produtos/Cadeira/1.webp'),
(143, 25, '../assets/imagens/Produtos/Cadeira/2.webp'),
(144, 25, '../assets/imagens/Produtos/Cadeira/3.webp'),
(145, 25, '../assets/imagens/Produtos/Cadeira/4.webp'),
(146, 26, '../assets/imagens/Produtos/Antena/1.jpg'),
(147, 26, '../assets/imagens/Produtos/Antena/2.jpg'),
(148, 26, '../assets/imagens/Produtos/Antena/3.jpg'),
(149, 26, '../assets/imagens/Produtos/Antena/4.jpg'),
(150, 27, '../assets/imagens/Produtos/Cafeteira/1.jpg'),
(151, 27, '../assets/imagens/Produtos/Cafeteira/2.jpg'),
(152, 27, '../assets/imagens/Produtos/Cafeteira/3.jpg'),
(153, 27, '../assets/imagens/Produtos/Cafeteira/4.jpg'),
(154, 28, '../assets/imagens/Produtos/Notebook/1.jpg'),
(155, 28, '../assets/imagens/Produtos/Notebook/2.jpg'),
(156, 28, '../assets/imagens/Produtos/Notebook/3.jpg'),
(157, 28, '../assets/imagens/Produtos/Notebook/4.jpg'),
(158, 31, '../assets/imagens/Produtos/Televisão/1.webp'),
(159, 31, '../assets/imagens/Produtos/Televisão/2.webp'),
(160, 31, '../assets/imagens/Produtos/Televisão/3.webp'),
(161, 31, '../assets/imagens/Produtos/Televisão/4.webp'),
(162, 32, '../assets/imagens/Produtos/Tomada/1.jpg'),
(163, 32, '../assets/imagens/Produtos/Tomada/2.jpg'),
(164, 32, '../assets/imagens/Produtos/Tomada/3.jpg'),
(167, 38, '../assets/imagens/Produtos/69210d2dd9290.jpg'),
(168, 38, '../assets/imagens/Produtos/69210d2dd98be.jpg'),
(169, 38, '../assets/imagens/Produtos/69210d2dd9f17.webp');

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
(3, 'João Silva Teste', 'joao.teste+qa@example.com', '123.456.789-00', '$2y$10$BzliJoZptHJnsCFGKk4ADO8MOXxT89I3LfYgG/QSqC7CXCjLgEfzO', '11 91234-5678', '2025-10-27 19:17:53', 'cliente'),
(4, 'Pedro Souza Fictício', 'pedro.souza+fake@mailinator.com', '111.222.333-44', '$2y$10$b32qw2nn.NS8UfQ47y6creN05WalRd3ydpm9MLVY47ryZNqxn1m.C', '31 91919-1919', '2025-10-28 10:56:46', 'cliente'),
(5, 'Roberto Alves Teste', 'roberto.alves@LojaLTDA.com', '777.888.999-00', '$2y$10$VpEj//DGv.3Yiee1zvtuFe0LjUks7s8h03jGMj.pkWEGyQE3q.gz2', '61 98765-4321', '2025-10-28 23:07:32', 'cliente'),
(6, 'Sandra Gomes Fictícia', 'sandra.gomes@LojaLTDA.com', '707.808.909-00', '$2y$10$87ZxH.N.bJtnM.2Od6txi.Vky0Rs7rzFyU/dV0xa3f.irbaDbymwe', '31 96666-5555', '2025-10-28 23:11:37', 'fornecedor'),
(7, 'asdasds', 'fulano.abc@LojaLTDA.com', '1231231', '$2y$10$5Rdgh5qP/dc6krnWE2Wy3.vQTdfiFWjUGPtzeBFDr72maBqRajYGq', '231231', '2025-11-21 18:47:51', 'fornecedor'),
(8, 'fulano', 'fulano@LojaLTDA.com', '123456789', '$2y$10$iPth3cP0OwK65PZ9EGcD6ulvTzwgRH08DrHVLJVhXiJAOyt2CoN1u', '123456789', '2025-11-21 19:13:23', 'fornecedor');

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
  ADD KEY `fk_PRODUTOS_CATEGORIAS_idx` (`categoria_id`),
  ADD KEY `fk_PRODUTOS_USUARIOS` (`usuario_id`);

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
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `carrinho`
--
ALTER TABLE `carrinho`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `carrinho_itens`
--
ALTER TABLE `carrinho_itens`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `categorias`
--
ALTER TABLE `categorias`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `enderecos`
--
ALTER TABLE `enderecos`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

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
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `pedido_itens`
--
ALTER TABLE `pedido_itens`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `produtos`
--
ALTER TABLE `produtos`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- AUTO_INCREMENT for table `produto_imagens`
--
ALTER TABLE `produto_imagens`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=170;

--
-- AUTO_INCREMENT for table `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

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
  ADD CONSTRAINT `fk_PRODUTOS_CATEGORIAS` FOREIGN KEY (`categoria_id`) REFERENCES `categorias` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_PRODUTOS_USUARIOS` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `produto_imagens`
--
ALTER TABLE `produto_imagens`
  ADD CONSTRAINT `fk_IMAGENS_PRODUTOS` FOREIGN KEY (`produto_id`) REFERENCES `produtos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
