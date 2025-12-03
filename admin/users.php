<?php
$pageTitle = 'Quản lý người dùng';
require_once '../config/database.php';
require_once '../config/session.php';
requireAdmin();

// Xử lý xóa user
if (isset($_GET['delete'])) {
    $userId = $_GET['delete'];
    $stmt = $conn->prepare("UPDATE NGUOIDUNG SET TRANGTHAI = 'inactive' WHERE MANGUOIDUNG = ?");
    $stmt->execute([$userId]);
    header('Location: users.php?msg=deleted');
    exit();
}

// Lấy danh sách users
$search = $_GET['search'] ?? '';
$query = "SELECT nd.*, 
          (SELECT COUNT(*) FROM DAT WHERE MANGUOIDUNG = nd.MANGUOIDUNG) as SoCauHoi,
          (SELECT COUNT(*) FROM TRALOI WHERE MANGUOIDUNG = nd.MANGUOIDUNG) as SoCauTraLoi,
          (SELECT GROUP_CONCAT(MAVAITRO) FROM COVT WHERE MANGUOIDUNG = nd.MANGUOIDUNG) as VaiTro
          FROM NGUOIDUNG nd
          WHERE nd.TRANGTHAI = 'active'";

if ($search) {
    $query .= " AND (nd.HOTEN LIKE ? OR nd.EMAIL LIKE ? OR nd.TENDANGNHAP LIKE ?)";
}

$query .= " ORDER BY nd.NGAYTAO DESC";

$stmt = $conn->prepare($query);
if ($search) {
    $searchParam = "%$search%";
    $stmt->execute([$searchParam, $searchParam, $searchParam]);
} else {
    $stmt->execute();
}
$users = $stmt->fetchAll();

require_once '../includes/admin_header.php';
?>

<div class="admin-container">
    <div class="container-fluid py-4">
        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="mb-1"><i class="bi bi-people-fill me-2"></i>Quản lý người dùng</h2>
                <p class="text-muted mb-0">Tổng số: <?php echo count($users); ?> người dùng</p>
            </div>
            <a href="user-add.php" class="btn btn-primary">
                <i class="bi bi-plus-circle me-2"></i>Thêm người dùng
            </a>
        </div>

        <?php if (isset($_GET['msg'])): ?>
        <div class="alert alert-success alert-modern">
            <i class="bi bi-check-circle-fill"></i>
            <span>Thao tác thành công!</span>
        </div>
        <?php endif; ?>

        <!-- Search -->
        <div class="card modern-card mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-10">
                        <input type="text" name="search" class="form-control" placeholder="Tìm kiếm theo tên, email, username..." value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-search me-2"></i>Tìm kiếm
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Users Table -->
        <div class="card modern-card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Người dùng</th>
                                <th>Email</th>
                                <th>Vai trò</th>
                                <th>Điểm</th>
                                <th>Câu hỏi</th>
                                <th>Trả lời</th>
                                <th>Ngày tạo</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $user): ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <img src="<?php echo htmlspecialchars($user['ANHDAIDIEN']); ?>" class="user-avatar me-3" alt="Avatar">
                                        <div>
                                            <div class="fw-semibold"><?php echo htmlspecialchars($user['HOTEN']); ?></div>
                                            <small class="text-muted">@<?php echo htmlspecialchars($user['TENDANGNHAP']); ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td><?php echo htmlspecialchars($user['EMAIL']); ?></td>
                                <td>
                                    <?php 
                                    $roles = explode(',', $user['VaiTro']);
                                    foreach ($roles as $role) {
                                        $badgeClass = $role == 'admin' ? 'bg-danger' : ($role == 'moderator' ? 'bg-warning' : 'bg-primary');
                                        echo "<span class='badge $badgeClass me-1'>$role</span>";
                                    }
                                    ?>
                                </td>
                                <td><span class="badge bg-success"><?php echo $user['DIEMDANHGIA']; ?></span></td>
                                <td><?php echo $user['SoCauHoi']; ?></td>
                                <td><?php echo $user['SoCauTraLoi']; ?></td>
                                <td><?php echo date('d/m/Y', strtotime($user['NGAYTAO'])); ?></td>
                                <td>
                                    <a href="user-edit.php?id=<?php echo $user['MANGUOIDUNG']; ?>" class="action-btn action-btn-edit" title="Sửa">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <a href="?delete=<?php echo $user['MANGUOIDUNG']; ?>" class="action-btn action-btn-delete" title="Xóa" onclick="return confirmDelete('Bạn có chắc muốn xóa người dùng này?')">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/admin_footer.php'; ?>
