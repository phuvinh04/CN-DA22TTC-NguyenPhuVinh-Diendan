<?php
$pageTitle = 'Trang chủ - Diễn Đàn Chuyên Ngành';
require_once 'config/database.php';
require_once 'includes/header.php';

// Lấy thống kê tổng quan
$statsQuery = "SELECT 
    (SELECT COUNT(*) FROM NGUOIDUNG WHERE TRANGTHAI = N'active') AS TongNguoiDung,
    (SELECT COUNT(*) FROM CAUHOI) AS TongCauHoi,
    (SELECT COUNT(*) FROM TRALOI) AS TongCauTraLoi,
    (SELECT SUM(LUOTXEM) FROM CAUHOI) AS TongLuotXem";
$stats = $conn->query($statsQuery)->fetch(PDO::FETCH_ASSOC);

// Lấy câu hỏi mới nhất
$questionsQuery = "SELECT 
    ch.MACAUHOI,
    ch.TIEUDE,
    ch.LUOTXEM,
    ch.TRANGTHAI,
    nd.HOTEN AS NguoiDat,
    nd.ANHDAIDIEN,
    t.TENTHE AS Tag,
    d.NGAYDANG,
    (SELECT COUNT(*) FROM TRALOI tl WHERE tl.MACAUHOI = ch.MACAUHOI) AS SoCauTraLoi
FROM CAUHOI ch
INNER JOIN DAT d ON ch.MACAUHOI = d.MACAUHOI
INNER JOIN NGUOIDUNG nd ON d.MANGUOIDUNG = nd.MANGUOIDUNG
INNER JOIN TAG t ON ch.MATHE = t.MATHE
ORDER BY d.NGAYDANG DESC
LIMIT 10";
$questions = $conn->query($questionsQuery)->fetchAll(PDO::FETCH_ASSOC);

// Lấy top tags
$tagsQuery = "SELECT
    t.MATHE,
    t.TENTHE,
    COUNT(ch.MACAUHOI) AS SoCauHoi
FROM TAG t
LEFT JOIN CAUHOI ch ON t.MATHE = ch.MATHE
GROUP BY t.MATHE, t.TENTHE
ORDER BY SoCauHoi DESC
LIMIT 8";
$topTags = $conn->query($tagsQuery)->fetchAll(PDO::FETCH_ASSOC);

// Lấy top users
$usersQuery = "SELECT
    nd.MANGUOIDUNG,
    nd.HOTEN,
    nd.ANHDAIDIEN,
    nd.DIEMDANHGIA,
    (SELECT COUNT(*) FROM TRALOI tl WHERE tl.MANGUOIDUNG = nd.MANGUOIDUNG) AS SoCauTraLoi
FROM NGUOIDUNG nd
WHERE TRANGTHAI = 'active'
ORDER BY nd.DIEMDANHGIA DESC
LIMIT 5";
$topUsers = $conn->query($usersQuery)->fetchAll(PDO::FETCH_ASSOC);
?>

<main class="py-4">
    <div class="container">
        <!-- Hero Section -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card bg-gradient text-white" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;">
                    <div class="card-body py-5 text-center">
                        <h1 class="display-4 fw-bold mb-3" style="color: white !important;">Chào mừng đến Diễn Đàn Chuyên Ngành</h1>
                        <p class="lead mb-4" style="color: white !important;">Nơi chia sẻ kiến thức và giải đáp thắc mắc chuyên môn</p>
                        <?php if ($currentUser): ?>
                            <a href="ask-question.php" class="btn btn-light btn-lg">
                                <i class="bi bi-plus-circle me-2"></i>Đặt câu hỏi ngay
                            </a>
                        <?php else: ?>
                            <a href="register.php" class="btn btn-light btn-lg me-2">Đăng ký</a>
                            <a href="login.php" class="btn btn-outline-light btn-lg">Đăng nhập</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="row mb-4">
            <div class="col-md-3 col-sm-6 mb-3">
                <div class="card stats-card">
                    <div class="stats-number text-primary"><?php echo number_format($stats['TongCauHoi']); ?></div>
                    <div class="stats-label">Câu hỏi</div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-3">
                <div class="card stats-card">
                    <div class="stats-number text-success"><?php echo number_format($stats['TongCauTraLoi']); ?></div>
                    <div class="stats-label">Câu trả lời</div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-3">
                <div class="card stats-card">
                    <div class="stats-number text-info"><?php echo number_format($stats['TongNguoiDung']); ?></div>
                    <div class="stats-label">Thành viên</div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-3">
                <div class="card stats-card">
                    <div class="stats-number text-warning"><?php echo number_format($stats['TongLuotXem']); ?></div>
                    <div class="stats-label">Lượt xem</div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Main Content -->
            <div class="col-lg-8">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h3><i class="bi bi-fire text-danger me-2"></i>Câu hỏi mới nhất</h3>
                    <a href="questions.php" class="btn btn-outline-primary btn-sm">Xem tất cả</a>
                </div>

                <?php foreach ($questions as $question): ?>
                <div class="card question-card fade-in">
                    <div class="row g-0">
                        <div class="col-auto">
                            <div class="question-stats">
                                <div class="stat-item <?php echo $question['SoCauTraLoi'] > 0 ? 'bg-success-subtle' : ''; ?>">
                                    <div class="stat-number <?php echo $question['SoCauTraLoi'] > 0 ? 'text-success' : ''; ?>">
                                        <?php echo $question['SoCauTraLoi']; ?>
                                    </div>
                                    <div class="stat-label">trả lời</div>
                                </div>
                                <div class="stat-item">
                                    <div class="stat-number text-secondary"><?php echo number_format($question['LUOTXEM']); ?></div>
                                    <div class="stat-label">xem</div>
                                </div>
                            </div>
                        </div>
                        <div class="col">
                            <h5 class="mb-2">
                                <a href="question-detail.php?id=<?php echo $question['MACAUHOI']; ?>" class="text-decoration-none text-dark">
                                    <?php echo htmlspecialchars($question['TIEUDE']); ?>
                                </a>
                            </h5>
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <span class="tag"><?php echo htmlspecialchars($question['Tag']); ?></span>
                                </div>
                                <div class="d-flex align-items-center text-muted small">
                                    <img src="<?php echo htmlspecialchars($question['ANHDAIDIEN']); ?>" alt="Avatar" class="user-avatar-sm me-2">
                                    <span><?php echo htmlspecialchars($question['NguoiDat']); ?></span>
                                    <span class="ms-2"><?php echo date('d/m/Y H:i', strtotime($question['NGAYDANG'])); ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Top Tags -->
                <div class="sidebar-widget">
                    <h5><i class="bi bi-tags-fill text-primary me-2"></i>Tags phổ biến</h5>
                    <div class="d-flex flex-wrap gap-2">
                        <?php foreach ($topTags as $tag): ?>
                        <a href="tags.php?id=<?php echo $tag['MATHE']; ?>" class="tag">
                            <?php echo htmlspecialchars($tag['TENTHE']); ?>
                            <span class="badge bg-primary rounded-pill ms-1"><?php echo $tag['SoCauHoi']; ?></span>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Top Users -->
                <div class="sidebar-widget">
                    <h5><i class="bi bi-trophy-fill text-warning me-2"></i>Thành viên nổi bật</h5>
                    <div class="list-group list-group-flush">
                        <?php foreach ($topUsers as $index => $user): ?>
                        <a href="profile.php?id=<?php echo $user['MANGUOIDUNG']; ?>" class="list-group-item list-group-item-action d-flex align-items-center border-0 px-0">
                            <span class="badge bg-primary rounded-circle me-3" style="width: 30px; height: 30px; display: flex; align-items: center; justify-content: center;">
                                <?php echo $index + 1; ?>
                            </span>
                            <img src="<?php echo htmlspecialchars($user['ANHDAIDIEN']); ?>" alt="Avatar" class="user-avatar me-3">
                            <div class="flex-grow-1">
                                <div class="fw-semibold"><?php echo htmlspecialchars($user['HOTEN']); ?></div>
                                <small class="text-muted"><?php echo $user['DIEMDANHGIA']; ?> điểm • <?php echo $user['SoCauTraLoi']; ?> trả lời</small>
                            </div>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Quick Links -->
                <div class="sidebar-widget" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                    <h5 class="text-white"><i class="bi bi-trophy-fill me-2"></i>Bảng xếp hạng</h5>
                    <p class="text-white-50 small mb-3">Xem những thành viên xuất sắc nhất</p>
                    <a href="leaderboard.php" class="btn btn-light w-100 mb-2">
                        <i class="bi bi-trophy me-2"></i>Xem bảng xếp hạng
                    </a>
                    <a href="points-system.php" class="btn btn-outline-light w-100">
                        <i class="bi bi-info-circle me-2"></i>Cách kiếm điểm
                    </a>
                </div>

                <!-- Quick Links -->
                <div class="sidebar-widget bg-light">
                    <h5><i class="bi bi-info-circle-fill text-info me-2"></i>Hướng dẫn</h5>
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2"><a href="points-system.php" class="text-decoration-none"><i class="bi bi-chevron-right me-1"></i>Hệ thống điểm và huy hiệu</a></li>
                        <li class="mb-2"><a href="#" class="text-decoration-none"><i class="bi bi-chevron-right me-1"></i>Cách đặt câu hỏi hay</a></li>
                        <li class="mb-2"><a href="#" class="text-decoration-none"><i class="bi bi-chevron-right me-1"></i>Quy tắc cộng đồng</a></li>
                        <li><a href="#" class="text-decoration-none"><i class="bi bi-chevron-right me-1"></i>Liên hệ hỗ trợ</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</main>

<?php require_once 'includes/footer.php'; ?>
