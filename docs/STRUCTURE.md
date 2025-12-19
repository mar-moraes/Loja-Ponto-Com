# Estrutura do Projeto


```
src/                                        # Diretório público (entry point) acessado via navegador
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
│   └── Sequência/                          # Diagrama de sequência
├── Diretrizes_TrabalhoPrático.pdf          # Especificações do projeto
├── Telas e requisitos.pdf                  # Requisitos funcionais
└── slides.pdf                              # Apresentação

docs/                                       # Documentação do projeto refatorada
├── ARCHITECTURE.md                         # Arquitetura e Diagramas
└── STRUCTURE.md                            # Estrutura do projeto (este arquivo)

testes/                                     # Testes automatizados
└── cypress/                                # Testes E2E
    ├── e2e/                                # Specs de teste
    └── support/                            # Utilitários de teste

.gitignore                                  # Arquivos ignorados pelo Git
CONTRIBUTING.md                             # Guia de contribuição
LICENSE                                     # Licença MIT
README.md                                   # Documentação principal
ROADMAP.md                                  # Mapa de melhorias futuras
```
