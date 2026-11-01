-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: techpulse
-- ------------------------------------------------------
-- Server version	10.4.32-MariaDB

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
-- Table structure for table `computer_sessions`
--

DROP TABLE IF EXISTS `computer_sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `computer_sessions` (
  `session_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `StudentID` int(11) DEFAULT NULL,
  `computer_id` int(10) unsigned NOT NULL,
  `time_in` datetime NOT NULL DEFAULT current_timestamp(),
  `time_out` datetime DEFAULT NULL,
  `active_student` int(11) GENERATED ALWAYS AS (if(`time_out` is null,`StudentID`,NULL)) STORED,
  `active_computer` int(10) unsigned GENERATED ALWAYS AS (if(`time_out` is null,`computer_id`,NULL)) STORED,
  PRIMARY KEY (`session_id`),
  UNIQUE KEY `uq_active_student` (`active_student`),
  UNIQUE KEY `uq_active_computer` (`active_computer`),
  KEY `ix_student_history` (`StudentID`,`time_in`),
  KEY `fk_session_computer` (`computer_id`),
  KEY `ix_session_recent` (`time_in`,`session_id`),
  CONSTRAINT `fk_session_computer` FOREIGN KEY (`computer_id`) REFERENCES `computers` (`computer_id`),
  CONSTRAINT `fk_session_student` FOREIGN KEY (`StudentID`) REFERENCES `users` (`StudentID`),
  CONSTRAINT `chk_session_times` CHECK (`time_out` is null or `time_out` >= `time_in`),
  CONSTRAINT `chk_session_student` CHECK (`StudentID` is not null or `time_out` is not null)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `computers`
--

DROP TABLE IF EXISTS `computers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `computers` (
  `computer_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `lab_id` tinyint(3) unsigned NOT NULL,
  `pc_number` tinyint(3) unsigned NOT NULL,
  `status` enum('available','maintenance') NOT NULL DEFAULT 'available',
  PRIMARY KEY (`computer_id`),
  UNIQUE KEY `uq_lab_pc` (`lab_id`,`pc_number`),
  CONSTRAINT `fk_computer_lab` FOREIGN KEY (`lab_id`) REFERENCES `laboratories` (`lab_id`),
  CONSTRAINT `chk_pc_number` CHECK (`pc_number` between 1 and 45)
) ENGINE=InnoDB AUTO_INCREMENT=200 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `courses`
--

DROP TABLE IF EXISTS `courses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `courses` (
  `course_code` varchar(50) NOT NULL,
  `course_name` varchar(150) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`course_code`),
  CONSTRAINT `chk_course_name` CHECK (char_length(trim(`course_name`)) > 0),
  CONSTRAINT `chk_course_active` CHECK (`is_active` in (0,1))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `laboratories`
--

DROP TABLE IF EXISTS `laboratories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `laboratories` (
  `lab_id` tinyint(3) unsigned NOT NULL,
  `lab_name` varchar(60) NOT NULL,
  PRIMARY KEY (`lab_id`),
  UNIQUE KEY `lab_name` (`lab_name`),
  CONSTRAINT `chk_lab_id` CHECK (`lab_id` > 0),
  CONSTRAINT `chk_lab_name` CHECK (char_length(trim(`lab_name`)) > 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `StudentID` int(20) NOT NULL,
  `firstname` varchar(67) NOT NULL,
  `lastname` varchar(67) NOT NULL,
  `course` varchar(50) NOT NULL,
  `yearlevel` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`StudentID`),
  KEY `fk_user_course` (`course`),
  KEY `fk_user_year` (`yearlevel`),
  KEY `ix_user_created` (`created_at`,`StudentID`),
  CONSTRAINT `fk_user_course` FOREIGN KEY (`course`) REFERENCES `courses` (`course_code`),
  CONSTRAINT `fk_user_year` FOREIGN KEY (`yearlevel`) REFERENCES `year_levels` (`year_name`),
  CONSTRAINT `chk_student_id` CHECK (`StudentID` > 0),
  CONSTRAINT `chk_firstname` CHECK (char_length(trim(`firstname`)) > 0),
  CONSTRAINT `chk_lastname` CHECK (char_length(trim(`lastname`)) > 0),
  CONSTRAINT `chk_password_hash` CHECK (char_length(`password`) between 60 and 255)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `year_levels`
--

DROP TABLE IF EXISTS `year_levels`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `year_levels` (
  `year_name` varchar(50) NOT NULL,
  `sort_order` tinyint(3) unsigned NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`year_name`),
  CONSTRAINT `chk_year_name` CHECK (char_length(trim(`year_name`)) > 0),
  CONSTRAINT `chk_year_order` CHECK (`sort_order` > 0),
  CONSTRAINT `chk_year_active` CHECK (`is_active` in (0,1))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-11-01 13:27:27
-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: techpulse
-- ------------------------------------------------------
-- Server version	10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Dumping data for table `courses`
--

LOCK TABLES `courses` WRITE;
/*!40000 ALTER TABLE `courses` DISABLE KEYS */;
INSERT INTO `courses` VALUES ('ACT','ACT - Associate in Computer Technology',1);
INSERT INTO `courses` VALUES ('BSAIS','BSAIS - BS in Accounting Information System',1);
INSERT INTO `courses` VALUES ('BSCA','BSCA - BS in Customs Administration',1);
INSERT INTO `courses` VALUES ('BSIS','BSIS - BS in Information Systems',1);
INSERT INTO `courses` VALUES ('BSOM','BSOM - BS in Office Management',1);
INSERT INTO `courses` VALUES ('BTVTED','BTVTED - BS in Technical-Vocational Teacher Education',1);
INSERT INTO `courses` VALUES ('DHRMT','DHRMT - Diploma in Hotel and Restaurant Management Technology',1);
INSERT INTO `courses` VALUES ('HB','HB - Bundled Hotel and Restaurant Services',1);
/*!40000 ALTER TABLE `courses` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `year_levels`
--

LOCK TABLES `year_levels` WRITE;
/*!40000 ALTER TABLE `year_levels` DISABLE KEYS */;
INSERT INTO `year_levels` VALUES ('1st Year',1,1);
INSERT INTO `year_levels` VALUES ('2nd Year',2,1);
INSERT INTO `year_levels` VALUES ('3rd Year',3,1);
INSERT INTO `year_levels` VALUES ('4th Year',4,1);
/*!40000 ALTER TABLE `year_levels` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `laboratories`
--

LOCK TABLES `laboratories` WRITE;
/*!40000 ALTER TABLE `laboratories` DISABLE KEYS */;
INSERT INTO `laboratories` VALUES (1,'ComLab 1');
INSERT INTO `laboratories` VALUES (2,'ComLab 2');
INSERT INTO `laboratories` VALUES (3,'ComLab 3');
INSERT INTO `laboratories` VALUES (4,'ComLab 4');
/*!40000 ALTER TABLE `laboratories` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-11-01 13:27:27


INSERT INTO computers (computer_id,lab_id,pc_number,status) VALUES
(1,1,1,'available'),
(2,1,2,'available'),
(3,1,3,'available'),
(4,1,4,'available'),
(5,1,5,'available'),
(6,1,6,'available'),
(7,1,7,'available'),
(8,1,8,'available'),
(9,1,9,'available'),
(10,1,10,'available'),
(11,1,11,'available'),
(12,1,12,'available'),
(13,1,13,'available'),
(14,1,14,'available'),
(15,1,15,'available'),
(16,1,16,'available'),
(17,1,17,'available'),
(18,1,18,'available'),
(19,1,19,'available'),
(20,1,20,'available'),
(21,1,21,'available'),
(22,1,22,'available'),
(23,1,23,'available'),
(24,1,24,'available'),
(25,1,25,'available'),
(26,1,26,'available'),
(27,1,27,'available'),
(28,1,28,'available'),
(29,1,29,'available'),
(30,1,30,'available'),
(31,1,31,'available'),
(32,1,32,'available'),
(33,1,33,'available'),
(34,1,34,'available'),
(35,1,35,'available'),
(36,1,36,'available'),
(37,1,37,'available'),
(38,1,38,'available'),
(39,1,39,'available'),
(40,1,40,'available'),
(41,1,41,'available'),
(42,1,42,'available'),
(43,1,43,'available'),
(44,1,44,'available'),
(45,1,45,'available'),
(46,2,1,'available'),
(47,2,2,'available'),
(48,2,3,'available'),
(49,2,4,'available'),
(50,2,5,'available'),
(51,2,6,'available'),
(52,2,7,'available'),
(53,2,8,'available'),
(54,2,9,'available'),
(55,2,10,'available'),
(56,2,11,'available'),
(57,2,12,'available'),
(58,2,13,'available'),
(59,2,14,'available'),
(60,2,15,'available'),
(61,2,16,'available'),
(62,2,17,'available'),
(63,2,18,'available'),
(64,2,19,'available'),
(65,2,20,'available'),
(66,2,21,'available'),
(67,2,22,'available'),
(68,2,23,'available'),
(69,2,24,'available'),
(70,2,25,'available'),
(71,2,26,'available'),
(72,2,27,'available'),
(73,2,28,'available'),
(74,2,29,'available'),
(75,2,30,'available'),
(76,2,31,'available'),
(77,2,32,'available'),
(78,2,33,'available'),
(79,2,34,'available'),
(80,2,35,'available'),
(81,2,36,'available'),
(82,2,37,'available'),
(83,2,38,'available'),
(84,2,39,'available'),
(85,2,40,'available'),
(86,2,41,'available'),
(87,2,42,'available'),
(88,2,43,'available'),
(89,2,44,'available'),
(90,2,45,'available'),
(91,3,1,'available'),
(92,3,2,'available'),
(93,3,3,'available'),
(94,3,4,'available'),
(95,3,5,'available'),
(96,3,6,'available'),
(97,3,7,'available'),
(98,3,8,'available'),
(99,3,9,'available'),
(100,3,10,'available'),
(101,3,11,'available'),
(102,3,12,'available'),
(103,3,13,'available'),
(104,3,14,'available'),
(105,3,15,'available'),
(106,3,16,'available'),
(107,3,17,'available'),
(108,3,18,'available'),
(109,3,19,'available'),
(110,3,20,'available'),
(111,3,21,'available'),
(112,3,22,'available'),
(113,3,23,'available'),
(114,3,24,'available'),
(115,3,25,'available'),
(116,3,26,'available'),
(117,3,27,'available'),
(118,3,28,'available'),
(119,3,29,'available'),
(120,3,30,'available'),
(121,3,31,'available'),
(122,3,32,'available'),
(123,3,33,'available'),
(124,3,34,'available'),
(125,3,35,'available'),
(126,3,36,'available'),
(127,3,37,'available'),
(128,3,38,'available'),
(129,3,39,'available'),
(130,3,40,'available'),
(131,3,41,'available'),
(132,3,42,'available'),
(133,3,43,'available'),
(134,3,44,'available'),
(135,3,45,'available'),
(136,4,1,'available'),
(137,4,2,'available'),
(138,4,3,'available'),
(139,4,4,'available'),
(140,4,5,'available'),
(141,4,6,'available'),
(142,4,7,'available'),
(143,4,8,'available'),
(144,4,9,'available'),
(145,4,10,'available'),
(146,4,11,'available'),
(147,4,12,'available'),
(148,4,13,'available'),
(149,4,14,'available'),
(150,4,15,'available'),
(151,4,16,'available'),
(152,4,17,'available'),
(153,4,18,'available'),
(154,4,19,'available'),
(155,4,20,'available'),
(156,4,21,'available'),
(157,4,22,'available'),
(158,4,23,'available'),
(159,4,24,'available'),
(160,4,25,'available'),
(161,4,26,'available'),
(162,4,27,'available'),
(163,4,28,'available'),
(164,4,29,'available'),
(165,4,30,'available'),
(166,4,31,'available'),
(167,4,32,'available'),
(168,4,33,'available'),
(169,4,34,'available'),
(170,4,35,'available'),
(171,4,36,'available'),
(172,4,37,'available'),
(173,4,38,'available'),
(174,4,39,'available'),
(175,4,40,'available'),
(176,4,41,'available'),
(177,4,42,'available'),
(178,4,43,'available'),
(179,4,44,'available'),
(180,4,45,'available');
INSERT INTO users (StudentID,firstname,lastname,course,yearlevel,password) VALUES (2000000100,'Demo','Student','BSIS','2nd Year','$2y$10$3cr01DUeDOE8yLA2IdAKpuLANvXZtyreF4SPp5RnAjarZT6dqUX2W');
ALTER TABLE computers AUTO_INCREMENT=181;
ALTER TABLE computer_sessions AUTO_INCREMENT=1;
