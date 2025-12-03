<?php
$pageTitle = 'Hệ thống điểm';
require_once 'config/database.php';
require_once 'includes/header.php';
?>

<main class="py-4">
    <div class="container">
        <!-- Page Header -->
        <div class="text-center mb-5">
            <h1 class="display-4 mb-3">
                <i class="bi bi-gem text-primary me-3"></i>
                Hệ Thống Điểm
            </h1>
            <p class="lead text-muted">Cách thức kiếm điểm và huy hiệu trong cộng đồng</p>
        </div>

        <div class="row">
            <!-- Cách kiếm điểm -->
            <div class="col-lg-8 mb-4">
                <div class="card modern-card mb-4">
                    <div class="card-header bg-gradient text-white" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                        <h4 class="mb-0"><i class="bi bi-plus-circle me-2"></i>Cách kiếm điểm</h4>
                    </div>
                    <div class="card-body">
                        <div class="points-list">
                            <!-- Đặt câu hỏi -->
                            <div class="points-item">
                                <div class="points-icon bg-primary">
                                    <i class="bi bi-question-circle-fill"></i>
                                </div>
                                <div class="points-content">
                                    <h5>Đặt câu hỏi</h5>
                                    <p class="text-muted mb-2">Đặt câu hỏi chất lượng, rõ ràng và hữu ích</p>
                                    <div class="points-value">+5 điểm</div>
                                </div>
                            </div>

                            <!-- Trả lời câu hỏi -->
                            <div class="points-item">
                                <div class="points-icon bg-success">
                                    <i class="bi bi-chat-left-text-fill"></i>
                                </div>
                                <div class="points-content">
                                    <h5>Trả lời câu hỏi</h5>
                                    <p class="text-muted mb-2">Cung cấp câu trả lời chi tiết và hữu ích</p>
                                    <div class="points-value">+10 điểm</div>
                                </div>
                            </div>

                            <!-- Câu trả lời được chấp nhận -->
                            <div class="points-item">
                                <div class="points-icon bg-warning">
                                    <i class="bi bi-check-circle-fill"></i>
                                </div>
                                <div class="points-content">
                                    <h5>Câu trả lời được chấp nhận</h5>
                                    <p class="text-muted mb-2">Câu trả lời của bạn được người hỏi chọn là tốt nhất</p>
                                    <div class="points-value">+15 điểm</div>
                                </div>
                            </div>

                            <!-- Nhận đánh giá sao -->
                            <div class="points-item">
                                <div class="points-icon bg-info">
                                    <i class="bi bi-star-fill"></i>
                                </div>
                                <div class="points-content">
                                    <h5>Nhận đánh giá sao</h5>
                                    <p class="text-muted mb-2">Câu hỏi hoặc câu trả lời của bạn được đánh giá từ 1-5 sao</p>
                                    <div class="points-value">+1 đến +5 điểm / đánh giá</div>
                                </div>
                            </div>

                            <!-- Đăng nhập hàng ngày -->
                            <div class="points-item">
                                <div class="points-icon bg-secondary">
                                    <i class="bi bi-calendar-check-fill"></i>
                                </div>
                                <div class="points-content">
                                    <h5>Đăng nhập hàng ngày</h5>
                                    <p class="text-muted mb-2">Đăng nhập mỗi ngày để nhận điểm thưởng</p>
                                    <div class="points-value">+1 điểm / ngày</div>
                                </div>
                            </div>

                            <!-- Hoàn thành profile -->
                            <div class="points-item">
                                <div class="points-icon bg-danger">
                                    <i class="bi bi-person-fill"></i>
                                </div>
                                <div class="points-content">
                                    <h5>Hoàn thành hồ sơ</h5>
                                    <p class="text-muted mb-2">Điền đầy đủ thông tin cá nhân và tiểu sử</p>
                                    <div class="points-value">+20 điểm (một lần)</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Cách mất điểm -->
                <div class="card modern-card">
                    <div class="card-header bg-danger text-white">
                        <h4 class="mb-0"><i class="bi bi-dash-circle me-2"></i>Cách mất điểm</h4>
                    </div>
                    <div class="card-body">
                        <div class="points-list">
                            <!-- Đánh giá thấp -->
                            <div class="points-item">
                                <div class="points-icon bg-danger">
                                    <i class="bi bi-star-half"></i>
                                </div>
                                <div class="points-content">
                                    <h5>Đánh giá thấp (1-2 sao)</h5>
                                    <p class="text-muted mb-2">Câu hỏi hoặc câu trả lời của bạn nhận đánh giá thấp</p>
                                    <div class="points-value text-danger">Chỉ +1 hoặc +2 điểm</div>
                                </div>
                            </div>

                            <!-- Câu hỏi bị xóa -->
                            <div class="points-item">
                                <div class="points-icon bg-warning">
                                    <i class="bi bi-trash-fill"></i>
                                </div>
                                <div class="points-content">
                                    <h5>Câu hỏi bị xóa</h5>
                                    <p class="text-muted mb-2">Câu hỏi vi phạm quy định và bị admin xóa</p>
                                    <div class="points-value text-danger">-5 điểm</div>
                                </div>
                            </div>

                            <!-- Spam -->
                            <div class="points-item">
                                <div class="points-icon bg-dark">
                                    <i class="bi bi-exclamation-triangle-fill"></i>
                                </div>
                                <div class="points-content">
                                    <h5>Spam hoặc vi phạm</h5>
                                    <p class="text-muted mb-2">Đăng nội dung spam, quảng cáo hoặc vi phạm quy định</p>
                                    <div class="points-value text-danger">-20 điểm</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Huy hiệu -->
                <div class="card modern-card mb-4">
                    <div class="card-header bg-transparent border-0">
                        <h5 class="mb-0"><i class="bi bi-award-fill text-warning me-2"></i>Huy hiệu</h5>
                    </div>
                    <div class="card-body">
                        <div class="badge-item mb-3">
                            <div class="d-flex align-items-center">
                                <span class="badge-icon">&#127793;</span>
                                <div class="flex-grow-1">
                                    <h6 class="mb-0">Người mới</h6>
                                    <small class="text-muted">Tham gia cộng đồng</small>
                                </div>
                            </div>
                        </div>

                        <div class="badge-item mb-3">
                            <div class="d-flex align-items-center">
                                <span class="badge-icon">&#128293;</span>
                                <div class="flex-grow-1">
                                    <h6 class="mb-0">Nhiệt tình</h6>
                                    <small class="text-muted">Trả lời 10 câu hỏi</small>
                                </div>
                            </div>
                        </div>

                        <div class="badge-item mb-3">
                            <div class="d-flex align-items-center">
                                <span class="badge-icon">&#11088;</span>
                                <div class="flex-grow-1">
                                    <h6 class="mb-0">Chuyên gia</h6>
                                    <small class="text-muted">Trả lời 50 câu hỏi</small>
                                </div>
                            </div>
                        </div>

                        <div class="badge-item mb-3">
                            <div class="d-flex align-items-center">
                                <span class="badge-icon">&#10067;</span>
                                <div class="flex-grow-1">
                                    <h6 class="mb-0">Người hỏi</h6>
                                    <small class="text-muted">Đặt 10 câu hỏi</small>
                                </div>
                            </div>
                        </div>

                        <div class="badge-item mb-3">
                            <div class="d-flex align-items-center">
                                <span class="badge-icon">&#10084;</span>
                                <div class="flex-grow-1">
                                    <h6 class="mb-0">Được yêu thích</h6>
                                    <small class="text-muted">Nhận 100 upvotes</small>
                                </div>
                            </div>
                        </div>

                        <div class="badge-item">
                            <div class="d-flex align-items-center">
                                <span class="badge-icon">&#127942;</span>
                                <div class="flex-grow-1">
                                    <h6 class="mb-0">Huyền thoại</h6>
                                    <small class="text-muted">Đạt 1000 điểm</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Stats -->
                <div class="card modern-card">
                    <div class="card-header bg-transparent border-0">
                        <h5 class="mb-0"><i class="bi bi-info-circle text-info me-2"></i>Thông tin</h5>
                    </div>
                    <div class="card-body">
                        <p class="mb-3"><strong>Điểm cao nhất:</strong> Không giới hạn</p>
                        <p class="mb-3"><strong>Điểm thấp nhất:</strong> 0 điểm</p>
                        <p class="mb-3"><strong>Cập nhật:</strong> Real-time</p>
                        <hr>
                        <div class="d-grid gap-2">
                            <a href="leaderboard.php" class="btn btn-primary">
                                <i class="bi bi-trophy me-2"></i>Xem bảng xếp hạng
                            </a>
                            <a href="ask-question.php" class="btn btn-outline-primary">
                                <i class="bi bi-plus-circle me-2"></i>Bắt đầu kiếm điểm
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<?php require_once 'includes/footer.php'; ?>
