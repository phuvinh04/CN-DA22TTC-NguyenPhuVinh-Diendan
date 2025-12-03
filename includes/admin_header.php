<?php
require_once __DIR__ . '/../config/session.php';
$currentUser = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? 'Admin Panel'; ?></title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <!-- Admin Sidebar -->
    <div class="admin-sidebar">
        <div class="admin-sidebar-brand">
            <h4><i class="bi bi-shield-check me-2"></i>Admin Panel</h4>
            <small class="text-white-50">Quản trị hệ thống</small>
        </div>
        
        <ul class="admin-nav">
            <li class="admin-nav-item">
                <a href="dashboard.php" class="admin-nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">
                    <i class="bi bi-speedometer2"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li class="admin-nav-item">
                <a href="users.php" class="admin-nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'users.php' ? 'active' : ''; ?>">
                    <i class="bi bi-people"></i>
                    <span>Người dùng</span>
                </a>
            </li>
            <li class="admin-nav-item">
                <a href="questions.php" class="admin-nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'questions.php' ? 'active' : ''; ?>">
                    <i class="bi bi-question-circle"></i>
                    <span>Câu hỏi</span>
                </a>
            </li>
            <li class="admin-nav-item">
                <a href="tags.php" class="admin-nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'tags.php' ? 'active' : ''; ?>">
                    <i class="bi bi-tags"></i>
                    <span>Tags</span>
                </a>
            </li>
            <li class="admin-nav-item">
                <a href="reports.php" class="admin-nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'reports.php' ? 'active' : ''; ?>">
                    <i class="bi bi-flag"></i>
                    <span>Báo cáo</span>
                </a>
            </li>
            <li class="admin-nav-item">
                <a href="settings.php" class="admin-nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'settings.php' ? 'active' : ''; ?>">
                    <i class="bi bi-gear"></i>
                    <span>Cài đặt</span>
                </a>
            </li>
            <li class="admin-nav-item mt-4">
                <a href="../index.php" class="admin-nav-link">
                    <i class="bi bi-house"></i>
                    <span>Về trang chủ</span>
                </a>
            </li>
            <li class="admin-nav-item">
                <a href="../logout.php" class="admin-nav-link">
                    <i class="bi bi-box-arrow-right"></i>
                    <span>Đăng xuất</span>
                </a>
            </li>
        </ul>
    </div>
