-- =============================================
-- DATABASE SCHEMA: SISTEM_DETEKSI_BRUTEFORCE
-- =============================================
-- Description: Brute Force Detection System Database
-- Created: 2026-07-04
-- Version: 1.0
-- =============================================

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

-- =============================================
-- Drop existing database and create new one
-- =============================================

DROP DATABASE IF EXISTS `sistem_deteksi_bruteforce`;
CREATE DATABASE `sistem_deteksi_bruteforce` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `sistem_deteksi_bruteforce`;

-- =============================================
-- TABLE: users
-- =============================================
-- Description: Tabel untuk menyimpan data user
-- =============================================

CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL UNIQUE,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `fullName` varchar(100) NOT NULL,
  `profile_photo` varchar(255) NULL,
  `role` enum('Administrator','User') NOT NULL DEFAULT 'User',
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_username` (`username`),
  UNIQUE KEY `unique_email` (`email`),
  KEY `idx_role` (`role`),
  KEY `idx_is_active` (`is_active`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- TABLE: login_attempts
-- =============================================
-- Description: Tabel untuk menyimpan log percobaan login
-- =============================================

CREATE TABLE `login_attempts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `status` enum('success','failed') NOT NULL DEFAULT 'failed',
  `attempt_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_username` (`username`),
  KEY `idx_ip_address` (`ip_address`),
  KEY `idx_status` (`status`),
  KEY `idx_attempt_time` (`attempt_time`),
  KEY `idx_username_ip` (`username`, `ip_address`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- TABLE: blocked_ips
-- =============================================
-- Description: Tabel untuk menyimpan IP yang diblokir
-- =============================================

CREATE TABLE `blocked_ips` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ip_address` varchar(45) NOT NULL UNIQUE,
  `reason` varchar(255) NULL,
  `block_count` int(11) NOT NULL DEFAULT 1,
  `status` enum('blocked','unblocked','whitelist') NOT NULL DEFAULT 'blocked',
  `blocked_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `blocked_until` timestamp NULL,
  `unblocked_at` timestamp NULL,
  `unblocked_by` int(11) NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_ip_address` (`ip_address`),
  KEY `idx_status` (`status`),
  KEY `idx_blocked_at` (`blocked_at`),
  KEY `idx_ip_status` (`ip_address`, `status`),
  FOREIGN KEY (`unblocked_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- TABLE: detection_logs
-- =============================================
-- Description: Tabel untuk menyimpan log deteksi brute force
-- =============================================

CREATE TABLE `detection_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `detection_type` varchar(50) NOT NULL DEFAULT 'Brute Force',
  `description` text NULL,
  `detected_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `resolved` tinyint(1) NOT NULL DEFAULT 0,
  `resolved_at` timestamp NULL,
  `resolved_by` int(11) NULL,
  PRIMARY KEY (`id`),
  KEY `idx_username` (`username`),
  KEY `idx_ip_address` (`ip_address`),
  KEY `idx_detected_at` (`detected_at`),
  KEY `idx_resolved` (`resolved`),
  KEY `idx_detection_type` (`detection_type`),
  FOREIGN KEY (`resolved_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- TABLE: activity_logs
-- =============================================
-- Description: Tabel untuk menyimpan log aktivitas user
-- =============================================

CREATE TABLE `activity_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `activity` text NOT NULL,
  `ip_address` varchar(45) NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_created_at` (`created_at`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- TABLE: threshold_logs
-- =============================================
-- Description: Tabel untuk menyimpan log threshold dan action
-- =============================================

CREATE TABLE `threshold_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ip_address` varchar(45) NOT NULL,
  `failed_attempts` int(11) NOT NULL DEFAULT 0,
  `threshold_limit` int(11) NOT NULL,
  `action_taken` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_ip_address` (`ip_address`),
  KEY `idx_created_at` (`created_at`),
  KEY `idx_action_taken` (`action_taken`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- TABLE: system_logs
-- =============================================
-- Description: Tabel untuk menyimpan log sistem
-- =============================================

CREATE TABLE `system_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `level` varchar(20) NOT NULL DEFAULT 'INFO',
  `message` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_level` (`level`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- TABLE: notification_reads
-- =============================================
-- Description: Tabel untuk menyimpan notifikasi yang sudah dibaca
-- =============================================

CREATE TABLE `notification_reads` (
  `user_id` int(11) NOT NULL,
  `last_read_at` datetime NOT NULL,
  `updated_at` datetime NULL,
  PRIMARY KEY (`user_id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- TABLE: settings
-- =============================================
-- Description: Tabel untuk menyimpan konfigurasi sistem
-- =============================================

CREATE TABLE `settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) NOT NULL UNIQUE,
  `setting_value` longtext NULL,
  `description` text NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_setting_key` (`setting_key`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- TABLE: reports
-- =============================================
-- Description: Tabel untuk menyimpan laporan keamanan
-- =============================================

CREATE TABLE `reports` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `description` text NULL,
  `report_type` varchar(50) NOT NULL,
  `generated_by` int(11) NOT NULL,
  `from_date` date NOT NULL,
  `to_date` date NOT NULL,
  `total_login_attempts` int(11) DEFAULT 0,
  `successful_logins` int(11) DEFAULT 0,
  `failed_logins` int(11) DEFAULT 0,
  `detected_attacks` int(11) DEFAULT 0,
  `blocked_ips_count` int(11) DEFAULT 0,
  `generated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `file_path` varchar(255) NULL,
  PRIMARY KEY (`id`),
  KEY `idx_generated_by` (`generated_by`),
  KEY `idx_generated_at` (`generated_at`),
  KEY `idx_report_type` (`report_type`),
  FOREIGN KEY (`generated_by`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- DEFAULT DATA
-- =============================================

-- Insert default admin user (password: admin123)
INSERT INTO `users` (`username`, `email`, `password`, `fullName`, `role`, `status`, `is_active`)
VALUES ('admin', 'admin@bruteforce.local', '$2y$10$if7q8Jojmqf8WlAbMgFfF.6uKC6w4juMR2xA6tzwDFCqbE2Dcjile', 'Administrator', 'Administrator', 'active', 1);

-- Insert default settings
INSERT INTO `settings` (`setting_key`, `setting_value`, `description`) VALUES
('brute_force_threshold', '5', 'Maximum login attempts allowed before blocking'),
('block_duration_minutes', '30', 'Duration in minutes to block an IP'),
('enable_email_notifications', '1', 'Send email notifications for detected threats'),
('enable_system_monitor', '1', 'Enable Python-based system monitor'),
('monitor_interval_seconds', '10', 'Interval in seconds for monitoring'),
('log_retention_days', '90', 'Number of days to retain logs'),
('app_name', 'Brute Force Detection System', 'Application name'),
('app_version', '1.0.0', 'Application version'),
('timezone', 'Asia/Jakarta', 'Application timezone');

-- =============================================
-- VIEWS (Optional for reporting)
-- =============================================

CREATE VIEW `vw_login_summary` AS
SELECT 
  DATE(attempt_time) as attempt_date,
  COUNT(*) as total_attempts,
  SUM(CASE WHEN status = 'success' THEN 1 ELSE 0 END) as successful,
  SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) as failed,
  COUNT(DISTINCT ip_address) as unique_ips
FROM login_attempts
GROUP BY DATE(attempt_time)
ORDER BY attempt_date DESC;

CREATE VIEW `vw_ip_activity` AS
SELECT 
  ip_address,
  COUNT(*) as total_attempts,
  SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) as failed_attempts,
  MAX(attempt_time) as last_attempt,
  (SELECT status FROM blocked_ips WHERE blocked_ips.ip_address = login_attempts.ip_address LIMIT 1) as block_status
FROM login_attempts
GROUP BY ip_address
ORDER BY total_attempts DESC;

-- =============================================
-- RESTORE SETTINGS
-- =============================================

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- =============================================
-- END OF DATABASE EXPORT
-- =============================================
