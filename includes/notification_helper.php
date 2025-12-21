<?php
/**
 * Notification Helper - Xử lý thông báo cho người dùng
 */

function getNotifications($userId, $limit = 10, $unreadOnly = false) {
    global $conn;
    if (!$conn) return [];
    
    try {
        $limit = (int)$limit;
        $whereClause = $unreadOnly ? "AND DADOC = 0" : "";
        $sql = "SELECT * FROM THONGBAO WHERE MANGUOIDUNG = ? $whereClause ORDER BY NGAYTAO DESC LIMIT $limit";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return [];
    }
}

function countUnreadNotifications($userId) {
    global $conn;
    if (!$conn) return 0;
    
    try {
        $stmt = $conn->prepare("SELECT COUNT(*) FROM THONGBAO WHERE MANGUOIDUNG = ? AND DADOC = 0");
        $stmt->execute([$userId]);
        return (int)$stmt->fetchColumn();
    } catch (PDOException $e) {
        return 0;
    }
}

function markNotificationAsRead($notificationId, $userId) {
    global $conn;
    if (!$conn) return false;
    
    try {
        $stmt = $conn->prepare("UPDATE THONGBAO SET DADOC = 1 WHERE MATHONGBAO = ? AND MANGUOIDUNG = ?");
        return $stmt->execute([$notificationId, $userId]);
    } catch (PDOException $e) {
        return false;
    }
}

function markAllNotificationsAsRead($userId) {
    global $conn;
    if (!$conn) return false;
    
    try {
        $stmt = $conn->prepare("UPDATE THONGBAO SET DADOC = 1 WHERE MANGUOIDUNG = ? AND DADOC = 0");
        return $stmt->execute([$userId]);
    } catch (PDOException $e) {
        return false;
    }
}

function deleteNotification($notificationId, $userId) {
    global $conn;
    if (!$conn) return false;
    
    try {
        $stmt = $conn->prepare("DELETE FROM THONGBAO WHERE MATHONGBAO = ? AND MANGUOIDUNG = ?");
        return $stmt->execute([$notificationId, $userId]);
    } catch (PDOException $e) {
        return false;
    }
}

function createNotification($userId, $type, $title, $link = '') {
    global $conn;
    if (!$conn) return false;
    
    try {
        $notifId = 'TB' . time() . rand(100, 999);
        $stmt = $conn->prepare("INSERT INTO THONGBAO (MATHONGBAO, MANGUOIDUNG, LOAI, TIEUDE, LINK, DADOC, NGAYTAO) VALUES (?, ?, ?, ?, ?, 0, NOW())");
        return $stmt->execute([$notifId, $userId, $type, $title, $link]);
    } catch (PDOException $e) {
        return false;
    }
}

function getNotificationIcon($type) {
    $icons = [
        'answer' => 'bi-chat-left-text text-primary',
        'vote' => 'bi-star-fill text-warning',
        'badge' => 'bi-award-fill text-success',
        'system' => 'bi-bell text-secondary'
    ];
    return $icons[$type] ?? 'bi-bell text-secondary';
}

function formatNotificationTime($datetime) {
    if (!$datetime) return '';
    $now = new DateTime();
    $time = new DateTime($datetime);
    $diff = $now->diff($time);
    
    if ($diff->y > 0) return $diff->y . ' năm trước';
    if ($diff->m > 0) return $diff->m . ' tháng trước';
    if ($diff->d > 0) return $diff->d . ' ngày trước';
    if ($diff->h > 0) return $diff->h . ' giờ trước';
    if ($diff->i > 0) return $diff->i . ' phút trước';
    return 'Vừa xong';
}
