describe('Captura de Screenshots para README', () => {
    beforeEach(() => {
        // Configura o viewport para um tamanho desktop padrão
        cy.viewport(1920, 1080)
    })

    it('1. Captura da Página Inicial (Home)', () => {
        cy.visit('http://localhost:8000/index.php')
        cy.wait(2000) // Aguarda carregar
        cy.screenshot('home', {
            capture: 'fullPage',
            overwrite: true
        })
    })

    it('2. Captura da Página do Produto', () => {
        cy.visit('http://localhost:8000/tela_produto.php?id=22')
        cy.wait(2000)
        cy.screenshot('produto', {
            capture: 'fullPage',
            overwrite: true
        })
    })

    it('3. Captura do Carrinho de Compras', () => {
        cy.visit('http://localhost:8000/tela_carrinho.php')
        cy.wait(2000)
        cy.screenshot('carrinho', {
            capture: 'fullPage',
            overwrite: true
        })
    })

    it('4. Captura da Tela de Login', () => {
        cy.visit('http://localhost:8000/tela_login.html')
        cy.wait(1000)
        cy.screenshot('login', {
            capture: 'fullPage',
            overwrite: true
        })
    })

    it('5. Captura da Tela de Cadastro', () => {
        cy.visit('http://localhost:8000/tela_cadastro.html')
        cy.wait(1000)
        cy.screenshot('cadastro', {
            capture: 'fullPage',
            overwrite: true
        })
    })

    it('6. Captura da Tela de Entrega (após login)', () => {
        // Primeiro faz login
        cy.visit('http://localhost:8000/tela_login.html')
        cy.get('input[name="email"]').type('teste@email.com')
        cy.get('input[name="senha"]').type('senha123')
        cy.get('button[type="submit"]').click()

        // Depois navega para entrega
        cy.visit('http://localhost:8000/tela_entrega.php', { failOnStatusCode: false })
        cy.wait(2000)
        cy.screenshot('entrega', {
            capture: 'fullPage',
            overwrite: true
        })
    })

    it('7. Captura da Tela de Pagamento (após login)', () => {
        // Login primeiro
        cy.visit('http://localhost:8000/tela_login.html')
        cy.get('input[name="email"]').type('teste@email.com')
        cy.get('input[name="senha"]').type('senha123')
        cy.get('button[type="submit"]').click()

        // Navega para pagamento
        cy.visit('http://localhost:8000/tela_pagamento.php', { failOnStatusCode: false })
        cy.wait(2000)
        cy.screenshot('pagamento', {
            capture: 'fullPage',
            overwrite: true
        })
    })

    it('8. Captura da Tela Minha Conta', () => {
        // Login primeiro
        cy.visit('http://localhost:8000/tela_login.html')
        cy.get('input[name="email"]').type('teste@email.com')
        cy.get('input[name="senha"]').type('senha123')
        cy.get('button[type="submit"]').click()

        // Navega para minha conta
        cy.visit('http://localhost:8000/tela_minha_conta.php')
        cy.wait(2000)
        cy.screenshot('minha_conta', {
            capture: 'fullPage',
            overwrite: true
        })
    })

    it('9. Captura do Painel do Fornecedor', () => {
        // Login como fornecedor
        cy.visit('http://localhost:8000/tela_login.html')
        cy.get('input[name="email"]').type('fornecedor@email.com')
        cy.get('input[name="senha"]').type('senha123')
        cy.get('button[type="submit"]').click()

        // Navega para tela de produto do fornecedor
        cy.visit('http://localhost:8000/tela_produto_do_fornecedor.php')
        cy.wait(2000)
        cy.screenshot('painel_fornecedor', {
            capture: 'fullPage',
            overwrite: true
        })
    })
})
