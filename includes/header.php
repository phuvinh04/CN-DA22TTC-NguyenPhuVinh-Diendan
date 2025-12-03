<?php
require_once __DIR__ . '/../config/session.php';
$currentUser = getCurrentUser();

// Xác định base path dựa trên vị trí file hiện tại
$basePath = '';
$currentPath = $_SERVER['PHP_SELF'];
if (strpos($currentPath, '/user/') !== false || strpos($currentPath, '/admin/') !== false) {
    $basePath = '../';
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? 'Diễn đàn Hỏi Đáp'; ?></title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?php echo $basePath; ?>assets/css/style.css">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary sticky-top shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold" href="<?php echo $basePath; ?>index.php">
                <i class="bi bi-chat-dots-fill me-2"></i>Diễn Đàn Chuyên Ngành
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo $basePath; ?>index.php"><i class="bi bi-house-door me-1"></i>Trang chủ</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo $basePath; ?>questions.php"><i class="bi bi-question-circle me-1"></i>Câu hỏi</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo $basePath; ?>tags.php"><i class="bi bi-tags me-1"></i>Tags</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo $basePath; ?>leaderboard.php"><i class="bi bi-trophy me-1"></i>Xếp hạng</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo $basePath; ?>users.php"><i class="bi bi-people me-1"></i>Thành viên</a>
                    </li>
                </ul>
                
                <form class="d-flex me-3" action="<?php echo $basePath; ?>search.php" method="GET">
                    <input class="form-control form-control-sm" type="search" name="q" placeholder="Tìm kiếm..." style="width: 250px;">
                </form>
                
                <?php if ($currentUser): ?>
                    <div class="dropdown">
                        <a class="btn btn-light btn-sm dropdown-toggle d-flex align-items-center" href="#" role="button" data-bs-toggle="dropdown">
                            <img src="<?php echo htmlspecialchars($currentUser['avatar']); ?>" alt="Avatar" class="rounded-circle me-2" width="30" height="30">
                            <span><?php echo htmlspecialchars($currentUser['fullname']); ?></span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="<?php echo $basePath; ?>profile.php"><i class="bi bi-person me-2"></i>Hồ sơ</a></li>
                            <li><a class="dropdown-item" href="<?php echo $basePath; ?>user/dashboard.php"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a></li>
                            <li><a class="dropdown-item" href="<?php echo $basePath; ?>ask-question.php"><i class="bi bi-plus-circle me-2"></i>Đặt câu hỏi</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="<?php echo $basePath; ?>logout.php"><i class="bi bi-box-arrow-right me-2"></i>Đăng xuất</a></li>
                        </ul>
                    </div>
                <?php else: ?>
                    <div>
                        <a href="<?php echo $basePath; ?>login.php" class="btn btn-light btn-sm me-2">Đăng nhập</a>
                        <a href="<?php echo $basePath; ?>register.php" class="btn btn-outline-light btn-sm">Đăng ký</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </nav>
