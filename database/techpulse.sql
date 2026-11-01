-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 01, 2026 at 08:03 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `techpulse`
--

-- --------------------------------------------------------

--
-- Table structure for table `computers`
--

CREATE TABLE `computers` (
  `computer_id` int(10) UNSIGNED NOT NULL,
  `lab_id` tinyint(3) UNSIGNED NOT NULL,
  `pc_number` tinyint(3) UNSIGNED NOT NULL,
  `status` enum('available','maintenance') NOT NULL DEFAULT 'available'
) ;

--
-- Dumping data for table `computers`
--

INSERT INTO `computers` (`computer_id`, `lab_id`, `pc_number`, `status`) VALUES
(1, 1, 1, 'available'),
(2, 2, 1, 'available'),
(3, 3, 1, 'available'),
(4, 1, 11, 'available'),
(5, 2, 11, 'available'),
(6, 3, 11, 'available'),
(7, 1, 21, 'available'),
(8, 2, 21, 'available'),
(9, 3, 21, 'available'),
(10, 1, 31, 'available'),
(11, 2, 31, 'available'),
(12, 3, 31, 'available'),
(13, 1, 41, 'available'),
(14, 2, 41, 'available'),
(15, 3, 41, 'available'),
(16, 1, 2, 'available'),
(17, 2, 2, 'available'),
(18, 3, 2, 'available'),
(19, 1, 12, 'available'),
(20, 2, 12, 'available'),
(21, 3, 12, 'available'),
(22, 1, 22, 'available'),
(23, 2, 22, 'available'),
(24, 3, 22, 'available'),
(25, 1, 32, 'available'),
(26, 2, 32, 'available'),
(27, 3, 32, 'available'),
(28, 1, 42, 'available'),
(29, 2, 42, 'available'),
(30, 3, 42, 'available'),
(31, 1, 3, 'available'),
(32, 2, 3, 'available'),
(33, 3, 3, 'available'),
(34, 1, 13, 'available'),
(35, 2, 13, 'available'),
(36, 3, 13, 'available'),
(37, 1, 23, 'available'),
(38, 2, 23, 'available'),
(39, 3, 23, 'available'),
(40, 1, 33, 'available'),
(41, 2, 33, 'available'),
(42, 3, 33, 'available'),
(43, 1, 43, 'available'),
(44, 2, 43, 'available'),
(45, 3, 43, 'available'),
(46, 1, 4, 'available'),
(47, 2, 4, 'available'),
(48, 3, 4, 'available'),
(49, 1, 14, 'available'),
(50, 2, 14, 'available'),
(51, 3, 14, 'available'),
(52, 1, 24, 'available'),
(53, 2, 24, 'available'),
(54, 3, 24, 'available'),
(55, 1, 34, 'available'),
(56, 2, 34, 'available'),
(57, 3, 34, 'available'),
(58, 1, 44, 'available'),
(59, 2, 44, 'available'),
(60, 3, 44, 'available'),
(61, 1, 5, 'available'),
(62, 2, 5, 'available'),
(63, 3, 5, 'available'),
(64, 1, 15, 'available'),
(65, 2, 15, 'available'),
(66, 3, 15, 'available'),
(67, 1, 25, 'available'),
(68, 2, 25, 'available'),
(69, 3, 25, 'available'),
(70, 1, 35, 'available'),
(71, 2, 35, 'available'),
(72, 3, 35, 'available'),
(73, 1, 45, 'available'),
(74, 2, 45, 'available'),
(75, 3, 45, 'available'),
(76, 1, 6, 'available'),
(77, 2, 6, 'available'),
(78, 3, 6, 'available'),
(79, 1, 16, 'available'),
(80, 2, 16, 'available'),
(81, 3, 16, 'available'),
(82, 1, 26, 'available'),
(83, 2, 26, 'available'),
(84, 3, 26, 'available'),
(85, 1, 36, 'available'),
(86, 2, 36, 'available'),
(87, 3, 36, 'available'),
(88, 1, 7, 'available'),
(89, 2, 7, 'available'),
(90, 3, 7, 'available'),
(91, 1, 17, 'available'),
(92, 2, 17, 'available'),
(93, 3, 17, 'available'),
(94, 1, 27, 'available'),
(95, 2, 27, 'available'),
(96, 3, 27, 'available'),
(97, 1, 37, 'available'),
(98, 2, 37, 'available'),
(99, 3, 37, 'available'),
(100, 1, 8, 'available'),
(101, 2, 8, 'available'),
(102, 3, 8, 'available'),
(103, 1, 18, 'available'),
(104, 2, 18, 'available'),
(105, 3, 18, 'available'),
(106, 1, 28, 'available'),
(107, 2, 28, 'available'),
(108, 3, 28, 'available'),
(109, 1, 38, 'available'),
(110, 2, 38, 'available'),
(111, 3, 38, 'available'),
(112, 1, 9, 'available'),
(113, 2, 9, 'available'),
(114, 3, 9, 'available'),
(115, 1, 19, 'available'),
(116, 2, 19, 'available'),
(117, 3, 19, 'available'),
(118, 1, 29, 'available'),
(119, 2, 29, 'available'),
(120, 3, 29, 'available'),
(121, 1, 39, 'available'),
(122, 2, 39, 'available'),
(123, 3, 39, 'available'),
(124, 1, 10, 'available'),
(125, 2, 10, 'available'),
(126, 3, 10, 'available'),
(127, 1, 20, 'available'),
(128, 2, 20, 'available'),
(129, 3, 20, 'available'),
(130, 1, 30, 'available'),
(131, 2, 30, 'available'),
(132, 3, 30, 'available'),
(133, 1, 40, 'available'),
(134, 2, 40, 'available'),
(135, 3, 40, 'available'),
(136, 4, 1, 'available'),
(137, 4, 2, 'available'),
(138, 4, 3, 'available'),
(139, 4, 4, 'available'),
(140, 4, 5, 'available'),
(141, 4, 6, 'available'),
(142, 4, 7, 'available'),
(143, 4, 8, 'available'),
(144, 4, 9, 'available'),
(145, 4, 10, 'available'),
(146, 4, 11, 'available'),
(147, 4, 12, 'available'),
(148, 4, 13, 'available'),
(149, 4, 14, 'available'),
(150, 4, 15, 'available'),
(151, 4, 16, 'available'),
(152, 4, 17, 'available'),
(153, 4, 18, 'available'),
(154, 4, 19, 'available'),
(155, 4, 20, 'available'),
(156, 4, 21, 'available'),
(157, 4, 22, 'available'),
(158, 4, 23, 'available'),
(159, 4, 24, 'available'),
(160, 4, 25, 'available'),
(161, 4, 26, 'available'),
(162, 4, 27, 'available'),
(163, 4, 28, 'available'),
(164, 4, 29, 'available'),
(165, 4, 30, 'available'),
(166, 4, 31, 'available'),
(167, 4, 32, 'available'),
(168, 4, 33, 'available'),
(169, 4, 34, 'available'),
(170, 4, 35, 'available'),
(171, 4, 36, 'available'),
(172, 4, 37, 'available'),
(173, 4, 38, 'available'),
(174, 4, 39, 'available'),
(175, 4, 40, 'available'),
(176, 4, 41, 'available'),
(177, 4, 42, 'available'),
(178, 4, 43, 'available'),
(179, 4, 44, 'available'),
(180, 4, 45, 'available');

-- --------------------------------------------------------

--
-- Table structure for table `computer_sessions`
--

CREATE TABLE `computer_sessions` (
  `session_id` bigint(20) UNSIGNED NOT NULL,
  `StudentID` int(11) DEFAULT NULL,
  `computer_id` int(10) UNSIGNED NOT NULL,
  `time_in` datetime NOT NULL DEFAULT current_timestamp(),
  `time_out` datetime DEFAULT NULL,
  `active_student` int(11) GENERATED ALWAYS AS (if(`time_out` is null,`StudentID`,NULL)) STORED,
  `active_computer` int(10) UNSIGNED GENERATED ALWAYS AS (if(`time_out` is null,`computer_id`,NULL)) STORED
) ;

-- --------------------------------------------------------

--
-- Table structure for table `courses`
--

CREATE TABLE `courses` (
  `course_code` varchar(50) NOT NULL,
  `course_name` varchar(150) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1
) ;

--
-- Dumping data for table `courses`
--

INSERT INTO `courses` (`course_code`, `course_name`, `is_active`) VALUES
('ACT', 'ACT - Associate in Computer Technology', 1),
('BSAIS', 'BSAIS - BS in Accounting Information System', 1),
('BSCA', 'BSCA - BS in Customs Administration', 1),
('BSIS', 'BSIS - BS in Information Systems', 1),
('BSOM', 'BSOM - BS in Office Management', 1),
('BTVTED', 'BTVTED - BS in Technical-Vocational Teacher Education', 1),
('DHRMT', 'DHRMT - Diploma in Hotel and Restaurant Management Technology', 1),
('HB', 'HB - Bundled Hotel and Restaurant Services', 1);

-- --------------------------------------------------------

--
-- Table structure for table `laboratories`
--

CREATE TABLE `laboratories` (
  `lab_id` tinyint(3) UNSIGNED NOT NULL,
  `lab_name` varchar(60) NOT NULL
) ;

--
-- Dumping data for table `laboratories`
--

INSERT INTO `laboratories` (`lab_id`, `lab_name`) VALUES
(1, 'ComLab 1'),
(2, 'ComLab 2'),
(3, 'ComLab 3'),
(4, 'ComLab 4');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `StudentID` int(20) NOT NULL,
  `firstname` varchar(67) NOT NULL,
  `lastname` varchar(67) NOT NULL,
  `course` varchar(50) NOT NULL,
  `yearlevel` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ;

-- --------------------------------------------------------

--
-- Table structure for table `year_levels`
--

CREATE TABLE `year_levels` (
  `year_name` varchar(50) NOT NULL,
  `sort_order` tinyint(3) UNSIGNED NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1
) ;

--
-- Dumping data for table `year_levels`
--

INSERT INTO `year_levels` (`year_name`, `sort_order`, `is_active`) VALUES
('1st Year', 1, 1),
('2nd Year', 2, 1),
('3rd Year', 3, 1),
('4th Year', 4, 1);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `computers`
--
ALTER TABLE `computers`
  ADD PRIMARY KEY (`computer_id`),
  ADD UNIQUE KEY `uq_lab_pc` (`lab_id`,`pc_number`);

--
-- Indexes for table `computer_sessions`
--
ALTER TABLE `computer_sessions`
  ADD PRIMARY KEY (`session_id`),
  ADD UNIQUE KEY `uq_active_student` (`active_student`),
  ADD UNIQUE KEY `uq_active_computer` (`active_computer`),
  ADD KEY `ix_student_history` (`StudentID`,`time_in`),
  ADD KEY `fk_session_computer` (`computer_id`),
  ADD KEY `ix_session_recent` (`time_in`,`session_id`);

--
-- Indexes for table `courses`
--
ALTER TABLE `courses`
  ADD PRIMARY KEY (`course_code`);

--
-- Indexes for table `laboratories`
--
ALTER TABLE `laboratories`
  ADD PRIMARY KEY (`lab_id`),
  ADD UNIQUE KEY `lab_name` (`lab_name`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`StudentID`),
  ADD KEY `fk_user_course` (`course`),
  ADD KEY `fk_user_year` (`yearlevel`),
  ADD KEY `ix_user_created` (`created_at`,`StudentID`);

--
-- Indexes for table `year_levels`
--
ALTER TABLE `year_levels`
  ADD PRIMARY KEY (`year_name`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `computers`
--
ALTER TABLE `computers`
  MODIFY `computer_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `computer_sessions`
--
ALTER TABLE `computer_sessions`
  MODIFY `session_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `computers`
--
ALTER TABLE `computers`
  ADD CONSTRAINT `fk_computer_lab` FOREIGN KEY (`lab_id`) REFERENCES `laboratories` (`lab_id`);

--
-- Constraints for table `computer_sessions`
--
ALTER TABLE `computer_sessions`
  ADD CONSTRAINT `fk_session_computer` FOREIGN KEY (`computer_id`) REFERENCES `computers` (`computer_id`),
  ADD CONSTRAINT `fk_session_student` FOREIGN KEY (`StudentID`) REFERENCES `users` (`StudentID`);

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `fk_user_course` FOREIGN KEY (`course`) REFERENCES `courses` (`course_code`),
  ADD CONSTRAINT `fk_user_year` FOREIGN KEY (`yearlevel`) REFERENCES `year_levels` (`year_name`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
