-- =============================================
-- DATABASE UPDATES - TẤT CẢ CẬP NHẬT
-- Chạy file này sau khi đã import database_mysql.sql
-- =============================================

USE diendan_hoidap;

-- =============================================
-- 1. CẬP NHẬT BẢNG NGUOIDUNG
-- =============================================
ALTER TABLE NGUOIDUNG ADD COLUMN IF NOT EXISTS LOGIN_STREAK INT DEFAULT 0 COMMENT 'Số ngày đăng nhập liên tiếp';
ALTER TABLE NGUOIDUNG ADD COLUMN IF NOT EXISTS LAST_LOGIN_DATE DATE NULL COMMENT 'Ngày đăng nhập gần nhất';
ALTER TABLE NGUOIDUNG ADD COLUMN IF NOT EXISTS KHUNG_AVATAR VARCHAR(100) NULL COMMENT 'Huy hiệu được chọn làm khung';

-- =============================================
-- 2. CẬP NHẬT BẢNG HUYHIEU
-- =============================================
ALTER TABLE HUYHIEU ADD COLUMN IF NOT EXISTS CAPDO INT DEFAULT 1 COMMENT 'Cấp độ huy hiệu (1-6)';
ALTER TABLE HUYHIEU ADD COLUMN IF NOT EXISTS MAUKHUNG VARCHAR(20) DEFAULT '#cbd5e1' COMMENT 'Màu khung avatar';

-- =============================================
-- 3. CẬP NHẬT BẢNG DAT (Đính kèm ảnh câu hỏi)
-- =============================================
ALTER TABLE DAT ADD COLUMN IF NOT EXISTS HINHANH TEXT NULL COMMENT 'JSON array chứa URLs ảnh đính kèm';

-- =============================================
-- 4. CẬP NHẬT BẢNG TRALOI
-- =============================================
ALTER TABLE TRALOI ADD COLUMN IF NOT EXISTS HINHANH TEXT NULL COMMENT 'JSON array chứa URLs ảnh đính kèm';
ALTER TABLE TRALOI ADD COLUMN IF NOT EXISTS MACAUTRALOI_CHA VARCHAR(50) NULL COMMENT 'ID câu trả lời cha (reply)';
ALTER TABLE TRALOI ADD COLUMN IF NOT EXISTS TRANGTHAI VARCHAR(20) DEFAULT 'pending' COMMENT 'Trạng thái duyệt';
ALTER TABLE TRALOI ADD COLUMN IF NOT EXISTS DUOCCHAPNHAN TINYINT DEFAULT 0 COMMENT '1 = được chấp nhận';

-- =============================================
-- 5. CẬP NHẬT BẢNG CAUHOI
-- =============================================
ALTER TABLE CAUHOI ADD COLUMN IF NOT EXISTS CAUTRALOI_CHAPNHAN VARCHAR(50) NULL COMMENT 'ID câu trả lời được chấp nhận';

-- =============================================
-- 6. TẠO BẢNG POINTS_LOG (Lịch sử điểm)
-- =============================================
CREATE TABLE IF NOT EXISTS POINTS_LOG (
    ID VARCHAR(50) NOT NULL,
    MANGUOIDUNG VARCHAR(100) NOT NULL,
    LOAI VARCHAR(50) NOT NULL COMMENT 'Loại hành động',
    DIEM INT NOT NULL COMMENT 'Số điểm (+/-)',
    MOTA VARCHAR(255) NULL COMMENT 'Mô tả',
    THAMCHIEU VARCHAR(100) NULL COMMENT 'ID câu hỏi/trả lời liên quan',
    NGAYTAO DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (ID),
    INDEX idx_user_date (MANGUOIDUNG, NGAYTAO),
    INDEX idx_type (LOAI)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- 7. CHUẨN HÓA HUY HIỆU
-- =============================================

-- Xóa huy hiệu cũ và thêm mới (cẩn thận!)
-- DELETE FROM NHAN;
-- DELETE FROM HUYHIEU;

-- Huy hiệu cơ bản
INSERT INTO HUYHIEU (MAHUYHIEU, TENHUYHIEU, MOTA, BIEUTUONG, LOAITIEUCHI, NGUONGTIEUCHI, CAPDO, MAUKHUNG) VALUES 
('HH001', 'Người mới', 'Chào mừng đến cộng đồng!', '🌱', 'ngaythamgia', 0, 1, '#94a3b8')
ON DUPLICATE KEY UPDATE TENHUYHIEU=VALUES(TENHUYHIEU), MOTA=VALUES(MOTA), CAPDO=VALUES(CAPDO), MAUKHUNG=VALUES(MAUKHUNG);

-- Huy hiệu câu hỏi
INSERT INTO HUYHIEU (MAHUYHIEU, TENHUYHIEU, MOTA, BIEUTUONG, LOAITIEUCHI, NGUONGTIEUCHI, CAPDO, MAUKHUNG) VALUES 
('HH002', 'Tò mò', 'Đặt 5 câu hỏi', '❓', 'cauhoi', 5, 1, '#60a5fa'),
('HH003', 'Người hỏi', 'Đặt 15 câu hỏi', '🔍', 'cauhoi', 15, 2, '#3b82f6'),
('HH004', 'Nhà nghiên cứu', 'Đặt 50 câu hỏi', '🔬', 'cauhoi', 50, 4, '#2563eb')
ON DUPLICATE KEY UPDATE TENHUYHIEU=VALUES(TENHUYHIEU), MOTA=VALUES(MOTA), CAPDO=VALUES(CAPDO), MAUKHUNG=VALUES(MAUKHUNG);

-- Huy hiệu câu trả lời
INSERT INTO HUYHIEU (MAHUYHIEU, TENHUYHIEU, MOTA, BIEUTUONG, LOAITIEUCHI, NGUONGTIEUCHI, CAPDO, MAUKHUNG) VALUES 
('HH005', 'Người giúp đỡ', 'Trả lời 5 câu hỏi', '🤝', 'cautraloi', 5, 1, '#4ade80'),
('HH006', 'Nhiệt tình', 'Trả lời 25 câu hỏi', '🔥', 'cautraloi', 25, 2, '#22c55e'),
('HH007', 'Chuyên gia', 'Trả lời 100 câu hỏi', '⭐', 'cautraloi', 100, 4, '#16a34a')
ON DUPLICATE KEY UPDATE TENHUYHIEU=VALUES(TENHUYHIEU), MOTA=VALUES(MOTA), CAPDO=VALUES(CAPDO), MAUKHUNG=VALUES(MAUKHUNG);

-- Huy hiệu điểm
INSERT INTO HUYHIEU (MAHUYHIEU, TENHUYHIEU, MOTA, BIEUTUONG, LOAITIEUCHI, NGUONGTIEUCHI, CAPDO, MAUKHUNG) VALUES 
('HH008', 'Ngôi sao mới', 'Đạt 100 điểm', '✨', 'diem', 100, 1, '#fcd34d'),
('HH009', 'Ngôi sao', 'Đạt 500 điểm', '⭐', 'diem', 500, 3, '#fbbf24'),
('HH010', 'Siêu sao', 'Đạt 1000 điểm', '🌟', 'diem', 1000, 4, '#f59e0b'),
('HH011', 'Huyền thoại', 'Đạt 5000 điểm', '🏆', 'diem', 5000, 6, '#d97706')
ON DUPLICATE KEY UPDATE TENHUYHIEU=VALUES(TENHUYHIEU), MOTA=VALUES(MOTA), CAPDO=VALUES(CAPDO), MAUKHUNG=VALUES(MAUKHUNG);

-- Huy hiệu đánh giá tốt (4-5 sao)
INSERT INTO HUYHIEU (MAHUYHIEU, TENHUYHIEU, MOTA, BIEUTUONG, LOAITIEUCHI, NGUONGTIEUCHI, CAPDO, MAUKHUNG) VALUES 
('HH012', 'Được thích', 'Nhận 10 đánh giá tốt (4-5⭐)', '👍', 'vote', 10, 1, '#f9a8d4'),
('HH013', 'Được yêu thích', 'Nhận 50 đánh giá tốt (4-5⭐)', '💖', 'vote', 50, 3, '#f472b6'),
('HH014', 'Được ngưỡng mộ', 'Nhận 200 đánh giá tốt (4-5⭐)', '💎', 'vote', 200, 5, '#ec4899')
ON DUPLICATE KEY UPDATE TENHUYHIEU=VALUES(TENHUYHIEU), MOTA=VALUES(MOTA), CAPDO=VALUES(CAPDO), MAUKHUNG=VALUES(MAUKHUNG);

-- Huy hiệu streak
INSERT INTO HUYHIEU (MAHUYHIEU, TENHUYHIEU, MOTA, BIEUTUONG, LOAITIEUCHI, NGUONGTIEUCHI, CAPDO, MAUKHUNG) VALUES 
('HH015', 'Siêng năng', 'Điểm danh 7 ngày liên tiếp', '📅', 'streak', 7, 2, '#fb923c'),
('HH016', 'Kiên trì', 'Điểm danh 30 ngày liên tiếp', '🔥', 'streak', 30, 4, '#ef4444'),
('HH017', 'Bất khuất', 'Điểm danh 100 ngày liên tiếp', '👑', 'streak', 100, 6, '#dc2626')
ON DUPLICATE KEY UPDATE TENHUYHIEU=VALUES(TENHUYHIEU), MOTA=VALUES(MOTA), CAPDO=VALUES(CAPDO), MAUKHUNG=VALUES(MAUKHUNG);

-- Huy hiệu câu trả lời được chấp nhận
INSERT INTO HUYHIEU (MAHUYHIEU, TENHUYHIEU, MOTA, BIEUTUONG, LOAITIEUCHI, NGUONGTIEUCHI, CAPDO, MAUKHUNG) VALUES 
('HH018', 'Hữu ích', 'Có 3 câu trả lời được chấp nhận', '✅', 'accepted', 3, 2, '#a78bfa'),
('HH019', 'Mentor', 'Có 15 câu trả lời được chấp nhận', '🎓', 'accepted', 15, 4, '#8b5cf6'),
('HH020', 'Bậc thầy', 'Có 50 câu trả lời được chấp nhận', '🧙', 'accepted', 50, 6, '#7c3aed')
ON DUPLICATE KEY UPDATE TENHUYHIEU=VALUES(TENHUYHIEU), MOTA=VALUES(MOTA), CAPDO=VALUES(CAPDO), MAUKHUNG=VALUES(MAUKHUNG);

-- =============================================
-- 8. THÊM INDEX
-- =============================================
-- ALTER TABLE TRALOI ADD INDEX IF NOT EXISTS idx_accepted (DUOCCHAPNHAN);
-- ALTER TABLE TRALOI ADD INDEX IF NOT EXISTS idx_trangthai (TRANGTHAI);

-- =============================================
-- HOÀN TẤT
-- =============================================
SELECT '✅ Cập nhật database thành công!' AS Message;
SELECT COUNT(*) AS 'Số huy hiệu' FROM HUYHIEU;
