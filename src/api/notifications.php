<?php
session_start();
require '../Banco de dados/conexao.php';
require '../vendor/autoload.php';

use Services\NotificationService;

header('Content-Type: application/json');

if (!isset($_SESSION['usuario_id'])) {
    // If user is not logged in, return empty data or error
    echo json_encode(['count' => 0, 'notifications' => [], 'error' => 'not_logged_in']);
    exit;
}

$service = new NotificationService($pdo);
$userId = $_SESSION['usuario_id'];
$action = $_GET['action'] ?? $_POST['action'] ?? '';

try {
    switch ($action) {
        case 'poll':
            $count = $service->getUnreadCount($userId);
            $notifications = $service->getByUser($userId, 5);
            echo json_encode(['count' => $count, 'notifications' => $notifications]);
            break;

        case 'mark_read':
            $id = $_POST['id'] ?? 0;
            if ($id) {
                $service->markAsRead($id, $userId);
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'error' => 'missing_id']);
            }
            break;

        case 'mark_all_read':
            $service->markAllAsRead($userId);
            echo json_encode(['success' => true]);
            break;

        default:
            echo json_encode(['error' => 'Invalid action']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
