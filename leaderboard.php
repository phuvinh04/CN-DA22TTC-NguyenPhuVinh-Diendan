<?php
$pageTitle = 'Bảng xếp hạng';
require_once 'config/database.php';
require_once 'includes/header.php';

// Lấy top users theo điểm
$topUsers = $conn->query("
    SELECT 
        nd.MANGUOIDUNG,
        nd.HOTEN,
        nd.ANHDAIDIEN,
        nd.DIEMDANHGIA,
        nd.NGAYTAO,
        (SELECT COUNT(*) FROM DAT WHERE MANGUOIDUNG = nd.MANGUOIDUNG) as SoCauHoi,
        (SELECT COUNT(*) FROM TRALOI WHERE MANGUOIDUNG = nd.MANGUOIDUNG) as SoTraLoi,
        (SELECT COUNT(*) FROM NHAN WHERE MANGUOIDUNG = nd.MANGUOIDUNG) as SoHuyHieu
    FROM NGUOIDUNG nd
    WHERE nd.TRANGTHAI = 'active'
    ORDER BY nd.DIEMDANHGIA DESC
    LIMIT 50
")->fetchAll();

// Lấy top contributors tuần này
$topWeek = $conn->query("
    SELECT 
        nd.MANGUOIDUNG,
        nd.HOTEN,
        nd.ANHDAIDIEN,
        COUNT(DISTINCT d.MACAUHOI) + COUNT(DISTINCT tl.MACAUTRALOI) as HoatDong
    FROM NGUOIDUNG nd
    LEFT JOIN DAT d ON nd.MANGUOIDUNG = d.MANGUOIDUNG AND d.NGAYDANG >= DATE_SUB(NOW(), INTERVAL 7 DAY)
    LEFT JOIN TRALOI tl ON nd.MANGUOIDUNG = tl.MANGUOIDUNG AND tl.NGAYTL >= DATE_SUB(NOW(), INTERVAL 7 DAY)
    WHERE nd.TRANGTHAI = 'active'
    GROUP BY nd.MANGUOIDUNG, nd.HOTEN, nd.ANHDAIDIEN
    HAVING HoatDong > 0
    ORDER BY HoatDong DESC
    LIMIT 10
")->fetchAll();

// Lấy top contributors tháng này
$topMonth = $conn->query("
    SELECT 
        nd.MANGUOIDUNG,
        nd.HOTEN,
        nd.ANHDAIDIEN,
        COUNT(DISTINCT d.MACAUHOI) + COUNT(DISTINCT tl.MACAUTRALOI) as HoatDong
    FROM NGUOIDUNG nd
    LEFT JOIN DAT d ON nd.MANGUOIDUNG = d.MANGUOIDUNG AND d.NGAYDANG >= DATE_SUB(NOW(), INTERVAL 30 DAY)
    LEFT JOIN TRALOI tl ON nd.MANGUOIDUNG = tl.MANGUOIDUNG AND tl.NGAYTL >= DATE_SUB(NOW(), INTERVAL 30 DAY)
    WHERE nd.TRANGTHAI = 'active'
    GROUP BY nd.MANGUOIDUNG, nd.HOTEN, nd.ANHDAIDIEN
    HAVING HoatDong > 0
    ORDER BY HoatDong DESC
    LIMIT 10
")->fetchAll();
?>

<main class="py-4">
    <div class="container">
        <!-- Page Header -->
        <div class="text-center mb-5">
            <h1 class="display-4 mb-3">
                <i class="bi bi-trophy-fill text-warning me-3"></i>
                Bảng Xếp Hạng
            </h1>
            <p class="lead text-muted">Những thành viên xuất sắc nhất của Diễn Đàn Chuyên Ngành</p>
        </div>

        <!-- Top 3 Podium -->
        <div class="row mb-5">
            <?php if (count($topUsers) >= 3): ?>
            <!-- Rank 2 -->
            <div class="col-md-4 order-md-1 mb-4">
                <div class="card modern-card text-center podium-card rank-2">
                    <div class="card-body py-4">
                        <div class="rank-badge rank-2-badge">
                            <i class="bi bi-award-fill"></i>
                            <span>2</span>
                        </div>
                        <img src="<?php echo htmlspecialchars($topUsers[1]['ANHDAIDIEN']); ?>" class="podium-avatar mb-3" alt="Avatar">
                        <h4 class="mb-2"><?php echo htmlspecialchars($topUsers[1]['HOTEN']); ?></h4>
                        <div class="podium-points">
                            <span class="points-number"><?php echo number_format($topUsers[1]['DIEMDANHGIA']); ?></span>
                            <span class="points-label">điểm</span>
                        </div>
                        <div class="podium-stats mt-3">
                            <span class="badge bg-primary me-2"><?php echo $topUsers[1]['SoCauHoi']; ?> câu hỏi</span>
                            <span class="badge bg-success"><?php echo $topUsers[1]['SoTraLoi']; ?> trả lời</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Rank 1 -->
            <div class="col-md-4 order-md-2 mb-4">
                <div class="card modern-card text-center podium-card rank-1">
                    <div class="card-body py-4">
                        <div class="rank-badge rank-1-badge">
                            <i class="bi bi-trophy-fill"></i>
                            <span>1</span>
                        </div>
                        <img src="<?php echo htmlspecialchars($topUsers[0]['ANHDAIDIEN']); ?>" class="podium-avatar mb-3" alt="Avatar">
                        <h3 class="mb-2"><?php echo htmlspecialchars($topUsers[0]['HOTEN']); ?></h3>
                        <div class="podium-points">
                            <span class="points-number"><?php echo number_format($topUsers[0]['DIEMDANHGIA']); ?></span>
                            <span class="points-label">điểm</span>
                        </div>
                        <div class="podium-stats mt-3">
                            <span class="badge bg-primary me-2"><?php echo $topUsers[0]['SoCauHoi']; ?> câu hỏi</span>
                            <span class="badge bg-success"><?php echo $topUsers[0]['SoTraLoi']; ?> trả lời</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Rank 3 -->
            <div class="col-md-4 order-md-3 mb-4">
                <div class="card modern-card text-center podium-card rank-3">
                    <div class="card-body py-4">
                        <div class="rank-badge rank-3-badge">
                            <i class="bi bi-award-fill"></i>
                            <span>3</span>
                        </div>
                        <img src="<?php echo htmlspecialchars($topUsers[2]['ANHDAIDIEN']); ?>" class="podium-avatar mb-3" alt="Avatar">
                        <h4 class="mb-2"><?php echo htmlspecialchars($topUsers[2]['HOTEN']); ?></h4>
                        <div class="podium-points">
                            <span class="points-number"><?php echo number_format($topUsers[2]['DIEMDANHGIA']); ?></span>
                            <span class="points-label">điểm</span>
                        </div>
                        <div class="podium-stats mt-3">
                            <span class="badge bg-primary me-2"><?php echo $topUsers[2]['SoCauHoi']; ?> câu hỏi</span>
                            <span class="badge bg-success"><?php echo $topUsers[2]['SoTraLoi']; ?> trả lời</span>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <div class="row">
            <!-- All Time Leaderboard -->
            <div class="col-lg-6 mb-4">
                <div class="card modern-card">
                    <div class="card-header bg-transparent border-0">
                        <h5 class="mb-0"><i class="bi bi-star-fill text-warning me-2"></i>Xếp hạng tổng thể</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th width="60">Hạng</th>
                                        <th>Thành viên</th>
                                        <th class="text-center">Điểm</th>
                                        <th class="text-center">Hoạt động</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($topUsers as $index => $user): ?>
                                    <tr>
                                        <td class="text-center">
                                            <?php if ($index < 3): ?>
                                                <span class="rank-medal rank-<?php echo $index + 1; ?>">
                                                    <?php echo $index + 1; ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="text-muted"><?php echo $index + 1; ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <img src="<?php echo htmlspecialchars($user['ANHDAIDIEN']); ?>" class="user-avatar-sm me-2" alt="Avatar">
                                                <div>
                                                    <div class="fw-semibold"><?php echo htmlspecialchars($user['HOTEN']); ?></div>
                                                    <small class="text-muted"><?php echo $user['SoHuyHieu']; ?> huy hiệu</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-warning text-dark"><?php echo number_format($user['DIEMDANHGIA']); ?></span>
                                        </td>
                                        <td class="text-center">
                                            <small class="text-muted">
                                                <?php echo $user['SoCauHoi']; ?> hỏi • <?php echo $user['SoTraLoi']; ?> trả lời
                                            </small>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Weekly & Monthly -->
            <div class="col-lg-6">
                <!-- Top tuần -->
                <div class="card modern-card mb-4">
                    <div class="card-header bg-transparent border-0">
                        <h5 class="mb-0"><i class="bi bi-fire text-danger me-2"></i>Nổi bật tuần này</h5>
                    </div>
                    <div class="card-body">
                        <?php foreach ($topWeek as $index => $user): ?>
                        <div class="d-flex align-items-center mb-3">
                            <span class="badge bg-danger rounded-circle me-3" style="width: 30px; height: 30px; display: flex; align-items: center; justify-content: center;">
                                <?php echo $index + 1; ?>
                            </span>
                            <img src="<?php echo htmlspecialchars($user['ANHDAIDIEN']); ?>" class="user-avatar-sm me-3" alt="Avatar">
                            <div class="flex-grow-1">
                                <div class="fw-semibold"><?php echo htmlspecialchars($user['HOTEN']); ?></div>
                                <small class="text-muted"><?php echo $user['HoatDong']; ?> hoạt động</small>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Top tháng -->
                <div class="card modern-card">
                    <div class="card-header bg-transparent border-0">
                        <h5 class="mb-0"><i class="bi bi-calendar-check text-success me-2"></i>Nổi bật tháng này</h5>
                    </div>
                    <div class="card-body">
                        <?php foreach ($topMonth as $index => $user): ?>
                        <div class="d-flex align-items-center mb-3">
                            <span class="badge bg-success rounded-circle me-3" style="width: 30px; height: 30px; display: flex; align-items: center; justify-content: center;">
                                <?php echo $index + 1; ?>
                            </span>
                            <img src="<?php echo htmlspecialchars($user['ANHDAIDIEN']); ?>" class="user-avatar-sm me-3" alt="Avatar">
                            <div class="flex-grow-1">
                                <div class="fw-semibold"><?php echo htmlspecialchars($user['HOTEN']); ?></div>
                                <small class="text-muted"><?php echo $user['HoatDong']; ?> hoạt động</small>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Call to Action -->
        <div class="text-center mt-5">
            <div class="card modern-card bg-gradient text-white" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <div class="card-body py-5">
                    <h3 class="mb-3">Bạn cũng có thể lên bảng xếp hạng!</h3>
                    <p class="lead mb-4">Tham gia đóng góp và kiếm điểm ngay hôm nay</p>
                    <a href="points-system.php" class="btn btn-light btn-lg me-2">
                        <i class="bi bi-info-circle me-2"></i>Xem cách kiếm điểm
                    </a>
                    <a href="ask-question.php" class="btn btn-outline-light btn-lg">
                        <i class="bi bi-plus-circle me-2"></i>Đặt câu hỏi ngay
                    </a>
                </div>
            </div>
        </div>
    </div>
</main>

<?php require_once 'includes/footer.php'; ?>
