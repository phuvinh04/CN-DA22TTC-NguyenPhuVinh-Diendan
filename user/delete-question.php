<?php
/**
 * Xóa câu hỏi của người dùng
 */
require_once '../config/database.php';
require_once '../config/session.php';
requireLogin();

$currentUser = getCurrentUser();
$questionId = $_GET['id'] ?? '';

if (empty($questionId)) {
    header('Location: my-questions.php');
    exit();
}

// Kiểm tra quyền xóa
$stmt = $conn->prepare("SELECT MANGUOIDUNG FROM DAT WHERE MACAUHOI = ?");
$stmt->execute([$questionId]);
$owner = $stmt->fetchColumn();

if ($owner !== $currentUser['id']) {
    header('Location: my-questions.php?error=permission');
    exit();
}

// Kiểm tra câu hỏi có câu trả lời chưa
$stmt = $conn->prepare("SELECT COUNT(*) FROM TRALOI WHERE MACAUHOI = ?");
$stmt->execute([$questionId]);
$answerCount = $stmt->fetchColumn();

if ($answerCount > 0) {
    header('Location: my-questions.php?error=has_answers');
    exit();
}

try {
    $conn->beginTransaction();
    
    // Xóa vote câu hỏi
    $conn->prepare("DELETE v FROM VOTE v JOIN BINHCHONCAUHOI bc ON v.MAVOTE = bc.MAVOTE WHERE bc.MACAUHOI = ?")->execute([$questionId]);
    $conn->prepare("DELETE FROM BINHCHONCAUHOI WHERE MACAUHOI = ?")->execute([$questionId]);
    
    // Xóa lượt xem
    $conn->prepare("DELETE FROM LUOTXEM WHERE MACAUHOI = ?")->execute([$questionId]);
    
    // Xóa người đặt câu hỏi
    $conn->prepare("DELETE FROM DAT WHERE MACAUHOI = ?")->execute([$questionId]);
    
    // Xóa câu hỏi
    $conn->prepare("DELETE FROM CAUHOI WHERE MACAUHOI = ?")->execute([$questionId]);
    
    // Trừ điểm
    $conn->prepare("UPDATE NGUOIDUNG SET DIEMDANHGIA = GREATEST(0, DIEMDANHGIA - 5) WHERE MANGUOIDUNG = ?")->execute([$currentUser['id']]);
    
    $conn->commit();
    header('Location: my-questions.php?msg=deleted');
} catch (Exception $e) {
    $conn->rollBack();
    header('Location: my-questions.php?error=delete_failed');
}
exit();
