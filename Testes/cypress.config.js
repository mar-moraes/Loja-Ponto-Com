// cypress.config.js
const { defineConfig } = require('cypress');

module.exports = defineConfig({
  e2e: {
    // Aponte para a RAÍZ do seu servidor, não para um arquivo
    baseUrl: 'http://localhost:3000/src', // ⬅️ CORREÇÃO: Aponta para a pasta src
    setupNodeEvents(on, config) {
      // implement node event listeners here
    },
  },
});