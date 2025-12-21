<?php
/**
 * Badge Helper - Quản lý huy hiệu người dùng
 */

/**
 * Lấy tất cả huy hiệu của user
 */
function getUserBadges($userId) {
    global $conn;
    
    $sql = "SELECT h.* FROM HUYHIEU h 
            INNER JOIN NHAN n ON h.MAHUYHIEU = n.MAHUYHIEU 
            WHERE n.MANGUOIDUNG = ?
            ORDER BY h.NGUONGTIEUCHI DESC";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute([$userId]);
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Kiểm tra user đã có huy hiệu chưa
 */
function userHasBadge($userId, $badgeId) {
    global $conn;
    
    $sql = "SELECT 1 FROM NHAN WHERE MANGUOIDUNG = ? AND MAHUYHIEU = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$userId, $badgeId]);
    
    return $stmt->rowCount() > 0;
}

/**
 * Cấp huy hiệu cho user
 */
function awardBadge($userId, $badgeId) {
    global $conn;
    
    if (userHasBadge($userId, $badgeId)) {
        return false;
    }
    
    try {
        $sql = "INSERT INTO NHAN (MANGUOIDUNG, MAHUYHIEU) VALUES (?, ?)";
        $stmt = $conn->prepare($sql);
        
        if ($stmt->execute([$userId, $badgeId])) {
            // Tạo thông báo cho user
            $badge = getBadgeById($badgeId);
            if ($badge) {
                try {
                    $notifId = 'TB' . time() . rand(100, 999);
                    $title = "Chúc mừng! Bạn đã nhận huy hiệu {$badge['BIEUTUONG']} {$badge['TENHUYHIEU']}";
                    $conn->prepare("INSERT INTO THONGBAO (MATHONGBAO, MANGUOIDUNG, LOAI, TIEUDE, LINK, DADOC, NGAYTAO) VALUES (?, ?, 'badge', ?, 'user/dashboard.php', 0, NOW())")
                        ->execute([$notifId, $userId, $title]);
                } catch (Exception $e) {}
            }
            return true;
        }
    } catch (Exception $e) {}
    
    return false;
}

/**
 * Lấy thông tin huy hiệu theo ID
 */
function getBadgeById($badgeId) {
    global $conn;
    
    $sql = "SELECT * FROM HUYHIEU WHERE MAHUYHIEU = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$badgeId]);
    
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

/**
 * Kiểm tra và cấp huy hiệu tự động dựa trên hoạt động
 */
function checkAndAwardBadges($userId)
{
    global $conn;

    try {
        // Lấy thống kê của user
        $stats = getUserStatsForBadge($userId);

        // Lấy tất cả huy hiệu
        $sql = "SELECT * FROM HUYHIEU";
        $result = $conn->query($sql);
        $badges = $result->fetchAll(PDO::FETCH_ASSOC);

        $awarded = [];

        foreach ($badges as $badge) {
            $shouldAward = false;

            switch ($badge['LOAITIEUCHI']) {
                case 'ngaythamgia':
                    $shouldAward = true;
                    break;

                case 'cautraloi':
                    if ($stats['answers'] >= $badge['NGUONGTIEUCHI']) {
                        $shouldAward = true;
                    }
                    break;

                case 'cauhoi':
                    if ($stats['questions'] >= $badge['NGUONGTIEUCHI']) {
                        $shouldAward = true;
                    }
                    break;

                case 'vote':
                    if ($stats['totalVotes'] >= $badge['NGUONGTIEUCHI']) {
                        $shouldAward = true;
                    }
                    break;

                case 'diem':
                    if ($stats['points'] >= $badge['NGUONGTIEUCHI']) {
                        $shouldAward = true;
                    }
                    break;

                case 'streak':
                    if ($stats['loginStreak'] >= $badge['NGUONGTIEUCHI']) {
                        $shouldAward = true;
                    }
                    break;

                case 'accepted':
                    if ($stats['acceptedAnswers'] >= $badge['NGUONGTIEUCHI']) {
                        $shouldAward = true;
                    }
                    break;
            }

            if ($shouldAward && !userHasBadge($userId, $badge['MAHUYHIEU'])) {
                if (awardBadge($userId, $badge['MAHUYHIEU'])) {
                    $awarded[] = $badge;
                }
            }
        }

        return $awarded;
    } catch (Exception $e) {
        return [];
    }
}

/**
 * Lấy thống kê của user cho badge
 */
function getUserStatsForBadge($userId)
{
    global $conn;

    // Đếm câu hỏi (qua bảng DAT)
    $stmt = $conn->prepare("SELECT COUNT(*) FROM DAT WHERE MANGUOIDUNG = ?");
    $stmt->execute([$userId]);
    $questions = $stmt->fetchColumn();

    // Đếm câu trả lời (qua bảng TRALOI)
    $stmt = $conn->prepare("SELECT COUNT(*) FROM TRALOI WHERE MANGUOIDUNG = ?");
    $stmt->execute([$userId]);
    $answers = $stmt->fetchColumn();

    // Tổng điểm đánh giá (điểm của user)
    $stmt = $conn->prepare("SELECT DIEMDANHGIA, LOGIN_STREAK FROM NGUOIDUNG WHERE MANGUOIDUNG = ?");
    $stmt->execute([$userId]);
    $userData = $stmt->fetch(PDO::FETCH_ASSOC);
    $points = $userData['DIEMDANHGIA'] ?? 0;
    $loginStreak = $userData['LOGIN_STREAK'] ?? 0;

    // Đếm tổng số vote TỐT nhận được (chỉ tính 4-5 sao)
    $totalVotes = 0;
    try {
        // Vote tốt từ câu hỏi (4-5 sao)
        $stmt = $conn->prepare("
            SELECT COUNT(*) FROM VOTE v 
            JOIN BINHCHONCAUHOI bc ON v.MAVOTE = bc.MAVOTE 
            JOIN DAT d ON bc.MACAUHOI = d.MACAUHOI 
            WHERE d.MANGUOIDUNG = ? AND v.LOAIVOTE >= 4
        ");
        $stmt->execute([$userId]);
        $totalVotes += (int)$stmt->fetchColumn();

        // Vote tốt từ câu trả lời (4-5 sao)
        $stmt = $conn->prepare("
            SELECT COUNT(*) FROM VOTE v 
            JOIN BINHCHONCAUTRALOI bc ON v.MAVOTE = bc.MAVOTE 
            JOIN TRALOI tl ON bc.MACAUTRALOI = tl.MACAUTRALOI 
            WHERE tl.MANGUOIDUNG = ? AND v.LOAIVOTE >= 4
        ");
        $stmt->execute([$userId]);
        $totalVotes += (int)$stmt->fetchColumn();
    } catch (Exception $e) {
        $totalVotes = 0;
    }

    // Đếm câu trả lời được chấp nhận (nếu có cột)
    $acceptedAnswers = 0;
    try {
        $stmt = $conn->prepare("SELECT COUNT(*) FROM TRALOI WHERE MANGUOIDUNG = ? AND DUOCCHAPNHAN = 1");
        $stmt->execute([$userId]);
        $acceptedAnswers = (int)$stmt->fetchColumn();
    } catch (Exception $e) {
        $acceptedAnswers = 0;
    }

    return [
        'questions' => (int)$questions,
        'answers' => (int)$answers,
        'totalVotes' => (int)$totalVotes,
        'points' => (int)$points,
        'loginStreak' => (int)$loginStreak,
        'acceptedAnswers' => (int)$acceptedAnswers
    ];
}

/**
 * Hiển thị huy hiệu dạng HTML
 */
function displayBadges($badges, $limit = null) {
    if (empty($badges)) {
        return '<span class="text-muted small">Chưa có huy hiệu</span>';
    }
    
    if ($limit) {
        $badges = array_slice($badges, 0, $limit);
    }
    
    $html = '';
    foreach ($badges as $badge) {
        $html .= sprintf(
            '<span class="badge-item" title="%s: %s" data-bs-toggle="tooltip">%s</span>',
            htmlspecialchars($badge['TENHUYHIEU']),
            htmlspecialchars($badge['MOTA']),
            $badge['BIEUTUONG']
        );
    }
    
    return $html;
}

/**
 * Hiển thị huy hiệu với tên
 */
function displayBadgesWithName($badges) {
    if (empty($badges)) {
        return '<p class="text-muted">Chưa có huy hiệu nào</p>';
    }
    
    $html = '<div class="badges-list">';
    foreach ($badges as $badge) {
        $html .= sprintf(
            '<div class="badge-card">
                <span class="badge-icon">%s</span>
                <div class="badge-info">
                    <strong>%s</strong>
                    <small class="text-muted d-block">%s</small>
                </div>
            </div>',
            $badge['BIEUTUONG'],
            htmlspecialchars($badge['TENHUYHIEU']),
            htmlspecialchars($badge['MOTA'])
        );
    }
    $html .= '</div>';
    
    return $html;
}


/**
 * Lấy khung avatar đã chọn của user (hoặc huy hiệu cao nhất nếu chưa chọn)
 */
function getUserHighestBadge($userId) {
    global $conn;
    
    try {
        // Kiểm tra cột KHUNG_AVATAR có tồn tại không
        $hasFrameCol = false;
        try {
            $checkCol = $conn->query("SHOW COLUMNS FROM NGUOIDUNG LIKE 'KHUNG_AVATAR'");
            $hasFrameCol = $checkCol->rowCount() > 0;
        } catch (Exception $e) {}
        
        if ($hasFrameCol) {
            // Kiểm tra user đã chọn khung chưa
            $stmt = $conn->prepare("SELECT KHUNG_AVATAR FROM NGUOIDUNG WHERE MANGUOIDUNG = ?");
            $stmt->execute([$userId]);
            $selectedFrame = $stmt->fetchColumn();
            
            if ($selectedFrame) {
                // Lấy thông tin huy hiệu đã chọn
                $stmt = $conn->prepare("SELECT h.* FROM HUYHIEU h WHERE h.MAHUYHIEU = ?");
                $stmt->execute([$selectedFrame]);
                $badge = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($badge) {
                    return $badge;
                }
            }
        }
        
        // Nếu chưa chọn, lấy huy hiệu cao nhất
        $sql = "SELECT h.* FROM HUYHIEU h 
                INNER JOIN NHAN n ON h.MAHUYHIEU = n.MAHUYHIEU 
                WHERE n.MANGUOIDUNG = ?
                ORDER BY h.CAPDO DESC, h.NGUONGTIEUCHI DESC
                LIMIT 1";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute([$userId]);
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        return null;
    }
}

/**
 * Render avatar với khung màu theo huy hiệu
 * @param string $avatarUrl URL ảnh avatar
 * @param string $userId ID người dùng
 * @param string $size 'sm', 'normal', 'lg'
 * @param bool $showBadge Hiển thị icon huy hiệu góc avatar
 */
function renderAvatarWithFrame($avatarUrl, $userId, $size = 'normal', $showBadge = true) {
    $badge = getUserHighestBadge($userId);
    
    $sizeClass = '';
    $badgeSizeClass = '';
    $sizeStyle = '';
    if ($size === 'sm') {
        $sizeClass = 'user-avatar-sm';
        $sizeStyle = 'width:28px;height:28px;';
    } elseif ($size === 'lg') {
        $sizeClass = 'user-avatar-lg';
        $badgeSizeClass = 'avatar-badge-lg';
        $sizeStyle = 'width:80px;height:80px;';
    } else {
        $sizeClass = 'user-avatar';
        $sizeStyle = 'width:40px;height:40px;';
    }
    
    // Màu khung mặc định (xám nhạt)
    $frameColor = '#cbd5e1';
    $level = 0;
    $icon = '';
    $title = '';
    
    if ($badge && !empty($badge['MAUKHUNG'])) {
        $frameColor = htmlspecialchars($badge['MAUKHUNG']);
        $level = (int)($badge['CAPDO'] ?? 0);
        $icon = $badge['BIEUTUONG'] ?? '';
        $title = htmlspecialchars($badge['TENHUYHIEU'] ?? '');
    }
    
    $glowStyle = '';
    if ($level >= 5) {
        $glowStyle = "box-shadow: 0 0 8px {$frameColor}, 0 0 15px {$frameColor};";
    }
    
    $html = '<span class="avatar-frame d-inline-block position-relative" data-level="' . $level . '" style="--frame-color: ' . $frameColor . ';">';
    $html .= '<img src="' . htmlspecialchars($avatarUrl) . '" alt="" class="' . $sizeClass . '" style="border-radius:50%;border:3px solid ' . $frameColor . ';object-fit:cover;' . $sizeStyle . $glowStyle . '"' . ($title ? ' title="' . $title . '"' : '') . '>';
    
    if ($showBadge && $size !== 'sm' && !empty($icon)) {
        $html .= '<span class="avatar-badge ' . $badgeSizeClass . ' position-absolute" style="bottom:-2px;right:-2px;background:#fff;border-radius:50%;padding:2px;font-size:' . ($size === 'lg' ? '16px' : '12px') . ';box-shadow:0 1px 3px rgba(0,0,0,0.2);" title="' . $title . '">' . $icon . '</span>';
    }
    $html .= '</span>';
    
    return $html;
}
