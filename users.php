<?php
$pageTitle = 'Thành viên';
require_once 'config/database.php';
require_once 'config/session.php';
require_once 'includes/badge_helper.php';

// Yêu cầu đăng nhập
$currentUser = getCurrentUser();
if (!$currentUser) {
    header('Location: login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
    exit();
}

require_once 'includes/header.php';

// Phân trang
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$perPage = 20;
$offset = ($page - 1) * $perPage;

// Tìm kiếm
$search = trim($_GET['q'] ?? '');

// Build query
$whereClause = "WHERE nd.TRANGTHAI = 'active'";
$params = [];

if ($search) {
    $whereClause .= " AND (nd.HOTEN LIKE ? OR nd.TENDANGNHAP LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

// Đếm tổng
$countQuery = "SELECT COUNT(*) FROM NGUOIDUNG nd $whereClause";
$stmt = $conn->prepare($countQuery);
$stmt->execute($params);
$totalUsers = $stmt->fetchColumn();
$totalPages = ceil($totalUsers / $perPage);

// Lấy danh sách users
$usersQuery = "SELECT 
    nd.MANGUOIDUNG,
    nd.HOTEN,
    nd.TENDANGNHAP,
    nd.ANHDAIDIEN,
    nd.DIEMDANHGIA,
    nd.NGAYTAO,
    (SELECT COUNT(*) FROM DAT WHERE MANGUOIDUNG = nd.MANGUOIDUNG) as SoCauHoi,
    (SELECT COUNT(*) FROM TRALOI WHERE MANGUOIDUNG = nd.MANGUOIDUNG) as SoTraLoi
FROM NGUOIDUNG nd
$whereClause
ORDER BY nd.DIEMDANHGIA DESC
LIMIT $perPage OFFSET $offset";

$stmt = $conn->prepare($usersQuery);
$stmt->execute($params);
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<main class="py-4">
    <div class="container">
        <div class="row mb-4">
            <div class="col-md-8">
                <h2><i class="bi bi-people-fill text-primary me-2"></i>Thành viên</h2>
                <p class="text-muted">Tìm thấy <?php echo number_format($totalUsers); ?> thành viên</p>
            </div>
            <div class="col-md-4">
                <form method="GET" action="">
                    <div class="input-group">
                        <input type="text" class="form-control" name="q" placeholder="Tìm thành viên..." value="<?php echo htmlspecialchars($search); ?>">
                        <button class="btn btn-primary" type="submit">
                            <i class="bi bi-search"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <?php if (empty($users)): ?>
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="bi bi-people text-muted" style="font-size: 4rem;"></i>
                <h4 class="mt-3">Không tìm thấy thành viên</h4>
                <p class="text-muted">Thử tìm kiếm với từ khóa khác</p>
            </div>
        </div>
        <?php else: ?>
        <div class="row">
            <?php foreach ($users as $index => $user): ?>
            <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                <div class="card h-100 text-center hover-shadow">
                    <div class="card-body">
                        <div class="position-relative d-inline-block mb-3">
                            <?php echo renderAvatarWithFrame($user['ANHDAIDIEN'], $user['MANGUOIDUNG'], 'lg', true); ?>
                            <?php if ($index < 3 && $page == 1 && !$search): ?>
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-warning">
                                <i class="bi bi-trophy-fill"></i>
                            </span>
                            <?php endif; ?>
                        </div>
                        <h6 class="mb-1">
                            <a href="profile.php?id=<?php echo $user['MANGUOIDUNG']; ?>" class="text-decoration-none">
                                <?php echo htmlspecialchars($user['HOTEN']); ?>
                            </a>
                        </h6>
                        <p class="text-muted small mb-2">@<?php echo htmlspecialchars($user['TENDANGNHAP']); ?></p>
                        <div class="mb-2">
                            <span class="badge bg-primary"><?php echo number_format($user['DIEMDANHGIA']); ?> điểm</span>
                        </div>
                        <div class="d-flex justify-content-center gap-3 small text-muted">
                            <span><i class="bi bi-question-circle me-1"></i><?php echo $user['SoCauHoi']; ?></span>
                            <span><i class="bi bi-chat-left-text me-1"></i><?php echo $user['SoTraLoi']; ?></span>
                        </div>
                    </div>
                    <div class="card-footer bg-transparent border-0">
                        <a href="profile.php?id=<?php echo $user['MANGUOIDUNG']; ?>" class="btn btn-outline-primary btn-sm w-100">
                            Xem hồ sơ
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
        <nav aria-label="Page navigation">
            <ul class="pagination justify-content-center">
                <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                    <a class="page-link" href="?page=<?php echo $page - 1; ?><?php echo $search ? '&q=' . urlencode($search) : ''; ?>">Trước</a>
                </li>
                
                <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                    <a class="page-link" href="?page=<?php echo $i; ?><?php echo $search ? '&q=' . urlencode($search) : ''; ?>"><?php echo $i; ?></a>
                </li>
                <?php endfor; ?>
                
                <li class="page-item <?php echo $page >= $totalPages ? 'disabled' : ''; ?>">
                    <a class="page-link" href="?page=<?php echo $page + 1; ?><?php echo $search ? '&q=' . urlencode($search) : ''; ?>">Sau</a>
                </li>
            </ul>
        </nav>
        <?php endif; ?>
        <?php endif; ?>
    </div>
</main>

<style>
.hover-shadow {
    transition: all 0.3s ease;
}
.hover-shadow:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
}
</style>

<?php require_once 'includes/footer.php'; ?>
