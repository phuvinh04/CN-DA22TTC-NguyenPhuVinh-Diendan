<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cài đặt Database - Diễn Đàn Chuyên Ngành</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h3 class="mb-0">Cài đặt Database</h3>
                    </div>
                    <div class="card-body">
                        <?php
                        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                            $host = $_POST['host'] ?? 'localhost';
                            $port = $_POST['port'] ?? '3306';
                            $username = $_POST['username'] ?? 'root';
                            $password = $_POST['password'] ?? '';
                            
                            try {
                                // Kết nối không cần database
                                $conn = new PDO("mysql:host=$host;port=$port;charset=utf8mb4", $username, $password);
                                $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                                
                                echo '<div class="alert alert-success">Kết nối MySQL thành công!</div>';
                                
                                // Đọc file SQL
                                $sqlFile = file_get_contents('database_mysql.sql');
                                
                                // Tách các câu lệnh SQL
                                $statements = explode(';', $sqlFile);
                                
                                $successCount = 0;
                                $errors = [];
                                
                                foreach ($statements as $statement) {
                                    $statement = trim($statement);
                                    if (!empty($statement) && $statement !== '--') {
                                        try {
                                            $conn->exec($statement);
                                            $successCount++;
                                        } catch (PDOException $e) {
                                            // Bỏ qua lỗi database đã tồn tại
                                            if (strpos($e->getMessage(), 'database exists') === false) {
                                                $errors[] = $e->getMessage();
                                            }
                                        }
                                    }
                                }
                                
                                echo '<div class="alert alert-success">';
                                echo "Đã thực thi $successCount câu lệnh SQL thành công!<br>";
                                echo 'Database <strong>diendan_hoidap</strong> đã được tạo!<br>';
                                echo 'Dữ liệu mẫu đã được import!';
                                echo '</div>';
                                
                                if (!empty($errors)) {
                                    echo '<div class="alert alert-warning">';
                                    echo '<strong>Một số cảnh báo:</strong><br>';
                                    foreach (array_slice($errors, 0, 5) as $error) {
                                        echo '- ' . htmlspecialchars($error) . '<br>';
                                    }
                                    echo '</div>';
                                }
                                
                                // Cập nhật file config
                                $configContent = "<?php
// Cấu hình kết nối database MySQL
define('DB_SERVER', '$host');
define('DB_PORT', '$port');
define('DB_USERNAME', '$username');
define('DB_PASSWORD', '$password');
define('DB_NAME', 'diendan_hoidap');

try {
    \$dsn = \"mysql:host=\" . DB_SERVER . \";port=\" . DB_PORT . \";dbname=\" . DB_NAME . \";charset=utf8mb4\";
    \$conn = new PDO(\$dsn, DB_USERNAME, DB_PASSWORD);
    \$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    \$conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException \$e) {
    die(\"Lỗi kết nối MySQL: \" . \$e->getMessage());
}
?>";
                                
                                file_put_contents('config/database.php', $configContent);
                                
                                echo '<div class="alert alert-info">';
                                echo 'File <code>config/database.php</code> đã được cập nhật!';
                                echo '</div>';
                                
                                echo '<div class="d-grid gap-2 mt-4">';
                                echo '<a href="index.php" class="btn btn-success btn-lg">Hoàn tất - Vào trang chủ</a>';
                                echo '<a href="login.php" class="btn btn-primary">Đăng nhập (admin / 123456)</a>';
                                echo '</div>';
                                
                            } catch (PDOException $e) {
                                echo '<div class="alert alert-danger">';
                                echo '<strong>Lỗi kết nối:</strong><br>';
                                echo htmlspecialchars($e->getMessage());
                                echo '<br><br><strong>Hướng dẫn khắc phục:</strong><br>';
                                echo '1. Kiểm tra MySQL đã start trong XAMPP Control Panel chưa<br>';
                                echo '2. Kiểm tra thông tin kết nối (host, port, username, password)<br>';
                                echo '3. Thử đổi port thành 3307 nếu 3306 bị chiếm<br>';
                                echo '</div>';
                            }
                        } else {
                        ?>
                        
                        <div class="alert alert-info">
                            <strong>📋 Hướng dẫn:</strong><br>
                            1. Đảm bảo MySQL đã start trong XAMPP Control Panel<br>
                            2. Nhập thông tin kết nối MySQL (mặc định XAMPP)<br>
                            3. Nhấn "Cài đặt" để tạo database tự động
                        </div>

                        <form method="POST" action="">
                            <div class="mb-3">
                                <label class="form-label">Host</label>
                                <input type="text" class="form-control" name="host" value="localhost" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Port</label>
                                <input type="text" class="form-control" name="port" value="3306" required>
                                <small class="text-muted">Thử 3307 nếu 3306 không được</small>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Username</label>
                                <input type="text" class="form-control" name="username" value="root" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Password</label>
                                <input type="password" class="form-control" name="password" placeholder="Để trống nếu dùng XAMPP mặc định">
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    Cài đặt Database
                                </button>
                            </div>
                        </form>

                        <?php } ?>
                    </div>
                </div>

                <div class="card mt-3">
                    <div class="card-body">
                        <h5>Khắc phục lỗi MySQL không start:</h5>
                        <ol>
                            <li>Mở XAMPP Control Panel</li>
                            <li>Nhấn "Stop" MySQL (nếu đang chạy)</li>
                            <li>Nhấn "Config" → "my.ini"</li>
                            <li>Tìm dòng <code>port=3306</code> và đổi thành <code>port=3307</code></li>
                            <li>Lưu file và nhấn "Start" MySQL</li>
                            <li>Quay lại form trên và đổi Port thành 3307</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
