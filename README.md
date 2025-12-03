# 🎓 Diễn Đàn Chuyên Ngành - Forum Q&A System

Hệ thống diễn đàn hỏi đáp chuyên nghiệp với giao diện UX/UI hiện đại, được xây dựng bằng PHP, MySQL và Bootstrap 5.

## ✨ Tính năng chính

### 🔐 Hệ thống xác thực
- ✅ Đăng ký tài khoản với validation đầy đủ
- ✅ Đăng nhập với remember me
- ✅ Phân quyền: Admin, Moderator, User
- ✅ Quản lý session an toàn

### 👥 Trang người dùng
- ✅ Dashboard cá nhân với thống kê
- ✅ Quản lý câu hỏi của tôi
- ✅ Quản lý câu trả lời
- ✅ Chỉnh sửa profile
- ✅ Hệ thống điểm và huy hiệu

### 🛡️ Trang Admin
- ✅ Dashboard với thống kê tổng quan
- ✅ Quản lý người dùng (CRUD)
- ✅ Quản lý câu hỏi
- ✅ Quản lý tags và chuyên ngành
- ✅ Báo cáo và thống kê
- ✅ Sidebar navigation chuyên nghiệp

### 🎨 Giao diện UX/UI
- ✅ Design hiện đại với gradient và animations
- ✅ Responsive hoàn toàn (Mobile, Tablet, Desktop)
- ✅ Smooth transitions và hover effects
- ✅ Loading states và feedback
- ✅ Toast notifications
- ✅ Modal dialogs

### 💬 Chức năng diễn đàn
- ✅ Đặt câu hỏi với editor
- ✅ Trả lời câu hỏi
- ✅ Vote up/down
- ✅ Comment và thảo luận
- ✅ Tags và categories
- ✅ Tìm kiếm nâng cao
- ✅ Lượt xem và thống kê

## 🚀 Cài đặt

### Yêu cầu hệ thống
- PHP 7.4 trở lên
- MySQL 5.7 trở lên / MariaDB
- XAMPP / WAMP / LAMP
- Web browser hiện đại

### Các bước cài đặt

1. **Clone hoặc tải project về**
   ```bash
   git clone [repository-url]
   cd diendan_hoidap
   ```

2. **Import database**
   - Mở phpMyAdmin: `http://localhost/phpmyadmin`
   - Tạo database mới tên `diendan_hoidap`
   - Import file `database_mysql.sql`

3. **Cấu hình database**
   - Mở file `config/database.php`
   - Chỉnh sửa thông tin kết nối nếu cần:
   ```php
   define('DB_SERVER', 'localhost');
   define('DB_PORT', '3306');
   define('DB_USERNAME', 'root');
   define('DB_PASSWORD', '');
   define('DB_NAME', 'diendan_hoidap');
   ```

4. **Chạy ứng dụng**
   - Start Apache và MySQL trong XAMPP
   - Truy cập: `http://localhost/diendan_hoidap`

## 👤 Tài khoản demo

### Admin
- Username: `admin`
- Password: `123456`
- Quyền: Quản trị viên toàn quyền
- Truy cập: `http://localhost/diendan_hoidap/admin/dashboard.php`

### User
- Username: `nguyenvana`
- Password: `123456`
- Quyền: Người dùng thông thường
- Truy cập: `http://localhost/diendan_hoidap/user/dashboard.php`

## 📁 Cấu trúc thư mục

```
diendan_hoidap/
├── admin/                  # Trang quản trị
│   ├── dashboard.php      # Dashboard admin
│   ├── users.php          # Quản lý users
│   ├── questions.php      # Quản lý câu hỏi
│   └── tags.php           # Quản lý tags
├── user/                   # Trang người dùng
│   └── dashboard.php      # Dashboard user
├── assets/
│   ├── css/
│   │   └── style.css      # CSS chính với animations
│   └── js/
│       └── main.js        # JavaScript chính
├── config/
│   ├── database.php       # Cấu hình database
│   └── session.php        # Quản lý session
├── includes/
│   ├── header.php         # Header chung
│   ├── footer.php         # Footer chung
│   ├── admin_header.php   # Header admin
│   └── admin_footer.php   # Footer admin
├── index.php              # Trang chủ
├── login.php              # Đăng nhập
├── register.php           # Đăng ký
├── profile.php            # Trang profile
├── questions.php          # Danh sách câu hỏi
├── question-detail.php    # Chi tiết câu hỏi
├── ask-question.php       # Đặt câu hỏi
└── database_mysql.sql     # Database schema
```

## 🎨 Tính năng giao diện

### Màu sắc chủ đạo
- **Primary**: `#667eea` → `#764ba2` (Gradient tím)
- **Success**: `#11998e` → `#38ef7d` (Gradient xanh lá)
- **Info**: `#4facfe` → `#00f2fe` (Gradient xanh dương)
- **Warning**: `#f093fb` → `#f5576c` (Gradient hồng)

### Animations
- Fade in / Slide up
- Hover effects
- Smooth transitions
- Loading spinners
- Pulse effects

### Components
- Modern cards với shadow
- Gradient buttons
- Floating labels
- Badge và tags
- Data tables
- Modal dialogs
- Toast notifications

## 🔧 Tùy chỉnh

### Thay đổi màu sắc
Chỉnh sửa trong `assets/css/style.css`:
```css
:root {
    --primary-color: #0d6efd;
    --gradient-primary: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}
```

### Thêm tính năng mới
1. Tạo file PHP mới trong thư mục tương ứng
2. Include header/footer phù hợp
3. Sử dụng các class CSS có sẵn
4. Thêm JavaScript nếu cần trong `main.js`

## 📱 Responsive Design

- **Mobile** (< 768px): Sidebar collapse, stack layout
- **Tablet** (768px - 992px): 2 columns layout
- **Desktop** (> 992px): Full layout với sidebar

## 🔒 Bảo mật

- ✅ Prepared statements (PDO) chống SQL Injection
- ✅ Password hashing với MD5 (nên nâng cấp lên bcrypt)
- ✅ Session management
- ✅ XSS protection với htmlspecialchars()
- ✅ Input validation
- ⚠️ Nên thêm CSRF tokens

## 🚧 Roadmap

- [ ] Nâng cấp password hashing lên bcrypt
- [ ] Thêm CSRF tokens
- [ ] Rich text editor (TinyMCE/CKEditor)
- [ ] Upload ảnh
- [ ] Notification system
- [ ] Email verification
- [ ] Social login
- [ ] API RESTful
- [ ] Real-time chat
- [ ] Dark mode toggle

## 🛠️ Công nghệ sử dụng

- **Backend**: PHP 7.4+ với PDO
- **Database**: MySQL 5.7+ / MariaDB
- **Frontend**: Bootstrap 5.3, HTML5, CSS3
- **JavaScript**: Vanilla JS (ES6+)
- **Icons**: Bootstrap Icons 1.11
- **Fonts**: Inter, Segoe UI

## 📄 License

MIT License - Tự do sử dụng cho mục đích học tập và thương mại

## 👨‍💻 Hỗ trợ

Nếu gặp vấn đề, vui lòng:
1. Kiểm tra database đã import đúng chưa
2. Kiểm tra Apache và MySQL đã start chưa
3. Kiểm tra cấu hình trong `config/database.php`
4. Xem log lỗi trong XAMPP

## 📸 Screenshots

### Trang đăng nhập
- Giao diện gradient hiện đại
- Form validation
- Remember me functionality

### Admin Dashboard
- Thống kê tổng quan với cards gradient
- Sidebar navigation chuyên nghiệp
- Quản lý users, questions, tags

### User Dashboard
- Profile card với avatar
- Thống kê cá nhân
- Danh sách câu hỏi và trả lời

---

**Phát triển bởi:** Diễn Đàn Chuyên Ngành Team  
**Version:** 1.0.0  
**Ngày cập nhật:** 2024
