<?php
/**
 * API xử lý câu trả lời
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
        case 'accept':
            // Chấp nhận câu trả lời
            $answerId = $data['answer_id'] ?? '';
            $questionId = $data['question_id'] ?? '';
            
            // Kiểm tra quyền (chỉ người đặt câu hỏi mới được chấp nhận)
            $stmt = $conn->prepare("SELECT MANGUOIDUNG FROM DAT WHERE MACAUHOI = ?");
            $stmt->execute([$questionId]);
            $owner = $stmt->fetchColumn();
            
            if ($owner !== $userId) {
                echo json_encode(['success' => false, 'message' => 'Bạn không có quyền thực hiện']);
                exit();
            }
            
            // Cập nhật trạng thái câu hỏi
            $conn->prepare("UPDATE CAUHOI SET TRANGTHAI = 'closed' WHERE MACAUHOI = ?")
                ->execute([$questionId]);
            
            // Cộng điểm cho người trả lời
            $stmt = $conn->prepare("SELECT MANGUOIDUNG FROM TRALOI WHERE MACAUTRALOI = ?");
            $stmt->execute([$answerId]);
            $answerOwner = $stmt->fetchColumn();
            
            if ($answerOwner) {
                $conn->prepare("UPDATE NGUOIDUNG SET DIEMDANHGIA = DIEMDANHGIA + 15 WHERE MANGUOIDUNG = ?")
                    ->execute([$answerOwner]);
            }
            
            echo json_encode(['success' => true, 'message' => 'Đã chấp nhận câu trả lời']);
            break;
            
        case 'delete':
            // Xóa câu trả lời (chỉ chủ sở hữu hoặc admin)
            $answerId = $data['answer_id'] ?? '';
            
            $stmt = $conn->prepare("SELECT MANGUOIDUNG FROM TRALOI WHERE MACAUTRALOI = ?");
            $stmt->execute([$answerId]);
            $owner = $stmt->fetchColumn();
            
            $isAdmin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
            
            if ($owner !== $userId && !$isAdmin) {
                echo json_encode(['success' => false, 'message' => 'Bạn không có quyền xóa']);
                exit();
            }
            
            $conn->prepare("DELETE FROM TRALOI WHERE MACAUTRALOI = ?")->execute([$answerId]);
            echo json_encode(['success' => true, 'message' => 'Đã xóa câu trả lời']);
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Action không hợp lệ']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()]);
}
