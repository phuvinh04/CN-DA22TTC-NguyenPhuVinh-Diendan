<?php
$pageTitle = 'Giới thiệu';
require_once 'config/database.php';
require_once 'includes/header.php';

// Thống kê
$stats = $conn->query("SELECT 
    (SELECT COUNT(*) FROM NGUOIDUNG WHERE TRANGTHAI = 'active') as users,
    (SELECT COUNT(*) FROM CAUHOI) as questions,
    (SELECT COUNT(*) FROM TRALOI) as answers,
    (SELECT COUNT(*) FROM TAG) as tags
")->fetch(PDO::FETCH_ASSOC);
?>

<main class="py-4">
    <div class="container">
        <!-- Hero -->
        <div class="text-center mb-5">
            <h1 class="display-4 fw-bold mb-3">
                <i class="bi bi-chat-dots-fill text-primary me-3"></i>
                Diễn Đàn Chuyên Ngành
            </h1>
            <p class="lead text-muted">Nơi chia sẻ kiến thức và giải đáp thắc mắc chuyên môn</p>
        </div>

        <!-- Stats -->
        <div class="row mb-5">
            <div class="col-md-3 col-6 mb-3">
                <div class="card modern-card text-center h-100">
                    <div class="card-body">
                        <i class="bi bi-people-fill text-primary" style="font-size: 2.5rem;"></i>
                        <h2 class="mt-2 mb-0"><?php echo number_format($stats['users']); ?></h2>
                        <p class="text-muted mb-0">Thành viên</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-6 mb-3">
                <div class="card modern-card text-center h-100">
                    <div class="card-body">
                        <i class="bi bi-question-circle-fill text-success" style="font-size: 2.5rem;"></i>
                        <h2 class="mt-2 mb-0"><?php echo number_format($stats['questions']); ?></h2>
                        <p class="text-muted mb-0">Câu hỏi</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-6 mb-3">
                <div class="card modern-card text-center h-100">
                    <div class="card-body">
                        <i class="bi bi-chat-left-text-fill text-info" style="font-size: 2.5rem;"></i>
                        <h2 class="mt-2 mb-0"><?php echo number_format($stats['answers']); ?></h2>
                        <p class="text-muted mb-0">Câu trả lời</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-6 mb-3">
                <div class="card modern-card text-center h-100">
                    <div class="card-body">
                        <i class="bi bi-tags-fill text-warning" style="font-size: 2.5rem;"></i>
                        <h2 class="mt-2 mb-0"><?php echo number_format($stats['tags']); ?></h2>
                        <p class="text-muted mb-0">Chủ đề</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- About -->
        <div class="row">
            <div class="col-lg-8 mx-auto">
                <div class="card modern-card">
                    <div class="card-body p-4">
                        <h3 class="mb-4"><i class="bi bi-info-circle me-2"></i>Về chúng tôi</h3>
                        <p>Diễn Đàn Chuyên Ngành là nền tảng hỏi đáp trực tuyến dành cho cộng đồng chuyên môn. Chúng tôi tin rằng việc chia sẻ kiến thức là cách tốt nhất để phát triển.</p>
                        
                        <h5 class="mt-4 mb-3">Tính năng nổi bật:</h5>
                        <ul class="list-unstyled">
                            <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i>Đặt câu hỏi và nhận câu trả lời từ cộng đồng</li>
                            <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i>Hệ thống điểm và huy hiệu</li>
                            <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i>Đánh giá sao cho câu hỏi và câu trả lời</li>
                            <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i>Bảng xếp hạng thành viên</li>
                            <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i>Tìm kiếm và lọc theo chủ đề</li>
                        </ul>

                        <div class="mt-4 text-center">
                            <a href="register.php" class="btn btn-primary btn-lg me-2">
                                <i class="bi bi-person-plus me-2"></i>Tham gia ngay
                            </a>
                            <a href="questions.php" class="btn btn-outline-primary btn-lg">
                                <i class="bi bi-search me-2"></i>Khám phá
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<?php require_once 'includes/footer.php'; ?>
