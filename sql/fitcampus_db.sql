-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 19, 2026 at 11:23 PM
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
-- Database: `fitcampus_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `reg_no` varchar(50) DEFAULT NULL COMMENT 'Student Reg No or Staff ID',
  `email` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `faculty` varchar(100) DEFAULT NULL,
  `nic` varchar(30) DEFAULT NULL,
  `dob` date DEFAULT NULL,
  `gender` enum('male','female','other') DEFAULT NULL,
  `emergency_contact` varchar(150) DEFAULT NULL,
  `profile_image` varchar(255) DEFAULT 'default_avatar.png',
  `id_front_image` varchar(255) DEFAULT NULL,
  `id_back_image` varchar(255) DEFAULT NULL,
  `role` enum('member','instructor','admin') NOT NULL DEFAULT 'member',
  `is_captain` tinyint(1) NOT NULL DEFAULT 0,
  `status` enum('active','pending','suspended') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `reg_no`, `email`, `password_hash`, `full_name`, `faculty`, `nic`, `dob`, `gender`, `emergency_contact`, `profile_image`, `id_front_image`, `id_back_image`, `role`, `is_captain`, `status`, `created_at`) VALUES
(1, '2022CS001', 'member@stu.cmb.ac.lk', '$2y$10$YcUbwJRQy3fTsfCpHFntkuJOZoSNLb6Lt21ZOjlxAV69FIaBn.JZ6', 'Kasun Perera', NULL, NULL, NULL, NULL, NULL, 'default_avatar.png', NULL, NULL, 'member', 0, 'active', '2026-08-17 16:50:44'),
(2, '2022CS002', 'captain@stu.cmb.ac.lk', '$2y$10$YcUbwJRQy3fTsfCpHFntkuJOZoSNLb6Lt21ZOjlxAV69FIaBn.JZ6', 'Ravindu Lochana', NULL, NULL, NULL, NULL, NULL, 'default_avatar.png', NULL, NULL, 'member', 1, 'active', '2026-08-17 16:50:44'),
(3, 'STAFF001', 'instructor@cmb.ac.lk', '$2y$10$YcUbwJRQy3fTsfCpHFntkuJOZoSNLb6Lt21ZOjlxAV69FIaBn.JZ6', 'Coach Nuwan', NULL, NULL, NULL, NULL, NULL, 'default_avatar.png', NULL, NULL, 'instructor', 0, 'active', '2026-08-17 16:50:44'),
(4, 'ADMIN001', 'admin@cmb.ac.lk', '$2y$10$YcUbwJRQy3fTsfCpHFntkuJOZoSNLb6Lt21ZOjlxAV69FIaBn.JZ6', 'System Admin', NULL, NULL, NULL, NULL, NULL, 'default_avatar.png', NULL, NULL, 'admin', 0, 'active', '2026-08-17 16:50:44');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `reg_no` (`reg_no`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
