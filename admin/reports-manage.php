<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$pageTitle = 'Quản lý báo cáo';
require_once '../config/database.php';
require_once '../config/session.php';
requireAdmin();

// Tạo bảng nếu chưa có
$conn->exec("CREATE TABLE IF NOT EXISTS BAOCAO (
    MABAOCAO VARCHAR(50) NOT NULL,
    MANGUOIDUNG VARCHAR(100) NOT NULL,
    LOAI VARCHAR(20) NOT NULL,
    MAID VARCHAR(50) NOT NULL,
    LYDO TEXT,
    TRANGTHAI VARCHAR(20) DEFAULT 'pending',
    NGAYTAO DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (MABAOCAO)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

// Xử lý actions
if (isset($_GET['action']) && isset($_GET['id'])) {
    $reportId = $_GET['id'];
    $action = $_GET['action'];
    
    if ($action === 'resolve') {
        $conn->prepare("UPDATE BAOCAO SET TRANGTHAI = 'resolved' WHERE MABAOCAO = ?")->execute([$reportId]);
    } elseif ($action === 'dismiss') {
        $conn->prepare("UPDATE BAOCAO SET TRANGTHAI = 'dismissed' WHERE MABAOCAO = ?")->execute([$reportId]);
    } elseif ($action === 'delete') {
        $conn->prepare("DELETE FROM BAOCAO WHERE MABAOCAO = ?")->execute([$reportId]);
    }
    
    header('Location: reports-manage.php?msg=success');
    exit();
}

// Lấy danh sách báo cáo
$statusFilter = $_GET['status'] ?? 'pending';
$whereClause = $statusFilter !== 'all' ? "WHERE bc.TRANGTHAI = '$statusFilter'" : "";

$reports = $conn->query("
    SELECT bc.*, nd.HOTEN as NguoiBaoCao
    FROM BAOCAO bc
    JOIN NGUOIDUNG nd ON bc.MANGUOIDUNG COLLATE utf8mb4_unicode_ci = nd.MANGUOIDUNG COLLATE utf8mb4_unicode_ci
    $whereClause
    ORDER BY bc.NGAYTAO DESC
")->fetchAll(PDO::FETCH_ASSOC);

$pendingCount = $conn->query("SELECT COUNT(*) FROM BAOCAO WHERE TRANGTHAI = 'pending'")->fetchColumn();

require_once '../includes/admin_header.php';
?>

<div class="admin-container">
    <div class="container-fluid py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="mb-1"><i class="bi bi-flag-fill me-2"></i>Quản lý báo cáo</h2>
                <p class="text-muted mb-0">
                    <?php echo $pendingCount; ?> báo cáo đang chờ xử lý
                </p>
            </div>
        </div>

        <?php if (isset($_GET['msg'])): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <i class="bi bi-check-circle me-2"></i>Thao tác thành công!
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <!-- Filter Tabs -->
        <ul class="nav nav-tabs mb-4">
            <li class="nav-item">
                <a class="nav-link <?php echo $statusFilter === 'pending' ? 'active' : ''; ?>" href="?status=pending">
                    Chờ xử lý <?php if ($pendingCount > 0): ?><span class="badge bg-warning"><?php echo $pendingCount; ?></span><?php endif; ?>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $statusFilter === 'resolved' ? 'active' : ''; ?>" href="?status=resolved">Đã xử lý</a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $statusFilter === 'dismissed' ? 'active' : ''; ?>" href="?status=dismissed">Đã bỏ qua</a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $statusFilter === 'all' ? 'active' : ''; ?>" href="?status=all">Tất cả</a>
            </li>
        </ul>

        <?php if (empty($reports)): ?>
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="bi bi-flag text-muted" style="font-size: 4rem;"></i>
                <h4 class="mt-3">Không có báo cáo nào</h4>
            </div>
        </div>
        <?php else: ?>
        <div class="card modern-card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Loại</th>
                                <th>Người báo cáo</th>
                                <th>Lý do</th>
                                <th>Trạng thái</th>
                                <th>Ngày tạo</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($reports as $report): ?>
                            <tr>
                                <td>
                                    <span class="badge bg-<?php echo $report['LOAI'] === 'question' ? 'primary' : 'info'; ?>">
                                        <?php echo $report['LOAI'] === 'question' ? 'Câu hỏi' : 'Câu trả lời'; ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars($report['NguoiBaoCao']); ?></td>
                                <td><?php echo htmlspecialchars(mb_substr($report['LYDO'], 0, 50)); ?>...</td>
                                <td>
                                    <?php if ($report['TRANGTHAI'] === 'pending'): ?>
                                        <span class="badge bg-warning text-dark">Chờ xử lý</span>
                                    <?php elseif ($report['TRANGTHAI'] === 'resolved'): ?>
                                        <span class="badge bg-success">Đã xử lý</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Đã bỏ qua</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo date('d/m/Y H:i', strtotime($report['NGAYTAO'])); ?></td>
                                <td>
                                    <?php if ($report['LOAI'] === 'question'): ?>
                                    <a href="../question-detail.php?id=<?php echo $report['MAID']; ?>" class="btn btn-sm btn-outline-primary" target="_blank">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <?php endif; ?>
                                    <?php if ($report['TRANGTHAI'] === 'pending'): ?>
                                    <a href="?action=resolve&id=<?php echo $report['MABAOCAO']; ?>" class="btn btn-sm btn-success" onclick="return confirm('Đánh dấu đã xử lý?')">
                                        <i class="bi bi-check"></i>
                                    </a>
                                    <a href="?action=dismiss&id=<?php echo $report['MABAOCAO']; ?>" class="btn btn-sm btn-secondary" onclick="return confirm('Bỏ qua báo cáo này?')">
                                        <i class="bi bi-x"></i>
                                    </a>
                                    <?php endif; ?>
                                    <a href="?action=delete&id=<?php echo $report['MABAOCAO']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Xóa báo cáo này?')">
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
        <?php endif; ?>
    </div>
</div>

<?php require_once '../includes/admin_footer.php'; ?>
