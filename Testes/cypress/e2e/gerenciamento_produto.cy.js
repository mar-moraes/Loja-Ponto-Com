describe('Gerenciamento de Produtos', () => {

    const usuarioFornecedor = 'joao.teste+qa@example.com';
    const senhaFornecedor = 'Teste@1234';

    beforeEach(() => {
        // Limpa sessão e visita a home
        cy.visit('/');
        cy.clearCookies();
        cy.clearLocalStorage();
    });

    it('Deve permitir cadastrar um novo produto com sucesso', () => {
        // 1. Login
        cy.visit('/tela_login.html');
        cy.get('#usuario').type(usuarioFornecedor);
        cy.get('#senha').type(senhaFornecedor);
        cy.get('button[type="submit"]').click();

        // Verifica se logou (redirecionamento ou cookie)
        // Assumindo que redireciona para home ou minha conta
        cy.url().should('not.include', 'tela_login.html');

        // 2. Acessar tela de novo produto
        // Se não houver link direto fácil, vamos direto pela URL
        cy.visit('/src/tela_produto_do_fornecedor.php');

        // Verifica se carregou a página
        cy.get('h2').contains('Características principais').should('be.visible');

        // 3. Preencher formulário
        const timestamp = new Date().getTime();
        const nomeProduto = `Produto Teste Cypress ${timestamp}`;

        cy.get('#produto-titulo').type(nomeProduto);

        // Selecionar categoria (aguarda carregar)
        cy.get('#produto-categoria').should('contain', 'Selecione uma categoria');
        // Seleciona a segunda opção (primeira categoria real)
        cy.get('#produto-categoria').find('option').eq(1).then(option => {
            cy.get('#produto-categoria').select(option.val());
        });

        cy.get('#produto-preco').type('150.50');
        cy.get('#produto-badge').type('10'); // 10% desconto
        cy.get('#produto-quantidade').type('50');
        cy.get('#produto-descricao').type('Descrição automática gerada pelo Cypress.');

        // Características (já vem com 1 linha vazia ou padrão?)
        // O script adiciona Marca/Modelo por padrão se for novo.
        // Vamos preencher os existentes.
        cy.get('input[name="caracteristica_valor[]"]').first().type('Marca Teste');
        cy.get('input[name="caracteristica_valor[]"]').eq(1).type('Modelo Teste');

        // Especificações
        cy.get('input[name="especificacao_rapida[]"]').first().type('Especificação Teste');

        // 4. Upload de Imagem
        // O ID é estranho: produto-../assets/imagens
        // Vamos usar um seletor de atributo para garantir
        cy.get('[id="produto-../assets/imagens"]').selectFile('Testes/cypress/fixtures/produto_teste.png', { force: true });

        // 5. Submeter
        cy.intercept('POST', '**/processa_novo_produto.php').as('postProduto');
        cy.get('button[type="submit"]').click();

        // 6. Verificar sucesso
        cy.wait('@postProduto').its('response.statusCode').should('eq', 200);

        // Verifica alerta ou redirecionamento
        // O código usa alert(), o Cypress captura window:alert automaticamente mas não falha.
        // O código redireciona para index.php em sucesso.
        cy.on('window:alert', (str) => {
            expect(str).to.contain('Produto publicado com sucesso');
        });

        cy.url().should('include', 'index.php');

        // Opcional: Verificar se aparece na home (pode ser difícil se não estiver ordenado)
    });
});
