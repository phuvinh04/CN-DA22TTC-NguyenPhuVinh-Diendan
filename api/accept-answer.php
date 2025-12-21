<?php
/**
 * API Chấp nhận câu trả lời
 * Chỉ người đặt câu hỏi mới có quyền chấp nhận
 */

require_once '../config/database.php';
require_once '../config/session.php';
require_once '../includes/badge_helper.php';

header('Content-Type: application/json');

$currentUser = getCurrentUser();
if (!$currentUser) {
    echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập']);
    exit();
}

// Lấy dữ liệu
$data = json_decode(file_get_contents('php://input'), true);
$answerId = $data['answer_id'] ?? '';
$questionId = $data['question_id'] ?? '';

if (empty($answerId) || empty($questionId)) {
    echo json_encode(['success' => false, 'message' => 'Thiếu thông tin']);
    exit();
}

try {
    // Kiểm tra người dùng có phải chủ câu hỏi không
    $stmt = $conn->prepare("SELECT MANGUOIDUNG FROM DAT WHERE MACAUHOI = ?");
    $stmt->execute([$questionId]);
    $questionOwner = $stmt->fetchColumn();

    if ($questionOwner !== $currentUser['id']) {
        echo json_encode(['success' => false, 'message' => 'Chỉ người đặt câu hỏi mới có thể chấp nhận câu trả lời']);
        exit();
    }

    // Kiểm tra câu trả lời có thuộc câu hỏi này không
    $stmt = $conn->prepare("SELECT MANGUOIDUNG FROM TRALOI WHERE MACAUTRALOI = ? AND MACAUHOI = ?");
    $stmt->execute([$answerId, $questionId]);
    $answerOwner = $stmt->fetchColumn();

    if (!$answerOwner) {
        echo json_encode(['success' => false, 'message' => 'Câu trả lời không tồn tại']);
        exit();
    }

    // Không cho chấp nhận câu trả lời của chính mình
    if ($answerOwner === $currentUser['id']) {
        echo json_encode(['success' => false, 'message' => 'Không thể chấp nhận câu trả lời của chính mình']);
        exit();
    }

    $conn->beginTransaction();

    // Kiểm tra câu trả lời này đã từng được cộng điểm accept chưa
    $stmt = $conn->prepare("SELECT COUNT(*) FROM POINTS_LOG WHERE LOAI = 'answer_accepted' AND THAMCHIEU = ?");
    $stmt->execute([$answerId]);
    $alreadyRewarded = $stmt->fetchColumn() > 0;

    // Bỏ chấp nhận câu trả lời cũ (nếu có)
    $stmt = $conn->prepare("UPDATE TRALOI SET DUOCCHAPNHAN = 0 WHERE MACAUHOI = ?");
    $stmt->execute([$questionId]);

    // Chấp nhận câu trả lời mới
    $stmt = $conn->prepare("UPDATE TRALOI SET DUOCCHAPNHAN = 1 WHERE MACAUTRALOI = ?");
    $stmt->execute([$answerId]);

    // Cập nhật câu hỏi
    $stmt = $conn->prepare("UPDATE CAUHOI SET CAUTRALOI_CHAPNHAN = ? WHERE MACAUHOI = ?");
    $stmt->execute([$answerId, $questionId]);

    // Chỉ cộng điểm nếu câu trả lời này chưa từng được cộng điểm accept
    if (!$alreadyRewarded) {
        // Cộng điểm cho người trả lời (+25 điểm)
        $stmt = $conn->prepare("UPDATE NGUOIDUNG SET DIEMDANHGIA = DIEMDANHGIA + 25 WHERE MANGUOIDUNG = ?");
        $stmt->execute([$answerOwner]);

        // Ghi log điểm
        try {
            $logId = 'PL' . time() . rand(100, 999);
            $stmt = $conn->prepare("INSERT INTO POINTS_LOG (ID, MANGUOIDUNG, LOAI, DIEM, MOTA, THAMCHIEU, NGAYTAO) VALUES (?, ?, 'answer_accepted', 25, 'Câu trả lời được chấp nhận', ?, NOW())");
            $stmt->execute([$logId, $answerOwner, $answerId]);
        } catch (Exception $e) {}

        // Gửi thông báo cho người trả lời
        try {
            $notifId = 'TB' . time() . rand(100, 999);
            $notifTitle = $currentUser['fullname'] . ' đã chấp nhận câu trả lời của bạn! (+25 điểm)';
            $notifLink = 'question-detail.php?id=' . $questionId . '#answer-' . $answerId;
            $stmt = $conn->prepare("INSERT INTO THONGBAO (MATHONGBAO, MANGUOIDUNG, LOAI, TIEUDE, LINK, DADOC, NGAYTAO) VALUES (?, ?, 'accepted', ?, ?, 0, NOW())");
            $stmt->execute([$notifId, $answerOwner, $notifTitle, $notifLink]);
        } catch (Exception $e) {}
    }

    $conn->commit();

    // Kiểm tra và cấp huy hiệu
    checkAndAwardBadges($answerOwner);

    if ($alreadyRewarded) {
        echo json_encode([
            'success' => true,
            'message' => 'Đã chấp nhận câu trả lời!'
        ]);
    } else {
        echo json_encode([
            'success' => true,
            'message' => 'Đã chấp nhận câu trả lời! Người trả lời nhận +25 điểm.'
        ]);
    }

} catch (Exception $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    echo json_encode(['success' => false, 'message' => 'Có lỗi xảy ra: ' . $e->getMessage()]);
}
