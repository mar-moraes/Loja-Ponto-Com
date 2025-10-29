<?php
session_start();

// Verifica se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    // Se não estiver logado, redireciona para o login
    header("Location: tela_login.html");
    exit();
}

// Inclui a conexão
require 'Banco de dados/conexao.php';

$usuario_id = $_SESSION['usuario_id'];
$usuario_logado = true; // Necessário para o template do header
$nome_usuario_completo = $_SESSION['usuario_nome']; // Pego da sessão de login
$nome_usuario = explode(' ', $nome_usuario_completo)[0]; // Primeiro nome para o header

// --- INÍCIO DA MODIFICAÇÃO: Buscar CPF e Telefone ---
$usuario_cpf = '';
$usuario_telefone = '';

try {
    // Busca dados adicionais do usuário (CPF/Telefone) da tabela USUARIOS
    $stmt_usuario = $pdo->prepare("SELECT cpf, telefone FROM USUARIOS WHERE id = ?");
    $stmt_usuario->execute([$usuario_id]);
    $dados_usuario = $stmt_usuario->fetch(PDO::FETCH_ASSOC);

    if ($dados_usuario) {
        $usuario_cpf = $dados_usuario['cpf'];
        $usuario_telefone = $dados_usuario['telefone'];
        
        // Salva na SESSÃO para a tela_pagamento e o PDF usarem
        $_SESSION['usuario_cpf'] = $usuario_cpf;
        $_SESSION['usuario_telefone'] = $usuario_telefone;
    }
} catch (PDOException $e) {
    error_log("Erro ao buscar dados (CPF/Telefone) do usuário: " . $e->getMessage());
    // A página continua, mas o CPF/Telefone ficarão vazios na sessão
}
// --- FIM DA MODIFICAÇÃO ---


$endereco_padrao = null;
$total_carrinho = 0.00; // Valor padrão
$frete = 0.00; // Frete padrão grátis
$total_final = 0.00;

// === BUSCAR ENDEREÇO ===
try {
    // Busca o último endereço cadastrado pelo usuário
    $stmt_addr = $pdo->prepare("SELECT * FROM ENDERECOS WHERE usuario_id = ? ORDER BY id DESC LIMIT 1");
    $stmt_addr->execute([$usuario_id]);
    $endereco_padrao = $stmt_addr->fetch(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    error_log("Erro ao buscar endereço: " . $e->getMessage());
    // $endereco_padrao continua null
}


// Busca o total do carrinho (código original)
try {
    // SQL para buscar o carrinho, itens e produtos, e calcular o total
    $sql_cart = "SELECT SUM(p.preco * ci.quantidade) as subtotal
                 FROM CARRINHO c
                 JOIN CARRINHO_ITENS ci ON c.id = ci.carrinho_id
                 JOIN PRODUTOS p ON ci.produto_id = p.id
                 WHERE c.usuario_id = ?";
                 
    $stmt_cart = $pdo->prepare($sql_cart);
    $stmt_cart->execute([$usuario_id]);
    $resultado = $stmt_cart->fetch(PDO::FETCH_ASSOC);

    if ($resultado && $resultado['subtotal'] > 0) {
        $total_carrinho = (float) $resultado['subtotal'];
    }
    
    // Salva na SESSÃO para a tela_pagamento usar
    $_SESSION['total_compra'] = $total_carrinho;

} catch (PDOException $e) {
    error_log("Erro ao buscar carrinho: " . $e->getMessage());
    // Se der erro, o total fica 0.00
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Forma de Entrega</title>

  <link rel="stylesheet" href="estilos/style.css">
  <link rel="stylesheet" href="estilos/estilo_entrega.css">
  
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
  <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
</head>
<body>

    <header class="topbar">
      <nav class="actions"> 
        <div class="logo-container"> 
            <a href="index.php" style="display: flex; align-items: center;">
              <img src="imagens/exemplo-logo.png" alt="" style="width: 40px; height: 40px;">
            </a>
          </div> 
        
        <form action="buscar.php" method="GET" style="position: relative; width: 600px; max-width: 100%;">
          <input type="search" id="pesquisa" name="q" placeholder="Digite sua pesquisa..." style="font-size: 16px; width: 100%; height: 40px; padding-left: 15px; padding-right: 45px; border-radius: 6px; border: none; box-sizing: border-box;">
          <button type="submit" style="position: absolute; right: 0; top: 0; height: 40px; width: 45px; border: none; background: transparent; cursor: pointer; display: flex; align-items: center; justify-content: center;">
            <img src="imagens/lupa.png" alt="lupa" style="width: 28px; height: 28px; opacity: 0.6;">
          </button>
        </form>
        
        <div style="display: flex; gap: 30px; align-items: center;">
          <a href="tela_minha_conta.php">Olá, <?php echo htmlspecialchars($nome_usuario); ?></a>
          <a href="Banco de dados/logout.php">Sair</a>
          <a href="tela_carrinho.php" style="display: flex; align-items: center; gap: 5px;">
            Carrinho
            <img src="imagens/carrinho invertido.png" alt="" style="width: 20px; height: 20px;">
          </a>
        </div>
        </nav>
    </header>


    <main class="entrega-layout-container">
        
        <div class="opcoes-entrega">
            <h2>Escolha a forma de entrega</h2>
            
            <form id="form-entrega">
                <div class="opcao-bloco selecionado">
                    <input type="radio" id="enviar-endereco" name="forma-entrega" value="endereco" checked>
                    <label for="enviar-endereco" class="opcao-label">
                        <div class="opcao-titulo">
                            <span>Enviar no meu endereço</span>
                            <span class="preco-gratis">Grátis</span>
                        </div>
                        
                        <div class="opcao-detalhes">
                            <?php if ($endereco_padrao): ?>
                                <p style="font-weight: 600; font-size: 15px;">
                                    <?php echo htmlspecialchars($endereco_padrao['rua']); ?>, <?php echo htmlspecialchars($endereco_padrao['numero']); ?> - <?php echo htmlspecialchars($endereco_padrao['bairro']); ?>
                                </p>
                                <p style="margin: 5px 0;">
                                    <?php echo htmlspecialchars($endereco_padrao['cep']); ?> - <?php echo htmlspecialchars($endereco_padrao['cidade']); ?> - <?php echo htmlspecialchars($endereco_padrao['estado']); ?>
                                </p>
                                <p class="tipo-endereco">Recebe: <?php echo htmlspecialchars($nome_usuario_completo); ?></p>
                            <?php else: ?>
                                <p style="font-weight: 600; font-size: 15px;">Nenhum endereço cadastrado.</p>
                                <p style="margin: 5px 0;">Por favor, adicione um endereço.</p>
                            <?php endif; ?>
                            <a href="#" class="link-acao" onclick="abrirModalEnderecoComLocalizacao()">Alterar ou escolher outro endereço</a>
                        </div>
                    </label>
                </div>

                <div class="opcao-bloco">
                    <input type="radio" id="retirar-agencia" name="forma-entrega" value="agencia">
                    <label for="retirar-agencia" class="opcao-label">
                        <div class="opcao-titulo">
                            <span>Retirada na Agência</span>
                            <span class="preco">R$ 5,99</span>
                        </div>
                        
                        <div class="opcao-detalhes" id="detalhes-agencia-selecionada">
                            <p class="distancia">A 450 m do seu endereço</p>
                            <p>Agência - LOJA 1 - (Centro)</p>
                            <p>Segunda à sexta das 9 às 17:30 hs. - Sábado das 9 às 15 hs.</p>
                            <a href="#" class="link-acao" onclick="mostrarModal('modal-retirar-agencia')">Ver agência no mapa ou selecionar outra</a>
                        </div>
                    </label>
                </div>
                
                <button type="button" class="btn-continuar-entrega">Continuar</button>
            </form>
        </div>

        <aside class="resumo-compra">
            <h3>Resumo da compra</h3>
            <div class="resumo-linha">
                <span>Produto</span>
                <span id="resumo-produto-preco">R$ 0,00</span>
            </div>
            <div class="resumo-linha">
                <span>Frete</span>
                <span id="resumo-frete-preco" class="preco-gratis">GRÁTIS</span>
            </div>
            <div class="resumo-divisor" style="display: block;"></div>
            <div class="resumo-linha total">
                <span>Total</span>
                <span id="resumo-total-preco">R$ 0,00</span>
            </div>
        </aside>

    </main>

    <div class="modal-overlay" id="modal-editar-endereco" onclick="fecharModal('modal-editar-endereco')">
        <div class="modal-conteudo" onclick="event.stopPropagation()">
            <button class="modal-fechar" onclick="fecharModal('modal-editar-endereco')"><i class="fa-solid fa-xmark"></i></button>
            <h3>Editar endereço</h3>
            
            <form class="form-modal">
                
                <input type="hidden" id="bairro">
                <input type="hidden" id="cidade">
                <input type="hidden" id="estado">

                <div class="form-grupo">
                    <label for="cep">CEP</label>
                    <input type="text" id="cep" placeholder="Ex.: 13184-000">
                </div>
                
                <div class="form-linha">
                    <div class="form-grupo" style="flex: 2;">
                        <label for="rua">Rua / Avenida</label>
                        <input type="text" id="rua" placeholder="Ex.: Avenida Los Leones, 4563">
                        <span class="msg-erro" id="erro-rua" style="display: none;">Você deve inserir o nome da rua.</span>
                    </div>
                    <div class="form-grupo" style="flex: 1;">
                        <label for="numero">Número</label>
                        <div style="display: flex; align-items: center; gap: 10px;">
                        <input type="text" id="numero" placeholder="Ex.: 45607052" style="flex: 1; min-width: 50px;">
                        <div class="checkbox-container" style="white-space: nowrap;">
                            <input type="checkbox" id="sem-numero">
                            <label for="sem-numero">Sem número</label>
                        </div>
                    </div>
                </div>
                </div>

                <div class="form-grupo">
                    <label for="complemento">Complemento (opcional)</label>
                    <input type="text" id="complemento" placeholder="Ex.: 201">
                </div>

                <div class="form-grupo">
                    <label for="info-adicional">Informações adicionais deste endereço (opcional)</label>
                    <textarea id="info-adicional" placeholder="Ex.: Entre ruas, cor do edifício, não tem campainha." maxlength="128"></textarea>
                    <span class="char-contador">0 / 128</span>
                </div>

                <div class="form-grupo">
                    <p>Este é o seu trabalho ou sua casa?</p>
                    <div class="radio-grupo">
                        <input type="radio" id="casa" name="tipo-local" checked>
                        <label for="casa"><i class="fa-solid fa-house"></i> Casa</label>
                    </div>
                    <div class="radio-grupo">
                        <input type="radio" id="trabalho" name="tipo-local">
                        <label for="trabalho"><i class="fa-solid fa-briefcase"></i> Trabalho</label>
                    </div>
                </div>

                <div class="form-grupo" style="margin-top: 15px;">
                    <button type="button" id="btn-salvar-endereco" class="btn-continuar-entrega">Ok</button>
                </div>

            </form>
        </div>
    </div>
    
    <div class="modal-overlay" id="modal-retirar-agencia" onclick="fecharModal('modal-retirar-agencia')">
        <div class="modal-conteudo modal-grande" onclick="event.stopPropagation()">
            <button class="modal-fechar" onclick="fecharModal('modal-retirar-agencia')"><i class="fa-solid fa-xmark"></i></button>
            <h3>Selecione um ponto de retirada</h3>
            <span class="subtitulo-modal">A agência deve estar localizada no mesmo estado do seu endereço de faturamento.</span>

            <div class="form-grupo form-grupo-com-icone" style="margin-top: 15px;">
                <input type="search" id="busca-local" placeholder="Busque uma localização (Ex: Av. Paulista, São Paulo)">
                <i class="fa-solid fa-magnifying-glass icone-busca" id="btn-busca-local"></i>
            </div>

            <div class="retirada-layout">
                <div class="lista-agencias">
                    <span class="resultado-busca">Mostrando 2 resultados</span>
                    <div class="agencia-item">
                        <h4>LOJA 1</h4>
                        <p>Agência</p>
                        <p><i class="fa-solid fa-location-dot"></i> RUA LUIZ CAMILO DE CAMARGO, 581, Centro, Hortolândia (13184-230) - a 0 m</p>
                        <p><i class="fa-regular fa-clock"></i> Segunda à sexta das 9 às 17:30 hs. - Sábado das 9 às 15 hs.</p>
                        <div class="agencia-footer">
                            <span>Chegará na agência quarta-feira <strong>R$ 5,99</strong></span>
                            <button class="btn-escolher">Escolher</button>
                        </div>
                    </div>
                    <div class="agencia-item">
                        <h4>PONTO EXTRA HORTOLÂNDIA</h4>
                        <p>Agência</p>
                        <p><i class="fa-solid fa-location-dot"></i> AV. DA EMACIPAÇÃO, 1200, Jardim Santa Rita de Cássia, Hortolândia (13186-505) - a 1,2 km</p>
                        <p><i class="fa-regular fa-clock"></i> Segunda à sábado das 8 às 18:30 hs. - Segunda à sábado das 14:30 às 18 hs. - Domingo das 8 às 12 hs.</p>
                        <div class="agencia-footer">
                            <span>Chegará na agência quarta-feira <strong>R$ 5,99</strong></span>
                            <button class="btn-escolher">Escolher</button>
                        </div>
                    </div>
                </div>
                <div class="mapa-container">
                    <div id="mapa"></div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // --- Variáveis globais ---
        let map = null; // Referência global para o mapa Leaflet
        
        // ==========================================================
        // --- (INÍCIO DA CORREÇÃO) ---
        // Tenta ler do localStorage primeiro (vindo do "Comprar Agora" ou "Carrinho")
        let subtotalStorage = localStorage.getItem("totalCompra");
        // Se não achar, usa o valor do PHP (fallback, que geralmente será 0)
        let subtotalCompra = parseFloat(subtotalStorage) || <?php echo $total_carrinho; ?>;
        
        // (O JavaScript das funções mostrarModal, fecharModal, atualizarResumoEntrega, 
        // geolocalização, etc., permanece o mesmo)
        // ...
        // --- Funções para Modais ---
        function mostrarModal(modalId) {
            document.getElementById(modalId).style.display = 'flex';

            // Inicializa o mapa apenas quando o modal de agência é aberto pela primeira vez
            if (modalId === 'modal-retirar-agencia' && !map) {
                const locais = [
                    {
                        nome: 'LOJA 1',
                        endereco: 'RUA LUIZ CAMILO DE CAMARGO, 581, Centro, Hortolândia (13184-230)',
                        coords: { lat: -22.85848, lng: -47.22129 }
                    },
                    {
                        nome: 'PONTO EXTRA HORTOLÂNDIA',
                        endereco: 'AV. DA EMACIPAÇÃO, 1200, Jardim Santa Rita de Cássia, Hortolândia (13186-505)',
                        coords: { lat: -22.86985, lng: -47.21461 }
                    }
                ];
                const centroDoMapa = [-22.864, -47.218]; // Coordenadas aproximadas do centro de Hortolândia

                // Verifica se o elemento 'mapa' existe antes de inicializar
                const mapContainer = document.getElementById('mapa');
                if (mapContainer) {
                    map = L.map(mapContainer).setView(centroDoMapa, 14); // Zoom inicial 14
                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
                    }).addTo(map);

                    // Adiciona marcadores para cada local
                    locais.forEach(local => {
                        L.marker([local.coords.lat, local.coords.lng])
                         .addTo(map)
                         .bindPopup(`<b>${local.nome}</b><br>${local.endereco}`);
                    });

                    // Força o redimensionamento do mapa após um pequeno atraso (necessário quando o mapa está escondido)
                    setTimeout(() => {
                        if (map) {
                            map.invalidateSize();
                        }
                    }, 100); // 100 milissegundos
                } else {
                    console.error("Elemento 'mapa' não encontrado para inicialização do Leaflet.");
                }
            }
        }

        function fecharModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }

// --- Função para Atualizar Resumo ---
        function atualizarResumoEntrega() {
            const freteSelecionadoRadio = document.querySelector('input[name="forma-entrega"]:checked');
            if (!freteSelecionadoRadio) return; // Sai se nenhum radio estiver selecionado

            const freteSelecionado = freteSelecionadoRadio.value;
            const freteSpan = document.getElementById("resumo-frete-preco");
            let valorFrete = 0.0;
            let textoFrete = "GRÁTIS";

            if (freteSelecionado === 'agencia') {
                valorFrete = 5.99;
                textoFrete = "R$ 5,99";
                if(freteSpan) freteSpan.classList.remove("preco-gratis");
            } else { // 'endereco'
                valorFrete = 0.0;
                textoFrete = "GRÁTIS";
                if(freteSpan) freteSpan.classList.add("preco-gratis");
            }

            const valorTotal = subtotalCompra + valorFrete;

            const elProduto = document.getElementById("resumo-produto-preco");
            const elTotal = document.getElementById("resumo-total-preco");

            if(elProduto) elProduto.innerText = "R$ " + subtotalCompra.toFixed(2).replace(".", ",");
            if(freteSpan) freteSpan.innerText = textoFrete;
            if(elTotal) elTotal.innerText = "R$ " + valorTotal.toFixed(2).replace(".", ",");

            // Salva no localStorage para a próxima página
            localStorage.setItem("totalCompra", subtotalCompra.toFixed(2));
            localStorage.setItem("valorFrete", valorFrete.toFixed(2));
            localStorage.setItem("valorFinal", valorTotal.toFixed(2));
        }

        // --- Funções Relacionadas à Geolocalização e Preenchimento ---
        function abrirModalEnderecoComLocalizacao() {
            const modalId = 'modal-editar-endereco';

            if ("geolocation" in navigator) {
                navigator.geolocation.getCurrentPosition(
                    (position) => {
                        const lat = position.coords.latitude;
                        const lon = position.coords.longitude;
                        console.log(`Localização obtida: Lat ${lat}, Lon ${lon}`);
                        buscarEnderecoPorCoords(lat, lon, modalId);
                    },
                    (error) => {
                        console.warn(`Erro ao obter localização (${error.code}): ${error.message}`);
                        limparFormularioEndereco();
                        mostrarModal(modalId);
                    },
                    { // Opções para getCurrentPosition
                        enableHighAccuracy: false, // Pode tentar 'true' se a precisão for baixa
                        timeout: 10000,         // Tempo máximo para obter a posição (10 segundos)
                        maximumAge: 0           // Força obter uma posição nova
                    }
                );
            } else {
                console.warn("Geolocalização não suportada pelo navegador.");
                limparFormularioEndereco();
                mostrarModal(modalId);
            }
        }

        function buscarEnderecoPorCoords(latitude, longitude, modalId) {
            // Usando API Nominatim para geocodificação reversa
            const url = `https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${latitude}&lon=${longitude}&addressdetails=1`; // addressdetails=1 para mais detalhes

            fetch(url)
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`Erro na API Nominatim: ${response.statusText}`);
                    }
                    return response.json();
                 })
                .then(data => {
                    console.log("Dados do endereço (Nominatim):", data);
                    if (data && data.address) {
                        preencherFormularioComDados(data.address);
                    } else {
                         console.warn("Não foi possível obter detalhes do endereço a partir das coordenadas.");
                         limparFormularioEndereco();
                    }
                    mostrarModal(modalId); // Mostra o modal DEPOIS de tentar preencher
                })
                .catch(error => {
                    console.error('Erro na geocodificação reversa:', error);
                    limparFormularioEndereco();
                    mostrarModal(modalId); // Mostra o modal mesmo se a busca falhar
                });
        }

        function preencherFormularioComDados(addressData) {
            // Mapeia os campos da resposta do Nominatim para os IDs do seu formulário
            // Atenção: Os nomes dos campos no Nominatim podem variar (road, postcode, city, suburb, etc.)
            const campos = {
                'cep': addressData.postcode,
                'rua': addressData.road,
                'numero': addressData.house_number, // Tenta pegar o número, se disponível
                'bairro': addressData.suburb, // Preenche o campo oculto
                'cidade': addressData.city || addressData.town, // Preenche o campo oculto
                'estado': addressData.state, // Preenche o campo oculto
            };

            for (const id in campos) {
                const input = document.getElementById(id);
                const valor = campos[id];
                if (input && valor) {
                    if (id === 'cep') {
                        input.value = valor.replace(/\D/g, ''); // Limpa CEP
                    } else {
                        input.value = valor;
                    }
                } else if (input) {
                    // input.value = ''; // Descomente se quiser limpar campos não encontrados
                }
            }
             if (campos['rua'] && campos['numero']) {
                 document.getElementById('complemento')?.focus();
             } else if (campos['rua']) {
                 document.getElementById('numero')?.focus();
             }
        }

        function limparFormularioEndereco() {
            // MODIFICADO: Removido 'nome' e 'telefone', adicionado campos ocultos
            const idsInputs = ['cep', 'rua', 'numero', 'complemento', 'info-adicional', 'bairro', 'cidade', 'estado'];
            idsInputs.forEach(id => {
                const input = document.getElementById(id);
                if (input) input.value = '';
            });

             const erroRua = document.getElementById('erro-rua');
             const inputRua = document.getElementById('rua');
             
             // REMOVIDO: Referências a erroNome, inputNome, erroTelefone, inputTelefone

             if(erroRua) erroRua.style.display = 'none';
             if(inputRua) inputRua.classList.remove('input-erro');
        }

        // --- Roda quando a página carrega ---
        document.addEventListener("DOMContentLoaded", () => {
 
            atualizarResumoEntrega(); // Atualiza o resumo inicial

            // --- Lógica para selecionar as opções de entrega (Radios) ---
            document.querySelectorAll('input[name="forma-entrega"]').forEach(radio => {
                radio.addEventListener('change', function() {
                    document.querySelectorAll('.opcao-bloco').forEach(bloco => {
                        bloco.classList.remove('selecionado');
                    });
                    if (this.checked) {
                        this.closest('.opcao-bloco').classList.add('selecionado');
                    }
                    atualizarResumoEntrega(); 
                });
            });

            // --- Lógica do botão "Escolher" dentro do Modal de Agências ---
            document.querySelectorAll('.btn-escolher').forEach(button => {
                button.addEventListener('click', (e) => {
                    const itemAgencia = e.target.closest('.agencia-item');
                    if (!itemAgencia) return;

                    const nomeAgencia = itemAgencia.querySelector('h4')?.innerText || 'Agência selecionada';
                    const horarioAgenciaElement = itemAgencia.querySelector('p > i.fa-regular.fa-clock');
                    const horarioAgencia = horarioAgenciaElement ? horarioAgenciaElement.parentElement.innerText : 'Horário indisponível';

                    const pEnderecoElement = itemAgencia.querySelector('p > i.fa-solid.fa-location-dot');
                    const pEndereco = pEnderecoElement ? pEnderecoElement.parentElement.innerText : '';
                    const regex = /^(.*?), (.*?), (.*?), (.*?) \((.*?)\)/; 
                    const match = pEndereco.match(regex);

                    let dadosAgencia = { tipo: 'agencia', nome: nomeAgencia, endereco: pEndereco }; // Fallback
                    if (match && match.length >= 6) {
                        dadosAgencia = {
                            tipo: 'agencia',
                            nome: nomeAgencia,
                            documento: 'ISENTO', 
                            endereco: `${match[1].trim()}, ${match[2].trim()}`, 
                            bairro: match[3].trim(),
                            municipio: match[4].trim(),
                            cep: match[5].trim(),
                            uf: 'SP', 
                            fone: '(19) 3888-8888' // Fictício
                        };
                    } else {
                        console.warn("Regex não capturou os dados do endereço da agência:", pEndereco);
                    }

                    localStorage.setItem('dadosEntrega', JSON.stringify(dadosAgencia));

                    const targetDetalhes = document.getElementById('detalhes-agencia-selecionada');
                    if (targetDetalhes) {
                        targetDetalhes.innerHTML = `
                            <p style="font-weight: 600; font-size: 15px;">${nomeAgencia}</p>
                            <p style="margin: 5px 0;">${horarioAgencia}</p>
                            <a href="#" class="link-acao" onclick="mostrarModal('modal-retirar-agencia')">Ver agência no mapa ou selecionar outra</a>
                        `;
                    }

                    const radioAgencia = document.getElementById('retirar-agencia');
                    if (radioAgencia) {
                        radioAgencia.checked = true;
                        document.querySelectorAll('.opcao-bloco').forEach(b => b.classList.remove('selecionado'));
                        radioAgencia.closest('.opcao-bloco').classList.add('selecionado');
                        radioAgencia.dispatchEvent(new Event('change', { bubbles: true }));
                    }

                    fecharModal('modal-retirar-agencia');
                });
            });


            // --- Lógica de Validação do Modal de Endereço ---
            // =================================================================
            // INÍCIO DAS VARIÁVEIS DECLARADAS (UMA SÓ VEZ)
            // =================================================================
            const btnSalvarEndereco = document.getElementById('btn-salvar-endereco');
            const inputRua = document.getElementById('rua');
            const erroRua = document.getElementById('erro-rua');
            
            // REMOVIDO: inputNome, erroNome, inputTelefone, erroTelefone
            
            const inputCep = document.getElementById('cep');
            const inputNumero = document.getElementById('numero');
            const checkboxSemNumero = document.getElementById('sem-numero');
            
            // Outras referências
            const inputComplemento = document.getElementById('complemento');
            const inputInfoAdicional = document.getElementById('info-adicional');
            // =================================================================
            // FIM DAS VARIÁVEIS DECLARADAS
            // =================================================================


            if (btnSalvarEndereco) {
                btnSalvarEndereco.addEventListener('click', () => {
                    let isValid = true;
                    
                    // Reset erros
                    if(erroRua) erroRua.style.display = 'none';
                    if(inputRua) inputRua.classList.remove('input-erro');
                    // REMOVIDO: Reset de erros de nome e telefone

                    // Validações
                    if (inputRua && inputRua.value.trim() === '') {
                        if(erroRua) erroRua.style.display = 'block';
                        inputRua.classList.add('input-erro');
                        isValid = false;
                    }
                    // REMOVIDO: Validação de nome e telefone

                    if (isValid) {
                        // 1. Coletar dados do formulário (campos de endereço)
                        const inputBairro = document.getElementById('bairro');
                        const inputCidade = document.getElementById('cidade');
                        const inputEstado = document.getElementById('estado');
                        let complementoFinal = inputComplemento ? inputComplemento.value.trim() : '';
                        let infoAdicional = inputInfoAdicional ? inputInfoAdicional.value.trim() : '';
                        
                        if (complementoFinal && infoAdicional) {
                            complementoFinal = `${complementoFinal} (${infoAdicional})`;
                        } else if (infoAdicional) {
                            complementoFinal = infoAdicional;
                        }

                        // 2. Criar o payload para o 'salvar_endereco.php'
                        //    (Este é o objeto que será salvo no BD)
                        const payloadEndereco = {
                            cep: inputCep ? inputCep.value : '',
                            rua: inputRua ? inputRua.value : '',
                            numero: checkboxSemNumero.checked ? 'S/N' : (inputNumero ? inputNumero.value : ''),
                            complemento: complementoFinal,
                            bairro: inputBairro ? inputBairro.value : '',
                            cidade: inputCidade ? inputCidade.value : '',
                            estado: inputEstado ? inputEstado.value : ''
                        };

                        // 3. Enviar dados para o PHP via fetch
                        fetch('Banco de dados/salvar_endereco.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify(payloadEndereco) // Envia só os dados do endereço
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.sucesso) {
                                // 4. SUCESSO! Pegar o endereço retornado (que tem o NOVO ID)
                                const novoEndereco = data.endereco; // ex: {id: 7, rua: "...", ...}

                                // 5. (O GRANDE FIX) Criar o objeto COMPLETO para o localStorage
                                //    usando os dados do PHP (para nome/cpf/fone)
                                //    e o 'novoEndereco' retornado (para id/rua/etc)
                                const dadosEntregaCompleto = {
                                    tipo: 'endereco',
                                    endereco_id: novoEndereco.id, // <-- O ID CRÍTICO
                                    nome: '<?php echo htmlspecialchars($nome_usuario_completo); ?>', 
                                    documento: '<?php echo htmlspecialchars($usuario_cpf); ?>',
                                    endereco: `${novoEndereco.rua}, ${novoEndereco.numero}`,
                                    bairro: novoEndereco.bairro,
                                    cep: novoEndereco.cep,
                                    municipio: novoEndereco.cidade,
                                    uf: novoEndereco.estado,
                                    fone: '<?php echo htmlspecialchars($usuario_telefone); ?>'
                                };
                                
                                // 6. (O GRANDE FIX) Salvar o objeto COMPLETO no localStorage
                                localStorage.setItem('dadosEntrega', JSON.stringify(dadosEntregaCompleto));

                                // 7. Atualizar a tela principal (HTML)
                                const detalhesEnderecoDiv = document.querySelector('#enviar-endereco').closest('.opcao-bloco').querySelector('.opcao-detalhes');
                                if (detalhesEnderecoDiv) {
                                    detalhesEnderecoDiv.innerHTML = `
                                        <p style="font-weight: 600; font-size: 15px;">
                                            ${novoEndereco.rua}, ${novoEndereco.numero} - ${novoEndereco.bairro}
                                        </p>
                                        <p style="margin: 5px 0;">
                                            ${novoEndereco.cep} - ${novoEndereco.cidade} - ${novoEndereco.estado}
                                        </p>
                                        <p class="tipo-endereco">Recebe: <?php echo htmlspecialchars($nome_usuario_completo); ?></p> 
                                        <a href="#" class="link-acao" onclick="abrirModalEnderecoComLocalizacao()">Alterar ou escolher outro endereço</a>
                                    `;
                                }
                                
                                // 8. Fechar o modal
                                fecharModal('modal-editar-endereco');
                                
                                // 9. Garantir que a opção "Enviar no endereço" esteja selecionada
                                const radioEndereco = document.getElementById('enviar-endereco');
                                if (radioEndereco) {
                                    radioEndereco.checked = true;
                                    radioEndereco.dispatchEvent(new Event('change', { bubbles: true }));
                                }

                            } else {
                                alert('Erro ao salvar endereço: ' + data.mensagem);
                            }
                        })
                        .catch(error => {
                            console.error('Erro no fetch:', error);
                            alert('Erro de comunicação. Tente novamente.');
                        });
                    }
                });
            } else {
                console.error("Botão 'btn-salvar-endereco' não encontrado.");
            }
            
            // --- Lógica do ViaCEP ---
            // =================================================================
            // INÍCIO DA CORREÇÃO 1: REMOVIDA A REDECLARAÇÃO
            // A variável 'inputCep' já foi declarada acima (linha 608)
            // =================================================================
            // const inputCep = document.getElementById('cep'); // <-- LINHA REMOVIDA
            
            function buscarCepLocal() {
                // (O resto da função permanece o mesmo, 
                // ela usará o 'inputCep' e 'inputRua' do escopo superior)
                if (!inputCep) return; 

                const cep = inputCep.value.replace(/\D/g, ''); 
                if(erroRua) erroRua.style.display = 'none'; 
                if(inputRua) inputRua.classList.remove('input-erro');

                if (cep.length === 8) {
                    if(inputRua) inputRua.value = "Buscando..."; 
                    fetch(`https://viacep.com.br/ws/${cep}/json/`)
                        .then(response => {
                             if (!response.ok) { throw new Error('Erro na resposta da API ViaCEP'); }
                             return response.json();
                         })
                        .then(data => {
                            // --- INÍCIO DA MODIFICAÇÃO (ViaCEP) ---
                            if (data.erro) {
                                console.warn("CEP não encontrado na base do ViaCEP.");
                                if(inputRua) inputRua.value = "";
                                // Limpa campos ocultos
                                document.getElementById('bairro').value = "";
                                document.getElementById('cidade').value = "";
                                document.getElementById('estado').value = "";
                                inputRua?.focus(); 
                            } else {
                                if(inputRua) inputRua.value = data.logradouro || ""; 
                                // Preenche campos ocultos
                                document.getElementById('bairro').value = data.bairro || "";
                                document.getElementById('cidade').value = data.localidade || "";
                                document.getElementById('estado').value = data.uf || "";
                                document.getElementById('numero')?.focus(); 
                            }
                            // --- FIM DA MODIFICAÇÃO (ViaCEP) ---
                        })
                        .catch(error => {
                            console.error('Erro ao consultar a API ViaCEP:', error);
                            if(inputRua) inputRua.value = ""; 
                        });
                } else if (cep.length > 0 && cep.length < 8) {
                     if(inputRua && inputRua.value === "Buscando...") inputRua.value = "";
                } else if (cep.length === 0) {
                     if(inputRua) inputRua.value = "";
                }
            }
            
            if (inputCep) {
                inputCep.addEventListener('blur', buscarCepLocal); 
                inputCep.addEventListener('input', () => { 
                     if(inputRua && inputRua.value === "Buscando...") inputRua.value = "";
                });
                inputCep.addEventListener('keydown', (event) => {
                    if (event.key === 'Enter') {
                        event.preventDefault(); 
                        buscarCepLocal(); 
                    }
                });
            } else {
                 console.error("Input 'cep' não encontrado.");
            }
            // =================================================================
            // FIM DA CORREÇÃO 1
            // =================================================================


             // --- Lógica da Busca de Localização (Mapa/Nominatim) ---
            const inputBuscaLocal = document.getElementById('busca-local');
            const btnBuscaLocal = document.getElementById('btn-busca-local');

            function buscarLocalizacaoMapa() {
                // (Função permanece a mesma)
                if (!inputBuscaLocal || !map) return; 

                const query = inputBuscaLocal.value;
                if (query.trim() === '') return;

                const queryComPais = query.includes('Brasil') ? query : `${query}, Brasil`;
                const url = `https://nominatim.openstreetmap.org/search?q=${encodeURIComponent(queryComPais)}&format=json&limit=1&countrycodes=br&addressdetails=1`;

                const originalPlaceholder = inputBuscaLocal.placeholder;
                inputBuscaLocal.placeholder = "Buscando...";
                inputBuscaLocal.disabled = true;

                fetch(url)
                    .then(response => {
                         if (!response.ok) { throw new Error('Erro na API Nominatim Search'); }
                         return response.json();
                     })
                    .then(data => {
                        inputBuscaLocal.placeholder = originalPlaceholder;
                        inputBuscaLocal.disabled = false;
                        if (data && data.length > 0) {
                            const result = data[0];
                            const lat = parseFloat(result.lat);
                            const lon = parseFloat(result.lon);
                            if (!isNaN(lat) && !isNaN(lon)) {
                                map.setView([lat, lon], 16); 
                            } else {
                                 console.warn("Coordenadas inválidas recebidas do Nominatim:", result);
                                 alert("Localização encontrada, mas com coordenadas inválidas.");
                            }
                        } else {
                            alert("Localização não encontrada. Tente termos mais específicos.");
                        }
                    })
                    .catch(err => {
                        console.error("Erro ao buscar localização no Nominatim:", err);
                        alert("Não foi possível realizar a busca no momento. Verifique sua conexão ou tente mais tarde.");
                        inputBuscaLocal.placeholder = originalPlaceholder;
                        inputBuscaLocal.disabled = false;
                    });
            }

            if (inputBuscaLocal && btnBuscaLocal) {
                inputBuscaLocal.addEventListener('keydown', (event) => {
                    if (event.key === 'Enter') {
                        event.preventDefault();
                        buscarLocalizacaoMapa();
                    }
                });
                btnBuscaLocal.addEventListener('click', buscarLocalizacaoMapa);
            } else {
                 console.error("Elementos 'busca-local' ou 'btn-busca-local' não encontrados.");
            }

            // --- Lógica do Botão Continuar (Navegação) ---
            const btnContinuar = document.querySelector('.btn-continuar-entrega');
            if (btnContinuar) {
                btnContinuar.addEventListener('click', () => {
                    // (Função permanece a mesma)
                    const freteSelecionadoRadio = document.querySelector('input[name="forma-entrega"]:checked');
                    if (!freteSelecionadoRadio) {
                        alert("Por favor, selecione uma forma de entrega.");
                        return;
                    }
                    const freteSelecionado = freteSelecionadoRadio.value;

                    if (freteSelecionado === 'endereco') {
                        
                        const dadosEditadosJSON = localStorage.getItem('dadosEntrega');
                        let dadosEditados = null;
                        try { dadosEditados = dadosEditadosJSON ? JSON.parse(dadosEditadosJSON) : null; } catch(e){}

                        if(dadosEditados && dadosEditados.tipo === 'endereco') {
                             // Já está salvo (provavelmente de um modal)
                        } else if (<?php echo json_encode($endereco_padrao); ?>) {
                            const phpEndereco = <?php echo json_encode($endereco_padrao); ?>;
                            const dadosHomeDefault = {
                                tipo: 'endereco',
                                endereco_id: phpEndereco ? phpEndereco.id : null, // <-- ADICIONADO
                                nome: '<?php echo htmlspecialchars($nome_usuario_completo); ?>', 
                                documento: '<?php echo htmlspecialchars($usuario_cpf); ?>',
                                endereco: `${phpEndereco.rua}, ${phpEndereco.numero}`,
                                bairro: phpEndereco.bairro,
                                cep: phpEndereco.cep,
                                municipio: phpEndereco.cidade,
                                uf: phpEndereco.estado,
                                fone: '<?php echo htmlspecialchars($usuario_telefone); ?>'
                            };
                            localStorage.setItem('dadosEntrega', JSON.stringify(dadosHomeDefault));
                        } else {
                            alert("Por favor, adicione um endereço de entrega clicando em 'Alterar ou escolher outro endereço'.");
                            return; 
                        }

                    } else { 
                        const dadosAgenciaJSON = localStorage.getItem('dadosEntrega');
                        let dadosAgencia = null;
                         try { dadosAgencia = dadosAgenciaJSON ? JSON.parse(dadosAgenciaJSON) : null; } catch(e){}

                        if (!dadosAgencia || dadosAgencia.tipo !== 'agencia') {
                             const dadosDefaultAgencia = {
                                tipo: 'agencia',
                                endereco_id: null, // <-- ADICIONADO
                                nome: 'Agência - LOJA 1', 
                                documento: 'ISENTO',
                                // ... resto dos dados ...
                            };
                             localStorage.setItem('dadosEntrega', JSON.stringify(dadosDefaultAgencia));
                        }
                    }

                    window.location.href = 'tela_pagamento.php';
                });
            } else {
                 console.error("Botão '.btn-continuar-entrega' não encontrado.");
            }

             // --- Ajuste para checkbox "Sem número" ---
            // =================================================================
            // INÍCIO DA CORREÇÃO 2: REMOVIDAS AS REDECLARAÇÕES
            // As variáveis 'checkboxSemNumero' e 'inputNumero' já foram
            // declaradas acima (linhas 609 e 610)
            // =================================================================
             // const checkboxSemNumero = document.getElementById('sem-numero'); // <-- LINHA REMOVIDA
             // const inputNumero = document.getElementById('numero'); // <-- LINHA REMOVIDA
             if (checkboxSemNumero && inputNumero) {
                 checkboxSemNumero.addEventListener('change', function() {
                     inputNumero.disabled = this.checked;
                     if (this.checked) {
                         inputNumero.value = ''; 
                     }
                 });
             }
            // =================================================================
            // FIM DA CORREÇÃO 2
            // =================================================================


             // --- Lógica contador de caracteres ---
             const textareaInfo = document.getElementById('info-adicional');
             const contadorSpan = document.querySelector('.char-contador');
             if(textareaInfo && contadorSpan) {
                 textareaInfo.addEventListener('input', () => {
                     const currentLength = textareaInfo.value.length;
                     contadorSpan.textContent = `${currentLength} / 128`;
                 });
                 contadorSpan.textContent = `${textareaInfo.value.length} / 128`;
             }

            // --- Lógica da Máscara de Telefone (REMOVIDA) ---
         
        }); // <-- FIM do DOMContentLoaded
    </script>
</body>
</html>