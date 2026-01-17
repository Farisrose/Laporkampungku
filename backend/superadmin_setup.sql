-- SQL Script untuk mendukung fitur Superadmin
-- Jalankan script ini di database dbkampungku

-- Tabel untuk konfigurasi sistem (jika belum ada)
CREATE TABLE IF NOT EXISTS `tbl_system_config` (
  `id` INT PRIMARY KEY,
  `app_name` VARCHAR(100),
  `app_email` VARCHAR(100),
  `app_phone` VARCHAR(20),
  `maintenance_mode` TINYINT(1) DEFAULT 0,
  `max_upload_size` INT DEFAULT 50,
  `report_retention_days` INT DEFAULT 365,
  `api_timeout` INT DEFAULT 30,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default system config
INSERT IGNORE INTO `tbl_system_config` 
VALUES (1, 'LaporKampungku', 'info@laporkampungku.id', '+62 887-4373-52670', 0, 50, 365, 30, NOW());

-- Tabel untuk activity log (jika belum ada)
CREATE TABLE IF NOT EXISTS `tbl_activity_log` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(50),
  `action` VARCHAR(100),
  `details` TEXT,
  `ip_address` VARCHAR(45),
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create index untuk performance
CREATE INDEX IF NOT EXISTS idx_activity_created ON `tbl_activity_log`(created_at);
CREATE INDEX IF NOT EXISTS idx_users_level ON `tbl_users`(level);
CREATE INDEX IF NOT EXISTS idx_users_active ON `tbl_users`(is_active);
CREATE INDEX IF NOT EXISTS idx_reports_created ON `tbl_reports`(created_at);
CREATE INDEX IF NOT EXISTS idx_reports_status ON `tbl_reports`(status);

-- Pastikan kolom yang diperlukan ada di tbl_users
ALTER TABLE `tbl_users` MODIFY `level` ENUM('warga','anggota','admin','superadmin') DEFAULT 'warga';

-- Insert default superadmin user (jika belum ada)
-- Username: superadmin, Password: SuperAdmin@123
INSERT IGNORE INTO `tbl_users` (username, email, password, level, is_active, created_at)
VALUES ('superadmin', 'superadmin@laporkampungku.id', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'superadmin', 1, NOW());

-- Query untuk statistik analytics
-- Get total users
SELECT COUNT(*) as total FROM `tbl_users`;

-- Get active users
SELECT COUNT(*) as total FROM `tbl_users` WHERE is_active = 1;

-- Get reports by category
SELECT kategori, COUNT(*) as total FROM `tbl_reports` GROUP BY kategori ORDER BY total DESC;

-- Get reports by status
SELECT status, COUNT(*) as total FROM `tbl_reports` GROUP BY status;

-- Get monthly reports
SELECT COUNT(*) as total FROM `tbl_reports` WHERE MONTH(created_at) = MONTH(NOW()) AND YEAR(created_at) = YEAR(NOW());

-- Get completion rate
SELECT 
  COUNT(CASE WHEN status = 'selesai' THEN 1 END) as completed,
  COUNT(*) as total,
  ROUND((COUNT(CASE WHEN status = 'selesai' THEN 1 END) / COUNT(*)) * 100, 2) as completion_rate
FROM `tbl_reports`;
