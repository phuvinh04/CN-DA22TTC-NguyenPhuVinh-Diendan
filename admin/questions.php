<?php
$pageTitle = 'Quản lý câu hỏi';
require_once '../config/database.php';
require_once '../config/session.php';
requireAdmin();

$currentUser = getCurrentUser();

// Xử lý duyệt câu hỏi
if (isset($_GET['approve'])) {
    $questionId = $_GET['approve'];
    $conn->prepare("UPDATE CAUHOI SET TRANGTHAI = 'open' WHERE MACAUHOI = ?")->execute([$questionId]);
    
    // Gửi thông báo cho người đặt câu hỏi
    $stmt = $conn->prepare("SELECT MANGUOIDUNG FROM DAT WHERE MACAUHOI = ?");
    $stmt->execute([$questionId]);
    $ownerId = $stmt->fetchColumn();
    if ($ownerId) {
        $notifId = 'TB' . time() . rand(100, 999);
        $conn->prepare("INSERT INTO THONGBAO (MATHONGBAO, MANGUOIDUNG, LOAI, TIEUDE, LINK, DADOC, NGAYTAO) VALUES (?, ?, 'system', ?, ?, 0, NOW())")
            ->execute([$notifId, $ownerId, 'Câu hỏi của bạn đã được duyệt!', 'question-detail.php?id=' . $questionId]);
    }
    
    header('Location: questions.php?msg=approved');
    exit();
}

// Xử lý từ chối câu hỏi
if (isset($_GET['reject'])) {
    $questionId = $_GET['reject'];
    $conn->prepare("UPDATE CAUHOI SET TRANGTHAI = 'rejected' WHERE MACAUHOI = ?")->execute([$questionId]);
    
    // Gửi thông báo cho người đặt câu hỏi
    $stmt = $conn->prepare("SELECT MANGUOIDUNG FROM DAT WHERE MACAUHOI = ?");
    $stmt->execute([$questionId]);
    $ownerId = $stmt->fetchColumn();
    if ($ownerId) {
        $notifId = 'TB' . time() . rand(100, 999);
        $conn->prepare("INSERT INTO THONGBAO (MATHONGBAO, MANGUOIDUNG, LOAI, TIEUDE, LINK, DADOC, NGAYTAO) VALUES (?, ?, 'system', ?, ?, 0, NOW())")
            ->execute([$notifId, $ownerId, 'Câu hỏi của bạn không được duyệt. Vui lòng kiểm tra lại nội dung.', '']);
    }
    
    header('Location: questions.php?msg=rejected');
    exit();
}

// Xử lý xóa câu hỏi
if (isset($_GET['delete'])) {
    $questionId = $_GET['delete'];
    
    try {
        $conn->beginTransaction();
        
        // Xóa các bản ghi liên quan trước
        // Tắt kiểm tra foreign key
        $conn->exec("SET FOREIGN_KEY_CHECKS = 0");
        
        // Xóa vote câu hỏi
        $conn->prepare("DELETE v FROM VOTE v JOIN BINHCHONCAUHOI bc ON v.MAVOTE = bc.MAVOTE WHERE bc.MACAUHOI = ?")->execute([$questionId]);
        $conn->prepare("DELETE FROM BINHCHONCAUHOI WHERE MACAUHOI = ?")->execute([$questionId]);
        
        // Xóa vote câu trả lời
        $conn->prepare("DELETE v FROM VOTE v JOIN BINHCHONCAUTRALOI bc ON v.MAVOTE = bc.MAVOTE JOIN TRALOI tl ON bc.MACAUTRALOI = tl.MACAUTRALOI WHERE tl.MACAUHOI = ?")->execute([$questionId]);
        $conn->prepare("DELETE bc FROM BINHCHONCAUTRALOI bc JOIN TRALOI tl ON bc.MACAUTRALOI = tl.MACAUTRALOI WHERE tl.MACAUHOI = ?")->execute([$questionId]);
        
        // Xóa câu trả lời
        $conn->prepare("DELETE FROM TRALOI WHERE MACAUHOI = ?")->execute([$questionId]);
        
        // Xóa lượt xem
        $conn->prepare("DELETE FROM LUOTXEM WHERE MACAUHOI = ?")->execute([$questionId]);
        
        // Xóa chủ đề tag liên quan (nếu có)
        try {
            $conn->prepare("DELETE FROM CHUDETAG WHERE MACAUHOI = ?")->execute([$questionId]);
        } catch (Exception $e) {}
        
        // Xóa báo cáo liên quan (nếu có)
        try {
            $conn->prepare("DELETE FROM BAOCAO WHERE MACAUHOI = ?")->execute([$questionId]);
        } catch (Exception $e) {}
        
        // Xóa người đặt câu hỏi
        $conn->prepare("DELETE FROM DAT WHERE MACAUHOI = ?")->execute([$questionId]);
        
        // Xóa câu hỏi
        $conn->prepare("DELETE FROM CAUHOI WHERE MACAUHOI = ?")->execute([$questionId]);
        
        // Bật lại kiểm tra foreign key
        $conn->exec("SET FOREIGN_KEY_CHECKS = 1");
        
        $conn->commit();
        header('Location: questions.php?msg=deleted');
    } catch (Exception $e) {
        $conn->rollBack();
        header('Location: questions.php?msg=error&detail=' . urlencode($e->getMessage()));
    }
    exit();
}

// Lọc theo trạng thái
$statusFilter = $_GET['status'] ?? 'all';

// Lấy danh sách câu hỏi
$whereClause = "";
if ($statusFilter === 'pending') {
    $whereClause = "WHERE ch.TRANGTHAI = 'pending'";
} elseif ($statusFilter === 'approved') {
    $whereClause = "WHERE ch.TRANGTHAI IN ('open', 'closed')";
} elseif ($statusFilter === 'rejected') {
    $whereClause = "WHERE ch.TRANGTHAI = 'rejected'";
}

$questions = $conn->query("
    SELECT ch.*, t.TENTHE, nd.HOTEN, nd.MANGUOIDUNG as USER_ID, d.NGAYDANG,
           (SELECT COUNT(*) FROM TRALOI WHERE MACAUHOI = ch.MACAUHOI) as SoTraLoi
    FROM CAUHOI ch
    JOIN TAG t ON ch.MATHE = t.MATHE
    JOIN DAT d ON ch.MACAUHOI = d.MACAUHOI
    JOIN NGUOIDUNG nd ON d.MANGUOIDUNG = nd.MANGUOIDUNG
    $whereClause
    ORDER BY CASE WHEN ch.TRANGTHAI = 'pending' THEN 0 ELSE 1 END, d.NGAYDANG DESC
")->fetchAll();

// Đếm số câu hỏi chờ duyệt
$pendingCount = $conn->query("SELECT COUNT(*) FROM CAUHOI WHERE TRANGTHAI = 'pending'")->fetchColumn();

require_once '../includes/admin_header.php';
?>

<div class="admin-container">
    <div class="container-fluid py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="mb-1"><i class="bi bi-question-circle-fill me-2"></i>Quản lý câu hỏi</h2>
                <p class="text-muted mb-0">Tổng số: <?php echo count($questions); ?> câu hỏi
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
            if ($msg === 'approved') echo 'Đã duyệt câu hỏi thành công!';
            elseif ($msg === 'rejected') echo 'Đã từ chối câu hỏi!';
            elseif ($msg === 'deleted') echo 'Đã xóa câu hỏi!';
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
                                <th>Tiêu đề</th>
                                <th>Tag</th>
                                <th>Người đặt</th>
                                <th>Trả lời</th>
                                <th>Lượt xem</th>
                                <th>Trạng thái</th>
                                <th>Ngày đăng</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($questions as $q): ?>
                            <tr>
                                <td>
                                    <a href="../question-detail.php?id=<?php echo $q['MACAUHOI']; ?>" class="text-decoration-none text-dark fw-semibold">
                                        <?php echo htmlspecialchars($q['TIEUDE']); ?>
                                    </a>
                                </td>
                                <td><span class="tag-sm"><?php echo htmlspecialchars($q['TENTHE']); ?></span></td>
                                <td><?php echo htmlspecialchars($q['HOTEN']); ?></td>
                                <td><span class="badge bg-info"><?php echo $q['SoTraLoi']; ?></span></td>
                                <td><?php echo number_format($q['LUOTXEM']); ?></td>
                                <td>
                                    <?php if ($q['TRANGTHAI'] == 'pending'): ?>
                                        <span class="badge bg-warning text-dark">Chờ duyệt</span>
                                    <?php elseif ($q['TRANGTHAI'] == 'open'): ?>
                                        <span class="badge bg-success">Đã duyệt</span>
                                    <?php elseif ($q['TRANGTHAI'] == 'rejected'): ?>
                                        <span class="badge bg-danger">Từ chối</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Đóng</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo date('d/m/Y H:i', strtotime($q['NGAYDANG'])); ?></td>
                                <td>
                                    <a href="../question-detail.php?id=<?php echo $q['MACAUHOI']; ?>" class="action-btn action-btn-edit" title="Xem">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <?php if ($q['TRANGTHAI'] == 'pending'): ?>
                                        <a href="?approve=<?php echo $q['MACAUHOI']; ?>" class="action-btn" style="background:#d4edda;color:#198754;" title="Duyệt" onclick="return confirm('Duyệt câu hỏi này?')">
                                            <i class="bi bi-check-lg"></i>
                                        </a>
                                        <a href="?reject=<?php echo $q['MACAUHOI']; ?>" class="action-btn" style="background:#f8d7da;color:#dc3545;" title="Từ chối" onclick="return confirm('Từ chối câu hỏi này?')">
                                            <i class="bi bi-x-lg"></i>
                                        </a>
                                    <?php endif; ?>
                                    <a href="?delete=<?php echo $q['MACAUHOI']; ?>" class="action-btn action-btn-delete" title="Xóa" onclick="return confirm('Bạn có chắc muốn xóa câu hỏi này?')">
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
