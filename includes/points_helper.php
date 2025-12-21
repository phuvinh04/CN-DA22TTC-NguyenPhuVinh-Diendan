<?php
/**
 * Points Helper - Hệ thống điểm nâng cao
 * Quản lý tất cả các hoạt động kiếm điểm
 */

// Định nghĩa các loại điểm
define('POINTS_CONFIG', [
    // Câu hỏi
    'ask_question' => ['points' => 5, 'desc' => 'Đặt câu hỏi mới'],
    'question_approved' => ['points' => 3, 'desc' => 'Câu hỏi được duyệt'],
    'question_featured' => ['points' => 20, 'desc' => 'Câu hỏi được đánh dấu nổi bật'],

    // Câu trả lời
    'answer_question' => ['points' => 10, 'desc' => 'Trả lời câu hỏi'],
    'answer_approved' => ['points' => 5, 'desc' => 'Câu trả lời được duyệt'],
    'answer_accepted' => ['points' => 25, 'desc' => 'Câu trả lời được chấp nhận'],
    'first_answer' => ['points' => 5, 'desc' => 'Người đầu tiên trả lời'],

    // Đánh giá
    'receive_star_1' => ['points' => 1, 'desc' => 'Nhận 1 sao'],
    'receive_star_2' => ['points' => 2, 'desc' => 'Nhận 2 sao'],
    'receive_star_3' => ['points' => 3, 'desc' => 'Nhận 3 sao'],
    'receive_star_4' => ['points' => 4, 'desc' => 'Nhận 4 sao'],
    'receive_star_5' => ['points' => 5, 'desc' => 'Nhận 5 sao'],
    'give_rating' => ['points' => 1, 'desc' => 'Đánh giá người khác'],

    // Tương tác
    'daily_login' => ['points' => 2, 'desc' => 'Đăng nhập hàng ngày'],
    'login_streak_7' => ['points' => 15, 'desc' => 'Đăng nhập 7 ngày liên tiếp'],
    'login_streak_30' => ['points' => 50, 'desc' => 'Đăng nhập 30 ngày liên tiếp'],
    'profile_complete' => ['points' => 10, 'desc' => 'Hoàn thiện hồ sơ'],
    'first_question' => ['points' => 10, 'desc' => 'Đặt câu hỏi đầu tiên'],
    'first_answer' => ['points' => 10, 'desc' => 'Trả lời đầu tiên'],

    // Chất lượng
    'helpful_answer' => ['points' => 15, 'desc' => 'Câu trả lời hữu ích (5+ votes)'],
    'great_question' => ['points' => 10, 'desc' => 'Câu hỏi hay (5+ votes)'],
    'popular_question' => ['points' => 20, 'desc' => 'Câu hỏi phổ biến (100+ views)'],

    // Phạt
    'spam_penalty' => ['points' => -20, 'desc' => 'Vi phạm spam'],
    'report_valid' => ['points' => -10, 'desc' => 'Bị báo cáo hợp lệ'],
    'delete_question' => ['points' => -5, 'desc' => 'Xóa câu hỏi'],
    'delete_answer' => ['points' => -3, 'desc' => 'Xóa câu trả lời'],
]);

/**
 * Cộng/trừ điểm cho user
 * @param string $userId ID người dùng
 * @param string $action Loại hành động
 * @param int|null $customPoints Điểm tùy chỉnh (nếu có)
 * @param string|null $referenceId ID tham chiếu (câu hỏi/trả lời)
 * @return array ['success' => bool, 'points' => int, 'message' => string]
 */
function awardPoints($userId, $action, $customPoints = null, $referenceId = null)
{
    global $conn;

    $config = POINTS_CONFIG[$action] ?? null;
    if (!$config && $customPoints === null) {
        return ['success' => false, 'points' => 0, 'message' => 'Hành động không hợp lệ'];
    }

    $points = $customPoints ?? $config['points'];
    $description = $config['desc'] ?? $action;

    try {
        $conn->beginTransaction();

        // Cập nhật điểm user
        $stmt = $conn->prepare("UPDATE NGUOIDUNG SET DIEMDANHGIA = DIEMDANHGIA + ? WHERE MANGUOIDUNG = ?");
        $stmt->execute([$points, $userId]);

        // Ghi log điểm (nếu có bảng)
        try {
            $logId = 'PL' . time() . rand(100, 999);
            $stmt = $conn->prepare("INSERT INTO POINTS_LOG (ID, MANGUOIDUNG, LOAI, DIEM, MOTA, THAMCHIEU, NGAYTAO) VALUES (?, ?, ?, ?, ?, ?, NOW())");
            $stmt->execute([$logId, $userId, $action, $points, $description, $referenceId]);
        } catch (Exception $e) {
            // Bảng chưa tồn tại, bỏ qua
        }

        $conn->commit();

        // Kiểm tra và cấp huy hiệu
        if (function_exists('checkAndAwardBadges')) {
            checkAndAwardBadges($userId);
        }

        return [
            'success' => true,
            'points' => $points,
            'message' => ($points > 0 ? '+' : '') . $points . ' điểm: ' . $description
        ];
    } catch (Exception $e) {
        $conn->rollBack();
        return ['success' => false, 'points' => 0, 'message' => 'Lỗi cập nhật điểm'];
    }
}

/**
 * Kiểm tra và cộng điểm đăng nhập hàng ngày
 */
function checkDailyLoginBonus($userId)
{
    global $conn;

    try {
        // Kiểm tra đã nhận điểm hôm nay chưa
        $stmt = $conn->prepare("SELECT COUNT(*) FROM POINTS_LOG WHERE MANGUOIDUNG = ? AND LOAI = 'daily_login' AND DATE(NGAYTAO) = CURDATE()");
        $stmt->execute([$userId]);

        if ($stmt->fetchColumn() > 0) {
            return null; // Đã nhận rồi
        }

        // Cộng điểm đăng nhập
        $result = awardPoints($userId, 'daily_login');

        // Kiểm tra streak
        $streak = getLoginStreak($userId);
        if ($streak == 7) {
            awardPoints($userId, 'login_streak_7');
        } elseif ($streak == 30) {
            awardPoints($userId, 'login_streak_30');
        }

        return $result;
    } catch (Exception $e) {
        return null;
    }
}

/**
 * Lấy số ngày đăng nhập liên tiếp
 */
function getLoginStreak($userId)
{
    global $conn;

    try {
        $stmt = $conn->prepare("
            SELECT COUNT(DISTINCT DATE(NGAYTAO)) as streak
            FROM POINTS_LOG 
            WHERE MANGUOIDUNG = ? 
            AND LOAI = 'daily_login'
            AND NGAYTAO >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
            ORDER BY NGAYTAO DESC
        ");
        $stmt->execute([$userId]);
        return (int)$stmt->fetchColumn();
    } catch (Exception $e) {
        return 0;
    }
}

/**
 * Kiểm tra và cộng điểm cho câu hỏi/trả lời chất lượng
 */
function checkQualityBonus($type, $id)
{
    global $conn;

    try {
        if ($type === 'question') {
            // Kiểm tra câu hỏi phổ biến (100+ views)
            $stmt = $conn->prepare("SELECT d.MANGUOIDUNG, ch.LUOTXEM FROM CAUHOI ch JOIN DAT d ON ch.MACAUHOI = d.MACAUHOI WHERE ch.MACAUHOI = ?");
            $stmt->execute([$id]);
            $data = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($data && $data['LUOTXEM'] >= 100) {
                // Kiểm tra đã nhận bonus chưa
                $stmt = $conn->prepare("SELECT COUNT(*) FROM POINTS_LOG WHERE MANGUOIDUNG = ? AND LOAI = 'popular_question' AND THAMCHIEU = ?");
                $stmt->execute([$data['MANGUOIDUNG'], $id]);
                if ($stmt->fetchColumn() == 0) {
                    awardPoints($data['MANGUOIDUNG'], 'popular_question', null, $id);
                }
            }

            // Kiểm tra câu hỏi hay (5+ votes)
            $stmt = $conn->prepare("SELECT COUNT(*) FROM BINHCHONCAUHOI bc JOIN VOTE v ON bc.MAVOTE = v.MAVOTE WHERE bc.MACAUHOI = ? AND v.LOAIVOTE >= 4");
            $stmt->execute([$id]);
            if ($stmt->fetchColumn() >= 5) {
                $stmt = $conn->prepare("SELECT COUNT(*) FROM POINTS_LOG WHERE MANGUOIDUNG = ? AND LOAI = 'great_question' AND THAMCHIEU = ?");
                $stmt->execute([$data['MANGUOIDUNG'], $id]);
                if ($stmt->fetchColumn() == 0) {
                    awardPoints($data['MANGUOIDUNG'], 'great_question', null, $id);
                }
            }
        } elseif ($type === 'answer') {
            // Kiểm tra câu trả lời hữu ích (5+ votes cao)
            $stmt = $conn->prepare("SELECT tl.MANGUOIDUNG FROM TRALOI tl WHERE tl.MACAUTRALOI = ?");
            $stmt->execute([$id]);
            $userId = $stmt->fetchColumn();

            $stmt = $conn->prepare("SELECT COUNT(*) FROM BINHCHONCAUTRALOI bc JOIN VOTE v ON bc.MAVOTE = v.MAVOTE WHERE bc.MACAUTRALOI = ? AND v.LOAIVOTE >= 4");
            $stmt->execute([$id]);
            if ($stmt->fetchColumn() >= 5) {
                $stmt = $conn->prepare("SELECT COUNT(*) FROM POINTS_LOG WHERE MANGUOIDUNG = ? AND LOAI = 'helpful_answer' AND THAMCHIEU = ?");
                $stmt->execute([$userId, $id]);
                if ($stmt->fetchColumn() == 0) {
                    awardPoints($userId, 'helpful_answer', null, $id);
                }
            }
        }
    } catch (Exception $e) {
        // Bỏ qua lỗi
    }
}

/**
 * Lấy lịch sử điểm của user
 */
function getPointsHistory($userId, $limit = 20)
{
    global $conn;

    try {
        $stmt = $conn->prepare("SELECT * FROM POINTS_LOG WHERE MANGUOIDUNG = ? ORDER BY NGAYTAO DESC LIMIT ?");
        $stmt->execute([$userId, $limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        return [];
    }
}

/**
 * Lấy tổng điểm kiếm được trong tuần/tháng
 */
function getPointsEarned($userId, $period = 'week')
{
    global $conn;

    try {
        $interval = $period === 'month' ? 30 : 7;
        $stmt = $conn->prepare("SELECT COALESCE(SUM(DIEM), 0) FROM POINTS_LOG WHERE MANGUOIDUNG = ? AND DIEM > 0 AND NGAYTAO >= DATE_SUB(NOW(), INTERVAL ? DAY)");
        $stmt->execute([$userId, $interval]);
        return (int)$stmt->fetchColumn();
    } catch (Exception $e) {
        return 0;
    }
}

/**
 * Kiểm tra user đã hoàn thiện profile chưa
 */
function checkProfileComplete($userId)
{
    global $conn;

    try {
        $stmt = $conn->prepare("SELECT HOTEN, EMAIL, TIEUSU, ANHDAIDIEN FROM NGUOIDUNG WHERE MANGUOIDUNG = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && !empty($user['HOTEN']) && !empty($user['EMAIL']) && !empty($user['TIEUSU']) && !empty($user['ANHDAIDIEN'])) {
            // Kiểm tra đã nhận bonus chưa
            $stmt = $conn->prepare("SELECT COUNT(*) FROM POINTS_LOG WHERE MANGUOIDUNG = ? AND LOAI = 'profile_complete'");
            $stmt->execute([$userId]);
            if ($stmt->fetchColumn() == 0) {
                return awardPoints($userId, 'profile_complete');
            }
        }
    } catch (Exception $e) {
    }
    return null;
}
