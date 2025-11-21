const puppeteer = require('puppeteer');
const path = require('path');

(async () => {
  // 1. Inicia o "navegador invisível"
  const browser = await puppeteer.launch({ headless: "new" });
  const page = await browser.newPage();

  // 2. Define o caminho absoluto para o seu arquivo HTML
  // Isso é mais confiável do que caminhos relativos
  const filePath = path.resolve(__dirname, 'slides.html');
  
  // 3. Vai para o seu arquivo local
  await page.goto(`file://${filePath}`, {
    // Espera até que a rede e o JS estejam "quietos"
    // Isso garante que o Mermaid termine de renderizar
    waitUntil: 'networkidle0' 
  });

  // 4. A MÁGICA: Gera o PDF com as dimensões exatas do seu slide
  await page.pdf({
    path: 'meus_slides.pdf', // O nome do arquivo de saída
    width: '1280px',         // Largura exata de cada slide
    height: '720px',         // Altura exata de cada slide
    printBackground: true,   // Força a impressão dos fundos escuros
    margin: { top: 0, right: 0, bottom: 0, left: 0 } // Remove margens
  });

  // 5. Fecha o navegador
  await browser.close();
  
  console.log('PDF gerado com sucesso: meus_slides.pdf');
})();