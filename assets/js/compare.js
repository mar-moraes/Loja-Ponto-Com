
// compare.js - Gerencia a lógica de comparação de produtos

const MAX_COMPARE = 3;
const STORAGE_KEY = 'produtos_comparacao';
const MODE_KEY = 'modo_comparacao_ativo';

// --- Funções Principais ---

function getComparisonList() {
    return JSON.parse(localStorage.getItem(STORAGE_KEY)) || [];
}

function addToComparison(produto) {
    let list = getComparisonList();
    if (list.length >= MAX_COMPARE) {
        alert("Você já selecionou o número máximo de produtos para comparar.");
        return false;
    }
    // Verifica se já existe
    if (list.find(p => p.id === produto.id)) {
        return false; // Já está na lista
    }

    list.push(produto);
    localStorage.setItem(STORAGE_KEY, JSON.stringify(list));
    updateComparisonUI();
    return true;
}

function removeFromComparison(produtoId) {
    let list = getComparisonList();
    list = list.filter(p => p.id != produtoId); // != para pegar string/int
    localStorage.setItem(STORAGE_KEY, JSON.stringify(list));
    updateComparisonUI();
}

function toggleComparisonMode(active) {
    if (active) {
        localStorage.setItem(MODE_KEY, 'true');
    } else {
        localStorage.removeItem(MODE_KEY);
        // Limpa a lista ao cancelar
        localStorage.removeItem(STORAGE_KEY);
    }
    updateComparisonUI();
}

function isComparisonMode() {
    return localStorage.getItem(MODE_KEY) === 'true';
}

// --- UI Updates ---

function updateComparisonUI() {
    // 1. Atualiza a Barra Flutuante
    const list = getComparisonList();
    const modeActive = isComparisonMode();

    let bar = document.getElementById('comparison-bar');

    if (modeActive) {
        if (!bar) {
            bar = document.createElement('div');
            bar.id = 'comparison-bar';
            document.body.appendChild(bar);
        }

        // Verifica se tem produtos suficientes (pelo menos 2 para comparar, mas permitimos 1 para começar fluxo)
        const canConfirm = list.length >= 2;

        bar.innerHTML = `
            <div class="comparison-content">
                <span>Comparar <strong>${list.length}</strong>/${MAX_COMPARE} produtos</span>
                <div class="comparison-actions">
                    <button class="btn-cancel" onclick="cancelComparison()">Cancelar</button>
                    <button class="btn-confirm ${canConfirm ? '' : 'disabled'}" onclick="confirmComparison()" ${canConfirm ? '' : 'disabled'}>
                        Confirmar
                    </button>
                </div>
            </div>
        `;
        bar.style.display = 'block';

        // Adiciona classe ao body para ajuste de padding se necessário
        document.body.classList.add('comparison-mode-active');

    } else {
        if (bar) bar.style.display = 'none';
        document.body.classList.remove('comparison-mode-active');
    }

    // 2. Atualiza bordas nos Cards (se estiver na tela inicial)
    const cards = document.querySelectorAll('.card-link');
    if (cards.length > 0) {
        cards.forEach(link => {
            const id = link.getAttribute('data-id');
            const card = link.querySelector('.card');

            if (modeActive) {
                // Desabilita link normal
                link.onclick = (e) => {
                    e.preventDefault();
                    toggleProductSelection(id, link);
                };

                // Marca se selecionado
                if (list.find(p => p.id == id)) {
                    card.classList.add('selected-for-compare');
                } else {
                    card.classList.remove('selected-for-compare');
                }
            } else {
                // Restaura link
                link.onclick = null;
                card.classList.remove('selected-for-compare');
            }
        });
    }
}

// --- Ações ---

function startComparisonFlow(currentProduct) {
    // 1. Limpa anterior
    localStorage.removeItem(STORAGE_KEY);
    // 2. Adiciona o atual
    addToComparison(currentProduct);
    // 3. Ativa modo
    toggleComparisonMode(true);
    // 4. Redireciona para Home
    window.location.href = 'index.php';
}

function cancelComparison() {
    toggleComparisonMode(false);
    // Se estiver na home, recarrega para limpar visualmente ou chama update
    updateComparisonUI();
    // Opcional: Recarregar página para garantir limpeza de eventos
    window.location.reload();
}

function confirmComparison() {
    const list = getComparisonList();
    if (list.length < 2) {
        alert("Selecione pelo menos 2 produtos para comparar.");
        return;
    }

    // Pega IDs
    const ids = list.map(p => p.id).join(',');
    window.location.href = `tela_comparacao.php?ids=${ids}`;
}

function toggleProductSelection(id, element) {
    let list = getComparisonList();
    const exists = list.find(p => p.id == id);

    if (exists) {
        removeFromComparison(id);
    } else {
        // Precisa pegar dados básicos do DOM para salvar (Título, Imagem, Preço)
        const card = element.querySelector('.card');
        const title = card.querySelector('.title').textContent;
        // Imagem url está no style background-image
        // Simplicidade: Apenas ID é crítico para a próxima tela.

        const success = addToComparison({ id: id, nome: title });
        if (!success) {
            // Feedback visual de erro (ex: shake)
        }
    }
}


// --- Inicialização ---
document.addEventListener('DOMContentLoaded', () => {
    updateComparisonUI();
});
