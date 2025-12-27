<?php
$pageTitle = 'Hệ thống điểm';
require_once 'config/database.php';
require_once 'includes/points_helper.php';
require_once 'includes/header.php';

// Lấy danh sách huy hiệu
$badges = $conn->query("SELECT * FROM HUYHIEU ORDER BY CAPDO ASC, NGUONGTIEUCHI ASC")->fetchAll(PDO::FETCH_ASSOC);

// Lấy thống kê của user hiện tại
$userStats = null;
$pointsHistory = [];
$weeklyPoints = 0;
if ($currentUser) {
    $userStats = getUserStatsForBadge($currentUser['id']);
    $pointsHistory = getPointsHistory($currentUser['id'], 10);
    $weeklyPoints = getPointsEarned($currentUser['id'], 'week');
}
?>

<main class="py-4">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <!-- Header -->
                <div class="text-center mb-5">
                    <h1 class="display-5 mb-3">
                        <i class="bi bi-star-fill text-warning me-2"></i>
                        Hệ Thống Điểm & Huy Hiệu
                    </h1>
                    <p class="lead text-muted">Tham gia đóng góp để kiếm điểm và nhận huy hiệu</p>
                </div>

                <?php if ($currentUser && $userStats): ?>
                <!-- Thống kê cá nhân -->
                <div class="row g-3 mb-4">
                    <div class="col-6 col-md-3">
                        <div class="stats-card-modern bg-gradient-primary">
                            <div class="stats-number"><?php echo number_format($currentUser['points']); ?></div>
                            <div class="stats-label">Tổng điểm</div>
                            <i class="bi bi-trophy stats-icon"></i>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="stats-card-modern bg-gradient-success">
                            <div class="stats-number">+<?php echo number_format($weeklyPoints); ?></div>
                            <div class="stats-label">Tuần này</div>
                            <i class="bi bi-graph-up stats-icon"></i>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="stats-card-modern bg-gradient-info">
                            <div class="stats-number"><?php echo $userStats['questions']; ?></div>
                            <div class="stats-label">Câu hỏi</div>
                            <i class="bi bi-question-circle stats-icon"></i>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="stats-card-modern bg-gradient-warning">
                            <div class="stats-number"><?php echo $userStats['answers']; ?></div>
                            <div class="stats-label">Câu trả lời</div>
                            <i class="bi bi-chat-dots stats-icon"></i>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Cách kiếm điểm - Mở rộng -->
                <div class="card modern-card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0"><i class="bi bi-coin me-2"></i>Cách kiếm điểm</h4>
                    </div>
                    <div class="card-body p-0">
                        <!-- Tab navigation -->
                        <ul class="nav nav-tabs nav-fill" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" data-bs-toggle="tab" href="#tab-basic">
                                    <i class="bi bi-star me-1"></i>Cơ bản
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="tab" href="#tab-bonus">
                                    <i class="bi bi-gift me-1"></i>Bonus
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="tab" href="#tab-quality">
                                    <i class="bi bi-award me-1"></i>Chất lượng
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="tab" href="#tab-penalty">
                                    <i class="bi bi-exclamation-triangle me-1"></i>Phạt
                                </a>
                            </li>
                        </ul>
                        
                        <div class="tab-content p-4">
                            <!-- Tab Cơ bản -->
                            <div class="tab-pane fade show active" id="tab-basic">
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Hoạt động</th>
                                                <th class="text-center">Điểm</th>
                                                <th>Mô tả</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td><i class="bi bi-question-circle text-primary me-2"></i>Đặt câu hỏi</td>
                                                <td class="text-center"><span class="badge bg-success">+5</span></td>
                                                <td>Mỗi câu hỏi được đăng</td>
                                            </tr>
                                            <tr>
                                                <td><i class="bi bi-check2-circle text-success me-2"></i>Câu hỏi được duyệt</td>
                                                <td class="text-center"><span class="badge bg-success">+3</span></td>
                                                <td>Khi moderator duyệt câu hỏi</td>
                                            </tr>
                                            <tr>
                                                <td><i class="bi bi-chat-left-text text-info me-2"></i>Trả lời câu hỏi</td>
                                                <td class="text-center"><span class="badge bg-success">+10</span></td>
                                                <td>Mỗi câu trả lời được đăng</td>
                                            </tr>
                                            <tr>
                                                <td><i class="bi bi-check-circle-fill text-success me-2"></i>Câu trả lời được chấp nhận</td>
                                                <td class="text-center"><span class="badge bg-success">+25</span></td>
                                                <td>Khi người hỏi chấp nhận câu trả lời</td>
                                            </tr>
                                            <tr>
                                                <td><i class="bi bi-star-fill text-warning me-2"></i>Nhận đánh giá sao</td>
                                                <td class="text-center"><span class="badge bg-success">+1 ~ +5</span></td>
                                                <td>Tùy theo số sao (1-5 sao)</td>
                                            </tr>
                                            <tr>
                                                <td><i class="bi bi-hand-thumbs-up text-primary me-2"></i>Đánh giá người khác</td>
                                                <td class="text-center"><span class="badge bg-success">+1</span></td>
                                                <td>Mỗi lần đánh giá câu hỏi/trả lời</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            
                            <!-- Tab Bonus -->
                            <div class="tab-pane fade" id="tab-bonus">
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Hoạt động</th>
                                                <th class="text-center">Điểm</th>
                                                <th>Mô tả</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td><i class="bi bi-calendar-check text-success me-2"></i>Đăng nhập hàng ngày</td>
                                                <td class="text-center"><span class="badge bg-info">+2</span></td>
                                                <td>Mỗi ngày đăng nhập</td>
                                            </tr>
                                            <tr>
                                                <td><i class="bi bi-calendar-week text-primary me-2"></i>Streak 7 ngày</td>
                                                <td class="text-center"><span class="badge bg-info">+15</span></td>
                                                <td>Đăng nhập 7 ngày liên tiếp</td>
                                            </tr>
                                            <tr>
                                                <td><i class="bi bi-fire text-danger me-2"></i>Streak 30 ngày</td>
                                                <td class="text-center"><span class="badge bg-info">+50</span></td>
                                                <td>Đăng nhập 30 ngày liên tiếp</td>
                                            </tr>
                                            <tr>
                                                <td><i class="bi bi-person-badge text-info me-2"></i>Hoàn thiện hồ sơ</td>
                                                <td class="text-center"><span class="badge bg-info">+10</span></td>
                                                <td>Điền đầy đủ thông tin cá nhân</td>
                                            </tr>
                                            <tr>
                                                <td><i class="bi bi-1-circle text-warning me-2"></i>Câu hỏi đầu tiên</td>
                                                <td class="text-center"><span class="badge bg-info">+10</span></td>
                                                <td>Đặt câu hỏi đầu tiên</td>
                                            </tr>
                                            <tr>
                                                <td><i class="bi bi-lightning text-warning me-2"></i>Người trả lời đầu tiên</td>
                                                <td class="text-center"><span class="badge bg-info">+5</span></td>
                                                <td>Là người đầu tiên trả lời câu hỏi</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            
                            <!-- Tab Chất lượng -->
                            <div class="tab-pane fade" id="tab-quality">
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Thành tựu</th>
                                                <th class="text-center">Điểm</th>
                                                <th>Điều kiện</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td><i class="bi bi-star text-warning me-2"></i>Câu hỏi nổi bật</td>
                                                <td class="text-center"><span class="badge bg-warning text-dark">+20</span></td>
                                                <td>Câu hỏi được đánh dấu nổi bật bởi mod</td>
                                            </tr>
                                            <tr>
                                                <td><i class="bi bi-heart-fill text-danger me-2"></i>Câu hỏi hay</td>
                                                <td class="text-center"><span class="badge bg-warning text-dark">+10</span></td>
                                                <td>Nhận 5+ đánh giá 4-5 sao</td>
                                            </tr>
                                            <tr>
                                                <td><i class="bi bi-eye-fill text-info me-2"></i>Câu hỏi phổ biến</td>
                                                <td class="text-center"><span class="badge bg-warning text-dark">+20</span></td>
                                                <td>Đạt 100+ lượt xem</td>
                                            </tr>
                                            <tr>
                                                <td><i class="bi bi-hand-thumbs-up-fill text-success me-2"></i>Câu trả lời hữu ích</td>
                                                <td class="text-center"><span class="badge bg-warning text-dark">+15</span></td>
                                                <td>Nhận 5+ đánh giá 4-5 sao</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            
                            <!-- Tab Phạt -->
                            <div class="tab-pane fade" id="tab-penalty">
                                <div class="alert alert-warning mb-3">
                                    <i class="bi bi-exclamation-triangle me-2"></i>
                                    Các hành vi vi phạm sẽ bị trừ điểm. Hãy tuân thủ quy tắc cộng đồng!
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Vi phạm</th>
                                                <th class="text-center">Điểm</th>
                                                <th>Mô tả</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td><i class="bi bi-trash text-danger me-2"></i>Xóa câu hỏi</td>
                                                <td class="text-center"><span class="badge bg-danger">-5</span></td>
                                                <td>Tự xóa câu hỏi đã đăng</td>
                                            </tr>
                                            <tr>
                                                <td><i class="bi bi-trash text-danger me-2"></i>Xóa câu trả lời</td>
                                                <td class="text-center"><span class="badge bg-danger">-3</span></td>
                                                <td>Tự xóa câu trả lời đã đăng</td>
                                            </tr>
                                            <tr>
                                                <td><i class="bi bi-flag text-warning me-2"></i>Bị báo cáo hợp lệ</td>
                                                <td class="text-center"><span class="badge bg-danger">-10</span></td>
                                                <td>Nội dung bị báo cáo và xác nhận vi phạm</td>
                                            </tr>
                                            <tr>
                                                <td><i class="bi bi-exclamation-octagon text-danger me-2"></i>Spam</td>
                                                <td class="text-center"><span class="badge bg-danger">-20</span></td>
                                                <td>Đăng nội dung spam, quảng cáo</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Huy hiệu -->
                <div class="card modern-card mb-4">
                    <div class="card-header bg-warning text-dark d-flex justify-content-between align-items-center">
                        <h4 class="mb-0"><i class="bi bi-award me-2"></i>Danh sách huy hiệu</h4>
                        <span class="badge bg-dark"><?php echo count($badges); ?> huy hiệu</span>
                    </div>
                    <div class="card-body">
                        <?php if (empty($badges)): ?>
                        <p class="text-muted text-center py-4">Chưa có huy hiệu nào được thiết lập</p>
                        <?php else: ?>
                        
                        <!-- Phân loại theo cấp độ -->
                        <?php
                        $badgesByLevel = [];
                        foreach ($badges as $badge) {
                            $level = $badge['CAPDO'] ?? 1;
                            $badgesByLevel[$level][] = $badge;
                        }
                        ksort($badgesByLevel);
                        
                        // Màu xen kẽ nóng/lạnh: Lạnh - Nóng - Lạnh - Nóng - Lạnh - Nóng
                        $levelNames = [
                            1 => ['name' => 'Cơ bản', 'color' => '#22c55e', 'icon' => '🌱'],      // Xanh lá (lạnh)
                            2 => ['name' => 'Thường', 'color' => '#f97316', 'icon' => '🌿'],      // Cam (nóng)
                            3 => ['name' => 'Hiếm', 'color' => '#3b82f6', 'icon' => '💎'],        // Xanh dương (lạnh)
                            4 => ['name' => 'Sử thi', 'color' => '#ef4444', 'icon' => '⚡'],      // Đỏ (nóng)
                            5 => ['name' => 'Huyền thoại', 'color' => '#8b5cf6', 'icon' => '🔥'], // Tím (lạnh)
                            6 => ['name' => 'Thần thoại', 'color' => '#eab308', 'icon' => '👑'],  // Vàng (nóng)
                        ];
                        ?>
                        
                        <?php foreach ($badgesByLevel as $level => $levelBadges): ?>
                        <?php $levelInfo = $levelNames[$level] ?? ['name' => 'Cấp ' . $level, 'color' => '#6b7280', 'icon' => '⭐']; ?>
                        <div class="mb-4">
                            <h6 class="d-flex align-items-center gap-2 mb-3">
                                <span><?php echo $levelInfo['icon']; ?></span>
                                <span class="badge" style="background: <?php echo $levelInfo['color']; ?>; color: #fff;"><?php echo $levelInfo['name']; ?></span>
                                <small class="text-muted">(<?php echo count($levelBadges); ?> huy hiệu)</small>
                            </h6>
                            <div class="row g-3">
                                <?php 
                                // Màu xanh dương thống nhất cho tất cả badge cards
                                $badgeColor = '#3b82f6';
                                foreach ($levelBadges as $badge): 
                                    $userHas = $currentUser && userHasBadge($currentUser['id'], $badge['MAHUYHIEU']);
                                ?>
                                <div class="col-md-6 col-lg-4">
                                    <div class="card h-100 badge-card <?php echo $userHas ? 'badge-owned' : 'badge-locked'; ?>" style="border-color: <?php echo $badgeColor; ?>;">
                                        <div class="card-body text-center py-3">
                                            <div class="badge-icon-large mb-2">
                                                <?php echo $badge['BIEUTUONG']; ?>
                                            </div>
                                            <h6 class="mb-1"><?php echo htmlspecialchars($badge['TENHUYHIEU']); ?></h6>
                                            <p class="text-muted small mb-2"><?php echo htmlspecialchars($badge['MOTA']); ?></p>
                                            <span class="badge" style="background: #f97316; color: #000;">
                                                <?php 
                                                $criteria = $badge['LOAITIEUCHI'];
                                                $threshold = $badge['NGUONGTIEUCHI'];
                                                switch ($criteria) {
                                                    case 'ngaythamgia': echo 'Đăng ký'; break;
                                                    case 'cautraloi': echo $threshold . ' trả lời'; break;
                                                    case 'cauhoi': echo $threshold . ' câu hỏi'; break;
                                                    case 'vote': echo $threshold . ' đánh giá tốt (4-5⭐)'; break;
                                                    case 'diem': echo $threshold . ' điểm'; break;
                                                    case 'streak': echo $threshold . ' ngày liên tiếp'; break;
                                                    case 'accepted': echo $threshold . ' được chấp nhận'; break;
                                                    default: echo $criteria;
                                                }
                                                ?>
                                            </span>
                                            <?php if ($userHas): ?>
                                            <div class="mt-2">
                                                <span class="badge" style="background: #22c55e; color: #fff;"><i class="bi bi-check-circle me-1"></i>Đã sở hữu</span>
                                            </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Quy tắc -->
                <div class="card modern-card">
                    <div class="card-header bg-info text-white">
                        <h4 class="mb-0"><i class="bi bi-info-circle me-2"></i>Quy tắc cộng đồng</h4>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled mb-0">
                            <li class="mb-3">
                                <i class="bi bi-check-circle-fill text-success me-2"></i>
                                <strong>Tôn trọng:</strong> Luôn tôn trọng các thành viên khác, không sử dụng ngôn ngữ xúc phạm.
                            </li>
                            <li class="mb-3">
                                <i class="bi bi-check-circle-fill text-success me-2"></i>
                                <strong>Chất lượng:</strong> Đặt câu hỏi rõ ràng, chi tiết. Trả lời đầy đủ và hữu ích.
                            </li>
                            <li class="mb-3">
                                <i class="bi bi-check-circle-fill text-success me-2"></i>
                                <strong>Trung thực:</strong> Không spam, không gian lận điểm.
                            </li>
                            <li class="mb-3">
                                <i class="bi bi-check-circle-fill text-success me-2"></i>
                                <strong>Chia sẻ:</strong> Đánh giá công bằng, giúp đỡ người mới.
                            </li>
                            <li>
                                <i class="bi bi-check-circle-fill text-success me-2"></i>
                                <strong>Học hỏi:</strong> Luôn cầu tiến và sẵn sàng học hỏi từ cộng đồng.
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- CTA -->
                <div class="text-center mt-4">
                    <?php if ($currentUser): ?>
                    <a href="ask-question.php" class="btn btn-primary btn-lg me-2">
                        <i class="bi bi-plus-circle me-2"></i>Đặt câu hỏi ngay
                    </a>
                    <a href="questions.php" class="btn btn-outline-primary btn-lg">
                        <i class="bi bi-search me-2"></i>Tìm câu hỏi để trả lời
                    </a>
                    <?php else: ?>
                    <a href="register.php" class="btn btn-primary btn-lg me-2">
                        <i class="bi bi-person-plus me-2"></i>Đăng ký ngay
                    </a>
                    <a href="login.php" class="btn btn-outline-primary btn-lg">
                        <i class="bi bi-box-arrow-in-right me-2"></i>Đăng nhập
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</main>

<style>
.badge-icon-large {
    font-size: 3rem;
}
</style>

<?php require_once 'includes/footer.php'; ?>
