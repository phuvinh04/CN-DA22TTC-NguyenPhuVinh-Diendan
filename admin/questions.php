<?php
$pageTitle = 'Quản lý câu hỏi';
require_once '../config/database.php';
require_once '../config/session.php';
requireAdmin();

// Xử lý xóa câu hỏi
if (isset($_GET['delete'])) {
    $questionId = $_GET['delete'];
    $conn->prepare("DELETE FROM CAUHOI WHERE MACAUHOI = ?")->execute([$questionId]);
    header('Location: questions.php?msg=deleted');
    exit();
}

// Lấy danh sách câu hỏi
$questions = $conn->query("
    SELECT ch.*, t.TENTHE, nd.HOTEN, d.NGAYDANG,
           (SELECT COUNT(*) FROM TRALOI WHERE MACAUHOI = ch.MACAUHOI) as SoTraLoi
    FROM CAUHOI ch
    JOIN TAG t ON ch.MATHE = t.MATHE
    JOIN DAT d ON ch.MACAUHOI = d.MACAUHOI
    JOIN NGUOIDUNG nd ON d.MANGUOIDUNG = nd.MANGUOIDUNG
    ORDER BY d.NGAYDANG DESC
")->fetchAll();

require_once '../includes/admin_header.php';
?>

<div class="admin-container">
    <div class="container-fluid py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="mb-1"><i class="bi bi-question-circle-fill me-2"></i>Quản lý câu hỏi</h2>
                <p class="text-muted mb-0">Tổng số: <?php echo count($questions); ?> câu hỏi</p>
            </div>
        </div>

        <?php if (isset($_GET['msg'])): ?>
        <div class="alert alert-success alert-modern">
            <i class="bi bi-check-circle-fill"></i>
            <span>Thao tác thành công!</span>
        </div>
        <?php endif; ?>

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
                                    <?php if ($q['TRANGTHAI'] == 'open'): ?>
                                        <span class="badge bg-success">Mở</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Đóng</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo date('d/m/Y H:i', strtotime($q['NGAYDANG'])); ?></td>
                                <td>
                                    <a href="../question-detail.php?id=<?php echo $q['MACAUHOI']; ?>" class="action-btn action-btn-edit" title="Xem">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="?delete=<?php echo $q['MACAUHOI']; ?>" class="action-btn action-btn-delete" title="Xóa" onclick="return confirmDelete('Bạn có chắc muốn xóa câu hỏi này?')">
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
