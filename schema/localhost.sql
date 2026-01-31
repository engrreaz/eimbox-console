mysqldump: [Warning] Using a password on the command line interface can be insecure.
Warning: A partial dump from a server that has GTIDs will by default include the GTIDs of all transactions, even those that changed suppressed parts of the database. If you don't want to restore GTIDs, pass --set-gtid-purged=OFF. To make a complete dump, pass --all-databases --triggers --routines --events. 
Warning: A dump from a server that has GTIDs enabled will by default include the GTIDs of all transactions, even those that were executed during its extraction and might not be represented in the dumped data. This might result in an inconsistent data dump. 
In order to ensure a consistent backup of the database, pass --single-transaction or --lock-all-tables or --source-data. 
-- MySQL dump 10.13  Distrib 9.6.0, for Win64 (x86_64)
--
-- Host: localhost    Database: secure_auth
-- ------------------------------------------------------
-- Server version	9.6.0-commercial

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
SET @MYSQLDUMP_TEMP_LOG_BIN = @@SESSION.SQL_LOG_BIN;
SET @@SESSION.SQL_LOG_BIN= 0;

--
-- GTID state at the beginning of the backup 
--

SET @@GLOBAL.GTID_PURGED=/*!80000 '+'*/ 'ecffe173-fe25-11f0-9c1b-80fa5b4a79c5:1-10366';

--
-- Table structure for table `accesslevel`
--

DROP TABLE IF EXISTS `accesslevel`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `accesslevel` (
  `id` int NOT NULL AUTO_INCREMENT,
  `permission` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `platinum` int NOT NULL,
  `gold` int NOT NULL,
  `silver` int NOT NULL,
  `bronge` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `account_head`
--

DROP TABLE IF EXISTS `account_head`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `account_head` (
  `id` int NOT NULL AUTO_INCREMENT,
  `account_head` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sccode` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `account_head_default`
--

DROP TABLE IF EXISTS `account_head_default`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `account_head_default` (
  `id` int NOT NULL AUTO_INCREMENT,
  `account_head` varchar(150) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `account_sub_head`
--

DROP TABLE IF EXISTS `account_sub_head`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `account_sub_head` (
  `id` int NOT NULL AUTO_INCREMENT,
  `sccode` int DEFAULT '0',
  `account_head_id` int DEFAULT NULL,
  `account_head` varchar(150) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `sub_head` varchar(200) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `income` int NOT NULL DEFAULT '0',
  `expenditure` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=68 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `account_sub_head_default`
--

DROP TABLE IF EXISTS `account_sub_head_default`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `account_sub_head_default` (
  `id` int NOT NULL AUTO_INCREMENT,
  `sccode` int DEFAULT '0',
  `account_head` varchar(150) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `sub_head` varchar(200) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `type` enum('income','expenditure') CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT 'expenditure',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=66 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `accounts`
--

DROP TABLE IF EXISTS `accounts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `accounts` (
  `id` int NOT NULL AUTO_INCREMENT,
  `sccode` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `company` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descn` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `amount` decimal(11,2) NOT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `sms` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `accounts_heads`
--

DROP TABLE IF EXISTS `accounts_heads`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `accounts_heads` (
  `id` int NOT NULL AUTO_INCREMENT,
  `sccode` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `head_name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `head_type` enum('income','expense','asset','liability') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` tinyint DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `accounts_trans`
--

DROP TABLE IF EXISTS `accounts_trans`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `accounts_trans` (
  `id` int NOT NULL AUTO_INCREMENT,
  `sccode` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `trans_date` date DEFAULT NULL,
  `head_id` int DEFAULT NULL,
  `debit` double DEFAULT '0',
  `credit` double DEFAULT '0',
  `remarks` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `achievement_events`
--

DROP TABLE IF EXISTS `achievement_events`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `achievement_events` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `achievement_id` int unsigned NOT NULL,
  `event_type` enum('awarded','revoked') COLLATE utf8mb4_unicode_ci NOT NULL,
  `reason` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `email` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `achievements_category`
--

DROP TABLE IF EXISTS `achievements_category`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `achievements_category` (
  `sl` int NOT NULL DEFAULT '0',
  `created_by` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `category` varchar(25) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `achievements_list`
--

DROP TABLE IF EXISTS `achievements_list`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `achievements_list` (
  `id` int NOT NULL AUTO_INCREMENT,
  `code` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `description` text COLLATE utf8mb4_general_ci,
  `level_requirement` varchar(200) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `tbl_name` varchar(30) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `aggregate` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `field` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `params` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'extra condition : parameters',
  `requirement` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `points` int DEFAULT '0',
  `category` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `type` varchar(20) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'Basic',
  `tier` enum('Bronze','Silver','Gold','Platinum','Diamond') COLLATE utf8mb4_general_ci DEFAULT 'Bronze',
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=35 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `active_sessions`
--

DROP TABLE IF EXISTS `active_sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `active_sessions` (
  `session_id` varchar(128) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint NOT NULL,
  `ip` varchar(45) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `device_fp` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `started_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `last_seen` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `is_authenticated` tinyint(1) DEFAULT '0',
  `auth_level` enum('password','otp','mfa_hard') COLLATE utf8mb4_unicode_ci DEFAULT 'password',
  PRIMARY KEY (`session_id`),
  KEY `user_id` (`user_id`),
  KEY `last_seen` (`last_seen`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `activities_master`
--

DROP TABLE IF EXISTS `activities_master`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `activities_master` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(150) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `category` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `status` tinyint DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=69 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `activity_categories`
--

DROP TABLE IF EXISTS `activity_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `activity_categories` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `status` tinyint DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `activity_levels`
--

DROP TABLE IF EXISTS `activity_levels`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `activity_levels` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `status` tinyint DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `activity_log`
--

DROP TABLE IF EXISTS `activity_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `activity_log` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `activity_type` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `points` int DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `api`
--

DROP TABLE IF EXISTS `api`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `api` (
  `id` int NOT NULL AUTO_INCREMENT,
  `sccode` int DEFAULT NULL,
  `api_type` varchar(15) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `api_key` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `api_secret` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `allowed_id` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_by` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `areas`
--

DROP TABLE IF EXISTS `areas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `areas` (
  `id` int NOT NULL AUTO_INCREMENT,
  `idno` int DEFAULT '0',
  `user` varchar(150) COLLATE utf8mb3_unicode_ci NOT NULL,
  `slot` varchar(20) COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT 'School',
  `medium` varchar(10) COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT 'Bengali',
  `version` varchar(10) COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT 'Bengali',
  `areaname` varchar(255) COLLATE utf8mb3_unicode_ci NOT NULL,
  `subarea` varchar(20) COLLATE utf8mb3_unicode_ci NOT NULL,
  `sessionyear` varchar(7) COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '2024',
  `yesno` int NOT NULL DEFAULT '1',
  `classteacher` bigint DEFAULT NULL,
  `rollfrom` int DEFAULT NULL,
  `rollto` int DEFAULT NULL,
  `combind_1` int DEFAULT NULL,
  `combind_2` int DEFAULT NULL,
  `combind_3` int DEFAULT NULL,
  `combind_4` int DEFAULT NULL,
  `fourth` int DEFAULT NULL,
  `allsubject` varchar(200) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `half` int NOT NULL DEFAULT '0' COMMENT 'Result Entry Req ',
  `full` int NOT NULL DEFAULT '0' COMMENT 'Result Entry Req ',
  `halfdone` int NOT NULL DEFAULT '0',
  `fulldone` int NOT NULL DEFAULT '0',
  `entrytime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `sccode` int DEFAULT NULL,
  `modifieddate` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4199 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `auth_logs`
--

DROP TABLE IF EXISTS `auth_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `auth_logs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int DEFAULT NULL,
  `email_attempted` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ip` varchar(45) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_agent` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `action` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=401 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `bankinfo`
--

DROP TABLE IF EXISTS `bankinfo`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `bankinfo` (
  `id` int NOT NULL AUTO_INCREMENT,
  `sccode` int DEFAULT NULL,
  `slot` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `accno` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `acctype` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bankname` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `branch` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `openingdate` date DEFAULT NULL,
  `closingdate` date DEFAULT NULL,
  `status` int NOT NULL DEFAULT '1',
  `modifieddate` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `bankinfo_after_insert` AFTER INSERT ON `bankinfo` FOR EACH ROW BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, new_data, changed_by)
        VALUES(
            'bankinfo',
            'INSERT',
            NEW.`id`,
            JSON_OBJECT('id', NEW.`id`,'sccode', NEW.`sccode`,'slot', NEW.`slot`,'accno', NEW.`accno`,'acctype', NEW.`acctype`,'bankname', NEW.`bankname`,'branch', NEW.`branch`,'openingdate', NEW.`openingdate`,'closingdate', NEW.`closingdate`,'status', NEW.`status`,'modifieddate', NEW.`modifieddate`),
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
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `bankinfo_after_update` AFTER UPDATE ON `bankinfo` FOR EACH ROW BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, new_data, changed_by)
        VALUES(
            'bankinfo',
            'UPDATE',
            NEW.`id`,
            JSON_OBJECT('id', OLD.`id`,'sccode', OLD.`sccode`,'slot', OLD.`slot`,'accno', OLD.`accno`,'acctype', OLD.`acctype`,'bankname', OLD.`bankname`,'branch', OLD.`branch`,'openingdate', OLD.`openingdate`,'closingdate', OLD.`closingdate`,'status', OLD.`status`,'modifieddate', OLD.`modifieddate`),
            JSON_OBJECT('id', NEW.`id`,'sccode', NEW.`sccode`,'slot', NEW.`slot`,'accno', NEW.`accno`,'acctype', NEW.`acctype`,'bankname', NEW.`bankname`,'branch', NEW.`branch`,'openingdate', NEW.`openingdate`,'closingdate', NEW.`closingdate`,'status', NEW.`status`,'modifieddate', NEW.`modifieddate`),
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
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `bankinfo_after_delete` AFTER DELETE ON `bankinfo` FOR EACH ROW BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, changed_by)
        VALUES(
            'bankinfo',
            'DELETE',
            OLD.`id`,
            JSON_OBJECT('id', OLD.`id`,'sccode', OLD.`sccode`,'slot', OLD.`slot`,'accno', OLD.`accno`,'acctype', OLD.`acctype`,'bankname', OLD.`bankname`,'branch', OLD.`branch`,'openingdate', OLD.`openingdate`,'closingdate', OLD.`closingdate`,'status', OLD.`status`,'modifieddate', OLD.`modifieddate`),
            COALESCE(@current_user,'system')
        );
    END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `banktrans`
--

DROP TABLE IF EXISTS `banktrans`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `banktrans` (
  `id` int NOT NULL AUTO_INCREMENT,
  `sccode` int DEFAULT NULL,
  `accid` int DEFAULT NULL,
  `accno` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `slno` int NOT NULL DEFAULT '0',
  `date` date DEFAULT NULL,
  `transopening` double DEFAULT '0',
  `transtype` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `partid` int DEFAULT NULL,
  `particulareng` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `particularben` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `chqno` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `amount` double DEFAULT '0',
  `balance` double DEFAULT '0',
  `refno` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `entryby` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `entrytime` datetime DEFAULT NULL,
  `verified` int NOT NULL DEFAULT '0',
  `verifyby` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `verifytime` datetime DEFAULT NULL,
  `modifieddate` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=314 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `banktrans_after_insert` AFTER INSERT ON `banktrans` FOR EACH ROW BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, new_data, changed_by)
        VALUES(
            'banktrans',
            'INSERT',
            NEW.`id`,
            JSON_OBJECT('id', NEW.`id`,'sccode', NEW.`sccode`,'accid', NEW.`accid`,'accno', NEW.`accno`,'date', NEW.`date`,'transopening', NEW.`transopening`,'transtype', NEW.`transtype`,'partid', NEW.`partid`,'particulareng', NEW.`particulareng`,'particularben', NEW.`particularben`,'chqno', NEW.`chqno`,'amount', NEW.`amount`,'balance', NEW.`balance`,'refno', NEW.`refno`,'entryby', NEW.`entryby`,'entrytime', NEW.`entrytime`,'verified', NEW.`verified`,'verifyby', NEW.`verifyby`,'verifytime', NEW.`verifytime`,'modifieddate', NEW.`modifieddate`),
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
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `banktrans_after_update` AFTER UPDATE ON `banktrans` FOR EACH ROW BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, new_data, changed_by)
        VALUES(
            'banktrans',
            'UPDATE',
            NEW.`id`,
            JSON_OBJECT('id', OLD.`id`,'sccode', OLD.`sccode`,'accid', OLD.`accid`,'accno', OLD.`accno`,'date', OLD.`date`,'transopening', OLD.`transopening`,'transtype', OLD.`transtype`,'partid', OLD.`partid`,'particulareng', OLD.`particulareng`,'particularben', OLD.`particularben`,'chqno', OLD.`chqno`,'amount', OLD.`amount`,'balance', OLD.`balance`,'refno', OLD.`refno`,'entryby', OLD.`entryby`,'entrytime', OLD.`entrytime`,'verified', OLD.`verified`,'verifyby', OLD.`verifyby`,'verifytime', OLD.`verifytime`,'modifieddate', OLD.`modifieddate`),
            JSON_OBJECT('id', NEW.`id`,'sccode', NEW.`sccode`,'accid', NEW.`accid`,'accno', NEW.`accno`,'date', NEW.`date`,'transopening', NEW.`transopening`,'transtype', NEW.`transtype`,'partid', NEW.`partid`,'particulareng', NEW.`particulareng`,'particularben', NEW.`particularben`,'chqno', NEW.`chqno`,'amount', NEW.`amount`,'balance', NEW.`balance`,'refno', NEW.`refno`,'entryby', NEW.`entryby`,'entrytime', NEW.`entrytime`,'verified', NEW.`verified`,'verifyby', NEW.`verifyby`,'verifytime', NEW.`verifytime`,'modifieddate', NEW.`modifieddate`),
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
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `banktrans_after_delete` AFTER DELETE ON `banktrans` FOR EACH ROW BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, changed_by)
        VALUES(
            'banktrans',
            'DELETE',
            OLD.`id`,
            JSON_OBJECT('id', OLD.`id`,'sccode', OLD.`sccode`,'accid', OLD.`accid`,'accno', OLD.`accno`,'date', OLD.`date`,'transopening', OLD.`transopening`,'transtype', OLD.`transtype`,'partid', OLD.`partid`,'particulareng', OLD.`particulareng`,'particularben', OLD.`particularben`,'chqno', OLD.`chqno`,'amount', OLD.`amount`,'balance', OLD.`balance`,'refno', OLD.`refno`,'entryby', OLD.`entryby`,'entrytime', OLD.`entrytime`,'verified', OLD.`verified`,'verifyby', OLD.`verifyby`,'verifytime', OLD.`verifytime`,'modifieddate', OLD.`modifieddate`),
            COALESCE(@current_user,'system')
        );
    END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `billing_invoices`
--

DROP TABLE IF EXISTS `billing_invoices`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `billing_invoices` (
  `id` int NOT NULL AUTO_INCREMENT,
  `sccode` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `customer_name` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `invoice_no` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `invoice_date` date NOT NULL,
  `due_date` date DEFAULT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `discount` decimal(10,2) DEFAULT '0.00',
  `vat_percent` decimal(5,2) DEFAULT '0.00',
  `vat_amount` decimal(10,2) DEFAULT '0.00',
  `grand_total` decimal(10,2) NOT NULL,
  `paid_amount` decimal(10,2) DEFAULT '0.00',
  `due_amount` decimal(10,2) DEFAULT '0.00',
  `payment_status` enum('unpaid','partial','paid','cancelled') COLLATE utf8mb4_unicode_ci DEFAULT 'unpaid',
  `created_by` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `invoice_no` (`invoice_no`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `billing_items`
--

DROP TABLE IF EXISTS `billing_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `billing_items` (
  `id` int NOT NULL AUTO_INCREMENT,
  `invoice_id` int NOT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `qty` decimal(10,2) DEFAULT '1.00',
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
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `billing_payments` (
  `id` int NOT NULL AUTO_INCREMENT,
  `invoice_id` int NOT NULL,
  `payment_date` date NOT NULL,
  `payment_method` enum('cash','bank','bkash','nagad','card','other') COLLATE utf8mb4_unicode_ci DEFAULT 'cash',
  `transaction_id` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `invoice_id` (`invoice_id`),
  CONSTRAINT `billing_payments_ibfk_1` FOREIGN KEY (`invoice_id`) REFERENCES `billing_invoices` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `bkash_token_list`
--

DROP TABLE IF EXISTS `bkash_token_list`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `bkash_token_list` (
  `id` int NOT NULL AUTO_INCREMENT,
  `sccode` int DEFAULT NULL,
  `date` date DEFAULT NULL,
  `token` varchar(2500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `refresh_token` varchar(2500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `generate_time` datetime DEFAULT NULL,
  `expire_time` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=60 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `calendar`
--

DROP TABLE IF EXISTS `calendar`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `calendar` (
  `id` int NOT NULL AUTO_INCREMENT,
  `date` date DEFAULT NULL,
  `day` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sccode` int DEFAULT '0',
  `descrip` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `category` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `work` int NOT NULL DEFAULT '1',
  `class` int NOT NULL DEFAULT '1',
  `dateto` date DEFAULT NULL,
  `day_count` int NOT NULL DEFAULT '1',
  `icon` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'calendar3-event-fill',
  `color` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'lightgray',
  `modifieddate` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `calendar_after_insert` AFTER INSERT ON `calendar` FOR EACH ROW BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, new_data, changed_by)
        VALUES(
            'calendar',
            'INSERT',
            NEW.`id`,
            JSON_OBJECT('id', NEW.`id`,'date', NEW.`date`,'day', NEW.`day`,'sccode', NEW.`sccode`,'descrip', NEW.`descrip`,'category', NEW.`category`,'work', NEW.`work`,'class', NEW.`class`,'dateto', NEW.`dateto`,'day_count', NEW.`day_count`,'icon', NEW.`icon`,'color', NEW.`color`,'modifieddate', NEW.`modifieddate`),
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
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `calendar_after_update` AFTER UPDATE ON `calendar` FOR EACH ROW BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, new_data, changed_by)
        VALUES(
            'calendar',
            'UPDATE',
            NEW.`id`,
            JSON_OBJECT('id', OLD.`id`,'date', OLD.`date`,'day', OLD.`day`,'sccode', OLD.`sccode`,'descrip', OLD.`descrip`,'category', OLD.`category`,'work', OLD.`work`,'class', OLD.`class`,'dateto', OLD.`dateto`,'day_count', OLD.`day_count`,'icon', OLD.`icon`,'color', OLD.`color`,'modifieddate', OLD.`modifieddate`),
            JSON_OBJECT('id', NEW.`id`,'date', NEW.`date`,'day', NEW.`day`,'sccode', NEW.`sccode`,'descrip', NEW.`descrip`,'category', NEW.`category`,'work', NEW.`work`,'class', NEW.`class`,'dateto', NEW.`dateto`,'day_count', NEW.`day_count`,'icon', NEW.`icon`,'color', NEW.`color`,'modifieddate', NEW.`modifieddate`),
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
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `calendar_after_delete` AFTER DELETE ON `calendar` FOR EACH ROW BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, changed_by)
        VALUES(
            'calendar',
            'DELETE',
            OLD.`id`,
            JSON_OBJECT('id', OLD.`id`,'date', OLD.`date`,'day', OLD.`day`,'sccode', OLD.`sccode`,'descrip', OLD.`descrip`,'category', OLD.`category`,'work', OLD.`work`,'class', OLD.`class`,'dateto', OLD.`dateto`,'day_count', OLD.`day_count`,'icon', OLD.`icon`,'color', OLD.`color`,'modifieddate', OLD.`modifieddate`),
            COALESCE(@current_user,'system')
        );
    END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `calendar_event_visibility`
--

DROP TABLE IF EXISTS `calendar_event_visibility`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `calendar_event_visibility` (
  `id` int NOT NULL AUTO_INCREMENT,
  `event_id` int NOT NULL,
  `viewer` enum('student','teacher','parent','staff') COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `event_id` (`event_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `calendar_events`
--

DROP TABLE IF EXISTS `calendar_events`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `calendar_events` (
  `id` int NOT NULL AUTO_INCREMENT,
  `institution_id` int NOT NULL,
  `session_id` int NOT NULL,
  `category_id` int NOT NULL,
  `title` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `location` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `start_date` datetime DEFAULT NULL,
  `end_date` datetime DEFAULT NULL,
  `all_day` tinyint(1) DEFAULT '0',
  `target_type` enum('ALL','CLASS','SECTION') COLLATE utf8mb4_unicode_ci DEFAULT 'ALL',
  `class_id` int DEFAULT NULL,
  `section_id` int DEFAULT NULL,
  `created_by` int DEFAULT NULL,
  `status` enum('active','cancelled') COLLATE utf8mb4_unicode_ci DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `institution_id` (`institution_id`,`session_id`),
  KEY `class_id` (`class_id`,`section_id`),
  KEY `category_id` (`category_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `cashbook`
--

DROP TABLE IF EXISTS `cashbook`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cashbook` (
  `id` int NOT NULL AUTO_INCREMENT,
  `sccode` int DEFAULT NULL,
  `sessionyear` int DEFAULT NULL,
  `month` int NOT NULL DEFAULT '0',
  `year` int NOT NULL DEFAULT '0',
  `slots` varchar(15) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date` date DEFAULT NULL,
  `type` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `refno` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT '0',
  `partid` int DEFAULT NULL,
  `category` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `memono` int DEFAULT '0',
  `particulars` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `income` double NOT NULL DEFAULT '0',
  `expenditure` double NOT NULL DEFAULT '0',
  `amount` double NOT NULL DEFAULT '0',
  `entryby` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `entrytime` datetime DEFAULT NULL,
  `ongoing` int NOT NULL DEFAULT '0',
  `module` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT 'bank/voucher/',
  `status` int DEFAULT NULL,
  `modifieddate` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=191 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `cashbook_after_insert` AFTER INSERT ON `cashbook` FOR EACH ROW BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, new_data, changed_by)
        VALUES(
            'cashbook',
            'INSERT',
            NEW.`id`,
            JSON_OBJECT('id', NEW.`id`,'sccode', NEW.`sccode`,'sessionyear', NEW.`sessionyear`,'month', NEW.`month`,'year', NEW.`year`,'slots', NEW.`slots`,'date', NEW.`date`,'type', NEW.`type`,'refno', NEW.`refno`,'partid', NEW.`partid`,'category', NEW.`category`,'memono', NEW.`memono`,'particulars', NEW.`particulars`,'income', NEW.`income`,'expenditure', NEW.`expenditure`,'amount', NEW.`amount`,'entryby', NEW.`entryby`,'entrytime', NEW.`entrytime`,'ongoing', NEW.`ongoing`,'module', NEW.`module`,'status', NEW.`status`,'modifieddate', NEW.`modifieddate`),
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
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `cashbook_after_update` AFTER UPDATE ON `cashbook` FOR EACH ROW BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, new_data, changed_by)
        VALUES(
            'cashbook',
            'UPDATE',
            NEW.`id`,
            JSON_OBJECT('id', OLD.`id`,'sccode', OLD.`sccode`,'sessionyear', OLD.`sessionyear`,'month', OLD.`month`,'year', OLD.`year`,'slots', OLD.`slots`,'date', OLD.`date`,'type', OLD.`type`,'refno', OLD.`refno`,'partid', OLD.`partid`,'category', OLD.`category`,'memono', OLD.`memono`,'particulars', OLD.`particulars`,'income', OLD.`income`,'expenditure', OLD.`expenditure`,'amount', OLD.`amount`,'entryby', OLD.`entryby`,'entrytime', OLD.`entrytime`,'ongoing', OLD.`ongoing`,'module', OLD.`module`,'status', OLD.`status`,'modifieddate', OLD.`modifieddate`),
            JSON_OBJECT('id', NEW.`id`,'sccode', NEW.`sccode`,'sessionyear', NEW.`sessionyear`,'month', NEW.`month`,'year', NEW.`year`,'slots', NEW.`slots`,'date', NEW.`date`,'type', NEW.`type`,'refno', NEW.`refno`,'partid', NEW.`partid`,'category', NEW.`category`,'memono', NEW.`memono`,'particulars', NEW.`particulars`,'income', NEW.`income`,'expenditure', NEW.`expenditure`,'amount', NEW.`amount`,'entryby', NEW.`entryby`,'entrytime', NEW.`entrytime`,'ongoing', NEW.`ongoing`,'module', NEW.`module`,'status', NEW.`status`,'modifieddate', NEW.`modifieddate`),
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
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `cashbook_after_delete` AFTER DELETE ON `cashbook` FOR EACH ROW BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, changed_by)
        VALUES(
            'cashbook',
            'DELETE',
            OLD.`id`,
            JSON_OBJECT('id', OLD.`id`,'sccode', OLD.`sccode`,'sessionyear', OLD.`sessionyear`,'month', OLD.`month`,'year', OLD.`year`,'slots', OLD.`slots`,'date', OLD.`date`,'type', OLD.`type`,'refno', OLD.`refno`,'partid', OLD.`partid`,'category', OLD.`category`,'memono', OLD.`memono`,'particulars', OLD.`particulars`,'income', OLD.`income`,'expenditure', OLD.`expenditure`,'amount', OLD.`amount`,'entryby', OLD.`entryby`,'entrytime', OLD.`entrytime`,'ongoing', OLD.`ongoing`,'module', OLD.`module`,'status', OLD.`status`,'modifieddate', OLD.`modifieddate`),
            COALESCE(@current_user,'system')
        );
    END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `chats`
--

DROP TABLE IF EXISTS `chats`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `chats` (
  `id` int NOT NULL AUTO_INCREMENT,
  `sccode` int DEFAULT '0' COMMENT 'Test Comments',
  `sender_id` int DEFAULT NULL,
  `receiver_id` int DEFAULT NULL,
  `message` text COLLATE utf8mb4_unicode_ci,
  `sent_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dev_notes`
--

DROP TABLE IF EXISTS `dev_notes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `dev_notes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `ref_id` int DEFAULT NULL,
  `sccode` int DEFAULT NULL,
  `ticket_id` int NOT NULL,
  `admin_id` int NOT NULL,
  `note_line` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('New','Open','Waiting','Replied','Progress','Hold','Resolved','Closed') COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `ticket_id` (`ticket_id`)
) ENGINE=InnoDB AUTO_INCREMENT=69 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dev_timeline`
--

DROP TABLE IF EXISTS `dev_timeline`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `dev_timeline` (
  `id` int NOT NULL AUTO_INCREMENT,
  `page_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `feature_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `action_type` enum('implement','update','bug_fix','remove','change','refactor','optimize','security_patch','deprecate','migrate','test_case','rollback','hotfix') COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('draft','planning','in_progress','testing','alpha','beta','rc','staging','stable','lts','deprecated','archived') COLLATE utf8mb4_unicode_ci DEFAULT 'draft',
  `description` text COLLATE utf8mb4_unicode_ci,
  `logged_by` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=214 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `event_categories`
--

DROP TABLE IF EXISTS `event_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `event_categories` (
  `id` int NOT NULL AUTO_INCREMENT,
  `institution_id` int NOT NULL,
  `name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `color` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT 'primary',
  `icon` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `priority` int DEFAULT '0',
  `status` enum('active','inactive') COLLATE utf8mb4_unicode_ci DEFAULT 'active',
  PRIMARY KEY (`id`),
  KEY `institution_id` (`institution_id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `events`
--

DROP TABLE IF EXISTS `events`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `events` (
  `id` int NOT NULL AUTO_INCREMENT,
  `sccode` int DEFAULT NULL,
  `user_id` int DEFAULT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `start` datetime NOT NULL,
  `end` datetime DEFAULT NULL,
  `all_day` tinyint(1) DEFAULT '0',
  `color` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT '#7367F0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `event_type` enum('holiday','exam','class','sports','meeting','notice','other') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'other',
  `scope` enum('institution','personal') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'institution',
  `parent_event_id` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=48 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `examlist`
--

DROP TABLE IF EXISTS `examlist`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `examlist` (
  `id` int NOT NULL AUTO_INCREMENT,
  `sccode` int DEFAULT NULL,
  `sessionyear` varchar(11) COLLATE utf32_unicode_ci DEFAULT NULL,
  `slot` varchar(20) COLLATE utf32_unicode_ci NOT NULL DEFAULT 'School',
  `examtitle` varchar(50) COLLATE utf32_unicode_ci DEFAULT NULL,
  `examcode` varchar(25) COLLATE utf32_unicode_ci DEFAULT NULL,
  `linkedexam` int DEFAULT NULL,
  `exam_group` varchar(25) COLLATE utf32_unicode_ci DEFAULT NULL,
  `exam_type` varchar(3) COLLATE utf32_unicode_ci DEFAULT 'PE',
  `classname` varchar(25) COLLATE utf32_unicode_ci DEFAULT NULL,
  `sectionname` varchar(25) COLLATE utf32_unicode_ci DEFAULT NULL,
  `datestart` date DEFAULT NULL,
  `result_publish` datetime DEFAULT NULL,
  `createdby` varchar(100) COLLATE utf32_unicode_ci DEFAULT NULL,
  `createtime` datetime DEFAULT NULL,
  `status` int DEFAULT '0',
  `hall_code` varchar(512) COLLATE utf32_unicode_ci DEFAULT NULL,
  `modifieddate` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=81 DEFAULT CHARSET=utf32 COLLATE=utf32_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
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
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
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
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
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
-- Table structure for table `examroutine`
--

DROP TABLE IF EXISTS `examroutine`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `examroutine` (
  `sessionyear` int NOT NULL,
  `examname` text NOT NULL,
  `id` int NOT NULL AUTO_INCREMENT,
  `sccode` int NOT NULL,
  `date` date NOT NULL,
  `time` time NOT NULL,
  `clsname` varchar(30) NOT NULL,
  `secname` varchar(50) NOT NULL,
  `subcode` int DEFAULT NULL,
  `subj` varchar(100) NOT NULL,
  `progress` int NOT NULL DEFAULT '0',
  `modifieddate` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1807 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `examroutine_after_insert` AFTER INSERT ON `examroutine` FOR EACH ROW BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, new_data, changed_by)
        VALUES(
            'examroutine',
            'INSERT',
            NEW.`id`,
            JSON_OBJECT('sessionyear', NEW.`sessionyear`,'examname', NEW.`examname`,'id', NEW.`id`,'sccode', NEW.`sccode`,'date', NEW.`date`,'time', NEW.`time`,'clsname', NEW.`clsname`,'secname', NEW.`secname`,'subcode', NEW.`subcode`,'subj', NEW.`subj`,'progress', NEW.`progress`,'modifieddate', NEW.`modifieddate`),
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
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `examroutine_after_update` AFTER UPDATE ON `examroutine` FOR EACH ROW BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, new_data, changed_by)
        VALUES(
            'examroutine',
            'UPDATE',
            NEW.`id`,
            JSON_OBJECT('sessionyear', OLD.`sessionyear`,'examname', OLD.`examname`,'id', OLD.`id`,'sccode', OLD.`sccode`,'date', OLD.`date`,'time', OLD.`time`,'clsname', OLD.`clsname`,'secname', OLD.`secname`,'subcode', OLD.`subcode`,'subj', OLD.`subj`,'progress', OLD.`progress`,'modifieddate', OLD.`modifieddate`),
            JSON_OBJECT('sessionyear', NEW.`sessionyear`,'examname', NEW.`examname`,'id', NEW.`id`,'sccode', NEW.`sccode`,'date', NEW.`date`,'time', NEW.`time`,'clsname', NEW.`clsname`,'secname', NEW.`secname`,'subcode', NEW.`subcode`,'subj', NEW.`subj`,'progress', NEW.`progress`,'modifieddate', NEW.`modifieddate`),
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
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `examroutine_after_delete` AFTER DELETE ON `examroutine` FOR EACH ROW BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, changed_by)
        VALUES(
            'examroutine',
            'DELETE',
            OLD.`id`,
            JSON_OBJECT('sessionyear', OLD.`sessionyear`,'examname', OLD.`examname`,'id', OLD.`id`,'sccode', OLD.`sccode`,'date', OLD.`date`,'time', OLD.`time`,'clsname', OLD.`clsname`,'secname', OLD.`secname`,'subcode', OLD.`subcode`,'subj', OLD.`subj`,'progress', OLD.`progress`,'modifieddate', OLD.`modifieddate`),
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
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `faqs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `page_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `question` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `answer` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` tinyint(1) DEFAULT '1',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `features`
--

DROP TABLE IF EXISTS `features`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `features` (
  `id` int NOT NULL AUTO_INCREMENT,
  `feature_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `module_name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fee_structure`
--

DROP TABLE IF EXISTS `fee_structure`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `fee_structure` (
  `id` int NOT NULL AUTO_INCREMENT,
  `sccode` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `class_name` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fee_type` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `amount` double DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `feedback_target`
--

DROP TABLE IF EXISTS `feedback_target`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `feedback_target` (
  `id` int NOT NULL AUTO_INCREMENT,
  `target_type` enum('module','user','system','other') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'module',
  `target_id` int DEFAULT NULL,
  `target_name` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `feedbacks`
--

DROP TABLE IF EXISTS `feedbacks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `feedbacks` (
  `id` int NOT NULL AUTO_INCREMENT,
  `sccode` int DEFAULT NULL,
  `email` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `rating` int NOT NULL,
  `feedback` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `target_id` int DEFAULT NULL,
  `target_type` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
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
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `financesetup` (
  `id` int NOT NULL AUTO_INCREMENT,
  `sccode` int NOT NULL,
  `slot` varchar(25) COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT 'School',
  `sessionyear` varchar(7) COLLATE utf8mb3_unicode_ci NOT NULL,
  `slno` int DEFAULT NULL,
  `itemcode` varchar(30) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `particulareng` varchar(255) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `particularben` varchar(255) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `new_only` int NOT NULL DEFAULT '0',
  `splitable` int NOT NULL DEFAULT '0',
  `play` int DEFAULT '0',
  `nursery` int DEFAULT '0',
  `one` int DEFAULT '0',
  `two` int DEFAULT '0',
  `three` int DEFAULT '0',
  `four` int DEFAULT '0',
  `five` int DEFAULT '0',
  `six` int NOT NULL DEFAULT '0',
  `seven` int NOT NULL DEFAULT '0',
  `eight` int NOT NULL DEFAULT '0',
  `nine` int NOT NULL DEFAULT '0',
  `ten` int NOT NULL DEFAULT '0',
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
  `month` int NOT NULL DEFAULT '1',
  `inexin` int NOT NULL DEFAULT '0',
  `inexex` int NOT NULL DEFAULT '0',
  `cheque` int NOT NULL DEFAULT '0' COMMENT 'issue cheque on this category',
  `custom` int NOT NULL DEFAULT '0',
  `last_update` datetime DEFAULT NULL,
  `need_update` int NOT NULL DEFAULT '1',
  `validationtime` datetime DEFAULT '2024-01-01 00:00:00',
  `modifieddate` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=620 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
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
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
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
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
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
-- Table structure for table `financesetupind`
--

DROP TABLE IF EXISTS `financesetupind`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `financesetupind` (
  `id` int NOT NULL AUTO_INCREMENT,
  `sccode` int NOT NULL,
  `slot` varchar(25) COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT 'School',
  `sessionyear` varchar(7) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `stid` bigint DEFAULT NULL,
  `slno` int DEFAULT NULL,
  `itemcode` varchar(32) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `classname` varchar(20) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `sectionname` varchar(20) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `amount` int DEFAULT '0',
  `update_time` datetime DEFAULT NULL,
  `month` int NOT NULL DEFAULT '1',
  `inexin` int NOT NULL DEFAULT '0',
  `inexex` int NOT NULL DEFAULT '0',
  `cheque` int NOT NULL DEFAULT '0' COMMENT 'issue cheque on this category',
  `custom` int NOT NULL DEFAULT '0',
  `last_update` datetime DEFAULT NULL,
  `need_update` int NOT NULL DEFAULT '1',
  `validationtime` datetime DEFAULT '2024-01-01 00:00:00',
  `modifieddate` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=420 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `financesetupind_after_insert` AFTER INSERT ON `financesetupind` FOR EACH ROW BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, new_data, changed_by)
        VALUES(
            'financesetupind',
            'INSERT',
            NEW.`id`,
            JSON_OBJECT('id', NEW.`id`,'sccode', NEW.`sccode`,'slot', NEW.`slot`,'sessionyear', NEW.`sessionyear`,'stid', NEW.`stid`,'slno', NEW.`slno`,'itemcode', NEW.`itemcode`,'classname', NEW.`classname`,'sectionname', NEW.`sectionname`,'amount', NEW.`amount`,'update_time', NEW.`update_time`,'month', NEW.`month`,'inexin', NEW.`inexin`,'inexex', NEW.`inexex`,'cheque', NEW.`cheque`,'custom', NEW.`custom`,'last_update', NEW.`last_update`,'need_update', NEW.`need_update`,'validationtime', NEW.`validationtime`,'modifieddate', NEW.`modifieddate`),
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
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `financesetupind_after_update` AFTER UPDATE ON `financesetupind` FOR EACH ROW BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, new_data, changed_by)
        VALUES(
            'financesetupind',
            'UPDATE',
            NEW.`id`,
            JSON_OBJECT('id', OLD.`id`,'sccode', OLD.`sccode`,'slot', OLD.`slot`,'sessionyear', OLD.`sessionyear`,'stid', OLD.`stid`,'slno', OLD.`slno`,'itemcode', OLD.`itemcode`,'classname', OLD.`classname`,'sectionname', OLD.`sectionname`,'amount', OLD.`amount`,'update_time', OLD.`update_time`,'month', OLD.`month`,'inexin', OLD.`inexin`,'inexex', OLD.`inexex`,'cheque', OLD.`cheque`,'custom', OLD.`custom`,'last_update', OLD.`last_update`,'need_update', OLD.`need_update`,'validationtime', OLD.`validationtime`,'modifieddate', OLD.`modifieddate`),
            JSON_OBJECT('id', NEW.`id`,'sccode', NEW.`sccode`,'slot', NEW.`slot`,'sessionyear', NEW.`sessionyear`,'stid', NEW.`stid`,'slno', NEW.`slno`,'itemcode', NEW.`itemcode`,'classname', NEW.`classname`,'sectionname', NEW.`sectionname`,'amount', NEW.`amount`,'update_time', NEW.`update_time`,'month', NEW.`month`,'inexin', NEW.`inexin`,'inexex', NEW.`inexex`,'cheque', NEW.`cheque`,'custom', NEW.`custom`,'last_update', NEW.`last_update`,'need_update', NEW.`need_update`,'validationtime', NEW.`validationtime`,'modifieddate', NEW.`modifieddate`),
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
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `financesetupind_after_delete` AFTER DELETE ON `financesetupind` FOR EACH ROW BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, changed_by)
        VALUES(
            'financesetupind',
            'DELETE',
            OLD.`id`,
            JSON_OBJECT('id', OLD.`id`,'sccode', OLD.`sccode`,'slot', OLD.`slot`,'sessionyear', OLD.`sessionyear`,'stid', OLD.`stid`,'slno', OLD.`slno`,'itemcode', OLD.`itemcode`,'classname', OLD.`classname`,'sectionname', OLD.`sectionname`,'amount', OLD.`amount`,'update_time', OLD.`update_time`,'month', OLD.`month`,'inexin', OLD.`inexin`,'inexex', OLD.`inexex`,'cheque', OLD.`cheque`,'custom', OLD.`custom`,'last_update', OLD.`last_update`,'need_update', OLD.`need_update`,'validationtime', OLD.`validationtime`,'modifieddate', OLD.`modifieddate`),
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
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `financesetupvalue` (
  `id` int NOT NULL AUTO_INCREMENT,
  `sccode` int NOT NULL,
  `slot` varchar(25) COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT 'School',
  `sessionyear` varchar(7) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `slno` int DEFAULT NULL,
  `itemcode` varchar(32) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `new_only` int NOT NULL DEFAULT '0',
  `splitable` int NOT NULL DEFAULT '0',
  `classname` varchar(20) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `sectionname` varchar(20) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `amount` int DEFAULT '0',
  `update_time` datetime DEFAULT NULL,
  `month` int NOT NULL DEFAULT '1',
  `inexin` int NOT NULL DEFAULT '0',
  `inexex` int NOT NULL DEFAULT '0',
  `cheque` int NOT NULL DEFAULT '0' COMMENT 'issue cheque on this category',
  `custom` int NOT NULL DEFAULT '0',
  `last_update` datetime DEFAULT NULL,
  `need_update` int NOT NULL DEFAULT '1',
  `validationtime` datetime DEFAULT '2024-01-01 00:00:00',
  `modifieddate` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=652 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
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
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
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
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
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
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `globalsettings` (
  `id` int NOT NULL AUTO_INCREMENT,
  `sccode` int DEFAULT NULL,
  `stattnd_sort` varchar(10) COLLATE utf8mb3_unicode_ci DEFAULT 'rollno',
  `stattnd_multi` int DEFAULT '1',
  `tattnd` int DEFAULT '0',
  `collection` int NOT NULL DEFAULT '0' COMMENT '0 = Class Teacher, 1 = Administrator, 2 = Head Teacher',
  `tattndradius` int NOT NULL DEFAULT '50',
  `tattndout` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=89 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
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
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
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
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
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
-- Table structure for table `gpa`
--

DROP TABLE IF EXISTS `gpa`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `gpa` (
  `id` int NOT NULL AUTO_INCREMENT,
  `sccode` int DEFAULT NULL,
  `slot` varchar(20) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `minvalues` int NOT NULL,
  `maxvalues` int NOT NULL,
  `gp` float NOT NULL,
  `gl` varchar(3) COLLATE utf8mb3_unicode_ci NOT NULL,
  `remark` varchar(50) COLLATE utf8mb3_unicode_ci NOT NULL,
  `colorcode` varchar(6) COLLATE utf8mb3_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=54 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `gpa_after_insert` AFTER INSERT ON `gpa` FOR EACH ROW BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, new_data, changed_by)
        VALUES(
            'gpa',
            'INSERT',
            NEW.`id`,
            JSON_OBJECT('id', NEW.`id`,'sccode', NEW.`sccode`,'slot', NEW.`slot`,'minvalues', NEW.`minvalues`,'maxvalues', NEW.`maxvalues`,'gp', NEW.`gp`,'gl', NEW.`gl`,'remark', NEW.`remark`,'colorcode', NEW.`colorcode`),
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
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `gpa_after_update` AFTER UPDATE ON `gpa` FOR EACH ROW BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, new_data, changed_by)
        VALUES(
            'gpa',
            'UPDATE',
            NEW.`id`,
            JSON_OBJECT('id', OLD.`id`,'sccode', OLD.`sccode`,'slot', OLD.`slot`,'minvalues', OLD.`minvalues`,'maxvalues', OLD.`maxvalues`,'gp', OLD.`gp`,'gl', OLD.`gl`,'remark', OLD.`remark`,'colorcode', OLD.`colorcode`),
            JSON_OBJECT('id', NEW.`id`,'sccode', NEW.`sccode`,'slot', NEW.`slot`,'minvalues', NEW.`minvalues`,'maxvalues', NEW.`maxvalues`,'gp', NEW.`gp`,'gl', NEW.`gl`,'remark', NEW.`remark`,'colorcode', NEW.`colorcode`),
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
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `gpa_after_delete` AFTER DELETE ON `gpa` FOR EACH ROW BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, changed_by)
        VALUES(
            'gpa',
            'DELETE',
            OLD.`id`,
            JSON_OBJECT('id', OLD.`id`,'sccode', OLD.`sccode`,'slot', OLD.`slot`,'minvalues', OLD.`minvalues`,'maxvalues', OLD.`maxvalues`,'gp', OLD.`gp`,'gl', OLD.`gl`,'remark', OLD.`remark`,'colorcode', OLD.`colorcode`),
            COALESCE(@current_user,'system')
        );
    END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `inoutdata`
--

DROP TABLE IF EXISTS `inoutdata`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `inoutdata` (
  `id` int NOT NULL AUTO_INCREMENT,
  `sccode` int NOT NULL,
  `stid` varchar(20) COLLATE utf8mb3_unicode_ci NOT NULL,
  `datetime` varchar(20) COLLATE utf8mb3_unicode_ci NOT NULL,
  `chkdate` date NOT NULL,
  `chktime` time NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=30058855 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `logbook`
--

DROP TABLE IF EXISTS `logbook`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `logbook` (
  `id` int NOT NULL AUTO_INCREMENT,
  `email` varchar(120) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `sccode` int DEFAULT NULL,
  `pagename` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `filesize` float NOT NULL DEFAULT '0',
  `platform` varchar(120) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `browser` varchar(120) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `entrytime` datetime DEFAULT NULL,
  `modifieddate` datetime DEFAULT NULL,
  `bandwidth` bigint DEFAULT '0',
  `ipaddr` varchar(45) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `location` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `duration` int DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_email` (`email`),
  KEY `idx_page` (`pagename`),
  KEY `idx_time` (`entrytime`),
  KEY `idx_logbook_email_entry` (`email`,`entrytime`),
  KEY `idx_logbook_page` (`pagename`)
) ENGINE=InnoDB AUTO_INCREMENT=16989 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `messages`
--

DROP TABLE IF EXISTS `messages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `messages` (
  `id` int NOT NULL AUTO_INCREMENT,
  `sender_id` int NOT NULL,
  `receiver_id` int NOT NULL,
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `modulelist`
--

DROP TABLE IF EXISTS `modulelist`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `modulelist` (
  `id` int NOT NULL AUTO_INCREMENT,
  `slno` int NOT NULL DEFAULT '99',
  `module_name` varchar(25) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `module_icon` varchar(20) COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT 'circle-square',
  `descrip` varchar(250) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `is_public` int NOT NULL DEFAULT '1',
  `core` int NOT NULL DEFAULT '0',
  `entryby` varchar(120) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `modifieddate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `modulemanager`
--

DROP TABLE IF EXISTS `modulemanager`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `modulemanager` (
  `id` int NOT NULL AUTO_INCREMENT,
  `module_name` varchar(25) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `module_topic` varchar(200) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `descrip` varchar(500) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `status_name` tinyint NOT NULL DEFAULT '0',
  `related_pages` varchar(500) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `nav_icon` varchar(25) COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT 'three-dots-vertical',
  `nav_title` varchar(50) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `root_page` varchar(50) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `entryby` varchar(120) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `modifieddate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=80 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `notifications`
--

DROP TABLE IF EXISTS `notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `notifications` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int DEFAULT NULL,
  `type` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `message` text COLLATE utf8mb4_unicode_ci,
  `link` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=70 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `otp_store`
--

DROP TABLE IF EXISTS `otp_store`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `otp_store` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `user_id` bigint NOT NULL,
  `otp_hash` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `channel` enum('email','sms','auth_app') COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `expires_at` timestamp NULL DEFAULT NULL,
  `consumed` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `expires_at` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `package_limit_data`
--

DROP TABLE IF EXISTS `package_limit_data`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `package_limit_data` (
  `id` int NOT NULL AUTO_INCREMENT,
  `sccode` int DEFAULT NULL,
  `email` varchar(100) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `page_name` varchar(50) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `entry_count` int DEFAULT NULL,
  `access_count` int DEFAULT NULL,
  `total_stay` float DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=77 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `package_map`
--

DROP TABLE IF EXISTS `package_map`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `package_map` (
  `id` int NOT NULL AUTO_INCREMENT,
  `page_name` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `package_id` int NOT NULL,
  `access` enum('Yes','No') COLLATE utf8mb4_general_ci DEFAULT 'Yes' COMMENT 'View Permission',
  `entry_limit` int DEFAULT NULL COMMENT 'Max number of entries allowed',
  `view_limit` int NOT NULL DEFAULT '0',
  `total_time_limit` int DEFAULT NULL COMMENT 'Total usage time limit (sec)',
  `access_count_limit` int DEFAULT NULL COMMENT 'How many times page can be accessed',
  `max_stay_limit` int DEFAULT NULL COMMENT 'Max stay duration per session',
  `print` enum('Yes','No') COLLATE utf8mb4_general_ci DEFAULT 'Yes',
  `created_by` int DEFAULT NULL,
  `modified_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `package_id` (`package_id`)
) ENGINE=InnoDB AUTO_INCREMENT=36 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `package_settings`
--

DROP TABLE IF EXISTS `package_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `package_settings` (
  `id` int NOT NULL AUTO_INCREMENT,
  `package_id` int NOT NULL,
  `ins_tier` enum('A','B','C','D','E') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `price` decimal(10,2) DEFAULT '0.00',
  `total_uses_limit` int DEFAULT NULL,
  `photo_upload` int DEFAULT NULL,
  `print` int DEFAULT NULL,
  `billing_cycle` enum('Monthly','Quarterly','Half Yearly','Yearly') COLLATE utf8mb4_unicode_ci DEFAULT 'Monthly',
  `payment_model` enum('Pre-paid','Post-paid') COLLATE utf8mb4_unicode_ci DEFAULT 'Pre-paid',
  `module` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `panel` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('active','inactive') COLLATE utf8mb4_unicode_ci DEFAULT 'active',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `packages`
--

DROP TABLE IF EXISTS `packages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `packages` (
  `id` int NOT NULL AUTO_INCREMENT,
  `serial` int NOT NULL,
  `package_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `package_code` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `status` enum('active','inactive') COLLATE utf8mb4_unicode_ci DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `package_code` (`package_code`)
) ENGINE=InnoDB AUTO_INCREMENT=61 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `page_feedback`
--

DROP TABLE IF EXISTS `page_feedback`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `page_feedback` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `sccode` int DEFAULT NULL,
  `page_name` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `feature_name` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `status_name` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `feedback_type` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `notes` text COLLATE utf8mb4_general_ci,
  `rating` tinyint unsigned DEFAULT '0',
  `logged_by` varchar(100) COLLATE utf8mb4_general_ci DEFAULT 'User',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=67 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `pages`
--

DROP TABLE IF EXISTS `pages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pages` (
  `id` int NOT NULL AUTO_INCREMENT,
  `page_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `page_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `payment_pgw`
--

DROP TABLE IF EXISTS `payment_pgw`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `payment_pgw` (
  `id` int NOT NULL AUTO_INCREMENT,
  `sccode` int NOT NULL,
  `sessionyear` int NOT NULL,
  `stid` bigint NOT NULL,
  `paydate` date NOT NULL,
  `partial` varchar(10) COLLATE utf8mb4_general_ci DEFAULT 'Full',
  `paymentID` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `trxID` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `transactionStatus` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `amount` decimal(10,2) DEFAULT NULL,
  `currency` varchar(10) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `intent` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `paymentExecuteTime` datetime DEFAULT NULL,
  `merchantInvoiceNumber` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `payerType` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `payerReference` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `customerMsisdn` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `payerAccount` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `maxRefundableAmount` decimal(10,2) DEFAULT NULL,
  `statusCode` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `statusMessage` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `gateway` varchar(20) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'bkash',
  `token_id` int DEFAULT NULL,
  `entrytime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `payments`
--

DROP TABLE IF EXISTS `payments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `payments` (
  `id` int NOT NULL AUTO_INCREMENT,
  `invoice_no` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` int DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `bkash_payment_id` varchar(60) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `trx_id` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('Pending','Success','Failed') COLLATE utf8mb4_unicode_ci DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `permission_audit`
--

DROP TABLE IF EXISTS `permission_audit`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `permission_audit` (
  `id` int NOT NULL AUTO_INCREMENT,
  `role_id` int DEFAULT NULL,
  `user_id` int DEFAULT NULL,
  `page_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `userlevel` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `old_permission` tinyint(1) DEFAULT '0',
  `new_permission` tinyint(1) DEFAULT '0',
  `crud_action` varchar(15) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `changed_by` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `changed_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=111 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `permission_map`
--

DROP TABLE IF EXISTS `permission_map`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `permission_map` (
  `id` int NOT NULL AUTO_INCREMENT,
  `page_name` varchar(40) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `userlevel` varchar(20) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `sccode` varchar(6) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `email` varchar(120) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `permission` int NOT NULL DEFAULT '0',
  `entryby` varchar(120) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `modifiedtime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=147 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `poll_votes`
--

DROP TABLE IF EXISTS `poll_votes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `poll_votes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `poll_id` int NOT NULL,
  `user_id` int NOT NULL,
  `option_text` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `voted_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_vote` (`poll_id`,`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `polls`
--

DROP TABLE IF EXISTS `polls`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `polls` (
  `id` int NOT NULL AUTO_INCREMENT,
  `question` text COLLATE utf8mb4_general_ci NOT NULL,
  `options_json` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_by` int DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `expires_at` datetime DEFAULT NULL,
  `status` enum('active','closed') COLLATE utf8mb4_general_ci DEFAULT 'active',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `project_documentation`
--

DROP TABLE IF EXISTS `project_documentation`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `project_documentation` (
  `id` int NOT NULL AUTO_INCREMENT,
  `page_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `feature_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `feature_title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `feature_description` text COLLATE utf8mb4_unicode_ci,
  `full_documentation` longtext COLLATE utf8mb4_unicode_ci,
  `created_by` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `tags` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `version` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT '1.0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `registrations`
--

DROP TABLE IF EXISTS `registrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `registrations` (
  `id` int NOT NULL AUTO_INCREMENT,
  `sessionyear` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '2026',
  `sccode` int DEFAULT NULL,
  `admit_class` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT 'Six',
  `roll_no` int DEFAULT NULL,
  `reg_id` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `pin` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `stnameeng` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `stnameben` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `religion` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Islam',
  `gender` varchar(8) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bgroup` varchar(3) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fname` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `falive` varchar(3) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Yes',
  `fmobile` varchar(11) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mname` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `malive` varchar(3) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Yes',
  `mmobile` varchar(11) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `guar` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Father',
  `guarname` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mnumber` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `dist` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ps` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `po` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `village` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `dob` date DEFAULT NULL,
  `brnno` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `testno` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `insdist` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `insps` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `inspo` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `insname` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `photo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `otp` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `otp_expires` datetime DEFAULT NULL,
  `verified` tinyint(1) DEFAULT '0',
  `verifytime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `adm_test_mark` float NOT NULL DEFAULT '0',
  `meritplace` int DEFAULT NULL,
  `marktime` datetime DEFAULT NULL,
  `stid` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=124 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `remembered_devices`
--

DROP TABLE IF EXISTS `remembered_devices`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `remembered_devices` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `user_id` bigint NOT NULL,
  `device_fp` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `label` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_seen` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
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
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `rolemanager` (
  `id` int NOT NULL AUTO_INCREMENT,
  `sccode` int NOT NULL DEFAULT '0',
  `userlevel` varchar(25) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `descrip` varchar(250) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `entryby` varchar(120) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `modifieddate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `schema_update_log`
--

DROP TABLE IF EXISTS `schema_update_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `schema_update_log` (
  `id` int NOT NULL AUTO_INCREMENT,
  `executed_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `sql_statement` text COLLATE utf8mb4_general_ci NOT NULL,
  `status` enum('APPLIED','FAILED') COLLATE utf8mb4_general_ci NOT NULL,
  `error_message` text COLLATE utf8mb4_general_ci,
  `backup_path` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=132 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `scinfo`
--

DROP TABLE IF EXISTS `scinfo`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `scinfo` (
  `id` int NOT NULL AUTO_INCREMENT,
  `sccode` int DEFAULT NULL,
  `scname` varchar(100) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `sccategory` varchar(50) COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT 'School',
  `short` varchar(10) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `scadd1` varchar(100) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `scadd2` varchar(100) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `ps` varchar(50) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `dist` varchar(50) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `postal_code` int DEFAULT NULL,
  `zone` varchar(25) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `mobile` varchar(11) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `scmail` varchar(200) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `scmail2` varchar(120) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `scweb` varchar(200) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `headname` varchar(50) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `headtitle` varchar(30) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `rootuser` varchar(100) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `modifieddate` datetime DEFAULT NULL,
  `logo` varchar(255) COLLATE utf8mb3_unicode_ci DEFAULT '-',
  `pack` int NOT NULL DEFAULT '0',
  `packdate` datetime DEFAULT NULL,
  `expire` datetime DEFAULT NULL,
  `count` int NOT NULL DEFAULT '0',
  `serviceattnd` int NOT NULL DEFAULT '0',
  `servicefinance` int NOT NULL DEFAULT '0',
  `servicestudent` int NOT NULL DEFAULT '0',
  `app` int NOT NULL DEFAULT '0',
  `progressguar` int NOT NULL DEFAULT '1' COMMENT 'Show Guardian in Progress Report',
  `browser` varchar(100) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `geolat` varchar(25) COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '23.72769',
  `geolon` varchar(25) COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '90.41047',
  `intime` time NOT NULL DEFAULT '09:45:00',
  `outtime` time NOT NULL DEFAULT '16:30:00',
  `dista_differ` int NOT NULL DEFAULT '50',
  `time_differ` int NOT NULL DEFAULT '600',
  `profile_track` int NOT NULL DEFAULT '0',
  `self_control` int NOT NULL DEFAULT '0',
  `backup` int NOT NULL DEFAULT '0',
  `algorithm` varchar(20) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `secret_key` varchar(50) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `api_key` varchar(50) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `backup_mail_2` varchar(100) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `backup_mail_3` varchar(100) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `daily_backup` int NOT NULL DEFAULT '0',
  `monthly_backup` int NOT NULL DEFAULT '0',
  `cloud_storage` int NOT NULL DEFAULT '0',
  `last_backup_time` datetime DEFAULT NULL,
  `display` int NOT NULL DEFAULT '1',
  `last_login_time` datetime DEFAULT NULL,
  `sms_send` int NOT NULL DEFAULT '0',
  `sms_success` int NOT NULL DEFAULT '0',
  `sms_error` int NOT NULL DEFAULT '0',
  `sms_cost` float NOT NULL DEFAULT '0',
  `sms_balance` float NOT NULL DEFAULT '0',
  `account_balance` float NOT NULL DEFAULT '0',
  `admin_data` varchar(1024) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `package_id` int NOT NULL DEFAULT '2',
  `package_name` varchar(20) COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT 'Trial',
  `tier` varchar(1) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `billing_data` varchar(100) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `valid_module` varchar(255) COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT 'Result',
  `active_module` varchar(255) COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT 'Result',
  `valid_panel` varchar(100) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `active_panel` varchar(100) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `theme` varchar(10) COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT 'Light',
  `bkash` varchar(255) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `rocket` varchar(255) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `nagad` varchar(255) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `bank` varchar(255) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `bkash_token` varchar(2500) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `bkash_refresh_token` varchar(2500) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `bkash_token_expire` datetime DEFAULT NULL,
  `sms_setting` varchar(500) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `sms_gateway` varchar(500) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `sms_in` varchar(500) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `sms_out` varchar(500) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `sms_absent` varchar(500) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `sms_payment` varchar(500) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `sms_dues` varchar(500) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `sms_month_report` varchar(500) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `reg_hash` varchar(32) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `hash_expire` datetime DEFAULT NULL,
  `active` int NOT NULL DEFAULT '0',
  `status` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=279 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
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
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
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
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
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
-- Table structure for table `scinfo_admin_data`
--

DROP TABLE IF EXISTS `scinfo_admin_data`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `scinfo_admin_data` (
  `id` int NOT NULL AUTO_INCREMENT,
  `sccode` int DEFAULT NULL,
  `package_id` int NOT NULL DEFAULT '2',
  `package_name` varchar(20) COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT 'Trial',
  `valid_module` varchar(100) COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT 'Result',
  `active_module` varchar(100) COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT 'Result',
  `theme` varchar(10) COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT 'Light',
  `bkash` varchar(255) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `rocket` varchar(255) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `nagad` varchar(255) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `bank` varchar(255) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `sms` varchar(512) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `sms_in` varchar(512) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `sms_out` varchar(512) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `sms_absent` varchar(512) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `sms_payment` varchar(512) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `sms_result` varchar(512) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `sms_report` varchar(512) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `search_index`
--

DROP TABLE IF EXISTS `search_index`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `search_index` (
  `id` int NOT NULL AUTO_INCREMENT,
  `sccode` int DEFAULT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `title_bn` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `icon` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `type` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `role` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ref_id` int DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `sessioninfo`
--

DROP TABLE IF EXISTS `sessioninfo`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sessioninfo` (
  `id` int NOT NULL AUTO_INCREMENT,
  `stid` varchar(11) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `sessionyear` varchar(9) COLLATE utf8mb3_unicode_ci NOT NULL,
  `classname` varchar(20) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `sectionname` varchar(30) COLLATE utf8mb3_unicode_ci NOT NULL,
  `rollno` int NOT NULL,
  `sccode` int NOT NULL,
  `slot` varchar(20) COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT 'School',
  `medium` varchar(10) COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT 'Bengali',
  `version` varchar(10) COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT 'Bengali',
  `fine` int NOT NULL DEFAULT '0',
  `icardst` varchar(10) COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '0',
  `rfidtag` varchar(12) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `fourth_subject` int NOT NULL DEFAULT '0',
  `voter_no` int DEFAULT NULL,
  `groupname` varchar(30) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `status` int NOT NULL DEFAULT '1',
  `gender` varchar(4) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `religion` varchar(10) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `finsetup` int NOT NULL DEFAULT '0',
  `lastpr` bigint DEFAULT NULL,
  `real_tution` int NOT NULL DEFAULT '0',
  `sector` varchar(50) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `rate` int NOT NULL DEFAULT '100',
  `amount` int NOT NULL DEFAULT '0',
  `trackyesterday` varchar(20) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `tracktoday` varchar(20) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `subject_list` varchar(100) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `validate` int DEFAULT '0',
  `validationtime` datetime NOT NULL DEFAULT '2024-01-01 00:00:00',
  `modifieddate` datetime DEFAULT NULL,
  `grand_merged` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=424 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
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
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
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
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
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
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sessionyear` (
  `id` int NOT NULL AUTO_INCREMENT,
  `sccode` int DEFAULT NULL,
  `syear` varchar(7) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `active` int NOT NULL DEFAULT '0',
  `entryby` varchar(120) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `entrytime` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=209 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
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
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
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
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
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
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `settings` (
  `id` int NOT NULL AUTO_INCREMENT,
  `slno` int DEFAULT NULL,
  `setting_title` varchar(100) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `sccode` int DEFAULT '0',
  `descrip` varchar(500) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `settings_value` varchar(100) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `modifieddate` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=198 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
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
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
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
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
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
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `settings_ins` (
  `id` int NOT NULL AUTO_INCREMENT,
  `sccode` int DEFAULT NULL,
  `settings_key` varchar(20) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `settings_value` varchar(255) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `update_by` varchar(100) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `update_time` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `slots`
--

DROP TABLE IF EXISTS `slots`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `slots` (
  `id` int NOT NULL AUTO_INCREMENT,
  `sccode` int DEFAULT NULL,
  `slotname` varchar(20) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `merit` int NOT NULL DEFAULT '0' COMMENT '0- total marks, 1- gpa',
  `cus_report` varchar(30) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `decimal_mark` int NOT NULL DEFAULT '0',
  `disp_entry_mark` int NOT NULL DEFAULT '0',
  `trans_name_eng` int NOT NULL DEFAULT '1',
  `trans_name_ben` int NOT NULL DEFAULT '1',
  `parents` varchar(4) COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT 'DOSO' COMMENT 'DOSO or, FM',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=45 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
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
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
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
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
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
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sms` (
  `id` int NOT NULL AUTO_INCREMENT,
  `sccode` int DEFAULT NULL,
  `sessionyear` varchar(10) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `stid` varchar(11) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `date` date DEFAULT NULL,
  `campaign` varchar(25) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `sms_type` varchar(100) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `mobile_number` varchar(11) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `sms_text` varchar(1024) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `sms_len` int NOT NULL DEFAULT '0',
  `count` int NOT NULL DEFAULT '0',
  `send_by` varchar(120) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `send_time` datetime DEFAULT NULL,
  `cost` float NOT NULL DEFAULT '0',
  `response_code` int NOT NULL DEFAULT '0',
  `message_id` varchar(10) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `success_message` varchar(50) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `error_message` varchar(50) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `status` varchar(11) COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '0',
  `comments` varchar(100) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `modifieddate` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=132 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
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
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
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
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
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
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sms_logs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `to_number` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `message` text COLLATE utf8mb4_unicode_ci,
  `status` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `sms_templete`
--

DROP TABLE IF EXISTS `sms_templete`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sms_templete` (
  `id` int NOT NULL AUTO_INCREMENT,
  `sccode` int DEFAULT NULL,
  `temp_type` varchar(20) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `temp_title` varchar(100) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `temp_text` varchar(500) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `created_by` varchar(100) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `created_time` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `sql_backup_log`
--

DROP TABLE IF EXISTS `sql_backup_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sql_backup_log` (
  `id` int NOT NULL AUTO_INCREMENT,
  `table_name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sql_statement` text COLLATE utf8mb4_unicode_ci,
  `action_type` enum('INSERT','UPDATE','DELETE') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `changed_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `exported` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=221 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `stattnd`
--

DROP TABLE IF EXISTS `stattnd`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `stattnd` (
  `id` int NOT NULL AUTO_INCREMENT,
  `sccode` int DEFAULT NULL,
  `sessionyear` int DEFAULT NULL,
  `stid` varchar(11) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `adate` date DEFAULT NULL,
  `period1` int NOT NULL DEFAULT '1',
  `period2` int NOT NULL DEFAULT '1',
  `period3` int NOT NULL DEFAULT '1',
  `period4` int NOT NULL DEFAULT '1',
  `period5` int NOT NULL DEFAULT '1',
  `period6` int NOT NULL DEFAULT '1',
  `period7` int NOT NULL DEFAULT '1',
  `period8` int DEFAULT '1',
  `bunk` int NOT NULL DEFAULT '0',
  `yn` int DEFAULT NULL,
  `entryby` varchar(30) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `entrytime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `classname` varchar(12) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `sectionname` varchar(20) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `rollno` int DEFAULT NULL,
  `stname` varchar(20) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `intime` time DEFAULT NULL,
  `outtime` time DEFAULT NULL,
  `sendsms` int NOT NULL DEFAULT '0',
  `mobileno` varchar(11) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `by2` varchar(120) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `by3` varchar(120) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `by4` varchar(120) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `by5` varchar(120) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `by6` varchar(120) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `by7` varchar(120) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `by8` varchar(120) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `time2` datetime DEFAULT NULL,
  `time3` datetime DEFAULT NULL,
  `time4` datetime DEFAULT NULL,
  `time5` datetime DEFAULT NULL,
  `time6` datetime DEFAULT NULL,
  `time7` datetime DEFAULT NULL,
  `time8` datetime DEFAULT NULL,
  `modifieddate` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=79701 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `stattnd_after_insert` AFTER INSERT ON `stattnd` FOR EACH ROW BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, new_data, changed_by)
        VALUES(
            'stattnd',
            'INSERT',
            NEW.`id`,
            JSON_OBJECT('id', NEW.`id`,'sccode', NEW.`sccode`,'sessionyear', NEW.`sessionyear`,'stid', NEW.`stid`,'adate', NEW.`adate`,'period1', NEW.`period1`,'period2', NEW.`period2`,'period3', NEW.`period3`,'period4', NEW.`period4`,'period5', NEW.`period5`,'period6', NEW.`period6`,'period7', NEW.`period7`,'period8', NEW.`period8`,'bunk', NEW.`bunk`,'yn', NEW.`yn`,'entryby', NEW.`entryby`,'entrytime', NEW.`entrytime`,'classname', NEW.`classname`,'sectionname', NEW.`sectionname`,'rollno', NEW.`rollno`,'stname', NEW.`stname`,'intime', NEW.`intime`,'outtime', NEW.`outtime`,'sendsms', NEW.`sendsms`,'mobileno', NEW.`mobileno`,'by2', NEW.`by2`,'by3', NEW.`by3`,'by4', NEW.`by4`,'by5', NEW.`by5`,'by6', NEW.`by6`,'by7', NEW.`by7`,'by8', NEW.`by8`,'time2', NEW.`time2`,'time3', NEW.`time3`,'time4', NEW.`time4`,'time5', NEW.`time5`,'time6', NEW.`time6`,'time7', NEW.`time7`,'time8', NEW.`time8`,'modifieddate', NEW.`modifieddate`),
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
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `stattnd_after_update` AFTER UPDATE ON `stattnd` FOR EACH ROW BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, new_data, changed_by)
        VALUES(
            'stattnd',
            'UPDATE',
            NEW.`id`,
            JSON_OBJECT('id', OLD.`id`,'sccode', OLD.`sccode`,'sessionyear', OLD.`sessionyear`,'stid', OLD.`stid`,'adate', OLD.`adate`,'period1', OLD.`period1`,'period2', OLD.`period2`,'period3', OLD.`period3`,'period4', OLD.`period4`,'period5', OLD.`period5`,'period6', OLD.`period6`,'period7', OLD.`period7`,'period8', OLD.`period8`,'bunk', OLD.`bunk`,'yn', OLD.`yn`,'entryby', OLD.`entryby`,'entrytime', OLD.`entrytime`,'classname', OLD.`classname`,'sectionname', OLD.`sectionname`,'rollno', OLD.`rollno`,'stname', OLD.`stname`,'intime', OLD.`intime`,'outtime', OLD.`outtime`,'sendsms', OLD.`sendsms`,'mobileno', OLD.`mobileno`,'by2', OLD.`by2`,'by3', OLD.`by3`,'by4', OLD.`by4`,'by5', OLD.`by5`,'by6', OLD.`by6`,'by7', OLD.`by7`,'by8', OLD.`by8`,'time2', OLD.`time2`,'time3', OLD.`time3`,'time4', OLD.`time4`,'time5', OLD.`time5`,'time6', OLD.`time6`,'time7', OLD.`time7`,'time8', OLD.`time8`,'modifieddate', OLD.`modifieddate`),
            JSON_OBJECT('id', NEW.`id`,'sccode', NEW.`sccode`,'sessionyear', NEW.`sessionyear`,'stid', NEW.`stid`,'adate', NEW.`adate`,'period1', NEW.`period1`,'period2', NEW.`period2`,'period3', NEW.`period3`,'period4', NEW.`period4`,'period5', NEW.`period5`,'period6', NEW.`period6`,'period7', NEW.`period7`,'period8', NEW.`period8`,'bunk', NEW.`bunk`,'yn', NEW.`yn`,'entryby', NEW.`entryby`,'entrytime', NEW.`entrytime`,'classname', NEW.`classname`,'sectionname', NEW.`sectionname`,'rollno', NEW.`rollno`,'stname', NEW.`stname`,'intime', NEW.`intime`,'outtime', NEW.`outtime`,'sendsms', NEW.`sendsms`,'mobileno', NEW.`mobileno`,'by2', NEW.`by2`,'by3', NEW.`by3`,'by4', NEW.`by4`,'by5', NEW.`by5`,'by6', NEW.`by6`,'by7', NEW.`by7`,'by8', NEW.`by8`,'time2', NEW.`time2`,'time3', NEW.`time3`,'time4', NEW.`time4`,'time5', NEW.`time5`,'time6', NEW.`time6`,'time7', NEW.`time7`,'time8', NEW.`time8`,'modifieddate', NEW.`modifieddate`),
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
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `stattnd_after_delete` AFTER DELETE ON `stattnd` FOR EACH ROW BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, changed_by)
        VALUES(
            'stattnd',
            'DELETE',
            OLD.`id`,
            JSON_OBJECT('id', OLD.`id`,'sccode', OLD.`sccode`,'sessionyear', OLD.`sessionyear`,'stid', OLD.`stid`,'adate', OLD.`adate`,'period1', OLD.`period1`,'period2', OLD.`period2`,'period3', OLD.`period3`,'period4', OLD.`period4`,'period5', OLD.`period5`,'period6', OLD.`period6`,'period7', OLD.`period7`,'period8', OLD.`period8`,'bunk', OLD.`bunk`,'yn', OLD.`yn`,'entryby', OLD.`entryby`,'entrytime', OLD.`entrytime`,'classname', OLD.`classname`,'sectionname', OLD.`sectionname`,'rollno', OLD.`rollno`,'stname', OLD.`stname`,'intime', OLD.`intime`,'outtime', OLD.`outtime`,'sendsms', OLD.`sendsms`,'mobileno', OLD.`mobileno`,'by2', OLD.`by2`,'by3', OLD.`by3`,'by4', OLD.`by4`,'by5', OLD.`by5`,'by6', OLD.`by6`,'by7', OLD.`by7`,'by8', OLD.`by8`,'time2', OLD.`time2`,'time3', OLD.`time3`,'time4', OLD.`time4`,'time5', OLD.`time5`,'time6', OLD.`time6`,'time7', OLD.`time7`,'time8', OLD.`time8`,'modifieddate', OLD.`modifieddate`),
            COALESCE(@current_user,'system')
        );
    END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `status_list`
--

DROP TABLE IF EXISTS `status_list`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `status_list` (
  `id` int NOT NULL AUTO_INCREMENT,
  `status` varchar(20) COLLATE utf8mb3_unicode_ci NOT NULL,
  `entrytime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `stfinance`
--

DROP TABLE IF EXISTS `stfinance`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `stfinance` (
  `id` int NOT NULL AUTO_INCREMENT,
  `sccode` int NOT NULL,
  `sessionyear` int NOT NULL,
  `classname` varchar(30) COLLATE utf8mb3_unicode_ci NOT NULL,
  `sectionname` varchar(30) COLLATE utf8mb3_unicode_ci NOT NULL,
  `stid` bigint DEFAULT NULL,
  `rollno` int NOT NULL,
  `partid` int DEFAULT NULL,
  `itemcode` varchar(32) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `particulareng` varchar(150) COLLATE utf8mb3_unicode_ci NOT NULL,
  `particularben` varchar(200) COLLATE utf8mb3_unicode_ci NOT NULL,
  `amount` int NOT NULL DEFAULT '0',
  `month` int NOT NULL,
  `idmon` varchar(30) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `setupdate` datetime DEFAULT NULL,
  `setupby` varchar(100) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `payableamt` int NOT NULL DEFAULT '0',
  `modifieddate` datetime DEFAULT NULL,
  `modifiedby` varchar(100) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `paid` int NOT NULL DEFAULT '0',
  `paidx` int NOT NULL DEFAULT '0',
  `dues` int NOT NULL DEFAULT '0',
  `pr1` int DEFAULT '0',
  `pr1no` bigint DEFAULT NULL,
  `pr1date` date DEFAULT NULL,
  `pr1by` varchar(100) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `cashbook1` int NOT NULL DEFAULT '0',
  `pr2` int DEFAULT '0',
  `pr2no` bigint DEFAULT NULL,
  `pr2date` date DEFAULT NULL,
  `pr2by` varchar(100) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `cashbook2` int NOT NULL DEFAULT '0',
  `remark` varchar(200) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `extra` int DEFAULT '0',
  `last_update` date DEFAULT NULL,
  `validate` int NOT NULL DEFAULT '0',
  `validationtime` datetime NOT NULL DEFAULT '2024-01-01 00:00:00',
  `deleteby` varchar(120) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `deletetime` datetime DEFAULT NULL,
  `splitid` varchar(10) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `splitid2` int DEFAULT NULL,
  `scan_status` int NOT NULL DEFAULT '3',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=670654 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
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
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
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
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
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
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `stmark` (
  `id` int NOT NULL AUTO_INCREMENT,
  `slot` varchar(20) COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT 'School',
  `sessionyear` varchar(11) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `sccode` int DEFAULT NULL,
  `exam` varchar(50) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `examid` int DEFAULT NULL,
  `linkedexam` int DEFAULT NULL,
  `exam_group` varchar(25) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `examtype` varchar(3) COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT 'PE',
  `classname` varchar(20) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `sectionname` varchar(30) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `subject` int DEFAULT NULL,
  `fullmark` int NOT NULL DEFAULT '0',
  `stid` bigint DEFAULT NULL,
  `ctest` decimal(7,2) DEFAULT '0.00',
  `mtest` int DEFAULT '0',
  `subj` int NOT NULL DEFAULT '0',
  `obj` int NOT NULL DEFAULT '0',
  `pra` int NOT NULL DEFAULT '0',
  `ca` decimal(5,2) NOT NULL DEFAULT '0.00',
  `sub_final` int NOT NULL DEFAULT '0',
  `obj_final` int NOT NULL DEFAULT '0',
  `pra_final` int NOT NULL DEFAULT '0',
  `markobt` decimal(5,2) NOT NULL DEFAULT '0.00',
  `on100` decimal(5,2) NOT NULL DEFAULT '0.00',
  `gp` float NOT NULL DEFAULT '0',
  `gl` varchar(3) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `excnt` int NOT NULL DEFAULT '1',
  `entrydate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `entryby` varchar(64) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `modifieddate` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=332715 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
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
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
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
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
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
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `stpr` (
  `id` int NOT NULL AUTO_INCREMENT,
  `sessionyear` int DEFAULT NULL,
  `sccode` int DEFAULT NULL,
  `classname` varchar(30) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `sectionname` varchar(30) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `stid` bigint DEFAULT NULL,
  `rollno` int DEFAULT NULL,
  `prno` bigint DEFAULT NULL,
  `prdate` date DEFAULT NULL,
  `partid` int DEFAULT NULL,
  `peng` varchar(255) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `pben` varchar(255) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `amount` int NOT NULL DEFAULT '0',
  `entryby` varchar(150) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `entrytime` datetime DEFAULT NULL,
  `smstxt` text COLLATE utf8mb3_unicode_ci,
  `smscnt` int DEFAULT NULL,
  `mobileno` varchar(11) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `smsstatus` int DEFAULT NULL,
  `statusvalue` varchar(50) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `cashbook` int NOT NULL DEFAULT '0',
  `collection_media` varchar(15) COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT 'Cash',
  `modifieddate` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7450 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
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
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
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
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
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
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `students` (
  `id` int NOT NULL AUTO_INCREMENT,
  `sccode` int DEFAULT NULL,
  `stid` varchar(11) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `stnameeng` varchar(40) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `stnameben` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fname` varchar(40) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fnameben` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `falive` int NOT NULL DEFAULT '1',
  `fprof` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fmobile` varchar(11) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fnid` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mname` varchar(40) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mnameben` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `malive` int NOT NULL DEFAULT '1',
  `mprof` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mmobile` varchar(11) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mnid` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `previll` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `prepo` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `preps` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `predist` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pervill` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `perpo` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `perps` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `perdist` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `dob` date DEFAULT NULL,
  `religion` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `brn` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `gender` varchar(7) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bgroup` varchar(3) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `disables` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `height` int DEFAULT NULL,
  `weight` int DEFAULT NULL,
  `mobileself` varchar(11) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `uniqueid` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `guarname` varchar(40) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `guaradd` varchar(60) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `guarrelation` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `guarmobile` varchar(11) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `guarnid` varchar(17) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `guarnameben` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `guaremail` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `guarmobile2` varchar(11) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `guaremail2` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tcno` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `preins` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `preinsadd` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `doa` date DEFAULT NULL,
  `modify` datetime DEFAULT NULL,
  `photo` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `photo_id` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `photo_pick_date` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `icardno` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `issuedate` date DEFAULT NULL,
  `rsnx` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `qrcode` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'QR Code value',
  `sscpassyear` int DEFAULT '0',
  `regdno` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `rollno` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `gpa` varchar(4) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `gla` varchar(3) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sibling1` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sibling2` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sibling3` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sibling4` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sibling5` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `voter_no` int NOT NULL DEFAULT '0',
  `benvill` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `benpo` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `benps` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bendist` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `modifieddate` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1933 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
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
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
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
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
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
-- Table structure for table `subjects`
--

DROP TABLE IF EXISTS `subjects`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `subjects` (
  `id` int NOT NULL AUTO_INCREMENT,
  `sccode` int NOT NULL DEFAULT '0',
  `sccategory` varchar(15) COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT 'School',
  `subcode` int DEFAULT NULL,
  `subject` varchar(70) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `subben` varchar(100) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `subshname` varchar(6) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `ncode` int NOT NULL DEFAULT '0' COMMENT 'Noipunno Code',
  `fourth` int NOT NULL DEFAULT '0',
  `sup_class` varchar(250) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `modifieddate` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=258 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `subjects_after_insert` AFTER INSERT ON `subjects` FOR EACH ROW BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, new_data, changed_by)
        VALUES(
            'subjects',
            'INSERT',
            NEW.`id`,
            JSON_OBJECT('id', NEW.`id`,'sccode', NEW.`sccode`,'sccategory', NEW.`sccategory`,'subcode', NEW.`subcode`,'subject', NEW.`subject`,'subben', NEW.`subben`,'subshname', NEW.`subshname`,'ncode', NEW.`ncode`,'fourth', NEW.`fourth`,'sup_class', NEW.`sup_class`,'modifieddate', NEW.`modifieddate`),
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
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `subjects_after_update` AFTER UPDATE ON `subjects` FOR EACH ROW BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, new_data, changed_by)
        VALUES(
            'subjects',
            'UPDATE',
            NEW.`id`,
            JSON_OBJECT('id', OLD.`id`,'sccode', OLD.`sccode`,'sccategory', OLD.`sccategory`,'subcode', OLD.`subcode`,'subject', OLD.`subject`,'subben', OLD.`subben`,'subshname', OLD.`subshname`,'ncode', OLD.`ncode`,'fourth', OLD.`fourth`,'sup_class', OLD.`sup_class`,'modifieddate', OLD.`modifieddate`),
            JSON_OBJECT('id', NEW.`id`,'sccode', NEW.`sccode`,'sccategory', NEW.`sccategory`,'subcode', NEW.`subcode`,'subject', NEW.`subject`,'subben', NEW.`subben`,'subshname', NEW.`subshname`,'ncode', NEW.`ncode`,'fourth', NEW.`fourth`,'sup_class', NEW.`sup_class`,'modifieddate', NEW.`modifieddate`),
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
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `subjects_after_delete` AFTER DELETE ON `subjects` FOR EACH ROW BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, changed_by)
        VALUES(
            'subjects',
            'DELETE',
            OLD.`id`,
            JSON_OBJECT('id', OLD.`id`,'sccode', OLD.`sccode`,'sccategory', OLD.`sccategory`,'subcode', OLD.`subcode`,'subject', OLD.`subject`,'subben', OLD.`subben`,'subshname', OLD.`subshname`,'ncode', OLD.`ncode`,'fourth', OLD.`fourth`,'sup_class', OLD.`sup_class`,'modifieddate', OLD.`modifieddate`),
            COALESCE(@current_user,'system')
        );
    END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `subscription_history`
--

DROP TABLE IF EXISTS `subscription_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `subscription_history` (
  `id` int NOT NULL AUTO_INCREMENT,
  `sccode` int DEFAULT NULL,
  `package_id` int DEFAULT NULL,
  `package_name` varchar(11) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `tier` varchar(1) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `billing_data` varchar(200) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `subscribe_by` varchar(100) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `subscribe_time` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `subsetup`
--

DROP TABLE IF EXISTS `subsetup`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `subsetup` (
  `id` int NOT NULL AUTO_INCREMENT,
  `slno` int NOT NULL DEFAULT '0',
  `sccode` int DEFAULT NULL,
  `sessionyear` varchar(11) COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '2025',
  `slot` varchar(20) COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT 'School',
  `classname` varchar(20) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `sectionname` varchar(50) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `subject` int DEFAULT NULL,
  `fullmarks` int DEFAULT NULL,
  `ctest` int NOT NULL DEFAULT '0',
  `mtest` int NOT NULL DEFAULT '0',
  `subj` int DEFAULT NULL,
  `obj` int DEFAULT NULL,
  `pra` int DEFAULT NULL,
  `ca` int DEFAULT NULL,
  `camanual` int NOT NULL DEFAULT '0',
  `ctmt` int NOT NULL DEFAULT '0',
  `pass_algorithm` int DEFAULT NULL,
  `cnt` int DEFAULT NULL,
  `reverse` int DEFAULT NULL,
  `tid` bigint DEFAULT NULL,
  `combind_1` int DEFAULT NULL,
  `combind_2` int DEFAULT NULL,
  `combind_3` int DEFAULT NULL,
  `combind_4` int DEFAULT NULL,
  `fourth` int DEFAULT '0',
  `entrycnt` int NOT NULL DEFAULT '0',
  `done1` int NOT NULL DEFAULT '0',
  `doneby1` varchar(100) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `donetime1` datetime DEFAULT NULL,
  `done2` int DEFAULT NULL,
  `doneby2` varchar(100) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `donetime2` datetime DEFAULT NULL,
  `modifieddate` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8866 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
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
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
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
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
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
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `support_tickets` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int DEFAULT NULL,
  `subject` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `message` text COLLATE utf8mb4_unicode_ci,
  `status` enum('open','in_progress','closed') COLLATE utf8mb4_unicode_ci DEFAULT 'open',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `suspicious_activity_types`
--

DROP TABLE IF EXISTS `suspicious_activity_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `suspicious_activity_types` (
  `id` int NOT NULL AUTO_INCREMENT,
  `category` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `title` varchar(150) COLLATE utf8mb4_general_ci NOT NULL,
  `description` text COLLATE utf8mb4_general_ci,
  `risk_score` int DEFAULT '10',
  `recommended_action` enum('log_only','alert','review','block') COLLATE utf8mb4_general_ci DEFAULT 'alert',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=125 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `suspicious_events`
--

DROP TABLE IF EXISTS `suspicious_events`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `suspicious_events` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int DEFAULT NULL,
  `email` varchar(120) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `ip_address` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `user_agent` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `event_type` varchar(150) COLLATE utf8mb4_general_ci NOT NULL,
  `description` text COLLATE utf8mb4_general_ci,
  `risk_score` int DEFAULT '10',
  `recommended_action` enum('log_only','alert','review','block') COLLATE utf8mb4_general_ci DEFAULT 'log_only',
  `matched_rule_id` int DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `event_type` (`event_type`),
  KEY `ip_address` (`ip_address`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tabulatingsheet`
--

DROP TABLE IF EXISTS `tabulatingsheet`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tabulatingsheet` (
  `id` int NOT NULL AUTO_INCREMENT,
  `sessionyear` varchar(10) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `sccode` int NOT NULL,
  `slot` varchar(20) COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT 'School',
  `exam` varchar(50) COLLATE utf8mb3_unicode_ci NOT NULL,
  `classname` varchar(20) COLLATE utf8mb3_unicode_ci NOT NULL,
  `sectionname` varchar(20) COLLATE utf8mb3_unicode_ci NOT NULL,
  `stid` bigint DEFAULT NULL,
  `rollno` int NOT NULL,
  `sub_1` int DEFAULT NULL,
  `sub_2` int DEFAULT NULL,
  `sub_3` int DEFAULT NULL,
  `sub_4` int DEFAULT NULL,
  `sub_5` int DEFAULT NULL,
  `sub_6` int DEFAULT NULL,
  `sub_7` int DEFAULT NULL,
  `sub_8` int DEFAULT NULL,
  `sub_9` int DEFAULT NULL,
  `sub_10` int DEFAULT NULL,
  `sub_11` int DEFAULT NULL,
  `sub_12` int DEFAULT NULL,
  `sub_13` int DEFAULT NULL,
  `sub_14` int DEFAULT NULL,
  `sub_15` int DEFAULT NULL,
  `sub_1_sub` int DEFAULT NULL,
  `sub_1_obj` int DEFAULT NULL,
  `sub_1_pra` int DEFAULT NULL,
  `sub_1_ca` decimal(5,2) DEFAULT NULL,
  `sub_1_total` decimal(5,2) DEFAULT NULL,
  `sub_1_ct` int NOT NULL DEFAULT '0',
  `sub_1_mt` int NOT NULL DEFAULT '0',
  `sub_1_100` float NOT NULL DEFAULT '0',
  `sub_1_gp` decimal(3,2) DEFAULT NULL,
  `sub_1_gl` varchar(3) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `sub_2_sub` int DEFAULT NULL,
  `sub_2_obj` int DEFAULT NULL,
  `sub_2_pra` int DEFAULT NULL,
  `sub_2_ca` decimal(5,2) DEFAULT NULL,
  `sub_2_total` decimal(5,2) DEFAULT NULL,
  `sub_2_ct` int NOT NULL DEFAULT '0',
  `sub_2_mt` int NOT NULL DEFAULT '0',
  `sub_2_100` float NOT NULL DEFAULT '0',
  `sub_2_gp` decimal(3,2) DEFAULT NULL,
  `sub_2_gl` varchar(2) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `sub_3_sub` int DEFAULT NULL,
  `sub_3_obj` int DEFAULT NULL,
  `sub_3_pra` int DEFAULT NULL,
  `sub_3_ca` decimal(5,2) DEFAULT NULL,
  `sub_3_total` decimal(5,2) DEFAULT NULL,
  `sub_3_ct` int NOT NULL DEFAULT '0',
  `sub_3_mt` int NOT NULL DEFAULT '0',
  `sub_3_100` float NOT NULL DEFAULT '0',
  `sub_3_gp` decimal(3,2) DEFAULT NULL,
  `sub_3_gl` varchar(3) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `sub_4_sub` int DEFAULT NULL,
  `sub_4_obj` int DEFAULT NULL,
  `sub_4_pra` int DEFAULT NULL,
  `sub_4_ca` decimal(5,2) DEFAULT NULL,
  `sub_4_total` decimal(5,2) DEFAULT NULL,
  `sub_4_ct` int NOT NULL DEFAULT '0',
  `sub_4_mt` int NOT NULL DEFAULT '0',
  `sub_4_100` float NOT NULL DEFAULT '0',
  `sub_4_gp` decimal(3,2) DEFAULT NULL,
  `sub_4_gl` varchar(3) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `sub_5_sub` int DEFAULT NULL,
  `sub_5_obj` int DEFAULT NULL,
  `sub_5_pra` int DEFAULT NULL,
  `sub_5_ca` decimal(5,2) DEFAULT NULL,
  `sub_5_total` decimal(5,2) DEFAULT NULL,
  `sub_5_ct` int NOT NULL DEFAULT '0',
  `sub_5_mt` int NOT NULL DEFAULT '0',
  `sub_5_100` float NOT NULL DEFAULT '0',
  `sub_5_gp` decimal(3,2) DEFAULT NULL,
  `sub_5_gl` varchar(3) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `sub_6_sub` int DEFAULT NULL,
  `sub_6_obj` int DEFAULT NULL,
  `sub_6_pra` int DEFAULT NULL,
  `sub_6_ca` decimal(5,2) DEFAULT NULL,
  `sub_6_total` decimal(5,2) DEFAULT NULL,
  `sub_6_ct` int NOT NULL DEFAULT '0',
  `sub_6_mt` int NOT NULL DEFAULT '0',
  `sub_6_100` float NOT NULL DEFAULT '0',
  `sub_6_gp` decimal(3,2) DEFAULT NULL,
  `sub_6_gl` varchar(3) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `sub_7_sub` int DEFAULT NULL,
  `sub_7_obj` int DEFAULT NULL,
  `sub_7_pra` int DEFAULT NULL,
  `sub_7_ca` decimal(5,2) DEFAULT NULL,
  `sub_7_total` decimal(5,2) DEFAULT NULL,
  `sub_7_ct` int NOT NULL DEFAULT '0',
  `sub_7_mt` int NOT NULL DEFAULT '0',
  `sub_7_100` float NOT NULL DEFAULT '0',
  `sub_7_gp` decimal(3,2) DEFAULT NULL,
  `sub_7_gl` varchar(3) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `sub_8_sub` int DEFAULT NULL,
  `sub_8_obj` int DEFAULT NULL,
  `sub_8_pra` int DEFAULT NULL,
  `sub_8_ca` decimal(5,2) DEFAULT NULL,
  `sub_8_total` decimal(5,2) DEFAULT NULL,
  `sub_8_ct` int NOT NULL DEFAULT '0',
  `sub_8_mt` int NOT NULL DEFAULT '0',
  `sub_8_100` float NOT NULL DEFAULT '0',
  `sub_8_gp` decimal(3,2) DEFAULT NULL,
  `sub_8_gl` varchar(3) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `sub_9_sub` int DEFAULT NULL,
  `sub_9_obj` int DEFAULT NULL,
  `sub_9_pra` int DEFAULT NULL,
  `sub_9_ca` decimal(5,2) DEFAULT NULL,
  `sub_9_total` decimal(5,2) DEFAULT NULL,
  `sub_9_ct` int NOT NULL DEFAULT '0',
  `sub_9_mt` int NOT NULL DEFAULT '0',
  `sub_9_100` float NOT NULL DEFAULT '0',
  `sub_9_gp` decimal(3,2) DEFAULT NULL,
  `sub_9_gl` varchar(3) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `sub_10_sub` int DEFAULT NULL,
  `sub_10_obj` int DEFAULT NULL,
  `sub_10_pra` int DEFAULT NULL,
  `sub_10_ca` decimal(5,2) DEFAULT NULL,
  `sub_10_total` decimal(5,2) DEFAULT NULL,
  `sub_10_ct` int NOT NULL DEFAULT '0',
  `sub_10_mt` int NOT NULL DEFAULT '0',
  `sub_10_100` float NOT NULL DEFAULT '0',
  `sub_10_gp` decimal(3,2) DEFAULT NULL,
  `sub_10_gl` varchar(3) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `sub_11_sub` int DEFAULT NULL,
  `sub_11_obj` int DEFAULT NULL,
  `sub_11_pra` int DEFAULT NULL,
  `sub_11_ca` decimal(5,2) DEFAULT NULL,
  `sub_11_total` decimal(5,2) DEFAULT NULL,
  `sub_11_ct` int NOT NULL DEFAULT '0',
  `sub_11_mt` int NOT NULL DEFAULT '0',
  `sub_11_100` float NOT NULL DEFAULT '0',
  `sub_11_gp` decimal(3,2) DEFAULT NULL,
  `sub_11_gl` varchar(3) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `sub_12_sub` int DEFAULT NULL,
  `sub_12_obj` int DEFAULT NULL,
  `sub_12_pra` int DEFAULT NULL,
  `sub_12_ca` decimal(5,2) DEFAULT NULL,
  `sub_12_total` decimal(5,2) DEFAULT NULL,
  `sub_12_ct` int NOT NULL DEFAULT '0',
  `sub_12_mt` int NOT NULL DEFAULT '0',
  `sub_12_100` float NOT NULL DEFAULT '0',
  `sub_12_gp` decimal(3,2) DEFAULT NULL,
  `sub_12_gl` varchar(3) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `sub_13_sub` int DEFAULT NULL,
  `sub_13_obj` int DEFAULT NULL,
  `sub_13_pra` int DEFAULT NULL,
  `sub_13_ca` decimal(5,2) DEFAULT NULL,
  `sub_13_total` decimal(5,2) DEFAULT NULL,
  `sub_13_ct` int NOT NULL DEFAULT '0',
  `sub_13_mt` int NOT NULL DEFAULT '0',
  `sub_13_100` float NOT NULL DEFAULT '0',
  `sub_13_gp` decimal(3,2) DEFAULT NULL,
  `sub_13_gl` varchar(3) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `sub_14_sub` int DEFAULT NULL,
  `sub_14_obj` int DEFAULT NULL,
  `sub_14_pra` int DEFAULT NULL,
  `sub_14_ca` decimal(5,2) DEFAULT NULL,
  `sub_14_total` decimal(5,2) DEFAULT NULL,
  `sub_14_ct` int NOT NULL DEFAULT '0',
  `sub_14_mt` int NOT NULL DEFAULT '0',
  `sub_14_100` float NOT NULL DEFAULT '0',
  `sub_14_gp` decimal(3,2) DEFAULT NULL,
  `sub_14_gl` varchar(3) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `sub_15_sub` decimal(5,2) DEFAULT NULL,
  `sub_15_obj` int DEFAULT NULL,
  `sub_15_pra` int DEFAULT NULL,
  `sub_15_ca` decimal(5,2) DEFAULT NULL,
  `sub_15_total` int DEFAULT NULL,
  `sub_15_ct` int NOT NULL DEFAULT '0',
  `sub_15_mt` int NOT NULL DEFAULT '0',
  `sub_15_100` float NOT NULL DEFAULT '0',
  `sub_15_gp` decimal(3,2) DEFAULT NULL,
  `sub_15_gl` varchar(3) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `all_subs_entry` text COLLATE utf8mb3_unicode_ci,
  `ben_sub` int DEFAULT NULL,
  `ben_obj` int DEFAULT NULL,
  `ben_pra` int DEFAULT NULL,
  `ben_ca` int DEFAULT NULL,
  `ben_total` decimal(5,2) DEFAULT NULL,
  `ben_gp` decimal(3,2) DEFAULT NULL,
  `ben_gl` varchar(3) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `eng_sub` int DEFAULT NULL,
  `eng_obj` int DEFAULT NULL,
  `eng_pra` int DEFAULT NULL,
  `eng_ca` int DEFAULT NULL,
  `eng_total` decimal(5,2) DEFAULT NULL,
  `eng_gp` decimal(3,2) DEFAULT NULL,
  `eng_gl` varchar(3) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `totalmarks` decimal(6,2) DEFAULT NULL,
  `full_marks` int DEFAULT NULL,
  `avgrate` decimal(5,2) DEFAULT NULL,
  `gpa` decimal(3,2) DEFAULT NULL,
  `gpaadd` decimal(5,2) NOT NULL DEFAULT '0.00',
  `gla` varchar(3) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `attnd` int DEFAULT NULL,
  `twday` int DEFAULT NULL,
  `totalfail` int DEFAULT '0',
  `meritplace` varchar(10) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `meritplacecomb` varchar(10) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `meritplacegender` varchar(10) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `meritnum` int NOT NULL DEFAULT '0',
  `meritnumcomb` int NOT NULL DEFAULT '0',
  `meritnumgender` int NOT NULL DEFAULT '0',
  `totalgp` decimal(5,2) DEFAULT NULL,
  `totalsubject` int DEFAULT NULL,
  `sublist` varchar(80) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `allsubject` varchar(300) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `gender` varchar(6) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `prevexam` int NOT NULL DEFAULT '0',
  `thisexam` int NOT NULL DEFAULT '0',
  `allfourth` varchar(12) COLLATE utf8mb3_unicode_ci DEFAULT '000',
  `failsub` varchar(300) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `ai_comm` varchar(500) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `modifieddate` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=188843 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `teacher`
--

DROP TABLE IF EXISTS `teacher`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `teacher` (
  `id` int NOT NULL AUTO_INCREMENT,
  `sl` int DEFAULT NULL,
  `tid` varchar(11) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `tname` varchar(40) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `tnameb` varchar(150) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `position` varchar(25) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `slots` varchar(20) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `jdate` date DEFAULT NULL,
  `ranks` int DEFAULT NULL,
  `subjects` varchar(20) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `fname` varchar(40) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `mname` varchar(40) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `spouse` varchar(100) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `emergency` varchar(11) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `preadd` varchar(100) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `previll` varchar(100) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `prepo` varchar(100) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `preps` varchar(100) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `predist` varchar(100) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `pervill` varchar(100) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `perpo` varchar(100) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `perps` varchar(100) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `perdist` varchar(100) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `dob` date DEFAULT NULL,
  `religion` varchar(10) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `gender` varchar(6) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `email` varchar(50) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `mobile` varchar(11) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `nid` varchar(17) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `bgroup` varchar(5) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `status` varchar(3) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `sccode` int NOT NULL,
  `curin` time NOT NULL,
  `curout` time NOT NULL,
  `salery` int NOT NULL,
  `fjdate` date NOT NULL,
  `mpoindex` varchar(15) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `tin` varchar(12) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `accno` varchar(20) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `bankname` varchar(25) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `branch` varchar(50) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `routing` varchar(20) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `accnosch` varchar(20) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `bnamesch` varchar(25) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `bbrsch` varchar(50) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `routesch` varchar(20) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `accnopf` varchar(20) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `bnamepf` varchar(25) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `bbrpf` varchar(50) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `routepf` varchar(20) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `paycode` int DEFAULT NULL,
  `payscale` int DEFAULT '0',
  `basic` int NOT NULL DEFAULT '0',
  `incentive` int NOT NULL DEFAULT '0',
  `house` int NOT NULL DEFAULT '0',
  `medical` int NOT NULL DEFAULT '0',
  `arrea` int NOT NULL DEFAULT '0',
  `welfare` int DEFAULT '0',
  `retire` int NOT NULL DEFAULT '0',
  `netamtgovt` int NOT NULL DEFAULT '0',
  `salary` int DEFAULT '0',
  `mobilevata` int NOT NULL DEFAULT '0',
  `travel` int NOT NULL DEFAULT '0',
  `medical2` int NOT NULL DEFAULT '0',
  `exam` int NOT NULL DEFAULT '0',
  `festival` int NOT NULL DEFAULT '0',
  `pf` int NOT NULL DEFAULT '0',
  `net2` int NOT NULL DEFAULT '0',
  `ex_1` varchar(30) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `val_1` varchar(100) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `ex_2` varchar(30) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `val_2` varchar(100) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `ex_3` varchar(30) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `val_3` varchar(100) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `ex_4` varchar(30) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `val_4` varchar(100) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `rfidtag` varchar(10) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `modifieddate` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1048 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `teacher_after_insert` AFTER INSERT ON `teacher` FOR EACH ROW BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, new_data, changed_by)
        VALUES(
            'teacher',
            'INSERT',
            NEW.`id`,
            JSON_OBJECT('id', NEW.`id`,'sl', NEW.`sl`,'tid', NEW.`tid`,'tname', NEW.`tname`,'tnameb', NEW.`tnameb`,'position', NEW.`position`,'slots', NEW.`slots`,'jdate', NEW.`jdate`,'ranks', NEW.`ranks`,'subjects', NEW.`subjects`,'fname', NEW.`fname`,'mname', NEW.`mname`,'spouse', NEW.`spouse`,'emergency', NEW.`emergency`,'preadd', NEW.`preadd`,'previll', NEW.`previll`,'prepo', NEW.`prepo`,'preps', NEW.`preps`,'predist', NEW.`predist`,'pervill', NEW.`pervill`,'perpo', NEW.`perpo`,'perps', NEW.`perps`,'perdist', NEW.`perdist`,'dob', NEW.`dob`,'religion', NEW.`religion`,'gender', NEW.`gender`,'email', NEW.`email`,'mobile', NEW.`mobile`,'nid', NEW.`nid`,'bgroup', NEW.`bgroup`,'status', NEW.`status`,'sccode', NEW.`sccode`,'curin', NEW.`curin`,'curout', NEW.`curout`,'salery', NEW.`salery`,'fjdate', NEW.`fjdate`,'mpoindex', NEW.`mpoindex`,'tin', NEW.`tin`,'accno', NEW.`accno`,'bankname', NEW.`bankname`,'branch', NEW.`branch`,'routing', NEW.`routing`,'accnosch', NEW.`accnosch`,'bnamesch', NEW.`bnamesch`,'bbrsch', NEW.`bbrsch`,'routesch', NEW.`routesch`,'accnopf', NEW.`accnopf`,'bnamepf', NEW.`bnamepf`,'bbrpf', NEW.`bbrpf`,'routepf', NEW.`routepf`,'paycode', NEW.`paycode`,'payscale', NEW.`payscale`,'basic', NEW.`basic`,'incentive', NEW.`incentive`,'house', NEW.`house`,'medical', NEW.`medical`,'arrea', NEW.`arrea`,'welfare', NEW.`welfare`,'retire', NEW.`retire`,'netamtgovt', NEW.`netamtgovt`,'salary', NEW.`salary`,'mobilevata', NEW.`mobilevata`,'travel', NEW.`travel`,'medical2', NEW.`medical2`,'exam', NEW.`exam`,'festival', NEW.`festival`,'pf', NEW.`pf`,'net2', NEW.`net2`,'ex_1', NEW.`ex_1`,'val_1', NEW.`val_1`,'ex_2', NEW.`ex_2`,'val_2', NEW.`val_2`,'ex_3', NEW.`ex_3`,'val_3', NEW.`val_3`,'ex_4', NEW.`ex_4`,'val_4', NEW.`val_4`,'rfidtag', NEW.`rfidtag`,'modifieddate', NEW.`modifieddate`),
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
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `teacher_after_update` AFTER UPDATE ON `teacher` FOR EACH ROW BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, new_data, changed_by)
        VALUES(
            'teacher',
            'UPDATE',
            NEW.`id`,
            JSON_OBJECT('id', OLD.`id`,'sl', OLD.`sl`,'tid', OLD.`tid`,'tname', OLD.`tname`,'tnameb', OLD.`tnameb`,'position', OLD.`position`,'slots', OLD.`slots`,'jdate', OLD.`jdate`,'ranks', OLD.`ranks`,'subjects', OLD.`subjects`,'fname', OLD.`fname`,'mname', OLD.`mname`,'spouse', OLD.`spouse`,'emergency', OLD.`emergency`,'preadd', OLD.`preadd`,'previll', OLD.`previll`,'prepo', OLD.`prepo`,'preps', OLD.`preps`,'predist', OLD.`predist`,'pervill', OLD.`pervill`,'perpo', OLD.`perpo`,'perps', OLD.`perps`,'perdist', OLD.`perdist`,'dob', OLD.`dob`,'religion', OLD.`religion`,'gender', OLD.`gender`,'email', OLD.`email`,'mobile', OLD.`mobile`,'nid', OLD.`nid`,'bgroup', OLD.`bgroup`,'status', OLD.`status`,'sccode', OLD.`sccode`,'curin', OLD.`curin`,'curout', OLD.`curout`,'salery', OLD.`salery`,'fjdate', OLD.`fjdate`,'mpoindex', OLD.`mpoindex`,'tin', OLD.`tin`,'accno', OLD.`accno`,'bankname', OLD.`bankname`,'branch', OLD.`branch`,'routing', OLD.`routing`,'accnosch', OLD.`accnosch`,'bnamesch', OLD.`bnamesch`,'bbrsch', OLD.`bbrsch`,'routesch', OLD.`routesch`,'accnopf', OLD.`accnopf`,'bnamepf', OLD.`bnamepf`,'bbrpf', OLD.`bbrpf`,'routepf', OLD.`routepf`,'paycode', OLD.`paycode`,'payscale', OLD.`payscale`,'basic', OLD.`basic`,'incentive', OLD.`incentive`,'house', OLD.`house`,'medical', OLD.`medical`,'arrea', OLD.`arrea`,'welfare', OLD.`welfare`,'retire', OLD.`retire`,'netamtgovt', OLD.`netamtgovt`,'salary', OLD.`salary`,'mobilevata', OLD.`mobilevata`,'travel', OLD.`travel`,'medical2', OLD.`medical2`,'exam', OLD.`exam`,'festival', OLD.`festival`,'pf', OLD.`pf`,'net2', OLD.`net2`,'ex_1', OLD.`ex_1`,'val_1', OLD.`val_1`,'ex_2', OLD.`ex_2`,'val_2', OLD.`val_2`,'ex_3', OLD.`ex_3`,'val_3', OLD.`val_3`,'ex_4', OLD.`ex_4`,'val_4', OLD.`val_4`,'rfidtag', OLD.`rfidtag`,'modifieddate', OLD.`modifieddate`),
            JSON_OBJECT('id', NEW.`id`,'sl', NEW.`sl`,'tid', NEW.`tid`,'tname', NEW.`tname`,'tnameb', NEW.`tnameb`,'position', NEW.`position`,'slots', NEW.`slots`,'jdate', NEW.`jdate`,'ranks', NEW.`ranks`,'subjects', NEW.`subjects`,'fname', NEW.`fname`,'mname', NEW.`mname`,'spouse', NEW.`spouse`,'emergency', NEW.`emergency`,'preadd', NEW.`preadd`,'previll', NEW.`previll`,'prepo', NEW.`prepo`,'preps', NEW.`preps`,'predist', NEW.`predist`,'pervill', NEW.`pervill`,'perpo', NEW.`perpo`,'perps', NEW.`perps`,'perdist', NEW.`perdist`,'dob', NEW.`dob`,'religion', NEW.`religion`,'gender', NEW.`gender`,'email', NEW.`email`,'mobile', NEW.`mobile`,'nid', NEW.`nid`,'bgroup', NEW.`bgroup`,'status', NEW.`status`,'sccode', NEW.`sccode`,'curin', NEW.`curin`,'curout', NEW.`curout`,'salery', NEW.`salery`,'fjdate', NEW.`fjdate`,'mpoindex', NEW.`mpoindex`,'tin', NEW.`tin`,'accno', NEW.`accno`,'bankname', NEW.`bankname`,'branch', NEW.`branch`,'routing', NEW.`routing`,'accnosch', NEW.`accnosch`,'bnamesch', NEW.`bnamesch`,'bbrsch', NEW.`bbrsch`,'routesch', NEW.`routesch`,'accnopf', NEW.`accnopf`,'bnamepf', NEW.`bnamepf`,'bbrpf', NEW.`bbrpf`,'routepf', NEW.`routepf`,'paycode', NEW.`paycode`,'payscale', NEW.`payscale`,'basic', NEW.`basic`,'incentive', NEW.`incentive`,'house', NEW.`house`,'medical', NEW.`medical`,'arrea', NEW.`arrea`,'welfare', NEW.`welfare`,'retire', NEW.`retire`,'netamtgovt', NEW.`netamtgovt`,'salary', NEW.`salary`,'mobilevata', NEW.`mobilevata`,'travel', NEW.`travel`,'medical2', NEW.`medical2`,'exam', NEW.`exam`,'festival', NEW.`festival`,'pf', NEW.`pf`,'net2', NEW.`net2`,'ex_1', NEW.`ex_1`,'val_1', NEW.`val_1`,'ex_2', NEW.`ex_2`,'val_2', NEW.`val_2`,'ex_3', NEW.`ex_3`,'val_3', NEW.`val_3`,'ex_4', NEW.`ex_4`,'val_4', NEW.`val_4`,'rfidtag', NEW.`rfidtag`,'modifieddate', NEW.`modifieddate`),
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
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `teacher_after_delete` AFTER DELETE ON `teacher` FOR EACH ROW BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, changed_by)
        VALUES(
            'teacher',
            'DELETE',
            OLD.`id`,
            JSON_OBJECT('id', OLD.`id`,'sl', OLD.`sl`,'tid', OLD.`tid`,'tname', OLD.`tname`,'tnameb', OLD.`tnameb`,'position', OLD.`position`,'slots', OLD.`slots`,'jdate', OLD.`jdate`,'ranks', OLD.`ranks`,'subjects', OLD.`subjects`,'fname', OLD.`fname`,'mname', OLD.`mname`,'spouse', OLD.`spouse`,'emergency', OLD.`emergency`,'preadd', OLD.`preadd`,'previll', OLD.`previll`,'prepo', OLD.`prepo`,'preps', OLD.`preps`,'predist', OLD.`predist`,'pervill', OLD.`pervill`,'perpo', OLD.`perpo`,'perps', OLD.`perps`,'perdist', OLD.`perdist`,'dob', OLD.`dob`,'religion', OLD.`religion`,'gender', OLD.`gender`,'email', OLD.`email`,'mobile', OLD.`mobile`,'nid', OLD.`nid`,'bgroup', OLD.`bgroup`,'status', OLD.`status`,'sccode', OLD.`sccode`,'curin', OLD.`curin`,'curout', OLD.`curout`,'salery', OLD.`salery`,'fjdate', OLD.`fjdate`,'mpoindex', OLD.`mpoindex`,'tin', OLD.`tin`,'accno', OLD.`accno`,'bankname', OLD.`bankname`,'branch', OLD.`branch`,'routing', OLD.`routing`,'accnosch', OLD.`accnosch`,'bnamesch', OLD.`bnamesch`,'bbrsch', OLD.`bbrsch`,'routesch', OLD.`routesch`,'accnopf', OLD.`accnopf`,'bnamepf', OLD.`bnamepf`,'bbrpf', OLD.`bbrpf`,'routepf', OLD.`routepf`,'paycode', OLD.`paycode`,'payscale', OLD.`payscale`,'basic', OLD.`basic`,'incentive', OLD.`incentive`,'house', OLD.`house`,'medical', OLD.`medical`,'arrea', OLD.`arrea`,'welfare', OLD.`welfare`,'retire', OLD.`retire`,'netamtgovt', OLD.`netamtgovt`,'salary', OLD.`salary`,'mobilevata', OLD.`mobilevata`,'travel', OLD.`travel`,'medical2', OLD.`medical2`,'exam', OLD.`exam`,'festival', OLD.`festival`,'pf', OLD.`pf`,'net2', OLD.`net2`,'ex_1', OLD.`ex_1`,'val_1', OLD.`val_1`,'ex_2', OLD.`ex_2`,'val_2', OLD.`val_2`,'ex_3', OLD.`ex_3`,'val_3', OLD.`val_3`,'ex_4', OLD.`ex_4`,'val_4', OLD.`val_4`,'rfidtag', OLD.`rfidtag`,'modifieddate', OLD.`modifieddate`),
            COALESCE(@current_user,'system')
        );
    END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `ticket_messages`
--

DROP TABLE IF EXISTS `ticket_messages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ticket_messages` (
  `id` int NOT NULL AUTO_INCREMENT,
  `ticket_id` int NOT NULL,
  `sender_id` int NOT NULL,
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `admin_response` int NOT NULL DEFAULT '0',
  `sent_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=42 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tickets`
--

DROP TABLE IF EXISTS `tickets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tickets` (
  `id` int NOT NULL AUTO_INCREMENT,
  `sccode` int DEFAULT NULL,
  `user_id` int NOT NULL,
  `subject` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('open','in_progress','closed') COLLATE utf8mb4_unicode_ci DEFAULT 'open',
  `ticket_for` enum('Institute','Application','Personal','') COLLATE utf8mb4_unicode_ci DEFAULT 'Institute',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `title_requirements`
--

DROP TABLE IF EXISTS `title_requirements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `title_requirements` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `title_id` int unsigned NOT NULL,
  `achievement_code` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
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
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `titles_list` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title_name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `min_points` int DEFAULT '0',
  `required_achievements` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `badge_color` varchar(20) COLLATE utf8mb4_general_ci DEFAULT 'secondary',
  `description` text COLLATE utf8mb4_general_ci,
  PRIMARY KEY (`id`),
  CONSTRAINT `titles_list_chk_1` CHECK (json_valid(`required_achievements`))
) ENGINE=InnoDB AUTO_INCREMENT=101 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `user_achievements`
--

DROP TABLE IF EXISTS `user_achievements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_achievements` (
  `id` int NOT NULL AUTO_INCREMENT,
  `sccode` int DEFAULT NULL,
  `email` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `user_id` int NOT NULL,
  `achievement_id` int NOT NULL,
  `achieved_at` datetime DEFAULT CURRENT_TIMESTAMP,
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
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_actions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `sccode` int DEFAULT NULL,
  `email` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `page` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `action` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `points` int DEFAULT '0',
  `timestamp` datetime DEFAULT NULL,
  `ip` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `browser` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_useractions_email_ts` (`email`,`timestamp`),
  KEY `idx_useractions_action` (`action`)
) ENGINE=InnoDB AUTO_INCREMENT=30912 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `user_points`
--

DROP TABLE IF EXISTS `user_points`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_points` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `name` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `total_points` int DEFAULT '0',
  `current_title_id` int DEFAULT NULL,
  `level` int DEFAULT '1',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `user_shortcuts`
--

DROP TABLE IF EXISTS `user_shortcuts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_shortcuts` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `user_email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sccode` int DEFAULT NULL COMMENT 'Unique shortcut code / identifier',
  `page_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Page URL or internal identifier',
  `page_title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Title of the page',
  `page_icon` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'close',
  `module` varchar(25) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Optional description of the page',
  `status` enum('active','inactive') COLLATE utf8mb4_unicode_ci DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `user_titles`
--

DROP TABLE IF EXISTS `user_titles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_titles` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `email` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `title_id` int unsigned NOT NULL,
  `awarded_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `is_active` tinyint(1) DEFAULT '1',
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
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_tokens` (
  `user_id` int NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `sccode` int DEFAULT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT 'User',
  `avatar` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('online','away','busy','offline') COLLATE utf8mb4_unicode_ci DEFAULT 'online',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `users_login_tracks`
--

DROP TABLE IF EXISTS `users_login_tracks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users_login_tracks` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `user_id` bigint NOT NULL,
  `event_type` enum('login_attempt','login_success','login_failure','logout','otp_sent','otp_verified') COLLATE utf8mb4_unicode_ci NOT NULL,
  `ip` varchar(45) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `device_fp` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `geo_country` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `geo_region` varchar(128) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `geo_city` varchar(128) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `lat` double DEFAULT NULL,
  `lon` double DEFAULT NULL,
  `asn` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ip_risk_score` int DEFAULT '0',
  `reason` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
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
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `usersapp` (
  `id` int NOT NULL AUTO_INCREMENT,
  `sccode` int DEFAULT NULL,
  `email` varchar(100) COLLATE utf8mb3_unicode_ci NOT NULL,
  `username` varchar(100) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `hash_salt_key` varchar(255) COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT 'cklpns',
  `password_hash` varchar(255) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `failed_attempts` int DEFAULT '0',
  `last_failed` timestamp NULL DEFAULT NULL,
  `lock_until` timestamp NULL DEFAULT NULL,
  `remember_token_hash` varchar(255) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `remember_token_expires` timestamp NULL DEFAULT NULL,
  `password_salt` varchar(32) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `reset_token_hash` varchar(255) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `reset_token_expires` timestamp NULL DEFAULT NULL,
  `mfa_enabled` tinyint NOT NULL DEFAULT '0',
  `mfa_type` enum('totp','sms','email','push') COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `mfa_secret` varchar(255) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `mfa_temp_token` varchar(255) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `mfa_temp_expires` timestamp NULL DEFAULT NULL,
  `two_factor` int NOT NULL DEFAULT '0',
  `secretkey` varchar(150) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `token` varchar(255) COLLATE utf8mb3_unicode_ci NOT NULL,
  `profilename` varchar(60) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `mobile` varchar(11) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `userlevel` varchar(25) COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT 'Guest',
  `is_chief` int NOT NULL DEFAULT '0',
  `hiddenuser` int NOT NULL DEFAULT '0',
  `userid` bigint DEFAULT NULL,
  `photourl` varchar(255) COLLATE utf8mb3_unicode_ci NOT NULL,
  `firstlogin` datetime DEFAULT NULL,
  `lastlogin` datetime DEFAULT NULL,
  `lastaccess` datetime DEFAULT NULL,
  `posx` float DEFAULT NULL,
  `posy` float DEFAULT NULL,
  `status` int NOT NULL DEFAULT '1',
  `otp` varchar(10) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `otptime` datetime DEFAULT NULL,
  `fixedpin` varchar(64) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `curexam` varchar(25) COLLATE utf8mb3_unicode_ci DEFAULT 'Half-Yearly',
  `session` int NOT NULL DEFAULT '2024',
  `userdata1` varchar(100) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `userdata2` varchar(100) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `area` varchar(30) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `ps` varchar(30) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `dist` varchar(30) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `admin` int NOT NULL DEFAULT '0' COMMENT '0- No Admin, 1- 2-, 3-Admin, 4-, 5\r\n-Super Admin',
  `login_gmail` int NOT NULL DEFAULT '1',
  `login_pass` int NOT NULL DEFAULT '1',
  `login_token` int NOT NULL DEFAULT '1',
  `login_qrcode` int NOT NULL DEFAULT '1',
  `setup_done` int NOT NULL DEFAULT '0',
  `whatsnew_last_id` int NOT NULL DEFAULT '0',
  `reg_status` varchar(15) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `reg_value` varchar(15) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `active` int NOT NULL DEFAULT '0',
  `theme` varchar(20) COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT 'dark',
  `customcss` int NOT NULL DEFAULT '0',
  `reset_otp` varchar(12) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `reset_hash` varchar(40) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `reset_link` varchar(160) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `st_entry_fld` varchar(1020) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `page_status_grant` int NOT NULL DEFAULT '6',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=978 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
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
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
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
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
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
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `usersapp_new` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fullname` varchar(63) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `username` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password_hash` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_level` varchar(25) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `failed_attempts` int DEFAULT '0',
  `last_failed` timestamp NULL DEFAULT NULL,
  `lock_until` timestamp NULL DEFAULT NULL,
  `remember_token_hash` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `remember_token_expires` timestamp NULL DEFAULT NULL,
  `password_salt` varchar(32) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reset_token_hash` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reset_token_expires` timestamp NULL DEFAULT NULL,
  `mfa_enabled` tinyint(1) DEFAULT '0',
  `mfa_type` enum('totp','sms','email','push') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mfa_secret` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mfa_temp_token` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mfa_temp_expires` timestamp NULL DEFAULT NULL,
  `last_name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` text COLLATE utf8mb4_unicode_ci,
  `dob` date DEFAULT NULL,
  `first_name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
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
SET @@SESSION.SQL_LOG_BIN = @MYSQLDUMP_TEMP_LOG_BIN;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-02-01  0:03:07
