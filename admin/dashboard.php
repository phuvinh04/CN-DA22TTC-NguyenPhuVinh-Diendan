<?php
$pageTitle = 'Admin Dashboard';
require_once '../config/database.php';
require_once '../config/session.php';
requireAdmin();

// Thống kê tổng quan
$stats = [
    'users' => $conn->query("SELECT COUNT(*) FROM NGUOIDUNG WHERE TRANGTHAI = 'active'")->fetchColumn(),
    'questions' => $conn->query("SELECT COUNT(*) FROM CAUHOI")->fetchColumn(),
    'answers' => $conn->query("SELECT COUNT(*) FROM TRALOI")->fetchColumn(),
    'views' => $conn->query("SELECT SUM(LUOTXEM) FROM CAUHOI")->fetchColumn(),
    'new_users_today' => $conn->query("SELECT COUNT(*) FROM NGUOIDUNG WHERE DATE(NGAYTAO) = CURDATE()")->fetchColumn(),
    'new_questions_today' => $conn->query("SELECT COUNT(*) FROM DAT WHERE DATE(NGAYDANG) = CURDATE()")->fetchColumn()
];

// Người dùng mới nhất
$newUsers = $conn->query("SELECT MANGUOIDUNG, HOTEN, EMAIL, ANHDAIDIEN, NGAYTAO FROM NGUOIDUNG ORDER BY NGAYTAO DESC LIMIT 5")->fetchAll();

// Câu hỏi mới nhất
$newQuestions = $conn->query("
    SELECT ch.MACAUHOI, ch.TIEUDE, nd.HOTEN, d.NGAYDANG, t.TENTHE
    FROM CAUHOI ch
    JOIN DAT d ON ch.MACAUHOI = d.MACAUHOI
    JOIN NGUOIDUNG nd ON d.MANGUOIDUNG = nd.MANGUOIDUNG
    JOIN TAG t ON ch.MATHE = t.MATHE
    ORDER BY d.NGAYDANG DESC LIMIT 5
")->fetchAll();

require_once '../includes/admin_header.php';
?>

<div class="admin-container">
    <div class="container-fluid py-4">
        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="mb-1"><i class="bi bi-speedometer2 me-2"></i>Dashboard</h2>
                <p class="text-muted mb-0">Tổng quan hệ thống diễn đàn</p>
            </div>
            <div class="text-muted">
                <i class="bi bi-calendar3 me-2"></i><?php echo date('d/m/Y H:i'); ?>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="row g-4 mb-4">
            <div class="col-xl-3 col-md-6">
                <div class="stats-card-modern bg-gradient-primary">
                    <div class="stats-icon">
                        <i class="bi bi-people-fill"></i>
                    </div>
                    <div class="stats-content">
                        <div class="stats-number"><?php echo number_format($stats['users']); ?></div>
                        <div class="stats-label">Thành viên</div>
                        <div class="stats-badge">+<?php echo $stats['new_users_today']; ?> hôm nay</div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="stats-card-modern bg-gradient-success">
                    <div class="stats-icon">
                        <i class="bi bi-question-circle-fill"></i>
                    </div>
                    <div class="stats-content">
                        <div class="stats-number"><?php echo number_format($stats['questions']); ?></div>
                        <div class="stats-label">Câu hỏi</div>
                        <div class="stats-badge">+<?php echo $stats['new_questions_today']; ?> hôm nay</div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="stats-card-modern bg-gradient-info">
                    <div class="stats-icon">
                        <i class="bi bi-chat-left-text-fill"></i>
                    </div>
                    <div class="stats-content">
                        <div class="stats-number"><?php echo number_format($stats['answers']); ?></div>
                        <div class="stats-label">Câu trả lời</div>
                        <div class="stats-badge"><?php echo number_format($stats['answers'] / max($stats['questions'], 1), 1); ?> / câu hỏi</div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="stats-card-modern bg-gradient-warning">
                    <div class="stats-icon">
                        <i class="bi bi-eye-fill"></i>
                    </div>
                    <div class="stats-content">
                        <div class="stats-number"><?php echo number_format($stats['views']); ?></div>
                        <div class="stats-label">Lượt xem</div>
                        <div class="stats-badge"><?php echo number_format($stats['views'] / max($stats['questions'], 1)); ?> / câu hỏi</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <!-- Người dùng mới -->
            <div class="col-lg-6">
                <div class="card modern-card">
                    <div class="card-header bg-transparent border-0 pb-0">
                        <h5 class="mb-0"><i class="bi bi-person-plus-fill text-primary me-2"></i>Người dùng mới</h5>
                    </div>
                    <div class="card-body">
                        <div class="list-group list-group-flush">
                            <?php foreach ($newUsers as $user): ?>
                            <div class="list-group-item border-0 px-0 d-flex align-items-center">
                                <img src="<?php echo htmlspecialchars($user['ANHDAIDIEN']); ?>" class="user-avatar me-3" alt="Avatar">
                                <div class="flex-grow-1">
                                    <div class="fw-semibold"><?php echo htmlspecialchars($user['HOTEN']); ?></div>
                                    <small class="text-muted"><?php echo htmlspecialchars($user['EMAIL']); ?></small>
                                </div>
                                <small class="text-muted"><?php echo date('d/m H:i', strtotime($user['NGAYTAO'])); ?></small>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <a href="users.php" class="btn btn-outline-primary btn-sm w-100 mt-3">Xem tất cả</a>
                    </div>
                </div>
            </div>

            <!-- Câu hỏi mới -->
            <div class="col-lg-6">
                <div class="card modern-card">
                    <div class="card-header bg-transparent border-0 pb-0">
                        <h5 class="mb-0"><i class="bi bi-chat-dots-fill text-success me-2"></i>Câu hỏi mới</h5>
                    </div>
                    <div class="card-body">
                        <div class="list-group list-group-flush">
                            <?php foreach ($newQuestions as $q): ?>
                            <div class="list-group-item border-0 px-0">
                                <div class="d-flex justify-content-between align-items-start mb-1">
                                    <a href="../question-detail.php?id=<?php echo $q['MACAUHOI']; ?>" class="text-decoration-none text-dark fw-semibold flex-grow-1">
                                        <?php echo htmlspecialchars($q['TIEUDE']); ?>
                                    </a>
                                </div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <span class="tag-sm"><?php echo htmlspecialchars($q['TENTHE']); ?></span>
                                        <small class="text-muted ms-2">bởi <?php echo htmlspecialchars($q['HOTEN']); ?></small>
                                    </div>
                                    <small class="text-muted"><?php echo date('d/m H:i', strtotime($q['NGAYDANG'])); ?></small>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <a href="questions.php" class="btn btn-outline-success btn-sm w-100 mt-3">Xem tất cả</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/admin_footer.php'; ?>
