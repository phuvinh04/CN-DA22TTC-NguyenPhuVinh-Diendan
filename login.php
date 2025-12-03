<?php
$pageTitle = 'Đăng nhập';
require_once 'config/database.php';
require_once 'config/session.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']);
    
    if (!empty($username) && !empty($password)) {
        $stmt = $conn->prepare("SELECT MANGUOIDUNG, TENDANGNHAP, HOTEN, ANHDAIDIEN, MATKHAU FROM NGUOIDUNG WHERE TENDANGNHAP = ? AND TRANGTHAI = 'active'");
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user && md5($password) === $user['MATKHAU']) {
            // Cập nhật lần hoạt động cuối
            $updateStmt = $conn->prepare("UPDATE NGUOIDUNG SET LANHOATDONGCUOI = NOW() WHERE MANGUOIDUNG = ?");
            $updateStmt->execute([$user['MANGUOIDUNG']]);
            
            // Lấy vai trò
            $role = getUserRole($conn, $user['MANGUOIDUNG']);
            
            $_SESSION['user_id'] = $user['MANGUOIDUNG'];
            $_SESSION['username'] = $user['TENDANGNHAP'];
            $_SESSION['fullname'] = $user['HOTEN'];
            $_SESSION['avatar'] = $user['ANHDAIDIEN'];
            $_SESSION['role'] = $role;
            
            // Remember me
            if ($remember) {
                setcookie('remember_user', $user['MANGUOIDUNG'], time() + (86400 * 30), '/');
            }
            
            // Chuyển hướng theo vai trò
            if ($role === 'admin') {
                header('Location: admin/dashboard.php');
            } else {
                header('Location: index.php');
            }
            exit();
        } else {
            $error = 'Tên đăng nhập hoặc mật khẩu không đúng!';
        }
    } else {
        $error = 'Vui lòng nhập đầy đủ thông tin!';
    }
}

?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập - Diễn Đàn Chuyên Ngành</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <i class="bi bi-shield-lock-fill"></i>
                <h2>Đăng nhập</h2>
                <p class="mb-0 opacity-75">Chào mừng bạn quay trở lại!</p>
            </div>
            
            <div class="auth-body">
                <?php if ($error): ?>
                <div class="alert alert-danger alert-modern">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    <span><?php echo $error; ?></span>
                </div>
                <?php endif; ?>

                <form method="POST" action="">
                    <div class="form-floating-custom">
                        <i class="bi bi-person-fill"></i>
                        <input type="text" name="username" placeholder="Tên đăng nhập" required autofocus>
                    </div>

                    <div class="form-floating-custom">
                        <i class="bi bi-lock-fill"></i>
                        <input type="password" name="password" placeholder="Mật khẩu" required>
                    </div>

                    <div class="form-check mb-3">
                        <input type="checkbox" class="form-check-input" id="remember" name="remember">
                        <label class="form-check-label" for="remember">Ghi nhớ đăng nhập</label>
                    </div>

                    <button type="submit" class="btn-auth">
                        <i class="bi bi-box-arrow-in-right me-2"></i>Đăng nhập
                    </button>
                </form>

                <div class="text-center mt-4">
                    <p class="mb-0">Chưa có tài khoản? <a href="register.php" class="text-decoration-none fw-semibold" style="color: #667eea;">Đăng ký ngay</a></p>
                </div>

                <div class="alert alert-info alert-modern mt-4">
                    <i class="bi bi-info-circle-fill"></i>
                    <div>
                        <small><strong>Tài khoản demo:</strong></small><br>
                        <small>Admin: <strong>admin</strong> / <strong>123456</strong></small><br>
                        <small>User: <strong>nguyenvana</strong> / <strong>123456</strong></small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
