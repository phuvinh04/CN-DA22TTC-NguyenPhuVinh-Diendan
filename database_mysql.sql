-- =============================================
-- Database: DIENDAN_HOIDAP (MySQL Version)
-- DBMS: MySQL 5.7+ / MariaDB
-- Tạo cho XAMPP
-- =============================================

-- Tạo Database
CREATE DATABASE IF NOT EXISTS diendan_hoidap CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE diendan_hoidap;

-- =============================================
-- Table: VAITRO
-- =============================================
CREATE TABLE VAITRO (
   MAVAITRO VARCHAR(20) NOT NULL,
   TENVAITRO VARCHAR(50) NULL,
   PRIMARY KEY (MAVAITRO)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- Table: CHUYENNGHANH
-- =============================================
CREATE TABLE CHUYENNGHANH (
   MACN VARCHAR(20) NOT NULL,
   TENCN VARCHAR(100) NULL,
   PRIMARY KEY (MACN)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- Table: NGUOIDUNG
-- =============================================
CREATE TABLE NGUOIDUNG (
   MANGUOIDUNG VARCHAR(100) NOT NULL,
   TENDANGNHAP VARCHAR(50) NULL,
   EMAIL VARCHAR(100) NULL,
   MATKHAU VARCHAR(255) NULL,
   HOTEN VARCHAR(100) NULL,
   ANHDAIDIEN VARCHAR(255) NULL,
   TIEUSU TEXT NULL,
   DIEMDANHGIA INT DEFAULT 0,
   TRANGTHAI VARCHAR(20) DEFAULT 'active',
   NGAYTAO DATETIME DEFAULT CURRENT_TIMESTAMP,
   LANHOATDONGCUOI DATETIME NULL,
   PRIMARY KEY (MANGUOIDUNG),
   UNIQUE KEY idx_tendangnhap (TENDANGNHAP),
   UNIQUE KEY idx_email (EMAIL)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- Table: HUYHIEU
-- =============================================
CREATE TABLE HUYHIEU (
   MAHUYHIEU VARCHAR(100) NOT NULL,
   TENHUYHIEU VARCHAR(50) NULL,
   MOTA TEXT NULL,
   BIEUTUONG VARCHAR(255) NULL,
   LOAITIEUCHI VARCHAR(50) NULL,
   NGUONGTIEUCHI INT NULL,
   PRIMARY KEY (MAHUYHIEU)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- Table: TAG
-- =============================================
CREATE TABLE TAG (
   MATHE VARCHAR(100) NOT NULL,
   MACN VARCHAR(20) NULL,
   TENTHE VARCHAR(50) NULL,
   MOTA TEXT NULL,
   PRIMARY KEY (MATHE),
   FOREIGN KEY (MACN) REFERENCES CHUYENNGHANH(MACN)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- Table: COVT (Người dùng - Vai trò)
-- =============================================
CREATE TABLE COVT (
   MAVAITRO VARCHAR(20) NOT NULL,
   MANGUOIDUNG VARCHAR(100) NOT NULL,
   PRIMARY KEY (MAVAITRO, MANGUOIDUNG),
   FOREIGN KEY (MAVAITRO) REFERENCES VAITRO(MAVAITRO),
   FOREIGN KEY (MANGUOIDUNG) REFERENCES NGUOIDUNG(MANGUOIDUNG)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- Table: NHAN (Người dùng - Huy hiệu)
-- =============================================
CREATE TABLE NHAN (
   MANGUOIDUNG VARCHAR(100) NOT NULL,
   MAHUYHIEU VARCHAR(100) NOT NULL,
   PRIMARY KEY (MANGUOIDUNG, MAHUYHIEU),
   FOREIGN KEY (MANGUOIDUNG) REFERENCES NGUOIDUNG(MANGUOIDUNG),
   FOREIGN KEY (MAHUYHIEU) REFERENCES HUYHIEU(MAHUYHIEU)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- Table: CAUHOI
-- =============================================
CREATE TABLE CAUHOI (
   MACAUHOI VARCHAR(50) NOT NULL,
   MATHE VARCHAR(100) NOT NULL,
   TIEUDE VARCHAR(255) NULL,
   TRANGTHAI VARCHAR(20) DEFAULT 'open',
   LUOTXEM INT DEFAULT 0,
   PRIMARY KEY (MACAUHOI),
   FOREIGN KEY (MATHE) REFERENCES TAG(MATHE)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- Table: DAT (Người dùng đặt câu hỏi)
-- =============================================
CREATE TABLE DAT (
   MANGUOIDUNG VARCHAR(100) NOT NULL,
   MACAUHOI VARCHAR(50) NOT NULL,
   NOIDUNG TEXT NULL,
   NGAYDANG DATETIME DEFAULT CURRENT_TIMESTAMP,
   PRIMARY KEY (MANGUOIDUNG, MACAUHOI),
   FOREIGN KEY (MANGUOIDUNG) REFERENCES NGUOIDUNG(MANGUOIDUNG),
   FOREIGN KEY (MACAUHOI) REFERENCES CAUHOI(MACAUHOI)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- Table: TRALOI
-- =============================================
CREATE TABLE TRALOI (
   MACAUTRALOI VARCHAR(50) NOT NULL,
   MANGUOIDUNG VARCHAR(100) NOT NULL,
   MACAUHOI VARCHAR(50) NOT NULL,
   NOIDUNGTL TEXT NULL,
   NGAYTL DATETIME DEFAULT CURRENT_TIMESTAMP,
   PRIMARY KEY (MACAUTRALOI),
   FOREIGN KEY (MANGUOIDUNG) REFERENCES NGUOIDUNG(MANGUOIDUNG),
   FOREIGN KEY (MACAUHOI) REFERENCES CAUHOI(MACAUHOI)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- Table: VOTE
-- =============================================
CREATE TABLE VOTE (
   MAVOTE VARCHAR(50) NOT NULL,
   MANGUOIDUNG VARCHAR(100) NOT NULL,
   LOAIVOTE TINYINT NULL,
   NGAYTAO DATETIME DEFAULT CURRENT_TIMESTAMP,
   PRIMARY KEY (MAVOTE),
   FOREIGN KEY (MANGUOIDUNG) REFERENCES NGUOIDUNG(MANGUOIDUNG)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- Table: BINHCHONCAUHOI (Vote câu hỏi)
-- =============================================
CREATE TABLE BINHCHONCAUHOI (
   MAVOTE VARCHAR(50) NOT NULL,
   MACAUHOI VARCHAR(50) NOT NULL,
   PRIMARY KEY (MAVOTE, MACAUHOI),
   FOREIGN KEY (MAVOTE) REFERENCES VOTE(MAVOTE),
   FOREIGN KEY (MACAUHOI) REFERENCES CAUHOI(MACAUHOI)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- Table: BINHCHONCAUTRALOI (Vote câu trả lời)
-- =============================================
CREATE TABLE BINHCHONCAUTRALOI (
   MAVOTE VARCHAR(50) NOT NULL,
   MACAUTRALOI VARCHAR(50) NOT NULL,
   PRIMARY KEY (MAVOTE, MACAUTRALOI),
   FOREIGN KEY (MAVOTE) REFERENCES VOTE(MAVOTE),
   FOREIGN KEY (MACAUTRALOI) REFERENCES TRALOI(MACAUTRALOI)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- Table: LUOTXEM
-- =============================================
CREATE TABLE LUOTXEM (
   ID INT AUTO_INCREMENT NOT NULL,
   MACAUHOI VARCHAR(50) NOT NULL,
   MANGUOIDUNG VARCHAR(100) NULL,
   IP_ADDRESS VARCHAR(50) NULL,
   NGAYXEM DATE DEFAULT (CURRENT_DATE),
   PRIMARY KEY (ID),
   FOREIGN KEY (MACAUHOI) REFERENCES CAUHOI(MACAUHOI)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- DỮ LIỆU MẪU
-- =============================================

-- Vai trò
INSERT INTO VAITRO VALUES ('admin', 'Quản trị viên');
INSERT INTO VAITRO VALUES ('user', 'Người dùng');
INSERT INTO VAITRO VALUES ('moderator', 'Điều hành viên');

-- Chuyên ngành
INSERT INTO CHUYENNGHANH VALUES ('CN001', 'Công nghệ thông tin');
INSERT INTO CHUYENNGHANH VALUES ('CN002', 'Kỹ thuật phần mềm');
INSERT INTO CHUYENNGHANH VALUES ('CN003', 'Hệ thống thông tin');
INSERT INTO CHUYENNGHANH VALUES ('CN004', 'Mạng máy tính');
INSERT INTO CHUYENNGHANH VALUES ('CN005', 'Trí tuệ Nhân tạo');

-- Tags
INSERT INTO TAG VALUES ('TAG001', 'CN001', 'PHP', 'Ngôn ngữ lập trình PHP');
INSERT INTO TAG VALUES ('TAG002', 'CN001', 'JavaScript', 'Ngôn ngữ JavaScript');
INSERT INTO TAG VALUES ('TAG003', 'CN001', 'SQL', 'Cơ sở dữ liệu SQL');
INSERT INTO TAG VALUES ('TAG004', 'CN001', 'HTML/CSS', 'Thiết kế web');
INSERT INTO TAG VALUES ('TAG005', 'CN001', 'Python', 'Ngôn ngữ Python');
INSERT INTO TAG VALUES ('TAG006', 'CN002', 'Java', 'Ngôn ngữ Java');
INSERT INTO TAG VALUES ('TAG007', 'CN005', 'Machine Learning', 'Học máy');
INSERT INTO TAG VALUES ('TAG008', 'CN004', 'Network Security', 'Bảo mật mạng');

-- Huy hiệu
INSERT INTO HUYHIEU VALUES ('HH001', 'Người mới', 'Tham gia cộng đồng', '🌱', 'ngaythamgia', 0);
INSERT INTO HUYHIEU VALUES ('HH002', 'Nhiệt tình', 'Trả lời 10 câu hỏi', '🔥', 'cautraloi', 10);
INSERT INTO HUYHIEU VALUES ('HH003', 'Chuyên gia', 'Trả lời 50 câu hỏi', '⭐', 'cautraloi', 50);
INSERT INTO HUYHIEU VALUES ('HH004', 'Người hỏi', 'Đặt 10 câu hỏi', '❓', 'cauhoi', 10);
INSERT INTO HUYHIEU VALUES ('HH005', 'Được yêu thích', 'Nhận 100 lượt đánh giá', '🖤', 'vote', 100);
INSERT INTO HUYHIEU VALUES ('HH006', 'Huyền thoại', 'Đạt 1000 điểm', '🏆', 'diem', 1000);

-- Người dùng mẫu (mật khẩu: 123456 = e10adc3949ba59abbe56e057f20f883e)
INSERT INTO NGUOIDUNG (MANGUOIDUNG, TENDANGNHAP, EMAIL, MATKHAU, HOTEN, ANHDAIDIEN, TIEUSU, DIEMDANHGIA, TRANGTHAI) VALUES 
('ND001', 'admin', 'admin@gmail.com', 'e10adc3949ba59abbe56e057f20f883e', 'Quản trị viên', 'https://ui-avatars.com/api/?name=Admin&background=4f46e5&color=fff', 'Tôi là quản trị viên hệ thống', 100, 'active'),
('ND002', 'nguyenvana', 'vana@gmail.com', 'e10adc3949ba59abbe56e057f20f883e', 'Nguyễn Văn A', 'https://ui-avatars.com/api/?name=Van+A&background=10b981&color=fff', 'Sinh viên CNTT năm 3', 50, 'active'),
('ND003', 'tranthib', 'thib@gmail.com', 'e10adc3949ba59abbe56e057f20f883e', 'Trần Thị B', 'https://ui-avatars.com/api/?name=Thi+B&background=f59e0b&color=fff', 'Yêu thích lập trình web', 35, 'active'),
('ND004', 'levanc', 'vanc@gmail.com', 'e10adc3949ba59abbe56e057f20f883e', 'Lê Văn C', 'https://ui-avatars.com/api/?name=Van+C&background=ef4444&color=fff', 'Developer tại FPT', 80, 'active'),
('ND005', 'phamthid', 'thid@gmail.com', 'e10adc3949ba59abbe56e057f20f883e', 'Phạm Thị D', 'https://ui-avatars.com/api/?name=Thi+D&background=8b5cf6&color=fff', 'Fresher Python', 20, 'active'),
('ND006', 'hoangvane', 'vane@gmail.com', 'e10adc3949ba59abbe56e057f20f883e', 'Hoàng Văn E', 'https://ui-avatars.com/api/?name=Van+E&background=06b6d4&color=fff', 'Full-stack developer', 90, 'active'),
('ND007', 'ngothif', 'thif@gmail.com', 'e10adc3949ba59abbe56e057f20f883e', 'Ngô Thị F', 'https://ui-avatars.com/api/?name=Thi+F&background=ec4899&color=fff', 'UI/UX Designer', 45, 'active'),
('ND008', 'dangvang', 'vang@gmail.com', 'e10adc3949ba59abbe56e057f20f883e', 'Đặng Văn G', 'https://ui-avatars.com/api/?name=Van+G&background=14b8a6&color=fff', 'Backend Developer', 60, 'active'),
('ND009', 'vuuthih', 'thih@gmail.com', 'e10adc3949ba59abbe56e057f20f883e', 'Vũ Thị H', 'https://ui-avatars.com/api/?name=Thi+H&background=f97316&color=fff', 'Data Analyst', 30, 'active'),
('ND010', 'buivani', 'vani@gmail.com', 'e10adc3949ba59abbe56e057f20f883e', 'Bùi Văn I', 'https://ui-avatars.com/api/?name=Van+I&background=6366f1&color=fff', 'Mobile Developer', 55, 'active');

-- Phân quyền người dùng
INSERT INTO COVT VALUES ('admin', 'ND001');
INSERT INTO COVT VALUES ('moderator', 'ND004');
INSERT INTO COVT VALUES ('moderator', 'ND006');
INSERT INTO COVT VALUES ('user', 'ND002');
INSERT INTO COVT VALUES ('user', 'ND003');
INSERT INTO COVT VALUES ('user', 'ND005');
INSERT INTO COVT VALUES ('user', 'ND007');
INSERT INTO COVT VALUES ('user', 'ND008');
INSERT INTO COVT VALUES ('user', 'ND009');
INSERT INTO COVT VALUES ('user', 'ND010');

-- Người dùng nhận huy hiệu
INSERT INTO NHAN VALUES ('ND001', 'HH003');
INSERT INTO NHAN VALUES ('ND002', 'HH001');
INSERT INTO NHAN VALUES ('ND003', 'HH001');
INSERT INTO NHAN VALUES ('ND004', 'HH002');
INSERT INTO NHAN VALUES ('ND006', 'HH005');

-- Câu hỏi mẫu
INSERT INTO CAUHOI VALUES ('CH001', 'TAG001', 'Làm thế nào để kết nối PHP với MySQL?', 'open', 150);
INSERT INTO CAUHOI VALUES ('CH002', 'TAG002', 'Sự khác nhau giữa let, const và var trong JavaScript?', 'open', 200);
INSERT INTO CAUHOI VALUES ('CH003', 'TAG003', 'Cách tối ưu hóa câu truy vấn SQL chạy chậm?', 'open', 180);
INSERT INTO CAUHOI VALUES ('CH004', 'TAG004', 'Flexbox và Grid khác nhau như thế nào?', 'open', 120);
INSERT INTO CAUHOI VALUES ('CH005', 'TAG005', 'Cách sử dụng list comprehension trong Python?', 'open', 90);
INSERT INTO CAUHOI VALUES ('CH006', 'TAG001', 'Session và Cookie khác nhau như thế nào trong PHP?', 'open', 110);
INSERT INTO CAUHOI VALUES ('CH007', 'TAG002', 'Async/Await hoạt động như thế nào?', 'open', 175);
INSERT INTO CAUHOI VALUES ('CH008', 'TAG003', 'Khi nào nên dùng INDEX trong SQL?', 'closed', 140);
INSERT INTO CAUHOI VALUES ('CH009', 'TAG004', 'Responsive design là gì và làm thế nào để implement?', 'open', 95);
INSERT INTO CAUHOI VALUES ('CH010', 'TAG005', 'Django và Flask khác nhau như thế nào?', 'open', 130);

-- Liên kết người dùng đặt câu hỏi (bảng DAT)
INSERT INTO DAT VALUES ('ND002', 'CH001', 'Mình mới học PHP và muốn kết nối với database MySQL. Mình đã thử dùng mysqli nhưng bị lỗi. Ai có thể hướng dẫn mình cách kết nối đúng không?', NOW());
INSERT INTO DAT VALUES ('ND003', 'CH002', 'Mình thấy JavaScript có 3 cách khai báo biến là let, const và var. Khi nào thì dùng cái nào? Có ai giải thích giúp mình không?', NOW());
INSERT INTO DAT VALUES ('ND005', 'CH003', 'Câu truy vấn của mình chạy rất chậm khi bảng có nhiều dữ liệu. Có cách nào để tối ưu không?', NOW());
INSERT INTO DAT VALUES ('ND007', 'CH004', 'Mình đang học CSS và thấy có Flexbox và Grid. Hai cái này khác nhau chỗ nào? Khi nào dùng cái nào?', NOW());
INSERT INTO DAT VALUES ('ND009', 'CH005', 'Mình nghe nói list comprehension trong Python rất tiện. Ai có thể cho ví dụ cụ thể không?', NOW());
INSERT INTO DAT VALUES ('ND002', 'CH006', 'Session và Cookie đều dùng để lưu dữ liệu nhưng mình không hiểu rõ sự khác nhau. Ai giải thích giúp?', NOW());
INSERT INTO DAT VALUES ('ND003', 'CH007', 'Mình đang học về asynchronous trong JavaScript. Async/Await hoạt động như thế nào?', NOW());
INSERT INTO DAT VALUES ('ND005', 'CH008', 'Mình nghe nói INDEX giúp truy vấn nhanh hơn. Khi nào nên tạo INDEX?', NOW());
INSERT INTO DAT VALUES ('ND007', 'CH009', 'Website của mình không hiển thị đẹp trên điện thoại. Làm sao để responsive?', NOW());
INSERT INTO DAT VALUES ('ND009', 'CH010', 'Mình muốn học Python web framework. Nên chọn Django hay Flask?', NOW());

-- Câu trả lời mẫu
INSERT INTO TRALOI VALUES ('TL001', 'ND004', 'CH001', 'Bạn có thể dùng PDO để kết nối. Ví dụ:\n$conn = new PDO("mysql:host=localhost;dbname=test", "root", "");\nĐây là cách an toàn và được khuyến khích.', NOW());
INSERT INTO TRALOI VALUES ('TL002', 'ND006', 'CH001', 'Ngoài PDO, bạn cũng có thể dùng mysqli. Nhưng PDO linh hoạt hơn vì hỗ trợ nhiều database.', NOW());
INSERT INTO TRALOI VALUES ('TL003', 'ND004', 'CH002', '- var: phạm vi function, có thể khai báo lại\n- let: phạm vi block, không thể khai báo lại\n- const: phạm vi block, không thể thay đổi giá trị\nNên dùng const mặc định, let khi cần thay đổi.', NOW());
INSERT INTO TRALOI VALUES ('TL004', 'ND008', 'CH003', 'Một số cách tối ưu:\n1. Thêm INDEX cho cột hay tìm kiếm\n2. Tránh SELECT *\n3. Dùng EXPLAIN để phân tích query\n4. Cân nhắc phân trang khi lấy nhiều data', NOW());
INSERT INTO TRALOI VALUES ('TL005', 'ND006', 'CH004', 'Flexbox: layout 1 chiều (hàng hoặc cột)\nGrid: layout 2 chiều (hàng và cột)\nDùng Flexbox cho component nhỏ, Grid cho layout tổng thể.', NOW());
INSERT INTO TRALOI VALUES ('TL006', 'ND004', 'CH005', 'List comprehension giúp tạo list ngắn gọn:\nsquares = [x**2 for x in range(10)]\nTương đương vòng for nhưng gọn hơn.', NOW());
INSERT INTO TRALOI VALUES ('TL007', 'ND008', 'CH006', 'Session lưu trên server, Cookie lưu trên trình duyệt.\nSession an toàn hơn, Cookie có thể bị user chỉnh sửa.\nSession hết khi đóng trình duyệt, Cookie có thể set thời hạn.', NOW());
INSERT INTO TRALOI VALUES ('TL008', 'ND006', 'CH007', 'Async/Await là cú pháp để xử lý Promise dễ đọc hơn:\nasync function getData() {\n  const result = await fetch(url);\n  return result.json();\n}', NOW());
INSERT INTO TRALOI VALUES ('TL009', 'ND004', 'CH008', 'Nên tạo INDEX khi:\n- Cột thường dùng trong WHERE\n- Cột dùng để JOIN\n- Cột dùng trong ORDER BY\nKhông nên INDEX cột ít giá trị unique hoặc bảng nhỏ.', NOW());
INSERT INTO TRALOI VALUES ('TL010', 'ND008', 'CH009', 'Dùng media queries:\n@media (max-width: 768px) { ... }\nVà viewport meta tag:\n<meta name="viewport" content="width=device-width, initial-scale=1">', NOW());

-- Vote mẫu
INSERT INTO VOTE VALUES ('VT001', 'ND002', 1, NOW());
INSERT INTO VOTE VALUES ('VT002', 'ND003', 1, NOW());
INSERT INTO VOTE VALUES ('VT003', 'ND005', 1, NOW());
INSERT INTO VOTE VALUES ('VT004', 'ND007', 1, NOW());
INSERT INTO VOTE VALUES ('VT005', 'ND009', 1, NOW());
INSERT INTO VOTE VALUES ('VT006', 'ND002', 1, NOW());
INSERT INTO VOTE VALUES ('VT007', 'ND003', 1, NOW());
INSERT INTO VOTE VALUES ('VT008', 'ND005', 1, NOW());
INSERT INTO VOTE VALUES ('VT009', 'ND007', 1, NOW());
INSERT INTO VOTE VALUES ('VT010', 'ND009', 1, NOW());

-- Bình chọn câu hỏi
INSERT INTO BINHCHONCAUHOI VALUES ('VT001', 'CH001');
INSERT INTO BINHCHONCAUHOI VALUES ('VT002', 'CH001');
INSERT INTO BINHCHONCAUHOI VALUES ('VT003', 'CH002');
INSERT INTO BINHCHONCAUHOI VALUES ('VT004', 'CH003');
INSERT INTO BINHCHONCAUHOI VALUES ('VT005', 'CH004');

-- Bình chọn câu trả lời
INSERT INTO BINHCHONCAUTRALOI VALUES ('VT006', 'TL001');
INSERT INTO BINHCHONCAUTRALOI VALUES ('VT007', 'TL003');
INSERT INTO BINHCHONCAUTRALOI VALUES ('VT008', 'TL004');
INSERT INTO BINHCHONCAUTRALOI VALUES ('VT009', 'TL005');
INSERT INTO BINHCHONCAUTRALOI VALUES ('VT010', 'TL007');

-- Lượt xem mẫu
INSERT INTO LUOTXEM (MACAUHOI, MANGUOIDUNG, IP_ADDRESS) VALUES ('CH001', 'ND003', '192.168.1.1');
INSERT INTO LUOTXEM (MACAUHOI, MANGUOIDUNG, IP_ADDRESS) VALUES ('CH001', 'ND005', '192.168.1.2');
INSERT INTO LUOTXEM (MACAUHOI, MANGUOIDUNG, IP_ADDRESS) VALUES ('CH002', 'ND002', '192.168.1.3');
INSERT INTO LUOTXEM (MACAUHOI, MANGUOIDUNG, IP_ADDRESS) VALUES ('CH002', 'ND004', '192.168.1.4');
INSERT INTO LUOTXEM (MACAUHOI, MANGUOIDUNG, IP_ADDRESS) VALUES ('CH003', 'ND006', '192.168.1.5');

-- Hoàn tất
SELECT '✅ Tạo database diendan_hoidap thành công!' AS Message;




-- =============================================
-- Table: THONGBAO (Notifications)
-- =============================================
CREATE TABLE IF NOT EXISTS THONGBAO (
    MATHONGBAO VARCHAR(50) NOT NULL,
    MANGUOIDUNG VARCHAR(100) NOT NULL,
    LOAI VARCHAR(20) DEFAULT 'system',
    TIEUDE VARCHAR(255) NULL,
    NOIDUNG TEXT NULL,
    LINK VARCHAR(500) NULL,
    DADOC TINYINT DEFAULT 0,
    NGAYTAO DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (MATHONGBAO),
    FOREIGN KEY (MANGUOIDUNG) REFERENCES NGUOIDUNG(MANGUOIDUNG) ON DELETE CASCADE,
    INDEX idx_user_unread (MANGUOIDUNG, DADOC)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dữ liệu mẫu thông báo
INSERT INTO THONGBAO VALUES 
('TB001', 'ND002', 'answer', 'Có câu trả lời mới', 'Nguyễn Văn A đã trả lời câu hỏi của bạn', 'question.php?id=CH001', 0, NOW()),
('TB002', 'ND002', 'vote', 'Bạn nhận được đánh giá', 'Câu hỏi của bạn được đánh giá 5 sao', 'question.php?id=CH001', 0, NOW()),
('TB003', 'ND003', 'system', 'Chào mừng!', 'Chào mừng bạn đến với diễn đàn', NULL, 1, NOW());
