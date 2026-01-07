<?php
// 1. Start output buffering to capture any unwanted whitespace/errors/warnings
ob_start();

// 2. Disable display_errors immediately
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require '../../Banco de dados/conexao.php';
require '../../vendor/autoload.php';

use Services\NotificationService;

header('Content-Type: application/json');


if (!isset($_SESSION['usuario_id'])) {
    // If user is not logged in, return empty data or error
    ob_clean();
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

            // Debug logging
            file_put_contents('debug_notifications_api.log', date('Y-m-d H:i:s') . " - Poll for User $userId: Count=$count, Notifs=" . count($notifications) . "\n", FILE_APPEND);

            // Clear buffer before sending JSON
            ob_clean();
            echo json_encode(['count' => $count, 'notifications' => $notifications]);
            break;

        case 'mark_read':
            $id = $_POST['id'] ?? 0;
            if ($id) {
                $service->markAsRead($id, $userId);
                ob_clean();
                echo json_encode(['success' => true]);
            } else {
                ob_clean();
                echo json_encode(['success' => false, 'error' => 'missing_id']);
            }
            break;

        case 'mark_all_read':
            $service->markAllAsRead($userId);
            ob_clean();
            echo json_encode(['success' => true]);
            break;

        default:
            ob_clean();
            echo json_encode(['error' => 'Invalid action']);
    }
} catch (Exception $e) {
    http_response_code(500);
    ob_clean();
    echo json_encode(['error' => $e->getMessage()]);
}
