<?php
/**
 * API lấy thông tin Streak của user
 */

require_once '../config/database.php';
require_once '../config/session.php';

header('Content-Type: application/json');

$currentUser = getCurrentUser();
if (!$currentUser) {
    echo json_encode(['success' => false, 'message' => 'Chưa đăng nhập']);
    exit();
}

$userId = $currentUser['id'];

try {
    $stmt = $conn->prepare("SELECT LOGIN_STREAK, LAST_LOGIN_DATE, DIEMDANHGIA FROM NGUOIDUNG WHERE MANGUOIDUNG = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    $streak = (int)($user['LOGIN_STREAK'] ?? 0);
    $lastLogin = $user['LAST_LOGIN_DATE'];
    $today = date('Y-m-d');
    $yesterday = date('Y-m-d', strtotime('-1 day'));

    // Kiểm tra streak có còn hiệu lực không
    $streakValid = ($lastLogin === $today || $lastLogin === $yesterday);
    if (!$streakValid && $lastLogin !== null) {
        $streak = 0; // Streak đã bị reset
    }

    // Kiểm tra đã điểm danh hôm nay chưa
    $checkedToday = ($lastLogin === $today);

    // Tính ngày tiếp theo để nhận bonus
    $nextBonus = 0;
    $nextBonusPoints = 0;
    if ($streak < 7) {
        $nextBonus = 7 - $streak;
        $nextBonusPoints = 15;
    } elseif ($streak < 14) {
        $nextBonus = 14 - $streak;
        $nextBonusPoints = 25;
    } elseif ($streak < 30) {
        $nextBonus = 30 - $streak;
        $nextBonusPoints = 50;
    } else {
        $nextBonus = 7 - ($streak % 7);
        if ($nextBonus == 0) $nextBonus = 7;
        $nextBonusPoints = 20;
    }

    echo json_encode([
        'success' => true,
        'streak' => $streak,
        'checked_today' => $checkedToday,
        'last_login' => $lastLogin,
        'total_points' => (int)$user['DIEMDANHGIA'],
        'next_bonus_in' => $nextBonus,
        'next_bonus_points' => $nextBonusPoints
    ]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()]);
}
