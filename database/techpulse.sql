-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 17, 2026 at 05:13 AM
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`StudentID`, `firstname`, `lastname`, `course`, `yearlevel`, `password`, `created_at`) VALUES
(1, 'hea', 'eahea', 'BSAIS', '2nd', '$2y$10$SaQudBGSJoA6REdPAa7Utux643jd6RbUa9LvnC67Gr2e.LHR.xZEm', '2026-08-14 09:39:59'),
(534535, 'gegea', 'heahe', 'ACT', '2nd Year', '$2y$10$RGDA0CGaTQ0TgBgMOKJvZu1P2o9rLKgTuJO/xCypipIfpIx2nP2Te', '2026-08-17 02:36:38'),
(25010992, 'Rainier James', 'Rodiel', 'BSIS', '2nd Year', '$2y$10$6hq71WPlMXjnlz6UhBui4.IX34M4afLIvIFdbQSVnR4dF0PtAu59G', '2026-08-17 02:55:56'),
(26019923, 'Rainier Jamesge', 'Rodielgea', 'BSAIS', '3rd', '$2y$10$2actcm4GW5VaJ6SFP0PvletCq/KuFIRwIokDe/Zx/pQbXBA3WtWJi', '2026-08-15 13:28:38'),
(26019924, 'Rainier James', 'Rodiel', 'Ggeagea', '1st Year', '$2y$10$utuHPzCLHKWLwK8OoRuhsO2VeEQISAhe5AimbJ9NtQnNfjxGItBhO', '2026-08-15 13:48:47'),
(29019772, 'heahe', 'heaheaheaheah', 'BSIS', '3rd Year', '$2y$10$6lAMWOZRGZrQHKfuoZsuNO9IsnX8LqGnGHq7cYEH80HbH16NauWhq', '2026-08-17 02:49:46'),
(84748895, 'yejyrfj', 'ytjyjtyty', 'BSAIS', '2nd Year', '$2y$10$wvG/S6Kp7Gdyc8YK/saEeOlDTfnmlL5WttrI96m/a81tXh0o/P8By', '2026-08-17 02:47:52');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`StudentID`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
