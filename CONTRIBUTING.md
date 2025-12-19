# Como Contribuir

Contribuições são bem-vindas! Para contribuir com o projeto:

## 1. Fork o Projeto

Clique no botão "Fork" no topo da página do repositório.

## 2. Crie uma Branch

```bash
git checkout -b feature/nova-funcionalidade
```

### Nomenclatura de Branches:

- `feature/nome-da-feature` - Para novas funcionalidades
- `bugfix/nome-do-bug` - Para correção de bugs
- `hotfix/nome-do-hotfix` - Para correções urgentes
- `refactor/nome-do-refactor` - Para refatorações

## 3. Faça suas Alterações

Desenvolva a funcionalidade ou correção seguindo as boas práticas do projeto.

## 4. Commit suas Mudanças

```bash
git add .
git commit -m "feat: adiciona nova funcionalidade X"
```

### Padrão de Commits (Conventional Commits):

- `feat:` - Nova funcionalidade
- `fix:` - Correção de bug
- `docs:` - Alterações na documentação
- `style:` - Formatação de código (sem mudança na lógica)
- `refactor:` - Refatoração de código
- `test:` - Adição ou correção de testes
- `chore:` - Tarefas de manutenção

## 5. Push para a Branch

```bash
git push origin feature/nova-funcionalidade
```

## 6. Abra um Pull Request

1. Vá para o repositório original no GitHub
2. Clique em "Pull Requests" > "New Pull Request"
3. Selecione sua branch
4. Descreva suas alterações detalhadamente
5. Aguarde a revisão

## Guia de Estilo

### PHP
- Use **4 espaços** para indentação
- Siga a PSR-12 quando possível
- Sempre use **Prepared Statements** para queries
- Comente código complexo
- Use nomes descritivos para variáveis e funções

### JavaScript
- Use **2 espaços** para indentação
- Declare variáveis com `const` ou `let` (nunca `var`)
- Use **arrow functions** quando apropriado
- Sempre use **ponto e vírgula**

### CSS
- Use **2 espaços** para indentação
- Organize propriedades alfabeticamente
- Use nomes de classes semânticos e descritivos
- Evite IDs para estilos (use classes)

### SQL
- Use UPPERCASE para palavras-chave SQL
- Indente subconsultas
- Use aliases descritivos
