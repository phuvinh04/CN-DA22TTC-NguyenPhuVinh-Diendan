<?php
$pageTitle = 'Đăng nhập';
require_once 'config/database.php';
require_once 'config/session.php';

if (getCurrentUser()) {
    header('Location: index.php');
    exit();
}

$error = '';
$redirect = $_GET['redirect'] ?? $_POST['redirect'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $error = 'Vui lòng nhập đầy đủ thông tin';
    } else {
        $stmt = $conn->prepare("SELECT nd.*, cv.MAVAITRO as role FROM NGUOIDUNG nd 
                               LEFT JOIN COVT cv ON nd.MANGUOIDUNG = cv.MANGUOIDUNG 
                               WHERE (nd.TENDANGNHAP = ? OR nd.EMAIL = ?) AND nd.TRANGTHAI = 'active'");
        $stmt->execute([$username, $username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user && md5($password) === $user['MATKHAU']) {
            $_SESSION['user_id'] = $user['MANGUOIDUNG'];
            $_SESSION['username'] = $user['TENDANGNHAP'];
            $_SESSION['fullname'] = $user['HOTEN'];
            $_SESSION['email'] = $user['EMAIL'];
            $_SESSION['avatar'] = $user['ANHDAIDIEN'];
            $_SESSION['role'] = $user['role'] ?? 'user'; // MAVAITRO: admin, moderator, user
            $_SESSION['points'] = $user['DIEMDANHGIA'] ?? 0;
            
            // Redirect về trang trước đó hoặc trang chủ
            $redirectUrl = !empty($redirect) ? $redirect : 'index.php';
            header('Location: ' . $redirectUrl);
            exit();
        } else {
            $error = 'Tên đăng nhập hoặc mật khẩu không đúng';
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
    <div class="auth-card">
        <div class="card-body">
            <div class="auth-header">
                <div class="auth-icon">
                    <i class="bi bi-person-fill"></i>
                </div>
                <h4>Đăng nhập</h4>
                <p>Chào mừng bạn quay trở lại</p>
            </div>

            <?php if ($error): ?>
            <div class="alert alert-danger auth-alert">
                <i class="bi bi-exclamation-triangle-fill"></i>
                <?php echo $error; ?>
            </div>
            <?php endif; ?>

            <form method="POST" class="auth-form">
                <input type="hidden" name="redirect" value="<?php echo htmlspecialchars($redirect); ?>">
                
                <div class="mb-4">
                    <label for="username" class="form-label">Tên đăng nhập hoặc Email</label>
                    <input type="text" class="form-control" id="username" name="username" 
                           value="<?php echo htmlspecialchars($username ?? ''); ?>" 
                           placeholder="Nhập tên đăng nhập..." required autofocus>
                </div>
                
                <div class="mb-3">
                    <label for="password" class="form-label">Mật khẩu</label>
                    <input type="password" class="form-control" id="password" name="password" 
                           placeholder="Nhập mật khẩu..." required>
                </div>
                
                <div class="mb-4 text-end">
                    <a href="forgot-password.php" class="forgot-link">Quên mật khẩu?</a>
                </div>
                
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-box-arrow-in-right me-2"></i>Đăng nhập
                </button>
            </form>

            <div class="auth-divider">
                <span>hoặc</span>
            </div>

            <a href="google-login.php" class="btn-social btn-google">
                <i class="bi bi-google"></i>
                Đăng nhập với Google
            </a>

            <div class="auth-footer">
                <p>Chưa có tài khoản? <a href="register.php">Đăng ký ngay</a></p>
            </div>
        </div>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
