<?php
$pageTitle = 'Thông báo của tôi';
require_once '../config/database.php';
require_once '../config/session.php';
require_once '../includes/notification_helper.php';
requireLogin();

$currentUser = getCurrentUser();
$userId = $currentUser['id'];

// Đánh dấu tất cả đã đọc khi vào trang
if (isset($_GET['mark_all_read'])) {
    markAllNotificationsAsRead($userId);
    header('Location: notifications.php');
    exit();
}

// Lấy tất cả thông báo
$notifications = getNotifications($userId, 50);
$unreadCount = countUnreadNotifications($userId);

require_once '../includes/header.php';
?>

<main class="py-4">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                        <h5 class="mb-0"><i class="bi bi-bell me-2 text-primary"></i>Thông báo (<?php echo $unreadCount; ?> chưa đọc)</h5>
                        <?php if ($unreadCount > 0): ?>
                        <a href="?mark_all_read=1" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-check-all me-1"></i>Đánh dấu tất cả đã đọc
                        </a>
                        <?php endif; ?>
                    </div>
                    <div class="card-body p-0">
                        <?php if (empty($notifications)): ?>
                            <div class="text-center py-5">
                                <i class="bi bi-bell-slash display-1 text-muted opacity-50"></i>
                                <p class="text-muted mt-3 mb-0">Bạn chưa có thông báo nào</p>
                            </div>
                        <?php else: ?>
                            <div class="list-group list-group-flush">
                                <?php foreach ($notifications as $notif): 
                                    $link = !empty($notif['LINK']) ? '../' . $notif['LINK'] : '#';
                                ?>
                                    <a href="<?php echo htmlspecialchars($link); ?>" 
                                       class="list-group-item list-group-item-action py-3 <?php echo $notif['DADOC'] ? '' : 'bg-light'; ?>"
                                       data-id="<?php echo $notif['MATHONGBAO']; ?>"
                                       onclick="markNotificationRead('<?php echo $notif['MATHONGBAO']; ?>')">
                                        <div class="d-flex align-items-start">
                                            <div class="me-3">
                                                <i class="bi <?php echo getNotificationIcon($notif['LOAI']); ?> fs-4"></i>
                                            </div>
                                            <div class="flex-grow-1">
                                                <h6 class="mb-1 <?php echo $notif['DADOC'] ? '' : 'fw-bold'; ?>"><?php echo htmlspecialchars($notif['TIEUDE']); ?></h6>
                                                <small class="text-muted"><?php echo formatNotificationTime($notif['NGAYTAO']); ?></small>
                                            </div>
                                            <?php if (!$notif['DADOC']): ?>
                                                <span class="badge bg-primary">Mới</span>
                                            <?php endif; ?>
                                        </div>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<script>
function markNotificationRead(notifId) {
    fetch('../api/notification.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'markRead', id: notifId })
    }).catch(function(err) { console.log(err); });
}
</script>

<?php require_once '../includes/footer.php'; ?>
