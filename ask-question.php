<?php
$pageTitle = 'Đặt câu hỏi';
require_once 'config/database.php';
require_once 'config/session.php';
require_once 'includes/badge_helper.php';

requireLogin();
$currentUser = getCurrentUser();

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $tagId = $_POST['tag'] ?? '';
    $images = $_POST['images'] ?? ''; // JSON array of image URLs
    
    if (empty($title) || empty($content) || empty($tagId)) {
        $error = 'Vui lòng điền đầy đủ thông tin';
    } elseif (strlen($title) < 10) {
        $error = 'Tiêu đề phải có ít nhất 10 ký tự';
    } elseif (strlen($content) < 20) {
        $error = 'Nội dung phải có ít nhất 20 ký tự';
    } else {
        try {
            $conn->beginTransaction();
            
            $questionId = 'CH' . time();
            
            $stmt = $conn->prepare("INSERT INTO CAUHOI (MACAUHOI, MATHE, TIEUDE, TRANGTHAI, LUOTXEM) VALUES (?, ?, ?, 'pending', 0)");
            $stmt->execute([$questionId, $tagId, $title]);
            
            // Kiểm tra xem cột HINHANH có tồn tại không
            $hasImageColumn = false;
            try {
                $checkColumn = $conn->query("SHOW COLUMNS FROM DAT LIKE 'HINHANH'");
                $hasImageColumn = $checkColumn->rowCount() > 0;
            } catch (Exception $e) {}
            
            if ($hasImageColumn && !empty($images)) {
                $stmt = $conn->prepare("INSERT INTO DAT (MANGUOIDUNG, MACAUHOI, NOIDUNG, NGAYDANG, HINHANH) VALUES (?, ?, ?, NOW(), ?)");
                $stmt->execute([$currentUser['id'], $questionId, $content, $images]);
            } else {
                $stmt = $conn->prepare("INSERT INTO DAT (MANGUOIDUNG, MACAUHOI, NOIDUNG, NGAYDANG) VALUES (?, ?, ?, NOW())");
                $stmt->execute([$currentUser['id'], $questionId, $content]);
            }
            
            $conn->prepare("UPDATE NGUOIDUNG SET DIEMDANHGIA = DIEMDANHGIA + 5 WHERE MANGUOIDUNG = ?")
                ->execute([$currentUser['id']]);
            
            checkAndAwardBadges($currentUser['id']);
            
            $conn->commit();
            $success = 'Đặt câu hỏi thành công! Câu hỏi đang chờ duyệt. Bạn nhận được +5 điểm.';
            $title = $content = $tagId = $images = '';
        } catch (Exception $e) {
            $conn->rollBack();
            $error = 'Có lỗi xảy ra, vui lòng thử lại';
        }
    }
}

$tags = $conn->query("SELECT * FROM TAG ORDER BY TENTHE")->fetchAll(PDO::FETCH_ASSOC);

require_once 'includes/header.php';
?>

<main class="py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card" style="box-shadow: var(--shadow-lg);">
                    <div class="card-header">
                        <h4 class="mb-0"><i class="bi bi-plus-circle me-2" style="color: var(--primary-500);"></i>Đặt câu hỏi mới</h4>
                    </div>
                    
                    <div class="card-body p-4">
                        <?php if ($error): ?>
                        <div class="alert alert-danger">
                            <i class="bi bi-exclamation-triangle"></i>
                            <?php echo $error; ?>
                        </div>
                        <?php endif; ?>

                        <?php if ($success): ?>
                        <div class="alert alert-success">
                            <i class="bi bi-check-circle"></i>
                            <?php echo $success; ?>
                        </div>
                        <?php endif; ?>

                        <form method="POST">
                            <div class="mb-4">
                                <label for="tag" class="form-label">Chủ đề</label>
                                <select class="form-select" id="tag" name="tag" required>
                                    <option value="">Chọn chủ đề phù hợp</option>
                                    <?php foreach ($tags as $tag): ?>
                                    <option value="<?php echo $tag['MATHE']; ?>" <?php echo ($tagId ?? '') === $tag['MATHE'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($tag['TENTHE']); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="mb-4">
                                <label for="title" class="form-label">Tiêu đề câu hỏi</label>
                                <input type="text" class="form-control" id="title" name="title" 
                                       placeholder="Mô tả ngắn gọn vấn đề của bạn..." 
                                       value="<?php echo htmlspecialchars($title ?? ''); ?>" required>
                                <div class="form-text">Tối thiểu 10 ký tự. Hãy viết tiêu đề rõ ràng, cụ thể.</div>
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
                                <textarea class="form-control" id="content" name="content" rows="10" 
                                          placeholder="Mô tả chi tiết vấn đề, những gì bạn đã thử, kết quả mong muốn...

Để chèn code, sử dụng:
```ngôn_ngữ
code của bạn
```

Ví dụ:
```php
echo 'Hello World';
```" 
                                          required><?php echo htmlspecialchars($content ?? ''); ?></textarea>
                                <div class="form-text">Tối thiểu 20 ký tự. Sử dụng ``` để bao code. Có thể đính kèm ảnh minh họa.</div>
                            </div>
                            
                            <!-- Upload ảnh -->
                            <div class="mb-4">
                                <input type="file" id="imageUpload" accept="image/*" multiple style="display: none;" onchange="uploadImages(this.files)">
                                <input type="hidden" name="images" id="imagesInput" value="">
                                <div id="imagePreview" class="image-preview-container"></div>
                            </div>
                            
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-send me-2"></i>Đăng câu hỏi
                                </button>
                                <a href="questions.php" class="btn btn-secondary">
                                    <i class="bi bi-arrow-left me-2"></i>Quay lại
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Tips -->
                <div class="sidebar-widget mt-4">
                    <h5><i class="bi bi-lightbulb"></i>Mẹo đặt câu hỏi hay</h5>
                    <ul class="list-unstyled mb-0" style="font-size: var(--font-sm); color: var(--gray-600);">
                        <li class="mb-2"><i class="bi bi-check2 me-2" style="color: var(--success);"></i>Tiêu đề rõ ràng, cụ thể</li>
                        <li class="mb-2"><i class="bi bi-check2 me-2" style="color: var(--success);"></i>Mô tả chi tiết vấn đề</li>
                        <li class="mb-2"><i class="bi bi-check2 me-2" style="color: var(--success);"></i>Chia sẻ những gì đã thử</li>
                        <li class="mb-2"><i class="bi bi-check2 me-2" style="color: var(--success);"></i>Chọn đúng chủ đề</li>
                        <li class="mb-2"><i class="bi bi-check2 me-2" style="color: var(--success);"></i>Sử dụng ngôn ngữ lịch sự</li>
                        <li class="mb-2"><i class="bi bi-code-slash me-2" style="color: var(--primary-500);"></i>Đính kèm code với ```</li>
                        <li><i class="bi bi-image me-2" style="color: var(--primary-500);"></i>Thêm ảnh minh họa nếu cần</li>
                    </ul>
                </div>
                
                <!-- Hướng dẫn code -->
                <div class="sidebar-widget mt-4">
                    <h5><i class="bi bi-code-square"></i>Cách chèn code</h5>
                    <div style="font-size: var(--font-sm); color: var(--gray-600);">
                        <p class="mb-2">Sử dụng 3 dấu ` để bao code:</p>
                        <pre class="code-example mb-0" style="background: var(--gray-100); padding: 10px; border-radius: 6px; font-size: 12px;">```php
echo "Hello World";
```</pre>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<script>
// Mảng lưu URLs ảnh đã upload
let uploadedImages = [];

// Chèn code block vào textarea
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
    
    // Đặt con trỏ vào giữa code block
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
            const response = await fetch('api/upload-image.php', {
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
            console.error(error);
        }
    }
}

// Cập nhật preview ảnh
function updateImagePreview() {
    const container = document.getElementById('imagePreview');
    container.innerHTML = uploadedImages.map((url, index) => `
        <div class="image-preview-item">
            <img src="${url}" alt="Preview">
            <button type="button" class="remove-image" onclick="removeImage(${index})" title="Xóa ảnh">
                <i class="bi bi-x"></i>
            </button>
        </div>
    `).join('');
}

// Xóa ảnh
function removeImage(index) {
    uploadedImages.splice(index, 1);
    updateImagePreview();
    document.getElementById('imagesInput').value = JSON.stringify(uploadedImages);
}
</script>

<?php require_once 'includes/footer.php'; ?>
