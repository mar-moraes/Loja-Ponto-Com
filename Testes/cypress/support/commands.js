// cypress/support/commands.js

Cypress.Commands.add('login', (email, password) => {
  cy.session([email, password], () => {
    cy.visit('/tela_login.html');
    
    cy.intercept('POST', '**/processa_login.php').as('loginRequest');

    // --- CORREÇÃO AQUI ---
    // O seletor foi trocado de #usuario para #email
    cy.get('#email').type(email); 
    // --- FIM DA CORREÇÃO ---

    cy.get('#senha').type(password);
    cy.get('button[type="submit"]').click();
    
    cy.wait('@loginRequest');
    cy.url().should('include', 'index.php');
  });
});