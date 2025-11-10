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
-- Table structure for table `accesslevel`
--

DROP TABLE IF EXISTS `accesslevel`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `accesslevel` (
  `permission` varchar(255) NOT NULL,
  `platinum` int(11) NOT NULL,
  `gold` int(11) NOT NULL,
  `silver` int(11) NOT NULL,
  `bronge` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `accounts`
--

DROP TABLE IF EXISTS `accounts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `accounts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sccode` varchar(20) NOT NULL,
  `company` varchar(100) NOT NULL,
  `descn` varchar(100) NOT NULL,
  `amount` decimal(11,2) NOT NULL,
  `date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `sms` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `achievement_events`
--

DROP TABLE IF EXISTS `achievement_events`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `achievement_events` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `achievement_id` int(10) unsigned NOT NULL,
  `event_type` enum('awarded','revoked') NOT NULL,
  `reason` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `email` varchar(120) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `achievements_category`
--

DROP TABLE IF EXISTS `achievements_category`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `achievements_category` (
  `sl` int(11) NOT NULL DEFAULT 0,
  `created_by` varchar(100) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `category` varchar(25) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
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
-- Table structure for table `areas`
--

DROP TABLE IF EXISTS `areas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `areas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `idno` int(11) DEFAULT 0,
  `user` varchar(150) NOT NULL,
  `slot` varchar(20) NOT NULL DEFAULT 'School',
  `medium` varchar(10) NOT NULL DEFAULT 'Bengali',
  `version` varchar(10) NOT NULL DEFAULT 'Bengali',
  `areaname` varchar(255) NOT NULL,
  `subarea` varchar(20) NOT NULL,
  `sessionyear` varchar(7) NOT NULL DEFAULT '2024',
  `yesno` int(11) NOT NULL DEFAULT 1,
  `classteacher` bigint(20) DEFAULT NULL,
  `rollfrom` int(11) DEFAULT NULL,
  `rollto` int(11) DEFAULT NULL,
  `combind_1` int(11) DEFAULT NULL,
  `combind_2` int(11) DEFAULT NULL,
  `combind_3` int(11) DEFAULT NULL,
  `combind_4` int(11) DEFAULT NULL,
  `fourth` int(11) DEFAULT NULL,
  `allsubject` varchar(200) DEFAULT NULL,
  `half` int(11) NOT NULL DEFAULT 0 COMMENT 'Result Entry Req ',
  `full` int(11) NOT NULL DEFAULT 0 COMMENT 'Result Entry Req ',
  `halfdone` int(11) NOT NULL DEFAULT 0,
  `fulldone` int(11) NOT NULL DEFAULT 0,
  `entrytime` timestamp NOT NULL DEFAULT current_timestamp(),
  `sccode` int(11) DEFAULT NULL,
  `modifieddate` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
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
) ENGINE=InnoDB AUTO_INCREMENT=263 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `billing_invoices`
--

DROP TABLE IF EXISTS `billing_invoices`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `billing_invoices` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sccode` varchar(20) NOT NULL,
  `customer_name` varchar(50) DEFAULT NULL,
  `invoice_no` varchar(30) NOT NULL,
  `invoice_date` date NOT NULL,
  `due_date` date DEFAULT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `discount` decimal(10,2) DEFAULT 0.00,
  `vat_percent` decimal(5,2) DEFAULT 0.00,
  `vat_amount` decimal(10,2) DEFAULT 0.00,
  `grand_total` decimal(10,2) NOT NULL,
  `paid_amount` decimal(10,2) DEFAULT 0.00,
  `due_amount` decimal(10,2) DEFAULT 0.00,
  `payment_status` enum('unpaid','partial','paid','cancelled') DEFAULT 'unpaid',
  `created_by` varchar(50) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `invoice_no` (`invoice_no`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `billing_items`
--

DROP TABLE IF EXISTS `billing_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `billing_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `invoice_id` int(11) NOT NULL,
  `description` varchar(255) NOT NULL,
  `qty` decimal(10,2) DEFAULT 1.00,
  `rate` decimal(10,2) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `invoice_id` (`invoice_id`),
  CONSTRAINT `billing_items_ibfk_1` FOREIGN KEY (`invoice_id`) REFERENCES `billing_invoices` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `billing_payments`
--

DROP TABLE IF EXISTS `billing_payments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `billing_payments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `invoice_id` int(11) NOT NULL,
  `payment_date` date NOT NULL,
  `payment_method` enum('cash','bank','bkash','nagad','card','other') DEFAULT 'cash',
  `transaction_id` varchar(50) DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `remarks` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `invoice_id` (`invoice_id`),
  CONSTRAINT `billing_payments_ibfk_1` FOREIGN KEY (`invoice_id`) REFERENCES `billing_invoices` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
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
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dev_notes`
--

DROP TABLE IF EXISTS `dev_notes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dev_notes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ref_id` int(11) DEFAULT NULL,
  `sccode` int(11) DEFAULT NULL,
  `ticket_id` int(11) NOT NULL,
  `admin_id` int(11) NOT NULL,
  `note_line` text NOT NULL,
  `status` enum('New','Open','Waiting','Replied','Progress','Hold','Resolved','Closed') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `ticket_id` (`ticket_id`)
) ENGINE=InnoDB AUTO_INCREMENT=67 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
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
) ENGINE=InnoDB AUTO_INCREMENT=180 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `examlist`
--

DROP TABLE IF EXISTS `examlist`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `examlist` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sccode` int(11) DEFAULT NULL,
  `sessionyear` varchar(11) DEFAULT NULL,
  `slot` varchar(20) NOT NULL DEFAULT 'School',
  `examtitle` varchar(50) DEFAULT NULL,
  `examcode` varchar(25) DEFAULT NULL,
  `linkedexam` int(11) DEFAULT NULL,
  `exam_group` varchar(25) DEFAULT NULL,
  `exam_type` varchar(3) DEFAULT 'PE',
  `classname` varchar(25) DEFAULT NULL,
  `sectionname` varchar(25) DEFAULT NULL,
  `datestart` date DEFAULT NULL,
  `result_publish` datetime DEFAULT NULL,
  `createdby` varchar(100) DEFAULT NULL,
  `createtime` datetime DEFAULT NULL,
  `status` int(11) DEFAULT 0,
  `hall_code` varchar(512) DEFAULT NULL,
  `modifieddate` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=81 DEFAULT CHARSET=utf32 COLLATE=utf32_unicode_ci;
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
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `examlist_after_insert` AFTER INSERT ON `examlist` FOR EACH ROW BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, new_data, changed_by)
        VALUES(
            'examlist',
            'INSERT',
            NEW.`id`,
            JSON_OBJECT('id', NEW.`id`,'sccode', NEW.`sccode`,'sessionyear', NEW.`sessionyear`,'slot', NEW.`slot`,'examtitle', NEW.`examtitle`,'examcode', NEW.`examcode`,'linkedexam', NEW.`linkedexam`,'exam_group', NEW.`exam_group`,'exam_type', NEW.`exam_type`,'classname', NEW.`classname`,'sectionname', NEW.`sectionname`,'datestart', NEW.`datestart`,'result_publish', NEW.`result_publish`,'createdby', NEW.`createdby`,'createtime', NEW.`createtime`,'status', NEW.`status`,'hall_code', NEW.`hall_code`,'modifieddate', NEW.`modifieddate`),
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
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `examlist_after_update` AFTER UPDATE ON `examlist` FOR EACH ROW BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, new_data, changed_by)
        VALUES(
            'examlist',
            'UPDATE',
            NEW.`id`,
            JSON_OBJECT('id', OLD.`id`,'sccode', OLD.`sccode`,'sessionyear', OLD.`sessionyear`,'slot', OLD.`slot`,'examtitle', OLD.`examtitle`,'examcode', OLD.`examcode`,'linkedexam', OLD.`linkedexam`,'exam_group', OLD.`exam_group`,'exam_type', OLD.`exam_type`,'classname', OLD.`classname`,'sectionname', OLD.`sectionname`,'datestart', OLD.`datestart`,'result_publish', OLD.`result_publish`,'createdby', OLD.`createdby`,'createtime', OLD.`createtime`,'status', OLD.`status`,'hall_code', OLD.`hall_code`,'modifieddate', OLD.`modifieddate`),
            JSON_OBJECT('id', NEW.`id`,'sccode', NEW.`sccode`,'sessionyear', NEW.`sessionyear`,'slot', NEW.`slot`,'examtitle', NEW.`examtitle`,'examcode', NEW.`examcode`,'linkedexam', NEW.`linkedexam`,'exam_group', NEW.`exam_group`,'exam_type', NEW.`exam_type`,'classname', NEW.`classname`,'sectionname', NEW.`sectionname`,'datestart', NEW.`datestart`,'result_publish', NEW.`result_publish`,'createdby', NEW.`createdby`,'createtime', NEW.`createtime`,'status', NEW.`status`,'hall_code', NEW.`hall_code`,'modifieddate', NEW.`modifieddate`),
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
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `examlist_after_delete` AFTER DELETE ON `examlist` FOR EACH ROW BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, changed_by)
        VALUES(
            'examlist',
            'DELETE',
            OLD.`id`,
            JSON_OBJECT('id', OLD.`id`,'sccode', OLD.`sccode`,'sessionyear', OLD.`sessionyear`,'slot', OLD.`slot`,'examtitle', OLD.`examtitle`,'examcode', OLD.`examcode`,'linkedexam', OLD.`linkedexam`,'exam_group', OLD.`exam_group`,'exam_type', OLD.`exam_type`,'classname', OLD.`classname`,'sectionname', OLD.`sectionname`,'datestart', OLD.`datestart`,'result_publish', OLD.`result_publish`,'createdby', OLD.`createdby`,'createtime', OLD.`createtime`,'status', OLD.`status`,'hall_code', OLD.`hall_code`,'modifieddate', OLD.`modifieddate`),
            COALESCE(@current_user,'system')
        );
    END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

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
-- Table structure for table `financesetup`
--

DROP TABLE IF EXISTS `financesetup`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `financesetup` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sccode` int(11) NOT NULL,
  `slot` varchar(25) NOT NULL DEFAULT 'School',
  `sessionyear` varchar(7) NOT NULL,
  `slno` int(11) DEFAULT NULL,
  `itemcode` varchar(30) DEFAULT NULL,
  `particulareng` varchar(255) DEFAULT NULL,
  `particularben` varchar(255) DEFAULT NULL,
  `new_only` int(11) NOT NULL DEFAULT 0,
  `splitable` int(11) NOT NULL DEFAULT 0,
  `play` int(11) DEFAULT 0,
  `nursery` int(11) DEFAULT 0,
  `one` int(11) DEFAULT 0,
  `two` int(11) DEFAULT 0,
  `three` int(11) DEFAULT 0,
  `four` int(11) DEFAULT 0,
  `five` int(11) DEFAULT 0,
  `six` int(11) NOT NULL DEFAULT 0,
  `seven` int(11) NOT NULL DEFAULT 0,
  `eight` int(11) NOT NULL DEFAULT 0,
  `nine` int(11) NOT NULL DEFAULT 0,
  `ten` int(11) NOT NULL DEFAULT 0,
  `play_update` datetime DEFAULT NULL,
  `nursery_update` datetime DEFAULT NULL,
  `one_update` datetime DEFAULT NULL,
  `two_update` datetime DEFAULT NULL,
  `three_update` datetime DEFAULT NULL,
  `four_update` datetime DEFAULT NULL,
  `five_update` datetime DEFAULT NULL,
  `six_update` datetime DEFAULT NULL,
  `seven_update` datetime DEFAULT NULL,
  `eight_update` datetime DEFAULT NULL,
  `nine_update` datetime DEFAULT NULL,
  `ten_update` datetime DEFAULT NULL,
  `month` int(11) NOT NULL DEFAULT 1,
  `inexin` int(11) NOT NULL DEFAULT 0,
  `inexex` int(11) NOT NULL DEFAULT 0,
  `cheque` int(11) NOT NULL DEFAULT 0 COMMENT 'issue cheque on this category',
  `custom` int(11) NOT NULL DEFAULT 0,
  `last_update` datetime DEFAULT NULL,
  `need_update` int(11) NOT NULL DEFAULT 1,
  `validationtime` datetime DEFAULT '2024-01-01 00:00:00',
  `modifieddate` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=592 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
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
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `financesetup_after_insert` AFTER INSERT ON `financesetup` FOR EACH ROW BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, new_data, changed_by)
        VALUES(
            'financesetup',
            'INSERT',
            NEW.`id`,
            JSON_OBJECT('id', NEW.`id`,'sccode', NEW.`sccode`,'slot', NEW.`slot`,'sessionyear', NEW.`sessionyear`,'slno', NEW.`slno`,'itemcode', NEW.`itemcode`,'particulareng', NEW.`particulareng`,'particularben', NEW.`particularben`,'new_only', NEW.`new_only`,'splitable', NEW.`splitable`,'play', NEW.`play`,'nursery', NEW.`nursery`,'one', NEW.`one`,'two', NEW.`two`,'three', NEW.`three`,'four', NEW.`four`,'five', NEW.`five`,'six', NEW.`six`,'seven', NEW.`seven`,'eight', NEW.`eight`,'nine', NEW.`nine`,'ten', NEW.`ten`,'play_update', NEW.`play_update`,'nursery_update', NEW.`nursery_update`,'one_update', NEW.`one_update`,'two_update', NEW.`two_update`,'three_update', NEW.`three_update`,'four_update', NEW.`four_update`,'five_update', NEW.`five_update`,'six_update', NEW.`six_update`,'seven_update', NEW.`seven_update`,'eight_update', NEW.`eight_update`,'nine_update', NEW.`nine_update`,'ten_update', NEW.`ten_update`,'month', NEW.`month`,'inexin', NEW.`inexin`,'inexex', NEW.`inexex`,'cheque', NEW.`cheque`,'custom', NEW.`custom`,'last_update', NEW.`last_update`,'need_update', NEW.`need_update`,'validationtime', NEW.`validationtime`,'modifieddate', NEW.`modifieddate`),
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
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `financesetup_after_update` AFTER UPDATE ON `financesetup` FOR EACH ROW BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, new_data, changed_by)
        VALUES(
            'financesetup',
            'UPDATE',
            NEW.`id`,
            JSON_OBJECT('id', OLD.`id`,'sccode', OLD.`sccode`,'slot', OLD.`slot`,'sessionyear', OLD.`sessionyear`,'slno', OLD.`slno`,'itemcode', OLD.`itemcode`,'particulareng', OLD.`particulareng`,'particularben', OLD.`particularben`,'new_only', OLD.`new_only`,'splitable', OLD.`splitable`,'play', OLD.`play`,'nursery', OLD.`nursery`,'one', OLD.`one`,'two', OLD.`two`,'three', OLD.`three`,'four', OLD.`four`,'five', OLD.`five`,'six', OLD.`six`,'seven', OLD.`seven`,'eight', OLD.`eight`,'nine', OLD.`nine`,'ten', OLD.`ten`,'play_update', OLD.`play_update`,'nursery_update', OLD.`nursery_update`,'one_update', OLD.`one_update`,'two_update', OLD.`two_update`,'three_update', OLD.`three_update`,'four_update', OLD.`four_update`,'five_update', OLD.`five_update`,'six_update', OLD.`six_update`,'seven_update', OLD.`seven_update`,'eight_update', OLD.`eight_update`,'nine_update', OLD.`nine_update`,'ten_update', OLD.`ten_update`,'month', OLD.`month`,'inexin', OLD.`inexin`,'inexex', OLD.`inexex`,'cheque', OLD.`cheque`,'custom', OLD.`custom`,'last_update', OLD.`last_update`,'need_update', OLD.`need_update`,'validationtime', OLD.`validationtime`,'modifieddate', OLD.`modifieddate`),
            JSON_OBJECT('id', NEW.`id`,'sccode', NEW.`sccode`,'slot', NEW.`slot`,'sessionyear', NEW.`sessionyear`,'slno', NEW.`slno`,'itemcode', NEW.`itemcode`,'particulareng', NEW.`particulareng`,'particularben', NEW.`particularben`,'new_only', NEW.`new_only`,'splitable', NEW.`splitable`,'play', NEW.`play`,'nursery', NEW.`nursery`,'one', NEW.`one`,'two', NEW.`two`,'three', NEW.`three`,'four', NEW.`four`,'five', NEW.`five`,'six', NEW.`six`,'seven', NEW.`seven`,'eight', NEW.`eight`,'nine', NEW.`nine`,'ten', NEW.`ten`,'play_update', NEW.`play_update`,'nursery_update', NEW.`nursery_update`,'one_update', NEW.`one_update`,'two_update', NEW.`two_update`,'three_update', NEW.`three_update`,'four_update', NEW.`four_update`,'five_update', NEW.`five_update`,'six_update', NEW.`six_update`,'seven_update', NEW.`seven_update`,'eight_update', NEW.`eight_update`,'nine_update', NEW.`nine_update`,'ten_update', NEW.`ten_update`,'month', NEW.`month`,'inexin', NEW.`inexin`,'inexex', NEW.`inexex`,'cheque', NEW.`cheque`,'custom', NEW.`custom`,'last_update', NEW.`last_update`,'need_update', NEW.`need_update`,'validationtime', NEW.`validationtime`,'modifieddate', NEW.`modifieddate`),
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
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `financesetup_after_delete` AFTER DELETE ON `financesetup` FOR EACH ROW BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, changed_by)
        VALUES(
            'financesetup',
            'DELETE',
            OLD.`id`,
            JSON_OBJECT('id', OLD.`id`,'sccode', OLD.`sccode`,'slot', OLD.`slot`,'sessionyear', OLD.`sessionyear`,'slno', OLD.`slno`,'itemcode', OLD.`itemcode`,'particulareng', OLD.`particulareng`,'particularben', OLD.`particularben`,'new_only', OLD.`new_only`,'splitable', OLD.`splitable`,'play', OLD.`play`,'nursery', OLD.`nursery`,'one', OLD.`one`,'two', OLD.`two`,'three', OLD.`three`,'four', OLD.`four`,'five', OLD.`five`,'six', OLD.`six`,'seven', OLD.`seven`,'eight', OLD.`eight`,'nine', OLD.`nine`,'ten', OLD.`ten`,'play_update', OLD.`play_update`,'nursery_update', OLD.`nursery_update`,'one_update', OLD.`one_update`,'two_update', OLD.`two_update`,'three_update', OLD.`three_update`,'four_update', OLD.`four_update`,'five_update', OLD.`five_update`,'six_update', OLD.`six_update`,'seven_update', OLD.`seven_update`,'eight_update', OLD.`eight_update`,'nine_update', OLD.`nine_update`,'ten_update', OLD.`ten_update`,'month', OLD.`month`,'inexin', OLD.`inexin`,'inexex', OLD.`inexex`,'cheque', OLD.`cheque`,'custom', OLD.`custom`,'last_update', OLD.`last_update`,'need_update', OLD.`need_update`,'validationtime', OLD.`validationtime`,'modifieddate', OLD.`modifieddate`),
            COALESCE(@current_user,'system')
        );
    END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `financesetupvalue`
--

DROP TABLE IF EXISTS `financesetupvalue`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `financesetupvalue` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sccode` int(11) NOT NULL,
  `slot` varchar(25) NOT NULL DEFAULT 'School',
  `sessionyear` varchar(7) DEFAULT NULL,
  `slno` int(11) DEFAULT NULL,
  `itemcode` varchar(32) DEFAULT NULL,
  `new_only` int(11) NOT NULL DEFAULT 0,
  `splitable` int(11) NOT NULL DEFAULT 0,
  `classname` varchar(20) DEFAULT NULL,
  `sectionname` varchar(20) DEFAULT NULL,
  `amount` int(11) DEFAULT 0,
  `update_time` datetime DEFAULT NULL,
  `month` int(11) NOT NULL DEFAULT 1,
  `inexin` int(11) NOT NULL DEFAULT 0,
  `inexex` int(11) NOT NULL DEFAULT 0,
  `cheque` int(11) NOT NULL DEFAULT 0 COMMENT 'issue cheque on this category',
  `custom` int(11) NOT NULL DEFAULT 0,
  `last_update` datetime DEFAULT NULL,
  `need_update` int(11) NOT NULL DEFAULT 1,
  `validationtime` datetime DEFAULT '2024-01-01 00:00:00',
  `modifieddate` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=219 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
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
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `financesetupvalue_after_insert` AFTER INSERT ON `financesetupvalue` FOR EACH ROW BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, new_data, changed_by)
        VALUES(
            'financesetupvalue',
            'INSERT',
            NEW.`id`,
            JSON_OBJECT('id', NEW.`id`,'sccode', NEW.`sccode`,'slot', NEW.`slot`,'sessionyear', NEW.`sessionyear`,'slno', NEW.`slno`,'itemcode', NEW.`itemcode`,'new_only', NEW.`new_only`,'splitable', NEW.`splitable`,'classname', NEW.`classname`,'sectionname', NEW.`sectionname`,'amount', NEW.`amount`,'update_time', NEW.`update_time`,'month', NEW.`month`,'inexin', NEW.`inexin`,'inexex', NEW.`inexex`,'cheque', NEW.`cheque`,'custom', NEW.`custom`,'last_update', NEW.`last_update`,'need_update', NEW.`need_update`,'validationtime', NEW.`validationtime`,'modifieddate', NEW.`modifieddate`),
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
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `financesetupvalue_after_update` AFTER UPDATE ON `financesetupvalue` FOR EACH ROW BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, new_data, changed_by)
        VALUES(
            'financesetupvalue',
            'UPDATE',
            NEW.`id`,
            JSON_OBJECT('id', OLD.`id`,'sccode', OLD.`sccode`,'slot', OLD.`slot`,'sessionyear', OLD.`sessionyear`,'slno', OLD.`slno`,'itemcode', OLD.`itemcode`,'new_only', OLD.`new_only`,'splitable', OLD.`splitable`,'classname', OLD.`classname`,'sectionname', OLD.`sectionname`,'amount', OLD.`amount`,'update_time', OLD.`update_time`,'month', OLD.`month`,'inexin', OLD.`inexin`,'inexex', OLD.`inexex`,'cheque', OLD.`cheque`,'custom', OLD.`custom`,'last_update', OLD.`last_update`,'need_update', OLD.`need_update`,'validationtime', OLD.`validationtime`,'modifieddate', OLD.`modifieddate`),
            JSON_OBJECT('id', NEW.`id`,'sccode', NEW.`sccode`,'slot', NEW.`slot`,'sessionyear', NEW.`sessionyear`,'slno', NEW.`slno`,'itemcode', NEW.`itemcode`,'new_only', NEW.`new_only`,'splitable', NEW.`splitable`,'classname', NEW.`classname`,'sectionname', NEW.`sectionname`,'amount', NEW.`amount`,'update_time', NEW.`update_time`,'month', NEW.`month`,'inexin', NEW.`inexin`,'inexex', NEW.`inexex`,'cheque', NEW.`cheque`,'custom', NEW.`custom`,'last_update', NEW.`last_update`,'need_update', NEW.`need_update`,'validationtime', NEW.`validationtime`,'modifieddate', NEW.`modifieddate`),
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
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `financesetupvalue_after_delete` AFTER DELETE ON `financesetupvalue` FOR EACH ROW BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, changed_by)
        VALUES(
            'financesetupvalue',
            'DELETE',
            OLD.`id`,
            JSON_OBJECT('id', OLD.`id`,'sccode', OLD.`sccode`,'slot', OLD.`slot`,'sessionyear', OLD.`sessionyear`,'slno', OLD.`slno`,'itemcode', OLD.`itemcode`,'new_only', OLD.`new_only`,'splitable', OLD.`splitable`,'classname', OLD.`classname`,'sectionname', OLD.`sectionname`,'amount', OLD.`amount`,'update_time', OLD.`update_time`,'month', OLD.`month`,'inexin', OLD.`inexin`,'inexex', OLD.`inexex`,'cheque', OLD.`cheque`,'custom', OLD.`custom`,'last_update', OLD.`last_update`,'need_update', OLD.`need_update`,'validationtime', OLD.`validationtime`,'modifieddate', OLD.`modifieddate`),
            COALESCE(@current_user,'system')
        );
    END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `globalsettings`
--

DROP TABLE IF EXISTS `globalsettings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `globalsettings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sccode` int(11) DEFAULT NULL,
  `stattnd_sort` varchar(10) DEFAULT 'rollno',
  `stattnd_multi` int(11) DEFAULT 1,
  `tattnd` int(11) DEFAULT 0,
  `collection` int(11) NOT NULL DEFAULT 0 COMMENT '0 = Class Teacher, 1 = Administrator, 2 = Head Teacher',
  `tattndradius` int(11) NOT NULL DEFAULT 50,
  `tattndout` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=88 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
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
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `globalsettings_after_insert` AFTER INSERT ON `globalsettings` FOR EACH ROW BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, new_data, changed_by)
        VALUES(
            'globalsettings',
            'INSERT',
            NEW.`id`,
            JSON_OBJECT('id', NEW.`id`,'sccode', NEW.`sccode`,'stattnd_sort', NEW.`stattnd_sort`,'stattnd_multi', NEW.`stattnd_multi`,'tattnd', NEW.`tattnd`,'collection', NEW.`collection`,'tattndradius', NEW.`tattndradius`,'tattndout', NEW.`tattndout`),
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
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `globalsettings_after_update` AFTER UPDATE ON `globalsettings` FOR EACH ROW BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, new_data, changed_by)
        VALUES(
            'globalsettings',
            'UPDATE',
            NEW.`id`,
            JSON_OBJECT('id', OLD.`id`,'sccode', OLD.`sccode`,'stattnd_sort', OLD.`stattnd_sort`,'stattnd_multi', OLD.`stattnd_multi`,'tattnd', OLD.`tattnd`,'collection', OLD.`collection`,'tattndradius', OLD.`tattndradius`,'tattndout', OLD.`tattndout`),
            JSON_OBJECT('id', NEW.`id`,'sccode', NEW.`sccode`,'stattnd_sort', NEW.`stattnd_sort`,'stattnd_multi', NEW.`stattnd_multi`,'tattnd', NEW.`tattnd`,'collection', NEW.`collection`,'tattndradius', NEW.`tattndradius`,'tattndout', NEW.`tattndout`),
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
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `globalsettings_after_delete` AFTER DELETE ON `globalsettings` FOR EACH ROW BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, changed_by)
        VALUES(
            'globalsettings',
            'DELETE',
            OLD.`id`,
            JSON_OBJECT('id', OLD.`id`,'sccode', OLD.`sccode`,'stattnd_sort', OLD.`stattnd_sort`,'stattnd_multi', OLD.`stattnd_multi`,'tattnd', OLD.`tattnd`,'collection', OLD.`collection`,'tattndradius', OLD.`tattndradius`,'tattndout', OLD.`tattndout`),
            COALESCE(@current_user,'system')
        );
    END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

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
  `filesize` float NOT NULL DEFAULT 0,
  `platform` varchar(120) DEFAULT NULL,
  `browser` varchar(120) DEFAULT NULL,
  `entrytime` datetime DEFAULT NULL,
  `modifieddate` datetime DEFAULT NULL,
  `bandwidth` bigint(20) DEFAULT 0,
  `ipaddr` varchar(45) DEFAULT NULL,
  `location` varchar(100) DEFAULT NULL,
  `duration` int(11) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `idx_email` (`email`),
  KEY `idx_page` (`pagename`),
  KEY `idx_time` (`entrytime`),
  KEY `idx_logbook_email_entry` (`email`,`entrytime`),
  KEY `idx_logbook_page` (`pagename`)
) ENGINE=InnoDB AUTO_INCREMENT=7059 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
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
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
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
  `root_page` varchar(50) DEFAULT NULL,
  `entryby` varchar(120) DEFAULT NULL,
  `modifieddate` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=65 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
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
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
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
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
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
-- Table structure for table `payments`
--

DROP TABLE IF EXISTS `payments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `payments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `invoice_no` varchar(40) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `bkash_payment_id` varchar(60) DEFAULT NULL,
  `trx_id` varchar(50) DEFAULT NULL,
  `status` enum('Pending','Success','Failed') DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
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
) ENGINE=InnoDB AUTO_INCREMENT=109 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
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
) ENGINE=InnoDB AUTO_INCREMENT=128 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
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
-- Table structure for table `registrations`
--

DROP TABLE IF EXISTS `registrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `registrations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sessionyear` varchar(10) NOT NULL DEFAULT '2026',
  `sccode` int(11) DEFAULT NULL,
  `roll_no` int(11) DEFAULT NULL,
  `reg_id` varchar(32) NOT NULL,
  `pin` varchar(10) NOT NULL,
  `stnameeng` varchar(255) DEFAULT NULL,
  `stnameben` varchar(255) DEFAULT NULL,
  `fname` varchar(255) DEFAULT NULL,
  `mname` varchar(255) DEFAULT NULL,
  `mnumber` varchar(30) DEFAULT NULL,
  `dist` varchar(120) DEFAULT NULL,
  `ps` varchar(120) DEFAULT NULL,
  `po` varchar(120) DEFAULT NULL,
  `village` varchar(255) DEFAULT NULL,
  `testno` varchar(100) DEFAULT NULL,
  `insdist` varchar(120) DEFAULT NULL,
  `insps` varchar(120) DEFAULT NULL,
  `inspo` varchar(120) DEFAULT NULL,
  `insname` varchar(255) DEFAULT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `otp` varchar(10) DEFAULT NULL,
  `otp_expires` datetime DEFAULT NULL,
  `verified` tinyint(1) DEFAULT 0,
  `verifytime` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
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
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
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
  `display` int(11) NOT NULL DEFAULT 1,
  `last_login_time` datetime DEFAULT NULL,
  `sms_send` int(11) NOT NULL DEFAULT 0,
  `sms_success` int(11) NOT NULL DEFAULT 0,
  `sms_error` int(11) NOT NULL DEFAULT 0,
  `sms_cost` float NOT NULL DEFAULT 0,
  `sms_balance` float NOT NULL DEFAULT 0,
  `account_balance` float NOT NULL DEFAULT 0,
  `admin_data` varchar(1024) DEFAULT NULL,
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
  `active` int(11) NOT NULL DEFAULT 0,
  `status` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=278 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
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
-- Table structure for table `sessioninfo`
--

DROP TABLE IF EXISTS `sessioninfo`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sessioninfo` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `stid` varchar(11) DEFAULT NULL,
  `sessionyear` varchar(9) NOT NULL,
  `classname` varchar(20) DEFAULT NULL,
  `sectionname` varchar(30) NOT NULL,
  `rollno` int(11) NOT NULL,
  `sccode` int(11) NOT NULL,
  `slot` varchar(20) NOT NULL DEFAULT 'School',
  `medium` varchar(10) NOT NULL DEFAULT 'Bengali',
  `version` varchar(10) NOT NULL DEFAULT 'Bengali',
  `fine` int(11) NOT NULL DEFAULT 0,
  `icardst` varchar(10) NOT NULL DEFAULT '0',
  `rfidtag` varchar(12) DEFAULT NULL,
  `fourth_subject` int(11) NOT NULL DEFAULT 0,
  `voter_no` int(11) DEFAULT NULL,
  `groupname` varchar(30) DEFAULT NULL,
  `status` int(11) NOT NULL DEFAULT 1,
  `gender` varchar(4) DEFAULT NULL,
  `religion` varchar(10) DEFAULT NULL,
  `finsetup` int(11) NOT NULL DEFAULT 0,
  `lastpr` bigint(20) DEFAULT NULL,
  `real_tution` int(11) NOT NULL DEFAULT 0,
  `sector` varchar(50) DEFAULT NULL,
  `rate` int(11) NOT NULL DEFAULT 100,
  `amount` int(11) NOT NULL DEFAULT 0,
  `trackyesterday` varchar(20) DEFAULT NULL,
  `tracktoday` varchar(20) DEFAULT NULL,
  `subject_list` varchar(100) DEFAULT NULL,
  `validate` int(11) DEFAULT 0,
  `validationtime` datetime NOT NULL DEFAULT '2024-01-01 00:00:00',
  `modifieddate` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=285 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
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
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `sessioninfo_after_insert` AFTER INSERT ON `sessioninfo` FOR EACH ROW BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, new_data, changed_by)
        VALUES(
            'sessioninfo',
            'INSERT',
            NEW.`id`,
            JSON_OBJECT('id', NEW.`id`,'stid', NEW.`stid`,'sessionyear', NEW.`sessionyear`,'classname', NEW.`classname`,'sectionname', NEW.`sectionname`,'rollno', NEW.`rollno`,'sccode', NEW.`sccode`,'slot', NEW.`slot`,'medium', NEW.`medium`,'version', NEW.`version`,'fine', NEW.`fine`,'icardst', NEW.`icardst`,'rfidtag', NEW.`rfidtag`,'fourth_subject', NEW.`fourth_subject`,'voter_no', NEW.`voter_no`,'groupname', NEW.`groupname`,'status', NEW.`status`,'gender', NEW.`gender`,'religion', NEW.`religion`,'finsetup', NEW.`finsetup`,'lastpr', NEW.`lastpr`,'real_tution', NEW.`real_tution`,'sector', NEW.`sector`,'rate', NEW.`rate`,'amount', NEW.`amount`,'trackyesterday', NEW.`trackyesterday`,'tracktoday', NEW.`tracktoday`,'validate', NEW.`validate`,'validationtime', NEW.`validationtime`,'modifieddate', NEW.`modifieddate`),
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
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `sessioninfo_after_update` AFTER UPDATE ON `sessioninfo` FOR EACH ROW BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, new_data, changed_by)
        VALUES(
            'sessioninfo',
            'UPDATE',
            NEW.`id`,
            JSON_OBJECT('id', OLD.`id`,'stid', OLD.`stid`,'sessionyear', OLD.`sessionyear`,'classname', OLD.`classname`,'sectionname', OLD.`sectionname`,'rollno', OLD.`rollno`,'sccode', OLD.`sccode`,'slot', OLD.`slot`,'medium', OLD.`medium`,'version', OLD.`version`,'fine', OLD.`fine`,'icardst', OLD.`icardst`,'rfidtag', OLD.`rfidtag`,'fourth_subject', OLD.`fourth_subject`,'voter_no', OLD.`voter_no`,'groupname', OLD.`groupname`,'status', OLD.`status`,'gender', OLD.`gender`,'religion', OLD.`religion`,'finsetup', OLD.`finsetup`,'lastpr', OLD.`lastpr`,'real_tution', OLD.`real_tution`,'sector', OLD.`sector`,'rate', OLD.`rate`,'amount', OLD.`amount`,'trackyesterday', OLD.`trackyesterday`,'tracktoday', OLD.`tracktoday`,'validate', OLD.`validate`,'validationtime', OLD.`validationtime`,'modifieddate', OLD.`modifieddate`),
            JSON_OBJECT('id', NEW.`id`,'stid', NEW.`stid`,'sessionyear', NEW.`sessionyear`,'classname', NEW.`classname`,'sectionname', NEW.`sectionname`,'rollno', NEW.`rollno`,'sccode', NEW.`sccode`,'slot', NEW.`slot`,'medium', NEW.`medium`,'version', NEW.`version`,'fine', NEW.`fine`,'icardst', NEW.`icardst`,'rfidtag', NEW.`rfidtag`,'fourth_subject', NEW.`fourth_subject`,'voter_no', NEW.`voter_no`,'groupname', NEW.`groupname`,'status', NEW.`status`,'gender', NEW.`gender`,'religion', NEW.`religion`,'finsetup', NEW.`finsetup`,'lastpr', NEW.`lastpr`,'real_tution', NEW.`real_tution`,'sector', NEW.`sector`,'rate', NEW.`rate`,'amount', NEW.`amount`,'trackyesterday', NEW.`trackyesterday`,'tracktoday', NEW.`tracktoday`,'validate', NEW.`validate`,'validationtime', NEW.`validationtime`,'modifieddate', NEW.`modifieddate`),
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
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `sessioninfo_after_delete` AFTER DELETE ON `sessioninfo` FOR EACH ROW BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, changed_by)
        VALUES(
            'sessioninfo',
            'DELETE',
            OLD.`id`,
            JSON_OBJECT('id', OLD.`id`,'stid', OLD.`stid`,'sessionyear', OLD.`sessionyear`,'classname', OLD.`classname`,'sectionname', OLD.`sectionname`,'rollno', OLD.`rollno`,'sccode', OLD.`sccode`,'slot', OLD.`slot`,'medium', OLD.`medium`,'version', OLD.`version`,'fine', OLD.`fine`,'icardst', OLD.`icardst`,'rfidtag', OLD.`rfidtag`,'fourth_subject', OLD.`fourth_subject`,'voter_no', OLD.`voter_no`,'groupname', OLD.`groupname`,'status', OLD.`status`,'gender', OLD.`gender`,'religion', OLD.`religion`,'finsetup', OLD.`finsetup`,'lastpr', OLD.`lastpr`,'real_tution', OLD.`real_tution`,'sector', OLD.`sector`,'rate', OLD.`rate`,'amount', OLD.`amount`,'trackyesterday', OLD.`trackyesterday`,'tracktoday', OLD.`tracktoday`,'validate', OLD.`validate`,'validationtime', OLD.`validationtime`,'modifieddate', OLD.`modifieddate`),
            COALESCE(@current_user,'system')
        );
    END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `sessionyear`
--

DROP TABLE IF EXISTS `sessionyear`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sessionyear` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sccode` int(11) DEFAULT NULL,
  `syear` varchar(7) DEFAULT NULL,
  `active` int(11) NOT NULL DEFAULT 0,
  `entryby` varchar(120) DEFAULT NULL,
  `entrytime` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=206 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
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
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `sessionyear_after_insert` AFTER INSERT ON `sessionyear` FOR EACH ROW BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, new_data, changed_by)
        VALUES(
            'sessionyear',
            'INSERT',
            NEW.`id`,
            JSON_OBJECT('id', NEW.`id`,'sccode', NEW.`sccode`,'syear', NEW.`syear`,'active', NEW.`active`,'entryby', NEW.`entryby`,'entrytime', NEW.`entrytime`),
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
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `sessionyear_after_update` AFTER UPDATE ON `sessionyear` FOR EACH ROW BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, new_data, changed_by)
        VALUES(
            'sessionyear',
            'UPDATE',
            NEW.`id`,
            JSON_OBJECT('id', OLD.`id`,'sccode', OLD.`sccode`,'syear', OLD.`syear`,'active', OLD.`active`,'entryby', OLD.`entryby`,'entrytime', OLD.`entrytime`),
            JSON_OBJECT('id', NEW.`id`,'sccode', NEW.`sccode`,'syear', NEW.`syear`,'active', NEW.`active`,'entryby', NEW.`entryby`,'entrytime', NEW.`entrytime`),
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
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `sessionyear_after_delete` AFTER DELETE ON `sessionyear` FOR EACH ROW BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, changed_by)
        VALUES(
            'sessionyear',
            'DELETE',
            OLD.`id`,
            JSON_OBJECT('id', OLD.`id`,'sccode', OLD.`sccode`,'syear', OLD.`syear`,'active', OLD.`active`,'entryby', OLD.`entryby`,'entrytime', OLD.`entrytime`),
            COALESCE(@current_user,'system')
        );
    END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `settings`
--

DROP TABLE IF EXISTS `settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `slno` int(11) DEFAULT NULL,
  `setting_title` varchar(100) DEFAULT NULL,
  `sccode` int(11) DEFAULT 0,
  `descrip` varchar(500) DEFAULT NULL,
  `settings_value` varchar(100) DEFAULT NULL,
  `modifieddate` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=188 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
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
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `settings_after_insert` AFTER INSERT ON `settings` FOR EACH ROW BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, new_data, changed_by)
        VALUES(
            'settings',
            'INSERT',
            NEW.`id`,
            JSON_OBJECT('id', NEW.`id`,'slno', NEW.`slno`,'setting_title', NEW.`setting_title`,'sccode', NEW.`sccode`,'descrip', NEW.`descrip`,'settings_value', NEW.`settings_value`,'modifieddate', NEW.`modifieddate`),
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
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `settings_after_update` AFTER UPDATE ON `settings` FOR EACH ROW BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, new_data, changed_by)
        VALUES(
            'settings',
            'UPDATE',
            NEW.`id`,
            JSON_OBJECT('id', OLD.`id`,'slno', OLD.`slno`,'setting_title', OLD.`setting_title`,'sccode', OLD.`sccode`,'descrip', OLD.`descrip`,'settings_value', OLD.`settings_value`,'modifieddate', OLD.`modifieddate`),
            JSON_OBJECT('id', NEW.`id`,'slno', NEW.`slno`,'setting_title', NEW.`setting_title`,'sccode', NEW.`sccode`,'descrip', NEW.`descrip`,'settings_value', NEW.`settings_value`,'modifieddate', NEW.`modifieddate`),
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
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `settings_after_delete` AFTER DELETE ON `settings` FOR EACH ROW BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, changed_by)
        VALUES(
            'settings',
            'DELETE',
            OLD.`id`,
            JSON_OBJECT('id', OLD.`id`,'slno', OLD.`slno`,'setting_title', OLD.`setting_title`,'sccode', OLD.`sccode`,'descrip', OLD.`descrip`,'settings_value', OLD.`settings_value`,'modifieddate', OLD.`modifieddate`),
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
-- Table structure for table `slots`
--

DROP TABLE IF EXISTS `slots`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `slots` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sccode` int(11) DEFAULT NULL,
  `slotname` varchar(20) DEFAULT NULL,
  `merit` int(11) NOT NULL DEFAULT 0 COMMENT '0- total marks, 1- gpa',
  `cus_report` varchar(30) DEFAULT NULL,
  `decimal_mark` int(11) NOT NULL DEFAULT 0,
  `disp_entry_mark` int(11) NOT NULL DEFAULT 0,
  `trans_name_eng` int(11) NOT NULL DEFAULT 1,
  `trans_name_ben` int(11) NOT NULL DEFAULT 1,
  `parents` varchar(4) NOT NULL DEFAULT 'DOSO' COMMENT 'DOSO or, FM',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=36 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
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
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `slots_after_insert` AFTER INSERT ON `slots` FOR EACH ROW BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, new_data, changed_by)
        VALUES(
            'slots',
            'INSERT',
            NEW.`id`,
            JSON_OBJECT('id', NEW.`id`,'sccode', NEW.`sccode`,'slotname', NEW.`slotname`,'merit', NEW.`merit`,'cus_report', NEW.`cus_report`,'decimal_mark', NEW.`decimal_mark`,'disp_entry_mark', NEW.`disp_entry_mark`,'trans_name_eng', NEW.`trans_name_eng`,'trans_name_ben', NEW.`trans_name_ben`,'parents', NEW.`parents`),
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
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `slots_after_update` AFTER UPDATE ON `slots` FOR EACH ROW BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, new_data, changed_by)
        VALUES(
            'slots',
            'UPDATE',
            NEW.`id`,
            JSON_OBJECT('id', OLD.`id`,'sccode', OLD.`sccode`,'slotname', OLD.`slotname`,'merit', OLD.`merit`,'cus_report', OLD.`cus_report`,'decimal_mark', OLD.`decimal_mark`,'disp_entry_mark', OLD.`disp_entry_mark`,'trans_name_eng', OLD.`trans_name_eng`,'trans_name_ben', OLD.`trans_name_ben`,'parents', OLD.`parents`),
            JSON_OBJECT('id', NEW.`id`,'sccode', NEW.`sccode`,'slotname', NEW.`slotname`,'merit', NEW.`merit`,'cus_report', NEW.`cus_report`,'decimal_mark', NEW.`decimal_mark`,'disp_entry_mark', NEW.`disp_entry_mark`,'trans_name_eng', NEW.`trans_name_eng`,'trans_name_ben', NEW.`trans_name_ben`,'parents', NEW.`parents`),
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
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `slots_after_delete` AFTER DELETE ON `slots` FOR EACH ROW BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, changed_by)
        VALUES(
            'slots',
            'DELETE',
            OLD.`id`,
            JSON_OBJECT('id', OLD.`id`,'sccode', OLD.`sccode`,'slotname', OLD.`slotname`,'merit', OLD.`merit`,'cus_report', OLD.`cus_report`,'decimal_mark', OLD.`decimal_mark`,'disp_entry_mark', OLD.`disp_entry_mark`,'trans_name_eng', OLD.`trans_name_eng`,'trans_name_ben', OLD.`trans_name_ben`,'parents', OLD.`parents`),
            COALESCE(@current_user,'system')
        );
    END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `sms`
--

DROP TABLE IF EXISTS `sms`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sms` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sccode` int(11) DEFAULT NULL,
  `sessionyear` varchar(10) DEFAULT NULL,
  `date` date DEFAULT NULL,
  `campaign` int(11) DEFAULT NULL,
  `sms_type` varchar(100) DEFAULT NULL,
  `mobile_number` varchar(11) DEFAULT NULL,
  `sms_text` varchar(1024) DEFAULT NULL,
  `sms_len` int(11) NOT NULL DEFAULT 0,
  `count` int(11) NOT NULL DEFAULT 0,
  `send_by` varchar(120) DEFAULT NULL,
  `send_time` datetime DEFAULT NULL,
  `cost` float NOT NULL DEFAULT 0,
  `response_code` int(11) NOT NULL DEFAULT 0,
  `message_id` varchar(10) DEFAULT NULL,
  `success_message` varchar(50) DEFAULT NULL,
  `error_message` varchar(50) DEFAULT NULL,
  `status` int(11) NOT NULL DEFAULT 0,
  `comments` varchar(100) DEFAULT NULL,
  `modifieddate` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
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
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `sms_after_insert` AFTER INSERT ON `sms` FOR EACH ROW BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, new_data, changed_by)
        VALUES(
            'sms',
            'INSERT',
            NEW.`id`,
            JSON_OBJECT('id', NEW.`id`,'sccode', NEW.`sccode`,'sessionyear', NEW.`sessionyear`,'date', NEW.`date`,'campaign', NEW.`campaign`,'sms_type', NEW.`sms_type`,'mobile_number', NEW.`mobile_number`,'sms_text', NEW.`sms_text`,'sms_len', NEW.`sms_len`,'count', NEW.`count`,'send_by', NEW.`send_by`,'send_time', NEW.`send_time`,'cost', NEW.`cost`,'response_code', NEW.`response_code`,'message_id', NEW.`message_id`,'success_message', NEW.`success_message`,'error_message', NEW.`error_message`,'status', NEW.`status`,'comments', NEW.`comments`,'modifieddate', NEW.`modifieddate`),
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
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `sms_after_update` AFTER UPDATE ON `sms` FOR EACH ROW BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, new_data, changed_by)
        VALUES(
            'sms',
            'UPDATE',
            NEW.`id`,
            JSON_OBJECT('id', OLD.`id`,'sccode', OLD.`sccode`,'sessionyear', OLD.`sessionyear`,'date', OLD.`date`,'campaign', OLD.`campaign`,'sms_type', OLD.`sms_type`,'mobile_number', OLD.`mobile_number`,'sms_text', OLD.`sms_text`,'sms_len', OLD.`sms_len`,'count', OLD.`count`,'send_by', OLD.`send_by`,'send_time', OLD.`send_time`,'cost', OLD.`cost`,'response_code', OLD.`response_code`,'message_id', OLD.`message_id`,'success_message', OLD.`success_message`,'error_message', OLD.`error_message`,'status', OLD.`status`,'comments', OLD.`comments`,'modifieddate', OLD.`modifieddate`),
            JSON_OBJECT('id', NEW.`id`,'sccode', NEW.`sccode`,'sessionyear', NEW.`sessionyear`,'date', NEW.`date`,'campaign', NEW.`campaign`,'sms_type', NEW.`sms_type`,'mobile_number', NEW.`mobile_number`,'sms_text', NEW.`sms_text`,'sms_len', NEW.`sms_len`,'count', NEW.`count`,'send_by', NEW.`send_by`,'send_time', NEW.`send_time`,'cost', NEW.`cost`,'response_code', NEW.`response_code`,'message_id', NEW.`message_id`,'success_message', NEW.`success_message`,'error_message', NEW.`error_message`,'status', NEW.`status`,'comments', NEW.`comments`,'modifieddate', NEW.`modifieddate`),
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
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `sms_after_delete` AFTER DELETE ON `sms` FOR EACH ROW BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, changed_by)
        VALUES(
            'sms',
            'DELETE',
            OLD.`id`,
            JSON_OBJECT('id', OLD.`id`,'sccode', OLD.`sccode`,'sessionyear', OLD.`sessionyear`,'date', OLD.`date`,'campaign', OLD.`campaign`,'sms_type', OLD.`sms_type`,'mobile_number', OLD.`mobile_number`,'sms_text', OLD.`sms_text`,'sms_len', OLD.`sms_len`,'count', OLD.`count`,'send_by', OLD.`send_by`,'send_time', OLD.`send_time`,'cost', OLD.`cost`,'response_code', OLD.`response_code`,'message_id', OLD.`message_id`,'success_message', OLD.`success_message`,'error_message', OLD.`error_message`,'status', OLD.`status`,'comments', OLD.`comments`,'modifieddate', OLD.`modifieddate`),
            COALESCE(@current_user,'system')
        );
    END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `sms_logs`
--

DROP TABLE IF EXISTS `sms_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sms_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `to_number` varchar(30) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
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
) ENGINE=InnoDB AUTO_INCREMENT=170 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
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
-- Table structure for table `stfinance`
--

DROP TABLE IF EXISTS `stfinance`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stfinance` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sccode` int(11) NOT NULL,
  `sessionyear` int(11) NOT NULL,
  `classname` varchar(30) NOT NULL,
  `sectionname` varchar(30) NOT NULL,
  `stid` bigint(20) DEFAULT NULL,
  `rollno` int(11) NOT NULL,
  `partid` int(11) DEFAULT NULL,
  `itemcode` varchar(32) DEFAULT NULL,
  `particulareng` varchar(150) NOT NULL,
  `particularben` varchar(200) NOT NULL,
  `amount` int(11) NOT NULL DEFAULT 0,
  `month` int(11) NOT NULL,
  `idmon` varchar(30) DEFAULT NULL,
  `setupdate` datetime DEFAULT NULL,
  `setupby` varchar(100) DEFAULT NULL,
  `payableamt` int(11) NOT NULL DEFAULT 0,
  `modifieddate` datetime DEFAULT NULL,
  `modifiedby` varchar(100) DEFAULT NULL,
  `paid` int(11) NOT NULL DEFAULT 0,
  `paidx` int(11) NOT NULL DEFAULT 0,
  `dues` int(11) NOT NULL DEFAULT 0,
  `pr1` int(11) DEFAULT 0,
  `pr1no` bigint(20) DEFAULT NULL,
  `pr1date` date DEFAULT NULL,
  `pr1by` varchar(100) DEFAULT NULL,
  `cashbook1` int(11) NOT NULL DEFAULT 0,
  `pr2` int(11) DEFAULT 0,
  `pr2no` bigint(20) DEFAULT NULL,
  `pr2date` date DEFAULT NULL,
  `pr2by` varchar(100) DEFAULT NULL,
  `cashbook2` int(11) NOT NULL DEFAULT 0,
  `remark` varchar(200) DEFAULT NULL,
  `extra` int(11) DEFAULT 0,
  `last_update` date DEFAULT NULL,
  `validate` int(11) NOT NULL DEFAULT 0,
  `validationtime` datetime NOT NULL DEFAULT '2024-01-01 00:00:00',
  `deleteby` varchar(120) DEFAULT NULL,
  `deletetime` datetime DEFAULT NULL,
  `splitid` varchar(10) DEFAULT NULL,
  `scan_status` int(11) NOT NULL DEFAULT 3,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=657941 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
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
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `stfinance_after_insert` AFTER INSERT ON `stfinance` FOR EACH ROW BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, new_data, changed_by)
        VALUES(
            'stfinance',
            'INSERT',
            NEW.`id`,
            JSON_OBJECT('id', NEW.`id`,'sccode', NEW.`sccode`,'sessionyear', NEW.`sessionyear`,'classname', NEW.`classname`,'sectionname', NEW.`sectionname`,'stid', NEW.`stid`,'rollno', NEW.`rollno`,'partid', NEW.`partid`,'itemcode', NEW.`itemcode`,'particulareng', NEW.`particulareng`,'particularben', NEW.`particularben`,'amount', NEW.`amount`,'month', NEW.`month`,'idmon', NEW.`idmon`,'setupdate', NEW.`setupdate`,'setupby', NEW.`setupby`,'payableamt', NEW.`payableamt`,'modifieddate', NEW.`modifieddate`,'modifiedby', NEW.`modifiedby`,'paid', NEW.`paid`,'paidx', NEW.`paidx`,'dues', NEW.`dues`,'pr1', NEW.`pr1`,'pr1no', NEW.`pr1no`,'pr1date', NEW.`pr1date`,'pr1by', NEW.`pr1by`,'cashbook1', NEW.`cashbook1`,'pr2', NEW.`pr2`,'pr2no', NEW.`pr2no`,'pr2date', NEW.`pr2date`,'pr2by', NEW.`pr2by`,'cashbook2', NEW.`cashbook2`,'remark', NEW.`remark`,'extra', NEW.`extra`,'last_update', NEW.`last_update`,'validate', NEW.`validate`,'validationtime', NEW.`validationtime`,'deleteby', NEW.`deleteby`,'deletetime', NEW.`deletetime`,'splitid', NEW.`splitid`,'scan_status', NEW.`scan_status`),
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
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `stfinance_after_update` AFTER UPDATE ON `stfinance` FOR EACH ROW BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, new_data, changed_by)
        VALUES(
            'stfinance',
            'UPDATE',
            NEW.`id`,
            JSON_OBJECT('id', OLD.`id`,'sccode', OLD.`sccode`,'sessionyear', OLD.`sessionyear`,'classname', OLD.`classname`,'sectionname', OLD.`sectionname`,'stid', OLD.`stid`,'rollno', OLD.`rollno`,'partid', OLD.`partid`,'itemcode', OLD.`itemcode`,'particulareng', OLD.`particulareng`,'particularben', OLD.`particularben`,'amount', OLD.`amount`,'month', OLD.`month`,'idmon', OLD.`idmon`,'setupdate', OLD.`setupdate`,'setupby', OLD.`setupby`,'payableamt', OLD.`payableamt`,'modifieddate', OLD.`modifieddate`,'modifiedby', OLD.`modifiedby`,'paid', OLD.`paid`,'paidx', OLD.`paidx`,'dues', OLD.`dues`,'pr1', OLD.`pr1`,'pr1no', OLD.`pr1no`,'pr1date', OLD.`pr1date`,'pr1by', OLD.`pr1by`,'cashbook1', OLD.`cashbook1`,'pr2', OLD.`pr2`,'pr2no', OLD.`pr2no`,'pr2date', OLD.`pr2date`,'pr2by', OLD.`pr2by`,'cashbook2', OLD.`cashbook2`,'remark', OLD.`remark`,'extra', OLD.`extra`,'last_update', OLD.`last_update`,'validate', OLD.`validate`,'validationtime', OLD.`validationtime`,'deleteby', OLD.`deleteby`,'deletetime', OLD.`deletetime`,'splitid', OLD.`splitid`,'scan_status', OLD.`scan_status`),
            JSON_OBJECT('id', NEW.`id`,'sccode', NEW.`sccode`,'sessionyear', NEW.`sessionyear`,'classname', NEW.`classname`,'sectionname', NEW.`sectionname`,'stid', NEW.`stid`,'rollno', NEW.`rollno`,'partid', NEW.`partid`,'itemcode', NEW.`itemcode`,'particulareng', NEW.`particulareng`,'particularben', NEW.`particularben`,'amount', NEW.`amount`,'month', NEW.`month`,'idmon', NEW.`idmon`,'setupdate', NEW.`setupdate`,'setupby', NEW.`setupby`,'payableamt', NEW.`payableamt`,'modifieddate', NEW.`modifieddate`,'modifiedby', NEW.`modifiedby`,'paid', NEW.`paid`,'paidx', NEW.`paidx`,'dues', NEW.`dues`,'pr1', NEW.`pr1`,'pr1no', NEW.`pr1no`,'pr1date', NEW.`pr1date`,'pr1by', NEW.`pr1by`,'cashbook1', NEW.`cashbook1`,'pr2', NEW.`pr2`,'pr2no', NEW.`pr2no`,'pr2date', NEW.`pr2date`,'pr2by', NEW.`pr2by`,'cashbook2', NEW.`cashbook2`,'remark', NEW.`remark`,'extra', NEW.`extra`,'last_update', NEW.`last_update`,'validate', NEW.`validate`,'validationtime', NEW.`validationtime`,'deleteby', NEW.`deleteby`,'deletetime', NEW.`deletetime`,'splitid', NEW.`splitid`,'scan_status', NEW.`scan_status`),
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
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `stfinance_after_delete` AFTER DELETE ON `stfinance` FOR EACH ROW BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, changed_by)
        VALUES(
            'stfinance',
            'DELETE',
            OLD.`id`,
            JSON_OBJECT('id', OLD.`id`,'sccode', OLD.`sccode`,'sessionyear', OLD.`sessionyear`,'classname', OLD.`classname`,'sectionname', OLD.`sectionname`,'stid', OLD.`stid`,'rollno', OLD.`rollno`,'partid', OLD.`partid`,'itemcode', OLD.`itemcode`,'particulareng', OLD.`particulareng`,'particularben', OLD.`particularben`,'amount', OLD.`amount`,'month', OLD.`month`,'idmon', OLD.`idmon`,'setupdate', OLD.`setupdate`,'setupby', OLD.`setupby`,'payableamt', OLD.`payableamt`,'modifieddate', OLD.`modifieddate`,'modifiedby', OLD.`modifiedby`,'paid', OLD.`paid`,'paidx', OLD.`paidx`,'dues', OLD.`dues`,'pr1', OLD.`pr1`,'pr1no', OLD.`pr1no`,'pr1date', OLD.`pr1date`,'pr1by', OLD.`pr1by`,'cashbook1', OLD.`cashbook1`,'pr2', OLD.`pr2`,'pr2no', OLD.`pr2no`,'pr2date', OLD.`pr2date`,'pr2by', OLD.`pr2by`,'cashbook2', OLD.`cashbook2`,'remark', OLD.`remark`,'extra', OLD.`extra`,'last_update', OLD.`last_update`,'validate', OLD.`validate`,'validationtime', OLD.`validationtime`,'deleteby', OLD.`deleteby`,'deletetime', OLD.`deletetime`,'splitid', OLD.`splitid`,'scan_status', OLD.`scan_status`),
            COALESCE(@current_user,'system')
        );
    END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `stmark`
--

DROP TABLE IF EXISTS `stmark`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stmark` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `slot` varchar(20) NOT NULL DEFAULT 'School',
  `sessionyear` varchar(11) DEFAULT NULL,
  `sccode` int(11) DEFAULT NULL,
  `exam` varchar(50) DEFAULT NULL,
  `examid` int(11) DEFAULT NULL,
  `linkedexam` int(11) DEFAULT NULL,
  `exam_group` varchar(25) DEFAULT NULL,
  `examtype` varchar(3) NOT NULL DEFAULT 'PE',
  `classname` varchar(20) DEFAULT NULL,
  `sectionname` varchar(30) DEFAULT NULL,
  `subject` int(11) DEFAULT NULL,
  `fullmark` int(11) NOT NULL DEFAULT 0,
  `stid` bigint(20) DEFAULT NULL,
  `ctest` decimal(7,2) DEFAULT 0.00,
  `mtest` int(11) DEFAULT 0,
  `subj` int(11) NOT NULL DEFAULT 0,
  `obj` int(11) NOT NULL DEFAULT 0,
  `pra` int(11) NOT NULL DEFAULT 0,
  `ca` decimal(5,2) NOT NULL DEFAULT 0.00,
  `sub_final` int(11) NOT NULL DEFAULT 0,
  `obj_final` int(11) NOT NULL DEFAULT 0,
  `pra_final` int(11) NOT NULL DEFAULT 0,
  `markobt` decimal(5,2) NOT NULL DEFAULT 0.00,
  `on100` decimal(5,2) NOT NULL DEFAULT 0.00,
  `gp` float NOT NULL DEFAULT 0,
  `gl` varchar(3) DEFAULT NULL,
  `entrydate` timestamp NOT NULL DEFAULT current_timestamp(),
  `entryby` varchar(64) DEFAULT NULL,
  `modifieddate` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=325129 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
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
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `stmark_after_insert` AFTER INSERT ON `stmark` FOR EACH ROW BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, new_data, changed_by)
        VALUES(
            'stmark',
            'INSERT',
            NEW.`id`,
            JSON_OBJECT('id', NEW.`id`,'slot', NEW.`slot`,'sessionyear', NEW.`sessionyear`,'sccode', NEW.`sccode`,'exam', NEW.`exam`,'examid', NEW.`examid`,'linkedexam', NEW.`linkedexam`,'exam_group', NEW.`exam_group`,'examtype', NEW.`examtype`,'classname', NEW.`classname`,'sectionname', NEW.`sectionname`,'subject', NEW.`subject`,'fullmark', NEW.`fullmark`,'stid', NEW.`stid`,'ctest', NEW.`ctest`,'mtest', NEW.`mtest`,'subj', NEW.`subj`,'obj', NEW.`obj`,'pra', NEW.`pra`,'ca', NEW.`ca`,'sub_final', NEW.`sub_final`,'obj_final', NEW.`obj_final`,'pra_final', NEW.`pra_final`,'markobt', NEW.`markobt`,'on100', NEW.`on100`,'gp', NEW.`gp`,'gl', NEW.`gl`,'entrydate', NEW.`entrydate`,'entryby', NEW.`entryby`,'modifieddate', NEW.`modifieddate`),
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
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `stmark_after_update` AFTER UPDATE ON `stmark` FOR EACH ROW BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, new_data, changed_by)
        VALUES(
            'stmark',
            'UPDATE',
            NEW.`id`,
            JSON_OBJECT('id', OLD.`id`,'slot', OLD.`slot`,'sessionyear', OLD.`sessionyear`,'sccode', OLD.`sccode`,'exam', OLD.`exam`,'examid', OLD.`examid`,'linkedexam', OLD.`linkedexam`,'exam_group', OLD.`exam_group`,'examtype', OLD.`examtype`,'classname', OLD.`classname`,'sectionname', OLD.`sectionname`,'subject', OLD.`subject`,'fullmark', OLD.`fullmark`,'stid', OLD.`stid`,'ctest', OLD.`ctest`,'mtest', OLD.`mtest`,'subj', OLD.`subj`,'obj', OLD.`obj`,'pra', OLD.`pra`,'ca', OLD.`ca`,'sub_final', OLD.`sub_final`,'obj_final', OLD.`obj_final`,'pra_final', OLD.`pra_final`,'markobt', OLD.`markobt`,'on100', OLD.`on100`,'gp', OLD.`gp`,'gl', OLD.`gl`,'entrydate', OLD.`entrydate`,'entryby', OLD.`entryby`,'modifieddate', OLD.`modifieddate`),
            JSON_OBJECT('id', NEW.`id`,'slot', NEW.`slot`,'sessionyear', NEW.`sessionyear`,'sccode', NEW.`sccode`,'exam', NEW.`exam`,'examid', NEW.`examid`,'linkedexam', NEW.`linkedexam`,'exam_group', NEW.`exam_group`,'examtype', NEW.`examtype`,'classname', NEW.`classname`,'sectionname', NEW.`sectionname`,'subject', NEW.`subject`,'fullmark', NEW.`fullmark`,'stid', NEW.`stid`,'ctest', NEW.`ctest`,'mtest', NEW.`mtest`,'subj', NEW.`subj`,'obj', NEW.`obj`,'pra', NEW.`pra`,'ca', NEW.`ca`,'sub_final', NEW.`sub_final`,'obj_final', NEW.`obj_final`,'pra_final', NEW.`pra_final`,'markobt', NEW.`markobt`,'on100', NEW.`on100`,'gp', NEW.`gp`,'gl', NEW.`gl`,'entrydate', NEW.`entrydate`,'entryby', NEW.`entryby`,'modifieddate', NEW.`modifieddate`),
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
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `stmark_after_delete` AFTER DELETE ON `stmark` FOR EACH ROW BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, changed_by)
        VALUES(
            'stmark',
            'DELETE',
            OLD.`id`,
            JSON_OBJECT('id', OLD.`id`,'slot', OLD.`slot`,'sessionyear', OLD.`sessionyear`,'sccode', OLD.`sccode`,'exam', OLD.`exam`,'examid', OLD.`examid`,'linkedexam', OLD.`linkedexam`,'exam_group', OLD.`exam_group`,'examtype', OLD.`examtype`,'classname', OLD.`classname`,'sectionname', OLD.`sectionname`,'subject', OLD.`subject`,'fullmark', OLD.`fullmark`,'stid', OLD.`stid`,'ctest', OLD.`ctest`,'mtest', OLD.`mtest`,'subj', OLD.`subj`,'obj', OLD.`obj`,'pra', OLD.`pra`,'ca', OLD.`ca`,'sub_final', OLD.`sub_final`,'obj_final', OLD.`obj_final`,'pra_final', OLD.`pra_final`,'markobt', OLD.`markobt`,'on100', OLD.`on100`,'gp', OLD.`gp`,'gl', OLD.`gl`,'entrydate', OLD.`entrydate`,'entryby', OLD.`entryby`,'modifieddate', OLD.`modifieddate`),
            COALESCE(@current_user,'system')
        );
    END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `stpr`
--

DROP TABLE IF EXISTS `stpr`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stpr` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sessionyear` int(11) DEFAULT NULL,
  `sccode` int(11) DEFAULT NULL,
  `classname` varchar(30) DEFAULT NULL,
  `sectionname` varchar(30) DEFAULT NULL,
  `stid` bigint(20) DEFAULT NULL,
  `rollno` int(11) DEFAULT NULL,
  `prno` bigint(20) DEFAULT NULL,
  `prdate` date DEFAULT NULL,
  `partid` int(11) DEFAULT NULL,
  `peng` varchar(255) DEFAULT NULL,
  `pben` varchar(255) DEFAULT NULL,
  `amount` int(11) NOT NULL DEFAULT 0,
  `entryby` varchar(150) DEFAULT NULL,
  `entrytime` datetime DEFAULT NULL,
  `smstxt` text DEFAULT NULL,
  `smscnt` int(11) DEFAULT NULL,
  `mobileno` varchar(11) DEFAULT NULL,
  `smsstatus` int(11) DEFAULT NULL,
  `statusvalue` varchar(50) DEFAULT NULL,
  `cashbook` int(11) NOT NULL DEFAULT 0,
  `modifieddate` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7415 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
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
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `stpr_after_insert` AFTER INSERT ON `stpr` FOR EACH ROW BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, new_data, changed_by)
        VALUES(
            'stpr',
            'INSERT',
            NEW.`id`,
            JSON_OBJECT('id', NEW.`id`,'sessionyear', NEW.`sessionyear`,'sccode', NEW.`sccode`,'classname', NEW.`classname`,'sectionname', NEW.`sectionname`,'stid', NEW.`stid`,'rollno', NEW.`rollno`,'prno', NEW.`prno`,'prdate', NEW.`prdate`,'partid', NEW.`partid`,'peng', NEW.`peng`,'pben', NEW.`pben`,'amount', NEW.`amount`,'entryby', NEW.`entryby`,'entrytime', NEW.`entrytime`,'smstxt', NEW.`smstxt`,'smscnt', NEW.`smscnt`,'mobileno', NEW.`mobileno`,'smsstatus', NEW.`smsstatus`,'statusvalue', NEW.`statusvalue`,'cashbook', NEW.`cashbook`,'modifieddate', NEW.`modifieddate`),
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
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `stpr_after_update` AFTER UPDATE ON `stpr` FOR EACH ROW BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, new_data, changed_by)
        VALUES(
            'stpr',
            'UPDATE',
            NEW.`id`,
            JSON_OBJECT('id', OLD.`id`,'sessionyear', OLD.`sessionyear`,'sccode', OLD.`sccode`,'classname', OLD.`classname`,'sectionname', OLD.`sectionname`,'stid', OLD.`stid`,'rollno', OLD.`rollno`,'prno', OLD.`prno`,'prdate', OLD.`prdate`,'partid', OLD.`partid`,'peng', OLD.`peng`,'pben', OLD.`pben`,'amount', OLD.`amount`,'entryby', OLD.`entryby`,'entrytime', OLD.`entrytime`,'smstxt', OLD.`smstxt`,'smscnt', OLD.`smscnt`,'mobileno', OLD.`mobileno`,'smsstatus', OLD.`smsstatus`,'statusvalue', OLD.`statusvalue`,'cashbook', OLD.`cashbook`,'modifieddate', OLD.`modifieddate`),
            JSON_OBJECT('id', NEW.`id`,'sessionyear', NEW.`sessionyear`,'sccode', NEW.`sccode`,'classname', NEW.`classname`,'sectionname', NEW.`sectionname`,'stid', NEW.`stid`,'rollno', NEW.`rollno`,'prno', NEW.`prno`,'prdate', NEW.`prdate`,'partid', NEW.`partid`,'peng', NEW.`peng`,'pben', NEW.`pben`,'amount', NEW.`amount`,'entryby', NEW.`entryby`,'entrytime', NEW.`entrytime`,'smstxt', NEW.`smstxt`,'smscnt', NEW.`smscnt`,'mobileno', NEW.`mobileno`,'smsstatus', NEW.`smsstatus`,'statusvalue', NEW.`statusvalue`,'cashbook', NEW.`cashbook`,'modifieddate', NEW.`modifieddate`),
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
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `stpr_after_delete` AFTER DELETE ON `stpr` FOR EACH ROW BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, changed_by)
        VALUES(
            'stpr',
            'DELETE',
            OLD.`id`,
            JSON_OBJECT('id', OLD.`id`,'sessionyear', OLD.`sessionyear`,'sccode', OLD.`sccode`,'classname', OLD.`classname`,'sectionname', OLD.`sectionname`,'stid', OLD.`stid`,'rollno', OLD.`rollno`,'prno', OLD.`prno`,'prdate', OLD.`prdate`,'partid', OLD.`partid`,'peng', OLD.`peng`,'pben', OLD.`pben`,'amount', OLD.`amount`,'entryby', OLD.`entryby`,'entrytime', OLD.`entrytime`,'smstxt', OLD.`smstxt`,'smscnt', OLD.`smscnt`,'mobileno', OLD.`mobileno`,'smsstatus', OLD.`smsstatus`,'statusvalue', OLD.`statusvalue`,'cashbook', OLD.`cashbook`,'modifieddate', OLD.`modifieddate`),
            COALESCE(@current_user,'system')
        );
    END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `students`
--

DROP TABLE IF EXISTS `students`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `students` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sccode` int(11) DEFAULT NULL,
  `stid` varchar(11) DEFAULT NULL,
  `stnameeng` varchar(40) DEFAULT NULL,
  `stnameben` varchar(255) DEFAULT NULL,
  `fname` varchar(40) DEFAULT NULL,
  `fnameben` varchar(100) DEFAULT NULL,
  `falive` int(11) NOT NULL DEFAULT 1,
  `fprof` varchar(30) DEFAULT NULL,
  `fmobile` varchar(11) DEFAULT NULL,
  `fnid` varchar(20) DEFAULT NULL,
  `mname` varchar(40) DEFAULT NULL,
  `mnameben` varchar(100) DEFAULT NULL,
  `malive` int(11) NOT NULL DEFAULT 1,
  `mprof` varchar(30) DEFAULT NULL,
  `mmobile` varchar(11) DEFAULT NULL,
  `mnid` varchar(20) DEFAULT NULL,
  `previll` varchar(30) DEFAULT NULL,
  `prepo` varchar(30) DEFAULT NULL,
  `preps` varchar(20) DEFAULT NULL,
  `predist` varchar(30) DEFAULT NULL,
  `pervill` varchar(30) DEFAULT NULL,
  `perpo` varchar(30) DEFAULT NULL,
  `perps` varchar(20) DEFAULT NULL,
  `perdist` varchar(30) DEFAULT NULL,
  `dob` date DEFAULT NULL,
  `religion` varchar(10) DEFAULT NULL,
  `brn` varchar(20) DEFAULT NULL,
  `gender` varchar(7) DEFAULT NULL,
  `bgroup` varchar(3) DEFAULT NULL,
  `disables` varchar(20) DEFAULT NULL,
  `height` int(11) DEFAULT NULL,
  `weight` int(11) DEFAULT NULL,
  `mobileself` varchar(11) DEFAULT NULL,
  `uniqueid` varchar(20) DEFAULT NULL,
  `guarname` varchar(40) DEFAULT NULL,
  `guaradd` varchar(60) DEFAULT NULL,
  `guarrelation` varchar(20) DEFAULT NULL,
  `guarmobile` varchar(11) DEFAULT NULL,
  `guarnid` varchar(17) DEFAULT NULL,
  `guarnameben` varchar(120) DEFAULT NULL,
  `guaremail` varchar(120) DEFAULT NULL,
  `guarmobile2` varchar(11) DEFAULT NULL,
  `guaremail2` varchar(120) DEFAULT NULL,
  `tcno` varchar(10) DEFAULT NULL,
  `preins` varchar(20) DEFAULT NULL,
  `preinsadd` varchar(255) DEFAULT NULL,
  `doa` date DEFAULT NULL,
  `modify` datetime DEFAULT NULL,
  `photo` varchar(20) DEFAULT NULL,
  `photo_id` varchar(50) DEFAULT NULL,
  `photo_pick_date` varchar(10) DEFAULT NULL,
  `icardno` varchar(20) DEFAULT NULL,
  `issuedate` date DEFAULT NULL,
  `rsnx` varchar(120) DEFAULT NULL,
  `qrcode` varchar(50) DEFAULT NULL COMMENT 'QR Code value',
  `sscpassyear` int(11) DEFAULT 0,
  `regdno` varchar(10) DEFAULT NULL,
  `rollno` varchar(10) DEFAULT NULL,
  `gpa` varchar(4) DEFAULT NULL,
  `gla` varchar(3) DEFAULT NULL,
  `sibling1` varchar(10) DEFAULT NULL,
  `sibling2` varchar(10) DEFAULT NULL,
  `sibling3` varchar(10) DEFAULT NULL,
  `sibling4` varchar(10) DEFAULT NULL,
  `sibling5` varchar(10) DEFAULT NULL,
  `voter_no` int(11) NOT NULL DEFAULT 0,
  `benvill` varchar(150) DEFAULT NULL,
  `benpo` varchar(150) DEFAULT NULL,
  `benps` varchar(150) DEFAULT NULL,
  `bendist` varchar(150) DEFAULT NULL,
  `modifieddate` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1793 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
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
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `students_after_insert` AFTER INSERT ON `students` FOR EACH ROW BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, new_data, changed_by)
        VALUES(
            'students',
            'INSERT',
            NEW.`id`,
            JSON_OBJECT('id', NEW.`id`,'sccode', NEW.`sccode`,'stid', NEW.`stid`,'stnameeng', NEW.`stnameeng`,'stnameben', NEW.`stnameben`,'fname', NEW.`fname`,'fnameben', NEW.`fnameben`,'falive', NEW.`falive`,'fprof', NEW.`fprof`,'fmobile', NEW.`fmobile`,'fnid', NEW.`fnid`,'mname', NEW.`mname`,'mnameben', NEW.`mnameben`,'malive', NEW.`malive`,'mprof', NEW.`mprof`,'mmobile', NEW.`mmobile`,'mnid', NEW.`mnid`,'previll', NEW.`previll`,'prepo', NEW.`prepo`,'preps', NEW.`preps`,'predist', NEW.`predist`,'pervill', NEW.`pervill`,'perpo', NEW.`perpo`,'perps', NEW.`perps`,'perdist', NEW.`perdist`,'dob', NEW.`dob`,'religion', NEW.`religion`,'brn', NEW.`brn`,'gender', NEW.`gender`,'bgroup', NEW.`bgroup`,'disables', NEW.`disables`,'height', NEW.`height`,'weight', NEW.`weight`,'mobileself', NEW.`mobileself`,'uniqueid', NEW.`uniqueid`,'guarname', NEW.`guarname`,'guaradd', NEW.`guaradd`,'guarrelation', NEW.`guarrelation`,'guarmobile', NEW.`guarmobile`,'guarnid', NEW.`guarnid`,'guarnameben', NEW.`guarnameben`,'guaremail', NEW.`guaremail`,'guarmobile2', NEW.`guarmobile2`,'guaremail2', NEW.`guaremail2`,'tcno', NEW.`tcno`,'preins', NEW.`preins`,'preinsadd', NEW.`preinsadd`,'doa', NEW.`doa`,'modify', NEW.`modify`,'photo', NEW.`photo`,'photo_id', NEW.`photo_id`,'photo_pick_date', NEW.`photo_pick_date`,'icardno', NEW.`icardno`,'issuedate', NEW.`issuedate`,'rsnx', NEW.`rsnx`,'qrcode', NEW.`qrcode`,'sscpassyear', NEW.`sscpassyear`,'regdno', NEW.`regdno`,'rollno', NEW.`rollno`,'gpa', NEW.`gpa`,'gla', NEW.`gla`,'sibling1', NEW.`sibling1`,'sibling2', NEW.`sibling2`,'sibling3', NEW.`sibling3`,'sibling4', NEW.`sibling4`,'sibling5', NEW.`sibling5`,'benvill', NEW.`benvill`,'benpo', NEW.`benpo`,'benps', NEW.`benps`,'bendist', NEW.`bendist`,'modifieddate', NEW.`modifieddate`),
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
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `students_after_update` AFTER UPDATE ON `students` FOR EACH ROW BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, new_data, changed_by)
        VALUES(
            'students',
            'UPDATE',
            NEW.`id`,
            JSON_OBJECT('id', OLD.`id`,'sccode', OLD.`sccode`,'stid', OLD.`stid`,'stnameeng', OLD.`stnameeng`,'stnameben', OLD.`stnameben`,'fname', OLD.`fname`,'fnameben', OLD.`fnameben`,'falive', OLD.`falive`,'fprof', OLD.`fprof`,'fmobile', OLD.`fmobile`,'fnid', OLD.`fnid`,'mname', OLD.`mname`,'mnameben', OLD.`mnameben`,'malive', OLD.`malive`,'mprof', OLD.`mprof`,'mmobile', OLD.`mmobile`,'mnid', OLD.`mnid`,'previll', OLD.`previll`,'prepo', OLD.`prepo`,'preps', OLD.`preps`,'predist', OLD.`predist`,'pervill', OLD.`pervill`,'perpo', OLD.`perpo`,'perps', OLD.`perps`,'perdist', OLD.`perdist`,'dob', OLD.`dob`,'religion', OLD.`religion`,'brn', OLD.`brn`,'gender', OLD.`gender`,'bgroup', OLD.`bgroup`,'disables', OLD.`disables`,'height', OLD.`height`,'weight', OLD.`weight`,'mobileself', OLD.`mobileself`,'uniqueid', OLD.`uniqueid`,'guarname', OLD.`guarname`,'guaradd', OLD.`guaradd`,'guarrelation', OLD.`guarrelation`,'guarmobile', OLD.`guarmobile`,'guarnid', OLD.`guarnid`,'guarnameben', OLD.`guarnameben`,'guaremail', OLD.`guaremail`,'guarmobile2', OLD.`guarmobile2`,'guaremail2', OLD.`guaremail2`,'tcno', OLD.`tcno`,'preins', OLD.`preins`,'preinsadd', OLD.`preinsadd`,'doa', OLD.`doa`,'modify', OLD.`modify`,'photo', OLD.`photo`,'photo_id', OLD.`photo_id`,'photo_pick_date', OLD.`photo_pick_date`,'icardno', OLD.`icardno`,'issuedate', OLD.`issuedate`,'rsnx', OLD.`rsnx`,'qrcode', OLD.`qrcode`,'sscpassyear', OLD.`sscpassyear`,'regdno', OLD.`regdno`,'rollno', OLD.`rollno`,'gpa', OLD.`gpa`,'gla', OLD.`gla`,'sibling1', OLD.`sibling1`,'sibling2', OLD.`sibling2`,'sibling3', OLD.`sibling3`,'sibling4', OLD.`sibling4`,'sibling5', OLD.`sibling5`,'benvill', OLD.`benvill`,'benpo', OLD.`benpo`,'benps', OLD.`benps`,'bendist', OLD.`bendist`,'modifieddate', OLD.`modifieddate`),
            JSON_OBJECT('id', NEW.`id`,'sccode', NEW.`sccode`,'stid', NEW.`stid`,'stnameeng', NEW.`stnameeng`,'stnameben', NEW.`stnameben`,'fname', NEW.`fname`,'fnameben', NEW.`fnameben`,'falive', NEW.`falive`,'fprof', NEW.`fprof`,'fmobile', NEW.`fmobile`,'fnid', NEW.`fnid`,'mname', NEW.`mname`,'mnameben', NEW.`mnameben`,'malive', NEW.`malive`,'mprof', NEW.`mprof`,'mmobile', NEW.`mmobile`,'mnid', NEW.`mnid`,'previll', NEW.`previll`,'prepo', NEW.`prepo`,'preps', NEW.`preps`,'predist', NEW.`predist`,'pervill', NEW.`pervill`,'perpo', NEW.`perpo`,'perps', NEW.`perps`,'perdist', NEW.`perdist`,'dob', NEW.`dob`,'religion', NEW.`religion`,'brn', NEW.`brn`,'gender', NEW.`gender`,'bgroup', NEW.`bgroup`,'disables', NEW.`disables`,'height', NEW.`height`,'weight', NEW.`weight`,'mobileself', NEW.`mobileself`,'uniqueid', NEW.`uniqueid`,'guarname', NEW.`guarname`,'guaradd', NEW.`guaradd`,'guarrelation', NEW.`guarrelation`,'guarmobile', NEW.`guarmobile`,'guarnid', NEW.`guarnid`,'guarnameben', NEW.`guarnameben`,'guaremail', NEW.`guaremail`,'guarmobile2', NEW.`guarmobile2`,'guaremail2', NEW.`guaremail2`,'tcno', NEW.`tcno`,'preins', NEW.`preins`,'preinsadd', NEW.`preinsadd`,'doa', NEW.`doa`,'modify', NEW.`modify`,'photo', NEW.`photo`,'photo_id', NEW.`photo_id`,'photo_pick_date', NEW.`photo_pick_date`,'icardno', NEW.`icardno`,'issuedate', NEW.`issuedate`,'rsnx', NEW.`rsnx`,'qrcode', NEW.`qrcode`,'sscpassyear', NEW.`sscpassyear`,'regdno', NEW.`regdno`,'rollno', NEW.`rollno`,'gpa', NEW.`gpa`,'gla', NEW.`gla`,'sibling1', NEW.`sibling1`,'sibling2', NEW.`sibling2`,'sibling3', NEW.`sibling3`,'sibling4', NEW.`sibling4`,'sibling5', NEW.`sibling5`,'benvill', NEW.`benvill`,'benpo', NEW.`benpo`,'benps', NEW.`benps`,'bendist', NEW.`bendist`,'modifieddate', NEW.`modifieddate`),
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
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `students_after_delete` AFTER DELETE ON `students` FOR EACH ROW BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, changed_by)
        VALUES(
            'students',
            'DELETE',
            OLD.`id`,
            JSON_OBJECT('id', OLD.`id`,'sccode', OLD.`sccode`,'stid', OLD.`stid`,'stnameeng', OLD.`stnameeng`,'stnameben', OLD.`stnameben`,'fname', OLD.`fname`,'fnameben', OLD.`fnameben`,'falive', OLD.`falive`,'fprof', OLD.`fprof`,'fmobile', OLD.`fmobile`,'fnid', OLD.`fnid`,'mname', OLD.`mname`,'mnameben', OLD.`mnameben`,'malive', OLD.`malive`,'mprof', OLD.`mprof`,'mmobile', OLD.`mmobile`,'mnid', OLD.`mnid`,'previll', OLD.`previll`,'prepo', OLD.`prepo`,'preps', OLD.`preps`,'predist', OLD.`predist`,'pervill', OLD.`pervill`,'perpo', OLD.`perpo`,'perps', OLD.`perps`,'perdist', OLD.`perdist`,'dob', OLD.`dob`,'religion', OLD.`religion`,'brn', OLD.`brn`,'gender', OLD.`gender`,'bgroup', OLD.`bgroup`,'disables', OLD.`disables`,'height', OLD.`height`,'weight', OLD.`weight`,'mobileself', OLD.`mobileself`,'uniqueid', OLD.`uniqueid`,'guarname', OLD.`guarname`,'guaradd', OLD.`guaradd`,'guarrelation', OLD.`guarrelation`,'guarmobile', OLD.`guarmobile`,'guarnid', OLD.`guarnid`,'guarnameben', OLD.`guarnameben`,'guaremail', OLD.`guaremail`,'guarmobile2', OLD.`guarmobile2`,'guaremail2', OLD.`guaremail2`,'tcno', OLD.`tcno`,'preins', OLD.`preins`,'preinsadd', OLD.`preinsadd`,'doa', OLD.`doa`,'modify', OLD.`modify`,'photo', OLD.`photo`,'photo_id', OLD.`photo_id`,'photo_pick_date', OLD.`photo_pick_date`,'icardno', OLD.`icardno`,'issuedate', OLD.`issuedate`,'rsnx', OLD.`rsnx`,'qrcode', OLD.`qrcode`,'sscpassyear', OLD.`sscpassyear`,'regdno', OLD.`regdno`,'rollno', OLD.`rollno`,'gpa', OLD.`gpa`,'gla', OLD.`gla`,'sibling1', OLD.`sibling1`,'sibling2', OLD.`sibling2`,'sibling3', OLD.`sibling3`,'sibling4', OLD.`sibling4`,'sibling5', OLD.`sibling5`,'benvill', OLD.`benvill`,'benpo', OLD.`benpo`,'benps', OLD.`benps`,'bendist', OLD.`bendist`,'modifieddate', OLD.`modifieddate`),
            COALESCE(@current_user,'system')
        );
    END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `subsetup`
--

DROP TABLE IF EXISTS `subsetup`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `subsetup` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `slno` int(11) NOT NULL DEFAULT 0,
  `sccode` int(11) DEFAULT NULL,
  `sessionyear` varchar(11) NOT NULL DEFAULT '2025',
  `slot` varchar(20) NOT NULL DEFAULT 'School',
  `classname` varchar(20) DEFAULT NULL,
  `sectionname` varchar(50) DEFAULT NULL,
  `subject` int(11) DEFAULT NULL,
  `fullmarks` int(11) DEFAULT NULL,
  `ctest` int(11) NOT NULL DEFAULT 0,
  `mtest` int(11) NOT NULL DEFAULT 0,
  `subj` int(11) DEFAULT NULL,
  `obj` int(11) DEFAULT NULL,
  `pra` int(11) DEFAULT NULL,
  `ca` int(11) DEFAULT NULL,
  `camanual` int(11) NOT NULL DEFAULT 0,
  `ctmt` int(11) NOT NULL DEFAULT 0,
  `pass_algorithm` int(11) DEFAULT NULL,
  `cnt` int(11) DEFAULT NULL,
  `reverse` int(11) DEFAULT NULL,
  `tid` bigint(20) DEFAULT NULL,
  `combind_1` int(11) DEFAULT NULL,
  `combind_2` int(11) DEFAULT NULL,
  `combind_3` int(11) DEFAULT NULL,
  `combind_4` int(11) DEFAULT NULL,
  `fourth` int(11) DEFAULT 0,
  `entrycnt` int(11) NOT NULL DEFAULT 0,
  `done1` int(11) NOT NULL DEFAULT 0,
  `doneby1` varchar(100) DEFAULT NULL,
  `donetime1` datetime DEFAULT NULL,
  `done2` int(11) DEFAULT NULL,
  `doneby2` varchar(100) DEFAULT NULL,
  `donetime2` datetime DEFAULT NULL,
  `modifieddate` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8715 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
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
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `subsetup_after_insert` AFTER INSERT ON `subsetup` FOR EACH ROW BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, new_data, changed_by)
        VALUES(
            'subsetup',
            'INSERT',
            NEW.`id`,
            JSON_OBJECT('id', NEW.`id`,'slno', NEW.`slno`,'sccode', NEW.`sccode`,'sessionyear', NEW.`sessionyear`,'slot', NEW.`slot`,'classname', NEW.`classname`,'sectionname', NEW.`sectionname`,'subject', NEW.`subject`,'fullmarks', NEW.`fullmarks`,'ctest', NEW.`ctest`,'mtest', NEW.`mtest`,'subj', NEW.`subj`,'obj', NEW.`obj`,'pra', NEW.`pra`,'ca', NEW.`ca`,'camanual', NEW.`camanual`,'ctmt', NEW.`ctmt`,'pass_algorithm', NEW.`pass_algorithm`,'cnt', NEW.`cnt`,'reverse', NEW.`reverse`,'tid', NEW.`tid`,'combind_1', NEW.`combind_1`,'combind_2', NEW.`combind_2`,'combind_3', NEW.`combind_3`,'combind_4', NEW.`combind_4`,'fourth', NEW.`fourth`,'entrycnt', NEW.`entrycnt`,'done1', NEW.`done1`,'doneby1', NEW.`doneby1`,'donetime1', NEW.`donetime1`,'done2', NEW.`done2`,'doneby2', NEW.`doneby2`,'donetime2', NEW.`donetime2`,'modifieddate', NEW.`modifieddate`),
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
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `subsetup_after_update` AFTER UPDATE ON `subsetup` FOR EACH ROW BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, new_data, changed_by)
        VALUES(
            'subsetup',
            'UPDATE',
            NEW.`id`,
            JSON_OBJECT('id', OLD.`id`,'slno', OLD.`slno`,'sccode', OLD.`sccode`,'sessionyear', OLD.`sessionyear`,'slot', OLD.`slot`,'classname', OLD.`classname`,'sectionname', OLD.`sectionname`,'subject', OLD.`subject`,'fullmarks', OLD.`fullmarks`,'ctest', OLD.`ctest`,'mtest', OLD.`mtest`,'subj', OLD.`subj`,'obj', OLD.`obj`,'pra', OLD.`pra`,'ca', OLD.`ca`,'camanual', OLD.`camanual`,'ctmt', OLD.`ctmt`,'pass_algorithm', OLD.`pass_algorithm`,'cnt', OLD.`cnt`,'reverse', OLD.`reverse`,'tid', OLD.`tid`,'combind_1', OLD.`combind_1`,'combind_2', OLD.`combind_2`,'combind_3', OLD.`combind_3`,'combind_4', OLD.`combind_4`,'fourth', OLD.`fourth`,'entrycnt', OLD.`entrycnt`,'done1', OLD.`done1`,'doneby1', OLD.`doneby1`,'donetime1', OLD.`donetime1`,'done2', OLD.`done2`,'doneby2', OLD.`doneby2`,'donetime2', OLD.`donetime2`,'modifieddate', OLD.`modifieddate`),
            JSON_OBJECT('id', NEW.`id`,'slno', NEW.`slno`,'sccode', NEW.`sccode`,'sessionyear', NEW.`sessionyear`,'slot', NEW.`slot`,'classname', NEW.`classname`,'sectionname', NEW.`sectionname`,'subject', NEW.`subject`,'fullmarks', NEW.`fullmarks`,'ctest', NEW.`ctest`,'mtest', NEW.`mtest`,'subj', NEW.`subj`,'obj', NEW.`obj`,'pra', NEW.`pra`,'ca', NEW.`ca`,'camanual', NEW.`camanual`,'ctmt', NEW.`ctmt`,'pass_algorithm', NEW.`pass_algorithm`,'cnt', NEW.`cnt`,'reverse', NEW.`reverse`,'tid', NEW.`tid`,'combind_1', NEW.`combind_1`,'combind_2', NEW.`combind_2`,'combind_3', NEW.`combind_3`,'combind_4', NEW.`combind_4`,'fourth', NEW.`fourth`,'entrycnt', NEW.`entrycnt`,'done1', NEW.`done1`,'doneby1', NEW.`doneby1`,'donetime1', NEW.`donetime1`,'done2', NEW.`done2`,'doneby2', NEW.`doneby2`,'donetime2', NEW.`donetime2`,'modifieddate', NEW.`modifieddate`),
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
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `subsetup_after_delete` AFTER DELETE ON `subsetup` FOR EACH ROW BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, changed_by)
        VALUES(
            'subsetup',
            'DELETE',
            OLD.`id`,
            JSON_OBJECT('id', OLD.`id`,'slno', OLD.`slno`,'sccode', OLD.`sccode`,'sessionyear', OLD.`sessionyear`,'slot', OLD.`slot`,'classname', OLD.`classname`,'sectionname', OLD.`sectionname`,'subject', OLD.`subject`,'fullmarks', OLD.`fullmarks`,'ctest', OLD.`ctest`,'mtest', OLD.`mtest`,'subj', OLD.`subj`,'obj', OLD.`obj`,'pra', OLD.`pra`,'ca', OLD.`ca`,'camanual', OLD.`camanual`,'ctmt', OLD.`ctmt`,'pass_algorithm', OLD.`pass_algorithm`,'cnt', OLD.`cnt`,'reverse', OLD.`reverse`,'tid', OLD.`tid`,'combind_1', OLD.`combind_1`,'combind_2', OLD.`combind_2`,'combind_3', OLD.`combind_3`,'combind_4', OLD.`combind_4`,'fourth', OLD.`fourth`,'entrycnt', OLD.`entrycnt`,'done1', OLD.`done1`,'doneby1', OLD.`doneby1`,'donetime1', OLD.`donetime1`,'done2', OLD.`done2`,'doneby2', OLD.`doneby2`,'donetime2', OLD.`donetime2`,'modifieddate', OLD.`modifieddate`),
            COALESCE(@current_user,'system')
        );
    END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

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
  `admin_response` int(11) NOT NULL DEFAULT 0,
  `sent_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=40 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tickets`
--

DROP TABLE IF EXISTS `tickets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tickets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sccode` int(11) DEFAULT NULL,
  `user_id` int(11) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `status` enum('open','in_progress','closed') DEFAULT 'open',
  `ticket_for` enum('Institute','Application','Personal','') DEFAULT 'Institute',
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
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
) ENGINE=InnoDB AUTO_INCREMENT=14519 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
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
) ENGINE=InnoDB AUTO_INCREMENT=30 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
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
  `sccode` int(11) DEFAULT NULL,
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
  `page_status_grant` int(11) NOT NULL DEFAULT 6,
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
  `last_name` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `dob` date DEFAULT NULL,
  `first_name` varchar(100) DEFAULT NULL,
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
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

-- Dump completed on 2025-11-10 23:33:10
