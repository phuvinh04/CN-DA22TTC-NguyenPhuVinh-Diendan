<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$pageTitle = 'Thống kê hệ thống';
require_once '../config/database.php';
require_once '../config/session.php';
requireAdmin();

try {
// Thống kê tổng quan
$stats = [];
$stats['totalUsers'] = $conn->query("SELECT COUNT(*) FROM NGUOIDUNG WHERE TRANGTHAI = 'active'")->fetchColumn();
$stats['totalQuestions'] = $conn->query("SELECT COUNT(*) FROM CAUHOI")->fetchColumn();
$stats['totalAnswers'] = $conn->query("SELECT COUNT(*) FROM TRALOI")->fetchColumn();
$stats['totalTags'] = $conn->query("SELECT COUNT(*) FROM TAG")->fetchColumn();
$stats['totalViews'] = $conn->query("SELECT SUM(LUOTXEM) FROM CAUHOI")->fetchColumn() ?: 0;

// Thống kê hôm nay
$stats['todayUsers'] = $conn->query("SELECT COUNT(*) FROM NGUOIDUNG WHERE DATE(NGAYTAO) = CURDATE()")->fetchColumn();
$stats['todayQuestions'] = $conn->query("SELECT COUNT(*) FROM DAT WHERE DATE(NGAYDANG) = CURDATE()")->fetchColumn();
$stats['todayAnswers'] = $conn->query("SELECT COUNT(*) FROM TRALOI WHERE DATE(NGAYTL) = CURDATE()")->fetchColumn();

// Thống kê tuần này
$stats['weekUsers'] = $conn->query("SELECT COUNT(*) FROM NGUOIDUNG WHERE NGAYTAO >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)")->fetchColumn();
$stats['weekQuestions'] = $conn->query("SELECT COUNT(*) FROM DAT WHERE NGAYDANG >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)")->fetchColumn();

// Thống kê tháng này
$stats['monthUsers'] = $conn->query("SELECT COUNT(*) FROM NGUOIDUNG WHERE NGAYTAO >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)")->fetchColumn();
$stats['monthQuestions'] = $conn->query("SELECT COUNT(*) FROM DAT WHERE NGAYDANG >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)")->fetchColumn();
$stats['monthAnswers'] = $conn->query("SELECT COUNT(*) FROM TRALOI WHERE NGAYTL >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)")->fetchColumn();

// Câu hỏi đã được trả lời vs chưa trả lời
$stats['answeredQuestions'] = $conn->query("SELECT COUNT(DISTINCT ch.MACAUHOI) FROM CAUHOI ch INNER JOIN TRALOI tl ON ch.MACAUHOI = tl.MACAUHOI")->fetchColumn();
$stats['unansweredQuestions'] = $stats['totalQuestions'] - $stats['answeredQuestions'];

// Câu hỏi có câu trả lời được chấp nhận
$stats['acceptedAnswers'] = $conn->query("SELECT COUNT(*) FROM TRALOI WHERE DUOCCHAPNHAN = 1")->fetchColumn();

// Top users theo điểm
$topUsers = $conn->query("
    SELECT nd.MANGUOIDUNG, nd.HOTEN, nd.ANHDAIDIEN, nd.DIEMDANHGIA,
           (SELECT COUNT(*) FROM DAT WHERE MANGUOIDUNG = nd.MANGUOIDUNG) as SoCauHoi,
           (SELECT COUNT(*) FROM TRALOI WHERE MANGUOIDUNG = nd.MANGUOIDUNG) as SoCauTraLoi
    FROM NGUOIDUNG nd
    WHERE nd.TRANGTHAI = 'active'
    ORDER BY nd.DIEMDANHGIA DESC
    LIMIT 10
")->fetchAll(PDO::FETCH_ASSOC);

// Top tags theo số câu hỏi
$topTags = $conn->query("
    SELECT t.MATHE, t.TENTHE, COUNT(ch.MACAUHOI) as SoCauHoi
    FROM TAG t
    LEFT JOIN CAUHOI ch ON t.MATHE = ch.MATHE
    GROUP BY t.MATHE, t.TENTHE
    ORDER BY SoCauHoi DESC
    LIMIT 10
")->fetchAll(PDO::FETCH_ASSOC);

// Thống kê theo tháng (6 tháng gần nhất)
$monthlyQuestions = $conn->query("
    SELECT DATE_FORMAT(NGAYDANG, '%Y-%m') as thang, COUNT(*) as soCauHoi
    FROM DAT
    WHERE NGAYDANG >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
    GROUP BY DATE_FORMAT(NGAYDANG, '%Y-%m')
    ORDER BY thang ASC
")->fetchAll(PDO::FETCH_ASSOC);

$monthlyUsers = $conn->query("
    SELECT DATE_FORMAT(NGAYTAO, '%Y-%m') as thang, COUNT(*) as soUser
    FROM NGUOIDUNG
    WHERE NGAYTAO >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
    GROUP BY DATE_FORMAT(NGAYTAO, '%Y-%m')
    ORDER BY thang ASC
")->fetchAll(PDO::FETCH_ASSOC);

$monthlyAnswers = $conn->query("
    SELECT DATE_FORMAT(NGAYTL, '%Y-%m') as thang, COUNT(*) as soTraLoi
    FROM TRALOI
    WHERE NGAYTL >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
    GROUP BY DATE_FORMAT(NGAYTL, '%Y-%m')
    ORDER BY thang ASC
")->fetchAll(PDO::FETCH_ASSOC);

// Thống kê 7 ngày gần nhất
$dailyStats = $conn->query("
    SELECT DATE(NGAYDANG) as ngay, COUNT(*) as soCauHoi
    FROM DAT
    WHERE NGAYDANG >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
    GROUP BY DATE(NGAYDANG)
    ORDER BY ngay ASC
")->fetchAll(PDO::FETCH_ASSOC);

$dailyAnswers = $conn->query("
    SELECT DATE(NGAYTL) as ngay, COUNT(*) as soTraLoi
    FROM TRALOI
    WHERE NGAYTL >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
    GROUP BY DATE(NGAYTL)
    ORDER BY ngay ASC
")->fetchAll(PDO::FETCH_ASSOC);

// Thống kê user theo vai trò (từ bảng COVT)
$userRoles = $conn->query("
    SELECT vt.TENVAITRO as VAITRO, COUNT(c.MANGUOIDUNG) as soLuong
    FROM VAITRO vt
    LEFT JOIN COVT c ON vt.MAVAITRO = c.MAVAITRO
    LEFT JOIN NGUOIDUNG nd ON c.MANGUOIDUNG = nd.MANGUOIDUNG AND nd.TRANGTHAI = 'active'
    GROUP BY vt.MAVAITRO, vt.TENVAITRO
")->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    die("Lỗi database: " . $e->getMessage());
}

require_once '../includes/admin_header.php';
?>

<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

<style>
/* Background gradient cho trang */
.admin-container {
    background: linear-gradient(135deg, #f5f7fa 0%, #e4e8f0 100%);
    min-height: 100vh;
}

.chart-container { position: relative; height: 300px; width: 100%; }
.chart-container-sm { position: relative; height: 250px; width: 100%; }

/* Stat cards với màu rực rỡ */
.stat-mini-card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 20px; padding: 25px; color: white;
    transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    position: relative; overflow: hidden;
}
.stat-mini-card::before {
    content: ''; position: absolute; top: -50%; right: -50%;
    width: 100%; height: 100%;
    background: radial-gradient(circle, rgba(255,255,255,0.2) 0%, transparent 70%);
    transform: rotate(30deg);
}
.stat-mini-card:hover { 
    transform: translateY(-10px) scale(1.02); 
    box-shadow: 0 20px 40px rgba(102, 126, 234, 0.5);
}
.stat-mini-card.green { 
    background: linear-gradient(135deg, #00b09b 0%, #96c93d 100%); 
}
.stat-mini-card.green:hover { box-shadow: 0 20px 40px rgba(0, 176, 155, 0.5); }
.stat-mini-card.orange { 
    background: linear-gradient(135deg, #ff6a00 0%, #ee0979 100%); 
}
.stat-mini-card.orange:hover { box-shadow: 0 20px 40px rgba(238, 9, 121, 0.5); }
.stat-mini-card.blue { 
    background: linear-gradient(135deg, #00c6ff 0%, #0072ff 100%); 
}
.stat-mini-card.blue:hover { box-shadow: 0 20px 40px rgba(0, 114, 255, 0.5); }

.stat-mini-card .stat-icon { 
    font-size: 3rem; opacity: 0.9;
    filter: drop-shadow(0 4px 8px rgba(0,0,0,0.2));
}
.stat-mini-card .stat-value { 
    font-size: 2.5rem; font-weight: 800;
    text-shadow: 0 2px 10px rgba(0,0,0,0.2);
}
.stat-mini-card .stat-label { font-size: 1rem; opacity: 0.95; font-weight: 500; }
.stat-mini-card .stat-change { 
    font-size: 0.85rem; background: rgba(255,255,255,0.25); 
    padding: 4px 12px; border-radius: 20px; 
    backdrop-filter: blur(10px);
}

/* Modern chart cards */
.modern-chart-card { 
    border: none; border-radius: 20px; 
    box-shadow: 0 10px 40px rgba(0,0,0,0.1); 
    transition: all 0.3s ease;
    background: white;
    overflow: hidden;
}
.modern-chart-card:hover { 
    transform: translateY(-5px); 
    box-shadow: 0 20px 50px rgba(0,0,0,0.15);
}
.modern-chart-card .card-header { 
    border-bottom: 2px solid rgba(102, 126, 234, 0.1); 
    padding: 1.25rem 1.5rem;
    background: linear-gradient(135deg, rgba(102, 126, 234, 0.05) 0%, rgba(118, 75, 162, 0.05) 100%);
}
.modern-chart-card .card-header h5 {
    font-weight: 700;
    color: #2d3748;
}

/* Week stats cards */
.week-stat-card {
    background: white;
    border-radius: 20px;
    padding: 30px;
    text-align: center;
    transition: all 0.3s ease;
    border: 2px solid transparent;
}
.week-stat-card:hover {
    border-color: #667eea;
    transform: translateY(-5px);
    box-shadow: 0 15px 35px rgba(102, 126, 234, 0.2);
}
.week-stat-card i {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}
.week-stat-card.green i {
    background: linear-gradient(135deg, #00b09b 0%, #96c93d 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}
.week-stat-card.cyan i {
    background: linear-gradient(135deg, #00c6ff 0%, #0072ff 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}
.week-stat-card h3 {
    font-size: 2.5rem;
    font-weight: 800;
    background: linear-gradient(135deg, #2d3748 0%, #4a5568 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

/* Month stats */
.month-stat-box {
    padding: 25px;
    border-radius: 16px;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}
.month-stat-box::before {
    content: ''; position: absolute; top: 0; left: 0;
    width: 100%; height: 4px;
    background: linear-gradient(90deg, #667eea, #764ba2);
}
.month-stat-box.green::before { background: linear-gradient(90deg, #00b09b, #96c93d); }
.month-stat-box.blue::before { background: linear-gradient(90deg, #00c6ff, #0072ff); }
.month-stat-box.pink::before { background: linear-gradient(90deg, #ff6a00, #ee0979); }
.month-stat-box:hover {
    transform: scale(1.05);
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
}
.month-stat-box h2 {
    font-size: 2.5rem;
    font-weight: 800;
}

/* Table styling */
.table-hover tbody tr:hover {
    background: linear-gradient(135deg, rgba(102, 126, 234, 0.05) 0%, rgba(118, 75, 162, 0.05) 100%);
}
.table thead th {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    font-weight: 600;
    border: none;
}

/* Animated badges */
.badge.bg-warning { 
    background: linear-gradient(135deg, #f6d365 0%, #fda085 100%) !important;
    animation: pulse 2s infinite;
}
.badge.bg-secondary { 
    background: linear-gradient(135deg, #bdc3c7 0%, #95a5a6 100%) !important;
}
.badge.bg-danger { 
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%) !important;
}

@keyframes pulse {
    0%, 100% { box-shadow: 0 0 0 0 rgba(246, 211, 101, 0.7); }
    50% { box-shadow: 0 0 0 10px rgba(246, 211, 101, 0); }
}

/* Page title */
.page-title {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    font-weight: 800;
}
</style>

<div class="admin-container">
    <div class="container-fluid py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="mb-1 page-title"><i class="bi bi-graph-up me-2"></i>📊 Thống kê hệ thống</h2>
                <p class="text-muted mb-0">Tổng quan về hoạt động của diễn đàn</p>
            </div>
            <div class="badge" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 10px 20px; font-size: 0.9rem;">
                <i class="bi bi-calendar3 me-2"></i>Cập nhật: <?php echo date('d/m/Y H:i'); ?>
            </div>
        </div>

        <!-- Thống kê tổng quan -->
        <div class="row g-4 mb-4">
            <div class="col-xl-3 col-md-6">
                <div class="stat-mini-card">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="stat-value"><?php echo number_format($stats['totalUsers']); ?></div>
                            <div class="stat-label">Tổng thành viên</div>
                            <div class="stat-change mt-2"><i class="bi bi-arrow-up"></i> +<?php echo $stats['todayUsers']; ?> hôm nay</div>
                        </div>
                        <div class="stat-icon"><i class="bi bi-people-fill"></i></div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="stat-mini-card green">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="stat-value"><?php echo number_format($stats['totalQuestions']); ?></div>
                            <div class="stat-label">Tổng câu hỏi</div>
                            <div class="stat-change mt-2"><i class="bi bi-arrow-up"></i> +<?php echo $stats['todayQuestions']; ?> hôm nay</div>
                        </div>
                        <div class="stat-icon"><i class="bi bi-question-circle-fill"></i></div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="stat-mini-card blue">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="stat-value"><?php echo number_format($stats['totalAnswers']); ?></div>
                            <div class="stat-label">Tổng câu trả lời</div>
                            <div class="stat-change mt-2"><i class="bi bi-arrow-up"></i> +<?php echo $stats['todayAnswers']; ?> hôm nay</div>
                        </div>
                        <div class="stat-icon"><i class="bi bi-chat-dots-fill"></i></div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="stat-mini-card orange">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="stat-value"><?php echo number_format($stats['totalViews']); ?></div>
                            <div class="stat-label">Tổng lượt xem</div>
                            <div class="stat-change mt-2"><i class="bi bi-tags"></i> <?php echo $stats['totalTags']; ?> chủ đề</div>
                        </div>
                        <div class="stat-icon"><i class="bi bi-eye-fill"></i></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Thống kê tuần -->
        <div class="row g-4 mb-4">
            <div class="col-md-4">
                <div class="week-stat-card">
                    <i class="bi bi-calendar-week" style="font-size: 3.5rem;"></i>
                    <h3 class="mt-3 mb-1"><?php echo $stats['weekUsers']; ?></h3>
                    <p class="text-muted mb-0">Thành viên mới tuần này</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="week-stat-card green">
                    <i class="bi bi-chat-square-text" style="font-size: 3.5rem;"></i>
                    <h3 class="mt-3 mb-1"><?php echo $stats['weekQuestions']; ?></h3>
                    <p class="text-muted mb-0">Câu hỏi mới tuần này</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="week-stat-card cyan">
                    <i class="bi bi-percent" style="font-size: 3.5rem;"></i>
                    <h3 class="mt-3 mb-1"><?php echo $stats['totalQuestions'] > 0 ? round(($stats['totalAnswers'] / $stats['totalQuestions']) * 100, 1) : 0; ?>%</h3>
                    <p class="text-muted mb-0">Tỷ lệ trả lời/câu hỏi</p>
                </div>
            </div>
        </div>

        <!-- Biểu đồ chính -->
        <div class="row g-4 mb-4">
            <!-- Line Chart - Hoạt động 6 tháng -->
            <div class="col-lg-8">
                <div class="card modern-chart-card">
                    <div class="card-header bg-transparent">
                        <h5 class="mb-0"><i class="bi bi-graph-up-arrow text-primary me-2"></i>Hoạt động 6 tháng gần nhất</h5>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="monthlyActivityChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Doughnut Chart - Trạng thái câu hỏi -->
            <div class="col-lg-4">
                <div class="card modern-chart-card">
                    <div class="card-header bg-transparent">
                        <h5 class="mb-0"><i class="bi bi-pie-chart-fill text-success me-2"></i>Trạng thái câu hỏi</h5>
                    </div>
                    <div class="card-body">
                        <div class="chart-container-sm">
                            <canvas id="questionStatusChart"></canvas>
                        </div>
                        <div class="mt-3">
                            <div class="d-flex justify-content-between mb-2">
                                <span><span class="badge bg-success me-2">&nbsp;</span>Đã trả lời</span>
                                <strong><?php echo $stats['answeredQuestions']; ?></strong>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span><span class="badge bg-warning me-2">&nbsp;</span>Chưa trả lời</span>
                                <strong><?php echo $stats['unansweredQuestions']; ?></strong>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span><span class="badge bg-primary me-2">&nbsp;</span>Đã chấp nhận</span>
                                <strong><?php echo $stats['acceptedAnswers']; ?></strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Biểu đồ 7 ngày và vai trò -->
        <div class="row g-4 mb-4">
            <!-- Bar Chart - 7 ngày gần nhất -->
            <div class="col-lg-8">
                <div class="card modern-chart-card">
                    <div class="card-header bg-transparent">
                        <h5 class="mb-0"><i class="bi bi-bar-chart-fill text-info me-2"></i>Hoạt động 7 ngày gần nhất</h5>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="dailyActivityChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Pie Chart - Vai trò người dùng -->
            <div class="col-lg-4">
                <div class="card modern-chart-card">
                    <div class="card-header bg-transparent">
                        <h5 class="mb-0"><i class="bi bi-person-badge text-warning me-2"></i>Phân bố vai trò</h5>
                    </div>
                    <div class="card-body">
                        <div class="chart-container-sm">
                            <canvas id="userRolesChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Top Tags Chart và Top Users -->
        <div class="row g-4 mb-4">
            <!-- Horizontal Bar Chart - Top Tags -->
            <div class="col-lg-6">
                <div class="card modern-chart-card">
                    <div class="card-header bg-transparent">
                        <h5 class="mb-0"><i class="bi bi-tags-fill text-primary me-2"></i>Top 10 chủ đề phổ biến</h5>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="topTagsChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Top Users -->
            <div class="col-lg-6">
                <div class="card modern-chart-card">
                    <div class="card-header bg-transparent">
                        <h5 class="mb-0"><i class="bi bi-trophy-fill text-warning me-2"></i>Top 10 thành viên</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>#</th>
                                        <th>Thành viên</th>
                                        <th class="text-center">Điểm</th>
                                        <th class="text-center">Hỏi</th>
                                        <th class="text-center">Trả lời</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($topUsers as $index => $user): ?>
                                    <tr>
                                        <td>
                                            <?php if ($index < 3): ?>
                                            <span class="badge <?php echo ['bg-warning', 'bg-secondary', 'bg-danger'][$index]; ?> rounded-pill">
                                                <?php echo $index + 1; ?>
                                            </span>
                                            <?php else: ?>
                                            <?php echo $index + 1; ?>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <img src="<?php echo htmlspecialchars($user['ANHDAIDIEN']); ?>" 
                                                     class="rounded-circle me-2" width="32" height="32" 
                                                     onerror="this.src='https://ui-avatars.com/api/?name=<?php echo urlencode($user['HOTEN']); ?>&background=random'">
                                                <span><?php echo htmlspecialchars($user['HOTEN']); ?></span>
                                            </div>
                                        </td>
                                        <td class="text-center"><strong class="text-primary"><?php echo number_format($user['DIEMDANHGIA']); ?></strong></td>
                                        <td class="text-center"><?php echo $user['SoCauHoi']; ?></td>
                                        <td class="text-center"><?php echo $user['SoCauTraLoi']; ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Thống kê tháng này -->
        <div class="row g-4 mb-4">
            <div class="col-12">
                <div class="card modern-chart-card">
                    <div class="card-header bg-transparent">
                        <h5 class="mb-0"><i class="bi bi-calendar-month text-success me-2"></i>Thống kê tháng này</h5>
                    </div>
                    <div class="card-body">
                        <div class="row text-center g-4">
                            <div class="col-md-3">
                                <div class="month-stat-box" style="background: linear-gradient(135deg, rgba(102, 126, 234, 0.1) 0%, rgba(118, 75, 162, 0.1) 100%);">
                                    <h2 class="text-primary mb-1"><?php echo $stats['monthUsers']; ?></h2>
                                    <p class="text-muted mb-0 fw-semibold">Thành viên mới</p>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="month-stat-box green" style="background: linear-gradient(135deg, rgba(0, 176, 155, 0.1) 0%, rgba(150, 201, 61, 0.1) 100%);">
                                    <h2 class="text-success mb-1"><?php echo $stats['monthQuestions']; ?></h2>
                                    <p class="text-muted mb-0 fw-semibold">Câu hỏi mới</p>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="month-stat-box blue" style="background: linear-gradient(135deg, rgba(0, 198, 255, 0.1) 0%, rgba(0, 114, 255, 0.1) 100%);">
                                    <h2 class="text-info mb-1"><?php echo $stats['monthAnswers']; ?></h2>
                                    <p class="text-muted mb-0 fw-semibold">Câu trả lời mới</p>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="month-stat-box pink" style="background: linear-gradient(135deg, rgba(255, 106, 0, 0.1) 0%, rgba(238, 9, 121, 0.1) 100%);">
                                    <h2 class="text-danger mb-1"><?php echo $stats['acceptedAnswers']; ?></h2>
                                    <p class="text-muted mb-0 fw-semibold">Được chấp nhận</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Dữ liệu từ PHP
    const monthlyData = {
        labels: <?php echo json_encode(array_map(function($item) {
            return date('m/Y', strtotime($item['thang'] . '-01'));
        }, $monthlyQuestions)); ?>,
        questions: <?php echo json_encode(array_column($monthlyQuestions, 'soCauHoi')); ?>,
        users: <?php echo json_encode(array_column($monthlyUsers, 'soUser')); ?>,
        answers: <?php echo json_encode(array_column($monthlyAnswers, 'soTraLoi')); ?>
    };

    const dailyData = {
        labels: <?php echo json_encode(array_map(function($item) {
            return date('d/m', strtotime($item['ngay']));
        }, $dailyStats)); ?>,
        questions: <?php echo json_encode(array_column($dailyStats, 'soCauHoi')); ?>,
        answers: <?php echo json_encode(array_column($dailyAnswers, 'soTraLoi')); ?>
    };

    const topTagsData = {
        labels: <?php echo json_encode(array_column($topTags, 'TENTHE')); ?>,
        data: <?php echo json_encode(array_column($topTags, 'SoCauHoi')); ?>
    };

    const userRolesData = {
        labels: <?php echo json_encode(array_column($userRoles, 'VAITRO')); ?>,
        data: <?php echo json_encode(array_map('intval', array_column($userRoles, 'soLuong'))); ?>
    };

    // 1. Line Chart - Hoạt động 6 tháng (Gradient)
    const ctx1 = document.getElementById('monthlyActivityChart').getContext('2d');
    const gradient1 = ctx1.createLinearGradient(0, 0, 0, 300);
    gradient1.addColorStop(0, 'rgba(102, 126, 234, 0.4)');
    gradient1.addColorStop(1, 'rgba(102, 126, 234, 0)');
    const gradient2 = ctx1.createLinearGradient(0, 0, 0, 300);
    gradient2.addColorStop(0, 'rgba(0, 176, 155, 0.4)');
    gradient2.addColorStop(1, 'rgba(0, 176, 155, 0)');
    const gradient3 = ctx1.createLinearGradient(0, 0, 0, 300);
    gradient3.addColorStop(0, 'rgba(238, 9, 121, 0.4)');
    gradient3.addColorStop(1, 'rgba(238, 9, 121, 0)');
    
    new Chart(ctx1, {
        type: 'line',
        data: {
            labels: monthlyData.labels,
            datasets: [{
                label: '📝 Câu hỏi',
                data: monthlyData.questions,
                borderColor: '#667eea',
                backgroundColor: gradient1,
                fill: true,
                tension: 0.4,
                borderWidth: 4,
                pointBackgroundColor: '#667eea',
                pointBorderColor: '#fff',
                pointBorderWidth: 3,
                pointRadius: 6,
                pointHoverRadius: 10
            }, {
                label: '💬 Câu trả lời',
                data: monthlyData.answers,
                borderColor: '#00b09b',
                backgroundColor: gradient2,
                fill: true,
                tension: 0.4,
                borderWidth: 4,
                pointBackgroundColor: '#00b09b',
                pointBorderColor: '#fff',
                pointBorderWidth: 3,
                pointRadius: 6,
                pointHoverRadius: 10
            }, {
                label: '👥 Thành viên mới',
                data: monthlyData.users,
                borderColor: '#ee0979',
                backgroundColor: gradient3,
                fill: true,
                tension: 0.4,
                borderWidth: 4,
                pointBackgroundColor: '#ee0979',
                pointBorderColor: '#fff',
                pointBorderWidth: 3,
                pointRadius: 6,
                pointHoverRadius: 10
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { 
                    position: 'top',
                    labels: { font: { size: 13, weight: 'bold' }, padding: 20 }
                }
            },
            scales: {
                y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.05)' } },
                x: { grid: { display: false } }
            },
            interaction: { intersect: false, mode: 'index' }
        }
    });

    // 2. Doughnut Chart - Trạng thái câu hỏi (Gradient colors)
    new Chart(document.getElementById('questionStatusChart'), {
        type: 'doughnut',
        data: {
            labels: ['✅ Đã trả lời', '⏳ Chưa trả lời', '🏆 Đã chấp nhận'],
            datasets: [{
                data: [<?php echo $stats['answeredQuestions']; ?>, <?php echo $stats['unansweredQuestions']; ?>, <?php echo $stats['acceptedAnswers']; ?>],
                backgroundColor: ['#00b09b', '#ff6a00', '#667eea'],
                borderWidth: 4,
                borderColor: '#fff',
                hoverOffset: 20,
                hoverBorderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '60%',
            plugins: {
                legend: { display: false }
            },
            animation: {
                animateRotate: true,
                animateScale: true
            }
        }
    });

    // 3. Bar Chart - 7 ngày gần nhất (Gradient bars)
    const ctx3 = document.getElementById('dailyActivityChart').getContext('2d');
    const barGradient1 = ctx3.createLinearGradient(0, 0, 0, 300);
    barGradient1.addColorStop(0, '#667eea');
    barGradient1.addColorStop(1, '#764ba2');
    const barGradient2 = ctx3.createLinearGradient(0, 0, 0, 300);
    barGradient2.addColorStop(0, '#00b09b');
    barGradient2.addColorStop(1, '#96c93d');
    
    new Chart(ctx3, {
        type: 'bar',
        data: {
            labels: dailyData.labels,
            datasets: [{
                label: '📝 Câu hỏi',
                data: dailyData.questions,
                backgroundColor: barGradient1,
                borderRadius: 12,
                barThickness: 25,
                borderSkipped: false
            }, {
                label: '💬 Câu trả lời',
                data: dailyData.answers,
                backgroundColor: barGradient2,
                borderRadius: 12,
                barThickness: 25,
                borderSkipped: false
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { 
                    position: 'top',
                    labels: { font: { size: 13, weight: 'bold' }, padding: 20 }
                }
            },
            scales: {
                y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.05)' } },
                x: { grid: { display: false } }
            }
        }
    });

    // 4. Pie Chart - Vai trò người dùng (Vibrant colors)
    new Chart(document.getElementById('userRolesChart'), {
        type: 'pie',
        data: {
            labels: userRolesData.labels,
            datasets: [{
                data: userRolesData.data,
                backgroundColor: ['#ee0979', '#ff6a00', '#667eea', '#00b09b'],
                borderWidth: 4,
                borderColor: '#fff',
                hoverOffset: 25,
                hoverBorderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { 
                    position: 'bottom',
                    labels: { font: { size: 12, weight: 'bold' }, padding: 15 }
                }
            },
            animation: {
                animateRotate: true,
                animateScale: true
            }
        }
    });

    // 5. Horizontal Bar Chart - Top Tags (Rainbow colors)
    new Chart(document.getElementById('topTagsChart'), {
        type: 'bar',
        data: {
            labels: topTagsData.labels,
            datasets: [{
                label: '📊 Số câu hỏi',
                data: topTagsData.data,
                backgroundColor: [
                    '#667eea', '#00b09b', '#ee0979', '#00c6ff', 
                    '#f6d365', '#ff6a00', '#96c93d', '#764ba2',
                    '#fda085', '#17a2b8'
                ],
                borderRadius: 10,
                barThickness: 28,
                borderSkipped: false
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                x: { 
                    beginAtZero: true, 
                    grid: { color: 'rgba(0,0,0,0.05)' },
                    ticks: { font: { weight: 'bold' } }
                },
                y: { 
                    grid: { display: false },
                    ticks: { font: { size: 12, weight: 'bold' } }
                }
            }
        }
    });
});
</script>

<?php require_once '../includes/admin_footer.php'; ?>
