// cypress/e2e/seguranca_api.cy.js

describe('Segurança - Testes de API (SQLi e XSS)', () => {

  // --- Teste de Injeção de SQL (SQLi) ---
  // (Este teste já estava correto)
  it('não deve permitir login via SQL Injection', () => {
    const sqlPayload = "' OR '1'='1";
    
    cy.request({
      method: 'POST',
      url: '/Banco de dados/processa_login.php', 
      form: true, 
      body: {
        usuario: sqlPayload,
        senha: 'qualquercoisa'
      },
      failOnStatusCode: false,
      followRedirect: false // Não seguir o redirecionamento
    }).then((response) => {
      // Verifica se fomos redirecionados de volta para o login com erro
      expect(response.status).to.eq(302);
      expect(response.headers['location']).to.include('tela_login.html?erro=login_invalido');
    });
  });

  // --- Teste de XSS Armazenado (Stored XSS) ---
  it('deve neutralizar XSS em avaliações de produto', () => {
    
    const xssPayload = '<img src=x onerror=alert("XSS-AVALIACAO")>';
    // Usando o ID 22 (Parafusadeira) do seu bancodadosteste.sql
    const produtoIdParaTestar = 22;

    // --- INÍCIO DAS CORREÇÕES ---
    
    // 1. FAZEMOS O LOGIN
    // Usa o usuário do seu bancodadosteste.sql
    // A senha 'senha123' corresponde ao hash $2y$10$BzliJoZpt...
    cy.login('joao.teste+qa@example.com', 'senha123');
    
    // 2. INJETAMOS O PAYLOAD (bloco descomentado)
    // Agora que estamos logados, este request vai funcionar
    cy.request({
      method: 'POST',
      url: '/Banco de dados/processa_avaliacao.php',
      // O seu script tela_produto.php envia JSON
      body: { 
        produto_id: produtoIdParaTestar,
        nota: 1, // Damos 1 estrela para a avaliação XSS
        comentario: xssPayload // Injeta o payload
      }
    });
    // --- FIM DAS CORREÇÕES ---

    // 3. Espionar a função `alert` do navegador
    const alertStub = cy.stub();
    cy.on('window:alert', alertStub);
    
    // 4. Visitar a página como vítima
    cy.visit(`/tela_produto.php?id=${produtoIdParaTestar}`);

    // 5. A Asserção (Verifica se o alert não disparou)
    cy.get('#opinioes').then(() => {
      expect(alertStub).to.not.have.been.called;
    });

    // 6. A Asserção (Verifica se o XSS foi neutralizado)
    // O seu PHP usa htmlspecialchars(),
    // então procuramos pelo texto escapado (&lt;) no HTML.
    cy.get('#opinioes').invoke('html').should('contain', '&lt;img src=x');
  });
});