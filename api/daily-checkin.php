<?php
/**
 * API Điểm danh hàng ngày
 * Cộng điểm và tính streak khi user điểm danh
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

$userId = $currentUser['id'];

try {
    // Lấy thông tin điểm danh của user
    $stmt = $conn->prepare("SELECT LOGIN_STREAK, LAST_LOGIN_DATE, DIEMDANHGIA FROM NGUOIDUNG WHERE MANGUOIDUNG = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        echo json_encode(['success' => false, 'message' => 'Không tìm thấy user']);
        exit();
    }

    $currentStreak = (int)($user['LOGIN_STREAK'] ?? 0);
    $lastLoginDate = $user['LAST_LOGIN_DATE'];
    $today = date('Y-m-d');

    // Kiểm tra đã điểm danh hôm nay chưa
    if ($lastLoginDate === $today) {
        echo json_encode([
            'success' => false,
            'message' => 'Bạn đã điểm danh hôm nay rồi!',
            'already_checked' => true,
            'streak' => $currentStreak,
            'points' => (int)$user['DIEMDANHGIA']
        ]);
        exit();
    }

    // Tính streak mới
    $yesterday = date('Y-m-d', strtotime('-1 day'));
    $newStreak = 1;

    if ($lastLoginDate === $yesterday) {
        // Điểm danh liên tiếp
        $newStreak = $currentStreak + 1;
    } elseif ($lastLoginDate !== null && $lastLoginDate !== $today) {
        // Bị gián đoạn, reset streak
        $newStreak = 1;
    }

    // Tính điểm thưởng
    $basePoints = 2; // Điểm cơ bản mỗi ngày
    $bonusPoints = 0;
    $bonusMessage = '';

    // Bonus theo streak
    if ($newStreak == 7) {
        $bonusPoints = 15;
        $bonusMessage = '🎉 Bonus 7 ngày liên tiếp!';
    } elseif ($newStreak == 14) {
        $bonusPoints = 25;
        $bonusMessage = '🔥 Bonus 14 ngày liên tiếp!';
    } elseif ($newStreak == 30) {
        $bonusPoints = 50;
        $bonusMessage = '👑 Bonus 30 ngày liên tiếp!';
    } elseif ($newStreak % 7 == 0 && $newStreak > 30) {
        $bonusPoints = 20;
        $bonusMessage = '⭐ Bonus tuần thứ ' . ($newStreak / 7) . '!';
    }

    $totalPoints = $basePoints + $bonusPoints;

    // Cập nhật database
    $conn->beginTransaction();

    $stmt = $conn->prepare("UPDATE NGUOIDUNG SET 
        LOGIN_STREAK = ?, 
        LAST_LOGIN_DATE = ?, 
        DIEMDANHGIA = DIEMDANHGIA + ? 
        WHERE MANGUOIDUNG = ?");
    $stmt->execute([$newStreak, $today, $totalPoints, $userId]);

    // Ghi log điểm (nếu có bảng)
    try {
        $logId = 'PL' . time() . rand(100, 999);
        $desc = "Điểm danh ngày " . date('d/m/Y') . " (Streak: $newStreak)";
        $stmt = $conn->prepare("INSERT INTO POINTS_LOG (ID, MANGUOIDUNG, LOAI, DIEM, MOTA, NGAYTAO) VALUES (?, ?, 'daily_checkin', ?, ?, NOW())");
        $stmt->execute([$logId, $userId, $totalPoints, $desc]);
    } catch (Exception $e) {
        // Bỏ qua nếu bảng chưa tồn tại
    }

    $conn->commit();

    // Kiểm tra và cấp huy hiệu
    $newBadges = checkAndAwardBadges($userId);

    // Lấy điểm mới
    $stmt = $conn->prepare("SELECT DIEMDANHGIA FROM NGUOIDUNG WHERE MANGUOIDUNG = ?");
    $stmt->execute([$userId]);
    $newPoints = $stmt->fetchColumn();

    echo json_encode([
        'success' => true,
        'message' => 'Điểm danh thành công!',
        'streak' => $newStreak,
        'points_earned' => $totalPoints,
        'base_points' => $basePoints,
        'bonus_points' => $bonusPoints,
        'bonus_message' => $bonusMessage,
        'total_points' => (int)$newPoints,
        'new_badges' => array_map(function ($b) {
            return ['icon' => $b['BIEUTUONG'], 'name' => $b['TENHUYHIEU']];
        }, $newBadges)
    ]);
} catch (Exception $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    echo json_encode(['success' => false, 'message' => 'Có lỗi xảy ra: ' . $e->getMessage()]);
}
