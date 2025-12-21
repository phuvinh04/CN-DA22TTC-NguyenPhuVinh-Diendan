<?php
$pageTitle = 'Không tìm thấy trang';
require_once 'config/database.php';
require_once 'includes/header.php';
?>

<main class="py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-6 text-center">
                <div class="mb-4">
                    <i class="bi bi-emoji-frown text-muted" style="font-size: 8rem;"></i>
                </div>
                <h1 class="display-1 fw-bold text-primary">404</h1>
                <h2 class="mb-4">Không tìm thấy trang</h2>
                <p class="text-muted mb-4">
                    Trang bạn đang tìm kiếm không tồn tại hoặc đã bị di chuyển.
                </p>
                <div class="d-flex justify-content-center gap-3">
                    <a href="index.php" class="btn btn-primary btn-lg">
                        <i class="bi bi-house me-2"></i>Về trang chủ
                    </a>
                    <a href="questions.php" class="btn btn-outline-primary btn-lg">
                        <i class="bi bi-search me-2"></i>Tìm câu hỏi
                    </a>
                </div>
            </div>
        </div>
    </div>
</main>

<?php require_once 'includes/footer.php'; ?>
