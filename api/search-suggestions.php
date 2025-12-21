<?php
header('Content-Type: application/json');
require_once '../config/database.php';
require_once '../config/session.php';

$currentUser = getCurrentUser();
if (!$currentUser) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$query = trim($_GET['q'] ?? '');
if (strlen($query) < 2) {
    echo json_encode(['suggestions' => []]);
    exit;
}

$suggestions = [];

try {
    // Tìm câu hỏi
    $stmt = $conn->prepare("
        SELECT DISTINCT ch.TIEUDE, ch.MACAUHOI, 'question' as type
        FROM CAUHOI ch 
        JOIN DAT d ON ch.MACAUHOI = d.MACAUHOI
        WHERE ch.TRANGTHAI IN ('open', 'closed') 
        AND ch.TIEUDE LIKE ?
        ORDER BY d.NGAYDANG DESC
        LIMIT 5
    ");
    $stmt->execute(['%' . $query . '%']);
    $questions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($questions as $q) {
        $suggestions[] = [
            'type' => 'question',
            'title' => $q['TIEUDE'],
            'id' => $q['MACAUHOI'],
            'icon' => 'bi-question-circle',
            'url' => '../question-detail.php?id=' . $q['MACAUHOI']
        ];
    }

    // Tìm tags
    $stmt = $conn->prepare("
        SELECT DISTINCT t.TENTAG, t.MATAG, COUNT(ct.MACAUHOI) as usage_count
        FROM TAG t
        LEFT JOIN CHUDETAG ct ON t.MATAG = ct.MATAG
        WHERE t.TENTAG LIKE ?
        GROUP BY t.MATAG, t.TENTAG
        ORDER BY usage_count DESC
        LIMIT 3
    ");
    $stmt->execute(['%' . $query . '%']);
    $tags = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($tags as $tag) {
        $suggestions[] = [
            'type' => 'tag',
            'title' => $tag['TENTAG'],
            'id' => $tag['MATAG'],
            'icon' => 'bi-tag',
            'url' => '../questions.php?tag=' . urlencode($tag['TENTAG']),
            'count' => $tag['usage_count']
        ];
    }

    // Tìm người dùng (tác giả)
    $stmt = $conn->prepare("
        SELECT DISTINCT nd.HOTEN, nd.MANGUOIDUNG, nd.ANHDAIDIEN, nd.DIEMDANHGIA
        FROM NGUOIDUNG nd
        WHERE nd.HOTEN LIKE ? AND nd.TRANGTHAI = 'active'
        ORDER BY nd.DIEMDANHGIA DESC
        LIMIT 3
    ");
    $stmt->execute(['%' . $query . '%']);
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($users as $user) {
        $suggestions[] = [
            'type' => 'user',
            'title' => $user['HOTEN'],
            'id' => $user['MANGUOIDUNG'],
            'icon' => 'bi-person',
            'url' => '../profile.php?id=' . $user['MANGUOIDUNG'],
            'avatar' => $user['ANHDAIDIEN'],
            'points' => $user['DIEMDANHGIA']
        ];
    }

    echo json_encode(['suggestions' => $suggestions]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error']);
}
?>
