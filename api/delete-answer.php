<?php
/**
 * API xóa câu trả lời
 */
session_start();
require_once '../config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Bạn cần đăng nhập']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);
$answerId = $data['answer_id'] ?? '';
$userId = $_SESSION['user_id'];
$isAdmin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';

if (empty($answerId)) {
    echo json_encode(['success' => false, 'message' => 'ID không hợp lệ']);
    exit();
}

try {
    // Kiểm tra quyền xóa
    $stmt = $conn->prepare("SELECT MANGUOIDUNG FROM TRALOI WHERE MACAUTRALOI = ?");
    $stmt->execute([$answerId]);
    $owner = $stmt->fetchColumn();
    
    if (!$owner) {
        echo json_encode(['success' => false, 'message' => 'Câu trả lời không tồn tại']);
        exit();
    }
    
    if ($owner !== $userId && !$isAdmin) {
        echo json_encode(['success' => false, 'message' => 'Bạn không có quyền xóa']);
        exit();
    }
    
    $conn->beginTransaction();
    
    // Xóa vote của câu trả lời
    $conn->prepare("DELETE v FROM VOTE v JOIN BINHCHONCAUTRALOI bc ON v.MAVOTE = bc.MAVOTE WHERE bc.MACAUTRALOI = ?")->execute([$answerId]);
    $conn->prepare("DELETE FROM BINHCHONCAUTRALOI WHERE MACAUTRALOI = ?")->execute([$answerId]);
    
    // Xóa câu trả lời
    $conn->prepare("DELETE FROM TRALOI WHERE MACAUTRALOI = ?")->execute([$answerId]);
    
    // Trừ điểm người trả lời
    $conn->prepare("UPDATE NGUOIDUNG SET DIEMDANHGIA = GREATEST(0, DIEMDANHGIA - 10) WHERE MANGUOIDUNG = ?")->execute([$owner]);
    
    $conn->commit();
    
    echo json_encode(['success' => true, 'message' => 'Đã xóa câu trả lời']);
} catch (Exception $e) {
    $conn->rollBack();
    echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()]);
}
