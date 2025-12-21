<?php
session_start();
require_once '../config/database.php';
require_once '../includes/notification_helper.php';

header('Content-Type: application/json');

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập']);
    exit;
}

$userId = $_SESSION['user_id'];

// Xử lý request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $action = $input['action'] ?? '';
    
    switch ($action) {
        case 'markRead':
            $notificationId = $input['id'] ?? '';
            if ($notificationId && markNotificationAsRead($notificationId, $userId)) {
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Không thể cập nhật']);
            }
            break;
            
        case 'markAllRead':
            if (markAllNotificationsAsRead($userId)) {
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Không thể cập nhật']);
            }
            break;
            
        case 'delete':
            $notificationId = $input['id'] ?? '';
            if ($notificationId && deleteNotification($notificationId, $userId)) {
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Không thể xóa']);
            }
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Action không hợp lệ']);
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
    $notifications = getNotifications($userId, $limit);
    $unreadCount = countUnreadNotifications($userId);
    
    echo json_encode([
        'success' => true,
        'notifications' => $notifications,
        'unreadCount' => $unreadCount
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Method không hợp lệ']);
}
?>
