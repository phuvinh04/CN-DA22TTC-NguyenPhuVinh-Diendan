<?php
$pageTitle = 'Đặt câu hỏi';
require_once 'config/database.php';
require_once 'config/session.php';

requireLogin();
$currentUser = getCurrentUser();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $tagId = $_POST['tag'] ?? '';
    
    if (empty($title) || empty($content) || empty($tagId)) {
        $error = 'Vui lòng nhập đầy đủ thông tin!';
    } elseif (strlen($title) < 10) {
        $error = 'Tiêu đề phải có ít nhất 10 ký tự!';
    } elseif (strlen($content) < 20) {
        $error = 'Nội dung phải có ít nhất 20 ký tự!';
    } else {
        $questionId = 'CH' . time();
        
        try {
            $conn->beginTransaction();
            
            // Thêm câu hỏi
            $stmt = $conn->prepare("INSERT INTO CAUHOI (MACAUHOI, MATHE, TIEUDE, TRANGTHAI, LUOTXEM) VALUES (?, ?, ?, 'open', 0)");
            $stmt->execute([$questionId, $tagId, $title]);
            
            // Thêm người đặt câu hỏi
            $stmt = $conn->prepare("INSERT INTO DAT (MANGUOIDUNG, MACAUHOI, NOIDUNG, NGAYDANG) VALUES (?, ?, ?, NOW())");
            $stmt->execute([$currentUser['id'], $questionId, $content]);
            
            $conn->commit();
            
            $success = 'Đặt câu hỏi thành công! Đang chuyển hướng...';
            // Cộng điểm cho user
            $conn->prepare("UPDATE NGUOIDUNG SET DIEMDANHGIA = DIEMDANHGIA + 5 WHERE MANGUOIDUNG = ?")
                ->execute([$currentUser['id']]);
            
            $success = 'Đặt câu hỏi thành công! Bạn nhận được +5 điểm. Đang chuyển hướng...';
            header('refresh:2;url=question-detail.php?id=' . $questionId);
        } catch (Exception $e) {
            $conn->rollBack();
            $error = 'Có lỗi xảy ra: ' . $e->getMessage();
        }
    }
}

// Lấy danh sách tags
$tagsQuery = "SELECT MATHE, TENTHE, MOTA FROM TAG ORDER BY TENTHE";
$tags = $conn->query($tagsQuery)->fetchAll(PDO::FETCH_ASSOC);

require_once 'includes/header.php';
?>

<main class="py-4">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card shadow">
                    <div class="card-body p-4">
                        <h2 class="mb-4">
                            <i class="bi bi-plus-circle-fill text-primary me-2"></i>Đặt câu hỏi mới
                        </h2>

                        <?php if ($error): ?>
                        <div class="alert alert-danger" role="alert">
                            <i class="bi bi-exclamation-triangle me-2"></i><?php echo $error; ?>
                        </div>
                        <?php endif; ?>

                        <?php if ($success): ?>
                        <div class="alert alert-success" role="alert">
                            <i class="bi bi-check-circle me-2"></i><?php echo $success; ?>
                        </div>
                        <?php endif; ?>

                        <div class="alert alert-info mb-4">
                            <h6><i class="bi bi-lightbulb me-2"></i>Mẹo đặt câu hỏi hay:</h6>
                            <ul class="mb-0 small">
                                <li>Tiêu đề ngắn gọn, súc tích, mô tả rõ vấn đề</li>
                                <li>Nội dung chi tiết, cung cấp đầy đủ thông tin</li>
                                <li>Chọn tag phù hợp để dễ tìm kiếm</li>
                                <li>Kiểm tra lại chính tả trước khi đăng</li>
                            </ul>
                        </div>

                        <form method="POST" action="">
                            <div class="mb-4">
                                <label for="title" class="form-label fw-semibold">
                                    Tiêu đề câu hỏi <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control form-control-lg" id="title" name="title" 
                                       placeholder="Ví dụ: Làm thế nào để kết nối PHP với MySQL?" 
                                       required minlength="10"
                                       value="<?php echo htmlspecialchars($_POST['title'] ?? ''); ?>">
                                <small class="text-muted">Tối thiểu 10 ký tự</small>
                            </div>

                            <div class="mb-4">
                                <label for="content" class="form-label fw-semibold">
                                    Nội dung chi tiết <span class="text-danger">*</span>
                                </label>
                                <textarea class="form-control" id="content" name="content" rows="10" 
                                          placeholder="Mô tả chi tiết vấn đề của bạn, bao gồm những gì bạn đã thử và kết quả nhận được..." 
                                          required minlength="20"><?php echo htmlspecialchars($_POST['content'] ?? ''); ?></textarea>
                                <small class="text-muted">Tối thiểu 20 ký tự. Hãy mô tả chi tiết để nhận được câu trả lời tốt nhất.</small>
                            </div>

                            <div class="mb-4">
                                <label for="tag" class="form-label fw-semibold">
                                    Chọn Tag <span class="text-danger">*</span>
                                </label>
                                <select class="form-select" id="tag" name="tag" required>
                                    <option value="">-- Chọn tag phù hợp --</option>
                                    <?php foreach ($tags as $tag): ?>
                                    <option value="<?php echo $tag['MATHE']; ?>" <?php echo (isset($_POST['tag']) && $_POST['tag'] === $tag['MATHE']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($tag['TENTHE']); ?>
                                        <?php if ($tag['MOTA']): ?>
                                        - <?php echo htmlspecialchars($tag['MOTA']); ?>
                                        <?php endif; ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="bi bi-send me-2"></i>Đăng câu hỏi
                                </button>
                                <a href="questions.php" class="btn btn-outline-secondary btn-lg">
                                    <i class="bi bi-x-circle me-2"></i>Hủy
                                </a>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Guidelines -->
                <div class="card mt-4">
                    <div class="card-body">
                        <h5 class="mb-3"><i class="bi bi-book me-2"></i>Hướng dẫn đặt câu hỏi</h5>
                        <div class="accordion" id="guidelinesAccordion">
                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse1">
                                        Làm thế nào để viết tiêu đề tốt?
                                    </button>
                                </h2>
                                <div id="collapse1" class="accordion-collapse collapse" data-bs-parent="#guidelinesAccordion">
                                    <div class="accordion-body">
                                        <ul>
                                            <li>Tóm tắt vấn đề cụ thể trong một câu</li>
                                            <li>Bao gồm từ khóa liên quan đến công nghệ</li>
                                            <li>Tránh tiêu đề chung chung như "Cần giúp đỡ"</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse2">
                                        Nội dung nên bao gồm những gì?
                                    </button>
                                </h2>
                                <div id="collapse2" class="accordion-collapse collapse" data-bs-parent="#guidelinesAccordion">
                                    <div class="accordion-body">
                                        <ul>
                                            <li>Mô tả chi tiết vấn đề bạn gặp phải</li>
                                            <li>Những gì bạn đã thử và kết quả</li>
                                            <li>Code mẫu nếu có (định dạng rõ ràng)</li>
                                            <li>Thông tin môi trường (phiên bản, hệ điều hành...)</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<?php require_once 'includes/footer.php'; ?>
