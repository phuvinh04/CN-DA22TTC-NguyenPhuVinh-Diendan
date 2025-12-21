<?php
$pageTitle = 'Câu trả lời của tôi';
require_once '../config/database.php';
require_once '../config/session.php';
requireLogin();

$currentUser = getCurrentUser();
$userId = $currentUser['id'];

// Phân trang
$page = max(1, intval($_GET['page'] ?? 1));
$perPage = 10;
$offset = ($page - 1) * $perPage;

// Lấy tổng số câu trả lời
$stmt = $conn->prepare("SELECT COUNT(*) FROM TRALOI WHERE MANGUOIDUNG = ?");
$stmt->execute([$userId]);
$totalAnswers = $stmt->fetchColumn();
$totalPages = ceil($totalAnswers / $perPage);

// Lấy danh sách câu trả lời
$stmt = $conn->prepare("
    SELECT tl.*, ch.TIEUDE, ch.MACAUHOI
    FROM TRALOI tl
    JOIN CAUHOI ch ON tl.MACAUHOI = ch.MACAUHOI
    WHERE tl.MANGUOIDUNG = ?
    ORDER BY tl.NGAYTL DESC
    LIMIT " . (int)$perPage . " OFFSET " . (int)$offset . "
");
$stmt->execute([$userId]);
$answers = $stmt->fetchAll(PDO::FETCH_ASSOC);

require_once '../includes/header.php';
?>

<main class="py-4">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2><i class="bi bi-chat-left-text-fill text-success me-2"></i>Câu trả lời của tôi</h2>
                <p class="text-muted mb-0">Tổng cộng <?php echo $totalAnswers; ?> câu trả lời</p>
            </div>
            <a href="../questions.php" class="btn btn-primary">
                <i class="bi bi-search me-2"></i>Tìm câu hỏi để trả lời
            </a>
        </div>

        <?php if (empty($answers)): ?>
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="bi bi-chat-left text-muted" style="font-size: 4rem;"></i>
                <h4 class="mt-3">Bạn chưa trả lời câu hỏi nào</h4>
                <p class="text-muted">Hãy giúp đỡ cộng đồng bằng cách trả lời câu hỏi!</p>
                <a href="../questions.php" class="btn btn-primary">
                    <i class="bi bi-search me-2"></i>Tìm câu hỏi
                </a>
            </div>
        </div>
        <?php else: ?>
            <?php foreach ($answers as $a): ?>
            <div class="card mb-3" id="answer-<?php echo $a['MACAUTRALOI']; ?>">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="flex-grow-1">
                            <h6 class="mb-2">
                                <a href="../question-detail.php?id=<?php echo $a['MACAUHOI']; ?>" class="text-decoration-none">
                                    <i class="bi bi-chat-quote me-2 text-primary"></i>
                                    <?php echo htmlspecialchars($a['TIEUDE']); ?>
                                </a>
                            </h6>
                            <p class="text-muted mb-2"><?php echo mb_substr(strip_tags($a['NOIDUNGTL']), 0, 200); ?>...</p>
                            <small class="text-muted">
                                <i class="bi bi-clock me-1"></i>Trả lời lúc <?php echo date('d/m/Y H:i', strtotime($a['NGAYTL'])); ?>
                            </small>
                        </div>
                        <div class="d-flex gap-1">
                            <a href="edit-answer.php?id=<?php echo $a['MACAUTRALOI']; ?>" class="btn btn-sm btn-outline-primary" title="Sửa câu trả lời">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteAnswer('<?php echo $a['MACAUTRALOI']; ?>')" title="Xóa câu trả lời">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>

            <script>
            function deleteAnswer(answerId) {
                Confirm.show({
                    title: 'Xóa câu trả lời',
                    message: 'Bạn có chắc muốn xóa câu trả lời này? Bạn sẽ bị trừ 10 điểm.',
                    confirmText: 'Xóa',
                    type: 'danger',
                    onConfirm: () => {
                        fetch('../api/delete-answer.php', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({ answer_id: answerId })
                        })
                        .then(r => r.json())
                        .then(data => {
                            if (data.success) {
                                document.getElementById('answer-' + answerId).remove();
                                Toast.success(data.message);
                            } else {
                                Toast.error(data.message);
                            }
                        })
                        .catch(() => Toast.error('Có lỗi xảy ra'));
                    }
                });
            }
            </script>

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

<?php require_once '../includes/footer.php'; ?>
