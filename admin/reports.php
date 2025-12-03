<?php
$pageTitle = 'Báo cáo & Thống kê';
require_once '../config/database.php';
require_once '../config/session.php';
requireAdmin();

// Thống kê theo thời gian
$statsToday = $conn->query("
    SELECT 
        (SELECT COUNT(*) FROM NGUOIDUNG WHERE DATE(NGAYTAO) = CURDATE()) as NewUsers,
        (SELECT COUNT(*) FROM DAT WHERE DATE(NGAYDANG) = CURDATE()) as NewQuestions,
        (SELECT COUNT(*) FROM TRALOI WHERE DATE(NGAYTL) = CURDATE()) as NewAnswers
")->fetch();

$statsWeek = $conn->query("
    SELECT 
        (SELECT COUNT(*) FROM NGUOIDUNG WHERE NGAYTAO >= DATE_SUB(NOW(), INTERVAL 7 DAY)) as NewUsers,
        (SELECT COUNT(*) FROM DAT WHERE NGAYDANG >= DATE_SUB(NOW(), INTERVAL 7 DAY)) as NewQuestions,
        (SELECT COUNT(*) FROM TRALOI WHERE NGAYTL >= DATE_SUB(NOW(), INTERVAL 7 DAY)) as NewAnswers
")->fetch();

// Top contributors
$topContributors = $conn->query("
    SELECT nd.MANGUOIDUNG, nd.HOTEN, nd.ANHDAIDIEN, nd.DIEMDANHGIA,
           (SELECT COUNT(*) FROM DAT WHERE MANGUOIDUNG = nd.MANGUOIDUNG) as SoCauHoi,
           (SELECT COUNT(*) FROM TRALOI WHERE MANGUOIDUNG = nd.MANGUOIDUNG) as SoTraLoi
    FROM NGUOIDUNG nd
    WHERE nd.TRANGTHAI = 'active'
    ORDER BY (SoCauHoi + SoTraLoi) DESC
    LIMIT 10
")->fetchAll();

// Popular tags
$popularTags = $conn->query("
    SELECT t.TENTHE, COUNT(ch.MACAUHOI) as SoCauHoi
    FROM TAG t
    LEFT JOIN CAUHOI ch ON t.MATHE = ch.MATHE
    GROUP BY t.MATHE, t.TENTHE
    ORDER BY SoCauHoi DESC
    LIMIT 10
")->fetchAll();

require_once '../includes/admin_header.php';
?>

<div class="admin-container">
    <div class="container-fluid py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="mb-1"><i class="bi bi-graph-up me-2"></i>Báo cáo & Thống kê</h2>
                <p class="text-muted mb-0">Phân tích hoạt động diễn đàn</p>
            </div>
            <button class="btn btn-primary" onclick="window.print()">
                <i class="bi bi-printer me-2"></i>In báo cáo
            </button>
        </div>

        <!-- Stats Cards -->
        <div class="row g-4 mb-4">
            <div class="col-md-6">
                <div class="card modern-card">
                    <div class="card-header bg-transparent border-0">
                        <h5 class="mb-0"><i class="bi bi-calendar-day text-primary me-2"></i>Hôm nay</h5>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-4">
                                <h3 class="text-primary"><?php echo $statsToday['NewUsers']; ?></h3>
                                <small class="text-muted">Người dùng mới</small>
                            </div>
                            <div class="col-4">
                                <h3 class="text-success"><?php echo $statsToday['NewQuestions']; ?></h3>
                                <small class="text-muted">Câu hỏi mới</small>
                            </div>
                            <div class="col-4">
                                <h3 class="text-info"><?php echo $statsToday['NewAnswers']; ?></h3>
                                <small class="text-muted">Câu trả lời</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card modern-card">
                    <div class="card-header bg-transparent border-0">
                        <h5 class="mb-0"><i class="bi bi-calendar-week text-success me-2"></i>7 ngày qua</h5>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-4">
                                <h3 class="text-primary"><?php echo $statsWeek['NewUsers']; ?></h3>
                                <small class="text-muted">Người dùng mới</small>
                            </div>
                            <div class="col-4">
                                <h3 class="text-success"><?php echo $statsWeek['NewQuestions']; ?></h3>
                                <small class="text-muted">Câu hỏi mới</small>
                            </div>
                            <div class="col-4">
                                <h3 class="text-info"><?php echo $statsWeek['NewAnswers']; ?></h3>
                                <small class="text-muted">Câu trả lời</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <!-- Top Contributors -->
            <div class="col-lg-6">
                <div class="card modern-card">
                    <div class="card-header bg-transparent border-0">
                        <h5 class="mb-0"><i class="bi bi-trophy-fill text-warning me-2"></i>Top Contributors</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Người dùng</th>
                                        <th>Câu hỏi</th>
                                        <th>Trả lời</th>
                                        <th>Điểm</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($topContributors as $index => $user): ?>
                                    <tr>
                                        <td><?php echo $index + 1; ?></td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <img src="<?php echo htmlspecialchars($user['ANHDAIDIEN']); ?>" class="user-avatar-sm me-2" alt="Avatar">
                                                <span><?php echo htmlspecialchars($user['HOTEN']); ?></span>
                                            </div>
                                        </td>
                                        <td><span class="badge bg-primary"><?php echo $user['SoCauHoi']; ?></span></td>
                                        <td><span class="badge bg-success"><?php echo $user['SoTraLoi']; ?></span></td>
                                        <td><span class="badge bg-warning"><?php echo $user['DIEMDANHGIA']; ?></span></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Popular Tags -->
            <div class="col-lg-6">
                <div class="card modern-card">
                    <div class="card-header bg-transparent border-0">
                        <h5 class="mb-0"><i class="bi bi-tags-fill text-info me-2"></i>Tags phổ biến</h5>
                    </div>
                    <div class="card-body">
                        <?php foreach ($popularTags as $tag): ?>
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="tag"><?php echo htmlspecialchars($tag['TENTHE']); ?></span>
                            <div>
                                <span class="badge bg-primary"><?php echo $tag['SoCauHoi']; ?> câu hỏi</span>
                                <div class="progress mt-1" style="width: 200px; height: 8px;">
                                    <div class="progress-bar" style="width: <?php echo min(100, $tag['SoCauHoi'] * 10); ?>%"></div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/admin_footer.php'; ?>
