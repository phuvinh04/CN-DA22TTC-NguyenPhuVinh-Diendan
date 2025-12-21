<?php
$pageTitle = 'Sửa câu trả lời';
require_once '../config/database.php';
require_once '../config/session.php';
requireLogin();

$currentUser = getCurrentUser();
$answerId = $_GET['id'] ?? '';

if (empty($answerId)) {
    header('Location: my-answers.php');
    exit();
}

// Kiểm tra cột HINHANH
$hasImageColumn = false;
try {
    $checkColumn = $conn->query("SHOW COLUMNS FROM TRALOI LIKE 'HINHANH'");
    $hasImageColumn = $checkColumn->rowCount() > 0;
} catch (Exception $e) {}

// Lấy thông tin câu trả lời
$imageSelect = $hasImageColumn ? ", tl.HINHANH" : ", NULL as HINHANH";
$stmt = $conn->prepare("
    SELECT tl.*, ch.TIEUDE, ch.MACAUHOI $imageSelect
    FROM TRALOI tl 
    JOIN CAUHOI ch ON tl.MACAUHOI = ch.MACAUHOI
    WHERE tl.MACAUTRALOI = ?
");
$stmt->execute([$answerId]);
$answer = $stmt->fetch(PDO::FETCH_ASSOC);

// Kiểm tra quyền sửa
if (!$answer || $answer['MANGUOIDUNG'] !== $currentUser['id']) {
    header('Location: my-answers.php?error=permission');
    exit();
}

$error = '';
$success = '';

// Xử lý cập nhật
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $content = trim($_POST['content'] ?? '');
    $images = $_POST['images'] ?? '';
    
    if (empty($content)) {
        $error = 'Nội dung không được để trống';
    } elseif (strlen($content) < 10) {
        $error = 'Nội dung phải có ít nhất 10 ký tự';
    } else {
        if ($hasImageColumn) {
            $stmt = $conn->prepare("UPDATE TRALOI SET NOIDUNGTL = ?, HINHANH = ? WHERE MACAUTRALOI = ?");
            $executed = $stmt->execute([$content, $images, $answerId]);
        } else {
            $stmt = $conn->prepare("UPDATE TRALOI SET NOIDUNGTL = ? WHERE MACAUTRALOI = ?");
            $executed = $stmt->execute([$content, $answerId]);
        }
        
        if ($executed) {
            $success = 'Cập nhật thành công!';
            $answer['NOIDUNGTL'] = $content;
            $answer['HINHANH'] = $images;
        } else {
            $error = 'Có lỗi xảy ra, vui lòng thử lại';
        }
    }
}

require_once '../includes/header.php';
?>

<main class="py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <nav aria-label="breadcrumb" class="mb-4">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="../index.php">Trang chủ</a></li>
                        <li class="breadcrumb-item"><a href="my-answers.php">Câu trả lời của tôi</a></li>
                        <li class="breadcrumb-item active">Sửa câu trả lời</li>
                    </ol>
                </nav>

                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-pencil-square me-2"></i>Sửa câu trả lời</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-4 p-3 rounded" style="background: var(--gray-50);">
                            <small class="text-muted d-block mb-1">Câu hỏi:</small>
                            <a href="../question-detail.php?id=<?php echo $answer['MACAUHOI']; ?>" class="fw-semibold">
                                <?php echo htmlspecialchars($answer['TIEUDE']); ?>
                            </a>
                        </div>

                        <?php if ($error): ?>
                        <div class="alert alert-danger">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i><?php echo $error; ?>
                        </div>
                        <?php endif; ?>

                        <?php if ($success): ?>
                        <div class="alert alert-success">
                            <i class="bi bi-check-circle-fill me-2"></i><?php echo $success; ?>
                            <a href="../question-detail.php?id=<?php echo $answer['MACAUHOI']; ?>" class="alert-link ms-2">Xem câu hỏi</a>
                        </div>
                        <?php endif; ?>

                        <form method="POST" data-validate>
                            <div class="mb-4">
                                <label for="content" class="form-label">Nội dung câu trả lời</label>
                                <div class="editor-toolbar mb-2">
                                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="insertCodeBlock()" title="Chèn code">
                                        <i class="bi bi-code-slash"></i> Code
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="document.getElementById('imageUpload').click()" title="Đính kèm ảnh">
                                        <i class="bi bi-image"></i> Ảnh
                                    </button>
                                </div>
                                <textarea class="form-control" id="content" name="content" rows="10" 
                                          required minlength="10" data-validate-live
                                          placeholder="Nhập nội dung câu trả lời...

Sử dụng ``` để chèn code:
```php
// code của bạn
```"><?php echo htmlspecialchars($answer['NOIDUNGTL']); ?></textarea>
                                <div class="invalid-feedback"></div>
                                <div class="form-text">Tối thiểu 10 ký tự. Sử dụng ``` để bao code.</div>
                            </div>
                            
                            <!-- Upload ảnh -->
                            <div class="mb-4">
                                <input type="file" id="imageUpload" accept="image/*" multiple style="display: none;" onchange="uploadImages(this.files)">
                                <input type="hidden" name="images" id="imagesInput" value="<?php echo htmlspecialchars($answer['HINHANH'] ?? ''); ?>">
                                <div id="imagePreview" class="image-preview-container"></div>
                            </div>
                            
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check-lg me-2"></i>Lưu thay đổi
                                </button>
                                <a href="my-answers.php" class="btn btn-secondary">
                                    <i class="bi bi-x-lg me-2"></i>Hủy
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
let uploadedImages = <?php echo $answer['HINHANH'] ? $answer['HINHANH'] : '[]'; ?>;
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
