<?php
$pageTitle = 'Điểm của tôi';
require_once '../config/database.php';
require_once '../config/session.php';
require_once '../includes/badge_helper.php';
require_once '../includes/points_helper.php';

requireLogin();
$currentUser = getCurrentUser();
$userId = $currentUser['id'];

// Lấy thông tin user đầy đủ từ database
$stmt = $conn->prepare("SELECT * FROM NGUOIDUNG WHERE MANGUOIDUNG = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    header('Location: ../login.php');
    exit();
}

// Fallback nếu không có cột mới
$user['LOGIN_STREAK'] = $user['LOGIN_STREAK'] ?? 0;
$user['LAST_LOGIN_DATE'] = $user['LAST_LOGIN_DATE'] ?? null;

// Lấy điểm từ database (đáng tin cậy nhất)
$totalPoints = (int)($user['DIEMDANHGIA'] ?? 0);

// Lấy thống kê
$stats = getUserStatsForBadge($userId);

// Lấy huy hiệu đã có
$userBadges = getUserBadges($userId);
$userBadgeIds = array_column($userBadges, 'MAHUYHIEU');

// Lấy tất cả huy hiệu
$allBadges = $conn->query("SELECT * FROM HUYHIEU ORDER BY CAPDO ASC, NGUONGTIEUCHI ASC")->fetchAll(PDO::FETCH_ASSOC);

// Lấy lịch sử điểm
$pointsHistory = [];
try {
    $stmt = $conn->prepare("SELECT * FROM POINTS_LOG WHERE MANGUOIDUNG = ? ORDER BY NGAYTAO DESC LIMIT 30");
    $stmt->execute([$userId]);
    $pointsHistory = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {}

// Tính điểm tuần này và tháng này
$weeklyPoints = getPointsEarned($userId, 'week');
$monthlyPoints = getPointsEarned($userId, 'month');

// Lấy thứ hạng
$stmt = $conn->prepare("SELECT COUNT(*) + 1 FROM NGUOIDUNG WHERE DIEMDANHGIA > (SELECT DIEMDANHGIA FROM NGUOIDUNG WHERE MANGUOIDUNG = ?)");
$stmt->execute([$userId]);
$rank = $stmt->fetchColumn();

// Streak info
$streak = $user['LOGIN_STREAK'] ?? 0;
$lastLogin = $user['LAST_LOGIN_DATE'] ?? null;

// Kiểm tra streak có còn hiệu lực không
$today = date('Y-m-d');
$yesterday = date('Y-m-d', strtotime('-1 day'));
$streakValid = ($lastLogin === $today || $lastLogin === $yesterday);
if (!$streakValid && $lastLogin !== null) {
    $streak = 0; // Streak đã bị reset vì không điểm danh liên tiếp
}

require_once '../includes/header.php';
?>

<main class="py-4">
    <div class="container">
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="../index.php">Trang chủ</a></li>
                <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                <li class="breadcrumb-item active">Điểm của tôi</li>
            </ol>
        </nav>

        <!-- Header Stats -->
        <div class="row g-3 mb-4">
            <div class="col-6 col-lg-3">
                <div class="stats-card-modern bg-gradient-primary">
                    <div class="stats-number"><?php echo number_format($totalPoints); ?></div>
                    <div class="stats-label">Tổng điểm</div>
                    <i class="bi bi-trophy stats-icon"></i>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="stats-card-modern bg-gradient-success">
                    <div class="stats-number">#<?php echo $rank; ?></div>
                    <div class="stats-label">Xếp hạng</div>
                    <i class="bi bi-bar-chart stats-icon"></i>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="stats-card-modern bg-gradient-warning">
                    <div class="stats-number"><?php echo $streak; ?></div>
                    <div class="stats-label">Ngày streak</div>
                    <i class="bi bi-fire stats-icon"></i>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="stats-card-modern bg-gradient-info">
                    <div class="stats-number"><?php echo count($userBadges); ?>/<?php echo count($allBadges); ?></div>
                    <div class="stats-label">Huy hiệu</div>
                    <i class="bi bi-award stats-icon"></i>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <!-- Left Column -->
            <div class="col-lg-8">
                <!-- Thống kê chi tiết -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-graph-up me-2"></i>Thống kê hoạt động</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-4">
                            <div class="col-md-6">
                                <div class="stat-item-detail">
                                    <div class="stat-icon bg-primary-light">
                                        <i class="bi bi-question-circle text-primary"></i>
                                    </div>
                                    <div class="stat-info">
                                        <div class="stat-value"><?php echo $stats['questions']; ?></div>
                                        <div class="stat-label">Câu hỏi đã đặt</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="stat-item-detail">
                                    <div class="stat-icon bg-success-light">
                                        <i class="bi bi-chat-dots text-success"></i>
                                    </div>
                                    <div class="stat-info">
                                        <div class="stat-value"><?php echo $stats['answers']; ?></div>
                                        <div class="stat-label">Câu trả lời</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="stat-item-detail">
                                    <div class="stat-icon bg-warning-light">
                                        <i class="bi bi-star text-warning"></i>
                                    </div>
                                    <div class="stat-info">
                                        <div class="stat-value"><?php echo $stats['totalVotes']; ?></div>
                                        <div class="stat-label">Đánh giá tốt nhận được</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="stat-item-detail">
                                    <div class="stat-icon bg-info-light">
                                        <i class="bi bi-check-circle text-info"></i>
                                    </div>
                                    <div class="stat-info">
                                        <div class="stat-value"><?php echo $stats['acceptedAnswers']; ?></div>
                                        <div class="stat-label">Câu TL được chấp nhận</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Điểm tuần/tháng -->
                        <hr class="my-4">
                        <div class="row text-center">
                            <div class="col-6">
                                <div class="points-earned">
                                    <span class="points-value text-success">+<?php echo $weeklyPoints; ?></span>
                                    <span class="points-period">điểm tuần này</span>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="points-earned">
                                    <span class="points-value text-primary">+<?php echo $monthlyPoints; ?></span>
                                    <span class="points-period">điểm tháng này</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Lịch sử điểm -->
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="bi bi-clock-history me-2"></i>Lịch sử điểm</h5>
                        <span class="badge bg-secondary"><?php echo count($pointsHistory); ?> hoạt động gần đây</span>
                    </div>
                    <div class="card-body p-0">
                        <?php if (empty($pointsHistory)): ?>
                        <div class="text-center py-5 text-muted">
                            <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                            <p>Chưa có lịch sử điểm</p>
                            <small>Hãy điểm danh hoặc tham gia hoạt động để kiếm điểm!</small>
                        </div>
                        <?php else: ?>
                        <div class="points-history-list">
                            <?php foreach ($pointsHistory as $log): ?>
                            <div class="points-history-item">
                                <div class="points-value <?php echo $log['DIEM'] > 0 ? 'positive' : 'negative'; ?>">
                                    <?php echo $log['DIEM'] > 0 ? '+' : ''; ?><?php echo $log['DIEM']; ?>
                                </div>
                                <div class="points-info flex-grow-1">
                                    <div class="points-desc"><?php echo htmlspecialchars($log['MOTA']); ?></div>
                                    <small class="text-muted">
                                        <?php echo date('d/m/Y H:i', strtotime($log['NGAYTAO'])); ?>
                                    </small>
                                </div>
                                <div class="points-type">
                                    <?php
                                    $typeIcons = [
                                        'daily_checkin' => '📅',
                                        'ask_question' => '❓',
                                        'answer_question' => '💬',
                                        'receive_star' => '⭐',
                                        'login_streak_7' => '🔥',
                                        'login_streak_30' => '👑',
                                    ];
                                    echo $typeIcons[$log['LOAI']] ?? '📌';
                                    ?>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Right Column -->
            <div class="col-lg-4">
                <!-- Streak Card -->
                <div class="card mb-4 streak-card">
                    <div class="card-body text-center">
                        <div class="streak-fire-large">🔥</div>
                        <div class="streak-number"><?php echo $streak; ?></div>
                        <div class="streak-label mb-3">ngày điểm danh liên tiếp</div>
                        
                        <?php
                        $today = date('Y-m-d');
                        $checkedToday = ($lastLogin === $today);
                        ?>
                        
                        <?php if ($checkedToday): ?>
                        <button class="btn btn-success" disabled>
                            <i class="bi bi-check-circle me-1"></i>Đã điểm danh hôm nay
                        </button>
                        <?php else: ?>
                        <button class="btn btn-checkin-large" onclick="showCheckinModal()">
                            <i class="bi bi-calendar-check me-1"></i>Điểm danh ngay
                        </button>
                        <?php endif; ?>
                        
                        <div class="mt-3">
                            <small class="text-muted">
                                <?php if ($streak >= 7): ?>
                                    🎉 Tuyệt vời! Giữ vững streak nhé!
                                <?php elseif ($streak > 0): ?>
                                    Còn <?php echo 7 - $streak; ?> ngày để nhận bonus +15 điểm
                                <?php else: ?>
                                    Bắt đầu streak của bạn ngay hôm nay!
                                <?php endif; ?>
                            </small>
                        </div>
                    </div>
                </div>

                <!-- Huy hiệu của tôi -->
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="bi bi-award me-2"></i>Huy hiệu</h5>
                        <a href="../points-system.php" class="btn btn-sm btn-outline-primary">Xem tất cả</a>
                    </div>
                    <div class="card-body">
                        <?php if (empty($userBadges)): ?>
                        <div class="text-center py-4 text-muted">
                            <i class="bi bi-award fs-1 d-block mb-2 opacity-50"></i>
                            <p class="mb-0">Chưa có huy hiệu nào</p>
                            <small>Tham gia hoạt động để nhận huy hiệu!</small>
                        </div>
                        <?php else: ?>
                        <div class="badges-grid">
                            <?php foreach ($userBadges as $badge): ?>
                            <div class="badge-item-small" title="<?php echo htmlspecialchars($badge['TENHUYHIEU'] . ': ' . $badge['MOTA']); ?>">
                                <span class="badge-icon"><?php echo $badge['BIEUTUONG']; ?></span>
                                <span class="badge-name"><?php echo htmlspecialchars($badge['TENHUYHIEU']); ?></span>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                        
                        <!-- Huy hiệu sắp đạt -->
                        <hr>
                        <h6 class="text-muted mb-3"><i class="bi bi-hourglass-split me-1"></i>Sắp đạt được</h6>
                        <?php
                        $upcomingBadges = [];
                        foreach ($allBadges as $badge) {
                            if (in_array($badge['MAHUYHIEU'], $userBadgeIds)) continue;
                            
                            $progress = 0;
                            $current = 0;
                            $target = $badge['NGUONGTIEUCHI'];
                            
                            switch ($badge['LOAITIEUCHI']) {
                                case 'cauhoi': $current = $stats['questions']; break;
                                case 'cautraloi': $current = $stats['answers']; break;
                                case 'diem': $current = $user['DIEMDANHGIA']; break;
                                case 'vote': $current = $stats['totalVotes']; break;
                                case 'streak': $current = $streak; break;
                                case 'accepted': $current = $stats['acceptedAnswers']; break;
                            }
                            
                            if ($target > 0) {
                                $progress = min(100, ($current / $target) * 100);
                            }
                            
                            if ($progress >= 50 && $progress < 100) {
                                $badge['progress'] = $progress;
                                $badge['current'] = $current;
                                $upcomingBadges[] = $badge;
                            }
                        }
                        usort($upcomingBadges, function($a, $b) {
                            return $b['progress'] - $a['progress'];
                        });
                        $upcomingBadges = array_slice($upcomingBadges, 0, 3);
                        ?>
                        
                        <?php if (empty($upcomingBadges)): ?>
                        <p class="text-muted small mb-0">Tiếp tục hoạt động để mở khóa huy hiệu mới!</p>
                        <?php else: ?>
                        <?php foreach ($upcomingBadges as $badge): ?>
                        <div class="upcoming-badge mb-3">
                            <div class="d-flex align-items-center mb-1">
                                <span class="me-2" style="font-size: 1.2rem;"><?php echo $badge['BIEUTUONG']; ?></span>
                                <span class="flex-grow-1"><?php echo htmlspecialchars($badge['TENHUYHIEU']); ?></span>
                                <small class="text-muted"><?php echo $badge['current']; ?>/<?php echo $badge['NGUONGTIEUCHI']; ?></small>
                            </div>
                            <div class="progress" style="height: 6px;">
                                <div class="progress-bar bg-primary" style="width: <?php echo $badge['progress']; ?>%"></div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<style>
.stat-item-detail {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem;
    background: var(--gray-50);
    border-radius: var(--rounded-lg);
}

.stat-icon {
    width: 50px;
    height: 50px;
    border-radius: var(--rounded-lg);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
}

.bg-primary-light { background: rgba(99, 102, 241, 0.1); }
.bg-success-light { background: rgba(34, 197, 94, 0.1); }
.bg-warning-light { background: rgba(245, 158, 11, 0.1); }
.bg-info-light { background: rgba(6, 182, 212, 0.1); }

.stat-value {
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--gray-900);
}

.stat-label {
    font-size: 0.875rem;
    color: var(--gray-500);
}

.points-earned {
    padding: 1rem;
    background: var(--gray-50);
    border-radius: var(--rounded-lg);
}

.points-earned .points-value {
    font-size: 1.5rem;
    font-weight: 700;
    display: block;
}

.points-earned .points-period {
    font-size: 0.875rem;
    color: var(--gray-500);
}

.points-history-list {
    max-height: 400px;
    overflow-y: auto;
}

.points-history-item {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem 1.25rem;
    border-bottom: 1px solid var(--gray-100);
}

.points-history-item:last-child {
    border-bottom: none;
}

.points-history-item .points-value {
    font-weight: 700;
    font-size: 1rem;
    min-width: 50px;
    text-align: center;
}

.points-history-item .points-value.positive { color: var(--success); }
.points-history-item .points-value.negative { color: var(--error); }

.points-info .points-desc {
    font-weight: 500;
    color: var(--gray-700);
}

.points-type {
    font-size: 1.25rem;
}

.streak-card {
    background: linear-gradient(135deg, #fef3c7, #fde68a);
    border: none;
}

.badges-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 0.75rem;
}

.badge-item-small {
    text-align: center;
    padding: 0.75rem 0.5rem;
    background: var(--gray-50);
    border-radius: var(--rounded-lg);
    cursor: help;
}

.badge-item-small .badge-icon {
    font-size: 1.5rem;
    display: block;
    margin-bottom: 0.25rem;
}

.badge-item-small .badge-name {
    font-size: 0.7rem;
    color: var(--gray-600);
    display: block;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.upcoming-badge .progress {
    background: var(--gray-200);
}
</style>

<?php require_once '../includes/footer.php'; ?>
