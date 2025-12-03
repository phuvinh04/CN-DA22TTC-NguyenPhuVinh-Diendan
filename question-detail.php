<?php
require_once 'config/database.php';
require_once 'config/session.php';

$questionId = $_GET['id'] ?? '';

if (empty($questionId)) {
    header('Location: questions.php');
    exit();
}

// Lấy thông tin câu hỏi
$questionQuery = "SELECT 
    ch.MACAUHOI,
    ch.TIEUDE,
    ch.LUOTXEM,
    ch.TRANGTHAI,
    d.NOIDUNG,
    d.NGAYDANG,
    nd.MANGUOIDUNG,
    nd.HOTEN AS NguoiDat,
    nd.ANHDAIDIEN,
    nd.DIEMDANHGIA,
    t.TENTHE AS Tag,
    t.MATHE
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

// Rating tạm thời bỏ vì chưa có bảng BINHCHONCAUHOI
$userQuestionRating = 0;

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
$hasViewed = $stmt->fetchColumn() > 0;

if (!$hasViewed) {
    $conn->prepare("UPDATE CAUHOI SET LUOTXEM = LUOTXEM + 1 WHERE MACAUHOI = ?")->execute([$questionId]);
    $conn->prepare("INSERT INTO LUOTXEM (MACAUHOI, MANGUOIDUNG, IP_ADDRESS, NGAYXEM) VALUES (?, ?, ?, CURDATE())")
        ->execute([$questionId, $userId, $ipAddress]);
}

// Lấy danh sách câu trả lời
$answersQuery = "SELECT 
    tl.MACAUTRALOI,
    tl.NOIDUNGTL,
    tl.NGAYTL,
    nd.MANGUOIDUNG,
    nd.HOTEN,
    nd.ANHDAIDIEN,
    nd.DIEMDANHGIA
FROM TRALOI tl
INNER JOIN NGUOIDUNG nd ON tl.MANGUOIDUNG = nd.MANGUOIDUNG
WHERE tl.MACAUHOI = ?
ORDER BY tl.NGAYTL ASC";

$stmt = $conn->prepare($answersQuery);
$stmt->execute([$questionId]);
$answers = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Rating tạm thời bỏ
$userAnswerRatings = [];

// Xử lý submit câu trả lời
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $currentUser) {
    $answerContent = trim($_POST['answer_content'] ?? '');
    
    if (!empty($answerContent)) {
        $answerId = 'TL' . time();
        $insertQuery = "INSERT INTO TRALOI (MACAUTRALOI, MANGUOIDUNG, MACAUHOI, NOIDUNGTL, NGAYTL) VALUES (?, ?, ?, ?, NOW())";
        $stmt = $conn->prepare($insertQuery);
        
        if ($stmt->execute([$answerId, $currentUser['id'], $questionId, $answerContent])) {
            $conn->prepare("UPDATE NGUOIDUNG SET DIEMDANHGIA = DIEMDANHGIA + 10 WHERE MANGUOIDUNG = ?")
                ->execute([$currentUser['id']]);
            
            header('Location: question-detail.php?id=' . $questionId);
            exit();
        }
    }
}

// Helper function để render stars
function renderStars($avgRating, $userRating = 0, $type = '', $id = '', $disabled = false) {
    $avgRating = round($avgRating, 1);
    $html = '<div class="star-rating" data-rating-id="' . $id . '">';
    
    for ($i = 1; $i <= 5; $i++) {
        $activeClass = $i <= round($avgRating) ? 'active' : '';
        $userClass = $userRating && $i <= $userRating ? 'user-rated' : '';
        $disabledAttr = $disabled ? 'disabled' : '';
        $onclick = $disabled ? '' : "onclick=\"rate('$type', '$id', $i)\"";
        
        $html .= "<button type=\"button\" class=\"star-btn $activeClass $userClass\" $onclick $disabledAttr title=\"$i sao\">";
        $html .= '<i class="bi bi-star-fill"></i>';
        $html .= '</button>';
    }
    
    $html .= '</div>';
    return $html;
}

require_once 'includes/header.php';
?>

<style>
/* Star Rating Styles - Inline để đảm bảo hoạt động */
.star-rating {
    display: flex;
    gap: 2px;
    justify-content: center;
    padding: 8px;
    background: #fff;
    border-radius: 10px;
}

.star-btn {
    background: none;
    border: none;
    padding: 4px;
    cursor: pointer;
    font-size: 1.4rem;
    color: #ccc !important;
    transition: all 0.2s ease;
    outline: none;
}

.star-btn:hover {
    transform: scale(1.3);
    color: #ffc107 !important;
}

.star-btn.hovered {
    color: #ffc107 !important;
    transform: scale(1.2);
}

.star-btn.active {
    color: #ffc107 !important;
}

.star-btn.user-rated {
    color: #ff9800 !important;
    text-shadow: 0 0 10px rgba(255, 152, 0, 0.5);
}
</style>

<main class="py-4">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php">Trang chủ</a></li>
                <li class="breadcrumb-item"><a href="questions.php">Câu hỏi</a></li>
                <li class="breadcrumb-item active"><?php echo htmlspecialchars($question['TIEUDE']); ?></li>
            </ol>
        </nav>

        <div class="row">
            <div class="col-lg-9">
                <!-- Question -->
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="mb-2">
                            <a href="questions.php?tag=<?php echo $question['MATHE']; ?>" class="tag">
                                <?php echo htmlspecialchars($question['Tag']); ?>
                            </a>
                        </div>
                        
                        <h2 class="mb-3"><?php echo htmlspecialchars($question['TIEUDE']); ?></h2>
                        
                        <div class="d-flex align-items-center mb-3 text-muted small">
                            <span class="me-3"><i class="bi bi-clock me-1"></i>Đăng <?php echo date('d/m/Y H:i', strtotime($question['NGAYDANG'])); ?></span>
                            <span class="me-3"><i class="bi bi-eye me-1"></i><?php echo number_format($question['LUOTXEM']); ?> lượt xem</span>
                            <span><i class="bi bi-chat-dots me-1"></i><?php echo count($answers); ?> trả lời</span>
                        </div>

                        <hr>

                        <div class="row">
                            <div class="col">
                                <div class="question-content mb-4">
                                    <?php echo nl2br(htmlspecialchars($question['NOIDUNG'])); ?>
                                </div>

                                <div class="d-flex justify-content-end align-items-center">
                                    <div class="card bg-light">
                                        <div class="card-body p-3">
                                            <small class="text-muted d-block mb-2">Đăng bởi</small>
                                            <div class="d-flex align-items-center">
                                                <img src="<?php echo htmlspecialchars($question['ANHDAIDIEN']); ?>" alt="Avatar" class="user-avatar me-2">
                                                <div>
                                                    <a href="profile.php?id=<?php echo $question['MANGUOIDUNG']; ?>" class="text-decoration-none fw-semibold">
                                                        <?php echo htmlspecialchars($question['NguoiDat']); ?>
                                                    </a>
                                                    <div class="small text-muted"><?php echo $question['DIEMDANHGIA']; ?> điểm</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Answers -->
                <h4 class="mb-3"><?php echo count($answers); ?> Câu trả lời</h4>

                <?php foreach ($answers as $answer): ?>
                <div class="card answer-item mb-3">
                    <div class="card-body">
                        <div class="row">
                            <div class="col">
                                <div class="answer-content mb-3">
                                    <?php echo nl2br(htmlspecialchars($answer['NOIDUNGTL'])); ?>
                                </div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <small class="text-muted">
                                        Trả lời <?php echo date('d/m/Y H:i', strtotime($answer['NGAYTL'])); ?>
                                    </small>
                                    <div class="d-flex align-items-center">
                                        <img src="<?php echo htmlspecialchars($answer['ANHDAIDIEN']); ?>" alt="Avatar" class="user-avatar-sm me-2">
                                        <div>
                                            <a href="profile.php?id=<?php echo $answer['MANGUOIDUNG']; ?>" class="text-decoration-none fw-semibold small">
                                                <?php echo htmlspecialchars($answer['HOTEN']); ?>
                                            </a>
                                            <div class="small text-muted"><?php echo $answer['DIEMDANHGIA']; ?> điểm</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>

                <!-- Answer Form -->
                <?php if ($currentUser): ?>
                <div class="card mt-4">
                    <div class="card-body">
                        <h5 class="mb-3">Câu trả lời của bạn</h5>
                        <form method="POST" action="">
                            <div class="mb-3">
                                <textarea class="form-control" name="answer_content" rows="6" placeholder="Nhập câu trả lời của bạn..." required></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-send me-2"></i>Gửi câu trả lời
                            </button>
                        </form>
                    </div>
                </div>
                <?php else: ?>
                <div class="alert alert-info mt-4">
                    <i class="bi bi-info-circle me-2"></i>
                    Bạn cần <a href="login.php" class="alert-link">đăng nhập</a> để trả lời câu hỏi này.
                </div>
                <?php endif; ?>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-3">
                <div class="sidebar-widget">
                    <h6 class="mb-3">Câu hỏi liên quan</h6>
                    <?php
                    $relatedQuery = "SELECT MACAUHOI, TIEUDE FROM CAUHOI WHERE MATHE = ? AND MACAUHOI != ? ORDER BY LUOTXEM DESC LIMIT 5";
                    $stmt = $conn->prepare($relatedQuery);
                    $stmt->execute([$question['MATHE'], $questionId]);
                    $relatedQuestions = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    ?>
                    <ul class="list-unstyled">
                        <?php foreach ($relatedQuestions as $related): ?>
                        <li class="mb-2">
                            <a href="question-detail.php?id=<?php echo $related['MACAUHOI']; ?>" class="text-decoration-none">
                                <i class="bi bi-chevron-right me-1"></i><?php echo htmlspecialchars($related['TIEUDE']); ?>
                            </a>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</main>

<script>
// Star Rating Function
function rate(type, id, rating) {
    console.log('Rating:', type, id, rating);
    
    fetch('api/vote.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            type: type,
            id: id,
            rating: rating
        })
    })
    .then(response => response.json())
    .then(data => {
        console.log('Response:', data);
        if (data.success) {
            const ratingContainer = document.querySelector(`[data-rating-id="${id}"]`);
            if (ratingContainer) {
                const avgDisplay = ratingContainer.querySelector('.avg-rating');
                if (avgDisplay) {
                    avgDisplay.textContent = data.avgRating;
                }
                
                const countDisplay = ratingContainer.querySelector('.rating-count');
                if (countDisplay) {
                    countDisplay.textContent = `(${data.totalRatings} đánh giá)`;
                }
                
                const stars = ratingContainer.querySelectorAll('.star-btn');
                stars.forEach((star, index) => {
                    star.classList.remove('active', 'user-rated');
                    if (index + 1 <= Math.round(data.avgRating)) {
                        star.classList.add('active');
                    }
                    if (index + 1 <= data.userRating) {
                        star.classList.add('user-rated');
                    }
                });
            }
            
            // Show toast instead of alert
            showRatingToast(data.message, 'success');
        } else {
            showRatingToast(data.message, 'danger');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showRatingToast('Có lỗi xảy ra', 'danger');
    });
}

// Toast notification
function showRatingToast(message, type) {
    let container = document.getElementById('ratingToast');
    if (!container) {
        container = document.createElement('div');
        container.id = 'ratingToast';
        container.style.cssText = 'position:fixed;top:20px;right:20px;z-index:9999;';
        document.body.appendChild(container);
    }
    
    const toast = document.createElement('div');
    toast.className = `alert alert-${type} alert-dismissible fade show`;
    toast.style.cssText = 'min-width:250px;box-shadow:0 4px 12px rgba(0,0,0,0.15);';
    toast.innerHTML = `
        <i class="bi bi-${type === 'success' ? 'check-circle' : 'exclamation-circle'} me-2"></i>
        ${message}
        <button type="button" class="btn-close" onclick="this.parentElement.remove()"></button>
    `;
    container.appendChild(toast);
    
    setTimeout(() => toast.remove(), 3000);
}

// Hover effect for stars - highlight từ sao 1 đến sao đang hover
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.star-rating').forEach(container => {
        const stars = Array.from(container.querySelectorAll('.star-btn'));
        
        stars.forEach((star, index) => {
            // Khi di chuột vào sao
            star.onmouseenter = function() {
                // Tô màu tất cả sao từ 1 đến sao hiện tại
                stars.forEach((s, i) => {
                    if (i <= index) {
                        s.classList.add('hovered');
                    } else {
                        s.classList.remove('hovered');
                    }
                });
            };
        });
        
        // Khi di chuột ra khỏi container
        container.onmouseleave = function() {
            stars.forEach(s => s.classList.remove('hovered'));
        };
    });
    
    console.log('Star rating hover initialized!');
});
</script>

<?php require_once 'includes/footer.php'; ?>
