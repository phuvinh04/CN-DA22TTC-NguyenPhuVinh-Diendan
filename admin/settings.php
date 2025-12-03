<?php
$pageTitle = 'Cài đặt hệ thống';
require_once '../config/database.php';
require_once '../config/session.php';
requireAdmin();

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Xử lý cập nhật cài đặt
    $success = 'Cập nhật cài đặt thành công!';
}

require_once '../includes/admin_header.php';
?>

<div class="admin-container">
    <div class="container-fluid py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="mb-1"><i class="bi bi-gear-fill me-2"></i>Cài đặt hệ thống</h2>
                <p class="text-muted mb-0">Quản lý cấu hình diễn đàn</p>
            </div>
        </div>

        <?php if ($success): ?>
        <div class="alert alert-success alert-modern">
            <i class="bi bi-check-circle-fill"></i>
            <span><?php echo $success; ?></span>
        </div>
        <?php endif; ?>

        <div class="row g-4">
            <!-- Cài đặt chung -->
            <div class="col-lg-6">
                <div class="card modern-card">
                    <div class="card-header bg-transparent border-0">
                        <h5 class="mb-0"><i class="bi bi-sliders text-primary me-2"></i>Cài đặt chung</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label">Tên diễn đàn</label>
                                <input type="text" class="form-control" value="Diễn Đàn IT" name="site_name">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Mô tả</label>
                                <textarea class="form-control" rows="3" name="site_description">Nơi chia sẻ kiến thức về công nghệ thông tin</textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Email liên hệ</label>
                                <input type="email" class="form-control" value="admin@diendan.com" name="contact_email">
                            </div>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save me-2"></i>Lưu thay đổi
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Cài đặt bảo mật -->
            <div class="col-lg-6">
                <div class="card modern-card">
                    <div class="card-header bg-transparent border-0">
                        <h5 class="mb-0"><i class="bi bi-shield-check text-success me-2"></i>Bảo mật</h5>
                    </div>
                    <div class="card-body">
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="requireEmail" checked>
                            <label class="form-check-label" for="requireEmail">
                                Yêu cầu xác thực email
                            </label>
                        </div>
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="allowRegister" checked>
                            <label class="form-check-label" for="allowRegister">
                                Cho phép đăng ký mới
                            </label>
                        </div>
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="moderateQuestions">
                            <label class="form-check-label" for="moderateQuestions">
                                Kiểm duyệt câu hỏi trước khi đăng
                            </label>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Độ dài mật khẩu tối thiểu</label>
                            <input type="number" class="form-control" value="6" min="6" max="20">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Thống kê hệ thống -->
            <div class="col-lg-12">
                <div class="card modern-card">
                    <div class="card-header bg-transparent border-0">
                        <h5 class="mb-0"><i class="bi bi-info-circle text-info me-2"></i>Thông tin hệ thống</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="text-center p-3">
                                    <i class="bi bi-server text-primary" style="font-size: 2rem;"></i>
                                    <h6 class="mt-2">PHP Version</h6>
                                    <p class="text-muted mb-0"><?php echo phpversion(); ?></p>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="text-center p-3">
                                    <i class="bi bi-database text-success" style="font-size: 2rem;"></i>
                                    <h6 class="mt-2">Database</h6>
                                    <p class="text-muted mb-0">MySQL <?php echo $conn->query('SELECT VERSION()')->fetchColumn(); ?></p>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="text-center p-3">
                                    <i class="bi bi-hdd text-warning" style="font-size: 2rem;"></i>
                                    <h6 class="mt-2">Disk Space</h6>
                                    <p class="text-muted mb-0"><?php echo round(disk_free_space('.') / 1024 / 1024 / 1024, 2); ?> GB free</p>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="text-center p-3">
                                    <i class="bi bi-clock-history text-info" style="font-size: 2rem;"></i>
                                    <h6 class="mt-2">Uptime</h6>
                                    <p class="text-muted mb-0">Online</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/admin_footer.php'; ?>
