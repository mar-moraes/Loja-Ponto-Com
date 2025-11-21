# Projeto de Desenvolvimento Web 1 - Loja Ponto Com

Este projeto foi desenvolvido como parte da avaliação da disciplina de Desenvolvimento Web 1. Trata-se de uma plataforma de e-commerce (Marketplace) onde usuários podem se cadastrar para comprar produtos e, opcionalmente, atuar como fornecedores para vender seus próprios itens.

## 📄 Documentação e Artefatos

Mais informações detalhadas sobre o projeto podem ser encontradas na pasta `Artefatos`:
- **Diretrizes_TrabalhoPrático.pdf**: Regras e escopo do trabalho.
- **Telas e requisitos.pdf**: Detalhamento das telas e requisitos funcionais.
- **slides.pdf**: Apresentação do projeto.

### Diagramas
Os diagramas do projeto estão disponíveis em `Artefatos/Diagramas`:
- **Banco de Dados**: `Artefatos/Diagramas/Banco de dados/BD - imagem.png`
- **Casos de Uso**: `Artefatos/Diagramas/Casos de Uso/UC - Imagem.png`
- **Diagrama de Estado**: `Artefatos/Diagramas/Estado/Estado - Imagem.png`
- **Diagrama de Sequência**: `Artefatos/Diagramas/Sequência/Sequência - Imagem.png`

---

## 🚀 Funcionalidades e Telas

O sistema atende aos requisitos propostos através das seguintes telas e funcionalidades:

### 1. Autenticação e Cadastro
- **Telas**: `tela_login.html`, `tela_cadastro.html`
- **Funcionalidade**: Permite que usuários criem contas (definindo se são apenas clientes ou fornecedores) e façam login.
- **Requisitos Atendidos**: Cadastro de usuários, Login, Sessão.

### 2. Catálogo e Busca
- **Telas**: `index.php`, `buscar.php`, `tela_produto.php`
- **Funcionalidade**:
    - Página inicial com listagem de produtos.
    - Barra de pesquisa funcional (`buscar.php`) que filtra produtos por nome.
    - Página de detalhes do produto (`tela_produto.php`) exibindo imagens, preço, características e especificações.

### 3. Carrinho de Compras
- **Tela**: `tela_carrinho.php`
- **Funcionalidade**: Adicionar itens, alterar quantidades, remover itens e visualizar o subtotal. O carrinho é persistido (pode usar sessão ou banco dependendo da implementação final).

### 4. Checkout (Finalização de Compra)
- **Telas**: `tela_entrega.php`, `tela_pagamento.php`
- **Funcionalidade**:
    - Seleção de endereço de entrega (cadastrado previamente ou novo).
    - Simulação de pagamento (Cartão de Crédito, PIX, Boleto).
    - Criação do pedido no banco de dados.

### 5. Minha Conta (Perfil do Usuário)
- **Tela**: `tela_minha_conta.php`
- **Funcionalidade**:
    - Exibição e edição de dados pessoais.
    - Gerenciamento de endereços (`tela_novo_endereco.php`, `tela_editar_endereco.php`).
    - Histórico de compras (pedidos realizados).

### 6. Painel do Fornecedor (Gestão de Produtos)
- **Telas**: `tela_minha_conta.php` (Aba "Meus produtos"), `tela_produto_do_fornecedor.php`
- **Funcionalidade**:
    - Listagem de produtos cadastrados e rascunhos.
    - Cadastro de novos produtos com upload de múltiplas imagens.
    - **Rascunhos**: Possibilidade de salvar um produto incompleto para terminar depois (funcionalidade robusta com persistência no banco).
    - Edição e Exclusão de produtos.

---

## 🛠 Tecnologias Utilizadas

- **Frontend**: HTML5, CSS3 (Vanilla), JavaScript (ES6+).
- **Backend**: PHP (Vanilla).
- **Banco de Dados**: MySQL.
- **Bibliotecas**: Nenhuma biblioteca externa pesada foi utilizada, focando no aprendizado dos fundamentos.

---

## ⚙️ Instalação e Execução

1. **Banco de Dados**:
   - Importe o arquivo `Banco de dados/bancodadosteste.sql` no seu SGBD MySQL.
   - Verifique as credenciais em `Banco de dados/conexao.php`.

2. **Servidor Web**:
   - Utilize o XAMPP, WAMP ou servidor embutido do PHP.
   - Aponte o diretório raiz para a pasta do projeto.

3. **Acesso**:
   - Acesse `http://localhost/seu-projeto/index.php`.

---

## 📈 Escalabilidade e Melhorias Futuras

### Pontos Fortes
- **Estrutura Modular**: O código está organizado em pastas (`Banco de dados`, `estilos`, `imagens`), facilitando a manutenção.
- **Banco de Dados Normalizado**: A estrutura do banco (tabelas `PRODUTOS`, `USUARIOS`, `PEDIDOS`, etc.) segue boas práticas de normalização.
- **Segurança Básica**: Uso de Prepared Statements (PDO) para evitar SQL Injection e `htmlspecialchars` para evitar XSS.

### Oportunidades de Melhoria
- **Frameworks**: A migração para um framework PHP (como Laravel) e um framework JS (como React/Vue) aumentaria a produtividade e padronização.
- **Validação Robusta**: Implementar validações mais estritas no backend para todos os campos.
- **Testes Automatizados**: Adicionar testes unitários e de integração (atualmente inexistentes).
- **Gateway de Pagamento Real**: Integrar com APIs reais (Stripe, Mercado Pago) em vez da simulação atual.
- **API REST**: Transformar o backend em uma API RESTful para servir tanto a web quanto possíveis aplicativos móveis.

---

*Documento gerado automaticamente pelo assistente de IA.*
