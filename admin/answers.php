<?php
$pageTitle = 'Quản lý câu trả lời';
require_once '../config/database.php';
require_once '../config/session.php';
requireAdmin();

$currentUser = getCurrentUser();

// Kiểm tra cột TRANGTHAI có tồn tại không
$hasStatusColumn = false;
try {
    $checkColumn = $conn->query("SHOW COLUMNS FROM TRALOI LIKE 'TRANGTHAI'");
    $hasStatusColumn = $checkColumn->rowCount() > 0;
} catch (Exception $e) {
    $hasStatusColumn = false;
}

// Nếu chưa có cột, thêm vào
if (!$hasStatusColumn) {
    try {
        $conn->exec("ALTER TABLE TRALOI ADD COLUMN TRANGTHAI VARCHAR(20) DEFAULT 'pending'");
        // Cập nhật các câu trả lời cũ thành đã duyệt
        $conn->exec("UPDATE TRALOI SET TRANGTHAI = 'approved' WHERE TRANGTHAI = 'pending' OR TRANGTHAI IS NULL");
        $conn->exec("ALTER TABLE TRALOI ADD INDEX idx_trangthai (TRANGTHAI)");
        $hasStatusColumn = true;
    } catch (Exception $e) {}
}

// Xử lý duyệt câu trả lời
if (isset($_GET['approve'])) {
    $answerId = $_GET['approve'];
    $conn->prepare("UPDATE TRALOI SET TRANGTHAI = 'approved' WHERE MACAUTRALOI = ?")->execute([$answerId]);
    
    // Gửi thông báo cho người trả lời
    $stmt = $conn->prepare("SELECT MANGUOIDUNG, MACAUHOI FROM TRALOI WHERE MACAUTRALOI = ?");
    $stmt->execute([$answerId]);
    $answerInfo = $stmt->fetch();
    if ($answerInfo) {
        $notifId = 'TB' . time() . rand(100, 999);
        $conn->prepare("INSERT INTO THONGBAO (MATHONGBAO, MANGUOIDUNG, LOAI, TIEUDE, LINK, DADOC, NGAYTAO) VALUES (?, ?, 'system', ?, ?, 0, NOW())")
            ->execute([$notifId, $answerInfo['MANGUOIDUNG'], 'Câu trả lời của bạn đã được duyệt!', 'question-detail.php?id=' . $answerInfo['MACAUHOI']]);
    }
    
    header('Location: answers.php?msg=approved');
    exit();
}

// Xử lý từ chối câu trả lời
if (isset($_GET['reject'])) {
    $answerId = $_GET['reject'];
    $conn->prepare("UPDATE TRALOI SET TRANGTHAI = 'rejected' WHERE MACAUTRALOI = ?")->execute([$answerId]);
    
    // Gửi thông báo
    $stmt = $conn->prepare("SELECT MANGUOIDUNG FROM TRALOI WHERE MACAUTRALOI = ?");
    $stmt->execute([$answerId]);
    $ownerId = $stmt->fetchColumn();
    if ($ownerId) {
        $notifId = 'TB' . time() . rand(100, 999);
        $conn->prepare("INSERT INTO THONGBAO (MATHONGBAO, MANGUOIDUNG, LOAI, TIEUDE, LINK, DADOC, NGAYTAO) VALUES (?, ?, 'system', ?, ?, 0, NOW())")
            ->execute([$notifId, $ownerId, 'Câu trả lời của bạn không được duyệt.', '']);
    }
    
    header('Location: answers.php?msg=rejected');
    exit();
}

// Xử lý xóa câu trả lời
if (isset($_GET['delete'])) {
    $answerId = $_GET['delete'];
    try {
        $conn->beginTransaction();
        $conn->exec("SET FOREIGN_KEY_CHECKS = 0");
        $conn->prepare("DELETE v FROM VOTE v JOIN BINHCHONCAUTRALOI bc ON v.MAVOTE = bc.MAVOTE WHERE bc.MACAUTRALOI = ?")->execute([$answerId]);
        $conn->prepare("DELETE FROM BINHCHONCAUTRALOI WHERE MACAUTRALOI = ?")->execute([$answerId]);
        $conn->prepare("DELETE FROM TRALOI WHERE MACAUTRALOI_CHA = ?")->execute([$answerId]);
        $conn->prepare("DELETE FROM TRALOI WHERE MACAUTRALOI = ?")->execute([$answerId]);
        $conn->exec("SET FOREIGN_KEY_CHECKS = 1");
        $conn->commit();
        header('Location: answers.php?msg=deleted');
    } catch (Exception $e) {
        $conn->rollBack();
        header('Location: answers.php?msg=error');
    }
    exit();
}

// Lọc theo trạng thái
$statusFilter = $_GET['status'] ?? 'all';
$whereClause = "";
if ($hasStatusColumn) {
    if ($statusFilter === 'pending') {
        $whereClause = "WHERE tl.TRANGTHAI = 'pending'";
    } elseif ($statusFilter === 'approved') {
        $whereClause = "WHERE tl.TRANGTHAI = 'approved'";
    } elseif ($statusFilter === 'rejected') {
        $whereClause = "WHERE tl.TRANGTHAI = 'rejected'";
    }
}

// Lấy danh sách câu trả lời
$statusSelect = $hasStatusColumn ? "tl.TRANGTHAI" : "'approved' as TRANGTHAI";
$answers = $conn->query("
    SELECT tl.MACAUTRALOI, tl.NOIDUNGTL, tl.NGAYTL, tl.MACAUHOI, $statusSelect,
           nd.HOTEN, nd.MANGUOIDUNG,
           ch.TIEUDE
    FROM TRALOI tl
    JOIN NGUOIDUNG nd ON tl.MANGUOIDUNG = nd.MANGUOIDUNG
    JOIN CAUHOI ch ON tl.MACAUHOI = ch.MACAUHOI
    $whereClause
    ORDER BY CASE WHEN tl.TRANGTHAI = 'pending' THEN 0 ELSE 1 END, tl.NGAYTL DESC
    LIMIT 200
")->fetchAll();

// Đếm số câu trả lời chờ duyệt
$pendingCount = 0;
if ($hasStatusColumn) {
    $pendingCount = $conn->query("SELECT COUNT(*) FROM TRALOI WHERE TRANGTHAI = 'pending'")->fetchColumn();
}

require_once '../includes/admin_header.php';
?>

<div class="admin-container">
    <div class="container-fluid py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="mb-1"><i class="bi bi-chat-left-text-fill me-2"></i>Quản lý câu trả lời</h2>
                <p class="text-muted mb-0">Tổng số: <?php echo count($answers); ?> câu trả lời
                    <?php if ($pendingCount > 0): ?>
                        <span class="badge bg-warning ms-2"><?php echo $pendingCount; ?> chờ duyệt</span>
                    <?php endif; ?>
                </p>
            </div>
        </div>

        <?php if (isset($_GET['msg'])): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <i class="bi bi-check-circle-fill me-2"></i>
            <?php 
            $msg = $_GET['msg'];
            if ($msg === 'approved') echo 'Đã duyệt câu trả lời thành công!';
            elseif ($msg === 'rejected') echo 'Đã từ chối câu trả lời!';
            elseif ($msg === 'deleted') echo 'Đã xóa câu trả lời!';
            else echo 'Thao tác thành công!';
            ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <!-- Filter Tabs -->
        <ul class="nav nav-tabs mb-4">
            <li class="nav-item">
                <a class="nav-link <?php echo $statusFilter === 'all' ? 'active' : ''; ?>" href="?status=all">Tất cả</a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $statusFilter === 'pending' ? 'active' : ''; ?>" href="?status=pending">
                    Chờ duyệt <?php if ($pendingCount > 0): ?><span class="badge bg-warning"><?php echo $pendingCount; ?></span><?php endif; ?>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $statusFilter === 'approved' ? 'active' : ''; ?>" href="?status=approved">Đã duyệt</a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $statusFilter === 'rejected' ? 'active' : ''; ?>" href="?status=rejected">Từ chối</a>
            </li>
        </ul>

        <div class="card modern-card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th style="width: 35%">Nội dung</th>
                                <th>Câu hỏi</th>
                                <th>Người trả lời</th>
                                <th>Trạng thái</th>
                                <th>Ngày đăng</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($answers as $a): ?>
                            <tr>
                                <td>
                                    <div class="text-truncate" style="max-width: 300px;" title="<?php echo htmlspecialchars($a['NOIDUNGTL']); ?>">
                                        <?php echo htmlspecialchars(mb_substr($a['NOIDUNGTL'], 0, 100)); ?>...
                                    </div>
                                </td>
                                <td>
                                    <a href="../question-detail.php?id=<?php echo $a['MACAUHOI']; ?>" class="text-decoration-none" target="_blank">
                                        <?php echo htmlspecialchars(mb_substr($a['TIEUDE'], 0, 40)); ?>...
                                    </a>
                                </td>
                                <td><?php echo htmlspecialchars($a['HOTEN']); ?></td>
                                <td>
                                    <?php if ($a['TRANGTHAI'] == 'pending'): ?>
                                        <span class="badge bg-warning text-dark">Chờ duyệt</span>
                                    <?php elseif ($a['TRANGTHAI'] == 'approved'): ?>
                                        <span class="badge bg-success">Đã duyệt</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">Từ chối</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo date('d/m/Y H:i', strtotime($a['NGAYTL'])); ?></td>
                                <td>
                                    <a href="../question-detail.php?id=<?php echo $a['MACAUHOI']; ?>#answer-<?php echo $a['MACAUTRALOI']; ?>" class="action-btn action-btn-edit" title="Xem" target="_blank">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <?php if ($a['TRANGTHAI'] == 'pending'): ?>
                                        <a href="?approve=<?php echo $a['MACAUTRALOI']; ?>" class="action-btn" style="background:#d4edda;color:#198754;" title="Duyệt" onclick="return confirm('Duyệt câu trả lời này?')">
                                            <i class="bi bi-check-lg"></i>
                                        </a>
                                        <a href="?reject=<?php echo $a['MACAUTRALOI']; ?>" class="action-btn" style="background:#f8d7da;color:#dc3545;" title="Từ chối" onclick="return confirm('Từ chối câu trả lời này?')">
                                            <i class="bi bi-x-lg"></i>
                                        </a>
                                    <?php endif; ?>
                                    <a href="?delete=<?php echo $a['MACAUTRALOI']; ?>" class="action-btn action-btn-delete" title="Xóa" onclick="return confirm('Bạn có chắc muốn xóa câu trả lời này?')">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($answers)): ?>
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">Không có câu trả lời nào</td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/admin_footer.php'; ?>
