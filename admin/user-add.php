<?php
$pageTitle = 'Thêm người dùng';
require_once '../config/database.php';
require_once '../config/session.php';
requireAdmin();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $fullname = trim($_POST['fullname'] ?? '');
    $role = $_POST['role'] ?? 'user';
    
    if (empty($username) || empty($email) || empty($password) || empty($fullname)) {
        $error = 'Vui lòng nhập đầy đủ thông tin!';
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
                $userId = 'ND' . time();
                $hashedPassword = md5($password);
                $avatar = "https://ui-avatars.com/api/?name=" . urlencode($fullname) . "&background=random&color=fff";
                
                $stmt = $conn->prepare("INSERT INTO NGUOIDUNG (MANGUOIDUNG, TENDANGNHAP, EMAIL, MATKHAU, HOTEN, ANHDAIDIEN, TRANGTHAI, NGAYTAO) VALUES (?, ?, ?, ?, ?, ?, 'active', NOW())");
                
                if ($stmt->execute([$userId, $username, $email, $hashedPassword, $fullname, $avatar])) {
                    // Gán vai trò
                    $stmt = $conn->prepare("INSERT INTO COVT (MAVAITRO, MANGUOIDUNG) VALUES (?, ?)");
                    $stmt->execute([$role, $userId]);
                    
                    $success = 'Thêm người dùng thành công!';
                    header('refresh:2;url=users.php');
                } else {
                    $error = 'Có lỗi xảy ra, vui lòng thử lại!';
                }
            }
        }
    }
}

require_once '../includes/admin_header.php';
?>

<div class="admin-container">
    <div class="container-fluid py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="mb-1"><i class="bi bi-person-plus-fill me-2"></i>Thêm người dùng</h2>
                <p class="text-muted mb-0">Tạo tài khoản người dùng mới</p>
            </div>
            <a href="users.php" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-2"></i>Quay lại
            </a>
        </div>

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

        <div class="card modern-card">
            <div class="card-body">
                <form method="POST">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Họ và tên <span class="text-danger">*</span></label>
                            <input type="text" name="fullname" class="form-control" required 
                                   value="<?php echo htmlspecialchars($_POST['fullname'] ?? ''); ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tên đăng nhập <span class="text-danger">*</span></label>
                            <input type="text" name="username" class="form-control" required
                                   value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control" required
                                   value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Mật khẩu <span class="text-danger">*</span></label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Vai trò</label>
                        <select name="role" class="form-select">
                            <option value="user">User</option>
                            <option value="moderator">Moderator</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-plus-circle me-2"></i>Thêm người dùng
                        </button>
                        <a href="users.php" class="btn btn-outline-secondary">Hủy</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/admin_footer.php'; ?>
