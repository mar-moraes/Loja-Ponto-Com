const puppeteer = require('puppeteer');
const path = require('path');
const fs = require('fs');

(async () => {
    try {
        console.log('Iniciando geração do PDF da Documentação Técnica...');

        // Importar marked dinamicamente (para suportar versões ESM recentes)
        const { marked } = await import('marked');

        // Caminhos
        const mdPath = path.resolve(__dirname, 'Documentacao_Tecnica.md');
        const pdfPath = path.resolve(__dirname, 'Documentacao_Tecnica.pdf');

        // Ler o arquivo Markdown
        console.log(`Lendo arquivo: ${mdPath}`);
        const mdContent = fs.readFileSync(mdPath, 'utf-8');

        // Converter para HTML
        const htmlContent = marked.parse(mdContent);

        // Estilos básicos para o PDF (estilo GitHub-like simplificado)
        const css = `
            <style>
                body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Helvetica, Arial, sans-serif; line-height: 1.6; color: #24292e; max-width: 800px; margin: 0 auto; padding: 20px; }
                h1, h2, h3 { border-bottom: 1px solid #eaecef; padding-bottom: 0.3em; }
                h1 { font-size: 2em; }
                h2 { font-size: 1.5em; }
                h3 { font-size: 1.25em; }
                code { background-color: #f6f8fa; padding: 0.2em 0.4em; border-radius: 3px; font-family: SFMono-Regular, Consolas, "Liberation Mono", Menlo, monospace; font-size: 85%; }
                pre { background-color: #f6f8fa; padding: 16px; overflow: auto; border-radius: 3px; }
                pre code { background-color: transparent; padding: 0; }
                blockquote { border-left: 0.25em solid #dfe2e5; color: #6a737d; padding: 0 1em; margin: 0; }
                table { border-collapse: collapse; width: 100%; margin-bottom: 16px; }
                table th, table td { padding: 6px 13px; border: 1px solid #dfe2e5; }
                table tr:nth-child(2n) { background-color: #f6f8fa; }
                img { max-width: 100%; box-sizing: content-box; }
                hr { height: 0.25em; padding: 0; margin: 24px 0; background-color: #e1e4e8; border: 0; }
            </style>
        `;

        // Montar o HTML final
        const finalHtml = `
            <!DOCTYPE html>
            <html>
            <head>
                <meta charset="UTF-8">
                <title>Documentação Técnica</title>
                ${css}
            </head>
            <body>
                ${htmlContent}
            </body>
            </html>
        `;

        // Iniciar Puppeteer
        const browser = await puppeteer.launch({ headless: "new" });
        const page = await browser.newPage();

        // Carregar conteúdo HTML
        await page.setContent(finalHtml, { waitUntil: 'networkidle0' });

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
