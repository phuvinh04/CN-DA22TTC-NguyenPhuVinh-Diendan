<?php
/**
 * API xử lý câu hỏi
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
        case 'delete':
            $questionId = $data['question_id'] ?? '';
            
            // Kiểm tra quyền
            $stmt = $conn->prepare("SELECT MANGUOIDUNG FROM DAT WHERE MACAUHOI = ?");
            $stmt->execute([$questionId]);
            $owner = $stmt->fetchColumn();
            
            $isAdmin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
            
            if ($owner !== $userId && !$isAdmin) {
                echo json_encode(['success' => false, 'message' => 'Bạn không có quyền xóa']);
                exit();
            }
            
            // Xóa các bản ghi liên quan
            $conn->prepare("DELETE FROM TRALOI WHERE MACAUHOI = ?")->execute([$questionId]);
            $conn->prepare("DELETE FROM DAT WHERE MACAUHOI = ?")->execute([$questionId]);
            $conn->prepare("DELETE FROM LUOTXEM WHERE MACAUHOI = ?")->execute([$questionId]);
            $conn->prepare("DELETE FROM CAUHOI WHERE MACAUHOI = ?")->execute([$questionId]);
            
            echo json_encode(['success' => true, 'message' => 'Đã xóa câu hỏi']);
            break;
            
        case 'close':
            $questionId = $data['question_id'] ?? '';
            
            $stmt = $conn->prepare("SELECT MANGUOIDUNG FROM DAT WHERE MACAUHOI = ?");
            $stmt->execute([$questionId]);
            $owner = $stmt->fetchColumn();
            
            if ($owner !== $userId) {
                echo json_encode(['success' => false, 'message' => 'Bạn không có quyền']);
                exit();
            }
            
            $conn->prepare("UPDATE CAUHOI SET TRANGTHAI = 'closed' WHERE MACAUHOI = ?")
                ->execute([$questionId]);
            
            echo json_encode(['success' => true, 'message' => 'Đã đóng câu hỏi']);
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Action không hợp lệ']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()]);
}
