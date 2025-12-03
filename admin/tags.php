<?php
$pageTitle = 'Quản lý Tags';
require_once '../config/database.php';
require_once '../config/session.php';
requireAdmin();

// Xử lý thêm/sửa/xóa tag
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_tag'])) {
        $tagId = 'TAG' . time();
        $tagName = trim($_POST['tag_name']);
        $tagDesc = trim($_POST['tag_desc']);
        $categoryId = $_POST['category_id'];
        
        $stmt = $conn->prepare("INSERT INTO TAG (MATHE, TENTHE, MOTA, MACN) VALUES (?, ?, ?, ?)");
        $stmt->execute([$tagId, $tagName, $tagDesc, $categoryId]);
        header('Location: tags.php?msg=added');
        exit();
    }
}

if (isset($_GET['delete'])) {
    $tagId = $_GET['delete'];
    $conn->prepare("DELETE FROM TAG WHERE MATHE = ?")->execute([$tagId]);
    header('Location: tags.php?msg=deleted');
    exit();
}

// Lấy danh sách tags
$tags = $conn->query("
    SELECT t.*, cn.TENCN,
           (SELECT COUNT(*) FROM CAUHOI WHERE MATHE = t.MATHE) as SoCauHoi
    FROM TAG t
    LEFT JOIN CHUYENNGHANH cn ON t.MACN = cn.MACN
    ORDER BY SoCauHoi DESC
")->fetchAll();

$categories = $conn->query("SELECT * FROM CHUYENNGHANH")->fetchAll();

require_once '../includes/admin_header.php';
?>

<div class="admin-container">
    <div class="container-fluid py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="mb-1"><i class="bi bi-tags-fill me-2"></i>Quản lý Tags</h2>
                <p class="text-muted mb-0">Tổng số: <?php echo count($tags); ?> tags</p>
            </div>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addTagModal">
                <i class="bi bi-plus-circle me-2"></i>Thêm Tag
            </button>
        </div>

        <?php if (isset($_GET['msg'])): ?>
        <div class="alert alert-success alert-modern">
            <i class="bi bi-check-circle-fill"></i>
            <span>Thao tác thành công!</span>
        </div>
        <?php endif; ?>

        <div class="row g-3">
            <?php foreach ($tags as $tag): ?>
            <div class="col-md-6 col-lg-4">
                <div class="card modern-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <h5 class="mb-0"><?php echo htmlspecialchars($tag['TENTHE']); ?></h5>
                            <div>
                                <a href="?delete=<?php echo $tag['MATHE']; ?>" class="action-btn action-btn-delete" onclick="return confirmDelete('Xóa tag này?')">
                                    <i class="bi bi-trash"></i>
                                </a>
                            </div>
                        </div>
                        <p class="text-muted small mb-3"><?php echo htmlspecialchars($tag['MOTA'] ?? 'Không có mô tả'); ?></p>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="badge bg-primary"><?php echo $tag['SoCauHoi']; ?> câu hỏi</span>
                            <small class="text-muted"><?php echo htmlspecialchars($tag['TENCN'] ?? 'N/A'); ?></small>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- Add Tag Modal -->
<div class="modal fade" id="addTagModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Thêm Tag mới</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Tên Tag</label>
                        <input type="text" name="tag_name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Mô tả</label>
                        <textarea name="tag_desc" class="form-control" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Chuyên ngành</label>
                        <select name="category_id" class="form-select" required>
                            <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo $cat['MACN']; ?>"><?php echo htmlspecialchars($cat['TENCN']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" name="add_tag" class="btn btn-primary">Thêm Tag</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once '../includes/admin_footer.php'; ?>
