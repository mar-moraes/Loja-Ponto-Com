<?php

namespace Services;

use PDO;

class NotificationService
{
    private $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function create($userId, $message, $type = 'primary', $link = null)
    {
        $stmt = $this->pdo->prepare("INSERT INTO notificacoes (usuario_id, mensagem, tipo, link) VALUES (:usuario_id, :mensagem, :tipo, :link)");
        return $stmt->execute([
            ':usuario_id' => $userId,
            ':mensagem' => $message,
            ':tipo' => $type,
            ':link' => $link
        ]);
    }

    public function getByUser($userId, $limit = 10)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM notificacoes WHERE usuario_id = :usuario_id ORDER BY data_criacao DESC LIMIT :limit");
        $stmt->bindValue(':usuario_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getUnreadCount($userId)
    {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM notificacoes WHERE usuario_id = :usuario_id AND lida = 0");
        $stmt->execute([':usuario_id' => $userId]);
        return $stmt->fetchColumn();
    }

    public function markAsRead($notificationId, $userId)
    {
        $stmt = $this->pdo->prepare("UPDATE notificacoes SET lida = 1 WHERE id = :id AND usuario_id = :usuario_id");
        return $stmt->execute([':id' => $notificationId, ':usuario_id' => $userId]);
    }

    public function markAllAsRead($userId)
    {
        $stmt = $this->pdo->prepare("UPDATE notificacoes SET lida = 1 WHERE usuario_id = :usuario_id");
        return $stmt->execute([':usuario_id' => $userId]);
    }
}
