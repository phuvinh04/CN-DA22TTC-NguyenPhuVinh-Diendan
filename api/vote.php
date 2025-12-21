<?php
session_start();
require_once '../config/database.php';
require_once '../includes/badge_helper.php';

header('Content-Type: application/json');

// ============ NOTIFICATION API ============
if (isset($_GET['notification'])) {
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'message' => 'Chưa đăng nhập']);
        exit();
    }
    
    $userId = $_SESSION['user_id'];
    $data = json_decode(file_get_contents('php://input'), true);
    $action = $data['action'] ?? '';
    
    if ($action === 'markAllRead') {
        $conn->prepare("UPDATE THONGBAO SET DADOC = 1 WHERE MANGUOIDUNG = ? AND DADOC = 0")->execute([$userId]);
        echo json_encode(['success' => true]);
    } elseif ($action === 'markRead' && !empty($data['id'])) {
        $conn->prepare("UPDATE THONGBAO SET DADOC = 1 WHERE MATHONGBAO = ? AND MANGUOIDUNG = ?")->execute([$data['id'], $userId]);
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false]);
    }
    exit();
}
// ============ END NOTIFICATION API ============

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Bạn cần đăng nhập để đánh giá']);
    exit();
}

// Lấy dữ liệu
$data = json_decode(file_get_contents('php://input'), true);
$type = $data['type'] ?? ''; // 'question' hoặc 'answer'
$id = $data['id'] ?? '';
$rating = intval($data['rating'] ?? 0); // 1-5 sao
$userId = $_SESSION['user_id'];

if (empty($type) || empty($id) || $rating < 1 || $rating > 5) {
    echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ (1-5 sao)']);
    exit();
}

try {
    // Kiểm tra không tự đánh giá bài của mình
    if ($type === 'question') {
        $authorStmt = $conn->prepare("SELECT MANGUOIDUNG FROM DAT WHERE MACAUHOI = ?");
        $authorStmt->execute([$id]);
    } else {
        $authorStmt = $conn->prepare("SELECT MANGUOIDUNG FROM TRALOI WHERE MACAUTRALOI = ?");
        $authorStmt->execute([$id]);
    }
    $author = $authorStmt->fetch();
    
    if ($author && $author['MANGUOIDUNG'] == $userId) {
        echo json_encode(['success' => false, 'message' => 'Bạn không thể tự đánh giá bài của mình']);
        exit();
    }

    // Kiểm tra xem user đã đánh giá chưa
    if ($type === 'question') {
        $checkVote = $conn->prepare("
            SELECT v.MAVOTE, v.LOAIVOTE 
            FROM VOTE v
            JOIN BINHCHONCAUHOI bc ON v.MAVOTE = bc.MAVOTE
            WHERE v.MANGUOIDUNG = ? AND bc.MACAUHOI = ?
        ");
        $checkVote->execute([$userId, $id]);
    } else {
        $checkVote = $conn->prepare("
            SELECT v.MAVOTE, v.LOAIVOTE 
            FROM VOTE v
            JOIN BINHCHONCAUTRALOI bc ON v.MAVOTE = bc.MAVOTE
            WHERE v.MANGUOIDUNG = ? AND bc.MACAUTRALOI = ?
        ");
        $checkVote->execute([$userId, $id]);
    }
    
    $existingVote = $checkVote->fetch();
    
    if ($existingVote) {
        // Đã đánh giá -> Cập nhật
        $oldRating = $existingVote['LOAIVOTE'];
        $conn->prepare("UPDATE VOTE SET LOAIVOTE = ?, NGAYTAO = NOW() WHERE MAVOTE = ?")
            ->execute([$rating, $existingVote['MAVOTE']]);
        
        // Cập nhật điểm cho người được đánh giá
        if ($author) {
            $pointChange = ($rating - $oldRating); // Chênh lệch điểm
            $conn->prepare("UPDATE NGUOIDUNG SET DIEMDANHGIA = DIEMDANHGIA + ? WHERE MANGUOIDUNG = ?")
                ->execute([$pointChange, $author['MANGUOIDUNG']]);
        }
        
        $message = 'Đã cập nhật đánh giá thành ' . $rating . ' sao';
    } else {
        // Chưa đánh giá -> Tạo mới
        $voteId = 'VT' . time() . rand(1000, 9999);
        
        // Tạo vote
        $stmt = $conn->prepare("INSERT INTO VOTE (MAVOTE, MANGUOIDUNG, LOAIVOTE, NGAYTAO) VALUES (?, ?, ?, NOW())");
        $stmt->execute([$voteId, $userId, $rating]);
        
        // Liên kết vote với câu hỏi hoặc câu trả lời
        if ($type === 'question') {
            $stmt = $conn->prepare("INSERT INTO BINHCHONCAUHOI (MAVOTE, MACAUHOI) VALUES (?, ?)");
            $stmt->execute([$voteId, $id]);
        } else {
            $stmt = $conn->prepare("INSERT INTO BINHCHONCAUTRALOI (MAVOTE, MACAUTRALOI) VALUES (?, ?)");
            $stmt->execute([$voteId, $id]);
        }
        
        // Cộng điểm cho người được đánh giá
        if ($author) {
            $conn->prepare("UPDATE NGUOIDUNG SET DIEMDANHGIA = DIEMDANHGIA + ? WHERE MANGUOIDUNG = ?")
                ->execute([$rating, $author['MANGUOIDUNG']]);
            
            // Tạo thông báo cho người được vote
            try {
                $voterName = $_SESSION['fullname'] ?? 'Ai đó';
                $notifId = 'TB' . time() . rand(100, 999);
                $notifTitle = $voterName . ' đã đánh giá ' . $rating . ' sao cho ' . ($type === 'question' ? 'câu hỏi' : 'câu trả lời') . ' của bạn';
                // Tạo link đến câu hỏi hoặc câu trả lời
                if ($type === 'question') {
                    $notifLink = 'question-detail.php?id=' . $id;
                } else {
                    // Lấy MACAUHOI từ câu trả lời
                    $qStmt = $conn->prepare("SELECT MACAUHOI FROM TRALOI WHERE MACAUTRALOI = ?");
                    $qStmt->execute([$id]);
                    $questionId = $qStmt->fetchColumn();
                    $notifLink = 'question-detail.php?id=' . $questionId . '#answer-' . $id;
                }
                $conn->prepare("INSERT INTO THONGBAO (MATHONGBAO, MANGUOIDUNG, LOAI, TIEUDE, LINK, DADOC, NGAYTAO) VALUES (?, ?, 'vote', ?, ?, 0, NOW())")
                    ->execute([$notifId, $author['MANGUOIDUNG'], $notifTitle, $notifLink]);
            } catch (PDOException $e) {}
            
            // Kiểm tra và cấp huy hiệu cho người được vote
            checkAndAwardBadges($author['MANGUOIDUNG']);
        }
        
        $message = 'Đánh giá ' . $rating . ' sao thành công';
    }
    
    // Tính điểm trung bình và số lượt đánh giá
    if ($type === 'question') {
        $statsStmt = $conn->prepare("
            SELECT 
                COUNT(*) as totalRatings,
                AVG(v.LOAIVOTE) as avgRating
            FROM VOTE v
            JOIN BINHCHONCAUHOI bc ON v.MAVOTE = bc.MAVOTE
            WHERE bc.MACAUHOI = ?
        ");
        $statsStmt->execute([$id]);
    } else {
        $statsStmt = $conn->prepare("
            SELECT 
                COUNT(*) as totalRatings,
                AVG(v.LOAIVOTE) as avgRating
            FROM VOTE v
            JOIN BINHCHONCAUTRALOI bc ON v.MAVOTE = bc.MAVOTE
            WHERE bc.MACAUTRALOI = ?
        ");
        $statsStmt->execute([$id]);
    }
    
    $stats = $statsStmt->fetch();
    $avgRating = round($stats['avgRating'], 1);
    $totalRatings = $stats['totalRatings'];
    
    echo json_encode([
        'success' => true,
        'message' => $message,
        'avgRating' => $avgRating,
        'totalRatings' => $totalRatings,
        'userRating' => $rating
    ]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()]);
}
?>
