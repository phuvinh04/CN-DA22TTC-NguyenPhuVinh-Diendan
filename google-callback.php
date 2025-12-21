<?php
/**
 * Google OAuth Callback
 * Xử lý sau khi user đăng nhập Google
 */
require_once 'config/database.php';
require_once 'config/session.php';
require_once 'config/google_config.php';

// Kiểm tra có code từ Google không
if (!isset($_GET['code'])) {
    header('Location: login.php?error=google_failed');
    exit();
}

$code = $_GET['code'];

// Lấy access token
$tokenData = getGoogleAccessToken($code);

if (!isset($tokenData['access_token'])) {
    header('Location: login.php?error=google_token_failed');
    exit();
}

// Lấy thông tin user từ Google
$googleUser = getGoogleUserInfo($tokenData['access_token']);

if (!isset($googleUser['email'])) {
    header('Location: login.php?error=google_user_failed');
    exit();
}

$email = $googleUser['email'];
$fullname = $googleUser['name'] ?? 'Google User';
$avatar = $googleUser['picture'] ?? 'https://ui-avatars.com/api/?name=' . urlencode($fullname) . '&background=4f46e5&color=fff';
$googleId = $googleUser['id'];

// Kiểm tra user đã tồn tại chưa (theo email)
$stmt = $conn->prepare("SELECT * FROM NGUOIDUNG WHERE EMAIL = ?");
$stmt->execute([$email]);
$existingUser = $stmt->fetch(PDO::FETCH_ASSOC);

if ($existingUser) {
    // User đã tồn tại - đăng nhập
    $userId = $existingUser['MANGUOIDUNG'];
    $username = $existingUser['TENDANGNHAP'];
    $fullname = $existingUser['HOTEN'];
    $avatar = $existingUser['ANHDAIDIEN'];
    
    // Cập nhật lần hoạt động cuối
    $conn->prepare("UPDATE NGUOIDUNG SET LANHOATDONGCUOI = NOW() WHERE MANGUOIDUNG = ?")->execute([$userId]);
} else {
    // User mới - tạo tài khoản
    $userId = 'ND' . time();
    $username = 'gg_' . substr($googleId, 0, 10);
    
    // Kiểm tra username đã tồn tại chưa
    $checkUsername = $conn->prepare("SELECT COUNT(*) FROM NGUOIDUNG WHERE TENDANGNHAP = ?");
    $checkUsername->execute([$username]);
    if ($checkUsername->fetchColumn() > 0) {
        $username = 'gg_' . time();
    }
    
    // Tạo user mới
    $stmt = $conn->prepare("INSERT INTO NGUOIDUNG (MANGUOIDUNG, TENDANGNHAP, EMAIL, MATKHAU, HOTEN, ANHDAIDIEN, TIEUSU, DIEMDANHGIA, TRANGTHAI, NGAYTAO) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, 0, 'active', NOW())");
    $stmt->execute([$userId, $username, $email, md5(uniqid()), $fullname, $avatar, 'Đăng ký qua Google']);
    
    // Gán vai trò user
    $conn->prepare("INSERT INTO COVT (MAVAITRO, MANGUOIDUNG) VALUES ('user', ?)")->execute([$userId]);
    
    // Gán huy hiệu người mới
    try {
        $conn->prepare("INSERT INTO NHAN (MANGUOIDUNG, MAHUYHIEU) VALUES (?, 'HH001')")->execute([$userId]);
    } catch (Exception $e) {}
}

// Lấy vai trò
$role = getUserRole($conn, $userId);

// Lưu session
$_SESSION['user_id'] = $userId;
$_SESSION['username'] = $username;
$_SESSION['fullname'] = $fullname;
$_SESSION['avatar'] = $avatar;
$_SESSION['role'] = $role;

// Chuyển hướng
if ($role === 'admin') {
    header('Location: admin/dashboard.php');
} else {
    header('Location: index.php');
}
exit();
