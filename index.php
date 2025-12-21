<?php
$pageTitle = 'Trang chủ - Diễn Đàn Chuyên Ngành';
require_once 'config/database.php';
require_once 'config/session.php';
require_once 'includes/badge_helper.php';

$currentUser = getCurrentUser();

require_once 'includes/header.php';

// Lấy thống kê
$statsQuery = "SELECT 
    (SELECT COUNT(*) FROM NGUOIDUNG WHERE TRANGTHAI = 'active') AS TongNguoiDung,
    (SELECT COUNT(*) FROM CAUHOI) AS TongCauHoi,
    (SELECT COUNT(*) FROM TRALOI) AS TongCauTraLoi,
    (SELECT SUM(LUOTXEM) FROM CAUHOI) AS TongLuotXem";
$stats = $conn->query($statsQuery)->fetch(PDO::FETCH_ASSOC);

// Lấy câu hỏi mới nhất
$questionsQuery = "SELECT 
    ch.MACAUHOI, ch.TIEUDE, ch.LUOTXEM, ch.TRANGTHAI,
    nd.MANGUOIDUNG, nd.HOTEN AS NguoiDat, nd.ANHDAIDIEN,
    t.TENTHE AS Tag, t.MATHE, d.NGAYDANG,
    (SELECT COUNT(*) FROM TRALOI tl WHERE tl.MACAUHOI = ch.MACAUHOI AND (tl.TRANGTHAI = 'approved' OR tl.TRANGTHAI IS NULL)) AS SoCauTraLoi
FROM CAUHOI ch
INNER JOIN DAT d ON ch.MACAUHOI = d.MACAUHOI
INNER JOIN NGUOIDUNG nd ON d.MANGUOIDUNG = nd.MANGUOIDUNG
INNER JOIN TAG t ON ch.MATHE = t.MATHE
WHERE ch.TRANGTHAI IN ('open', 'closed')
ORDER BY d.NGAYDANG DESC LIMIT 8";
$questions = $conn->query($questionsQuery)->fetchAll(PDO::FETCH_ASSOC);

// Lấy top tags
$tagsQuery = "SELECT t.MATHE, t.TENTHE, COUNT(ch.MACAUHOI) AS SoCauHoi
FROM TAG t LEFT JOIN CAUHOI ch ON t.MATHE = ch.MATHE
GROUP BY t.MATHE, t.TENTHE ORDER BY SoCauHoi DESC LIMIT 8";
$topTags = $conn->query($tagsQuery)->fetchAll(PDO::FETCH_ASSOC);

// Lấy top users
$usersQuery = "SELECT nd.MANGUOIDUNG, nd.HOTEN, nd.ANHDAIDIEN, nd.DIEMDANHGIA,
    (SELECT COUNT(*) FROM TRALOI tl WHERE tl.MANGUOIDUNG = nd.MANGUOIDUNG) AS SoCauTraLoi
FROM NGUOIDUNG nd WHERE TRANGTHAI = 'active'
ORDER BY nd.DIEMDANHGIA DESC LIMIT 5";
$topUsers = $conn->query($usersQuery)->fetchAll(PDO::FETCH_ASSOC);
?>

<main class="py-5">
    <div class="container">
        <!-- Hero Section -->
        <div class="hero-section mb-5">
            <?php if ($currentUser): ?>
                <h1>Chào mừng <?php echo htmlspecialchars($currentUser['fullname']); ?> đến Diễn Đàn</h1>
                <p>Nơi chia sẻ kiến thức và giải đáp thắc mắc chuyên môn</p>
                <a href="ask-question.php" class="btn btn-light btn-lg">
                    <i class="bi bi-plus-circle me-2"></i>Đặt câu hỏi ngay
                </a>
            <?php else: ?>
                <h1>Chào mừng đến Diễn Đàn</h1>
                <p>Nơi chia sẻ kiến thức và giải đáp thắc mắc chuyên môn</p>
                <a href="register.php" class="btn btn-light btn-lg me-2">Đăng ký</a>
                <a href="login.php" class="btn btn-outline-light btn-lg">Đăng nhập</a>
            <?php endif; ?>
        </div>

        <!-- Stats - Ẩn hoàn toàn với người dùng, chỉ hiển thị trong admin dashboard -->
        <!-- Phần thống kê đã được chuyển sang admin/dashboard.php -->

        <div class="row g-4">
            <!-- Main Content -->
            <div class="col-lg-8">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 class="mb-0">
                        <i class="bi bi-lightning-charge-fill me-2" style="color: var(--warning);"></i>Câu hỏi mới
                    </h4>
                    <?php if ($currentUser): ?>
                    <a href="questions.php" class="btn btn-outline-primary btn-sm">Xem tất cả</a>
                    <?php else: ?>
                    <a href="login.php" class="btn btn-outline-primary btn-sm">Đăng nhập để xem thêm</a>
                    <?php endif; ?>
                </div>
                
                <?php if (!$currentUser): ?>
                <div class="alert alert-info mb-4">
                    <i class="bi bi-info-circle me-2"></i>
                    <strong>Đăng nhập</strong> để xem chi tiết câu hỏi, đặt câu hỏi và tham gia thảo luận.
                    <a href="login.php" class="alert-link">Đăng nhập ngay</a> hoặc <a href="register.php" class="alert-link">Đăng ký</a>
                </div>
                <?php endif; ?>

                <?php if (empty($questions)): ?>
                <div class="card">
                    <div class="card-body empty-state">
                        <i class="bi bi-chat-square-text"></i>
                        <h5>Chưa có câu hỏi nào</h5>
                        <p>Hãy là người đầu tiên đặt câu hỏi!</p>
                        <a href="ask-question.php" class="btn btn-primary">Đặt câu hỏi</a>
                    </div>
                </div>
                <?php else: ?>
                <?php foreach ($questions as $q): ?>
                <div class="question-card">
                    <div class="d-flex gap-4">
                        <div class="d-none d-md-flex flex-column gap-2" style="min-width: 70px;">
                            <div class="stat-item <?php echo $q['SoCauTraLoi'] > 0 ? 'has-answers' : ''; ?>">
                                <span class="stat-number"><?php echo $q['SoCauTraLoi']; ?></span>
                                <span class="stat-label">trả lời</span>
                            </div>
                            <div class="stat-item">
                                <span class="stat-number" style="color: var(--gray-500); font-size: var(--font-base);"><?php echo number_format($q['LUOTXEM']); ?></span>
                                <span class="stat-label">xem</span>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h5 class="mb-2">
                                <?php if ($currentUser): ?>
                                <a href="question-detail.php?id=<?php echo $q['MACAUHOI']; ?>">
                                    <?php echo htmlspecialchars($q['TIEUDE']); ?>
                                </a>
                                <?php else: ?>
                                <a href="login.php?redirect=<?php echo urlencode('question-detail.php?id=' . $q['MACAUHOI']); ?>" class="text-dark">
                                    <?php echo htmlspecialchars($q['TIEUDE']); ?>
                                    <i class="bi bi-lock-fill text-muted ms-1" style="font-size: 0.8em;" title="Đăng nhập để xem"></i>
                                </a>
                                <?php endif; ?>
                            </h5>
                            <div class="d-flex flex-wrap align-items-center gap-3">
                                <a href="questions.php?tag=<?php echo $q['MATHE'] ?? ''; ?>" class="tag"><?php echo htmlspecialchars($q['Tag']); ?></a>
                                <div class="d-flex align-items-center gap-2 text-muted" style="font-size: var(--font-sm);">
                                    <?php echo renderAvatarWithFrame($q['ANHDAIDIEN'], $q['MANGUOIDUNG'], 'sm', false); ?>
                                    <span><?php echo htmlspecialchars($q['NguoiDat']); ?></span>
                                    <span>•</span>
                                    <span><?php echo date('d/m/Y', strtotime($q['NGAYDANG'])); ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Tags -->
                <div class="sidebar-widget">
                    <h5><i class="bi bi-tags-fill"></i>Tags phổ biến</h5>
                    <div class="d-flex flex-wrap gap-2">
                        <?php foreach ($topTags as $tag): ?>
                        <a href="questions.php?tag=<?php echo $tag['MATHE']; ?>" class="tag">
                            <?php echo htmlspecialchars($tag['TENTHE']); ?>
                            <span style="opacity: 0.7; margin-left: 4px;"><?php echo $tag['SoCauHoi']; ?></span>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Top Users -->
                <div class="sidebar-widget">
                    <h5><i class="bi bi-trophy-fill"></i>Thành viên nổi bật</h5>
                    <div class="d-flex flex-column gap-2">
                        <?php foreach ($topUsers as $i => $user): ?>
                        <a href="profile.php?id=<?php echo $user['MANGUOIDUNG']; ?>" class="d-flex align-items-center gap-3 p-2 rounded text-decoration-none" style="transition: background var(--transition-fast);" onmouseover="this.style.background='var(--gray-50)'" onmouseout="this.style.background='transparent'">
                            <span class="rank-medal <?php echo $i < 3 ? 'rank-'.($i+1) : ''; ?>" style="<?php echo $i >= 3 ? 'background: var(--gray-400);' : ''; ?>">
                                <?php echo $i + 1; ?>
                            </span>
                            <?php echo renderAvatarWithFrame($user['ANHDAIDIEN'], $user['MANGUOIDUNG'], 'sm', false); ?>
                            <div class="flex-grow-1">
                                <div style="font-weight: var(--weight-medium); color: var(--gray-900); font-size: var(--font-sm);">
                                    <?php echo htmlspecialchars($user['HOTEN']); ?>
                                </div>
                                <small class="text-muted"><?php echo number_format($user['DIEMDANHGIA']); ?> điểm</small>
                            </div>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Quick Links -->
                <div class="sidebar-widget" style="background: linear-gradient(135deg, var(--primary-500), var(--primary-600)); border: none;">
                    <h5 style="color: white;"><i class="bi bi-lightning-fill" style="color: var(--warning);"></i>Liên kết nhanh</h5>
                    <div class="d-flex flex-column gap-2">
                        <a href="leaderboard.php" class="btn btn-light w-100">
                            <i class="bi bi-trophy me-2"></i>Bảng xếp hạng
                        </a>
                        <a href="points-system.php" class="btn btn-outline-light w-100">
                            <i class="bi bi-info-circle me-2"></i>Hệ thống điểm
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<?php require_once 'includes/footer.php'; ?>
