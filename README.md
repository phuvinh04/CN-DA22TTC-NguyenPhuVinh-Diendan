# 🎓 Diễn Đàn Hỏi Đáp Chuyên Ngành

Hệ thống diễn đàn hỏi đáp chuyên nghiệp với tính năng Đánh giá sao và Vinh danh tự động, được xây dựng bằng PHP, MySQL và Bootstrap 5.

## ✨ Tính năng chính

### 🔐 Hệ thống xác thực
- Đăng ký tài khoản với validation đầy đủ
- Đăng nhập thường và đăng nhập Google OAuth 2.0
- Phân quyền 3 cấp: Admin, Moderator, User
- Quản lý session an toàn

### � Chứnc năng diễn đàn
- Đặt câu hỏi với hỗ trợ code block và upload ảnh
- Trả lời câu hỏi
- Đánh giá sao (1-5 sao) cho câu hỏi và câu trả lời
- Chấp nhận câu trả lời hay nhất
- Tags phân loại theo chuyên ngành
- Tìm kiếm và lọc câu hỏi
- Báo cáo vi phạm

### 🏆 Hệ thống Vinh danh (Gamification)
- Điểm uy tín tự động cộng/trừ theo hoạt động
- 20 huy hiệu với điều kiện đạt được khác nhau
- Bảng xếp hạng Top thành viên
- Chuỗi đăng nhập liên tiếp

### 👥 Trang người dùng
- Hồ sơ cá nhân với avatar và tiểu sử
- Quản lý câu hỏi của tôi
- Quản lý câu trả lời của tôi
- Lịch sử điểm chi tiết
- Thông báo hệ thống
- Chỉnh sửa hồ sơ và đổi mật khẩu

### �️ Tứrang Admin
- Dashboard thống kê tổng quan
- Quản lý người dùng (khóa/mở khóa)
- Duyệt câu hỏi và câu trả lời
- Quản lý tags và chuyên ngành
- Xử lý báo cáo vi phạm
- Cấp huy hiệu hàng loạt
- Thống kê biểu đồ

### 🎨 Giao diện UX/UI
- Design hiện đại với gradient và animations
- Responsive hoàn toàn (Mobile, Tablet, Desktop)
- Smooth transitions và hover effects
- Toast notifications

## 🎨 Bảng màu giao diện

### Màu chủ đạo
- **Primary (Xanh dương)**: `#3b82f6` - Dùng cho navbar, header, các thành phần chính
- **Accent (Cam)**: `#f97316` - Dùng cho nút CTA, highlights, điểm nhấn

### Gradient
- **Gradient Primary**: `linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%)` - Navbar, hero section
- **Gradient Warm**: `linear-gradient(135deg, #f97316 0%, #ea580c 100%)` - Nút chính, CTA
- **Gradient Success**: `linear-gradient(135deg, #22c55e 0%, #16a34a 100%)` - Trạng thái thành công
- **Gradient Sunset**: `linear-gradient(135deg, #f97316 0%, #fbbf24 100%)` - Warning, hạng nhất

### Màu ngữ nghĩa
- **Success (Xanh lá)**: `#16a34a` - Thành công, câu trả lời được chấp nhận
- **Warning (Vàng)**: `#f59e0b` - Cảnh báo, đánh giá sao
- **Error (Đỏ)**: `#dc2626` - Lỗi, xóa
- **Info (Xanh nhạt)**: `#0284c7` - Thông tin

### Màu nền
- **Background**: `#fafaf9` với gradient overlay nhẹ
- **Card**: `#ffffff` với border `#e7e5e4`
- **Gray scale**: Từ `#fafaf9` đến `#1c1917`

## 🚀 Cài đặt

### Yêu cầu hệ thống
- PHP 7.4 trở lên
- MySQL 5.7 trở lên
- XAMPP (Apache + MySQL + PHP)
- Web browser hiện đại

### Các bước cài đặt

1. **Clone hoặc tải project về**
   ```bash
   git clone [repository-url]
   cd diendan_hoidap
   ```

2. **Import database**
   - Mở phpMyAdmin hoặc MySQL Workbench
   - Tạo database mới tên `ForumDB`
   - Import file `database.sql`

3. **Cấu hình database**
   - Mở file `config/database.php`
   - Chỉnh sửa thông tin kết nối:
   ```php
   define('DB_SERVER', 'localhost');
   define('DB_NAME', 'ForumDB');
   define('DB_USERNAME', 'sa');
   define('DB_PASSWORD', 'your_password');
   ```

4. **Cấu hình Google OAuth (tùy chọn)**
   - Mở file `config/google_config.php`
   - Nhập Client ID và Client Secret từ Google Console

5. **Chạy ứng dụng**
   - Start Apache và MySQL trong XAMPP
   - Truy cập: `http://localhost/diendan_hoidap`

## 👤 Tài khoản demo

### Admin
- Username: `admin`
- Password: `123456`
- Truy cập: `/admin/dashboard.php`

### User
- Username: `nguyenvana`
- Password: `123654`

## 📁 Cấu trúc thư mục

```
diendan_hoidap/
├── admin/                  # Trang quản trị
│   ├── dashboard.php
│   ├── users.php
│   ├── questions.php
│   ├── answers.php
│   ├── tags.php
│   ├── reports.php
│   ├── statistics.php
│   └── award-all-badges.php
├── user/                   # Trang người dùng
│   ├── my-questions.php
│   ├── my-answers.php
│   ├── my-points.php
│   ├── notifications.php
│   └── edit-profile.php
├── api/                    # API endpoints
│   ├── rate.php
│   ├── accept-answer.php
│   └── user.php
├── assets/
│   ├── css/style.css
│   └── js/main.js
├── config/
│   ├── database.php
│   └── google_config.php
├── includes/
│   ├── header.php
│   ├── footer.php
│   ├── badge_helper.php
│   └── notification_helper.php
├── docs/                   # Tài liệu
├── index.php
├── login.php
├── register.php
├── questions.php
├── question-detail.php
├── ask-question.php
├── profile.php
├── leaderboard.php
├── tags.php
├── search.php
├── users.php
├── points-system.php
└── database.sql
```

## 🔧 Công nghệ sử dụng

- **Backend**: PHP 7.4+ với PDO (MySQL driver)
- **Database**: MySQL 5.7+
- **Frontend**: Bootstrap 5.3, HTML5, CSS3
- **JavaScript**: Vanilla JS (ES6+)
- **Icons**: Bootstrap Icons 1.11
- **Authentication**: Google OAuth 2.0
- **Fonts**: Inter, Segoe UI

## 🔒 Bảo mật

- Prepared statements (PDO) chống SQL Injection
- Password hashing với MD5
- Session management
- XSS protection với htmlspecialchars()
- Input validation

## 📄 License

MIT License - Tự do sử dụng cho mục đích học tập

---

**Phát triển bởi:** Nguyễn Phú Vinh
