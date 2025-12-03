<?php
// Khởi tạo session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Kiểm tra đăng nhập
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Lấy thông tin user hiện tại
function getCurrentUser() {
    if (isLoggedIn()) {
        return [
            'id' => $_SESSION['user_id'],
            'username' => $_SESSION['username'],
            'fullname' => $_SESSION['fullname'],
            'avatar' => $_SESSION['avatar'] ?? 'https://ui-avatars.com/api/?name=User&background=4f46e5&color=fff',
            'role' => $_SESSION['role'] ?? 'user'
        ];
    }
    return null;
}

// Lấy vai trò của user
function getUserRole($conn, $userId) {
    $stmt = $conn->prepare("SELECT MAVAITRO FROM COVT WHERE MANGUOIDUNG = ?");
    $stmt->execute([$userId]);
    $roles = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (in_array('admin', $roles)) return 'admin';
    if (in_array('moderator', $roles)) return 'moderator';
    return 'user';
}

// Xác định base path dựa trên vị trí file hiện tại
function getBasePath() {
    $currentPath = $_SERVER['PHP_SELF'];
    if (strpos($currentPath, '/user/') !== false || strpos($currentPath, '/admin/') !== false || strpos($currentPath, '/api/') !== false) {
        return '../';
    }
    return '';
}

// Yêu cầu đăng nhập
function requireLogin() {
    if (!isLoggedIn()) {
        $basePath = getBasePath();
        header('Location: ' . $basePath . 'login.php');
        exit();
    }
}

// Yêu cầu quyền admin
function requireAdmin() {
    requireLogin();
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
        $basePath = getBasePath();
        header('Location: ' . $basePath . 'index.php');
        exit();
    }
}
?>
