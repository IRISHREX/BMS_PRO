-- QR Attendance settings. Run once only if the web application database user cannot create tables.
CREATE TABLE IF NOT EXISTS `qr_attendance_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `auto_attendance` tinyint(1) NOT NULL DEFAULT 1,
  `camera_enabled` tinyint(1) NOT NULL DEFAULT 1,
  `scanner_enabled` tinyint(1) NOT NULL DEFAULT 1,
  `camera_facing_mode` varchar(20) NOT NULL DEFAULT 'environment',
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `qr_attendance_settings` (`auto_attendance`, `camera_enabled`, `scanner_enabled`, `camera_facing_mode`, `updated_at`)
SELECT 1, 1, 1, 'environment', NOW()
WHERE NOT EXISTS (SELECT 1 FROM `qr_attendance_settings`);
