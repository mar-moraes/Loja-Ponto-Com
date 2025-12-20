# Arquitetura do Projeto

Esta documentação detalha a estrutura técnica, banco de dados e fluxos de interação do sistema Loja Ponto Com.

## Diagramas

### Banco de Dados (DER)

O Diagrama Entidade-Relacionamento (DER) ilustra como as tabelas do banco de dados interagem, mostrando as relações entre Usuários, Produtos, Pedidos e outras entidades.

![Diagrama Entidade-Relacionamento](../Artefatos/Diagramas/Banco%20de%20dados/BD%20-%20imagem.png)

### Casos de Uso

O Diagrama de Casos de Uso descreve as interações dos atores (Cliente, Fornecedor) com o sistema, detalhando as funcionalidades acessíveis a cada perfil.

![Casos de Uso](../Artefatos/Diagramas/Casos%20de%20Uso/UC%20-%20Imagem.png)

### Diagrama de Sequência

O Diagrama de Sequência detalha a ordem cronológica das mensagens trocadas entre os objetos e componentes do sistema para realizar uma funcionalidade específica (ex: Processamento de Compra).

![Sequência](../Artefatos/Diagramas/Sequência/Sequência%20-%20Imagem.png)

## Serviços Externos

### Cloudinary (CDN de Imagens)

O sistema utiliza o Cloudinary para armazenamento e entrega otimizada de imagens.
- **Upload**: O backend (`CloudinaryService`) envia imagens diretamente para a API do Cloudinary.
- **Armazenamento**: As imagens não são salvas no disco local do servidor (exceto temporariamente em `/tmp`).
- **Banco de Dados**: A tabela `produtos` armazena a URL absoluta da imagem (`imagem_url`) fornecida pelo Cloudinary (ex: `https://res.cloudinary.com/...`).
