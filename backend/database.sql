-- Database schema for LaporKampungku
-- Complete structure with user management, role-based access, and audit trail

-- Create database
CREATE DATABASE IF NOT EXISTS `dbkampungku`
  DEFAULT CHARACTER SET = utf8mb4
  DEFAULT COLLATE = utf8mb4_unicode_ci;

USE `dbkampungku`;

-- ==================== USER & ROLE MANAGEMENT ====================

-- Table: tbl_users
-- Core authentication and role assignment
CREATE TABLE IF NOT EXISTS `tbl_users` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `username` VARCHAR(100) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `email` VARCHAR(255) NOT NULL UNIQUE,
  `level` ENUM('superadmin','admin','anggota','warga') NOT NULL DEFAULT 'warga',
  `is_active` BOOLEAN NOT NULL DEFAULT TRUE,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_username` (`username`),
  INDEX `idx_level` (`level`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: tbl_superadmin
-- Profile of top-level administrator (pimpinan tertinggi)
CREATE TABLE IF NOT EXISTS `tbl_superadmin` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` BIGINT UNSIGNED NOT NULL UNIQUE,
  `nama_lengkap` VARCHAR(255) NOT NULL,
  `no_identitas` VARCHAR(50),
  `jabatan` VARCHAR(255),
  `foto_profil` VARCHAR(1024),
  `no_telp` VARCHAR(20),
  `alamat` VARCHAR(512),
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_superadmin_user` FOREIGN KEY (`user_id`) REFERENCES `tbl_users`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: tbl_admin
-- Profile of admin/officer who processes reports (petugas atau instansi)
CREATE TABLE IF NOT EXISTS `tbl_admin` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` BIGINT UNSIGNED NOT NULL UNIQUE,
  `nama_lengkap` VARCHAR(255) NOT NULL,
  `instansi` VARCHAR(255),
  `no_identitas` VARCHAR(50),
  `jabatan` VARCHAR(255),
  `foto_profil` VARCHAR(1024),
  `no_telp` VARCHAR(20),
  `alamat` VARCHAR(512),
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_admin_user` FOREIGN KEY (`user_id`) REFERENCES `tbl_users`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: tbl_anggota
-- Profile of development team members
CREATE TABLE IF NOT EXISTS `tbl_anggota` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` BIGINT UNSIGNED NOT NULL UNIQUE,
  `nama_lengkap` VARCHAR(255) NOT NULL,
  `nim_or_id` VARCHAR(50),
  `peran` VARCHAR(255),
  `foto_profil` VARCHAR(1024),
  `no_telp` VARCHAR(20),
  `email_pribadi` VARCHAR(255),
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_anggota_user` FOREIGN KEY (`user_id`) REFERENCES `tbl_users`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==================== REPORTS & STATUS ====================

-- Table: tbl_status
-- List of available report statuses
CREATE TABLE IF NOT EXISTS `tbl_status` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nama_status` VARCHAR(100) NOT NULL UNIQUE,
  `deskripsi` TEXT,
  `warna` VARCHAR(7),
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default statuses
INSERT IGNORE INTO `tbl_status` (`nama_status`, `deskripsi`, `warna`) VALUES
('Menunggu', 'Laporan baru menunggu diproses', '#FFA500'),
('Diproses', 'Laporan sedang dalam proses penanganan', '#4285F4'),
('Selesai', 'Laporan telah diselesaikan', '#34A853'),
('Ditolak', 'Laporan ditolak atau tidak valid', '#EA4335');

-- Table: tbl_laporan
-- Main report data from citizens
CREATE TABLE IF NOT EXISTS `tbl_laporan` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` BIGINT UNSIGNED,
  `kategori` ENUM('Pipa Bocor','Jalan Rusak','Lampu Jalan Mati','Drainase Tersumbat','Lainnya') NOT NULL,
  `judul` VARCHAR(255),
  `deskripsi` TEXT,
  `latitude` DECIMAL(10,7) NOT NULL,
  `longitude` DECIMAL(10,7) NOT NULL,
  `alamat` VARCHAR(512),
  `prioritas` ENUM('Rendah','Sedang','Tinggi') DEFAULT 'Sedang',
  `status_id` INT UNSIGNED NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_kategori` (`kategori`),
  INDEX `idx_status_id` (`status_id`),
  INDEX `idx_lat_lon` (`latitude`, `longitude`),
  CONSTRAINT `fk_laporan_user` FOREIGN KEY (`user_id`) REFERENCES `tbl_users`(`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_laporan_status` FOREIGN KEY (`status_id`) REFERENCES `tbl_status`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: tbl_foto_laporan
-- One-to-many: one report -> many photos
CREATE TABLE IF NOT EXISTS `tbl_foto_laporan` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `laporan_id` BIGINT UNSIGNED NOT NULL,
  `file_path` VARCHAR(1024) NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_laporan_id` (`laporan_id`),
  CONSTRAINT `fk_foto_laporan_laporan` FOREIGN KEY (`laporan_id`) REFERENCES `tbl_laporan`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==================== AUDIT & HISTORY ====================

-- Table: tbl_riwayat
-- Audit trail: track status changes, who changed it, and when
CREATE TABLE IF NOT EXISTS `tbl_riwayat` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `laporan_id` BIGINT UNSIGNED NOT NULL,
  `status_lama_id` INT UNSIGNED,
  `status_baru_id` INT UNSIGNED NOT NULL,
  `admin_id` BIGINT UNSIGNED,
  `catatan` TEXT,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_laporan_id` (`laporan_id`),
  INDEX `idx_created_at` (`created_at`),
  CONSTRAINT `fk_riwayat_laporan` FOREIGN KEY (`laporan_id`) REFERENCES `tbl_laporan`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_riwayat_status_lama` FOREIGN KEY (`status_lama_id`) REFERENCES `tbl_status`(`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_riwayat_status_baru` FOREIGN KEY (`status_baru_id`) REFERENCES `tbl_status`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_riwayat_admin` FOREIGN KEY (`admin_id`) REFERENCES `tbl_admin`(`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
