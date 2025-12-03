<?php
$pageTitle = 'Tìm kiếm';
require_once 'config/database.php';
require_once 'includes/header.php';

$searchQuery = trim($_GET['q'] ?? '');
$results = [];
$totalResults = 0;

if (!empty($searchQuery)) {
    // Tìm kiếm câu hỏi
    $stmt = $conn->prepare("
        SELECT 
            ch.MACAUHOI,
            ch.TIEUDE,
            ch.LUOTXEM,
            d.NOIDUNG,
            d.NGAYDANG,
            nd.HOTEN AS NguoiDat,
            nd.ANHDAIDIEN,
            t.TENTHE AS Tag,
            t.MATHE,
            (SELECT COUNT(*) FROM TRALOI WHERE MACAUHOI = ch.MACAUHOI) AS SoCauTraLoi
        FROM CAUHOI ch
        INNER JOIN DAT d ON ch.MACAUHOI = d.MACAUHOI
        INNER JOIN NGUOIDUNG nd ON d.MANGUOIDUNG = nd.MANGUOIDUNG
        INNER JOIN TAG t ON ch.MATHE = t.MATHE
        WHERE ch.TIEUDE LIKE ? OR d.NOIDUNG LIKE ?
        ORDER BY d.NGAYDANG DESC
        LIMIT 50
    ");
    $searchParam = "%$searchQuery%";
    $stmt->execute([$searchParam, $searchParam]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $totalResults = count($results);
}
?>

<main class="py-4">
    <div class="container">
        <div class="row mb-4">
            <div class="col-12">
                <h2><i class="bi bi-search text-primary me-2"></i>Kết quả tìm kiếm</h2>
                <?php if (!empty($searchQuery)): ?>
                <p class="text-muted">
                    Tìm thấy <?php echo $totalResults; ?> kết quả cho "<strong><?php echo htmlspecialchars($searchQuery); ?></strong>"
                </p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Search Form -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" action="">
                    <div class="input-group input-group-lg">
                        <input type="text" class="form-control" name="q" 
                               placeholder="Nhập từ khóa tìm kiếm..." 
                               value="<?php echo htmlspecialchars($searchQuery); ?>" autofocus>
                        <button class="btn btn-primary" type="submit">
                            <i class="bi bi-search me-2"></i>Tìm kiếm
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <?php if (empty($searchQuery)): ?>
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="bi bi-search text-muted" style="font-size: 4rem;"></i>
                <h4 class="mt-3">Nhập từ khóa để tìm kiếm</h4>
                <p class="text-muted">Tìm kiếm câu hỏi theo tiêu đề hoặc nội dung</p>
            </div>
        </div>
        <?php elseif (empty($results)): ?>
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="bi bi-inbox text-muted" style="font-size: 4rem;"></i>
                <h4 class="mt-3">Không tìm thấy kết quả</h4>
                <p class="text-muted">Thử tìm kiếm với từ khóa khác</p>
                <a href="ask-question.php" class="btn btn-primary mt-2">
                    <i class="bi bi-plus-circle me-2"></i>Đặt câu hỏi mới
                </a>
            </div>
        </div>
        <?php else: ?>
            <?php foreach ($results as $question): ?>
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
                                <?php 
                                // Highlight từ khóa trong tiêu đề
                                $title = htmlspecialchars($question['TIEUDE']);
                                $title = preg_replace('/(' . preg_quote(htmlspecialchars($searchQuery), '/') . ')/i', '<mark>$1</mark>', $title);
                                echo $title;
                                ?>
                            </a>
                        </h5>
                        <p class="text-muted mb-2">
                            <?php 
                            $content = mb_substr(strip_tags($question['NOIDUNG']), 0, 200);
                            $content = htmlspecialchars($content);
                            $content = preg_replace('/(' . preg_quote(htmlspecialchars($searchQuery), '/') . ')/i', '<mark>$1</mark>', $content);
                            echo $content;
                            ?>...
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
        <?php endif; ?>
    </div>
</main>

<?php require_once 'includes/footer.php'; ?>
