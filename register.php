<?php
$pageTitle = 'Đăng ký';
require_once 'config/database.php';
require_once 'config/session.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $fullname = trim($_POST['fullname'] ?? '');
    
    if (empty($username) || empty($email) || empty($password) || empty($fullname)) {
        $error = 'Vui lòng nhập đầy đủ thông tin!';
    } elseif ($password !== $confirm_password) {
        $error = 'Mật khẩu xác nhận không khớp!';
    } elseif (strlen($password) < 6) {
        $error = 'Mật khẩu phải có ít nhất 6 ký tự!';
    } else {
        // Kiểm tra username đã tồn tại
        $stmt = $conn->prepare("SELECT COUNT(*) FROM NGUOIDUNG WHERE TENDANGNHAP = ?");
        $stmt->execute([$username]);
        if ($stmt->fetchColumn() > 0) {
            $error = 'Tên đăng nhập đã tồn tại!';
        } else {
            // Kiểm tra email đã tồn tại
            $stmt = $conn->prepare("SELECT COUNT(*) FROM NGUOIDUNG WHERE EMAIL = ?");
            $stmt->execute([$email]);
            if ($stmt->fetchColumn() > 0) {
                $error = 'Email đã được sử dụng!';
            } else {
                // Tạo tài khoản mới
                $userId = 'ND' . time();
                $hashedPassword = md5($password);
                $avatar = "https://ui-avatars.com/api/?name=" . urlencode($fullname) . "&background=random&color=fff";
                
                $stmt = $conn->prepare("INSERT INTO NGUOIDUNG (MANGUOIDUNG, TENDANGNHAP, EMAIL, MATKHAU, HOTEN, ANHDAIDIEN, TRANGTHAI, NGAYTAO) VALUES (?, ?, ?, ?, ?, ?, 'active', NOW())");
                
                if ($stmt->execute([$userId, $username, $email, $hashedPassword, $fullname, $avatar])) {
                    // Gán vai trò user
                    $stmt = $conn->prepare("INSERT INTO COVT (MAVAITRO, MANGUOIDUNG) VALUES ('user', ?)");
                    $stmt->execute([$userId]);
                    
                    $success = 'Đăng ký thành công! Đang chuyển hướng...';
                    header('refresh:2;url=login.php');
                } else {
                    $error = 'Có lỗi xảy ra, vui lòng thử lại!';
                }
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
    <title>Đăng ký - Diễn Đàn Chuyên Ngành</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-card" style="max-width: 500px;">
            <div class="auth-header">
                <i class="bi bi-person-plus-fill"></i>
                <h2>Đăng ký tài khoản</h2>
                <p class="mb-0 opacity-75">Tham gia cộng đồng ngay hôm nay!</p>
            </div>
            
            <div class="auth-body">
                <?php if ($error): ?>
                <div class="alert alert-danger alert-modern">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    <span><?php echo $error; ?></span>
                </div>
                <?php endif; ?>

                <?php if ($success): ?>
                <div class="alert alert-success alert-modern">
                    <i class="bi bi-check-circle-fill"></i>
                    <span><?php echo $success; ?></span>
                </div>
                <?php endif; ?>

                <form method="POST" action="">
                    <div class="form-floating-custom">
                        <i class="bi bi-person-fill"></i>
                        <input type="text" name="fullname" placeholder="Họ và tên" required value="<?php echo htmlspecialchars($_POST['fullname'] ?? ''); ?>">
                    </div>

                    <div class="form-floating-custom">
                        <i class="bi bi-at"></i>
                        <input type="text" name="username" placeholder="Tên đăng nhập" required value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>">
                    </div>

                    <div class="form-floating-custom">
                        <i class="bi bi-envelope-fill"></i>
                        <input type="email" name="email" placeholder="Email" required value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                    </div>

                    <div class="form-floating-custom">
                        <i class="bi bi-lock-fill"></i>
                        <input type="password" name="password" placeholder="Mật khẩu (tối thiểu 6 ký tự)" required>
                    </div>

                    <div class="form-floating-custom">
                        <i class="bi bi-shield-lock-fill"></i>
                        <input type="password" name="confirm_password" placeholder="Xác nhận mật khẩu" required>
                    </div>

                    <button type="submit" class="btn-auth">
                        <i class="bi bi-person-plus me-2"></i>Đăng ký
                    </button>
                </form>

                <div class="text-center mt-4">
                    <p class="mb-0">Đã có tài khoản? <a href="login.php" class="text-decoration-none fw-semibold" style="color: #667eea;">Đăng nhập</a></p>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
