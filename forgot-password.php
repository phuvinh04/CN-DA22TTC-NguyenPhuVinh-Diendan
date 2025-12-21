<?php
$pageTitle = 'Quên mật khẩu';
require_once 'config/database.php';
require_once 'config/session.php';

if (getCurrentUser()) {
    header('Location: index.php');
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    
    if (empty($email)) {
        $error = 'Vui lòng nhập email';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Email không hợp lệ';
    } else {
        // Kiểm tra email tồn tại
        $stmt = $conn->prepare("SELECT MANGUOIDUNG, HOTEN FROM NGUOIDUNG WHERE EMAIL = ? AND TRANGTHAI = 'active'");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user) {
            // Tạo token reset password
            $token = bin2hex(random_bytes(32));
            $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));
            
            // Tạo bảng reset token nếu chưa có
            $conn->exec("CREATE TABLE IF NOT EXISTS PASSWORD_RESET (
                TOKEN VARCHAR(64) PRIMARY KEY,
                MANGUOIDUNG VARCHAR(100) NOT NULL,
                EXPIRY DATETIME NOT NULL,
                USED TINYINT DEFAULT 0
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
            
            // Xóa token cũ của user này
            $conn->prepare("DELETE FROM PASSWORD_RESET WHERE MANGUOIDUNG = ?")->execute([$user['MANGUOIDUNG']]);
            
            // Lưu token mới
            $conn->prepare("INSERT INTO PASSWORD_RESET (TOKEN, MANGUOIDUNG, EXPIRY) VALUES (?, ?, ?)")
                ->execute([$token, $user['MANGUOIDUNG'], $expiry]);
            
            // Trong thực tế, gửi email với link reset
            // Ở đây chỉ hiển thị link (demo)
            $resetLink = "reset-password.php?token=" . $token;
            
            $success = "Link đặt lại mật khẩu đã được tạo. <br><small class='text-muted'>(Demo) Link: <a href='$resetLink'>$resetLink</a></small>";
        } else {
            // Không tiết lộ email có tồn tại hay không
            $success = 'Nếu email tồn tại trong hệ thống, bạn sẽ nhận được hướng dẫn đặt lại mật khẩu.';
        }
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
                                <i class="bi bi-key" style="font-size: 3rem; color: var(--primary-500);"></i>
                            </div>
                            <h4 class="mb-1">Quên mật khẩu</h4>
                            <p class="text-muted" style="font-size: var(--font-sm);">Nhập email để đặt lại mật khẩu</p>
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
                        <?php else: ?>
                        <form method="POST">
                            <div class="mb-4">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       placeholder="Nhập email đã đăng ký..." required autofocus>
                            </div>
                            
                            <button type="submit" class="btn btn-primary w-100 mb-3">
                                <i class="bi bi-envelope me-2"></i>Gửi yêu cầu
                            </button>
                        </form>
                        <?php endif; ?>

                        <p class="text-center text-muted mb-0" style="font-size: var(--font-sm);">
                            <a href="login.php"><i class="bi bi-arrow-left me-1"></i>Quay lại đăng nhập</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<?php require_once 'includes/footer.php'; ?>
