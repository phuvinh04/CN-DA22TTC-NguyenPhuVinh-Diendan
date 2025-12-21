<?php
/**
 * Cấu hình chung cho website
 */

// Tự động detect base URL
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$scriptPath = dirname($_SERVER['SCRIPT_NAME']);

// Xác định thư mục gốc của project
$projectFolder = '';
if (strpos($scriptPath, '/user') !== false || strpos($scriptPath, '/admin') !== false || strpos($scriptPath, '/api') !== false) {
    $projectFolder = dirname($scriptPath);
} else {
    $projectFolder = $scriptPath;
}

// Đảm bảo có dấu / ở cuối
$projectFolder = rtrim($projectFolder, '/') . '/';

// Base URL
define('BASE_URL', $protocol . '://' . $host . $projectFolder);
define('SITE_NAME', 'Diễn Đàn Chuyên Ngành');

// Hàm helper để tạo URL
function url($path = '') {
    return BASE_URL . ltrim($path, '/');
}

// Hàm helper để tạo đường dẫn asset
function asset($path) {
    return BASE_URL . 'assets/' . ltrim($path, '/');
}
