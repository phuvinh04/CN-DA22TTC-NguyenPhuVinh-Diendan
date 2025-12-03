<?php
// Cấu hình kết nối SQLite (không cần MySQL)
$dbFile = __DIR__ . '/../database/diendan_hoidap.db';

// Tạo thư mục database nếu chưa có
$dbDir = dirname($dbFile);
if (!is_dir($dbDir)) {
    mkdir($dbDir, 0777, true);
}

try {
    $conn = new PDO("sqlite:$dbFile");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
    // Enable foreign keys
    $conn->exec('PRAGMA foreign_keys = ON');
} catch(PDOException $e) {
    die("Lỗi kết nối SQLite: " . $e->getMessage());
}
?>
