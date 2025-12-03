<?php
$pageTitle = 'Thành viên';
require_once 'config/database.php';
require_once 'includes/header.php';

// Phân trang
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 20;
$offset = ($page - 1) * $perPage;

// Tìm kiếm
$searchQuery = $_GET['q'] ?? '';
$whereClause = "WHERE TRANGTHAI = 'active'";
$params = [];

if ($searchQuery) {
    $whereClause .= " AND (HOTEN LIKE ? OR TENDANGNHAP LIKE ?)";
    $params[] = "%$searchQuery%";
    $params[] = "%$searchQuery%";
}

// Đếm tổng số users
$countQuery = "SELECT COUNT(*) FROM NGUOIDUNG $whereClause";
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
    nd.TIEUSU,
    nd.DIEMDANHGIA,
    nd.NGAYTAO,
    (SELECT COUNT(*) FROM DAT d WHERE d.MANGUOIDUNG = nd.MANGUOIDUNG) AS SoCauHoi,
    (SELECT COUNT(*) FROM TRALOI tl WHERE tl.MANGUOIDUNG = nd.MANGUOIDUNG) AS SoCauTraLoi
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
                <p class="text-muted"><?php echo number_format($totalUsers); ?> thành viên đang hoạt động</p>
            </div>
            <div class="col-md-4">
                <form method="GET" action="">
                    <div class="input-group">
                        <input type="text" class="form-control" name="q" placeholder="Tìm kiếm thành viên..." value="<?php echo htmlspecialchars($searchQuery); ?>">
                        <button class="btn btn-primary" type="submit">
                            <i class="bi bi-search"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="row">
            <?php foreach ($users as $user): ?>
            <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                <div class="card h-100 hover-shadow">
                    <div class="card-body text-center">
                        <img src="<?php echo htmlspecialchars($user['ANHDAIDIEN']); ?>" alt="Avatar" class="user-avatar-lg mb-3">
                        <h5 class="mb-1">
                            <a href="profile.php?id=<?php echo $user['MANGUOIDUNG']; ?>" class="text-decoration-none text-dark">
                                <?php echo htmlspecialchars($user['HOTEN']); ?>
                            </a>
                        </h5>
                        <p class="text-muted small mb-2">@<?php echo htmlspecialchars($user['TENDANGNHAP']); ?></p>
                        
                        <?php if ($user['TIEUSU']): ?>
                        <p class="text-muted small mb-3">
                            <?php echo mb_substr(htmlspecialchars($user['TIEUSU']), 0, 60); ?>...
                        </p>
                        <?php endif; ?>

                        <div class="d-flex justify-content-around mb-3">
                            <div>
                                <div class="fw-bold text-primary"><?php echo number_format($user['DIEMDANHGIA']); ?></div>
                                <small class="text-muted">Điểm</small>
                            </div>
                            <div>
                                <div class="fw-bold text-success"><?php echo $user['SoCauHoi']; ?></div>
                                <small class="text-muted">Câu hỏi</small>
                            </div>
                            <div>
                                <div class="fw-bold text-info"><?php echo $user['SoCauTraLoi']; ?></div>
                                <small class="text-muted">Trả lời</small>
                            </div>
                        </div>

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
                    <a class="page-link" href="?page=<?php echo $page - 1; ?><?php echo $searchQuery ? '&q=' . urlencode($searchQuery) : ''; ?>">Trước</a>
                </li>
                
                <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                    <a class="page-link" href="?page=<?php echo $i; ?><?php echo $searchQuery ? '&q=' . urlencode($searchQuery) : ''; ?>"><?php echo $i; ?></a>
                </li>
                <?php endfor; ?>
                
                <li class="page-item <?php echo $page >= $totalPages ? 'disabled' : ''; ?>">
                    <a class="page-link" href="?page=<?php echo $page + 1; ?><?php echo $searchQuery ? '&q=' . urlencode($searchQuery) : ''; ?>">Sau</a>
                </li>
            </ul>
        </nav>
        <?php endif; ?>
    </div>
</main>

<?php require_once 'includes/footer.php'; ?>
