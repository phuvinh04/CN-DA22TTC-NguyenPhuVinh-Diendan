<?php
$pageTitle = 'Đăng ký';
require_once 'config/database.php';
require_once 'config/session.php';
require_once 'includes/badge_helper.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $fullname = trim($_POST['fullname'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    
    if (empty($username) || empty($email) || empty($fullname) || empty($password)) {
        $error = 'Vui lòng điền đầy đủ thông tin';
    } elseif ($password !== $confirmPassword) {
        $error = 'Mật khẩu xác nhận không khớp';
    } elseif (strlen($password) < 6) {
        $error = 'Mật khẩu phải có ít nhất 6 ký tự';
    } else {
        $stmt = $conn->prepare("SELECT MANGUOIDUNG FROM NGUOIDUNG WHERE TENDANGNHAP = ? OR EMAIL = ?");
        $stmt->execute([$username, $email]);
        
        if ($stmt->fetch()) {
            $error = 'Tên đăng nhập hoặc email đã tồn tại';
        } else {
            try {
                $conn->beginTransaction();
                
                $userId = 'ND' . time();
                $hashedPassword = md5($password);
                $avatar = "https://ui-avatars.com/api/?name=" . urlencode($fullname) . "&background=6366f1&color=fff";
                
                $stmt = $conn->prepare("INSERT INTO NGUOIDUNG (MANGUOIDUNG, TENDANGNHAP, EMAIL, MATKHAU, HOTEN, ANHDAIDIEN, TRANGTHAI, NGAYTAO) VALUES (?, ?, ?, ?, ?, ?, 'active', NOW())");
                $stmt->execute([$userId, $username, $email, $hashedPassword, $fullname, $avatar]);
                
                $stmt = $conn->prepare("INSERT INTO COVT (MAVAITRO, MANGUOIDUNG) VALUES ('user', ?)");
                $stmt->execute([$userId]);
                
                awardBadge($userId, 'HH001');
                
                $conn->commit();
                $success = 'Đăng ký thành công! Đang chuyển hướng...';
                echo '<script>setTimeout(function(){ window.location.href = "login.php"; }, 2000);</script>';
            } catch (Exception $e) {
                $conn->rollBack();
                $error = 'Có lỗi xảy ra, vui lòng thử lại';
            }
        }
    }
}

?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - Diễn Đàn Chuyên Ngành</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<main class="auth-page">
    <div class="auth-card" style="max-width: 480px;">
        <div class="card-body">
            <div class="auth-header">
                <div class="auth-icon">
                    <i class="bi bi-person-plus-fill"></i>
                </div>
                <h4>Đăng ký</h4>
                <p>Tạo tài khoản để tham gia cộng đồng</p>
            </div>

            <?php if ($error): ?>
            <div class="alert alert-danger auth-alert">
                <i class="bi bi-exclamation-triangle-fill"></i>
                <?php echo $error; ?>
            </div>
            <?php endif; ?>

            <?php if ($success): ?>
            <div class="alert alert-success auth-alert">
                <i class="bi bi-check-circle-fill"></i>
                <?php echo $success; ?>
            </div>
            <?php endif; ?>

            <form method="POST" class="auth-form">
                <div class="mb-4">
                    <label for="fullname" class="form-label">Họ và tên</label>
                    <input type="text" class="form-control" id="fullname" name="fullname" 
                           value="<?php echo htmlspecialchars($fullname ?? ''); ?>" 
                           placeholder="Nhập họ và tên..." required>
                </div>
                
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label for="username" class="form-label">Tên đăng nhập</label>
                        <input type="text" class="form-control" id="username" name="username" 
                               value="<?php echo htmlspecialchars($username ?? ''); ?>" 
                               placeholder="username" required>
                    </div>
                    <div class="col-md-6">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" 
                               value="<?php echo htmlspecialchars($email ?? ''); ?>" 
                               placeholder="email@example.com" required>
                    </div>
                </div>
                
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label for="password" class="form-label">Mật khẩu</label>
                        <input type="password" class="form-control" id="password" name="password" 
                               placeholder="Tối thiểu 6 ký tự" required>
                    </div>
                    <div class="col-md-6">
                        <label for="confirm_password" class="form-label">Xác nhận mật khẩu</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" 
                               placeholder="Nhập lại mật khẩu" required>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-person-plus me-2"></i>Đăng ký
                </button>
            </form>

            <div class="auth-divider">
                <span>hoặc</span>
            </div>

            <a href="google-login.php" class="btn-social btn-google">
                <i class="bi bi-google"></i>
                Đăng ký với Google
            </a>

            <div class="auth-footer">
                <p>Đã có tài khoản? <a href="login.php">Đăng nhập ngay</a></p>
            </div>
        </div>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
