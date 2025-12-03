<?php
// Cấu hình kết nối database MySQL
define('DB_SERVER', 'localhost');
define('DB_PORT', '3306'); // Đổi thành 3307 nếu đã thay đổi port
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'diendan_hoidap');

try {
    $dsn = "mysql:host=" . DB_SERVER . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4";
    $conn = new PDO($dsn, DB_USERNAME, DB_PASSWORD);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Lỗi kết nối MySQL: " . $e->getMessage() . "<br>Vui lòng kiểm tra:<br>1. MySQL đã start trong XAMPP chưa?<br>2. Thông tin kết nối trong config/database.php có đúng không?");
}
?>
