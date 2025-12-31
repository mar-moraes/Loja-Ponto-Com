<?php
session_start();
require '../../Banco de dados/conexao.php';
require '../../vendor/autoload.php';

use Services\ChatService;
use Services\NotificationService;

header('Content-Type: application/json');

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['error' => 'Usuário não autenticado']);
    exit;
}

$userId = $_SESSION['usuario_id'];
$notificationService = new NotificationService($pdo);
$chatService = new ChatService($pdo, $notificationService);

$action = $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'create':
            // Cria nova conversa (ex: vinda da tela de produto)
            $input = json_decode(file_get_contents('php://input'), true);
            $fornecedorId = $input['fornecedor_id'] ?? null;
            $produtoId = $input['produto_id'] ?? null;

            if (!$fornecedorId) {
                throw new Exception("ID do fornecedor obrigatório.");
            }

            // O usuário logado é sempre o COMPRADOR neste fluxo inicial, 
            // mas se fosse um sistema reverso, teríamos que validar.
            // Para simplificar: quem clica em "Falar" é o iniciador.
            $conversaId = $chatService->createConversation($userId, $fornecedorId, $produtoId);

            echo json_encode(['sucesso' => true, 'conversa_id' => $conversaId]);
            break;

        case 'send':
            $input = json_decode(file_get_contents('php://input'), true);
            $conversaId = $input['conversa_id'];
            $conteudo = $input['conteudo'];

            if (empty($conteudo)) throw new Exception("Mensagem vazia.");

            $success = $chatService->sendMessage($conversaId, $userId, $conteudo);
            echo json_encode(['sucesso' => $success]);
            break;

        case 'list':
            $conversas = $chatService->getConversations($userId);
            echo json_encode(['conversas' => $conversas]);
            break;

        case 'history':
            $conversaId = $_GET['chat_id'] ?? 0;
            $mensagens = $chatService->getMessages($conversaId, $userId);
            echo json_encode(['mensagens' => $mensagens]);
            break;

        default:
            echo json_encode(['error' => 'Ação inválida']);
    }
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
