<?php

namespace Services;

use PDO;
use Exception;

class ChatService
{
    private $pdo;
    private $notificationService;

    public function __construct(PDO $pdo, NotificationService $notificationService = null)
    {
        $this->pdo = $pdo;
        $this->notificationService = $notificationService;
    }

    /**
     * Cria ou retorna uma conversa existente entre comprador e fornecedor para um produto/pedido
     */
    public function createConversation($compradorId, $fornecedorId, $produtoId = null, $pedidoId = null)
    {
        if ($compradorId == $fornecedorId) {
            throw new Exception("Não é possível iniciar conversa consigo mesmo.");
        }

        // 1. Verificar se já existe conversa (opcional: focar apenas em produto ou pedido específico para não duplicar)
        // Aqui, vamos permitir múltiplas conversas se forem de PRODUTOS diferentes, mas reusar se for o MESMO contexto.

        $sql = "SELECT id FROM conversas WHERE comprador_id = :comprador AND fornecedor_id = :fornecedor";
        $params = [':comprador' => $compradorId, ':fornecedor' => $fornecedorId];

        if ($produtoId) {
            $sql .= " AND produto_id = :produto";
            $params[':produto'] = $produtoId;
        } elseif ($pedidoId) {
            $sql .= " AND pedido_id = :pedido";
            $params[':pedido'] = $pedidoId;
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $existing = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($existing) {
            return $existing['id'];
        }

        // 2. Criar nova conversa
        $stmtInsert = $this->pdo->prepare("INSERT INTO conversas (comprador_id, fornecedor_id, produto_id, pedido_id) VALUES (:comprador, :fornecedor, :produto, :pedido)");
        $stmtInsert->execute([
            ':comprador' => $compradorId,
            ':fornecedor' => $fornecedorId,
            ':produto' => $produtoId,
            ':pedido' => $pedidoId
        ]);

        return $this->pdo->lastInsertId();
    }

    /**
     * Envia uma mensagem em uma conversa
     */
    public function sendMessage($conversaId, $remetenteId, $conteudo)
    {
        // 1. Inserir mensagem
        $stmt = $this->pdo->prepare("INSERT INTO mensagens (conversa_id, remetente_id, conteudo) VALUES (:conversa, :remetente, :conteudo)");
        $success = $stmt->execute([
            ':conversa' => $conversaId,
            ':remetente' => $remetenteId,
            ':conteudo' => $conteudo
        ]);

        if ($success) {
            // 2. Atualizar data da conversa (para ordenação)
            $stmtUpdate = $this->pdo->prepare("UPDATE conversas SET data_atualizacao = NOW() WHERE id = ?");
            $stmtUpdate->execute([$conversaId]);

            // 3. Notificar o destinatário (se NotificationService estiver disponível)
            if ($this->notificationService) {
                // Descobre quem é o OUTRO participante
                $stmtConv = $this->pdo->prepare("SELECT comprador_id, fornecedor_id FROM conversas WHERE id = ?");
                $stmtConv->execute([$conversaId]);
                $chat = $stmtConv->fetch(PDO::FETCH_ASSOC);

                if ($chat) {
                    $destinatarioId = ($chat['comprador_id'] == $remetenteId) ? $chat['fornecedor_id'] : $chat['comprador_id'];
                    $msgPreview = substr($conteudo, 0, 50) . (strlen($conteudo) > 50 ? '...' : '');

                    $this->notificationService->create(
                        $destinatarioId,
                        "Nova mensagem: " . $msgPreview,
                        "primary",
                        "tela_chat.php?chat_id=" . $conversaId
                    );
                }
            }
        }

        return $success;
    }

    /**
     * Lista conversas de um usuário
     */
    public function getConversations($userId)
    {
        // Busca conversas onde o usuário é comprador OU fornecedor
        // Traz também o nome do Outro Participante e a última mensagem
        $sql = "
            SELECT 
                c.*, 
                CASE 
                    WHEN c.comprador_id = :user THEN u_fornec.nome 
                    ELSE u_comp.nome 
                END as outro_participante_nome,
                p.nome as produto_nome,
                (SELECT conteudo FROM mensagens m WHERE m.conversa_id = c.id ORDER BY m.data_envio DESC LIMIT 1) as ultima_mensagem,
                (SELECT data_envio FROM mensagens m WHERE m.conversa_id = c.id ORDER BY m.data_envio DESC LIMIT 1) as data_ultima_mensagem,
                (SELECT COUNT(*) FROM mensagens m WHERE m.conversa_id = c.id AND m.lida = 0 AND m.remetente_id != :user) as nao_lidas
            FROM conversas c
            JOIN usuarios u_comp ON c.comprador_id = u_comp.id
            JOIN usuarios u_fornec ON c.fornecedor_id = u_fornec.id
            LEFT JOIN produtos p ON c.produto_id = p.id
            WHERE c.comprador_id = :user OR c.fornecedor_id = :user
            ORDER BY data_atualizacao DESC
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':user' => $userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Busca histórico de mensagens de uma conversa
     */
    public function getMessages($conversaId, $userId)
    {
        // Verifica permissão (se o usuário pertence à conversa)
        $stmtCheck = $this->pdo->prepare("SELECT id FROM conversas WHERE id = ? AND (comprador_id = ? OR fornecedor_id = ?)");
        $stmtCheck->execute([$conversaId, $userId, $userId]);
        if (!$stmtCheck->fetch()) {
            throw new Exception("Acesso negado à conversa.");
        }

        // Marca como lidas as mensagens que NÃO foram enviadas por mim
        $stmtRead = $this->pdo->prepare("UPDATE mensagens SET lida = 1 WHERE conversa_id = ? AND remetente_id != ?");
        $stmtRead->execute([$conversaId, $userId]);

        // Busca mensagens
        $stmt = $this->pdo->prepare("
            SELECT m.*, u.nome as remetente_nome 
            FROM mensagens m
            JOIN usuarios u ON m.remetente_id = u.id
            WHERE m.conversa_id = ?
            ORDER BY m.data_envio ASC
        ");
        $stmt->execute([$conversaId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
