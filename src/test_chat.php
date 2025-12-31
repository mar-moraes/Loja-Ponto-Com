<?php
require __DIR__ . '/../Banco de dados/conexao.php';
require __DIR__ . '/../vendor/autoload.php';

use Services\ChatService;
use Services\NotificationService;

$notificationService = new NotificationService($pdo);
$chatService = new ChatService($pdo, $notificationService);

echo "--- Iniciando Teste do Chat System ---\n";

// 1. Criar Conversa (Comprador: 3, Fornecedor: 6, Produto: 22)
// IDs baseados no dump do banco de dados
$compradorId = 3;
$fornecedorId = 6;
$produtoId = 22;

echo "1. Criando conversa (User $compradorId -> Fornecedor $fornecedorId, Produto $produtoId)...\n";
$chatId = $chatService->createConversation($compradorId, $fornecedorId, $produtoId);
echo "   Conversa ID: $chatId\n";

// 2. Enviar Mensagem (Comprador)
echo "2. Enviando mensagem do Comprador...\n";
$msgConteudo = "Olá, este produto ainda está disponível? " . date('H:i:s');
$chatService->sendMessage($chatId, $compradorId, $msgConteudo);
echo "   Mensagem enviada.\n";

// 3. Enviar Mensagem (Fornecedor)
echo "3. Enviando resposta do Fornecedor...\n";
$respConteudo = "Sim, temos em estoque! " . date('H:i:s');
$chatService->sendMessage($chatId, $fornecedorId, $respConteudo);
echo "   Resposta enviada.\n";

// 4. Listar Conversas do Comprador
echo "4. Listando conversas do comprador ($compradorId)...\n";
$conversas = $chatService->getConversations($compradorId);
foreach ($conversas as $c) {
    echo "   [Chat {$c['id']}] Com: {$c['outro_participante_nome']} | Última: {$c['ultima_mensagem']}\n";
}

// 5. Ler Mensagens (Como Comprador)
echo "5. Lendo mensagens do chat $chatId como comprador...\n";
$msgs = $chatService->getMessages($chatId, $compradorId);
foreach ($msgs as $m) {
    $status = $m['lida'] ? '[Lida]' : '[Não lida]';
    echo "   $status {$m['remetente_nome']}: {$m['conteudo']}\n";
}

echo "--- Teste Finalizado ---\n";
