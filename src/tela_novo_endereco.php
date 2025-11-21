<?php
session_start();
// Verifica se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: tela_login.html');
    exit();
}
?>
<!doctype html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Novo Endereço</title>
  <link rel="stylesheet" href="estilos/style.css">
  <style>
    /* Estilos simples para o formulário */
    .container { max-width: 600px; margin-top: 40px; }
    
    /* NOVO: Estilo "card" copiado da tela_minha_conta.php */
    .conta-secao {
        background-color: #ffffff;
        border-radius: 8px;
        padding: 24px;
        margin-top: 20px; /* Espaço do Título */
        box-shadow: 0 1px 6px rgba(0, 0, 0, 0.06);
    }

    .form-grupo { margin-bottom: 15px; }
    .form-grupo label { display: block; margin-bottom: 5px; font-weight: 500; }
    .form-grupo input {
        width: 100%;
        padding: 10px;
        font-size: 1rem;
        border: 1px solid #ccc;
        border-radius: 4px;
        box-sizing: border-box; /* Garante que padding não afete a largura */
    }
    
    /* ATUALIZADO: Botão no padrão azul do site */
    .btn-salvar {
        width: 100%; /* Botão ocupa 100% da largura do card */
        padding: 12px 20px;
        font-size: 1rem;
        font-weight: 600;
        background-color: #2968C8; /* Cor padrão do site */
        color: white;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        transition: opacity 0.2s;
    }
    .btn-salvar:hover { 
        opacity: 0.9; 
    }
  </style>
</head>
<body>
    <main class="container">
        <h1>Adicionar Novo Endereço</h1>
        
        <div class="conta-secao">
            <form action="Banco de dados/processa_novo_endereco.php" method="POST">
                <div class="form-grupo">
                    <label for="cep">CEP:</label>
                    <input type="text" id="cep" name="cep" required>
                </div>
                <div class="form-grupo">
                    <label for="rua">Rua:</label>
                    <input type="text" id="rua" name="rua" required>
                </div>
                <div class="form-grupo">
                    <label for="numero">Número:</label>
                    <input type="text" id="numero" name="numero" required>
                </div>
                <div class="form-grupo">
                    <label for="complemento">Complemento: (Opcional)</label>
                    <input type="text" id="complemento" name="complemento">
                </div>
                <div class="form-grupo">
                    <label for="bairro">Bairro:</label>
                    <input type="text" id="bairro" name="bairro" required>
                </div>
                <div class="form-grupo">
                    <label for="cidade">Cidade:</label>
                    <input type="text" id="cidade" name="cidade" required>
                </div>
                
                <div class="form-grupo">
                    <label for="estado">Estado: (Sigla, ex: SP)</label>
                    <input type="text" id="estado" name="estado" list="estados-lista" maxlength="2" required autocomplete="off">
                    
                    <datalist id="estados-lista">
                        <option value="AC">Acre</option>
                        <option value="AL">Alagoas</option>
                        <option value="AP">Amapá</option>
                        <option value="AM">Amazonas</option>
                        <option value="BA">Bahia</option>
                        <option value="CE">Ceará</option>
                        <option value="DF">Distrito Federal</option>
                        <option value="ES">Espírito Santo</option>
                        <option value="GO">Goiás</option>
                        <option value="MA">Maranhão</option>
                        <option value="MT">Mato Grosso</option>
                        <option value="MS">Mato Grosso do Sul</option>
                        <option value="MG">Minas Gerais</option>
                        <option value="PA">Pará</option>
                        <option value="PB">Paraíba</option>
                        <option value="PR">Paraná</option>
                        <option value="PE">Pernambuco</option>
                        <option value="PI">Piauí</option>
                        <option value="RJ">Rio de Janeiro</option>
                        <option value="RN">Rio Grande do Norte</option>
                        <option value="RS">Rio Grande do Sul</option>
                        <option value="RO">Rondônia</option>
                        <option value="RR">Roraima</option>
                        <option value="SC">Santa Catarina</option>
                        <option value="SP">São Paulo</option>
                        <option value="SE">Sergipe</option>
                        <option value="TO">Tocantins</option>
                    </datalist>
                </div>
                
                <button type="submit" class="btn-salvar">Salvar Endereço</button>
            </form>
        </div>
    </main>

    <script>
    document.addEventListener("DOMContentLoaded", () => {
        
        // --- Referências aos campos do formulário ---
        const inputCep = document.getElementById('cep');
        const inputRua = document.getElementById('rua');
        const inputNumero = document.getElementById('numero');
        const inputBairro = document.getElementById('bairro');
        const inputCidade = document.getElementById('cidade');
        const inputEstado = document.getElementById('estado');

        // --- Função para buscar o CEP (copiada de tela_entrega.php e adaptada) ---
        function buscarCep() {
            if (!inputCep) return; 

            const cep = inputCep.value.replace(/\D/g, ''); // Remove não dígitos

            if (cep.length === 8) {
                // Feedback visual de busca
                inputRua.value = "Buscando...";
                inputBairro.value = "Buscando...";
                inputCidade.value = "Buscando...";
                inputEstado.value = ""; // Limpa estado

                fetch(`https://viacep.com.br/ws/${cep}/json/`)
                    .then(response => {
                         if (!response.ok) { throw new Error('Erro na resposta da API ViaCEP'); }
                         return response.json();
                     })
                    .then(data => {
                        if (data.erro) {
                            console.warn("CEP não encontrado na base do ViaCEP.");
                            inputRua.value = "";
                            inputBairro.value = "";
                            inputCidade.value = "";
                            inputRua.focus(); // Foca na rua para preenchimento manual
                        } else {
                            // Preenche todos os campos
                            inputRua.value = data.logradouro || "";
                            inputBairro.value = data.bairro || "";
                            inputCidade.value = data.localidade || "";
                            inputEstado.value = data.uf || "";
                            
                            inputNumero.focus(); // Foca no número para o usuário preencher
                        }
                    })
                    .catch(error => {
                        console.error('Erro ao consultar a API ViaCEP:', error);
                        // Limpa campos em caso de erro
                        inputRua.value = ""; 
                        inputBairro.value = "";
                        inputCidade.value = "";
                        inputEstado.value = "";
                    });
            } else if (cep.length > 0 && cep.length < 8) {
                 // CEP incompleto, limpa os campos se estavam "Buscando..."
                 if(inputRua && inputRua.value === "Buscando...") {
                    inputRua.value = "";
                    inputBairro.value = "";
                    inputCidade.value = "";
                 }
            } else if (cep.length === 0) {
                 // Campo CEP vazio, limpa os campos
                 inputRua.value = "";
                 inputBairro.value = "";
                 inputCidade.value = "";
            }
        }

        // --- Adiciona os 'escutadores' de evento no campo CEP ---
        if (inputCep) {
            inputCep.addEventListener('blur', buscarCep); // Busca quando perde o foco
            
            inputCep.addEventListener('input', () => { // Limpa "Buscando..." se o usuário digitar mais
                 if(inputRua && inputRua.value === "Buscando...") {
                    inputRua.value = "";
                    inputBairro.value = "";
                    inputCidade.value = "";
                 }
            });
            
            inputCep.addEventListener('keydown', (event) => {
                if (event.key === 'Enter') {
                    event.preventDefault(); // Evita submit do formulário
                    buscarCep(); // Busca ao pressionar Enter
                }
            });
        } else {
             console.error("Input 'cep' não encontrado.");
        }
    });
    </script>
</body>
</html>