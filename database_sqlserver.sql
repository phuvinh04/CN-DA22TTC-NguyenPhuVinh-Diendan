-- =============================================
-- Database: DIENDAN_HOIDAP (SQL Server Version)
-- DBMS: Microsoft SQL Server
-- =============================================

-- Tạo Database
IF NOT EXISTS (SELECT * FROM sys.databases WHERE name = 'diendan_hoidap')
BEGIN
    CREATE DATABASE diendan_hoidap COLLATE Vietnamese_CI_AS;
END
GO

USE diendan_hoidap;
GO

-- =============================================
-- Table: VAITRO
-- =============================================
IF NOT EXISTS (SELECT * FROM sys.tables WHERE name = 'VAITRO')
CREATE TABLE VAITRO (
   MAVAITRO NVARCHAR(20) NOT NULL,
   TENVAITRO NVARCHAR(50) NULL,
   PRIMARY KEY (MAVAITRO)
);
GO

-- =============================================
-- Table: CHUYENNGHANH
-- =============================================
IF NOT EXISTS (SELECT * FROM sys.tables WHERE name = 'CHUYENNGHANH')
CREATE TABLE CHUYENNGHANH (
   MACN NVARCHAR(20) NOT NULL,
   TENCN NVARCHAR(100) NULL,
   PRIMARY KEY (MACN)
);
GO

-- =============================================
-- Table: NGUOIDUNG
-- =============================================
IF NOT EXISTS (SELECT * FROM sys.tables WHERE name = 'NGUOIDUNG')
CREATE TABLE NGUOIDUNG (
   MANGUOIDUNG NVARCHAR(100) NOT NULL,
   TENDANGNHAP NVARCHAR(50) NULL,
   EMAIL NVARCHAR(100) NULL,
   MATKHAU NVARCHAR(255) NULL,
   HOTEN NVARCHAR(100) NULL,
   ANHDAIDIEN NVARCHAR(255) NULL,
   TIEUSU NVARCHAR(MAX) NULL,
   DIEMDANHGIA INT DEFAULT 0,
   TRANGTHAI NVARCHAR(20) DEFAULT 'active',
   NGAYTAO DATETIME DEFAULT GETDATE(),
   LANHOATDONGCUOI DATETIME NULL,
   LOGIN_STREAK INT DEFAULT 0,
   LAST_LOGIN_DATE DATE NULL,
   KHUNG_AVATAR NVARCHAR(100) NULL,
   PRIMARY KEY (MANGUOIDUNG)
);
GO

-- Unique constraints
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = 'idx_tendangnhap')
CREATE UNIQUE INDEX idx_tendangnhap ON NGUOIDUNG(TENDANGNHAP) WHERE TENDANGNHAP IS NOT NULL;
GO

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = 'idx_email')
CREATE UNIQUE INDEX idx_email ON NGUOIDUNG(EMAIL) WHERE EMAIL IS NOT NULL;
GO

-- =============================================
-- Table: HUYHIEU
-- =============================================
IF NOT EXISTS (SELECT * FROM sys.tables WHERE name = 'HUYHIEU')
CREATE TABLE HUYHIEU (
   MAHUYHIEU NVARCHAR(100) NOT NULL,
   TENHUYHIEU NVARCHAR(50) NULL,
   MOTA NVARCHAR(MAX) NULL,
   BIEUTUONG NVARCHAR(255) NULL,
   LOAITIEUCHI NVARCHAR(50) NULL,
   NGUONGTIEUCHI INT NULL,
   CAPDO INT DEFAULT 1,
   MAUKHUNG NVARCHAR(20) DEFAULT '#cbd5e1',
   PRIMARY KEY (MAHUYHIEU)
);
GO

-- =============================================
-- Table: TAG
-- =============================================
IF NOT EXISTS (SELECT * FROM sys.tables WHERE name = 'TAG')
CREATE TABLE TAG (
   MATHE NVARCHAR(100) NOT NULL,
   MACN NVARCHAR(20) NULL,
   TENTHE NVARCHAR(50) NULL,
   MOTA NVARCHAR(MAX) NULL,
   PRIMARY KEY (MATHE),
   FOREIGN KEY (MACN) REFERENCES CHUYENNGHANH(MACN)
);
GO

-- =============================================
-- Table: COVT (Người dùng - Vai trò)
-- =============================================
IF NOT EXISTS (SELECT * FROM sys.tables WHERE name = 'COVT')
CREATE TABLE COVT (
   MAVAITRO NVARCHAR(20) NOT NULL,
   MANGUOIDUNG NVARCHAR(100) NOT NULL,
   PRIMARY KEY (MAVAITRO, MANGUOIDUNG),
   FOREIGN KEY (MAVAITRO) REFERENCES VAITRO(MAVAITRO),
   FOREIGN KEY (MANGUOIDUNG) REFERENCES NGUOIDUNG(MANGUOIDUNG)
);
GO

-- =============================================
-- Table: NHAN (Người dùng - Huy hiệu)
-- =============================================
IF NOT EXISTS (SELECT * FROM sys.tables WHERE name = 'NHAN')
CREATE TABLE NHAN (
   MANGUOIDUNG NVARCHAR(100) NOT NULL,
   MAHUYHIEU NVARCHAR(100) NOT NULL,
   PRIMARY KEY (MANGUOIDUNG, MAHUYHIEU),
   FOREIGN KEY (MANGUOIDUNG) REFERENCES NGUOIDUNG(MANGUOIDUNG),
   FOREIGN KEY (MAHUYHIEU) REFERENCES HUYHIEU(MAHUYHIEU)
);
GO

-- =============================================
-- Table: CAUHOI
-- =============================================
IF NOT EXISTS (SELECT * FROM sys.tables WHERE name = 'CAUHOI')
CREATE TABLE CAUHOI (
   MACAUHOI NVARCHAR(50) NOT NULL,
   MATHE NVARCHAR(100) NOT NULL,
   TIEUDE NVARCHAR(255) NULL,
   TRANGTHAI NVARCHAR(20) DEFAULT 'open',
   LUOTXEM INT DEFAULT 0,
   CAUTRALOI_CHAPNHAN NVARCHAR(50) NULL,
   PRIMARY KEY (MACAUHOI),
   FOREIGN KEY (MATHE) REFERENCES TAG(MATHE)
);
GO

-- =============================================
-- Table: DAT (Người dùng đặt câu hỏi)
-- =============================================
IF NOT EXISTS (SELECT * FROM sys.tables WHERE name = 'DAT')
CREATE TABLE DAT (
   MANGUOIDUNG NVARCHAR(100) NOT NULL,
   MACAUHOI NVARCHAR(50) NOT NULL,
   NOIDUNG NVARCHAR(MAX) NULL,
   NGAYDANG DATETIME DEFAULT GETDATE(),
   HINHANH NVARCHAR(MAX) NULL,
   PRIMARY KEY (MANGUOIDUNG, MACAUHOI),
   FOREIGN KEY (MANGUOIDUNG) REFERENCES NGUOIDUNG(MANGUOIDUNG),
   FOREIGN KEY (MACAUHOI) REFERENCES CAUHOI(MACAUHOI)
);
GO

-- =============================================
-- Table: TRALOI
-- =============================================
IF NOT EXISTS (SELECT * FROM sys.tables WHERE name = 'TRALOI')
CREATE TABLE TRALOI (
   MACAUTRALOI NVARCHAR(50) NOT NULL,
   MANGUOIDUNG NVARCHAR(100) NOT NULL,
   MACAUHOI NVARCHAR(50) NOT NULL,
   NOIDUNGTL NVARCHAR(MAX) NULL,
   NGAYTL DATETIME DEFAULT GETDATE(),
   HINHANH NVARCHAR(MAX) NULL,
   MACAUTRALOI_CHA NVARCHAR(50) NULL,
   TRANGTHAI NVARCHAR(20) DEFAULT 'pending',
   DUOCCHAPNHAN TINYINT DEFAULT 0,
   PRIMARY KEY (MACAUTRALOI),
   FOREIGN KEY (MANGUOIDUNG) REFERENCES NGUOIDUNG(MANGUOIDUNG),
   FOREIGN KEY (MACAUHOI) REFERENCES CAUHOI(MACAUHOI)
);
GO

-- =============================================
-- Table: VOTE
-- =============================================
IF NOT EXISTS (SELECT * FROM sys.tables WHERE name = 'VOTE')
CREATE TABLE VOTE (
   MAVOTE NVARCHAR(50) NOT NULL,
   MANGUOIDUNG NVARCHAR(100) NOT NULL,
   LOAIVOTE TINYINT NULL,
   NGAYTAO DATETIME DEFAULT GETDATE(),
   PRIMARY KEY (MAVOTE),
   FOREIGN KEY (MANGUOIDUNG) REFERENCES NGUOIDUNG(MANGUOIDUNG)
);
GO

-- =============================================
-- Table: BINHCHONCAUHOI (Vote câu hỏi)
-- =============================================
IF NOT EXISTS (SELECT * FROM sys.tables WHERE name = 'BINHCHONCAUHOI')
CREATE TABLE BINHCHONCAUHOI (
   MAVOTE NVARCHAR(50) NOT NULL,
   MACAUHOI NVARCHAR(50) NOT NULL,
   PRIMARY KEY (MAVOTE, MACAUHOI),
   FOREIGN KEY (MAVOTE) REFERENCES VOTE(MAVOTE),
   FOREIGN KEY (MACAUHOI) REFERENCES CAUHOI(MACAUHOI)
);
GO

-- =============================================
-- Table: BINHCHONCAUTRALOI (Vote câu trả lời)
-- =============================================
IF NOT EXISTS (SELECT * FROM sys.tables WHERE name = 'BINHCHONCAUTRALOI')
CREATE TABLE BINHCHONCAUTRALOI (
   MAVOTE NVARCHAR(50) NOT NULL,
   MACAUTRALOI NVARCHAR(50) NOT NULL,
   PRIMARY KEY (MAVOTE, MACAUTRALOI),
   FOREIGN KEY (MAVOTE) REFERENCES VOTE(MAVOTE),
   FOREIGN KEY (MACAUTRALOI) REFERENCES TRALOI(MACAUTRALOI)
);
GO

-- =============================================
-- Table: LUOTXEM
-- =============================================
IF NOT EXISTS (SELECT * FROM sys.tables WHERE name = 'LUOTXEM')
CREATE TABLE LUOTXEM (
   ID INT IDENTITY(1,1) NOT NULL,
   MACAUHOI NVARCHAR(50) NOT NULL,
   MANGUOIDUNG NVARCHAR(100) NULL,
   IP_ADDRESS NVARCHAR(50) NULL,
   NGAYXEM DATE DEFAULT CAST(GETDATE() AS DATE),
   PRIMARY KEY (ID),
   FOREIGN KEY (MACAUHOI) REFERENCES CAUHOI(MACAUHOI)
);
GO

-- =============================================
-- Table: THONGBAO (Notifications)
-- =============================================
IF NOT EXISTS (SELECT * FROM sys.tables WHERE name = 'THONGBAO')
CREATE TABLE THONGBAO (
    MATHONGBAO NVARCHAR(50) NOT NULL,
    MANGUOIDUNG NVARCHAR(100) NOT NULL,
    LOAI NVARCHAR(20) DEFAULT 'system',
    TIEUDE NVARCHAR(255) NULL,
    NOIDUNG NVARCHAR(MAX) NULL,
    LINK NVARCHAR(500) NULL,
    DADOC TINYINT DEFAULT 0,
    NGAYTAO DATETIME DEFAULT GETDATE(),
    PRIMARY KEY (MATHONGBAO),
    FOREIGN KEY (MANGUOIDUNG) REFERENCES NGUOIDUNG(MANGUOIDUNG) ON DELETE CASCADE
);
GO

-- Index
CREATE INDEX idx_user_unread ON THONGBAO(MANGUOIDUNG, DADOC);
GO

-- =============================================
-- Table: POINTS_LOG (Lịch sử điểm)
-- =============================================
IF NOT EXISTS (SELECT * FROM sys.tables WHERE name = 'POINTS_LOG')
CREATE TABLE POINTS_LOG (
    ID NVARCHAR(50) NOT NULL,
    MANGUOIDUNG NVARCHAR(100) NOT NULL,
    LOAI NVARCHAR(50) NOT NULL,
    DIEM INT NOT NULL,
    MOTA NVARCHAR(255) NULL,
    THAMCHIEU NVARCHAR(100) NULL,
    NGAYTAO DATETIME DEFAULT GETDATE(),
    PRIMARY KEY (ID)
);
GO

CREATE INDEX idx_user_date ON POINTS_LOG(MANGUOIDUNG, NGAYTAO);
CREATE INDEX idx_type ON POINTS_LOG(LOAI);
GO


-- =============================================
-- DỮ LIỆU MẪU
-- =============================================

-- Vai trò
INSERT INTO VAITRO VALUES (N'admin', N'Quản trị viên');
INSERT INTO VAITRO VALUES (N'user', N'Người dùng');
INSERT INTO VAITRO VALUES (N'moderator', N'Điều hành viên');
GO

-- Chuyên ngành
INSERT INTO CHUYENNGHANH VALUES (N'CN001', N'Công nghệ thông tin');
INSERT INTO CHUYENNGHANH VALUES (N'CN002', N'Kỹ thuật phần mềm');
INSERT INTO CHUYENNGHANH VALUES (N'CN003', N'Hệ thống thông tin');
INSERT INTO CHUYENNGHANH VALUES (N'CN004', N'Mạng máy tính');
INSERT INTO CHUYENNGHANH VALUES (N'CN005', N'Trí tuệ Nhân tạo');
GO

-- Tags
INSERT INTO TAG VALUES (N'TAG001', N'CN001', N'PHP', N'Ngôn ngữ lập trình PHP');
INSERT INTO TAG VALUES (N'TAG002', N'CN001', N'JavaScript', N'Ngôn ngữ JavaScript');
INSERT INTO TAG VALUES (N'TAG003', N'CN001', N'SQL', N'Cơ sở dữ liệu SQL');
INSERT INTO TAG VALUES (N'TAG004', N'CN001', N'HTML/CSS', N'Thiết kế web');
INSERT INTO TAG VALUES (N'TAG005', N'CN001', N'Python', N'Ngôn ngữ Python');
INSERT INTO TAG VALUES (N'TAG006', N'CN002', N'Java', N'Ngôn ngữ Java');
INSERT INTO TAG VALUES (N'TAG007', N'CN005', N'Machine Learning', N'Học máy');
INSERT INTO TAG VALUES (N'TAG008', N'CN004', N'Network Security', N'Bảo mật mạng');
GO

-- Huy hiệu (Tiếng Việt)
INSERT INTO HUYHIEU VALUES (N'HH001', N'Người mới', N'Chào mừng đến cộng đồng!', N'🌱', N'ngaythamgia', 0, 1, N'#94a3b8');
INSERT INTO HUYHIEU VALUES (N'HH002', N'Tò mò', N'Đặt 5 câu hỏi đầu tiên', N'❓', N'cauhoi', 5, 1, N'#60a5fa');
INSERT INTO HUYHIEU VALUES (N'HH003', N'Người hỏi', N'Đặt 15 câu hỏi', N'🔍', N'cauhoi', 15, 2, N'#3b82f6');
INSERT INTO HUYHIEU VALUES (N'HH004', N'Nhà nghiên cứu', N'Đặt 50 câu hỏi', N'🔬', N'cauhoi', 50, 4, N'#2563eb');
INSERT INTO HUYHIEU VALUES (N'HH005', N'Người giúp đỡ', N'Trả lời 5 câu hỏi', N'🤝', N'cautraloi', 5, 1, N'#4ade80');
INSERT INTO HUYHIEU VALUES (N'HH006', N'Nhiệt tình', N'Trả lời 25 câu hỏi', N'🔥', N'cautraloi', 25, 2, N'#22c55e');
INSERT INTO HUYHIEU VALUES (N'HH007', N'Chuyên gia', N'Trả lời 100 câu hỏi', N'⭐', N'cautraloi', 100, 4, N'#16a34a');
INSERT INTO HUYHIEU VALUES (N'HH008', N'Ngôi sao mới', N'Đạt 100 điểm', N'✨', N'diem', 100, 1, N'#fcd34d');
INSERT INTO HUYHIEU VALUES (N'HH009', N'Ngôi sao', N'Đạt 500 điểm', N'⭐', N'diem', 500, 3, N'#fbbf24');
INSERT INTO HUYHIEU VALUES (N'HH010', N'Siêu sao', N'Đạt 1000 điểm', N'🌟', N'diem', 1000, 4, N'#f59e0b');
INSERT INTO HUYHIEU VALUES (N'HH011', N'Huyền thoại', N'Đạt 5000 điểm', N'🏆', N'diem', 5000, 6, N'#d97706');
INSERT INTO HUYHIEU VALUES (N'HH012', N'Được thích', N'Nhận 10 đánh giá tốt (4-5⭐)', N'👍', N'vote', 10, 1, N'#f9a8d4');
INSERT INTO HUYHIEU VALUES (N'HH013', N'Được yêu thích', N'Nhận 50 đánh giá tốt (4-5⭐)', N'💖', N'vote', 50, 3, N'#f472b6');
INSERT INTO HUYHIEU VALUES (N'HH014', N'Được ngưỡng mộ', N'Nhận 200 đánh giá tốt (4-5⭐)', N'💎', N'vote', 200, 5, N'#ec4899');
INSERT INTO HUYHIEU VALUES (N'HH015', N'Siêng năng', N'Điểm danh 7 ngày liên tiếp', N'📅', N'streak', 7, 2, N'#fb923c');
INSERT INTO HUYHIEU VALUES (N'HH016', N'Kiên trì', N'Điểm danh 30 ngày liên tiếp', N'🔥', N'streak', 30, 4, N'#ef4444');
INSERT INTO HUYHIEU VALUES (N'HH017', N'Bất khuất', N'Điểm danh 100 ngày liên tiếp', N'👑', N'streak', 100, 6, N'#dc2626');
INSERT INTO HUYHIEU VALUES (N'HH018', N'Hữu ích', N'Có 3 câu trả lời được chấp nhận', N'✅', N'accepted', 3, 2, N'#a78bfa');
INSERT INTO HUYHIEU VALUES (N'HH019', N'Người hướng dẫn', N'Có 15 câu trả lời được chấp nhận', N'🎓', N'accepted', 15, 4, N'#8b5cf6');
INSERT INTO HUYHIEU VALUES (N'HH020', N'Bậc thầy', N'Có 50 câu trả lời được chấp nhận', N'🧙', N'accepted', 50, 6, N'#7c3aed');
GO

-- Người dùng mẫu (mật khẩu: 123456 = e10adc3949ba59abbe56e057f20f883e)
INSERT INTO NGUOIDUNG (MANGUOIDUNG, TENDANGNHAP, EMAIL, MATKHAU, HOTEN, ANHDAIDIEN, TIEUSU, DIEMDANHGIA, TRANGTHAI) VALUES 
(N'ND001', N'admin', N'admin@gmail.com', N'e10adc3949ba59abbe56e057f20f883e', N'Quản trị viên', N'https://ui-avatars.com/api/?name=Admin&background=4f46e5&color=fff', N'Tôi là quản trị viên hệ thống', 100, N'active');
INSERT INTO NGUOIDUNG (MANGUOIDUNG, TENDANGNHAP, EMAIL, MATKHAU, HOTEN, ANHDAIDIEN, TIEUSU, DIEMDANHGIA, TRANGTHAI) VALUES 
(N'ND002', N'nguyenvana', N'vana@gmail.com', N'e10adc3949ba59abbe56e057f20f883e', N'Nguyễn Văn A', N'https://ui-avatars.com/api/?name=Van+A&background=10b981&color=fff', N'Sinh viên CNTT năm 3', 50, N'active');
INSERT INTO NGUOIDUNG (MANGUOIDUNG, TENDANGNHAP, EMAIL, MATKHAU, HOTEN, ANHDAIDIEN, TIEUSU, DIEMDANHGIA, TRANGTHAI) VALUES 
(N'ND003', N'tranthib', N'thib@gmail.com', N'e10adc3949ba59abbe56e057f20f883e', N'Trần Thị B', N'https://ui-avatars.com/api/?name=Thi+B&background=f59e0b&color=fff', N'Yêu thích lập trình web', 35, N'active');
INSERT INTO NGUOIDUNG (MANGUOIDUNG, TENDANGNHAP, EMAIL, MATKHAU, HOTEN, ANHDAIDIEN, TIEUSU, DIEMDANHGIA, TRANGTHAI) VALUES 
(N'ND004', N'levanc', N'vanc@gmail.com', N'e10adc3949ba59abbe56e057f20f883e', N'Lê Văn C', N'https://ui-avatars.com/api/?name=Van+C&background=ef4444&color=fff', N'Developer tại FPT', 80, N'active');
INSERT INTO NGUOIDUNG (MANGUOIDUNG, TENDANGNHAP, EMAIL, MATKHAU, HOTEN, ANHDAIDIEN, TIEUSU, DIEMDANHGIA, TRANGTHAI) VALUES 
(N'ND005', N'phamthid', N'thid@gmail.com', N'e10adc3949ba59abbe56e057f20f883e', N'Phạm Thị D', N'https://ui-avatars.com/api/?name=Thi+D&background=8b5cf6&color=fff', N'Fresher Python', 20, N'active');
GO

-- Phân quyền người dùng
INSERT INTO COVT VALUES (N'admin', N'ND001');
INSERT INTO COVT VALUES (N'moderator', N'ND004');
INSERT INTO COVT VALUES (N'user', N'ND002');
INSERT INTO COVT VALUES (N'user', N'ND003');
INSERT INTO COVT VALUES (N'user', N'ND005');
GO

-- Người dùng nhận huy hiệu
INSERT INTO NHAN VALUES (N'ND001', N'HH007');
INSERT INTO NHAN VALUES (N'ND002', N'HH001');
INSERT INTO NHAN VALUES (N'ND003', N'HH001');
INSERT INTO NHAN VALUES (N'ND004', N'HH006');
GO

-- Câu hỏi mẫu
INSERT INTO CAUHOI VALUES (N'CH001', N'TAG001', N'Làm thế nào để kết nối PHP với MySQL?', N'open', 150, NULL);
INSERT INTO CAUHOI VALUES (N'CH002', N'TAG002', N'Sự khác nhau giữa let, const và var trong JavaScript?', N'open', 200, NULL);
INSERT INTO CAUHOI VALUES (N'CH003', N'TAG003', N'Cách tối ưu hóa câu truy vấn SQL chạy chậm?', N'open', 180, NULL);
INSERT INTO CAUHOI VALUES (N'CH004', N'TAG004', N'Flexbox và Grid khác nhau như thế nào?', N'open', 120, NULL);
INSERT INTO CAUHOI VALUES (N'CH005', N'TAG005', N'Cách sử dụng list comprehension trong Python?', N'open', 90, NULL);
GO

-- Liên kết người dùng đặt câu hỏi (bảng DAT)
INSERT INTO DAT VALUES (N'ND002', N'CH001', N'Mình mới học PHP và muốn kết nối với database MySQL. Mình đã thử dùng mysqli nhưng bị lỗi. Ai có thể hướng dẫn mình cách kết nối đúng không?', GETDATE(), NULL);
INSERT INTO DAT VALUES (N'ND003', N'CH002', N'Mình thấy JavaScript có 3 cách khai báo biến là let, const và var. Khi nào thì dùng cái nào? Có ai giải thích giúp mình không?', GETDATE(), NULL);
INSERT INTO DAT VALUES (N'ND005', N'CH003', N'Câu truy vấn của mình chạy rất chậm khi bảng có nhiều dữ liệu. Có cách nào để tối ưu không?', GETDATE(), NULL);
INSERT INTO DAT VALUES (N'ND002', N'CH004', N'Mình đang học CSS và thấy có Flexbox và Grid. Hai cái này khác nhau chỗ nào? Khi nào dùng cái nào?', GETDATE(), NULL);
INSERT INTO DAT VALUES (N'ND003', N'CH005', N'Mình nghe nói list comprehension trong Python rất tiện. Ai có thể cho ví dụ cụ thể không?', GETDATE(), NULL);
GO

-- Câu trả lời mẫu
INSERT INTO TRALOI VALUES (N'TL001', N'ND004', N'CH001', N'Bạn có thể dùng PDO để kết nối. Ví dụ:
$conn = new PDO("mysql:host=localhost;dbname=test", "root", "");
Đây là cách an toàn và được khuyến khích.', GETDATE(), NULL, NULL, N'approved', 0);
INSERT INTO TRALOI VALUES (N'TL002', N'ND004', N'CH002', N'- var: phạm vi function, có thể khai báo lại
- let: phạm vi block, không thể khai báo lại
- const: phạm vi block, không thể thay đổi giá trị
Nên dùng const mặc định, let khi cần thay đổi.', GETDATE(), NULL, NULL, N'approved', 0);
INSERT INTO TRALOI VALUES (N'TL003', N'ND004', N'CH003', N'Một số cách tối ưu:
1. Thêm INDEX cho cột hay tìm kiếm
2. Tránh SELECT *
3. Dùng EXPLAIN để phân tích query
4. Cân nhắc phân trang khi lấy nhiều data', GETDATE(), NULL, NULL, N'approved', 0);
GO

-- Vote mẫu
INSERT INTO VOTE VALUES (N'VT001', N'ND002', 5, GETDATE());
INSERT INTO VOTE VALUES (N'VT002', N'ND003', 4, GETDATE());
INSERT INTO VOTE VALUES (N'VT003', N'ND005', 5, GETDATE());
GO

-- Bình chọn câu trả lời
INSERT INTO BINHCHONCAUTRALOI VALUES (N'VT001', N'TL001');
INSERT INTO BINHCHONCAUTRALOI VALUES (N'VT002', N'TL002');
INSERT INTO BINHCHONCAUTRALOI VALUES (N'VT003', N'TL003');
GO

-- Thông báo mẫu
INSERT INTO THONGBAO VALUES 
(N'TB001', N'ND002', N'answer', N'Có câu trả lời mới', N'Lê Văn C đã trả lời câu hỏi của bạn', N'question-detail.php?id=CH001', 0, GETDATE());
INSERT INTO THONGBAO VALUES 
(N'TB002', N'ND002', N'vote', N'Bạn nhận được đánh giá', N'Câu hỏi của bạn được đánh giá 5 sao', N'question-detail.php?id=CH001', 0, GETDATE());
INSERT INTO THONGBAO VALUES 
(N'TB003', N'ND003', N'system', N'Chào mừng!', N'Chào mừng bạn đến với diễn đàn', NULL, 1, GETDATE());
GO

-- =============================================
-- HOÀN TẤT
-- =============================================
PRINT N'✅ Tạo database diendan_hoidap (SQL Server) thành công!';
PRINT N'📊 Tổng số huy hiệu: 20';
PRINT N'👤 Tổng số người dùng mẫu: 5';
PRINT N'❓ Tổng số câu hỏi mẫu: 5';
GO
