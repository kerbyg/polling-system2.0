-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 05, 2025 at 07:09 AM
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
-- Database: `polling_mvp`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `username`, `password`) VALUES
(3, 'new_admin', '$2y$10$aR3EDpbxKzrw7QpVAqg70ei.A3Hr.ZAd3PrKsz9rJsuCd.67aCwvy'),
(5, 'admin', '$2y$10$5f6U44LjT.JXNNIHDyLDoOOnat.g2mDFG7C87t94n6A83sJUzJopi');

-- --------------------------------------------------------

--
-- Table structure for table `options`
--

CREATE TABLE `options` (
  `id` int(11) NOT NULL,
  `poll_id` int(11) NOT NULL,
  `option_text` varchar(255) NOT NULL,
  `votes` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `options`
--

INSERT INTO `options` (`id`, `poll_id`, `option_text`, `votes`) VALUES
(9, 3, 'Lebron James', 0),
(10, 3, 'Michael Jordan', 1),
(11, 3, 'Kobe Bryant', 0),
(12, 3, 'Kareem Abdul-Jabar', 0),
(13, 4, 'Ray Allen', 0),
(14, 4, 'Reggie Miller', 0),
(15, 4, 'Klay Thompson', 0),
(16, 4, 'Stephen Curry', 0),
(17, 5, 'Nikola Jokic', 0),
(18, 5, 'Luka Doncic', 0),
(19, 5, 'Giannis Antetokounmpo', 0),
(20, 5, 'Shai Gilgeous-Alexander', 0);

-- --------------------------------------------------------

--
-- Table structure for table `polls`
--

CREATE TABLE `polls` (
  `id` int(11) NOT NULL,
  `question` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `polls`
--

INSERT INTO `polls` (`id`, `question`, `created_at`, `is_active`) VALUES
(3, 'Who is the greatest basketball player of all time?', '2025-09-04 11:17:02', 1),
(4, 'Who is the Greatest Shooter of all time in NBA', '2025-09-04 13:45:35', 1),
(5, 'Which current NBA player is most likely to win the MVP award next season?', '2025-09-04 15:00:35', 1);

-- --------------------------------------------------------

--
-- Table structure for table `voters`
--

CREATE TABLE `voters` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `voters`
--

INSERT INTO `voters` (`id`, `username`, `password`, `created_at`) VALUES
(1, 'kerby', '$2y$10$RQ2n7ia.Sr3bG7G6tQHTOOibTd8YXQqK8FYx8cSIkoA2ihZfmtXL6', '2025-09-04 11:30:10'),
(2, 'bj', '$2y$10$FvxFog0QUMrOdk9s1M1W7uAgCEViwzLG9imzqb0PXKKmXFLMwTQwm', '2025-09-04 11:34:45'),
(3, 'ezikiel', '$2y$10$V655VSgvFRiDdc.CAuL/v.Qgv8Jcs3V9YLljYloms3FOEKJyeegBa', '2025-09-04 12:48:40'),
(4, 'brian', '$2y$10$bAEpe.oeDi9/t4tt9fBcr.jzLomovz./VfYpilzkqqfNB1dFjBOvu', '2025-09-04 13:27:44'),
(5, 'bak', '$2y$10$Yg/Ejnjb3UPwun7WGskhk.noiNeQnQ8KtvS6TCVs6j3XMtJ/1G3Je', '2025-09-04 14:53:14'),
(6, 'last', '$2y$10$J.GWyvfhvrNMhImYpMn.XugDYDsuUzY2/CAqvjJ/vAW.26aY7Co6G', '2025-09-04 15:15:55');

-- --------------------------------------------------------

--
-- Table structure for table `votes`
--

CREATE TABLE `votes` (
  `id` int(11) NOT NULL,
  `poll_id` int(11) NOT NULL,
  `option_id` int(11) NOT NULL,
  `voter_id` int(11) NOT NULL,
  `ip_address` varchar(64) NOT NULL,
  `voted_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `votes`
--

INSERT INTO `votes` (`id`, `poll_id`, `option_id`, `voter_id`, `ip_address`, `voted_at`) VALUES
(4, 3, 11, 2, '', '2025-09-04 11:46:28'),
(8, 3, 12, 1, '', '2025-09-04 12:48:04'),
(9, 3, 9, 3, '', '2025-09-04 12:48:59'),
(10, 3, 11, 4, '', '2025-09-04 13:28:37'),
(11, 4, 15, 1, '', '2025-09-04 13:46:22'),
(12, 4, 16, 2, '', '2025-09-04 14:07:22'),
(13, 4, 14, 5, '', '2025-09-04 14:53:46'),
(14, 3, 10, 5, '', '2025-09-04 14:54:14'),
(15, 5, 17, 2, '', '2025-09-04 15:01:26'),
(16, 5, 18, 5, '', '2025-09-04 15:04:51'),
(17, 5, 20, 6, '', '2025-09-04 15:16:35');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `options`
--
ALTER TABLE `options`
  ADD PRIMARY KEY (`id`),
  ADD KEY `poll_id` (`poll_id`);

--
-- Indexes for table `polls`
--
ALTER TABLE `polls`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `voters`
--
ALTER TABLE `voters`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `votes`
--
ALTER TABLE `votes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_vote` (`poll_id`,`voter_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `options`
--
ALTER TABLE `options`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `polls`
--
ALTER TABLE `polls`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `voters`
--
ALTER TABLE `voters`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `votes`
--
ALTER TABLE `votes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `options`
--
ALTER TABLE `options`
  ADD CONSTRAINT `options_ibfk_1` FOREIGN KEY (`poll_id`) REFERENCES `polls` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
