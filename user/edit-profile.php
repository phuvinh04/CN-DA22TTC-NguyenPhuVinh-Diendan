<?php
$pageTitle = 'Chỉnh sửa hồ sơ';
require_once '../config/database.php';
require_once '../config/session.php';
require_once '../includes/badge_helper.php';

requireLogin();

$currentUser = getCurrentUser();
$error = '';
$success = '';

// Kiểm tra và thêm cột KHUNG_AVATAR nếu chưa có
try {
    $checkCol = $conn->query("SHOW COLUMNS FROM NGUOIDUNG LIKE 'KHUNG_AVATAR'");
    if ($checkCol->rowCount() == 0) {
        $conn->exec("ALTER TABLE NGUOIDUNG ADD COLUMN KHUNG_AVATAR VARCHAR(100) DEFAULT NULL");
    }
} catch (Exception $e) {}

// Lấy thông tin user hiện tại
$stmt = $conn->prepare("SELECT * FROM NGUOIDUNG WHERE MANGUOIDUNG = ?");
$stmt->execute([$currentUser['id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'update_profile';
    
    if ($action === 'update_profile') {
        $fullname = trim($_POST['fullname'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $bio = trim($_POST['bio'] ?? '');
        
        if (empty($fullname) || empty($email)) {
            $error = 'Vui lòng nhập đầy đủ thông tin bắt buộc!';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Email không hợp lệ!';
        } else {
            // Kiểm tra email đã tồn tại
            $stmt = $conn->prepare("SELECT COUNT(*) FROM NGUOIDUNG WHERE EMAIL = ? AND MANGUOIDUNG != ?");
            $stmt->execute([$email, $currentUser['id']]);
            if ($stmt->fetchColumn() > 0) {
                $error = 'Email đã được sử dụng bởi tài khoản khác!';
            } else {
                $stmt = $conn->prepare("UPDATE NGUOIDUNG SET HOTEN = ?, EMAIL = ?, TIEUSU = ? WHERE MANGUOIDUNG = ?");
                $stmt->execute([$fullname, $email, $bio, $currentUser['id']]);
                $_SESSION['fullname'] = $fullname;
                $success = 'Cập nhật hồ sơ thành công!';
                
                // Refresh user data
                $stmt = $conn->prepare("SELECT * FROM NGUOIDUNG WHERE MANGUOIDUNG = ?");
                $stmt->execute([$currentUser['id']]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
            }
        }
    } elseif ($action === 'change_password') {
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        
        if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
            $error = 'Vui lòng nhập đầy đủ thông tin mật khẩu!';
        } elseif (md5($currentPassword) !== $user['MATKHAU']) {
            $error = 'Mật khẩu hiện tại không đúng!';
        } elseif (strlen($newPassword) < 6) {
            $error = 'Mật khẩu mới phải có ít nhất 6 ký tự!';
        } elseif ($newPassword !== $confirmPassword) {
            $error = 'Xác nhận mật khẩu không khớp!';
        } else {
            $stmt = $conn->prepare("UPDATE NGUOIDUNG SET MATKHAU = ? WHERE MANGUOIDUNG = ?");
            $stmt->execute([md5($newPassword), $currentUser['id']]);
            $success = 'Đổi mật khẩu thành công!';
        }
    } elseif ($action === 'update_avatar') {
        $avatarUrl = trim($_POST['avatar_url'] ?? '');
        if (!empty($avatarUrl)) {
            $stmt = $conn->prepare("UPDATE NGUOIDUNG SET ANHDAIDIEN = ? WHERE MANGUOIDUNG = ?");
            $stmt->execute([$avatarUrl, $currentUser['id']]);
            $_SESSION['avatar'] = $avatarUrl;
            $success = 'Cập nhật ảnh đại diện thành công!';
            $user['ANHDAIDIEN'] = $avatarUrl;
        }
    } elseif ($action === 'update_frame') {
        $frameId = trim($_POST['frame_id'] ?? '');
        // Kiểm tra user có huy hiệu này không (hoặc chọn không khung)
        if (empty($frameId)) {
            $stmt = $conn->prepare("UPDATE NGUOIDUNG SET KHUNG_AVATAR = NULL WHERE MANGUOIDUNG = ?");
            $stmt->execute([$currentUser['id']]);
            $success = 'Đã bỏ khung avatar!';
            $user['KHUNG_AVATAR'] = null;
        } else {
            // Admin có thể dùng bất kỳ khung nào
            $canUseFrame = ($currentUser['role'] === 'admin');
            
            if (!$canUseFrame) {
                // Kiểm tra user có huy hiệu này không
                $checkStmt = $conn->prepare("SELECT 1 FROM NHAN WHERE MANGUOIDUNG = ? AND MAHUYHIEU = ?");
                $checkStmt->execute([$currentUser['id'], $frameId]);
                $canUseFrame = $checkStmt->fetchColumn();
            }
            
            if ($canUseFrame) {
                $stmt = $conn->prepare("UPDATE NGUOIDUNG SET KHUNG_AVATAR = ? WHERE MANGUOIDUNG = ?");
                $stmt->execute([$frameId, $currentUser['id']]);
                $success = 'Cập nhật khung avatar thành công!';
                $user['KHUNG_AVATAR'] = $frameId;
            } else {
                $error = 'Bạn chưa đạt được huy hiệu này!';
            }
        }
    }
}

// Lấy danh sách huy hiệu đã đạt được (admin có tất cả)
if ($currentUser['role'] === 'admin') {
    // Admin có thể dùng tất cả khung
    $userBadges = $conn->query("SELECT * FROM HUYHIEU ORDER BY CAPDO DESC, NGUONGTIEUCHI DESC")->fetchAll(PDO::FETCH_ASSOC);
} else {
    $userBadges = getUserBadges($currentUser['id']);
}

require_once '../includes/header.php';
?>

<main class="py-4">
    <div class="container">
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="../index.php">Trang chủ</a></li>
                <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                <li class="breadcrumb-item active">Chỉnh sửa hồ sơ</li>
            </ol>
        </nav>

        <div class="row">
            <div class="col-lg-4 mb-4">
                <!-- Avatar Card -->
                <div class="card">
                    <div class="card-body text-center">
                        <?php 
                        // Hiển thị avatar với khung đã chọn
                        $currentFrameColor = '#667eea';
                        $currentFrameBadge = null;
                        try {
                            if (!empty($user['KHUNG_AVATAR'])) {
                                $frameStmt = $conn->prepare("SELECT * FROM HUYHIEU WHERE MAHUYHIEU = ?");
                                $frameStmt->execute([$user['KHUNG_AVATAR']]);
                                $currentFrameBadge = $frameStmt->fetch(PDO::FETCH_ASSOC);
                                if ($currentFrameBadge && !empty($currentFrameBadge['MAUKHUNG'])) {
                                    $currentFrameColor = $currentFrameBadge['MAUKHUNG'];
                                }
                            }
                        } catch (Exception $e) {}
                        ?>
                        <div class="position-relative d-inline-block mb-3">
                            <img src="<?php echo htmlspecialchars($user['ANHDAIDIEN']); ?>" 
                                 alt="Avatar" class="rounded-circle" 
                                 style="width: 150px; height: 150px; border: 5px solid <?php echo $currentFrameColor; ?>; object-fit: cover;
                                 <?php if ($currentFrameBadge && ($currentFrameBadge['CAPDO'] ?? 0) >= 5): ?>
                                 box-shadow: 0 0 15px <?php echo $currentFrameColor; ?>, 0 0 30px <?php echo $currentFrameColor; ?>;
                                 <?php endif; ?>">
                            <?php if ($currentFrameBadge): ?>
                            <span class="position-absolute bottom-0 end-0 bg-white rounded-circle p-1" style="font-size: 24px; box-shadow: 0 2px 5px rgba(0,0,0,0.2);">
                                <?php echo $currentFrameBadge['BIEUTUONG']; ?>
                            </span>
                            <?php endif; ?>
                        </div>
                        <h5><?php echo htmlspecialchars($user['HOTEN']); ?></h5>
                        <p class="text-muted">@<?php echo htmlspecialchars($user['TENDANGNHAP']); ?></p>
                        
                        <form method="POST" class="mt-3">
                            <input type="hidden" name="action" value="update_avatar">
                            <div class="input-group input-group-sm">
                                <input type="url" name="avatar_url" class="form-control" 
                                       placeholder="URL ảnh đại diện mới" 
                                       value="<?php echo htmlspecialchars($user['ANHDAIDIEN']); ?>">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-camera"></i>
                                </button>
                            </div>
                        </form>
                        
                        <hr>
                        
                        <div class="d-grid gap-2">
                            <a href="dashboard.php" class="btn btn-outline-primary">
                                <i class="bi bi-speedometer2 me-2"></i>Dashboard
                            </a>
                            <a href="../profile.php?id=<?php echo $currentUser['id']; ?>" class="btn btn-outline-secondary">
                                <i class="bi bi-person me-2"></i>Xem hồ sơ công khai
                            </a>
                        </div>
                    </div>
                </div>
                
                <!-- Chọn khung Avatar -->
                <div class="card mt-3">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span><i class="bi bi-palette me-2"></i>Khung Avatar</span>
                        <span class="badge bg-secondary"><?php echo count($userBadges); ?> khung</span>
                    </div>
                    <div class="card-body">
                        <?php if (empty($userBadges)): ?>
                            <div class="text-center py-4">
                                <i class="bi bi-lock text-muted" style="font-size: 2rem;"></i>
                                <p class="text-muted mt-2 mb-0">Đạt huy hiệu để mở khóa khung avatar!</p>
                            </div>
                        <?php else: ?>
                            <form method="POST" id="frameForm">
                                <input type="hidden" name="action" value="update_frame">
                                
                                <!-- Preview khung đang chọn -->
                                <div class="text-center mb-3 pb-3 border-bottom">
                                    <small class="text-muted d-block mb-2">Xem trước</small>
                                    <div class="d-inline-block position-relative" id="framePreview">
                                        <img src="<?php echo htmlspecialchars($user['ANHDAIDIEN']); ?>" 
                                             class="rounded-circle" id="previewImg"
                                             style="width: 60px; height: 60px; border: 3px solid <?php echo $currentFrameColor; ?>; object-fit: cover;">
                                    </div>
                                </div>
                                
                                <!-- Grid chọn khung -->
                                <div class="frame-grid">
                                    <!-- Không khung -->
                                    <div class="frame-item <?php echo empty($user['KHUNG_AVATAR']) ? 'selected' : ''; ?>" 
                                         onclick="selectFrame(this, '', '#ccc')" title="Không khung">
                                        <input type="radio" name="frame_id" value="" <?php echo empty($user['KHUNG_AVATAR']) ? 'checked' : ''; ?>>
                                        <div class="frame-circle" style="border-color: #ccc;"></div>
                                        <span class="frame-check"><i class="bi bi-check"></i></span>
                                    </div>
                                    
                                    <?php foreach ($userBadges as $badge): ?>
                                    <?php 
                                    $badgeColor = $badge['MAUKHUNG'] ?? '#6366f1';
                                    $badgeLevel = $badge['CAPDO'] ?? 1;
                                    $isSelected = ($user['KHUNG_AVATAR'] ?? '') === $badge['MAHUYHIEU'];
                                    $glowStyle = $badgeLevel >= 5 ? "box-shadow: 0 0 8px {$badgeColor};" : "";
                                    ?>
                                    <div class="frame-item <?php echo $isSelected ? 'selected' : ''; ?>" 
                                         onclick="selectFrame(this, '<?php echo $badge['MAHUYHIEU']; ?>', '<?php echo $badgeColor; ?>')"
                                         title="<?php echo htmlspecialchars($badge['TENHUYHIEU']); ?>">
                                        <input type="radio" name="frame_id" value="<?php echo $badge['MAHUYHIEU']; ?>" <?php echo $isSelected ? 'checked' : ''; ?>>
                                        <div class="frame-circle" style="border-color: <?php echo $badgeColor; ?>; <?php echo $glowStyle; ?>"></div>
                                        <span class="frame-icon"><?php echo $badge['BIEUTUONG']; ?></span>
                                        <span class="frame-check"><i class="bi bi-check"></i></span>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                                
                                <button type="submit" class="btn btn-primary w-100 mt-3">
                                    <i class="bi bi-check2-circle me-2"></i>Lưu khung avatar
                                </button>
                            </form>
                            
                            <script>
                            function selectFrame(el, frameId, color) {
                                // Bỏ chọn tất cả
                                document.querySelectorAll('.frame-item').forEach(item => item.classList.remove('selected'));
                                // Chọn item mới
                                el.classList.add('selected');
                                el.querySelector('input').checked = true;
                                // Cập nhật preview
                                document.getElementById('previewImg').style.borderColor = color;
                            }
                            </script>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Stats Card -->
                <div class="card mt-3">
                    <div class="card-header bg-primary text-white">
                        <i class="bi bi-bar-chart me-2"></i>Thống kê
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Điểm đánh giá:</span>
                            <strong class="text-primary"><?php echo number_format($user['DIEMDANHGIA']); ?></strong>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Ngày tham gia:</span>
                            <strong><?php echo date('d/m/Y', strtotime($user['NGAYTAO'])); ?></strong>
                        </div>
                        <?php if ($user['LANHOATDONGCUOI']): ?>
                        <div class="d-flex justify-content-between">
                            <span>Hoạt động cuối:</span>
                            <strong><?php echo date('d/m/Y H:i', strtotime($user['LANHOATDONGCUOI'])); ?></strong>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="col-lg-8">
                <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <i class="bi bi-exclamation-triangle me-2"></i><?php echo $error; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>

                <?php if ($success): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <i class="bi bi-check-circle me-2"></i><?php echo $success; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>

                <!-- Profile Form -->
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="bi bi-person-gear me-2"></i>Thông tin cá nhân</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <input type="hidden" name="action" value="update_profile">
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Họ và tên <span class="text-danger">*</span></label>
                                    <input type="text" name="fullname" class="form-control" 
                                           value="<?php echo htmlspecialchars($user['HOTEN']); ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Tên đăng nhập</label>
                                    <input type="text" class="form-control" 
                                           value="<?php echo htmlspecialchars($user['TENDANGNHAP']); ?>" disabled>
                                    <small class="text-muted">Không thể thay đổi</small>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Email <span class="text-danger">*</span></label>
                                <input type="email" name="email" class="form-control" 
                                       value="<?php echo htmlspecialchars($user['EMAIL']); ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Tiểu sử</label>
                                <textarea name="bio" class="form-control" rows="4" 
                                          placeholder="Giới thiệu về bản thân..."><?php echo htmlspecialchars($user['TIEUSU'] ?? ''); ?></textarea>
                            </div>
                            
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save me-2"></i>Lưu thay đổi
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Password Form -->
                <div class="card">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="mb-0"><i class="bi bi-shield-lock me-2"></i>Đổi mật khẩu</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <input type="hidden" name="action" value="change_password">
                            
                            <div class="mb-3">
                                <label class="form-label">Mật khẩu hiện tại <span class="text-danger">*</span></label>
                                <input type="password" name="current_password" class="form-control" required>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Mật khẩu mới <span class="text-danger">*</span></label>
                                    <input type="password" name="new_password" class="form-control" 
                                           minlength="6" required>
                                    <small class="text-muted">Tối thiểu 6 ký tự</small>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Xác nhận mật khẩu <span class="text-danger">*</span></label>
                                    <input type="password" name="confirm_password" class="form-control" 
                                           minlength="6" required>
                                </div>
                            </div>
                            
                            <button type="submit" class="btn btn-warning">
                                <i class="bi bi-key me-2"></i>Đổi mật khẩu
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<?php require_once '../includes/footer.php'; ?>
