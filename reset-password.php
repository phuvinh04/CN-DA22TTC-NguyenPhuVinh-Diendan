<?php
$pageTitle = 'Đặt lại mật khẩu';
require_once 'config/database.php';
require_once 'config/session.php';

if (getCurrentUser()) {
    header('Location: index.php');
    exit();
}

$token = $_GET['token'] ?? '';
$error = '';
$success = '';
$validToken = false;
$userId = null;

if (empty($token)) {
    $error = 'Link không hợp lệ';
} else {
    // Kiểm tra token
    $stmt = $conn->prepare("SELECT pr.*, nd.HOTEN FROM PASSWORD_RESET pr JOIN NGUOIDUNG nd ON pr.MANGUOIDUNG = nd.MANGUOIDUNG WHERE pr.TOKEN = ? AND pr.USED = 0 AND pr.EXPIRY > NOW()");
    $stmt->execute([$token]);
    $resetData = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($resetData) {
        $validToken = true;
        $userId = $resetData['MANGUOIDUNG'];
    } else {
        $error = 'Link đã hết hạn hoặc không hợp lệ';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $validToken) {
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    
    if (empty($password)) {
        $error = 'Vui lòng nhập mật khẩu mới';
    } elseif (strlen($password) < 6) {
        $error = 'Mật khẩu phải có ít nhất 6 ký tự';
    } elseif ($password !== $confirmPassword) {
        $error = 'Xác nhận mật khẩu không khớp';
    } else {
        // Cập nhật mật khẩu
        $hashedPassword = md5($password);
        $conn->prepare("UPDATE NGUOIDUNG SET MATKHAU = ? WHERE MANGUOIDUNG = ?")->execute([$hashedPassword, $userId]);
        
        // Đánh dấu token đã sử dụng
        $conn->prepare("UPDATE PASSWORD_RESET SET USED = 1 WHERE TOKEN = ?")->execute([$token]);
        
        $success = 'Đặt lại mật khẩu thành công! Đang chuyển hướng...';
        echo '<script>setTimeout(function(){ window.location.href = "login.php"; }, 2000);</script>';
        $validToken = false;
    }
}

require_once 'includes/header.php';
?>

<main class="py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-5 col-lg-4">
                <div class="card" style="box-shadow: var(--shadow-lg);">
                    <div class="card-body p-5">
                        <div class="text-center mb-4">
                            <div class="mb-3">
                                <i class="bi bi-shield-lock" style="font-size: 3rem; color: var(--primary-500);"></i>
                            </div>
                            <h4 class="mb-1">Đặt lại mật khẩu</h4>
                            <p class="text-muted" style="font-size: var(--font-sm);">Nhập mật khẩu mới của bạn</p>
                        </div>

                        <?php if ($error): ?>
                        <div class="alert alert-danger">
                            <i class="bi bi-exclamation-triangle"></i>
                            <?php echo $error; ?>
                        </div>
                        <?php endif; ?>

                        <?php if ($success): ?>
                        <div class="alert alert-success">
                            <i class="bi bi-check-circle"></i>
                            <?php echo $success; ?>
                        </div>
                        <?php endif; ?>

                        <?php if ($validToken): ?>
                        <form method="POST">
                            <div class="mb-3">
                                <label for="password" class="form-label">Mật khẩu mới</label>
                                <input type="password" class="form-control" id="password" name="password" 
                                       placeholder="Tối thiểu 6 ký tự..." required>
                            </div>
                            
                            <div class="mb-4">
                                <label for="confirm_password" class="form-label">Xác nhận mật khẩu</label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" 
                                       placeholder="Nhập lại mật khẩu..." required>
                            </div>
                            
                            <button type="submit" class="btn btn-primary w-100 mb-3">
                                <i class="bi bi-check-lg me-2"></i>Đặt lại mật khẩu
                            </button>
                        </form>
                        <?php elseif (!$success): ?>
                        <div class="text-center">
                            <a href="forgot-password.php" class="btn btn-primary">
                                <i class="bi bi-arrow-repeat me-2"></i>Yêu cầu link mới
                            </a>
                        </div>
                        <?php endif; ?>

                        <p class="text-center text-muted mb-0 mt-3" style="font-size: var(--font-sm);">
                            <a href="login.php"><i class="bi bi-arrow-left me-1"></i>Quay lại đăng nhập</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<?php require_once 'includes/footer.php'; ?>
