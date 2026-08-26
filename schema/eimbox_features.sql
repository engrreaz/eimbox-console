-- ==========================================================
-- EIMBox Feature & Platform Status Matrix
-- Table Structure: eimbox_features
-- Author: EIMBox Team
-- ==========================================================

CREATE TABLE IF NOT EXISTS `eimbox_features` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `module` VARCHAR(100) NOT NULL COMMENT 'e.g., Attendance, Accounts, Academics, Exam & Result, Analytics, Routine, Settings, etc.',
  `feature` VARCHAR(150) NOT NULL COMMENT 'Feature or sub-system name',
  `platform` ENUM('Dashboard', 'Console', 'Android Lite', 'Android Premium', 'Desktop', 'API', 'General') NOT NULL DEFAULT 'Dashboard' COMMENT 'Target platform',
  `script` VARCHAR(255) DEFAULT NULL COMMENT 'Path, filename, route or screen (e.g., attendance-register.php)',
  `topic` VARCHAR(255) DEFAULT NULL COMMENT 'Specific sub-topic or component name',
  `issues` TEXT DEFAULT NULL COMMENT 'Identified issues, bug descriptions, pending requirements',
  `response` TEXT DEFAULT NULL COMMENT 'Developer response, fix remarks, action taken',
  `status` ENUM('Open', 'Pending', 'Ongoing', 'Testing', 'Completed', 'Closed', 'On Hold') NOT NULL DEFAULT 'Open' COMMENT 'Current lifecycle status',
  `priority` ENUM('Critical', 'High', 'Medium', 'Low') NOT NULL DEFAULT 'Medium' COMMENT 'Priority level',
  `progress_percent` TINYINT(3) UNSIGNED NOT NULL DEFAULT 0 COMMENT '0 to 100 percentage',
  `assigned_to` VARCHAR(100) DEFAULT NULL COMMENT 'Developer / QA name',
  `created_by` VARCHAR(100) DEFAULT NULL COMMENT 'User who registered the entry',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_platform` (`platform`),
  KEY `idx_module` (`module`),
  KEY `idx_status` (`status`),
  KEY `idx_priority` (`priority`),
  FULLTEXT KEY `ft_search` (`feature`, `topic`, `issues`, `response`, `script`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==========================================================
-- Sample Initial EIMBox Feature Tracking Data
-- ==========================================================

INSERT INTO `eimbox_features` (`module`, `feature`, `platform`, `script`, `topic`, `issues`, `response`, `status`, `priority`, `progress_percent`, `assigned_to`, `created_by`) VALUES
('Attendance', 'Daily Student Attendance', 'Dashboard', 'attendance-register.php', 'Fast Batch Attendance', 'UI becomes slow when class size exceeds 120 students.', 'Implemented pagination and bulk ajax update.', 'Testing', 'High', 85, 'Reaz', 'Admin'),
('Attendance', 'Daily Student Attendance', 'Android Lite', 'lib/screens/attendance_quick.dart', 'Offline Sync & QR Scan', 'Local SQLite cache sync conflict when reconnected to network.', 'Pending review on background sync queue.', 'Ongoing', 'Critical', 60, 'Reaz', 'Admin'),
('Attendance', 'Teacher Biometric Log', 'Console', 'api/biometric_sync.php', 'ZKTeco Device Webhook Integration', 'Device time offset causes wrong punch log in peak hours.', 'Added server-side timestamp validation buffer.', 'Completed', 'High', 100, 'Dev Team', 'Admin'),
('Accounts', 'Student Fee Collection', 'Dashboard', 'payments-collection.php', 'bKash / PGW & Cash Receipt', 'Print receipt layout alignment issue on thermal POS printers.', 'Adjusted 80mm thermal printer CSS styles.', 'Completed', 'High', 100, 'Dev Team', 'Admin'),
('Accounts', 'Daily Collection Summary', 'Android Premium', 'lib/screens/finance_dashboard.dart', 'Visual Revenue Charts', 'Charts need weekly and monthly trend toggle.', 'Integrated syncfusion fl_chart component.', 'Testing', 'Medium', 90, 'Dev Team', 'Admin'),
('Exam & Result', 'OMR Sheet Processor', 'Desktop', 'omr-processor.exe / omr-mapping.php', 'Camera & Flatbed Scanner OMR Sync', 'Image contrast threshold adjustment needed for faint pencil marks.', 'Added automated histogram equalization algorithm.', 'Testing', 'Critical', 90, 'Reaz', 'Admin'),
('Exam & Result', 'Mark Entry & Tabulation', 'Dashboard', 'mark-entry.php', 'Grade Sheet & GPA Calculator', 'Merge marks formula needs support for 4th subject optional exemptions.', 'Updated core calculation engine in result-processor.php.', 'Ongoing', 'High', 70, 'Dev Team', 'Admin'),
('Analytics', 'Subject-wise Performance Report', 'Dashboard', 'analytics/get_detailed_subject_report.php', 'Comparative Section Analytics', 'Filter by shift and section needs instant AJAX refresh.', 'Added AJAX fetch listener and cached queries.', 'Completed', 'Medium', 100, 'Reaz', 'Admin'),
('Analytics', 'Executive Summary Dashboard', 'Android Lite', 'lib/screens/principal_summary.dart', 'Principal KPI Cards', 'Needs push notifications for daily attendance & fee alerts.', 'FCM setup pending backend trigger cron.', 'Pending', 'Medium', 40, 'Dev Team', 'Admin'),
('Communication', 'SMS & Push Notification Engine', 'Console', 'sms-gateway.php', 'Bulk Masking SMS Delivery', 'Failed SMS queue needs auto-retry mechanism with exponential backoff.', 'Cron job added to re-attempt failed SMS twice.', 'Completed', 'High', 100, 'Reaz', 'Admin'),
('Routine & Schedule', 'Class Routine Builder', 'Dashboard', 'class-routine.php', 'Teacher Conflict Detection', 'Drag & drop slot swapping sometimes allows double booking.', 'Under development with client-side slot matrix validator.', 'Ongoing', 'High', 55, 'Dev Team', 'Admin');
