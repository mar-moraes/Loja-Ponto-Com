// cypress/e2e/jornada_cliente.cy.js

describe('Jornada E2E do Cliente (Happy Path)', () => {

  beforeEach(() => {
    cy.visit('/');
    cy.clearLocalStorage('carrinho');
    cy.clearLocalStorage('totalCompra');
    cy.clearLocalStorage('valorFinal');
  });

  it('deve permitir que um usuário adicione um item, faça login e finalize a compra', () => {
    
    // --- 1. Home (index.php) ---
    // Clica no primeiro produto (que será o ID 22, "Parafusadeira")
    cy.get('.card-link').first().click();
    
    // --- 2. Página de Produto (tela_produto.php) ---
    cy.url().should('include', 'tela_produto.php');
    cy.get('#btn-adicionar-carrinho').click();

    // --- 3. Página do Carrinho (tela_carrinho.php) ---
    cy.url().should('include', 'tela_carrinho.php');
    cy.get('#valor-total').should('not.contain', 'R$ 0,00');
    cy.get('.btn-continuar').click();

    // --- 4. Página de Login (tela_login.html) ---
    cy.url().should('include', 'tela_login.html');
    cy.intercept('POST', '**/processa_login.php').as('postLogin');

    // --- CORREÇÃO APLICADA ---
    // Usamos o usuário e senha que existem no seu bancodadosteste.sql
    // A senha para 'joao.teste+qa@example.com' é 'senha123' (hash $2y$10$BzliJoZptHJnsCFGKk4ADO8MOXxT89I3LfYgG/QSqC7CXCjLgEfzO)
    cy.get('#usuario').type('joao.teste+qa@example.com');
    cy.get('#senha').type('senha123'); // Você precisará saber a senha real
    cy.get('button[type="submit"]').click();

    cy.wait('@postLogin');

    // --- 5. Página de Entrega (tela_entrega.php) ---
    cy.url().should('include', 'tela_entrega.php');
    cy.get('#resumo-total-preco').should('not.contain', 'R$ 0,00');
    cy.get('.btn-continuar-entrega').click();

    // --- 6. Página de Pagamento (tela_pagamento.php) ---
    cy.url().should('include', 'tela_pagamento.php');
    cy.intercept('POST', '**/Banco de dados/processa_pedido.php').as('processaPedido');
    cy.get('#pix').check();
    cy.get('.btn-continuar-entrega').click();

    // --- 7. Sucesso ---
    cy.wait('@processaPedido').its('response.statusCode').should('eq', 200);
    cy.get('#success-notification')
      .should('be.visible')
      .and('contain', 'Pagamento bem-sucedido');
    
    // --- 8. Verificação Pós-Compra ---
    cy.visit('/tela_carrinho.php');
    cy.get('#painel-carrinho').should('contain', 'Nenhum produto no carrinho');
  });
});