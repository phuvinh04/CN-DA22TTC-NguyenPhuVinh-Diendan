<?php
$pageTitle = 'Liên hệ';
require_once 'config/database.php';
require_once 'includes/header.php';

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');
    
    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        $error = 'Vui lòng điền đầy đủ thông tin!';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Email không hợp lệ!';
    } else {
        // Trong thực tế, bạn sẽ gửi email hoặc lưu vào database
        $success = 'Cảm ơn bạn đã liên hệ! Chúng tôi sẽ phản hồi sớm nhất có thể.';
    }
}
?>

<main class="py-4">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="text-center mb-5">
                    <h1 class="display-5 fw-bold">
                        <i class="bi bi-envelope-fill text-primary me-3"></i>Liên hệ
                    </h1>
                    <p class="lead text-muted">Chúng tôi luôn sẵn sàng lắng nghe ý kiến của bạn</p>
                </div>

                <?php if ($success): ?>
                <div class="alert alert-success">
                    <i class="bi bi-check-circle me-2"></i><?php echo $success; ?>
                </div>
                <?php endif; ?>

                <?php if ($error): ?>
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle me-2"></i><?php echo $error; ?>
                </div>
                <?php endif; ?>

                <div class="card modern-card">
                    <div class="card-body p-4">
                        <form method="POST">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Họ và tên <span class="text-danger">*</span></label>
                                    <input type="text" name="name" class="form-control" required 
                                           value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Email <span class="text-danger">*</span></label>
                                    <input type="email" name="email" class="form-control" required
                                           value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Chủ đề <span class="text-danger">*</span></label>
                                <input type="text" name="subject" class="form-control" required
                                       value="<?php echo htmlspecialchars($_POST['subject'] ?? ''); ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Nội dung <span class="text-danger">*</span></label>
                                <textarea name="message" class="form-control" rows="6" required><?php echo htmlspecialchars($_POST['message'] ?? ''); ?></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="bi bi-send me-2"></i>Gửi tin nhắn
                            </button>
                        </form>
                    </div>
                </div>

                <div class="row mt-4">
                    <div class="col-md-4 mb-3">
                        <div class="card modern-card text-center h-100">
                            <div class="card-body">
                                <i class="bi bi-envelope text-primary" style="font-size: 2rem;"></i>
                                <h6 class="mt-3">Email</h6>
                                <p class="text-muted mb-0">support@diendan.com</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="card modern-card text-center h-100">
                            <div class="card-body">
                                <i class="bi bi-telephone text-success" style="font-size: 2rem;"></i>
                                <h6 class="mt-3">Điện thoại</h6>
                                <p class="text-muted mb-0">0123 456 789</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="card modern-card text-center h-100">
                            <div class="card-body">
                                <i class="bi bi-geo-alt text-danger" style="font-size: 2rem;"></i>
                                <h6 class="mt-3">Địa chỉ</h6>
                                <p class="text-muted mb-0">Hà Nội, Việt Nam</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<?php require_once 'includes/footer.php'; ?>
