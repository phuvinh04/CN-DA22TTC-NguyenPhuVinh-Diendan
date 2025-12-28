/*==============================================================*/
/* Database: DIENDAN_HOIDAP                                     */
/* DBMS: Microsoft SQL Server 2017+                             */
/* Tạo từ PowerDesigner - Đã chỉnh sửa                          */
/*==============================================================*/

-- Tạo Database
CREATE DATABASE diendan_hoidap;
GO

USE diendan_hoidap;
GO

/*==============================================================*/
/* Table: VAITRO                                                */
/*==============================================================*/
CREATE TABLE VAITRO (
   MAVAITRO             VARCHAR(20)          NOT NULL,
   TENVAITRO            NVARCHAR(20)         NULL,
   CONSTRAINT PK_VAITRO PRIMARY KEY (MAVAITRO)
);
GO

/*==============================================================*/
/* Table: CHUYENNGHANH                                          */
/*==============================================================*/
CREATE TABLE CHUYENNGHANH (
   MACN                 VARCHAR(20)          NOT NULL,
   TENCN                NVARCHAR(50)         NULL,
   CONSTRAINT PK_CHUYENNGHANH PRIMARY KEY (MACN)
);
GO

/*==============================================================*/
/* Table: NGUOIDUNG                                             */
/*==============================================================*/
CREATE TABLE NGUOIDUNG (
   MANGUOIDUNG          VARCHAR(100)         NOT NULL,
   TENDANGNHAP          NVARCHAR(50)         NULL,
   EMAIL                VARCHAR(100)         NULL,
   MATKHAU              VARCHAR(255)         NULL,
   HOTEN                NVARCHAR(100)        NULL,
   ANHDAIDIEN           NVARCHAR(255)        NULL,
   TIEUSU               NVARCHAR(MAX)        NULL,
   DIEMDANHGIA          INT                  DEFAULT 0,
   TRANGTHAI            NVARCHAR(20)         DEFAULT N'active',
   NGAYTAO              DATETIME             DEFAULT GETDATE(),
   LANHOATDONGCUOI      DATETIME             NULL,
   CONSTRAINT PK_NGUOIDUNG PRIMARY KEY (MANGUOIDUNG)
);
GO

/*==============================================================*/
/* Table: HUYHIEU                                               */
/*==============================================================*/
CREATE TABLE HUYHIEU (
   MAHUYHIEU            VARCHAR(100)         NOT NULL,
   TENHUYHIEU           NVARCHAR(50)         NULL,
   MOTA                 NVARCHAR(MAX)        NULL,
   BIEUTUONG            NVARCHAR(255)        NULL,
   LOAITIEUCHI          NVARCHAR(50)         NULL,
   NGUONGTIEUCHI        INT                  NULL,
   CONSTRAINT PK_HUYHIEU PRIMARY KEY (MAHUYHIEU)
);
GO

/*==============================================================*/
/* Table: TAG                                                   */
/*==============================================================*/
CREATE TABLE TAG (
   MATHE                VARCHAR(100)         NOT NULL,
   MACN                 VARCHAR(20)          NULL,
   TENTHE               NVARCHAR(50)         NULL,
   MOTA                 NVARCHAR(MAX)        NULL,
   CONSTRAINT PK_TAG PRIMARY KEY (MATHE),
   CONSTRAINT FK_TAG_CHUYENNGHANH FOREIGN KEY (MACN) REFERENCES CHUYENNGHANH(MACN)
);
GO

/*==============================================================*/
/* Table: COVT (Người dùng - Vai trò)                           */
/*==============================================================*/
CREATE TABLE COVT (
   MAVAITRO             VARCHAR(20)          NOT NULL,
   MANGUOIDUNG          VARCHAR(100)         NOT NULL,
   CONSTRAINT PK_COVT PRIMARY KEY (MAVAITRO, MANGUOIDUNG),
   CONSTRAINT FK_COVT_VAITRO FOREIGN KEY (MAVAITRO) REFERENCES VAITRO(MAVAITRO),
   CONSTRAINT FK_COVT_NGUOIDUNG FOREIGN KEY (MANGUOIDUNG) REFERENCES NGUOIDUNG(MANGUOIDUNG)
);
GO

/*==============================================================*/
/* Table: NHAN (Người dùng - Huy hiệu)                          */
/*==============================================================*/
CREATE TABLE NHAN (
   MANGUOIDUNG          VARCHAR(100)         NOT NULL,
   MAHUYHIEU            VARCHAR(100)         NOT NULL,
   CONSTRAINT PK_NHAN PRIMARY KEY (MANGUOIDUNG, MAHUYHIEU),
   CONSTRAINT FK_NHAN_NGUOIDUNG FOREIGN KEY (MANGUOIDUNG) REFERENCES NGUOIDUNG(MANGUOIDUNG),
   CONSTRAINT FK_NHAN_HUYHIEU FOREIGN KEY (MAHUYHIEU) REFERENCES HUYHIEU(MAHUYHIEU)
);
GO

/*==============================================================*/
/* Table: CAUHOI                                                */
/*==============================================================*/
CREATE TABLE CAUHOI (
   MACAUHOI             VARCHAR(50)          NOT NULL,
   MATHE                VARCHAR(100)         NOT NULL,
   TIEUDE               NVARCHAR(255)        NULL,
   TRANGTHAI            VARCHAR(20)          DEFAULT 'open',
   LUOTXEM              INT                  DEFAULT 0,
   CONSTRAINT PK_CAUHOI PRIMARY KEY (MACAUHOI),
   CONSTRAINT FK_CAUHOI_TAG FOREIGN KEY (MATHE) REFERENCES TAG(MATHE)
);
GO

/*==============================================================*/
/* Table: DAT (Người dùng đặt câu hỏi)                          */
/*==============================================================*/
CREATE TABLE DAT (
   MANGUOIDUNG          VARCHAR(100)         NOT NULL,
   MACAUHOI             VARCHAR(50)          NOT NULL,
   NOIDUNG              NVARCHAR(MAX)        NULL,
   NGAYDANG             DATETIME             DEFAULT GETDATE(),
   CONSTRAINT PK_DAT PRIMARY KEY (MANGUOIDUNG, MACAUHOI),
   CONSTRAINT FK_DAT_NGUOIDUNG FOREIGN KEY (MANGUOIDUNG) REFERENCES NGUOIDUNG(MANGUOIDUNG),
   CONSTRAINT FK_DAT_CAUHOI FOREIGN KEY (MACAUHOI) REFERENCES CAUHOI(MACAUHOI)
);
GO

/*==============================================================*/
/* Table: TRALOI                                                */
/*==============================================================*/
CREATE TABLE TRALOI (
   MACAUTRALOI          VARCHAR(50)          NOT NULL,
   MANGUOIDUNG          VARCHAR(100)         NOT NULL,
   MACAUHOI             VARCHAR(50)          NOT NULL,
   NOIDUNGTL            NVARCHAR(MAX)        NULL,
   NGAYTL               DATETIME             DEFAULT GETDATE(),
   CONSTRAINT PK_TRALOI PRIMARY KEY (MACAUTRALOI),
   CONSTRAINT FK_TRALOI_NGUOIDUNG FOREIGN KEY (MANGUOIDUNG) REFERENCES NGUOIDUNG(MANGUOIDUNG),
   CONSTRAINT FK_TRALOI_CAUHOI FOREIGN KEY (MACAUHOI) REFERENCES CAUHOI(MACAUHOI)
);
GO

/*==============================================================*/
/* Table: VOTE                                                  */
/*==============================================================*/
CREATE TABLE VOTE (
   MAVOTE               VARCHAR(50)          NOT NULL,
   MANGUOIDUNG          VARCHAR(100)         NOT NULL,
   LOAIVOTE             TINYINT              NULL,
   NGAYTAO              DATETIME             DEFAULT GETDATE(),
   CONSTRAINT PK_VOTE PRIMARY KEY (MAVOTE),
   CONSTRAINT FK_VOTE_NGUOIDUNG FOREIGN KEY (MANGUOIDUNG) REFERENCES NGUOIDUNG(MANGUOIDUNG)
);
GO

/*==============================================================*/
/* Table: BINHCHONCAUHOI (Vote câu hỏi)                         */
/*==============================================================*/
CREATE TABLE BINHCHONCAUHOI (
   MAVOTE               VARCHAR(50)          NOT NULL,
   MACAUHOI             VARCHAR(50)          NOT NULL,
   CONSTRAINT PK_BINHCHONCAUHOI PRIMARY KEY (MAVOTE, MACAUHOI),
   CONSTRAINT FK_BINHCHONCAUHOI_VOTE FOREIGN KEY (MAVOTE) REFERENCES VOTE(MAVOTE),
   CONSTRAINT FK_BINHCHONCAUHOI_CAUHOI FOREIGN KEY (MACAUHOI) REFERENCES CAUHOI(MACAUHOI)
);
GO

/*==============================================================*/
/* Table: BINHCHONCAUTRALOI (Vote câu trả lời)                  */
/*==============================================================*/
CREATE TABLE BINHCHONCAUTRALOI (
   MAVOTE               VARCHAR(50)          NOT NULL,
   MACAUTRALOI          VARCHAR(50)          NOT NULL,
   CONSTRAINT PK_BINHCHONCAUTRALOI PRIMARY KEY (MAVOTE, MACAUTRALOI),
   CONSTRAINT FK_BINHCHON_VOTE FOREIGN KEY (MAVOTE) REFERENCES VOTE(MAVOTE),
   CONSTRAINT FK_BINHCHON_TRALOI FOREIGN KEY (MACAUTRALOI) REFERENCES TRALOI(MACAUTRALOI)
);
GO

/*==============================================================*/
/* Table: LUOTXEM                                               */
/*==============================================================*/
CREATE TABLE LUOTXEM (
   ID                   INT IDENTITY(1,1)    NOT NULL,
   MACAUHOI             VARCHAR(50)          NOT NULL,
   MANGUOIDUNG          VARCHAR(100)         NULL,
   IP_ADDRESS           VARCHAR(50)          NULL,
   NGAYXEM              DATE                 DEFAULT CAST(GETDATE() AS DATE),
   CONSTRAINT PK_LUOTXEM PRIMARY KEY (ID),
   CONSTRAINT FK_LUOTXEM_CAUHOI FOREIGN KEY (MACAUHOI) REFERENCES CAUHOI(MACAUHOI)
);
GO

/*==============================================================*/
/* DỮ LIỆU MẪU                                                  */
/*==============================================================*/

-- Vai trò
INSERT INTO VAITRO VALUES ('admin', N'Quản trị viên');
INSERT INTO VAITRO VALUES ('user', N'Người dùng');
INSERT INTO VAITRO VALUES ('moderator', N'Điều hành viên');

-- Chuyên ngành
INSERT INTO CHUYENNGHANH VALUES ('CN001', N'Công nghệ thông tin');
INSERT INTO CHUYENNGHANH VALUES ('CN002', N'Kỹ thuật phần mềm');
INSERT INTO CHUYENNGHANH VALUES ('CN003', N'Hệ thống thông tin');
INSERT INTO CHUYENNGHANH VALUES ('CN004', N'Mạng máy tính');
INSERT INTO CHUYENNGHANH VALUES ('CN005', N'Trí tuệ Nhân tạo');

-- Tags
INSERT INTO TAG VALUES ('TAG001', 'CN001', N'PHP', N'Ngôn ngữ lập trình PHP');
INSERT INTO TAG VALUES ('TAG002', 'CN001', N'JavaScript', N'Ngôn ngữ JavaScript');
INSERT INTO TAG VALUES ('TAG003', 'CN001', N'SQL', N'Cơ sở dữ liệu SQL');
INSERT INTO TAG VALUES ('TAG004', 'CN001', N'HTML/CSS', N'Thiết kế web');
INSERT INTO TAG VALUES ('TAG005', 'CN001', N'Python', N'Ngôn ngữ Python');

-- Huy hiệu
INSERT INTO HUYHIEU VALUES ('HH001', N'Người mới', N'Thành viên mới tham gia', N'🌱', N'ngaythamgia', 0);
INSERT INTO HUYHIEU VALUES ('HH002', N'Nhiệt tình', N'Đã trả lời 10 câu hỏi', N'🔥', N'cautraloi', 10);
INSERT INTO HUYHIEU VALUES ('HH003', N'Chuyên gia', N'Đã trả lời 50 câu hỏi', N'⭐', N'cautraloi', 50);
INSERT INTO HUYHIEU VALUES ('HH004', N'Người hỏi', N'Đã đặt 10 câu hỏi', N'❓', N'cauhoi', 10);
INSERT INTO HUYHIEU VALUES ('HH005', N'Được yêu thích', N'Nhận 100 vote', N'❤️', N'vote', 100);

-- Người dùng mẫu (mật khẩu: 123456 = MD5)
INSERT INTO NGUOIDUNG VALUES ('ND001', N'admin', 'admin@gmail.com', '25f9e794323b453885f5181f1b624d0b', N'Quản trị viên', N'https://ui-avatars.com/api/?name=Admin&background=4f46e5&color=fff', N'Tôi là quản trị viên hệ thống', 100, N'active', GETDATE(), GETDATE());
INSERT INTO NGUOIDUNG VALUES ('ND002', N'nguyenvana', 'vana@gmail.com', '25f9e794323b453885f5181f1b624d0b', N'Nguyễn Văn A', N'https://ui-avatars.com/api/?name=Van+A&background=10b981&color=fff', N'Sinh viên CNTT năm 3', 50, N'active', GETDATE(), GETDATE());
INSERT INTO NGUOIDUNG VALUES ('ND003', N'tranthib', 'thib@gmail.com', '25f9e794323b453885f5181f1b624d0b', N'Trần Thị B', N'https://ui-avatars.com/api/?name=Thi+B&background=f59e0b&color=fff', N'Yêu thích lập trình web', 35, N'active', GETDATE(), GETDATE());
INSERT INTO NGUOIDUNG VALUES ('ND004', N'levanc', 'vanc@gmail.com', '25f9e794323b453885f5181f1b624d0b', N'Lê Văn C', N'https://ui-avatars.com/api/?name=Van+C&background=ef4444&color=fff', N'Developer tại FPT', 80, N'active', GETDATE(), GETDATE());
INSERT INTO NGUOIDUNG VALUES ('ND005', N'phamthid', 'thid@gmail.com', '25f9e794323b453885f5181f1b624d0b', N'Phạm Thị D', N'https://ui-avatars.com/api/?name=Thi+D&background=8b5cf6&color=fff', N'Fresher Python', 20, N'active', GETDATE(), GETDATE());
INSERT INTO NGUOIDUNG VALUES ('ND006', N'hoangvane', 'vane@gmail.com', '25f9e794323b453885f5181f1b624d0b', N'Hoàng Văn E', N'https://ui-avatars.com/api/?name=Van+E&background=06b6d4&color=fff', N'Full-stack developer', 90, N'active', GETDATE(), GETDATE());
INSERT INTO NGUOIDUNG VALUES ('ND007', N'ngothif', 'thif@gmail.com', '25f9e794323b453885f5181f1b624d0b', N'Ngô Thị F', N'https://ui-avatars.com/api/?name=Thi+F&background=ec4899&color=fff', N'UI/UX Designer', 45, N'active', GETDATE(), GETDATE());
INSERT INTO NGUOIDUNG VALUES ('ND008', N'dangvang', 'vang@gmail.com', '25f9e794323b453885f5181f1b624d0b', N'Đặng Văn G', N'https://ui-avatars.com/api/?name=Van+G&background=14b8a6&color=fff', N'Backend Developer', 60, N'active', GETDATE(), GETDATE());
INSERT INTO NGUOIDUNG VALUES ('ND009', N'vuuthih', 'thih@gmail.com', '25f9e794323b453885f5181f1b624d0b', N'Vũ Thị H', N'https://ui-avatars.com/api/?name=Thi+H&background=f97316&color=fff', N'Data Analyst', 30, N'active', GETDATE(), GETDATE());
INSERT INTO NGUOIDUNG VALUES ('ND010', N'buivani', 'vani@gmail.com', '25f9e794323b453885f5181f1b624d0b', N'Bùi Văn I', N'https://ui-avatars.com/api/?name=Van+I&background=6366f1&color=fff', N'Mobile Developer', 55, N'active', GETDATE(), GETDATE());

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
INSERT INTO CAUHOI VALUES ('CH001', 'TAG001', N'Làm thế nào để kết nối PHP với MySQL?', 'open', 150);
INSERT INTO CAUHOI VALUES ('CH002', 'TAG002', N'Sự khác nhau giữa let, const và var trong JavaScript?', 'open', 200);
INSERT INTO CAUHOI VALUES ('CH003', 'TAG003', N'Cách tối ưu hóa câu truy vấn SQL chạy chậm?', 'open', 180);
INSERT INTO CAUHOI VALUES ('CH004', 'TAG004', N'Flexbox và Grid khác nhau như thế nào?', 'open', 120);
INSERT INTO CAUHOI VALUES ('CH005', 'TAG005', N'Cách sử dụng list comprehension trong Python?', 'open', 90);
INSERT INTO CAUHOI VALUES ('CH006', 'TAG001', N'Session và Cookie khác nhau như thế nào trong PHP?', 'open', 110);
INSERT INTO CAUHOI VALUES ('CH007', 'TAG002', N'Async/Await hoạt động như thế nào?', 'open', 175);
INSERT INTO CAUHOI VALUES ('CH008', 'TAG003', N'Khi nào nên dùng INDEX trong SQL?', 'closed', 140);
INSERT INTO CAUHOI VALUES ('CH009', 'TAG004', N'Responsive design là gì và làm thế nào để implement?', 'open', 95);
INSERT INTO CAUHOI VALUES ('CH010', 'TAG005', N'Django và Flask khác nhau như thế nào?', 'open', 130);

-- Liên kết người dùng đặt câu hỏi (bảng DAT)
INSERT INTO DAT VALUES ('ND002', 'CH001', N'Mình mới học PHP và muốn kết nối với database MySQL. Mình đã thử dùng mysqli nhưng bị lỗi. Ai có thể hướng dẫn mình cách kết nối đúng không?', GETDATE());
INSERT INTO DAT VALUES ('ND003', 'CH002', N'Mình thấy JavaScript có 3 cách khai báo biến là let, const và var. Khi nào thì dùng cái nào? Có ai giải thích giúp mình không?', GETDATE());
INSERT INTO DAT VALUES ('ND005', 'CH003', N'Câu truy vấn của mình chạy rất chậm khi bảng có nhiều dữ liệu. Có cách nào để tối ưu không?', GETDATE());
INSERT INTO DAT VALUES ('ND007', 'CH004', N'Mình đang học CSS và thấy có Flexbox và Grid. Hai cái này khác nhau chỗ nào? Khi nào dùng cái nào?', GETDATE());
INSERT INTO DAT VALUES ('ND009', 'CH005', N'Mình nghe nói list comprehension trong Python rất tiện. Ai có thể cho ví dụ cụ thể không?', GETDATE());
INSERT INTO DAT VALUES ('ND002', 'CH006', N'Session và Cookie đều dùng để lưu dữ liệu nhưng mình không hiểu rõ sự khác nhau. Ai giải thích giúp?', GETDATE());
INSERT INTO DAT VALUES ('ND003', 'CH007', N'Mình đang học về asynchronous trong JavaScript. Async/Await hoạt động như thế nào?', GETDATE());
INSERT INTO DAT VALUES ('ND005', 'CH008', N'Mình nghe nói INDEX giúp truy vấn nhanh hơn. Khi nào nên tạo INDEX?', GETDATE());
INSERT INTO DAT VALUES ('ND007', 'CH009', N'Website của mình không hiển thị đẹp trên điện thoại. Làm sao để responsive?', GETDATE());
INSERT INTO DAT VALUES ('ND009', 'CH010', N'Mình muốn học Python web framework. Nên chọn Django hay Flask?', GETDATE());

-- Câu trả lời mẫu
INSERT INTO TRALOI VALUES ('TL001', 'ND004', 'CH001', N'Bạn có thể dùng PDO để kết nối. Ví dụ:\n$conn = new PDO("mysql:host=localhost;dbname=test", "root", "");\nĐây là cách an toàn và được khuyến khích.', GETDATE());
INSERT INTO TRALOI VALUES ('TL002', 'ND006', 'CH001', N'Ngoài PDO, bạn cũng có thể dùng mysqli. Nhưng PDO linh hoạt hơn vì hỗ trợ nhiều database.', GETDATE());
INSERT INTO TRALOI VALUES ('TL003', 'ND004', 'CH002', N'- var: phạm vi function, có thể khai báo lại\n- let: phạm vi block, không thể khai báo lại\n- const: phạm vi block, không thể thay đổi giá trị\nNên dùng const mặc định, let khi cần thay đổi.', GETDATE());
INSERT INTO TRALOI VALUES ('TL004', 'ND008', 'CH003', N'Một số cách tối ưu:\n1. Thêm INDEX cho cột hay tìm kiếm\n2. Tránh SELECT *\n3. Dùng EXPLAIN để phân tích query\n4. Cân nhắc phân trang khi lấy nhiều data', GETDATE());
INSERT INTO TRALOI VALUES ('TL005', 'ND006', 'CH004', N'Flexbox: layout 1 chiều (hàng hoặc cột)\nGrid: layout 2 chiều (hàng và cột)\nDùng Flexbox cho component nhỏ, Grid cho layout tổng thể.', GETDATE());
INSERT INTO TRALOI VALUES ('TL006', 'ND004', 'CH005', N'List comprehension giúp tạo list ngắn gọn:\nsquares = [x**2 for x in range(10)]\nTương đương vòng for nhưng gọn hơn.', GETDATE());
INSERT INTO TRALOI VALUES ('TL007', 'ND008', 'CH006', N'Session lưu trên server, Cookie lưu trên trình duyệt.\nSession an toàn hơn, Cookie có thể bị user chỉnh sửa.\nSession hết khi đóng trình duyệt, Cookie có thể set thời hạn.', GETDATE());
INSERT INTO TRALOI VALUES ('TL008', 'ND006', 'CH007', N'Async/Await là cú pháp để xử lý Promise dễ đọc hơn:\nasync function getData() {\n  const result = await fetch(url);\n  return result.json();\n}', GETDATE());
INSERT INTO TRALOI VALUES ('TL009', 'ND004', 'CH008', N'Nên tạo INDEX khi:\n- Cột thường dùng trong WHERE\n- Cột dùng để JOIN\n- Cột dùng trong ORDER BY\nKhông nên INDEX cột ít giá trị unique hoặc bảng nhỏ.', GETDATE());
INSERT INTO TRALOI VALUES ('TL010', 'ND008', 'CH009', N'Dùng media queries:\n@media (max-width: 768px) { ... }\nVà viewport meta tag:\n<meta name="viewport" content="width=device-width, initial-scale=1">', GETDATE());

-- Vote mẫu
INSERT INTO VOTE VALUES ('VT001', 'ND002', 1, GETDATE());
INSERT INTO VOTE VALUES ('VT002', 'ND003', 1, GETDATE());
INSERT INTO VOTE VALUES ('VT003', 'ND005', 1, GETDATE());
INSERT INTO VOTE VALUES ('VT004', 'ND007', 1, GETDATE());
INSERT INTO VOTE VALUES ('VT005', 'ND009', 1, GETDATE());
INSERT INTO VOTE VALUES ('VT006', 'ND002', 1, GETDATE());
INSERT INTO VOTE VALUES ('VT007', 'ND003', 1, GETDATE());
INSERT INTO VOTE VALUES ('VT008', 'ND005', 1, GETDATE());
INSERT INTO VOTE VALUES ('VT009', 'ND007', 1, GETDATE());
INSERT INTO VOTE VALUES ('VT010', 'ND009', 1, GETDATE());

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

PRINT N'✅ Tạo database diendan_hoidap thành công!';
GO

select * from nguoidung


-- 1. Lấy danh sách tất cả câu hỏi kèm thông tin người đặt và số câu trả lời
SELECT 
    ch.MACAUHOI,
    ch.TIEUDE,
    ch.LUOTXEM,
    ch.TRANGTHAI,
    nd.HOTEN AS NguoiDat,
    t.TENTHE AS Tag,
    d.NGAYDANG,
    (SELECT COUNT(*) FROM TRALOI tl WHERE tl.MACAUHOI = ch.MACAUHOI) AS SoCauTraLoi
FROM CAUHOI ch
INNER JOIN DAT d ON ch.MACAUHOI = d.MACAUHOI
INNER JOIN NGUOIDUNG nd ON d.MANGUOIDUNG = nd.MANGUOIDUNG
INNER JOIN TAG t ON ch.MATHE = t.MATHE
ORDER BY d.NGAYDANG DESC;
GO

-- 2. Lấy chi tiết một câu hỏi cùng tất cả câu trả lời
SELECT 
    ch.TIEUDE,
    d.NOIDUNG AS NoiDungCauHoi,
    nd_hoi.HOTEN AS NguoiHoi,
    tl.NOIDUNGTL AS CauTraLoi,
    nd_tl.HOTEN AS NguoiTraLoi,
    tl.NGAYTL
FROM CAUHOI ch
INNER JOIN DAT d ON ch.MACAUHOI = d.MACAUHOI
INNER JOIN NGUOIDUNG nd_hoi ON d.MANGUOIDUNG = nd_hoi.MANGUOIDUNG
LEFT JOIN TRALOI tl ON ch.MACAUHOI = tl.MACAUHOI
LEFT JOIN NGUOIDUNG nd_tl ON tl.MANGUOIDUNG = nd_tl.MANGUOIDUNG
WHERE ch.MACAUHOI = 'CH001';
GO

-- 3. Đếm số vote của mỗi câu hỏi
SELECT 
    ch.MACAUHOI,
    ch.TIEUDE,
    COUNT(bc.MAVOTE) AS SoVote
FROM CAUHOI ch
LEFT JOIN BINHCHONCAUHOI bc ON ch.MACAUHOI = bc.MACAUHOI
GROUP BY ch.MACAUHOI, ch.TIEUDE
ORDER BY SoVote DESC;
GO

-- 4. Bảng xếp hạng người dùng theo điểm đánh giá
SELECT 
    ROW_NUMBER() OVER (ORDER BY DIEMDANHGIA DESC) AS Hang,
    HOTEN,
    TENDANGNHAP,
    DIEMDANHGIA,
    (SELECT COUNT(*) FROM DAT d WHERE d.MANGUOIDUNG = nd.MANGUOIDUNG) AS SoCauHoi,
    (SELECT COUNT(*) FROM TRALOI tl WHERE tl.MANGUOIDUNG = nd.MANGUOIDUNG) AS SoCauTraLoi
FROM NGUOIDUNG nd
WHERE TRANGTHAI = N'active'
ORDER BY DIEMDANHGIA DESC;
GO

-- 5. Thống kê số câu hỏi theo từng tag
SELECT 
    t.TENTHE,
    t.MOTA,
    COUNT(ch.MACAUHOI) AS SoCauHoi
FROM TAG t
LEFT JOIN CAUHOI ch ON t.MATHE = ch.MATHE
GROUP BY t.MATHE, t.TENTHE, t.MOTA
ORDER BY SoCauHoi DESC;
GO

-- 6. Tìm kiếm câu hỏi theo từ khóa
SELECT 
    ch.MACAUHOI,
    ch.TIEUDE,
    d.NOIDUNG,
    nd.HOTEN AS NguoiDat,
    t.TENTHE
FROM CAUHOI ch
INNER JOIN DAT d ON ch.MACAUHOI = d.MACAUHOI
INNER JOIN NGUOIDUNG nd ON d.MANGUOIDUNG = nd.MANGUOIDUNG
INNER JOIN TAG t ON ch.MATHE = t.MATHE
WHERE ch.TIEUDE LIKE N'%PHP%' OR d.NOIDUNG LIKE N'%PHP%';
GO

-- 7. Lấy danh sách người dùng cùng vai trò của họ
SELECT 
    nd.MANGUOIDUNG,
    nd.HOTEN,
    nd.EMAIL,
    STRING_AGG(vt.TENVAITRO, ', ') AS VaiTro
FROM NGUOIDUNG nd
LEFT JOIN COVT c ON nd.MANGUOIDUNG = c.MANGUOIDUNG
LEFT JOIN VAITRO vt ON c.MAVAITRO = vt.MAVAITRO
GROUP BY nd.MANGUOIDUNG, nd.HOTEN, nd.EMAIL;
GO

-- 8. Câu hỏi được xem nhiều nhất trong tuần
SELECT TOP 10
    ch.MACAUHOI,
    ch.TIEUDE,
    ch.LUOTXEM,
    nd.HOTEN AS NguoiDat,
    d.NGAYDANG
FROM CAUHOI ch
INNER JOIN DAT d ON ch.MACAUHOI = d.MACAUHOI
INNER JOIN NGUOIDUNG nd ON d.MANGUOIDUNG = nd.MANGUOIDUNG
WHERE d.NGAYDANG >= DATEADD(DAY, -7, GETDATE())
ORDER BY ch.LUOTXEM DESC;
GO

-- 9. Người dùng hoạt động tích cực nhất (nhiều câu trả lời nhất)
SELECT TOP 5
    nd.MANGUOIDUNG,
    nd.HOTEN,
    nd.ANHDAIDIEN,
    COUNT(tl.MACAUTRALOI) AS SoCauTraLoi,
    nd.DIEMDANHGIA
FROM NGUOIDUNG nd
INNER JOIN TRALOI tl ON nd.MANGUOIDUNG = tl.MANGUOIDUNG
GROUP BY nd.MANGUOIDUNG, nd.HOTEN, nd.ANHDAIDIEN, nd.DIEMDANHGIA
ORDER BY SoCauTraLoi DESC;
GO

-- 10. Thống kê tổng quan hệ thống
SELECT 
    (SELECT COUNT(*) FROM NGUOIDUNG WHERE TRANGTHAI = N'active') AS TongNguoiDung,
    (SELECT COUNT(*) FROM CAUHOI) AS TongCauHoi,
    (SELECT COUNT(*) FROM TRALOI) AS TongCauTraLoi,
    (SELECT COUNT(*) FROM VOTE) AS TongVote,
    (SELECT SUM(LUOTXEM) FROM CAUHOI) AS TongLuotXem;
GO
-- 11. Danh sách các câu hỏi chưa có câu trả lời nào (Cần sự trợ giúp)
-- Mục đích: Giúp cộng đồng lọc ra các câu hỏi đang bị bỏ quên.
SELECT 
    ch.MACAUHOI,
    ch.TIEUDE,
    t.TENTHE,
    nd.HOTEN AS NguoiHoi,
    d.NGAYDANG
FROM CAUHOI ch
INNER JOIN DAT d ON ch.MACAUHOI = d.MACAUHOI
INNER JOIN NGUOIDUNG nd ON d.MANGUOIDUNG = nd.MANGUOIDUNG
INNER JOIN TAG t ON ch.MATHE = t.MATHE
WHERE NOT EXISTS (SELECT 1 FROM TRALOI tl WHERE tl.MACAUHOI = ch.MACAUHOI)
ORDER BY d.NGAYDANG DESC;
GO

-- 12. Thống kê tổng số lượt xem của từng Chuyên ngành
-- Mục đích: Xem chuyên ngành nào đang được quan tâm nhất dựa trên view của các câu hỏi thuộc tag chuyên ngành đó.
SELECT 
    cn.TENCN,
    SUM(ch.LUOTXEM) AS TongLuotXem,
    COUNT(DISTINCT ch.MACAUHOI) AS SoLuongCauHoi
FROM CHUYENNGHANH cn
INNER JOIN TAG t ON cn.MACN = t.MACN
INNER JOIN CAUHOI ch ON t.MATHE = ch.MATHE
GROUP BY cn.TENCN
ORDER BY TongLuotXem DESC;
GO

-- 13. Danh sách người dùng và các huy hiệu họ đã đạt được (Gộp dòng)
-- Mục đích: Hiển thị profile người dùng kèm danh sách huy hiệu dạng chuỗi (VD: "Người mới, Nhiệt tình").
SELECT 
    nd.MANGUOIDUNG,
    nd.HOTEN,
    COUNT(hh.MAHUYHIEU) AS SoLuongHuyHieu,
    STRING_AGG(hh.TENHUYHIEU, ', ') WITHIN GROUP (ORDER BY hh.TENHUYHIEU) AS DanhSachHuyHieu
FROM NGUOIDUNG nd
LEFT JOIN NHAN n ON nd.MANGUOIDUNG = n.MANGUOIDUNG
LEFT JOIN HUYHIEU hh ON n.MAHUYHIEU = hh.MAHUYHIEU
GROUP BY nd.MANGUOIDUNG, nd.HOTEN;
GO

-- 14. Top 5 câu trả lời được Vote nhiều nhất (Câu trả lời chất lượng)
-- Mục đích: Tìm ra những giải pháp được cộng đồng đánh giá cao nhất.
SELECT TOP 5
    tl.MACAUTRALOI,
    ch.TIEUDE AS CauHoi,
    nd.HOTEN AS NguoiTraLoi,
    SUBSTRING(tl.NOIDUNGTL, 1, 50) + '...' AS TrichDanTraLoi,
    COUNT(bc.MAVOTE) AS SoVote
FROM TRALOI tl
INNER JOIN CAUHOI ch ON tl.MACAUHOI = ch.MACAUHOI
INNER JOIN NGUOIDUNG nd ON tl.MANGUOIDUNG = nd.MANGUOIDUNG
LEFT JOIN BINHCHONCAUTRALOI bc ON tl.MACAUTRALOI = bc.MACAUTRALOI
GROUP BY tl.MACAUTRALOI, ch.TIEUDE, nd.HOTEN, tl.NOIDUNGTL
ORDER BY SoVote DESC;
GO

-- 15. Tìm những người dùng "tự hỏi tự trả lời"
-- Mục đích: Tìm các trường hợp người dùng chia sẻ kiến thức bằng cách tự đặt câu hỏi và tự trả lời (hoặc spam).
SELECT 
    ch.TIEUDE,
    nd.HOTEN AS NguoiDung,
    d.NGAYDANG AS NgayHoi,
    tl.NGAYTL AS NgayTraLoi
FROM CAUHOI ch
INNER JOIN DAT d ON ch.MACAUHOI = d.MACAUHOI
INNER JOIN TRALOI tl ON ch.MACAUHOI = tl.MACAUHOI
INNER JOIN NGUOIDUNG nd ON d.MANGUOIDUNG = nd.MANGUOIDUNG 
WHERE d.MANGUOIDUNG = tl.MANGUOIDUNG; -- Điều kiện người đặt và người trả lời là một
GO

-- 16. Thống kê số lượng câu hỏi mới theo từng tháng trong năm hiện tại
-- Mục đích: Báo cáo tăng trưởng nội dung theo thời gian.
SELECT 
    MONTH(d.NGAYDANG) AS Thang,
    COUNT(ch.MACAUHOI) AS SoCauHoiMoi
FROM CAUHOI ch
INNER JOIN DAT d ON ch.MACAUHOI = d.MACAUHOI
WHERE YEAR(d.NGAYDANG) = YEAR(GETDATE())
GROUP BY MONTH(d.NGAYDANG)
ORDER BY Thang;
GO

-- 17. Danh sách người dùng "tích cực" nhưng chưa có huy hiệu nào
-- Mục đích: Tìm tiềm năng để trao thưởng hoặc khích lệ (Người có điểm đánh giá > 0 nhưng chưa có trong bảng NHAN).
SELECT 
    nd.MANGUOIDUNG,
    nd.HOTEN,
    nd.DIEMDANHGIA,
    nd.NGAYTAO
FROM NGUOIDUNG nd
WHERE nd.DIEMDANHGIA > 10 
AND NOT EXISTS (SELECT 1 FROM NHAN n WHERE n.MANGUOIDUNG = nd.MANGUOIDUNG)
ORDER BY nd.DIEMDANHGIA DESC;
GO

-- 18. Chi tiết lịch sử xem của một câu hỏi cụ thể (Bao gồm cả khách vãng lai)
-- Mục đích: Phân tích traffic của bài viết (Check IP và User).
SELECT 
    ch.TIEUDE,
    lx.IP_ADDRESS,
    COALESCE(nd.HOTEN, N'Khách vãng lai') AS NguoiXem, -- Nếu không đăng nhập thì hiện là Khách
    lx.NGAYXEM
FROM LUOTXEM lx
INNER JOIN CAUHOI ch ON lx.MACAUHOI = ch.MACAUHOI
LEFT JOIN NGUOIDUNG nd ON lx.MANGUOIDUNG = nd.MANGUOIDUNG
WHERE ch.MACAUHOI = 'CH001' -- Thay mã câu hỏi cần xem
ORDER BY lx.NGAYXEM DESC;
GO

-- 19. Tỷ lệ phản hồi: So sánh số lượt xem so với số lượt trả lời của các câu hỏi
-- Mục đích: Đánh giá độ khó hoặc độ hấp dẫn của câu hỏi (Nhiều view ít trả lời = Khó/Chưa rõ ràng).
SELECT 
    ch.TIEUDE,
    ch.LUOTXEM,
    COUNT(tl.MACAUTRALOI) AS SoTraLoi,
    CASE 
        WHEN COUNT(tl.MACAUTRALOI) = 0 THEN 0 
        ELSE CAST(ch.LUOTXEM AS FLOAT) / COUNT(tl.MACAUTRALOI) 
    END AS TyLeViewTrenTraLoi
FROM CAUHOI ch
LEFT JOIN TRALOI tl ON ch.MACAUHOI = tl.MACAUHOI
GROUP BY ch.MACAUHOI, ch.TIEUDE, ch.LUOTXEM
ORDER BY ch.LUOTXEM DESC;
GO

-- 20. Tìm kiếm người dùng theo chuyên ngành sở trường (Dựa trên Tag câu trả lời họ tham gia nhiều nhất)
-- Mục đích: Gợi ý chuyên gia cho một lĩnh vực cụ thể (VD: Tìm người giỏi SQL).
SELECT TOP 5
    nd.HOTEN,
    t.TENTHE AS SoTruong,
    COUNT(tl.MACAUTRALOI) AS SoCauTraLoiTrongTag
FROM NGUOIDUNG nd
INNER JOIN TRALOI tl ON nd.MANGUOIDUNG = tl.MANGUOIDUNG
INNER JOIN CAUHOI ch ON tl.MACAUHOI = ch.MACAUHOI
INNER JOIN TAG t ON ch.MATHE = t.MATHE
WHERE t.TENTHE = 'SQL' -- Thay đổi tag để tìm chuyên gia khác
GROUP BY nd.HOTEN, t.TENTHE
ORDER BY SoCauTraLoiTrongTag DESC;
GO
ALTER TABLE CHUYENNGHANH
ALTER COLUMN TENCN NVARCHAR(200);
GO
PRINT N'✅ Hoàn tất tạo dữ liệu mẫu và câu truy vấn!';
GO
