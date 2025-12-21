<?php
$pageTitle = 'Câu hỏi của tôi';
require_once '../config/database.php';
require_once '../config/session.php';
requireLogin();

$currentUser = getCurrentUser();
$userId = $currentUser['id'];

// Phân trang
$page = max(1, intval($_GET['page'] ?? 1));
$perPage = 10;
$offset = ($page - 1) * $perPage;

// Lấy tổng số câu hỏi
$stmt = $conn->prepare("SELECT COUNT(*) FROM DAT WHERE MANGUOIDUNG = ?");
$stmt->execute([$userId]);
$totalQuestions = $stmt->fetchColumn();
$totalPages = ceil($totalQuestions / $perPage);

// Lấy danh sách câu hỏi
$stmt = $conn->prepare("
    SELECT ch.*, t.TENTHE, d.NGAYDANG, d.NOIDUNG,
           (SELECT COUNT(*) FROM TRALOI WHERE MACAUHOI = ch.MACAUHOI) as SoTraLoi
    FROM CAUHOI ch
    JOIN TAG t ON ch.MATHE = t.MATHE
    JOIN DAT d ON ch.MACAUHOI = d.MACAUHOI
    WHERE d.MANGUOIDUNG = ?
    ORDER BY d.NGAYDANG DESC
    LIMIT " . (int)$perPage . " OFFSET " . (int)$offset . "
");
$stmt->execute([$userId]);
$questions = $stmt->fetchAll(PDO::FETCH_ASSOC);

require_once '../includes/header.php';
?>

<main class="py-4">
    <div class="container">
        <?php if (isset($_GET['msg'])): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <i class="bi bi-check-circle me-2"></i>
            <?php 
            if ($_GET['msg'] === 'deleted') echo 'Đã xóa câu hỏi thành công!';
            else echo 'Thao tác thành công!';
            ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="bi bi-exclamation-triangle me-2"></i>
            <?php 
            $err = $_GET['error'];
            if ($err === 'permission') echo 'Bạn không có quyền thực hiện thao tác này!';
            elseif ($err === 'has_answers') echo 'Không thể xóa câu hỏi đã có câu trả lời!';
            elseif ($err === 'cannot_edit') echo 'Chỉ có thể sửa câu hỏi đang chờ duyệt hoặc bị từ chối!';
            else echo 'Có lỗi xảy ra!';
            ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2><i class="bi bi-question-circle-fill text-primary me-2"></i>Câu hỏi của tôi</h2>
                <p class="text-muted mb-0">Tổng cộng <?php echo $totalQuestions; ?> câu hỏi</p>
            </div>
            <a href="../ask-question.php" class="btn btn-primary">
                <i class="bi bi-plus-circle me-2"></i>Đặt câu hỏi mới
            </a>
        </div>

        <?php if (empty($questions)): ?>
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="bi bi-inbox text-muted" style="font-size: 4rem;"></i>
                <h4 class="mt-3">Bạn chưa đặt câu hỏi nào</h4>
                <p class="text-muted">Hãy đặt câu hỏi đầu tiên của bạn!</p>
                <a href="../ask-question.php" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-2"></i>Đặt câu hỏi ngay
                </a>
            </div>
        </div>
        <?php else: ?>
            <?php foreach ($questions as $q): ?>
            <div class="card question-card mb-3">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="flex-grow-1">
                            <h5 class="mb-2">
                                <a href="../question-detail.php?id=<?php echo $q['MACAUHOI']; ?>" class="text-decoration-none">
                                    <?php echo htmlspecialchars($q['TIEUDE']); ?>
                                </a>
                            </h5>
                            <p class="text-muted mb-2"><?php echo mb_substr(strip_tags($q['NOIDUNG']), 0, 150); ?>...</p>
                            <div class="d-flex align-items-center gap-3">
                                <span class="tag"><?php echo htmlspecialchars($q['TENTHE']); ?></span>
                                <small class="text-muted">
                                    <i class="bi bi-chat me-1"></i><?php echo $q['SoTraLoi']; ?> trả lời
                                </small>
                                <small class="text-muted">
                                    <i class="bi bi-eye me-1"></i><?php echo number_format($q['LUOTXEM']); ?> lượt xem
                                </small>
                                <small class="text-muted">
                                    <i class="bi bi-clock me-1"></i><?php echo date('d/m/Y H:i', strtotime($q['NGAYDANG'])); ?>
                                </small>
                            </div>
                        </div>
                        <div class="ms-3 d-flex flex-column align-items-end gap-2">
                            <?php if ($q['TRANGTHAI'] == 'pending'): ?>
                                <span class="badge bg-warning text-dark">Chờ duyệt</span>
                            <?php elseif ($q['TRANGTHAI'] == 'open'): ?>
                                <span class="badge bg-success">Đã duyệt</span>
                            <?php elseif ($q['TRANGTHAI'] == 'rejected'): ?>
                                <span class="badge bg-danger">Bị từ chối</span>
                            <?php else: ?>
                                <span class="badge bg-secondary">Đã đóng</span>
                            <?php endif; ?>
                            
                            <div class="btn-group btn-group-sm">
                                <?php if (in_array($q['TRANGTHAI'], ['pending', 'rejected'])): ?>
                                <a href="edit-question.php?id=<?php echo $q['MACAUHOI']; ?>" class="btn btn-outline-primary" title="Sửa">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <?php endif; ?>
                                <?php if ($q['SoTraLoi'] == 0): ?>
                                <button type="button" class="btn btn-outline-danger" title="Xóa" onclick="deleteQuestion('<?php echo $q['MACAUHOI']; ?>')">
                                    <i class="bi bi-trash"></i>
                                </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>

            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
            <nav>
                <ul class="pagination justify-content-center">
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                    </li>
                    <?php endfor; ?>
                </ul>
            </nav>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</main>

<script>
function deleteQuestion(questionId) {
    Confirm.show({
        title: 'Xóa câu hỏi',
        message: 'Bạn có chắc muốn xóa câu hỏi này? Bạn sẽ bị trừ 5 điểm.',
        confirmText: 'Xóa',
        type: 'danger',
        onConfirm: () => {
            window.location.href = 'delete-question.php?id=' + questionId;
        }
    });
}
</script>

<?php require_once '../includes/footer.php'; ?>
