<?php
// Include files cần thiết
if (!isset($conn)) {
    require_once __DIR__ . '/../config/database.php';
}
if (!function_exists('getCurrentUser')) {
    require_once __DIR__ . '/../config/session.php';
}
if (!function_exists('getNotifications')) {
    require_once __DIR__ . '/notification_helper.php';
}
if (!function_exists('getUserBadges')) {
    require_once __DIR__ . '/badge_helper.php';
}

// Lấy currentUser nếu chưa có
if (!isset($currentUser)) {
    $currentUser = getCurrentUser();
}

// Lấy thông báo và huy hiệu
$notifications = [];
$unreadCount = 0;
$headerBadges = [];
if ($currentUser && function_exists('getNotifications')) {
    $notifications = getNotifications($currentUser['id'], 5);
    $unreadCount = countUnreadNotifications($currentUser['id']);
    $headerBadges = getUserBadges($currentUser['id']);
}

// Xác định base path
$basePath = '';
$currentPath = $_SERVER['PHP_SELF'];
if (strpos($currentPath, '/user/') !== false || strpos($currentPath, '/admin/') !== false || strpos($currentPath, '/moderator/') !== false) {
    $basePath = '../';
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? 'Diễn đàn Hỏi Đáp'; ?></title>
    
    <!-- Google Fonts - Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?php echo $basePath; ?>assets/css/style.css">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark sticky-top">
        <div class="container">
            <a class="navbar-brand" href="<?php echo $basePath; ?>index.php">
                <i class="bi bi-mortarboard-fill"></i>
                <span>Diễn Đàn CN</span>
            </a>
            
            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo $basePath; ?>index.php">
                            <i class="bi bi-house me-1"></i>Trang chủ
                        </a>
                    </li>
                    <?php if ($currentUser): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo $basePath; ?>questions.php">
                            <i class="bi bi-chat-left-text me-1"></i>Câu hỏi
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo $basePath; ?>tags.php">
                            <i class="bi bi-tags me-1"></i>Tags
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo $basePath; ?>leaderboard.php">
                            <i class="bi bi-trophy me-1"></i>Xếp hạng
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo $basePath; ?>users.php">
                            <i class="bi bi-people me-1"></i>Thành viên
                        </a>
                    </li>
                    <?php endif; ?>
                </ul>
                
                <!-- Search với Suggestions (chỉ hiện khi đăng nhập) -->
                <?php if ($currentUser): ?>
                <div class="search-container d-none d-lg-block me-2">
                    <form class="search-form" action="<?php echo $basePath; ?>questions.php" method="GET" id="searchForm">
                        <i class="bi bi-search search-icon"></i>
                        <input class="search-input" type="search" name="q" id="searchInput" 
                               placeholder="Tìm kiếm..." autocomplete="off"
                               value="<?php echo htmlspecialchars($_GET['q'] ?? ''); ?>">
                    </form>
                    <div id="searchSuggestions" class="search-suggestions"></div>
                </div>
                <?php endif; ?>

                <?php if ($currentUser): ?>
                    <?php if ($currentUser['role'] === 'admin'): ?>
                        <a href="<?php echo $basePath; ?>admin/dashboard.php" class="btn btn-danger btn-sm me-2">
                            <i class="bi bi-shield-check me-1"></i>Admin
                        </a>
                    <?php else: ?>
                        <a href="<?php echo $basePath; ?>ask-question.php" class="btn btn-ask btn-sm me-2 d-none d-xl-inline-flex">
                            <i class="bi bi-plus-lg me-1"></i>Đặt câu hỏi
                        </a>
                    <?php endif; ?>
                    
                    <!-- Daily Check-in Button -->
                    <button type="button" class="btn btn-checkin me-2" id="checkinBtn" onclick="showCheckinModal()" title="Điểm danh hàng ngày">
                        <i class="bi bi-calendar-check"></i>
                        <span class="streak-badge" id="streakBadge" style="display: none;">0</span>
                    </button>

                    <!-- Notifications -->
                    <div class="dropdown me-2">
                        <a class="btn btn-link text-white position-relative p-2 notification-bell" href="#" data-bs-toggle="dropdown" data-bs-auto-close="outside">
                            <i class="bi bi-bell fs-5"></i>
                            <?php if ($unreadCount > 0): ?>
                                <span class="notification-badge"><?php echo $unreadCount > 9 ? '9+' : $unreadCount; ?></span>
                            <?php endif; ?>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end notification-dropdown p-0">
                            <div class="notification-header d-flex justify-content-between align-items-center">
                                <h6 class="mb-0">Thông báo</h6>
                                <?php if ($unreadCount > 0): ?>
                                    <a href="#" class="text-primary small" onclick="markAllRead(event)">Đánh dấu đã đọc</a>
                                <?php endif; ?>
                            </div>
                            <div class="notification-body">
                                <?php if (empty($notifications)): ?>
                                    <div class="text-center py-5 text-muted">
                                        <i class="bi bi-bell-slash fs-1 d-block mb-2 opacity-50"></i>
                                        <span class="small">Không có thông báo</span>
                                    </div>
                                <?php else: ?>
                                    <?php foreach ($notifications as $notif): ?>
                                        <a href="<?php echo $notif['LINK'] ? htmlspecialchars($basePath . $notif['LINK']) : '#'; ?>" 
                                           class="notification-item <?php echo $notif['DADOC'] ? '' : 'unread'; ?>"
                                           data-id="<?php echo $notif['MATHONGBAO']; ?>">
                                            <div class="notification-icon me-3">
                                                <i class="bi <?php echo getNotificationIcon($notif['LOAI']); ?>"></i>
                                            </div>
                                            <div class="notification-content flex-grow-1">
                                                <p class="notification-title mb-1"><?php echo htmlspecialchars($notif['TIEUDE']); ?></p>
                                                <small class="text-muted"><?php echo formatNotificationTime($notif['NGAYTAO']); ?></small>
                                            </div>
                                            <?php if (!$notif['DADOC']): ?>
                                                <span class="unread-dot"></span>
                                            <?php endif; ?>
                                        </a>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                            <div class="notification-footer text-center">
                                <a href="<?php echo $basePath; ?>user/notifications.php" class="text-primary small">Xem tất cả</a>
                            </div>
                        </div>
                    </div>

                    <!-- User Menu -->
                    <div class="dropdown">
                        <a class="btn btn-light btn-sm dropdown-toggle d-flex align-items-center gap-2" href="#" data-bs-toggle="dropdown">
                            <?php echo renderAvatarWithFrame($currentUser['avatar'], $currentUser['id'], 'sm', false); ?>
                            <span class="d-none d-md-inline"><?php echo htmlspecialchars($currentUser['fullname']); ?></span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li class="dropdown-header">
                                <div class="d-flex align-items-center gap-2">
                                    <strong><?php echo htmlspecialchars($currentUser['fullname']); ?></strong>
                                    <?php if ($currentUser['role'] === 'admin'): ?>
                                        <span class="badge bg-danger">Admin</span>
                                    <?php elseif ($currentUser['role'] === 'moderator'): ?>
                                        <span class="badge bg-warning">Mod</span>
                                    <?php endif; ?>
                                </div>
                                <small class="text-muted">@<?php echo htmlspecialchars($currentUser['username']); ?></small>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            
                            <?php if ($currentUser['role'] === 'admin'): ?>
                                <li><a class="dropdown-item" href="<?php echo $basePath; ?>admin/dashboard.php"><i class="bi bi-speedometer2 me-2"></i>Quản trị</a></li>
                                <li><a class="dropdown-item" href="<?php echo $basePath; ?>admin/questions.php"><i class="bi bi-check-circle me-2"></i>Duyệt câu hỏi</a></li>
                                <li><a class="dropdown-item" href="<?php echo $basePath; ?>admin/answers.php"><i class="bi bi-chat-left-text me-2"></i>Duyệt câu trả lời</a></li>
                                <li><a class="dropdown-item" href="<?php echo $basePath; ?>admin/users.php"><i class="bi bi-people me-2"></i>Người dùng</a></li>
                            <?php elseif ($currentUser['role'] === 'moderator'): ?>
                                <li><a class="dropdown-item" href="<?php echo $basePath; ?>user/dashboard.php"><i class="bi bi-speedometer2 me-2"></i>Tổng quan</a></li>
                                <li><a class="dropdown-item" href="<?php echo $basePath; ?>moderator/index.php"><i class="bi bi-shield-check me-2"></i>Quản lý nội dung</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="<?php echo $basePath; ?>user/my-questions.php"><i class="bi bi-chat-left-text me-2"></i>Câu hỏi của tôi</a></li>
                                <li><a class="dropdown-item" href="<?php echo $basePath; ?>user/my-answers.php"><i class="bi bi-chat-right-text me-2"></i>Trả lời của tôi</a></li>
                                <li><a class="dropdown-item" href="<?php echo $basePath; ?>user/my-points.php"><i class="bi bi-trophy me-2"></i>Điểm của tôi</a></li>
                            <?php else: ?>
                                <li><a class="dropdown-item" href="<?php echo $basePath; ?>user/dashboard.php"><i class="bi bi-speedometer2 me-2"></i>Tổng quan</a></li>
                                <li><a class="dropdown-item" href="<?php echo $basePath; ?>user/my-questions.php"><i class="bi bi-chat-left-text me-2"></i>Câu hỏi của tôi</a></li>
                                <li><a class="dropdown-item" href="<?php echo $basePath; ?>user/my-answers.php"><i class="bi bi-chat-right-text me-2"></i>Trả lời của tôi</a></li>
                                <li><a class="dropdown-item" href="<?php echo $basePath; ?>user/my-points.php"><i class="bi bi-trophy me-2"></i>Điểm của tôi</a></li>
                            <?php endif; ?>
                            <li><a class="dropdown-item" href="<?php echo $basePath; ?>user/edit-profile.php"><i class="bi bi-gear me-2"></i>Cài đặt</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="<?php echo $basePath; ?>logout.php"><i class="bi bi-box-arrow-right me-2"></i>Đăng xuất</a></li>
                        </ul>
                    </div>
                <?php else: ?>
                    <div class="d-flex gap-2">
                        <a href="<?php echo $basePath; ?>login.php" class="btn btn-light btn-sm">Đăng nhập</a>
                        <a href="<?php echo $basePath; ?>register.php" class="btn btn-outline-light btn-sm">Đăng ký</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </nav>
