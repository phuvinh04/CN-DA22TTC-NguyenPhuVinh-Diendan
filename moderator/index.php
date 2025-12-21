<?php
$pageTitle = 'Quản lý nội dung - Moderator';
require_once '../config/database.php';
require_once '../config/session.php';

requireLogin();
$currentUser = getCurrentUser();

// Chỉ moderator và admin mới vào được
if ($currentUser['role'] !== 'moderator' && $currentUser['role'] !== 'admin') {
    header('Location: ../index.php');
    exit();
}

// Kiểm tra cột TRANGTHAI trong TRALOI
$hasAnswerStatus = false;
try {
    $checkColumn = $conn->query("SHOW COLUMNS FROM TRALOI LIKE 'TRANGTHAI'");
    $hasAnswerStatus = $checkColumn->rowCount() > 0;
} catch (Exception $e) {}

// Xử lý duyệt câu hỏi
if (isset($_GET['approve'])) {
    $questionId = $_GET['approve'];
    $conn->prepare("UPDATE CAUHOI SET TRANGTHAI = 'open' WHERE MACAUHOI = ?")->execute([$questionId]);
    
    $stmt = $conn->prepare("SELECT MANGUOIDUNG FROM DAT WHERE MACAUHOI = ?");
    $stmt->execute([$questionId]);
    $ownerId = $stmt->fetchColumn();
    if ($ownerId) {
        $notifId = 'TB' . time() . rand(100, 999);
        $conn->prepare("INSERT INTO THONGBAO (MATHONGBAO, MANGUOIDUNG, LOAI, TIEUDE, LINK, DADOC, NGAYTAO) VALUES (?, ?, 'system', ?, ?, 0, NOW())")
            ->execute([$notifId, $ownerId, 'Câu hỏi của bạn đã được duyệt!', 'question-detail.php?id=' . $questionId]);
    }
    header('Location: index.php?msg=approved');
    exit();
}

// Xử lý từ chối câu hỏi
if (isset($_GET['reject'])) {
    $questionId = $_GET['reject'];
    $conn->prepare("UPDATE CAUHOI SET TRANGTHAI = 'rejected' WHERE MACAUHOI = ?")->execute([$questionId]);
    
    $stmt = $conn->prepare("SELECT MANGUOIDUNG FROM DAT WHERE MACAUHOI = ?");
    $stmt->execute([$questionId]);
    $ownerId = $stmt->fetchColumn();
    if ($ownerId) {
        $notifId = 'TB' . time() . rand(100, 999);
        $conn->prepare("INSERT INTO THONGBAO (MATHONGBAO, MANGUOIDUNG, LOAI, TIEUDE, LINK, DADOC, NGAYTAO) VALUES (?, ?, 'system', ?, ?, 0, NOW())")
            ->execute([$notifId, $ownerId, 'Câu hỏi của bạn không được duyệt.', '']);
    }
    header('Location: index.php?msg=rejected');
    exit();
}

// Xử lý duyệt câu trả lời
if (isset($_GET['approve_answer']) && $hasAnswerStatus) {
    $answerId = $_GET['approve_answer'];
    $conn->prepare("UPDATE TRALOI SET TRANGTHAI = 'approved' WHERE MACAUTRALOI = ?")->execute([$answerId]);
    
    $stmt = $conn->prepare("SELECT MANGUOIDUNG, MACAUHOI FROM TRALOI WHERE MACAUTRALOI = ?");
    $stmt->execute([$answerId]);
    $answerInfo = $stmt->fetch();
    if ($answerInfo) {
        $notifId = 'TB' . time() . rand(100, 999);
        $conn->prepare("INSERT INTO THONGBAO (MATHONGBAO, MANGUOIDUNG, LOAI, TIEUDE, LINK, DADOC, NGAYTAO) VALUES (?, ?, 'system', ?, ?, 0, NOW())")
            ->execute([$notifId, $answerInfo['MANGUOIDUNG'], 'Câu trả lời của bạn đã được duyệt!', 'question-detail.php?id=' . $answerInfo['MACAUHOI']]);
    }
    header('Location: index.php?msg=answer_approved');
    exit();
}

// Xử lý từ chối câu trả lời
if (isset($_GET['reject_answer']) && $hasAnswerStatus) {
    $answerId = $_GET['reject_answer'];
    $conn->prepare("UPDATE TRALOI SET TRANGTHAI = 'rejected' WHERE MACAUTRALOI = ?")->execute([$answerId]);
    
    $stmt = $conn->prepare("SELECT MANGUOIDUNG FROM TRALOI WHERE MACAUTRALOI = ?");
    $stmt->execute([$answerId]);
    $ownerId = $stmt->fetchColumn();
    if ($ownerId) {
        $notifId = 'TB' . time() . rand(100, 999);
        $conn->prepare("INSERT INTO THONGBAO (MATHONGBAO, MANGUOIDUNG, LOAI, TIEUDE, LINK, DADOC, NGAYTAO) VALUES (?, ?, 'system', ?, ?, 0, NOW())")
            ->execute([$notifId, $ownerId, 'Câu trả lời của bạn không được duyệt.', '']);
    }
    header('Location: index.php?msg=answer_rejected');
    exit();
}

// Lấy câu hỏi chờ duyệt
$pendingQuestions = $conn->query("
    SELECT ch.*, t.TENTHE, nd.HOTEN, d.NGAYDANG, d.NOIDUNG
    FROM CAUHOI ch
    JOIN TAG t ON ch.MATHE = t.MATHE
    JOIN DAT d ON ch.MACAUHOI = d.MACAUHOI
    JOIN NGUOIDUNG nd ON d.MANGUOIDUNG = nd.MANGUOIDUNG
    WHERE ch.TRANGTHAI = 'pending'
    ORDER BY d.NGAYDANG DESC
")->fetchAll();

$pendingQuestionCount = count($pendingQuestions);

// Lấy câu trả lời chờ duyệt
$pendingAnswers = [];
$pendingAnswerCount = 0;
if ($hasAnswerStatus) {
    $pendingAnswers = $conn->query("
        SELECT tl.MACAUTRALOI, tl.NOIDUNGTL, tl.NGAYTL, tl.MACAUHOI,
               nd.HOTEN, ch.TIEUDE
        FROM TRALOI tl
        JOIN NGUOIDUNG nd ON tl.MANGUOIDUNG = nd.MANGUOIDUNG
        JOIN CAUHOI ch ON tl.MACAUHOI = ch.MACAUHOI
        WHERE tl.TRANGTHAI = 'pending'
        ORDER BY tl.NGAYTL DESC
    ")->fetchAll();
    $pendingAnswerCount = count($pendingAnswers);
}

$totalPending = $pendingQuestionCount + $pendingAnswerCount;

require_once '../includes/header.php';
?>

<main class="py-4">
    <div class="container">
        <div class="row mb-4">
            <div class="col-12">
                <div class="card bg-warning text-dark">
                    <div class="card-body d-flex align-items-center">
                        <i class="bi bi-shield-check fs-1 me-3"></i>
                        <div>
                            <h4 class="mb-1">Trang Moderator</h4>
                            <p class="mb-0">Xin chào <?php echo htmlspecialchars($currentUser['fullname']); ?>! Bạn có <?php echo $totalPending; ?> nội dung cần duyệt.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php if (isset($_GET['msg'])): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <i class="bi bi-check-circle me-2"></i>
            <?php 
            if ($_GET['msg'] === 'approved') echo 'Đã duyệt câu hỏi!';
            elseif ($_GET['msg'] === 'rejected') echo 'Đã từ chối câu hỏi!';
            elseif ($_GET['msg'] === 'answer_approved') echo 'Đã duyệt câu trả lời!';
            elseif ($_GET['msg'] === 'answer_rejected') echo 'Đã từ chối câu trả lời!';
            ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <!-- Thống kê nhanh -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card border-warning">
                    <div class="card-body text-center">
                        <h3 class="text-warning"><?php echo $pendingQuestionCount; ?></h3>
                        <p class="mb-0 text-muted">Câu hỏi chờ duyệt</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card border-info">
                    <div class="card-body text-center">
                        <h3 class="text-info"><?php echo $pendingAnswerCount; ?></h3>
                        <p class="mb-0 text-muted">Câu trả lời chờ duyệt</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Câu hỏi chờ duyệt -->
        <div class="card mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-question-circle me-2 text-warning"></i>Câu hỏi chờ duyệt (<?php echo $pendingQuestionCount; ?>)</h5>
            </div>
            <div class="card-body p-0">
                <?php if (empty($pendingQuestions)): ?>
                    <div class="text-center py-4">
                        <i class="bi bi-check-circle text-success" style="font-size: 3rem;"></i>
                        <p class="mt-2 mb-0 text-muted">Không có câu hỏi nào cần duyệt</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($pendingQuestions as $q): ?>
                    <div class="border-bottom p-3">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="flex-grow-1">
                                <h6 class="mb-1"><?php echo htmlspecialchars($q['TIEUDE']); ?></h6>
                                <p class="text-muted small mb-2"><?php echo mb_substr(strip_tags($q['NOIDUNG']), 0, 200); ?>...</p>
                                <div class="d-flex align-items-center gap-3 small">
                                    <span class="badge bg-secondary"><?php echo htmlspecialchars($q['TENTHE']); ?></span>
                                    <span><i class="bi bi-person me-1"></i><?php echo htmlspecialchars($q['HOTEN']); ?></span>
                                    <span><i class="bi bi-clock me-1"></i><?php echo date('d/m/Y H:i', strtotime($q['NGAYDANG'])); ?></span>
                                </div>
                            </div>
                            <div class="ms-3 d-flex gap-2">
                                <a href="?approve=<?php echo $q['MACAUHOI']; ?>" class="btn btn-success btn-sm" onclick="return confirm('Duyệt câu hỏi này?')">
                                    <i class="bi bi-check-lg"></i> Duyệt
                                </a>
                                <a href="?reject=<?php echo $q['MACAUHOI']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Từ chối câu hỏi này?')">
                                    <i class="bi bi-x-lg"></i> Từ chối
                                </a>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Câu trả lời chờ duyệt -->
        <div class="card">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-chat-left-text me-2 text-info"></i>Câu trả lời chờ duyệt (<?php echo $pendingAnswerCount; ?>)</h5>
            </div>
            <div class="card-body p-0">
                <?php if (empty($pendingAnswers)): ?>
                    <div class="text-center py-4">
                        <i class="bi bi-check-circle text-success" style="font-size: 3rem;"></i>
                        <p class="mt-2 mb-0 text-muted">Không có câu trả lời nào cần duyệt</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($pendingAnswers as $a): ?>
                    <div class="border-bottom p-3">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="flex-grow-1">
                                <p class="mb-1"><?php echo htmlspecialchars(mb_substr($a['NOIDUNGTL'], 0, 200)); ?>...</p>
                                <div class="d-flex align-items-center gap-3 small text-muted">
                                    <span><i class="bi bi-chat-quote me-1"></i>Trả lời: <a href="../question-detail.php?id=<?php echo $a['MACAUHOI']; ?>" target="_blank"><?php echo htmlspecialchars(mb_substr($a['TIEUDE'], 0, 50)); ?>...</a></span>
                                    <span><i class="bi bi-person me-1"></i><?php echo htmlspecialchars($a['HOTEN']); ?></span>
                                    <span><i class="bi bi-clock me-1"></i><?php echo date('d/m/Y H:i', strtotime($a['NGAYTL'])); ?></span>
                                </div>
                            </div>
                            <div class="ms-3 d-flex gap-2">
                                <a href="?approve_answer=<?php echo $a['MACAUTRALOI']; ?>" class="btn btn-success btn-sm" onclick="return confirm('Duyệt câu trả lời này?')">
                                    <i class="bi bi-check-lg"></i> Duyệt
                                </a>
                                <a href="?reject_answer=<?php echo $a['MACAUTRALOI']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Từ chối câu trả lời này?')">
                                    <i class="bi bi-x-lg"></i> Từ chối
                                </a>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</main>

<?php require_once '../includes/footer.php'; ?>
