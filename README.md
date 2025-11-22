# 🛒 Loja Ponto Com – Marketplace de Compras Online

<div align="center">

![Status](https://img.shields.io/badge/Status-Em%20Desenvolvimento-yellow)
![PHP](https://img.shields.io/badge/PHP-777BB4?logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-4479A1?logo=mysql&logoColor=white)
![JavaScript](https://img.shields.io/badge/JavaScript-F7DF1E?logo=javascript&logoColor=black)

</div>

---

## Sobre o Projeto

**Loja Ponto Com** é uma plataforma de e-commerce completa (Marketplace) desenvolvida como projeto prático da disciplina de Desenvolvimento Web 1 do IFSP. O sistema permite que usuários comprem produtos de diversos fornecedores, além de possibilitar que os próprios usuários se cadastrem como vendedores para oferecer seus produtos na plataforma.

### Objetivo

Criar uma solução de marketplace que:
- Facilite a compra e venda de produtos online
- Ofereça uma experiência de compra completa, desde a navegação até o pagamento
- Permita que qualquer usuário se torne um fornecedor
- Gerencie todo o ciclo de vida de um pedido

### 🌟 Diferenciais

- **Marketplace Completo**: Múltiplos fornecedores em uma única plataforma
- **Sistema de Rascunhos**: Fornecedores podem salvar produtos incompletos para continuar depois
- **Gestão Inteligente de Carrinho**: Persistência de dados com sincronização entre sessões
- **Múltiplas Formas de Pagamento**: Suporte para Cartão de Crédito, PIX e Boleto
- **Sistema de Avaliações**: Usuários podem avaliar e comentar produtos
- **Interface Responsiva**: Adaptável a diferentes tamanhos de tela

---

## Funcionalidades

### Para Clientes

- ✅ **Cadastro e Login**: Sistema completo de autenticação de usuários
- ✅ **Catálogo de Produtos**: Navegação por categorias e visualização detalhada
- ✅ **Busca e Filtros**: Sistema de busca inteligente por nome de produtos
- ✅ **Carrinho de Compras**: Adicionar, remover e alterar quantidades de produtos
- ✅ **Gestão de Endereços**: Cadastrar, editar e excluir endereços de entrega
- ✅ **Finalização de Pedido**: Processo completo de checkout
- ✅ **Múltiplas Formas de Pagamento**: Cartão de Crédito, PIX e Boleto
- ✅ **Histórico de Compras**: Visualização de todos os pedidos realizados
- ✅ **Avaliações e Comentários**: Avaliar produtos comprados
- ✅ **Perfil do Usuário**: Atualização de dados pessoais

### Para Fornecedores

- ✅ **Gestão de Produtos**: Cadastrar, editar e excluir produtos
- ✅ **Upload de Múltiplas Imagens**: Até 5 imagens por produto
- ✅ **Sistema de Rascunhos**: Salvar produtos incompletos para finalizar depois
- ✅ **Categorização**: Organizar produtos por categorias
- ✅ **Especificações Técnicas**: Adicionar características e detalhes dos produtos
- ✅ **Controle de Estoque**: Gerenciar quantidade disponível
- ✅ **Visualização de Vendas**: Acompanhar produtos vendidos

### 🔧 Funcionalidades Técnicas

- ✅ **Sessões Persistentes**: Manutenção de estado do usuário
- ✅ **Segurança**: Proteção contra SQL Injection e XSS
- ✅ **Validação de Dados**: Frontend e Backend
- ✅ **Responsividade**: Design adaptável para mobile, tablet e desktop
- ✅ **Performance**: Otimização de consultas ao banco de dados

---

## Tecnologias Utilizadas

### Frontend
- **HTML5**: Estruturação semântica das páginas
- **CSS3**: Estilização vanilla (sem frameworks)
- **JavaScript (ES6+)**: Interatividade e validações do lado do cliente
- **Ajax/Fetch API**: Requisições assíncronas

### Backend
- **PHP (Vanilla)**: Lógica de negócio e processamento de dados
- **PDO (PHP Data Objects)**: Abstração de banco de dados com Prepared Statements

### Banco de Dados
- **MySQL**: Sistema de gerenciamento de banco de dados relacional
- **Modelo Normalizado**: Estrutura otimizada com relacionamentos adequados

### Segurança
- **Prepared Statements**: Prevenção de SQL Injection
- **htmlspecialchars()**: Prevenção de XSS (Cross-Site Scripting)
- **Password Hashing**: Senhas criptografadas
- **Validação de Sessão**: Controle de acesso às páginas

### Ferramentas de Desenvolvimento
- **XAMPP/WAMP**: Ambiente de desenvolvimento local
- **Git**: Controle de versão
- **VSCode**: Editor de código
- **Cypress**: Testes E2E automatizados

---

## Estrutura do Projeto

```
src/                                        # Código-fonte das páginas
├── index.php                               # Página inicial (catálogo)
├── buscar.php                              # Sistema de busca
├── tela_login.html                         # Login de usuários
├── tela_cadastro.html                      # Cadastro de novos usuários
├── tela_produto.php                        # Detalhes do produto
├── tela_carrinho.php                       # Carrinho de compras
├── tela_entrega.php                        # Seleção de endereço
├── tela_pagamento.php                      # Pagamento
├── tela_minha_conta.php                    # Perfil e histórico
├── tela_novo_endereco.php                  # Cadastro de endereço
├── tela_editar_endereco.php                # Edição de endereço
├── tela_produto_do_fornecedor.php          # Gestão de produtos (fornecedor)
├── tela_gerenciar_produtos.php             # Listagem de produtos do fornecedor
└── update_db.php                           # Atualização de dados do usuário

assets/                                     # Recursos estáticos
├── estilos/                                # Arquivos CSS
│   ├── estilo_cadastro.css
│   ├── estilo_carrinho.css
│   ├── estilo_inicial.css
│   ├── estilo_login.css
│   └── estilo_minha_conta.css
├── imagens/                                # Imagens do site
│   └── produtos/                           # Uploads de produtos
└── script.js                               # JavaScript global

Banco de dados/                             # Scripts e lógica do banco
├── bancodadosteste.sql                     # Dump do banco de dados
├── conexao.php                             # Configuração de conexão
├── processa_login.php                      # Autenticação
├── processa_cadastro.php                   # Registro de usuários
├── processa_novo_produto.php               # Cadastro de produtos
├── processa_pedido.php                     # Finalização de compras
├── processa_avaliacao.php                  # Sistema de avaliações
├── salvar_rascunho.php                     # Salvar produtos incompletos
├── sincronizar_carrinho.php                # Sincronização do carrinho
├── buscar_categorias.php                   # API de categorias
├── buscar_produto.php                      # API de produtos
├── buscar_recomendacoes.php                # Produtos recomendados
├── excluir_produto.php                     # Exclusão de produtos
├── excluir_avaliacao.php                   # Exclusão de avaliações
└── logout.php                              # Encerramento de sessão

Artefatos/                                  # Documentação do projeto
├── Diagramas/                              # Diagramas UML
│   ├── Banco de dados/                     # Modelo ER
│   ├── Casos de Uso/                       # Diagrama de casos de uso
│   ├── Estado/                             # Diagrama de estados
│   └── Sequência/                          # Diagrama de sequência
├── Diretrizes_TrabalhoPrático.pdf          # Especificações do projeto
├── Telas e requisitos.pdf                  # Requisitos funcionais
└── slides.pdf                              # Apresentação

Testes/                                     # Testes automatizados
└── cypress/                                # Testes E2E
    ├── e2e/                                # Specs de teste
    └── support/                            # Utilitários de teste

.gitignore                                  # Arquivos ignorados pelo Git
README.md                                   # Este arquivo
```

---

## Como Instalar e Rodar o Projeto

### Pré-requisitos

- **PHP 7.4+** ou superior
- **MySQL 5.7+** ou superior
- **Servidor Web** (Apache ou Nginx)
- **XAMPP** ou **WAMP** (recomendado para ambiente local)

### Passo a Passo

#### 1. Clone o Repositório

```bash
git clone https://github.com/KekkaiSensen/Projeto-de-desenvolvimento-web-.git
cd Projeto-de-desenvolvimento-web-
```

#### 2. Configure o Banco de Dados

##### 2.1. Crie o Banco de Dados

Abra o **phpMyAdmin** (geralmente em `http://localhost/phpmyadmin`) ou seu cliente MySQL preferido.

##### 2.2. Importe o SQL

Importe o arquivo de dump do banco de dados:

```bash
mysql -u seu_usuario -p < "Banco de dados/bancodadosteste.sql"
```

Ou pelo phpMyAdmin:
1. Clique em "Importar"
2. Selecione o arquivo `Banco de dados/bancodadosteste.sql`
3. Clique em "Executar"

#### 3. Configure as Credenciais do Banco

Edite o arquivo `Banco de dados/conexao.php` e ajuste as credenciais:

```php
<?php
$host = 'localhost';        // Host do banco de dados
$dbname = 'bancodadosteste'; // Nome do banco de dados
$username = 'root';         // Seu usuário MySQL
$password = '';             // Sua senha MySQL
?>
```

#### 4. Configure o Servidor Web

##### Usando XAMPP/WAMP:

1. Copie a pasta do projeto para `htdocs/` (XAMPP) ou `www/` (WAMP)
2. Inicie o Apache e MySQL pelo painel de controle
3. Acesse: `http://localhost/src/index.php`

##### Usando o Servidor Embutido do PHP:

```bash
cd src
php -S localhost:8000
```

Acesse: `http://localhost:8000/index.php`

#### 5. Acesse o Sistema

- **Página Inicial**: `http://localhost/src/index.php`
- **Login**: `http://localhost/src/tela_login.html`
- **Cadastro**: `http://localhost/src/tela_cadastro.html`

---

## Configuração de Variáveis de Ambiente

O projeto utiliza arquivo de configuração direto em PHP. Edite o arquivo `Banco de dados/conexao.php`:

```php
<?php
// Configurações do Banco de Dados
$host = 'localhost';              // Host do MySQL
$dbname = 'bancodadosteste';       // Nome do banco de dados
$username = 'root';               // Usuário do MySQL
$password = '';                   // Senha do MySQL
$charset = 'utf8mb4';             // Charset

// DSN para conexão PDO
$dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";

// Opções do PDO
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $username, $password, $options);
} catch (\PDOException $e) {
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}
?>
```

### Configurações Importantes:

| Variável | Descrição | Valor Padrão |
|----------|-----------|--------------|
| `$host` | Endereço do servidor MySQL | `localhost` |
| `$dbname` | Nome do banco de dados | `bancodadosteste` |
| `$username` | Usuário do banco de dados | `root` |
| `$password` | Senha do banco de dados | _(vazio)_ |

---

## Como Contribuir

Contribuições são bem-vindas! Para contribuir com o projeto:

### 1. Fork o Projeto

Clique no botão "Fork" no topo da página do repositório.

### 2. Crie uma Branch

```bash
git checkout -b feature/nova-funcionalidade
```

#### Nomenclatura de Branches:

- `feature/nome-da-feature` - Para novas funcionalidades
- `bugfix/nome-do-bug` - Para correção de bugs
- `hotfix/nome-do-hotfix` - Para correções urgentes
- `refactor/nome-do-refactor` - Para refatorações

### 3. Faça suas Alterações

Desenvolva a funcionalidade ou correção seguindo as boas práticas do projeto.

### 4. Commit suas Mudanças

```bash
git add .
git commit -m "feat: adiciona nova funcionalidade X"
```

#### Padrão de Commits (Conventional Commits):

- `feat:` - Nova funcionalidade
- `fix:` - Correção de bug
- `docs:` - Alterações na documentação
- `style:` - Formatação de código (sem mudança na lógica)
- `refactor:` - Refatoração de código
- `test:` - Adição ou correção de testes
- `chore:` - Tarefas de manutenção

### 5. Push para a Branch

```bash
git push origin feature/nova-funcionalidade
```

### 6. Abra um Pull Request

1. Vá para o repositório original no GitHub
2. Clique em "Pull Requests" > "New Pull Request"
3. Selecione sua branch
4. Descreva suas alterações detalhadamente
5. Aguarde a revisão

### Guia de Estilo

#### PHP
- Use **4 espaços** para indentação
- Siga a PSR-12 quando possível
- Sempre use **Prepared Statements** para queries
- Comente código complexo
- Use nomes descritivos para variáveis e funções

#### JavaScript
- Use **2 espaços** para indentação
- Declare variáveis com `const` ou `let` (nunca `var`)
- Use **arrow functions** quando apropriado
- Sempre use **ponto e vírgula**

#### CSS
- Use **2 espaços** para indentação
- Organize propriedades alfabeticamente
- Use nomes de classes semânticos e descritivos
- Evite IDs para estilos (use classes)

#### SQL
- Use UPPERCASE para palavras-chave SQL
- Indente subconsultas
- Use aliases descritivos

---

## Capturas de Tela

### Página Inicial

![Página Inicial](Artefatos/Readme/Fotos%20do%20readme/homepage.jpg)

---

### Detalhes do Produto

![Produto](Artefatos/Readme/Fotos%20do%20readme/produto_1.jpg)

---

### Carrinho de Compras

![Carrinho](Artefatos/Readme/Fotos%20do%20readme/carrinho.jpg)

---

### Checkout - Entrega

![Entrega](Artefatos/Readme/Fotos%20do%20readme/entrega.jpg)

---

### Checkout - Pagamento

![Pagamento](Artefatos/Readme/Fotos%20do%20readme/pagamento.jpg)

---

### Minha Conta

![Minha Conta](Artefatos/Readme/Fotos%20do%20readme/minha_conta_endereço.jpg)

---

### Painel do Fornecedor

![Painel Fornecedor](Artefatos/Readme/Fotos%20do%20readme/fornecedor.jpg)

---

## Diagramas

### Banco de Dados (Modelo ER)

![Diagrama ER](Artefatos/Diagramas/Banco%20de%20dados/BD%20-%20imagem.png)

### Casos de Uso

![Casos de Uso](Artefatos/Diagramas/Casos%20de%20Uso/UC%20-%20Imagem.png)

### Diagrama de Sequência

![Sequência](Artefatos/Diagramas/Sequência/Sequência%20-%20Imagem.png)

### Diagrama de Estado

![Estado](Artefatos/Diagramas/Estado/Estado%20-%20Imagem.png)

---

## Testes

O projeto conta com testes E2E automatizados usando Cypress.

### Rodando os Testes

```bash
# Instalar dependências
npm install

# Abrir Cypress (modo interativo)
npx cypress open

# Executar testes (modo headless)
npx cypress run
```

### Cobertura de Testes

- ✅ Fluxo de cadastro de usuário
- ✅ Fluxo de login
- ✅ Navegação no catálogo
- ✅ Adicionar produtos ao carrinho
- ✅ Processo de checkout completo
- ✅ Cadastro de produtos (fornecedor)
- ✅ Sistema de rascunhos

---

## Documentação Adicional

Para mais informações sobre o projeto, consulte:

- **[Diretrizes do Trabalho Prático](Artefatos/Diretrizes_TrabalhoPrático.pdf)**: Escopo e regras do projeto
- **[Telas e Requisitos](Artefatos/Telas%20e%20requisitos.pdf)**: Detalhamento funcional
- **[Apresentação do Projeto](Artefatos/Slides/slides.pdf)**: Slides da apresentação

---

## Melhorias Futuras

### Planejadas

- [ ] Integração com Gateway de Pagamento Real (Mercado Pago/Stripe)
- [ ] Sistema de Notificações (Email/Push)
- [ ] Chat entre Comprador e Fornecedor
- [ ] Sistema de Cupons e Descontos
- [ ] Rastreamento de Pedidos
- [ ] Relatórios e Dashboard para Fornecedores
- [ ] API REST para integração com apps mobile
- [ ] Comparação de Produtos

### Sugestões Técnicas

- [ ] Migração para Laravel ou Symfony
- [ ] Frontend em React ou Vue.js
- [ ] Implementação de Cache (Redis)
- [ ] CDN para imagens
- [ ] Containerização com Docker
- [ ] CI/CD com GitHub Actions
- [ ] Testes Unitários e de Integração

---

## Autores

<table width="100%">
  <tr>
    <td align="left" width="33%">
      <a href="https://github.com/KekkaiSensen">
        <img src="https://github.com/KekkaiSensen.png" width="80" />
      </a>
    </td>
    <td align="center" width="33%">
      <a href="https://github.com/Vanamaral">
        <img src="https://github.com/Vanamaral.png" width="80" />
      </a>
    </td>
    <td align="right" width="33%">
      <a href="https://github.com/Igaust-5767">
        <img src="https://github.com/Igaust-5767.png" width="80" />
      </a>
    </td>
  </tr>
</table>



---


## Suporte

Encontrou algum problema ou tem alguma sugestão? Abra uma [issue](https://github.com/KekkaiSensen/Projeto-de-desenvolvimento-web-/issues) no GitHub.

---

<div align="center">


[⬆ Voltar ao topo](#-loja-ponto-com--marketplace-de-compras-online)

</div>
