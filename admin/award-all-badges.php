<?php
/**
 * Script cấp huy hiệu tự động cho tất cả người dùng
 */
$pageTitle = 'Cấp huy hiệu tự động';
require_once '../config/database.php';
require_once '../config/session.php';
require_once '../includes/badge_helper.php';

requireAdmin();

$results = [];
$totalAwarded = 0;

if (isset($_POST['award_all']) || isset($_GET['run'])) {
    // Lấy tất cả người dùng
    $users = $conn->query("SELECT MANGUOIDUNG, HOTEN FROM NGUOIDUNG")->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($users as $user) {
        $awarded = checkAndAwardBadges($user['MANGUOIDUNG']);
        if (!empty($awarded)) {
            $results[] = [
                'user' => $user['HOTEN'],
                'badges' => $awarded
            ];
            $totalAwarded += count($awarded);
        }
    }
}

require_once '../includes/admin_header.php';
?>

<div class="admin-container">
    <div class="container-fluid py-4">
        <h2 class="mb-4"><i class="bi bi-award me-2"></i>Cấp huy hiệu tự động</h2>
        
        <div class="card mb-4">
            <div class="card-body">
                <p>Chức năng này sẽ kiểm tra tất cả người dùng và cấp huy hiệu dựa trên tiêu chí:</p>
                
                <?php
                // Lấy danh sách huy hiệu từ database
                $allBadges = $conn->query("SELECT * FROM HUYHIEU ORDER BY LOAITIEUCHI, NGUONGTIEUCHI")->fetchAll(PDO::FETCH_ASSOC);
                $badgesByType = [];
                foreach ($allBadges as $b) {
                    $badgesByType[$b['LOAITIEUCHI']][] = $b;
                }
                ?>
                
                <div class="row">
                    <?php if (!empty($badgesByType['ngaythamgia'])): ?>
                    <div class="col-md-6 col-lg-4 mb-3">
                        <h6 class="text-muted">Tham gia</h6>
                        <ul class="list-unstyled">
                            <?php foreach ($badgesByType['ngaythamgia'] as $b): ?>
                            <li><?php echo $b['BIEUTUONG']; ?> <strong><?php echo $b['TENHUYHIEU']; ?></strong> - <?php echo $b['MOTA']; ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($badgesByType['cauhoi'])): ?>
                    <div class="col-md-6 col-lg-4 mb-3">
                        <h6 class="text-muted">Đặt câu hỏi</h6>
                        <ul class="list-unstyled">
                            <?php foreach ($badgesByType['cauhoi'] as $b): ?>
                            <li><?php echo $b['BIEUTUONG']; ?> <strong><?php echo $b['TENHUYHIEU']; ?></strong> - <?php echo $b['NGUONGTIEUCHI']; ?> câu</li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($badgesByType['cautraloi'])): ?>
                    <div class="col-md-6 col-lg-4 mb-3">
                        <h6 class="text-muted">Trả lời</h6>
                        <ul class="list-unstyled">
                            <?php foreach ($badgesByType['cautraloi'] as $b): ?>
                            <li><?php echo $b['BIEUTUONG']; ?> <strong><?php echo $b['TENHUYHIEU']; ?></strong> - <?php echo $b['NGUONGTIEUCHI']; ?> câu</li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($badgesByType['diem'])): ?>
                    <div class="col-md-6 col-lg-4 mb-3">
                        <h6 class="text-muted">Điểm số</h6>
                        <ul class="list-unstyled">
                            <?php foreach ($badgesByType['diem'] as $b): ?>
                            <li><?php echo $b['BIEUTUONG']; ?> <strong><?php echo $b['TENHUYHIEU']; ?></strong> - <?php echo number_format($b['NGUONGTIEUCHI']); ?> điểm</li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($badgesByType['vote'])): ?>
                    <div class="col-md-6 col-lg-4 mb-3">
                        <h6 class="text-muted">Đánh giá</h6>
                        <ul class="list-unstyled">
                            <?php foreach ($badgesByType['vote'] as $b): ?>
                            <li><?php echo $b['BIEUTUONG']; ?> <strong><?php echo $b['TENHUYHIEU']; ?></strong> - <?php echo $b['NGUONGTIEUCHI']; ?> vote</li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <?php endif; ?>
                </div>
                
                <form method="POST">
                    <button type="submit" name="award_all" class="btn btn-primary btn-lg">
                        <i class="bi bi-magic me-2"></i>Cấp huy hiệu cho tất cả người dùng
                    </button>
                </form>
            </div>
        </div>
        
        <?php if (!empty($results)): ?>
        <div class="alert alert-success">
            <i class="bi bi-check-circle me-2"></i>
            Đã cấp <strong><?php echo $totalAwarded; ?></strong> huy hiệu cho <strong><?php echo count($results); ?></strong> người dùng!
        </div>
        
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Kết quả</h5>
            </div>
            <div class="card-body p-0">
                <table class="table table-striped mb-0">
                    <thead>
                        <tr>
                            <th>Người dùng</th>
                            <th>Huy hiệu được cấp</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($results as $r): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($r['user']); ?></td>
                            <td>
                                <?php foreach ($r['badges'] as $badge): ?>
                                <span class="badge bg-light text-dark me-1">
                                    <?php echo $badge['BIEUTUONG']; ?> <?php echo htmlspecialchars($badge['TENHUYHIEU']); ?>
                                </span>
                                <?php endforeach; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php elseif (isset($_POST['award_all'])): ?>
        <div class="alert alert-info">
            <i class="bi bi-info-circle me-2"></i>
            Tất cả người dùng đã có đầy đủ huy hiệu phù hợp với tiêu chí của họ.
        </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../includes/admin_footer.php'; ?>
