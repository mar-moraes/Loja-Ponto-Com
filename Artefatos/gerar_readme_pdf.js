const puppeteer = require('puppeteer');
const path = require('path');

(async () => {
    try {
        console.log('Iniciando geração do PDF...');
        const browser = await puppeteer.launch({ headless: "new" });
        const page = await browser.newPage();

        const filePath = path.resolve(__dirname, 'readme.html');
        const pdfPath = path.resolve(__dirname, 'readme.pdf');

        console.log(`Lendo arquivo: ${filePath}`);
        await page.goto(`file://${filePath}`, {
            waitUntil: 'networkidle0'
        });

        console.log('Gerando PDF...');
        await page.pdf({
            path: pdfPath,
            format: 'A4',
            printBackground: true,
            margin: {
                top: '2cm',
                right: '2cm',
                bottom: '2cm',
                left: '2cm'
            }
        });

        await browser.close();
        console.log(`PDF gerado com sucesso em: ${pdfPath}`);
    } catch (error) {
        console.error('Erro ao gerar PDF:', error);
        process.exit(1);
    }
})();
