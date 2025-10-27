-- MariaDB dump 10.19  Distrib 10.4.28-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: eimbox
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
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER accesslevel_after_insert
    AFTER INSERT ON `accesslevel`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, new_data, changed_by)
        VALUES(
            'accesslevel',
            'INSERT',
            NEW.`id`,
            JSON_OBJECT('id', NEW.`id`,'permission', NEW.`permission`,'platinum', NEW.`platinum`,'gold', NEW.`gold`,'silver', NEW.`silver`,'bronge', NEW.`bronge`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER accesslevel_after_update
    AFTER UPDATE ON `accesslevel`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, new_data, changed_by)
        VALUES(
            'accesslevel',
            'UPDATE',
            NEW.`id`,
            JSON_OBJECT('id', OLD.`id`,'permission', OLD.`permission`,'platinum', OLD.`platinum`,'gold', OLD.`gold`,'silver', OLD.`silver`,'bronge', OLD.`bronge`),
            JSON_OBJECT('id', NEW.`id`,'permission', NEW.`permission`,'platinum', NEW.`platinum`,'gold', NEW.`gold`,'silver', NEW.`silver`,'bronge', NEW.`bronge`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER accesslevel_after_delete
    AFTER DELETE ON `accesslevel`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, changed_by)
        VALUES(
            'accesslevel',
            'DELETE',
            OLD.`id`,
            JSON_OBJECT('id', OLD.`id`,'permission', OLD.`permission`,'platinum', OLD.`platinum`,'gold', OLD.`gold`,'silver', OLD.`silver`,'bronge', OLD.`bronge`),
            COALESCE(@current_user,'system')
        );
    END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

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
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER accounts_after_insert
    AFTER INSERT ON `accounts`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, new_data, changed_by)
        VALUES(
            'accounts',
            'INSERT',
            NEW.`id`,
            JSON_OBJECT('id', NEW.`id`,'sccode', NEW.`sccode`,'company', NEW.`company`,'descn', NEW.`descn`,'amount', NEW.`amount`,'date', NEW.`date`,'sms', NEW.`sms`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER accounts_after_update
    AFTER UPDATE ON `accounts`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, new_data, changed_by)
        VALUES(
            'accounts',
            'UPDATE',
            NEW.`id`,
            JSON_OBJECT('id', OLD.`id`,'sccode', OLD.`sccode`,'company', OLD.`company`,'descn', OLD.`descn`,'amount', OLD.`amount`,'date', OLD.`date`,'sms', OLD.`sms`),
            JSON_OBJECT('id', NEW.`id`,'sccode', NEW.`sccode`,'company', NEW.`company`,'descn', NEW.`descn`,'amount', NEW.`amount`,'date', NEW.`date`,'sms', NEW.`sms`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER accounts_after_delete
    AFTER DELETE ON `accounts`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, changed_by)
        VALUES(
            'accounts',
            'DELETE',
            OLD.`id`,
            JSON_OBJECT('id', OLD.`id`,'sccode', OLD.`sccode`,'company', OLD.`company`,'descn', OLD.`descn`,'amount', OLD.`amount`,'date', OLD.`date`,'sms', OLD.`sms`),
            COALESCE(@current_user,'system')
        );
    END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

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
-- Table structure for table `adminnotes`
--

DROP TABLE IF EXISTS `adminnotes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `adminnotes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sccode` int(11) NOT NULL,
  `notes` varchar(500) NOT NULL,
  `count` int(11) NOT NULL DEFAULT 0,
  `entryby` varchar(100) NOT NULL,
  `entrytime` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
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
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER adminnotes_after_insert
    AFTER INSERT ON `adminnotes`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, new_data, changed_by)
        VALUES(
            'adminnotes',
            'INSERT',
            NEW.`id`,
            JSON_OBJECT('id', NEW.`id`,'sccode', NEW.`sccode`,'notes', NEW.`notes`,'count', NEW.`count`,'entryby', NEW.`entryby`,'entrytime', NEW.`entrytime`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER adminnotes_after_update
    AFTER UPDATE ON `adminnotes`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, new_data, changed_by)
        VALUES(
            'adminnotes',
            'UPDATE',
            NEW.`id`,
            JSON_OBJECT('id', OLD.`id`,'sccode', OLD.`sccode`,'notes', OLD.`notes`,'count', OLD.`count`,'entryby', OLD.`entryby`,'entrytime', OLD.`entrytime`),
            JSON_OBJECT('id', NEW.`id`,'sccode', NEW.`sccode`,'notes', NEW.`notes`,'count', NEW.`count`,'entryby', NEW.`entryby`,'entrytime', NEW.`entrytime`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER adminnotes_after_delete
    AFTER DELETE ON `adminnotes`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, changed_by)
        VALUES(
            'adminnotes',
            'DELETE',
            OLD.`id`,
            JSON_OBJECT('id', OLD.`id`,'sccode', OLD.`sccode`,'notes', OLD.`notes`,'count', OLD.`count`,'entryby', OLD.`entryby`,'entrytime', OLD.`entrytime`),
            COALESCE(@current_user,'system')
        );
    END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `admission`
--

DROP TABLE IF EXISTS `admission`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `admission` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sccode` int(11) DEFAULT NULL,
  `stid` varchar(11) DEFAULT NULL,
  `stnameeng` varchar(40) DEFAULT NULL,
  `stnameben` varchar(100) DEFAULT NULL,
  `fname` varchar(40) DEFAULT NULL,
  `fprof` varchar(30) DEFAULT NULL,
  `fmobile` varchar(11) DEFAULT NULL,
  `mname` varchar(40) DEFAULT NULL,
  `mprof` varchar(30) DEFAULT NULL,
  `mmobile` varchar(11) DEFAULT NULL,
  `previll` varchar(30) DEFAULT NULL,
  `prepo` varchar(30) DEFAULT NULL,
  `preps` varchar(20) DEFAULT NULL,
  `predist` varchar(30) DEFAULT NULL,
  `pervill` varchar(30) DEFAULT NULL,
  `perpo` varchar(30) DEFAULT NULL,
  `perps` varchar(20) DEFAULT NULL,
  `perdist` varchar(10) DEFAULT NULL,
  `dob` date DEFAULT NULL,
  `religion` varchar(10) DEFAULT NULL,
  `brn` varchar(17) DEFAULT NULL,
  `gender` varchar(4) DEFAULT NULL,
  `guarname` varchar(40) DEFAULT NULL,
  `guaradd` varchar(60) DEFAULT NULL,
  `guarrelation` varchar(20) DEFAULT NULL,
  `guarmobile` varchar(11) DEFAULT NULL,
  `tcno` varchar(10) DEFAULT NULL,
  `preins` varchar(20) DEFAULT NULL,
  `preinsadd` varchar(255) DEFAULT NULL,
  `doa` date DEFAULT NULL,
  `modify` datetime DEFAULT NULL,
  `admdate` date DEFAULT NULL,
  `openingfee` int(11) NOT NULL DEFAULT 0,
  `admclass` varchar(10) DEFAULT NULL,
  `admby` varchar(100) DEFAULT NULL,
  `admtime` datetime DEFAULT NULL,
  `modifieddate` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
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
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER admission_after_insert
    AFTER INSERT ON `admission`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, new_data, changed_by)
        VALUES(
            'admission',
            'INSERT',
            NEW.`id`,
            JSON_OBJECT('id', NEW.`id`,'sccode', NEW.`sccode`,'stid', NEW.`stid`,'stnameeng', NEW.`stnameeng`,'stnameben', NEW.`stnameben`,'fname', NEW.`fname`,'fprof', NEW.`fprof`,'fmobile', NEW.`fmobile`,'mname', NEW.`mname`,'mprof', NEW.`mprof`,'mmobile', NEW.`mmobile`,'previll', NEW.`previll`,'prepo', NEW.`prepo`,'preps', NEW.`preps`,'predist', NEW.`predist`,'pervill', NEW.`pervill`,'perpo', NEW.`perpo`,'perps', NEW.`perps`,'perdist', NEW.`perdist`,'dob', NEW.`dob`,'religion', NEW.`religion`,'brn', NEW.`brn`,'gender', NEW.`gender`,'guarname', NEW.`guarname`,'guaradd', NEW.`guaradd`,'guarrelation', NEW.`guarrelation`,'guarmobile', NEW.`guarmobile`,'tcno', NEW.`tcno`,'preins', NEW.`preins`,'preinsadd', NEW.`preinsadd`,'doa', NEW.`doa`,'modify', NEW.`modify`,'admdate', NEW.`admdate`,'openingfee', NEW.`openingfee`,'admclass', NEW.`admclass`,'admby', NEW.`admby`,'admtime', NEW.`admtime`,'modifieddate', NEW.`modifieddate`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER admission_after_update
    AFTER UPDATE ON `admission`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, new_data, changed_by)
        VALUES(
            'admission',
            'UPDATE',
            NEW.`id`,
            JSON_OBJECT('id', OLD.`id`,'sccode', OLD.`sccode`,'stid', OLD.`stid`,'stnameeng', OLD.`stnameeng`,'stnameben', OLD.`stnameben`,'fname', OLD.`fname`,'fprof', OLD.`fprof`,'fmobile', OLD.`fmobile`,'mname', OLD.`mname`,'mprof', OLD.`mprof`,'mmobile', OLD.`mmobile`,'previll', OLD.`previll`,'prepo', OLD.`prepo`,'preps', OLD.`preps`,'predist', OLD.`predist`,'pervill', OLD.`pervill`,'perpo', OLD.`perpo`,'perps', OLD.`perps`,'perdist', OLD.`perdist`,'dob', OLD.`dob`,'religion', OLD.`religion`,'brn', OLD.`brn`,'gender', OLD.`gender`,'guarname', OLD.`guarname`,'guaradd', OLD.`guaradd`,'guarrelation', OLD.`guarrelation`,'guarmobile', OLD.`guarmobile`,'tcno', OLD.`tcno`,'preins', OLD.`preins`,'preinsadd', OLD.`preinsadd`,'doa', OLD.`doa`,'modify', OLD.`modify`,'admdate', OLD.`admdate`,'openingfee', OLD.`openingfee`,'admclass', OLD.`admclass`,'admby', OLD.`admby`,'admtime', OLD.`admtime`,'modifieddate', OLD.`modifieddate`),
            JSON_OBJECT('id', NEW.`id`,'sccode', NEW.`sccode`,'stid', NEW.`stid`,'stnameeng', NEW.`stnameeng`,'stnameben', NEW.`stnameben`,'fname', NEW.`fname`,'fprof', NEW.`fprof`,'fmobile', NEW.`fmobile`,'mname', NEW.`mname`,'mprof', NEW.`mprof`,'mmobile', NEW.`mmobile`,'previll', NEW.`previll`,'prepo', NEW.`prepo`,'preps', NEW.`preps`,'predist', NEW.`predist`,'pervill', NEW.`pervill`,'perpo', NEW.`perpo`,'perps', NEW.`perps`,'perdist', NEW.`perdist`,'dob', NEW.`dob`,'religion', NEW.`religion`,'brn', NEW.`brn`,'gender', NEW.`gender`,'guarname', NEW.`guarname`,'guaradd', NEW.`guaradd`,'guarrelation', NEW.`guarrelation`,'guarmobile', NEW.`guarmobile`,'tcno', NEW.`tcno`,'preins', NEW.`preins`,'preinsadd', NEW.`preinsadd`,'doa', NEW.`doa`,'modify', NEW.`modify`,'admdate', NEW.`admdate`,'openingfee', NEW.`openingfee`,'admclass', NEW.`admclass`,'admby', NEW.`admby`,'admtime', NEW.`admtime`,'modifieddate', NEW.`modifieddate`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER admission_after_delete
    AFTER DELETE ON `admission`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, changed_by)
        VALUES(
            'admission',
            'DELETE',
            OLD.`id`,
            JSON_OBJECT('id', OLD.`id`,'sccode', OLD.`sccode`,'stid', OLD.`stid`,'stnameeng', OLD.`stnameeng`,'stnameben', OLD.`stnameben`,'fname', OLD.`fname`,'fprof', OLD.`fprof`,'fmobile', OLD.`fmobile`,'mname', OLD.`mname`,'mprof', OLD.`mprof`,'mmobile', OLD.`mmobile`,'previll', OLD.`previll`,'prepo', OLD.`prepo`,'preps', OLD.`preps`,'predist', OLD.`predist`,'pervill', OLD.`pervill`,'perpo', OLD.`perpo`,'perps', OLD.`perps`,'perdist', OLD.`perdist`,'dob', OLD.`dob`,'religion', OLD.`religion`,'brn', OLD.`brn`,'gender', OLD.`gender`,'guarname', OLD.`guarname`,'guaradd', OLD.`guaradd`,'guarrelation', OLD.`guarrelation`,'guarmobile', OLD.`guarmobile`,'tcno', OLD.`tcno`,'preins', OLD.`preins`,'preinsadd', OLD.`preinsadd`,'doa', OLD.`doa`,'modify', OLD.`modify`,'admdate', OLD.`admdate`,'openingfee', OLD.`openingfee`,'admclass', OLD.`admclass`,'admby', OLD.`admby`,'admtime', OLD.`admtime`,'modifieddate', OLD.`modifieddate`),
            COALESCE(@current_user,'system')
        );
    END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `amountrequest`
--

DROP TABLE IF EXISTS `amountrequest`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `amountrequest` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user` varchar(30) NOT NULL,
  `bkashno` text NOT NULL,
  `amount` int(11) NOT NULL,
  `date` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` varchar(20) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
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
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER amountrequest_after_insert
    AFTER INSERT ON `amountrequest`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, new_data, changed_by)
        VALUES(
            'amountrequest',
            'INSERT',
            NEW.`id`,
            JSON_OBJECT('id', NEW.`id`,'user', NEW.`user`,'bkashno', NEW.`bkashno`,'amount', NEW.`amount`,'date', NEW.`date`,'status', NEW.`status`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER amountrequest_after_update
    AFTER UPDATE ON `amountrequest`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, new_data, changed_by)
        VALUES(
            'amountrequest',
            'UPDATE',
            NEW.`id`,
            JSON_OBJECT('id', OLD.`id`,'user', OLD.`user`,'bkashno', OLD.`bkashno`,'amount', OLD.`amount`,'date', OLD.`date`,'status', OLD.`status`),
            JSON_OBJECT('id', NEW.`id`,'user', NEW.`user`,'bkashno', NEW.`bkashno`,'amount', NEW.`amount`,'date', NEW.`date`,'status', NEW.`status`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER amountrequest_after_delete
    AFTER DELETE ON `amountrequest`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, changed_by)
        VALUES(
            'amountrequest',
            'DELETE',
            OLD.`id`,
            JSON_OBJECT('id', OLD.`id`,'user', OLD.`user`,'bkashno', OLD.`bkashno`,'amount', OLD.`amount`,'date', OLD.`date`,'status', OLD.`status`),
            COALESCE(@current_user,'system')
        );
    END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

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
) ENGINE=InnoDB AUTO_INCREMENT=1508 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
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
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER areas_after_insert
    AFTER INSERT ON `areas`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, new_data, changed_by)
        VALUES(
            'areas',
            'INSERT',
            NEW.`id`,
            JSON_OBJECT('id', NEW.`id`,'idno', NEW.`idno`,'user', NEW.`user`,'slot', NEW.`slot`,'medium', NEW.`medium`,'version', NEW.`version`,'areaname', NEW.`areaname`,'subarea', NEW.`subarea`,'sessionyear', NEW.`sessionyear`,'yesno', NEW.`yesno`,'classteacher', NEW.`classteacher`,'rollfrom', NEW.`rollfrom`,'rollto', NEW.`rollto`,'combind_1', NEW.`combind_1`,'combind_2', NEW.`combind_2`,'combind_3', NEW.`combind_3`,'combind_4', NEW.`combind_4`,'fourth', NEW.`fourth`,'allsubject', NEW.`allsubject`,'half', NEW.`half`,'full', NEW.`full`,'halfdone', NEW.`halfdone`,'fulldone', NEW.`fulldone`,'entrytime', NEW.`entrytime`,'sccode', NEW.`sccode`,'modifieddate', NEW.`modifieddate`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER areas_after_update
    AFTER UPDATE ON `areas`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, new_data, changed_by)
        VALUES(
            'areas',
            'UPDATE',
            NEW.`id`,
            JSON_OBJECT('id', OLD.`id`,'idno', OLD.`idno`,'user', OLD.`user`,'slot', OLD.`slot`,'medium', OLD.`medium`,'version', OLD.`version`,'areaname', OLD.`areaname`,'subarea', OLD.`subarea`,'sessionyear', OLD.`sessionyear`,'yesno', OLD.`yesno`,'classteacher', OLD.`classteacher`,'rollfrom', OLD.`rollfrom`,'rollto', OLD.`rollto`,'combind_1', OLD.`combind_1`,'combind_2', OLD.`combind_2`,'combind_3', OLD.`combind_3`,'combind_4', OLD.`combind_4`,'fourth', OLD.`fourth`,'allsubject', OLD.`allsubject`,'half', OLD.`half`,'full', OLD.`full`,'halfdone', OLD.`halfdone`,'fulldone', OLD.`fulldone`,'entrytime', OLD.`entrytime`,'sccode', OLD.`sccode`,'modifieddate', OLD.`modifieddate`),
            JSON_OBJECT('id', NEW.`id`,'idno', NEW.`idno`,'user', NEW.`user`,'slot', NEW.`slot`,'medium', NEW.`medium`,'version', NEW.`version`,'areaname', NEW.`areaname`,'subarea', NEW.`subarea`,'sessionyear', NEW.`sessionyear`,'yesno', NEW.`yesno`,'classteacher', NEW.`classteacher`,'rollfrom', NEW.`rollfrom`,'rollto', NEW.`rollto`,'combind_1', NEW.`combind_1`,'combind_2', NEW.`combind_2`,'combind_3', NEW.`combind_3`,'combind_4', NEW.`combind_4`,'fourth', NEW.`fourth`,'allsubject', NEW.`allsubject`,'half', NEW.`half`,'full', NEW.`full`,'halfdone', NEW.`halfdone`,'fulldone', NEW.`fulldone`,'entrytime', NEW.`entrytime`,'sccode', NEW.`sccode`,'modifieddate', NEW.`modifieddate`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER areas_after_delete
    AFTER DELETE ON `areas`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, changed_by)
        VALUES(
            'areas',
            'DELETE',
            OLD.`id`,
            JSON_OBJECT('id', OLD.`id`,'idno', OLD.`idno`,'user', OLD.`user`,'slot', OLD.`slot`,'medium', OLD.`medium`,'version', OLD.`version`,'areaname', OLD.`areaname`,'subarea', OLD.`subarea`,'sessionyear', OLD.`sessionyear`,'yesno', OLD.`yesno`,'classteacher', OLD.`classteacher`,'rollfrom', OLD.`rollfrom`,'rollto', OLD.`rollto`,'combind_1', OLD.`combind_1`,'combind_2', OLD.`combind_2`,'combind_3', OLD.`combind_3`,'combind_4', OLD.`combind_4`,'fourth', OLD.`fourth`,'allsubject', OLD.`allsubject`,'half', OLD.`half`,'full', OLD.`full`,'halfdone', OLD.`halfdone`,'fulldone', OLD.`fulldone`,'entrytime', OLD.`entrytime`,'sccode', OLD.`sccode`,'modifieddate', OLD.`modifieddate`),
            COALESCE(@current_user,'system')
        );
    END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `audit_temp`
--

DROP TABLE IF EXISTS `audit_temp`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `audit_temp` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cashbook_id` int(11) DEFAULT NULL,
  `sccode` int(11) DEFAULT NULL,
  `month` int(11) NOT NULL DEFAULT 0,
  `year` int(11) DEFAULT 0,
  `type` varchar(50) DEFAULT NULL,
  `date` date DEFAULT NULL,
  `particular` varchar(100) DEFAULT NULL,
  `institute_in` double NOT NULL DEFAULT 0,
  `govt_in` double NOT NULL DEFAULT 0,
  `eduboard_in` double NOT NULL DEFAULT 0,
  `institute_out` double NOT NULL DEFAULT 0,
  `govt_out` double NOT NULL DEFAULT 0,
  `bank_out` double NOT NULL DEFAULT 0,
  `amount` double NOT NULL DEFAULT 0,
  `block` varchar(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2647 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
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
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER audit_temp_after_insert
    AFTER INSERT ON `audit_temp`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, new_data, changed_by)
        VALUES(
            'audit_temp',
            'INSERT',
            NEW.`id`,
            JSON_OBJECT('id', NEW.`id`,'cashbook_id', NEW.`cashbook_id`,'sccode', NEW.`sccode`,'month', NEW.`month`,'year', NEW.`year`,'type', NEW.`type`,'date', NEW.`date`,'particular', NEW.`particular`,'institute_in', NEW.`institute_in`,'govt_in', NEW.`govt_in`,'eduboard_in', NEW.`eduboard_in`,'institute_out', NEW.`institute_out`,'govt_out', NEW.`govt_out`,'bank_out', NEW.`bank_out`,'amount', NEW.`amount`,'block', NEW.`block`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER audit_temp_after_update
    AFTER UPDATE ON `audit_temp`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, new_data, changed_by)
        VALUES(
            'audit_temp',
            'UPDATE',
            NEW.`id`,
            JSON_OBJECT('id', OLD.`id`,'cashbook_id', OLD.`cashbook_id`,'sccode', OLD.`sccode`,'month', OLD.`month`,'year', OLD.`year`,'type', OLD.`type`,'date', OLD.`date`,'particular', OLD.`particular`,'institute_in', OLD.`institute_in`,'govt_in', OLD.`govt_in`,'eduboard_in', OLD.`eduboard_in`,'institute_out', OLD.`institute_out`,'govt_out', OLD.`govt_out`,'bank_out', OLD.`bank_out`,'amount', OLD.`amount`,'block', OLD.`block`),
            JSON_OBJECT('id', NEW.`id`,'cashbook_id', NEW.`cashbook_id`,'sccode', NEW.`sccode`,'month', NEW.`month`,'year', NEW.`year`,'type', NEW.`type`,'date', NEW.`date`,'particular', NEW.`particular`,'institute_in', NEW.`institute_in`,'govt_in', NEW.`govt_in`,'eduboard_in', NEW.`eduboard_in`,'institute_out', NEW.`institute_out`,'govt_out', NEW.`govt_out`,'bank_out', NEW.`bank_out`,'amount', NEW.`amount`,'block', NEW.`block`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER audit_temp_after_delete
    AFTER DELETE ON `audit_temp`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, changed_by)
        VALUES(
            'audit_temp',
            'DELETE',
            OLD.`id`,
            JSON_OBJECT('id', OLD.`id`,'cashbook_id', OLD.`cashbook_id`,'sccode', OLD.`sccode`,'month', OLD.`month`,'year', OLD.`year`,'type', OLD.`type`,'date', OLD.`date`,'particular', OLD.`particular`,'institute_in', OLD.`institute_in`,'govt_in', OLD.`govt_in`,'eduboard_in', OLD.`eduboard_in`,'institute_out', OLD.`institute_out`,'govt_out', OLD.`govt_out`,'bank_out', OLD.`bank_out`,'amount', OLD.`amount`,'block', OLD.`block`),
            COALESCE(@current_user,'system')
        );
    END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `backup_info`
--

DROP TABLE IF EXISTS `backup_info`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `backup_info` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sccode` int(11) DEFAULT NULL,
  `type` varchar(20) DEFAULT NULL,
  `param1` varchar(30) DEFAULT NULL,
  `param2` varchar(30) DEFAULT NULL,
  `param3` varchar(30) DEFAULT NULL,
  `filename` varchar(50) DEFAULT NULL,
  `backupby` varchar(100) DEFAULT NULL,
  `backup_time` datetime DEFAULT NULL,
  `file_size` int(11) DEFAULT 0,
  `restore_time` datetime DEFAULT NULL,
  `deletion_time` datetime DEFAULT NULL,
  `modifieddate` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=82 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
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
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER backup_info_after_insert
    AFTER INSERT ON `backup_info`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, new_data, changed_by)
        VALUES(
            'backup_info',
            'INSERT',
            NEW.`id`,
            JSON_OBJECT('id', NEW.`id`,'sccode', NEW.`sccode`,'type', NEW.`type`,'param1', NEW.`param1`,'param2', NEW.`param2`,'param3', NEW.`param3`,'filename', NEW.`filename`,'backupby', NEW.`backupby`,'backup_time', NEW.`backup_time`,'file_size', NEW.`file_size`,'restore_time', NEW.`restore_time`,'deletion_time', NEW.`deletion_time`,'modifieddate', NEW.`modifieddate`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER backup_info_after_update
    AFTER UPDATE ON `backup_info`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, new_data, changed_by)
        VALUES(
            'backup_info',
            'UPDATE',
            NEW.`id`,
            JSON_OBJECT('id', OLD.`id`,'sccode', OLD.`sccode`,'type', OLD.`type`,'param1', OLD.`param1`,'param2', OLD.`param2`,'param3', OLD.`param3`,'filename', OLD.`filename`,'backupby', OLD.`backupby`,'backup_time', OLD.`backup_time`,'file_size', OLD.`file_size`,'restore_time', OLD.`restore_time`,'deletion_time', OLD.`deletion_time`,'modifieddate', OLD.`modifieddate`),
            JSON_OBJECT('id', NEW.`id`,'sccode', NEW.`sccode`,'type', NEW.`type`,'param1', NEW.`param1`,'param2', NEW.`param2`,'param3', NEW.`param3`,'filename', NEW.`filename`,'backupby', NEW.`backupby`,'backup_time', NEW.`backup_time`,'file_size', NEW.`file_size`,'restore_time', NEW.`restore_time`,'deletion_time', NEW.`deletion_time`,'modifieddate', NEW.`modifieddate`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER backup_info_after_delete
    AFTER DELETE ON `backup_info`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, changed_by)
        VALUES(
            'backup_info',
            'DELETE',
            OLD.`id`,
            JSON_OBJECT('id', OLD.`id`,'sccode', OLD.`sccode`,'type', OLD.`type`,'param1', OLD.`param1`,'param2', OLD.`param2`,'param3', OLD.`param3`,'filename', OLD.`filename`,'backupby', OLD.`backupby`,'backup_time', OLD.`backup_time`,'file_size', OLD.`file_size`,'restore_time', OLD.`restore_time`,'deletion_time', OLD.`deletion_time`,'modifieddate', OLD.`modifieddate`),
            COALESCE(@current_user,'system')
        );
    END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `backup_module`
--

DROP TABLE IF EXISTS `backup_module`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `backup_module` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `module_name` varchar(20) DEFAULT NULL,
  `table_list` varchar(500) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf32 COLLATE=utf32_unicode_ci;
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
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER backup_module_after_insert
    AFTER INSERT ON `backup_module`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, new_data, changed_by)
        VALUES(
            'backup_module',
            'INSERT',
            NEW.`id`,
            JSON_OBJECT('id', NEW.`id`,'module_name', NEW.`module_name`,'table_list', NEW.`table_list`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER backup_module_after_update
    AFTER UPDATE ON `backup_module`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, new_data, changed_by)
        VALUES(
            'backup_module',
            'UPDATE',
            NEW.`id`,
            JSON_OBJECT('id', OLD.`id`,'module_name', OLD.`module_name`,'table_list', OLD.`table_list`),
            JSON_OBJECT('id', NEW.`id`,'module_name', NEW.`module_name`,'table_list', NEW.`table_list`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER backup_module_after_delete
    AFTER DELETE ON `backup_module`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, changed_by)
        VALUES(
            'backup_module',
            'DELETE',
            OLD.`id`,
            JSON_OBJECT('id', OLD.`id`,'module_name', OLD.`module_name`,'table_list', OLD.`table_list`),
            COALESCE(@current_user,'system')
        );
    END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `badges`
--

DROP TABLE IF EXISTS `badges`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `badges` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `icon_url` varchar(255) DEFAULT NULL,
  `condition_type` varchar(50) DEFAULT NULL,
  `condition_value` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
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
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER badges_after_insert
    AFTER INSERT ON `badges`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, new_data, changed_by)
        VALUES(
            'badges',
            'INSERT',
            NEW.`id`,
            JSON_OBJECT('id', NEW.`id`,'name', NEW.`name`,'description', NEW.`description`,'icon_url', NEW.`icon_url`,'condition_type', NEW.`condition_type`,'condition_value', NEW.`condition_value`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER badges_after_update
    AFTER UPDATE ON `badges`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, new_data, changed_by)
        VALUES(
            'badges',
            'UPDATE',
            NEW.`id`,
            JSON_OBJECT('id', OLD.`id`,'name', OLD.`name`,'description', OLD.`description`,'icon_url', OLD.`icon_url`,'condition_type', OLD.`condition_type`,'condition_value', OLD.`condition_value`),
            JSON_OBJECT('id', NEW.`id`,'name', NEW.`name`,'description', NEW.`description`,'icon_url', NEW.`icon_url`,'condition_type', NEW.`condition_type`,'condition_value', NEW.`condition_value`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER badges_after_delete
    AFTER DELETE ON `badges`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, changed_by)
        VALUES(
            'badges',
            'DELETE',
            OLD.`id`,
            JSON_OBJECT('id', OLD.`id`,'name', OLD.`name`,'description', OLD.`description`,'icon_url', OLD.`icon_url`,'condition_type', OLD.`condition_type`,'condition_value', OLD.`condition_value`),
            COALESCE(@current_user,'system')
        );
    END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `bank_deposit_item`
--

DROP TABLE IF EXISTS `bank_deposit_item`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bank_deposit_item` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sccode` varchar(6) DEFAULT NULL,
  `accno` varchar(15) DEFAULT NULL,
  `chqno` varchar(20) DEFAULT NULL,
  `descrip` varchar(100) DEFAULT NULL,
  `amount` int(11) NOT NULL DEFAULT 0,
  `entryby` varchar(120) DEFAULT NULL,
  `entrytime` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
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
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER bank_deposit_item_after_insert
    AFTER INSERT ON `bank_deposit_item`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, new_data, changed_by)
        VALUES(
            'bank_deposit_item',
            'INSERT',
            NEW.`id`,
            JSON_OBJECT('id', NEW.`id`,'sccode', NEW.`sccode`,'accno', NEW.`accno`,'chqno', NEW.`chqno`,'descrip', NEW.`descrip`,'amount', NEW.`amount`,'entryby', NEW.`entryby`,'entrytime', NEW.`entrytime`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER bank_deposit_item_after_update
    AFTER UPDATE ON `bank_deposit_item`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, new_data, changed_by)
        VALUES(
            'bank_deposit_item',
            'UPDATE',
            NEW.`id`,
            JSON_OBJECT('id', OLD.`id`,'sccode', OLD.`sccode`,'accno', OLD.`accno`,'chqno', OLD.`chqno`,'descrip', OLD.`descrip`,'amount', OLD.`amount`,'entryby', OLD.`entryby`,'entrytime', OLD.`entrytime`),
            JSON_OBJECT('id', NEW.`id`,'sccode', NEW.`sccode`,'accno', NEW.`accno`,'chqno', NEW.`chqno`,'descrip', NEW.`descrip`,'amount', NEW.`amount`,'entryby', NEW.`entryby`,'entrytime', NEW.`entrytime`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER bank_deposit_item_after_delete
    AFTER DELETE ON `bank_deposit_item`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, changed_by)
        VALUES(
            'bank_deposit_item',
            'DELETE',
            OLD.`id`,
            JSON_OBJECT('id', OLD.`id`,'sccode', OLD.`sccode`,'accno', OLD.`accno`,'chqno', OLD.`chqno`,'descrip', OLD.`descrip`,'amount', OLD.`amount`,'entryby', OLD.`entryby`,'entrytime', OLD.`entrytime`),
            COALESCE(@current_user,'system')
        );
    END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `bankinfo`
--

DROP TABLE IF EXISTS `bankinfo`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bankinfo` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sccode` int(11) DEFAULT NULL,
  `slot` varchar(20) DEFAULT NULL,
  `accno` varchar(20) DEFAULT NULL,
  `acctype` varchar(20) DEFAULT NULL,
  `bankname` varchar(30) DEFAULT NULL,
  `branch` varchar(150) DEFAULT NULL,
  `openingdate` date DEFAULT NULL,
  `closingdate` date DEFAULT NULL,
  `status` int(11) NOT NULL DEFAULT 1,
  `modifieddate` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
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
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER bankinfo_after_insert
    AFTER INSERT ON `bankinfo`
    FOR EACH ROW
    BEGIN
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
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER bankinfo_after_update
    AFTER UPDATE ON `bankinfo`
    FOR EACH ROW
    BEGIN
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
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER bankinfo_after_delete
    AFTER DELETE ON `bankinfo`
    FOR EACH ROW
    BEGIN
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
-- Table structure for table `banklist`
--

DROP TABLE IF EXISTS `banklist`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `banklist` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sccode` int(11) DEFAULT NULL,
  `bname` varchar(50) DEFAULT NULL,
  `modifieddate` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
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
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER banklist_after_insert
    AFTER INSERT ON `banklist`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, new_data, changed_by)
        VALUES(
            'banklist',
            'INSERT',
            NEW.`id`,
            JSON_OBJECT('id', NEW.`id`,'sccode', NEW.`sccode`,'bname', NEW.`bname`,'modifieddate', NEW.`modifieddate`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER banklist_after_update
    AFTER UPDATE ON `banklist`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, new_data, changed_by)
        VALUES(
            'banklist',
            'UPDATE',
            NEW.`id`,
            JSON_OBJECT('id', OLD.`id`,'sccode', OLD.`sccode`,'bname', OLD.`bname`,'modifieddate', OLD.`modifieddate`),
            JSON_OBJECT('id', NEW.`id`,'sccode', NEW.`sccode`,'bname', NEW.`bname`,'modifieddate', NEW.`modifieddate`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER banklist_after_delete
    AFTER DELETE ON `banklist`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, changed_by)
        VALUES(
            'banklist',
            'DELETE',
            OLD.`id`,
            JSON_OBJECT('id', OLD.`id`,'sccode', OLD.`sccode`,'bname', OLD.`bname`,'modifieddate', OLD.`modifieddate`),
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
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `banktrans` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sccode` int(11) DEFAULT NULL,
  `accid` int(11) DEFAULT NULL,
  `accno` varchar(20) DEFAULT NULL,
  `date` date DEFAULT NULL,
  `transopening` double DEFAULT 0,
  `transtype` varchar(50) DEFAULT NULL,
  `partid` int(11) DEFAULT NULL,
  `particulareng` varchar(200) DEFAULT NULL,
  `particularben` varchar(200) DEFAULT NULL,
  `chqno` varchar(20) DEFAULT NULL,
  `amount` double DEFAULT 0,
  `balance` double DEFAULT 0,
  `refno` varchar(20) DEFAULT NULL,
  `entryby` varchar(150) DEFAULT NULL,
  `entrytime` datetime DEFAULT NULL,
  `verified` int(11) NOT NULL DEFAULT 0,
  `verifyby` varchar(150) DEFAULT NULL,
  `verifytime` datetime DEFAULT NULL,
  `modifieddate` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=292 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
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
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER banktrans_after_insert
    AFTER INSERT ON `banktrans`
    FOR EACH ROW
    BEGIN
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
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER banktrans_after_update
    AFTER UPDATE ON `banktrans`
    FOR EACH ROW
    BEGIN
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
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER banktrans_after_delete
    AFTER DELETE ON `banktrans`
    FOR EACH ROW
    BEGIN
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
-- Table structure for table `ben_address`
--

DROP TABLE IF EXISTS `ben_address`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ben_address` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sccode` int(11) DEFAULT NULL,
  `eng_str` varchar(100) DEFAULT NULL,
  `ben_str` varchar(150) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=75 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
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
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER ben_address_after_insert
    AFTER INSERT ON `ben_address`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, new_data, changed_by)
        VALUES(
            'ben_address',
            'INSERT',
            NEW.`id`,
            JSON_OBJECT('id', NEW.`id`,'sccode', NEW.`sccode`,'eng_str', NEW.`eng_str`,'ben_str', NEW.`ben_str`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER ben_address_after_update
    AFTER UPDATE ON `ben_address`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, new_data, changed_by)
        VALUES(
            'ben_address',
            'UPDATE',
            NEW.`id`,
            JSON_OBJECT('id', OLD.`id`,'sccode', OLD.`sccode`,'eng_str', OLD.`eng_str`,'ben_str', OLD.`ben_str`),
            JSON_OBJECT('id', NEW.`id`,'sccode', NEW.`sccode`,'eng_str', NEW.`eng_str`,'ben_str', NEW.`ben_str`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER ben_address_after_delete
    AFTER DELETE ON `ben_address`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, changed_by)
        VALUES(
            'ben_address',
            'DELETE',
            OLD.`id`,
            JSON_OBJECT('id', OLD.`id`,'sccode', OLD.`sccode`,'eng_str', OLD.`eng_str`,'ben_str', OLD.`ben_str`),
            COALESCE(@current_user,'system')
        );
    END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `blood`
--

DROP TABLE IF EXISTS `blood`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `blood` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(80) DEFAULT NULL,
  `vill` varchar(50) DEFAULT NULL,
  `ps` varchar(30) DEFAULT NULL,
  `dist` varchar(20) DEFAULT NULL,
  `mobile` varchar(11) DEFAULT NULL,
  `age` int(11) DEFAULT NULL,
  `bgroup` varchar(3) DEFAULT NULL,
  `sex` varchar(1) NOT NULL,
  `donar` varchar(1) NOT NULL DEFAULT '0',
  `lastdonatedate` date DEFAULT NULL,
  `bodystructure` varchar(1) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
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
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER blood_after_insert
    AFTER INSERT ON `blood`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, new_data, changed_by)
        VALUES(
            'blood',
            'INSERT',
            NEW.`id`,
            JSON_OBJECT('id', NEW.`id`,'name', NEW.`name`,'vill', NEW.`vill`,'ps', NEW.`ps`,'dist', NEW.`dist`,'mobile', NEW.`mobile`,'age', NEW.`age`,'bgroup', NEW.`bgroup`,'sex', NEW.`sex`,'donar', NEW.`donar`,'lastdonatedate', NEW.`lastdonatedate`,'bodystructure', NEW.`bodystructure`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER blood_after_update
    AFTER UPDATE ON `blood`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, new_data, changed_by)
        VALUES(
            'blood',
            'UPDATE',
            NEW.`id`,
            JSON_OBJECT('id', OLD.`id`,'name', OLD.`name`,'vill', OLD.`vill`,'ps', OLD.`ps`,'dist', OLD.`dist`,'mobile', OLD.`mobile`,'age', OLD.`age`,'bgroup', OLD.`bgroup`,'sex', OLD.`sex`,'donar', OLD.`donar`,'lastdonatedate', OLD.`lastdonatedate`,'bodystructure', OLD.`bodystructure`),
            JSON_OBJECT('id', NEW.`id`,'name', NEW.`name`,'vill', NEW.`vill`,'ps', NEW.`ps`,'dist', NEW.`dist`,'mobile', NEW.`mobile`,'age', NEW.`age`,'bgroup', NEW.`bgroup`,'sex', NEW.`sex`,'donar', NEW.`donar`,'lastdonatedate', NEW.`lastdonatedate`,'bodystructure', NEW.`bodystructure`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER blood_after_delete
    AFTER DELETE ON `blood`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, changed_by)
        VALUES(
            'blood',
            'DELETE',
            OLD.`id`,
            JSON_OBJECT('id', OLD.`id`,'name', OLD.`name`,'vill', OLD.`vill`,'ps', OLD.`ps`,'dist', OLD.`dist`,'mobile', OLD.`mobile`,'age', OLD.`age`,'bgroup', OLD.`bgroup`,'sex', OLD.`sex`,'donar', OLD.`donar`,'lastdonatedate', OLD.`lastdonatedate`,'bodystructure', OLD.`bodystructure`),
            COALESCE(@current_user,'system')
        );
    END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `branchlist`
--

DROP TABLE IF EXISTS `branchlist`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `branchlist` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sccode` int(11) DEFAULT NULL,
  `brname` varchar(50) DEFAULT NULL,
  `brdisp` varchar(50) CHARACTER SET utf32 COLLATE utf32_unicode_ci DEFAULT NULL,
  `modifieddate` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
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
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER branchlist_after_insert
    AFTER INSERT ON `branchlist`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, new_data, changed_by)
        VALUES(
            'branchlist',
            'INSERT',
            NEW.`id`,
            JSON_OBJECT('id', NEW.`id`,'sccode', NEW.`sccode`,'brname', NEW.`brname`,'brdisp', NEW.`brdisp`,'modifieddate', NEW.`modifieddate`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER branchlist_after_update
    AFTER UPDATE ON `branchlist`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, new_data, changed_by)
        VALUES(
            'branchlist',
            'UPDATE',
            NEW.`id`,
            JSON_OBJECT('id', OLD.`id`,'sccode', OLD.`sccode`,'brname', OLD.`brname`,'brdisp', OLD.`brdisp`,'modifieddate', OLD.`modifieddate`),
            JSON_OBJECT('id', NEW.`id`,'sccode', NEW.`sccode`,'brname', NEW.`brname`,'brdisp', NEW.`brdisp`,'modifieddate', NEW.`modifieddate`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER branchlist_after_delete
    AFTER DELETE ON `branchlist`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, changed_by)
        VALUES(
            'branchlist',
            'DELETE',
            OLD.`id`,
            JSON_OBJECT('id', OLD.`id`,'sccode', OLD.`sccode`,'brname', OLD.`brname`,'brdisp', OLD.`brdisp`,'modifieddate', OLD.`modifieddate`),
            COALESCE(@current_user,'system')
        );
    END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `browser`
--

DROP TABLE IF EXISTS `browser`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `browser` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `browsername` varchar(100) DEFAULT NULL,
  `css` varchar(20) DEFAULT NULL,
  `eiin` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
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
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER browser_after_insert
    AFTER INSERT ON `browser`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, new_data, changed_by)
        VALUES(
            'browser',
            'INSERT',
            NEW.`id`,
            JSON_OBJECT('id', NEW.`id`,'browsername', NEW.`browsername`,'css', NEW.`css`,'eiin', NEW.`eiin`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER browser_after_update
    AFTER UPDATE ON `browser`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, new_data, changed_by)
        VALUES(
            'browser',
            'UPDATE',
            NEW.`id`,
            JSON_OBJECT('id', OLD.`id`,'browsername', OLD.`browsername`,'css', OLD.`css`,'eiin', OLD.`eiin`),
            JSON_OBJECT('id', NEW.`id`,'browsername', NEW.`browsername`,'css', NEW.`css`,'eiin', NEW.`eiin`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER browser_after_delete
    AFTER DELETE ON `browser`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, changed_by)
        VALUES(
            'browser',
            'DELETE',
            OLD.`id`,
            JSON_OBJECT('id', OLD.`id`,'browsername', OLD.`browsername`,'css', OLD.`css`,'eiin', OLD.`eiin`),
            COALESCE(@current_user,'system')
        );
    END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `calendar`
--

DROP TABLE IF EXISTS `calendar`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `calendar` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date` date DEFAULT NULL,
  `day` varchar(10) NOT NULL,
  `sccode` int(11) DEFAULT 0,
  `descrip` varchar(150) DEFAULT NULL,
  `category` varchar(30) DEFAULT NULL,
  `work` int(11) NOT NULL DEFAULT 1,
  `class` int(11) NOT NULL DEFAULT 1,
  `dateto` date DEFAULT NULL,
  `day_count` int(11) NOT NULL DEFAULT 1,
  `icon` varchar(20) NOT NULL DEFAULT 'calendar3-event-fill',
  `color` varchar(20) NOT NULL DEFAULT 'lightgray',
  `modifieddate` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2975 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
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
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER calendar_after_insert
    AFTER INSERT ON `calendar`
    FOR EACH ROW
    BEGIN
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
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER calendar_after_update
    AFTER UPDATE ON `calendar`
    FOR EACH ROW
    BEGIN
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
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER calendar_after_delete
    AFTER DELETE ON `calendar`
    FOR EACH ROW
    BEGIN
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
-- Table structure for table `cashbook`
--

DROP TABLE IF EXISTS `cashbook`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cashbook` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sccode` int(11) DEFAULT NULL,
  `sessionyear` int(11) DEFAULT NULL,
  `month` int(11) NOT NULL DEFAULT 0,
  `year` int(11) NOT NULL DEFAULT 0,
  `slots` varchar(15) DEFAULT NULL,
  `date` date DEFAULT NULL,
  `type` varchar(20) DEFAULT NULL,
  `refno` varchar(20) DEFAULT '0',
  `partid` int(11) DEFAULT NULL,
  `category` varchar(100) DEFAULT NULL,
  `memono` int(11) DEFAULT 0,
  `particulars` varchar(200) DEFAULT NULL,
  `income` double NOT NULL DEFAULT 0,
  `expenditure` double NOT NULL DEFAULT 0,
  `amount` double NOT NULL DEFAULT 0,
  `entryby` varchar(150) DEFAULT NULL,
  `entrytime` datetime DEFAULT NULL,
  `ongoing` int(11) NOT NULL DEFAULT 0,
  `module` varchar(20) DEFAULT '' COMMENT 'bank/voucher/',
  `status` int(11) DEFAULT NULL,
  `modifieddate` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2832 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
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
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER cashbook_after_insert
    AFTER INSERT ON `cashbook`
    FOR EACH ROW
    BEGIN
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
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER cashbook_after_update
    AFTER UPDATE ON `cashbook`
    FOR EACH ROW
    BEGIN
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
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER cashbook_after_delete
    AFTER DELETE ON `cashbook`
    FOR EACH ROW
    BEGIN
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
-- Table structure for table `classroutine`
--

DROP TABLE IF EXISTS `classroutine`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `classroutine` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sessionyear` int(11) DEFAULT NULL,
  `sccode` int(11) NOT NULL,
  `shift` varchar(10) NOT NULL,
  `period` varchar(10) NOT NULL,
  `classname` varchar(10) NOT NULL,
  `sectionname` varchar(25) NOT NULL,
  `day` varchar(10) NOT NULL,
  `tid` int(11) NOT NULL,
  `subject` varchar(25) NOT NULL,
  `periodtime` time NOT NULL,
  `periodtimeend` time NOT NULL,
  `modifieddate` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1447 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
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
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER classroutine_after_insert
    AFTER INSERT ON `classroutine`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, new_data, changed_by)
        VALUES(
            'classroutine',
            'INSERT',
            NEW.`id`,
            JSON_OBJECT('id', NEW.`id`,'sessionyear', NEW.`sessionyear`,'sccode', NEW.`sccode`,'shift', NEW.`shift`,'period', NEW.`period`,'classname', NEW.`classname`,'sectionname', NEW.`sectionname`,'day', NEW.`day`,'tid', NEW.`tid`,'subject', NEW.`subject`,'periodtime', NEW.`periodtime`,'periodtimeend', NEW.`periodtimeend`,'modifieddate', NEW.`modifieddate`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER classroutine_after_update
    AFTER UPDATE ON `classroutine`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, new_data, changed_by)
        VALUES(
            'classroutine',
            'UPDATE',
            NEW.`id`,
            JSON_OBJECT('id', OLD.`id`,'sessionyear', OLD.`sessionyear`,'sccode', OLD.`sccode`,'shift', OLD.`shift`,'period', OLD.`period`,'classname', OLD.`classname`,'sectionname', OLD.`sectionname`,'day', OLD.`day`,'tid', OLD.`tid`,'subject', OLD.`subject`,'periodtime', OLD.`periodtime`,'periodtimeend', OLD.`periodtimeend`,'modifieddate', OLD.`modifieddate`),
            JSON_OBJECT('id', NEW.`id`,'sessionyear', NEW.`sessionyear`,'sccode', NEW.`sccode`,'shift', NEW.`shift`,'period', NEW.`period`,'classname', NEW.`classname`,'sectionname', NEW.`sectionname`,'day', NEW.`day`,'tid', NEW.`tid`,'subject', NEW.`subject`,'periodtime', NEW.`periodtime`,'periodtimeend', NEW.`periodtimeend`,'modifieddate', NEW.`modifieddate`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER classroutine_after_delete
    AFTER DELETE ON `classroutine`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, changed_by)
        VALUES(
            'classroutine',
            'DELETE',
            OLD.`id`,
            JSON_OBJECT('id', OLD.`id`,'sessionyear', OLD.`sessionyear`,'sccode', OLD.`sccode`,'shift', OLD.`shift`,'period', OLD.`period`,'classname', OLD.`classname`,'sectionname', OLD.`sectionname`,'day', OLD.`day`,'tid', OLD.`tid`,'subject', OLD.`subject`,'periodtime', OLD.`periodtime`,'periodtimeend', OLD.`periodtimeend`,'modifieddate', OLD.`modifieddate`),
            COALESCE(@current_user,'system')
        );
    END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `classschedule`
--

DROP TABLE IF EXISTS `classschedule`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `classschedule` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sccode` int(11) DEFAULT NULL,
  `sessionyear` varchar(11) DEFAULT NULL,
  `slots` varchar(20) DEFAULT NULL,
  `shift` varchar(20) DEFAULT NULL,
  `period` int(11) DEFAULT NULL,
  `timestart` time DEFAULT NULL,
  `timeend` time DEFAULT NULL,
  `duration` int(11) DEFAULT NULL,
  `modifieddate` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=63 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
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
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER classschedule_after_insert
    AFTER INSERT ON `classschedule`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, new_data, changed_by)
        VALUES(
            'classschedule',
            'INSERT',
            NEW.`id`,
            JSON_OBJECT('id', NEW.`id`,'sccode', NEW.`sccode`,'sessionyear', NEW.`sessionyear`,'slots', NEW.`slots`,'shift', NEW.`shift`,'period', NEW.`period`,'timestart', NEW.`timestart`,'timeend', NEW.`timeend`,'duration', NEW.`duration`,'modifieddate', NEW.`modifieddate`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER classschedule_after_update
    AFTER UPDATE ON `classschedule`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, new_data, changed_by)
        VALUES(
            'classschedule',
            'UPDATE',
            NEW.`id`,
            JSON_OBJECT('id', OLD.`id`,'sccode', OLD.`sccode`,'sessionyear', OLD.`sessionyear`,'slots', OLD.`slots`,'shift', OLD.`shift`,'period', OLD.`period`,'timestart', OLD.`timestart`,'timeend', OLD.`timeend`,'duration', OLD.`duration`,'modifieddate', OLD.`modifieddate`),
            JSON_OBJECT('id', NEW.`id`,'sccode', NEW.`sccode`,'sessionyear', NEW.`sessionyear`,'slots', NEW.`slots`,'shift', NEW.`shift`,'period', NEW.`period`,'timestart', NEW.`timestart`,'timeend', NEW.`timeend`,'duration', NEW.`duration`,'modifieddate', NEW.`modifieddate`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER classschedule_after_delete
    AFTER DELETE ON `classschedule`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, changed_by)
        VALUES(
            'classschedule',
            'DELETE',
            OLD.`id`,
            JSON_OBJECT('id', OLD.`id`,'sccode', OLD.`sccode`,'sessionyear', OLD.`sessionyear`,'slots', OLD.`slots`,'shift', OLD.`shift`,'period', OLD.`period`,'timestart', OLD.`timestart`,'timeend', OLD.`timeend`,'duration', OLD.`duration`,'modifieddate', OLD.`modifieddate`),
            COALESCE(@current_user,'system')
        );
    END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `clsroutine`
--

DROP TABLE IF EXISTS `clsroutine`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `clsroutine` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sccode` int(11) DEFAULT NULL,
  `sessionyear` int(11) DEFAULT NULL,
  `classname` varchar(15) DEFAULT NULL,
  `sectionname` varchar(20) DEFAULT NULL,
  `period` int(11) DEFAULT NULL,
  `wday` int(11) DEFAULT NULL,
  `day` varchar(12) DEFAULT NULL,
  `subcode` int(11) DEFAULT NULL,
  `tid` bigint(20) DEFAULT NULL,
  `entryby` varchar(100) DEFAULT NULL,
  `modifieddate` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1142 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
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
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER clsroutine_after_insert
    AFTER INSERT ON `clsroutine`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, new_data, changed_by)
        VALUES(
            'clsroutine',
            'INSERT',
            NEW.`id`,
            JSON_OBJECT('id', NEW.`id`,'sccode', NEW.`sccode`,'sessionyear', NEW.`sessionyear`,'classname', NEW.`classname`,'sectionname', NEW.`sectionname`,'period', NEW.`period`,'wday', NEW.`wday`,'day', NEW.`day`,'subcode', NEW.`subcode`,'tid', NEW.`tid`,'entryby', NEW.`entryby`,'modifieddate', NEW.`modifieddate`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER clsroutine_after_update
    AFTER UPDATE ON `clsroutine`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, new_data, changed_by)
        VALUES(
            'clsroutine',
            'UPDATE',
            NEW.`id`,
            JSON_OBJECT('id', OLD.`id`,'sccode', OLD.`sccode`,'sessionyear', OLD.`sessionyear`,'classname', OLD.`classname`,'sectionname', OLD.`sectionname`,'period', OLD.`period`,'wday', OLD.`wday`,'day', OLD.`day`,'subcode', OLD.`subcode`,'tid', OLD.`tid`,'entryby', OLD.`entryby`,'modifieddate', OLD.`modifieddate`),
            JSON_OBJECT('id', NEW.`id`,'sccode', NEW.`sccode`,'sessionyear', NEW.`sessionyear`,'classname', NEW.`classname`,'sectionname', NEW.`sectionname`,'period', NEW.`period`,'wday', NEW.`wday`,'day', NEW.`day`,'subcode', NEW.`subcode`,'tid', NEW.`tid`,'entryby', NEW.`entryby`,'modifieddate', NEW.`modifieddate`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER clsroutine_after_delete
    AFTER DELETE ON `clsroutine`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, changed_by)
        VALUES(
            'clsroutine',
            'DELETE',
            OLD.`id`,
            JSON_OBJECT('id', OLD.`id`,'sccode', OLD.`sccode`,'sessionyear', OLD.`sessionyear`,'classname', OLD.`classname`,'sectionname', OLD.`sectionname`,'period', OLD.`period`,'wday', OLD.`wday`,'day', OLD.`day`,'subcode', OLD.`subcode`,'tid', OLD.`tid`,'entryby', OLD.`entryby`,'modifieddate', OLD.`modifieddate`),
            COALESCE(@current_user,'system')
        );
    END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `clublist`
--

DROP TABLE IF EXISTS `clublist`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `clublist` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `clubname` varchar(50) DEFAULT NULL,
  `descrip` varchar(250) DEFAULT NULL,
  `president` tinyint(1) NOT NULL DEFAULT 1,
  `vicepresident` int(11) NOT NULL DEFAULT 0,
  `secretary` int(11) NOT NULL DEFAULT 1,
  `jointsecretary` int(11) NOT NULL DEFAULT 0,
  `accountants` int(11) NOT NULL DEFAULT 0,
  `reservemem` int(11) NOT NULL DEFAULT 0,
  `restmember` int(11) NOT NULL DEFAULT 5,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf32 COLLATE=utf32_unicode_ci;
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
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER clublist_after_insert
    AFTER INSERT ON `clublist`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, new_data, changed_by)
        VALUES(
            'clublist',
            'INSERT',
            NEW.`id`,
            JSON_OBJECT('id', NEW.`id`,'clubname', NEW.`clubname`,'descrip', NEW.`descrip`,'president', NEW.`president`,'vicepresident', NEW.`vicepresident`,'secretary', NEW.`secretary`,'jointsecretary', NEW.`jointsecretary`,'accountants', NEW.`accountants`,'reservemem', NEW.`reservemem`,'restmember', NEW.`restmember`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER clublist_after_update
    AFTER UPDATE ON `clublist`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, new_data, changed_by)
        VALUES(
            'clublist',
            'UPDATE',
            NEW.`id`,
            JSON_OBJECT('id', OLD.`id`,'clubname', OLD.`clubname`,'descrip', OLD.`descrip`,'president', OLD.`president`,'vicepresident', OLD.`vicepresident`,'secretary', OLD.`secretary`,'jointsecretary', OLD.`jointsecretary`,'accountants', OLD.`accountants`,'reservemem', OLD.`reservemem`,'restmember', OLD.`restmember`),
            JSON_OBJECT('id', NEW.`id`,'clubname', NEW.`clubname`,'descrip', NEW.`descrip`,'president', NEW.`president`,'vicepresident', NEW.`vicepresident`,'secretary', NEW.`secretary`,'jointsecretary', NEW.`jointsecretary`,'accountants', NEW.`accountants`,'reservemem', NEW.`reservemem`,'restmember', NEW.`restmember`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER clublist_after_delete
    AFTER DELETE ON `clublist`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, changed_by)
        VALUES(
            'clublist',
            'DELETE',
            OLD.`id`,
            JSON_OBJECT('id', OLD.`id`,'clubname', OLD.`clubname`,'descrip', OLD.`descrip`,'president', OLD.`president`,'vicepresident', OLD.`vicepresident`,'secretary', OLD.`secretary`,'jointsecretary', OLD.`jointsecretary`,'accountants', OLD.`accountants`,'reservemem', OLD.`reservemem`,'restmember', OLD.`restmember`),
            COALESCE(@current_user,'system')
        );
    END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `committee`
--

DROP TABLE IF EXISTS `committee`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `committee` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sccode` int(11) DEFAULT NULL,
  `title` varchar(100) DEFAULT NULL,
  `descrip` varchar(500) DEFAULT NULL,
  `cycle` int(11) NOT NULL DEFAULT 0,
  `active_date` date DEFAULT NULL,
  `expire_date` date DEFAULT NULL,
  `dismis_date` date DEFAULT NULL,
  `entryby` varchar(120) DEFAULT NULL,
  `entrytime` datetime DEFAULT NULL,
  `modifieddate` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf32 COLLATE=utf32_unicode_ci;
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
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER committee_after_insert
    AFTER INSERT ON `committee`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, new_data, changed_by)
        VALUES(
            'committee',
            'INSERT',
            NEW.`id`,
            JSON_OBJECT('id', NEW.`id`,'sccode', NEW.`sccode`,'title', NEW.`title`,'descrip', NEW.`descrip`,'cycle', NEW.`cycle`,'active_date', NEW.`active_date`,'expire_date', NEW.`expire_date`,'dismis_date', NEW.`dismis_date`,'entryby', NEW.`entryby`,'entrytime', NEW.`entrytime`,'modifieddate', NEW.`modifieddate`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER committee_after_update
    AFTER UPDATE ON `committee`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, new_data, changed_by)
        VALUES(
            'committee',
            'UPDATE',
            NEW.`id`,
            JSON_OBJECT('id', OLD.`id`,'sccode', OLD.`sccode`,'title', OLD.`title`,'descrip', OLD.`descrip`,'cycle', OLD.`cycle`,'active_date', OLD.`active_date`,'expire_date', OLD.`expire_date`,'dismis_date', OLD.`dismis_date`,'entryby', OLD.`entryby`,'entrytime', OLD.`entrytime`,'modifieddate', OLD.`modifieddate`),
            JSON_OBJECT('id', NEW.`id`,'sccode', NEW.`sccode`,'title', NEW.`title`,'descrip', NEW.`descrip`,'cycle', NEW.`cycle`,'active_date', NEW.`active_date`,'expire_date', NEW.`expire_date`,'dismis_date', NEW.`dismis_date`,'entryby', NEW.`entryby`,'entrytime', NEW.`entrytime`,'modifieddate', NEW.`modifieddate`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER committee_after_delete
    AFTER DELETE ON `committee`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, changed_by)
        VALUES(
            'committee',
            'DELETE',
            OLD.`id`,
            JSON_OBJECT('id', OLD.`id`,'sccode', OLD.`sccode`,'title', OLD.`title`,'descrip', OLD.`descrip`,'cycle', OLD.`cycle`,'active_date', OLD.`active_date`,'expire_date', OLD.`expire_date`,'dismis_date', OLD.`dismis_date`,'entryby', OLD.`entryby`,'entrytime', OLD.`entrytime`,'modifieddate', OLD.`modifieddate`),
            COALESCE(@current_user,'system')
        );
    END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `datatest`
--

DROP TABLE IF EXISTS `datatest`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `datatest` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fld1` varchar(200) DEFAULT NULL,
  `fld2` varchar(200) DEFAULT NULL,
  `fld3` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=102695 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
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
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER datatest_after_insert
    AFTER INSERT ON `datatest`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, new_data, changed_by)
        VALUES(
            'datatest',
            'INSERT',
            NEW.`id`,
            JSON_OBJECT('id', NEW.`id`,'fld1', NEW.`fld1`,'fld2', NEW.`fld2`,'fld3', NEW.`fld3`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER datatest_after_update
    AFTER UPDATE ON `datatest`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, new_data, changed_by)
        VALUES(
            'datatest',
            'UPDATE',
            NEW.`id`,
            JSON_OBJECT('id', OLD.`id`,'fld1', OLD.`fld1`,'fld2', OLD.`fld2`,'fld3', OLD.`fld3`),
            JSON_OBJECT('id', NEW.`id`,'fld1', NEW.`fld1`,'fld2', NEW.`fld2`,'fld3', NEW.`fld3`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER datatest_after_delete
    AFTER DELETE ON `datatest`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, changed_by)
        VALUES(
            'datatest',
            'DELETE',
            OLD.`id`,
            JSON_OBJECT('id', OLD.`id`,'fld1', OLD.`fld1`,'fld2', OLD.`fld2`,'fld3', OLD.`fld3`),
            COALESCE(@current_user,'system')
        );
    END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `designation`
--

DROP TABLE IF EXISTS `designation`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `designation` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sl` int(11) DEFAULT NULL,
  `title` varchar(25) DEFAULT NULL,
  `ranks` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
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
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER designation_after_insert
    AFTER INSERT ON `designation`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, new_data, changed_by)
        VALUES(
            'designation',
            'INSERT',
            NEW.`id`,
            JSON_OBJECT('id', NEW.`id`,'sl', NEW.`sl`,'title', NEW.`title`,'ranks', NEW.`ranks`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER designation_after_update
    AFTER UPDATE ON `designation`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, new_data, changed_by)
        VALUES(
            'designation',
            'UPDATE',
            NEW.`id`,
            JSON_OBJECT('id', OLD.`id`,'sl', OLD.`sl`,'title', OLD.`title`,'ranks', OLD.`ranks`),
            JSON_OBJECT('id', NEW.`id`,'sl', NEW.`sl`,'title', NEW.`title`,'ranks', NEW.`ranks`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER designation_after_delete
    AFTER DELETE ON `designation`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, changed_by)
        VALUES(
            'designation',
            'DELETE',
            OLD.`id`,
            JSON_OBJECT('id', OLD.`id`,'sl', OLD.`sl`,'title', OLD.`title`,'ranks', OLD.`ranks`),
            COALESCE(@current_user,'system')
        );
    END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `diagnosis_results`
--

DROP TABLE IF EXISTS `diagnosis_results`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `diagnosis_results` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sccode` int(11) NOT NULL,
  `stid` varchar(20) NOT NULL,
  `topic_code` varchar(50) NOT NULL,
  `score` decimal(5,2) NOT NULL,
  `max_score` decimal(5,2) DEFAULT NULL,
  `level` enum('weak','average','strong') NOT NULL,
  `attempted_at` datetime NOT NULL DEFAULT current_timestamp(),
  `details` text DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
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
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER diagnosis_results_after_insert
    AFTER INSERT ON `diagnosis_results`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, new_data, changed_by)
        VALUES(
            'diagnosis_results',
            'INSERT',
            NEW.`id`,
            JSON_OBJECT('id', NEW.`id`,'sccode', NEW.`sccode`,'stid', NEW.`stid`,'topic_code', NEW.`topic_code`,'score', NEW.`score`,'max_score', NEW.`max_score`,'level', NEW.`level`,'attempted_at', NEW.`attempted_at`,'details', NEW.`details`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER diagnosis_results_after_update
    AFTER UPDATE ON `diagnosis_results`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, new_data, changed_by)
        VALUES(
            'diagnosis_results',
            'UPDATE',
            NEW.`id`,
            JSON_OBJECT('id', OLD.`id`,'sccode', OLD.`sccode`,'stid', OLD.`stid`,'topic_code', OLD.`topic_code`,'score', OLD.`score`,'max_score', OLD.`max_score`,'level', OLD.`level`,'attempted_at', OLD.`attempted_at`,'details', OLD.`details`),
            JSON_OBJECT('id', NEW.`id`,'sccode', NEW.`sccode`,'stid', NEW.`stid`,'topic_code', NEW.`topic_code`,'score', NEW.`score`,'max_score', NEW.`max_score`,'level', NEW.`level`,'attempted_at', NEW.`attempted_at`,'details', NEW.`details`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER diagnosis_results_after_delete
    AFTER DELETE ON `diagnosis_results`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, changed_by)
        VALUES(
            'diagnosis_results',
            'DELETE',
            OLD.`id`,
            JSON_OBJECT('id', OLD.`id`,'sccode', OLD.`sccode`,'stid', OLD.`stid`,'topic_code', OLD.`topic_code`,'score', OLD.`score`,'max_score', OLD.`max_score`,'level', OLD.`level`,'attempted_at', OLD.`attempted_at`,'details', OLD.`details`),
            COALESCE(@current_user,'system')
        );
    END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

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
) ENGINE=InnoDB AUTO_INCREMENT=77 DEFAULT CHARSET=utf32 COLLATE=utf32_unicode_ci;
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
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER examlist_after_insert
    AFTER INSERT ON `examlist`
    FOR EACH ROW
    BEGIN
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER examlist_after_update
    AFTER UPDATE ON `examlist`
    FOR EACH ROW
    BEGIN
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER examlist_after_delete
    AFTER DELETE ON `examlist`
    FOR EACH ROW
    BEGIN
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
-- Table structure for table `examname`
--

DROP TABLE IF EXISTS `examname`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `examname` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `examtitle` varchar(30) NOT NULL,
  `examdisplayname` varchar(30) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
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
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER examname_after_insert
    AFTER INSERT ON `examname`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, new_data, changed_by)
        VALUES(
            'examname',
            'INSERT',
            NEW.`id`,
            JSON_OBJECT('id', NEW.`id`,'examtitle', NEW.`examtitle`,'examdisplayname', NEW.`examdisplayname`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER examname_after_update
    AFTER UPDATE ON `examname`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, new_data, changed_by)
        VALUES(
            'examname',
            'UPDATE',
            NEW.`id`,
            JSON_OBJECT('id', OLD.`id`,'examtitle', OLD.`examtitle`,'examdisplayname', OLD.`examdisplayname`),
            JSON_OBJECT('id', NEW.`id`,'examtitle', NEW.`examtitle`,'examdisplayname', NEW.`examdisplayname`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER examname_after_delete
    AFTER DELETE ON `examname`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, changed_by)
        VALUES(
            'examname',
            'DELETE',
            OLD.`id`,
            JSON_OBJECT('id', OLD.`id`,'examtitle', OLD.`examtitle`,'examdisplayname', OLD.`examdisplayname`),
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
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `examroutine` (
  `sessionyear` int(11) NOT NULL,
  `examname` text NOT NULL,
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sccode` int(11) NOT NULL,
  `date` date NOT NULL,
  `time` time NOT NULL,
  `clsname` varchar(30) NOT NULL,
  `secname` varchar(50) NOT NULL,
  `subcode` int(11) DEFAULT NULL,
  `subj` varchar(100) NOT NULL,
  `progress` int(11) NOT NULL DEFAULT 0,
  `modifieddate` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1795 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
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
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER examroutine_after_insert
    AFTER INSERT ON `examroutine`
    FOR EACH ROW
    BEGIN
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
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER examroutine_after_update
    AFTER UPDATE ON `examroutine`
    FOR EACH ROW
    BEGIN
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
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER examroutine_after_delete
    AFTER DELETE ON `examroutine`
    FOR EACH ROW
    BEGIN
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
-- Table structure for table `feedback_logs`
--

DROP TABLE IF EXISTS `feedback_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `feedback_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sccode` int(11) NOT NULL,
  `stid` varchar(20) NOT NULL,
  `quiz_id` int(11) NOT NULL,
  `topic_code` varchar(50) DEFAULT NULL,
  `score` decimal(5,2) NOT NULL,
  `max_score` decimal(5,2) DEFAULT NULL,
  `feedback_text` text DEFAULT NULL,
  `provided_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=51 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
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
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER feedback_logs_after_insert
    AFTER INSERT ON `feedback_logs`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, new_data, changed_by)
        VALUES(
            'feedback_logs',
            'INSERT',
            NEW.`id`,
            JSON_OBJECT('id', NEW.`id`,'sccode', NEW.`sccode`,'stid', NEW.`stid`,'quiz_id', NEW.`quiz_id`,'topic_code', NEW.`topic_code`,'score', NEW.`score`,'max_score', NEW.`max_score`,'feedback_text', NEW.`feedback_text`,'provided_at', NEW.`provided_at`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER feedback_logs_after_update
    AFTER UPDATE ON `feedback_logs`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, new_data, changed_by)
        VALUES(
            'feedback_logs',
            'UPDATE',
            NEW.`id`,
            JSON_OBJECT('id', OLD.`id`,'sccode', OLD.`sccode`,'stid', OLD.`stid`,'quiz_id', OLD.`quiz_id`,'topic_code', OLD.`topic_code`,'score', OLD.`score`,'max_score', OLD.`max_score`,'feedback_text', OLD.`feedback_text`,'provided_at', OLD.`provided_at`),
            JSON_OBJECT('id', NEW.`id`,'sccode', NEW.`sccode`,'stid', NEW.`stid`,'quiz_id', NEW.`quiz_id`,'topic_code', NEW.`topic_code`,'score', NEW.`score`,'max_score', NEW.`max_score`,'feedback_text', NEW.`feedback_text`,'provided_at', NEW.`provided_at`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER feedback_logs_after_delete
    AFTER DELETE ON `feedback_logs`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, changed_by)
        VALUES(
            'feedback_logs',
            'DELETE',
            OLD.`id`,
            JSON_OBJECT('id', OLD.`id`,'sccode', OLD.`sccode`,'stid', OLD.`stid`,'quiz_id', OLD.`quiz_id`,'topic_code', OLD.`topic_code`,'score', OLD.`score`,'max_score', OLD.`max_score`,'feedback_text', OLD.`feedback_text`,'provided_at', OLD.`provided_at`),
            COALESCE(@current_user,'system')
        );
    END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `filelist`
--

DROP TABLE IF EXISTS `filelist`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `filelist` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `filename` varchar(100) DEFAULT NULL,
  `attendance` int(11) NOT NULL DEFAULT 0,
  `payment` int(11) NOT NULL DEFAULT 0,
  `result` int(11) NOT NULL DEFAULT 0,
  `admin` int(11) NOT NULL DEFAULT 0,
  `updatetime` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf32 COLLATE=utf32_unicode_ci;
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
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER filelist_after_insert
    AFTER INSERT ON `filelist`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, new_data, changed_by)
        VALUES(
            'filelist',
            'INSERT',
            NEW.`id`,
            JSON_OBJECT('id', NEW.`id`,'filename', NEW.`filename`,'attendance', NEW.`attendance`,'payment', NEW.`payment`,'result', NEW.`result`,'admin', NEW.`admin`,'updatetime', NEW.`updatetime`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER filelist_after_update
    AFTER UPDATE ON `filelist`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, new_data, changed_by)
        VALUES(
            'filelist',
            'UPDATE',
            NEW.`id`,
            JSON_OBJECT('id', OLD.`id`,'filename', OLD.`filename`,'attendance', OLD.`attendance`,'payment', OLD.`payment`,'result', OLD.`result`,'admin', OLD.`admin`,'updatetime', OLD.`updatetime`),
            JSON_OBJECT('id', NEW.`id`,'filename', NEW.`filename`,'attendance', NEW.`attendance`,'payment', NEW.`payment`,'result', NEW.`result`,'admin', NEW.`admin`,'updatetime', NEW.`updatetime`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER filelist_after_delete
    AFTER DELETE ON `filelist`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, changed_by)
        VALUES(
            'filelist',
            'DELETE',
            OLD.`id`,
            JSON_OBJECT('id', OLD.`id`,'filename', OLD.`filename`,'attendance', OLD.`attendance`,'payment', OLD.`payment`,'result', OLD.`result`,'admin', OLD.`admin`,'updatetime', OLD.`updatetime`),
            COALESCE(@current_user,'system')
        );
    END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `financeitem`
--

DROP TABLE IF EXISTS `financeitem`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `financeitem` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `slno` int(11) DEFAULT NULL,
  `particulareng` varchar(255) DEFAULT NULL,
  `particularben` varchar(255) DEFAULT NULL,
  `month` int(11) NOT NULL DEFAULT 0,
  `payment` int(11) NOT NULL DEFAULT 1,
  `income` int(11) NOT NULL DEFAULT 0,
  `expenditure` int(11) NOT NULL DEFAULT 0,
  `sccode` int(11) NOT NULL DEFAULT 0,
  `icon` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=119 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
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
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER financeitem_after_insert
    AFTER INSERT ON `financeitem`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, new_data, changed_by)
        VALUES(
            'financeitem',
            'INSERT',
            NEW.`id`,
            JSON_OBJECT('id', NEW.`id`,'slno', NEW.`slno`,'particulareng', NEW.`particulareng`,'particularben', NEW.`particularben`,'month', NEW.`month`,'payment', NEW.`payment`,'income', NEW.`income`,'expenditure', NEW.`expenditure`,'sccode', NEW.`sccode`,'icon', NEW.`icon`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER financeitem_after_update
    AFTER UPDATE ON `financeitem`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, new_data, changed_by)
        VALUES(
            'financeitem',
            'UPDATE',
            NEW.`id`,
            JSON_OBJECT('id', OLD.`id`,'slno', OLD.`slno`,'particulareng', OLD.`particulareng`,'particularben', OLD.`particularben`,'month', OLD.`month`,'payment', OLD.`payment`,'income', OLD.`income`,'expenditure', OLD.`expenditure`,'sccode', OLD.`sccode`,'icon', OLD.`icon`),
            JSON_OBJECT('id', NEW.`id`,'slno', NEW.`slno`,'particulareng', NEW.`particulareng`,'particularben', NEW.`particularben`,'month', NEW.`month`,'payment', NEW.`payment`,'income', NEW.`income`,'expenditure', NEW.`expenditure`,'sccode', NEW.`sccode`,'icon', NEW.`icon`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER financeitem_after_delete
    AFTER DELETE ON `financeitem`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, changed_by)
        VALUES(
            'financeitem',
            'DELETE',
            OLD.`id`,
            JSON_OBJECT('id', OLD.`id`,'slno', OLD.`slno`,'particulareng', OLD.`particulareng`,'particularben', OLD.`particularben`,'month', OLD.`month`,'payment', OLD.`payment`,'income', OLD.`income`,'expenditure', OLD.`expenditure`,'sccode', OLD.`sccode`,'icon', OLD.`icon`),
            COALESCE(@current_user,'system')
        );
    END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

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
) ENGINE=InnoDB AUTO_INCREMENT=574 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
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
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER financesetup_after_insert
    AFTER INSERT ON `financesetup`
    FOR EACH ROW
    BEGIN
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER financesetup_after_update
    AFTER UPDATE ON `financesetup`
    FOR EACH ROW
    BEGIN
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER financesetup_after_delete
    AFTER DELETE ON `financesetup`
    FOR EACH ROW
    BEGIN
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
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `financesetupind` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sccode` int(11) NOT NULL,
  `slot` varchar(25) NOT NULL DEFAULT 'School',
  `sessionyear` varchar(7) DEFAULT NULL,
  `stid` bigint(20) DEFAULT NULL,
  `slno` int(11) DEFAULT NULL,
  `itemcode` varchar(32) DEFAULT NULL,
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
) ENGINE=InnoDB AUTO_INCREMENT=395 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
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
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER financesetupind_after_insert
    AFTER INSERT ON `financesetupind`
    FOR EACH ROW
    BEGIN
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
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER financesetupind_after_update
    AFTER UPDATE ON `financesetupind`
    FOR EACH ROW
    BEGIN
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
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER financesetupind_after_delete
    AFTER DELETE ON `financesetupind`
    FOR EACH ROW
    BEGIN
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
) ENGINE=InnoDB AUTO_INCREMENT=201 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
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
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER financesetupvalue_after_insert
    AFTER INSERT ON `financesetupvalue`
    FOR EACH ROW
    BEGIN
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER financesetupvalue_after_update
    AFTER UPDATE ON `financesetupvalue`
    FOR EACH ROW
    BEGIN
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER financesetupvalue_after_delete
    AFTER DELETE ON `financesetupvalue`
    FOR EACH ROW
    BEGIN
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
-- Table structure for table `georecord`
--

DROP TABLE IF EXISTS `georecord`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `georecord` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(200) DEFAULT NULL,
  `posx` double DEFAULT NULL,
  `posy` double DEFAULT NULL,
  `recordtime` datetime DEFAULT NULL,
  `distance` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5662 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
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
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER georecord_after_insert
    AFTER INSERT ON `georecord`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, new_data, changed_by)
        VALUES(
            'georecord',
            'INSERT',
            NEW.`id`,
            JSON_OBJECT('id', NEW.`id`,'email', NEW.`email`,'posx', NEW.`posx`,'posy', NEW.`posy`,'recordtime', NEW.`recordtime`,'distance', NEW.`distance`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER georecord_after_update
    AFTER UPDATE ON `georecord`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, new_data, changed_by)
        VALUES(
            'georecord',
            'UPDATE',
            NEW.`id`,
            JSON_OBJECT('id', OLD.`id`,'email', OLD.`email`,'posx', OLD.`posx`,'posy', OLD.`posy`,'recordtime', OLD.`recordtime`,'distance', OLD.`distance`),
            JSON_OBJECT('id', NEW.`id`,'email', NEW.`email`,'posx', NEW.`posx`,'posy', NEW.`posy`,'recordtime', NEW.`recordtime`,'distance', NEW.`distance`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER georecord_after_delete
    AFTER DELETE ON `georecord`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, changed_by)
        VALUES(
            'georecord',
            'DELETE',
            OLD.`id`,
            JSON_OBJECT('id', OLD.`id`,'email', OLD.`email`,'posx', OLD.`posx`,'posy', OLD.`posy`,'recordtime', OLD.`recordtime`,'distance', OLD.`distance`),
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
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
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
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER globalsettings_after_insert
    AFTER INSERT ON `globalsettings`
    FOR EACH ROW
    BEGIN
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER globalsettings_after_update
    AFTER UPDATE ON `globalsettings`
    FOR EACH ROW
    BEGIN
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER globalsettings_after_delete
    AFTER DELETE ON `globalsettings`
    FOR EACH ROW
    BEGIN
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
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `gpa` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sccode` int(11) DEFAULT NULL,
  `slot` varchar(20) DEFAULT NULL,
  `minvalues` int(11) NOT NULL,
  `maxvalues` int(11) NOT NULL,
  `gp` float NOT NULL,
  `gl` varchar(3) NOT NULL,
  `remark` varchar(50) NOT NULL,
  `colorcode` varchar(6) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
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
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER gpa_after_insert
    AFTER INSERT ON `gpa`
    FOR EACH ROW
    BEGIN
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
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER gpa_after_update
    AFTER UPDATE ON `gpa`
    FOR EACH ROW
    BEGIN
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
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER gpa_after_delete
    AFTER DELETE ON `gpa`
    FOR EACH ROW
    BEGIN
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
-- Table structure for table `guar_student`
--

DROP TABLE IF EXISTS `guar_student`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `guar_student` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sccode` int(11) DEFAULT NULL,
  `guarid` bigint(20) DEFAULT NULL,
  `stid` bigint(20) DEFAULT NULL,
  `bindby` varchar(120) DEFAULT NULL,
  `bindmethod` varchar(20) DEFAULT NULL,
  `bindtime` datetime DEFAULT NULL,
  `status` int(11) DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
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
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER guar_student_after_insert
    AFTER INSERT ON `guar_student`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, new_data, changed_by)
        VALUES(
            'guar_student',
            'INSERT',
            NEW.`id`,
            JSON_OBJECT('id', NEW.`id`,'sccode', NEW.`sccode`,'guarid', NEW.`guarid`,'stid', NEW.`stid`,'bindby', NEW.`bindby`,'bindmethod', NEW.`bindmethod`,'bindtime', NEW.`bindtime`,'status', NEW.`status`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER guar_student_after_update
    AFTER UPDATE ON `guar_student`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, new_data, changed_by)
        VALUES(
            'guar_student',
            'UPDATE',
            NEW.`id`,
            JSON_OBJECT('id', OLD.`id`,'sccode', OLD.`sccode`,'guarid', OLD.`guarid`,'stid', OLD.`stid`,'bindby', OLD.`bindby`,'bindmethod', OLD.`bindmethod`,'bindtime', OLD.`bindtime`,'status', OLD.`status`),
            JSON_OBJECT('id', NEW.`id`,'sccode', NEW.`sccode`,'guarid', NEW.`guarid`,'stid', NEW.`stid`,'bindby', NEW.`bindby`,'bindmethod', NEW.`bindmethod`,'bindtime', NEW.`bindtime`,'status', NEW.`status`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER guar_student_after_delete
    AFTER DELETE ON `guar_student`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, changed_by)
        VALUES(
            'guar_student',
            'DELETE',
            OLD.`id`,
            JSON_OBJECT('id', OLD.`id`,'sccode', OLD.`sccode`,'guarid', OLD.`guarid`,'stid', OLD.`stid`,'bindby', OLD.`bindby`,'bindmethod', OLD.`bindmethod`,'bindtime', OLD.`bindtime`,'status', OLD.`status`),
            COALESCE(@current_user,'system')
        );
    END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `holiday`
--

DROP TABLE IF EXISTS `holiday`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `holiday` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sccode` int(11) NOT NULL,
  `date` date NOT NULL,
  `reason` varchar(150) NOT NULL,
  `hdtype` varchar(12) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=156 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
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
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER holiday_after_insert
    AFTER INSERT ON `holiday`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, new_data, changed_by)
        VALUES(
            'holiday',
            'INSERT',
            NEW.`id`,
            JSON_OBJECT('id', NEW.`id`,'sccode', NEW.`sccode`,'date', NEW.`date`,'reason', NEW.`reason`,'hdtype', NEW.`hdtype`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER holiday_after_update
    AFTER UPDATE ON `holiday`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, new_data, changed_by)
        VALUES(
            'holiday',
            'UPDATE',
            NEW.`id`,
            JSON_OBJECT('id', OLD.`id`,'sccode', OLD.`sccode`,'date', OLD.`date`,'reason', OLD.`reason`,'hdtype', OLD.`hdtype`),
            JSON_OBJECT('id', NEW.`id`,'sccode', NEW.`sccode`,'date', NEW.`date`,'reason', NEW.`reason`,'hdtype', NEW.`hdtype`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER holiday_after_delete
    AFTER DELETE ON `holiday`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, changed_by)
        VALUES(
            'holiday',
            'DELETE',
            OLD.`id`,
            JSON_OBJECT('id', OLD.`id`,'sccode', OLD.`sccode`,'date', OLD.`date`,'reason', OLD.`reason`,'hdtype', OLD.`hdtype`),
            COALESCE(@current_user,'system')
        );
    END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `indivisualrecord`
--

DROP TABLE IF EXISTS `indivisualrecord`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `indivisualrecord` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sccode` bigint(20) DEFAULT NULL,
  `stid` varchar(10) NOT NULL,
  `chkdate` date NOT NULL,
  `cnt` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=460463 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
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
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER indivisualrecord_after_insert
    AFTER INSERT ON `indivisualrecord`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, new_data, changed_by)
        VALUES(
            'indivisualrecord',
            'INSERT',
            NEW.`id`,
            JSON_OBJECT('id', NEW.`id`,'sccode', NEW.`sccode`,'stid', NEW.`stid`,'chkdate', NEW.`chkdate`,'cnt', NEW.`cnt`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER indivisualrecord_after_update
    AFTER UPDATE ON `indivisualrecord`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, new_data, changed_by)
        VALUES(
            'indivisualrecord',
            'UPDATE',
            NEW.`id`,
            JSON_OBJECT('id', OLD.`id`,'sccode', OLD.`sccode`,'stid', OLD.`stid`,'chkdate', OLD.`chkdate`,'cnt', OLD.`cnt`),
            JSON_OBJECT('id', NEW.`id`,'sccode', NEW.`sccode`,'stid', NEW.`stid`,'chkdate', NEW.`chkdate`,'cnt', NEW.`cnt`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER indivisualrecord_after_delete
    AFTER DELETE ON `indivisualrecord`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, changed_by)
        VALUES(
            'indivisualrecord',
            'DELETE',
            OLD.`id`,
            JSON_OBJECT('id', OLD.`id`,'sccode', OLD.`sccode`,'stid', OLD.`stid`,'chkdate', OLD.`chkdate`,'cnt', OLD.`cnt`),
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
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `inoutdata` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sccode` int(11) NOT NULL,
  `stid` varchar(20) NOT NULL,
  `datetime` varchar(20) NOT NULL,
  `chkdate` date NOT NULL,
  `chktime` time NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=24233419 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
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
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER inoutdata_after_insert
    AFTER INSERT ON `inoutdata`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, new_data, changed_by)
        VALUES(
            'inoutdata',
            'INSERT',
            NEW.`id`,
            JSON_OBJECT('id', NEW.`id`,'sccode', NEW.`sccode`,'stid', NEW.`stid`,'datetime', NEW.`datetime`,'chkdate', NEW.`chkdate`,'chktime', NEW.`chktime`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER inoutdata_after_update
    AFTER UPDATE ON `inoutdata`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, new_data, changed_by)
        VALUES(
            'inoutdata',
            'UPDATE',
            NEW.`id`,
            JSON_OBJECT('id', OLD.`id`,'sccode', OLD.`sccode`,'stid', OLD.`stid`,'datetime', OLD.`datetime`,'chkdate', OLD.`chkdate`,'chktime', OLD.`chktime`),
            JSON_OBJECT('id', NEW.`id`,'sccode', NEW.`sccode`,'stid', NEW.`stid`,'datetime', NEW.`datetime`,'chkdate', NEW.`chkdate`,'chktime', NEW.`chktime`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER inoutdata_after_delete
    AFTER DELETE ON `inoutdata`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, changed_by)
        VALUES(
            'inoutdata',
            'DELETE',
            OLD.`id`,
            JSON_OBJECT('id', OLD.`id`,'sccode', OLD.`sccode`,'stid', OLD.`stid`,'datetime', OLD.`datetime`,'chkdate', OLD.`chkdate`,'chktime', OLD.`chktime`),
            COALESCE(@current_user,'system')
        );
    END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `inoutdate`
--

DROP TABLE IF EXISTS `inoutdate`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `inoutdate` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sccode` int(11) NOT NULL,
  `date` date NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1137 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
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
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER inoutdate_after_insert
    AFTER INSERT ON `inoutdate`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, new_data, changed_by)
        VALUES(
            'inoutdate',
            'INSERT',
            NEW.`id`,
            JSON_OBJECT('id', NEW.`id`,'sccode', NEW.`sccode`,'date', NEW.`date`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER inoutdate_after_update
    AFTER UPDATE ON `inoutdate`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, new_data, changed_by)
        VALUES(
            'inoutdate',
            'UPDATE',
            NEW.`id`,
            JSON_OBJECT('id', OLD.`id`,'sccode', OLD.`sccode`,'date', OLD.`date`),
            JSON_OBJECT('id', NEW.`id`,'sccode', NEW.`sccode`,'date', NEW.`date`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER inoutdate_after_delete
    AFTER DELETE ON `inoutdate`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, changed_by)
        VALUES(
            'inoutdate',
            'DELETE',
            OLD.`id`,
            JSON_OBJECT('id', OLD.`id`,'sccode', OLD.`sccode`,'date', OLD.`date`),
            COALESCE(@current_user,'system')
        );
    END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `issue`
--

DROP TABLE IF EXISTS `issue`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `issue` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category` varchar(50) NOT NULL,
  `description` varchar(150) NOT NULL,
  `deadline` date NOT NULL,
  `issueby` varchar(120) NOT NULL,
  `issuetime` datetime NOT NULL,
  `progress` int(11) NOT NULL,
  `status` varchar(30) NOT NULL,
  `stt` int(11) NOT NULL DEFAULT 0,
  `progby` varchar(120) NOT NULL,
  `progtime` datetime NOT NULL,
  `icon` varchar(10) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=33 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
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
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER issue_after_insert
    AFTER INSERT ON `issue`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, new_data, changed_by)
        VALUES(
            'issue',
            'INSERT',
            NEW.`id`,
            JSON_OBJECT('id', NEW.`id`,'category', NEW.`category`,'description', NEW.`description`,'deadline', NEW.`deadline`,'issueby', NEW.`issueby`,'issuetime', NEW.`issuetime`,'progress', NEW.`progress`,'status', NEW.`status`,'stt', NEW.`stt`,'progby', NEW.`progby`,'progtime', NEW.`progtime`,'icon', NEW.`icon`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER issue_after_update
    AFTER UPDATE ON `issue`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, new_data, changed_by)
        VALUES(
            'issue',
            'UPDATE',
            NEW.`id`,
            JSON_OBJECT('id', OLD.`id`,'category', OLD.`category`,'description', OLD.`description`,'deadline', OLD.`deadline`,'issueby', OLD.`issueby`,'issuetime', OLD.`issuetime`,'progress', OLD.`progress`,'status', OLD.`status`,'stt', OLD.`stt`,'progby', OLD.`progby`,'progtime', OLD.`progtime`,'icon', OLD.`icon`),
            JSON_OBJECT('id', NEW.`id`,'category', NEW.`category`,'description', NEW.`description`,'deadline', NEW.`deadline`,'issueby', NEW.`issueby`,'issuetime', NEW.`issuetime`,'progress', NEW.`progress`,'status', NEW.`status`,'stt', NEW.`stt`,'progby', NEW.`progby`,'progtime', NEW.`progtime`,'icon', NEW.`icon`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER issue_after_delete
    AFTER DELETE ON `issue`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, changed_by)
        VALUES(
            'issue',
            'DELETE',
            OLD.`id`,
            JSON_OBJECT('id', OLD.`id`,'category', OLD.`category`,'description', OLD.`description`,'deadline', OLD.`deadline`,'issueby', OLD.`issueby`,'issuetime', OLD.`issuetime`,'progress', OLD.`progress`,'status', OLD.`status`,'stt', OLD.`stt`,'progby', OLD.`progby`,'progtime', OLD.`progtime`,'icon', OLD.`icon`),
            COALESCE(@current_user,'system')
        );
    END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `kbase1`
--

DROP TABLE IF EXISTS `kbase1`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kbase1` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `icon` varchar(50) DEFAULT NULL,
  `sl` int(11) NOT NULL DEFAULT 0,
  `title` varchar(255) DEFAULT NULL,
  `createdate` datetime DEFAULT NULL,
  `modifieddate` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
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
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER kbase1_after_insert
    AFTER INSERT ON `kbase1`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, new_data, changed_by)
        VALUES(
            'kbase1',
            'INSERT',
            NEW.`id`,
            JSON_OBJECT('id', NEW.`id`,'icon', NEW.`icon`,'sl', NEW.`sl`,'title', NEW.`title`,'createdate', NEW.`createdate`,'modifieddate', NEW.`modifieddate`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER kbase1_after_update
    AFTER UPDATE ON `kbase1`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, new_data, changed_by)
        VALUES(
            'kbase1',
            'UPDATE',
            NEW.`id`,
            JSON_OBJECT('id', OLD.`id`,'icon', OLD.`icon`,'sl', OLD.`sl`,'title', OLD.`title`,'createdate', OLD.`createdate`,'modifieddate', OLD.`modifieddate`),
            JSON_OBJECT('id', NEW.`id`,'icon', NEW.`icon`,'sl', NEW.`sl`,'title', NEW.`title`,'createdate', NEW.`createdate`,'modifieddate', NEW.`modifieddate`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER kbase1_after_delete
    AFTER DELETE ON `kbase1`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, changed_by)
        VALUES(
            'kbase1',
            'DELETE',
            OLD.`id`,
            JSON_OBJECT('id', OLD.`id`,'icon', OLD.`icon`,'sl', OLD.`sl`,'title', OLD.`title`,'createdate', OLD.`createdate`,'modifieddate', OLD.`modifieddate`),
            COALESCE(@current_user,'system')
        );
    END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `kbase2`
--

DROP TABLE IF EXISTS `kbase2`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kbase2` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sl` int(11) NOT NULL DEFAULT 0,
  `kbase1` int(11) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `createdate` datetime DEFAULT NULL,
  `modifieddate` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
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
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER kbase2_after_insert
    AFTER INSERT ON `kbase2`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, new_data, changed_by)
        VALUES(
            'kbase2',
            'INSERT',
            NEW.`id`,
            JSON_OBJECT('id', NEW.`id`,'sl', NEW.`sl`,'kbase1', NEW.`kbase1`,'title', NEW.`title`,'createdate', NEW.`createdate`,'modifieddate', NEW.`modifieddate`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER kbase2_after_update
    AFTER UPDATE ON `kbase2`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, new_data, changed_by)
        VALUES(
            'kbase2',
            'UPDATE',
            NEW.`id`,
            JSON_OBJECT('id', OLD.`id`,'sl', OLD.`sl`,'kbase1', OLD.`kbase1`,'title', OLD.`title`,'createdate', OLD.`createdate`,'modifieddate', OLD.`modifieddate`),
            JSON_OBJECT('id', NEW.`id`,'sl', NEW.`sl`,'kbase1', NEW.`kbase1`,'title', NEW.`title`,'createdate', NEW.`createdate`,'modifieddate', NEW.`modifieddate`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER kbase2_after_delete
    AFTER DELETE ON `kbase2`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, changed_by)
        VALUES(
            'kbase2',
            'DELETE',
            OLD.`id`,
            JSON_OBJECT('id', OLD.`id`,'sl', OLD.`sl`,'kbase1', OLD.`kbase1`,'title', OLD.`title`,'createdate', OLD.`createdate`,'modifieddate', OLD.`modifieddate`),
            COALESCE(@current_user,'system')
        );
    END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `kbase3`
--

DROP TABLE IF EXISTS `kbase3`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kbase3` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sl` int(11) NOT NULL DEFAULT 0,
  `kbase1` int(11) DEFAULT NULL,
  `kbase2` int(11) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `createdate` datetime DEFAULT NULL,
  `modifieddate` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
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
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER kbase3_after_insert
    AFTER INSERT ON `kbase3`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, new_data, changed_by)
        VALUES(
            'kbase3',
            'INSERT',
            NEW.`id`,
            JSON_OBJECT('id', NEW.`id`,'sl', NEW.`sl`,'kbase1', NEW.`kbase1`,'kbase2', NEW.`kbase2`,'title', NEW.`title`,'createdate', NEW.`createdate`,'modifieddate', NEW.`modifieddate`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER kbase3_after_update
    AFTER UPDATE ON `kbase3`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, new_data, changed_by)
        VALUES(
            'kbase3',
            'UPDATE',
            NEW.`id`,
            JSON_OBJECT('id', OLD.`id`,'sl', OLD.`sl`,'kbase1', OLD.`kbase1`,'kbase2', OLD.`kbase2`,'title', OLD.`title`,'createdate', OLD.`createdate`,'modifieddate', OLD.`modifieddate`),
            JSON_OBJECT('id', NEW.`id`,'sl', NEW.`sl`,'kbase1', NEW.`kbase1`,'kbase2', NEW.`kbase2`,'title', NEW.`title`,'createdate', NEW.`createdate`,'modifieddate', NEW.`modifieddate`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER kbase3_after_delete
    AFTER DELETE ON `kbase3`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, changed_by)
        VALUES(
            'kbase3',
            'DELETE',
            OLD.`id`,
            JSON_OBJECT('id', OLD.`id`,'sl', OLD.`sl`,'kbase1', OLD.`kbase1`,'kbase2', OLD.`kbase2`,'title', OLD.`title`,'createdate', OLD.`createdate`,'modifieddate', OLD.`modifieddate`),
            COALESCE(@current_user,'system')
        );
    END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `kbase4`
--

DROP TABLE IF EXISTS `kbase4`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kbase4` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sl` int(11) NOT NULL DEFAULT 0,
  `kbase1` int(11) DEFAULT NULL,
  `kbase2` int(11) DEFAULT NULL,
  `kbase3` int(11) DEFAULT NULL,
  `stepid` int(11) DEFAULT NULL,
  `descrip` varchar(1000) DEFAULT NULL,
  `pic` int(11) NOT NULL DEFAULT 0,
  `status` int(11) NOT NULL DEFAULT 1,
  `createdate` datetime DEFAULT NULL,
  `modifieddate` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=35 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
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
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER kbase4_after_insert
    AFTER INSERT ON `kbase4`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, new_data, changed_by)
        VALUES(
            'kbase4',
            'INSERT',
            NEW.`id`,
            JSON_OBJECT('id', NEW.`id`,'sl', NEW.`sl`,'kbase1', NEW.`kbase1`,'kbase2', NEW.`kbase2`,'kbase3', NEW.`kbase3`,'stepid', NEW.`stepid`,'descrip', NEW.`descrip`,'pic', NEW.`pic`,'status', NEW.`status`,'createdate', NEW.`createdate`,'modifieddate', NEW.`modifieddate`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER kbase4_after_update
    AFTER UPDATE ON `kbase4`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, new_data, changed_by)
        VALUES(
            'kbase4',
            'UPDATE',
            NEW.`id`,
            JSON_OBJECT('id', OLD.`id`,'sl', OLD.`sl`,'kbase1', OLD.`kbase1`,'kbase2', OLD.`kbase2`,'kbase3', OLD.`kbase3`,'stepid', OLD.`stepid`,'descrip', OLD.`descrip`,'pic', OLD.`pic`,'status', OLD.`status`,'createdate', OLD.`createdate`,'modifieddate', OLD.`modifieddate`),
            JSON_OBJECT('id', NEW.`id`,'sl', NEW.`sl`,'kbase1', NEW.`kbase1`,'kbase2', NEW.`kbase2`,'kbase3', NEW.`kbase3`,'stepid', NEW.`stepid`,'descrip', NEW.`descrip`,'pic', NEW.`pic`,'status', NEW.`status`,'createdate', NEW.`createdate`,'modifieddate', NEW.`modifieddate`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER kbase4_after_delete
    AFTER DELETE ON `kbase4`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, changed_by)
        VALUES(
            'kbase4',
            'DELETE',
            OLD.`id`,
            JSON_OBJECT('id', OLD.`id`,'sl', OLD.`sl`,'kbase1', OLD.`kbase1`,'kbase2', OLD.`kbase2`,'kbase3', OLD.`kbase3`,'stepid', OLD.`stepid`,'descrip', OLD.`descrip`,'pic', OLD.`pic`,'status', OLD.`status`,'createdate', OLD.`createdate`,'modifieddate', OLD.`modifieddate`),
            COALESCE(@current_user,'system')
        );
    END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `kbasedone`
--

DROP TABLE IF EXISTS `kbasedone`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kbasedone` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sl` int(11) NOT NULL DEFAULT 0,
  `kbase1` int(11) DEFAULT NULL,
  `kbase2` int(11) DEFAULT NULL,
  `kbase3` int(11) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `times` int(11) DEFAULT 1,
  `lastlearn` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=39 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
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
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER kbasedone_after_insert
    AFTER INSERT ON `kbasedone`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, new_data, changed_by)
        VALUES(
            'kbasedone',
            'INSERT',
            NEW.`id`,
            JSON_OBJECT('id', NEW.`id`,'sl', NEW.`sl`,'kbase1', NEW.`kbase1`,'kbase2', NEW.`kbase2`,'kbase3', NEW.`kbase3`,'email', NEW.`email`,'times', NEW.`times`,'lastlearn', NEW.`lastlearn`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER kbasedone_after_update
    AFTER UPDATE ON `kbasedone`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, new_data, changed_by)
        VALUES(
            'kbasedone',
            'UPDATE',
            NEW.`id`,
            JSON_OBJECT('id', OLD.`id`,'sl', OLD.`sl`,'kbase1', OLD.`kbase1`,'kbase2', OLD.`kbase2`,'kbase3', OLD.`kbase3`,'email', OLD.`email`,'times', OLD.`times`,'lastlearn', OLD.`lastlearn`),
            JSON_OBJECT('id', NEW.`id`,'sl', NEW.`sl`,'kbase1', NEW.`kbase1`,'kbase2', NEW.`kbase2`,'kbase3', NEW.`kbase3`,'email', NEW.`email`,'times', NEW.`times`,'lastlearn', NEW.`lastlearn`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER kbasedone_after_delete
    AFTER DELETE ON `kbasedone`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, changed_by)
        VALUES(
            'kbasedone',
            'DELETE',
            OLD.`id`,
            JSON_OBJECT('id', OLD.`id`,'sl', OLD.`sl`,'kbase1', OLD.`kbase1`,'kbase2', OLD.`kbase2`,'kbase3', OLD.`kbase3`,'email', OLD.`email`,'times', OLD.`times`,'lastlearn', OLD.`lastlearn`),
            COALESCE(@current_user,'system')
        );
    END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `kbasestep`
--

DROP TABLE IF EXISTS `kbasestep`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kbasestep` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sl` int(11) NOT NULL DEFAULT 0,
  `kbase1` int(11) DEFAULT NULL,
  `kbase2` int(11) DEFAULT NULL,
  `kbase3` int(11) DEFAULT NULL,
  `step` varchar(255) DEFAULT NULL,
  `createdate` datetime DEFAULT NULL,
  `modifieddate` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
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
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER kbasestep_after_insert
    AFTER INSERT ON `kbasestep`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, new_data, changed_by)
        VALUES(
            'kbasestep',
            'INSERT',
            NEW.`id`,
            JSON_OBJECT('id', NEW.`id`,'sl', NEW.`sl`,'kbase1', NEW.`kbase1`,'kbase2', NEW.`kbase2`,'kbase3', NEW.`kbase3`,'step', NEW.`step`,'createdate', NEW.`createdate`,'modifieddate', NEW.`modifieddate`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER kbasestep_after_update
    AFTER UPDATE ON `kbasestep`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, new_data, changed_by)
        VALUES(
            'kbasestep',
            'UPDATE',
            NEW.`id`,
            JSON_OBJECT('id', OLD.`id`,'sl', OLD.`sl`,'kbase1', OLD.`kbase1`,'kbase2', OLD.`kbase2`,'kbase3', OLD.`kbase3`,'step', OLD.`step`,'createdate', OLD.`createdate`,'modifieddate', OLD.`modifieddate`),
            JSON_OBJECT('id', NEW.`id`,'sl', NEW.`sl`,'kbase1', NEW.`kbase1`,'kbase2', NEW.`kbase2`,'kbase3', NEW.`kbase3`,'step', NEW.`step`,'createdate', NEW.`createdate`,'modifieddate', NEW.`modifieddate`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER kbasestep_after_delete
    AFTER DELETE ON `kbasestep`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, changed_by)
        VALUES(
            'kbasestep',
            'DELETE',
            OLD.`id`,
            JSON_OBJECT('id', OLD.`id`,'sl', OLD.`sl`,'kbase1', OLD.`kbase1`,'kbase2', OLD.`kbase2`,'kbase3', OLD.`kbase3`,'step', OLD.`step`,'createdate', OLD.`createdate`,'modifieddate', OLD.`modifieddate`),
            COALESCE(@current_user,'system')
        );
    END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `learning_paths`
--

DROP TABLE IF EXISTS `learning_paths`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `learning_paths` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sccode` int(11) NOT NULL,
  `stid` varchar(20) NOT NULL,
  `topic_code` varchar(50) NOT NULL,
  `suggested_resource` varchar(255) NOT NULL,
  `resource_type` enum('video','pdf','quiz','text','interactive') DEFAULT 'pdf',
  `status` enum('pending','in_progress','completed') NOT NULL DEFAULT 'pending',
  `assigned_at` datetime NOT NULL DEFAULT current_timestamp(),
  `completed_at` datetime DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=738 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
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
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER learning_paths_after_insert
    AFTER INSERT ON `learning_paths`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, new_data, changed_by)
        VALUES(
            'learning_paths',
            'INSERT',
            NEW.`id`,
            JSON_OBJECT('id', NEW.`id`,'sccode', NEW.`sccode`,'stid', NEW.`stid`,'topic_code', NEW.`topic_code`,'suggested_resource', NEW.`suggested_resource`,'resource_type', NEW.`resource_type`,'status', NEW.`status`,'assigned_at', NEW.`assigned_at`,'completed_at', NEW.`completed_at`,'remarks', NEW.`remarks`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER learning_paths_after_update
    AFTER UPDATE ON `learning_paths`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, new_data, changed_by)
        VALUES(
            'learning_paths',
            'UPDATE',
            NEW.`id`,
            JSON_OBJECT('id', OLD.`id`,'sccode', OLD.`sccode`,'stid', OLD.`stid`,'topic_code', OLD.`topic_code`,'suggested_resource', OLD.`suggested_resource`,'resource_type', OLD.`resource_type`,'status', OLD.`status`,'assigned_at', OLD.`assigned_at`,'completed_at', OLD.`completed_at`,'remarks', OLD.`remarks`),
            JSON_OBJECT('id', NEW.`id`,'sccode', NEW.`sccode`,'stid', NEW.`stid`,'topic_code', NEW.`topic_code`,'suggested_resource', NEW.`suggested_resource`,'resource_type', NEW.`resource_type`,'status', NEW.`status`,'assigned_at', NEW.`assigned_at`,'completed_at', NEW.`completed_at`,'remarks', NEW.`remarks`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER learning_paths_after_delete
    AFTER DELETE ON `learning_paths`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, changed_by)
        VALUES(
            'learning_paths',
            'DELETE',
            OLD.`id`,
            JSON_OBJECT('id', OLD.`id`,'sccode', OLD.`sccode`,'stid', OLD.`stid`,'topic_code', OLD.`topic_code`,'suggested_resource', OLD.`suggested_resource`,'resource_type', OLD.`resource_type`,'status', OLD.`status`,'assigned_at', OLD.`assigned_at`,'completed_at', OLD.`completed_at`,'remarks', OLD.`remarks`),
            COALESCE(@current_user,'system')
        );
    END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `leaveapp`
--

DROP TABLE IF EXISTS `leaveapp`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `leaveapp` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tid` int(11) NOT NULL,
  `sccode` int(11) NOT NULL,
  `ldate` date NOT NULL,
  `applytime` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `entryby` varchar(30) NOT NULL,
  `status` varchar(20) NOT NULL,
  `leavetype` varchar(20) NOT NULL,
  `reason` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=73 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
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
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER leaveapp_after_insert
    AFTER INSERT ON `leaveapp`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, new_data, changed_by)
        VALUES(
            'leaveapp',
            'INSERT',
            NEW.`id`,
            JSON_OBJECT('id', NEW.`id`,'tid', NEW.`tid`,'sccode', NEW.`sccode`,'ldate', NEW.`ldate`,'applytime', NEW.`applytime`,'entryby', NEW.`entryby`,'status', NEW.`status`,'leavetype', NEW.`leavetype`,'reason', NEW.`reason`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER leaveapp_after_update
    AFTER UPDATE ON `leaveapp`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, new_data, changed_by)
        VALUES(
            'leaveapp',
            'UPDATE',
            NEW.`id`,
            JSON_OBJECT('id', OLD.`id`,'tid', OLD.`tid`,'sccode', OLD.`sccode`,'ldate', OLD.`ldate`,'applytime', OLD.`applytime`,'entryby', OLD.`entryby`,'status', OLD.`status`,'leavetype', OLD.`leavetype`,'reason', OLD.`reason`),
            JSON_OBJECT('id', NEW.`id`,'tid', NEW.`tid`,'sccode', NEW.`sccode`,'ldate', NEW.`ldate`,'applytime', NEW.`applytime`,'entryby', NEW.`entryby`,'status', NEW.`status`,'leavetype', NEW.`leavetype`,'reason', NEW.`reason`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER leaveapp_after_delete
    AFTER DELETE ON `leaveapp`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, changed_by)
        VALUES(
            'leaveapp',
            'DELETE',
            OLD.`id`,
            JSON_OBJECT('id', OLD.`id`,'tid', OLD.`tid`,'sccode', OLD.`sccode`,'ldate', OLD.`ldate`,'applytime', OLD.`applytime`,'entryby', OLD.`entryby`,'status', OLD.`status`,'leavetype', OLD.`leavetype`,'reason', OLD.`reason`),
            COALESCE(@current_user,'system')
        );
    END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `links`
--

DROP TABLE IF EXISTS `links`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `links` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `slno` int(11) DEFAULT NULL,
  `linkbn` varchar(255) DEFAULT NULL,
  `linken` varchar(255) DEFAULT NULL,
  `linkurl` varchar(255) DEFAULT NULL,
  `comment` varchar(255) DEFAULT NULL,
  `entrytime` datetime NOT NULL,
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER links_after_insert
    AFTER INSERT ON `links`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, new_data, changed_by)
        VALUES(
            'links',
            'INSERT',
            NEW.`id`,
            JSON_OBJECT('id', NEW.`id`,'slno', NEW.`slno`,'linkbn', NEW.`linkbn`,'linken', NEW.`linken`,'linkurl', NEW.`linkurl`,'comment', NEW.`comment`,'entrytime', NEW.`entrytime`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER links_after_update
    AFTER UPDATE ON `links`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, new_data, changed_by)
        VALUES(
            'links',
            'UPDATE',
            NEW.`id`,
            JSON_OBJECT('id', OLD.`id`,'slno', OLD.`slno`,'linkbn', OLD.`linkbn`,'linken', OLD.`linken`,'linkurl', OLD.`linkurl`,'comment', OLD.`comment`,'entrytime', OLD.`entrytime`),
            JSON_OBJECT('id', NEW.`id`,'slno', NEW.`slno`,'linkbn', NEW.`linkbn`,'linken', NEW.`linken`,'linkurl', NEW.`linkurl`,'comment', NEW.`comment`,'entrytime', NEW.`entrytime`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER links_after_delete
    AFTER DELETE ON `links`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, changed_by)
        VALUES(
            'links',
            'DELETE',
            OLD.`id`,
            JSON_OBJECT('id', OLD.`id`,'slno', OLD.`slno`,'linkbn', OLD.`linkbn`,'linken', OLD.`linken`,'linkurl', OLD.`linkurl`,'comment', OLD.`comment`,'entrytime', OLD.`entrytime`),
            COALESCE(@current_user,'system')
        );
    END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `loader`
--

DROP TABLE IF EXISTS `loader`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `loader` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `page` varchar(50) DEFAULT NULL,
  `title` varchar(100) DEFAULT NULL,
  `subtitle` varchar(200) DEFAULT NULL,
  `icon` varchar(50) DEFAULT NULL,
  `description` varchar(500) DEFAULT NULL,
  `progress` int(11) NOT NULL DEFAULT 0,
  `youtube` varchar(200) DEFAULT NULL,
  `docs` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
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
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER loader_after_insert
    AFTER INSERT ON `loader`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, new_data, changed_by)
        VALUES(
            'loader',
            'INSERT',
            NEW.`id`,
            JSON_OBJECT('id', NEW.`id`,'page', NEW.`page`,'title', NEW.`title`,'subtitle', NEW.`subtitle`,'icon', NEW.`icon`,'description', NEW.`description`,'progress', NEW.`progress`,'youtube', NEW.`youtube`,'docs', NEW.`docs`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER loader_after_update
    AFTER UPDATE ON `loader`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, new_data, changed_by)
        VALUES(
            'loader',
            'UPDATE',
            NEW.`id`,
            JSON_OBJECT('id', OLD.`id`,'page', OLD.`page`,'title', OLD.`title`,'subtitle', OLD.`subtitle`,'icon', OLD.`icon`,'description', OLD.`description`,'progress', OLD.`progress`,'youtube', OLD.`youtube`,'docs', OLD.`docs`),
            JSON_OBJECT('id', NEW.`id`,'page', NEW.`page`,'title', NEW.`title`,'subtitle', NEW.`subtitle`,'icon', NEW.`icon`,'description', NEW.`description`,'progress', NEW.`progress`,'youtube', NEW.`youtube`,'docs', NEW.`docs`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER loader_after_delete
    AFTER DELETE ON `loader`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, changed_by)
        VALUES(
            'loader',
            'DELETE',
            OLD.`id`,
            JSON_OBJECT('id', OLD.`id`,'page', OLD.`page`,'title', OLD.`title`,'subtitle', OLD.`subtitle`,'icon', OLD.`icon`,'description', OLD.`description`,'progress', OLD.`progress`,'youtube', OLD.`youtube`,'docs', OLD.`docs`),
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
  `duration` int(11) DEFAULT NULL,
  `filesize` float NOT NULL DEFAULT 0,
  `ipaddr` varchar(15) DEFAULT NULL,
  `platform` varchar(120) DEFAULT NULL,
  `browser` varchar(120) DEFAULT NULL,
  `location` varchar(50) DEFAULT NULL,
  `entrytime` datetime DEFAULT NULL,
  `modifieddate` datetime DEFAULT NULL,
  `bandwidth` bigint(20) DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8927 DEFAULT CHARSET=utf32 COLLATE=utf32_unicode_ci;
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
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER logbook_after_insert
    AFTER INSERT ON `logbook`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, new_data, changed_by)
        VALUES(
            'logbook',
            'INSERT',
            NEW.`id`,
            JSON_OBJECT('id', NEW.`id`,'email', NEW.`email`,'sccode', NEW.`sccode`,'pagename', NEW.`pagename`,'duration', NEW.`duration`,'filesize', NEW.`filesize`,'ipaddr', NEW.`ipaddr`,'platform', NEW.`platform`,'browser', NEW.`browser`,'location', NEW.`location`,'entrytime', NEW.`entrytime`,'modifieddate', NEW.`modifieddate`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER logbook_after_update
    AFTER UPDATE ON `logbook`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, new_data, changed_by)
        VALUES(
            'logbook',
            'UPDATE',
            NEW.`id`,
            JSON_OBJECT('id', OLD.`id`,'email', OLD.`email`,'sccode', OLD.`sccode`,'pagename', OLD.`pagename`,'duration', OLD.`duration`,'filesize', OLD.`filesize`,'ipaddr', OLD.`ipaddr`,'platform', OLD.`platform`,'browser', OLD.`browser`,'location', OLD.`location`,'entrytime', OLD.`entrytime`,'modifieddate', OLD.`modifieddate`),
            JSON_OBJECT('id', NEW.`id`,'email', NEW.`email`,'sccode', NEW.`sccode`,'pagename', NEW.`pagename`,'duration', NEW.`duration`,'filesize', NEW.`filesize`,'ipaddr', NEW.`ipaddr`,'platform', NEW.`platform`,'browser', NEW.`browser`,'location', NEW.`location`,'entrytime', NEW.`entrytime`,'modifieddate', NEW.`modifieddate`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER logbook_after_delete
    AFTER DELETE ON `logbook`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, changed_by)
        VALUES(
            'logbook',
            'DELETE',
            OLD.`id`,
            JSON_OBJECT('id', OLD.`id`,'email', OLD.`email`,'sccode', OLD.`sccode`,'pagename', OLD.`pagename`,'duration', OLD.`duration`,'filesize', OLD.`filesize`,'ipaddr', OLD.`ipaddr`,'platform', OLD.`platform`,'browser', OLD.`browser`,'location', OLD.`location`,'entrytime', OLD.`entrytime`,'modifieddate', OLD.`modifieddate`),
            COALESCE(@current_user,'system')
        );
    END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `lottery`
--

DROP TABLE IF EXISTS `lottery`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lottery` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sl` int(11) NOT NULL,
  `coupon` int(11) DEFAULT NULL,
  `codestatus` int(11) NOT NULL DEFAULT 0,
  `prize` varchar(150) DEFAULT NULL,
  `prizestatus` int(11) NOT NULL DEFAULT 0,
  `taka` int(11) NOT NULL DEFAULT 0,
  `randcode` int(11) DEFAULT NULL,
  `randprize` varchar(150) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=151 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
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
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER lottery_after_insert
    AFTER INSERT ON `lottery`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, new_data, changed_by)
        VALUES(
            'lottery',
            'INSERT',
            NEW.`id`,
            JSON_OBJECT('id', NEW.`id`,'sl', NEW.`sl`,'coupon', NEW.`coupon`,'codestatus', NEW.`codestatus`,'prize', NEW.`prize`,'prizestatus', NEW.`prizestatus`,'taka', NEW.`taka`,'randcode', NEW.`randcode`,'randprize', NEW.`randprize`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER lottery_after_update
    AFTER UPDATE ON `lottery`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, new_data, changed_by)
        VALUES(
            'lottery',
            'UPDATE',
            NEW.`id`,
            JSON_OBJECT('id', OLD.`id`,'sl', OLD.`sl`,'coupon', OLD.`coupon`,'codestatus', OLD.`codestatus`,'prize', OLD.`prize`,'prizestatus', OLD.`prizestatus`,'taka', OLD.`taka`,'randcode', OLD.`randcode`,'randprize', OLD.`randprize`),
            JSON_OBJECT('id', NEW.`id`,'sl', NEW.`sl`,'coupon', NEW.`coupon`,'codestatus', NEW.`codestatus`,'prize', NEW.`prize`,'prizestatus', NEW.`prizestatus`,'taka', NEW.`taka`,'randcode', NEW.`randcode`,'randprize', NEW.`randprize`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER lottery_after_delete
    AFTER DELETE ON `lottery`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, changed_by)
        VALUES(
            'lottery',
            'DELETE',
            OLD.`id`,
            JSON_OBJECT('id', OLD.`id`,'sl', OLD.`sl`,'coupon', OLD.`coupon`,'codestatus', OLD.`codestatus`,'prize', OLD.`prize`,'prizestatus', OLD.`prizestatus`,'taka', OLD.`taka`,'randcode', OLD.`randcode`,'randprize', OLD.`randprize`),
            COALESCE(@current_user,'system')
        );
    END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `markmodify`
--

DROP TABLE IF EXISTS `markmodify`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `markmodify` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sessionyear` int(11) DEFAULT NULL,
  `sccode` int(11) DEFAULT NULL,
  `exam` varchar(50) DEFAULT NULL,
  `classname` varchar(50) DEFAULT NULL,
  `sectionname` varchar(30) DEFAULT NULL,
  `subcode` int(11) DEFAULT NULL,
  `dtmodify` timestamp NOT NULL DEFAULT current_timestamp(),
  `dtprocess` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modifieddate` datetime DEFAULT NULL,
  `locked` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=276850 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
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
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER markmodify_after_insert
    AFTER INSERT ON `markmodify`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, new_data, changed_by)
        VALUES(
            'markmodify',
            'INSERT',
            NEW.`id`,
            JSON_OBJECT('id', NEW.`id`,'sessionyear', NEW.`sessionyear`,'sccode', NEW.`sccode`,'exam', NEW.`exam`,'classname', NEW.`classname`,'sectionname', NEW.`sectionname`,'subcode', NEW.`subcode`,'dtmodify', NEW.`dtmodify`,'dtprocess', NEW.`dtprocess`,'modifieddate', NEW.`modifieddate`,'locked', NEW.`locked`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER markmodify_after_update
    AFTER UPDATE ON `markmodify`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, new_data, changed_by)
        VALUES(
            'markmodify',
            'UPDATE',
            NEW.`id`,
            JSON_OBJECT('id', OLD.`id`,'sessionyear', OLD.`sessionyear`,'sccode', OLD.`sccode`,'exam', OLD.`exam`,'classname', OLD.`classname`,'sectionname', OLD.`sectionname`,'subcode', OLD.`subcode`,'dtmodify', OLD.`dtmodify`,'dtprocess', OLD.`dtprocess`,'modifieddate', OLD.`modifieddate`,'locked', OLD.`locked`),
            JSON_OBJECT('id', NEW.`id`,'sessionyear', NEW.`sessionyear`,'sccode', NEW.`sccode`,'exam', NEW.`exam`,'classname', NEW.`classname`,'sectionname', NEW.`sectionname`,'subcode', NEW.`subcode`,'dtmodify', NEW.`dtmodify`,'dtprocess', NEW.`dtprocess`,'modifieddate', NEW.`modifieddate`,'locked', NEW.`locked`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER markmodify_after_delete
    AFTER DELETE ON `markmodify`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, changed_by)
        VALUES(
            'markmodify',
            'DELETE',
            OLD.`id`,
            JSON_OBJECT('id', OLD.`id`,'sessionyear', OLD.`sessionyear`,'sccode', OLD.`sccode`,'exam', OLD.`exam`,'classname', OLD.`classname`,'sectionname', OLD.`sectionname`,'subcode', OLD.`subcode`,'dtmodify', OLD.`dtmodify`,'dtprocess', OLD.`dtprocess`,'modifieddate', OLD.`modifieddate`,'locked', OLD.`locked`),
            COALESCE(@current_user,'system')
        );
    END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `meritlist`
--

DROP TABLE IF EXISTS `meritlist`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `meritlist` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `numplace` int(11) NOT NULL,
  `meritplace` varchar(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=753 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
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
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER meritlist_after_insert
    AFTER INSERT ON `meritlist`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, new_data, changed_by)
        VALUES(
            'meritlist',
            'INSERT',
            NEW.`id`,
            JSON_OBJECT('id', NEW.`id`,'numplace', NEW.`numplace`,'meritplace', NEW.`meritplace`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER meritlist_after_update
    AFTER UPDATE ON `meritlist`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, new_data, changed_by)
        VALUES(
            'meritlist',
            'UPDATE',
            NEW.`id`,
            JSON_OBJECT('id', OLD.`id`,'numplace', OLD.`numplace`,'meritplace', OLD.`meritplace`),
            JSON_OBJECT('id', NEW.`id`,'numplace', NEW.`numplace`,'meritplace', NEW.`meritplace`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER meritlist_after_delete
    AFTER DELETE ON `meritlist`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, changed_by)
        VALUES(
            'meritlist',
            'DELETE',
            OLD.`id`,
            JSON_OBJECT('id', OLD.`id`,'numplace', OLD.`numplace`,'meritplace', OLD.`meritplace`),
            COALESCE(@current_user,'system')
        );
    END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `msgsupport`
--

DROP TABLE IF EXISTS `msgsupport`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `msgsupport` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `yourmail` varchar(100) NOT NULL,
  `msgto` varchar(30) NOT NULL,
  `yoursubject` varchar(100) NOT NULL,
  `yourmsg` longtext NOT NULL,
  `datetime` timestamp NOT NULL DEFAULT current_timestamp(),
  `msgstatus` varchar(15) NOT NULL DEFAULT 'UNREAD',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
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
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER msgsupport_after_insert
    AFTER INSERT ON `msgsupport`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, new_data, changed_by)
        VALUES(
            'msgsupport',
            'INSERT',
            NEW.`id`,
            JSON_OBJECT('id', NEW.`id`,'yourmail', NEW.`yourmail`,'msgto', NEW.`msgto`,'yoursubject', NEW.`yoursubject`,'yourmsg', NEW.`yourmsg`,'datetime', NEW.`datetime`,'msgstatus', NEW.`msgstatus`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER msgsupport_after_update
    AFTER UPDATE ON `msgsupport`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, new_data, changed_by)
        VALUES(
            'msgsupport',
            'UPDATE',
            NEW.`id`,
            JSON_OBJECT('id', OLD.`id`,'yourmail', OLD.`yourmail`,'msgto', OLD.`msgto`,'yoursubject', OLD.`yoursubject`,'yourmsg', OLD.`yourmsg`,'datetime', OLD.`datetime`,'msgstatus', OLD.`msgstatus`),
            JSON_OBJECT('id', NEW.`id`,'yourmail', NEW.`yourmail`,'msgto', NEW.`msgto`,'yoursubject', NEW.`yoursubject`,'yourmsg', NEW.`yourmsg`,'datetime', NEW.`datetime`,'msgstatus', NEW.`msgstatus`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER msgsupport_after_delete
    AFTER DELETE ON `msgsupport`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, changed_by)
        VALUES(
            'msgsupport',
            'DELETE',
            OLD.`id`,
            JSON_OBJECT('id', OLD.`id`,'yourmail', OLD.`yourmail`,'msgto', OLD.`msgto`,'yoursubject', OLD.`yoursubject`,'yourmsg', OLD.`yourmsg`,'datetime', OLD.`datetime`,'msgstatus', OLD.`msgstatus`),
            COALESCE(@current_user,'system')
        );
    END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `my-club`
--

DROP TABLE IF EXISTS `my-club`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `my-club` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sccode` int(11) NOT NULL,
  `clubname` int(11) NOT NULL,
  `clubtitle` int(11) NOT NULL,
  `builddate` int(11) NOT NULL,
  `constitution` int(11) NOT NULL,
  `president` int(11) NOT NULL,
  `vicepresident` int(11) NOT NULL,
  `secretary` int(11) NOT NULL,
  `jointsecretary` int(11) NOT NULL,
  `accountants` int(11) NOT NULL,
  `reservemember` int(11) NOT NULL,
  `restmember` int(11) NOT NULL,
  `comitteedur` int(11) NOT NULL,
  `durationunit` int(11) NOT NULL,
  `entrydate` int(11) NOT NULL,
  `entryby` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `my_club`
--

DROP TABLE IF EXISTS `my_club`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `my_club` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sccode` int(11) NOT NULL,
  `clubname` int(11) NOT NULL,
  `clubtitle` int(11) NOT NULL,
  `builddate` int(11) NOT NULL,
  `constitution` int(11) NOT NULL,
  `president` int(11) NOT NULL,
  `vicepresident` int(11) NOT NULL,
  `secretary` int(11) NOT NULL,
  `jointsecretary` int(11) NOT NULL,
  `accountants` int(11) NOT NULL,
  `reservemember` int(11) NOT NULL,
  `restmember` int(11) NOT NULL,
  `comitteedur` int(11) NOT NULL,
  `durationunit` int(11) NOT NULL,
  `entrydate` int(11) NOT NULL,
  `entryby` int(11) NOT NULL,
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER my_club_after_insert
    AFTER INSERT ON `my_club`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, new_data, changed_by)
        VALUES(
            'my_club',
            'INSERT',
            NEW.`id`,
            JSON_OBJECT('id', NEW.`id`,'sccode', NEW.`sccode`,'clubname', NEW.`clubname`,'clubtitle', NEW.`clubtitle`,'builddate', NEW.`builddate`,'constitution', NEW.`constitution`,'president', NEW.`president`,'vicepresident', NEW.`vicepresident`,'secretary', NEW.`secretary`,'jointsecretary', NEW.`jointsecretary`,'accountants', NEW.`accountants`,'reservemember', NEW.`reservemember`,'restmember', NEW.`restmember`,'comitteedur', NEW.`comitteedur`,'durationunit', NEW.`durationunit`,'entrydate', NEW.`entrydate`,'entryby', NEW.`entryby`,'modifieddate', NEW.`modifieddate`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER my_club_after_update
    AFTER UPDATE ON `my_club`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, new_data, changed_by)
        VALUES(
            'my_club',
            'UPDATE',
            NEW.`id`,
            JSON_OBJECT('id', OLD.`id`,'sccode', OLD.`sccode`,'clubname', OLD.`clubname`,'clubtitle', OLD.`clubtitle`,'builddate', OLD.`builddate`,'constitution', OLD.`constitution`,'president', OLD.`president`,'vicepresident', OLD.`vicepresident`,'secretary', OLD.`secretary`,'jointsecretary', OLD.`jointsecretary`,'accountants', OLD.`accountants`,'reservemember', OLD.`reservemember`,'restmember', OLD.`restmember`,'comitteedur', OLD.`comitteedur`,'durationunit', OLD.`durationunit`,'entrydate', OLD.`entrydate`,'entryby', OLD.`entryby`,'modifieddate', OLD.`modifieddate`),
            JSON_OBJECT('id', NEW.`id`,'sccode', NEW.`sccode`,'clubname', NEW.`clubname`,'clubtitle', NEW.`clubtitle`,'builddate', NEW.`builddate`,'constitution', NEW.`constitution`,'president', NEW.`president`,'vicepresident', NEW.`vicepresident`,'secretary', NEW.`secretary`,'jointsecretary', NEW.`jointsecretary`,'accountants', NEW.`accountants`,'reservemember', NEW.`reservemember`,'restmember', NEW.`restmember`,'comitteedur', NEW.`comitteedur`,'durationunit', NEW.`durationunit`,'entrydate', NEW.`entrydate`,'entryby', NEW.`entryby`,'modifieddate', NEW.`modifieddate`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER my_club_after_delete
    AFTER DELETE ON `my_club`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, changed_by)
        VALUES(
            'my_club',
            'DELETE',
            OLD.`id`,
            JSON_OBJECT('id', OLD.`id`,'sccode', OLD.`sccode`,'clubname', OLD.`clubname`,'clubtitle', OLD.`clubtitle`,'builddate', OLD.`builddate`,'constitution', OLD.`constitution`,'president', OLD.`president`,'vicepresident', OLD.`vicepresident`,'secretary', OLD.`secretary`,'jointsecretary', OLD.`jointsecretary`,'accountants', OLD.`accountants`,'reservemember', OLD.`reservemember`,'restmember', OLD.`restmember`,'comitteedur', OLD.`comitteedur`,'durationunit', OLD.`durationunit`,'entrydate', OLD.`entrydate`,'entryby', OLD.`entryby`,'modifieddate', OLD.`modifieddate`),
            COALESCE(@current_user,'system')
        );
    END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `notice`
--

DROP TABLE IF EXISTS `notice`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `notice` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sccode` int(11) DEFAULT NULL,
  `category` varchar(30) DEFAULT NULL,
  `title` varchar(200) DEFAULT NULL,
  `descrip` varchar(500) DEFAULT NULL,
  `icon` varchar(30) NOT NULL DEFAULT 'bell-fill',
  `color` varchar(20) NOT NULL DEFAULT 'black',
  `expdate` date DEFAULT NULL,
  `display_to` varchar(100) DEFAULT NULL,
  `teacher` tinyint(1) NOT NULL DEFAULT 1,
  `smc` tinyint(1) NOT NULL DEFAULT 1,
  `guardian` tinyint(1) NOT NULL DEFAULT 1,
  `sms` tinyint(1) NOT NULL DEFAULT 0,
  `pushnoti` tinyint(1) NOT NULL DEFAULT 1,
  `email` tinyint(1) NOT NULL DEFAULT 0,
  `entryby` varchar(120) DEFAULT NULL,
  `entrytime` datetime DEFAULT NULL,
  `modifieddate` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
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
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER notice_after_insert
    AFTER INSERT ON `notice`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, new_data, changed_by)
        VALUES(
            'notice',
            'INSERT',
            NEW.`id`,
            JSON_OBJECT('id', NEW.`id`,'sccode', NEW.`sccode`,'category', NEW.`category`,'title', NEW.`title`,'descrip', NEW.`descrip`,'icon', NEW.`icon`,'color', NEW.`color`,'expdate', NEW.`expdate`,'display_to', NEW.`display_to`,'teacher', NEW.`teacher`,'smc', NEW.`smc`,'guardian', NEW.`guardian`,'sms', NEW.`sms`,'pushnoti', NEW.`pushnoti`,'email', NEW.`email`,'entryby', NEW.`entryby`,'entrytime', NEW.`entrytime`,'modifieddate', NEW.`modifieddate`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER notice_after_update
    AFTER UPDATE ON `notice`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, new_data, changed_by)
        VALUES(
            'notice',
            'UPDATE',
            NEW.`id`,
            JSON_OBJECT('id', OLD.`id`,'sccode', OLD.`sccode`,'category', OLD.`category`,'title', OLD.`title`,'descrip', OLD.`descrip`,'icon', OLD.`icon`,'color', OLD.`color`,'expdate', OLD.`expdate`,'display_to', OLD.`display_to`,'teacher', OLD.`teacher`,'smc', OLD.`smc`,'guardian', OLD.`guardian`,'sms', OLD.`sms`,'pushnoti', OLD.`pushnoti`,'email', OLD.`email`,'entryby', OLD.`entryby`,'entrytime', OLD.`entrytime`,'modifieddate', OLD.`modifieddate`),
            JSON_OBJECT('id', NEW.`id`,'sccode', NEW.`sccode`,'category', NEW.`category`,'title', NEW.`title`,'descrip', NEW.`descrip`,'icon', NEW.`icon`,'color', NEW.`color`,'expdate', NEW.`expdate`,'display_to', NEW.`display_to`,'teacher', NEW.`teacher`,'smc', NEW.`smc`,'guardian', NEW.`guardian`,'sms', NEW.`sms`,'pushnoti', NEW.`pushnoti`,'email', NEW.`email`,'entryby', NEW.`entryby`,'entrytime', NEW.`entrytime`,'modifieddate', NEW.`modifieddate`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER notice_after_delete
    AFTER DELETE ON `notice`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, changed_by)
        VALUES(
            'notice',
            'DELETE',
            OLD.`id`,
            JSON_OBJECT('id', OLD.`id`,'sccode', OLD.`sccode`,'category', OLD.`category`,'title', OLD.`title`,'descrip', OLD.`descrip`,'icon', OLD.`icon`,'color', OLD.`color`,'expdate', OLD.`expdate`,'display_to', OLD.`display_to`,'teacher', OLD.`teacher`,'smc', OLD.`smc`,'guardian', OLD.`guardian`,'sms', OLD.`sms`,'pushnoti', OLD.`pushnoti`,'email', OLD.`email`,'entryby', OLD.`entryby`,'entrytime', OLD.`entrytime`,'modifieddate', OLD.`modifieddate`),
            COALESCE(@current_user,'system')
        );
    END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `notice_category`
--

DROP TABLE IF EXISTS `notice_category`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `notice_category` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category` varchar(100) DEFAULT NULL,
  `icon` varchar(20) DEFAULT NULL,
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER notice_category_after_insert
    AFTER INSERT ON `notice_category`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, new_data, changed_by)
        VALUES(
            'notice_category',
            'INSERT',
            NEW.`id`,
            JSON_OBJECT('id', NEW.`id`,'category', NEW.`category`,'icon', NEW.`icon`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER notice_category_after_update
    AFTER UPDATE ON `notice_category`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, new_data, changed_by)
        VALUES(
            'notice_category',
            'UPDATE',
            NEW.`id`,
            JSON_OBJECT('id', OLD.`id`,'category', OLD.`category`,'icon', OLD.`icon`),
            JSON_OBJECT('id', NEW.`id`,'category', NEW.`category`,'icon', NEW.`icon`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER notice_category_after_delete
    AFTER DELETE ON `notice_category`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, changed_by)
        VALUES(
            'notice_category',
            'DELETE',
            OLD.`id`,
            JSON_OBJECT('id', OLD.`id`,'category', OLD.`category`,'icon', OLD.`icon`),
            COALESCE(@current_user,'system')
        );
    END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `notification`
--

DROP TABLE IF EXISTS `notification`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `notification` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sccode` int(11) DEFAULT NULL,
  `sessionyear` int(11) DEFAULT NULL,
  `datetime` datetime DEFAULT NULL,
  `castid` varchar(50) DEFAULT NULL,
  `success` int(11) DEFAULT NULL,
  `failure` int(11) DEFAULT NULL,
  `conid` varchar(50) DEFAULT NULL,
  `msgid` varchar(50) DEFAULT NULL,
  `error` varchar(100) DEFAULT NULL,
  `frommail` varchar(100) DEFAULT NULL,
  `tomail` varchar(100) DEFAULT NULL,
  `fromusercat` varchar(50) DEFAULT NULL,
  `tousercat` varchar(50) DEFAULT NULL,
  `msgtype` varchar(50) DEFAULT NULL,
  `title` varchar(100) DEFAULT NULL,
  `smstext` varchar(500) DEFAULT NULL,
  `fromuserid` varchar(15) DEFAULT NULL,
  `touserid` varchar(15) DEFAULT NULL,
  `rwstatus` int(11) NOT NULL DEFAULT 0,
  `icon` varchar(50) DEFAULT NULL,
  `color` varchar(10) NOT NULL DEFAULT '#000000',
  `value` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
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
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER notification_after_insert
    AFTER INSERT ON `notification`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, new_data, changed_by)
        VALUES(
            'notification',
            'INSERT',
            NEW.`id`,
            JSON_OBJECT('id', NEW.`id`,'sccode', NEW.`sccode`,'sessionyear', NEW.`sessionyear`,'datetime', NEW.`datetime`,'castid', NEW.`castid`,'success', NEW.`success`,'failure', NEW.`failure`,'conid', NEW.`conid`,'msgid', NEW.`msgid`,'error', NEW.`error`,'frommail', NEW.`frommail`,'tomail', NEW.`tomail`,'fromusercat', NEW.`fromusercat`,'tousercat', NEW.`tousercat`,'msgtype', NEW.`msgtype`,'title', NEW.`title`,'smstext', NEW.`smstext`,'fromuserid', NEW.`fromuserid`,'touserid', NEW.`touserid`,'rwstatus', NEW.`rwstatus`,'icon', NEW.`icon`,'color', NEW.`color`,'value', NEW.`value`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER notification_after_update
    AFTER UPDATE ON `notification`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, new_data, changed_by)
        VALUES(
            'notification',
            'UPDATE',
            NEW.`id`,
            JSON_OBJECT('id', OLD.`id`,'sccode', OLD.`sccode`,'sessionyear', OLD.`sessionyear`,'datetime', OLD.`datetime`,'castid', OLD.`castid`,'success', OLD.`success`,'failure', OLD.`failure`,'conid', OLD.`conid`,'msgid', OLD.`msgid`,'error', OLD.`error`,'frommail', OLD.`frommail`,'tomail', OLD.`tomail`,'fromusercat', OLD.`fromusercat`,'tousercat', OLD.`tousercat`,'msgtype', OLD.`msgtype`,'title', OLD.`title`,'smstext', OLD.`smstext`,'fromuserid', OLD.`fromuserid`,'touserid', OLD.`touserid`,'rwstatus', OLD.`rwstatus`,'icon', OLD.`icon`,'color', OLD.`color`,'value', OLD.`value`),
            JSON_OBJECT('id', NEW.`id`,'sccode', NEW.`sccode`,'sessionyear', NEW.`sessionyear`,'datetime', NEW.`datetime`,'castid', NEW.`castid`,'success', NEW.`success`,'failure', NEW.`failure`,'conid', NEW.`conid`,'msgid', NEW.`msgid`,'error', NEW.`error`,'frommail', NEW.`frommail`,'tomail', NEW.`tomail`,'fromusercat', NEW.`fromusercat`,'tousercat', NEW.`tousercat`,'msgtype', NEW.`msgtype`,'title', NEW.`title`,'smstext', NEW.`smstext`,'fromuserid', NEW.`fromuserid`,'touserid', NEW.`touserid`,'rwstatus', NEW.`rwstatus`,'icon', NEW.`icon`,'color', NEW.`color`,'value', NEW.`value`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER notification_after_delete
    AFTER DELETE ON `notification`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, changed_by)
        VALUES(
            'notification',
            'DELETE',
            OLD.`id`,
            JSON_OBJECT('id', OLD.`id`,'sccode', OLD.`sccode`,'sessionyear', OLD.`sessionyear`,'datetime', OLD.`datetime`,'castid', OLD.`castid`,'success', OLD.`success`,'failure', OLD.`failure`,'conid', OLD.`conid`,'msgid', OLD.`msgid`,'error', OLD.`error`,'frommail', OLD.`frommail`,'tomail', OLD.`tomail`,'fromusercat', OLD.`fromusercat`,'tousercat', OLD.`tousercat`,'msgtype', OLD.`msgtype`,'title', OLD.`title`,'smstext', OLD.`smstext`,'fromuserid', OLD.`fromuserid`,'touserid', OLD.`touserid`,'rwstatus', OLD.`rwstatus`,'icon', OLD.`icon`,'color', OLD.`color`,'value', OLD.`value`),
            COALESCE(@current_user,'system')
        );
    END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `orders`
--

DROP TABLE IF EXISTS `orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `orders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sccode` int(11) NOT NULL,
  `orderdate` date NOT NULL,
  `category` varchar(50) NOT NULL,
  `subcategory` varchar(50) NOT NULL,
  `description` varchar(255) NOT NULL,
  `qnt` int(11) NOT NULL,
  `unitprice` float NOT NULL,
  `amount` float NOT NULL,
  `payment` float NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=95 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
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
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER orders_after_insert
    AFTER INSERT ON `orders`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, new_data, changed_by)
        VALUES(
            'orders',
            'INSERT',
            NEW.`id`,
            JSON_OBJECT('id', NEW.`id`,'sccode', NEW.`sccode`,'orderdate', NEW.`orderdate`,'category', NEW.`category`,'subcategory', NEW.`subcategory`,'description', NEW.`description`,'qnt', NEW.`qnt`,'unitprice', NEW.`unitprice`,'amount', NEW.`amount`,'payment', NEW.`payment`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER orders_after_update
    AFTER UPDATE ON `orders`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, new_data, changed_by)
        VALUES(
            'orders',
            'UPDATE',
            NEW.`id`,
            JSON_OBJECT('id', OLD.`id`,'sccode', OLD.`sccode`,'orderdate', OLD.`orderdate`,'category', OLD.`category`,'subcategory', OLD.`subcategory`,'description', OLD.`description`,'qnt', OLD.`qnt`,'unitprice', OLD.`unitprice`,'amount', OLD.`amount`,'payment', OLD.`payment`),
            JSON_OBJECT('id', NEW.`id`,'sccode', NEW.`sccode`,'orderdate', NEW.`orderdate`,'category', NEW.`category`,'subcategory', NEW.`subcategory`,'description', NEW.`description`,'qnt', NEW.`qnt`,'unitprice', NEW.`unitprice`,'amount', NEW.`amount`,'payment', NEW.`payment`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER orders_after_delete
    AFTER DELETE ON `orders`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, changed_by)
        VALUES(
            'orders',
            'DELETE',
            OLD.`id`,
            JSON_OBJECT('id', OLD.`id`,'sccode', OLD.`sccode`,'orderdate', OLD.`orderdate`,'category', OLD.`category`,'subcategory', OLD.`subcategory`,'description', OLD.`description`,'qnt', OLD.`qnt`,'unitprice', OLD.`unitprice`,'amount', OLD.`amount`,'payment', OLD.`payment`),
            COALESCE(@current_user,'system')
        );
    END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `otp`
--

DROP TABLE IF EXISTS `otp`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `otp` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(100) DEFAULT NULL,
  `userid` varchar(100) DEFAULT NULL,
  `otp` varchar(20) DEFAULT NULL,
  `otptime` datetime DEFAULT NULL,
  `login` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7250 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
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
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER otp_after_insert
    AFTER INSERT ON `otp`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, new_data, changed_by)
        VALUES(
            'otp',
            'INSERT',
            NEW.`id`,
            JSON_OBJECT('id', NEW.`id`,'username', NEW.`username`,'userid', NEW.`userid`,'otp', NEW.`otp`,'otptime', NEW.`otptime`,'login', NEW.`login`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER otp_after_update
    AFTER UPDATE ON `otp`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, new_data, changed_by)
        VALUES(
            'otp',
            'UPDATE',
            NEW.`id`,
            JSON_OBJECT('id', OLD.`id`,'username', OLD.`username`,'userid', OLD.`userid`,'otp', OLD.`otp`,'otptime', OLD.`otptime`,'login', OLD.`login`),
            JSON_OBJECT('id', NEW.`id`,'username', NEW.`username`,'userid', NEW.`userid`,'otp', NEW.`otp`,'otptime', NEW.`otptime`,'login', NEW.`login`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER otp_after_delete
    AFTER DELETE ON `otp`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, changed_by)
        VALUES(
            'otp',
            'DELETE',
            OLD.`id`,
            JSON_OBJECT('id', OLD.`id`,'username', OLD.`username`,'userid', OLD.`userid`,'otp', OLD.`otp`,'otptime', OLD.`otptime`,'login', OLD.`login`),
            COALESCE(@current_user,'system')
        );
    END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `payroll_track`
--

DROP TABLE IF EXISTS `payroll_track`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `payroll_track` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sccode` int(11) DEFAULT NULL,
  `salyear` int(11) NOT NULL DEFAULT 0,
  `salmonth` int(11) NOT NULL DEFAULT 0,
  `year` int(11) NOT NULL DEFAULT 0,
  `month` int(11) NOT NULL DEFAULT 0,
  `period` int(11) NOT NULL DEFAULT 1,
  `date` date DEFAULT NULL,
  `paystructure` int(11) NOT NULL DEFAULT 0,
  `attnd` int(11) NOT NULL DEFAULT 0,
  `bonus` int(11) NOT NULL DEFAULT 0,
  `calc` int(11) NOT NULL DEFAULT 0,
  `payoff` int(11) NOT NULL DEFAULT 0,
  `dispuch` int(11) NOT NULL DEFAULT 0,
  `cheque` int(11) NOT NULL DEFAULT 0,
  `entryby` varchar(100) DEFAULT NULL,
  `entrytime` datetime DEFAULT NULL,
  `modifytime` datetime DEFAULT NULL,
  `modifieddate` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=34 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
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
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER payroll_track_after_insert
    AFTER INSERT ON `payroll_track`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, new_data, changed_by)
        VALUES(
            'payroll_track',
            'INSERT',
            NEW.`id`,
            JSON_OBJECT('id', NEW.`id`,'sccode', NEW.`sccode`,'salyear', NEW.`salyear`,'salmonth', NEW.`salmonth`,'year', NEW.`year`,'month', NEW.`month`,'period', NEW.`period`,'date', NEW.`date`,'paystructure', NEW.`paystructure`,'attnd', NEW.`attnd`,'bonus', NEW.`bonus`,'calc', NEW.`calc`,'payoff', NEW.`payoff`,'dispuch', NEW.`dispuch`,'cheque', NEW.`cheque`,'entryby', NEW.`entryby`,'entrytime', NEW.`entrytime`,'modifytime', NEW.`modifytime`,'modifieddate', NEW.`modifieddate`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER payroll_track_after_update
    AFTER UPDATE ON `payroll_track`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, new_data, changed_by)
        VALUES(
            'payroll_track',
            'UPDATE',
            NEW.`id`,
            JSON_OBJECT('id', OLD.`id`,'sccode', OLD.`sccode`,'salyear', OLD.`salyear`,'salmonth', OLD.`salmonth`,'year', OLD.`year`,'month', OLD.`month`,'period', OLD.`period`,'date', OLD.`date`,'paystructure', OLD.`paystructure`,'attnd', OLD.`attnd`,'bonus', OLD.`bonus`,'calc', OLD.`calc`,'payoff', OLD.`payoff`,'dispuch', OLD.`dispuch`,'cheque', OLD.`cheque`,'entryby', OLD.`entryby`,'entrytime', OLD.`entrytime`,'modifytime', OLD.`modifytime`,'modifieddate', OLD.`modifieddate`),
            JSON_OBJECT('id', NEW.`id`,'sccode', NEW.`sccode`,'salyear', NEW.`salyear`,'salmonth', NEW.`salmonth`,'year', NEW.`year`,'month', NEW.`month`,'period', NEW.`period`,'date', NEW.`date`,'paystructure', NEW.`paystructure`,'attnd', NEW.`attnd`,'bonus', NEW.`bonus`,'calc', NEW.`calc`,'payoff', NEW.`payoff`,'dispuch', NEW.`dispuch`,'cheque', NEW.`cheque`,'entryby', NEW.`entryby`,'entrytime', NEW.`entrytime`,'modifytime', NEW.`modifytime`,'modifieddate', NEW.`modifieddate`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER payroll_track_after_delete
    AFTER DELETE ON `payroll_track`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, changed_by)
        VALUES(
            'payroll_track',
            'DELETE',
            OLD.`id`,
            JSON_OBJECT('id', OLD.`id`,'sccode', OLD.`sccode`,'salyear', OLD.`salyear`,'salmonth', OLD.`salmonth`,'year', OLD.`year`,'month', OLD.`month`,'period', OLD.`period`,'date', OLD.`date`,'paystructure', OLD.`paystructure`,'attnd', OLD.`attnd`,'bonus', OLD.`bonus`,'calc', OLD.`calc`,'payoff', OLD.`payoff`,'dispuch', OLD.`dispuch`,'cheque', OLD.`cheque`,'entryby', OLD.`entryby`,'entrytime', OLD.`entrytime`,'modifytime', OLD.`modifytime`,'modifieddate', OLD.`modifieddate`),
            COALESCE(@current_user,'system')
        );
    END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `payscale`
--

DROP TABLE IF EXISTS `payscale`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `payscale` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `paycode` int(11) DEFAULT NULL,
  `structure` varchar(255) DEFAULT NULL,
  `amount` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
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
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER payscale_after_insert
    AFTER INSERT ON `payscale`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, new_data, changed_by)
        VALUES(
            'payscale',
            'INSERT',
            NEW.`id`,
            JSON_OBJECT('id', NEW.`id`,'paycode', NEW.`paycode`,'structure', NEW.`structure`,'amount', NEW.`amount`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER payscale_after_update
    AFTER UPDATE ON `payscale`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, new_data, changed_by)
        VALUES(
            'payscale',
            'UPDATE',
            NEW.`id`,
            JSON_OBJECT('id', OLD.`id`,'paycode', OLD.`paycode`,'structure', OLD.`structure`,'amount', OLD.`amount`),
            JSON_OBJECT('id', NEW.`id`,'paycode', NEW.`paycode`,'structure', NEW.`structure`,'amount', NEW.`amount`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER payscale_after_delete
    AFTER DELETE ON `payscale`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, changed_by)
        VALUES(
            'payscale',
            'DELETE',
            OLD.`id`,
            JSON_OBJECT('id', OLD.`id`,'paycode', OLD.`paycode`,'structure', OLD.`structure`,'amount', OLD.`amount`),
            COALESCE(@current_user,'system')
        );
    END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `permissions_role`
--

DROP TABLE IF EXISTS `permissions_role`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `permissions_role` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sl` int(11) NOT NULL DEFAULT 0,
  `pagetitle` varchar(50) DEFAULT NULL,
  `pagedescrip` varchar(250) DEFAULT NULL,
  `filename` varchar(100) DEFAULT NULL,
  `qstring` varchar(100) DEFAULT NULL,
  `sadmin` int(11) NOT NULL DEFAULT 3,
  `admin` int(11) NOT NULL DEFAULT 3,
  `headteacher` int(11) NOT NULL DEFAULT 3,
  `clsteacher` int(11) NOT NULL DEFAULT 0,
  `teacher` int(11) NOT NULL DEFAULT 0,
  `accountants` int(11) NOT NULL DEFAULT 0,
  `officeasstt` int(11) NOT NULL DEFAULT 0,
  `labasstt` int(11) NOT NULL DEFAULT 0,
  `librarian` int(11) NOT NULL DEFAULT 0,
  `student` int(11) NOT NULL DEFAULT 0,
  `guardian` int(11) NOT NULL DEFAULT 0,
  `guest` int(11) NOT NULL DEFAULT 0,
  `smcchairman` int(11) NOT NULL DEFAULT 0,
  `smcmember` int(11) NOT NULL DEFAULT 0,
  `staff` int(11) NOT NULL DEFAULT 0,
  `useo` int(11) NOT NULL DEFAULT 0,
  `uno` int(11) NOT NULL DEFAULT 0,
  `deo` int(11) NOT NULL DEFAULT 0,
  `settings` int(11) NOT NULL DEFAULT 1,
  `bind` int(11) NOT NULL DEFAULT 0,
  `sccode` int(11) NOT NULL DEFAULT 999999,
  `modifieddate` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf32 COLLATE=utf32_unicode_ci;
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
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER permissions_role_after_insert
    AFTER INSERT ON `permissions_role`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, new_data, changed_by)
        VALUES(
            'permissions_role',
            'INSERT',
            NEW.`id`,
            JSON_OBJECT('id', NEW.`id`,'sl', NEW.`sl`,'pagetitle', NEW.`pagetitle`,'pagedescrip', NEW.`pagedescrip`,'filename', NEW.`filename`,'qstring', NEW.`qstring`,'sadmin', NEW.`sadmin`,'admin', NEW.`admin`,'headteacher', NEW.`headteacher`,'clsteacher', NEW.`clsteacher`,'teacher', NEW.`teacher`,'accountants', NEW.`accountants`,'officeasstt', NEW.`officeasstt`,'labasstt', NEW.`labasstt`,'librarian', NEW.`librarian`,'student', NEW.`student`,'guardian', NEW.`guardian`,'guest', NEW.`guest`,'smcchairman', NEW.`smcchairman`,'smcmember', NEW.`smcmember`,'staff', NEW.`staff`,'useo', NEW.`useo`,'uno', NEW.`uno`,'deo', NEW.`deo`,'settings', NEW.`settings`,'bind', NEW.`bind`,'sccode', NEW.`sccode`,'modifieddate', NEW.`modifieddate`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER permissions_role_after_update
    AFTER UPDATE ON `permissions_role`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, new_data, changed_by)
        VALUES(
            'permissions_role',
            'UPDATE',
            NEW.`id`,
            JSON_OBJECT('id', OLD.`id`,'sl', OLD.`sl`,'pagetitle', OLD.`pagetitle`,'pagedescrip', OLD.`pagedescrip`,'filename', OLD.`filename`,'qstring', OLD.`qstring`,'sadmin', OLD.`sadmin`,'admin', OLD.`admin`,'headteacher', OLD.`headteacher`,'clsteacher', OLD.`clsteacher`,'teacher', OLD.`teacher`,'accountants', OLD.`accountants`,'officeasstt', OLD.`officeasstt`,'labasstt', OLD.`labasstt`,'librarian', OLD.`librarian`,'student', OLD.`student`,'guardian', OLD.`guardian`,'guest', OLD.`guest`,'smcchairman', OLD.`smcchairman`,'smcmember', OLD.`smcmember`,'staff', OLD.`staff`,'useo', OLD.`useo`,'uno', OLD.`uno`,'deo', OLD.`deo`,'settings', OLD.`settings`,'bind', OLD.`bind`,'sccode', OLD.`sccode`,'modifieddate', OLD.`modifieddate`),
            JSON_OBJECT('id', NEW.`id`,'sl', NEW.`sl`,'pagetitle', NEW.`pagetitle`,'pagedescrip', NEW.`pagedescrip`,'filename', NEW.`filename`,'qstring', NEW.`qstring`,'sadmin', NEW.`sadmin`,'admin', NEW.`admin`,'headteacher', NEW.`headteacher`,'clsteacher', NEW.`clsteacher`,'teacher', NEW.`teacher`,'accountants', NEW.`accountants`,'officeasstt', NEW.`officeasstt`,'labasstt', NEW.`labasstt`,'librarian', NEW.`librarian`,'student', NEW.`student`,'guardian', NEW.`guardian`,'guest', NEW.`guest`,'smcchairman', NEW.`smcchairman`,'smcmember', NEW.`smcmember`,'staff', NEW.`staff`,'useo', NEW.`useo`,'uno', NEW.`uno`,'deo', NEW.`deo`,'settings', NEW.`settings`,'bind', NEW.`bind`,'sccode', NEW.`sccode`,'modifieddate', NEW.`modifieddate`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER permissions_role_after_delete
    AFTER DELETE ON `permissions_role`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, changed_by)
        VALUES(
            'permissions_role',
            'DELETE',
            OLD.`id`,
            JSON_OBJECT('id', OLD.`id`,'sl', OLD.`sl`,'pagetitle', OLD.`pagetitle`,'pagedescrip', OLD.`pagedescrip`,'filename', OLD.`filename`,'qstring', OLD.`qstring`,'sadmin', OLD.`sadmin`,'admin', OLD.`admin`,'headteacher', OLD.`headteacher`,'clsteacher', OLD.`clsteacher`,'teacher', OLD.`teacher`,'accountants', OLD.`accountants`,'officeasstt', OLD.`officeasstt`,'labasstt', OLD.`labasstt`,'librarian', OLD.`librarian`,'student', OLD.`student`,'guardian', OLD.`guardian`,'guest', OLD.`guest`,'smcchairman', OLD.`smcchairman`,'smcmember', OLD.`smcmember`,'staff', OLD.`staff`,'useo', OLD.`useo`,'uno', OLD.`uno`,'deo', OLD.`deo`,'settings', OLD.`settings`,'bind', OLD.`bind`,'sccode', OLD.`sccode`,'modifieddate', OLD.`modifieddate`),
            COALESCE(@current_user,'system')
        );
    END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `permissions_user`
--

DROP TABLE IF EXISTS `permissions_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `permissions_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sccode` int(11) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `filename` varchar(100) DEFAULT NULL,
  `qstring` varchar(100) DEFAULT NULL,
  `permis` int(11) DEFAULT NULL,
  `issuedby` varchar(100) DEFAULT NULL,
  `issuetime` datetime DEFAULT NULL,
  `modifieddate` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
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
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER permissions_user_after_insert
    AFTER INSERT ON `permissions_user`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, new_data, changed_by)
        VALUES(
            'permissions_user',
            'INSERT',
            NEW.`id`,
            JSON_OBJECT('id', NEW.`id`,'sccode', NEW.`sccode`,'email', NEW.`email`,'filename', NEW.`filename`,'qstring', NEW.`qstring`,'permis', NEW.`permis`,'issuedby', NEW.`issuedby`,'issuetime', NEW.`issuetime`,'modifieddate', NEW.`modifieddate`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER permissions_user_after_update
    AFTER UPDATE ON `permissions_user`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, new_data, changed_by)
        VALUES(
            'permissions_user',
            'UPDATE',
            NEW.`id`,
            JSON_OBJECT('id', OLD.`id`,'sccode', OLD.`sccode`,'email', OLD.`email`,'filename', OLD.`filename`,'qstring', OLD.`qstring`,'permis', OLD.`permis`,'issuedby', OLD.`issuedby`,'issuetime', OLD.`issuetime`,'modifieddate', OLD.`modifieddate`),
            JSON_OBJECT('id', NEW.`id`,'sccode', NEW.`sccode`,'email', NEW.`email`,'filename', NEW.`filename`,'qstring', NEW.`qstring`,'permis', NEW.`permis`,'issuedby', NEW.`issuedby`,'issuetime', NEW.`issuetime`,'modifieddate', NEW.`modifieddate`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER permissions_user_after_delete
    AFTER DELETE ON `permissions_user`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, changed_by)
        VALUES(
            'permissions_user',
            'DELETE',
            OLD.`id`,
            JSON_OBJECT('id', OLD.`id`,'sccode', OLD.`sccode`,'email', OLD.`email`,'filename', OLD.`filename`,'qstring', OLD.`qstring`,'permis', OLD.`permis`,'issuedby', OLD.`issuedby`,'issuetime', OLD.`issuetime`,'modifieddate', OLD.`modifieddate`),
            COALESCE(@current_user,'system')
        );
    END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `pibiarea`
--

DROP TABLE IF EXISTS `pibiarea`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pibiarea` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sessionyear` int(11) NOT NULL,
  `class` varchar(20) NOT NULL,
  `exam` varchar(20) NOT NULL,
  `exam2` varchar(20) DEFAULT NULL,
  `subcode` int(11) NOT NULL DEFAULT 0,
  `subname` varchar(30) DEFAULT NULL,
  `pibiarea` varchar(50) DEFAULT NULL,
  `count6` int(11) DEFAULT NULL,
  `count7` int(11) DEFAULT NULL,
  `topiccode` varchar(7) DEFAULT NULL,
  `topictitle` varchar(255) DEFAULT NULL,
  `level1` varchar(255) DEFAULT NULL,
  `level2` varchar(255) DEFAULT NULL,
  `level3` varchar(255) DEFAULT NULL,
  `continious` int(11) NOT NULL DEFAULT 0,
  `total` int(11) NOT NULL DEFAULT 0,
  `behave` int(11) NOT NULL DEFAULT 0,
  `entrytime` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=48 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
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
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER pibiarea_after_insert
    AFTER INSERT ON `pibiarea`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, new_data, changed_by)
        VALUES(
            'pibiarea',
            'INSERT',
            NEW.`id`,
            JSON_OBJECT('id', NEW.`id`,'sessionyear', NEW.`sessionyear`,'class', NEW.`class`,'exam', NEW.`exam`,'exam2', NEW.`exam2`,'subcode', NEW.`subcode`,'subname', NEW.`subname`,'pibiarea', NEW.`pibiarea`,'count6', NEW.`count6`,'count7', NEW.`count7`,'topiccode', NEW.`topiccode`,'topictitle', NEW.`topictitle`,'level1', NEW.`level1`,'level2', NEW.`level2`,'level3', NEW.`level3`,'continious', NEW.`continious`,'total', NEW.`total`,'behave', NEW.`behave`,'entrytime', NEW.`entrytime`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER pibiarea_after_update
    AFTER UPDATE ON `pibiarea`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, new_data, changed_by)
        VALUES(
            'pibiarea',
            'UPDATE',
            NEW.`id`,
            JSON_OBJECT('id', OLD.`id`,'sessionyear', OLD.`sessionyear`,'class', OLD.`class`,'exam', OLD.`exam`,'exam2', OLD.`exam2`,'subcode', OLD.`subcode`,'subname', OLD.`subname`,'pibiarea', OLD.`pibiarea`,'count6', OLD.`count6`,'count7', OLD.`count7`,'topiccode', OLD.`topiccode`,'topictitle', OLD.`topictitle`,'level1', OLD.`level1`,'level2', OLD.`level2`,'level3', OLD.`level3`,'continious', OLD.`continious`,'total', OLD.`total`,'behave', OLD.`behave`,'entrytime', OLD.`entrytime`),
            JSON_OBJECT('id', NEW.`id`,'sessionyear', NEW.`sessionyear`,'class', NEW.`class`,'exam', NEW.`exam`,'exam2', NEW.`exam2`,'subcode', NEW.`subcode`,'subname', NEW.`subname`,'pibiarea', NEW.`pibiarea`,'count6', NEW.`count6`,'count7', NEW.`count7`,'topiccode', NEW.`topiccode`,'topictitle', NEW.`topictitle`,'level1', NEW.`level1`,'level2', NEW.`level2`,'level3', NEW.`level3`,'continious', NEW.`continious`,'total', NEW.`total`,'behave', NEW.`behave`,'entrytime', NEW.`entrytime`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER pibiarea_after_delete
    AFTER DELETE ON `pibiarea`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, changed_by)
        VALUES(
            'pibiarea',
            'DELETE',
            OLD.`id`,
            JSON_OBJECT('id', OLD.`id`,'sessionyear', OLD.`sessionyear`,'class', OLD.`class`,'exam', OLD.`exam`,'exam2', OLD.`exam2`,'subcode', OLD.`subcode`,'subname', OLD.`subname`,'pibiarea', OLD.`pibiarea`,'count6', OLD.`count6`,'count7', OLD.`count7`,'topiccode', OLD.`topiccode`,'topictitle', OLD.`topictitle`,'level1', OLD.`level1`,'level2', OLD.`level2`,'level3', OLD.`level3`,'continious', OLD.`continious`,'total', OLD.`total`,'behave', OLD.`behave`,'entrytime', OLD.`entrytime`),
            COALESCE(@current_user,'system')
        );
    END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `pibientry`
--

DROP TABLE IF EXISTS `pibientry`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pibientry` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sessionyear` int(11) DEFAULT NULL,
  `exam` varchar(25) DEFAULT NULL,
  `subcode` int(11) DEFAULT NULL,
  `subname` varchar(30) DEFAULT NULL,
  `sccode` int(11) DEFAULT NULL,
  `classname` varchar(10) DEFAULT NULL,
  `sectionname` varchar(20) DEFAULT NULL,
  `stid` bigint(20) DEFAULT NULL,
  `roll` int(11) DEFAULT NULL,
  `assesstype` varchar(25) DEFAULT NULL,
  `topicid` int(11) DEFAULT NULL,
  `areacode` int(11) DEFAULT NULL,
  `assessment` int(11) DEFAULT NULL,
  `entryby` varchar(100) DEFAULT NULL,
  `entrytime` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1296289 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
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
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER pibientry_after_insert
    AFTER INSERT ON `pibientry`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, new_data, changed_by)
        VALUES(
            'pibientry',
            'INSERT',
            NEW.`id`,
            JSON_OBJECT('id', NEW.`id`,'sessionyear', NEW.`sessionyear`,'exam', NEW.`exam`,'subcode', NEW.`subcode`,'subname', NEW.`subname`,'sccode', NEW.`sccode`,'classname', NEW.`classname`,'sectionname', NEW.`sectionname`,'stid', NEW.`stid`,'roll', NEW.`roll`,'assesstype', NEW.`assesstype`,'topicid', NEW.`topicid`,'areacode', NEW.`areacode`,'assessment', NEW.`assessment`,'entryby', NEW.`entryby`,'entrytime', NEW.`entrytime`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER pibientry_after_update
    AFTER UPDATE ON `pibientry`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, new_data, changed_by)
        VALUES(
            'pibientry',
            'UPDATE',
            NEW.`id`,
            JSON_OBJECT('id', OLD.`id`,'sessionyear', OLD.`sessionyear`,'exam', OLD.`exam`,'subcode', OLD.`subcode`,'subname', OLD.`subname`,'sccode', OLD.`sccode`,'classname', OLD.`classname`,'sectionname', OLD.`sectionname`,'stid', OLD.`stid`,'roll', OLD.`roll`,'assesstype', OLD.`assesstype`,'topicid', OLD.`topicid`,'areacode', OLD.`areacode`,'assessment', OLD.`assessment`,'entryby', OLD.`entryby`,'entrytime', OLD.`entrytime`),
            JSON_OBJECT('id', NEW.`id`,'sessionyear', NEW.`sessionyear`,'exam', NEW.`exam`,'subcode', NEW.`subcode`,'subname', NEW.`subname`,'sccode', NEW.`sccode`,'classname', NEW.`classname`,'sectionname', NEW.`sectionname`,'stid', NEW.`stid`,'roll', NEW.`roll`,'assesstype', NEW.`assesstype`,'topicid', NEW.`topicid`,'areacode', NEW.`areacode`,'assessment', NEW.`assessment`,'entryby', NEW.`entryby`,'entrytime', NEW.`entrytime`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER pibientry_after_delete
    AFTER DELETE ON `pibientry`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, changed_by)
        VALUES(
            'pibientry',
            'DELETE',
            OLD.`id`,
            JSON_OBJECT('id', OLD.`id`,'sessionyear', OLD.`sessionyear`,'exam', OLD.`exam`,'subcode', OLD.`subcode`,'subname', OLD.`subname`,'sccode', OLD.`sccode`,'classname', OLD.`classname`,'sectionname', OLD.`sectionname`,'stid', OLD.`stid`,'roll', OLD.`roll`,'assesstype', OLD.`assesstype`,'topicid', OLD.`topicid`,'areacode', OLD.`areacode`,'assessment', OLD.`assessment`,'entryby', OLD.`entryby`,'entrytime', OLD.`entrytime`),
            COALESCE(@current_user,'system')
        );
    END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `pibigroup`
--

DROP TABLE IF EXISTS `pibigroup`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pibigroup` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sccode` int(11) NOT NULL,
  `sessionyear` int(11) NOT NULL,
  `classname` varchar(20) NOT NULL,
  `sectionname` varchar(20) NOT NULL,
  `groupname` varchar(100) NOT NULL,
  `rolls` varchar(100) DEFAULT NULL,
  `entryby` varchar(100) NOT NULL,
  `entrytime` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=80 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
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
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER pibigroup_after_insert
    AFTER INSERT ON `pibigroup`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, new_data, changed_by)
        VALUES(
            'pibigroup',
            'INSERT',
            NEW.`id`,
            JSON_OBJECT('id', NEW.`id`,'sccode', NEW.`sccode`,'sessionyear', NEW.`sessionyear`,'classname', NEW.`classname`,'sectionname', NEW.`sectionname`,'groupname', NEW.`groupname`,'rolls', NEW.`rolls`,'entryby', NEW.`entryby`,'entrytime', NEW.`entrytime`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER pibigroup_after_update
    AFTER UPDATE ON `pibigroup`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, new_data, changed_by)
        VALUES(
            'pibigroup',
            'UPDATE',
            NEW.`id`,
            JSON_OBJECT('id', OLD.`id`,'sccode', OLD.`sccode`,'sessionyear', OLD.`sessionyear`,'classname', OLD.`classname`,'sectionname', OLD.`sectionname`,'groupname', OLD.`groupname`,'rolls', OLD.`rolls`,'entryby', OLD.`entryby`,'entrytime', OLD.`entrytime`),
            JSON_OBJECT('id', NEW.`id`,'sccode', NEW.`sccode`,'sessionyear', NEW.`sessionyear`,'classname', NEW.`classname`,'sectionname', NEW.`sectionname`,'groupname', NEW.`groupname`,'rolls', NEW.`rolls`,'entryby', NEW.`entryby`,'entrytime', NEW.`entrytime`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER pibigroup_after_delete
    AFTER DELETE ON `pibigroup`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, changed_by)
        VALUES(
            'pibigroup',
            'DELETE',
            OLD.`id`,
            JSON_OBJECT('id', OLD.`id`,'sccode', OLD.`sccode`,'sessionyear', OLD.`sessionyear`,'classname', OLD.`classname`,'sectionname', OLD.`sectionname`,'groupname', OLD.`groupname`,'rolls', OLD.`rolls`,'entryby', OLD.`entryby`,'entrytime', OLD.`entrytime`),
            COALESCE(@current_user,'system')
        );
    END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `pibiprocess`
--

DROP TABLE IF EXISTS `pibiprocess`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pibiprocess` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sessionyear` varchar(10) DEFAULT NULL,
  `sccode` int(11) DEFAULT NULL,
  `sccategory` varchar(20) DEFAULT NULL,
  `slot` varchar(20) NOT NULL DEFAULT 'School',
  `exam` varchar(20) DEFAULT NULL,
  `classname` varchar(10) DEFAULT NULL,
  `sectionname` varchar(20) DEFAULT NULL,
  `subcode` int(11) DEFAULT NULL,
  `subname` varchar(50) DEFAULT NULL,
  `assess` varchar(30) DEFAULT NULL,
  `txt` varchar(200) DEFAULT NULL,
  `jobdone` int(11) DEFAULT 0,
  `jobreq` int(11) DEFAULT 0,
  `jobrate` double DEFAULT 0,
  `status` int(11) DEFAULT NULL,
  `jobby` varchar(100) DEFAULT NULL,
  `jobdate` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=64916 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
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
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER pibiprocess_after_insert
    AFTER INSERT ON `pibiprocess`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, new_data, changed_by)
        VALUES(
            'pibiprocess',
            'INSERT',
            NEW.`id`,
            JSON_OBJECT('id', NEW.`id`,'sessionyear', NEW.`sessionyear`,'sccode', NEW.`sccode`,'sccategory', NEW.`sccategory`,'slot', NEW.`slot`,'exam', NEW.`exam`,'classname', NEW.`classname`,'sectionname', NEW.`sectionname`,'subcode', NEW.`subcode`,'subname', NEW.`subname`,'assess', NEW.`assess`,'txt', NEW.`txt`,'jobdone', NEW.`jobdone`,'jobreq', NEW.`jobreq`,'jobrate', NEW.`jobrate`,'status', NEW.`status`,'jobby', NEW.`jobby`,'jobdate', NEW.`jobdate`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER pibiprocess_after_update
    AFTER UPDATE ON `pibiprocess`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, new_data, changed_by)
        VALUES(
            'pibiprocess',
            'UPDATE',
            NEW.`id`,
            JSON_OBJECT('id', OLD.`id`,'sessionyear', OLD.`sessionyear`,'sccode', OLD.`sccode`,'sccategory', OLD.`sccategory`,'slot', OLD.`slot`,'exam', OLD.`exam`,'classname', OLD.`classname`,'sectionname', OLD.`sectionname`,'subcode', OLD.`subcode`,'subname', OLD.`subname`,'assess', OLD.`assess`,'txt', OLD.`txt`,'jobdone', OLD.`jobdone`,'jobreq', OLD.`jobreq`,'jobrate', OLD.`jobrate`,'status', OLD.`status`,'jobby', OLD.`jobby`,'jobdate', OLD.`jobdate`),
            JSON_OBJECT('id', NEW.`id`,'sessionyear', NEW.`sessionyear`,'sccode', NEW.`sccode`,'sccategory', NEW.`sccategory`,'slot', NEW.`slot`,'exam', NEW.`exam`,'classname', NEW.`classname`,'sectionname', NEW.`sectionname`,'subcode', NEW.`subcode`,'subname', NEW.`subname`,'assess', NEW.`assess`,'txt', NEW.`txt`,'jobdone', NEW.`jobdone`,'jobreq', NEW.`jobreq`,'jobrate', NEW.`jobrate`,'status', NEW.`status`,'jobby', NEW.`jobby`,'jobdate', NEW.`jobdate`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER pibiprocess_after_delete
    AFTER DELETE ON `pibiprocess`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, changed_by)
        VALUES(
            'pibiprocess',
            'DELETE',
            OLD.`id`,
            JSON_OBJECT('id', OLD.`id`,'sessionyear', OLD.`sessionyear`,'sccode', OLD.`sccode`,'sccategory', OLD.`sccategory`,'slot', OLD.`slot`,'exam', OLD.`exam`,'classname', OLD.`classname`,'sectionname', OLD.`sectionname`,'subcode', OLD.`subcode`,'subname', OLD.`subname`,'assess', OLD.`assess`,'txt', OLD.`txt`,'jobdone', OLD.`jobdone`,'jobreq', OLD.`jobreq`,'jobrate', OLD.`jobrate`,'status', OLD.`status`,'jobby', OLD.`jobby`,'jobdate', OLD.`jobdate`),
            COALESCE(@current_user,'system')
        );
    END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `pibiskill`
--

DROP TABLE IF EXISTS `pibiskill`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pibiskill` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `year` varchar(11) DEFAULT NULL,
  `classname` varchar(11) DEFAULT NULL,
  `sectionname` varchar(11) DEFAULT NULL,
  `subj` int(11) DEFAULT NULL,
  `skillid` int(11) DEFAULT NULL,
  `skillno` varchar(11) DEFAULT NULL,
  `skilltitle` varchar(500) DEFAULT NULL,
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER pibiskill_after_insert
    AFTER INSERT ON `pibiskill`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, new_data, changed_by)
        VALUES(
            'pibiskill',
            'INSERT',
            NEW.`id`,
            JSON_OBJECT('id', NEW.`id`,'year', NEW.`year`,'classname', NEW.`classname`,'sectionname', NEW.`sectionname`,'subj', NEW.`subj`,'skillid', NEW.`skillid`,'skillno', NEW.`skillno`,'skilltitle', NEW.`skilltitle`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER pibiskill_after_update
    AFTER UPDATE ON `pibiskill`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, new_data, changed_by)
        VALUES(
            'pibiskill',
            'UPDATE',
            NEW.`id`,
            JSON_OBJECT('id', OLD.`id`,'year', OLD.`year`,'classname', OLD.`classname`,'sectionname', OLD.`sectionname`,'subj', OLD.`subj`,'skillid', OLD.`skillid`,'skillno', OLD.`skillno`,'skilltitle', OLD.`skilltitle`),
            JSON_OBJECT('id', NEW.`id`,'year', NEW.`year`,'classname', NEW.`classname`,'sectionname', NEW.`sectionname`,'subj', NEW.`subj`,'skillid', NEW.`skillid`,'skillno', NEW.`skillno`,'skilltitle', NEW.`skilltitle`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER pibiskill_after_delete
    AFTER DELETE ON `pibiskill`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, changed_by)
        VALUES(
            'pibiskill',
            'DELETE',
            OLD.`id`,
            JSON_OBJECT('id', OLD.`id`,'year', OLD.`year`,'classname', OLD.`classname`,'sectionname', OLD.`sectionname`,'subj', OLD.`subj`,'skillid', OLD.`skillid`,'skillno', OLD.`skillno`,'skilltitle', OLD.`skilltitle`),
            COALESCE(@current_user,'system')
        );
    END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `pibitopics`
--

DROP TABLE IF EXISTS `pibitopics`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pibitopics` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sessionyear` int(11) DEFAULT NULL,
  `class` varchar(20) DEFAULT NULL,
  `exam` varchar(20) DEFAULT NULL,
  `exam2` varchar(20) DEFAULT NULL,
  `subcode` int(11) NOT NULL DEFAULT 0,
  `subname` varchar(30) DEFAULT NULL,
  `pibiarea` varchar(50) DEFAULT NULL,
  `skillcode` varchar(15) DEFAULT NULL,
  `skilltitle` varchar(500) DEFAULT NULL,
  `topiccode` varchar(15) DEFAULT NULL,
  `topictitle` varchar(255) DEFAULT NULL,
  `level1` varchar(255) DEFAULT NULL,
  `level2` varchar(255) DEFAULT NULL,
  `level3` varchar(255) DEFAULT NULL,
  `continious` int(11) NOT NULL DEFAULT 0,
  `total` int(11) NOT NULL DEFAULT 0,
  `behave` int(11) NOT NULL DEFAULT 0,
  `half_yearly` int(11) NOT NULL DEFAULT 0,
  `entrytime` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=534 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
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
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER pibitopics_after_insert
    AFTER INSERT ON `pibitopics`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, new_data, changed_by)
        VALUES(
            'pibitopics',
            'INSERT',
            NEW.`id`,
            JSON_OBJECT('id', NEW.`id`,'sessionyear', NEW.`sessionyear`,'class', NEW.`class`,'exam', NEW.`exam`,'exam2', NEW.`exam2`,'subcode', NEW.`subcode`,'subname', NEW.`subname`,'pibiarea', NEW.`pibiarea`,'skillcode', NEW.`skillcode`,'skilltitle', NEW.`skilltitle`,'topiccode', NEW.`topiccode`,'topictitle', NEW.`topictitle`,'level1', NEW.`level1`,'level2', NEW.`level2`,'level3', NEW.`level3`,'continious', NEW.`continious`,'total', NEW.`total`,'behave', NEW.`behave`,'half_yearly', NEW.`half_yearly`,'entrytime', NEW.`entrytime`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER pibitopics_after_update
    AFTER UPDATE ON `pibitopics`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, new_data, changed_by)
        VALUES(
            'pibitopics',
            'UPDATE',
            NEW.`id`,
            JSON_OBJECT('id', OLD.`id`,'sessionyear', OLD.`sessionyear`,'class', OLD.`class`,'exam', OLD.`exam`,'exam2', OLD.`exam2`,'subcode', OLD.`subcode`,'subname', OLD.`subname`,'pibiarea', OLD.`pibiarea`,'skillcode', OLD.`skillcode`,'skilltitle', OLD.`skilltitle`,'topiccode', OLD.`topiccode`,'topictitle', OLD.`topictitle`,'level1', OLD.`level1`,'level2', OLD.`level2`,'level3', OLD.`level3`,'continious', OLD.`continious`,'total', OLD.`total`,'behave', OLD.`behave`,'half_yearly', OLD.`half_yearly`,'entrytime', OLD.`entrytime`),
            JSON_OBJECT('id', NEW.`id`,'sessionyear', NEW.`sessionyear`,'class', NEW.`class`,'exam', NEW.`exam`,'exam2', NEW.`exam2`,'subcode', NEW.`subcode`,'subname', NEW.`subname`,'pibiarea', NEW.`pibiarea`,'skillcode', NEW.`skillcode`,'skilltitle', NEW.`skilltitle`,'topiccode', NEW.`topiccode`,'topictitle', NEW.`topictitle`,'level1', NEW.`level1`,'level2', NEW.`level2`,'level3', NEW.`level3`,'continious', NEW.`continious`,'total', NEW.`total`,'behave', NEW.`behave`,'half_yearly', NEW.`half_yearly`,'entrytime', NEW.`entrytime`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER pibitopics_after_delete
    AFTER DELETE ON `pibitopics`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, changed_by)
        VALUES(
            'pibitopics',
            'DELETE',
            OLD.`id`,
            JSON_OBJECT('id', OLD.`id`,'sessionyear', OLD.`sessionyear`,'class', OLD.`class`,'exam', OLD.`exam`,'exam2', OLD.`exam2`,'subcode', OLD.`subcode`,'subname', OLD.`subname`,'pibiarea', OLD.`pibiarea`,'skillcode', OLD.`skillcode`,'skilltitle', OLD.`skilltitle`,'topiccode', OLD.`topiccode`,'topictitle', OLD.`topictitle`,'level1', OLD.`level1`,'level2', OLD.`level2`,'level3', OLD.`level3`,'continious', OLD.`continious`,'total', OLD.`total`,'behave', OLD.`behave`,'half_yearly', OLD.`half_yearly`,'entrytime', OLD.`entrytime`),
            COALESCE(@current_user,'system')
        );
    END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `profile_track`
--

DROP TABLE IF EXISTS `profile_track`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `profile_track` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sl` int(11) DEFAULT NULL,
  `modulename` varchar(50) DEFAULT NULL,
  `tablename` varchar(20) DEFAULT NULL,
  `field_name` varchar(20) DEFAULT NULL,
  `check_type` varchar(20) DEFAULT NULL COMMENT 'Count, Sum, Length',
  `perc` int(11) DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
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
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER profile_track_after_insert
    AFTER INSERT ON `profile_track`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, new_data, changed_by)
        VALUES(
            'profile_track',
            'INSERT',
            NEW.`id`,
            JSON_OBJECT('id', NEW.`id`,'sl', NEW.`sl`,'modulename', NEW.`modulename`,'tablename', NEW.`tablename`,'field_name', NEW.`field_name`,'check_type', NEW.`check_type`,'perc', NEW.`perc`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER profile_track_after_update
    AFTER UPDATE ON `profile_track`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, new_data, changed_by)
        VALUES(
            'profile_track',
            'UPDATE',
            NEW.`id`,
            JSON_OBJECT('id', OLD.`id`,'sl', OLD.`sl`,'modulename', OLD.`modulename`,'tablename', OLD.`tablename`,'field_name', OLD.`field_name`,'check_type', OLD.`check_type`,'perc', OLD.`perc`),
            JSON_OBJECT('id', NEW.`id`,'sl', NEW.`sl`,'modulename', NEW.`modulename`,'tablename', NEW.`tablename`,'field_name', NEW.`field_name`,'check_type', NEW.`check_type`,'perc', NEW.`perc`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER profile_track_after_delete
    AFTER DELETE ON `profile_track`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, changed_by)
        VALUES(
            'profile_track',
            'DELETE',
            OLD.`id`,
            JSON_OBJECT('id', OLD.`id`,'sl', OLD.`sl`,'modulename', OLD.`modulename`,'tablename', OLD.`tablename`,'field_name', OLD.`field_name`,'check_type', OLD.`check_type`,'perc', OLD.`perc`),
            COALESCE(@current_user,'system')
        );
    END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `proxylist`
--

DROP TABLE IF EXISTS `proxylist`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `proxylist` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sccode` int(11) NOT NULL,
  `date` date NOT NULL,
  `shift` varchar(11) NOT NULL,
  `period` varchar(11) NOT NULL,
  `classname` varchar(10) NOT NULL,
  `mtid` int(11) NOT NULL,
  `ptid` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=203 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
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
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER proxylist_after_insert
    AFTER INSERT ON `proxylist`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, new_data, changed_by)
        VALUES(
            'proxylist',
            'INSERT',
            NEW.`id`,
            JSON_OBJECT('id', NEW.`id`,'sccode', NEW.`sccode`,'date', NEW.`date`,'shift', NEW.`shift`,'period', NEW.`period`,'classname', NEW.`classname`,'mtid', NEW.`mtid`,'ptid', NEW.`ptid`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER proxylist_after_update
    AFTER UPDATE ON `proxylist`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, new_data, changed_by)
        VALUES(
            'proxylist',
            'UPDATE',
            NEW.`id`,
            JSON_OBJECT('id', OLD.`id`,'sccode', OLD.`sccode`,'date', OLD.`date`,'shift', OLD.`shift`,'period', OLD.`period`,'classname', OLD.`classname`,'mtid', OLD.`mtid`,'ptid', OLD.`ptid`),
            JSON_OBJECT('id', NEW.`id`,'sccode', NEW.`sccode`,'date', NEW.`date`,'shift', NEW.`shift`,'period', NEW.`period`,'classname', NEW.`classname`,'mtid', NEW.`mtid`,'ptid', NEW.`ptid`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER proxylist_after_delete
    AFTER DELETE ON `proxylist`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, changed_by)
        VALUES(
            'proxylist',
            'DELETE',
            OLD.`id`,
            JSON_OBJECT('id', OLD.`id`,'sccode', OLD.`sccode`,'date', OLD.`date`,'shift', OLD.`shift`,'period', OLD.`period`,'classname', OLD.`classname`,'mtid', OLD.`mtid`,'ptid', OLD.`ptid`),
            COALESCE(@current_user,'system')
        );
    END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `qrcodelogin`
--

DROP TABLE IF EXISTS `qrcodelogin`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `qrcodelogin` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `token` varchar(30) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `generatetime` datetime DEFAULT NULL,
  `logintime` datetime DEFAULT NULL,
  `status` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=44581 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
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
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER qrcodelogin_after_insert
    AFTER INSERT ON `qrcodelogin`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, new_data, changed_by)
        VALUES(
            'qrcodelogin',
            'INSERT',
            NEW.`id`,
            JSON_OBJECT('id', NEW.`id`,'token', NEW.`token`,'email', NEW.`email`,'generatetime', NEW.`generatetime`,'logintime', NEW.`logintime`,'status', NEW.`status`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER qrcodelogin_after_update
    AFTER UPDATE ON `qrcodelogin`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, new_data, changed_by)
        VALUES(
            'qrcodelogin',
            'UPDATE',
            NEW.`id`,
            JSON_OBJECT('id', OLD.`id`,'token', OLD.`token`,'email', OLD.`email`,'generatetime', OLD.`generatetime`,'logintime', OLD.`logintime`,'status', OLD.`status`),
            JSON_OBJECT('id', NEW.`id`,'token', NEW.`token`,'email', NEW.`email`,'generatetime', NEW.`generatetime`,'logintime', NEW.`logintime`,'status', NEW.`status`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER qrcodelogin_after_delete
    AFTER DELETE ON `qrcodelogin`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, changed_by)
        VALUES(
            'qrcodelogin',
            'DELETE',
            OLD.`id`,
            JSON_OBJECT('id', OLD.`id`,'token', OLD.`token`,'email', OLD.`email`,'generatetime', OLD.`generatetime`,'logintime', OLD.`logintime`,'status', OLD.`status`),
            COALESCE(@current_user,'system')
        );
    END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `quiz_attempts`
--

DROP TABLE IF EXISTS `quiz_attempts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `quiz_attempts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sccode` int(11) NOT NULL,
  `stid` varchar(20) NOT NULL,
  `quiz_id` int(11) NOT NULL,
  `attempted_at` datetime NOT NULL DEFAULT current_timestamp(),
  `score` decimal(5,2) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
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
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER quiz_attempts_after_insert
    AFTER INSERT ON `quiz_attempts`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, new_data, changed_by)
        VALUES(
            'quiz_attempts',
            'INSERT',
            NEW.`id`,
            JSON_OBJECT('id', NEW.`id`,'sccode', NEW.`sccode`,'stid', NEW.`stid`,'quiz_id', NEW.`quiz_id`,'attempted_at', NEW.`attempted_at`,'score', NEW.`score`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER quiz_attempts_after_update
    AFTER UPDATE ON `quiz_attempts`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, new_data, changed_by)
        VALUES(
            'quiz_attempts',
            'UPDATE',
            NEW.`id`,
            JSON_OBJECT('id', OLD.`id`,'sccode', OLD.`sccode`,'stid', OLD.`stid`,'quiz_id', OLD.`quiz_id`,'attempted_at', OLD.`attempted_at`,'score', OLD.`score`),
            JSON_OBJECT('id', NEW.`id`,'sccode', NEW.`sccode`,'stid', NEW.`stid`,'quiz_id', NEW.`quiz_id`,'attempted_at', NEW.`attempted_at`,'score', NEW.`score`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER quiz_attempts_after_delete
    AFTER DELETE ON `quiz_attempts`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, changed_by)
        VALUES(
            'quiz_attempts',
            'DELETE',
            OLD.`id`,
            JSON_OBJECT('id', OLD.`id`,'sccode', OLD.`sccode`,'stid', OLD.`stid`,'quiz_id', OLD.`quiz_id`,'attempted_at', OLD.`attempted_at`,'score', OLD.`score`),
            COALESCE(@current_user,'system')
        );
    END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `quiz_questions`
--

DROP TABLE IF EXISTS `quiz_questions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `quiz_questions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `quiz_id` int(11) DEFAULT NULL,
  `question_text` text DEFAULT NULL,
  `option_a` text DEFAULT NULL,
  `option_b` text DEFAULT NULL,
  `option_c` text DEFAULT NULL,
  `option_d` text DEFAULT NULL,
  `correct_answer` char(1) DEFAULT NULL,
  `topic_code` varchar(50) DEFAULT NULL,
  `difficulty_level` enum('very_easy','easy','medium','hard','very_hard') DEFAULT 'medium',
  `expected_time` int(11) DEFAULT 60,
  `bloom_level` enum('remember','understand','apply','analyze','evaluate','create') DEFAULT NULL,
  `skill_tag` varchar(100) DEFAULT NULL,
  `xp` int(11) DEFAULT 10,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=46 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
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
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER quiz_questions_after_insert
    AFTER INSERT ON `quiz_questions`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, new_data, changed_by)
        VALUES(
            'quiz_questions',
            'INSERT',
            NEW.`id`,
            JSON_OBJECT('id', NEW.`id`,'quiz_id', NEW.`quiz_id`,'question_text', NEW.`question_text`,'option_a', NEW.`option_a`,'option_b', NEW.`option_b`,'option_c', NEW.`option_c`,'option_d', NEW.`option_d`,'correct_answer', NEW.`correct_answer`,'topic_code', NEW.`topic_code`,'difficulty_level', NEW.`difficulty_level`,'expected_time', NEW.`expected_time`,'bloom_level', NEW.`bloom_level`,'skill_tag', NEW.`skill_tag`,'xp', NEW.`xp`,'created_at', NEW.`created_at`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER quiz_questions_after_update
    AFTER UPDATE ON `quiz_questions`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, new_data, changed_by)
        VALUES(
            'quiz_questions',
            'UPDATE',
            NEW.`id`,
            JSON_OBJECT('id', OLD.`id`,'quiz_id', OLD.`quiz_id`,'question_text', OLD.`question_text`,'option_a', OLD.`option_a`,'option_b', OLD.`option_b`,'option_c', OLD.`option_c`,'option_d', OLD.`option_d`,'correct_answer', OLD.`correct_answer`,'topic_code', OLD.`topic_code`,'difficulty_level', OLD.`difficulty_level`,'expected_time', OLD.`expected_time`,'bloom_level', OLD.`bloom_level`,'skill_tag', OLD.`skill_tag`,'xp', OLD.`xp`,'created_at', OLD.`created_at`),
            JSON_OBJECT('id', NEW.`id`,'quiz_id', NEW.`quiz_id`,'question_text', NEW.`question_text`,'option_a', NEW.`option_a`,'option_b', NEW.`option_b`,'option_c', NEW.`option_c`,'option_d', NEW.`option_d`,'correct_answer', NEW.`correct_answer`,'topic_code', NEW.`topic_code`,'difficulty_level', NEW.`difficulty_level`,'expected_time', NEW.`expected_time`,'bloom_level', NEW.`bloom_level`,'skill_tag', NEW.`skill_tag`,'xp', NEW.`xp`,'created_at', NEW.`created_at`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER quiz_questions_after_delete
    AFTER DELETE ON `quiz_questions`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, changed_by)
        VALUES(
            'quiz_questions',
            'DELETE',
            OLD.`id`,
            JSON_OBJECT('id', OLD.`id`,'quiz_id', OLD.`quiz_id`,'question_text', OLD.`question_text`,'option_a', OLD.`option_a`,'option_b', OLD.`option_b`,'option_c', OLD.`option_c`,'option_d', OLD.`option_d`,'correct_answer', OLD.`correct_answer`,'topic_code', OLD.`topic_code`,'difficulty_level', OLD.`difficulty_level`,'expected_time', OLD.`expected_time`,'bloom_level', OLD.`bloom_level`,'skill_tag', OLD.`skill_tag`,'xp', OLD.`xp`,'created_at', OLD.`created_at`),
            COALESCE(@current_user,'system')
        );
    END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `quizzes`
--

DROP TABLE IF EXISTS `quizzes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `quizzes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `quiz_name` varchar(255) NOT NULL,
  `class` varchar(10) NOT NULL,
  `quiz_type` enum('regular','diagnostic') NOT NULL DEFAULT 'regular',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
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
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER quizzes_after_insert
    AFTER INSERT ON `quizzes`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, new_data, changed_by)
        VALUES(
            'quizzes',
            'INSERT',
            NEW.`id`,
            JSON_OBJECT('id', NEW.`id`,'quiz_name', NEW.`quiz_name`,'class', NEW.`class`,'quiz_type', NEW.`quiz_type`,'created_at', NEW.`created_at`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER quizzes_after_update
    AFTER UPDATE ON `quizzes`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, new_data, changed_by)
        VALUES(
            'quizzes',
            'UPDATE',
            NEW.`id`,
            JSON_OBJECT('id', OLD.`id`,'quiz_name', OLD.`quiz_name`,'class', OLD.`class`,'quiz_type', OLD.`quiz_type`,'created_at', OLD.`created_at`),
            JSON_OBJECT('id', NEW.`id`,'quiz_name', NEW.`quiz_name`,'class', NEW.`class`,'quiz_type', NEW.`quiz_type`,'created_at', NEW.`created_at`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER quizzes_after_delete
    AFTER DELETE ON `quizzes`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, changed_by)
        VALUES(
            'quizzes',
            'DELETE',
            OLD.`id`,
            JSON_OBJECT('id', OLD.`id`,'quiz_name', OLD.`quiz_name`,'class', OLD.`class`,'quiz_type', OLD.`quiz_type`,'created_at', OLD.`created_at`),
            COALESCE(@current_user,'system')
        );
    END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `refbook`
--

DROP TABLE IF EXISTS `refbook`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `refbook` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sccode` int(11) DEFAULT NULL,
  `slot` varchar(20) DEFAULT NULL,
  `sessionyear` int(11) DEFAULT NULL,
  `refno` varchar(30) DEFAULT NULL,
  `date` date DEFAULT NULL,
  `year` int(11) DEFAULT NULL,
  `month` int(11) DEFAULT NULL,
  `category` varchar(20) DEFAULT NULL,
  `partid` int(11) DEFAULT NULL,
  `title` varchar(150) DEFAULT NULL,
  `descrip` varchar(500) DEFAULT NULL,
  `module` varchar(100) NOT NULL DEFAULT '',
  `dbtable` varchar(100) NOT NULL DEFAULT '',
  `sqltext` varchar(1000) NOT NULL DEFAULT '',
  `entryby` varchar(100) DEFAULT NULL,
  `entrytime` datetime DEFAULT NULL,
  `modifieddate` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=85 DEFAULT CHARSET=utf16 COLLATE=utf16_unicode_ci;
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
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER refbook_after_insert
    AFTER INSERT ON `refbook`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, new_data, changed_by)
        VALUES(
            'refbook',
            'INSERT',
            NEW.`id`,
            JSON_OBJECT('id', NEW.`id`,'sccode', NEW.`sccode`,'slot', NEW.`slot`,'sessionyear', NEW.`sessionyear`,'refno', NEW.`refno`,'date', NEW.`date`,'year', NEW.`year`,'month', NEW.`month`,'category', NEW.`category`,'partid', NEW.`partid`,'title', NEW.`title`,'descrip', NEW.`descrip`,'module', NEW.`module`,'dbtable', NEW.`dbtable`,'sqltext', NEW.`sqltext`,'entryby', NEW.`entryby`,'entrytime', NEW.`entrytime`,'modifieddate', NEW.`modifieddate`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER refbook_after_update
    AFTER UPDATE ON `refbook`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, new_data, changed_by)
        VALUES(
            'refbook',
            'UPDATE',
            NEW.`id`,
            JSON_OBJECT('id', OLD.`id`,'sccode', OLD.`sccode`,'slot', OLD.`slot`,'sessionyear', OLD.`sessionyear`,'refno', OLD.`refno`,'date', OLD.`date`,'year', OLD.`year`,'month', OLD.`month`,'category', OLD.`category`,'partid', OLD.`partid`,'title', OLD.`title`,'descrip', OLD.`descrip`,'module', OLD.`module`,'dbtable', OLD.`dbtable`,'sqltext', OLD.`sqltext`,'entryby', OLD.`entryby`,'entrytime', OLD.`entrytime`,'modifieddate', OLD.`modifieddate`),
            JSON_OBJECT('id', NEW.`id`,'sccode', NEW.`sccode`,'slot', NEW.`slot`,'sessionyear', NEW.`sessionyear`,'refno', NEW.`refno`,'date', NEW.`date`,'year', NEW.`year`,'month', NEW.`month`,'category', NEW.`category`,'partid', NEW.`partid`,'title', NEW.`title`,'descrip', NEW.`descrip`,'module', NEW.`module`,'dbtable', NEW.`dbtable`,'sqltext', NEW.`sqltext`,'entryby', NEW.`entryby`,'entrytime', NEW.`entrytime`,'modifieddate', NEW.`modifieddate`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER refbook_after_delete
    AFTER DELETE ON `refbook`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, changed_by)
        VALUES(
            'refbook',
            'DELETE',
            OLD.`id`,
            JSON_OBJECT('id', OLD.`id`,'sccode', OLD.`sccode`,'slot', OLD.`slot`,'sessionyear', OLD.`sessionyear`,'refno', OLD.`refno`,'date', OLD.`date`,'year', OLD.`year`,'month', OLD.`month`,'category', OLD.`category`,'partid', OLD.`partid`,'title', OLD.`title`,'descrip', OLD.`descrip`,'module', OLD.`module`,'dbtable', OLD.`dbtable`,'sqltext', OLD.`sqltext`,'entryby', OLD.`entryby`,'entrytime', OLD.`entrytime`,'modifieddate', OLD.`modifieddate`),
            COALESCE(@current_user,'system')
        );
    END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `reqnew`
--

DROP TABLE IF EXISTS `reqnew`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `reqnew` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `reqname` varchar(100) DEFAULT NULL,
  `rank` varchar(30) DEFAULT NULL,
  `scname` varchar(120) DEFAULT NULL,
  `sccode` int(11) DEFAULT NULL,
  `mno` varchar(11) DEFAULT NULL,
  `email` varchar(120) DEFAULT NULL,
  `reqtime` datetime DEFAULT NULL,
  `ipaddress` varchar(15) DEFAULT NULL,
  `browser` varchar(200) DEFAULT NULL,
  `comments` varchar(500) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
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
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER reqnew_after_insert
    AFTER INSERT ON `reqnew`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, new_data, changed_by)
        VALUES(
            'reqnew',
            'INSERT',
            NEW.`id`,
            JSON_OBJECT('id', NEW.`id`,'reqname', NEW.`reqname`,'rank', NEW.`rank`,'scname', NEW.`scname`,'sccode', NEW.`sccode`,'mno', NEW.`mno`,'email', NEW.`email`,'reqtime', NEW.`reqtime`,'ipaddress', NEW.`ipaddress`,'browser', NEW.`browser`,'comments', NEW.`comments`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER reqnew_after_update
    AFTER UPDATE ON `reqnew`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, new_data, changed_by)
        VALUES(
            'reqnew',
            'UPDATE',
            NEW.`id`,
            JSON_OBJECT('id', OLD.`id`,'reqname', OLD.`reqname`,'rank', OLD.`rank`,'scname', OLD.`scname`,'sccode', OLD.`sccode`,'mno', OLD.`mno`,'email', OLD.`email`,'reqtime', OLD.`reqtime`,'ipaddress', OLD.`ipaddress`,'browser', OLD.`browser`,'comments', OLD.`comments`),
            JSON_OBJECT('id', NEW.`id`,'reqname', NEW.`reqname`,'rank', NEW.`rank`,'scname', NEW.`scname`,'sccode', NEW.`sccode`,'mno', NEW.`mno`,'email', NEW.`email`,'reqtime', NEW.`reqtime`,'ipaddress', NEW.`ipaddress`,'browser', NEW.`browser`,'comments', NEW.`comments`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER reqnew_after_delete
    AFTER DELETE ON `reqnew`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, changed_by)
        VALUES(
            'reqnew',
            'DELETE',
            OLD.`id`,
            JSON_OBJECT('id', OLD.`id`,'reqname', OLD.`reqname`,'rank', OLD.`rank`,'scname', OLD.`scname`,'sccode', OLD.`sccode`,'mno', OLD.`mno`,'email', OLD.`email`,'reqtime', OLD.`reqtime`,'ipaddress', OLD.`ipaddress`,'browser', OLD.`browser`,'comments', OLD.`comments`),
            COALESCE(@current_user,'system')
        );
    END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `reqquery`
--

DROP TABLE IF EXISTS `reqquery`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `reqquery` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `reqname` varchar(120) DEFAULT NULL,
  `email` varchar(120) DEFAULT NULL,
  `msg` varchar(500) DEFAULT NULL,
  `reqtime` datetime DEFAULT NULL,
  `ipaddress` varchar(15) DEFAULT NULL,
  `browser` varchar(200) DEFAULT NULL,
  `comments` varchar(500) DEFAULT NULL,
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER reqquery_after_insert
    AFTER INSERT ON `reqquery`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, new_data, changed_by)
        VALUES(
            'reqquery',
            'INSERT',
            NEW.`id`,
            JSON_OBJECT('id', NEW.`id`,'reqname', NEW.`reqname`,'email', NEW.`email`,'msg', NEW.`msg`,'reqtime', NEW.`reqtime`,'ipaddress', NEW.`ipaddress`,'browser', NEW.`browser`,'comments', NEW.`comments`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER reqquery_after_update
    AFTER UPDATE ON `reqquery`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, new_data, changed_by)
        VALUES(
            'reqquery',
            'UPDATE',
            NEW.`id`,
            JSON_OBJECT('id', OLD.`id`,'reqname', OLD.`reqname`,'email', OLD.`email`,'msg', OLD.`msg`,'reqtime', OLD.`reqtime`,'ipaddress', OLD.`ipaddress`,'browser', OLD.`browser`,'comments', OLD.`comments`),
            JSON_OBJECT('id', NEW.`id`,'reqname', NEW.`reqname`,'email', NEW.`email`,'msg', NEW.`msg`,'reqtime', NEW.`reqtime`,'ipaddress', NEW.`ipaddress`,'browser', NEW.`browser`,'comments', NEW.`comments`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER reqquery_after_delete
    AFTER DELETE ON `reqquery`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, changed_by)
        VALUES(
            'reqquery',
            'DELETE',
            OLD.`id`,
            JSON_OBJECT('id', OLD.`id`,'reqname', OLD.`reqname`,'email', OLD.`email`,'msg', OLD.`msg`,'reqtime', OLD.`reqtime`,'ipaddress', OLD.`ipaddress`,'browser', OLD.`browser`,'comments', OLD.`comments`),
            COALESCE(@current_user,'system')
        );
    END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `resources`
--

DROP TABLE IF EXISTS `resources`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `resources` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `topic_code` varchar(50) NOT NULL,
  `resource_link` varchar(255) NOT NULL,
  `resource_type` enum('video','pdf','quiz','text','interactive') NOT NULL DEFAULT 'pdf',
  `description` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
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
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER resources_after_insert
    AFTER INSERT ON `resources`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, new_data, changed_by)
        VALUES(
            'resources',
            'INSERT',
            NEW.`id`,
            JSON_OBJECT('id', NEW.`id`,'topic_code', NEW.`topic_code`,'resource_link', NEW.`resource_link`,'resource_type', NEW.`resource_type`,'description', NEW.`description`,'created_at', NEW.`created_at`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER resources_after_update
    AFTER UPDATE ON `resources`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, new_data, changed_by)
        VALUES(
            'resources',
            'UPDATE',
            NEW.`id`,
            JSON_OBJECT('id', OLD.`id`,'topic_code', OLD.`topic_code`,'resource_link', OLD.`resource_link`,'resource_type', OLD.`resource_type`,'description', OLD.`description`,'created_at', OLD.`created_at`),
            JSON_OBJECT('id', NEW.`id`,'topic_code', NEW.`topic_code`,'resource_link', NEW.`resource_link`,'resource_type', NEW.`resource_type`,'description', NEW.`description`,'created_at', NEW.`created_at`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER resources_after_delete
    AFTER DELETE ON `resources`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, changed_by)
        VALUES(
            'resources',
            'DELETE',
            OLD.`id`,
            JSON_OBJECT('id', OLD.`id`,'topic_code', OLD.`topic_code`,'resource_link', OLD.`resource_link`,'resource_type', OLD.`resource_type`,'description', OLD.`description`,'created_at', OLD.`created_at`),
            COALESCE(@current_user,'system')
        );
    END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `result_progress`
--

DROP TABLE IF EXISTS `result_progress`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `result_progress` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sessionyear` varchar(10) DEFAULT NULL,
  `sccode` int(11) DEFAULT NULL,
  `sccategory` varchar(20) DEFAULT NULL,
  `slot` varchar(20) NOT NULL DEFAULT 'School',
  `exam` varchar(20) DEFAULT NULL,
  `classname` varchar(10) DEFAULT NULL,
  `sectionname` varchar(20) DEFAULT NULL,
  `subcode` int(11) DEFAULT NULL,
  `subname` varchar(50) DEFAULT NULL,
  `step` int(11) DEFAULT NULL,
  `txt` varchar(200) DEFAULT NULL,
  `jobdone` int(11) DEFAULT 0,
  `jobreq` int(11) DEFAULT 0,
  `jobrate` double DEFAULT 0,
  `status` int(11) DEFAULT NULL,
  `jobby` varchar(100) DEFAULT NULL,
  `jobdate` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=49364 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
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
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER result_progress_after_insert
    AFTER INSERT ON `result_progress`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, new_data, changed_by)
        VALUES(
            'result_progress',
            'INSERT',
            NEW.`id`,
            JSON_OBJECT('id', NEW.`id`,'sessionyear', NEW.`sessionyear`,'sccode', NEW.`sccode`,'sccategory', NEW.`sccategory`,'slot', NEW.`slot`,'exam', NEW.`exam`,'classname', NEW.`classname`,'sectionname', NEW.`sectionname`,'subcode', NEW.`subcode`,'subname', NEW.`subname`,'step', NEW.`step`,'txt', NEW.`txt`,'jobdone', NEW.`jobdone`,'jobreq', NEW.`jobreq`,'jobrate', NEW.`jobrate`,'status', NEW.`status`,'jobby', NEW.`jobby`,'jobdate', NEW.`jobdate`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER result_progress_after_update
    AFTER UPDATE ON `result_progress`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, new_data, changed_by)
        VALUES(
            'result_progress',
            'UPDATE',
            NEW.`id`,
            JSON_OBJECT('id', OLD.`id`,'sessionyear', OLD.`sessionyear`,'sccode', OLD.`sccode`,'sccategory', OLD.`sccategory`,'slot', OLD.`slot`,'exam', OLD.`exam`,'classname', OLD.`classname`,'sectionname', OLD.`sectionname`,'subcode', OLD.`subcode`,'subname', OLD.`subname`,'step', OLD.`step`,'txt', OLD.`txt`,'jobdone', OLD.`jobdone`,'jobreq', OLD.`jobreq`,'jobrate', OLD.`jobrate`,'status', OLD.`status`,'jobby', OLD.`jobby`,'jobdate', OLD.`jobdate`),
            JSON_OBJECT('id', NEW.`id`,'sessionyear', NEW.`sessionyear`,'sccode', NEW.`sccode`,'sccategory', NEW.`sccategory`,'slot', NEW.`slot`,'exam', NEW.`exam`,'classname', NEW.`classname`,'sectionname', NEW.`sectionname`,'subcode', NEW.`subcode`,'subname', NEW.`subname`,'step', NEW.`step`,'txt', NEW.`txt`,'jobdone', NEW.`jobdone`,'jobreq', NEW.`jobreq`,'jobrate', NEW.`jobrate`,'status', NEW.`status`,'jobby', NEW.`jobby`,'jobdate', NEW.`jobdate`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER result_progress_after_delete
    AFTER DELETE ON `result_progress`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, changed_by)
        VALUES(
            'result_progress',
            'DELETE',
            OLD.`id`,
            JSON_OBJECT('id', OLD.`id`,'sessionyear', OLD.`sessionyear`,'sccode', OLD.`sccode`,'sccategory', OLD.`sccategory`,'slot', OLD.`slot`,'exam', OLD.`exam`,'classname', OLD.`classname`,'sectionname', OLD.`sectionname`,'subcode', OLD.`subcode`,'subname', OLD.`subname`,'step', OLD.`step`,'txt', OLD.`txt`,'jobdone', OLD.`jobdone`,'jobreq', OLD.`jobreq`,'jobrate', OLD.`jobrate`,'status', OLD.`status`,'jobby', OLD.`jobby`,'jobdate', OLD.`jobdate`),
            COALESCE(@current_user,'system')
        );
    END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `result_progress_step`
--

DROP TABLE IF EXISTS `result_progress_step`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `result_progress_step` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `text` varchar(50) DEFAULT NULL,
  `icon` varchar(30) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
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
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER result_progress_step_after_insert
    AFTER INSERT ON `result_progress_step`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, new_data, changed_by)
        VALUES(
            'result_progress_step',
            'INSERT',
            NEW.`id`,
            JSON_OBJECT('id', NEW.`id`,'text', NEW.`text`,'icon', NEW.`icon`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER result_progress_step_after_update
    AFTER UPDATE ON `result_progress_step`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, new_data, changed_by)
        VALUES(
            'result_progress_step',
            'UPDATE',
            NEW.`id`,
            JSON_OBJECT('id', OLD.`id`,'text', OLD.`text`,'icon', OLD.`icon`),
            JSON_OBJECT('id', NEW.`id`,'text', NEW.`text`,'icon', NEW.`icon`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER result_progress_step_after_delete
    AFTER DELETE ON `result_progress_step`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, changed_by)
        VALUES(
            'result_progress_step',
            'DELETE',
            OLD.`id`,
            JSON_OBJECT('id', OLD.`id`,'text', OLD.`text`,'icon', OLD.`icon`),
            COALESCE(@current_user,'system')
        );
    END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `salarydetails`
--

DROP TABLE IF EXISTS `salarydetails`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `salarydetails` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sessionyear` int(11) DEFAULT NULL,
  `sccode` int(11) DEFAULT NULL,
  `slots` varchar(15) DEFAULT NULL,
  `month` int(11) DEFAULT NULL,
  `year` int(11) DEFAULT NULL,
  `refnogovt` varchar(15) DEFAULT NULL,
  `refnosch` varchar(15) DEFAULT NULL,
  `refnopf` varchar(15) DEFAULT NULL,
  `refnoextra` varchar(20) DEFAULT NULL,
  `refnogovtcol1` varchar(20) DEFAULT NULL,
  `refnogovtcol2` varchar(20) DEFAULT NULL,
  `refnogovtcol3` varchar(20) DEFAULT NULL,
  `refnoschoolcol1` varchar(20) DEFAULT NULL,
  `refnoschoolcol2` varchar(20) DEFAULT NULL,
  `refnoschoolcol3` varchar(20) DEFAULT NULL,
  `tid` bigint(20) DEFAULT NULL,
  `ranks` int(11) DEFAULT NULL,
  `joindate` date DEFAULT NULL,
  `accno` varchar(15) DEFAULT NULL,
  `bankname` varchar(20) DEFAULT NULL,
  `bankbr` varchar(50) DEFAULT NULL,
  `basic` int(11) DEFAULT NULL,
  `incentive` int(11) DEFAULT NULL,
  `house` int(11) DEFAULT NULL,
  `medical` int(11) DEFAULT NULL,
  `arrear` int(11) DEFAULT NULL,
  `welfare` int(11) DEFAULT NULL,
  `retire` int(11) DEFAULT NULL,
  `govtcol1` int(11) NOT NULL DEFAULT 0,
  `govtcol2` int(11) NOT NULL DEFAULT 0,
  `govtcol3` float NOT NULL DEFAULT 0,
  `govt` int(11) DEFAULT NULL,
  `accnosch` varchar(15) DEFAULT NULL,
  `banknamesch` varchar(15) DEFAULT NULL,
  `bankbrsch` varchar(15) DEFAULT NULL,
  `salary` int(11) DEFAULT NULL,
  `mpa` int(11) DEFAULT NULL,
  `travel` int(11) DEFAULT NULL,
  `med2` int(11) DEFAULT NULL,
  `exam` int(11) DEFAULT NULL,
  `festival` int(11) DEFAULT NULL,
  `other2` int(11) DEFAULT NULL,
  `arrear2` int(11) DEFAULT NULL,
  `pf` int(11) DEFAULT NULL,
  `schoolcol1` int(11) NOT NULL DEFAULT 0,
  `schoolcol2` int(11) NOT NULL DEFAULT 0,
  `schoolcol3` float NOT NULL DEFAULT 0,
  `school` int(11) DEFAULT NULL,
  `accnopf` varchar(15) DEFAULT NULL,
  `banknamepf` varchar(15) DEFAULT NULL,
  `bankbrpf` varchar(15) DEFAULT NULL,
  `govtchq` double NOT NULL DEFAULT 0,
  `schoolchq` double NOT NULL DEFAULT 0,
  `total` int(11) DEFAULT NULL,
  `entrytime` datetime DEFAULT NULL,
  `edit_lock` int(11) NOT NULL DEFAULT 0,
  `modifieddate` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=809 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
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
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER salarydetails_after_insert
    AFTER INSERT ON `salarydetails`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, new_data, changed_by)
        VALUES(
            'salarydetails',
            'INSERT',
            NEW.`id`,
            JSON_OBJECT('id', NEW.`id`,'sessionyear', NEW.`sessionyear`,'sccode', NEW.`sccode`,'slots', NEW.`slots`,'month', NEW.`month`,'year', NEW.`year`,'refnogovt', NEW.`refnogovt`,'refnosch', NEW.`refnosch`,'refnopf', NEW.`refnopf`,'refnoextra', NEW.`refnoextra`,'refnogovtcol1', NEW.`refnogovtcol1`,'refnogovtcol2', NEW.`refnogovtcol2`,'refnogovtcol3', NEW.`refnogovtcol3`,'refnoschoolcol1', NEW.`refnoschoolcol1`,'refnoschoolcol2', NEW.`refnoschoolcol2`,'refnoschoolcol3', NEW.`refnoschoolcol3`,'tid', NEW.`tid`,'ranks', NEW.`ranks`,'joindate', NEW.`joindate`,'accno', NEW.`accno`,'bankname', NEW.`bankname`,'bankbr', NEW.`bankbr`,'basic', NEW.`basic`,'incentive', NEW.`incentive`,'house', NEW.`house`,'medical', NEW.`medical`,'arrear', NEW.`arrear`,'welfare', NEW.`welfare`,'retire', NEW.`retire`,'govtcol1', NEW.`govtcol1`,'govtcol2', NEW.`govtcol2`,'govtcol3', NEW.`govtcol3`,'govt', NEW.`govt`,'accnosch', NEW.`accnosch`,'banknamesch', NEW.`banknamesch`,'bankbrsch', NEW.`bankbrsch`,'salary', NEW.`salary`,'mpa', NEW.`mpa`,'travel', NEW.`travel`,'med2', NEW.`med2`,'exam', NEW.`exam`,'festival', NEW.`festival`,'other2', NEW.`other2`,'arrear2', NEW.`arrear2`,'pf', NEW.`pf`,'schoolcol1', NEW.`schoolcol1`,'schoolcol2', NEW.`schoolcol2`,'schoolcol3', NEW.`schoolcol3`,'school', NEW.`school`,'accnopf', NEW.`accnopf`,'banknamepf', NEW.`banknamepf`,'bankbrpf', NEW.`bankbrpf`,'govtchq', NEW.`govtchq`,'schoolchq', NEW.`schoolchq`,'total', NEW.`total`,'entrytime', NEW.`entrytime`,'edit_lock', NEW.`edit_lock`,'modifieddate', NEW.`modifieddate`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER salarydetails_after_update
    AFTER UPDATE ON `salarydetails`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, new_data, changed_by)
        VALUES(
            'salarydetails',
            'UPDATE',
            NEW.`id`,
            JSON_OBJECT('id', OLD.`id`,'sessionyear', OLD.`sessionyear`,'sccode', OLD.`sccode`,'slots', OLD.`slots`,'month', OLD.`month`,'year', OLD.`year`,'refnogovt', OLD.`refnogovt`,'refnosch', OLD.`refnosch`,'refnopf', OLD.`refnopf`,'refnoextra', OLD.`refnoextra`,'refnogovtcol1', OLD.`refnogovtcol1`,'refnogovtcol2', OLD.`refnogovtcol2`,'refnogovtcol3', OLD.`refnogovtcol3`,'refnoschoolcol1', OLD.`refnoschoolcol1`,'refnoschoolcol2', OLD.`refnoschoolcol2`,'refnoschoolcol3', OLD.`refnoschoolcol3`,'tid', OLD.`tid`,'ranks', OLD.`ranks`,'joindate', OLD.`joindate`,'accno', OLD.`accno`,'bankname', OLD.`bankname`,'bankbr', OLD.`bankbr`,'basic', OLD.`basic`,'incentive', OLD.`incentive`,'house', OLD.`house`,'medical', OLD.`medical`,'arrear', OLD.`arrear`,'welfare', OLD.`welfare`,'retire', OLD.`retire`,'govtcol1', OLD.`govtcol1`,'govtcol2', OLD.`govtcol2`,'govtcol3', OLD.`govtcol3`,'govt', OLD.`govt`,'accnosch', OLD.`accnosch`,'banknamesch', OLD.`banknamesch`,'bankbrsch', OLD.`bankbrsch`,'salary', OLD.`salary`,'mpa', OLD.`mpa`,'travel', OLD.`travel`,'med2', OLD.`med2`,'exam', OLD.`exam`,'festival', OLD.`festival`,'other2', OLD.`other2`,'arrear2', OLD.`arrear2`,'pf', OLD.`pf`,'schoolcol1', OLD.`schoolcol1`,'schoolcol2', OLD.`schoolcol2`,'schoolcol3', OLD.`schoolcol3`,'school', OLD.`school`,'accnopf', OLD.`accnopf`,'banknamepf', OLD.`banknamepf`,'bankbrpf', OLD.`bankbrpf`,'govtchq', OLD.`govtchq`,'schoolchq', OLD.`schoolchq`,'total', OLD.`total`,'entrytime', OLD.`entrytime`,'edit_lock', OLD.`edit_lock`,'modifieddate', OLD.`modifieddate`),
            JSON_OBJECT('id', NEW.`id`,'sessionyear', NEW.`sessionyear`,'sccode', NEW.`sccode`,'slots', NEW.`slots`,'month', NEW.`month`,'year', NEW.`year`,'refnogovt', NEW.`refnogovt`,'refnosch', NEW.`refnosch`,'refnopf', NEW.`refnopf`,'refnoextra', NEW.`refnoextra`,'refnogovtcol1', NEW.`refnogovtcol1`,'refnogovtcol2', NEW.`refnogovtcol2`,'refnogovtcol3', NEW.`refnogovtcol3`,'refnoschoolcol1', NEW.`refnoschoolcol1`,'refnoschoolcol2', NEW.`refnoschoolcol2`,'refnoschoolcol3', NEW.`refnoschoolcol3`,'tid', NEW.`tid`,'ranks', NEW.`ranks`,'joindate', NEW.`joindate`,'accno', NEW.`accno`,'bankname', NEW.`bankname`,'bankbr', NEW.`bankbr`,'basic', NEW.`basic`,'incentive', NEW.`incentive`,'house', NEW.`house`,'medical', NEW.`medical`,'arrear', NEW.`arrear`,'welfare', NEW.`welfare`,'retire', NEW.`retire`,'govtcol1', NEW.`govtcol1`,'govtcol2', NEW.`govtcol2`,'govtcol3', NEW.`govtcol3`,'govt', NEW.`govt`,'accnosch', NEW.`accnosch`,'banknamesch', NEW.`banknamesch`,'bankbrsch', NEW.`bankbrsch`,'salary', NEW.`salary`,'mpa', NEW.`mpa`,'travel', NEW.`travel`,'med2', NEW.`med2`,'exam', NEW.`exam`,'festival', NEW.`festival`,'other2', NEW.`other2`,'arrear2', NEW.`arrear2`,'pf', NEW.`pf`,'schoolcol1', NEW.`schoolcol1`,'schoolcol2', NEW.`schoolcol2`,'schoolcol3', NEW.`schoolcol3`,'school', NEW.`school`,'accnopf', NEW.`accnopf`,'banknamepf', NEW.`banknamepf`,'bankbrpf', NEW.`bankbrpf`,'govtchq', NEW.`govtchq`,'schoolchq', NEW.`schoolchq`,'total', NEW.`total`,'entrytime', NEW.`entrytime`,'edit_lock', NEW.`edit_lock`,'modifieddate', NEW.`modifieddate`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER salarydetails_after_delete
    AFTER DELETE ON `salarydetails`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, changed_by)
        VALUES(
            'salarydetails',
            'DELETE',
            OLD.`id`,
            JSON_OBJECT('id', OLD.`id`,'sessionyear', OLD.`sessionyear`,'sccode', OLD.`sccode`,'slots', OLD.`slots`,'month', OLD.`month`,'year', OLD.`year`,'refnogovt', OLD.`refnogovt`,'refnosch', OLD.`refnosch`,'refnopf', OLD.`refnopf`,'refnoextra', OLD.`refnoextra`,'refnogovtcol1', OLD.`refnogovtcol1`,'refnogovtcol2', OLD.`refnogovtcol2`,'refnogovtcol3', OLD.`refnogovtcol3`,'refnoschoolcol1', OLD.`refnoschoolcol1`,'refnoschoolcol2', OLD.`refnoschoolcol2`,'refnoschoolcol3', OLD.`refnoschoolcol3`,'tid', OLD.`tid`,'ranks', OLD.`ranks`,'joindate', OLD.`joindate`,'accno', OLD.`accno`,'bankname', OLD.`bankname`,'bankbr', OLD.`bankbr`,'basic', OLD.`basic`,'incentive', OLD.`incentive`,'house', OLD.`house`,'medical', OLD.`medical`,'arrear', OLD.`arrear`,'welfare', OLD.`welfare`,'retire', OLD.`retire`,'govtcol1', OLD.`govtcol1`,'govtcol2', OLD.`govtcol2`,'govtcol3', OLD.`govtcol3`,'govt', OLD.`govt`,'accnosch', OLD.`accnosch`,'banknamesch', OLD.`banknamesch`,'bankbrsch', OLD.`bankbrsch`,'salary', OLD.`salary`,'mpa', OLD.`mpa`,'travel', OLD.`travel`,'med2', OLD.`med2`,'exam', OLD.`exam`,'festival', OLD.`festival`,'other2', OLD.`other2`,'arrear2', OLD.`arrear2`,'pf', OLD.`pf`,'schoolcol1', OLD.`schoolcol1`,'schoolcol2', OLD.`schoolcol2`,'schoolcol3', OLD.`schoolcol3`,'school', OLD.`school`,'accnopf', OLD.`accnopf`,'banknamepf', OLD.`banknamepf`,'bankbrpf', OLD.`bankbrpf`,'govtchq', OLD.`govtchq`,'schoolchq', OLD.`schoolchq`,'total', OLD.`total`,'entrytime', OLD.`entrytime`,'edit_lock', OLD.`edit_lock`,'modifieddate', OLD.`modifieddate`),
            COALESCE(@current_user,'system')
        );
    END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `salaryextracolumn`
--

DROP TABLE IF EXISTS `salaryextracolumn`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `salaryextracolumn` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sccode` int(11) DEFAULT NULL,
  `sessionyear` int(11) DEFAULT NULL,
  `month` int(11) DEFAULT NULL,
  `govt1title` varchar(50) DEFAULT NULL,
  `govt1type` varchar(20) DEFAULT NULL,
  `govt1value` int(11) NOT NULL DEFAULT 0,
  `govt2title` varchar(50) DEFAULT NULL,
  `govt2type` varchar(20) DEFAULT NULL,
  `govt2value` int(11) NOT NULL DEFAULT 0,
  `govt3title` varchar(50) DEFAULT NULL,
  `govt3type` varchar(20) DEFAULT NULL,
  `govt3value` int(11) NOT NULL DEFAULT 0,
  `school1title` varchar(50) DEFAULT NULL,
  `school1type` varchar(20) DEFAULT NULL,
  `school1value` int(11) NOT NULL DEFAULT 0,
  `school2title` varchar(50) DEFAULT NULL,
  `school2type` varchar(20) DEFAULT NULL,
  `school2value` int(11) NOT NULL DEFAULT 0,
  `school3title` varchar(50) DEFAULT NULL,
  `school3type` varchar(20) DEFAULT NULL,
  `school3value` int(11) NOT NULL DEFAULT 0,
  `govt1pool` varchar(20) DEFAULT NULL,
  `govt2pool` varchar(20) DEFAULT NULL,
  `govt3pool` varchar(20) DEFAULT NULL,
  `school1pool` varchar(20) DEFAULT NULL,
  `school2pool` varchar(20) DEFAULT NULL,
  `school3pool` varchar(20) DEFAULT NULL,
  `govt1chq` int(11) NOT NULL DEFAULT 0,
  `govt2chq` int(11) NOT NULL DEFAULT 0,
  `govt3chq` int(11) NOT NULL DEFAULT 0,
  `school1chq` int(11) NOT NULL DEFAULT 0,
  `school2chq` int(11) NOT NULL DEFAULT 0,
  `school3chq` int(11) NOT NULL DEFAULT 0,
  `govt1desc` varchar(120) DEFAULT NULL,
  `govt2desc` varchar(120) DEFAULT NULL,
  `govt3desc` varchar(120) DEFAULT NULL,
  `school1desc` varchar(120) DEFAULT NULL,
  `school2desc` varchar(120) DEFAULT NULL,
  `school3desc` varchar(120) DEFAULT NULL,
  `entrytime` datetime DEFAULT NULL,
  `modifieddate` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf32 COLLATE=utf32_unicode_ci;
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
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER salaryextracolumn_after_insert
    AFTER INSERT ON `salaryextracolumn`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, new_data, changed_by)
        VALUES(
            'salaryextracolumn',
            'INSERT',
            NEW.`id`,
            JSON_OBJECT('id', NEW.`id`,'sccode', NEW.`sccode`,'sessionyear', NEW.`sessionyear`,'month', NEW.`month`,'govt1title', NEW.`govt1title`,'govt1type', NEW.`govt1type`,'govt1value', NEW.`govt1value`,'govt2title', NEW.`govt2title`,'govt2type', NEW.`govt2type`,'govt2value', NEW.`govt2value`,'govt3title', NEW.`govt3title`,'govt3type', NEW.`govt3type`,'govt3value', NEW.`govt3value`,'school1title', NEW.`school1title`,'school1type', NEW.`school1type`,'school1value', NEW.`school1value`,'school2title', NEW.`school2title`,'school2type', NEW.`school2type`,'school2value', NEW.`school2value`,'school3title', NEW.`school3title`,'school3type', NEW.`school3type`,'school3value', NEW.`school3value`,'govt1pool', NEW.`govt1pool`,'govt2pool', NEW.`govt2pool`,'govt3pool', NEW.`govt3pool`,'school1pool', NEW.`school1pool`,'school2pool', NEW.`school2pool`,'school3pool', NEW.`school3pool`,'govt1chq', NEW.`govt1chq`,'govt2chq', NEW.`govt2chq`,'govt3chq', NEW.`govt3chq`,'school1chq', NEW.`school1chq`,'school2chq', NEW.`school2chq`,'school3chq', NEW.`school3chq`,'govt1desc', NEW.`govt1desc`,'govt2desc', NEW.`govt2desc`,'govt3desc', NEW.`govt3desc`,'school1desc', NEW.`school1desc`,'school2desc', NEW.`school2desc`,'school3desc', NEW.`school3desc`,'entrytime', NEW.`entrytime`,'modifieddate', NEW.`modifieddate`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER salaryextracolumn_after_update
    AFTER UPDATE ON `salaryextracolumn`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, new_data, changed_by)
        VALUES(
            'salaryextracolumn',
            'UPDATE',
            NEW.`id`,
            JSON_OBJECT('id', OLD.`id`,'sccode', OLD.`sccode`,'sessionyear', OLD.`sessionyear`,'month', OLD.`month`,'govt1title', OLD.`govt1title`,'govt1type', OLD.`govt1type`,'govt1value', OLD.`govt1value`,'govt2title', OLD.`govt2title`,'govt2type', OLD.`govt2type`,'govt2value', OLD.`govt2value`,'govt3title', OLD.`govt3title`,'govt3type', OLD.`govt3type`,'govt3value', OLD.`govt3value`,'school1title', OLD.`school1title`,'school1type', OLD.`school1type`,'school1value', OLD.`school1value`,'school2title', OLD.`school2title`,'school2type', OLD.`school2type`,'school2value', OLD.`school2value`,'school3title', OLD.`school3title`,'school3type', OLD.`school3type`,'school3value', OLD.`school3value`,'govt1pool', OLD.`govt1pool`,'govt2pool', OLD.`govt2pool`,'govt3pool', OLD.`govt3pool`,'school1pool', OLD.`school1pool`,'school2pool', OLD.`school2pool`,'school3pool', OLD.`school3pool`,'govt1chq', OLD.`govt1chq`,'govt2chq', OLD.`govt2chq`,'govt3chq', OLD.`govt3chq`,'school1chq', OLD.`school1chq`,'school2chq', OLD.`school2chq`,'school3chq', OLD.`school3chq`,'govt1desc', OLD.`govt1desc`,'govt2desc', OLD.`govt2desc`,'govt3desc', OLD.`govt3desc`,'school1desc', OLD.`school1desc`,'school2desc', OLD.`school2desc`,'school3desc', OLD.`school3desc`,'entrytime', OLD.`entrytime`,'modifieddate', OLD.`modifieddate`),
            JSON_OBJECT('id', NEW.`id`,'sccode', NEW.`sccode`,'sessionyear', NEW.`sessionyear`,'month', NEW.`month`,'govt1title', NEW.`govt1title`,'govt1type', NEW.`govt1type`,'govt1value', NEW.`govt1value`,'govt2title', NEW.`govt2title`,'govt2type', NEW.`govt2type`,'govt2value', NEW.`govt2value`,'govt3title', NEW.`govt3title`,'govt3type', NEW.`govt3type`,'govt3value', NEW.`govt3value`,'school1title', NEW.`school1title`,'school1type', NEW.`school1type`,'school1value', NEW.`school1value`,'school2title', NEW.`school2title`,'school2type', NEW.`school2type`,'school2value', NEW.`school2value`,'school3title', NEW.`school3title`,'school3type', NEW.`school3type`,'school3value', NEW.`school3value`,'govt1pool', NEW.`govt1pool`,'govt2pool', NEW.`govt2pool`,'govt3pool', NEW.`govt3pool`,'school1pool', NEW.`school1pool`,'school2pool', NEW.`school2pool`,'school3pool', NEW.`school3pool`,'govt1chq', NEW.`govt1chq`,'govt2chq', NEW.`govt2chq`,'govt3chq', NEW.`govt3chq`,'school1chq', NEW.`school1chq`,'school2chq', NEW.`school2chq`,'school3chq', NEW.`school3chq`,'govt1desc', NEW.`govt1desc`,'govt2desc', NEW.`govt2desc`,'govt3desc', NEW.`govt3desc`,'school1desc', NEW.`school1desc`,'school2desc', NEW.`school2desc`,'school3desc', NEW.`school3desc`,'entrytime', NEW.`entrytime`,'modifieddate', NEW.`modifieddate`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER salaryextracolumn_after_delete
    AFTER DELETE ON `salaryextracolumn`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, changed_by)
        VALUES(
            'salaryextracolumn',
            'DELETE',
            OLD.`id`,
            JSON_OBJECT('id', OLD.`id`,'sccode', OLD.`sccode`,'sessionyear', OLD.`sessionyear`,'month', OLD.`month`,'govt1title', OLD.`govt1title`,'govt1type', OLD.`govt1type`,'govt1value', OLD.`govt1value`,'govt2title', OLD.`govt2title`,'govt2type', OLD.`govt2type`,'govt2value', OLD.`govt2value`,'govt3title', OLD.`govt3title`,'govt3type', OLD.`govt3type`,'govt3value', OLD.`govt3value`,'school1title', OLD.`school1title`,'school1type', OLD.`school1type`,'school1value', OLD.`school1value`,'school2title', OLD.`school2title`,'school2type', OLD.`school2type`,'school2value', OLD.`school2value`,'school3title', OLD.`school3title`,'school3type', OLD.`school3type`,'school3value', OLD.`school3value`,'govt1pool', OLD.`govt1pool`,'govt2pool', OLD.`govt2pool`,'govt3pool', OLD.`govt3pool`,'school1pool', OLD.`school1pool`,'school2pool', OLD.`school2pool`,'school3pool', OLD.`school3pool`,'govt1chq', OLD.`govt1chq`,'govt2chq', OLD.`govt2chq`,'govt3chq', OLD.`govt3chq`,'school1chq', OLD.`school1chq`,'school2chq', OLD.`school2chq`,'school3chq', OLD.`school3chq`,'govt1desc', OLD.`govt1desc`,'govt2desc', OLD.`govt2desc`,'govt3desc', OLD.`govt3desc`,'school1desc', OLD.`school1desc`,'school2desc', OLD.`school2desc`,'school3desc', OLD.`school3desc`,'entrytime', OLD.`entrytime`,'modifieddate', OLD.`modifieddate`),
            COALESCE(@current_user,'system')
        );
    END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `salarysummery`
--

DROP TABLE IF EXISTS `salarysummery`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `salarysummery` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sessionyear` int(11) DEFAULT NULL,
  `sccode` int(11) DEFAULT NULL,
  `month` int(11) DEFAULT NULL,
  `year` int(11) DEFAULT NULL,
  `refno` varchar(15) DEFAULT NULL,
  `slot` varchar(15) DEFAULT NULL,
  `category` varchar(15) DEFAULT NULL,
  `partid` int(11) DEFAULT NULL,
  `particulareng` varchar(200) DEFAULT NULL,
  `particularben` varchar(200) DEFAULT NULL,
  `salarymonth` int(11) DEFAULT NULL,
  `salaryyear` int(11) DEFAULT NULL,
  `amount` int(11) DEFAULT NULL,
  `chequeno` varchar(12) DEFAULT NULL,
  `date` date DEFAULT NULL,
  `accid` int(11) DEFAULT NULL,
  `accno` varchar(20) DEFAULT NULL,
  `issuedate` datetime DEFAULT NULL,
  `issueby` varchar(120) DEFAULT NULL,
  `status` int(11) NOT NULL DEFAULT 0,
  `modifieddate` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=201 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
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
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER salarysummery_after_insert
    AFTER INSERT ON `salarysummery`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, new_data, changed_by)
        VALUES(
            'salarysummery',
            'INSERT',
            NEW.`id`,
            JSON_OBJECT('id', NEW.`id`,'sessionyear', NEW.`sessionyear`,'sccode', NEW.`sccode`,'month', NEW.`month`,'year', NEW.`year`,'refno', NEW.`refno`,'slot', NEW.`slot`,'category', NEW.`category`,'partid', NEW.`partid`,'particulareng', NEW.`particulareng`,'particularben', NEW.`particularben`,'salarymonth', NEW.`salarymonth`,'salaryyear', NEW.`salaryyear`,'amount', NEW.`amount`,'chequeno', NEW.`chequeno`,'date', NEW.`date`,'accid', NEW.`accid`,'accno', NEW.`accno`,'issuedate', NEW.`issuedate`,'issueby', NEW.`issueby`,'status', NEW.`status`,'modifieddate', NEW.`modifieddate`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER salarysummery_after_update
    AFTER UPDATE ON `salarysummery`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, new_data, changed_by)
        VALUES(
            'salarysummery',
            'UPDATE',
            NEW.`id`,
            JSON_OBJECT('id', OLD.`id`,'sessionyear', OLD.`sessionyear`,'sccode', OLD.`sccode`,'month', OLD.`month`,'year', OLD.`year`,'refno', OLD.`refno`,'slot', OLD.`slot`,'category', OLD.`category`,'partid', OLD.`partid`,'particulareng', OLD.`particulareng`,'particularben', OLD.`particularben`,'salarymonth', OLD.`salarymonth`,'salaryyear', OLD.`salaryyear`,'amount', OLD.`amount`,'chequeno', OLD.`chequeno`,'date', OLD.`date`,'accid', OLD.`accid`,'accno', OLD.`accno`,'issuedate', OLD.`issuedate`,'issueby', OLD.`issueby`,'status', OLD.`status`,'modifieddate', OLD.`modifieddate`),
            JSON_OBJECT('id', NEW.`id`,'sessionyear', NEW.`sessionyear`,'sccode', NEW.`sccode`,'month', NEW.`month`,'year', NEW.`year`,'refno', NEW.`refno`,'slot', NEW.`slot`,'category', NEW.`category`,'partid', NEW.`partid`,'particulareng', NEW.`particulareng`,'particularben', NEW.`particularben`,'salarymonth', NEW.`salarymonth`,'salaryyear', NEW.`salaryyear`,'amount', NEW.`amount`,'chequeno', NEW.`chequeno`,'date', NEW.`date`,'accid', NEW.`accid`,'accno', NEW.`accno`,'issuedate', NEW.`issuedate`,'issueby', NEW.`issueby`,'status', NEW.`status`,'modifieddate', NEW.`modifieddate`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER salarysummery_after_delete
    AFTER DELETE ON `salarysummery`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, changed_by)
        VALUES(
            'salarysummery',
            'DELETE',
            OLD.`id`,
            JSON_OBJECT('id', OLD.`id`,'sessionyear', OLD.`sessionyear`,'sccode', OLD.`sccode`,'month', OLD.`month`,'year', OLD.`year`,'refno', OLD.`refno`,'slot', OLD.`slot`,'category', OLD.`category`,'partid', OLD.`partid`,'particulareng', OLD.`particulareng`,'particularben', OLD.`particularben`,'salarymonth', OLD.`salarymonth`,'salaryyear', OLD.`salaryyear`,'amount', OLD.`amount`,'chequeno', OLD.`chequeno`,'date', OLD.`date`,'accid', OLD.`accid`,'accno', OLD.`accno`,'issuedate', OLD.`issuedate`,'issueby', OLD.`issueby`,'status', OLD.`status`,'modifieddate', OLD.`modifieddate`),
            COALESCE(@current_user,'system')
        );
    END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `salery`
--

DROP TABLE IF EXISTS `salery`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `salery` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `idno` int(11) NOT NULL,
  `date` date NOT NULL,
  `salery` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
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
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER salery_after_insert
    AFTER INSERT ON `salery`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, new_data, changed_by)
        VALUES(
            'salery',
            'INSERT',
            NEW.`id`,
            JSON_OBJECT('id', NEW.`id`,'idno', NEW.`idno`,'date', NEW.`date`,'salery', NEW.`salery`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER salery_after_update
    AFTER UPDATE ON `salery`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, new_data, changed_by)
        VALUES(
            'salery',
            'UPDATE',
            NEW.`id`,
            JSON_OBJECT('id', OLD.`id`,'idno', OLD.`idno`,'date', OLD.`date`,'salery', OLD.`salery`),
            JSON_OBJECT('id', NEW.`id`,'idno', NEW.`idno`,'date', NEW.`date`,'salery', NEW.`salery`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER salery_after_delete
    AFTER DELETE ON `salery`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, changed_by)
        VALUES(
            'salery',
            'DELETE',
            OLD.`id`,
            JSON_OBJECT('id', OLD.`id`,'idno', OLD.`idno`,'date', OLD.`date`,'salery', OLD.`salery`),
            COALESCE(@current_user,'system')
        );
    END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `salerypay`
--

DROP TABLE IF EXISTS `salerypay`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `salerypay` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `idno` int(11) NOT NULL,
  `date` date NOT NULL,
  `amount` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
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
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER salerypay_after_insert
    AFTER INSERT ON `salerypay`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, new_data, changed_by)
        VALUES(
            'salerypay',
            'INSERT',
            NEW.`id`,
            JSON_OBJECT('id', NEW.`id`,'idno', NEW.`idno`,'date', NEW.`date`,'amount', NEW.`amount`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER salerypay_after_update
    AFTER UPDATE ON `salerypay`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, new_data, changed_by)
        VALUES(
            'salerypay',
            'UPDATE',
            NEW.`id`,
            JSON_OBJECT('id', OLD.`id`,'idno', OLD.`idno`,'date', OLD.`date`,'amount', OLD.`amount`),
            JSON_OBJECT('id', NEW.`id`,'idno', NEW.`idno`,'date', NEW.`date`,'amount', NEW.`amount`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER salerypay_after_delete
    AFTER DELETE ON `salerypay`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, changed_by)
        VALUES(
            'salerypay',
            'DELETE',
            OLD.`id`,
            JSON_OBJECT('id', OLD.`id`,'idno', OLD.`idno`,'date', OLD.`date`,'amount', OLD.`amount`),
            COALESCE(@current_user,'system')
        );
    END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

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
  `active` int(11) NOT NULL DEFAULT 1,
  `status` int(11) NOT NULL DEFAULT 1,
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
) ENGINE=InnoDB AUTO_INCREMENT=243 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
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
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER scinfo_after_insert
    AFTER INSERT ON `scinfo`
    FOR EACH ROW
    BEGIN
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER scinfo_after_update
    AFTER UPDATE ON `scinfo`
    FOR EACH ROW
    BEGIN
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER scinfo_after_delete
    AFTER DELETE ON `scinfo`
    FOR EACH ROW
    BEGIN
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
-- Table structure for table `se`
--

DROP TABLE IF EXISTS `se`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `se` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `judge` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
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
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER se_after_insert
    AFTER INSERT ON `se`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, new_data, changed_by)
        VALUES(
            'se',
            'INSERT',
            NEW.`id`,
            JSON_OBJECT('id', NEW.`id`,'judge', NEW.`judge`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER se_after_update
    AFTER UPDATE ON `se`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, new_data, changed_by)
        VALUES(
            'se',
            'UPDATE',
            NEW.`id`,
            JSON_OBJECT('id', OLD.`id`,'judge', OLD.`judge`),
            JSON_OBJECT('id', NEW.`id`,'judge', NEW.`judge`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER se_after_delete
    AFTER DELETE ON `se`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, changed_by)
        VALUES(
            'se',
            'DELETE',
            OLD.`id`,
            JSON_OBJECT('id', OLD.`id`,'judge', OLD.`judge`),
            COALESCE(@current_user,'system')
        );
    END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `services`
--

DROP TABLE IF EXISTS `services`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `services` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `servicecode` int(11) DEFAULT NULL,
  `servicename` varchar(100) DEFAULT NULL,
  `servicemodule` varchar(100) DEFAULT NULL,
  `details` varchar(255) DEFAULT NULL,
  `amount` int(11) DEFAULT NULL,
  `validityday` int(11) NOT NULL DEFAULT 0,
  `validitytime` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
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
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER services_after_insert
    AFTER INSERT ON `services`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, new_data, changed_by)
        VALUES(
            'services',
            'INSERT',
            NEW.`id`,
            JSON_OBJECT('id', NEW.`id`,'servicecode', NEW.`servicecode`,'servicename', NEW.`servicename`,'servicemodule', NEW.`servicemodule`,'details', NEW.`details`,'amount', NEW.`amount`,'validityday', NEW.`validityday`,'validitytime', NEW.`validitytime`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER services_after_update
    AFTER UPDATE ON `services`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, new_data, changed_by)
        VALUES(
            'services',
            'UPDATE',
            NEW.`id`,
            JSON_OBJECT('id', OLD.`id`,'servicecode', OLD.`servicecode`,'servicename', OLD.`servicename`,'servicemodule', OLD.`servicemodule`,'details', OLD.`details`,'amount', OLD.`amount`,'validityday', OLD.`validityday`,'validitytime', OLD.`validitytime`),
            JSON_OBJECT('id', NEW.`id`,'servicecode', NEW.`servicecode`,'servicename', NEW.`servicename`,'servicemodule', NEW.`servicemodule`,'details', NEW.`details`,'amount', NEW.`amount`,'validityday', NEW.`validityday`,'validitytime', NEW.`validitytime`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER services_after_delete
    AFTER DELETE ON `services`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, changed_by)
        VALUES(
            'services',
            'DELETE',
            OLD.`id`,
            JSON_OBJECT('id', OLD.`id`,'servicecode', OLD.`servicecode`,'servicename', OLD.`servicename`,'servicemodule', OLD.`servicemodule`,'details', OLD.`details`,'amount', OLD.`amount`,'validityday', OLD.`validityday`,'validitytime', OLD.`validitytime`),
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
) ENGINE=InnoDB AUTO_INCREMENT=58599 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
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
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER sessioninfo_after_insert
    AFTER INSERT ON `sessioninfo`
    FOR EACH ROW
    BEGIN
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER sessioninfo_after_update
    AFTER UPDATE ON `sessioninfo`
    FOR EACH ROW
    BEGIN
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER sessioninfo_after_delete
    AFTER DELETE ON `sessioninfo`
    FOR EACH ROW
    BEGIN
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
) ENGINE=InnoDB AUTO_INCREMENT=204 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
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
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER sessionyear_after_insert
    AFTER INSERT ON `sessionyear`
    FOR EACH ROW
    BEGIN
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER sessionyear_after_update
    AFTER UPDATE ON `sessionyear`
    FOR EACH ROW
    BEGIN
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER sessionyear_after_delete
    AFTER DELETE ON `sessionyear`
    FOR EACH ROW
    BEGIN
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
) ENGINE=InnoDB AUTO_INCREMENT=146 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
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
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER settings_after_insert
    AFTER INSERT ON `settings`
    FOR EACH ROW
    BEGIN
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER settings_after_update
    AFTER UPDATE ON `settings`
    FOR EACH ROW
    BEGIN
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER settings_after_delete
    AFTER DELETE ON `settings`
    FOR EACH ROW
    BEGIN
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
-- Table structure for table `sheet1`
--

DROP TABLE IF EXISTS `sheet1`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sheet1` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sccode` int(11) DEFAULT NULL,
  `stid` int(11) DEFAULT NULL,
  `stnameeng` varchar(30) DEFAULT NULL,
  `fname` varchar(30) DEFAULT NULL,
  `mname` varchar(27) DEFAULT NULL,
  `bgroup` varchar(3) DEFAULT NULL,
  `guarmobile` int(11) DEFAULT NULL,
  `photo` varchar(23) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1587 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
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
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER sheet1_after_insert
    AFTER INSERT ON `sheet1`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, new_data, changed_by)
        VALUES(
            'sheet1',
            'INSERT',
            NEW.`id`,
            JSON_OBJECT('id', NEW.`id`,'sccode', NEW.`sccode`,'stid', NEW.`stid`,'stnameeng', NEW.`stnameeng`,'fname', NEW.`fname`,'mname', NEW.`mname`,'bgroup', NEW.`bgroup`,'guarmobile', NEW.`guarmobile`,'photo', NEW.`photo`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER sheet1_after_update
    AFTER UPDATE ON `sheet1`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, new_data, changed_by)
        VALUES(
            'sheet1',
            'UPDATE',
            NEW.`id`,
            JSON_OBJECT('id', OLD.`id`,'sccode', OLD.`sccode`,'stid', OLD.`stid`,'stnameeng', OLD.`stnameeng`,'fname', OLD.`fname`,'mname', OLD.`mname`,'bgroup', OLD.`bgroup`,'guarmobile', OLD.`guarmobile`,'photo', OLD.`photo`),
            JSON_OBJECT('id', NEW.`id`,'sccode', NEW.`sccode`,'stid', NEW.`stid`,'stnameeng', NEW.`stnameeng`,'fname', NEW.`fname`,'mname', NEW.`mname`,'bgroup', NEW.`bgroup`,'guarmobile', NEW.`guarmobile`,'photo', NEW.`photo`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER sheet1_after_delete
    AFTER DELETE ON `sheet1`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, changed_by)
        VALUES(
            'sheet1',
            'DELETE',
            OLD.`id`,
            JSON_OBJECT('id', OLD.`id`,'sccode', OLD.`sccode`,'stid', OLD.`stid`,'stnameeng', OLD.`stnameeng`,'fname', OLD.`fname`,'mname', OLD.`mname`,'bgroup', OLD.`bgroup`,'guarmobile', OLD.`guarmobile`,'photo', OLD.`photo`),
            COALESCE(@current_user,'system')
        );
    END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

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
) ENGINE=InnoDB AUTO_INCREMENT=33 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
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
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER slots_after_insert
    AFTER INSERT ON `slots`
    FOR EACH ROW
    BEGIN
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER slots_after_update
    AFTER UPDATE ON `slots`
    FOR EACH ROW
    BEGIN
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER slots_after_delete
    AFTER DELETE ON `slots`
    FOR EACH ROW
    BEGIN
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
) ENGINE=InnoDB AUTO_INCREMENT=37 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
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
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER sms_after_insert
    AFTER INSERT ON `sms`
    FOR EACH ROW
    BEGIN
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER sms_after_update
    AFTER UPDATE ON `sms`
    FOR EACH ROW
    BEGIN
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER sms_after_delete
    AFTER DELETE ON `sms`
    FOR EACH ROW
    BEGIN
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
-- Table structure for table `sms_old_data`
--

DROP TABLE IF EXISTS `sms_old_data`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sms_old_data` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `rid` int(11) NOT NULL,
  `mobile` varchar(11) NOT NULL,
  `text` varchar(250) NOT NULL,
  `sccode` int(11) NOT NULL,
  `date` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `cnt` int(11) NOT NULL,
  `modifieddate` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7213 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
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
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER sms_old_data_after_insert
    AFTER INSERT ON `sms_old_data`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, new_data, changed_by)
        VALUES(
            'sms_old_data',
            'INSERT',
            NEW.`id`,
            JSON_OBJECT('id', NEW.`id`,'rid', NEW.`rid`,'mobile', NEW.`mobile`,'text', NEW.`text`,'sccode', NEW.`sccode`,'date', NEW.`date`,'cnt', NEW.`cnt`,'modifieddate', NEW.`modifieddate`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER sms_old_data_after_update
    AFTER UPDATE ON `sms_old_data`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, new_data, changed_by)
        VALUES(
            'sms_old_data',
            'UPDATE',
            NEW.`id`,
            JSON_OBJECT('id', OLD.`id`,'rid', OLD.`rid`,'mobile', OLD.`mobile`,'text', OLD.`text`,'sccode', OLD.`sccode`,'date', OLD.`date`,'cnt', OLD.`cnt`,'modifieddate', OLD.`modifieddate`),
            JSON_OBJECT('id', NEW.`id`,'rid', NEW.`rid`,'mobile', NEW.`mobile`,'text', NEW.`text`,'sccode', NEW.`sccode`,'date', NEW.`date`,'cnt', NEW.`cnt`,'modifieddate', NEW.`modifieddate`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER sms_old_data_after_delete
    AFTER DELETE ON `sms_old_data`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, changed_by)
        VALUES(
            'sms_old_data',
            'DELETE',
            OLD.`id`,
            JSON_OBJECT('id', OLD.`id`,'rid', OLD.`rid`,'mobile', OLD.`mobile`,'text', OLD.`text`,'sccode', OLD.`sccode`,'date', OLD.`date`,'cnt', OLD.`cnt`,'modifieddate', OLD.`modifieddate`),
            COALESCE(@current_user,'system')
        );
    END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `sms_templete`
--

DROP TABLE IF EXISTS `sms_templete`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sms_templete` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sccode` int(11) DEFAULT NULL,
  `smstemp` varchar(250) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
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
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER sms_templete_after_insert
    AFTER INSERT ON `sms_templete`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, new_data, changed_by)
        VALUES(
            'sms_templete',
            'INSERT',
            NEW.`id`,
            JSON_OBJECT('id', NEW.`id`,'sccode', NEW.`sccode`,'smstemp', NEW.`smstemp`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER sms_templete_after_update
    AFTER UPDATE ON `sms_templete`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, new_data, changed_by)
        VALUES(
            'sms_templete',
            'UPDATE',
            NEW.`id`,
            JSON_OBJECT('id', OLD.`id`,'sccode', OLD.`sccode`,'smstemp', OLD.`smstemp`),
            JSON_OBJECT('id', NEW.`id`,'sccode', NEW.`sccode`,'smstemp', NEW.`smstemp`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER sms_templete_after_delete
    AFTER DELETE ON `sms_templete`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, changed_by)
        VALUES(
            'sms_templete',
            'DELETE',
            OLD.`id`,
            JSON_OBJECT('id', OLD.`id`,'sccode', OLD.`sccode`,'smstemp', OLD.`smstemp`),
            COALESCE(@current_user,'system')
        );
    END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `smschairman`
--

DROP TABLE IF EXISTS `smschairman`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `smschairman` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date` date NOT NULL,
  `sendsms` int(11) NOT NULL,
  `comments` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=45 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
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
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER smschairman_after_insert
    AFTER INSERT ON `smschairman`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, new_data, changed_by)
        VALUES(
            'smschairman',
            'INSERT',
            NEW.`id`,
            JSON_OBJECT('id', NEW.`id`,'date', NEW.`date`,'sendsms', NEW.`sendsms`,'comments', NEW.`comments`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER smschairman_after_update
    AFTER UPDATE ON `smschairman`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, new_data, changed_by)
        VALUES(
            'smschairman',
            'UPDATE',
            NEW.`id`,
            JSON_OBJECT('id', OLD.`id`,'date', OLD.`date`,'sendsms', OLD.`sendsms`,'comments', OLD.`comments`),
            JSON_OBJECT('id', NEW.`id`,'date', NEW.`date`,'sendsms', NEW.`sendsms`,'comments', NEW.`comments`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER smschairman_after_delete
    AFTER DELETE ON `smschairman`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, changed_by)
        VALUES(
            'smschairman',
            'DELETE',
            OLD.`id`,
            JSON_OBJECT('id', OLD.`id`,'date', OLD.`date`,'sendsms', OLD.`sendsms`,'comments', OLD.`comments`),
            COALESCE(@current_user,'system')
        );
    END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `stattnd`
--

DROP TABLE IF EXISTS `stattnd`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stattnd` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sccode` int(11) DEFAULT NULL,
  `sessionyear` int(11) DEFAULT NULL,
  `stid` varchar(11) DEFAULT NULL,
  `adate` date DEFAULT NULL,
  `period1` int(11) NOT NULL DEFAULT 1,
  `period2` int(11) NOT NULL DEFAULT 1,
  `period3` int(11) NOT NULL DEFAULT 1,
  `period4` int(11) NOT NULL DEFAULT 1,
  `period5` int(11) NOT NULL DEFAULT 1,
  `period6` int(11) NOT NULL DEFAULT 1,
  `period7` int(11) NOT NULL DEFAULT 1,
  `period8` int(11) DEFAULT 1,
  `bunk` int(11) NOT NULL DEFAULT 0,
  `yn` int(11) DEFAULT NULL,
  `entryby` varchar(30) DEFAULT NULL,
  `entrytime` datetime NOT NULL DEFAULT current_timestamp(),
  `classname` varchar(12) DEFAULT NULL,
  `sectionname` varchar(20) DEFAULT NULL,
  `rollno` int(11) DEFAULT NULL,
  `stname` varchar(20) DEFAULT NULL,
  `intime` time DEFAULT NULL,
  `outtime` time DEFAULT NULL,
  `sendsms` int(11) NOT NULL DEFAULT 0,
  `mobileno` varchar(11) DEFAULT NULL,
  `by2` varchar(120) DEFAULT NULL,
  `by3` varchar(120) DEFAULT NULL,
  `by4` varchar(120) DEFAULT NULL,
  `by5` varchar(120) DEFAULT NULL,
  `by6` varchar(120) DEFAULT NULL,
  `by7` varchar(120) DEFAULT NULL,
  `by8` varchar(120) DEFAULT NULL,
  `time2` datetime DEFAULT NULL,
  `time3` datetime DEFAULT NULL,
  `time4` datetime DEFAULT NULL,
  `time5` datetime DEFAULT NULL,
  `time6` datetime DEFAULT NULL,
  `time7` datetime DEFAULT NULL,
  `time8` datetime DEFAULT NULL,
  `modifieddate` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=79359 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
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
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER stattnd_after_insert
    AFTER INSERT ON `stattnd`
    FOR EACH ROW
    BEGIN
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
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER stattnd_after_update
    AFTER UPDATE ON `stattnd`
    FOR EACH ROW
    BEGIN
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
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER stattnd_after_delete
    AFTER DELETE ON `stattnd`
    FOR EACH ROW
    BEGIN
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
-- Table structure for table `stattndsummery`
--

DROP TABLE IF EXISTS `stattndsummery`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stattndsummery` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sccode` int(11) DEFAULT NULL,
  `sessionyear` int(11) DEFAULT NULL,
  `date` date DEFAULT NULL,
  `classname` varchar(20) DEFAULT NULL,
  `sectionname` varchar(20) DEFAULT NULL,
  `totalstudent` int(11) DEFAULT NULL,
  `attndstudent` int(11) DEFAULT NULL,
  `attndrate` float DEFAULT NULL,
  `submitby` varchar(100) DEFAULT NULL,
  `submittime` datetime DEFAULT NULL,
  `modifieddate` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1666 DEFAULT CHARSET=utf32 COLLATE=utf32_unicode_ci;
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
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER stattndsummery_after_insert
    AFTER INSERT ON `stattndsummery`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, new_data, changed_by)
        VALUES(
            'stattndsummery',
            'INSERT',
            NEW.`id`,
            JSON_OBJECT('id', NEW.`id`,'sccode', NEW.`sccode`,'sessionyear', NEW.`sessionyear`,'date', NEW.`date`,'classname', NEW.`classname`,'sectionname', NEW.`sectionname`,'totalstudent', NEW.`totalstudent`,'attndstudent', NEW.`attndstudent`,'attndrate', NEW.`attndrate`,'submitby', NEW.`submitby`,'submittime', NEW.`submittime`,'modifieddate', NEW.`modifieddate`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER stattndsummery_after_update
    AFTER UPDATE ON `stattndsummery`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, new_data, changed_by)
        VALUES(
            'stattndsummery',
            'UPDATE',
            NEW.`id`,
            JSON_OBJECT('id', OLD.`id`,'sccode', OLD.`sccode`,'sessionyear', OLD.`sessionyear`,'date', OLD.`date`,'classname', OLD.`classname`,'sectionname', OLD.`sectionname`,'totalstudent', OLD.`totalstudent`,'attndstudent', OLD.`attndstudent`,'attndrate', OLD.`attndrate`,'submitby', OLD.`submitby`,'submittime', OLD.`submittime`,'modifieddate', OLD.`modifieddate`),
            JSON_OBJECT('id', NEW.`id`,'sccode', NEW.`sccode`,'sessionyear', NEW.`sessionyear`,'date', NEW.`date`,'classname', NEW.`classname`,'sectionname', NEW.`sectionname`,'totalstudent', NEW.`totalstudent`,'attndstudent', NEW.`attndstudent`,'attndrate', NEW.`attndrate`,'submitby', NEW.`submitby`,'submittime', NEW.`submittime`,'modifieddate', NEW.`modifieddate`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER stattndsummery_after_delete
    AFTER DELETE ON `stattndsummery`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, changed_by)
        VALUES(
            'stattndsummery',
            'DELETE',
            OLD.`id`,
            JSON_OBJECT('id', OLD.`id`,'sccode', OLD.`sccode`,'sessionyear', OLD.`sessionyear`,'date', OLD.`date`,'classname', OLD.`classname`,'sectionname', OLD.`sectionname`,'totalstudent', OLD.`totalstudent`,'attndstudent', OLD.`attndstudent`,'attndrate', OLD.`attndrate`,'submitby', OLD.`submitby`,'submittime', OLD.`submittime`,'modifieddate', OLD.`modifieddate`),
            COALESCE(@current_user,'system')
        );
    END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

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
) ENGINE=InnoDB AUTO_INCREMENT=657934 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
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
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER stfinance_after_insert
    AFTER INSERT ON `stfinance`
    FOR EACH ROW
    BEGIN
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER stfinance_after_update
    AFTER UPDATE ON `stfinance`
    FOR EACH ROW
    BEGIN
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER stfinance_after_delete
    AFTER DELETE ON `stfinance`
    FOR EACH ROW
    BEGIN
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
) ENGINE=InnoDB AUTO_INCREMENT=298499 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
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
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER stmark_after_insert
    AFTER INSERT ON `stmark`
    FOR EACH ROW
    BEGIN
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER stmark_after_update
    AFTER UPDATE ON `stmark`
    FOR EACH ROW
    BEGIN
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER stmark_after_delete
    AFTER DELETE ON `stmark`
    FOR EACH ROW
    BEGIN
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
) ENGINE=InnoDB AUTO_INCREMENT=7391 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
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
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER stpr_after_insert
    AFTER INSERT ON `stpr`
    FOR EACH ROW
    BEGIN
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER stpr_after_update
    AFTER UPDATE ON `stpr`
    FOR EACH ROW
    BEGIN
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER stpr_after_delete
    AFTER DELETE ON `stpr`
    FOR EACH ROW
    BEGIN
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
-- Table structure for table `sttracking`
--

DROP TABLE IF EXISTS `sttracking`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sttracking` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sessionyear` int(11) DEFAULT NULL,
  `sccode` int(11) DEFAULT NULL,
  `stid` bigint(20) DEFAULT NULL,
  `classname` varchar(20) DEFAULT NULL,
  `sectionname` varchar(30) DEFAULT NULL,
  `rollno` int(11) DEFAULT NULL,
  `date` date DEFAULT NULL,
  `subject` int(11) DEFAULT NULL,
  `responsetime` datetime DEFAULT NULL,
  `distance` int(11) DEFAULT NULL,
  `modifieddate` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=262 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
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
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER sttracking_after_insert
    AFTER INSERT ON `sttracking`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, new_data, changed_by)
        VALUES(
            'sttracking',
            'INSERT',
            NEW.`id`,
            JSON_OBJECT('id', NEW.`id`,'sessionyear', NEW.`sessionyear`,'sccode', NEW.`sccode`,'stid', NEW.`stid`,'classname', NEW.`classname`,'sectionname', NEW.`sectionname`,'rollno', NEW.`rollno`,'date', NEW.`date`,'subject', NEW.`subject`,'responsetime', NEW.`responsetime`,'distance', NEW.`distance`,'modifieddate', NEW.`modifieddate`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER sttracking_after_update
    AFTER UPDATE ON `sttracking`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, new_data, changed_by)
        VALUES(
            'sttracking',
            'UPDATE',
            NEW.`id`,
            JSON_OBJECT('id', OLD.`id`,'sessionyear', OLD.`sessionyear`,'sccode', OLD.`sccode`,'stid', OLD.`stid`,'classname', OLD.`classname`,'sectionname', OLD.`sectionname`,'rollno', OLD.`rollno`,'date', OLD.`date`,'subject', OLD.`subject`,'responsetime', OLD.`responsetime`,'distance', OLD.`distance`,'modifieddate', OLD.`modifieddate`),
            JSON_OBJECT('id', NEW.`id`,'sessionyear', NEW.`sessionyear`,'sccode', NEW.`sccode`,'stid', NEW.`stid`,'classname', NEW.`classname`,'sectionname', NEW.`sectionname`,'rollno', NEW.`rollno`,'date', NEW.`date`,'subject', NEW.`subject`,'responsetime', NEW.`responsetime`,'distance', NEW.`distance`,'modifieddate', NEW.`modifieddate`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER sttracking_after_delete
    AFTER DELETE ON `sttracking`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, changed_by)
        VALUES(
            'sttracking',
            'DELETE',
            OLD.`id`,
            JSON_OBJECT('id', OLD.`id`,'sessionyear', OLD.`sessionyear`,'sccode', OLD.`sccode`,'stid', OLD.`stid`,'classname', OLD.`classname`,'sectionname', OLD.`sectionname`,'rollno', OLD.`rollno`,'date', OLD.`date`,'subject', OLD.`subject`,'responsetime', OLD.`responsetime`,'distance', OLD.`distance`,'modifieddate', OLD.`modifieddate`),
            COALESCE(@current_user,'system')
        );
    END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `studentfinancesetting`
--

DROP TABLE IF EXISTS `studentfinancesetting`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `studentfinancesetting` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sessionyear` int(11) NOT NULL,
  `stid` int(11) NOT NULL,
  `admfee` int(11) NOT NULL,
  `book` int(11) NOT NULL,
  `tfee` int(11) NOT NULL,
  `cfee` int(11) NOT NULL,
  `vfee` int(11) NOT NULL,
  `efee` int(11) NOT NULL,
  `modelfee` int(11) NOT NULL,
  `pecfee` int(11) NOT NULL,
  `akefee` int(11) NOT NULL,
  `syllabus` int(11) NOT NULL,
  `pcard` int(11) NOT NULL,
  `preport` int(11) NOT NULL,
  `cap` int(11) NOT NULL,
  `tie` int(11) NOT NULL,
  `badge` int(11) NOT NULL,
  `idcard` int(11) NOT NULL,
  `sfee` int(11) NOT NULL,
  `mfee` int(11) NOT NULL,
  `sccode` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=247 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
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
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER studentfinancesetting_after_insert
    AFTER INSERT ON `studentfinancesetting`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, new_data, changed_by)
        VALUES(
            'studentfinancesetting',
            'INSERT',
            NEW.`id`,
            JSON_OBJECT('id', NEW.`id`,'sessionyear', NEW.`sessionyear`,'stid', NEW.`stid`,'admfee', NEW.`admfee`,'book', NEW.`book`,'tfee', NEW.`tfee`,'cfee', NEW.`cfee`,'vfee', NEW.`vfee`,'efee', NEW.`efee`,'modelfee', NEW.`modelfee`,'pecfee', NEW.`pecfee`,'akefee', NEW.`akefee`,'syllabus', NEW.`syllabus`,'pcard', NEW.`pcard`,'preport', NEW.`preport`,'cap', NEW.`cap`,'tie', NEW.`tie`,'badge', NEW.`badge`,'idcard', NEW.`idcard`,'sfee', NEW.`sfee`,'mfee', NEW.`mfee`,'sccode', NEW.`sccode`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER studentfinancesetting_after_update
    AFTER UPDATE ON `studentfinancesetting`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, new_data, changed_by)
        VALUES(
            'studentfinancesetting',
            'UPDATE',
            NEW.`id`,
            JSON_OBJECT('id', OLD.`id`,'sessionyear', OLD.`sessionyear`,'stid', OLD.`stid`,'admfee', OLD.`admfee`,'book', OLD.`book`,'tfee', OLD.`tfee`,'cfee', OLD.`cfee`,'vfee', OLD.`vfee`,'efee', OLD.`efee`,'modelfee', OLD.`modelfee`,'pecfee', OLD.`pecfee`,'akefee', OLD.`akefee`,'syllabus', OLD.`syllabus`,'pcard', OLD.`pcard`,'preport', OLD.`preport`,'cap', OLD.`cap`,'tie', OLD.`tie`,'badge', OLD.`badge`,'idcard', OLD.`idcard`,'sfee', OLD.`sfee`,'mfee', OLD.`mfee`,'sccode', OLD.`sccode`),
            JSON_OBJECT('id', NEW.`id`,'sessionyear', NEW.`sessionyear`,'stid', NEW.`stid`,'admfee', NEW.`admfee`,'book', NEW.`book`,'tfee', NEW.`tfee`,'cfee', NEW.`cfee`,'vfee', NEW.`vfee`,'efee', NEW.`efee`,'modelfee', NEW.`modelfee`,'pecfee', NEW.`pecfee`,'akefee', NEW.`akefee`,'syllabus', NEW.`syllabus`,'pcard', NEW.`pcard`,'preport', NEW.`preport`,'cap', NEW.`cap`,'tie', NEW.`tie`,'badge', NEW.`badge`,'idcard', NEW.`idcard`,'sfee', NEW.`sfee`,'mfee', NEW.`mfee`,'sccode', NEW.`sccode`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER studentfinancesetting_after_delete
    AFTER DELETE ON `studentfinancesetting`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, changed_by)
        VALUES(
            'studentfinancesetting',
            'DELETE',
            OLD.`id`,
            JSON_OBJECT('id', OLD.`id`,'sessionyear', OLD.`sessionyear`,'stid', OLD.`stid`,'admfee', OLD.`admfee`,'book', OLD.`book`,'tfee', OLD.`tfee`,'cfee', OLD.`cfee`,'vfee', OLD.`vfee`,'efee', OLD.`efee`,'modelfee', OLD.`modelfee`,'pecfee', OLD.`pecfee`,'akefee', OLD.`akefee`,'syllabus', OLD.`syllabus`,'pcard', OLD.`pcard`,'preport', OLD.`preport`,'cap', OLD.`cap`,'tie', OLD.`tie`,'badge', OLD.`badge`,'idcard', OLD.`idcard`,'sfee', OLD.`sfee`,'mfee', OLD.`mfee`,'sccode', OLD.`sccode`),
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
) ENGINE=MyISAM AUTO_INCREMENT=57426 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
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
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER students_after_insert
    AFTER INSERT ON `students`
    FOR EACH ROW
    BEGIN
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER students_after_update
    AFTER UPDATE ON `students`
    FOR EACH ROW
    BEGIN
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER students_after_delete
    AFTER DELETE ON `students`
    FOR EACH ROW
    BEGIN
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
-- Table structure for table `subjectlist`
--

DROP TABLE IF EXISTS `subjectlist`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `subjectlist` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `subjectname` varchar(30) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
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
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER subjectlist_after_insert
    AFTER INSERT ON `subjectlist`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, new_data, changed_by)
        VALUES(
            'subjectlist',
            'INSERT',
            NEW.`id`,
            JSON_OBJECT('id', NEW.`id`,'subjectname', NEW.`subjectname`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER subjectlist_after_update
    AFTER UPDATE ON `subjectlist`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, new_data, changed_by)
        VALUES(
            'subjectlist',
            'UPDATE',
            NEW.`id`,
            JSON_OBJECT('id', OLD.`id`,'subjectname', OLD.`subjectname`),
            JSON_OBJECT('id', NEW.`id`,'subjectname', NEW.`subjectname`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER subjectlist_after_delete
    AFTER DELETE ON `subjectlist`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, changed_by)
        VALUES(
            'subjectlist',
            'DELETE',
            OLD.`id`,
            JSON_OBJECT('id', OLD.`id`,'subjectname', OLD.`subjectname`),
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
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `subjects` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sccode` int(11) NOT NULL DEFAULT 0,
  `sccategory` varchar(15) NOT NULL DEFAULT 'School',
  `subcode` int(11) DEFAULT NULL,
  `subject` varchar(70) DEFAULT NULL,
  `subben` varchar(100) DEFAULT NULL,
  `subshname` varchar(6) DEFAULT NULL,
  `ncode` int(11) NOT NULL DEFAULT 0 COMMENT 'Noipunno Code',
  `fourth` int(11) NOT NULL DEFAULT 0,
  `sup_class` varchar(250) DEFAULT NULL,
  `modifieddate` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=258 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
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
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER subjects_after_insert
    AFTER INSERT ON `subjects`
    FOR EACH ROW
    BEGIN
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
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER subjects_after_update
    AFTER UPDATE ON `subjects`
    FOR EACH ROW
    BEGIN
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
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER subjects_after_delete
    AFTER DELETE ON `subjects`
    FOR EACH ROW
    BEGIN
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
-- Table structure for table `subjectsettinglist`
--

DROP TABLE IF EXISTS `subjectsettinglist`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `subjectsettinglist` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sccode` int(11) NOT NULL,
  `sccategory` varchar(20) NOT NULL DEFAULT 'School',
  `classname` varchar(20) NOT NULL,
  `sectionname` varchar(50) NOT NULL,
  `slno` int(11) DEFAULT 0,
  `subject` int(11) NOT NULL,
  `fullmarks` int(11) NOT NULL,
  `subj` int(11) NOT NULL,
  `obj` int(11) NOT NULL,
  `pra` int(11) NOT NULL,
  `ca` int(11) NOT NULL,
  `camanual` int(11) NOT NULL DEFAULT 0,
  `pass_algorithm` int(11) NOT NULL,
  `cnt` int(11) NOT NULL,
  `reverse` int(11) NOT NULL,
  `tid` int(11) DEFAULT NULL,
  `entrycnt` int(11) NOT NULL DEFAULT 0,
  `modifieddate` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3409 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
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
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER subjectsettinglist_after_insert
    AFTER INSERT ON `subjectsettinglist`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, new_data, changed_by)
        VALUES(
            'subjectsettinglist',
            'INSERT',
            NEW.`id`,
            JSON_OBJECT('id', NEW.`id`,'sccode', NEW.`sccode`,'sccategory', NEW.`sccategory`,'classname', NEW.`classname`,'sectionname', NEW.`sectionname`,'slno', NEW.`slno`,'subject', NEW.`subject`,'fullmarks', NEW.`fullmarks`,'subj', NEW.`subj`,'obj', NEW.`obj`,'pra', NEW.`pra`,'ca', NEW.`ca`,'camanual', NEW.`camanual`,'pass_algorithm', NEW.`pass_algorithm`,'cnt', NEW.`cnt`,'reverse', NEW.`reverse`,'tid', NEW.`tid`,'entrycnt', NEW.`entrycnt`,'modifieddate', NEW.`modifieddate`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER subjectsettinglist_after_update
    AFTER UPDATE ON `subjectsettinglist`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, new_data, changed_by)
        VALUES(
            'subjectsettinglist',
            'UPDATE',
            NEW.`id`,
            JSON_OBJECT('id', OLD.`id`,'sccode', OLD.`sccode`,'sccategory', OLD.`sccategory`,'classname', OLD.`classname`,'sectionname', OLD.`sectionname`,'slno', OLD.`slno`,'subject', OLD.`subject`,'fullmarks', OLD.`fullmarks`,'subj', OLD.`subj`,'obj', OLD.`obj`,'pra', OLD.`pra`,'ca', OLD.`ca`,'camanual', OLD.`camanual`,'pass_algorithm', OLD.`pass_algorithm`,'cnt', OLD.`cnt`,'reverse', OLD.`reverse`,'tid', OLD.`tid`,'entrycnt', OLD.`entrycnt`,'modifieddate', OLD.`modifieddate`),
            JSON_OBJECT('id', NEW.`id`,'sccode', NEW.`sccode`,'sccategory', NEW.`sccategory`,'classname', NEW.`classname`,'sectionname', NEW.`sectionname`,'slno', NEW.`slno`,'subject', NEW.`subject`,'fullmarks', NEW.`fullmarks`,'subj', NEW.`subj`,'obj', NEW.`obj`,'pra', NEW.`pra`,'ca', NEW.`ca`,'camanual', NEW.`camanual`,'pass_algorithm', NEW.`pass_algorithm`,'cnt', NEW.`cnt`,'reverse', NEW.`reverse`,'tid', NEW.`tid`,'entrycnt', NEW.`entrycnt`,'modifieddate', NEW.`modifieddate`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER subjectsettinglist_after_delete
    AFTER DELETE ON `subjectsettinglist`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, changed_by)
        VALUES(
            'subjectsettinglist',
            'DELETE',
            OLD.`id`,
            JSON_OBJECT('id', OLD.`id`,'sccode', OLD.`sccode`,'sccategory', OLD.`sccategory`,'classname', OLD.`classname`,'sectionname', OLD.`sectionname`,'slno', OLD.`slno`,'subject', OLD.`subject`,'fullmarks', OLD.`fullmarks`,'subj', OLD.`subj`,'obj', OLD.`obj`,'pra', OLD.`pra`,'ca', OLD.`ca`,'camanual', OLD.`camanual`,'pass_algorithm', OLD.`pass_algorithm`,'cnt', OLD.`cnt`,'reverse', OLD.`reverse`,'tid', OLD.`tid`,'entrycnt', OLD.`entrycnt`,'modifieddate', OLD.`modifieddate`),
            COALESCE(@current_user,'system')
        );
    END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `subscription`
--

DROP TABLE IF EXISTS `subscription`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `subscription` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sccode` int(11) DEFAULT NULL,
  `user` varchar(100) DEFAULT NULL,
  `servicecode` int(11) DEFAULT NULL,
  `amount` int(11) NOT NULL DEFAULT 0,
  `activationtime` datetime DEFAULT NULL,
  `expiretime` datetime DEFAULT NULL,
  `payable` int(11) DEFAULT NULL,
  `paid` int(11) NOT NULL DEFAULT 0,
  `status` int(11) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
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
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER subscription_after_insert
    AFTER INSERT ON `subscription`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, new_data, changed_by)
        VALUES(
            'subscription',
            'INSERT',
            NEW.`id`,
            JSON_OBJECT('id', NEW.`id`,'sccode', NEW.`sccode`,'user', NEW.`user`,'servicecode', NEW.`servicecode`,'amount', NEW.`amount`,'activationtime', NEW.`activationtime`,'expiretime', NEW.`expiretime`,'payable', NEW.`payable`,'paid', NEW.`paid`,'status', NEW.`status`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER subscription_after_update
    AFTER UPDATE ON `subscription`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, new_data, changed_by)
        VALUES(
            'subscription',
            'UPDATE',
            NEW.`id`,
            JSON_OBJECT('id', OLD.`id`,'sccode', OLD.`sccode`,'user', OLD.`user`,'servicecode', OLD.`servicecode`,'amount', OLD.`amount`,'activationtime', OLD.`activationtime`,'expiretime', OLD.`expiretime`,'payable', OLD.`payable`,'paid', OLD.`paid`,'status', OLD.`status`),
            JSON_OBJECT('id', NEW.`id`,'sccode', NEW.`sccode`,'user', NEW.`user`,'servicecode', NEW.`servicecode`,'amount', NEW.`amount`,'activationtime', NEW.`activationtime`,'expiretime', NEW.`expiretime`,'payable', NEW.`payable`,'paid', NEW.`paid`,'status', NEW.`status`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER subscription_after_delete
    AFTER DELETE ON `subscription`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, changed_by)
        VALUES(
            'subscription',
            'DELETE',
            OLD.`id`,
            JSON_OBJECT('id', OLD.`id`,'sccode', OLD.`sccode`,'user', OLD.`user`,'servicecode', OLD.`servicecode`,'amount', OLD.`amount`,'activationtime', OLD.`activationtime`,'expiretime', OLD.`expiretime`,'payable', OLD.`payable`,'paid', OLD.`paid`,'status', OLD.`status`),
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
) ENGINE=InnoDB AUTO_INCREMENT=8564 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
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
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER subsetup_after_insert
    AFTER INSERT ON `subsetup`
    FOR EACH ROW
    BEGIN
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER subsetup_after_update
    AFTER UPDATE ON `subsetup`
    FOR EACH ROW
    BEGIN
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER subsetup_after_delete
    AFTER DELETE ON `subsetup`
    FOR EACH ROW
    BEGIN
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
-- Table structure for table `subsetupctmt`
--

DROP TABLE IF EXISTS `subsetupctmt`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `subsetupctmt` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  `fourth` int(11) DEFAULT NULL,
  `examname` varchar(30) DEFAULT NULL,
  `examcode` varchar(32) DEFAULT NULL,
  `examid` int(11) NOT NULL DEFAULT 0,
  `linkedid` int(11) NOT NULL DEFAULT 0,
  `examtype` varchar(3) NOT NULL DEFAULT 'PE',
  `entrycnt` int(11) NOT NULL DEFAULT 0,
  `done1` int(11) NOT NULL DEFAULT 0,
  `doneby1` varchar(100) DEFAULT NULL,
  `donetime1` datetime DEFAULT NULL,
  `done2` int(11) DEFAULT NULL,
  `doneby2` varchar(100) DEFAULT NULL,
  `donetime2` datetime DEFAULT NULL,
  `modifieddate` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
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
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER subsetupctmt_after_insert
    AFTER INSERT ON `subsetupctmt`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, new_data, changed_by)
        VALUES(
            'subsetupctmt',
            'INSERT',
            NEW.`id`,
            JSON_OBJECT('id', NEW.`id`,'sccode', NEW.`sccode`,'sessionyear', NEW.`sessionyear`,'slot', NEW.`slot`,'classname', NEW.`classname`,'sectionname', NEW.`sectionname`,'subject', NEW.`subject`,'fullmarks', NEW.`fullmarks`,'ctest', NEW.`ctest`,'mtest', NEW.`mtest`,'subj', NEW.`subj`,'obj', NEW.`obj`,'pra', NEW.`pra`,'ca', NEW.`ca`,'camanual', NEW.`camanual`,'ctmt', NEW.`ctmt`,'pass_algorithm', NEW.`pass_algorithm`,'cnt', NEW.`cnt`,'reverse', NEW.`reverse`,'tid', NEW.`tid`,'combind_1', NEW.`combind_1`,'combind_2', NEW.`combind_2`,'combind_3', NEW.`combind_3`,'combind_4', NEW.`combind_4`,'fourth', NEW.`fourth`,'examname', NEW.`examname`,'examcode', NEW.`examcode`,'examid', NEW.`examid`,'linkedid', NEW.`linkedid`,'examtype', NEW.`examtype`,'entrycnt', NEW.`entrycnt`,'done1', NEW.`done1`,'doneby1', NEW.`doneby1`,'donetime1', NEW.`donetime1`,'done2', NEW.`done2`,'doneby2', NEW.`doneby2`,'donetime2', NEW.`donetime2`,'modifieddate', NEW.`modifieddate`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER subsetupctmt_after_update
    AFTER UPDATE ON `subsetupctmt`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, new_data, changed_by)
        VALUES(
            'subsetupctmt',
            'UPDATE',
            NEW.`id`,
            JSON_OBJECT('id', OLD.`id`,'sccode', OLD.`sccode`,'sessionyear', OLD.`sessionyear`,'slot', OLD.`slot`,'classname', OLD.`classname`,'sectionname', OLD.`sectionname`,'subject', OLD.`subject`,'fullmarks', OLD.`fullmarks`,'ctest', OLD.`ctest`,'mtest', OLD.`mtest`,'subj', OLD.`subj`,'obj', OLD.`obj`,'pra', OLD.`pra`,'ca', OLD.`ca`,'camanual', OLD.`camanual`,'ctmt', OLD.`ctmt`,'pass_algorithm', OLD.`pass_algorithm`,'cnt', OLD.`cnt`,'reverse', OLD.`reverse`,'tid', OLD.`tid`,'combind_1', OLD.`combind_1`,'combind_2', OLD.`combind_2`,'combind_3', OLD.`combind_3`,'combind_4', OLD.`combind_4`,'fourth', OLD.`fourth`,'examname', OLD.`examname`,'examcode', OLD.`examcode`,'examid', OLD.`examid`,'linkedid', OLD.`linkedid`,'examtype', OLD.`examtype`,'entrycnt', OLD.`entrycnt`,'done1', OLD.`done1`,'doneby1', OLD.`doneby1`,'donetime1', OLD.`donetime1`,'done2', OLD.`done2`,'doneby2', OLD.`doneby2`,'donetime2', OLD.`donetime2`,'modifieddate', OLD.`modifieddate`),
            JSON_OBJECT('id', NEW.`id`,'sccode', NEW.`sccode`,'sessionyear', NEW.`sessionyear`,'slot', NEW.`slot`,'classname', NEW.`classname`,'sectionname', NEW.`sectionname`,'subject', NEW.`subject`,'fullmarks', NEW.`fullmarks`,'ctest', NEW.`ctest`,'mtest', NEW.`mtest`,'subj', NEW.`subj`,'obj', NEW.`obj`,'pra', NEW.`pra`,'ca', NEW.`ca`,'camanual', NEW.`camanual`,'ctmt', NEW.`ctmt`,'pass_algorithm', NEW.`pass_algorithm`,'cnt', NEW.`cnt`,'reverse', NEW.`reverse`,'tid', NEW.`tid`,'combind_1', NEW.`combind_1`,'combind_2', NEW.`combind_2`,'combind_3', NEW.`combind_3`,'combind_4', NEW.`combind_4`,'fourth', NEW.`fourth`,'examname', NEW.`examname`,'examcode', NEW.`examcode`,'examid', NEW.`examid`,'linkedid', NEW.`linkedid`,'examtype', NEW.`examtype`,'entrycnt', NEW.`entrycnt`,'done1', NEW.`done1`,'doneby1', NEW.`doneby1`,'donetime1', NEW.`donetime1`,'done2', NEW.`done2`,'doneby2', NEW.`doneby2`,'donetime2', NEW.`donetime2`,'modifieddate', NEW.`modifieddate`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER subsetupctmt_after_delete
    AFTER DELETE ON `subsetupctmt`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, changed_by)
        VALUES(
            'subsetupctmt',
            'DELETE',
            OLD.`id`,
            JSON_OBJECT('id', OLD.`id`,'sccode', OLD.`sccode`,'sessionyear', OLD.`sessionyear`,'slot', OLD.`slot`,'classname', OLD.`classname`,'sectionname', OLD.`sectionname`,'subject', OLD.`subject`,'fullmarks', OLD.`fullmarks`,'ctest', OLD.`ctest`,'mtest', OLD.`mtest`,'subj', OLD.`subj`,'obj', OLD.`obj`,'pra', OLD.`pra`,'ca', OLD.`ca`,'camanual', OLD.`camanual`,'ctmt', OLD.`ctmt`,'pass_algorithm', OLD.`pass_algorithm`,'cnt', OLD.`cnt`,'reverse', OLD.`reverse`,'tid', OLD.`tid`,'combind_1', OLD.`combind_1`,'combind_2', OLD.`combind_2`,'combind_3', OLD.`combind_3`,'combind_4', OLD.`combind_4`,'fourth', OLD.`fourth`,'examname', OLD.`examname`,'examcode', OLD.`examcode`,'examid', OLD.`examid`,'linkedid', OLD.`linkedid`,'examtype', OLD.`examtype`,'entrycnt', OLD.`entrycnt`,'done1', OLD.`done1`,'doneby1', OLD.`doneby1`,'donetime1', OLD.`donetime1`,'done2', OLD.`done2`,'doneby2', OLD.`doneby2`,'donetime2', OLD.`donetime2`,'modifieddate', OLD.`modifieddate`),
            COALESCE(@current_user,'system')
        );
    END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `syllabus_progress`
--

DROP TABLE IF EXISTS `syllabus_progress`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `syllabus_progress` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sccode` int(11) NOT NULL,
  `stid` varchar(20) NOT NULL,
  `class` varchar(10) NOT NULL,
  `topic_code` varchar(50) NOT NULL,
  `is_completed` tinyint(1) NOT NULL DEFAULT 0,
  `completed_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `sccode` (`sccode`,`stid`),
  KEY `class` (`class`),
  KEY `topic_code` (`topic_code`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
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
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER syllabus_progress_after_insert
    AFTER INSERT ON `syllabus_progress`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, new_data, changed_by)
        VALUES(
            'syllabus_progress',
            'INSERT',
            NEW.`id`,
            JSON_OBJECT('id', NEW.`id`,'sccode', NEW.`sccode`,'stid', NEW.`stid`,'class', NEW.`class`,'topic_code', NEW.`topic_code`,'is_completed', NEW.`is_completed`,'completed_at', NEW.`completed_at`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER syllabus_progress_after_update
    AFTER UPDATE ON `syllabus_progress`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, new_data, changed_by)
        VALUES(
            'syllabus_progress',
            'UPDATE',
            NEW.`id`,
            JSON_OBJECT('id', OLD.`id`,'sccode', OLD.`sccode`,'stid', OLD.`stid`,'class', OLD.`class`,'topic_code', OLD.`topic_code`,'is_completed', OLD.`is_completed`,'completed_at', OLD.`completed_at`),
            JSON_OBJECT('id', NEW.`id`,'sccode', NEW.`sccode`,'stid', NEW.`stid`,'class', NEW.`class`,'topic_code', NEW.`topic_code`,'is_completed', NEW.`is_completed`,'completed_at', NEW.`completed_at`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER syllabus_progress_after_delete
    AFTER DELETE ON `syllabus_progress`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, changed_by)
        VALUES(
            'syllabus_progress',
            'DELETE',
            OLD.`id`,
            JSON_OBJECT('id', OLD.`id`,'sccode', OLD.`sccode`,'stid', OLD.`stid`,'class', OLD.`class`,'topic_code', OLD.`topic_code`,'is_completed', OLD.`is_completed`,'completed_at', OLD.`completed_at`),
            COALESCE(@current_user,'system')
        );
    END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `syllabus_topics`
--

DROP TABLE IF EXISTS `syllabus_topics`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `syllabus_topics` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `class` varchar(10) NOT NULL,
  `topic_code` varchar(50) NOT NULL,
  `topic_name` varchar(150) NOT NULL,
  `parent_topic` varchar(50) DEFAULT NULL,
  `sequence_order` int(11) DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `class` (`class`,`topic_code`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
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
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER syllabus_topics_after_insert
    AFTER INSERT ON `syllabus_topics`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, new_data, changed_by)
        VALUES(
            'syllabus_topics',
            'INSERT',
            NEW.`id`,
            JSON_OBJECT('id', NEW.`id`,'class', NEW.`class`,'topic_code', NEW.`topic_code`,'topic_name', NEW.`topic_name`,'parent_topic', NEW.`parent_topic`,'sequence_order', NEW.`sequence_order`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER syllabus_topics_after_update
    AFTER UPDATE ON `syllabus_topics`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, new_data, changed_by)
        VALUES(
            'syllabus_topics',
            'UPDATE',
            NEW.`id`,
            JSON_OBJECT('id', OLD.`id`,'class', OLD.`class`,'topic_code', OLD.`topic_code`,'topic_name', OLD.`topic_name`,'parent_topic', OLD.`parent_topic`,'sequence_order', OLD.`sequence_order`),
            JSON_OBJECT('id', NEW.`id`,'class', NEW.`class`,'topic_code', NEW.`topic_code`,'topic_name', NEW.`topic_name`,'parent_topic', NEW.`parent_topic`,'sequence_order', NEW.`sequence_order`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER syllabus_topics_after_delete
    AFTER DELETE ON `syllabus_topics`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, changed_by)
        VALUES(
            'syllabus_topics',
            'DELETE',
            OLD.`id`,
            JSON_OBJECT('id', OLD.`id`,'class', OLD.`class`,'topic_code', OLD.`topic_code`,'topic_name', OLD.`topic_name`,'parent_topic', OLD.`parent_topic`,'sequence_order', OLD.`sequence_order`),
            COALESCE(@current_user,'system')
        );
    END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `tabulatingsheet`
--

DROP TABLE IF EXISTS `tabulatingsheet`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tabulatingsheet` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sessionyear` varchar(10) DEFAULT NULL,
  `sccode` int(11) NOT NULL,
  `slot` varchar(20) NOT NULL DEFAULT 'School',
  `exam` varchar(50) NOT NULL,
  `classname` varchar(20) NOT NULL,
  `sectionname` varchar(20) NOT NULL,
  `stid` bigint(20) DEFAULT NULL,
  `rollno` int(11) NOT NULL,
  `sub_1` int(11) DEFAULT NULL,
  `sub_2` int(11) DEFAULT NULL,
  `sub_3` int(11) DEFAULT NULL,
  `sub_4` int(11) DEFAULT NULL,
  `sub_5` int(11) DEFAULT NULL,
  `sub_6` int(11) DEFAULT NULL,
  `sub_7` int(11) DEFAULT NULL,
  `sub_8` int(11) DEFAULT NULL,
  `sub_9` int(11) DEFAULT NULL,
  `sub_10` int(11) DEFAULT NULL,
  `sub_11` int(11) DEFAULT NULL,
  `sub_12` int(11) DEFAULT NULL,
  `sub_13` int(11) DEFAULT NULL,
  `sub_14` int(11) DEFAULT NULL,
  `sub_15` int(11) DEFAULT NULL,
  `sub_1_sub` int(11) DEFAULT NULL,
  `sub_1_obj` int(11) DEFAULT NULL,
  `sub_1_pra` int(11) DEFAULT NULL,
  `sub_1_ca` decimal(5,2) DEFAULT NULL,
  `sub_1_total` decimal(5,2) DEFAULT NULL,
  `sub_1_ct` int(11) NOT NULL DEFAULT 0,
  `sub_1_mt` int(11) NOT NULL DEFAULT 0,
  `sub_1_100` float NOT NULL DEFAULT 0,
  `sub_1_gp` decimal(3,2) DEFAULT NULL,
  `sub_1_gl` varchar(3) DEFAULT NULL,
  `sub_2_sub` int(11) DEFAULT NULL,
  `sub_2_obj` int(11) DEFAULT NULL,
  `sub_2_pra` int(11) DEFAULT NULL,
  `sub_2_ca` decimal(5,2) DEFAULT NULL,
  `sub_2_total` decimal(5,2) DEFAULT NULL,
  `sub_2_ct` int(11) NOT NULL DEFAULT 0,
  `sub_2_mt` int(11) NOT NULL DEFAULT 0,
  `sub_2_100` float NOT NULL DEFAULT 0,
  `sub_2_gp` decimal(3,2) DEFAULT NULL,
  `sub_2_gl` varchar(2) DEFAULT NULL,
  `sub_3_sub` int(11) DEFAULT NULL,
  `sub_3_obj` int(11) DEFAULT NULL,
  `sub_3_pra` int(11) DEFAULT NULL,
  `sub_3_ca` decimal(5,2) DEFAULT NULL,
  `sub_3_total` decimal(5,2) DEFAULT NULL,
  `sub_3_ct` int(11) NOT NULL DEFAULT 0,
  `sub_3_mt` int(11) NOT NULL DEFAULT 0,
  `sub_3_100` float NOT NULL DEFAULT 0,
  `sub_3_gp` decimal(3,2) DEFAULT NULL,
  `sub_3_gl` varchar(3) DEFAULT NULL,
  `sub_4_sub` int(11) DEFAULT NULL,
  `sub_4_obj` int(11) DEFAULT NULL,
  `sub_4_pra` int(11) DEFAULT NULL,
  `sub_4_ca` decimal(5,2) DEFAULT NULL,
  `sub_4_total` decimal(5,2) DEFAULT NULL,
  `sub_4_ct` int(11) NOT NULL DEFAULT 0,
  `sub_4_mt` int(11) NOT NULL DEFAULT 0,
  `sub_4_100` float NOT NULL DEFAULT 0,
  `sub_4_gp` decimal(3,2) DEFAULT NULL,
  `sub_4_gl` varchar(3) DEFAULT NULL,
  `sub_5_sub` int(11) DEFAULT NULL,
  `sub_5_obj` int(11) DEFAULT NULL,
  `sub_5_pra` int(11) DEFAULT NULL,
  `sub_5_ca` decimal(5,2) DEFAULT NULL,
  `sub_5_total` decimal(5,2) DEFAULT NULL,
  `sub_5_ct` int(11) NOT NULL DEFAULT 0,
  `sub_5_mt` int(11) NOT NULL DEFAULT 0,
  `sub_5_100` float NOT NULL DEFAULT 0,
  `sub_5_gp` decimal(3,2) DEFAULT NULL,
  `sub_5_gl` varchar(3) DEFAULT NULL,
  `sub_6_sub` int(11) DEFAULT NULL,
  `sub_6_obj` int(11) DEFAULT NULL,
  `sub_6_pra` int(11) DEFAULT NULL,
  `sub_6_ca` decimal(5,2) DEFAULT NULL,
  `sub_6_total` decimal(5,2) DEFAULT NULL,
  `sub_6_ct` int(11) NOT NULL DEFAULT 0,
  `sub_6_mt` int(11) NOT NULL DEFAULT 0,
  `sub_6_100` float NOT NULL DEFAULT 0,
  `sub_6_gp` decimal(3,2) DEFAULT NULL,
  `sub_6_gl` varchar(3) DEFAULT NULL,
  `sub_7_sub` int(11) DEFAULT NULL,
  `sub_7_obj` int(11) DEFAULT NULL,
  `sub_7_pra` int(11) DEFAULT NULL,
  `sub_7_ca` decimal(5,2) DEFAULT NULL,
  `sub_7_total` decimal(5,2) DEFAULT NULL,
  `sub_7_ct` int(11) NOT NULL DEFAULT 0,
  `sub_7_mt` int(11) NOT NULL DEFAULT 0,
  `sub_7_100` float NOT NULL DEFAULT 0,
  `sub_7_gp` decimal(3,2) DEFAULT NULL,
  `sub_7_gl` varchar(3) DEFAULT NULL,
  `sub_8_sub` int(11) DEFAULT NULL,
  `sub_8_obj` int(11) DEFAULT NULL,
  `sub_8_pra` int(11) DEFAULT NULL,
  `sub_8_ca` decimal(5,2) DEFAULT NULL,
  `sub_8_total` decimal(5,2) DEFAULT NULL,
  `sub_8_ct` int(11) NOT NULL DEFAULT 0,
  `sub_8_mt` int(11) NOT NULL DEFAULT 0,
  `sub_8_100` float NOT NULL DEFAULT 0,
  `sub_8_gp` decimal(3,2) DEFAULT NULL,
  `sub_8_gl` varchar(3) DEFAULT NULL,
  `sub_9_sub` int(11) DEFAULT NULL,
  `sub_9_obj` int(11) DEFAULT NULL,
  `sub_9_pra` int(11) DEFAULT NULL,
  `sub_9_ca` decimal(5,2) DEFAULT NULL,
  `sub_9_total` decimal(5,2) DEFAULT NULL,
  `sub_9_ct` int(11) NOT NULL DEFAULT 0,
  `sub_9_mt` int(11) NOT NULL DEFAULT 0,
  `sub_9_100` float NOT NULL DEFAULT 0,
  `sub_9_gp` decimal(3,2) DEFAULT NULL,
  `sub_9_gl` varchar(3) DEFAULT NULL,
  `sub_10_sub` int(11) DEFAULT NULL,
  `sub_10_obj` int(11) DEFAULT NULL,
  `sub_10_pra` int(11) DEFAULT NULL,
  `sub_10_ca` decimal(5,2) DEFAULT NULL,
  `sub_10_total` decimal(5,2) DEFAULT NULL,
  `sub_10_ct` int(11) NOT NULL DEFAULT 0,
  `sub_10_mt` int(11) NOT NULL DEFAULT 0,
  `sub_10_100` float NOT NULL DEFAULT 0,
  `sub_10_gp` decimal(3,2) DEFAULT NULL,
  `sub_10_gl` varchar(3) DEFAULT NULL,
  `sub_11_sub` int(11) DEFAULT NULL,
  `sub_11_obj` int(11) DEFAULT NULL,
  `sub_11_pra` int(11) DEFAULT NULL,
  `sub_11_ca` decimal(5,2) DEFAULT NULL,
  `sub_11_total` decimal(5,2) DEFAULT NULL,
  `sub_11_ct` int(11) NOT NULL DEFAULT 0,
  `sub_11_mt` int(11) NOT NULL DEFAULT 0,
  `sub_11_100` float NOT NULL DEFAULT 0,
  `sub_11_gp` decimal(3,2) DEFAULT NULL,
  `sub_11_gl` varchar(3) DEFAULT NULL,
  `sub_12_sub` int(11) DEFAULT NULL,
  `sub_12_obj` int(11) DEFAULT NULL,
  `sub_12_pra` int(11) DEFAULT NULL,
  `sub_12_ca` decimal(5,2) DEFAULT NULL,
  `sub_12_total` decimal(5,2) DEFAULT NULL,
  `sub_12_ct` int(11) NOT NULL DEFAULT 0,
  `sub_12_mt` int(11) NOT NULL DEFAULT 0,
  `sub_12_100` float NOT NULL DEFAULT 0,
  `sub_12_gp` decimal(3,2) DEFAULT NULL,
  `sub_12_gl` varchar(3) DEFAULT NULL,
  `sub_13_sub` int(11) DEFAULT NULL,
  `sub_13_obj` int(11) DEFAULT NULL,
  `sub_13_pra` int(11) DEFAULT NULL,
  `sub_13_ca` decimal(5,2) DEFAULT NULL,
  `sub_13_total` decimal(5,2) DEFAULT NULL,
  `sub_13_ct` int(11) NOT NULL DEFAULT 0,
  `sub_13_mt` int(11) NOT NULL DEFAULT 0,
  `sub_13_100` float NOT NULL DEFAULT 0,
  `sub_13_gp` decimal(3,2) DEFAULT NULL,
  `sub_13_gl` varchar(3) DEFAULT NULL,
  `sub_14_sub` int(11) DEFAULT NULL,
  `sub_14_obj` int(11) DEFAULT NULL,
  `sub_14_pra` int(11) DEFAULT NULL,
  `sub_14_ca` decimal(5,2) DEFAULT NULL,
  `sub_14_total` decimal(5,2) DEFAULT NULL,
  `sub_14_ct` int(11) NOT NULL DEFAULT 0,
  `sub_14_mt` int(11) NOT NULL DEFAULT 0,
  `sub_14_100` float NOT NULL DEFAULT 0,
  `sub_14_gp` decimal(3,2) DEFAULT NULL,
  `sub_14_gl` varchar(3) DEFAULT NULL,
  `sub_15_sub` decimal(5,2) DEFAULT NULL,
  `sub_15_obj` int(11) DEFAULT NULL,
  `sub_15_pra` int(11) DEFAULT NULL,
  `sub_15_ca` decimal(5,2) DEFAULT NULL,
  `sub_15_total` int(11) DEFAULT NULL,
  `sub_15_ct` int(11) NOT NULL DEFAULT 0,
  `sub_15_mt` int(11) NOT NULL DEFAULT 0,
  `sub_15_100` float NOT NULL DEFAULT 0,
  `sub_15_gp` decimal(3,2) DEFAULT NULL,
  `sub_15_gl` varchar(3) DEFAULT NULL,
  `all_subs_entry` text DEFAULT NULL,
  `ben_sub` int(11) DEFAULT NULL,
  `ben_obj` int(11) DEFAULT NULL,
  `ben_pra` int(11) DEFAULT NULL,
  `ben_ca` int(11) DEFAULT NULL,
  `ben_total` decimal(5,2) DEFAULT NULL,
  `ben_gp` decimal(3,2) DEFAULT NULL,
  `ben_gl` varchar(3) DEFAULT NULL,
  `eng_sub` int(11) DEFAULT NULL,
  `eng_obj` int(11) DEFAULT NULL,
  `eng_pra` int(11) DEFAULT NULL,
  `eng_ca` int(11) DEFAULT NULL,
  `eng_total` decimal(5,2) DEFAULT NULL,
  `eng_gp` decimal(3,2) DEFAULT NULL,
  `eng_gl` varchar(3) DEFAULT NULL,
  `totalmarks` decimal(6,2) DEFAULT NULL,
  `full_marks` int(11) DEFAULT NULL,
  `avgrate` decimal(5,2) DEFAULT NULL,
  `gpa` decimal(3,2) DEFAULT NULL,
  `gpaadd` decimal(5,2) NOT NULL DEFAULT 0.00,
  `gla` varchar(3) DEFAULT NULL,
  `attnd` int(11) DEFAULT NULL,
  `twday` int(11) DEFAULT NULL,
  `totalfail` int(11) DEFAULT 0,
  `meritplace` varchar(10) DEFAULT NULL,
  `meritplacecomb` varchar(10) DEFAULT NULL,
  `meritplacegender` varchar(10) DEFAULT NULL,
  `meritnum` int(11) NOT NULL DEFAULT 0,
  `meritnumcomb` int(11) NOT NULL DEFAULT 0,
  `meritnumgender` int(11) NOT NULL DEFAULT 0,
  `totalgp` decimal(5,2) DEFAULT NULL,
  `totalsubject` int(11) DEFAULT NULL,
  `sublist` varchar(80) DEFAULT NULL,
  `allsubject` varchar(300) DEFAULT NULL,
  `gender` varchar(6) DEFAULT NULL,
  `prevexam` int(11) NOT NULL DEFAULT 0,
  `thisexam` int(11) NOT NULL DEFAULT 0,
  `allfourth` varchar(12) DEFAULT '000',
  `failsub` varchar(300) DEFAULT NULL,
  `ai_comm` varchar(500) DEFAULT NULL,
  `modifieddate` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=207957 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
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
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER tabulatingsheet_after_insert
    AFTER INSERT ON `tabulatingsheet`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, new_data, changed_by)
        VALUES(
            'tabulatingsheet',
            'INSERT',
            NEW.`id`,
            JSON_OBJECT('id', NEW.`id`,'sessionyear', NEW.`sessionyear`,'sccode', NEW.`sccode`,'slot', NEW.`slot`,'exam', NEW.`exam`,'classname', NEW.`classname`,'sectionname', NEW.`sectionname`,'stid', NEW.`stid`,'rollno', NEW.`rollno`,'sub_1', NEW.`sub_1`,'sub_2', NEW.`sub_2`,'sub_3', NEW.`sub_3`,'sub_4', NEW.`sub_4`,'sub_5', NEW.`sub_5`,'sub_6', NEW.`sub_6`,'sub_7', NEW.`sub_7`,'sub_8', NEW.`sub_8`,'sub_9', NEW.`sub_9`,'sub_10', NEW.`sub_10`,'sub_11', NEW.`sub_11`,'sub_12', NEW.`sub_12`,'sub_13', NEW.`sub_13`,'sub_14', NEW.`sub_14`,'sub_15', NEW.`sub_15`,'sub_1_sub', NEW.`sub_1_sub`,'sub_1_obj', NEW.`sub_1_obj`,'sub_1_pra', NEW.`sub_1_pra`,'sub_1_ca', NEW.`sub_1_ca`,'sub_1_total', NEW.`sub_1_total`,'sub_1_ct', NEW.`sub_1_ct`,'sub_1_mt', NEW.`sub_1_mt`,'sub_1_100', NEW.`sub_1_100`,'sub_1_gp', NEW.`sub_1_gp`,'sub_1_gl', NEW.`sub_1_gl`,'sub_2_sub', NEW.`sub_2_sub`,'sub_2_obj', NEW.`sub_2_obj`,'sub_2_pra', NEW.`sub_2_pra`,'sub_2_ca', NEW.`sub_2_ca`,'sub_2_total', NEW.`sub_2_total`,'sub_2_ct', NEW.`sub_2_ct`,'sub_2_mt', NEW.`sub_2_mt`,'sub_2_100', NEW.`sub_2_100`,'sub_2_gp', NEW.`sub_2_gp`,'sub_2_gl', NEW.`sub_2_gl`,'sub_3_sub', NEW.`sub_3_sub`,'sub_3_obj', NEW.`sub_3_obj`,'sub_3_pra', NEW.`sub_3_pra`,'sub_3_ca', NEW.`sub_3_ca`,'sub_3_total', NEW.`sub_3_total`,'sub_3_ct', NEW.`sub_3_ct`,'sub_3_mt', NEW.`sub_3_mt`,'sub_3_100', NEW.`sub_3_100`,'sub_3_gp', NEW.`sub_3_gp`,'sub_3_gl', NEW.`sub_3_gl`,'sub_4_sub', NEW.`sub_4_sub`,'sub_4_obj', NEW.`sub_4_obj`,'sub_4_pra', NEW.`sub_4_pra`,'sub_4_ca', NEW.`sub_4_ca`,'sub_4_total', NEW.`sub_4_total`,'sub_4_ct', NEW.`sub_4_ct`,'sub_4_mt', NEW.`sub_4_mt`,'sub_4_100', NEW.`sub_4_100`,'sub_4_gp', NEW.`sub_4_gp`,'sub_4_gl', NEW.`sub_4_gl`,'sub_5_sub', NEW.`sub_5_sub`,'sub_5_obj', NEW.`sub_5_obj`,'sub_5_pra', NEW.`sub_5_pra`,'sub_5_ca', NEW.`sub_5_ca`,'sub_5_total', NEW.`sub_5_total`,'sub_5_ct', NEW.`sub_5_ct`,'sub_5_mt', NEW.`sub_5_mt`,'sub_5_100', NEW.`sub_5_100`,'sub_5_gp', NEW.`sub_5_gp`,'sub_5_gl', NEW.`sub_5_gl`,'sub_6_sub', NEW.`sub_6_sub`,'sub_6_obj', NEW.`sub_6_obj`,'sub_6_pra', NEW.`sub_6_pra`,'sub_6_ca', NEW.`sub_6_ca`,'sub_6_total', NEW.`sub_6_total`,'sub_6_ct', NEW.`sub_6_ct`,'sub_6_mt', NEW.`sub_6_mt`,'sub_6_100', NEW.`sub_6_100`,'sub_6_gp', NEW.`sub_6_gp`,'sub_6_gl', NEW.`sub_6_gl`,'sub_7_sub', NEW.`sub_7_sub`,'sub_7_obj', NEW.`sub_7_obj`,'sub_7_pra', NEW.`sub_7_pra`,'sub_7_ca', NEW.`sub_7_ca`,'sub_7_total', NEW.`sub_7_total`,'sub_7_ct', NEW.`sub_7_ct`,'sub_7_mt', NEW.`sub_7_mt`,'sub_7_100', NEW.`sub_7_100`,'sub_7_gp', NEW.`sub_7_gp`,'sub_7_gl', NEW.`sub_7_gl`,'sub_8_sub', NEW.`sub_8_sub`,'sub_8_obj', NEW.`sub_8_obj`,'sub_8_pra', NEW.`sub_8_pra`,'sub_8_ca', NEW.`sub_8_ca`,'sub_8_total', NEW.`sub_8_total`,'sub_8_ct', NEW.`sub_8_ct`,'sub_8_mt', NEW.`sub_8_mt`,'sub_8_100', NEW.`sub_8_100`,'sub_8_gp', NEW.`sub_8_gp`,'sub_8_gl', NEW.`sub_8_gl`,'sub_9_sub', NEW.`sub_9_sub`,'sub_9_obj', NEW.`sub_9_obj`,'sub_9_pra', NEW.`sub_9_pra`,'sub_9_ca', NEW.`sub_9_ca`,'sub_9_total', NEW.`sub_9_total`,'sub_9_ct', NEW.`sub_9_ct`,'sub_9_mt', NEW.`sub_9_mt`,'sub_9_100', NEW.`sub_9_100`,'sub_9_gp', NEW.`sub_9_gp`,'sub_9_gl', NEW.`sub_9_gl`,'sub_10_sub', NEW.`sub_10_sub`,'sub_10_obj', NEW.`sub_10_obj`,'sub_10_pra', NEW.`sub_10_pra`,'sub_10_ca', NEW.`sub_10_ca`,'sub_10_total', NEW.`sub_10_total`,'sub_10_ct', NEW.`sub_10_ct`,'sub_10_mt', NEW.`sub_10_mt`,'sub_10_100', NEW.`sub_10_100`,'sub_10_gp', NEW.`sub_10_gp`,'sub_10_gl', NEW.`sub_10_gl`,'sub_11_sub', NEW.`sub_11_sub`,'sub_11_obj', NEW.`sub_11_obj`,'sub_11_pra', NEW.`sub_11_pra`,'sub_11_ca', NEW.`sub_11_ca`,'sub_11_total', NEW.`sub_11_total`,'sub_11_ct', NEW.`sub_11_ct`,'sub_11_mt', NEW.`sub_11_mt`,'sub_11_100', NEW.`sub_11_100`,'sub_11_gp', NEW.`sub_11_gp`,'sub_11_gl', NEW.`sub_11_gl`,'sub_12_sub', NEW.`sub_12_sub`,'sub_12_obj', NEW.`sub_12_obj`,'sub_12_pra', NEW.`sub_12_pra`,'sub_12_ca', NEW.`sub_12_ca`,'sub_12_total', NEW.`sub_12_total`,'sub_12_ct', NEW.`sub_12_ct`,'sub_12_mt', NEW.`sub_12_mt`,'sub_12_100', NEW.`sub_12_100`,'sub_12_gp', NEW.`sub_12_gp`,'sub_12_gl', NEW.`sub_12_gl`,'sub_13_sub', NEW.`sub_13_sub`,'sub_13_obj', NEW.`sub_13_obj`,'sub_13_pra', NEW.`sub_13_pra`,'sub_13_ca', NEW.`sub_13_ca`,'sub_13_total', NEW.`sub_13_total`,'sub_13_ct', NEW.`sub_13_ct`,'sub_13_mt', NEW.`sub_13_mt`,'sub_13_100', NEW.`sub_13_100`,'sub_13_gp', NEW.`sub_13_gp`,'sub_13_gl', NEW.`sub_13_gl`,'sub_14_sub', NEW.`sub_14_sub`,'sub_14_obj', NEW.`sub_14_obj`,'sub_14_pra', NEW.`sub_14_pra`,'sub_14_ca', NEW.`sub_14_ca`,'sub_14_total', NEW.`sub_14_total`,'sub_14_ct', NEW.`sub_14_ct`,'sub_14_mt', NEW.`sub_14_mt`,'sub_14_100', NEW.`sub_14_100`,'sub_14_gp', NEW.`sub_14_gp`,'sub_14_gl', NEW.`sub_14_gl`,'sub_15_sub', NEW.`sub_15_sub`,'sub_15_obj', NEW.`sub_15_obj`,'sub_15_pra', NEW.`sub_15_pra`,'sub_15_ca', NEW.`sub_15_ca`,'sub_15_total', NEW.`sub_15_total`,'sub_15_ct', NEW.`sub_15_ct`,'sub_15_mt', NEW.`sub_15_mt`,'sub_15_100', NEW.`sub_15_100`,'sub_15_gp', NEW.`sub_15_gp`,'sub_15_gl', NEW.`sub_15_gl`,'all_subs_entry', NEW.`all_subs_entry`,'ben_sub', NEW.`ben_sub`,'ben_obj', NEW.`ben_obj`,'ben_pra', NEW.`ben_pra`,'ben_ca', NEW.`ben_ca`,'ben_total', NEW.`ben_total`,'ben_gp', NEW.`ben_gp`,'ben_gl', NEW.`ben_gl`,'eng_sub', NEW.`eng_sub`,'eng_obj', NEW.`eng_obj`,'eng_pra', NEW.`eng_pra`,'eng_ca', NEW.`eng_ca`,'eng_total', NEW.`eng_total`,'eng_gp', NEW.`eng_gp`,'eng_gl', NEW.`eng_gl`,'totalmarks', NEW.`totalmarks`,'full_marks', NEW.`full_marks`,'avgrate', NEW.`avgrate`,'gpa', NEW.`gpa`,'gpaadd', NEW.`gpaadd`,'gla', NEW.`gla`,'attnd', NEW.`attnd`,'twday', NEW.`twday`,'totalfail', NEW.`totalfail`,'meritplace', NEW.`meritplace`,'meritplacecomb', NEW.`meritplacecomb`,'meritplacegender', NEW.`meritplacegender`,'meritnum', NEW.`meritnum`,'meritnumcomb', NEW.`meritnumcomb`,'meritnumgender', NEW.`meritnumgender`,'totalgp', NEW.`totalgp`,'totalsubject', NEW.`totalsubject`,'sublist', NEW.`sublist`,'allsubject', NEW.`allsubject`,'gender', NEW.`gender`,'prevexam', NEW.`prevexam`,'thisexam', NEW.`thisexam`,'allfourth', NEW.`allfourth`,'failsub', NEW.`failsub`,'ai_comm', NEW.`ai_comm`,'modifieddate', NEW.`modifieddate`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER tabulatingsheet_after_update
    AFTER UPDATE ON `tabulatingsheet`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, new_data, changed_by)
        VALUES(
            'tabulatingsheet',
            'UPDATE',
            NEW.`id`,
            JSON_OBJECT('id', OLD.`id`,'sessionyear', OLD.`sessionyear`,'sccode', OLD.`sccode`,'slot', OLD.`slot`,'exam', OLD.`exam`,'classname', OLD.`classname`,'sectionname', OLD.`sectionname`,'stid', OLD.`stid`,'rollno', OLD.`rollno`,'sub_1', OLD.`sub_1`,'sub_2', OLD.`sub_2`,'sub_3', OLD.`sub_3`,'sub_4', OLD.`sub_4`,'sub_5', OLD.`sub_5`,'sub_6', OLD.`sub_6`,'sub_7', OLD.`sub_7`,'sub_8', OLD.`sub_8`,'sub_9', OLD.`sub_9`,'sub_10', OLD.`sub_10`,'sub_11', OLD.`sub_11`,'sub_12', OLD.`sub_12`,'sub_13', OLD.`sub_13`,'sub_14', OLD.`sub_14`,'sub_15', OLD.`sub_15`,'sub_1_sub', OLD.`sub_1_sub`,'sub_1_obj', OLD.`sub_1_obj`,'sub_1_pra', OLD.`sub_1_pra`,'sub_1_ca', OLD.`sub_1_ca`,'sub_1_total', OLD.`sub_1_total`,'sub_1_ct', OLD.`sub_1_ct`,'sub_1_mt', OLD.`sub_1_mt`,'sub_1_100', OLD.`sub_1_100`,'sub_1_gp', OLD.`sub_1_gp`,'sub_1_gl', OLD.`sub_1_gl`,'sub_2_sub', OLD.`sub_2_sub`,'sub_2_obj', OLD.`sub_2_obj`,'sub_2_pra', OLD.`sub_2_pra`,'sub_2_ca', OLD.`sub_2_ca`,'sub_2_total', OLD.`sub_2_total`,'sub_2_ct', OLD.`sub_2_ct`,'sub_2_mt', OLD.`sub_2_mt`,'sub_2_100', OLD.`sub_2_100`,'sub_2_gp', OLD.`sub_2_gp`,'sub_2_gl', OLD.`sub_2_gl`,'sub_3_sub', OLD.`sub_3_sub`,'sub_3_obj', OLD.`sub_3_obj`,'sub_3_pra', OLD.`sub_3_pra`,'sub_3_ca', OLD.`sub_3_ca`,'sub_3_total', OLD.`sub_3_total`,'sub_3_ct', OLD.`sub_3_ct`,'sub_3_mt', OLD.`sub_3_mt`,'sub_3_100', OLD.`sub_3_100`,'sub_3_gp', OLD.`sub_3_gp`,'sub_3_gl', OLD.`sub_3_gl`,'sub_4_sub', OLD.`sub_4_sub`,'sub_4_obj', OLD.`sub_4_obj`,'sub_4_pra', OLD.`sub_4_pra`,'sub_4_ca', OLD.`sub_4_ca`,'sub_4_total', OLD.`sub_4_total`,'sub_4_ct', OLD.`sub_4_ct`,'sub_4_mt', OLD.`sub_4_mt`,'sub_4_100', OLD.`sub_4_100`,'sub_4_gp', OLD.`sub_4_gp`,'sub_4_gl', OLD.`sub_4_gl`,'sub_5_sub', OLD.`sub_5_sub`,'sub_5_obj', OLD.`sub_5_obj`,'sub_5_pra', OLD.`sub_5_pra`,'sub_5_ca', OLD.`sub_5_ca`,'sub_5_total', OLD.`sub_5_total`,'sub_5_ct', OLD.`sub_5_ct`,'sub_5_mt', OLD.`sub_5_mt`,'sub_5_100', OLD.`sub_5_100`,'sub_5_gp', OLD.`sub_5_gp`,'sub_5_gl', OLD.`sub_5_gl`,'sub_6_sub', OLD.`sub_6_sub`,'sub_6_obj', OLD.`sub_6_obj`,'sub_6_pra', OLD.`sub_6_pra`,'sub_6_ca', OLD.`sub_6_ca`,'sub_6_total', OLD.`sub_6_total`,'sub_6_ct', OLD.`sub_6_ct`,'sub_6_mt', OLD.`sub_6_mt`,'sub_6_100', OLD.`sub_6_100`,'sub_6_gp', OLD.`sub_6_gp`,'sub_6_gl', OLD.`sub_6_gl`,'sub_7_sub', OLD.`sub_7_sub`,'sub_7_obj', OLD.`sub_7_obj`,'sub_7_pra', OLD.`sub_7_pra`,'sub_7_ca', OLD.`sub_7_ca`,'sub_7_total', OLD.`sub_7_total`,'sub_7_ct', OLD.`sub_7_ct`,'sub_7_mt', OLD.`sub_7_mt`,'sub_7_100', OLD.`sub_7_100`,'sub_7_gp', OLD.`sub_7_gp`,'sub_7_gl', OLD.`sub_7_gl`,'sub_8_sub', OLD.`sub_8_sub`,'sub_8_obj', OLD.`sub_8_obj`,'sub_8_pra', OLD.`sub_8_pra`,'sub_8_ca', OLD.`sub_8_ca`,'sub_8_total', OLD.`sub_8_total`,'sub_8_ct', OLD.`sub_8_ct`,'sub_8_mt', OLD.`sub_8_mt`,'sub_8_100', OLD.`sub_8_100`,'sub_8_gp', OLD.`sub_8_gp`,'sub_8_gl', OLD.`sub_8_gl`,'sub_9_sub', OLD.`sub_9_sub`,'sub_9_obj', OLD.`sub_9_obj`,'sub_9_pra', OLD.`sub_9_pra`,'sub_9_ca', OLD.`sub_9_ca`,'sub_9_total', OLD.`sub_9_total`,'sub_9_ct', OLD.`sub_9_ct`,'sub_9_mt', OLD.`sub_9_mt`,'sub_9_100', OLD.`sub_9_100`,'sub_9_gp', OLD.`sub_9_gp`,'sub_9_gl', OLD.`sub_9_gl`,'sub_10_sub', OLD.`sub_10_sub`,'sub_10_obj', OLD.`sub_10_obj`,'sub_10_pra', OLD.`sub_10_pra`,'sub_10_ca', OLD.`sub_10_ca`,'sub_10_total', OLD.`sub_10_total`,'sub_10_ct', OLD.`sub_10_ct`,'sub_10_mt', OLD.`sub_10_mt`,'sub_10_100', OLD.`sub_10_100`,'sub_10_gp', OLD.`sub_10_gp`,'sub_10_gl', OLD.`sub_10_gl`,'sub_11_sub', OLD.`sub_11_sub`,'sub_11_obj', OLD.`sub_11_obj`,'sub_11_pra', OLD.`sub_11_pra`,'sub_11_ca', OLD.`sub_11_ca`,'sub_11_total', OLD.`sub_11_total`,'sub_11_ct', OLD.`sub_11_ct`,'sub_11_mt', OLD.`sub_11_mt`,'sub_11_100', OLD.`sub_11_100`,'sub_11_gp', OLD.`sub_11_gp`,'sub_11_gl', OLD.`sub_11_gl`,'sub_12_sub', OLD.`sub_12_sub`,'sub_12_obj', OLD.`sub_12_obj`,'sub_12_pra', OLD.`sub_12_pra`,'sub_12_ca', OLD.`sub_12_ca`,'sub_12_total', OLD.`sub_12_total`,'sub_12_ct', OLD.`sub_12_ct`,'sub_12_mt', OLD.`sub_12_mt`,'sub_12_100', OLD.`sub_12_100`,'sub_12_gp', OLD.`sub_12_gp`,'sub_12_gl', OLD.`sub_12_gl`,'sub_13_sub', OLD.`sub_13_sub`,'sub_13_obj', OLD.`sub_13_obj`,'sub_13_pra', OLD.`sub_13_pra`,'sub_13_ca', OLD.`sub_13_ca`,'sub_13_total', OLD.`sub_13_total`,'sub_13_ct', OLD.`sub_13_ct`,'sub_13_mt', OLD.`sub_13_mt`,'sub_13_100', OLD.`sub_13_100`,'sub_13_gp', OLD.`sub_13_gp`,'sub_13_gl', OLD.`sub_13_gl`,'sub_14_sub', OLD.`sub_14_sub`,'sub_14_obj', OLD.`sub_14_obj`,'sub_14_pra', OLD.`sub_14_pra`,'sub_14_ca', OLD.`sub_14_ca`,'sub_14_total', OLD.`sub_14_total`,'sub_14_ct', OLD.`sub_14_ct`,'sub_14_mt', OLD.`sub_14_mt`,'sub_14_100', OLD.`sub_14_100`,'sub_14_gp', OLD.`sub_14_gp`,'sub_14_gl', OLD.`sub_14_gl`,'sub_15_sub', OLD.`sub_15_sub`,'sub_15_obj', OLD.`sub_15_obj`,'sub_15_pra', OLD.`sub_15_pra`,'sub_15_ca', OLD.`sub_15_ca`,'sub_15_total', OLD.`sub_15_total`,'sub_15_ct', OLD.`sub_15_ct`,'sub_15_mt', OLD.`sub_15_mt`,'sub_15_100', OLD.`sub_15_100`,'sub_15_gp', OLD.`sub_15_gp`,'sub_15_gl', OLD.`sub_15_gl`,'all_subs_entry', OLD.`all_subs_entry`,'ben_sub', OLD.`ben_sub`,'ben_obj', OLD.`ben_obj`,'ben_pra', OLD.`ben_pra`,'ben_ca', OLD.`ben_ca`,'ben_total', OLD.`ben_total`,'ben_gp', OLD.`ben_gp`,'ben_gl', OLD.`ben_gl`,'eng_sub', OLD.`eng_sub`,'eng_obj', OLD.`eng_obj`,'eng_pra', OLD.`eng_pra`,'eng_ca', OLD.`eng_ca`,'eng_total', OLD.`eng_total`,'eng_gp', OLD.`eng_gp`,'eng_gl', OLD.`eng_gl`,'totalmarks', OLD.`totalmarks`,'full_marks', OLD.`full_marks`,'avgrate', OLD.`avgrate`,'gpa', OLD.`gpa`,'gpaadd', OLD.`gpaadd`,'gla', OLD.`gla`,'attnd', OLD.`attnd`,'twday', OLD.`twday`,'totalfail', OLD.`totalfail`,'meritplace', OLD.`meritplace`,'meritplacecomb', OLD.`meritplacecomb`,'meritplacegender', OLD.`meritplacegender`,'meritnum', OLD.`meritnum`,'meritnumcomb', OLD.`meritnumcomb`,'meritnumgender', OLD.`meritnumgender`,'totalgp', OLD.`totalgp`,'totalsubject', OLD.`totalsubject`,'sublist', OLD.`sublist`,'allsubject', OLD.`allsubject`,'gender', OLD.`gender`,'prevexam', OLD.`prevexam`,'thisexam', OLD.`thisexam`,'allfourth', OLD.`allfourth`,'failsub', OLD.`failsub`,'ai_comm', OLD.`ai_comm`,'modifieddate', OLD.`modifieddate`),
            JSON_OBJECT('id', NEW.`id`,'sessionyear', NEW.`sessionyear`,'sccode', NEW.`sccode`,'slot', NEW.`slot`,'exam', NEW.`exam`,'classname', NEW.`classname`,'sectionname', NEW.`sectionname`,'stid', NEW.`stid`,'rollno', NEW.`rollno`,'sub_1', NEW.`sub_1`,'sub_2', NEW.`sub_2`,'sub_3', NEW.`sub_3`,'sub_4', NEW.`sub_4`,'sub_5', NEW.`sub_5`,'sub_6', NEW.`sub_6`,'sub_7', NEW.`sub_7`,'sub_8', NEW.`sub_8`,'sub_9', NEW.`sub_9`,'sub_10', NEW.`sub_10`,'sub_11', NEW.`sub_11`,'sub_12', NEW.`sub_12`,'sub_13', NEW.`sub_13`,'sub_14', NEW.`sub_14`,'sub_15', NEW.`sub_15`,'sub_1_sub', NEW.`sub_1_sub`,'sub_1_obj', NEW.`sub_1_obj`,'sub_1_pra', NEW.`sub_1_pra`,'sub_1_ca', NEW.`sub_1_ca`,'sub_1_total', NEW.`sub_1_total`,'sub_1_ct', NEW.`sub_1_ct`,'sub_1_mt', NEW.`sub_1_mt`,'sub_1_100', NEW.`sub_1_100`,'sub_1_gp', NEW.`sub_1_gp`,'sub_1_gl', NEW.`sub_1_gl`,'sub_2_sub', NEW.`sub_2_sub`,'sub_2_obj', NEW.`sub_2_obj`,'sub_2_pra', NEW.`sub_2_pra`,'sub_2_ca', NEW.`sub_2_ca`,'sub_2_total', NEW.`sub_2_total`,'sub_2_ct', NEW.`sub_2_ct`,'sub_2_mt', NEW.`sub_2_mt`,'sub_2_100', NEW.`sub_2_100`,'sub_2_gp', NEW.`sub_2_gp`,'sub_2_gl', NEW.`sub_2_gl`,'sub_3_sub', NEW.`sub_3_sub`,'sub_3_obj', NEW.`sub_3_obj`,'sub_3_pra', NEW.`sub_3_pra`,'sub_3_ca', NEW.`sub_3_ca`,'sub_3_total', NEW.`sub_3_total`,'sub_3_ct', NEW.`sub_3_ct`,'sub_3_mt', NEW.`sub_3_mt`,'sub_3_100', NEW.`sub_3_100`,'sub_3_gp', NEW.`sub_3_gp`,'sub_3_gl', NEW.`sub_3_gl`,'sub_4_sub', NEW.`sub_4_sub`,'sub_4_obj', NEW.`sub_4_obj`,'sub_4_pra', NEW.`sub_4_pra`,'sub_4_ca', NEW.`sub_4_ca`,'sub_4_total', NEW.`sub_4_total`,'sub_4_ct', NEW.`sub_4_ct`,'sub_4_mt', NEW.`sub_4_mt`,'sub_4_100', NEW.`sub_4_100`,'sub_4_gp', NEW.`sub_4_gp`,'sub_4_gl', NEW.`sub_4_gl`,'sub_5_sub', NEW.`sub_5_sub`,'sub_5_obj', NEW.`sub_5_obj`,'sub_5_pra', NEW.`sub_5_pra`,'sub_5_ca', NEW.`sub_5_ca`,'sub_5_total', NEW.`sub_5_total`,'sub_5_ct', NEW.`sub_5_ct`,'sub_5_mt', NEW.`sub_5_mt`,'sub_5_100', NEW.`sub_5_100`,'sub_5_gp', NEW.`sub_5_gp`,'sub_5_gl', NEW.`sub_5_gl`,'sub_6_sub', NEW.`sub_6_sub`,'sub_6_obj', NEW.`sub_6_obj`,'sub_6_pra', NEW.`sub_6_pra`,'sub_6_ca', NEW.`sub_6_ca`,'sub_6_total', NEW.`sub_6_total`,'sub_6_ct', NEW.`sub_6_ct`,'sub_6_mt', NEW.`sub_6_mt`,'sub_6_100', NEW.`sub_6_100`,'sub_6_gp', NEW.`sub_6_gp`,'sub_6_gl', NEW.`sub_6_gl`,'sub_7_sub', NEW.`sub_7_sub`,'sub_7_obj', NEW.`sub_7_obj`,'sub_7_pra', NEW.`sub_7_pra`,'sub_7_ca', NEW.`sub_7_ca`,'sub_7_total', NEW.`sub_7_total`,'sub_7_ct', NEW.`sub_7_ct`,'sub_7_mt', NEW.`sub_7_mt`,'sub_7_100', NEW.`sub_7_100`,'sub_7_gp', NEW.`sub_7_gp`,'sub_7_gl', NEW.`sub_7_gl`,'sub_8_sub', NEW.`sub_8_sub`,'sub_8_obj', NEW.`sub_8_obj`,'sub_8_pra', NEW.`sub_8_pra`,'sub_8_ca', NEW.`sub_8_ca`,'sub_8_total', NEW.`sub_8_total`,'sub_8_ct', NEW.`sub_8_ct`,'sub_8_mt', NEW.`sub_8_mt`,'sub_8_100', NEW.`sub_8_100`,'sub_8_gp', NEW.`sub_8_gp`,'sub_8_gl', NEW.`sub_8_gl`,'sub_9_sub', NEW.`sub_9_sub`,'sub_9_obj', NEW.`sub_9_obj`,'sub_9_pra', NEW.`sub_9_pra`,'sub_9_ca', NEW.`sub_9_ca`,'sub_9_total', NEW.`sub_9_total`,'sub_9_ct', NEW.`sub_9_ct`,'sub_9_mt', NEW.`sub_9_mt`,'sub_9_100', NEW.`sub_9_100`,'sub_9_gp', NEW.`sub_9_gp`,'sub_9_gl', NEW.`sub_9_gl`,'sub_10_sub', NEW.`sub_10_sub`,'sub_10_obj', NEW.`sub_10_obj`,'sub_10_pra', NEW.`sub_10_pra`,'sub_10_ca', NEW.`sub_10_ca`,'sub_10_total', NEW.`sub_10_total`,'sub_10_ct', NEW.`sub_10_ct`,'sub_10_mt', NEW.`sub_10_mt`,'sub_10_100', NEW.`sub_10_100`,'sub_10_gp', NEW.`sub_10_gp`,'sub_10_gl', NEW.`sub_10_gl`,'sub_11_sub', NEW.`sub_11_sub`,'sub_11_obj', NEW.`sub_11_obj`,'sub_11_pra', NEW.`sub_11_pra`,'sub_11_ca', NEW.`sub_11_ca`,'sub_11_total', NEW.`sub_11_total`,'sub_11_ct', NEW.`sub_11_ct`,'sub_11_mt', NEW.`sub_11_mt`,'sub_11_100', NEW.`sub_11_100`,'sub_11_gp', NEW.`sub_11_gp`,'sub_11_gl', NEW.`sub_11_gl`,'sub_12_sub', NEW.`sub_12_sub`,'sub_12_obj', NEW.`sub_12_obj`,'sub_12_pra', NEW.`sub_12_pra`,'sub_12_ca', NEW.`sub_12_ca`,'sub_12_total', NEW.`sub_12_total`,'sub_12_ct', NEW.`sub_12_ct`,'sub_12_mt', NEW.`sub_12_mt`,'sub_12_100', NEW.`sub_12_100`,'sub_12_gp', NEW.`sub_12_gp`,'sub_12_gl', NEW.`sub_12_gl`,'sub_13_sub', NEW.`sub_13_sub`,'sub_13_obj', NEW.`sub_13_obj`,'sub_13_pra', NEW.`sub_13_pra`,'sub_13_ca', NEW.`sub_13_ca`,'sub_13_total', NEW.`sub_13_total`,'sub_13_ct', NEW.`sub_13_ct`,'sub_13_mt', NEW.`sub_13_mt`,'sub_13_100', NEW.`sub_13_100`,'sub_13_gp', NEW.`sub_13_gp`,'sub_13_gl', NEW.`sub_13_gl`,'sub_14_sub', NEW.`sub_14_sub`,'sub_14_obj', NEW.`sub_14_obj`,'sub_14_pra', NEW.`sub_14_pra`,'sub_14_ca', NEW.`sub_14_ca`,'sub_14_total', NEW.`sub_14_total`,'sub_14_ct', NEW.`sub_14_ct`,'sub_14_mt', NEW.`sub_14_mt`,'sub_14_100', NEW.`sub_14_100`,'sub_14_gp', NEW.`sub_14_gp`,'sub_14_gl', NEW.`sub_14_gl`,'sub_15_sub', NEW.`sub_15_sub`,'sub_15_obj', NEW.`sub_15_obj`,'sub_15_pra', NEW.`sub_15_pra`,'sub_15_ca', NEW.`sub_15_ca`,'sub_15_total', NEW.`sub_15_total`,'sub_15_ct', NEW.`sub_15_ct`,'sub_15_mt', NEW.`sub_15_mt`,'sub_15_100', NEW.`sub_15_100`,'sub_15_gp', NEW.`sub_15_gp`,'sub_15_gl', NEW.`sub_15_gl`,'all_subs_entry', NEW.`all_subs_entry`,'ben_sub', NEW.`ben_sub`,'ben_obj', NEW.`ben_obj`,'ben_pra', NEW.`ben_pra`,'ben_ca', NEW.`ben_ca`,'ben_total', NEW.`ben_total`,'ben_gp', NEW.`ben_gp`,'ben_gl', NEW.`ben_gl`,'eng_sub', NEW.`eng_sub`,'eng_obj', NEW.`eng_obj`,'eng_pra', NEW.`eng_pra`,'eng_ca', NEW.`eng_ca`,'eng_total', NEW.`eng_total`,'eng_gp', NEW.`eng_gp`,'eng_gl', NEW.`eng_gl`,'totalmarks', NEW.`totalmarks`,'full_marks', NEW.`full_marks`,'avgrate', NEW.`avgrate`,'gpa', NEW.`gpa`,'gpaadd', NEW.`gpaadd`,'gla', NEW.`gla`,'attnd', NEW.`attnd`,'twday', NEW.`twday`,'totalfail', NEW.`totalfail`,'meritplace', NEW.`meritplace`,'meritplacecomb', NEW.`meritplacecomb`,'meritplacegender', NEW.`meritplacegender`,'meritnum', NEW.`meritnum`,'meritnumcomb', NEW.`meritnumcomb`,'meritnumgender', NEW.`meritnumgender`,'totalgp', NEW.`totalgp`,'totalsubject', NEW.`totalsubject`,'sublist', NEW.`sublist`,'allsubject', NEW.`allsubject`,'gender', NEW.`gender`,'prevexam', NEW.`prevexam`,'thisexam', NEW.`thisexam`,'allfourth', NEW.`allfourth`,'failsub', NEW.`failsub`,'ai_comm', NEW.`ai_comm`,'modifieddate', NEW.`modifieddate`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER tabulatingsheet_after_delete
    AFTER DELETE ON `tabulatingsheet`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, changed_by)
        VALUES(
            'tabulatingsheet',
            'DELETE',
            OLD.`id`,
            JSON_OBJECT('id', OLD.`id`,'sessionyear', OLD.`sessionyear`,'sccode', OLD.`sccode`,'slot', OLD.`slot`,'exam', OLD.`exam`,'classname', OLD.`classname`,'sectionname', OLD.`sectionname`,'stid', OLD.`stid`,'rollno', OLD.`rollno`,'sub_1', OLD.`sub_1`,'sub_2', OLD.`sub_2`,'sub_3', OLD.`sub_3`,'sub_4', OLD.`sub_4`,'sub_5', OLD.`sub_5`,'sub_6', OLD.`sub_6`,'sub_7', OLD.`sub_7`,'sub_8', OLD.`sub_8`,'sub_9', OLD.`sub_9`,'sub_10', OLD.`sub_10`,'sub_11', OLD.`sub_11`,'sub_12', OLD.`sub_12`,'sub_13', OLD.`sub_13`,'sub_14', OLD.`sub_14`,'sub_15', OLD.`sub_15`,'sub_1_sub', OLD.`sub_1_sub`,'sub_1_obj', OLD.`sub_1_obj`,'sub_1_pra', OLD.`sub_1_pra`,'sub_1_ca', OLD.`sub_1_ca`,'sub_1_total', OLD.`sub_1_total`,'sub_1_ct', OLD.`sub_1_ct`,'sub_1_mt', OLD.`sub_1_mt`,'sub_1_100', OLD.`sub_1_100`,'sub_1_gp', OLD.`sub_1_gp`,'sub_1_gl', OLD.`sub_1_gl`,'sub_2_sub', OLD.`sub_2_sub`,'sub_2_obj', OLD.`sub_2_obj`,'sub_2_pra', OLD.`sub_2_pra`,'sub_2_ca', OLD.`sub_2_ca`,'sub_2_total', OLD.`sub_2_total`,'sub_2_ct', OLD.`sub_2_ct`,'sub_2_mt', OLD.`sub_2_mt`,'sub_2_100', OLD.`sub_2_100`,'sub_2_gp', OLD.`sub_2_gp`,'sub_2_gl', OLD.`sub_2_gl`,'sub_3_sub', OLD.`sub_3_sub`,'sub_3_obj', OLD.`sub_3_obj`,'sub_3_pra', OLD.`sub_3_pra`,'sub_3_ca', OLD.`sub_3_ca`,'sub_3_total', OLD.`sub_3_total`,'sub_3_ct', OLD.`sub_3_ct`,'sub_3_mt', OLD.`sub_3_mt`,'sub_3_100', OLD.`sub_3_100`,'sub_3_gp', OLD.`sub_3_gp`,'sub_3_gl', OLD.`sub_3_gl`,'sub_4_sub', OLD.`sub_4_sub`,'sub_4_obj', OLD.`sub_4_obj`,'sub_4_pra', OLD.`sub_4_pra`,'sub_4_ca', OLD.`sub_4_ca`,'sub_4_total', OLD.`sub_4_total`,'sub_4_ct', OLD.`sub_4_ct`,'sub_4_mt', OLD.`sub_4_mt`,'sub_4_100', OLD.`sub_4_100`,'sub_4_gp', OLD.`sub_4_gp`,'sub_4_gl', OLD.`sub_4_gl`,'sub_5_sub', OLD.`sub_5_sub`,'sub_5_obj', OLD.`sub_5_obj`,'sub_5_pra', OLD.`sub_5_pra`,'sub_5_ca', OLD.`sub_5_ca`,'sub_5_total', OLD.`sub_5_total`,'sub_5_ct', OLD.`sub_5_ct`,'sub_5_mt', OLD.`sub_5_mt`,'sub_5_100', OLD.`sub_5_100`,'sub_5_gp', OLD.`sub_5_gp`,'sub_5_gl', OLD.`sub_5_gl`,'sub_6_sub', OLD.`sub_6_sub`,'sub_6_obj', OLD.`sub_6_obj`,'sub_6_pra', OLD.`sub_6_pra`,'sub_6_ca', OLD.`sub_6_ca`,'sub_6_total', OLD.`sub_6_total`,'sub_6_ct', OLD.`sub_6_ct`,'sub_6_mt', OLD.`sub_6_mt`,'sub_6_100', OLD.`sub_6_100`,'sub_6_gp', OLD.`sub_6_gp`,'sub_6_gl', OLD.`sub_6_gl`,'sub_7_sub', OLD.`sub_7_sub`,'sub_7_obj', OLD.`sub_7_obj`,'sub_7_pra', OLD.`sub_7_pra`,'sub_7_ca', OLD.`sub_7_ca`,'sub_7_total', OLD.`sub_7_total`,'sub_7_ct', OLD.`sub_7_ct`,'sub_7_mt', OLD.`sub_7_mt`,'sub_7_100', OLD.`sub_7_100`,'sub_7_gp', OLD.`sub_7_gp`,'sub_7_gl', OLD.`sub_7_gl`,'sub_8_sub', OLD.`sub_8_sub`,'sub_8_obj', OLD.`sub_8_obj`,'sub_8_pra', OLD.`sub_8_pra`,'sub_8_ca', OLD.`sub_8_ca`,'sub_8_total', OLD.`sub_8_total`,'sub_8_ct', OLD.`sub_8_ct`,'sub_8_mt', OLD.`sub_8_mt`,'sub_8_100', OLD.`sub_8_100`,'sub_8_gp', OLD.`sub_8_gp`,'sub_8_gl', OLD.`sub_8_gl`,'sub_9_sub', OLD.`sub_9_sub`,'sub_9_obj', OLD.`sub_9_obj`,'sub_9_pra', OLD.`sub_9_pra`,'sub_9_ca', OLD.`sub_9_ca`,'sub_9_total', OLD.`sub_9_total`,'sub_9_ct', OLD.`sub_9_ct`,'sub_9_mt', OLD.`sub_9_mt`,'sub_9_100', OLD.`sub_9_100`,'sub_9_gp', OLD.`sub_9_gp`,'sub_9_gl', OLD.`sub_9_gl`,'sub_10_sub', OLD.`sub_10_sub`,'sub_10_obj', OLD.`sub_10_obj`,'sub_10_pra', OLD.`sub_10_pra`,'sub_10_ca', OLD.`sub_10_ca`,'sub_10_total', OLD.`sub_10_total`,'sub_10_ct', OLD.`sub_10_ct`,'sub_10_mt', OLD.`sub_10_mt`,'sub_10_100', OLD.`sub_10_100`,'sub_10_gp', OLD.`sub_10_gp`,'sub_10_gl', OLD.`sub_10_gl`,'sub_11_sub', OLD.`sub_11_sub`,'sub_11_obj', OLD.`sub_11_obj`,'sub_11_pra', OLD.`sub_11_pra`,'sub_11_ca', OLD.`sub_11_ca`,'sub_11_total', OLD.`sub_11_total`,'sub_11_ct', OLD.`sub_11_ct`,'sub_11_mt', OLD.`sub_11_mt`,'sub_11_100', OLD.`sub_11_100`,'sub_11_gp', OLD.`sub_11_gp`,'sub_11_gl', OLD.`sub_11_gl`,'sub_12_sub', OLD.`sub_12_sub`,'sub_12_obj', OLD.`sub_12_obj`,'sub_12_pra', OLD.`sub_12_pra`,'sub_12_ca', OLD.`sub_12_ca`,'sub_12_total', OLD.`sub_12_total`,'sub_12_ct', OLD.`sub_12_ct`,'sub_12_mt', OLD.`sub_12_mt`,'sub_12_100', OLD.`sub_12_100`,'sub_12_gp', OLD.`sub_12_gp`,'sub_12_gl', OLD.`sub_12_gl`,'sub_13_sub', OLD.`sub_13_sub`,'sub_13_obj', OLD.`sub_13_obj`,'sub_13_pra', OLD.`sub_13_pra`,'sub_13_ca', OLD.`sub_13_ca`,'sub_13_total', OLD.`sub_13_total`,'sub_13_ct', OLD.`sub_13_ct`,'sub_13_mt', OLD.`sub_13_mt`,'sub_13_100', OLD.`sub_13_100`,'sub_13_gp', OLD.`sub_13_gp`,'sub_13_gl', OLD.`sub_13_gl`,'sub_14_sub', OLD.`sub_14_sub`,'sub_14_obj', OLD.`sub_14_obj`,'sub_14_pra', OLD.`sub_14_pra`,'sub_14_ca', OLD.`sub_14_ca`,'sub_14_total', OLD.`sub_14_total`,'sub_14_ct', OLD.`sub_14_ct`,'sub_14_mt', OLD.`sub_14_mt`,'sub_14_100', OLD.`sub_14_100`,'sub_14_gp', OLD.`sub_14_gp`,'sub_14_gl', OLD.`sub_14_gl`,'sub_15_sub', OLD.`sub_15_sub`,'sub_15_obj', OLD.`sub_15_obj`,'sub_15_pra', OLD.`sub_15_pra`,'sub_15_ca', OLD.`sub_15_ca`,'sub_15_total', OLD.`sub_15_total`,'sub_15_ct', OLD.`sub_15_ct`,'sub_15_mt', OLD.`sub_15_mt`,'sub_15_100', OLD.`sub_15_100`,'sub_15_gp', OLD.`sub_15_gp`,'sub_15_gl', OLD.`sub_15_gl`,'all_subs_entry', OLD.`all_subs_entry`,'ben_sub', OLD.`ben_sub`,'ben_obj', OLD.`ben_obj`,'ben_pra', OLD.`ben_pra`,'ben_ca', OLD.`ben_ca`,'ben_total', OLD.`ben_total`,'ben_gp', OLD.`ben_gp`,'ben_gl', OLD.`ben_gl`,'eng_sub', OLD.`eng_sub`,'eng_obj', OLD.`eng_obj`,'eng_pra', OLD.`eng_pra`,'eng_ca', OLD.`eng_ca`,'eng_total', OLD.`eng_total`,'eng_gp', OLD.`eng_gp`,'eng_gl', OLD.`eng_gl`,'totalmarks', OLD.`totalmarks`,'full_marks', OLD.`full_marks`,'avgrate', OLD.`avgrate`,'gpa', OLD.`gpa`,'gpaadd', OLD.`gpaadd`,'gla', OLD.`gla`,'attnd', OLD.`attnd`,'twday', OLD.`twday`,'totalfail', OLD.`totalfail`,'meritplace', OLD.`meritplace`,'meritplacecomb', OLD.`meritplacecomb`,'meritplacegender', OLD.`meritplacegender`,'meritnum', OLD.`meritnum`,'meritnumcomb', OLD.`meritnumcomb`,'meritnumgender', OLD.`meritnumgender`,'totalgp', OLD.`totalgp`,'totalsubject', OLD.`totalsubject`,'sublist', OLD.`sublist`,'allsubject', OLD.`allsubject`,'gender', OLD.`gender`,'prevexam', OLD.`prevexam`,'thisexam', OLD.`thisexam`,'allfourth', OLD.`allfourth`,'failsub', OLD.`failsub`,'ai_comm', OLD.`ai_comm`,'modifieddate', OLD.`modifieddate`),
            COALESCE(@current_user,'system')
        );
    END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `tabulatingsheetex`
--

DROP TABLE IF EXISTS `tabulatingsheetex`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tabulatingsheetex` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tsheet_id` bigint(20) DEFAULT NULL,
  `sessionyear` varchar(10) DEFAULT NULL,
  `sccode` int(11) NOT NULL,
  `slot` varchar(20) NOT NULL DEFAULT 'School',
  `exam` varchar(50) NOT NULL,
  `classname` varchar(20) NOT NULL,
  `sectionname` varchar(20) NOT NULL,
  `stid` bigint(20) DEFAULT NULL,
  `rollno` int(11) NOT NULL,
  `sub_1` varchar(20) DEFAULT NULL,
  `sub_2` varchar(20) DEFAULT NULL,
  `sub_3` varchar(20) DEFAULT NULL,
  `sub_4` varchar(20) DEFAULT NULL,
  `sub_5` varchar(20) DEFAULT NULL,
  `sub_6` varchar(20) DEFAULT NULL,
  `sub_7` varchar(20) DEFAULT NULL,
  `sub_8` varchar(20) DEFAULT NULL,
  `sub_9` varchar(20) DEFAULT NULL,
  `sub_10` varchar(20) DEFAULT NULL,
  `sub_code_1` int(11) DEFAULT NULL,
  `sub_code_2` int(11) DEFAULT NULL,
  `sub_code_3` int(11) DEFAULT NULL,
  `sub_code_4` int(11) DEFAULT NULL,
  `sub_code_5` int(11) DEFAULT NULL,
  `sub_code_6` int(11) DEFAULT NULL,
  `sub_code_7` int(11) DEFAULT NULL,
  `sub_code_8` int(11) DEFAULT NULL,
  `sub_code_9` int(11) DEFAULT NULL,
  `sub_code_10` int(11) DEFAULT NULL,
  `sub_name_1` varchar(20) DEFAULT NULL,
  `sub_name_2` varchar(20) DEFAULT NULL,
  `sub_name_3` varchar(20) DEFAULT NULL,
  `sub_name_4` varchar(20) DEFAULT NULL,
  `sub_name_5` varchar(20) DEFAULT NULL,
  `sub_name_6` varchar(20) DEFAULT NULL,
  `sub_name_7` varchar(20) DEFAULT NULL,
  `sub_name_8` varchar(20) DEFAULT NULL,
  `sub_name_9` varchar(20) DEFAULT NULL,
  `sub_name_10` varchar(20) DEFAULT NULL,
  `sub_fm_1` int(11) NOT NULL DEFAULT 0,
  `sub_fm_2` int(11) NOT NULL DEFAULT 0,
  `sub_fm_3` int(11) NOT NULL DEFAULT 0,
  `sub_fm_4` int(11) NOT NULL DEFAULT 0,
  `sub_fm_5` int(11) NOT NULL DEFAULT 0,
  `sub_fm_6` int(11) NOT NULL DEFAULT 0,
  `sub_fm_7` int(11) NOT NULL DEFAULT 0,
  `sub_fm_8` int(11) NOT NULL DEFAULT 0,
  `sub_fm_9` int(11) NOT NULL DEFAULT 0,
  `sub_fm_10` int(11) NOT NULL DEFAULT 0,
  `sub_1_sub` int(11) DEFAULT NULL,
  `sub_1_obj` int(11) DEFAULT NULL,
  `sub_1_pra` int(11) DEFAULT NULL,
  `sub_1_ca` decimal(5,2) DEFAULT NULL,
  `sub_1_total` decimal(5,2) DEFAULT NULL,
  `sub_1_gp` decimal(3,2) DEFAULT NULL,
  `sub_1_gl` varchar(3) DEFAULT NULL,
  `sub_1_ct` int(11) DEFAULT NULL,
  `sub_1_mt` int(11) DEFAULT NULL,
  `sub_2_sub` int(11) DEFAULT NULL,
  `sub_2_obj` int(11) DEFAULT NULL,
  `sub_2_pra` int(11) DEFAULT NULL,
  `sub_2_ca` decimal(5,2) DEFAULT NULL,
  `sub_2_total` decimal(5,2) DEFAULT NULL,
  `sub_2_gp` decimal(3,2) DEFAULT NULL,
  `sub_2_gl` varchar(2) DEFAULT NULL,
  `sub_3_sub` int(11) DEFAULT NULL,
  `sub_3_obj` int(11) DEFAULT NULL,
  `sub_3_pra` int(11) DEFAULT NULL,
  `sub_3_ca` decimal(5,2) DEFAULT NULL,
  `sub_3_total` decimal(5,2) DEFAULT NULL,
  `sub_3_gp` decimal(3,2) DEFAULT NULL,
  `sub_3_gl` varchar(3) DEFAULT NULL,
  `sub_4_sub` int(11) DEFAULT NULL,
  `sub_4_obj` int(11) DEFAULT NULL,
  `sub_4_pra` int(11) DEFAULT NULL,
  `sub_4_ca` decimal(5,2) DEFAULT NULL,
  `sub_4_total` decimal(5,2) DEFAULT NULL,
  `sub_4_gp` decimal(3,2) DEFAULT NULL,
  `sub_4_gl` varchar(3) DEFAULT NULL,
  `sub_5_sub` int(11) DEFAULT NULL,
  `sub_5_obj` int(11) DEFAULT NULL,
  `sub_5_pra` int(11) DEFAULT NULL,
  `sub_5_ca` decimal(5,2) DEFAULT NULL,
  `sub_5_total` decimal(5,2) DEFAULT NULL,
  `sub_5_gp` decimal(3,2) DEFAULT NULL,
  `sub_5_gl` varchar(3) DEFAULT NULL,
  `sub_6_sub` int(11) DEFAULT NULL,
  `sub_6_obj` int(11) DEFAULT NULL,
  `sub_6_pra` int(11) DEFAULT NULL,
  `sub_6_ca` decimal(5,2) DEFAULT NULL,
  `sub_6_total` decimal(5,2) DEFAULT NULL,
  `sub_6_gp` decimal(3,2) DEFAULT NULL,
  `sub_6_gl` varchar(3) DEFAULT NULL,
  `sub_7_sub` int(11) DEFAULT NULL,
  `sub_7_obj` int(11) DEFAULT NULL,
  `sub_7_pra` int(11) DEFAULT NULL,
  `sub_7_ca` decimal(5,2) DEFAULT NULL,
  `sub_7_total` decimal(5,2) DEFAULT NULL,
  `sub_7_gp` decimal(3,2) DEFAULT NULL,
  `sub_7_gl` varchar(3) DEFAULT NULL,
  `sub_8_sub` int(11) DEFAULT NULL,
  `sub_8_obj` int(11) DEFAULT NULL,
  `sub_8_pra` int(11) DEFAULT NULL,
  `sub_8_ca` decimal(5,2) DEFAULT NULL,
  `sub_8_total` date DEFAULT NULL,
  `sub_8_gp` decimal(3,2) DEFAULT NULL,
  `sub_8_gl` varchar(3) DEFAULT NULL,
  `sub_9_sub` int(11) DEFAULT NULL,
  `sub_9_obj` int(11) DEFAULT NULL,
  `sub_9_pra` int(11) DEFAULT NULL,
  `sub_9_ca` decimal(5,2) DEFAULT NULL,
  `sub_9_total` decimal(5,2) DEFAULT NULL,
  `sub_9_gp` decimal(3,2) DEFAULT NULL,
  `sub_9_gl` varchar(3) DEFAULT NULL,
  `sub_10_sub` int(11) DEFAULT NULL,
  `sub_10_obj` int(11) DEFAULT NULL,
  `sub_10_pra` int(11) DEFAULT NULL,
  `sub_10_ca` decimal(5,2) DEFAULT NULL,
  `sub_10_total` decimal(5,2) DEFAULT NULL,
  `sub_10_gp` decimal(3,2) DEFAULT NULL,
  `sub_10_gl` varchar(3) DEFAULT NULL,
  `fourth_subj` int(11) DEFAULT NULL,
  `fourth_obj` int(11) DEFAULT NULL,
  `fourth_pra` int(11) DEFAULT NULL,
  `fourth_ca` decimal(5,2) DEFAULT NULL,
  `fourth_total` decimal(5,2) DEFAULT NULL,
  `fourth_gp` decimal(10,0) NOT NULL DEFAULT 0,
  `fourth_gl` varchar(3) DEFAULT NULL,
  `add_gp` decimal(10,2) NOT NULL DEFAULT 0.00,
  `totalmarks` decimal(6,2) DEFAULT NULL,
  `full_marks` int(11) DEFAULT NULL,
  `avgrate` decimal(5,2) DEFAULT NULL,
  `gpa` decimal(3,2) DEFAULT NULL,
  `gla` varchar(3) DEFAULT NULL,
  `attnd` int(11) DEFAULT NULL,
  `twday` int(11) DEFAULT NULL,
  `totalfail` int(11) DEFAULT NULL,
  `meritplace` varchar(10) DEFAULT NULL,
  `totalgp` decimal(5,2) DEFAULT NULL,
  `totalsubject` int(11) DEFAULT NULL,
  `gender` varchar(6) DEFAULT NULL,
  `prevexam` int(11) NOT NULL DEFAULT 0,
  `thisexam` int(11) NOT NULL DEFAULT 0,
  `allsubject` varchar(200) NOT NULL,
  `allfourth` varchar(12) DEFAULT '000',
  `failsub` varchar(100) DEFAULT NULL,
  `modifieddate` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=77756 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
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
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER tabulatingsheetex_after_insert
    AFTER INSERT ON `tabulatingsheetex`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, new_data, changed_by)
        VALUES(
            'tabulatingsheetex',
            'INSERT',
            NEW.`id`,
            JSON_OBJECT('id', NEW.`id`,'tsheet_id', NEW.`tsheet_id`,'sessionyear', NEW.`sessionyear`,'sccode', NEW.`sccode`,'slot', NEW.`slot`,'exam', NEW.`exam`,'classname', NEW.`classname`,'sectionname', NEW.`sectionname`,'stid', NEW.`stid`,'rollno', NEW.`rollno`,'sub_1', NEW.`sub_1`,'sub_2', NEW.`sub_2`,'sub_3', NEW.`sub_3`,'sub_4', NEW.`sub_4`,'sub_5', NEW.`sub_5`,'sub_6', NEW.`sub_6`,'sub_7', NEW.`sub_7`,'sub_8', NEW.`sub_8`,'sub_9', NEW.`sub_9`,'sub_10', NEW.`sub_10`,'sub_code_1', NEW.`sub_code_1`,'sub_code_2', NEW.`sub_code_2`,'sub_code_3', NEW.`sub_code_3`,'sub_code_4', NEW.`sub_code_4`,'sub_code_5', NEW.`sub_code_5`,'sub_code_6', NEW.`sub_code_6`,'sub_code_7', NEW.`sub_code_7`,'sub_code_8', NEW.`sub_code_8`,'sub_code_9', NEW.`sub_code_9`,'sub_code_10', NEW.`sub_code_10`,'sub_name_1', NEW.`sub_name_1`,'sub_name_2', NEW.`sub_name_2`,'sub_name_3', NEW.`sub_name_3`,'sub_name_4', NEW.`sub_name_4`,'sub_name_5', NEW.`sub_name_5`,'sub_name_6', NEW.`sub_name_6`,'sub_name_7', NEW.`sub_name_7`,'sub_name_8', NEW.`sub_name_8`,'sub_name_9', NEW.`sub_name_9`,'sub_name_10', NEW.`sub_name_10`,'sub_fm_1', NEW.`sub_fm_1`,'sub_fm_2', NEW.`sub_fm_2`,'sub_fm_3', NEW.`sub_fm_3`,'sub_fm_4', NEW.`sub_fm_4`,'sub_fm_5', NEW.`sub_fm_5`,'sub_fm_6', NEW.`sub_fm_6`,'sub_fm_7', NEW.`sub_fm_7`,'sub_fm_8', NEW.`sub_fm_8`,'sub_fm_9', NEW.`sub_fm_9`,'sub_fm_10', NEW.`sub_fm_10`,'sub_1_sub', NEW.`sub_1_sub`,'sub_1_obj', NEW.`sub_1_obj`,'sub_1_pra', NEW.`sub_1_pra`,'sub_1_ca', NEW.`sub_1_ca`,'sub_1_total', NEW.`sub_1_total`,'sub_1_gp', NEW.`sub_1_gp`,'sub_1_gl', NEW.`sub_1_gl`,'sub_1_ct', NEW.`sub_1_ct`,'sub_1_mt', NEW.`sub_1_mt`,'sub_2_sub', NEW.`sub_2_sub`,'sub_2_obj', NEW.`sub_2_obj`,'sub_2_pra', NEW.`sub_2_pra`,'sub_2_ca', NEW.`sub_2_ca`,'sub_2_total', NEW.`sub_2_total`,'sub_2_gp', NEW.`sub_2_gp`,'sub_2_gl', NEW.`sub_2_gl`,'sub_3_sub', NEW.`sub_3_sub`,'sub_3_obj', NEW.`sub_3_obj`,'sub_3_pra', NEW.`sub_3_pra`,'sub_3_ca', NEW.`sub_3_ca`,'sub_3_total', NEW.`sub_3_total`,'sub_3_gp', NEW.`sub_3_gp`,'sub_3_gl', NEW.`sub_3_gl`,'sub_4_sub', NEW.`sub_4_sub`,'sub_4_obj', NEW.`sub_4_obj`,'sub_4_pra', NEW.`sub_4_pra`,'sub_4_ca', NEW.`sub_4_ca`,'sub_4_total', NEW.`sub_4_total`,'sub_4_gp', NEW.`sub_4_gp`,'sub_4_gl', NEW.`sub_4_gl`,'sub_5_sub', NEW.`sub_5_sub`,'sub_5_obj', NEW.`sub_5_obj`,'sub_5_pra', NEW.`sub_5_pra`,'sub_5_ca', NEW.`sub_5_ca`,'sub_5_total', NEW.`sub_5_total`,'sub_5_gp', NEW.`sub_5_gp`,'sub_5_gl', NEW.`sub_5_gl`,'sub_6_sub', NEW.`sub_6_sub`,'sub_6_obj', NEW.`sub_6_obj`,'sub_6_pra', NEW.`sub_6_pra`,'sub_6_ca', NEW.`sub_6_ca`,'sub_6_total', NEW.`sub_6_total`,'sub_6_gp', NEW.`sub_6_gp`,'sub_6_gl', NEW.`sub_6_gl`,'sub_7_sub', NEW.`sub_7_sub`,'sub_7_obj', NEW.`sub_7_obj`,'sub_7_pra', NEW.`sub_7_pra`,'sub_7_ca', NEW.`sub_7_ca`,'sub_7_total', NEW.`sub_7_total`,'sub_7_gp', NEW.`sub_7_gp`,'sub_7_gl', NEW.`sub_7_gl`,'sub_8_sub', NEW.`sub_8_sub`,'sub_8_obj', NEW.`sub_8_obj`,'sub_8_pra', NEW.`sub_8_pra`,'sub_8_ca', NEW.`sub_8_ca`,'sub_8_total', NEW.`sub_8_total`,'sub_8_gp', NEW.`sub_8_gp`,'sub_8_gl', NEW.`sub_8_gl`,'sub_9_sub', NEW.`sub_9_sub`,'sub_9_obj', NEW.`sub_9_obj`,'sub_9_pra', NEW.`sub_9_pra`,'sub_9_ca', NEW.`sub_9_ca`,'sub_9_total', NEW.`sub_9_total`,'sub_9_gp', NEW.`sub_9_gp`,'sub_9_gl', NEW.`sub_9_gl`,'sub_10_sub', NEW.`sub_10_sub`,'sub_10_obj', NEW.`sub_10_obj`,'sub_10_pra', NEW.`sub_10_pra`,'sub_10_ca', NEW.`sub_10_ca`,'sub_10_total', NEW.`sub_10_total`,'sub_10_gp', NEW.`sub_10_gp`,'sub_10_gl', NEW.`sub_10_gl`,'fourth_subj', NEW.`fourth_subj`,'fourth_obj', NEW.`fourth_obj`,'fourth_pra', NEW.`fourth_pra`,'fourth_ca', NEW.`fourth_ca`,'fourth_total', NEW.`fourth_total`,'fourth_gp', NEW.`fourth_gp`,'fourth_gl', NEW.`fourth_gl`,'add_gp', NEW.`add_gp`,'totalmarks', NEW.`totalmarks`,'full_marks', NEW.`full_marks`,'avgrate', NEW.`avgrate`,'gpa', NEW.`gpa`,'gla', NEW.`gla`,'attnd', NEW.`attnd`,'twday', NEW.`twday`,'totalfail', NEW.`totalfail`,'meritplace', NEW.`meritplace`,'totalgp', NEW.`totalgp`,'totalsubject', NEW.`totalsubject`,'gender', NEW.`gender`,'prevexam', NEW.`prevexam`,'thisexam', NEW.`thisexam`,'allsubject', NEW.`allsubject`,'allfourth', NEW.`allfourth`,'failsub', NEW.`failsub`,'modifieddate', NEW.`modifieddate`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER tabulatingsheetex_after_update
    AFTER UPDATE ON `tabulatingsheetex`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, new_data, changed_by)
        VALUES(
            'tabulatingsheetex',
            'UPDATE',
            NEW.`id`,
            JSON_OBJECT('id', OLD.`id`,'tsheet_id', OLD.`tsheet_id`,'sessionyear', OLD.`sessionyear`,'sccode', OLD.`sccode`,'slot', OLD.`slot`,'exam', OLD.`exam`,'classname', OLD.`classname`,'sectionname', OLD.`sectionname`,'stid', OLD.`stid`,'rollno', OLD.`rollno`,'sub_1', OLD.`sub_1`,'sub_2', OLD.`sub_2`,'sub_3', OLD.`sub_3`,'sub_4', OLD.`sub_4`,'sub_5', OLD.`sub_5`,'sub_6', OLD.`sub_6`,'sub_7', OLD.`sub_7`,'sub_8', OLD.`sub_8`,'sub_9', OLD.`sub_9`,'sub_10', OLD.`sub_10`,'sub_code_1', OLD.`sub_code_1`,'sub_code_2', OLD.`sub_code_2`,'sub_code_3', OLD.`sub_code_3`,'sub_code_4', OLD.`sub_code_4`,'sub_code_5', OLD.`sub_code_5`,'sub_code_6', OLD.`sub_code_6`,'sub_code_7', OLD.`sub_code_7`,'sub_code_8', OLD.`sub_code_8`,'sub_code_9', OLD.`sub_code_9`,'sub_code_10', OLD.`sub_code_10`,'sub_name_1', OLD.`sub_name_1`,'sub_name_2', OLD.`sub_name_2`,'sub_name_3', OLD.`sub_name_3`,'sub_name_4', OLD.`sub_name_4`,'sub_name_5', OLD.`sub_name_5`,'sub_name_6', OLD.`sub_name_6`,'sub_name_7', OLD.`sub_name_7`,'sub_name_8', OLD.`sub_name_8`,'sub_name_9', OLD.`sub_name_9`,'sub_name_10', OLD.`sub_name_10`,'sub_fm_1', OLD.`sub_fm_1`,'sub_fm_2', OLD.`sub_fm_2`,'sub_fm_3', OLD.`sub_fm_3`,'sub_fm_4', OLD.`sub_fm_4`,'sub_fm_5', OLD.`sub_fm_5`,'sub_fm_6', OLD.`sub_fm_6`,'sub_fm_7', OLD.`sub_fm_7`,'sub_fm_8', OLD.`sub_fm_8`,'sub_fm_9', OLD.`sub_fm_9`,'sub_fm_10', OLD.`sub_fm_10`,'sub_1_sub', OLD.`sub_1_sub`,'sub_1_obj', OLD.`sub_1_obj`,'sub_1_pra', OLD.`sub_1_pra`,'sub_1_ca', OLD.`sub_1_ca`,'sub_1_total', OLD.`sub_1_total`,'sub_1_gp', OLD.`sub_1_gp`,'sub_1_gl', OLD.`sub_1_gl`,'sub_1_ct', OLD.`sub_1_ct`,'sub_1_mt', OLD.`sub_1_mt`,'sub_2_sub', OLD.`sub_2_sub`,'sub_2_obj', OLD.`sub_2_obj`,'sub_2_pra', OLD.`sub_2_pra`,'sub_2_ca', OLD.`sub_2_ca`,'sub_2_total', OLD.`sub_2_total`,'sub_2_gp', OLD.`sub_2_gp`,'sub_2_gl', OLD.`sub_2_gl`,'sub_3_sub', OLD.`sub_3_sub`,'sub_3_obj', OLD.`sub_3_obj`,'sub_3_pra', OLD.`sub_3_pra`,'sub_3_ca', OLD.`sub_3_ca`,'sub_3_total', OLD.`sub_3_total`,'sub_3_gp', OLD.`sub_3_gp`,'sub_3_gl', OLD.`sub_3_gl`,'sub_4_sub', OLD.`sub_4_sub`,'sub_4_obj', OLD.`sub_4_obj`,'sub_4_pra', OLD.`sub_4_pra`,'sub_4_ca', OLD.`sub_4_ca`,'sub_4_total', OLD.`sub_4_total`,'sub_4_gp', OLD.`sub_4_gp`,'sub_4_gl', OLD.`sub_4_gl`,'sub_5_sub', OLD.`sub_5_sub`,'sub_5_obj', OLD.`sub_5_obj`,'sub_5_pra', OLD.`sub_5_pra`,'sub_5_ca', OLD.`sub_5_ca`,'sub_5_total', OLD.`sub_5_total`,'sub_5_gp', OLD.`sub_5_gp`,'sub_5_gl', OLD.`sub_5_gl`,'sub_6_sub', OLD.`sub_6_sub`,'sub_6_obj', OLD.`sub_6_obj`,'sub_6_pra', OLD.`sub_6_pra`,'sub_6_ca', OLD.`sub_6_ca`,'sub_6_total', OLD.`sub_6_total`,'sub_6_gp', OLD.`sub_6_gp`,'sub_6_gl', OLD.`sub_6_gl`,'sub_7_sub', OLD.`sub_7_sub`,'sub_7_obj', OLD.`sub_7_obj`,'sub_7_pra', OLD.`sub_7_pra`,'sub_7_ca', OLD.`sub_7_ca`,'sub_7_total', OLD.`sub_7_total`,'sub_7_gp', OLD.`sub_7_gp`,'sub_7_gl', OLD.`sub_7_gl`,'sub_8_sub', OLD.`sub_8_sub`,'sub_8_obj', OLD.`sub_8_obj`,'sub_8_pra', OLD.`sub_8_pra`,'sub_8_ca', OLD.`sub_8_ca`,'sub_8_total', OLD.`sub_8_total`,'sub_8_gp', OLD.`sub_8_gp`,'sub_8_gl', OLD.`sub_8_gl`,'sub_9_sub', OLD.`sub_9_sub`,'sub_9_obj', OLD.`sub_9_obj`,'sub_9_pra', OLD.`sub_9_pra`,'sub_9_ca', OLD.`sub_9_ca`,'sub_9_total', OLD.`sub_9_total`,'sub_9_gp', OLD.`sub_9_gp`,'sub_9_gl', OLD.`sub_9_gl`,'sub_10_sub', OLD.`sub_10_sub`,'sub_10_obj', OLD.`sub_10_obj`,'sub_10_pra', OLD.`sub_10_pra`,'sub_10_ca', OLD.`sub_10_ca`,'sub_10_total', OLD.`sub_10_total`,'sub_10_gp', OLD.`sub_10_gp`,'sub_10_gl', OLD.`sub_10_gl`,'fourth_subj', OLD.`fourth_subj`,'fourth_obj', OLD.`fourth_obj`,'fourth_pra', OLD.`fourth_pra`,'fourth_ca', OLD.`fourth_ca`,'fourth_total', OLD.`fourth_total`,'fourth_gp', OLD.`fourth_gp`,'fourth_gl', OLD.`fourth_gl`,'add_gp', OLD.`add_gp`,'totalmarks', OLD.`totalmarks`,'full_marks', OLD.`full_marks`,'avgrate', OLD.`avgrate`,'gpa', OLD.`gpa`,'gla', OLD.`gla`,'attnd', OLD.`attnd`,'twday', OLD.`twday`,'totalfail', OLD.`totalfail`,'meritplace', OLD.`meritplace`,'totalgp', OLD.`totalgp`,'totalsubject', OLD.`totalsubject`,'gender', OLD.`gender`,'prevexam', OLD.`prevexam`,'thisexam', OLD.`thisexam`,'allsubject', OLD.`allsubject`,'allfourth', OLD.`allfourth`,'failsub', OLD.`failsub`,'modifieddate', OLD.`modifieddate`),
            JSON_OBJECT('id', NEW.`id`,'tsheet_id', NEW.`tsheet_id`,'sessionyear', NEW.`sessionyear`,'sccode', NEW.`sccode`,'slot', NEW.`slot`,'exam', NEW.`exam`,'classname', NEW.`classname`,'sectionname', NEW.`sectionname`,'stid', NEW.`stid`,'rollno', NEW.`rollno`,'sub_1', NEW.`sub_1`,'sub_2', NEW.`sub_2`,'sub_3', NEW.`sub_3`,'sub_4', NEW.`sub_4`,'sub_5', NEW.`sub_5`,'sub_6', NEW.`sub_6`,'sub_7', NEW.`sub_7`,'sub_8', NEW.`sub_8`,'sub_9', NEW.`sub_9`,'sub_10', NEW.`sub_10`,'sub_code_1', NEW.`sub_code_1`,'sub_code_2', NEW.`sub_code_2`,'sub_code_3', NEW.`sub_code_3`,'sub_code_4', NEW.`sub_code_4`,'sub_code_5', NEW.`sub_code_5`,'sub_code_6', NEW.`sub_code_6`,'sub_code_7', NEW.`sub_code_7`,'sub_code_8', NEW.`sub_code_8`,'sub_code_9', NEW.`sub_code_9`,'sub_code_10', NEW.`sub_code_10`,'sub_name_1', NEW.`sub_name_1`,'sub_name_2', NEW.`sub_name_2`,'sub_name_3', NEW.`sub_name_3`,'sub_name_4', NEW.`sub_name_4`,'sub_name_5', NEW.`sub_name_5`,'sub_name_6', NEW.`sub_name_6`,'sub_name_7', NEW.`sub_name_7`,'sub_name_8', NEW.`sub_name_8`,'sub_name_9', NEW.`sub_name_9`,'sub_name_10', NEW.`sub_name_10`,'sub_fm_1', NEW.`sub_fm_1`,'sub_fm_2', NEW.`sub_fm_2`,'sub_fm_3', NEW.`sub_fm_3`,'sub_fm_4', NEW.`sub_fm_4`,'sub_fm_5', NEW.`sub_fm_5`,'sub_fm_6', NEW.`sub_fm_6`,'sub_fm_7', NEW.`sub_fm_7`,'sub_fm_8', NEW.`sub_fm_8`,'sub_fm_9', NEW.`sub_fm_9`,'sub_fm_10', NEW.`sub_fm_10`,'sub_1_sub', NEW.`sub_1_sub`,'sub_1_obj', NEW.`sub_1_obj`,'sub_1_pra', NEW.`sub_1_pra`,'sub_1_ca', NEW.`sub_1_ca`,'sub_1_total', NEW.`sub_1_total`,'sub_1_gp', NEW.`sub_1_gp`,'sub_1_gl', NEW.`sub_1_gl`,'sub_1_ct', NEW.`sub_1_ct`,'sub_1_mt', NEW.`sub_1_mt`,'sub_2_sub', NEW.`sub_2_sub`,'sub_2_obj', NEW.`sub_2_obj`,'sub_2_pra', NEW.`sub_2_pra`,'sub_2_ca', NEW.`sub_2_ca`,'sub_2_total', NEW.`sub_2_total`,'sub_2_gp', NEW.`sub_2_gp`,'sub_2_gl', NEW.`sub_2_gl`,'sub_3_sub', NEW.`sub_3_sub`,'sub_3_obj', NEW.`sub_3_obj`,'sub_3_pra', NEW.`sub_3_pra`,'sub_3_ca', NEW.`sub_3_ca`,'sub_3_total', NEW.`sub_3_total`,'sub_3_gp', NEW.`sub_3_gp`,'sub_3_gl', NEW.`sub_3_gl`,'sub_4_sub', NEW.`sub_4_sub`,'sub_4_obj', NEW.`sub_4_obj`,'sub_4_pra', NEW.`sub_4_pra`,'sub_4_ca', NEW.`sub_4_ca`,'sub_4_total', NEW.`sub_4_total`,'sub_4_gp', NEW.`sub_4_gp`,'sub_4_gl', NEW.`sub_4_gl`,'sub_5_sub', NEW.`sub_5_sub`,'sub_5_obj', NEW.`sub_5_obj`,'sub_5_pra', NEW.`sub_5_pra`,'sub_5_ca', NEW.`sub_5_ca`,'sub_5_total', NEW.`sub_5_total`,'sub_5_gp', NEW.`sub_5_gp`,'sub_5_gl', NEW.`sub_5_gl`,'sub_6_sub', NEW.`sub_6_sub`,'sub_6_obj', NEW.`sub_6_obj`,'sub_6_pra', NEW.`sub_6_pra`,'sub_6_ca', NEW.`sub_6_ca`,'sub_6_total', NEW.`sub_6_total`,'sub_6_gp', NEW.`sub_6_gp`,'sub_6_gl', NEW.`sub_6_gl`,'sub_7_sub', NEW.`sub_7_sub`,'sub_7_obj', NEW.`sub_7_obj`,'sub_7_pra', NEW.`sub_7_pra`,'sub_7_ca', NEW.`sub_7_ca`,'sub_7_total', NEW.`sub_7_total`,'sub_7_gp', NEW.`sub_7_gp`,'sub_7_gl', NEW.`sub_7_gl`,'sub_8_sub', NEW.`sub_8_sub`,'sub_8_obj', NEW.`sub_8_obj`,'sub_8_pra', NEW.`sub_8_pra`,'sub_8_ca', NEW.`sub_8_ca`,'sub_8_total', NEW.`sub_8_total`,'sub_8_gp', NEW.`sub_8_gp`,'sub_8_gl', NEW.`sub_8_gl`,'sub_9_sub', NEW.`sub_9_sub`,'sub_9_obj', NEW.`sub_9_obj`,'sub_9_pra', NEW.`sub_9_pra`,'sub_9_ca', NEW.`sub_9_ca`,'sub_9_total', NEW.`sub_9_total`,'sub_9_gp', NEW.`sub_9_gp`,'sub_9_gl', NEW.`sub_9_gl`,'sub_10_sub', NEW.`sub_10_sub`,'sub_10_obj', NEW.`sub_10_obj`,'sub_10_pra', NEW.`sub_10_pra`,'sub_10_ca', NEW.`sub_10_ca`,'sub_10_total', NEW.`sub_10_total`,'sub_10_gp', NEW.`sub_10_gp`,'sub_10_gl', NEW.`sub_10_gl`,'fourth_subj', NEW.`fourth_subj`,'fourth_obj', NEW.`fourth_obj`,'fourth_pra', NEW.`fourth_pra`,'fourth_ca', NEW.`fourth_ca`,'fourth_total', NEW.`fourth_total`,'fourth_gp', NEW.`fourth_gp`,'fourth_gl', NEW.`fourth_gl`,'add_gp', NEW.`add_gp`,'totalmarks', NEW.`totalmarks`,'full_marks', NEW.`full_marks`,'avgrate', NEW.`avgrate`,'gpa', NEW.`gpa`,'gla', NEW.`gla`,'attnd', NEW.`attnd`,'twday', NEW.`twday`,'totalfail', NEW.`totalfail`,'meritplace', NEW.`meritplace`,'totalgp', NEW.`totalgp`,'totalsubject', NEW.`totalsubject`,'gender', NEW.`gender`,'prevexam', NEW.`prevexam`,'thisexam', NEW.`thisexam`,'allsubject', NEW.`allsubject`,'allfourth', NEW.`allfourth`,'failsub', NEW.`failsub`,'modifieddate', NEW.`modifieddate`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER tabulatingsheetex_after_delete
    AFTER DELETE ON `tabulatingsheetex`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, changed_by)
        VALUES(
            'tabulatingsheetex',
            'DELETE',
            OLD.`id`,
            JSON_OBJECT('id', OLD.`id`,'tsheet_id', OLD.`tsheet_id`,'sessionyear', OLD.`sessionyear`,'sccode', OLD.`sccode`,'slot', OLD.`slot`,'exam', OLD.`exam`,'classname', OLD.`classname`,'sectionname', OLD.`sectionname`,'stid', OLD.`stid`,'rollno', OLD.`rollno`,'sub_1', OLD.`sub_1`,'sub_2', OLD.`sub_2`,'sub_3', OLD.`sub_3`,'sub_4', OLD.`sub_4`,'sub_5', OLD.`sub_5`,'sub_6', OLD.`sub_6`,'sub_7', OLD.`sub_7`,'sub_8', OLD.`sub_8`,'sub_9', OLD.`sub_9`,'sub_10', OLD.`sub_10`,'sub_code_1', OLD.`sub_code_1`,'sub_code_2', OLD.`sub_code_2`,'sub_code_3', OLD.`sub_code_3`,'sub_code_4', OLD.`sub_code_4`,'sub_code_5', OLD.`sub_code_5`,'sub_code_6', OLD.`sub_code_6`,'sub_code_7', OLD.`sub_code_7`,'sub_code_8', OLD.`sub_code_8`,'sub_code_9', OLD.`sub_code_9`,'sub_code_10', OLD.`sub_code_10`,'sub_name_1', OLD.`sub_name_1`,'sub_name_2', OLD.`sub_name_2`,'sub_name_3', OLD.`sub_name_3`,'sub_name_4', OLD.`sub_name_4`,'sub_name_5', OLD.`sub_name_5`,'sub_name_6', OLD.`sub_name_6`,'sub_name_7', OLD.`sub_name_7`,'sub_name_8', OLD.`sub_name_8`,'sub_name_9', OLD.`sub_name_9`,'sub_name_10', OLD.`sub_name_10`,'sub_fm_1', OLD.`sub_fm_1`,'sub_fm_2', OLD.`sub_fm_2`,'sub_fm_3', OLD.`sub_fm_3`,'sub_fm_4', OLD.`sub_fm_4`,'sub_fm_5', OLD.`sub_fm_5`,'sub_fm_6', OLD.`sub_fm_6`,'sub_fm_7', OLD.`sub_fm_7`,'sub_fm_8', OLD.`sub_fm_8`,'sub_fm_9', OLD.`sub_fm_9`,'sub_fm_10', OLD.`sub_fm_10`,'sub_1_sub', OLD.`sub_1_sub`,'sub_1_obj', OLD.`sub_1_obj`,'sub_1_pra', OLD.`sub_1_pra`,'sub_1_ca', OLD.`sub_1_ca`,'sub_1_total', OLD.`sub_1_total`,'sub_1_gp', OLD.`sub_1_gp`,'sub_1_gl', OLD.`sub_1_gl`,'sub_1_ct', OLD.`sub_1_ct`,'sub_1_mt', OLD.`sub_1_mt`,'sub_2_sub', OLD.`sub_2_sub`,'sub_2_obj', OLD.`sub_2_obj`,'sub_2_pra', OLD.`sub_2_pra`,'sub_2_ca', OLD.`sub_2_ca`,'sub_2_total', OLD.`sub_2_total`,'sub_2_gp', OLD.`sub_2_gp`,'sub_2_gl', OLD.`sub_2_gl`,'sub_3_sub', OLD.`sub_3_sub`,'sub_3_obj', OLD.`sub_3_obj`,'sub_3_pra', OLD.`sub_3_pra`,'sub_3_ca', OLD.`sub_3_ca`,'sub_3_total', OLD.`sub_3_total`,'sub_3_gp', OLD.`sub_3_gp`,'sub_3_gl', OLD.`sub_3_gl`,'sub_4_sub', OLD.`sub_4_sub`,'sub_4_obj', OLD.`sub_4_obj`,'sub_4_pra', OLD.`sub_4_pra`,'sub_4_ca', OLD.`sub_4_ca`,'sub_4_total', OLD.`sub_4_total`,'sub_4_gp', OLD.`sub_4_gp`,'sub_4_gl', OLD.`sub_4_gl`,'sub_5_sub', OLD.`sub_5_sub`,'sub_5_obj', OLD.`sub_5_obj`,'sub_5_pra', OLD.`sub_5_pra`,'sub_5_ca', OLD.`sub_5_ca`,'sub_5_total', OLD.`sub_5_total`,'sub_5_gp', OLD.`sub_5_gp`,'sub_5_gl', OLD.`sub_5_gl`,'sub_6_sub', OLD.`sub_6_sub`,'sub_6_obj', OLD.`sub_6_obj`,'sub_6_pra', OLD.`sub_6_pra`,'sub_6_ca', OLD.`sub_6_ca`,'sub_6_total', OLD.`sub_6_total`,'sub_6_gp', OLD.`sub_6_gp`,'sub_6_gl', OLD.`sub_6_gl`,'sub_7_sub', OLD.`sub_7_sub`,'sub_7_obj', OLD.`sub_7_obj`,'sub_7_pra', OLD.`sub_7_pra`,'sub_7_ca', OLD.`sub_7_ca`,'sub_7_total', OLD.`sub_7_total`,'sub_7_gp', OLD.`sub_7_gp`,'sub_7_gl', OLD.`sub_7_gl`,'sub_8_sub', OLD.`sub_8_sub`,'sub_8_obj', OLD.`sub_8_obj`,'sub_8_pra', OLD.`sub_8_pra`,'sub_8_ca', OLD.`sub_8_ca`,'sub_8_total', OLD.`sub_8_total`,'sub_8_gp', OLD.`sub_8_gp`,'sub_8_gl', OLD.`sub_8_gl`,'sub_9_sub', OLD.`sub_9_sub`,'sub_9_obj', OLD.`sub_9_obj`,'sub_9_pra', OLD.`sub_9_pra`,'sub_9_ca', OLD.`sub_9_ca`,'sub_9_total', OLD.`sub_9_total`,'sub_9_gp', OLD.`sub_9_gp`,'sub_9_gl', OLD.`sub_9_gl`,'sub_10_sub', OLD.`sub_10_sub`,'sub_10_obj', OLD.`sub_10_obj`,'sub_10_pra', OLD.`sub_10_pra`,'sub_10_ca', OLD.`sub_10_ca`,'sub_10_total', OLD.`sub_10_total`,'sub_10_gp', OLD.`sub_10_gp`,'sub_10_gl', OLD.`sub_10_gl`,'fourth_subj', OLD.`fourth_subj`,'fourth_obj', OLD.`fourth_obj`,'fourth_pra', OLD.`fourth_pra`,'fourth_ca', OLD.`fourth_ca`,'fourth_total', OLD.`fourth_total`,'fourth_gp', OLD.`fourth_gp`,'fourth_gl', OLD.`fourth_gl`,'add_gp', OLD.`add_gp`,'totalmarks', OLD.`totalmarks`,'full_marks', OLD.`full_marks`,'avgrate', OLD.`avgrate`,'gpa', OLD.`gpa`,'gla', OLD.`gla`,'attnd', OLD.`attnd`,'twday', OLD.`twday`,'totalfail', OLD.`totalfail`,'meritplace', OLD.`meritplace`,'totalgp', OLD.`totalgp`,'totalsubject', OLD.`totalsubject`,'gender', OLD.`gender`,'prevexam', OLD.`prevexam`,'thisexam', OLD.`thisexam`,'allsubject', OLD.`allsubject`,'allfourth', OLD.`allfourth`,'failsub', OLD.`failsub`,'modifieddate', OLD.`modifieddate`),
            COALESCE(@current_user,'system')
        );
    END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `tabulatingsheetpibi`
--

DROP TABLE IF EXISTS `tabulatingsheetpibi`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tabulatingsheetpibi` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sccode` int(11) DEFAULT NULL,
  `sessionyear` int(11) DEFAULT NULL,
  `exam` varchar(50) DEFAULT NULL,
  `classname` varchar(20) DEFAULT NULL,
  `sectionname` varchar(50) DEFAULT NULL,
  `stid` bigint(20) DEFAULT NULL,
  `roll` int(11) DEFAULT NULL,
  `stnameeng` varchar(200) DEFAULT NULL,
  `stnameben` varchar(200) DEFAULT NULL,
  `pi1` int(11) NOT NULL DEFAULT 0,
  `pi2` int(11) NOT NULL DEFAULT 0,
  `pi3` int(11) NOT NULL DEFAULT 0,
  `pi4` int(11) NOT NULL DEFAULT 0,
  `pi5` int(11) NOT NULL DEFAULT 0,
  `pi6` int(11) NOT NULL DEFAULT 0,
  `pi7` int(11) NOT NULL DEFAULT 0,
  `pi8` int(11) NOT NULL DEFAULT 0,
  `pi9` int(11) NOT NULL DEFAULT 0,
  `pi10` int(11) NOT NULL DEFAULT 0,
  `pi11` int(11) NOT NULL DEFAULT 0,
  `pi12` int(11) NOT NULL DEFAULT 0,
  `pi13` int(11) NOT NULL DEFAULT 0,
  `pi14` int(11) NOT NULL DEFAULT 0,
  `pi15` int(11) NOT NULL DEFAULT 0,
  `pi16` int(11) NOT NULL DEFAULT 0,
  `pi17` int(11) NOT NULL DEFAULT 0,
  `pi18` int(11) NOT NULL DEFAULT 0,
  `pi19` int(11) NOT NULL DEFAULT 0,
  `pi20` int(11) NOT NULL DEFAULT 0,
  `pi21` int(11) NOT NULL DEFAULT 0,
  `pi22` int(11) NOT NULL DEFAULT 0,
  `pi23` int(11) NOT NULL DEFAULT 0,
  `pi24` int(11) NOT NULL DEFAULT 0,
  `pi25` int(11) NOT NULL DEFAULT 0,
  `pi26` int(11) NOT NULL DEFAULT 0,
  `pi27` int(11) NOT NULL DEFAULT 0,
  `pi28` int(11) NOT NULL DEFAULT 0,
  `pi29` int(11) NOT NULL DEFAULT 0,
  `pi30` int(11) NOT NULL DEFAULT 0,
  `pi31` int(11) NOT NULL DEFAULT 0,
  `pi32` int(11) NOT NULL DEFAULT 0,
  `pi33` int(11) NOT NULL DEFAULT 0,
  `pi34` int(11) NOT NULL DEFAULT 0,
  `pi35` int(11) NOT NULL DEFAULT 0,
  `pi36` int(11) NOT NULL DEFAULT 0,
  `pi37` int(11) NOT NULL DEFAULT 0,
  `pi38` int(11) NOT NULL DEFAULT 0,
  `pi39` int(11) NOT NULL DEFAULT 0,
  `pi40` int(11) NOT NULL DEFAULT 0,
  `pi41` int(11) NOT NULL DEFAULT 0,
  `pi42` int(11) NOT NULL DEFAULT 0,
  `pi43` int(11) NOT NULL DEFAULT 0,
  `pi44` int(11) NOT NULL DEFAULT 0,
  `pi45` int(11) NOT NULL DEFAULT 0,
  `pi46` int(11) NOT NULL DEFAULT 0,
  `pi47` int(11) NOT NULL DEFAULT 0,
  `pi48` int(11) NOT NULL DEFAULT 0,
  `pi49` int(11) NOT NULL DEFAULT 0,
  `pi50` int(11) NOT NULL DEFAULT 0,
  `pi51` int(11) NOT NULL DEFAULT 0,
  `pi52` int(11) NOT NULL DEFAULT 0,
  `pi53` int(11) NOT NULL DEFAULT 0,
  `pi54` int(11) NOT NULL DEFAULT 0,
  `pi55` int(11) NOT NULL DEFAULT 0,
  `pi56` int(11) NOT NULL DEFAULT 0,
  `pi57` int(11) NOT NULL DEFAULT 0,
  `pi58` int(11) NOT NULL DEFAULT 0,
  `pi59` int(11) NOT NULL DEFAULT 0,
  `pi60` int(11) NOT NULL DEFAULT 0,
  `pi61` int(11) NOT NULL DEFAULT 0,
  `pi62` int(11) NOT NULL DEFAULT 0,
  `pi63` int(11) NOT NULL DEFAULT 0,
  `pi64` int(11) NOT NULL DEFAULT 0,
  `pi65` int(11) NOT NULL DEFAULT 0,
  `pi66` int(11) NOT NULL DEFAULT 0,
  `pi67` int(11) NOT NULL DEFAULT 0,
  `pi68` int(11) NOT NULL DEFAULT 0,
  `pi69` int(11) DEFAULT 0,
  `pi70` int(11) NOT NULL DEFAULT 0,
  `pi71` int(11) NOT NULL DEFAULT 0,
  `pi72` int(11) NOT NULL DEFAULT 0,
  `pi73` int(11) NOT NULL DEFAULT 0,
  `pi74` int(11) NOT NULL DEFAULT 0,
  `pi75` int(11) NOT NULL DEFAULT 0,
  `pi76` int(11) NOT NULL DEFAULT 0,
  `pi77` int(11) NOT NULL DEFAULT 0,
  `pi78` int(11) NOT NULL DEFAULT 0,
  `pi79` int(11) NOT NULL DEFAULT 0,
  `pi80` int(11) NOT NULL DEFAULT 0,
  `pi81` int(11) NOT NULL DEFAULT 0,
  `pi82` int(11) NOT NULL DEFAULT 0,
  `pi83` int(11) NOT NULL DEFAULT 0,
  `pi84` int(11) NOT NULL DEFAULT 0,
  `pi85` int(11) NOT NULL DEFAULT 0,
  `pi86` int(11) NOT NULL DEFAULT 0,
  `pi87` int(11) NOT NULL DEFAULT 0,
  `pi88` int(11) NOT NULL DEFAULT 0,
  `pi89` int(11) NOT NULL DEFAULT 0,
  `pi90` int(11) NOT NULL DEFAULT 0,
  `pi91` int(11) NOT NULL DEFAULT 0,
  `pi92` int(11) NOT NULL DEFAULT 0,
  `pi93` int(11) NOT NULL DEFAULT 0,
  `pi94` int(11) NOT NULL DEFAULT 0,
  `pi95` int(11) NOT NULL DEFAULT 0,
  `pi96` int(11) NOT NULL DEFAULT 0,
  `pi97` int(11) NOT NULL DEFAULT 0,
  `pi98` int(11) NOT NULL DEFAULT 0,
  `pi99` int(11) NOT NULL DEFAULT 0,
  `pi100` int(11) NOT NULL DEFAULT 0,
  `pi101` int(11) NOT NULL DEFAULT 0,
  `pi102` int(11) DEFAULT 0,
  `pi103` int(11) NOT NULL DEFAULT 0,
  `pi104` int(11) NOT NULL DEFAULT 0,
  `pi105` int(11) NOT NULL DEFAULT 0,
  `pi106` int(11) NOT NULL DEFAULT 0,
  `pi107` int(11) NOT NULL DEFAULT 0,
  `pi108` int(11) NOT NULL DEFAULT 0,
  `pi109` int(11) NOT NULL DEFAULT 0,
  `pi110` int(11) NOT NULL DEFAULT 0,
  `pi111` int(11) NOT NULL DEFAULT 0,
  `pi112` int(11) NOT NULL DEFAULT 0,
  `pi113` int(11) NOT NULL DEFAULT 0,
  `pi114` int(11) NOT NULL DEFAULT 0,
  `pi115` int(11) NOT NULL DEFAULT 0,
  `pi116` int(11) NOT NULL DEFAULT 0,
  `pi117` int(11) NOT NULL DEFAULT 0,
  `pi118` int(11) NOT NULL DEFAULT 0,
  `pi119` int(11) NOT NULL DEFAULT 0,
  `pi120` int(11) NOT NULL DEFAULT 0,
  `pi121` int(11) NOT NULL DEFAULT 0,
  `pi122` int(11) NOT NULL DEFAULT 0,
  `pi123` int(11) NOT NULL DEFAULT 0,
  `pi124` int(11) NOT NULL DEFAULT 0,
  `pi125` int(11) NOT NULL DEFAULT 0,
  `pi126` int(11) NOT NULL DEFAULT 0,
  `pi127` int(11) NOT NULL DEFAULT 0,
  `pi128` int(11) NOT NULL DEFAULT 0,
  `pi129` int(11) NOT NULL DEFAULT 0,
  `pi130` int(11) NOT NULL DEFAULT 0,
  `pi131` int(11) NOT NULL DEFAULT 0,
  `pi132` int(11) NOT NULL DEFAULT 0,
  `pi133` int(11) NOT NULL DEFAULT 0,
  `pi134` int(11) NOT NULL DEFAULT 0,
  `pi135` int(11) NOT NULL DEFAULT 0,
  `pi136` int(11) NOT NULL DEFAULT 0,
  `pi137` int(11) NOT NULL DEFAULT 0,
  `pi138` int(11) NOT NULL DEFAULT 0,
  `pi139` int(11) NOT NULL DEFAULT 0,
  `pi140` int(11) NOT NULL DEFAULT 0,
  `pi141` int(11) NOT NULL DEFAULT 0,
  `pi142` int(11) NOT NULL DEFAULT 0,
  `pi143` int(11) NOT NULL DEFAULT 0,
  `pi144` int(11) NOT NULL DEFAULT 0,
  `pi145` int(11) NOT NULL DEFAULT 0,
  `pi146` int(11) NOT NULL DEFAULT 0,
  `pi147` int(11) NOT NULL DEFAULT 0,
  `pi148` int(11) NOT NULL DEFAULT 0,
  `pi149` int(11) NOT NULL DEFAULT 0,
  `pi150` int(11) NOT NULL DEFAULT 0,
  `h1` int(11) DEFAULT 0,
  `h2` int(11) DEFAULT 0,
  `h3` int(11) DEFAULT 0,
  `h4` int(11) DEFAULT 0,
  `h5` int(11) DEFAULT 0,
  `h6` int(11) DEFAULT 0,
  `h7` int(11) DEFAULT 0,
  `h8` int(11) DEFAULT 0,
  `h9` int(11) DEFAULT 0,
  `h10` int(11) DEFAULT 0,
  `h11` int(11) DEFAULT 0,
  `h12` int(11) DEFAULT 0,
  `h13` int(11) DEFAULT 0,
  `h14` int(11) DEFAULT 0,
  `h15` int(11) DEFAULT 0,
  `h16` int(11) DEFAULT 0,
  `h17` int(11) DEFAULT 0,
  `h18` int(11) DEFAULT 0,
  `h19` int(11) DEFAULT 0,
  `h20` int(11) DEFAULT 0,
  `h21` int(11) DEFAULT 0,
  `h22` int(11) DEFAULT 0,
  `h23` int(11) DEFAULT 0,
  `h24` int(11) DEFAULT 0,
  `h25` int(11) DEFAULT 0,
  `h26` int(11) DEFAULT 0,
  `h27` int(11) DEFAULT 0,
  `h28` int(11) DEFAULT 0,
  `h29` int(11) DEFAULT 0,
  `h30` int(11) DEFAULT 0,
  `h31` int(11) DEFAULT 0,
  `h32` int(11) DEFAULT 0,
  `h33` int(11) DEFAULT 0,
  `h34` int(11) DEFAULT 0,
  `h35` int(11) DEFAULT 0,
  `h36` int(11) DEFAULT 0,
  `h37` int(11) DEFAULT 0,
  `h38` int(11) DEFAULT 0,
  `h39` int(11) DEFAULT 0,
  `h40` int(11) DEFAULT 0,
  `h41` int(11) DEFAULT 0,
  `h42` int(11) DEFAULT 0,
  `h43` int(11) DEFAULT 0,
  `h44` int(11) DEFAULT 0,
  `h45` int(11) DEFAULT 0,
  `h46` int(11) DEFAULT 0,
  `h47` int(11) DEFAULT 0,
  `h48` int(11) DEFAULT 0,
  `h49` int(11) DEFAULT 0,
  `h50` int(11) DEFAULT 0,
  `l1` int(11) DEFAULT 0,
  `l2` int(11) DEFAULT 0,
  `l3` int(11) DEFAULT 0,
  `l4` int(11) DEFAULT 0,
  `l5` int(11) DEFAULT 0,
  `l6` int(11) DEFAULT 0,
  `l7` int(11) DEFAULT 0,
  `l8` int(11) DEFAULT 0,
  `l9` int(11) DEFAULT 0,
  `l10` int(11) DEFAULT 0,
  `l11` int(11) DEFAULT 0,
  `l12` int(11) DEFAULT 0,
  `l13` int(11) DEFAULT 0,
  `l14` int(11) DEFAULT 0,
  `l15` int(11) DEFAULT 0,
  `l16` int(11) DEFAULT 0,
  `l17` int(11) DEFAULT 0,
  `l18` int(11) DEFAULT 0,
  `l19` int(11) DEFAULT 0,
  `l20` int(11) DEFAULT 0,
  `l21` int(11) DEFAULT 0,
  `l22` int(11) DEFAULT 0,
  `l23` int(11) DEFAULT 0,
  `l24` int(11) DEFAULT 0,
  `l25` int(11) DEFAULT 0,
  `l26` int(11) DEFAULT 0,
  `l27` int(11) DEFAULT 0,
  `l28` int(11) DEFAULT 0,
  `l29` int(11) DEFAULT 0,
  `l30` int(11) DEFAULT 0,
  `l31` int(11) DEFAULT 0,
  `l32` int(11) DEFAULT 0,
  `l33` int(11) DEFAULT 0,
  `l34` int(11) DEFAULT 0,
  `l35` int(11) DEFAULT 0,
  `l36` int(11) DEFAULT 0,
  `l37` int(11) DEFAULT 0,
  `l38` int(11) DEFAULT 0,
  `l39` int(11) DEFAULT 0,
  `l40` int(11) DEFAULT 0,
  `l41` int(11) DEFAULT 0,
  `l42` int(11) DEFAULT 0,
  `l43` int(11) DEFAULT 0,
  `l44` int(11) DEFAULT 0,
  `l45` int(11) DEFAULT 0,
  `l46` int(11) DEFAULT 0,
  `l47` int(11) DEFAULT 0,
  `l48` int(11) DEFAULT 0,
  `l49` int(11) DEFAULT 0,
  `l50` int(11) DEFAULT 0,
  `rate` double NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7378 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
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
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER tabulatingsheetpibi_after_insert
    AFTER INSERT ON `tabulatingsheetpibi`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, new_data, changed_by)
        VALUES(
            'tabulatingsheetpibi',
            'INSERT',
            NEW.`id`,
            JSON_OBJECT('id', NEW.`id`,'sccode', NEW.`sccode`,'sessionyear', NEW.`sessionyear`,'exam', NEW.`exam`,'classname', NEW.`classname`,'sectionname', NEW.`sectionname`,'stid', NEW.`stid`,'roll', NEW.`roll`,'stnameeng', NEW.`stnameeng`,'stnameben', NEW.`stnameben`,'pi1', NEW.`pi1`,'pi2', NEW.`pi2`,'pi3', NEW.`pi3`,'pi4', NEW.`pi4`,'pi5', NEW.`pi5`,'pi6', NEW.`pi6`,'pi7', NEW.`pi7`,'pi8', NEW.`pi8`,'pi9', NEW.`pi9`,'pi10', NEW.`pi10`,'pi11', NEW.`pi11`,'pi12', NEW.`pi12`,'pi13', NEW.`pi13`,'pi14', NEW.`pi14`,'pi15', NEW.`pi15`,'pi16', NEW.`pi16`,'pi17', NEW.`pi17`,'pi18', NEW.`pi18`,'pi19', NEW.`pi19`,'pi20', NEW.`pi20`,'pi21', NEW.`pi21`,'pi22', NEW.`pi22`,'pi23', NEW.`pi23`,'pi24', NEW.`pi24`,'pi25', NEW.`pi25`,'pi26', NEW.`pi26`,'pi27', NEW.`pi27`,'pi28', NEW.`pi28`,'pi29', NEW.`pi29`,'pi30', NEW.`pi30`,'pi31', NEW.`pi31`,'pi32', NEW.`pi32`,'pi33', NEW.`pi33`,'pi34', NEW.`pi34`,'pi35', NEW.`pi35`,'pi36', NEW.`pi36`,'pi37', NEW.`pi37`,'pi38', NEW.`pi38`,'pi39', NEW.`pi39`,'pi40', NEW.`pi40`,'pi41', NEW.`pi41`,'pi42', NEW.`pi42`,'pi43', NEW.`pi43`,'pi44', NEW.`pi44`,'pi45', NEW.`pi45`,'pi46', NEW.`pi46`,'pi47', NEW.`pi47`,'pi48', NEW.`pi48`,'pi49', NEW.`pi49`,'pi50', NEW.`pi50`,'pi51', NEW.`pi51`,'pi52', NEW.`pi52`,'pi53', NEW.`pi53`,'pi54', NEW.`pi54`,'pi55', NEW.`pi55`,'pi56', NEW.`pi56`,'pi57', NEW.`pi57`,'pi58', NEW.`pi58`,'pi59', NEW.`pi59`,'pi60', NEW.`pi60`,'pi61', NEW.`pi61`,'pi62', NEW.`pi62`,'pi63', NEW.`pi63`,'pi64', NEW.`pi64`,'pi65', NEW.`pi65`,'pi66', NEW.`pi66`,'pi67', NEW.`pi67`,'pi68', NEW.`pi68`,'pi69', NEW.`pi69`,'pi70', NEW.`pi70`,'pi71', NEW.`pi71`,'pi72', NEW.`pi72`,'pi73', NEW.`pi73`,'pi74', NEW.`pi74`,'pi75', NEW.`pi75`,'pi76', NEW.`pi76`,'pi77', NEW.`pi77`,'pi78', NEW.`pi78`,'pi79', NEW.`pi79`,'pi80', NEW.`pi80`,'pi81', NEW.`pi81`,'pi82', NEW.`pi82`,'pi83', NEW.`pi83`,'pi84', NEW.`pi84`,'pi85', NEW.`pi85`,'pi86', NEW.`pi86`,'pi87', NEW.`pi87`,'pi88', NEW.`pi88`,'pi89', NEW.`pi89`,'pi90', NEW.`pi90`,'pi91', NEW.`pi91`,'pi92', NEW.`pi92`,'pi93', NEW.`pi93`,'pi94', NEW.`pi94`,'pi95', NEW.`pi95`,'pi96', NEW.`pi96`,'pi97', NEW.`pi97`,'pi98', NEW.`pi98`,'pi99', NEW.`pi99`,'pi100', NEW.`pi100`,'pi101', NEW.`pi101`,'pi102', NEW.`pi102`,'pi103', NEW.`pi103`,'pi104', NEW.`pi104`,'pi105', NEW.`pi105`,'pi106', NEW.`pi106`,'pi107', NEW.`pi107`,'pi108', NEW.`pi108`,'pi109', NEW.`pi109`,'pi110', NEW.`pi110`,'pi111', NEW.`pi111`,'pi112', NEW.`pi112`,'pi113', NEW.`pi113`,'pi114', NEW.`pi114`,'pi115', NEW.`pi115`,'pi116', NEW.`pi116`,'pi117', NEW.`pi117`,'pi118', NEW.`pi118`,'pi119', NEW.`pi119`,'pi120', NEW.`pi120`,'pi121', NEW.`pi121`,'pi122', NEW.`pi122`,'pi123', NEW.`pi123`,'pi124', NEW.`pi124`,'pi125', NEW.`pi125`,'pi126', NEW.`pi126`,'pi127', NEW.`pi127`,'pi128', NEW.`pi128`,'pi129', NEW.`pi129`,'pi130', NEW.`pi130`,'pi131', NEW.`pi131`,'pi132', NEW.`pi132`,'pi133', NEW.`pi133`,'pi134', NEW.`pi134`,'pi135', NEW.`pi135`,'pi136', NEW.`pi136`,'pi137', NEW.`pi137`,'pi138', NEW.`pi138`,'pi139', NEW.`pi139`,'pi140', NEW.`pi140`,'pi141', NEW.`pi141`,'pi142', NEW.`pi142`,'pi143', NEW.`pi143`,'pi144', NEW.`pi144`,'pi145', NEW.`pi145`,'pi146', NEW.`pi146`,'pi147', NEW.`pi147`,'pi148', NEW.`pi148`,'pi149', NEW.`pi149`,'pi150', NEW.`pi150`,'h1', NEW.`h1`,'h2', NEW.`h2`,'h3', NEW.`h3`,'h4', NEW.`h4`,'h5', NEW.`h5`,'h6', NEW.`h6`,'h7', NEW.`h7`,'h8', NEW.`h8`,'h9', NEW.`h9`,'h10', NEW.`h10`,'h11', NEW.`h11`,'h12', NEW.`h12`,'h13', NEW.`h13`,'h14', NEW.`h14`,'h15', NEW.`h15`,'h16', NEW.`h16`,'h17', NEW.`h17`,'h18', NEW.`h18`,'h19', NEW.`h19`,'h20', NEW.`h20`,'h21', NEW.`h21`,'h22', NEW.`h22`,'h23', NEW.`h23`,'h24', NEW.`h24`,'h25', NEW.`h25`,'h26', NEW.`h26`,'h27', NEW.`h27`,'h28', NEW.`h28`,'h29', NEW.`h29`,'h30', NEW.`h30`,'h31', NEW.`h31`,'h32', NEW.`h32`,'h33', NEW.`h33`,'h34', NEW.`h34`,'h35', NEW.`h35`,'h36', NEW.`h36`,'h37', NEW.`h37`,'h38', NEW.`h38`,'h39', NEW.`h39`,'h40', NEW.`h40`,'h41', NEW.`h41`,'h42', NEW.`h42`,'h43', NEW.`h43`,'h44', NEW.`h44`,'h45', NEW.`h45`,'h46', NEW.`h46`,'h47', NEW.`h47`,'h48', NEW.`h48`,'h49', NEW.`h49`,'h50', NEW.`h50`,'l1', NEW.`l1`,'l2', NEW.`l2`,'l3', NEW.`l3`,'l4', NEW.`l4`,'l5', NEW.`l5`,'l6', NEW.`l6`,'l7', NEW.`l7`,'l8', NEW.`l8`,'l9', NEW.`l9`,'l10', NEW.`l10`,'l11', NEW.`l11`,'l12', NEW.`l12`,'l13', NEW.`l13`,'l14', NEW.`l14`,'l15', NEW.`l15`,'l16', NEW.`l16`,'l17', NEW.`l17`,'l18', NEW.`l18`,'l19', NEW.`l19`,'l20', NEW.`l20`,'l21', NEW.`l21`,'l22', NEW.`l22`,'l23', NEW.`l23`,'l24', NEW.`l24`,'l25', NEW.`l25`,'l26', NEW.`l26`,'l27', NEW.`l27`,'l28', NEW.`l28`,'l29', NEW.`l29`,'l30', NEW.`l30`,'l31', NEW.`l31`,'l32', NEW.`l32`,'l33', NEW.`l33`,'l34', NEW.`l34`,'l35', NEW.`l35`,'l36', NEW.`l36`,'l37', NEW.`l37`,'l38', NEW.`l38`,'l39', NEW.`l39`,'l40', NEW.`l40`,'l41', NEW.`l41`,'l42', NEW.`l42`,'l43', NEW.`l43`,'l44', NEW.`l44`,'l45', NEW.`l45`,'l46', NEW.`l46`,'l47', NEW.`l47`,'l48', NEW.`l48`,'l49', NEW.`l49`,'l50', NEW.`l50`,'rate', NEW.`rate`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER tabulatingsheetpibi_after_update
    AFTER UPDATE ON `tabulatingsheetpibi`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, new_data, changed_by)
        VALUES(
            'tabulatingsheetpibi',
            'UPDATE',
            NEW.`id`,
            JSON_OBJECT('id', OLD.`id`,'sccode', OLD.`sccode`,'sessionyear', OLD.`sessionyear`,'exam', OLD.`exam`,'classname', OLD.`classname`,'sectionname', OLD.`sectionname`,'stid', OLD.`stid`,'roll', OLD.`roll`,'stnameeng', OLD.`stnameeng`,'stnameben', OLD.`stnameben`,'pi1', OLD.`pi1`,'pi2', OLD.`pi2`,'pi3', OLD.`pi3`,'pi4', OLD.`pi4`,'pi5', OLD.`pi5`,'pi6', OLD.`pi6`,'pi7', OLD.`pi7`,'pi8', OLD.`pi8`,'pi9', OLD.`pi9`,'pi10', OLD.`pi10`,'pi11', OLD.`pi11`,'pi12', OLD.`pi12`,'pi13', OLD.`pi13`,'pi14', OLD.`pi14`,'pi15', OLD.`pi15`,'pi16', OLD.`pi16`,'pi17', OLD.`pi17`,'pi18', OLD.`pi18`,'pi19', OLD.`pi19`,'pi20', OLD.`pi20`,'pi21', OLD.`pi21`,'pi22', OLD.`pi22`,'pi23', OLD.`pi23`,'pi24', OLD.`pi24`,'pi25', OLD.`pi25`,'pi26', OLD.`pi26`,'pi27', OLD.`pi27`,'pi28', OLD.`pi28`,'pi29', OLD.`pi29`,'pi30', OLD.`pi30`,'pi31', OLD.`pi31`,'pi32', OLD.`pi32`,'pi33', OLD.`pi33`,'pi34', OLD.`pi34`,'pi35', OLD.`pi35`,'pi36', OLD.`pi36`,'pi37', OLD.`pi37`,'pi38', OLD.`pi38`,'pi39', OLD.`pi39`,'pi40', OLD.`pi40`,'pi41', OLD.`pi41`,'pi42', OLD.`pi42`,'pi43', OLD.`pi43`,'pi44', OLD.`pi44`,'pi45', OLD.`pi45`,'pi46', OLD.`pi46`,'pi47', OLD.`pi47`,'pi48', OLD.`pi48`,'pi49', OLD.`pi49`,'pi50', OLD.`pi50`,'pi51', OLD.`pi51`,'pi52', OLD.`pi52`,'pi53', OLD.`pi53`,'pi54', OLD.`pi54`,'pi55', OLD.`pi55`,'pi56', OLD.`pi56`,'pi57', OLD.`pi57`,'pi58', OLD.`pi58`,'pi59', OLD.`pi59`,'pi60', OLD.`pi60`,'pi61', OLD.`pi61`,'pi62', OLD.`pi62`,'pi63', OLD.`pi63`,'pi64', OLD.`pi64`,'pi65', OLD.`pi65`,'pi66', OLD.`pi66`,'pi67', OLD.`pi67`,'pi68', OLD.`pi68`,'pi69', OLD.`pi69`,'pi70', OLD.`pi70`,'pi71', OLD.`pi71`,'pi72', OLD.`pi72`,'pi73', OLD.`pi73`,'pi74', OLD.`pi74`,'pi75', OLD.`pi75`,'pi76', OLD.`pi76`,'pi77', OLD.`pi77`,'pi78', OLD.`pi78`,'pi79', OLD.`pi79`,'pi80', OLD.`pi80`,'pi81', OLD.`pi81`,'pi82', OLD.`pi82`,'pi83', OLD.`pi83`,'pi84', OLD.`pi84`,'pi85', OLD.`pi85`,'pi86', OLD.`pi86`,'pi87', OLD.`pi87`,'pi88', OLD.`pi88`,'pi89', OLD.`pi89`,'pi90', OLD.`pi90`,'pi91', OLD.`pi91`,'pi92', OLD.`pi92`,'pi93', OLD.`pi93`,'pi94', OLD.`pi94`,'pi95', OLD.`pi95`,'pi96', OLD.`pi96`,'pi97', OLD.`pi97`,'pi98', OLD.`pi98`,'pi99', OLD.`pi99`,'pi100', OLD.`pi100`,'pi101', OLD.`pi101`,'pi102', OLD.`pi102`,'pi103', OLD.`pi103`,'pi104', OLD.`pi104`,'pi105', OLD.`pi105`,'pi106', OLD.`pi106`,'pi107', OLD.`pi107`,'pi108', OLD.`pi108`,'pi109', OLD.`pi109`,'pi110', OLD.`pi110`,'pi111', OLD.`pi111`,'pi112', OLD.`pi112`,'pi113', OLD.`pi113`,'pi114', OLD.`pi114`,'pi115', OLD.`pi115`,'pi116', OLD.`pi116`,'pi117', OLD.`pi117`,'pi118', OLD.`pi118`,'pi119', OLD.`pi119`,'pi120', OLD.`pi120`,'pi121', OLD.`pi121`,'pi122', OLD.`pi122`,'pi123', OLD.`pi123`,'pi124', OLD.`pi124`,'pi125', OLD.`pi125`,'pi126', OLD.`pi126`,'pi127', OLD.`pi127`,'pi128', OLD.`pi128`,'pi129', OLD.`pi129`,'pi130', OLD.`pi130`,'pi131', OLD.`pi131`,'pi132', OLD.`pi132`,'pi133', OLD.`pi133`,'pi134', OLD.`pi134`,'pi135', OLD.`pi135`,'pi136', OLD.`pi136`,'pi137', OLD.`pi137`,'pi138', OLD.`pi138`,'pi139', OLD.`pi139`,'pi140', OLD.`pi140`,'pi141', OLD.`pi141`,'pi142', OLD.`pi142`,'pi143', OLD.`pi143`,'pi144', OLD.`pi144`,'pi145', OLD.`pi145`,'pi146', OLD.`pi146`,'pi147', OLD.`pi147`,'pi148', OLD.`pi148`,'pi149', OLD.`pi149`,'pi150', OLD.`pi150`,'h1', OLD.`h1`,'h2', OLD.`h2`,'h3', OLD.`h3`,'h4', OLD.`h4`,'h5', OLD.`h5`,'h6', OLD.`h6`,'h7', OLD.`h7`,'h8', OLD.`h8`,'h9', OLD.`h9`,'h10', OLD.`h10`,'h11', OLD.`h11`,'h12', OLD.`h12`,'h13', OLD.`h13`,'h14', OLD.`h14`,'h15', OLD.`h15`,'h16', OLD.`h16`,'h17', OLD.`h17`,'h18', OLD.`h18`,'h19', OLD.`h19`,'h20', OLD.`h20`,'h21', OLD.`h21`,'h22', OLD.`h22`,'h23', OLD.`h23`,'h24', OLD.`h24`,'h25', OLD.`h25`,'h26', OLD.`h26`,'h27', OLD.`h27`,'h28', OLD.`h28`,'h29', OLD.`h29`,'h30', OLD.`h30`,'h31', OLD.`h31`,'h32', OLD.`h32`,'h33', OLD.`h33`,'h34', OLD.`h34`,'h35', OLD.`h35`,'h36', OLD.`h36`,'h37', OLD.`h37`,'h38', OLD.`h38`,'h39', OLD.`h39`,'h40', OLD.`h40`,'h41', OLD.`h41`,'h42', OLD.`h42`,'h43', OLD.`h43`,'h44', OLD.`h44`,'h45', OLD.`h45`,'h46', OLD.`h46`,'h47', OLD.`h47`,'h48', OLD.`h48`,'h49', OLD.`h49`,'h50', OLD.`h50`,'l1', OLD.`l1`,'l2', OLD.`l2`,'l3', OLD.`l3`,'l4', OLD.`l4`,'l5', OLD.`l5`,'l6', OLD.`l6`,'l7', OLD.`l7`,'l8', OLD.`l8`,'l9', OLD.`l9`,'l10', OLD.`l10`,'l11', OLD.`l11`,'l12', OLD.`l12`,'l13', OLD.`l13`,'l14', OLD.`l14`,'l15', OLD.`l15`,'l16', OLD.`l16`,'l17', OLD.`l17`,'l18', OLD.`l18`,'l19', OLD.`l19`,'l20', OLD.`l20`,'l21', OLD.`l21`,'l22', OLD.`l22`,'l23', OLD.`l23`,'l24', OLD.`l24`,'l25', OLD.`l25`,'l26', OLD.`l26`,'l27', OLD.`l27`,'l28', OLD.`l28`,'l29', OLD.`l29`,'l30', OLD.`l30`,'l31', OLD.`l31`,'l32', OLD.`l32`,'l33', OLD.`l33`,'l34', OLD.`l34`,'l35', OLD.`l35`,'l36', OLD.`l36`,'l37', OLD.`l37`,'l38', OLD.`l38`,'l39', OLD.`l39`,'l40', OLD.`l40`,'l41', OLD.`l41`,'l42', OLD.`l42`,'l43', OLD.`l43`,'l44', OLD.`l44`,'l45', OLD.`l45`,'l46', OLD.`l46`,'l47', OLD.`l47`,'l48', OLD.`l48`,'l49', OLD.`l49`,'l50', OLD.`l50`,'rate', OLD.`rate`),
            JSON_OBJECT('id', NEW.`id`,'sccode', NEW.`sccode`,'sessionyear', NEW.`sessionyear`,'exam', NEW.`exam`,'classname', NEW.`classname`,'sectionname', NEW.`sectionname`,'stid', NEW.`stid`,'roll', NEW.`roll`,'stnameeng', NEW.`stnameeng`,'stnameben', NEW.`stnameben`,'pi1', NEW.`pi1`,'pi2', NEW.`pi2`,'pi3', NEW.`pi3`,'pi4', NEW.`pi4`,'pi5', NEW.`pi5`,'pi6', NEW.`pi6`,'pi7', NEW.`pi7`,'pi8', NEW.`pi8`,'pi9', NEW.`pi9`,'pi10', NEW.`pi10`,'pi11', NEW.`pi11`,'pi12', NEW.`pi12`,'pi13', NEW.`pi13`,'pi14', NEW.`pi14`,'pi15', NEW.`pi15`,'pi16', NEW.`pi16`,'pi17', NEW.`pi17`,'pi18', NEW.`pi18`,'pi19', NEW.`pi19`,'pi20', NEW.`pi20`,'pi21', NEW.`pi21`,'pi22', NEW.`pi22`,'pi23', NEW.`pi23`,'pi24', NEW.`pi24`,'pi25', NEW.`pi25`,'pi26', NEW.`pi26`,'pi27', NEW.`pi27`,'pi28', NEW.`pi28`,'pi29', NEW.`pi29`,'pi30', NEW.`pi30`,'pi31', NEW.`pi31`,'pi32', NEW.`pi32`,'pi33', NEW.`pi33`,'pi34', NEW.`pi34`,'pi35', NEW.`pi35`,'pi36', NEW.`pi36`,'pi37', NEW.`pi37`,'pi38', NEW.`pi38`,'pi39', NEW.`pi39`,'pi40', NEW.`pi40`,'pi41', NEW.`pi41`,'pi42', NEW.`pi42`,'pi43', NEW.`pi43`,'pi44', NEW.`pi44`,'pi45', NEW.`pi45`,'pi46', NEW.`pi46`,'pi47', NEW.`pi47`,'pi48', NEW.`pi48`,'pi49', NEW.`pi49`,'pi50', NEW.`pi50`,'pi51', NEW.`pi51`,'pi52', NEW.`pi52`,'pi53', NEW.`pi53`,'pi54', NEW.`pi54`,'pi55', NEW.`pi55`,'pi56', NEW.`pi56`,'pi57', NEW.`pi57`,'pi58', NEW.`pi58`,'pi59', NEW.`pi59`,'pi60', NEW.`pi60`,'pi61', NEW.`pi61`,'pi62', NEW.`pi62`,'pi63', NEW.`pi63`,'pi64', NEW.`pi64`,'pi65', NEW.`pi65`,'pi66', NEW.`pi66`,'pi67', NEW.`pi67`,'pi68', NEW.`pi68`,'pi69', NEW.`pi69`,'pi70', NEW.`pi70`,'pi71', NEW.`pi71`,'pi72', NEW.`pi72`,'pi73', NEW.`pi73`,'pi74', NEW.`pi74`,'pi75', NEW.`pi75`,'pi76', NEW.`pi76`,'pi77', NEW.`pi77`,'pi78', NEW.`pi78`,'pi79', NEW.`pi79`,'pi80', NEW.`pi80`,'pi81', NEW.`pi81`,'pi82', NEW.`pi82`,'pi83', NEW.`pi83`,'pi84', NEW.`pi84`,'pi85', NEW.`pi85`,'pi86', NEW.`pi86`,'pi87', NEW.`pi87`,'pi88', NEW.`pi88`,'pi89', NEW.`pi89`,'pi90', NEW.`pi90`,'pi91', NEW.`pi91`,'pi92', NEW.`pi92`,'pi93', NEW.`pi93`,'pi94', NEW.`pi94`,'pi95', NEW.`pi95`,'pi96', NEW.`pi96`,'pi97', NEW.`pi97`,'pi98', NEW.`pi98`,'pi99', NEW.`pi99`,'pi100', NEW.`pi100`,'pi101', NEW.`pi101`,'pi102', NEW.`pi102`,'pi103', NEW.`pi103`,'pi104', NEW.`pi104`,'pi105', NEW.`pi105`,'pi106', NEW.`pi106`,'pi107', NEW.`pi107`,'pi108', NEW.`pi108`,'pi109', NEW.`pi109`,'pi110', NEW.`pi110`,'pi111', NEW.`pi111`,'pi112', NEW.`pi112`,'pi113', NEW.`pi113`,'pi114', NEW.`pi114`,'pi115', NEW.`pi115`,'pi116', NEW.`pi116`,'pi117', NEW.`pi117`,'pi118', NEW.`pi118`,'pi119', NEW.`pi119`,'pi120', NEW.`pi120`,'pi121', NEW.`pi121`,'pi122', NEW.`pi122`,'pi123', NEW.`pi123`,'pi124', NEW.`pi124`,'pi125', NEW.`pi125`,'pi126', NEW.`pi126`,'pi127', NEW.`pi127`,'pi128', NEW.`pi128`,'pi129', NEW.`pi129`,'pi130', NEW.`pi130`,'pi131', NEW.`pi131`,'pi132', NEW.`pi132`,'pi133', NEW.`pi133`,'pi134', NEW.`pi134`,'pi135', NEW.`pi135`,'pi136', NEW.`pi136`,'pi137', NEW.`pi137`,'pi138', NEW.`pi138`,'pi139', NEW.`pi139`,'pi140', NEW.`pi140`,'pi141', NEW.`pi141`,'pi142', NEW.`pi142`,'pi143', NEW.`pi143`,'pi144', NEW.`pi144`,'pi145', NEW.`pi145`,'pi146', NEW.`pi146`,'pi147', NEW.`pi147`,'pi148', NEW.`pi148`,'pi149', NEW.`pi149`,'pi150', NEW.`pi150`,'h1', NEW.`h1`,'h2', NEW.`h2`,'h3', NEW.`h3`,'h4', NEW.`h4`,'h5', NEW.`h5`,'h6', NEW.`h6`,'h7', NEW.`h7`,'h8', NEW.`h8`,'h9', NEW.`h9`,'h10', NEW.`h10`,'h11', NEW.`h11`,'h12', NEW.`h12`,'h13', NEW.`h13`,'h14', NEW.`h14`,'h15', NEW.`h15`,'h16', NEW.`h16`,'h17', NEW.`h17`,'h18', NEW.`h18`,'h19', NEW.`h19`,'h20', NEW.`h20`,'h21', NEW.`h21`,'h22', NEW.`h22`,'h23', NEW.`h23`,'h24', NEW.`h24`,'h25', NEW.`h25`,'h26', NEW.`h26`,'h27', NEW.`h27`,'h28', NEW.`h28`,'h29', NEW.`h29`,'h30', NEW.`h30`,'h31', NEW.`h31`,'h32', NEW.`h32`,'h33', NEW.`h33`,'h34', NEW.`h34`,'h35', NEW.`h35`,'h36', NEW.`h36`,'h37', NEW.`h37`,'h38', NEW.`h38`,'h39', NEW.`h39`,'h40', NEW.`h40`,'h41', NEW.`h41`,'h42', NEW.`h42`,'h43', NEW.`h43`,'h44', NEW.`h44`,'h45', NEW.`h45`,'h46', NEW.`h46`,'h47', NEW.`h47`,'h48', NEW.`h48`,'h49', NEW.`h49`,'h50', NEW.`h50`,'l1', NEW.`l1`,'l2', NEW.`l2`,'l3', NEW.`l3`,'l4', NEW.`l4`,'l5', NEW.`l5`,'l6', NEW.`l6`,'l7', NEW.`l7`,'l8', NEW.`l8`,'l9', NEW.`l9`,'l10', NEW.`l10`,'l11', NEW.`l11`,'l12', NEW.`l12`,'l13', NEW.`l13`,'l14', NEW.`l14`,'l15', NEW.`l15`,'l16', NEW.`l16`,'l17', NEW.`l17`,'l18', NEW.`l18`,'l19', NEW.`l19`,'l20', NEW.`l20`,'l21', NEW.`l21`,'l22', NEW.`l22`,'l23', NEW.`l23`,'l24', NEW.`l24`,'l25', NEW.`l25`,'l26', NEW.`l26`,'l27', NEW.`l27`,'l28', NEW.`l28`,'l29', NEW.`l29`,'l30', NEW.`l30`,'l31', NEW.`l31`,'l32', NEW.`l32`,'l33', NEW.`l33`,'l34', NEW.`l34`,'l35', NEW.`l35`,'l36', NEW.`l36`,'l37', NEW.`l37`,'l38', NEW.`l38`,'l39', NEW.`l39`,'l40', NEW.`l40`,'l41', NEW.`l41`,'l42', NEW.`l42`,'l43', NEW.`l43`,'l44', NEW.`l44`,'l45', NEW.`l45`,'l46', NEW.`l46`,'l47', NEW.`l47`,'l48', NEW.`l48`,'l49', NEW.`l49`,'l50', NEW.`l50`,'rate', NEW.`rate`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER tabulatingsheetpibi_after_delete
    AFTER DELETE ON `tabulatingsheetpibi`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, changed_by)
        VALUES(
            'tabulatingsheetpibi',
            'DELETE',
            OLD.`id`,
            JSON_OBJECT('id', OLD.`id`,'sccode', OLD.`sccode`,'sessionyear', OLD.`sessionyear`,'exam', OLD.`exam`,'classname', OLD.`classname`,'sectionname', OLD.`sectionname`,'stid', OLD.`stid`,'roll', OLD.`roll`,'stnameeng', OLD.`stnameeng`,'stnameben', OLD.`stnameben`,'pi1', OLD.`pi1`,'pi2', OLD.`pi2`,'pi3', OLD.`pi3`,'pi4', OLD.`pi4`,'pi5', OLD.`pi5`,'pi6', OLD.`pi6`,'pi7', OLD.`pi7`,'pi8', OLD.`pi8`,'pi9', OLD.`pi9`,'pi10', OLD.`pi10`,'pi11', OLD.`pi11`,'pi12', OLD.`pi12`,'pi13', OLD.`pi13`,'pi14', OLD.`pi14`,'pi15', OLD.`pi15`,'pi16', OLD.`pi16`,'pi17', OLD.`pi17`,'pi18', OLD.`pi18`,'pi19', OLD.`pi19`,'pi20', OLD.`pi20`,'pi21', OLD.`pi21`,'pi22', OLD.`pi22`,'pi23', OLD.`pi23`,'pi24', OLD.`pi24`,'pi25', OLD.`pi25`,'pi26', OLD.`pi26`,'pi27', OLD.`pi27`,'pi28', OLD.`pi28`,'pi29', OLD.`pi29`,'pi30', OLD.`pi30`,'pi31', OLD.`pi31`,'pi32', OLD.`pi32`,'pi33', OLD.`pi33`,'pi34', OLD.`pi34`,'pi35', OLD.`pi35`,'pi36', OLD.`pi36`,'pi37', OLD.`pi37`,'pi38', OLD.`pi38`,'pi39', OLD.`pi39`,'pi40', OLD.`pi40`,'pi41', OLD.`pi41`,'pi42', OLD.`pi42`,'pi43', OLD.`pi43`,'pi44', OLD.`pi44`,'pi45', OLD.`pi45`,'pi46', OLD.`pi46`,'pi47', OLD.`pi47`,'pi48', OLD.`pi48`,'pi49', OLD.`pi49`,'pi50', OLD.`pi50`,'pi51', OLD.`pi51`,'pi52', OLD.`pi52`,'pi53', OLD.`pi53`,'pi54', OLD.`pi54`,'pi55', OLD.`pi55`,'pi56', OLD.`pi56`,'pi57', OLD.`pi57`,'pi58', OLD.`pi58`,'pi59', OLD.`pi59`,'pi60', OLD.`pi60`,'pi61', OLD.`pi61`,'pi62', OLD.`pi62`,'pi63', OLD.`pi63`,'pi64', OLD.`pi64`,'pi65', OLD.`pi65`,'pi66', OLD.`pi66`,'pi67', OLD.`pi67`,'pi68', OLD.`pi68`,'pi69', OLD.`pi69`,'pi70', OLD.`pi70`,'pi71', OLD.`pi71`,'pi72', OLD.`pi72`,'pi73', OLD.`pi73`,'pi74', OLD.`pi74`,'pi75', OLD.`pi75`,'pi76', OLD.`pi76`,'pi77', OLD.`pi77`,'pi78', OLD.`pi78`,'pi79', OLD.`pi79`,'pi80', OLD.`pi80`,'pi81', OLD.`pi81`,'pi82', OLD.`pi82`,'pi83', OLD.`pi83`,'pi84', OLD.`pi84`,'pi85', OLD.`pi85`,'pi86', OLD.`pi86`,'pi87', OLD.`pi87`,'pi88', OLD.`pi88`,'pi89', OLD.`pi89`,'pi90', OLD.`pi90`,'pi91', OLD.`pi91`,'pi92', OLD.`pi92`,'pi93', OLD.`pi93`,'pi94', OLD.`pi94`,'pi95', OLD.`pi95`,'pi96', OLD.`pi96`,'pi97', OLD.`pi97`,'pi98', OLD.`pi98`,'pi99', OLD.`pi99`,'pi100', OLD.`pi100`,'pi101', OLD.`pi101`,'pi102', OLD.`pi102`,'pi103', OLD.`pi103`,'pi104', OLD.`pi104`,'pi105', OLD.`pi105`,'pi106', OLD.`pi106`,'pi107', OLD.`pi107`,'pi108', OLD.`pi108`,'pi109', OLD.`pi109`,'pi110', OLD.`pi110`,'pi111', OLD.`pi111`,'pi112', OLD.`pi112`,'pi113', OLD.`pi113`,'pi114', OLD.`pi114`,'pi115', OLD.`pi115`,'pi116', OLD.`pi116`,'pi117', OLD.`pi117`,'pi118', OLD.`pi118`,'pi119', OLD.`pi119`,'pi120', OLD.`pi120`,'pi121', OLD.`pi121`,'pi122', OLD.`pi122`,'pi123', OLD.`pi123`,'pi124', OLD.`pi124`,'pi125', OLD.`pi125`,'pi126', OLD.`pi126`,'pi127', OLD.`pi127`,'pi128', OLD.`pi128`,'pi129', OLD.`pi129`,'pi130', OLD.`pi130`,'pi131', OLD.`pi131`,'pi132', OLD.`pi132`,'pi133', OLD.`pi133`,'pi134', OLD.`pi134`,'pi135', OLD.`pi135`,'pi136', OLD.`pi136`,'pi137', OLD.`pi137`,'pi138', OLD.`pi138`,'pi139', OLD.`pi139`,'pi140', OLD.`pi140`,'pi141', OLD.`pi141`,'pi142', OLD.`pi142`,'pi143', OLD.`pi143`,'pi144', OLD.`pi144`,'pi145', OLD.`pi145`,'pi146', OLD.`pi146`,'pi147', OLD.`pi147`,'pi148', OLD.`pi148`,'pi149', OLD.`pi149`,'pi150', OLD.`pi150`,'h1', OLD.`h1`,'h2', OLD.`h2`,'h3', OLD.`h3`,'h4', OLD.`h4`,'h5', OLD.`h5`,'h6', OLD.`h6`,'h7', OLD.`h7`,'h8', OLD.`h8`,'h9', OLD.`h9`,'h10', OLD.`h10`,'h11', OLD.`h11`,'h12', OLD.`h12`,'h13', OLD.`h13`,'h14', OLD.`h14`,'h15', OLD.`h15`,'h16', OLD.`h16`,'h17', OLD.`h17`,'h18', OLD.`h18`,'h19', OLD.`h19`,'h20', OLD.`h20`,'h21', OLD.`h21`,'h22', OLD.`h22`,'h23', OLD.`h23`,'h24', OLD.`h24`,'h25', OLD.`h25`,'h26', OLD.`h26`,'h27', OLD.`h27`,'h28', OLD.`h28`,'h29', OLD.`h29`,'h30', OLD.`h30`,'h31', OLD.`h31`,'h32', OLD.`h32`,'h33', OLD.`h33`,'h34', OLD.`h34`,'h35', OLD.`h35`,'h36', OLD.`h36`,'h37', OLD.`h37`,'h38', OLD.`h38`,'h39', OLD.`h39`,'h40', OLD.`h40`,'h41', OLD.`h41`,'h42', OLD.`h42`,'h43', OLD.`h43`,'h44', OLD.`h44`,'h45', OLD.`h45`,'h46', OLD.`h46`,'h47', OLD.`h47`,'h48', OLD.`h48`,'h49', OLD.`h49`,'h50', OLD.`h50`,'l1', OLD.`l1`,'l2', OLD.`l2`,'l3', OLD.`l3`,'l4', OLD.`l4`,'l5', OLD.`l5`,'l6', OLD.`l6`,'l7', OLD.`l7`,'l8', OLD.`l8`,'l9', OLD.`l9`,'l10', OLD.`l10`,'l11', OLD.`l11`,'l12', OLD.`l12`,'l13', OLD.`l13`,'l14', OLD.`l14`,'l15', OLD.`l15`,'l16', OLD.`l16`,'l17', OLD.`l17`,'l18', OLD.`l18`,'l19', OLD.`l19`,'l20', OLD.`l20`,'l21', OLD.`l21`,'l22', OLD.`l22`,'l23', OLD.`l23`,'l24', OLD.`l24`,'l25', OLD.`l25`,'l26', OLD.`l26`,'l27', OLD.`l27`,'l28', OLD.`l28`,'l29', OLD.`l29`,'l30', OLD.`l30`,'l31', OLD.`l31`,'l32', OLD.`l32`,'l33', OLD.`l33`,'l34', OLD.`l34`,'l35', OLD.`l35`,'l36', OLD.`l36`,'l37', OLD.`l37`,'l38', OLD.`l38`,'l39', OLD.`l39`,'l40', OLD.`l40`,'l41', OLD.`l41`,'l42', OLD.`l42`,'l43', OLD.`l43`,'l44', OLD.`l44`,'l45', OLD.`l45`,'l46', OLD.`l46`,'l47', OLD.`l47`,'l48', OLD.`l48`,'l49', OLD.`l49`,'l50', OLD.`l50`,'rate', OLD.`rate`),
            COALESCE(@current_user,'system')
        );
    END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `taka`
--

DROP TABLE IF EXISTS `taka`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `taka` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `val` int(11) NOT NULL,
  `eng` varchar(20) DEFAULT NULL,
  `ben` varchar(60) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=101 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
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
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER taka_after_insert
    AFTER INSERT ON `taka`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, new_data, changed_by)
        VALUES(
            'taka',
            'INSERT',
            NEW.`id`,
            JSON_OBJECT('id', NEW.`id`,'val', NEW.`val`,'eng', NEW.`eng`,'ben', NEW.`ben`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER taka_after_update
    AFTER UPDATE ON `taka`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, new_data, changed_by)
        VALUES(
            'taka',
            'UPDATE',
            NEW.`id`,
            JSON_OBJECT('id', OLD.`id`,'val', OLD.`val`,'eng', OLD.`eng`,'ben', OLD.`ben`),
            JSON_OBJECT('id', NEW.`id`,'val', NEW.`val`,'eng', NEW.`eng`,'ben', NEW.`ben`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER taka_after_delete
    AFTER DELETE ON `taka`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, changed_by)
        VALUES(
            'taka',
            'DELETE',
            OLD.`id`,
            JSON_OBJECT('id', OLD.`id`,'val', OLD.`val`,'eng', OLD.`eng`,'ben', OLD.`ben`),
            COALESCE(@current_user,'system')
        );
    END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `tcert`
--

DROP TABLE IF EXISTS `tcert`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tcert` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sccode` int(11) DEFAULT NULL,
  `sessionyear` int(11) DEFAULT NULL,
  `tcno` int(11) NOT NULL,
  `stid` varchar(10) NOT NULL,
  `stnameeng` varchar(100) NOT NULL,
  `stnameben` varchar(150) NOT NULL,
  `fname` varchar(120) NOT NULL,
  `mname` varchar(120) NOT NULL,
  `vill` varchar(100) NOT NULL,
  `po` varchar(100) NOT NULL,
  `ps` varchar(50) NOT NULL,
  `dist` varchar(50) NOT NULL,
  `dob` date NOT NULL,
  `cls` varchar(20) DEFAULT NULL,
  `cause` varchar(120) NOT NULL,
  `amount` int(11) NOT NULL,
  `issueby` varchar(120) NOT NULL,
  `issuetime` datetime NOT NULL,
  `refno` varchar(50) NOT NULL,
  `modifieddate` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
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
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER tcert_after_insert
    AFTER INSERT ON `tcert`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, new_data, changed_by)
        VALUES(
            'tcert',
            'INSERT',
            NEW.`id`,
            JSON_OBJECT('id', NEW.`id`,'sccode', NEW.`sccode`,'sessionyear', NEW.`sessionyear`,'tcno', NEW.`tcno`,'stid', NEW.`stid`,'stnameeng', NEW.`stnameeng`,'stnameben', NEW.`stnameben`,'fname', NEW.`fname`,'mname', NEW.`mname`,'vill', NEW.`vill`,'po', NEW.`po`,'ps', NEW.`ps`,'dist', NEW.`dist`,'dob', NEW.`dob`,'cls', NEW.`cls`,'cause', NEW.`cause`,'amount', NEW.`amount`,'issueby', NEW.`issueby`,'issuetime', NEW.`issuetime`,'refno', NEW.`refno`,'modifieddate', NEW.`modifieddate`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER tcert_after_update
    AFTER UPDATE ON `tcert`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, new_data, changed_by)
        VALUES(
            'tcert',
            'UPDATE',
            NEW.`id`,
            JSON_OBJECT('id', OLD.`id`,'sccode', OLD.`sccode`,'sessionyear', OLD.`sessionyear`,'tcno', OLD.`tcno`,'stid', OLD.`stid`,'stnameeng', OLD.`stnameeng`,'stnameben', OLD.`stnameben`,'fname', OLD.`fname`,'mname', OLD.`mname`,'vill', OLD.`vill`,'po', OLD.`po`,'ps', OLD.`ps`,'dist', OLD.`dist`,'dob', OLD.`dob`,'cls', OLD.`cls`,'cause', OLD.`cause`,'amount', OLD.`amount`,'issueby', OLD.`issueby`,'issuetime', OLD.`issuetime`,'refno', OLD.`refno`,'modifieddate', OLD.`modifieddate`),
            JSON_OBJECT('id', NEW.`id`,'sccode', NEW.`sccode`,'sessionyear', NEW.`sessionyear`,'tcno', NEW.`tcno`,'stid', NEW.`stid`,'stnameeng', NEW.`stnameeng`,'stnameben', NEW.`stnameben`,'fname', NEW.`fname`,'mname', NEW.`mname`,'vill', NEW.`vill`,'po', NEW.`po`,'ps', NEW.`ps`,'dist', NEW.`dist`,'dob', NEW.`dob`,'cls', NEW.`cls`,'cause', NEW.`cause`,'amount', NEW.`amount`,'issueby', NEW.`issueby`,'issuetime', NEW.`issuetime`,'refno', NEW.`refno`,'modifieddate', NEW.`modifieddate`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER tcert_after_delete
    AFTER DELETE ON `tcert`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, changed_by)
        VALUES(
            'tcert',
            'DELETE',
            OLD.`id`,
            JSON_OBJECT('id', OLD.`id`,'sccode', OLD.`sccode`,'sessionyear', OLD.`sessionyear`,'tcno', OLD.`tcno`,'stid', OLD.`stid`,'stnameeng', OLD.`stnameeng`,'stnameben', OLD.`stnameben`,'fname', OLD.`fname`,'mname', OLD.`mname`,'vill', OLD.`vill`,'po', OLD.`po`,'ps', OLD.`ps`,'dist', OLD.`dist`,'dob', OLD.`dob`,'cls', OLD.`cls`,'cause', OLD.`cause`,'amount', OLD.`amount`,'issueby', OLD.`issueby`,'issuetime', OLD.`issuetime`,'refno', OLD.`refno`,'modifieddate', OLD.`modifieddate`),
            COALESCE(@current_user,'system')
        );
    END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `teacher`
--

DROP TABLE IF EXISTS `teacher`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `teacher` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sl` int(11) DEFAULT NULL,
  `tid` varchar(11) DEFAULT NULL,
  `tname` varchar(40) DEFAULT NULL,
  `tnameb` varchar(150) DEFAULT NULL,
  `position` varchar(25) DEFAULT NULL,
  `slots` varchar(20) DEFAULT NULL,
  `jdate` date DEFAULT NULL,
  `ranks` int(11) DEFAULT NULL,
  `subjects` varchar(20) DEFAULT NULL,
  `fname` varchar(40) DEFAULT NULL,
  `mname` varchar(40) DEFAULT NULL,
  `spouse` varchar(100) DEFAULT NULL,
  `emergency` varchar(11) DEFAULT NULL,
  `preadd` varchar(100) DEFAULT NULL,
  `previll` varchar(100) DEFAULT NULL,
  `prepo` varchar(100) DEFAULT NULL,
  `preps` varchar(100) DEFAULT NULL,
  `predist` varchar(100) DEFAULT NULL,
  `pervill` varchar(100) DEFAULT NULL,
  `perpo` varchar(100) DEFAULT NULL,
  `perps` varchar(100) DEFAULT NULL,
  `perdist` varchar(100) DEFAULT NULL,
  `dob` date DEFAULT NULL,
  `religion` varchar(10) DEFAULT NULL,
  `gender` varchar(6) DEFAULT NULL,
  `email` varchar(50) DEFAULT NULL,
  `mobile` varchar(11) DEFAULT NULL,
  `nid` varchar(17) DEFAULT NULL,
  `bgroup` varchar(5) DEFAULT NULL,
  `status` varchar(3) DEFAULT NULL,
  `sccode` int(11) NOT NULL,
  `curin` time NOT NULL,
  `curout` time NOT NULL,
  `salery` int(11) NOT NULL,
  `fjdate` date NOT NULL,
  `mpoindex` varchar(15) DEFAULT NULL,
  `tin` varchar(12) DEFAULT NULL,
  `accno` varchar(20) DEFAULT NULL,
  `bankname` varchar(25) DEFAULT NULL,
  `branch` varchar(50) DEFAULT NULL,
  `routing` varchar(20) DEFAULT NULL,
  `accnosch` varchar(20) DEFAULT NULL,
  `bnamesch` varchar(25) DEFAULT NULL,
  `bbrsch` varchar(50) DEFAULT NULL,
  `routesch` varchar(20) DEFAULT NULL,
  `accnopf` varchar(20) DEFAULT NULL,
  `bnamepf` varchar(25) DEFAULT NULL,
  `bbrpf` varchar(50) DEFAULT NULL,
  `routepf` varchar(20) DEFAULT NULL,
  `paycode` int(11) DEFAULT NULL,
  `payscale` int(11) DEFAULT 0,
  `basic` int(11) NOT NULL DEFAULT 0,
  `incentive` int(11) NOT NULL DEFAULT 0,
  `house` int(11) NOT NULL DEFAULT 0,
  `medical` int(11) NOT NULL DEFAULT 0,
  `arrea` int(11) NOT NULL DEFAULT 0,
  `welfare` int(11) DEFAULT 0,
  `retire` int(11) NOT NULL DEFAULT 0,
  `netamtgovt` int(11) NOT NULL DEFAULT 0,
  `salary` int(11) DEFAULT 0,
  `mobilevata` int(11) NOT NULL DEFAULT 0,
  `travel` int(11) NOT NULL DEFAULT 0,
  `medical2` int(11) NOT NULL DEFAULT 0,
  `exam` int(11) NOT NULL DEFAULT 0,
  `festival` int(11) NOT NULL DEFAULT 0,
  `pf` int(11) NOT NULL DEFAULT 0,
  `net2` int(11) NOT NULL DEFAULT 0,
  `ex_1` varchar(30) DEFAULT NULL,
  `val_1` varchar(100) DEFAULT NULL,
  `ex_2` varchar(30) DEFAULT NULL,
  `val_2` varchar(100) DEFAULT NULL,
  `ex_3` varchar(30) DEFAULT NULL,
  `val_3` varchar(100) DEFAULT NULL,
  `ex_4` varchar(30) DEFAULT NULL,
  `val_4` varchar(100) DEFAULT NULL,
  `rfidtag` varchar(10) DEFAULT NULL,
  `modifieddate` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1048 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
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
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER teacher_after_insert
    AFTER INSERT ON `teacher`
    FOR EACH ROW
    BEGIN
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
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER teacher_after_update
    AFTER UPDATE ON `teacher`
    FOR EACH ROW
    BEGIN
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
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER teacher_after_delete
    AFTER DELETE ON `teacher`
    FOR EACH ROW
    BEGIN
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
-- Table structure for table `teacher_leave_app`
--

DROP TABLE IF EXISTS `teacher_leave_app`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `teacher_leave_app` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sccode` int(11) DEFAULT NULL,
  `tid` int(11) DEFAULT NULL,
  `apply_date` date DEFAULT NULL,
  `date_from` date DEFAULT NULL,
  `date_to` date DEFAULT NULL,
  `days` int(11) DEFAULT NULL,
  `leave_type` varchar(25) DEFAULT NULL,
  `leave_reason` varchar(50) DEFAULT NULL,
  `apply_by` varchar(120) DEFAULT NULL,
  `apply_time` datetime DEFAULT NULL,
  `status` int(11) DEFAULT NULL,
  `response_by` varchar(120) DEFAULT NULL,
  `response_time` datetime DEFAULT NULL,
  `modifieddate` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
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
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER teacher_leave_app_after_insert
    AFTER INSERT ON `teacher_leave_app`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, new_data, changed_by)
        VALUES(
            'teacher_leave_app',
            'INSERT',
            NEW.`id`,
            JSON_OBJECT('id', NEW.`id`,'sccode', NEW.`sccode`,'tid', NEW.`tid`,'apply_date', NEW.`apply_date`,'date_from', NEW.`date_from`,'date_to', NEW.`date_to`,'days', NEW.`days`,'leave_type', NEW.`leave_type`,'leave_reason', NEW.`leave_reason`,'apply_by', NEW.`apply_by`,'apply_time', NEW.`apply_time`,'status', NEW.`status`,'response_by', NEW.`response_by`,'response_time', NEW.`response_time`,'modifieddate', NEW.`modifieddate`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER teacher_leave_app_after_update
    AFTER UPDATE ON `teacher_leave_app`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, new_data, changed_by)
        VALUES(
            'teacher_leave_app',
            'UPDATE',
            NEW.`id`,
            JSON_OBJECT('id', OLD.`id`,'sccode', OLD.`sccode`,'tid', OLD.`tid`,'apply_date', OLD.`apply_date`,'date_from', OLD.`date_from`,'date_to', OLD.`date_to`,'days', OLD.`days`,'leave_type', OLD.`leave_type`,'leave_reason', OLD.`leave_reason`,'apply_by', OLD.`apply_by`,'apply_time', OLD.`apply_time`,'status', OLD.`status`,'response_by', OLD.`response_by`,'response_time', OLD.`response_time`,'modifieddate', OLD.`modifieddate`),
            JSON_OBJECT('id', NEW.`id`,'sccode', NEW.`sccode`,'tid', NEW.`tid`,'apply_date', NEW.`apply_date`,'date_from', NEW.`date_from`,'date_to', NEW.`date_to`,'days', NEW.`days`,'leave_type', NEW.`leave_type`,'leave_reason', NEW.`leave_reason`,'apply_by', NEW.`apply_by`,'apply_time', NEW.`apply_time`,'status', NEW.`status`,'response_by', NEW.`response_by`,'response_time', NEW.`response_time`,'modifieddate', NEW.`modifieddate`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER teacher_leave_app_after_delete
    AFTER DELETE ON `teacher_leave_app`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, changed_by)
        VALUES(
            'teacher_leave_app',
            'DELETE',
            OLD.`id`,
            JSON_OBJECT('id', OLD.`id`,'sccode', OLD.`sccode`,'tid', OLD.`tid`,'apply_date', OLD.`apply_date`,'date_from', OLD.`date_from`,'date_to', OLD.`date_to`,'days', OLD.`days`,'leave_type', OLD.`leave_type`,'leave_reason', OLD.`leave_reason`,'apply_by', OLD.`apply_by`,'apply_time', OLD.`apply_time`,'status', OLD.`status`,'response_by', OLD.`response_by`,'response_time', OLD.`response_time`,'modifieddate', OLD.`modifieddate`),
            COALESCE(@current_user,'system')
        );
    END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `teacher_salary_structure`
--

DROP TABLE IF EXISTS `teacher_salary_structure`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `teacher_salary_structure` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tid` varchar(11) DEFAULT NULL,
  `sccode` int(11) NOT NULL,
  `applydate` date DEFAULT NULL,
  `accno` varchar(20) DEFAULT NULL,
  `bankname` varchar(25) DEFAULT NULL,
  `branch` varchar(50) DEFAULT NULL,
  `routing` varchar(20) DEFAULT NULL,
  `accnosch` varchar(20) DEFAULT NULL,
  `bnamesch` varchar(25) DEFAULT NULL,
  `bbrsch` varchar(50) DEFAULT NULL,
  `routesch` varchar(20) DEFAULT NULL,
  `accnopf` varchar(20) DEFAULT NULL,
  `bnamepf` varchar(25) DEFAULT NULL,
  `bbrpf` varchar(50) DEFAULT NULL,
  `routepf` varchar(20) DEFAULT NULL,
  `paycode` int(11) DEFAULT NULL,
  `payscale` int(11) DEFAULT 0,
  `basic` int(11) NOT NULL DEFAULT 0,
  `incentive` int(11) NOT NULL DEFAULT 0,
  `house` int(11) NOT NULL DEFAULT 0,
  `medical` int(11) NOT NULL DEFAULT 0,
  `arrea` int(11) NOT NULL DEFAULT 0,
  `welfare` int(11) DEFAULT 0,
  `retire` int(11) NOT NULL DEFAULT 0,
  `netamtgovt` int(11) NOT NULL DEFAULT 0,
  `salary` int(11) DEFAULT 0,
  `mobilevata` int(11) NOT NULL DEFAULT 0,
  `travel` int(11) NOT NULL DEFAULT 0,
  `medical2` int(11) NOT NULL DEFAULT 0,
  `exam` int(11) NOT NULL DEFAULT 0,
  `festival` int(11) NOT NULL DEFAULT 0,
  `pf` int(11) NOT NULL DEFAULT 0,
  `net2` int(11) NOT NULL DEFAULT 0,
  `modifieddate` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=76 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
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
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER teacher_salary_structure_after_insert
    AFTER INSERT ON `teacher_salary_structure`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, new_data, changed_by)
        VALUES(
            'teacher_salary_structure',
            'INSERT',
            NEW.`id`,
            JSON_OBJECT('id', NEW.`id`,'tid', NEW.`tid`,'sccode', NEW.`sccode`,'applydate', NEW.`applydate`,'accno', NEW.`accno`,'bankname', NEW.`bankname`,'branch', NEW.`branch`,'routing', NEW.`routing`,'accnosch', NEW.`accnosch`,'bnamesch', NEW.`bnamesch`,'bbrsch', NEW.`bbrsch`,'routesch', NEW.`routesch`,'accnopf', NEW.`accnopf`,'bnamepf', NEW.`bnamepf`,'bbrpf', NEW.`bbrpf`,'routepf', NEW.`routepf`,'paycode', NEW.`paycode`,'payscale', NEW.`payscale`,'basic', NEW.`basic`,'incentive', NEW.`incentive`,'house', NEW.`house`,'medical', NEW.`medical`,'arrea', NEW.`arrea`,'welfare', NEW.`welfare`,'retire', NEW.`retire`,'netamtgovt', NEW.`netamtgovt`,'salary', NEW.`salary`,'mobilevata', NEW.`mobilevata`,'travel', NEW.`travel`,'medical2', NEW.`medical2`,'exam', NEW.`exam`,'festival', NEW.`festival`,'pf', NEW.`pf`,'net2', NEW.`net2`,'modifieddate', NEW.`modifieddate`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER teacher_salary_structure_after_update
    AFTER UPDATE ON `teacher_salary_structure`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, new_data, changed_by)
        VALUES(
            'teacher_salary_structure',
            'UPDATE',
            NEW.`id`,
            JSON_OBJECT('id', OLD.`id`,'tid', OLD.`tid`,'sccode', OLD.`sccode`,'applydate', OLD.`applydate`,'accno', OLD.`accno`,'bankname', OLD.`bankname`,'branch', OLD.`branch`,'routing', OLD.`routing`,'accnosch', OLD.`accnosch`,'bnamesch', OLD.`bnamesch`,'bbrsch', OLD.`bbrsch`,'routesch', OLD.`routesch`,'accnopf', OLD.`accnopf`,'bnamepf', OLD.`bnamepf`,'bbrpf', OLD.`bbrpf`,'routepf', OLD.`routepf`,'paycode', OLD.`paycode`,'payscale', OLD.`payscale`,'basic', OLD.`basic`,'incentive', OLD.`incentive`,'house', OLD.`house`,'medical', OLD.`medical`,'arrea', OLD.`arrea`,'welfare', OLD.`welfare`,'retire', OLD.`retire`,'netamtgovt', OLD.`netamtgovt`,'salary', OLD.`salary`,'mobilevata', OLD.`mobilevata`,'travel', OLD.`travel`,'medical2', OLD.`medical2`,'exam', OLD.`exam`,'festival', OLD.`festival`,'pf', OLD.`pf`,'net2', OLD.`net2`,'modifieddate', OLD.`modifieddate`),
            JSON_OBJECT('id', NEW.`id`,'tid', NEW.`tid`,'sccode', NEW.`sccode`,'applydate', NEW.`applydate`,'accno', NEW.`accno`,'bankname', NEW.`bankname`,'branch', NEW.`branch`,'routing', NEW.`routing`,'accnosch', NEW.`accnosch`,'bnamesch', NEW.`bnamesch`,'bbrsch', NEW.`bbrsch`,'routesch', NEW.`routesch`,'accnopf', NEW.`accnopf`,'bnamepf', NEW.`bnamepf`,'bbrpf', NEW.`bbrpf`,'routepf', NEW.`routepf`,'paycode', NEW.`paycode`,'payscale', NEW.`payscale`,'basic', NEW.`basic`,'incentive', NEW.`incentive`,'house', NEW.`house`,'medical', NEW.`medical`,'arrea', NEW.`arrea`,'welfare', NEW.`welfare`,'retire', NEW.`retire`,'netamtgovt', NEW.`netamtgovt`,'salary', NEW.`salary`,'mobilevata', NEW.`mobilevata`,'travel', NEW.`travel`,'medical2', NEW.`medical2`,'exam', NEW.`exam`,'festival', NEW.`festival`,'pf', NEW.`pf`,'net2', NEW.`net2`,'modifieddate', NEW.`modifieddate`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER teacher_salary_structure_after_delete
    AFTER DELETE ON `teacher_salary_structure`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, changed_by)
        VALUES(
            'teacher_salary_structure',
            'DELETE',
            OLD.`id`,
            JSON_OBJECT('id', OLD.`id`,'tid', OLD.`tid`,'sccode', OLD.`sccode`,'applydate', OLD.`applydate`,'accno', OLD.`accno`,'bankname', OLD.`bankname`,'branch', OLD.`branch`,'routing', OLD.`routing`,'accnosch', OLD.`accnosch`,'bnamesch', OLD.`bnamesch`,'bbrsch', OLD.`bbrsch`,'routesch', OLD.`routesch`,'accnopf', OLD.`accnopf`,'bnamepf', OLD.`bnamepf`,'bbrpf', OLD.`bbrpf`,'routepf', OLD.`routepf`,'paycode', OLD.`paycode`,'payscale', OLD.`payscale`,'basic', OLD.`basic`,'incentive', OLD.`incentive`,'house', OLD.`house`,'medical', OLD.`medical`,'arrea', OLD.`arrea`,'welfare', OLD.`welfare`,'retire', OLD.`retire`,'netamtgovt', OLD.`netamtgovt`,'salary', OLD.`salary`,'mobilevata', OLD.`mobilevata`,'travel', OLD.`travel`,'medical2', OLD.`medical2`,'exam', OLD.`exam`,'festival', OLD.`festival`,'pf', OLD.`pf`,'net2', OLD.`net2`,'modifieddate', OLD.`modifieddate`),
            COALESCE(@current_user,'system')
        );
    END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `teacherattnd`
--

DROP TABLE IF EXISTS `teacherattnd`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `teacherattnd` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user` varchar(120) DEFAULT NULL,
  `tid` bigint(20) DEFAULT NULL,
  `adate` date DEFAULT NULL,
  `reqin` time NOT NULL DEFAULT '10:00:00',
  `reqout` time NOT NULL DEFAULT '16:00:00',
  `realin` time DEFAULT NULL,
  `realout` time DEFAULT NULL,
  `balin` time DEFAULT NULL,
  `balout` time DEFAULT NULL,
  `statusin` varchar(20) DEFAULT NULL,
  `statusout` varchar(20) DEFAULT NULL,
  `detectin` varchar(15) DEFAULT NULL COMMENT 'Manual, GPS, Fingerprint, RFID Card, PIN, Face',
  `detectout` varchar(15) DEFAULT NULL,
  `disin` int(11) DEFAULT 0,
  `disout` int(11) NOT NULL DEFAULT 0,
  `dutytime` time DEFAULT NULL,
  `entryby` varchar(30) DEFAULT NULL,
  `sccode` int(11) DEFAULT NULL,
  `st` varchar(10) DEFAULT NULL,
  `entrycode` varchar(10) DEFAULT NULL,
  `entrytime` timestamp NOT NULL DEFAULT current_timestamp(),
  `modifieddate` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1549 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
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
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER teacherattnd_after_insert
    AFTER INSERT ON `teacherattnd`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, new_data, changed_by)
        VALUES(
            'teacherattnd',
            'INSERT',
            NEW.`id`,
            JSON_OBJECT('id', NEW.`id`,'user', NEW.`user`,'tid', NEW.`tid`,'adate', NEW.`adate`,'reqin', NEW.`reqin`,'reqout', NEW.`reqout`,'realin', NEW.`realin`,'realout', NEW.`realout`,'balin', NEW.`balin`,'balout', NEW.`balout`,'statusin', NEW.`statusin`,'statusout', NEW.`statusout`,'detectin', NEW.`detectin`,'detectout', NEW.`detectout`,'disin', NEW.`disin`,'disout', NEW.`disout`,'dutytime', NEW.`dutytime`,'entryby', NEW.`entryby`,'sccode', NEW.`sccode`,'st', NEW.`st`,'entrycode', NEW.`entrycode`,'entrytime', NEW.`entrytime`,'modifieddate', NEW.`modifieddate`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER teacherattnd_after_update
    AFTER UPDATE ON `teacherattnd`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, new_data, changed_by)
        VALUES(
            'teacherattnd',
            'UPDATE',
            NEW.`id`,
            JSON_OBJECT('id', OLD.`id`,'user', OLD.`user`,'tid', OLD.`tid`,'adate', OLD.`adate`,'reqin', OLD.`reqin`,'reqout', OLD.`reqout`,'realin', OLD.`realin`,'realout', OLD.`realout`,'balin', OLD.`balin`,'balout', OLD.`balout`,'statusin', OLD.`statusin`,'statusout', OLD.`statusout`,'detectin', OLD.`detectin`,'detectout', OLD.`detectout`,'disin', OLD.`disin`,'disout', OLD.`disout`,'dutytime', OLD.`dutytime`,'entryby', OLD.`entryby`,'sccode', OLD.`sccode`,'st', OLD.`st`,'entrycode', OLD.`entrycode`,'entrytime', OLD.`entrytime`,'modifieddate', OLD.`modifieddate`),
            JSON_OBJECT('id', NEW.`id`,'user', NEW.`user`,'tid', NEW.`tid`,'adate', NEW.`adate`,'reqin', NEW.`reqin`,'reqout', NEW.`reqout`,'realin', NEW.`realin`,'realout', NEW.`realout`,'balin', NEW.`balin`,'balout', NEW.`balout`,'statusin', NEW.`statusin`,'statusout', NEW.`statusout`,'detectin', NEW.`detectin`,'detectout', NEW.`detectout`,'disin', NEW.`disin`,'disout', NEW.`disout`,'dutytime', NEW.`dutytime`,'entryby', NEW.`entryby`,'sccode', NEW.`sccode`,'st', NEW.`st`,'entrycode', NEW.`entrycode`,'entrytime', NEW.`entrytime`,'modifieddate', NEW.`modifieddate`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER teacherattnd_after_delete
    AFTER DELETE ON `teacherattnd`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, changed_by)
        VALUES(
            'teacherattnd',
            'DELETE',
            OLD.`id`,
            JSON_OBJECT('id', OLD.`id`,'user', OLD.`user`,'tid', OLD.`tid`,'adate', OLD.`adate`,'reqin', OLD.`reqin`,'reqout', OLD.`reqout`,'realin', OLD.`realin`,'realout', OLD.`realout`,'balin', OLD.`balin`,'balout', OLD.`balout`,'statusin', OLD.`statusin`,'statusout', OLD.`statusout`,'detectin', OLD.`detectin`,'detectout', OLD.`detectout`,'disin', OLD.`disin`,'disout', OLD.`disout`,'dutytime', OLD.`dutytime`,'entryby', OLD.`entryby`,'sccode', OLD.`sccode`,'st', OLD.`st`,'entrycode', OLD.`entrycode`,'entrytime', OLD.`entrytime`,'modifieddate', OLD.`modifieddate`),
            COALESCE(@current_user,'system')
        );
    END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `testimonial`
--

DROP TABLE IF EXISTS `testimonial`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `testimonial` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sccode` varchar(10) DEFAULT NULL,
  `stid` varchar(12) DEFAULT NULL,
  `pubexam` varchar(5) DEFAULT NULL,
  `regdno` varchar(10) DEFAULT NULL,
  `regdyear` int(11) DEFAULT NULL,
  `rollno` varchar(10) DEFAULT NULL,
  `passyear` int(11) DEFAULT NULL,
  `session` varchar(7) DEFAULT NULL,
  `gpa` float DEFAULT 0,
  `grade` varchar(2) DEFAULT 'F',
  `slno` int(11) DEFAULT NULL,
  `testslno` varchar(20) DEFAULT NULL,
  `testdate` date DEFAULT NULL,
  `groupsection` varchar(20) DEFAULT NULL,
  `examcenter` varchar(20) DEFAULT NULL,
  `issueby` varchar(120) DEFAULT NULL,
  `issuetime` datetime DEFAULT NULL,
  `modifieddate` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=621 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
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
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER testimonial_after_insert
    AFTER INSERT ON `testimonial`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, new_data, changed_by)
        VALUES(
            'testimonial',
            'INSERT',
            NEW.`id`,
            JSON_OBJECT('id', NEW.`id`,'sccode', NEW.`sccode`,'stid', NEW.`stid`,'pubexam', NEW.`pubexam`,'regdno', NEW.`regdno`,'regdyear', NEW.`regdyear`,'rollno', NEW.`rollno`,'passyear', NEW.`passyear`,'session', NEW.`session`,'gpa', NEW.`gpa`,'grade', NEW.`grade`,'slno', NEW.`slno`,'testslno', NEW.`testslno`,'testdate', NEW.`testdate`,'groupsection', NEW.`groupsection`,'examcenter', NEW.`examcenter`,'issueby', NEW.`issueby`,'issuetime', NEW.`issuetime`,'modifieddate', NEW.`modifieddate`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER testimonial_after_update
    AFTER UPDATE ON `testimonial`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, new_data, changed_by)
        VALUES(
            'testimonial',
            'UPDATE',
            NEW.`id`,
            JSON_OBJECT('id', OLD.`id`,'sccode', OLD.`sccode`,'stid', OLD.`stid`,'pubexam', OLD.`pubexam`,'regdno', OLD.`regdno`,'regdyear', OLD.`regdyear`,'rollno', OLD.`rollno`,'passyear', OLD.`passyear`,'session', OLD.`session`,'gpa', OLD.`gpa`,'grade', OLD.`grade`,'slno', OLD.`slno`,'testslno', OLD.`testslno`,'testdate', OLD.`testdate`,'groupsection', OLD.`groupsection`,'examcenter', OLD.`examcenter`,'issueby', OLD.`issueby`,'issuetime', OLD.`issuetime`,'modifieddate', OLD.`modifieddate`),
            JSON_OBJECT('id', NEW.`id`,'sccode', NEW.`sccode`,'stid', NEW.`stid`,'pubexam', NEW.`pubexam`,'regdno', NEW.`regdno`,'regdyear', NEW.`regdyear`,'rollno', NEW.`rollno`,'passyear', NEW.`passyear`,'session', NEW.`session`,'gpa', NEW.`gpa`,'grade', NEW.`grade`,'slno', NEW.`slno`,'testslno', NEW.`testslno`,'testdate', NEW.`testdate`,'groupsection', NEW.`groupsection`,'examcenter', NEW.`examcenter`,'issueby', NEW.`issueby`,'issuetime', NEW.`issuetime`,'modifieddate', NEW.`modifieddate`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER testimonial_after_delete
    AFTER DELETE ON `testimonial`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, changed_by)
        VALUES(
            'testimonial',
            'DELETE',
            OLD.`id`,
            JSON_OBJECT('id', OLD.`id`,'sccode', OLD.`sccode`,'stid', OLD.`stid`,'pubexam', OLD.`pubexam`,'regdno', OLD.`regdno`,'regdyear', OLD.`regdyear`,'rollno', OLD.`rollno`,'passyear', OLD.`passyear`,'session', OLD.`session`,'gpa', OLD.`gpa`,'grade', OLD.`grade`,'slno', OLD.`slno`,'testslno', OLD.`testslno`,'testdate', OLD.`testdate`,'groupsection', OLD.`groupsection`,'examcenter', OLD.`examcenter`,'issueby', OLD.`issueby`,'issuetime', OLD.`issuetime`,'modifieddate', OLD.`modifieddate`),
            COALESCE(@current_user,'system')
        );
    END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `textbook`
--

DROP TABLE IF EXISTS `textbook`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `textbook` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `classname` varchar(20) NOT NULL,
  `subcode` int(11) NOT NULL,
  `uniqid` varchar(20) NOT NULL,
  `order1` int(11) DEFAULT NULL,
  `order2` int(11) DEFAULT NULL,
  `chapter` int(11) NOT NULL,
  `title` varchar(100) NOT NULL,
  `subchapter` int(11) DEFAULT NULL,
  `subtitle` varchar(250) DEFAULT NULL,
  `reqclass` int(11) DEFAULT NULL,
  `entryby` varchar(120) DEFAULT NULL,
  `entrytime` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniqid_unique` (`uniqid`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
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
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER textbook_after_insert
    AFTER INSERT ON `textbook`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, new_data, changed_by)
        VALUES(
            'textbook',
            'INSERT',
            NEW.`id`,
            JSON_OBJECT('id', NEW.`id`,'classname', NEW.`classname`,'subcode', NEW.`subcode`,'uniqid', NEW.`uniqid`,'order1', NEW.`order1`,'order2', NEW.`order2`,'chapter', NEW.`chapter`,'title', NEW.`title`,'subchapter', NEW.`subchapter`,'subtitle', NEW.`subtitle`,'reqclass', NEW.`reqclass`,'entryby', NEW.`entryby`,'entrytime', NEW.`entrytime`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER textbook_after_update
    AFTER UPDATE ON `textbook`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, new_data, changed_by)
        VALUES(
            'textbook',
            'UPDATE',
            NEW.`id`,
            JSON_OBJECT('id', OLD.`id`,'classname', OLD.`classname`,'subcode', OLD.`subcode`,'uniqid', OLD.`uniqid`,'order1', OLD.`order1`,'order2', OLD.`order2`,'chapter', OLD.`chapter`,'title', OLD.`title`,'subchapter', OLD.`subchapter`,'subtitle', OLD.`subtitle`,'reqclass', OLD.`reqclass`,'entryby', OLD.`entryby`,'entrytime', OLD.`entrytime`),
            JSON_OBJECT('id', NEW.`id`,'classname', NEW.`classname`,'subcode', NEW.`subcode`,'uniqid', NEW.`uniqid`,'order1', NEW.`order1`,'order2', NEW.`order2`,'chapter', NEW.`chapter`,'title', NEW.`title`,'subchapter', NEW.`subchapter`,'subtitle', NEW.`subtitle`,'reqclass', NEW.`reqclass`,'entryby', NEW.`entryby`,'entrytime', NEW.`entrytime`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER textbook_after_delete
    AFTER DELETE ON `textbook`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, changed_by)
        VALUES(
            'textbook',
            'DELETE',
            OLD.`id`,
            JSON_OBJECT('id', OLD.`id`,'classname', OLD.`classname`,'subcode', OLD.`subcode`,'uniqid', OLD.`uniqid`,'order1', OLD.`order1`,'order2', OLD.`order2`,'chapter', OLD.`chapter`,'title', OLD.`title`,'subchapter', OLD.`subchapter`,'subtitle', OLD.`subtitle`,'reqclass', OLD.`reqclass`,'entryby', OLD.`entryby`,'entrytime', OLD.`entrytime`),
            COALESCE(@current_user,'system')
        );
    END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `todolist`
--

DROP TABLE IF EXISTS `todolist`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `todolist` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sccode` int(11) DEFAULT NULL,
  `date` date DEFAULT NULL,
  `user` varchar(100) DEFAULT NULL,
  `todotype` varchar(50) DEFAULT NULL,
  `descrip1` varchar(150) DEFAULT NULL,
  `descrip2` varchar(150) DEFAULT NULL,
  `status` int(11) DEFAULT 0,
  `creationtime` datetime DEFAULT NULL,
  `response` varchar(50) DEFAULT NULL,
  `responsetxt` varchar(20) DEFAULT NULL,
  `responsetime` datetime DEFAULT NULL,
  `modifieddate` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6197 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
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
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER todolist_after_insert
    AFTER INSERT ON `todolist`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, new_data, changed_by)
        VALUES(
            'todolist',
            'INSERT',
            NEW.`id`,
            JSON_OBJECT('id', NEW.`id`,'sccode', NEW.`sccode`,'date', NEW.`date`,'user', NEW.`user`,'todotype', NEW.`todotype`,'descrip1', NEW.`descrip1`,'descrip2', NEW.`descrip2`,'status', NEW.`status`,'creationtime', NEW.`creationtime`,'response', NEW.`response`,'responsetxt', NEW.`responsetxt`,'responsetime', NEW.`responsetime`,'modifieddate', NEW.`modifieddate`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER todolist_after_update
    AFTER UPDATE ON `todolist`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, new_data, changed_by)
        VALUES(
            'todolist',
            'UPDATE',
            NEW.`id`,
            JSON_OBJECT('id', OLD.`id`,'sccode', OLD.`sccode`,'date', OLD.`date`,'user', OLD.`user`,'todotype', OLD.`todotype`,'descrip1', OLD.`descrip1`,'descrip2', OLD.`descrip2`,'status', OLD.`status`,'creationtime', OLD.`creationtime`,'response', OLD.`response`,'responsetxt', OLD.`responsetxt`,'responsetime', OLD.`responsetime`,'modifieddate', OLD.`modifieddate`),
            JSON_OBJECT('id', NEW.`id`,'sccode', NEW.`sccode`,'date', NEW.`date`,'user', NEW.`user`,'todotype', NEW.`todotype`,'descrip1', NEW.`descrip1`,'descrip2', NEW.`descrip2`,'status', NEW.`status`,'creationtime', NEW.`creationtime`,'response', NEW.`response`,'responsetxt', NEW.`responsetxt`,'responsetime', NEW.`responsetime`,'modifieddate', NEW.`modifieddate`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER todolist_after_delete
    AFTER DELETE ON `todolist`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, changed_by)
        VALUES(
            'todolist',
            'DELETE',
            OLD.`id`,
            JSON_OBJECT('id', OLD.`id`,'sccode', OLD.`sccode`,'date', OLD.`date`,'user', OLD.`user`,'todotype', OLD.`todotype`,'descrip1', OLD.`descrip1`,'descrip2', OLD.`descrip2`,'status', OLD.`status`,'creationtime', OLD.`creationtime`,'response', OLD.`response`,'responsetxt', OLD.`responsetxt`,'responsetime', OLD.`responsetime`,'modifieddate', OLD.`modifieddate`),
            COALESCE(@current_user,'system')
        );
    END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `trackbook`
--

DROP TABLE IF EXISTS `trackbook`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `trackbook` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sccode` varchar(10) DEFAULT NULL,
  `email` varchar(120) DEFAULT NULL,
  `module` varchar(30) DEFAULT NULL,
  `page` varchar(50) DEFAULT NULL,
  `action` varchar(100) DEFAULT NULL,
  `notes` varchar(150) DEFAULT NULL,
  `actiontime` datetime DEFAULT NULL,
  `ipaddress` varchar(20) DEFAULT NULL,
  `browser` varchar(20) DEFAULT NULL,
  `platform` varchar(10) DEFAULT NULL,
  `modifieddate` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=507 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
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
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER trackbook_after_insert
    AFTER INSERT ON `trackbook`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, new_data, changed_by)
        VALUES(
            'trackbook',
            'INSERT',
            NEW.`id`,
            JSON_OBJECT('id', NEW.`id`,'sccode', NEW.`sccode`,'email', NEW.`email`,'module', NEW.`module`,'page', NEW.`page`,'action', NEW.`action`,'notes', NEW.`notes`,'actiontime', NEW.`actiontime`,'ipaddress', NEW.`ipaddress`,'browser', NEW.`browser`,'platform', NEW.`platform`,'modifieddate', NEW.`modifieddate`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER trackbook_after_update
    AFTER UPDATE ON `trackbook`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, new_data, changed_by)
        VALUES(
            'trackbook',
            'UPDATE',
            NEW.`id`,
            JSON_OBJECT('id', OLD.`id`,'sccode', OLD.`sccode`,'email', OLD.`email`,'module', OLD.`module`,'page', OLD.`page`,'action', OLD.`action`,'notes', OLD.`notes`,'actiontime', OLD.`actiontime`,'ipaddress', OLD.`ipaddress`,'browser', OLD.`browser`,'platform', OLD.`platform`,'modifieddate', OLD.`modifieddate`),
            JSON_OBJECT('id', NEW.`id`,'sccode', NEW.`sccode`,'email', NEW.`email`,'module', NEW.`module`,'page', NEW.`page`,'action', NEW.`action`,'notes', NEW.`notes`,'actiontime', NEW.`actiontime`,'ipaddress', NEW.`ipaddress`,'browser', NEW.`browser`,'platform', NEW.`platform`,'modifieddate', NEW.`modifieddate`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER trackbook_after_delete
    AFTER DELETE ON `trackbook`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, changed_by)
        VALUES(
            'trackbook',
            'DELETE',
            OLD.`id`,
            JSON_OBJECT('id', OLD.`id`,'sccode', OLD.`sccode`,'email', OLD.`email`,'module', OLD.`module`,'page', OLD.`page`,'action', OLD.`action`,'notes', OLD.`notes`,'actiontime', OLD.`actiontime`,'ipaddress', OLD.`ipaddress`,'browser', OLD.`browser`,'platform', OLD.`platform`,'modifieddate', OLD.`modifieddate`),
            COALESCE(@current_user,'system')
        );
    END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `transaction`
--

DROP TABLE IF EXISTS `transaction`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `transaction` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sccode` int(11) DEFAULT NULL,
  `sessionyear` int(11) DEFAULT NULL,
  `classname` varchar(20) NOT NULL,
  `sectionname` varchar(50) NOT NULL,
  `date` date DEFAULT NULL,
  `receivedby` varchar(120) DEFAULT NULL,
  `receivedfrom` varchar(120) DEFAULT NULL,
  `amount` int(11) DEFAULT NULL,
  `entrytime` datetime DEFAULT NULL,
  `bankaccid` int(11) DEFAULT NULL,
  `modifieddate` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=213 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
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
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER transaction_after_insert
    AFTER INSERT ON `transaction`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, new_data, changed_by)
        VALUES(
            'transaction',
            'INSERT',
            NEW.`id`,
            JSON_OBJECT('id', NEW.`id`,'sccode', NEW.`sccode`,'sessionyear', NEW.`sessionyear`,'classname', NEW.`classname`,'sectionname', NEW.`sectionname`,'date', NEW.`date`,'receivedby', NEW.`receivedby`,'receivedfrom', NEW.`receivedfrom`,'amount', NEW.`amount`,'entrytime', NEW.`entrytime`,'bankaccid', NEW.`bankaccid`,'modifieddate', NEW.`modifieddate`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER transaction_after_update
    AFTER UPDATE ON `transaction`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, new_data, changed_by)
        VALUES(
            'transaction',
            'UPDATE',
            NEW.`id`,
            JSON_OBJECT('id', OLD.`id`,'sccode', OLD.`sccode`,'sessionyear', OLD.`sessionyear`,'classname', OLD.`classname`,'sectionname', OLD.`sectionname`,'date', OLD.`date`,'receivedby', OLD.`receivedby`,'receivedfrom', OLD.`receivedfrom`,'amount', OLD.`amount`,'entrytime', OLD.`entrytime`,'bankaccid', OLD.`bankaccid`,'modifieddate', OLD.`modifieddate`),
            JSON_OBJECT('id', NEW.`id`,'sccode', NEW.`sccode`,'sessionyear', NEW.`sessionyear`,'classname', NEW.`classname`,'sectionname', NEW.`sectionname`,'date', NEW.`date`,'receivedby', NEW.`receivedby`,'receivedfrom', NEW.`receivedfrom`,'amount', NEW.`amount`,'entrytime', NEW.`entrytime`,'bankaccid', NEW.`bankaccid`,'modifieddate', NEW.`modifieddate`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER transaction_after_delete
    AFTER DELETE ON `transaction`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, changed_by)
        VALUES(
            'transaction',
            'DELETE',
            OLD.`id`,
            JSON_OBJECT('id', OLD.`id`,'sccode', OLD.`sccode`,'sessionyear', OLD.`sessionyear`,'classname', OLD.`classname`,'sectionname', OLD.`sectionname`,'date', OLD.`date`,'receivedby', OLD.`receivedby`,'receivedfrom', OLD.`receivedfrom`,'amount', OLD.`amount`,'entrytime', OLD.`entrytime`,'bankaccid', OLD.`bankaccid`,'modifieddate', OLD.`modifieddate`),
            COALESCE(@current_user,'system')
        );
    END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `transaction_details`
--

DROP TABLE IF EXISTS `transaction_details`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `transaction_details` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sccode` int(11) DEFAULT NULL,
  `user` varchar(120) DEFAULT NULL,
  `date` date DEFAULT NULL,
  `income` double NOT NULL DEFAULT 0,
  `stpr` double NOT NULL DEFAULT 0,
  `withdrawal` double NOT NULL DEFAULT 0,
  `deposit` double NOT NULL DEFAULT 0,
  `expenditure` double NOT NULL DEFAULT 0,
  `trans_in` double NOT NULL DEFAULT 0,
  `trans_out` double NOT NULL DEFAULT 0,
  `balance` double NOT NULL DEFAULT 0,
  `entrytime` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7022 DEFAULT CHARSET=utf32 COLLATE=utf32_unicode_ci;
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
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER transaction_details_after_insert
    AFTER INSERT ON `transaction_details`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, new_data, changed_by)
        VALUES(
            'transaction_details',
            'INSERT',
            NEW.`id`,
            JSON_OBJECT('id', NEW.`id`,'sccode', NEW.`sccode`,'user', NEW.`user`,'date', NEW.`date`,'income', NEW.`income`,'stpr', NEW.`stpr`,'withdrawal', NEW.`withdrawal`,'deposit', NEW.`deposit`,'expenditure', NEW.`expenditure`,'trans_in', NEW.`trans_in`,'trans_out', NEW.`trans_out`,'balance', NEW.`balance`,'entrytime', NEW.`entrytime`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER transaction_details_after_update
    AFTER UPDATE ON `transaction_details`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, new_data, changed_by)
        VALUES(
            'transaction_details',
            'UPDATE',
            NEW.`id`,
            JSON_OBJECT('id', OLD.`id`,'sccode', OLD.`sccode`,'user', OLD.`user`,'date', OLD.`date`,'income', OLD.`income`,'stpr', OLD.`stpr`,'withdrawal', OLD.`withdrawal`,'deposit', OLD.`deposit`,'expenditure', OLD.`expenditure`,'trans_in', OLD.`trans_in`,'trans_out', OLD.`trans_out`,'balance', OLD.`balance`,'entrytime', OLD.`entrytime`),
            JSON_OBJECT('id', NEW.`id`,'sccode', NEW.`sccode`,'user', NEW.`user`,'date', NEW.`date`,'income', NEW.`income`,'stpr', NEW.`stpr`,'withdrawal', NEW.`withdrawal`,'deposit', NEW.`deposit`,'expenditure', NEW.`expenditure`,'trans_in', NEW.`trans_in`,'trans_out', NEW.`trans_out`,'balance', NEW.`balance`,'entrytime', NEW.`entrytime`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER transaction_details_after_delete
    AFTER DELETE ON `transaction_details`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, changed_by)
        VALUES(
            'transaction_details',
            'DELETE',
            OLD.`id`,
            JSON_OBJECT('id', OLD.`id`,'sccode', OLD.`sccode`,'user', OLD.`user`,'date', OLD.`date`,'income', OLD.`income`,'stpr', OLD.`stpr`,'withdrawal', OLD.`withdrawal`,'deposit', OLD.`deposit`,'expenditure', OLD.`expenditure`,'trans_in', OLD.`trans_in`,'trans_out', OLD.`trans_out`,'balance', OLD.`balance`,'entrytime', OLD.`entrytime`),
            COALESCE(@current_user,'system')
        );
    END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `transaction_tracker`
--

DROP TABLE IF EXISTS `transaction_tracker`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `transaction_tracker` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sccode` int(11) DEFAULT NULL,
  `date` date DEFAULT NULL,
  `user` varchar(120) DEFAULT NULL,
  `deposit` double DEFAULT 0,
  `dispuch` double DEFAULT 0,
  `stpr` double NOT NULL DEFAULT 0,
  `balance` double NOT NULL DEFAULT 0,
  `entrytime` datetime DEFAULT NULL,
  `modifieddate` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1309 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
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
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER transaction_tracker_after_insert
    AFTER INSERT ON `transaction_tracker`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, new_data, changed_by)
        VALUES(
            'transaction_tracker',
            'INSERT',
            NEW.`id`,
            JSON_OBJECT('id', NEW.`id`,'sccode', NEW.`sccode`,'date', NEW.`date`,'user', NEW.`user`,'deposit', NEW.`deposit`,'dispuch', NEW.`dispuch`,'stpr', NEW.`stpr`,'balance', NEW.`balance`,'entrytime', NEW.`entrytime`,'modifieddate', NEW.`modifieddate`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER transaction_tracker_after_update
    AFTER UPDATE ON `transaction_tracker`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, new_data, changed_by)
        VALUES(
            'transaction_tracker',
            'UPDATE',
            NEW.`id`,
            JSON_OBJECT('id', OLD.`id`,'sccode', OLD.`sccode`,'date', OLD.`date`,'user', OLD.`user`,'deposit', OLD.`deposit`,'dispuch', OLD.`dispuch`,'stpr', OLD.`stpr`,'balance', OLD.`balance`,'entrytime', OLD.`entrytime`,'modifieddate', OLD.`modifieddate`),
            JSON_OBJECT('id', NEW.`id`,'sccode', NEW.`sccode`,'date', NEW.`date`,'user', NEW.`user`,'deposit', NEW.`deposit`,'dispuch', NEW.`dispuch`,'stpr', NEW.`stpr`,'balance', NEW.`balance`,'entrytime', NEW.`entrytime`,'modifieddate', NEW.`modifieddate`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER transaction_tracker_after_delete
    AFTER DELETE ON `transaction_tracker`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, changed_by)
        VALUES(
            'transaction_tracker',
            'DELETE',
            OLD.`id`,
            JSON_OBJECT('id', OLD.`id`,'sccode', OLD.`sccode`,'date', OLD.`date`,'user', OLD.`user`,'deposit', OLD.`deposit`,'dispuch', OLD.`dispuch`,'stpr', OLD.`stpr`,'balance', OLD.`balance`,'entrytime', OLD.`entrytime`,'modifieddate', OLD.`modifieddate`),
            COALESCE(@current_user,'system')
        );
    END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `tsalery`
--

DROP TABLE IF EXISTS `tsalery`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tsalery` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sccode` int(11) NOT NULL,
  `tid` int(11) NOT NULL,
  `salery` int(11) NOT NULL,
  `salmonth` int(11) NOT NULL,
  `salyear` int(11) NOT NULL,
  `late` int(11) NOT NULL,
  `casual` int(11) NOT NULL,
  `absent` int(11) NOT NULL,
  `workday` int(11) NOT NULL,
  `silent` int(11) NOT NULL,
  `present` int(11) NOT NULL,
  `proxy` int(11) NOT NULL,
  `netsalery` int(11) NOT NULL,
  `payment` int(11) NOT NULL,
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
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER tsalery_after_insert
    AFTER INSERT ON `tsalery`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, new_data, changed_by)
        VALUES(
            'tsalery',
            'INSERT',
            NEW.`id`,
            JSON_OBJECT('id', NEW.`id`,'sccode', NEW.`sccode`,'tid', NEW.`tid`,'salery', NEW.`salery`,'salmonth', NEW.`salmonth`,'salyear', NEW.`salyear`,'late', NEW.`late`,'casual', NEW.`casual`,'absent', NEW.`absent`,'workday', NEW.`workday`,'silent', NEW.`silent`,'present', NEW.`present`,'proxy', NEW.`proxy`,'netsalery', NEW.`netsalery`,'payment', NEW.`payment`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER tsalery_after_update
    AFTER UPDATE ON `tsalery`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, new_data, changed_by)
        VALUES(
            'tsalery',
            'UPDATE',
            NEW.`id`,
            JSON_OBJECT('id', OLD.`id`,'sccode', OLD.`sccode`,'tid', OLD.`tid`,'salery', OLD.`salery`,'salmonth', OLD.`salmonth`,'salyear', OLD.`salyear`,'late', OLD.`late`,'casual', OLD.`casual`,'absent', OLD.`absent`,'workday', OLD.`workday`,'silent', OLD.`silent`,'present', OLD.`present`,'proxy', OLD.`proxy`,'netsalery', OLD.`netsalery`,'payment', OLD.`payment`),
            JSON_OBJECT('id', NEW.`id`,'sccode', NEW.`sccode`,'tid', NEW.`tid`,'salery', NEW.`salery`,'salmonth', NEW.`salmonth`,'salyear', NEW.`salyear`,'late', NEW.`late`,'casual', NEW.`casual`,'absent', NEW.`absent`,'workday', NEW.`workday`,'silent', NEW.`silent`,'present', NEW.`present`,'proxy', NEW.`proxy`,'netsalery', NEW.`netsalery`,'payment', NEW.`payment`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER tsalery_after_delete
    AFTER DELETE ON `tsalery`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, changed_by)
        VALUES(
            'tsalery',
            'DELETE',
            OLD.`id`,
            JSON_OBJECT('id', OLD.`id`,'sccode', OLD.`sccode`,'tid', OLD.`tid`,'salery', OLD.`salery`,'salmonth', OLD.`salmonth`,'salyear', OLD.`salyear`,'late', OLD.`late`,'casual', OLD.`casual`,'absent', OLD.`absent`,'workday', OLD.`workday`,'silent', OLD.`silent`,'present', OLD.`present`,'proxy', OLD.`proxy`,'netsalery', OLD.`netsalery`,'payment', OLD.`payment`),
            COALESCE(@current_user,'system')
        );
    END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `user_badges`
--

DROP TABLE IF EXISTS `user_badges`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_badges` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(120) DEFAULT NULL,
  `badge_id` int(11) DEFAULT NULL,
  `earned_date` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf32 COLLATE=utf32_unicode_ci;
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
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER user_badges_after_insert
    AFTER INSERT ON `user_badges`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, new_data, changed_by)
        VALUES(
            'user_badges',
            'INSERT',
            NEW.`id`,
            JSON_OBJECT('id', NEW.`id`,'email', NEW.`email`,'badge_id', NEW.`badge_id`,'earned_date', NEW.`earned_date`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER user_badges_after_update
    AFTER UPDATE ON `user_badges`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, new_data, changed_by)
        VALUES(
            'user_badges',
            'UPDATE',
            NEW.`id`,
            JSON_OBJECT('id', OLD.`id`,'email', OLD.`email`,'badge_id', OLD.`badge_id`,'earned_date', OLD.`earned_date`),
            JSON_OBJECT('id', NEW.`id`,'email', NEW.`email`,'badge_id', NEW.`badge_id`,'earned_date', NEW.`earned_date`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER user_badges_after_delete
    AFTER DELETE ON `user_badges`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, changed_by)
        VALUES(
            'user_badges',
            'DELETE',
            OLD.`id`,
            JSON_OBJECT('id', OLD.`id`,'email', OLD.`email`,'badge_id', OLD.`badge_id`,'earned_date', OLD.`earned_date`),
            COALESCE(@current_user,'system')
        );
    END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `user_level`
--

DROP TABLE IF EXISTS `user_level`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_level` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `levelname` varchar(20) NOT NULL,
  `levelrank` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
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
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER user_level_after_insert
    AFTER INSERT ON `user_level`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, new_data, changed_by)
        VALUES(
            'user_level',
            'INSERT',
            NEW.`id`,
            JSON_OBJECT('id', NEW.`id`,'levelname', NEW.`levelname`,'levelrank', NEW.`levelrank`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER user_level_after_update
    AFTER UPDATE ON `user_level`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, new_data, changed_by)
        VALUES(
            'user_level',
            'UPDATE',
            NEW.`id`,
            JSON_OBJECT('id', OLD.`id`,'levelname', OLD.`levelname`,'levelrank', OLD.`levelrank`),
            JSON_OBJECT('id', NEW.`id`,'levelname', NEW.`levelname`,'levelrank', NEW.`levelrank`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER user_level_after_delete
    AFTER DELETE ON `user_level`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, changed_by)
        VALUES(
            'user_level',
            'DELETE',
            OLD.`id`,
            JSON_OBJECT('id', OLD.`id`,'levelname', OLD.`levelname`,'levelrank', OLD.`levelrank`),
            COALESCE(@current_user,'system')
        );
    END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `user_xp_log`
--

DROP TABLE IF EXISTS `user_xp_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_xp_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `email` varchar(120) DEFAULT NULL,
  `xp` int(11) NOT NULL,
  `reason` varchar(255) DEFAULT NULL,
  `date` date DEFAULT NULL,
  `task_id` varchar(100) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_user_task` (`user_id`,`task_id`),
  CONSTRAINT `user_xp_log_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `usersapp` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
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
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER user_xp_log_after_insert
    AFTER INSERT ON `user_xp_log`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, new_data, changed_by)
        VALUES(
            'user_xp_log',
            'INSERT',
            NEW.`id`,
            JSON_OBJECT('id', NEW.`id`,'user_id', NEW.`user_id`,'email', NEW.`email`,'xp', NEW.`xp`,'reason', NEW.`reason`,'date', NEW.`date`,'task_id', NEW.`task_id`,'created_at', NEW.`created_at`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER user_xp_log_after_update
    AFTER UPDATE ON `user_xp_log`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, new_data, changed_by)
        VALUES(
            'user_xp_log',
            'UPDATE',
            NEW.`id`,
            JSON_OBJECT('id', OLD.`id`,'user_id', OLD.`user_id`,'email', OLD.`email`,'xp', OLD.`xp`,'reason', OLD.`reason`,'date', OLD.`date`,'task_id', OLD.`task_id`,'created_at', OLD.`created_at`),
            JSON_OBJECT('id', NEW.`id`,'user_id', NEW.`user_id`,'email', NEW.`email`,'xp', NEW.`xp`,'reason', NEW.`reason`,'date', NEW.`date`,'task_id', NEW.`task_id`,'created_at', NEW.`created_at`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER user_xp_log_after_delete
    AFTER DELETE ON `user_xp_log`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, changed_by)
        VALUES(
            'user_xp_log',
            'DELETE',
            OLD.`id`,
            JSON_OBJECT('id', OLD.`id`,'user_id', OLD.`user_id`,'email', OLD.`email`,'xp', OLD.`xp`,'reason', OLD.`reason`,'date', OLD.`date`,'task_id', OLD.`task_id`,'created_at', OLD.`created_at`),
            COALESCE(@current_user,'system')
        );
    END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `userlog`
--

DROP TABLE IF EXISTS `userlog`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `userlog` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ipadd` varchar(15) NOT NULL,
  `date` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=49985 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
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
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER userlog_after_insert
    AFTER INSERT ON `userlog`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, new_data, changed_by)
        VALUES(
            'userlog',
            'INSERT',
            NEW.`id`,
            JSON_OBJECT('id', NEW.`id`,'ipadd', NEW.`ipadd`,'date', NEW.`date`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER userlog_after_update
    AFTER UPDATE ON `userlog`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, new_data, changed_by)
        VALUES(
            'userlog',
            'UPDATE',
            NEW.`id`,
            JSON_OBJECT('id', OLD.`id`,'ipadd', OLD.`ipadd`,'date', OLD.`date`),
            JSON_OBJECT('id', NEW.`id`,'ipadd', NEW.`ipadd`,'date', NEW.`date`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER userlog_after_delete
    AFTER DELETE ON `userlog`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, changed_by)
        VALUES(
            'userlog',
            'DELETE',
            OLD.`id`,
            JSON_OBJECT('id', OLD.`id`,'ipadd', OLD.`ipadd`,'date', OLD.`date`),
            COALESCE(@current_user,'system')
        );
    END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `user_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'auto incrementing user_id of each user, unique index',
  `user_name` varchar(64) NOT NULL COMMENT 'user''s name, unique',
  `user_password_hash` varchar(255) NOT NULL COMMENT 'user''s password in salted and hashed format',
  `user_email` varchar(64) NOT NULL COMMENT 'user''s email, unique',
  `user_active` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'user''s activation status',
  `user_activation_hash` varchar(40) DEFAULT NULL COMMENT 'user''s email verification hash string',
  `user_password_reset_hash` char(40) DEFAULT NULL COMMENT 'user''s password reset code',
  `user_password_reset_timestamp` bigint(20) DEFAULT NULL COMMENT 'timestamp of the password reset request',
  `user_rememberme_token` varchar(64) DEFAULT NULL COMMENT 'user''s remember-me cookie token',
  `user_failed_logins` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'user''s failed login attemps',
  `user_last_failed_login` int(11) DEFAULT NULL COMMENT 'unix timestamp of last failed login attempt',
  `user_registration_datetime` datetime DEFAULT NULL,
  `user_registration_ip` varchar(39) NOT NULL DEFAULT '0.0.0.0',
  `user_group` varchar(100) NOT NULL DEFAULT 'Headmaster',
  `einame` varchar(100) NOT NULL,
  `eiaddress` varchar(255) NOT NULL,
  `eicontact` varchar(11) NOT NULL,
  `eiin` varchar(6) NOT NULL DEFAULT '000000',
  `fullname` varchar(100) NOT NULL,
  `ei_category` varchar(30) NOT NULL,
  `eidist` varchar(20) DEFAULT NULL,
  `eips` varchar(20) DEFAULT NULL,
  `eipo` varchar(20) DEFAULT NULL,
  `user_level` int(11) DEFAULT 100,
  `applyas` varchar(20) DEFAULT NULL,
  `applytime` timestamp NOT NULL DEFAULT current_timestamp(),
  `shortname` varchar(15) NOT NULL,
  `smsprice` float NOT NULL DEFAULT 0.85,
  `holiday` varchar(10) NOT NULL DEFAULT 'Friday',
  `estd` int(11) NOT NULL,
  `htspeech` longtext NOT NULL,
  `history` longtext NOT NULL,
  `expiredate` timestamp NOT NULL DEFAULT current_timestamp(),
  `eiintype` varchar(20) NOT NULL,
  `mobilechairman` varchar(11) NOT NULL,
  `mobilethird` varchar(11) NOT NULL,
  `smstemp` int(11) NOT NULL DEFAULT 1,
  `eimboxid` varchar(10) NOT NULL,
  `token` text DEFAULT NULL,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `user_name` (`user_name`),
  UNIQUE KEY `user_email` (`user_email`),
  FULLTEXT KEY `user_password_hash` (`user_password_hash`)
) ENGINE=InnoDB AUTO_INCREMENT=227 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='user data';
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
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER users_after_insert
    AFTER INSERT ON `users`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, new_data, changed_by)
        VALUES(
            'users',
            'INSERT',
            NEW.`user_id`,
            JSON_OBJECT('user_id', NEW.`user_id`,'user_name', NEW.`user_name`,'user_password_hash', NEW.`user_password_hash`,'user_email', NEW.`user_email`,'user_active', NEW.`user_active`,'user_activation_hash', NEW.`user_activation_hash`,'user_password_reset_hash', NEW.`user_password_reset_hash`,'user_password_reset_timestamp', NEW.`user_password_reset_timestamp`,'user_rememberme_token', NEW.`user_rememberme_token`,'user_failed_logins', NEW.`user_failed_logins`,'user_last_failed_login', NEW.`user_last_failed_login`,'user_registration_datetime', NEW.`user_registration_datetime`,'user_registration_ip', NEW.`user_registration_ip`,'user_group', NEW.`user_group`,'einame', NEW.`einame`,'eiaddress', NEW.`eiaddress`,'eicontact', NEW.`eicontact`,'eiin', NEW.`eiin`,'fullname', NEW.`fullname`,'ei_category', NEW.`ei_category`,'eidist', NEW.`eidist`,'eips', NEW.`eips`,'eipo', NEW.`eipo`,'user_level', NEW.`user_level`,'applyas', NEW.`applyas`,'applytime', NEW.`applytime`,'shortname', NEW.`shortname`,'smsprice', NEW.`smsprice`,'holiday', NEW.`holiday`,'estd', NEW.`estd`,'htspeech', NEW.`htspeech`,'history', NEW.`history`,'expiredate', NEW.`expiredate`,'eiintype', NEW.`eiintype`,'mobilechairman', NEW.`mobilechairman`,'mobilethird', NEW.`mobilethird`,'smstemp', NEW.`smstemp`,'eimboxid', NEW.`eimboxid`,'token', NEW.`token`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER users_after_update
    AFTER UPDATE ON `users`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, new_data, changed_by)
        VALUES(
            'users',
            'UPDATE',
            NEW.`user_id`,
            JSON_OBJECT('user_id', OLD.`user_id`,'user_name', OLD.`user_name`,'user_password_hash', OLD.`user_password_hash`,'user_email', OLD.`user_email`,'user_active', OLD.`user_active`,'user_activation_hash', OLD.`user_activation_hash`,'user_password_reset_hash', OLD.`user_password_reset_hash`,'user_password_reset_timestamp', OLD.`user_password_reset_timestamp`,'user_rememberme_token', OLD.`user_rememberme_token`,'user_failed_logins', OLD.`user_failed_logins`,'user_last_failed_login', OLD.`user_last_failed_login`,'user_registration_datetime', OLD.`user_registration_datetime`,'user_registration_ip', OLD.`user_registration_ip`,'user_group', OLD.`user_group`,'einame', OLD.`einame`,'eiaddress', OLD.`eiaddress`,'eicontact', OLD.`eicontact`,'eiin', OLD.`eiin`,'fullname', OLD.`fullname`,'ei_category', OLD.`ei_category`,'eidist', OLD.`eidist`,'eips', OLD.`eips`,'eipo', OLD.`eipo`,'user_level', OLD.`user_level`,'applyas', OLD.`applyas`,'applytime', OLD.`applytime`,'shortname', OLD.`shortname`,'smsprice', OLD.`smsprice`,'holiday', OLD.`holiday`,'estd', OLD.`estd`,'htspeech', OLD.`htspeech`,'history', OLD.`history`,'expiredate', OLD.`expiredate`,'eiintype', OLD.`eiintype`,'mobilechairman', OLD.`mobilechairman`,'mobilethird', OLD.`mobilethird`,'smstemp', OLD.`smstemp`,'eimboxid', OLD.`eimboxid`,'token', OLD.`token`),
            JSON_OBJECT('user_id', NEW.`user_id`,'user_name', NEW.`user_name`,'user_password_hash', NEW.`user_password_hash`,'user_email', NEW.`user_email`,'user_active', NEW.`user_active`,'user_activation_hash', NEW.`user_activation_hash`,'user_password_reset_hash', NEW.`user_password_reset_hash`,'user_password_reset_timestamp', NEW.`user_password_reset_timestamp`,'user_rememberme_token', NEW.`user_rememberme_token`,'user_failed_logins', NEW.`user_failed_logins`,'user_last_failed_login', NEW.`user_last_failed_login`,'user_registration_datetime', NEW.`user_registration_datetime`,'user_registration_ip', NEW.`user_registration_ip`,'user_group', NEW.`user_group`,'einame', NEW.`einame`,'eiaddress', NEW.`eiaddress`,'eicontact', NEW.`eicontact`,'eiin', NEW.`eiin`,'fullname', NEW.`fullname`,'ei_category', NEW.`ei_category`,'eidist', NEW.`eidist`,'eips', NEW.`eips`,'eipo', NEW.`eipo`,'user_level', NEW.`user_level`,'applyas', NEW.`applyas`,'applytime', NEW.`applytime`,'shortname', NEW.`shortname`,'smsprice', NEW.`smsprice`,'holiday', NEW.`holiday`,'estd', NEW.`estd`,'htspeech', NEW.`htspeech`,'history', NEW.`history`,'expiredate', NEW.`expiredate`,'eiintype', NEW.`eiintype`,'mobilechairman', NEW.`mobilechairman`,'mobilethird', NEW.`mobilethird`,'smstemp', NEW.`smstemp`,'eimboxid', NEW.`eimboxid`,'token', NEW.`token`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER users_after_delete
    AFTER DELETE ON `users`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, changed_by)
        VALUES(
            'users',
            'DELETE',
            OLD.`user_id`,
            JSON_OBJECT('user_id', OLD.`user_id`,'user_name', OLD.`user_name`,'user_password_hash', OLD.`user_password_hash`,'user_email', OLD.`user_email`,'user_active', OLD.`user_active`,'user_activation_hash', OLD.`user_activation_hash`,'user_password_reset_hash', OLD.`user_password_reset_hash`,'user_password_reset_timestamp', OLD.`user_password_reset_timestamp`,'user_rememberme_token', OLD.`user_rememberme_token`,'user_failed_logins', OLD.`user_failed_logins`,'user_last_failed_login', OLD.`user_last_failed_login`,'user_registration_datetime', OLD.`user_registration_datetime`,'user_registration_ip', OLD.`user_registration_ip`,'user_group', OLD.`user_group`,'einame', OLD.`einame`,'eiaddress', OLD.`eiaddress`,'eicontact', OLD.`eicontact`,'eiin', OLD.`eiin`,'fullname', OLD.`fullname`,'ei_category', OLD.`ei_category`,'eidist', OLD.`eidist`,'eips', OLD.`eips`,'eipo', OLD.`eipo`,'user_level', OLD.`user_level`,'applyas', OLD.`applyas`,'applytime', OLD.`applytime`,'shortname', OLD.`shortname`,'smsprice', OLD.`smsprice`,'holiday', OLD.`holiday`,'estd', OLD.`estd`,'htspeech', OLD.`htspeech`,'history', OLD.`history`,'expiredate', OLD.`expiredate`,'eiintype', OLD.`eiintype`,'mobilechairman', OLD.`mobilechairman`,'mobilethird', OLD.`mobilethird`,'smstemp', OLD.`smstemp`,'eimboxid', OLD.`eimboxid`,'token', OLD.`token`),
            COALESCE(@current_user,'system')
        );
    END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

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
  `hash_salt_key` varchar(255) NOT NULL DEFAULT 'cklpns',
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
  `username` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=951 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
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
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER usersapp_after_insert
    AFTER INSERT ON `usersapp`
    FOR EACH ROW
    BEGIN
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER usersapp_after_update
    AFTER UPDATE ON `usersapp`
    FOR EACH ROW
    BEGIN
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER usersapp_after_delete
    AFTER DELETE ON `usersapp`
    FOR EACH ROW
    BEGIN
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
-- Table structure for table `usersrequest`
--

DROP TABLE IF EXISTS `usersrequest`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `usersrequest` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usertype` varchar(15) DEFAULT NULL,
  `userid` varchar(15) DEFAULT NULL,
  `dob` date DEFAULT NULL,
  `mobile` varchar(11) DEFAULT NULL,
  `email` varchar(120) DEFAULT NULL,
  `passpin` varchar(32) DEFAULT NULL,
  `submittime` datetime DEFAULT NULL,
  `ipaddress` varchar(15) DEFAULT NULL,
  `useragent` varchar(255) DEFAULT NULL,
  `sccode` int(11) DEFAULT NULL,
  `otp` varchar(10) DEFAULT NULL,
  `otptime` datetime DEFAULT NULL,
  `status` varchar(20) DEFAULT NULL,
  `responsetime` datetime DEFAULT NULL,
  `responseby` varchar(120) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=41 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
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
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER usersrequest_after_insert
    AFTER INSERT ON `usersrequest`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, new_data, changed_by)
        VALUES(
            'usersrequest',
            'INSERT',
            NEW.`id`,
            JSON_OBJECT('id', NEW.`id`,'usertype', NEW.`usertype`,'userid', NEW.`userid`,'dob', NEW.`dob`,'mobile', NEW.`mobile`,'email', NEW.`email`,'passpin', NEW.`passpin`,'submittime', NEW.`submittime`,'ipaddress', NEW.`ipaddress`,'useragent', NEW.`useragent`,'sccode', NEW.`sccode`,'otp', NEW.`otp`,'otptime', NEW.`otptime`,'status', NEW.`status`,'responsetime', NEW.`responsetime`,'responseby', NEW.`responseby`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER usersrequest_after_update
    AFTER UPDATE ON `usersrequest`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, new_data, changed_by)
        VALUES(
            'usersrequest',
            'UPDATE',
            NEW.`id`,
            JSON_OBJECT('id', OLD.`id`,'usertype', OLD.`usertype`,'userid', OLD.`userid`,'dob', OLD.`dob`,'mobile', OLD.`mobile`,'email', OLD.`email`,'passpin', OLD.`passpin`,'submittime', OLD.`submittime`,'ipaddress', OLD.`ipaddress`,'useragent', OLD.`useragent`,'sccode', OLD.`sccode`,'otp', OLD.`otp`,'otptime', OLD.`otptime`,'status', OLD.`status`,'responsetime', OLD.`responsetime`,'responseby', OLD.`responseby`),
            JSON_OBJECT('id', NEW.`id`,'usertype', NEW.`usertype`,'userid', NEW.`userid`,'dob', NEW.`dob`,'mobile', NEW.`mobile`,'email', NEW.`email`,'passpin', NEW.`passpin`,'submittime', NEW.`submittime`,'ipaddress', NEW.`ipaddress`,'useragent', NEW.`useragent`,'sccode', NEW.`sccode`,'otp', NEW.`otp`,'otptime', NEW.`otptime`,'status', NEW.`status`,'responsetime', NEW.`responsetime`,'responseby', NEW.`responseby`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER usersrequest_after_delete
    AFTER DELETE ON `usersrequest`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, changed_by)
        VALUES(
            'usersrequest',
            'DELETE',
            OLD.`id`,
            JSON_OBJECT('id', OLD.`id`,'usertype', OLD.`usertype`,'userid', OLD.`userid`,'dob', OLD.`dob`,'mobile', OLD.`mobile`,'email', OLD.`email`,'passpin', OLD.`passpin`,'submittime', OLD.`submittime`,'ipaddress', OLD.`ipaddress`,'useragent', OLD.`useragent`,'sccode', OLD.`sccode`,'otp', OLD.`otp`,'otptime', OLD.`otptime`,'status', OLD.`status`,'responsetime', OLD.`responsetime`,'responseby', OLD.`responseby`),
            COALESCE(@current_user,'system')
        );
    END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `whatsnew`
--

DROP TABLE IF EXISTS `whatsnew`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `whatsnew` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` int(11) NOT NULL,
  `descrip` int(11) NOT NULL,
  `type` int(11) NOT NULL,
  `administrator` int(11) NOT NULL DEFAULT 0,
  `clsteacher` int(11) NOT NULL DEFAULT 0,
  `teacher` int(11) NOT NULL DEFAULT 0,
  `guardian` int(11) NOT NULL DEFAULT 0,
  `student` int(11) NOT NULL DEFAULT 0,
  `accountants` int(11) NOT NULL DEFAULT 0,
  `librarian` int(11) NOT NULL DEFAULT 0,
  `guest` int(11) NOT NULL DEFAULT 0,
  `icon` int(11) NOT NULL,
  `link` int(11) NOT NULL,
  `updatetime` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
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
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER whatsnew_after_insert
    AFTER INSERT ON `whatsnew`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, new_data, changed_by)
        VALUES(
            'whatsnew',
            'INSERT',
            NEW.`id`,
            JSON_OBJECT('id', NEW.`id`,'title', NEW.`title`,'descrip', NEW.`descrip`,'type', NEW.`type`,'administrator', NEW.`administrator`,'clsteacher', NEW.`clsteacher`,'teacher', NEW.`teacher`,'guardian', NEW.`guardian`,'student', NEW.`student`,'accountants', NEW.`accountants`,'librarian', NEW.`librarian`,'guest', NEW.`guest`,'icon', NEW.`icon`,'link', NEW.`link`,'updatetime', NEW.`updatetime`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER whatsnew_after_update
    AFTER UPDATE ON `whatsnew`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, new_data, changed_by)
        VALUES(
            'whatsnew',
            'UPDATE',
            NEW.`id`,
            JSON_OBJECT('id', OLD.`id`,'title', OLD.`title`,'descrip', OLD.`descrip`,'type', OLD.`type`,'administrator', OLD.`administrator`,'clsteacher', OLD.`clsteacher`,'teacher', OLD.`teacher`,'guardian', OLD.`guardian`,'student', OLD.`student`,'accountants', OLD.`accountants`,'librarian', OLD.`librarian`,'guest', OLD.`guest`,'icon', OLD.`icon`,'link', OLD.`link`,'updatetime', OLD.`updatetime`),
            JSON_OBJECT('id', NEW.`id`,'title', NEW.`title`,'descrip', NEW.`descrip`,'type', NEW.`type`,'administrator', NEW.`administrator`,'clsteacher', NEW.`clsteacher`,'teacher', NEW.`teacher`,'guardian', NEW.`guardian`,'student', NEW.`student`,'accountants', NEW.`accountants`,'librarian', NEW.`librarian`,'guest', NEW.`guest`,'icon', NEW.`icon`,'link', NEW.`link`,'updatetime', NEW.`updatetime`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER whatsnew_after_delete
    AFTER DELETE ON `whatsnew`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, changed_by)
        VALUES(
            'whatsnew',
            'DELETE',
            OLD.`id`,
            JSON_OBJECT('id', OLD.`id`,'title', OLD.`title`,'descrip', OLD.`descrip`,'type', OLD.`type`,'administrator', OLD.`administrator`,'clsteacher', OLD.`clsteacher`,'teacher', OLD.`teacher`,'guardian', OLD.`guardian`,'student', OLD.`student`,'accountants', OLD.`accountants`,'librarian', OLD.`librarian`,'guest', OLD.`guest`,'icon', OLD.`icon`,'link', OLD.`link`,'updatetime', OLD.`updatetime`),
            COALESCE(@current_user,'system')
        );
    END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `whatsnew_track`
--

DROP TABLE IF EXISTS `whatsnew_track`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `whatsnew_track` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `wnewid` int(11) DEFAULT NULL,
  `email` varchar(120) DEFAULT NULL,
  `modifieddate` datetime DEFAULT NULL COMMENT 'Visiting Time',
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER whatsnew_track_after_insert
    AFTER INSERT ON `whatsnew_track`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, new_data, changed_by)
        VALUES(
            'whatsnew_track',
            'INSERT',
            NEW.`id`,
            JSON_OBJECT('id', NEW.`id`,'wnewid', NEW.`wnewid`,'email', NEW.`email`,'modifieddate', NEW.`modifieddate`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER whatsnew_track_after_update
    AFTER UPDATE ON `whatsnew_track`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, new_data, changed_by)
        VALUES(
            'whatsnew_track',
            'UPDATE',
            NEW.`id`,
            JSON_OBJECT('id', OLD.`id`,'wnewid', OLD.`wnewid`,'email', OLD.`email`,'modifieddate', OLD.`modifieddate`),
            JSON_OBJECT('id', NEW.`id`,'wnewid', NEW.`wnewid`,'email', NEW.`email`,'modifieddate', NEW.`modifieddate`),
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER whatsnew_track_after_delete
    AFTER DELETE ON `whatsnew_track`
    FOR EACH ROW
    BEGIN
        INSERT INTO sldijsf87sxckf.audit_log(table_name, action, row_id, old_data, changed_by)
        VALUES(
            'whatsnew_track',
            'DELETE',
            OLD.`id`,
            JSON_OBJECT('id', OLD.`id`,'wnewid', OLD.`wnewid`,'email', OLD.`email`,'modifieddate', OLD.`modifieddate`),
            COALESCE(@current_user,'system')
        );
    END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Dumping events for database 'eimbox'
--

--
-- Dumping routines for database 'eimbox'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-10-28  2:55:20
