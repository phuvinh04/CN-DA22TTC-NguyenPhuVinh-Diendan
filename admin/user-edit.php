<?php
$pageTitle = 'Sửa người dùng';
require_once '../config/database.php';
require_once '../config/session.php';
requireAdmin();

$userId = $_GET['id'] ?? '';
if (empty($userId)) {
    header('Location: users.php');
    exit();
}

// Lấy thông tin user
$stmt = $conn->prepare("SELECT * FROM NGUOIDUNG WHERE MANGUOIDUNG = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();

if (!$user) {
    header('Location: users.php');
    exit();
}

// Lấy vai trò hiện tại
$stmt = $conn->prepare("SELECT MAVAITRO FROM COVT WHERE MANGUOIDUNG = ?");
$stmt->execute([$userId]);
$currentRole = $stmt->fetchColumn() ?: 'user';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $fullname = trim($_POST['fullname'] ?? '');
    $role = $_POST['role'] ?? 'user';
    $status = $_POST['status'] ?? 'active';
    $newPassword = $_POST['password'] ?? '';
    
    if (empty($email) || empty($fullname)) {
        $error = 'Vui lòng nhập đầy đủ thông tin!';
    } else {
        // Kiểm tra email đã tồn tại (trừ user hiện tại)
        $stmt = $conn->prepare("SELECT COUNT(*) FROM NGUOIDUNG WHERE EMAIL = ? AND MANGUOIDUNG != ?");
        $stmt->execute([$email, $userId]);
        if ($stmt->fetchColumn() > 0) {
            $error = 'Email đã được sử dụng!';
        } else {
            // Cập nhật thông tin
            if (!empty($newPassword)) {
                $hashedPassword = md5($newPassword);
                $stmt = $conn->prepare("UPDATE NGUOIDUNG SET EMAIL = ?, HOTEN = ?, MATKHAU = ?, TRANGTHAI = ? WHERE MANGUOIDUNG = ?");
                $stmt->execute([$email, $fullname, $hashedPassword, $status, $userId]);
            } else {
                $stmt = $conn->prepare("UPDATE NGUOIDUNG SET EMAIL = ?, HOTEN = ?, TRANGTHAI = ? WHERE MANGUOIDUNG = ?");
                $stmt->execute([$email, $fullname, $status, $userId]);
            }
            
            // Cập nhật vai trò
            $conn->prepare("DELETE FROM COVT WHERE MANGUOIDUNG = ?")->execute([$userId]);
            $conn->prepare("INSERT INTO COVT (MAVAITRO, MANGUOIDUNG) VALUES (?, ?)")->execute([$role, $userId]);
            
            $success = 'Cập nhật thành công!';
            $currentRole = $role;
            
            // Refresh user data
            $stmt = $conn->prepare("SELECT * FROM NGUOIDUNG WHERE MANGUOIDUNG = ?");
            $stmt->execute([$userId]);
            $user = $stmt->fetch();
        }
    }
}

require_once '../includes/admin_header.php';
?>

<div class="admin-container">
    <div class="container-fluid py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="mb-1"><i class="bi bi-person-gear me-2"></i>Sửa người dùng</h2>
                <p class="text-muted mb-0">Chỉnh sửa thông tin: <?php echo htmlspecialchars($user['HOTEN']); ?></p>
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

        <div class="row">
            <div class="col-lg-4 mb-4">
                <div class="card modern-card text-center">
                    <div class="card-body py-4">
                        <img src="<?php echo htmlspecialchars($user['ANHDAIDIEN']); ?>" class="rounded-circle mb-3" style="width: 120px; height: 120px;" alt="Avatar">
                        <h4><?php echo htmlspecialchars($user['HOTEN']); ?></h4>
                        <p class="text-muted">@<?php echo htmlspecialchars($user['TENDANGNHAP']); ?></p>
                        <span class="badge bg-<?php echo $currentRole == 'admin' ? 'danger' : ($currentRole == 'moderator' ? 'warning' : 'primary'); ?>">
                            <?php echo ucfirst($currentRole); ?>
                        </span>
                        <hr>
                        <div class="text-start">
                            <p class="mb-2"><strong>Điểm:</strong> <?php echo $user['DIEMDANHGIA']; ?></p>
                            <p class="mb-2"><strong>Ngày tạo:</strong> <?php echo date('d/m/Y', strtotime($user['NGAYTAO'])); ?></p>
                            <p class="mb-0"><strong>Trạng thái:</strong> 
                                <span class="badge bg-<?php echo $user['TRANGTHAI'] == 'active' ? 'success' : 'secondary'; ?>">
                                    <?php echo $user['TRANGTHAI'] == 'active' ? 'Hoạt động' : 'Vô hiệu'; ?>
                                </span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-8">
                <div class="card modern-card">
                    <div class="card-body">
                        <form method="POST">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Họ và tên <span class="text-danger">*</span></label>
                                    <input type="text" name="fullname" class="form-control" required 
                                           value="<?php echo htmlspecialchars($user['HOTEN']); ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Tên đăng nhập</label>
                                    <input type="text" class="form-control" disabled
                                           value="<?php echo htmlspecialchars($user['TENDANGNHAP']); ?>">
                                    <small class="text-muted">Không thể thay đổi</small>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Email <span class="text-danger">*</span></label>
                                    <input type="email" name="email" class="form-control" required
                                           value="<?php echo htmlspecialchars($user['EMAIL']); ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Mật khẩu mới</label>
                                    <input type="password" name="password" class="form-control" placeholder="Để trống nếu không đổi">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Vai trò</label>
                                    <select name="role" class="form-select">
                                        <option value="user" <?php echo $currentRole == 'user' ? 'selected' : ''; ?>>User</option>
                                        <option value="moderator" <?php echo $currentRole == 'moderator' ? 'selected' : ''; ?>>Moderator</option>
                                        <option value="admin" <?php echo $currentRole == 'admin' ? 'selected' : ''; ?>>Admin</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Trạng thái</label>
                                    <select name="status" class="form-select">
                                        <option value="active" <?php echo $user['TRANGTHAI'] == 'active' ? 'selected' : ''; ?>>Hoạt động</option>
                                        <option value="inactive" <?php echo $user['TRANGTHAI'] == 'inactive' ? 'selected' : ''; ?>>Vô hiệu</option>
                                    </select>
                                </div>
                            </div>
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-save me-2"></i>Lưu thay đổi
                                </button>
                                <a href="users.php" class="btn btn-outline-secondary">Hủy</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/admin_footer.php'; ?>
