<?php
$pageTitle = 'Tags';
require_once 'config/database.php';
require_once 'config/session.php';

// Yêu cầu đăng nhập
$currentUser = getCurrentUser();
if (!$currentUser) {
    header('Location: login.php');
    exit();
}

require_once 'includes/header.php';

// Lấy danh sách tags với số lượng câu hỏi
$tagsQuery = "SELECT 
    t.MATHE,
    t.TENTHE,
    t.MOTA,
    cn.TENCN AS ChuyenNganh,
    COUNT(ch.MACAUHOI) AS SoCauHoi
FROM TAG t
LEFT JOIN CHUYENNGHANH cn ON t.MACN = cn.MACN
LEFT JOIN CAUHOI ch ON t.MATHE = ch.MATHE
GROUP BY t.MATHE, t.TENTHE, t.MOTA, cn.TENCN
ORDER BY SoCauHoi DESC";

$tags = $conn->query($tagsQuery)->fetchAll(PDO::FETCH_ASSOC);

// Nhóm tags theo chuyên ngành
$tagsByCategory = [];
foreach ($tags as $tag) {
    $category = $tag['ChuyenNganh'] ?? 'Khác';
    if (!isset($tagsByCategory[$category])) {
        $tagsByCategory[$category] = [];
    }
    $tagsByCategory[$category][] = $tag;
}
?>

<main class="py-4">
    <div class="container">
        <div class="row mb-4">
            <div class="col-12">
                <h2><i class="bi bi-tags-fill text-primary me-2"></i>Tags</h2>
                <p class="text-muted">Khám phá các chủ đề và công nghệ được thảo luận nhiều nhất</p>
            </div>
        </div>

        <!-- Top Tags -->
        <div class="card mb-4">
            <div class="card-body">
                <h5 class="mb-3"><i class="bi bi-fire text-danger me-2"></i>Tags phổ biến nhất</h5>
                <div class="row">
                    <?php 
                    $topTags = array_slice($tags, 0, 8);
                    foreach ($topTags as $tag): 
                    ?>
                    <div class="col-lg-3 col-md-4 col-sm-6 mb-3">
                        <div class="card h-100 hover-shadow">
                            <div class="card-body">
                                <h5 class="mb-2">
                                    <a href="questions.php?tag=<?php echo $tag['MATHE']; ?>" class="text-decoration-none">
                                        <?php echo htmlspecialchars($tag['TENTHE']); ?>
                                    </a>
                                </h5>
                                <p class="text-muted small mb-2">
                                    <?php echo htmlspecialchars($tag['MOTA'] ?? 'Không có mô tả'); ?>
                                </p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="badge bg-primary"><?php echo $tag['SoCauHoi']; ?> câu hỏi</span>
                                    <a href="questions.php?tag=<?php echo $tag['MATHE']; ?>" class="btn btn-sm btn-outline-primary">
                                        Xem <i class="bi bi-arrow-right"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Tags by Category -->
        <?php foreach ($tagsByCategory as $category => $categoryTags): ?>
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h5 class="mb-0"><i class="bi bi-folder-fill text-warning me-2"></i><?php echo htmlspecialchars($category); ?></h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <?php foreach ($categoryTags as $tag): ?>
                    <div class="col-lg-4 col-md-6 mb-3">
                        <div class="d-flex align-items-start">
                            <div class="flex-grow-1">
                                <h6 class="mb-1">
                                    <a href="questions.php?tag=<?php echo $tag['MATHE']; ?>" class="text-decoration-none">
                                        <?php echo htmlspecialchars($tag['TENTHE']); ?>
                                    </a>
                                </h6>
                                <p class="text-muted small mb-1">
                                    <?php echo htmlspecialchars($tag['MOTA'] ?? 'Không có mô tả'); ?>
                                </p>
                                <span class="badge bg-secondary"><?php echo $tag['SoCauHoi']; ?> câu hỏi</span>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</main>

<?php require_once 'includes/footer.php'; ?>
