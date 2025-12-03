<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$pageTitle = 'Danh sách câu hỏi';
require_once 'config/database.php';
require_once 'includes/header.php';

// Phân trang
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 15;
$offset = ($page - 1) * $perPage;

// Lọc theo tag
$tagFilter = $_GET['tag'] ?? '';
$searchQuery = $_GET['q'] ?? '';

// Build query
$whereClause = "WHERE 1=1";
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
    nd.HOTEN AS NguoiDat,
    nd.ANHDAIDIEN,
    t.TENTHE AS Tag,
    t.MATHE,
    d.NGAYDANG,
    d.NOIDUNG,
    (SELECT COUNT(*) FROM TRALOI tl WHERE tl.MACAUHOI = ch.MACAUHOI) AS SoCauTraLoi
FROM CAUHOI ch
INNER JOIN DAT d ON ch.MACAUHOI = d.MACAUHOI
INNER JOIN NGUOIDUNG nd ON d.MANGUOIDUNG = nd.MANGUOIDUNG
INNER JOIN TAG t ON ch.MATHE = t.MATHE
$whereClause
ORDER BY d.NGAYDANG DESC
LIMIT $perPage OFFSET $offset";

$stmt = $conn->prepare($questionsQuery);
$stmt->execute($params);
$questions = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Lấy tất cả tags cho filter
$tagsQuery = "SELECT MATHE, TENTHE FROM TAG ORDER BY TENTHE";
$allTags = $conn->query($tagsQuery)->fetchAll(PDO::FETCH_ASSOC);
?>

<main class="py-4">
    <div class="container">
        <div class="row mb-4">
            <div class="col-md-8">
                <h2><i class="bi bi-question-circle-fill text-primary me-2"></i>Tất cả câu hỏi</h2>
                <p class="text-muted">Tìm thấy <?php echo number_format($totalQuestions); ?> câu hỏi</p>
            </div>
            <div class="col-md-4 text-end">
                <?php if ($currentUser): ?>
                <a href="ask-question.php" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-2"></i>Đặt câu hỏi
                </a>
                <?php endif; ?>
            </div>
        </div>

        <div class="row">
            <!-- Filters -->
            <div class="col-lg-3 mb-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title mb-3">Bộ lọc</h5>
                        
                        <!-- Search -->
                        <form method="GET" action="" class="mb-3">
                            <div class="input-group input-group-sm">
                                <input type="text" class="form-control" name="q" placeholder="Tìm kiếm..." value="<?php echo htmlspecialchars($searchQuery); ?>">
                                <button class="btn btn-primary" type="submit">
                                    <i class="bi bi-search"></i>
                                </button>
                            </div>
                        </form>

                        <!-- Tags Filter -->
                        <h6 class="mb-2">Lọc theo Tag</h6>
                        <div class="list-group list-group-flush">
                            <a href="questions.php" class="list-group-item list-group-item-action border-0 px-0 <?php echo !$tagFilter ? 'active' : ''; ?>">
                                Tất cả
                            </a>
                            <?php foreach ($allTags as $tag): ?>
                            <a href="questions.php?tag=<?php echo $tag['MATHE']; ?>" class="list-group-item list-group-item-action border-0 px-0 <?php echo $tagFilter === $tag['MATHE'] ? 'active' : ''; ?>">
                                <?php echo htmlspecialchars($tag['TENTHE']); ?>
                            </a>
                            <?php endforeach; ?>
                        </div>
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
                                        <img src="<?php echo htmlspecialchars($question['ANHDAIDIEN']); ?>" alt="Avatar" class="user-avatar-sm me-2">
                                        <span><?php echo htmlspecialchars($question['NguoiDat']); ?></span>
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
