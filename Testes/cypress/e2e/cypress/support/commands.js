// cypress/support/commands.js

/**
 * Cria um comando de login reutilizável.
 * Isso usa o cy.session() para manter o cookie de login
 * entre os testes, tornando-os muito mais rápidos.
 */
Cypress.Commands.add('login', (email, password) => {
  cy.session([email, password], () => {
    // Visita a página de login
    cy.visit('/tela_login.html');
    
    // Intercepta a chamada para saber quando ela terminar
    cy.intercept('POST', '**/processa_login.php').as('loginRequest');

    // Preenche os dados do seu bancodadosteste.sql
    cy.get('#usuario').type(email);
    cy.get('#senha').type(password);
    cy.get('button[type="submit"]').click();
    
    // Espera o redirecionamento para o index.php
    cy.wait('@loginRequest');
    cy.url().should('include', 'index.php');
  });
});

// cypress/support/commands.js

/**
 * Cria um comando de login reutilizável.
 * Isso usa o cy.session() para manter o cookie de login
 * entre os testes, tornando-os muito mais rápidos.
 */
Cypress.Commands.add('login', (email, password) => {
  cy.session([email, password], () => {
    // Visita a página de login
    cy.visit('/tela_login.html');
    
    // Intercepta a chamada para saber quando ela terminar
    // **Ajuste o caminho se o seu processa_login.php estiver em outro lugar**
    cy.intercept('POST', '**/processa_login.php').as('loginRequest');

    // Preenche os dados
    cy.get('#usuario').type(email);
    cy.get('#senha').type(password);
    cy.get('button[type="submit"]').click();
    
    // Espera o redirecionamento para o index.php
    cy.wait('@loginRequest');
    cy.url().should('include', 'index.php'); // Confirma que o login deu certo
  });
});