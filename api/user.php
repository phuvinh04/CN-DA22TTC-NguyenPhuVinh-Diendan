<?php
/**
 * API xử lý người dùng
 */
session_start();
require_once '../config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Bạn cần đăng nhập']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);
$action = $data['action'] ?? '';
$userId = $_SESSION['user_id'];

try {
    switch ($action) {
        case 'update_last_activity':
            $conn->prepare("UPDATE NGUOIDUNG SET LANHOATDONGCUOI = NOW() WHERE MANGUOIDUNG = ?")
                ->execute([$userId]);
            echo json_encode(['success' => true]);
            break;
            
        case 'get_stats':
            $targetUserId = $data['user_id'] ?? $userId;
            $stmt = $conn->prepare("
                SELECT 
                    (SELECT COUNT(*) FROM DAT WHERE MANGUOIDUNG = ?) as questions,
                    (SELECT COUNT(*) FROM TRALOI WHERE MANGUOIDUNG = ?) as answers,
                    nd.DIEMDANHGIA as points
                FROM NGUOIDUNG nd WHERE nd.MANGUOIDUNG = ?
            ");
            $stmt->execute([$targetUserId, $targetUserId, $targetUserId]);
            $stats = $stmt->fetch(PDO::FETCH_ASSOC);
            echo json_encode(['success' => true, 'stats' => $stats]);
            break;
            
        case 'check_username':
            $username = $data['username'] ?? '';
            $stmt = $conn->prepare("SELECT COUNT(*) FROM NGUOIDUNG WHERE TENDANGNHAP = ?");
            $stmt->execute([$username]);
            $exists = $stmt->fetchColumn() > 0;
            echo json_encode(['success' => true, 'exists' => $exists]);
            break;
            
        case 'check_email':
            $email = $data['email'] ?? '';
            $stmt = $conn->prepare("SELECT COUNT(*) FROM NGUOIDUNG WHERE EMAIL = ?");
            $stmt->execute([$email]);
            $exists = $stmt->fetchColumn() > 0;
            echo json_encode(['success' => true, 'exists' => $exists]);
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Action không hợp lệ']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()]);
}
