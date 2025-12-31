<?php
session_start();
require '../Banco de dados/conexao.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: tela_login.html?redirecionar=tela_chat.php');
    exit();
}

$usuario_id = $_SESSION['usuario_id'];
$nome_usuario = explode(' ', $_SESSION['usuario_nome'])[0];
$usuario_logado = true;
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>Suas Conversas - Loja Ponto Com</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/estilos/style.css">
    <link rel="stylesheet" href="../assets/estilos/notifications.css">
    <link rel="stylesheet" href="../assets/estilos/chat.css">
    <script>
        const USER_ID = <?php echo $usuario_id; ?>;
    </script>
</head>

<body>

    <header class="topbar">
        <nav class="actions">
            <div class="logo-container">
                <a href="index.php" style="display: flex; align-items: center;">
                    <img src="../assets/imagens/exemplo-logo.png" alt="" style="width: 40px; height: 40px;">
                </a>
            </div>

            <div style="flex: 1;"></div> <!-- Spacer -->

            <div style="display: flex; gap: 30px; align-items: center;">
                <a href="tela_minha_conta.php">Olá, <?php echo htmlspecialchars($nome_usuario); ?></a>
                <a href="../Banco de dados/logout.php">Sair</a>
                <a href="tela_carrinho.php" style="display: flex; align-items: center; gap: 5px;">
                    Carrinho
                    <img src="../assets/imagens/carrinho invertido.png" alt="" style="width: 20px; height: 20px;">
                </a>
                <?php if ($usuario_logado): ?>
                    <!-- Notification System -->
                    <div id="notification-bell" class="notification-container">
                        <svg class="notification-bell-icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                            <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                        </svg>
                        <span id="notification-badge" class="notification-badge"></span>
                        <div id="notification-dropdown" class="notification-dropdown">
                            <div class="notification-header">
                                <span>Notificações</span>
                                <span id="mark-all-read" class="mark-all-read">Marcar todas como lidas</span>
                            </div>
                            <div id="notification-list"></div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </nav>
    </header>

    <div class="chat-container">
        <aside class="chat-sidebar">
            <div class="sidebar-header">
                <h3>Conversas</h3>
            </div>
            <div id="conversation-list" class="conversation-list">
                <!-- Lista carregada via JS -->
                <p style="padding:10px; color:#666;">Carregando...</p>
            </div>
        </aside>

        <main class="chat-window">
            <div id="chat-header" class="chat-header" style="display: none;">
                <div class="user-info">
                    <h3 id="chat-partner-name">Nome do Contato</h3>
                    <span id="chat-product-context" class="product-context">Produto: ...</span>
                </div>
            </div>

            <div id="chat-messages" class="chat-messages">
                <div class="empty-state">
                    <img src="../assets/imagens/chat-placeholder.png" alt="Chat" style="width: 100px; opacity: 0.5;">
                    <p>Selecione uma conversa para começar</p>
                </div>
            </div>

            <div id="chat-input-area" class="chat-input-area" style="display: none;">
                <form id="form-send-message">
                    <input type="hidden" id="current-chat-id" value="">
                    <input type="text" id="message-input" placeholder="Digite sua mensagem..." autocomplete="off">
                    <button type="submit">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="22" y1="2" x2="11" y2="13"></line>
                            <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
                        </svg>
                    </button>
                </form>
            </div>
        </main>
    </div>

    <script src="../assets/js/notifications.js"></script>
    <script src="../assets/js/chat.js"></script>
</body>

</html>