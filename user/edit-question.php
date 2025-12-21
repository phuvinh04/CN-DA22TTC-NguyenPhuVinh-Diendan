<?php
$pageTitle = 'Sửa câu hỏi';
require_once '../config/database.php';
require_once '../config/session.php';
requireLogin();

$currentUser = getCurrentUser();
$questionId = $_GET['id'] ?? '';

if (empty($questionId)) {
    header('Location: my-questions.php');
    exit();
}

// Kiểm tra cột HINHANH
$hasImageColumn = false;
try {
    $checkColumn = $conn->query("SHOW COLUMNS FROM DAT LIKE 'HINHANH'");
    $hasImageColumn = $checkColumn->rowCount() > 0;
} catch (Exception $e) {}

// Lấy thông tin câu hỏi
$imageSelect = $hasImageColumn ? ", d.HINHANH" : ", NULL as HINHANH";
$stmt = $conn->prepare("
    SELECT ch.*, d.NOIDUNG, d.MANGUOIDUNG $imageSelect
    FROM CAUHOI ch 
    JOIN DAT d ON ch.MACAUHOI = d.MACAUHOI 
    WHERE ch.MACAUHOI = ?
");
$stmt->execute([$questionId]);
$question = $stmt->fetch(PDO::FETCH_ASSOC);

// Kiểm tra quyền sửa
if (!$question || $question['MANGUOIDUNG'] !== $currentUser['id']) {
    header('Location: my-questions.php?error=permission');
    exit();
}

// Chỉ cho sửa câu hỏi pending hoặc rejected
if (!in_array($question['TRANGTHAI'], ['pending', 'rejected'])) {
    header('Location: my-questions.php?error=cannot_edit');
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $tagId = $_POST['tag'] ?? '';
    $images = $_POST['images'] ?? '';
    
    if (empty($title) || empty($content) || empty($tagId)) {
        $error = 'Vui lòng điền đầy đủ thông tin';
    } elseif (strlen($title) < 10) {
        $error = 'Tiêu đề phải có ít nhất 10 ký tự';
    } elseif (strlen($content) < 20) {
        $error = 'Nội dung phải có ít nhất 20 ký tự';
    } else {
        try {
            $conn->beginTransaction();
            
            // Cập nhật câu hỏi
            $stmt = $conn->prepare("UPDATE CAUHOI SET MATHE = ?, TIEUDE = ?, TRANGTHAI = 'pending' WHERE MACAUHOI = ?");
            $stmt->execute([$tagId, $title, $questionId]);
            
            // Cập nhật nội dung và ảnh
            if ($hasImageColumn) {
                $stmt = $conn->prepare("UPDATE DAT SET NOIDUNG = ?, HINHANH = ? WHERE MACAUHOI = ?");
                $stmt->execute([$content, $images, $questionId]);
            } else {
                $stmt = $conn->prepare("UPDATE DAT SET NOIDUNG = ? WHERE MACAUHOI = ?");
                $stmt->execute([$content, $questionId]);
            }
            
            $conn->commit();
            $success = 'Cập nhật câu hỏi thành công! Câu hỏi đang chờ duyệt lại.';
            
            // Refresh data
            $stmt = $conn->prepare("SELECT ch.*, d.NOIDUNG $imageSelect FROM CAUHOI ch JOIN DAT d ON ch.MACAUHOI = d.MACAUHOI WHERE ch.MACAUHOI = ?");
            $stmt->execute([$questionId]);
            $question = $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            $conn->rollBack();
            $error = 'Có lỗi xảy ra, vui lòng thử lại';
        }
    }
}

// Lấy danh sách tags
$tags = $conn->query("SELECT * FROM TAG ORDER BY TENTHE")->fetchAll(PDO::FETCH_ASSOC);

require_once '../includes/header.php';
?>

<main class="py-4">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <nav aria-label="breadcrumb" class="mb-4">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="../index.php">Trang chủ</a></li>
                        <li class="breadcrumb-item"><a href="my-questions.php">Câu hỏi của tôi</a></li>
                        <li class="breadcrumb-item active">Sửa câu hỏi</li>
                    </ol>
                </nav>

                <div class="card">
                    <div class="card-header">
                        <h4 class="mb-0"><i class="bi bi-pencil-square me-2 text-primary"></i>Sửa câu hỏi</h4>
                    </div>
                    <div class="card-body">
                        <?php if ($error): ?>
                        <div class="alert alert-danger"><i class="bi bi-exclamation-triangle me-2"></i><?php echo $error; ?></div>
                        <?php endif; ?>

                        <?php if ($success): ?>
                        <div class="alert alert-success"><i class="bi bi-check-circle me-2"></i><?php echo $success; ?></div>
                        <?php endif; ?>

                        <form method="POST">
                            <div class="mb-3">
                                <label for="title" class="form-label">Tiêu đề câu hỏi</label>
                                <input type="text" class="form-control" id="title" name="title" 
                                       value="<?php echo htmlspecialchars($question['TIEUDE']); ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="tag" class="form-label">Chủ đề</label>
                                <select class="form-select" id="tag" name="tag" required>
                                    <?php foreach ($tags as $tag): ?>
                                    <option value="<?php echo $tag['MATHE']; ?>" <?php echo $question['MATHE'] === $tag['MATHE'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($tag['TENTHE']); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="mb-4">
                                <label for="content" class="form-label">Nội dung chi tiết</label>
                                <div class="editor-toolbar mb-2">
                                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="insertCodeBlock()" title="Chèn code">
                                        <i class="bi bi-code-slash"></i> Code
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="document.getElementById('imageUpload').click()" title="Đính kèm ảnh">
                                        <i class="bi bi-image"></i> Ảnh
                                    </button>
                                </div>
                                <textarea class="form-control" id="content" name="content" rows="10" required><?php echo htmlspecialchars($question['NOIDUNG']); ?></textarea>
                                <div class="form-text">Sử dụng ``` để bao code. Có thể đính kèm ảnh minh họa.</div>
                            </div>
                            
                            <!-- Upload ảnh -->
                            <div class="mb-4">
                                <input type="file" id="imageUpload" accept="image/*" multiple style="display: none;" onchange="uploadImages(this.files)">
                                <input type="hidden" name="images" id="imagesInput" value="<?php echo htmlspecialchars($question['HINHANH'] ?? ''); ?>">
                                <div id="imagePreview" class="image-preview-container"></div>
                            </div>
                            
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-save me-2"></i>Lưu thay đổi
                                </button>
                                <a href="my-questions.php" class="btn btn-secondary">
                                    <i class="bi bi-arrow-left me-2"></i>Quay lại
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<script>
// Khởi tạo ảnh đã có
let uploadedImages = <?php echo $question['HINHANH'] ? $question['HINHANH'] : '[]'; ?>;
updateImagePreview();

// Chèn code block
function insertCodeBlock() {
    const textarea = document.getElementById('content');
    const start = textarea.selectionStart;
    const end = textarea.selectionEnd;
    const selectedText = textarea.value.substring(start, end);
    
    const codeBlock = selectedText 
        ? "```\n" + selectedText + "\n```"
        : "```php\n// Code của bạn ở đây\n```";
    
    textarea.value = textarea.value.substring(0, start) + codeBlock + textarea.value.substring(end);
    textarea.focus();
    const newPos = start + (selectedText ? codeBlock.length : 7);
    textarea.setSelectionRange(newPos, newPos);
}

// Upload ảnh
async function uploadImages(files) {
    for (let file of files) {
        if (!file.type.startsWith('image/')) {
            alert('Chỉ chấp nhận file ảnh!');
            continue;
        }
        if (file.size > 5 * 1024 * 1024) {
            alert('File ' + file.name + ' quá lớn (tối đa 5MB)');
            continue;
        }
        
        const formData = new FormData();
        formData.append('image', file);
        
        try {
            const response = await fetch('../api/upload-image.php', {
                method: 'POST',
                body: formData
            });
            const result = await response.json();
            
            if (result.success) {
                uploadedImages.push(result.url);
                updateImagePreview();
                document.getElementById('imagesInput').value = JSON.stringify(uploadedImages);
            } else {
                alert('Lỗi upload: ' + result.message);
            }
        } catch (error) {
            alert('Lỗi kết nối server');
        }
    }
}

function updateImagePreview() {
    const container = document.getElementById('imagePreview');
    container.innerHTML = uploadedImages.map((url, index) => `
        <div class="image-preview-item">
            <img src="../${url}" alt="Preview">
            <button type="button" class="remove-image" onclick="removeImage(${index})" title="Xóa ảnh">
                <i class="bi bi-x"></i>
            </button>
        </div>
    `).join('');
}

function removeImage(index) {
    uploadedImages.splice(index, 1);
    updateImagePreview();
    document.getElementById('imagesInput').value = JSON.stringify(uploadedImages);
}
</script>

<?php require_once '../includes/footer.php'; ?>
