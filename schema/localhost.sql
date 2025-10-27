-- MariaDB dump 10.19  Distrib 10.4.28-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: secure_auth
-- ------------------------------------------------------
-- Server version	10.4.28-MariaDB

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

--
-- Table structure for table `achievement_events`
--

DROP TABLE IF EXISTS `achievement_events`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `achievement_events` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `email` varchar(120) NOT NULL,
  `achievement_id` int(10) unsigned NOT NULL,
  `event_type` enum('awarded','revoked') NOT NULL,
  `reason` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `achievements_category`
--

DROP TABLE IF EXISTS `achievements_category`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `achievements_category` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sl` int(11) NOT NULL DEFAULT 0,
  `category` varchar(25) DEFAULT NULL,
  `created_by` varchar(100) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `achievements_list`
--

DROP TABLE IF EXISTS `achievements_list`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `achievements_list` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(50) DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `level_requirement` varchar(200) DEFAULT NULL,
  `tbl_name` varchar(30) DEFAULT NULL,
  `aggregate` varchar(20) DEFAULT NULL,
  `field` varchar(20) DEFAULT NULL,
  `params` varchar(255) DEFAULT NULL COMMENT 'extra condition : parameters',
  `requirement` varchar(20) DEFAULT NULL,
  `points` int(11) DEFAULT 0,
  `category` varchar(50) DEFAULT NULL,
  `type` varchar(20) NOT NULL DEFAULT 'Basic',
  `tier` enum('Bronze','Silver','Gold','Platinum','Diamond') DEFAULT 'Bronze',
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=35 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `active_sessions`
--

DROP TABLE IF EXISTS `active_sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `active_sessions` (
  `session_id` varchar(128) NOT NULL,
  `user_id` bigint(20) NOT NULL,
  `ip` varchar(45) NOT NULL,
  `user_agent` text DEFAULT NULL,
  `device_fp` varchar(255) DEFAULT NULL,
  `started_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_seen` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `is_authenticated` tinyint(1) DEFAULT 0,
  `auth_level` enum('password','otp','mfa_hard') DEFAULT 'password',
  PRIMARY KEY (`session_id`),
  KEY `user_id` (`user_id`),
  KEY `last_seen` (`last_seen`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `activity_log`
--

DROP TABLE IF EXISTS `activity_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `activity_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `activity_type` varchar(100) DEFAULT NULL,
  `points` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `auth_logs`
--

DROP TABLE IF EXISTS `auth_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `auth_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `email_attempted` varchar(255) DEFAULT NULL,
  `ip` varchar(45) NOT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `action` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=204 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `chats`
--

DROP TABLE IF EXISTS `chats`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `chats` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sccode` int(11) DEFAULT 0 COMMENT 'Test Comments',
  `sender_id` int(11) DEFAULT NULL,
  `receiver_id` int(11) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `sent_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dev_timeline`
--

DROP TABLE IF EXISTS `dev_timeline`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dev_timeline` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `page_name` varchar(255) NOT NULL,
  `feature_name` varchar(255) NOT NULL,
  `action_type` enum('implement','update','bug_fix','remove','change','refactor','optimize','security_patch','deprecate','migrate','test_case','rollback','hotfix') NOT NULL,
  `status` enum('draft','planning','in_progress','testing','alpha','beta','rc','staging','stable','lts','deprecated','archived') DEFAULT 'draft',
  `description` text DEFAULT NULL,
  `logged_by` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=168 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `faqs`
--

DROP TABLE IF EXISTS `faqs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `faqs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `page_name` varchar(100) NOT NULL,
  `question` text NOT NULL,
  `answer` text NOT NULL,
  `status` tinyint(1) DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `features`
--

DROP TABLE IF EXISTS `features`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `features` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `feature_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `module_name` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `feedback_target`
--

DROP TABLE IF EXISTS `feedback_target`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `feedback_target` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `target_type` enum('module','user','system','other') NOT NULL DEFAULT 'module',
  `target_id` int(11) DEFAULT NULL,
  `target_name` varchar(150) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `feedbacks`
--

DROP TABLE IF EXISTS `feedbacks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `feedbacks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sccode` int(11) DEFAULT NULL,
  `email` varchar(120) DEFAULT NULL,
  `rating` int(11) NOT NULL,
  `feedback` text NOT NULL,
  `target_id` int(11) DEFAULT NULL,
  `target_type` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_feedbacks_email_created` (`email`,`created_at`),
  KEY `idx_feedbacks_target` (`target_type`,`target_id`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `invoices`
--

DROP TABLE IF EXISTS `invoices`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `invoices` (
  `invoice_id` int(11) NOT NULL AUTO_INCREMENT,
  `invoice_number` varchar(50) NOT NULL,
  `sccode` varchar(50) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `status` enum('Pending','Paid','Partial','Cancelled') DEFAULT 'Pending',
  `bill_date` date NOT NULL,
  `payment_date` date DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `notes` text DEFAULT NULL,
  PRIMARY KEY (`invoice_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `logbook`
--

DROP TABLE IF EXISTS `logbook`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `logbook` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(120) DEFAULT NULL,
  `sccode` int(11) DEFAULT NULL,
  `pagename` varchar(100) DEFAULT NULL,
  `duration` int(11) DEFAULT 0,
  `filesize` float NOT NULL DEFAULT 0,
  `ipaddr` varchar(45) DEFAULT NULL,
  `platform` varchar(120) DEFAULT NULL,
  `browser` varchar(120) DEFAULT NULL,
  `location` varchar(100) DEFAULT NULL,
  `entrytime` datetime DEFAULT NULL,
  `modifieddate` datetime DEFAULT NULL,
  `bandwidth` bigint(20) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `idx_email` (`email`),
  KEY `idx_page` (`pagename`),
  KEY `idx_time` (`entrytime`),
  KEY `idx_logbook_email_entry` (`email`,`entrytime`),
  KEY `idx_logbook_page` (`pagename`)
) ENGINE=InnoDB AUTO_INCREMENT=5054 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `messages`
--

DROP TABLE IF EXISTS `messages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sender_id` int(11) NOT NULL,
  `receiver_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `modulelist`
--

DROP TABLE IF EXISTS `modulelist`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `modulelist` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `slno` int(11) NOT NULL DEFAULT 99,
  `module_name` varchar(25) DEFAULT NULL,
  `module_icon` varchar(20) NOT NULL DEFAULT 'circle-square',
  `descrip` varchar(250) DEFAULT NULL,
  `entryby` varchar(120) DEFAULT NULL,
  `modifieddate` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `modulemanager`
--

DROP TABLE IF EXISTS `modulemanager`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `modulemanager` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `module_name` varchar(25) DEFAULT NULL,
  `module_topic` varchar(200) DEFAULT NULL,
  `descrip` varchar(500) DEFAULT NULL,
  `status_name` tinyint(4) NOT NULL DEFAULT 0,
  `related_pages` varchar(500) DEFAULT NULL,
  `nav_icon` varchar(25) NOT NULL DEFAULT 'three-dots-vertical',
  `nav_title` varchar(50) DEFAULT NULL,
  `entryby` varchar(120) DEFAULT NULL,
  `modifieddate` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=57 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER modulemanager_after_insert
    AFTER INSERT ON modulemanager
    FOR EACH ROW
    BEGIN
        INSERT INTO sql_backup_log(table_name, sql_statement, action_type)
        VALUES ('modulemanager',
            CONCAT('INSERT INTO modulemanager (id, module_name, module_topic, descrip, status_name, related_pages, nav_icon, nav_title, entryby, modifieddate) VALUES (', CONCAT('"', IFNULL(NEW.id,'NULL'), '"', ',' ,'"', IFNULL(NEW.module_name,'NULL'), '"', ',' ,'"', IFNULL(NEW.module_topic,'NULL'), '"', ',' ,'"', IFNULL(NEW.descrip,'NULL'), '"', ',' ,'"', IFNULL(NEW.status_name,'NULL'), '"', ',' ,'"', IFNULL(NEW.related_pages,'NULL'), '"', ',' ,'"', IFNULL(NEW.nav_icon,'NULL'), '"', ',' ,'"', IFNULL(NEW.nav_title,'NULL'), '"', ',' ,'"', IFNULL(NEW.entryby,'NULL'), '"', ',' ,'"', IFNULL(NEW.modifieddate,'NULL'), '"'), ');'),
            'INSERT');
    END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER modulemanager_after_update
    AFTER UPDATE ON modulemanager
    FOR EACH ROW
    BEGIN
        INSERT INTO sql_backup_log(table_name, sql_statement, action_type)
        VALUES ('modulemanager',
            CONCAT('UPDATE modulemanager SET ', 'id=', '"', IFNULL(NEW.id,'NULL'), '"', ',', 'module_name=', '"', IFNULL(NEW.module_name,'NULL'), '"', ',', 'module_topic=', '"', IFNULL(NEW.module_topic,'NULL'), '"', ',', 'descrip=', '"', IFNULL(NEW.descrip,'NULL'), '"', ',', 'status_name=', '"', IFNULL(NEW.status_name,'NULL'), '"', ',', 'related_pages=', '"', IFNULL(NEW.related_pages,'NULL'), '"', ',', 'nav_icon=', '"', IFNULL(NEW.nav_icon,'NULL'), '"', ',', 'nav_title=', '"', IFNULL(NEW.nav_title,'NULL'), '"', ',', 'entryby=', '"', IFNULL(NEW.entryby,'NULL'), '"', ',', 'modifieddate=', '"', IFNULL(NEW.modifieddate,'NULL'), '"', ' WHERE id=', IFNULL(OLD.id,'NULL'), ';'),
            'UPDATE');
    END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER modulemanager_after_delete
    AFTER DELETE ON modulemanager
    FOR EACH ROW
    BEGIN
        INSERT INTO sql_backup_log(table_name, sql_statement, action_type)
        VALUES ('modulemanager',
            CONCAT('DELETE FROM modulemanager WHERE id=', IFNULL(OLD.id,'NULL'), ';'),
            'DELETE');
    END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `notifications`
--

DROP TABLE IF EXISTS `notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `notifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `type` varchar(50) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `message` text DEFAULT NULL,
  `link` varchar(255) DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=65 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `otp_store`
--

DROP TABLE IF EXISTS `otp_store`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `otp_store` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) NOT NULL,
  `otp_hash` varchar(255) NOT NULL,
  `channel` enum('email','sms','auth_app') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `expires_at` timestamp NULL DEFAULT NULL,
  `consumed` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `expires_at` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `package_map`
--

DROP TABLE IF EXISTS `package_map`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `package_map` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `page_name` varchar(255) NOT NULL,
  `package_id` int(11) NOT NULL,
  `access` enum('Yes','No') DEFAULT 'Yes' COMMENT 'View Permission',
  `entry_limit` int(11) DEFAULT NULL COMMENT 'Max number of entries allowed',
  `view_limit` int(11) NOT NULL DEFAULT 0,
  `total_time_limit` int(11) DEFAULT NULL COMMENT 'Total usage time limit (sec)',
  `access_count_limit` int(11) DEFAULT NULL COMMENT 'How many times page can be accessed',
  `max_stay_limit` int(11) DEFAULT NULL COMMENT 'Max stay duration per session',
  `print` enum('Yes','No') DEFAULT 'Yes',
  `created_by` int(11) DEFAULT NULL,
  `modified_time` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `package_id` (`package_id`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `package_settings`
--

DROP TABLE IF EXISTS `package_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `package_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `package_id` int(11) NOT NULL,
  `ins_cat` enum('A','B','C','D','E') NOT NULL,
  `price_cat_a` decimal(10,2) DEFAULT 0.00,
  `price_cat_b` decimal(10,2) DEFAULT 0.00,
  `price_cat_c` decimal(10,2) DEFAULT 0.00,
  `price_cat_d` decimal(10,2) DEFAULT 0.00,
  `price_cat_e` decimal(10,2) DEFAULT 0.00,
  `billing_cycle` enum('Monthly','Quarterly','Half Yearly','Yearly') DEFAULT 'Monthly',
  `payment_model` enum('Pre-paid','Post-paid') DEFAULT 'Pre-paid',
  `cat_a_per` enum('Student','Fixed') DEFAULT 'Student',
  `cat_b_per` enum('Student','Fixed') DEFAULT 'Student',
  `cat_c_per` enum('Student','Fixed') DEFAULT 'Student',
  `cat_d_per` enum('Student','Fixed') DEFAULT 'Student',
  `cat_e_per` enum('Student','Fixed') DEFAULT 'Student',
  `status` enum('active','inactive') DEFAULT 'active',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `packages`
--

DROP TABLE IF EXISTS `packages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `packages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `serial` int(11) NOT NULL,
  `package_name` varchar(100) NOT NULL,
  `package_code` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `package_code` (`package_code`)
) ENGINE=InnoDB AUTO_INCREMENT=61 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `page_feedback`
--

DROP TABLE IF EXISTS `page_feedback`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `page_feedback` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `sccode` int(11) DEFAULT NULL,
  `page_name` varchar(255) NOT NULL,
  `feature_name` varchar(255) NOT NULL,
  `status_name` varchar(50) NOT NULL,
  `feedback_type` varchar(100) NOT NULL,
  `notes` text DEFAULT NULL,
  `rating` tinyint(3) unsigned DEFAULT 0,
  `logged_by` varchar(100) DEFAULT 'User',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=67 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `pages`
--

DROP TABLE IF EXISTS `pages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `page_name` varchar(100) NOT NULL,
  `page_url` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `permission_audit`
--

DROP TABLE IF EXISTS `permission_audit`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `permission_audit` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `role_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `page_name` varchar(255) DEFAULT NULL,
  `userlevel` varchar(30) DEFAULT NULL,
  `email` varchar(120) DEFAULT NULL,
  `old_permission` tinyint(1) DEFAULT 0,
  `new_permission` tinyint(1) DEFAULT 0,
  `crud_action` varchar(15) DEFAULT NULL,
  `changed_by` varchar(120) DEFAULT NULL,
  `changed_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=97 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `permission_map`
--

DROP TABLE IF EXISTS `permission_map`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `permission_map` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `page_name` varchar(40) DEFAULT NULL,
  `userlevel` varchar(20) DEFAULT NULL,
  `sccode` varchar(6) DEFAULT NULL,
  `email` varchar(120) DEFAULT NULL,
  `permission` int(11) NOT NULL DEFAULT 0,
  `entryby` varchar(120) DEFAULT NULL,
  `modifiedtime` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=116 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `poll_votes`
--

DROP TABLE IF EXISTS `poll_votes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `poll_votes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `poll_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `option_text` varchar(255) DEFAULT NULL,
  `voted_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_vote` (`poll_id`,`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `polls`
--

DROP TABLE IF EXISTS `polls`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `polls` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `question` text NOT NULL,
  `options_json` varchar(255) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `expires_at` datetime DEFAULT NULL,
  `status` enum('active','closed') DEFAULT 'active',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `project_documentation`
--

DROP TABLE IF EXISTS `project_documentation`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `project_documentation` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `page_name` varchar(255) NOT NULL,
  `feature_name` varchar(255) NOT NULL,
  `feature_title` varchar(255) NOT NULL,
  `feature_description` text DEFAULT NULL,
  `full_documentation` longtext DEFAULT NULL,
  `created_by` varchar(100) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `tags` varchar(255) DEFAULT NULL,
  `version` varchar(50) DEFAULT '1.0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `remembered_devices`
--

DROP TABLE IF EXISTS `remembered_devices`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `remembered_devices` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) NOT NULL,
  `device_fp` varchar(255) NOT NULL,
  `label` varchar(191) DEFAULT NULL,
  `last_seen` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id_2` (`user_id`,`device_fp`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `rolemanager`
--

DROP TABLE IF EXISTS `rolemanager`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `rolemanager` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sccode` int(11) NOT NULL DEFAULT 0,
  `userlevel` varchar(25) DEFAULT NULL,
  `descrip` varchar(250) DEFAULT NULL,
  `entryby` varchar(120) DEFAULT NULL,
  `modifieddate` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `schema_update_log`
--

DROP TABLE IF EXISTS `schema_update_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `schema_update_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `executed_at` datetime DEFAULT current_timestamp(),
  `sql_statement` text NOT NULL,
  `status` enum('APPLIED','FAILED') NOT NULL,
  `error_message` text DEFAULT NULL,
  `backup_path` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=132 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `scinfo`
--

DROP TABLE IF EXISTS `scinfo`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `scinfo` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sccode` int(11) DEFAULT NULL,
  `scname` varchar(100) DEFAULT NULL,
  `sccategory` varchar(50) NOT NULL DEFAULT 'School',
  `short` varchar(10) DEFAULT NULL,
  `scadd1` varchar(100) DEFAULT NULL,
  `scadd2` varchar(100) DEFAULT NULL,
  `ps` varchar(50) DEFAULT NULL,
  `dist` varchar(50) DEFAULT NULL,
  `postal_code` int(11) DEFAULT NULL,
  `zone` varchar(25) DEFAULT NULL,
  `mobile` varchar(11) DEFAULT NULL,
  `scmail` varchar(200) DEFAULT NULL,
  `scmail2` varchar(120) DEFAULT NULL,
  `scweb` varchar(200) DEFAULT NULL,
  `headname` varchar(50) DEFAULT NULL,
  `headtitle` varchar(30) DEFAULT NULL,
  `rootuser` varchar(100) DEFAULT NULL,
  `modifieddate` datetime DEFAULT NULL,
  `logo` varchar(255) DEFAULT '-',
  `pack` int(11) NOT NULL DEFAULT 0,
  `packdate` datetime DEFAULT NULL,
  `expire` datetime DEFAULT NULL,
  `count` int(11) NOT NULL DEFAULT 0,
  `serviceattnd` int(11) NOT NULL DEFAULT 0,
  `servicefinance` int(11) NOT NULL DEFAULT 0,
  `servicestudent` int(11) NOT NULL DEFAULT 0,
  `app` int(11) NOT NULL DEFAULT 0,
  `progressguar` int(11) NOT NULL DEFAULT 1 COMMENT 'Show Guardian in Progress Report',
  `browser` varchar(100) DEFAULT NULL,
  `geolat` varchar(25) NOT NULL DEFAULT '23.72769',
  `geolon` varchar(25) NOT NULL DEFAULT '90.41047',
  `intime` time NOT NULL DEFAULT '09:45:00',
  `outtime` time NOT NULL DEFAULT '16:30:00',
  `dista_differ` int(11) NOT NULL DEFAULT 50,
  `time_differ` int(11) NOT NULL DEFAULT 600,
  `profile_track` int(11) NOT NULL DEFAULT 0,
  `self_control` int(11) NOT NULL DEFAULT 0,
  `backup` int(11) NOT NULL DEFAULT 0,
  `algorithm` varchar(20) DEFAULT NULL,
  `secret_key` varchar(50) DEFAULT NULL,
  `api_key` varchar(50) DEFAULT NULL,
  `backup_mail_2` varchar(100) DEFAULT NULL,
  `backup_mail_3` varchar(100) DEFAULT NULL,
  `daily_backup` int(11) NOT NULL DEFAULT 0,
  `monthly_backup` int(11) NOT NULL DEFAULT 0,
  `cloud_storage` int(11) NOT NULL DEFAULT 0,
  `last_backup_time` datetime DEFAULT NULL,
  `active` int(11) NOT NULL DEFAULT 0,
  `status` int(11) NOT NULL DEFAULT 0,
  `display` int(11) NOT NULL DEFAULT 1,
  `last_login_time` datetime DEFAULT NULL,
  `sms_send` int(11) NOT NULL DEFAULT 0,
  `sms_success` int(11) NOT NULL DEFAULT 0,
  `sms_error` int(11) NOT NULL DEFAULT 0,
  `sms_cost` float NOT NULL DEFAULT 0,
  `sms_balance` float NOT NULL DEFAULT 0,
  `account_balance` float NOT NULL DEFAULT 0,
  `admin_data` varchar(512) DEFAULT NULL,
  `sms_setting` varchar(500) DEFAULT NULL,
  `sms_gateway` varchar(500) DEFAULT NULL,
  `sms_in` varchar(500) DEFAULT NULL,
  `sms_out` varchar(500) DEFAULT NULL,
  `sms_absent` varchar(500) DEFAULT NULL,
  `sms_payment` varchar(500) DEFAULT NULL,
  `sms_dues` varchar(500) DEFAULT NULL,
  `sms_month_report` varchar(500) DEFAULT NULL,
  `reg_hash` varchar(32) DEFAULT NULL,
  `hash_expire` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=277 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `scinfo_after_insert` AFTER INSERT ON `scinfo` FOR EACH ROW BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, new_data, changed_by)
        VALUES(
            'scinfo',
            'INSERT',
            NEW.`id`,
            JSON_OBJECT('id', NEW.`id`,'sccode', NEW.`sccode`,'scname', NEW.`scname`,'sccategory', NEW.`sccategory`,'short', NEW.`short`,'scadd1', NEW.`scadd1`,'scadd2', NEW.`scadd2`,'ps', NEW.`ps`,'dist', NEW.`dist`,'postal_code', NEW.`postal_code`,'zone', NEW.`zone`,'mobile', NEW.`mobile`,'scmail', NEW.`scmail`,'scmail2', NEW.`scmail2`,'scweb', NEW.`scweb`,'headname', NEW.`headname`,'headtitle', NEW.`headtitle`,'rootuser', NEW.`rootuser`,'modifieddate', NEW.`modifieddate`,'logo', NEW.`logo`,'pack', NEW.`pack`,'packdate', NEW.`packdate`,'expire', NEW.`expire`,'count', NEW.`count`,'serviceattnd', NEW.`serviceattnd`,'servicefinance', NEW.`servicefinance`,'servicestudent', NEW.`servicestudent`,'app', NEW.`app`,'progressguar', NEW.`progressguar`,'browser', NEW.`browser`,'geolat', NEW.`geolat`,'geolon', NEW.`geolon`,'intime', NEW.`intime`,'outtime', NEW.`outtime`,'dista_differ', NEW.`dista_differ`,'time_differ', NEW.`time_differ`,'profile_track', NEW.`profile_track`,'self_control', NEW.`self_control`,'backup', NEW.`backup`,'algorithm', NEW.`algorithm`,'secret_key', NEW.`secret_key`,'api_key', NEW.`api_key`,'backup_mail_2', NEW.`backup_mail_2`,'backup_mail_3', NEW.`backup_mail_3`,'daily_backup', NEW.`daily_backup`,'monthly_backup', NEW.`monthly_backup`,'cloud_storage', NEW.`cloud_storage`,'last_backup_time', NEW.`last_backup_time`,'active', NEW.`active`,'status', NEW.`status`,'display', NEW.`display`,'last_login_time', NEW.`last_login_time`,'sms_send', NEW.`sms_send`,'sms_success', NEW.`sms_success`,'sms_error', NEW.`sms_error`,'sms_cost', NEW.`sms_cost`,'sms_balance', NEW.`sms_balance`,'account_balance', NEW.`account_balance`,'admin_data', NEW.`admin_data`),
            COALESCE(@current_user,'system')
        );
    END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `scinfo_after_update` AFTER UPDATE ON `scinfo` FOR EACH ROW BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, new_data, changed_by)
        VALUES(
            'scinfo',
            'UPDATE',
            NEW.`id`,
            JSON_OBJECT('id', OLD.`id`,'sccode', OLD.`sccode`,'scname', OLD.`scname`,'sccategory', OLD.`sccategory`,'short', OLD.`short`,'scadd1', OLD.`scadd1`,'scadd2', OLD.`scadd2`,'ps', OLD.`ps`,'dist', OLD.`dist`,'postal_code', OLD.`postal_code`,'zone', OLD.`zone`,'mobile', OLD.`mobile`,'scmail', OLD.`scmail`,'scmail2', OLD.`scmail2`,'scweb', OLD.`scweb`,'headname', OLD.`headname`,'headtitle', OLD.`headtitle`,'rootuser', OLD.`rootuser`,'modifieddate', OLD.`modifieddate`,'logo', OLD.`logo`,'pack', OLD.`pack`,'packdate', OLD.`packdate`,'expire', OLD.`expire`,'count', OLD.`count`,'serviceattnd', OLD.`serviceattnd`,'servicefinance', OLD.`servicefinance`,'servicestudent', OLD.`servicestudent`,'app', OLD.`app`,'progressguar', OLD.`progressguar`,'browser', OLD.`browser`,'geolat', OLD.`geolat`,'geolon', OLD.`geolon`,'intime', OLD.`intime`,'outtime', OLD.`outtime`,'dista_differ', OLD.`dista_differ`,'time_differ', OLD.`time_differ`,'profile_track', OLD.`profile_track`,'self_control', OLD.`self_control`,'backup', OLD.`backup`,'algorithm', OLD.`algorithm`,'secret_key', OLD.`secret_key`,'api_key', OLD.`api_key`,'backup_mail_2', OLD.`backup_mail_2`,'backup_mail_3', OLD.`backup_mail_3`,'daily_backup', OLD.`daily_backup`,'monthly_backup', OLD.`monthly_backup`,'cloud_storage', OLD.`cloud_storage`,'last_backup_time', OLD.`last_backup_time`,'active', OLD.`active`,'status', OLD.`status`,'display', OLD.`display`,'last_login_time', OLD.`last_login_time`,'sms_send', OLD.`sms_send`,'sms_success', OLD.`sms_success`,'sms_error', OLD.`sms_error`,'sms_cost', OLD.`sms_cost`,'sms_balance', OLD.`sms_balance`,'account_balance', OLD.`account_balance`,'admin_data', OLD.`admin_data`),
            JSON_OBJECT('id', NEW.`id`,'sccode', NEW.`sccode`,'scname', NEW.`scname`,'sccategory', NEW.`sccategory`,'short', NEW.`short`,'scadd1', NEW.`scadd1`,'scadd2', NEW.`scadd2`,'ps', NEW.`ps`,'dist', NEW.`dist`,'postal_code', NEW.`postal_code`,'zone', NEW.`zone`,'mobile', NEW.`mobile`,'scmail', NEW.`scmail`,'scmail2', NEW.`scmail2`,'scweb', NEW.`scweb`,'headname', NEW.`headname`,'headtitle', NEW.`headtitle`,'rootuser', NEW.`rootuser`,'modifieddate', NEW.`modifieddate`,'logo', NEW.`logo`,'pack', NEW.`pack`,'packdate', NEW.`packdate`,'expire', NEW.`expire`,'count', NEW.`count`,'serviceattnd', NEW.`serviceattnd`,'servicefinance', NEW.`servicefinance`,'servicestudent', NEW.`servicestudent`,'app', NEW.`app`,'progressguar', NEW.`progressguar`,'browser', NEW.`browser`,'geolat', NEW.`geolat`,'geolon', NEW.`geolon`,'intime', NEW.`intime`,'outtime', NEW.`outtime`,'dista_differ', NEW.`dista_differ`,'time_differ', NEW.`time_differ`,'profile_track', NEW.`profile_track`,'self_control', NEW.`self_control`,'backup', NEW.`backup`,'algorithm', NEW.`algorithm`,'secret_key', NEW.`secret_key`,'api_key', NEW.`api_key`,'backup_mail_2', NEW.`backup_mail_2`,'backup_mail_3', NEW.`backup_mail_3`,'daily_backup', NEW.`daily_backup`,'monthly_backup', NEW.`monthly_backup`,'cloud_storage', NEW.`cloud_storage`,'last_backup_time', NEW.`last_backup_time`,'active', NEW.`active`,'status', NEW.`status`,'display', NEW.`display`,'last_login_time', NEW.`last_login_time`,'sms_send', NEW.`sms_send`,'sms_success', NEW.`sms_success`,'sms_error', NEW.`sms_error`,'sms_cost', NEW.`sms_cost`,'sms_balance', NEW.`sms_balance`,'account_balance', NEW.`account_balance`,'admin_data', NEW.`admin_data`),
            COALESCE(@current_user,'system')
        );
    END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `scinfo_after_delete` AFTER DELETE ON `scinfo` FOR EACH ROW BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, changed_by)
        VALUES(
            'scinfo',
            'DELETE',
            OLD.`id`,
            JSON_OBJECT('id', OLD.`id`,'sccode', OLD.`sccode`,'scname', OLD.`scname`,'sccategory', OLD.`sccategory`,'short', OLD.`short`,'scadd1', OLD.`scadd1`,'scadd2', OLD.`scadd2`,'ps', OLD.`ps`,'dist', OLD.`dist`,'postal_code', OLD.`postal_code`,'zone', OLD.`zone`,'mobile', OLD.`mobile`,'scmail', OLD.`scmail`,'scmail2', OLD.`scmail2`,'scweb', OLD.`scweb`,'headname', OLD.`headname`,'headtitle', OLD.`headtitle`,'rootuser', OLD.`rootuser`,'modifieddate', OLD.`modifieddate`,'logo', OLD.`logo`,'pack', OLD.`pack`,'packdate', OLD.`packdate`,'expire', OLD.`expire`,'count', OLD.`count`,'serviceattnd', OLD.`serviceattnd`,'servicefinance', OLD.`servicefinance`,'servicestudent', OLD.`servicestudent`,'app', OLD.`app`,'progressguar', OLD.`progressguar`,'browser', OLD.`browser`,'geolat', OLD.`geolat`,'geolon', OLD.`geolon`,'intime', OLD.`intime`,'outtime', OLD.`outtime`,'dista_differ', OLD.`dista_differ`,'time_differ', OLD.`time_differ`,'profile_track', OLD.`profile_track`,'self_control', OLD.`self_control`,'backup', OLD.`backup`,'algorithm', OLD.`algorithm`,'secret_key', OLD.`secret_key`,'api_key', OLD.`api_key`,'backup_mail_2', OLD.`backup_mail_2`,'backup_mail_3', OLD.`backup_mail_3`,'daily_backup', OLD.`daily_backup`,'monthly_backup', OLD.`monthly_backup`,'cloud_storage', OLD.`cloud_storage`,'last_backup_time', OLD.`last_backup_time`,'active', OLD.`active`,'status', OLD.`status`,'display', OLD.`display`,'last_login_time', OLD.`last_login_time`,'sms_send', OLD.`sms_send`,'sms_success', OLD.`sms_success`,'sms_error', OLD.`sms_error`,'sms_cost', OLD.`sms_cost`,'sms_balance', OLD.`sms_balance`,'account_balance', OLD.`account_balance`,'admin_data', OLD.`admin_data`),
            COALESCE(@current_user,'system')
        );
    END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `settings_ins`
--

DROP TABLE IF EXISTS `settings_ins`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `settings_ins` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sccode` int(11) DEFAULT NULL,
  `settings_key` varchar(20) DEFAULT NULL,
  `settings_value` varchar(255) DEFAULT NULL,
  `update_by` varchar(100) DEFAULT NULL,
  `update_time` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `sql_backup_log`
--

DROP TABLE IF EXISTS `sql_backup_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sql_backup_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `table_name` varchar(100) DEFAULT NULL,
  `sql_statement` text DEFAULT NULL,
  `action_type` enum('INSERT','UPDATE','DELETE') DEFAULT NULL,
  `changed_at` datetime DEFAULT current_timestamp(),
  `exported` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=120 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `status_list`
--

DROP TABLE IF EXISTS `status_list`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `status_list` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `status` varchar(20) NOT NULL,
  `entrytime` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `support_tickets`
--

DROP TABLE IF EXISTS `support_tickets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `support_tickets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `status` enum('open','in_progress','closed') DEFAULT 'open',
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `suspicious_activity_types`
--

DROP TABLE IF EXISTS `suspicious_activity_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `suspicious_activity_types` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category` varchar(100) NOT NULL,
  `title` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `risk_score` int(11) DEFAULT 10,
  `recommended_action` enum('log_only','alert','review','block') DEFAULT 'alert',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=125 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `suspicious_events`
--

DROP TABLE IF EXISTS `suspicious_events`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `suspicious_events` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `email` varchar(120) DEFAULT NULL,
  `ip_address` varchar(50) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `event_type` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `risk_score` int(11) DEFAULT 10,
  `recommended_action` enum('log_only','alert','review','block') DEFAULT 'log_only',
  `matched_rule_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `event_type` (`event_type`),
  KEY `ip_address` (`ip_address`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ticket_messages`
--

DROP TABLE IF EXISTS `ticket_messages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ticket_messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ticket_id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `sent_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tickets`
--

DROP TABLE IF EXISTS `tickets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tickets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `status` enum('open','in_progress','closed') DEFAULT 'open',
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `title_requirements`
--

DROP TABLE IF EXISTS `title_requirements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `title_requirements` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title_id` int(10) unsigned NOT NULL,
  `achievement_code` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_title_ach` (`title_id`,`achievement_code`),
  KEY `achievement_code` (`achievement_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `titles_list`
--

DROP TABLE IF EXISTS `titles_list`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `titles_list` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title_name` varchar(100) NOT NULL,
  `min_points` int(11) DEFAULT 0,
  `required_achievements` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`required_achievements`)),
  `badge_color` varchar(20) DEFAULT 'secondary',
  `description` text DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=101 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `user_achievements`
--

DROP TABLE IF EXISTS `user_achievements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_achievements` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sccode` int(11) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `user_id` int(11) NOT NULL,
  `achievement_id` int(11) NOT NULL,
  `achieved_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_user_achievement` (`user_id`,`achievement_id`),
  UNIQUE KEY `ux_email_ach` (`email`,`achievement_id`),
  UNIQUE KEY `ux_email_achievement` (`email`,`achievement_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `user_actions`
--

DROP TABLE IF EXISTS `user_actions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_actions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sccode` int(11) DEFAULT NULL,
  `email` varchar(120) DEFAULT NULL,
  `url` varchar(255) DEFAULT NULL,
  `page` varchar(50) DEFAULT NULL,
  `action` varchar(255) DEFAULT NULL,
  `points` int(11) DEFAULT 0,
  `timestamp` datetime DEFAULT NULL,
  `ip` varchar(50) DEFAULT NULL,
  `browser` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_useractions_email_ts` (`email`,`timestamp`),
  KEY `idx_useractions_action` (`action`)
) ENGINE=InnoDB AUTO_INCREMENT=10425 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `user_points`
--

DROP TABLE IF EXISTS `user_points`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_points` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `name` varchar(20) DEFAULT NULL,
  `total_points` int(11) DEFAULT 0,
  `current_title_id` int(11) DEFAULT NULL,
  `level` int(11) DEFAULT 1,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `user_shortcuts`
--

DROP TABLE IF EXISTS `user_shortcuts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_shortcuts` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_email` varchar(255) NOT NULL,
  `sccode` int(11) DEFAULT NULL COMMENT 'Unique shortcut code / identifier',
  `page_name` varchar(255) NOT NULL COMMENT 'Page URL or internal identifier',
  `page_title` varchar(255) DEFAULT NULL COMMENT 'Title of the page',
  `page_icon` varchar(30) NOT NULL DEFAULT 'close',
  `module` varchar(25) DEFAULT NULL COMMENT 'Optional description of the page',
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `user_titles`
--

DROP TABLE IF EXISTS `user_titles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_titles` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `email` varchar(120) NOT NULL,
  `title_id` int(10) unsigned NOT NULL,
  `awarded_at` datetime NOT NULL DEFAULT current_timestamp(),
  `is_active` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ux_user_title` (`email`,`title_id`),
  UNIQUE KEY `ux_email_title` (`email`,`title_id`),
  KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `user_tokens`
--

DROP TABLE IF EXISTS `user_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_tokens` (
  `user_id` int(11) NOT NULL,
  `token` varchar(255) NOT NULL,
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `role` varchar(50) DEFAULT 'User',
  `avatar` varchar(255) DEFAULT NULL,
  `status` enum('online','away','busy','offline') DEFAULT 'online',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `users_login_tracks`
--

DROP TABLE IF EXISTS `users_login_tracks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users_login_tracks` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) NOT NULL,
  `event_type` enum('login_attempt','login_success','login_failure','logout','otp_sent','otp_verified') NOT NULL,
  `ip` varchar(45) NOT NULL,
  `user_agent` text DEFAULT NULL,
  `device_fp` varchar(255) DEFAULT NULL,
  `geo_country` varchar(64) DEFAULT NULL,
  `geo_region` varchar(128) DEFAULT NULL,
  `geo_city` varchar(128) DEFAULT NULL,
  `lat` double DEFAULT NULL,
  `lon` double DEFAULT NULL,
  `asn` varchar(64) DEFAULT NULL,
  `ip_risk_score` int(11) DEFAULT 0,
  `reason` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `usersapp`
--

DROP TABLE IF EXISTS `usersapp`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `usersapp` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sccode` int(11) DEFAULT NULL,
  `email` varchar(100) NOT NULL,
  `username` varchar(100) DEFAULT NULL,
  `hash_salt_key` varchar(255) NOT NULL DEFAULT 'cklpns',
  `password_hash` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `failed_attempts` int(11) DEFAULT 0,
  `last_failed` timestamp NULL DEFAULT NULL,
  `lock_until` timestamp NULL DEFAULT NULL,
  `remember_token_hash` varchar(255) DEFAULT NULL,
  `remember_token_expires` timestamp NULL DEFAULT NULL,
  `password_salt` varchar(32) DEFAULT NULL,
  `reset_token_hash` varchar(255) DEFAULT NULL,
  `reset_token_expires` timestamp NULL DEFAULT NULL,
  `mfa_enabled` tinyint(4) NOT NULL DEFAULT 0,
  `mfa_type` enum('totp','sms','email','push') DEFAULT NULL,
  `mfa_secret` varchar(255) DEFAULT NULL,
  `mfa_temp_token` varchar(255) DEFAULT NULL,
  `mfa_temp_expires` timestamp NULL DEFAULT NULL,
  `two_factor` int(11) NOT NULL DEFAULT 0,
  `secretkey` varchar(150) DEFAULT NULL,
  `token` varchar(255) NOT NULL,
  `profilename` varchar(60) DEFAULT NULL,
  `mobile` varchar(11) DEFAULT NULL,
  `userlevel` varchar(25) NOT NULL DEFAULT 'Guest',
  `hiddenuser` int(11) NOT NULL DEFAULT 0,
  `userid` bigint(20) DEFAULT NULL,
  `photourl` varchar(255) NOT NULL,
  `firstlogin` datetime DEFAULT NULL,
  `lastlogin` datetime DEFAULT NULL,
  `lastaccess` datetime DEFAULT NULL,
  `posx` float DEFAULT NULL,
  `posy` float DEFAULT NULL,
  `status` int(11) NOT NULL DEFAULT 1,
  `otp` varchar(10) DEFAULT NULL,
  `otptime` datetime DEFAULT NULL,
  `fixedpin` varchar(64) DEFAULT NULL,
  `curexam` varchar(25) DEFAULT 'Half-Yearly',
  `session` int(11) NOT NULL DEFAULT 2024,
  `userdata1` varchar(100) DEFAULT NULL,
  `userdata2` varchar(100) DEFAULT NULL,
  `area` varchar(30) DEFAULT NULL,
  `ps` varchar(30) DEFAULT NULL,
  `dist` varchar(30) DEFAULT NULL,
  `admin` int(11) NOT NULL DEFAULT 0 COMMENT '0- No Admin, 1- 2-, 3-Admin, 4-, 5\r\n-Super Admin',
  `login_gmail` int(11) NOT NULL DEFAULT 1,
  `login_pass` int(11) NOT NULL DEFAULT 1,
  `login_token` int(11) NOT NULL DEFAULT 1,
  `login_qrcode` int(11) NOT NULL DEFAULT 1,
  `setup_done` int(11) NOT NULL DEFAULT 0,
  `whatsnew_last_id` int(11) NOT NULL DEFAULT 0,
  `reg_status` varchar(15) DEFAULT NULL,
  `reg_value` varchar(15) DEFAULT NULL,
  `active` int(11) NOT NULL DEFAULT 0,
  `theme` varchar(20) NOT NULL DEFAULT 'dark',
  `customcss` int(11) NOT NULL DEFAULT 0,
  `reset_otp` varchar(10) DEFAULT NULL,
  `reset_hash` varchar(32) DEFAULT NULL,
  `reset_link` varchar(150) DEFAULT NULL,
  `st_entry_fld` varchar(1024) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=978 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `usersapp_after_insert` AFTER INSERT ON `usersapp` FOR EACH ROW BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, new_data, changed_by)
        VALUES(
            'usersapp',
            'INSERT',
            NEW.`id`,
            JSON_OBJECT('id', NEW.`id`,'sccode', NEW.`sccode`,'email', NEW.`email`,'secretkey', NEW.`secretkey`,'token', NEW.`token`,'profilename', NEW.`profilename`,'mobile', NEW.`mobile`,'userlevel', NEW.`userlevel`,'hiddenuser', NEW.`hiddenuser`,'userid', NEW.`userid`,'photourl', NEW.`photourl`,'firstlogin', NEW.`firstlogin`,'lastlogin', NEW.`lastlogin`,'lastaccess', NEW.`lastaccess`,'posx', NEW.`posx`,'posy', NEW.`posy`,'status', NEW.`status`,'otp', NEW.`otp`,'otptime', NEW.`otptime`,'fixedpin', NEW.`fixedpin`,'curexam', NEW.`curexam`,'session', NEW.`session`,'userdata1', NEW.`userdata1`,'userdata2', NEW.`userdata2`,'area', NEW.`area`,'ps', NEW.`ps`,'dist', NEW.`dist`,'admin', NEW.`admin`,'login_gmail', NEW.`login_gmail`,'login_pass', NEW.`login_pass`,'login_token', NEW.`login_token`,'login_qrcode', NEW.`login_qrcode`,'setup_done', NEW.`setup_done`,'whatsnew_last_id', NEW.`whatsnew_last_id`,'reg_status', NEW.`reg_status`,'reg_value', NEW.`reg_value`,'active', NEW.`active`,'theme', NEW.`theme`,'customcss', NEW.`customcss`,'reset_otp', NEW.`reset_otp`,'reset_hash', NEW.`reset_hash`,'reset_link', NEW.`reset_link`),
            COALESCE(@current_user,'system')
        );
    END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `usersapp_after_update` AFTER UPDATE ON `usersapp` FOR EACH ROW BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, new_data, changed_by)
        VALUES(
            'usersapp',
            'UPDATE',
            NEW.`id`,
            JSON_OBJECT('id', OLD.`id`,'sccode', OLD.`sccode`,'email', OLD.`email`,'secretkey', OLD.`secretkey`,'token', OLD.`token`,'profilename', OLD.`profilename`,'mobile', OLD.`mobile`,'userlevel', OLD.`userlevel`,'hiddenuser', OLD.`hiddenuser`,'userid', OLD.`userid`,'photourl', OLD.`photourl`,'firstlogin', OLD.`firstlogin`,'lastlogin', OLD.`lastlogin`,'lastaccess', OLD.`lastaccess`,'posx', OLD.`posx`,'posy', OLD.`posy`,'status', OLD.`status`,'otp', OLD.`otp`,'otptime', OLD.`otptime`,'fixedpin', OLD.`fixedpin`,'curexam', OLD.`curexam`,'session', OLD.`session`,'userdata1', OLD.`userdata1`,'userdata2', OLD.`userdata2`,'area', OLD.`area`,'ps', OLD.`ps`,'dist', OLD.`dist`,'admin', OLD.`admin`,'login_gmail', OLD.`login_gmail`,'login_pass', OLD.`login_pass`,'login_token', OLD.`login_token`,'login_qrcode', OLD.`login_qrcode`,'setup_done', OLD.`setup_done`,'whatsnew_last_id', OLD.`whatsnew_last_id`,'reg_status', OLD.`reg_status`,'reg_value', OLD.`reg_value`,'active', OLD.`active`,'theme', OLD.`theme`,'customcss', OLD.`customcss`,'reset_otp', OLD.`reset_otp`,'reset_hash', OLD.`reset_hash`,'reset_link', OLD.`reset_link`),
            JSON_OBJECT('id', NEW.`id`,'sccode', NEW.`sccode`,'email', NEW.`email`,'secretkey', NEW.`secretkey`,'token', NEW.`token`,'profilename', NEW.`profilename`,'mobile', NEW.`mobile`,'userlevel', NEW.`userlevel`,'hiddenuser', NEW.`hiddenuser`,'userid', NEW.`userid`,'photourl', NEW.`photourl`,'firstlogin', NEW.`firstlogin`,'lastlogin', NEW.`lastlogin`,'lastaccess', NEW.`lastaccess`,'posx', NEW.`posx`,'posy', NEW.`posy`,'status', NEW.`status`,'otp', NEW.`otp`,'otptime', NEW.`otptime`,'fixedpin', NEW.`fixedpin`,'curexam', NEW.`curexam`,'session', NEW.`session`,'userdata1', NEW.`userdata1`,'userdata2', NEW.`userdata2`,'area', NEW.`area`,'ps', NEW.`ps`,'dist', NEW.`dist`,'admin', NEW.`admin`,'login_gmail', NEW.`login_gmail`,'login_pass', NEW.`login_pass`,'login_token', NEW.`login_token`,'login_qrcode', NEW.`login_qrcode`,'setup_done', NEW.`setup_done`,'whatsnew_last_id', NEW.`whatsnew_last_id`,'reg_status', NEW.`reg_status`,'reg_value', NEW.`reg_value`,'active', NEW.`active`,'theme', NEW.`theme`,'customcss', NEW.`customcss`,'reset_otp', NEW.`reset_otp`,'reset_hash', NEW.`reset_hash`,'reset_link', NEW.`reset_link`),
            COALESCE(@current_user,'system')
        );
    END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `usersapp_after_delete` AFTER DELETE ON `usersapp` FOR EACH ROW BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, changed_by)
        VALUES(
            'usersapp',
            'DELETE',
            OLD.`id`,
            JSON_OBJECT('id', OLD.`id`,'sccode', OLD.`sccode`,'email', OLD.`email`,'secretkey', OLD.`secretkey`,'token', OLD.`token`,'profilename', OLD.`profilename`,'mobile', OLD.`mobile`,'userlevel', OLD.`userlevel`,'hiddenuser', OLD.`hiddenuser`,'userid', OLD.`userid`,'photourl', OLD.`photourl`,'firstlogin', OLD.`firstlogin`,'lastlogin', OLD.`lastlogin`,'lastaccess', OLD.`lastaccess`,'posx', OLD.`posx`,'posy', OLD.`posy`,'status', OLD.`status`,'otp', OLD.`otp`,'otptime', OLD.`otptime`,'fixedpin', OLD.`fixedpin`,'curexam', OLD.`curexam`,'session', OLD.`session`,'userdata1', OLD.`userdata1`,'userdata2', OLD.`userdata2`,'area', OLD.`area`,'ps', OLD.`ps`,'dist', OLD.`dist`,'admin', OLD.`admin`,'login_gmail', OLD.`login_gmail`,'login_pass', OLD.`login_pass`,'login_token', OLD.`login_token`,'login_qrcode', OLD.`login_qrcode`,'setup_done', OLD.`setup_done`,'whatsnew_last_id', OLD.`whatsnew_last_id`,'reg_status', OLD.`reg_status`,'reg_value', OLD.`reg_value`,'active', OLD.`active`,'theme', OLD.`theme`,'customcss', OLD.`customcss`,'reset_otp', OLD.`reset_otp`,'reset_hash', OLD.`reset_hash`,'reset_link', OLD.`reset_link`),
            COALESCE(@current_user,'system')
        );
    END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `usersapp_new`
--

DROP TABLE IF EXISTS `usersapp_new`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `usersapp_new` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL,
  `fullname` varchar(63) DEFAULT NULL,
  `username` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `user_level` varchar(25) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `failed_attempts` int(11) DEFAULT 0,
  `last_failed` timestamp NULL DEFAULT NULL,
  `lock_until` timestamp NULL DEFAULT NULL,
  `remember_token_hash` varchar(255) DEFAULT NULL,
  `remember_token_expires` timestamp NULL DEFAULT NULL,
  `password_salt` varchar(32) DEFAULT NULL,
  `reset_token_hash` varchar(255) DEFAULT NULL,
  `reset_token_expires` timestamp NULL DEFAULT NULL,
  `mfa_enabled` tinyint(1) DEFAULT 0,
  `mfa_type` enum('totp','sms','email','push') DEFAULT NULL,
  `mfa_secret` varchar(255) DEFAULT NULL,
  `mfa_temp_token` varchar(255) DEFAULT NULL,
  `mfa_temp_expires` timestamp NULL DEFAULT NULL,
  `first_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `dob` date DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping events for database 'secure_auth'
--

--
-- Dumping routines for database 'secure_auth'
--
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `sp_recalc_user_points_by_email` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_recalc_user_points_by_email`(IN p_email VARCHAR(120))
BEGIN
  DECLARE v_action_points BIGINT DEFAULT 0;
  DECLARE v_achievement_points BIGINT DEFAULT 0;
  DECLARE v_total BIGINT DEFAULT 0;

  SELECT COALESCE(SUM(points),0) INTO v_action_points FROM user_actions WHERE email = p_email;
  SELECT COALESCE(SUM(a.points),0) INTO v_achievement_points
    FROM user_achievements ua
    JOIN achievements_list a ON ua.achievement_id = a.id
    WHERE ua.email = p_email;

  SET v_total = COALESCE(v_action_points,0) + COALESCE(v_achievement_points,0);

  -- optional: update cached users.total_points if that column exists
  -- UPDATE users SET total_points = v_total WHERE email = p_email;

  -- return the total for callers
  SELECT v_action_points AS action_points, v_achievement_points AS achievement_points, v_total AS total_points;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-10-28  3:02:32
