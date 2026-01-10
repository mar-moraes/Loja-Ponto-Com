document.addEventListener("DOMContentLoaded", function () {
    const listContainer = document.getElementById("conversation-list");
    const chatHeader = document.getElementById("chat-header");
    const messagesContainer = document.getElementById("chat-messages");
    const inputArea = document.getElementById("chat-input-area");
    const formSend = document.getElementById("form-send-message");
    const inputMessage = document.getElementById("message-input");
    const chatPartnerName = document.getElementById("chat-partner-name");
    const chatProductContext = document.getElementById("chat-product-context");
    const currentChatIdInput = document.getElementById("current-chat-id");

    let currentChatId = null;
    let pollingInterval = null;

    // Assegura que USER_ID está definido (fallback para 0)
    const MY_USER_ID = (typeof USER_ID !== 'undefined') ? USER_ID : 0;

    // --- 1. INICIALIZAÇÃO E CHECAGEM DE URL PARAMS ---
    const urlParams = new URLSearchParams(window.location.search);
    const newChatFornecedor = urlParams.get('create_chat_with');
    const newChatProduto = urlParams.get('product_id');
    const existingChatId = urlParams.get('chat_id');

    if (newChatFornecedor) {
        criarOuAbrirConversa(newChatFornecedor, newChatProduto);
    } else if (existingChatId) {
        // Se tem chat_id na URL, definimos como atual para o filtro funcionar
        currentChatId = existingChatId;
        carregarConversas().then((conversas) => {
            const conv = conversas ? conversas.find(c => c.id == existingChatId) : null;
            selecionarConversa(existingChatId, conv);
        });
    } else {
        carregarConversas();
    }

    // --- 2. FUNÇÕES DE API ---

    function carregarConversas() {
        return fetch('../src/api/chat.php?action=list')
            .then(res => res.json())
            .then(data => {
                let conversasFinal = data.conversas || [];

                // Se estivermos em modo de foco (veio do produto), filtra apenas a conversa atual
                const params = new URLSearchParams(window.location.search);
                if (params.get('focus') === 'true' && currentChatId) {
                    conversasFinal = conversasFinal.filter(c => c.id == currentChatId);
                }

                renderizarListaConversas(conversasFinal);
                return conversasFinal; // Retorna para uso no encadeamento
            })
            .catch(err => console.error("Erro ao carregar conversas:", err));
    }

    function criarOuAbrirConversa(fornecedorId, produtoId) {
        fetch('../src/api/chat.php?action=create', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ fornecedor_id: fornecedorId, produto_id: produtoId })
        })
            .then(res => res.json())
            .then(data => {
                if (data.sucesso) {
                    const params = new URLSearchParams(window.location.search);
                    let newUrl = window.location.pathname + '?chat_id=' + data.conversa_id;
                    if (params.get('focus') === 'true') {
                        newUrl += '&focus=true';
                    }

                    window.history.pushState({ path: newUrl }, '', newUrl);


                    currentChatId = data.conversa_id;
                    carregarConversas().then((conversas) => {
                        const conv = conversas ? conversas.find(c => c.id == data.conversa_id) : null;
                        selecionarConversa(data.conversa_id, conv);
                    });
                } else {
                    alert("Erro ao iniciar conversa: " + (data.error || "Erro desconhecido"));
                }
            });
    }

    function carregarMensagens(chatId) {
        return fetch(`../src/api/chat.php?action=history&chat_id=${chatId}`)
            .then(res => res.json())
            .then(data => {
                renderizarMensagens(data.mensagens || []);
            });
    }

    function enviarMensagem(chatId, conteudo) {
        return fetch('../src/api/chat.php?action=send', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ conversa_id: chatId, conteudo: conteudo })
        })
            .then(res => res.json());
    }

    // --- 3. LÓGICA DE UI ---

    function renderizarListaConversas(conversas) {
        listContainer.innerHTML = "";

        if (conversas.length === 0) {
            listContainer.innerHTML = '<p style="padding:15px; text-align:center; color:#999;">Nenhuma conversa iniciada.</p>';
            return;
        }

        conversas.forEach(conv => {
            const item = document.createElement("div");
            item.className = `conversation-item ${conv.id == currentChatId ? 'active' : ''}`;
            item.dataset.id = conv.id;

            const date = new Date(conv.data_atualizacao);
            const timeString = date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });

            item.innerHTML = `
                <span class="conversation-name">${conv.outro_participante_nome}</span>
                <span class="conversation-preview">${conv.ultima_mensagem || '<i>Iniciar conversa</i>'}</span>
                <div class="conversation-meta">
                    <span>${timeString}</span>
                    ${conv.nao_lidas > 0 ? `<span class="unread-badge">${conv.nao_lidas}</span>` : ''}
                </div>
            `;

            item.addEventListener("click", () => selecionarConversa(conv.id, conv));
            listContainer.appendChild(item);
        });
    }

    function selecionarConversa(chatId, dadosConversa = null) {
        currentChatId = chatId;
        currentChatIdInput.value = chatId;

        document.querySelectorAll(".conversation-item").forEach(el => el.classList.remove("active"));
        const activeItem = document.querySelector(`.conversation-item[data-id='${chatId}']`);
        if (activeItem) activeItem.classList.add("active");

        if (dadosConversa) {
            atualizarHeader(dadosConversa);
        }

        chatHeader.style.display = "flex";
        inputArea.style.display = "flex";

        carregarMensagens(chatId).then(() => {
            scrollToBottom();
        });

        if (pollingInterval) clearInterval(pollingInterval);
        pollingInterval = setInterval(() => {
            carregarMensagens(chatId);
        }, 5000);
    }

    function atualizarHeader(conv) {
        chatPartnerName.innerText = conv.outro_participante_nome;
        chatProductContext.innerText = conv.produto_nome ? `Produto: ${conv.produto_nome}` : (conv.pedido_id ? `Pedido #${conv.pedido_id}` : '');
        chatProductContext.style.display = (conv.produto_nome || conv.pedido_id) ? 'inline-block' : 'none';
    }

    function renderizarMensagens(mensagens) {
        messagesContainer.innerHTML = "";

        if (!mensagens || mensagens.length === 0) {
            messagesContainer.innerHTML = '<div class="empty-state"><p>Nenhuma mensagem ainda.</p></div>';
            return;
        }

        mensagens.forEach(msg => {
            const div = document.createElement("div");
            // Usamos a constante global definida no inicio
            const souEu = (msg.remetente_id == MY_USER_ID);

            div.className = `message-bubble ${souEu ? 'sent' : 'received'}`;

            const date = new Date(msg.data_envio);
            const time = date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });

            const check = souEu ? `<span class="status-check ${msg.lida == 1 ? 'read' : ''}">✓✓</span>` : '';

            div.innerHTML = `
                ${msg.conteudo}
                <div class="message-time">
                    ${time}
                    ${check}
                </div>
            `;
            messagesContainer.appendChild(div);
        });
    }

    function scrollToBottom() {
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }

    // --- 4. ENVIO ---
    // --- 4. ENVIO ---

    // Auto-resize do textarea
    inputMessage.addEventListener('input', function () {
        this.style.height = 'auto';
        this.style.height = (this.scrollHeight) + 'px';
        if (this.value === '') {
            this.style.height = ''; // Volta ao CSS original (min-height)
        }
    });

    // Enter para enviar, Shift+Enter para nova linha
    inputMessage.addEventListener('keydown', function (e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            formSend.dispatchEvent(new Event('submit'));
        }
    });

    formSend.addEventListener("submit", (e) => {
        e.preventDefault();
        const msg = inputMessage.value.trim();
        if (!msg || !currentChatId) return;

        inputMessage.value = "";
        inputMessage.style.height = ''; // Reseta altura

        enviarMensagem(currentChatId, msg).then(data => {
            if (data.sucesso) {
                carregarMensagens(currentChatId).then(scrollToBottom);
            } else {
                alert("Erro ao enviar.");
            }
        });
    });

});
