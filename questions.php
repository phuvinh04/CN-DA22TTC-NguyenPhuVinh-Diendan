<?php
$pageTitle = 'Danh sách câu hỏi';
require_once 'config/database.php';
require_once 'config/session.php';
require_once 'includes/badge_helper.php';

// Yêu cầu đăng nhập
$currentUser = getCurrentUser();
if (!$currentUser) {
    header('Location: login.php');
    exit();
}

require_once 'includes/header.php';

// Phân trang
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 15;
$offset = ($page - 1) * $perPage;

// Lọc và sắp xếp
$tagFilter = $_GET['tag'] ?? '';
$searchQuery = $_GET['q'] ?? '';
$sortBy = $_GET['sort'] ?? 'newest';

// Xác định ORDER BY
$orderBy = match($sortBy) {
    'oldest' => 'd.NGAYDANG ASC',
    'most_answers' => 'SoCauTraLoi DESC, d.NGAYDANG DESC',
    'most_views' => 'ch.LUOTXEM DESC, d.NGAYDANG DESC',
    'unanswered' => 'SoCauTraLoi ASC, d.NGAYDANG DESC',
    default => 'd.NGAYDANG DESC'
};

// Build query - chỉ hiện câu hỏi đã duyệt (open hoặc closed)
$whereClause = "WHERE ch.TRANGTHAI IN ('open', 'closed')";
$params = [];

if ($tagFilter) {
    $whereClause .= " AND t.MATHE = ?";
    $params[] = $tagFilter;
}

if ($searchQuery) {
    $whereClause .= " AND (ch.TIEUDE LIKE ? OR d.NOIDUNG LIKE ?)";
    $params[] = "%$searchQuery%";
    $params[] = "%$searchQuery%";
}

// Đếm tổng số câu hỏi
$countQuery = "SELECT COUNT(*) FROM CAUHOI ch
    INNER JOIN DAT d ON ch.MACAUHOI = d.MACAUHOI
    INNER JOIN TAG t ON ch.MATHE = t.MATHE
    $whereClause";
$stmt = $conn->prepare($countQuery);
$stmt->execute($params);
$totalQuestions = $stmt->fetchColumn();
$totalPages = ceil($totalQuestions / $perPage);

// Lấy danh sách câu hỏi
$questionsQuery = "SELECT 
    ch.MACAUHOI,
    ch.TIEUDE,
    ch.LUOTXEM,
    ch.TRANGTHAI,
    nd.MANGUOIDUNG,
    nd.HOTEN AS NguoiDat,
    nd.ANHDAIDIEN,
    t.TENTHE AS Tag,
    t.MATHE,
    d.NGAYDANG,
    d.NOIDUNG,
    (SELECT COUNT(*) FROM TRALOI tl WHERE tl.MACAUHOI = ch.MACAUHOI AND (tl.TRANGTHAI = 'approved' OR tl.TRANGTHAI IS NULL)) AS SoCauTraLoi
FROM CAUHOI ch
INNER JOIN DAT d ON ch.MACAUHOI = d.MACAUHOI
INNER JOIN NGUOIDUNG nd ON d.MANGUOIDUNG = nd.MANGUOIDUNG
INNER JOIN TAG t ON ch.MATHE = t.MATHE
$whereClause
ORDER BY $orderBy
LIMIT $perPage OFFSET $offset";

$stmt = $conn->prepare($questionsQuery);
$stmt->execute($params);
$questions = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Lấy tất cả tags cho filter
$tagsQuery = "SELECT MATHE, TENTHE FROM TAG ORDER BY TENTHE";
$allTags = $conn->query($tagsQuery)->fetchAll(PDO::FETCH_ASSOC);
?>

<main class="py-5">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="mb-1">Tất cả câu hỏi</h2>
                <p class="text-muted mb-0">Tìm thấy <?php echo number_format($totalQuestions); ?> câu hỏi</p>
            </div>
            <?php if ($currentUser): ?>
            <a href="ask-question.php" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Đặt câu hỏi
            </a>
            <?php endif; ?>
        </div>

        <div class="row">
            <!-- Filters -->
            <div class="col-lg-3 mb-4">
                <div class="sidebar-widget">
                    <h5><i class="bi bi-funnel"></i> Bộ lọc</h5>
                    
                    <!-- Search -->
                    <form method="GET" action="" class="mb-4">
                        <div class="input-group">
                            <input type="text" class="form-control" name="q" placeholder="Tìm kiếm..." value="<?php echo htmlspecialchars($searchQuery); ?>">
                            <button class="btn btn-primary" type="submit">
                                <i class="bi bi-search"></i>
                            </button>
                        </div>
                        <?php if ($tagFilter): ?>
                        <input type="hidden" name="tag" value="<?php echo htmlspecialchars($tagFilter); ?>">
                        <?php endif; ?>
                        <?php if ($sortBy !== 'newest'): ?>
                        <input type="hidden" name="sort" value="<?php echo htmlspecialchars($sortBy); ?>">
                        <?php endif; ?>
                    </form>

                    <!-- Sort Options -->
                    <h6 class="mb-3" style="font-size: var(--font-sm); color: var(--gray-500);">SẮP XẾP</h6>
                    <div class="d-flex flex-column gap-1 mb-4">
                        <a href="?sort=newest<?php echo $tagFilter ? '&tag='.$tagFilter : ''; ?><?php echo $searchQuery ? '&q='.urlencode($searchQuery) : ''; ?>" 
                           class="btn btn-sm <?php echo $sortBy === 'newest' ? 'btn-primary' : 'btn-outline-secondary'; ?>">
                            <i class="bi bi-clock me-1"></i>Mới nhất
                        </a>
                        <a href="?sort=oldest<?php echo $tagFilter ? '&tag='.$tagFilter : ''; ?><?php echo $searchQuery ? '&q='.urlencode($searchQuery) : ''; ?>" 
                           class="btn btn-sm <?php echo $sortBy === 'oldest' ? 'btn-primary' : 'btn-outline-secondary'; ?>">
                            <i class="bi bi-clock-history me-1"></i>Cũ nhất
                        </a>
                        <a href="?sort=most_answers<?php echo $tagFilter ? '&tag='.$tagFilter : ''; ?><?php echo $searchQuery ? '&q='.urlencode($searchQuery) : ''; ?>" 
                           class="btn btn-sm <?php echo $sortBy === 'most_answers' ? 'btn-primary' : 'btn-outline-secondary'; ?>">
                            <i class="bi bi-chat-dots me-1"></i>Nhiều trả lời
                        </a>
                        <a href="?sort=most_views<?php echo $tagFilter ? '&tag='.$tagFilter : ''; ?><?php echo $searchQuery ? '&q='.urlencode($searchQuery) : ''; ?>" 
                           class="btn btn-sm <?php echo $sortBy === 'most_views' ? 'btn-primary' : 'btn-outline-secondary'; ?>">
                            <i class="bi bi-eye me-1"></i>Nhiều lượt xem
                        </a>
                        <a href="?sort=unanswered<?php echo $tagFilter ? '&tag='.$tagFilter : ''; ?><?php echo $searchQuery ? '&q='.urlencode($searchQuery) : ''; ?>" 
                           class="btn btn-sm <?php echo $sortBy === 'unanswered' ? 'btn-primary' : 'btn-outline-secondary'; ?>">
                            <i class="bi bi-question-circle me-1"></i>Chưa trả lời
                        </a>
                    </div>

                    <!-- Tags Filter -->
                    <h6 class="mb-3" style="font-size: var(--font-sm); color: var(--gray-500);">LỌC THEO TAG</h6>
                    <div class="d-flex flex-column gap-1">
                        <a href="questions.php" class="tag <?php echo !$tagFilter ? 'active' : ''; ?>" style="<?php echo !$tagFilter ? 'background: var(--primary-600); color: white;' : ''; ?>">
                            Tất cả
                        </a>
                        <?php foreach ($allTags as $tag): ?>
                        <a href="questions.php?tag=<?php echo $tag['MATHE']; ?>" class="tag <?php echo $tagFilter === $tag['MATHE'] ? 'active' : ''; ?>" style="<?php echo $tagFilter === $tag['MATHE'] ? 'background: var(--primary-600); color: white;' : ''; ?>">
                            <?php echo htmlspecialchars($tag['TENTHE']); ?>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Questions List -->
            <div class="col-lg-9">
                <?php if (empty($questions)): ?>
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="bi bi-inbox text-muted" style="font-size: 4rem;"></i>
                        <h4 class="mt-3">Không tìm thấy câu hỏi nào</h4>
                        <p class="text-muted">Hãy thử tìm kiếm với từ khóa khác hoặc đặt câu hỏi mới</p>
                    </div>
                </div>
                <?php else: ?>
                    <?php foreach ($questions as $question): ?>
                    <div class="card question-card mb-3">
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
                                <p class="text-muted mb-2">
                                    <?php echo mb_substr(strip_tags($question['NOIDUNG']), 0, 150); ?>...
                                </p>
                                <div class="d-flex align-items-center justify-content-between">
                                    <div>
                                        <a href="questions.php?tag=<?php echo $question['MATHE']; ?>" class="tag">
                                            <?php echo htmlspecialchars($question['Tag']); ?>
                                        </a>
                                    </div>
                                    <div class="d-flex align-items-center text-muted small">
                                        <?php echo renderAvatarWithFrame($question['ANHDAIDIEN'], $question['MANGUOIDUNG'], 'sm', false); ?>
                                        <span class="ms-2"><?php echo htmlspecialchars($question['NguoiDat']); ?></span>
                                        <span class="ms-2"><?php echo date('d/m/Y H:i', strtotime($question['NGAYDANG'])); ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>

                    <!-- Pagination -->
                    <?php if ($totalPages > 1): ?>
                    <nav aria-label="Page navigation">
                        <ul class="pagination justify-content-center">
                            <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $page - 1; ?><?php echo $tagFilter ? '&tag=' . $tagFilter : ''; ?><?php echo $searchQuery ? '&q=' . urlencode($searchQuery) : ''; ?>">Trước</a>
                            </li>
                            
                            <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                            <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $i; ?><?php echo $tagFilter ? '&tag=' . $tagFilter : ''; ?><?php echo $searchQuery ? '&q=' . urlencode($searchQuery) : ''; ?>"><?php echo $i; ?></a>
                            </li>
                            <?php endfor; ?>
                            
                            <li class="page-item <?php echo $page >= $totalPages ? 'disabled' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $page + 1; ?><?php echo $tagFilter ? '&tag=' . $tagFilter : ''; ?><?php echo $searchQuery ? '&q=' . urlencode($searchQuery) : ''; ?>">Sau</a>
                            </li>
                        </ul>
                    </nav>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</main>

<?php require_once 'includes/footer.php'; ?>
