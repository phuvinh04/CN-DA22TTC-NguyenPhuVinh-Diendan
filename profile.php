<?php
$pageTitle = 'Hồ sơ';
require_once 'config/database.php';
require_once 'config/session.php';

// Cho phép xem profile người khác hoặc của mình
$profileId = $_GET['id'] ?? ($currentUser['id'] ?? null);

if (!$profileId) {
    header('Location: login.php');
    exit();
}

$isOwnProfile = $currentUser && $currentUser['id'] === $profileId;

// Lấy thông tin user
$stmt = $conn->prepare("SELECT * FROM NGUOIDUNG WHERE MANGUOIDUNG = ? AND TRANGTHAI = 'active'");
$stmt->execute([$profileId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    header('Location: index.php');
    exit();
}

$pageTitle = 'Hồ sơ - ' . $user['HOTEN'];
$userId = $profileId;

// Lấy thống kê
$statsQuery = "SELECT 
    (SELECT COUNT(*) FROM DAT WHERE MANGUOIDUNG = ?) AS SoCauHoi,
    (SELECT COUNT(*) FROM TRALOI WHERE MANGUOIDUNG = ?) AS SoCauTraLoi,
    (SELECT COUNT(*) FROM VOTE WHERE MANGUOIDUNG = ?) AS SoVote";
$stmt = $conn->prepare($statsQuery);
$stmt->execute([$userId, $userId, $userId]);
$stats = $stmt->fetch(PDO::FETCH_ASSOC);

// Lấy câu hỏi của user
$questionsQuery = "SELECT ch.MACAUHOI, ch.TIEUDE, d.NGAYDANG, ch.LUOTXEM,
    (SELECT COUNT(*) FROM TRALOI WHERE MACAUHOI = ch.MACAUHOI) AS SoCauTraLoi
FROM CAUHOI ch
INNER JOIN DAT d ON ch.MACAUHOI = d.MACAUHOI
WHERE d.MANGUOIDUNG = ?
ORDER BY d.NGAYDANG DESC
LIMIT 10";
$stmt = $conn->prepare($questionsQuery);
$stmt->execute([$userId]);
$questions = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Lấy câu trả lời của user
$answersQuery = "SELECT tl.*, ch.TIEUDE, ch.MACAUHOI
FROM TRALOI tl
JOIN CAUHOI ch ON tl.MACAUHOI = ch.MACAUHOI
WHERE tl.MANGUOIDUNG = ?
ORDER BY tl.NGAYTL DESC
LIMIT 10";
$stmt = $conn->prepare($answersQuery);
$stmt->execute([$userId]);
$answers = $stmt->fetchAll(PDO::FETCH_ASSOC);

require_once 'includes/header.php';
?>

<main class="py-4">
    <div class="container">
        <div class="row">
            <div class="col-lg-4 mb-4">
                <div class="card user-profile-card">
                    <div class="card-body text-center">
                        <img src="<?php echo htmlspecialchars($user['ANHDAIDIEN']); ?>" alt="Avatar" class="rounded-circle mb-3" style="width: 120px; height: 120px; border: 4px solid #667eea;">
                        <h4><?php echo htmlspecialchars($user['HOTEN']); ?></h4>
                        <p class="text-muted">@<?php echo htmlspecialchars($user['TENDANGNHAP']); ?></p>
                        <p class="mb-3"><?php echo htmlspecialchars($user['TIEUSU'] ?? 'Chưa có tiểu sử'); ?></p>
                        
                        <div class="d-flex justify-content-around mb-3">
                            <div>
                                <div class="fw-bold text-primary fs-4"><?php echo number_format($user['DIEMDANHGIA']); ?></div>
                                <small class="text-muted">Điểm</small>
                            </div>
                            <div>
                                <div class="fw-bold text-success fs-4"><?php echo $stats['SoCauHoi']; ?></div>
                                <small class="text-muted">Câu hỏi</small>
                            </div>
                            <div>
                                <div class="fw-bold text-info fs-4"><?php echo $stats['SoCauTraLoi']; ?></div>
                                <small class="text-muted">Trả lời</small>
                            </div>
                        </div>
                        
                        <hr>
                        
                        <div class="text-start">
                            <p class="mb-2"><i class="bi bi-envelope me-2 text-muted"></i><?php echo htmlspecialchars($user['EMAIL']); ?></p>
                            <p class="mb-2"><i class="bi bi-calendar me-2 text-muted"></i>Tham gia: <?php echo date('d/m/Y', strtotime($user['NGAYTAO'])); ?></p>
                            <?php if ($user['LANHOATDONGCUOI']): ?>
                            <p class="mb-0"><i class="bi bi-clock me-2 text-muted"></i>Hoạt động: <?php echo date('d/m/Y H:i', strtotime($user['LANHOATDONGCUOI'])); ?></p>
                            <?php endif; ?>
                        </div>
                        
                        <?php if ($isOwnProfile): ?>
                        <div class="mt-3">
                            <a href="user/dashboard.php" class="btn btn-primary w-100">
                                <i class="bi bi-speedometer2 me-2"></i>Dashboard
                            </a>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="col-lg-8">
                <!-- Tabs -->
                <ul class="nav nav-tabs mb-3" id="profileTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="questions-tab" data-bs-toggle="tab" data-bs-target="#questions" type="button">
                            <i class="bi bi-question-circle me-1"></i>Câu hỏi (<?php echo $stats['SoCauHoi']; ?>)
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="answers-tab" data-bs-toggle="tab" data-bs-target="#answers" type="button">
                            <i class="bi bi-chat-left-text me-1"></i>Trả lời (<?php echo $stats['SoCauTraLoi']; ?>)
                        </button>
                    </li>
                </ul>
                
                <div class="tab-content" id="profileTabsContent">
                    <!-- Questions Tab -->
                    <div class="tab-pane fade show active" id="questions" role="tabpanel">
                        <div class="card">
                            <div class="card-body">
                                <?php if (empty($questions)): ?>
                                <p class="text-muted text-center py-4">Chưa có câu hỏi nào</p>
                                <?php else: ?>
                                <div class="list-group list-group-flush">
                                    <?php foreach ($questions as $q): ?>
                                    <a href="question-detail.php?id=<?php echo $q['MACAUHOI']; ?>" class="list-group-item list-group-item-action">
                                        <div class="d-flex justify-content-between">
                                            <h6 class="mb-1"><?php echo htmlspecialchars($q['TIEUDE']); ?></h6>
                                            <small class="text-muted"><?php echo date('d/m/Y', strtotime($q['NGAYDANG'])); ?></small>
                                        </div>
                                        <small class="text-muted">
                                            <span class="me-3"><i class="bi bi-chat me-1"></i><?php echo $q['SoCauTraLoi']; ?> trả lời</span>
                                            <span><i class="bi bi-eye me-1"></i><?php echo number_format($q['LUOTXEM']); ?> lượt xem</span>
                                        </small>
                                    </a>
                                    <?php endforeach; ?>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Answers Tab -->
                    <div class="tab-pane fade" id="answers" role="tabpanel">
                        <div class="card">
                            <div class="card-body">
                                <?php if (empty($answers)): ?>
                                <p class="text-muted text-center py-4">Chưa có câu trả lời nào</p>
                                <?php else: ?>
                                <div class="list-group list-group-flush">
                                    <?php foreach ($answers as $a): ?>
                                    <a href="question-detail.php?id=<?php echo $a['MACAUHOI']; ?>" class="list-group-item list-group-item-action">
                                        <div class="d-flex justify-content-between">
                                            <h6 class="mb-1"><?php echo htmlspecialchars($a['TIEUDE']); ?></h6>
                                            <small class="text-muted"><?php echo date('d/m/Y', strtotime($a['NGAYTL'])); ?></small>
                                        </div>
                                        <p class="mb-1 text-muted small"><?php echo mb_substr(strip_tags($a['NOIDUNGTL']), 0, 150); ?>...</p>
                                    </a>
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
</main>

<?php require_once 'includes/footer.php'; ?>
