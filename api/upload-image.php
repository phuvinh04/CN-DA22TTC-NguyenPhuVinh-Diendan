<?php
/**
 * API Upload Image
 * Cho phép upload ảnh đính kèm trong câu hỏi và câu trả lời
 */

require_once '../config/database.php';
require_once '../config/session.php';

header('Content-Type: application/json');

// Kiểm tra đăng nhập
$currentUser = getCurrentUser();
if (!$currentUser) {
    echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập']);
    exit();
}

// Kiểm tra request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method không hợp lệ']);
    exit();
}

// Kiểm tra file upload
if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
    $errorMessages = [
        UPLOAD_ERR_INI_SIZE => 'File quá lớn (vượt quá giới hạn server)',
        UPLOAD_ERR_FORM_SIZE => 'File quá lớn (vượt quá giới hạn form)',
        UPLOAD_ERR_PARTIAL => 'File chỉ được upload một phần',
        UPLOAD_ERR_NO_FILE => 'Không có file nào được upload',
        UPLOAD_ERR_NO_TMP_DIR => 'Thiếu thư mục tạm',
        UPLOAD_ERR_CANT_WRITE => 'Không thể ghi file',
        UPLOAD_ERR_EXTENSION => 'Upload bị chặn bởi extension'
    ];
    $error = $_FILES['image']['error'] ?? UPLOAD_ERR_NO_FILE;
    echo json_encode(['success' => false, 'message' => $errorMessages[$error] ?? 'Lỗi upload file']);
    exit();
}

$file = $_FILES['image'];
$allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
$maxSize = 5 * 1024 * 1024; // 5MB

// Kiểm tra loại file
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mimeType = finfo_file($finfo, $file['tmp_name']);
finfo_close($finfo);

if (!in_array($mimeType, $allowedTypes)) {
    echo json_encode(['success' => false, 'message' => 'Chỉ chấp nhận file ảnh (JPG, PNG, GIF, WebP)']);
    exit();
}

// Kiểm tra kích thước
if ($file['size'] > $maxSize) {
    echo json_encode(['success' => false, 'message' => 'File không được vượt quá 5MB']);
    exit();
}

// Tạo thư mục uploads nếu chưa có
$uploadDir = '../uploads/images/' . date('Y/m');
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

// Tạo tên file unique
$extension = pathinfo($file['name'], PATHINFO_EXTENSION);
$filename = uniqid('img_') . '_' . time() . '.' . $extension;
$filepath = $uploadDir . '/' . $filename;

// Di chuyển file
if (move_uploaded_file($file['tmp_name'], $filepath)) {
    // Nén và resize ảnh nếu quá lớn
    $optimizedPath = optimizeImage($filepath, $mimeType);

    // Trả về URL tương đối
    $relativeUrl = 'uploads/images/' . date('Y/m') . '/' . $filename;

    echo json_encode([
        'success' => true,
        'message' => 'Upload thành công',
        'url' => $relativeUrl,
        'filename' => $filename
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Không thể lưu file']);
}

/**
 * Tối ưu hóa ảnh - resize nếu quá lớn và nén
 */
function optimizeImage($filepath, $mimeType)
{
    // Kiểm tra GD library
    if (!extension_loaded('gd')) {
        return $filepath;
    }

    $maxWidth = 1920;
    $maxHeight = 1080;
    $quality = 85;

    // Lấy kích thước ảnh gốc
    $imageInfo = getimagesize($filepath);
    if (!$imageInfo) {
        return $filepath;
    }

    $width = $imageInfo[0];
    $height = $imageInfo[1];

    // Không cần resize nếu ảnh nhỏ hơn max
    if ($width <= $maxWidth && $height <= $maxHeight) {
        return $filepath;
    }

    // Tính tỷ lệ resize
    $ratio = min($maxWidth / $width, $maxHeight / $height);
    $newWidth = (int)($width * $ratio);
    $newHeight = (int)($height * $ratio);

    // Tạo ảnh từ file gốc
    switch ($mimeType) {
        case 'image/jpeg':
            $source = imagecreatefromjpeg($filepath);
            break;
        case 'image/png':
            $source = imagecreatefrompng($filepath);
            break;
        case 'image/gif':
            $source = imagecreatefromgif($filepath);
            break;
        case 'image/webp':
            $source = imagecreatefromwebp($filepath);
            break;
        default:
            return $filepath;
    }

    if (!$source) {
        return $filepath;
    }

    // Tạo ảnh mới với kích thước đã resize
    $destination = imagecreatetruecolor($newWidth, $newHeight);

    // Giữ transparency cho PNG và GIF
    if ($mimeType === 'image/png' || $mimeType === 'image/gif') {
        imagealphablending($destination, false);
        imagesavealpha($destination, true);
        $transparent = imagecolorallocatealpha($destination, 255, 255, 255, 127);
        imagefilledrectangle($destination, 0, 0, $newWidth, $newHeight, $transparent);
    }

    // Resize
    imagecopyresampled($destination, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

    // Lưu ảnh đã tối ưu
    switch ($mimeType) {
        case 'image/jpeg':
            imagejpeg($destination, $filepath, $quality);
            break;
        case 'image/png':
            imagepng($destination, $filepath, 8);
            break;
        case 'image/gif':
            imagegif($destination, $filepath);
            break;
        case 'image/webp':
            imagewebp($destination, $filepath, $quality);
            break;
    }

    // Giải phóng bộ nhớ
    imagedestroy($source);
    imagedestroy($destination);

    return $filepath;
}
