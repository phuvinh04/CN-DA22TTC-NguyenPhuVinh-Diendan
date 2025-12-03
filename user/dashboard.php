<?php
$pageTitle = 'Trang cá nhân';
require_once '../config/database.php';
require_once '../config/session.php';
requireLogin();

$userId = $currentUser['id'];

// Debug - Kiểm tra user ID
// echo "User ID: " . $userId . "<br>";

// Thống kê cá nhân
$userStats = $conn->prepare("
    SELECT 
        (SELECT COUNT(*) FROM DAT WHERE MANGUOIDUNG = ?) as SoCauHoi,
        (SELECT COUNT(*) FROM TRALOI WHERE MANGUOIDUNG = ?) as SoTraLoi,
        (SELECT SUM(LUOTXEM) FROM CAUHOI ch JOIN DAT d ON ch.MACAUHOI = d.MACAUHOI WHERE d.MANGUOIDUNG = ?) as TongLuotXem,
        nd.DIEMDANHGIA
    FROM NGUOIDUNG nd WHERE MANGUOIDUNG = ?
");
$userStats->execute([$userId, $userId, $userId, $userId]);
$stats = $userStats->fetch();

// Câu hỏi của tôi
$myQuestions = $conn->prepare("
    SELECT ch.*, t.TENTHE, d.NGAYDANG,
           (SELECT COUNT(*) FROM TRALOI WHERE MACAUHOI = ch.MACAUHOI) as SoTraLoi
    FROM CAUHOI ch
    JOIN TAG t ON ch.MATHE = t.MATHE
    JOIN DAT d ON ch.MACAUHOI = d.MACAUHOI
    WHERE d.MANGUOIDUNG = ?
    ORDER BY d.NGAYDANG DESC
    LIMIT 5
");
$myQuestions->execute([$userId]);
$questions = $myQuestions->fetchAll();

// Câu trả lời của tôi
$myAnswers = $conn->prepare("
    SELECT tl.*, ch.TIEUDE, ch.MACAUHOI, tl.NGAYTL
    FROM TRALOI tl
    JOIN CAUHOI ch ON tl.MACAUHOI = ch.MACAUHOI
    WHERE tl.MANGUOIDUNG = ?
    ORDER BY tl.NGAYTL DESC
    LIMIT 5
");
$myAnswers->execute([$userId]);
$answers = $myAnswers->fetchAll();

require_once '../includes/header.php';
?>

<main class="py-4">
    <div class="container">
        <div class="row">
            <!-- Sidebar Profile -->
            <div class="col-lg-4 mb-4">
                <div class="user-profile-card slide-in-up">
                    <img src="<?php echo htmlspecialchars($currentUser['avatar']); ?>" class="user-profile-avatar" alt="Avatar">
                    <h3 class="user-profile-name"><?php echo htmlspecialchars($currentUser['fullname']); ?></h3>
                    <p class="text-muted mb-3">@<?php echo htmlspecialchars($currentUser['username']); ?></p>
                    <span class="user-profile-role"><?php echo ucfirst($currentUser['role']); ?></span>
                    
                    <div class="user-stats">
                        <div class="user-stat-item">
                            <div class="user-stat-number"><?php echo $stats['DIEMDANHGIA']; ?></div>
                            <div class="user-stat-label">Điểm</div>
                        </div>
                        <div class="user-stat-item">
                            <div class="user-stat-number"><?php echo $stats['SoCauHoi']; ?></div>
                            <div class="user-stat-label">Câu hỏi</div>
                        </div>
                        <div class="user-stat-item">
                            <div class="user-stat-number"><?php echo $stats['SoTraLoi']; ?></div>
                            <div class="user-stat-label">Trả lời</div>
                        </div>
                    </div>
                    
                    <div class="mt-4">
                        <a href="../profile.php" class="btn btn-primary w-100 mb-2">
                            <i class="bi bi-pencil me-2"></i>Chỉnh sửa hồ sơ
                        </a>
                        <a href="../ask-question.php" class="btn btn-outline-primary w-100">
                            <i class="bi bi-plus-circle me-2"></i>Đặt câu hỏi mới
                        </a>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-lg-8">
                <!-- Stats Cards -->
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <div class="card modern-card text-center">
                            <div class="card-body">
                                <i class="bi bi-question-circle-fill text-primary" style="font-size: 2.5rem;"></i>
                                <h3 class="mt-2 mb-0"><?php echo $stats['SoCauHoi']; ?></h3>
                                <p class="text-muted mb-0">Câu hỏi</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card modern-card text-center">
                            <div class="card-body">
                                <i class="bi bi-chat-left-text-fill text-success" style="font-size: 2.5rem;"></i>
                                <h3 class="mt-2 mb-0"><?php echo $stats['SoTraLoi']; ?></h3>
                                <p class="text-muted mb-0">Trả lời</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card modern-card text-center">
                            <div class="card-body">
                                <i class="bi bi-eye-fill text-info" style="font-size: 2.5rem;"></i>
                                <h3 class="mt-2 mb-0"><?php echo number_format($stats['TongLuotXem'] ?? 0); ?></h3>
                                <p class="text-muted mb-0">Lượt xem</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Câu hỏi của tôi -->
                <div class="card modern-card mb-4">
                    <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="bi bi-question-circle text-primary me-2"></i>Câu hỏi của tôi</h5>
                        <a href="../questions.php?user=<?php echo $userId; ?>" class="btn btn-sm btn-outline-primary">Xem tất cả</a>
                    </div>
                    <div class="card-body">
                        <?php if (empty($questions)): ?>
                            <p class="text-muted text-center py-4">Bạn chưa đặt câu hỏi nào</p>
                        <?php else: ?>
                            <?php foreach ($questions as $q): ?>
                            <div class="question-card-enhanced">
                                <a href="../question-detail.php?id=<?php echo $q['MACAUHOI']; ?>" class="question-title">
                                    <?php echo htmlspecialchars($q['TIEUDE']); ?>
                                </a>
                                <div class="question-meta">
                                    <div class="meta-item">
                                        <i class="bi bi-tag"></i>
                                        <span><?php echo htmlspecialchars($q['TENTHE']); ?></span>
                                    </div>
                                    <div class="meta-item">
                                        <i class="bi bi-chat"></i>
                                        <span><?php echo $q['SoTraLoi']; ?> trả lời</span>
                                    </div>
                                    <div class="meta-item">
                                        <i class="bi bi-eye"></i>
                                        <span><?php echo number_format($q['LUOTXEM']); ?> lượt xem</span>
                                    </div>
                                    <div class="meta-item">
                                        <i class="bi bi-clock"></i>
                                        <span><?php echo date('d/m/Y', strtotime($q['NGAYDANG'])); ?></span>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Câu trả lời của tôi -->
                <div class="card modern-card">
                    <div class="card-header bg-transparent border-0">
                        <h5 class="mb-0"><i class="bi bi-chat-left-text text-success me-2"></i>Câu trả lời gần đây</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($answers)): ?>
                            <p class="text-muted text-center py-4">Bạn chưa trả lời câu hỏi nào</p>
                        <?php else: ?>
                            <div class="list-group list-group-flush">
                                <?php foreach ($answers as $a): ?>
                                <a href="../question-detail.php?id=<?php echo $a['MACAUHOI']; ?>" class="list-group-item list-group-item-action border-0 px-0">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div class="flex-grow-1">
                                            <h6 class="mb-1"><?php echo htmlspecialchars($a['TIEUDE']); ?></h6>
                                            <p class="mb-1 text-muted small"><?php echo substr(strip_tags($a['NOIDUNGTL']), 0, 100); ?>...</p>
                                        </div>
                                        <small class="text-muted"><?php echo date('d/m/Y', strtotime($a['NGAYTL'])); ?></small>
                                    </div>
                                </a>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<?php require_once '../includes/footer.php'; ?>
