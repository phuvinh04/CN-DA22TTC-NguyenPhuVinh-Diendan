# 📦 Hướng dẫn cài đặt chi tiết

## Bước 1: Cài đặt XAMPP

### Windows
1. Tải XAMPP từ: https://www.apachefriends.org/download.html
2. Chọn phiên bản PHP 7.4 hoặc cao hơn
3. Chạy file cài đặt và làm theo hướng dẫn
4. Cài đặt vào thư mục mặc định: `C:\xampp`

### macOS
```bash
# Sử dụng Homebrew
brew install --cask xampp
```

### Linux
```bash
# Ubuntu/Debian
wget https://www.apachefriends.org/xampp-files/[version]/xampp-linux-x64-[version]-installer.run
chmod +x xampp-linux-x64-[version]-installer.run
sudo ./xampp-linux-x64-[version]-installer.run
```

## Bước 2: Khởi động XAMPP

1. Mở **XAMPP Control Panel**
2. Click **Start** cho **Apache**
3. Click **Start** cho **MySQL**
4. Đảm bảo cả hai đều hiển thị màu xanh

### Kiểm tra cài đặt
- Mở trình duyệt và truy cập: `http://localhost`
- Bạn sẽ thấy trang chào mừng của XAMPP

## Bước 3: Copy source code

### Cách 1: Tải trực tiếp
1. Tải file ZIP của project
2. Giải nén vào thư mục: `C:\xampp\htdocs\diendan_hoidap`

### Cách 2: Clone từ Git
```bash
cd C:\xampp\htdocs
git clone [repository-url] diendan_hoidap
```

## Bước 4: Tạo Database

### Sử dụng phpMyAdmin
1. Mở trình duyệt và truy cập: `http://localhost/phpmyadmin`
2. Click tab **"Databases"**
3. Nhập tên database: `diendan_hoidap`
4. Chọn Collation: `utf8mb4_unicode_ci`
5. Click **"Create"**

### Import dữ liệu
1. Click vào database `diendan_hoidap` vừa tạo
2. Click tab **"Import"**
3. Click **"Choose File"** và chọn file `database_mysql.sql`
4. Click **"Go"** để import
5. Đợi cho đến khi thấy thông báo thành công

### Sử dụng Command Line (Tùy chọn)
```bash
# Mở Command Prompt
cd C:\xampp\mysql\bin

# Đăng nhập MySQL
mysql -u root -p

# Tạo database
CREATE DATABASE diendan_hoidap CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

# Sử dụng database
USE diendan_hoidap;

# Import file SQL
SOURCE C:/xampp/htdocs/diendan_hoidap/database_mysql.sql;

# Thoát
EXIT;
```

## Bước 5: Cấu hình kết nối Database

Mở file `config/database.php` và kiểm tra cấu hình:

```php
<?php
define('DB_SERVER', 'localhost');
define('DB_PORT', '3306');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');  // Mặc định XAMPP để trống
define('DB_NAME', 'diendan_hoidap');
```

### Nếu bạn đã đặt mật khẩu cho MySQL:
```php
define('DB_PASSWORD', 'your_password_here');
```

### Nếu MySQL chạy trên port khác:
```php
define('DB_PORT', '3307');  // Hoặc port bạn đã cấu hình
```

## Bước 6: Kiểm tra quyền thư mục

### Windows
Thư mục `C:\xampp\htdocs\diendan_hoidap` cần có quyền đọc/ghi

### Linux/macOS
```bash
sudo chmod -R 755 /opt/lampp/htdocs/diendan_hoidap
sudo chown -R daemon:daemon /opt/lampp/htdocs/diendan_hoidap
```

## Bước 7: Truy cập website

Mở trình duyệt và truy cập:
```
http://localhost/diendan_hoidap
```

Bạn sẽ thấy trang chủ của diễn đàn!

## Bước 8: Đăng nhập

### Tài khoản Admin
- URL: `http://localhost/diendan_hoidap/login.php`
- Username: `admin`
- Password: `123456`

Sau khi đăng nhập, bạn sẽ được chuyển đến Admin Dashboard:
```
http://localhost/diendan_hoidap/admin/dashboard.php
```

### Tài khoản User
- Username: `nguyenvana`
- Password: `123456`

## 🔧 Xử lý sự cố

### Lỗi: "Connection refused"
**Nguyên nhân:** MySQL chưa khởi động

**Giải pháp:**
1. Mở XAMPP Control Panel
2. Click Start cho MySQL
3. Kiểm tra log nếu không start được

### Lỗi: "Access denied for user 'root'@'localhost'"
**Nguyên nhân:** Mật khẩu MySQL không đúng

**Giải pháp:**
1. Kiểm tra mật khẩu trong `config/database.php`
2. Reset mật khẩu MySQL nếu cần:
```bash
cd C:\xampp\mysql\bin
mysql -u root
UPDATE mysql.user SET Password=PASSWORD('') WHERE User='root';
FLUSH PRIVILEGES;
```

### Lỗi: "Database 'diendan_hoidap' doesn't exist"
**Nguyên nhân:** Chưa tạo database hoặc import SQL

**Giải pháp:**
1. Truy cập phpMyAdmin
2. Tạo database `diendan_hoidap`
3. Import file `database_mysql.sql`

### Lỗi: "Port 80 already in use"
**Nguyên nhân:** Port 80 đã được sử dụng bởi ứng dụng khác

**Giải pháp:**
1. Mở XAMPP Control Panel
2. Click Config cho Apache
3. Chọn `httpd.conf`
4. Tìm `Listen 80` và đổi thành `Listen 8080`
5. Tìm `ServerName localhost:80` và đổi thành `ServerName localhost:8080`
6. Save và restart Apache
7. Truy cập: `http://localhost:8080/diendan_hoidap`

### Lỗi: "Page not found" hoặc 404
**Nguyên nhân:** Đường dẫn không đúng

**Giải pháp:**
1. Kiểm tra thư mục đã copy đúng vào `htdocs` chưa
2. Đảm bảo tên thư mục là `diendan_hoidap`
3. Truy cập đúng URL: `http://localhost/diendan_hoidap`

### Lỗi: CSS/JS không load
**Nguyên nhân:** Đường dẫn tuyệt đối không đúng

**Giải pháp:**
1. Kiểm tra file `.htaccess` có tồn tại không
2. Enable mod_rewrite trong Apache:
   - Mở `httpd.conf`
   - Tìm `#LoadModule rewrite_module modules/mod_rewrite.so`
   - Bỏ dấu `#` ở đầu dòng
   - Restart Apache

## 📝 Checklist sau khi cài đặt

- [ ] XAMPP đã cài đặt và chạy
- [ ] Apache và MySQL đang chạy (màu xanh)
- [ ] Database `diendan_hoidap` đã được tạo
- [ ] File SQL đã import thành công
- [ ] File `config/database.php` đã cấu hình đúng
- [ ] Truy cập `http://localhost/diendan_hoidap` thành công
- [ ] Đăng nhập với tài khoản admin thành công
- [ ] Trang admin dashboard hiển thị đúng

## 🎉 Hoàn tất!

Bây giờ bạn đã có thể:
- Đăng nhập với tài khoản admin
- Quản lý người dùng, câu hỏi, tags
- Xem thống kê và báo cáo
- Đăng ký tài khoản mới
- Đặt câu hỏi và trả lời

## 📚 Tài liệu tham khảo

- [XAMPP Documentation](https://www.apachefriends.org/docs/)
- [PHP Manual](https://www.php.net/manual/en/)
- [MySQL Documentation](https://dev.mysql.com/doc/)
- [Bootstrap 5 Documentation](https://getbootstrap.com/docs/5.3/)

## 💡 Tips

1. **Backup thường xuyên:** Export database định kỳ
2. **Đổi mật khẩu admin:** Sau khi cài đặt xong
3. **Cập nhật PHP:** Sử dụng phiên bản PHP mới nhất
4. **Enable error reporting:** Trong quá trình phát triển
5. **Sử dụng HTTPS:** Trong môi trường production

## 🆘 Cần trợ giúp?

Nếu gặp vấn đề không giải quyết được:
1. Kiểm tra log lỗi trong XAMPP
2. Xem file error.log của Apache
3. Kiểm tra MySQL error log
4. Tìm kiếm lỗi trên Google/Stack Overflow
5. Liên hệ support team
