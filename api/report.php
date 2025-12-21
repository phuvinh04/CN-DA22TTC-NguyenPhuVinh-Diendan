<?php
/**
 * API xử lý báo cáo nội dung vi phạm
 */
session_start();
require_once '../config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Bạn cần đăng nhập']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);
$action = $data['action'] ?? 'create';
$userId = $_SESSION['user_id'];

try {
    // Kiểm tra bảng BAOCAO tồn tại, nếu không thì tạo
    $conn->exec("CREATE TABLE IF NOT EXISTS BAOCAO (
        MABAOCAO VARCHAR(50) NOT NULL,
        MANGUOIDUNG VARCHAR(100) NOT NULL,
        LOAI VARCHAR(20) NOT NULL,
        MAID VARCHAR(50) NOT NULL,
        LYDO TEXT,
        TRANGTHAI VARCHAR(20) DEFAULT 'pending',
        NGAYTAO DATETIME DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (MABAOCAO)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    switch ($action) {
        case 'create':
            $type = $data['type'] ?? ''; // question, answer
            $targetId = $data['target_id'] ?? '';
            $reason = trim($data['reason'] ?? '');
            
            if (empty($type) || empty($targetId) || empty($reason)) {
                echo json_encode(['success' => false, 'message' => 'Vui lòng điền đầy đủ thông tin']);
                exit();
            }
            
            // Kiểm tra đã báo cáo chưa
            $stmt = $conn->prepare("SELECT COUNT(*) FROM BAOCAO WHERE MANGUOIDUNG = ? AND LOAI = ? AND MAID = ?");
            $stmt->execute([$userId, $type, $targetId]);
            if ($stmt->fetchColumn() > 0) {
                echo json_encode(['success' => false, 'message' => 'Bạn đã báo cáo nội dung này rồi']);
                exit();
            }
            
            $reportId = 'BC' . time() . rand(100, 999);
            $stmt = $conn->prepare("INSERT INTO BAOCAO (MABAOCAO, MANGUOIDUNG, LOAI, MAID, LYDO) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$reportId, $userId, $type, $targetId, $reason]);
            
            echo json_encode(['success' => true, 'message' => 'Báo cáo đã được gửi. Cảm ơn bạn!']);
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Action không hợp lệ']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()]);
}
