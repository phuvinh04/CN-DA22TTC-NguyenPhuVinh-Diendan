<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'config/database.php';
require_once 'config/session.php';
require_once 'includes/badge_helper.php';
require_once 'includes/content_helper.php';

// Yêu cầu đăng nhập để xem chi tiết câu hỏi
$currentUser = getCurrentUser();
if (!$currentUser) {
    header('Location: login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
    exit();
}

$questionId = $_GET['id'] ?? '';

if (empty($questionId)) {
    header('Location: questions.php');
    exit();
}

// Kiểm tra cột HINHANH trong bảng DAT
$hasQuestionImages = false;
try {
    $checkColumn = $conn->query("SHOW COLUMNS FROM DAT LIKE 'HINHANH'");
    $hasQuestionImages = $checkColumn->rowCount() > 0;
} catch (Exception $e) {}

// Kiểm tra cột HINHANH trong bảng TRALOI
$hasAnswerImages = false;
try {
    $checkColumn = $conn->query("SHOW COLUMNS FROM TRALOI LIKE 'HINHANH'");
    $hasAnswerImages = $checkColumn->rowCount() > 0;
} catch (Exception $e) {}

// Lấy thông tin câu hỏi
$imageSelect = $hasQuestionImages ? ", d.HINHANH" : ", NULL as HINHANH";
$questionQuery = "SELECT 
    ch.MACAUHOI, ch.TIEUDE, ch.LUOTXEM, ch.TRANGTHAI,
    d.NOIDUNG, d.NGAYDANG $imageSelect,
    nd.MANGUOIDUNG, nd.HOTEN AS NguoiDat, nd.ANHDAIDIEN, nd.DIEMDANHGIA,
    t.TENTHE AS Tag, t.MATHE
FROM CAUHOI ch
INNER JOIN DAT d ON ch.MACAUHOI = d.MACAUHOI
INNER JOIN NGUOIDUNG nd ON d.MANGUOIDUNG = nd.MANGUOIDUNG
INNER JOIN TAG t ON ch.MATHE = t.MATHE
WHERE ch.MACAUHOI = ?";

$stmt = $conn->prepare($questionQuery);
$stmt->execute([$questionId]);
$question = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$question) {
    header('Location: questions.php');
    exit();
}

$pageTitle = $question['TIEUDE'];

// Cập nhật lượt xem
$userId = $currentUser['id'] ?? null;
$ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'unknown';

$checkViewQuery = "SELECT COUNT(*) FROM LUOTXEM WHERE MACAUHOI = ? AND NGAYXEM = CURDATE() AND ";
if ($userId) {
    $checkViewQuery .= "MANGUOIDUNG = ?";
    $checkParams = [$questionId, $userId];
} else {
    $checkViewQuery .= "IP_ADDRESS = ?";
    $checkParams = [$questionId, $ipAddress];
}

$stmt = $conn->prepare($checkViewQuery);
$stmt->execute($checkParams);
if (!$stmt->fetchColumn()) {
    $conn->prepare("UPDATE CAUHOI SET LUOTXEM = LUOTXEM + 1 WHERE MACAUHOI = ?")->execute([$questionId]);
    $conn->prepare("INSERT INTO LUOTXEM (MACAUHOI, MANGUOIDUNG, IP_ADDRESS, NGAYXEM) VALUES (?, ?, ?, CURDATE())")
        ->execute([$questionId, $userId, $ipAddress]);
}

// Kiểm tra xem cột MACAUTRALOI_CHA có tồn tại không
$hasReplyColumn = false;
try {
    $checkColumn = $conn->query("SHOW COLUMNS FROM TRALOI LIKE 'MACAUTRALOI_CHA'");
    $hasReplyColumn = $checkColumn->rowCount() > 0;
} catch (Exception $e) {
    $hasReplyColumn = false;
}

// Kiểm tra cột TRANGTHAI trong TRALOI và tự động thêm nếu chưa có
$hasAnswerStatus = false;
try {
    $checkColumn = $conn->query("SHOW COLUMNS FROM TRALOI LIKE 'TRANGTHAI'");
    $hasAnswerStatus = $checkColumn->rowCount() > 0;
    
    // Tự động thêm cột nếu chưa có
    if (!$hasAnswerStatus) {
        $conn->exec("ALTER TABLE TRALOI ADD COLUMN TRANGTHAI VARCHAR(20) DEFAULT 'pending'");
        // Cập nhật các câu trả lời cũ thành đã duyệt
        $conn->exec("UPDATE TRALOI SET TRANGTHAI = 'approved' WHERE TRANGTHAI = 'pending' OR TRANGTHAI IS NULL");
        $conn->exec("ALTER TABLE TRALOI ADD INDEX idx_trangthai (TRANGTHAI)");
        $hasAnswerStatus = true;
    }
} catch (Exception $e) {
    $hasAnswerStatus = false;
}

// Điều kiện lọc câu trả lời đã duyệt (hoặc tất cả nếu là admin/mod)
$isModOrAdmin = $currentUser && ($currentUser['role'] === 'admin' || $currentUser['role'] === 'moderator');
$statusCondition = "";
if ($hasAnswerStatus && !$isModOrAdmin) {
    $currentUserId = $conn->quote($currentUser['id'] ?? '');
    $statusCondition = "AND (tl.TRANGTHAI = 'approved' OR tl.MANGUOIDUNG = $currentUserId)";
}

// Lấy câu trả lời (bao gồm cả replies lồng nhau nếu có)
$statusSelect = $hasAnswerStatus ? "tl.TRANGTHAI as ANSWER_STATUS" : "'approved' as ANSWER_STATUS";
$imageSelect = $hasAnswerImages ? ", tl.HINHANH" : ", NULL as HINHANH";

// Kiểm tra cột DUOCCHAPNHAN
$hasAcceptedColumn = false;
try {
    $checkCol = $conn->query("SHOW COLUMNS FROM TRALOI LIKE 'DUOCCHAPNHAN'");
    $hasAcceptedColumn = $checkCol->rowCount() > 0;
} catch (Exception $e) {}
$acceptedSelect = $hasAcceptedColumn ? ", tl.DUOCCHAPNHAN" : ", 0 as DUOCCHAPNHAN";

if ($hasReplyColumn) {
    $answersQuery = "SELECT 
        tl.MACAUTRALOI, tl.NOIDUNGTL, tl.NGAYTL, tl.MACAUTRALOI_CHA, $statusSelect $imageSelect $acceptedSelect,
        nd.MANGUOIDUNG, nd.HOTEN, nd.ANHDAIDIEN, nd.DIEMDANHGIA
    FROM TRALOI tl
    INNER JOIN NGUOIDUNG nd ON tl.MANGUOIDUNG = nd.MANGUOIDUNG
    WHERE tl.MACAUHOI = ? $statusCondition
    ORDER BY tl.DUOCCHAPNHAN DESC, tl.NGAYTL ASC";
} else {
    $answersQuery = "SELECT 
        tl.MACAUTRALOI, tl.NOIDUNGTL, tl.NGAYTL, NULL as MACAUTRALOI_CHA, $statusSelect $imageSelect $acceptedSelect,
        nd.MANGUOIDUNG, nd.HOTEN, nd.ANHDAIDIEN, nd.DIEMDANHGIA
    FROM TRALOI tl
    INNER JOIN NGUOIDUNG nd ON tl.MANGUOIDUNG = nd.MANGUOIDUNG
    WHERE tl.MACAUHOI = ? $statusCondition
    ORDER BY tl.DUOCCHAPNHAN DESC, tl.NGAYTL ASC";
}

$stmt = $conn->prepare($answersQuery);
$stmt->execute([$questionId]);
$allAnswers = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Bước 1: Lưu tất cả thông tin câu trả lời theo ID
$answerData = []; 
foreach ($allAnswers as $answer) {
    $answerData[$answer['MACAUTRALOI']] = [
        'name' => $answer['HOTEN'],
        'content' => $answer['NOIDUNGTL'],
        'parent' => $answer['MACAUTRALOI_CHA']
    ];
}

// Bước 2: Tổ chức câu trả lời theo cấu trúc cha-con
$answers = [];
$replies = [];

foreach ($allAnswers as &$answer) {
    // Thêm thông tin câu được reply (nếu có)
    if (!empty($answer['MACAUTRALOI_CHA'])) {
        $parentId = $answer['MACAUTRALOI_CHA'];
        $answer['REPLY_TO_NAME'] = $answerData[$parentId]['name'] ?? '';
        $answer['REPLY_TO_CONTENT'] = $answerData[$parentId]['content'] ?? '';
        
        // Tìm câu trả lời gốc (cấp 1) để gom replies
        $rootParentId = $parentId;
        while (!empty($answerData[$rootParentId]['parent'])) {
            $rootParentId = $answerData[$rootParentId]['parent'];
        }
        
        if (!isset($replies[$rootParentId])) {
            $replies[$rootParentId] = [];
        }
        $replies[$rootParentId][] = $answer;
    } else {
        $answers[] = $answer;
    }
}
unset($answer);

// Xử lý submit câu trả lời (bao gồm cả reply)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $currentUser) {
    $answerContent = trim($_POST['answer_content'] ?? '');
    $parentAnswerId = trim($_POST['parent_answer_id'] ?? '');
    $answerImages = $_POST['answer_images'] ?? ''; // JSON array of image URLs
    
    if (!empty($answerContent)) {
        $answerId = 'TL' . time() . rand(100, 999);
        
        // Xác định trạng thái ban đầu (admin/mod được duyệt tự động)
        $initialStatus = ($currentUser['role'] === 'admin' || $currentUser['role'] === 'moderator') ? 'approved' : 'pending';
        
        // Nếu là reply và có cột MACAUTRALOI_CHA
        if (!empty($parentAnswerId) && $hasReplyColumn) {
            if ($hasAnswerStatus && $hasAnswerImages) {
                $stmt = $conn->prepare("INSERT INTO TRALOI (MACAUTRALOI, MANGUOIDUNG, MACAUHOI, NOIDUNGTL, NGAYTL, MACAUTRALOI_CHA, TRANGTHAI, HINHANH) VALUES (?, ?, ?, ?, NOW(), ?, ?, ?)");
                $executed = $stmt->execute([$answerId, $currentUser['id'], $questionId, $answerContent, $parentAnswerId, $initialStatus, $answerImages]);
            } elseif ($hasAnswerStatus) {
                $stmt = $conn->prepare("INSERT INTO TRALOI (MACAUTRALOI, MANGUOIDUNG, MACAUHOI, NOIDUNGTL, NGAYTL, MACAUTRALOI_CHA, TRANGTHAI) VALUES (?, ?, ?, ?, NOW(), ?, ?)");
                $executed = $stmt->execute([$answerId, $currentUser['id'], $questionId, $answerContent, $parentAnswerId, $initialStatus]);
            } else {
                $stmt = $conn->prepare("INSERT INTO TRALOI (MACAUTRALOI, MANGUOIDUNG, MACAUHOI, NOIDUNGTL, NGAYTL, MACAUTRALOI_CHA) VALUES (?, ?, ?, ?, NOW(), ?)");
                $executed = $stmt->execute([$answerId, $currentUser['id'], $questionId, $answerContent, $parentAnswerId]);
            }
            $points = 5; // Reply được ít điểm hơn
        } else {
            if ($hasAnswerStatus && $hasAnswerImages) {
                $stmt = $conn->prepare("INSERT INTO TRALOI (MACAUTRALOI, MANGUOIDUNG, MACAUHOI, NOIDUNGTL, NGAYTL, TRANGTHAI, HINHANH) VALUES (?, ?, ?, ?, NOW(), ?, ?)");
                $executed = $stmt->execute([$answerId, $currentUser['id'], $questionId, $answerContent, $initialStatus, $answerImages]);
            } elseif ($hasAnswerStatus) {
                $stmt = $conn->prepare("INSERT INTO TRALOI (MACAUTRALOI, MANGUOIDUNG, MACAUHOI, NOIDUNGTL, NGAYTL, TRANGTHAI) VALUES (?, ?, ?, ?, NOW(), ?)");
                $executed = $stmt->execute([$answerId, $currentUser['id'], $questionId, $answerContent, $initialStatus]);
            } else {
                $stmt = $conn->prepare("INSERT INTO TRALOI (MACAUTRALOI, MANGUOIDUNG, MACAUHOI, NOIDUNGTL, NGAYTL) VALUES (?, ?, ?, ?, NOW())");
                $executed = $stmt->execute([$answerId, $currentUser['id'], $questionId, $answerContent]);
            }
            $points = 10;
        }
        
        if ($executed) {
            $conn->prepare("UPDATE NGUOIDUNG SET DIEMDANHGIA = DIEMDANHGIA + ? WHERE MANGUOIDUNG = ?")
                ->execute([$points, $currentUser['id']]);
            
            checkAndAwardBadges($currentUser['id']);
            
            // Thông báo cho người liên quan
            if (!empty($parentAnswerId)) {
                // Reply: thông báo cho người viết câu trả lời gốc
                $parentOwnerStmt = $conn->prepare("SELECT MANGUOIDUNG FROM TRALOI WHERE MACAUTRALOI = ?");
                $parentOwnerStmt->execute([$parentAnswerId]);
                $parentOwnerId = $parentOwnerStmt->fetchColumn();
                
                if ($parentOwnerId && $parentOwnerId !== $currentUser['id']) {
                    try {
                        $notifId = 'TB' . time() . rand(100, 999);
                        $notifTitle = $currentUser['fullname'] . ' đã trả lời bình luận của bạn';
                        $notifLink = 'question-detail.php?id=' . $questionId . '#answer-' . $parentAnswerId;
                        $conn->prepare("INSERT INTO THONGBAO (MATHONGBAO, MANGUOIDUNG, LOAI, TIEUDE, LINK, DADOC, NGAYTAO) VALUES (?, ?, 'reply', ?, ?, 0, NOW())")
                            ->execute([$notifId, $parentOwnerId, $notifTitle, $notifLink]);
                    } catch (PDOException $e) {}
                }
            } else {
                // Câu trả lời chính: thông báo cho người đặt câu hỏi
                $questionOwnerStmt = $conn->prepare("SELECT MANGUOIDUNG FROM DAT WHERE MACAUHOI = ?");
                $questionOwnerStmt->execute([$questionId]);
                $questionOwnerId = $questionOwnerStmt->fetchColumn();
                
                if ($questionOwnerId && $questionOwnerId !== $currentUser['id']) {
                    try {
                        $notifId = 'TB' . time() . rand(100, 999);
                        $notifTitle = $currentUser['fullname'] . ' đã trả lời câu hỏi của bạn';
                        $notifLink = 'question-detail.php?id=' . $questionId;
                        $conn->prepare("INSERT INTO THONGBAO (MATHONGBAO, MANGUOIDUNG, LOAI, TIEUDE, LINK, DADOC, NGAYTAO) VALUES (?, ?, 'answer', ?, ?, 0, NOW())")
                            ->execute([$notifId, $questionOwnerId, $notifTitle, $notifLink]);
                    } catch (PDOException $e) {}
                }
            }
            
            // Redirect với thông báo phù hợp
            if ($initialStatus === 'pending') {
                header('Location: question-detail.php?id=' . $questionId . '&answered=pending');
            } else {
                header('Location: question-detail.php?id=' . $questionId . '&answered=1');
            }
            exit();
        }
    }
}

$justAnswered = isset($_GET['answered']) && $_GET['answered'] == '1';
$answerPending = isset($_GET['answered']) && $_GET['answered'] == 'pending';

// Helper function render stars
function renderStars($avgRating, $userRating = 0, $type = '', $id = '', $disabled = false) {
    $avgRating = round($avgRating, 1);
    $html = '<div class="star-rating" data-rating-id="' . $id . '">';
    for ($i = 1; $i <= 5; $i++) {
        $activeClass = $i <= round($avgRating) ? 'active' : '';
        $userClass = $userRating && $i <= $userRating ? 'user-rated' : '';
        $disabledAttr = $disabled ? 'disabled' : '';
        $onclick = $disabled ? '' : "onclick=\"rate('$type', '$id', $i)\"";
        $html .= "<button type=\"button\" class=\"star-btn $activeClass $userClass\" $onclick $disabledAttr title=\"$i sao\">";
        $html .= '<i class="bi bi-star-fill"></i></button>';
    }
    $html .= '</div>';
    return $html;
}

require_once 'includes/header.php';
?>

<main class="py-5">
    <div class="container">
        <?php if ($justAnswered): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <i class="bi bi-check-circle-fill"></i>
            <strong>Thành công!</strong> Câu trả lời đã được gửi. Bạn nhận được <strong>+10 điểm</strong>! 🎉
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <script>
            if (window.history.replaceState) {
                window.history.replaceState(null, null, window.location.pathname + '?id=<?php echo $questionId; ?>');
            }
        </script>
        <?php endif; ?>

        <?php if ($answerPending): ?>
        <div class="alert alert-warning alert-dismissible fade show">
            <i class="bi bi-hourglass-split"></i>
            <strong>Đã gửi!</strong> Câu trả lời của bạn đang chờ duyệt. Bạn sẽ nhận được thông báo khi được duyệt.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <script>
            if (window.history.replaceState) {
                window.history.replaceState(null, null, window.location.pathname + '?id=<?php echo $questionId; ?>');
            }
        </script>
        <?php endif; ?>

        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php">Trang chủ</a></li>
                <li class="breadcrumb-item"><a href="questions.php">Câu hỏi</a></li>
                <li class="breadcrumb-item active"><?php echo mb_substr(htmlspecialchars($question['TIEUDE']), 0, 40); ?>...</li>
            </ol>
        </nav>

        <div class="row g-4">
            <div class="col-lg-8">
                <!-- Question -->
                <div class="card mb-4">
                    <div class="card-body">
                        <!-- Chủ đề (Tag) - hiển thị trên tiêu đề -->
                        <div class="mb-2">
                            <a href="questions.php?tag=<?php echo $question['MATHE']; ?>" class="badge bg-primary text-decoration-none" style="font-size: 12px; padding: 6px 12px;">
                                <i class="bi bi-folder me-1"></i><?php echo htmlspecialchars($question['Tag']); ?>
                            </a>
                        </div>
                        
                        <!-- Tiêu đề câu hỏi -->
                        <h2 class="mb-3" style="font-size: var(--font-2xl);"><?php echo htmlspecialchars($question['TIEUDE']); ?></h2>
                        
                        <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
                            <div class="d-flex flex-wrap gap-4 text-muted" style="font-size: var(--font-sm);">
                                <span><i class="bi bi-clock me-1"></i><?php echo date('d/m/Y H:i', strtotime($question['NGAYDANG'])); ?></span>
                                <span><i class="bi bi-eye me-1"></i><?php echo number_format($question['LUOTXEM']); ?> lượt xem</span>
                                <span><i class="bi bi-chat-dots me-1"></i><?php echo count($answers); ?> trả lời</span>
                            </div>
                            <?php if ($currentUser && $currentUser['id'] === $question['MANGUOIDUNG'] && $question['TRANGTHAI'] === 'open'): ?>
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="closeQuestion()">
                                <i class="bi bi-lock me-1"></i>Đóng câu hỏi
                            </button>
                            <?php elseif ($question['TRANGTHAI'] === 'closed'): ?>
                            <span class="badge bg-secondary"><i class="bi bi-lock me-1"></i>Đã đóng</span>
                            <?php endif; ?>
                        </div>

                        <hr>

                        <div class="row g-4">
                            <!-- Rating -->
                            <div class="col-auto">
                                <?php
                                $ratingStmt = $conn->prepare("SELECT AVG(v.LOAIVOTE) as avgRating, COUNT(*) as totalRatings FROM VOTE v JOIN BINHCHONCAUHOI bc ON v.MAVOTE = bc.MAVOTE WHERE bc.MACAUHOI = ?");
                                $ratingStmt->execute([$questionId]);
                                $qRating = $ratingStmt->fetch(PDO::FETCH_ASSOC);
                                $avgQuestionRating = round($qRating['avgRating'] ?? 0, 1);
                                $totalQuestionRatings = $qRating['totalRatings'] ?? 0;
                                
                                $userQuestionRating = 0;
                                if ($currentUser) {
                                    $userRatingStmt = $conn->prepare("SELECT v.LOAIVOTE FROM VOTE v JOIN BINHCHONCAUHOI bc ON v.MAVOTE = bc.MAVOTE WHERE v.MANGUOIDUNG = ? AND bc.MACAUHOI = ?");
                                    $userRatingStmt->execute([$currentUser['id'], $questionId]);
                                    $userQuestionRating = $userRatingStmt->fetchColumn() ?: 0;
                                }
                                
                                $canRateQuestion = $currentUser && $currentUser['id'] !== $question['MANGUOIDUNG'];
                                ?>
                                <div class="rating-box">
                                    <div class="avg-rating-display">
                                        <span class="avg-rating"><?php echo $avgQuestionRating; ?></span>
                                        <i class="bi bi-star-fill text-warning"></i>
                                    </div>
                                    <small class="rating-count d-block mb-2">(<?php echo $totalQuestionRatings; ?> đánh giá)</small>
                                    <?php echo renderStars($avgQuestionRating, $userQuestionRating, 'question', $questionId, !$canRateQuestion); ?>
                                    <?php if (!$currentUser): ?>
                                    <small class="text-muted d-block mt-2" style="font-size: var(--font-xs);">Đăng nhập để đánh giá</small>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="col">
                                <div class="mb-4" style="line-height: var(--leading-relaxed);">
                                    <?php echo renderFullContent($question['NOIDUNG'], $question['HINHANH'] ?? ''); ?>
                                </div>

                                <div class="d-flex justify-content-between align-items-end">
                                    <!-- Action buttons -->
                                    <?php if ($currentUser): ?>
                                    <div class="d-flex gap-2">
                                        <?php if ($question['TRANGTHAI'] === 'open'): ?>
                                        <a href="#answerForm" class="btn btn-sm btn-primary">
                                            <i class="bi bi-reply"></i> Trả lời
                                        </a>
                                        <?php endif; ?>
                                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="shareQuestion()">
                                            <i class="bi bi-share"></i> Chia sẻ
                                        </button>
                                        <?php if ($currentUser['id'] !== $question['MANGUOIDUNG']): ?>
                                        <button type="button" class="btn btn-sm btn-outline-warning" data-bs-toggle="modal" data-bs-target="#reportModal">
                                            <i class="bi bi-flag"></i> Báo cáo
                                        </button>
                                        <?php endif; ?>
                                    </div>
                                    <?php else: ?>
                                    <div></div>
                                    <?php endif; ?>
                                    
                                    <?php
                                    // Lấy huy hiệu của người đặt câu hỏi
                                    $questionUserBadges = $conn->prepare("SELECT h.BIEUTUONG, h.TENHUYHIEU FROM NHAN n JOIN HUYHIEU h ON n.MAHUYHIEU = h.MAHUYHIEU WHERE n.MANGUOIDUNG = ? LIMIT 5");
                                    $questionUserBadges->execute([$question['MANGUOIDUNG']]);
                                    $qBadges = $questionUserBadges->fetchAll(PDO::FETCH_ASSOC);
                                    ?>
                                    <div class="p-3 rounded" style="background: var(--gray-50);">
                                        <small class="text-muted d-block mb-2">Đăng bởi</small>
                                        <div class="d-flex align-items-center gap-2">
                                            <?php echo renderAvatarWithFrame($question['ANHDAIDIEN'], $question['MANGUOIDUNG'], 'normal', true); ?>
                                            <div>
                                                <a href="profile.php?id=<?php echo $question['MANGUOIDUNG']; ?>" class="fw-semibold text-decoration-none">
                                                    <?php echo htmlspecialchars($question['NguoiDat']); ?>
                                                </a>
                                                <div class="text-muted" style="font-size: var(--font-xs);"><?php echo number_format($question['DIEMDANHGIA']); ?> điểm</div>
                                                <?php if (!empty($qBadges)): ?>
                                                <div class="mt-1">
                                                    <?php foreach ($qBadges as $badge): ?>
                                                    <span class="badge-icon" title="<?php echo htmlspecialchars($badge['TENHUYHIEU']); ?>" style="cursor: help;"><?php echo $badge['BIEUTUONG']; ?></span>
                                                    <?php endforeach; ?>
                                                </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Answers -->
                <h4 class="mb-4"><?php echo count($answers); ?> Câu trả lời</h4>

                <?php foreach ($answers as $answer): 
                    $answerRatingStmt = $conn->prepare("SELECT AVG(v.LOAIVOTE) as avgRating, COUNT(*) as totalRatings FROM VOTE v JOIN BINHCHONCAUTRALOI bc ON v.MAVOTE = bc.MAVOTE WHERE bc.MACAUTRALOI = ?");
                    $answerRatingStmt->execute([$answer['MACAUTRALOI']]);
                    $aRating = $answerRatingStmt->fetch(PDO::FETCH_ASSOC);
                    $avgAnswerRating = round($aRating['avgRating'] ?? 0, 1);
                    $totalAnswerRatings = $aRating['totalRatings'] ?? 0;
                    
                    $userAnswerRating = 0;
                    if ($currentUser) {
                        $userARatingStmt = $conn->prepare("SELECT v.LOAIVOTE FROM VOTE v JOIN BINHCHONCAUTRALOI bc ON v.MAVOTE = bc.MAVOTE WHERE v.MANGUOIDUNG = ? AND bc.MACAUTRALOI = ?");
                        $userARatingStmt->execute([$currentUser['id'], $answer['MACAUTRALOI']]);
                        $userAnswerRating = $userARatingStmt->fetchColumn() ?: 0;
                    }
                    
                    $canRateAnswer = $currentUser && $currentUser['id'] !== $answer['MANGUOIDUNG'];
                    $answerReplies = $replies[$answer['MACAUTRALOI']] ?? [];
                ?>
                <?php 
                $answerClasses = 'answer-item';
                if (isset($answer['ANSWER_STATUS']) && $answer['ANSWER_STATUS'] === 'pending') {
                    $answerClasses .= ' border-warning';
                }
                if (isset($answer['DUOCCHAPNHAN']) && $answer['DUOCCHAPNHAN'] == 1) {
                    $answerClasses .= ' accepted position-relative';
                }
                ?>
                <div class="<?php echo $answerClasses; ?>" id="answer-<?php echo $answer['MACAUTRALOI']; ?>">
                    <div class="card-body">
                        <?php if (isset($answer['ANSWER_STATUS']) && $answer['ANSWER_STATUS'] === 'pending'): ?>
                        <div class="alert alert-warning py-2 mb-3">
                            <i class="bi bi-hourglass-split me-1"></i>
                            <small>Câu trả lời đang chờ duyệt<?php echo ($currentUser && $currentUser['id'] === $answer['MANGUOIDUNG']) ? ' - Chỉ bạn có thể thấy' : ''; ?></small>
                        </div>
                        <?php endif; ?>
                        <div class="row g-3">
                            <div class="col-auto">
                                <div class="rating-box" style="min-width: 90px;">
                                    <div class="avg-rating-display">
                                        <span class="avg-rating" style="font-size: var(--font-xl);"><?php echo $avgAnswerRating; ?></span>
                                        <i class="bi bi-star-fill text-warning" style="font-size: var(--font-base);"></i>
                                    </div>
                                    <small class="rating-count d-block mb-2">(<?php echo $totalAnswerRatings; ?>)</small>
                                    <?php echo renderStars($avgAnswerRating, $userAnswerRating, 'answer', $answer['MACAUTRALOI'], !$canRateAnswer); ?>
                                </div>
                            </div>
                            
                            <div class="col">
                                <div class="answer-content mb-3">
                                    <?php echo renderFullContent($answer['NOIDUNGTL'], $answer['HINHANH'] ?? ''); ?>
                                </div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="d-flex align-items-center gap-2">
                                        <small class="text-muted">
                                            <?php echo date('d/m/Y H:i', strtotime($answer['NGAYTL'])); ?>
                                        </small>
                                        <?php if ($currentUser && $question['TRANGTHAI'] === 'open'): ?>
                                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="showReplyForm('<?php echo $answer['MACAUTRALOI']; ?>')" title="Trả lời">
                                            <i class="bi bi-reply"></i> Trả lời
                                        </button>
                                        <?php endif; ?>
                                        <?php 
                                        // Nút chấp nhận câu trả lời - chỉ hiện cho chủ câu hỏi
                                        $isQuestionOwner = $currentUser && $currentUser['id'] === $question['MANGUOIDUNG'];
                                        $isAccepted = isset($answer['DUOCCHAPNHAN']) && $answer['DUOCCHAPNHAN'] == 1;
                                        $canAccept = $isQuestionOwner && $currentUser['id'] !== $answer['MANGUOIDUNG'];
                                        ?>
                                        <?php if ($isAccepted): ?>
                                        <span class="btn btn-sm btn-success" title="Câu trả lời được chấp nhận">
                                            <i class="bi bi-check-circle-fill"></i> Đã chấp nhận
                                        </span>
                                        <?php elseif ($canAccept): ?>
                                        <button type="button" class="btn btn-sm btn-outline-success" onclick="acceptAnswer('<?php echo $answer['MACAUTRALOI']; ?>', '<?php echo $questionId; ?>')" title="Chấp nhận câu trả lời này">
                                            <i class="bi bi-check-circle"></i> Chấp nhận
                                        </button>
                                        <?php endif; ?>
                                        <?php if ($currentUser && ($currentUser['id'] === $answer['MANGUOIDUNG'] || $currentUser['role'] === 'admin')): ?>
                                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteAnswer('<?php echo $answer['MACAUTRALOI']; ?>')" title="Xóa câu trả lời">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                        <?php endif; ?>
                                        <?php if ($currentUser && $currentUser['id'] !== $answer['MANGUOIDUNG']): ?>
                                        <button type="button" class="btn btn-sm btn-outline-warning" onclick="reportAnswer('<?php echo $answer['MACAUTRALOI']; ?>')" title="Báo cáo">
                                            <i class="bi bi-flag"></i>
                                        </button>
                                        <?php endif; ?>
                                    </div>
                                    <?php
                                    // Lấy huy hiệu của người trả lời
                                    $answerUserBadges = $conn->prepare("SELECT h.BIEUTUONG, h.TENHUYHIEU FROM NHAN n JOIN HUYHIEU h ON n.MAHUYHIEU = h.MAHUYHIEU WHERE n.MANGUOIDUNG = ? LIMIT 5");
                                    $answerUserBadges->execute([$answer['MANGUOIDUNG']]);
                                    $aBadges = $answerUserBadges->fetchAll(PDO::FETCH_ASSOC);
                                    ?>
                                    <div class="d-flex align-items-center gap-2">
                                        <?php echo renderAvatarWithFrame($answer['ANHDAIDIEN'], $answer['MANGUOIDUNG'], 'sm', false); ?>
                                        <div>
                                            <a href="profile.php?id=<?php echo $answer['MANGUOIDUNG']; ?>" class="fw-semibold text-decoration-none" style="font-size: var(--font-sm);">
                                                <?php echo htmlspecialchars($answer['HOTEN']); ?>
                                            </a>
                                            <div class="text-muted" style="font-size: var(--font-xs);">
                                                <?php echo number_format($answer['DIEMDANHGIA']); ?> điểm
                                                <?php if (!empty($aBadges)): ?>
                                                <span class="ms-1">
                                                    <?php foreach ($aBadges as $badge): ?>
                                                    <span title="<?php echo htmlspecialchars($badge['TENHUYHIEU']); ?>" style="cursor: help;"><?php echo $badge['BIEUTUONG']; ?></span>
                                                    <?php endforeach; ?>
                                                </span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Reply Form (ẩn mặc định) -->
                                <div class="reply-form mt-3" id="reply-form-<?php echo $answer['MACAUTRALOI']; ?>" style="display: none;">
                                    <form method="POST" class="d-flex gap-2">
                                        <input type="hidden" name="parent_answer_id" value="<?php echo $answer['MACAUTRALOI']; ?>">
                                        <textarea class="form-control form-control-sm" name="answer_content" rows="2" placeholder="Viết trả lời..." required></textarea>
                                        <div class="d-flex flex-column gap-1">
                                            <button type="submit" class="btn btn-sm btn-primary"><i class="bi bi-send"></i></button>
                                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="hideReplyForm('<?php echo $answer['MACAUTRALOI']; ?>')"><i class="bi bi-x"></i></button>
                                        </div>
                                    </form>
                                </div>
                                
                                <!-- Nested Replies -->
                                <?php if (!empty($answerReplies)): ?>
                                <div class="nested-replies mt-3">
                                    <?php foreach ($answerReplies as $reply): ?>
                                    <div class="reply-item" id="answer-<?php echo $reply['MACAUTRALOI']; ?>">
                                        <div class="d-flex gap-2">
                                            <img src="<?php echo htmlspecialchars($reply['ANHDAIDIEN']); ?>" alt="" class="rounded-circle" width="32" height="32">
                                            <div class="flex-grow-1">
                                                <div class="d-flex align-items-center gap-2 mb-1">
                                                    <a href="profile.php?id=<?php echo $reply['MANGUOIDUNG']; ?>" class="fw-semibold text-decoration-none" style="font-size: var(--font-sm);">
                                                        <?php echo htmlspecialchars($reply['HOTEN']); ?>
                                                    </a>
                                                    <small class="text-muted"><?php echo date('d/m/Y H:i', strtotime($reply['NGAYTL'])); ?></small>
                                                    <?php if ($currentUser && $question['TRANGTHAI'] === 'open'): ?>
                                                    <button type="button" class="btn btn-sm btn-link text-primary p-0" onclick="showReplyForm('<?php echo $reply['MACAUTRALOI']; ?>')" title="Trả lời">
                                                        <i class="bi bi-reply"></i>
                                                    </button>
                                                    <?php endif; ?>
                                                    <?php if ($currentUser && ($currentUser['id'] === $reply['MANGUOIDUNG'] || $currentUser['role'] === 'admin')): ?>
                                                    <button type="button" class="btn btn-sm btn-link text-danger p-0" onclick="deleteAnswer('<?php echo $reply['MACAUTRALOI']; ?>')" title="Xóa">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                    <?php endif; ?>
                                                </div>
                                                <?php if (!empty($reply['REPLY_TO_CONTENT'])): ?>
                                                <div class="reply-quote">
                                                    <div class="reply-quote-header">
                                                        <i class="bi bi-reply-fill"></i>
                                                        Trả lời <strong><?php echo htmlspecialchars($reply['REPLY_TO_NAME']); ?></strong>
                                                    </div>
                                                    <div class="reply-quote-content">
                                                        <?php echo htmlspecialchars(mb_substr($reply['REPLY_TO_CONTENT'], 0, 100)); ?><?php echo mb_strlen($reply['REPLY_TO_CONTENT']) > 100 ? '...' : ''; ?>
                                                    </div>
                                                </div>
                                                <?php endif; ?>
                                                <div class="reply-content" style="font-size: var(--font-sm);">
                                                    <?php echo nl2br(htmlspecialchars($reply['NOIDUNGTL'])); ?>
                                                </div>
                                                
                                                <!-- Reply Form cho reply con - lưu ID của reply được chọn -->
                                                <div class="reply-form mt-2" id="reply-form-<?php echo $reply['MACAUTRALOI']; ?>" style="display: none;">
                                                    <form method="POST" class="d-flex gap-2">
                                                        <input type="hidden" name="parent_answer_id" value="<?php echo $reply['MACAUTRALOI']; ?>">
                                                        <textarea class="form-control form-control-sm" name="answer_content" rows="1" placeholder="Trả lời @<?php echo htmlspecialchars($reply['HOTEN']); ?>..." required></textarea>
                                                        <button type="submit" class="btn btn-sm btn-primary"><i class="bi bi-send"></i></button>
                                                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="hideReplyForm('<?php echo $reply['MACAUTRALOI']; ?>')"><i class="bi bi-x"></i></button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>

                <!-- Answer Form -->
                <?php if ($question['TRANGTHAI'] === 'closed'): ?>
                <div class="alert alert-secondary mt-4">
                    <i class="bi bi-lock me-2"></i>
                    Câu hỏi này đã được đóng và không nhận thêm câu trả lời.
                </div>
                <?php elseif ($currentUser): ?>
                <div class="card mt-4" id="answerForm">
                    <div class="card-body">
                        <h5 class="mb-3">Câu trả lời của bạn</h5>
                        <form method="POST" id="mainAnswerForm">
                            <div class="mb-3">
                                <div class="editor-toolbar">
                                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="insertCodeBlockAnswer('answer_content')" title="Chèn code">
                                        <i class="bi bi-code-slash"></i> Code
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="document.getElementById('answerImageUpload').click()" title="Đính kèm ảnh">
                                        <i class="bi bi-image"></i> Ảnh
                                    </button>
                                </div>
                                <textarea class="form-control" name="answer_content" id="answer_content" rows="6" placeholder="Nhập câu trả lời của bạn...

Sử dụng ``` để chèn code:
```php
// code của bạn
```" required></textarea>
                            </div>
                            <input type="file" id="answerImageUpload" accept="image/*" multiple style="display: none;" onchange="uploadAnswerImages(this.files)">
                            <input type="hidden" name="answer_images" id="answerImagesInput" value="">
                            <div id="answerImagePreview" class="image-preview-container mb-3"></div>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-send me-2"></i>Gửi câu trả lời
                            </button>
                        </form>
                    </div>
                </div>
                <?php else: ?>
                <div class="alert alert-info mt-4">
                    <i class="bi bi-info-circle"></i>
                    Bạn cần <a href="login.php" class="alert-link">đăng nhập</a> để trả lời câu hỏi này.
                </div>
                <?php endif; ?>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <div class="sidebar-widget">
                    <h6><i class="bi bi-link-45deg"></i>Câu hỏi liên quan</h6>
                    <?php
                    $relatedQuery = "SELECT MACAUHOI, TIEUDE FROM CAUHOI WHERE MATHE = ? AND MACAUHOI != ? AND TRANGTHAI IN ('open', 'closed') ORDER BY LUOTXEM DESC LIMIT 5";
                    $stmt = $conn->prepare($relatedQuery);
                    $stmt->execute([$question['MATHE'], $questionId]);
                    $relatedQuestions = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    ?>
                    <?php if (empty($relatedQuestions)): ?>
                    <p class="text-muted" style="font-size: var(--font-sm);">Không có câu hỏi liên quan</p>
                    <?php else: ?>
                    <ul class="list-unstyled mb-0">
                        <?php foreach ($relatedQuestions as $related): ?>
                        <li class="mb-2">
                            <a href="question-detail.php?id=<?php echo $related['MACAUHOI']; ?>" class="text-decoration-none" style="font-size: var(--font-sm);">
                                <i class="bi bi-chevron-right me-1" style="color: var(--primary-500);"></i>
                                <?php echo htmlspecialchars($related['TIEUDE']); ?>
                            </a>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</main>

<script>
function rate(type, id, rating) {
    fetch('api/vote.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ type, id, rating })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            const container = document.querySelector(`[data-rating-id="${id}"]`);
            if (container) {
                const avgDisplay = container.closest('.rating-box').querySelector('.avg-rating');
                const countDisplay = container.closest('.rating-box').querySelector('.rating-count');
                if (avgDisplay) avgDisplay.textContent = data.avgRating;
                if (countDisplay) countDisplay.textContent = `(${data.totalRatings} đánh giá)`;
                
                container.querySelectorAll('.star-btn').forEach((star, i) => {
                    star.classList.remove('active', 'user-rated');
                    if (i + 1 <= Math.round(data.avgRating)) star.classList.add('active');
                    if (i + 1 <= data.userRating) star.classList.add('user-rated');
                });
            }
            showToast(data.message, 'success');
        } else {
            showToast(data.message, 'danger');
        }
    })
    .catch(() => showToast('Có lỗi xảy ra', 'danger'));
}

function showToast(message, type) {
    let container = document.getElementById('toastContainer');
    if (!container) {
        container = document.createElement('div');
        container.id = 'toastContainer';
        container.style.cssText = 'position:fixed;top:20px;right:20px;z-index:9999;';
        document.body.appendChild(container);
    }
    
    const toast = document.createElement('div');
    toast.className = `alert alert-${type} alert-dismissible fade show`;
    toast.style.cssText = 'min-width:250px;box-shadow:var(--shadow-lg);';
    toast.innerHTML = `<i class="bi bi-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i> ${message}
        <button type="button" class="btn-close" onclick="this.parentElement.remove()"></button>`;
    container.appendChild(toast);
    setTimeout(() => toast.remove(), 3000);
}

// Star hover effect
document.querySelectorAll('.star-rating').forEach(container => {
    const stars = Array.from(container.querySelectorAll('.star-btn'));
    stars.forEach((star, index) => {
        star.onmouseenter = () => stars.forEach((s, i) => s.classList.toggle('hovered', i <= index));
    });
    container.onmouseleave = () => stars.forEach(s => s.classList.remove('hovered'));
});

// Share question
function shareQuestion() {
    const url = window.location.href;
    if (navigator.share) {
        navigator.share({
            title: '<?php echo addslashes($question['TIEUDE']); ?>',
            url: url
        });
    } else if (navigator.clipboard) {
        navigator.clipboard.writeText(url).then(() => {
            showToast('Đã sao chép link!', 'success');
        });
    }
}

// Report question
function submitReport() {
    const reason = document.getElementById('reportReason').value;
    if (!reason.trim()) {
        showToast('Vui lòng nhập lý do báo cáo', 'warning');
        return;
    }
    
    fetch('api/report.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            action: 'create',
            type: 'question',
            target_id: '<?php echo $questionId; ?>',
            reason: reason
        })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            showToast(data.message, 'success');
            bootstrap.Modal.getInstance(document.getElementById('reportModal')).hide();
            document.getElementById('reportReason').value = '';
        } else {
            showToast(data.message, 'danger');
        }
    })
    .catch(() => showToast('Có lỗi xảy ra', 'danger'));
}

// Accept answer
function acceptAnswer(answerId, questionId) {
    if (!confirm('Chấp nhận câu trả lời này? Người trả lời sẽ nhận +25 điểm.')) return;
    
    fetch('api/accept-answer.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ answer_id: answerId, question_id: questionId })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            showToast(data.message, 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            showToast(data.message, 'danger');
        }
    })
    .catch(() => showToast('Có lỗi xảy ra', 'danger'));
}

// Delete answer
function deleteAnswer(answerId) {
    if (!confirm('Bạn có chắc muốn xóa câu trả lời này?')) return;
    
    fetch('api/delete-answer.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ answer_id: answerId })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            showToast(data.message, 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            showToast(data.message, 'danger');
        }
    })
    .catch(() => showToast('Có lỗi xảy ra', 'danger'));
}

// Report answer
function reportAnswer(answerId) {
    const reason = prompt('Nhập lý do báo cáo câu trả lời này:');
    if (!reason || !reason.trim()) return;
    
    fetch('api/report.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            action: 'create',
            type: 'answer',
            target_id: answerId,
            reason: reason
        })
    })
    .then(r => r.json())
    .then(data => {
        showToast(data.message, data.success ? 'success' : 'danger');
    })
    .catch(() => showToast('Có lỗi xảy ra', 'danger'));
}

// Show/Hide reply form
function showReplyForm(answerId) {
    document.getElementById('reply-form-' + answerId).style.display = 'block';
    document.querySelector('#reply-form-' + answerId + ' textarea').focus();
}

function hideReplyForm(answerId) {
    document.getElementById('reply-form-' + answerId).style.display = 'none';
}

// === CODE & IMAGE UPLOAD FUNCTIONS ===

// Chèn code block vào textarea
function insertCodeBlockAnswer(textareaId) {
    const textarea = document.getElementById(textareaId);
    if (!textarea) return;
    
    const start = textarea.selectionStart;
    const end = textarea.selectionEnd;
    const selectedText = textarea.value.substring(start, end);
    
    const codeBlock = selectedText 
        ? "```\n" + selectedText + "\n```"
        : "```php\n// Code của bạn ở đây\n```";
    
    textarea.value = textarea.value.substring(0, start) + codeBlock + textarea.value.substring(end);
    textarea.focus();
    
    const newPos = start + (selectedText ? codeBlock.length : 7);
    textarea.setSelectionRange(newPos, newPos);
}

// Mảng lưu URLs ảnh đã upload cho câu trả lời
let answerUploadedImages = [];

// Upload ảnh cho câu trả lời
async function uploadAnswerImages(files) {
    for (let file of files) {
        if (!file.type.startsWith('image/')) {
            showToast('Chỉ chấp nhận file ảnh!', 'warning');
            continue;
        }
        
        if (file.size > 5 * 1024 * 1024) {
            showToast('File ' + file.name + ' quá lớn (tối đa 5MB)', 'warning');
            continue;
        }
        
        const formData = new FormData();
        formData.append('image', file);
        
        try {
            const response = await fetch('api/upload-image.php', {
                method: 'POST',
                body: formData
            });
            
            const result = await response.json();
            
            if (result.success) {
                answerUploadedImages.push(result.url);
                updateAnswerImagePreview();
                document.getElementById('answerImagesInput').value = JSON.stringify(answerUploadedImages);
                showToast('Upload ảnh thành công!', 'success');
            } else {
                showToast('Lỗi upload: ' + result.message, 'danger');
            }
        } catch (error) {
            showToast('Lỗi kết nối server', 'danger');
            console.error(error);
        }
    }
}

// Cập nhật preview ảnh cho câu trả lời
function updateAnswerImagePreview() {
    const container = document.getElementById('answerImagePreview');
    if (!container) return;
    
    container.innerHTML = answerUploadedImages.map((url, index) => `
        <div class="image-preview-item">
            <img src="${url}" alt="Preview">
            <button type="button" class="remove-image" onclick="removeAnswerImage(${index})" title="Xóa ảnh">
                <i class="bi bi-x"></i>
            </button>
        </div>
    `).join('');
}

// Xóa ảnh từ câu trả lời
function removeAnswerImage(index) {
    answerUploadedImages.splice(index, 1);
    updateAnswerImagePreview();
    document.getElementById('answerImagesInput').value = JSON.stringify(answerUploadedImages);
}

// Copy code block
function copyCode(blockId) {
    const codeBlock = document.getElementById(blockId);
    if (!codeBlock) return;
    
    const code = codeBlock.querySelector('code').textContent;
    navigator.clipboard.writeText(code).then(() => {
        const btn = codeBlock.querySelector('.code-block-copy');
        btn.innerHTML = '<i class="bi bi-check"></i> Copied!';
        btn.classList.add('copied');
        setTimeout(() => {
            btn.innerHTML = '<i class="bi bi-clipboard"></i> Copy';
            btn.classList.remove('copied');
        }, 2000);
    });
}

// Image Lightbox với Gallery Navigation
let currentGallery = [];
let currentImageIndex = 0;

function openLightbox(imageUrl, gallery = null, index = 0) {
    currentGallery = gallery || [imageUrl];
    currentImageIndex = index;

    let lightbox = document.getElementById('imageLightbox');
    if (!lightbox) {
        lightbox = document.createElement('div');
        lightbox.id = 'imageLightbox';
        lightbox.className = 'image-lightbox';
        lightbox.innerHTML = `
            <button class="close-lightbox" onclick="closeLightbox()"><i class="bi bi-x-lg"></i></button>
            <button class="lightbox-nav lightbox-prev" onclick="prevImage(event)"><i class="bi bi-chevron-left"></i></button>
            <img src="" alt="Full size image">
            <button class="lightbox-nav lightbox-next" onclick="nextImage(event)"><i class="bi bi-chevron-right"></i></button>
            <div class="lightbox-counter"></div>
        `;
        lightbox.onclick = function(e) {
            if (e.target === lightbox) closeLightbox();
        };
        document.body.appendChild(lightbox);
    }

    updateLightboxImage();
    lightbox.classList.add('active');
    document.body.style.overflow = 'hidden';
}

function updateLightboxImage() {
    const lightbox = document.getElementById('imageLightbox');
    if (!lightbox) return;

    lightbox.querySelector('img').src = currentGallery[currentImageIndex];

    // Cập nhật counter
    const counter = lightbox.querySelector('.lightbox-counter');
    if (currentGallery.length > 1) {
        counter.textContent = `${currentImageIndex + 1} / ${currentGallery.length}`;
        counter.style.display = 'block';
        lightbox.querySelector('.lightbox-prev').style.display = 'flex';
        lightbox.querySelector('.lightbox-next').style.display = 'flex';
    } else {
        counter.style.display = 'none';
        lightbox.querySelector('.lightbox-prev').style.display = 'none';
        lightbox.querySelector('.lightbox-next').style.display = 'none';
    }
}

function prevImage(e) {
    e.stopPropagation();
    currentImageIndex = (currentImageIndex - 1 + currentGallery.length) % currentGallery.length;
    updateLightboxImage();
}

function nextImage(e) {
    e.stopPropagation();
    currentImageIndex = (currentImageIndex + 1) % currentGallery.length;
    updateLightboxImage();
}

function closeLightbox() {
    const lightbox = document.getElementById('imageLightbox');
    if (lightbox) {
        lightbox.classList.remove('active');
        document.body.style.overflow = '';
    }
}

// Keyboard navigation
document.addEventListener('keydown', function(e) {
    const lightbox = document.getElementById('imageLightbox');
    if (!lightbox || !lightbox.classList.contains('active')) return;

    if (e.key === 'Escape') closeLightbox();
    if (e.key === 'ArrowLeft') prevImage(e);
    if (e.key === 'ArrowRight') nextImage(e);
});

// Close question
function closeQuestion() {
    if (!confirm('Bạn có chắc muốn đóng câu hỏi này? Sau khi đóng, không ai có thể trả lời thêm.')) return;
    
    fetch('api/question.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            action: 'close',
            question_id: '<?php echo $questionId; ?>'
        })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            showToast(data.message, 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            showToast(data.message, 'danger');
        }
    })
    .catch(() => showToast('Có lỗi xảy ra', 'danger'));
}
</script>

<!-- Report Modal -->
<?php if ($currentUser && $currentUser['id'] !== $question['MANGUOIDUNG']): ?>
<div class="modal fade" id="reportModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-flag me-2"></i>Báo cáo câu hỏi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Lý do báo cáo</label>
                    <select class="form-select mb-3" onchange="document.getElementById('reportReason').value = this.value">
                        <option value="">-- Chọn lý do --</option>
                        <option value="Spam hoặc quảng cáo">Spam hoặc quảng cáo</option>
                        <option value="Nội dung không phù hợp">Nội dung không phù hợp</option>
                        <option value="Ngôn ngữ xúc phạm">Ngôn ngữ xúc phạm</option>
                        <option value="Câu hỏi trùng lặp">Câu hỏi trùng lặp</option>
                        <option value="Khác">Khác</option>
                    </select>
                    <textarea class="form-control" id="reportReason" rows="3" placeholder="Mô tả chi tiết lý do báo cáo..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                <button type="button" class="btn btn-warning" onclick="submitReport()">
                    <i class="bi bi-flag me-2"></i>Gửi báo cáo
                </button>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>
