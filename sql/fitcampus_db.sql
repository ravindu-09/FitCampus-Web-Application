-- ============================================================
-- FITCAMPUS DATABASE SETUP
-- ============================================================

CREATE DATABASE IF NOT EXISTS `fitcampus_db`
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE `fitcampus_db`;

-- Allow tables to be recreated safely
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS
    `team_workout_exercise`,
    `team_workout`,
    `personal_workout_exercise`,
    `personal_workout`,
    `common_workout_exercise`,
    `common_workout`,
    `gym_rule`,
    `penalty`,
    `feedback`,
    `goal`,
    `calorie_log`,
    `attendance`,
    `booking`,
    `team_member`,
    `team`,
    `facility_equipment`,
    `exercise`,
    `university_student`,
    `announcement`,
    `instructor`,
    `admin`,
    `facility`,
    `user`;

SET FOREIGN_KEY_CHECKS = 1;

-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 22, 2026 at 07:20 PM
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
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `Admin_ID` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`Admin_ID`) VALUES
(4);

-- --------------------------------------------------------

--
-- Table structure for table `announcement`
--

CREATE TABLE `announcement` (
  `Announcement_ID` int(10) UNSIGNED NOT NULL,
  `Admin_ID` int(10) UNSIGNED NOT NULL,
  `Title` varchar(150) NOT NULL,
  `Description` text NOT NULL,
  `Publish_Date` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `attendance`
--

CREATE TABLE `attendance` (
  `Attendance_ID` int(10) UNSIGNED NOT NULL,
  `User_ID` int(10) UNSIGNED NOT NULL,
  `Facility_ID` int(10) UNSIGNED NOT NULL,
  `Check_In_Time` datetime NOT NULL,
  `Check_Out_Time` datetime DEFAULT NULL,
  `Check_In_Method` varchar(30) DEFAULT NULL,
  `Check_Out_Method` varchar(30) DEFAULT NULL,
  `Active_Status` tinyint(1) NOT NULL DEFAULT 1
) ;

-- --------------------------------------------------------

--
-- Table structure for table `booking`
--

CREATE TABLE `booking` (
  `Booking_ID` int(10) UNSIGNED NOT NULL,
  `Team_ID` int(10) UNSIGNED NOT NULL,
  `Requested_By` int(10) UNSIGNED NOT NULL,
  `Facility_ID` int(10) UNSIGNED NOT NULL,
  `Approved_By` int(10) UNSIGNED DEFAULT NULL,
  `Booking_Time` datetime NOT NULL,
  `Reserve_Date` date NOT NULL,
  `Start_Time` time NOT NULL,
  `End_Time` time NOT NULL,
  `Status` enum('Pending','Approved','Rejected','Cancelled') NOT NULL DEFAULT 'Pending',
  `Exception_Reason` text DEFAULT NULL
) ;

-- --------------------------------------------------------

--
-- Table structure for table `calorie_log`
--

CREATE TABLE `calorie_log` (
  `User_ID` int(10) UNSIGNED NOT NULL,
  `Date` date NOT NULL,
  `Calories_In` decimal(8,2) DEFAULT 0.00,
  `Calories_Out` decimal(8,2) DEFAULT 0.00
) ;

-- --------------------------------------------------------

--
-- Table structure for table `common_workout`
--

CREATE TABLE `common_workout` (
  `Common_Workout_ID` int(10) UNSIGNED NOT NULL,
  `Instructor_ID` int(10) UNSIGNED NOT NULL,
  `Workout_Level` varchar(50) DEFAULT NULL,
  `Duration` int(10) UNSIGNED DEFAULT NULL,
  `Title` varchar(150) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `common_workout_exercise`
--

CREATE TABLE `common_workout_exercise` (
  `Common_Workout_ID` int(10) UNSIGNED NOT NULL,
  `Exercise_ID` int(10) UNSIGNED NOT NULL,
  `Sets` int(10) UNSIGNED DEFAULT NULL,
  `Reps` int(10) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `exercise`
--

CREATE TABLE `exercise` (
  `Exercise_ID` int(10) UNSIGNED NOT NULL,
  `Exercise_Name` varchar(100) NOT NULL,
  `Description` text DEFAULT NULL,
  `Demo_Link` varchar(500) DEFAULT NULL,
  `Type` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `facility`
--

CREATE TABLE `facility` (
  `Facility_ID` int(10) UNSIGNED NOT NULL,
  `Facility_Name` varchar(100) NOT NULL,
  `Location` varchar(255) DEFAULT NULL,
  `Capacity` int(10) UNSIGNED NOT NULL,
  `Open_Time` time NOT NULL,
  `Close_Time` time NOT NULL
) ;

-- --------------------------------------------------------

--
-- Table structure for table `facility_equipment`
--

CREATE TABLE `facility_equipment` (
  `Equipment_ID` int(10) UNSIGNED NOT NULL,
  `Facility_ID` int(10) UNSIGNED NOT NULL,
  `Equipment_Name` varchar(100) NOT NULL,
  `Quantity` int(10) UNSIGNED NOT NULL DEFAULT 0
) ;

-- --------------------------------------------------------

--
-- Table structure for table `feedback`
--

CREATE TABLE `feedback` (
  `Feedback_ID` int(10) UNSIGNED NOT NULL,
  `User_ID` int(10) UNSIGNED NOT NULL,
  `Responded_Admin` int(10) UNSIGNED DEFAULT NULL,
  `Category` varchar(50) DEFAULT NULL,
  `Description` text NOT NULL,
  `Submitted_Date` datetime NOT NULL DEFAULT current_timestamp(),
  `Status` enum('Pending','In Progress','Resolved','Rejected') NOT NULL DEFAULT 'Pending',
  `Response_Date` datetime DEFAULT NULL,
  `Response` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `goal`
--

CREATE TABLE `goal` (
  `Goal_ID` int(10) UNSIGNED NOT NULL,
  `User_ID` int(10) UNSIGNED NOT NULL,
  `Goal_Type` varchar(100) NOT NULL,
  `Target` decimal(10,2) DEFAULT NULL,
  `Start_Date` date DEFAULT NULL,
  `End_Date` date DEFAULT NULL,
  `Status` varchar(30) DEFAULT NULL
) ;

-- --------------------------------------------------------

--
-- Table structure for table `gym_rule`
--

CREATE TABLE `gym_rule` (
  `Rule_No` int(10) UNSIGNED NOT NULL,
  `Rule` text NOT NULL,
  `Penalty_For_Violation` text DEFAULT NULL,
  `Instructor_ID` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `instructor`
--

CREATE TABLE `instructor` (
  `Instructor_ID` int(10) UNSIGNED NOT NULL,
  `Facility_ID` int(10) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `instructor`
--

INSERT INTO `instructor` (`Instructor_ID`, `Facility_ID`) VALUES
(3, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `penalty`
--

CREATE TABLE `penalty` (
  `Penalty_ID` int(10) UNSIGNED NOT NULL,
  `User_ID` int(10) UNSIGNED NOT NULL,
  `Team_ID` int(10) UNSIGNED DEFAULT NULL,
  `Instructor_ID` int(10) UNSIGNED DEFAULT NULL,
  `Description` text NOT NULL,
  `Date` date NOT NULL,
  `Severity` varchar(30) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `personal_workout`
--

CREATE TABLE `personal_workout` (
  `Personal_Workout_ID` int(10) UNSIGNED NOT NULL,
  `User_ID` int(10) UNSIGNED NOT NULL,
  `Duration` int(10) UNSIGNED DEFAULT NULL,
  `Title` varchar(150) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `personal_workout_exercise`
--

CREATE TABLE `personal_workout_exercise` (
  `Personal_Workout_ID` int(10) UNSIGNED NOT NULL,
  `Exercise_ID` int(10) UNSIGNED NOT NULL,
  `Sets` int(10) UNSIGNED DEFAULT NULL,
  `Reps` int(10) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `team`
--

CREATE TABLE `team` (
  `Team_ID` int(10) UNSIGNED NOT NULL,
  `Team_Name` varchar(100) NOT NULL,
  `Sport` varchar(100) NOT NULL,
  `Sport_Gender` varchar(30) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `team`
--

INSERT INTO `team` (`Team_ID`, `Team_Name`, `Sport`, `Sport_Gender`) VALUES
(1, 'UOC Track & Field', 'Athletics', 'Men');

-- --------------------------------------------------------

--
-- Table structure for table `team_member`
--

CREATE TABLE `team_member` (
  `Team_ID` int(10) UNSIGNED NOT NULL,
  `User_ID` int(10) UNSIGNED NOT NULL,
  `Role_In_Team` enum('Captain','Member') NOT NULL DEFAULT 'Member'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `team_member`
--

INSERT INTO `team_member` (`Team_ID`, `User_ID`, `Role_In_Team`) VALUES
(1, 1, 'Member'),
(1, 2, 'Captain');

-- --------------------------------------------------------

--
-- Table structure for table `team_workout`
--

CREATE TABLE `team_workout` (
  `Workout_ID` int(10) UNSIGNED NOT NULL,
  `Team_ID` int(10) UNSIGNED NOT NULL,
  `Booking_ID` int(10) UNSIGNED NOT NULL,
  `Title` varchar(150) NOT NULL,
  `Description` text DEFAULT NULL,
  `Duration` int(10) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `team_workout_exercise`
--

CREATE TABLE `team_workout_exercise` (
  `Workout_ID` int(10) UNSIGNED NOT NULL,
  `Exercise_ID` int(10) UNSIGNED NOT NULL,
  `Sets` int(10) UNSIGNED DEFAULT NULL,
  `Reps` int(10) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `university_student`
--

CREATE TABLE `university_student` (
  `User_ID` int(10) UNSIGNED NOT NULL,
  `Registration_Number` varchar(50) NOT NULL,
  `NIC` varchar(30) NOT NULL,
  `DOB` date NOT NULL,
  `Faculty` varchar(100) DEFAULT NULL,
  `QR_Token` varchar(255) DEFAULT NULL,
  `Gender` varchar(30) DEFAULT NULL,
  `Profile_Image` varchar(255) DEFAULT 'default_avatar.png',
  `Status` enum('active','pending','suspended') NOT NULL DEFAULT 'active',
  `Created_At` timestamp NOT NULL DEFAULT current_timestamp(),
  `Emergency_Contact` varchar(150) DEFAULT NULL,
  `Student_ID_Front` varchar(255) DEFAULT NULL,
  `Student_ID_Back` varchar(255) DEFAULT NULL,
  `Date_of_Final_Exam` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `university_student`
--

INSERT INTO `university_student` (`User_ID`, `Registration_Number`, `NIC`, `DOB`, `Faculty`, `QR_Token`, `Gender`, `Profile_Image`, `Status`, `Created_At`, `Emergency_Contact`, `Student_ID_Front`, `Student_ID_Back`, `Date_of_Final_Exam`) VALUES
(1, '2022CS001', '200112345678', '2001-05-15', 'Computing', NULL, NULL, 'default_avatar.png', 'active', '2026-08-17 11:20:44', NULL, NULL, NULL, NULL),
(2, '2022CS002', '200187654321', '2001-08-20', 'Computing', NULL, NULL, 'default_avatar.png', 'active', '2026-08-17 11:20:44', NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `User_ID` int(10) UNSIGNED NOT NULL,
  `First_Name` varchar(50) NOT NULL,
  `Last_Name` varchar(50) NOT NULL,
  `Email` varchar(100) NOT NULL,
  `Password` varchar(255) NOT NULL,
  `Role` enum('Student','Instructor','Admin') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`User_ID`, `First_Name`, `Last_Name`, `Email`, `Password`, `Role`) VALUES
(1, 'Kasun', 'Perera', 'member@stu.cmb.ac.lk', '$2y$10$YcUbwJRQy3fTsfCpHFntkuJOZoSNLb6Lt21ZOjlxAV69FIaBn.JZ6', 'Student'),
(2, 'Ravindu', 'Lochana', 'captain@stu.cmb.ac.lk', '$2y$10$YcUbwJRQy3fTsfCpHFntkuJOZoSNLb6Lt21ZOjlxAV69FIaBn.JZ6', 'Student'),
(3, 'Coach', 'Nuwan', 'instructor@cmb.ac.lk', '$2y$10$YcUbwJRQy3fTsfCpHFntkuJOZoSNLb6Lt21ZOjlxAV69FIaBn.JZ6', 'Instructor'),
(4, 'System', 'Admin', 'admin@cmb.ac.lk', '$2y$10$YcUbwJRQy3fTsfCpHFntkuJOZoSNLb6Lt21ZOjlxAV69FIaBn.JZ6', 'Admin');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`Admin_ID`);

--
-- Indexes for table `announcement`
--
ALTER TABLE `announcement`
  ADD PRIMARY KEY (`Announcement_ID`),
  ADD KEY `fk_announcement_admin` (`Admin_ID`);

--
-- Indexes for table `attendance`
--
ALTER TABLE `attendance`
  ADD PRIMARY KEY (`Attendance_ID`),
  ADD KEY `fk_attendance_user` (`User_ID`),
  ADD KEY `fk_attendance_facility` (`Facility_ID`);

--
-- Indexes for table `booking`
--
ALTER TABLE `booking`
  ADD PRIMARY KEY (`Booking_ID`),
  ADD KEY `fk_booking_team` (`Team_ID`),
  ADD KEY `fk_booking_requested_by` (`Requested_By`),
  ADD KEY `fk_booking_facility` (`Facility_ID`),
  ADD KEY `fk_booking_approved_by` (`Approved_By`);

--
-- Indexes for table `calorie_log`
--
ALTER TABLE `calorie_log`
  ADD PRIMARY KEY (`User_ID`,`Date`);

--
-- Indexes for table `common_workout`
--
ALTER TABLE `common_workout`
  ADD PRIMARY KEY (`Common_Workout_ID`),
  ADD KEY `fk_common_workout_instructor` (`Instructor_ID`);

--
-- Indexes for table `common_workout_exercise`
--
ALTER TABLE `common_workout_exercise`
  ADD PRIMARY KEY (`Common_Workout_ID`,`Exercise_ID`),
  ADD KEY `fk_common_workout_exercise_exercise` (`Exercise_ID`);

--
-- Indexes for table `exercise`
--
ALTER TABLE `exercise`
  ADD PRIMARY KEY (`Exercise_ID`),
  ADD UNIQUE KEY `uq_exercise_name` (`Exercise_Name`);

--
-- Indexes for table `facility`
--
ALTER TABLE `facility`
  ADD PRIMARY KEY (`Facility_ID`);

--
-- Indexes for table `facility_equipment`
--
ALTER TABLE `facility_equipment`
  ADD PRIMARY KEY (`Equipment_ID`),
  ADD KEY `fk_equipment_facility` (`Facility_ID`);

--
-- Indexes for table `feedback`
--
ALTER TABLE `feedback`
  ADD PRIMARY KEY (`Feedback_ID`),
  ADD KEY `fk_feedback_user` (`User_ID`),
  ADD KEY `fk_feedback_admin` (`Responded_Admin`);

--
-- Indexes for table `goal`
--
ALTER TABLE `goal`
  ADD PRIMARY KEY (`Goal_ID`),
  ADD KEY `fk_goal_user` (`User_ID`);

--
-- Indexes for table `gym_rule`
--
ALTER TABLE `gym_rule`
  ADD PRIMARY KEY (`Rule_No`),
  ADD KEY `fk_gym_rule_instructor` (`Instructor_ID`);

--
-- Indexes for table `instructor`
--
ALTER TABLE `instructor`
  ADD PRIMARY KEY (`Instructor_ID`),
  ADD KEY `fk_instructor_facility` (`Facility_ID`);

--
-- Indexes for table `penalty`
--
ALTER TABLE `penalty`
  ADD PRIMARY KEY (`Penalty_ID`),
  ADD KEY `fk_penalty_user` (`User_ID`),
  ADD KEY `fk_penalty_team` (`Team_ID`),
  ADD KEY `fk_penalty_instructor` (`Instructor_ID`);

--
-- Indexes for table `personal_workout`
--
ALTER TABLE `personal_workout`
  ADD PRIMARY KEY (`Personal_Workout_ID`),
  ADD KEY `fk_personal_workout_user` (`User_ID`);

--
-- Indexes for table `personal_workout_exercise`
--
ALTER TABLE `personal_workout_exercise`
  ADD PRIMARY KEY (`Personal_Workout_ID`,`Exercise_ID`),
  ADD KEY `fk_personal_workout_exercise_exercise` (`Exercise_ID`);

--
-- Indexes for table `team`
--
ALTER TABLE `team`
  ADD PRIMARY KEY (`Team_ID`);

--
-- Indexes for table `team_member`
--
ALTER TABLE `team_member`
  ADD PRIMARY KEY (`Team_ID`,`User_ID`),
  ADD KEY `fk_team_member_user` (`User_ID`);

--
-- Indexes for table `team_workout`
--
ALTER TABLE `team_workout`
  ADD PRIMARY KEY (`Workout_ID`),
  ADD KEY `fk_team_workout_team` (`Team_ID`),
  ADD KEY `fk_team_workout_booking` (`Booking_ID`);

--
-- Indexes for table `team_workout_exercise`
--
ALTER TABLE `team_workout_exercise`
  ADD PRIMARY KEY (`Workout_ID`,`Exercise_ID`),
  ADD KEY `fk_team_workout_exercise_exercise` (`Exercise_ID`);

--
-- Indexes for table `university_student`
--
ALTER TABLE `university_student`
  ADD PRIMARY KEY (`User_ID`),
  ADD UNIQUE KEY `uq_student_registration_number` (`Registration_Number`),
  ADD UNIQUE KEY `uq_student_nic` (`NIC`),
  ADD UNIQUE KEY `uq_student_qr_token` (`QR_Token`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`User_ID`),
  ADD UNIQUE KEY `uq_user_email` (`Email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `announcement`
--
ALTER TABLE `announcement`
  MODIFY `Announcement_ID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `attendance`
--
ALTER TABLE `attendance`
  MODIFY `Attendance_ID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `booking`
--
ALTER TABLE `booking`
  MODIFY `Booking_ID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `common_workout`
--
ALTER TABLE `common_workout`
  MODIFY `Common_Workout_ID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `exercise`
--
ALTER TABLE `exercise`
  MODIFY `Exercise_ID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `facility`
--
ALTER TABLE `facility`
  MODIFY `Facility_ID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `facility_equipment`
--
ALTER TABLE `facility_equipment`
  MODIFY `Equipment_ID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `feedback`
--
ALTER TABLE `feedback`
  MODIFY `Feedback_ID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `goal`
--
ALTER TABLE `goal`
  MODIFY `Goal_ID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `gym_rule`
--
ALTER TABLE `gym_rule`
  MODIFY `Rule_No` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `penalty`
--
ALTER TABLE `penalty`
  MODIFY `Penalty_ID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `personal_workout`
--
ALTER TABLE `personal_workout`
  MODIFY `Personal_Workout_ID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `team`
--
ALTER TABLE `team`
  MODIFY `Team_ID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `team_workout`
--
ALTER TABLE `team_workout`
  MODIFY `Workout_ID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `User_ID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `admin`
--
ALTER TABLE `admin`
  ADD CONSTRAINT `fk_admin_user` FOREIGN KEY (`Admin_ID`) REFERENCES `user` (`User_ID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `announcement`
--
ALTER TABLE `announcement`
  ADD CONSTRAINT `fk_announcement_admin` FOREIGN KEY (`Admin_ID`) REFERENCES `admin` (`Admin_ID`) ON UPDATE CASCADE;

--
-- Constraints for table `attendance`
--
ALTER TABLE `attendance`
  ADD CONSTRAINT `fk_attendance_facility` FOREIGN KEY (`Facility_ID`) REFERENCES `facility` (`Facility_ID`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_attendance_user` FOREIGN KEY (`User_ID`) REFERENCES `user` (`User_ID`) ON UPDATE CASCADE;

--
-- Constraints for table `booking`
--
ALTER TABLE `booking`
  ADD CONSTRAINT `fk_booking_approved_by` FOREIGN KEY (`Approved_By`) REFERENCES `admin` (`Admin_ID`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_booking_facility` FOREIGN KEY (`Facility_ID`) REFERENCES `facility` (`Facility_ID`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_booking_requested_by` FOREIGN KEY (`Requested_By`) REFERENCES `user` (`User_ID`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_booking_team` FOREIGN KEY (`Team_ID`) REFERENCES `team` (`Team_ID`) ON UPDATE CASCADE;

--
-- Constraints for table `calorie_log`
--
ALTER TABLE `calorie_log`
  ADD CONSTRAINT `fk_calorie_log_user` FOREIGN KEY (`User_ID`) REFERENCES `user` (`User_ID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `common_workout`
--
ALTER TABLE `common_workout`
  ADD CONSTRAINT `fk_common_workout_instructor` FOREIGN KEY (`Instructor_ID`) REFERENCES `instructor` (`Instructor_ID`) ON UPDATE CASCADE;

--
-- Constraints for table `common_workout_exercise`
--
ALTER TABLE `common_workout_exercise`
  ADD CONSTRAINT `fk_common_workout_exercise_exercise` FOREIGN KEY (`Exercise_ID`) REFERENCES `exercise` (`Exercise_ID`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_common_workout_exercise_workout` FOREIGN KEY (`Common_Workout_ID`) REFERENCES `common_workout` (`Common_Workout_ID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `facility_equipment`
--
ALTER TABLE `facility_equipment`
  ADD CONSTRAINT `fk_equipment_facility` FOREIGN KEY (`Facility_ID`) REFERENCES `facility` (`Facility_ID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `feedback`
--
ALTER TABLE `feedback`
  ADD CONSTRAINT `fk_feedback_admin` FOREIGN KEY (`Responded_Admin`) REFERENCES `admin` (`Admin_ID`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_feedback_user` FOREIGN KEY (`User_ID`) REFERENCES `user` (`User_ID`) ON UPDATE CASCADE;

--
-- Constraints for table `goal`
--
ALTER TABLE `goal`
  ADD CONSTRAINT `fk_goal_user` FOREIGN KEY (`User_ID`) REFERENCES `user` (`User_ID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `gym_rule`
--
ALTER TABLE `gym_rule`
  ADD CONSTRAINT `fk_gym_rule_instructor` FOREIGN KEY (`Instructor_ID`) REFERENCES `instructor` (`Instructor_ID`) ON UPDATE CASCADE;

--
-- Constraints for table `instructor`
--
ALTER TABLE `instructor`
  ADD CONSTRAINT `fk_instructor_facility` FOREIGN KEY (`Facility_ID`) REFERENCES `facility` (`Facility_ID`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_instructor_user` FOREIGN KEY (`Instructor_ID`) REFERENCES `user` (`User_ID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `penalty`
--
ALTER TABLE `penalty`
  ADD CONSTRAINT `fk_penalty_instructor` FOREIGN KEY (`Instructor_ID`) REFERENCES `instructor` (`Instructor_ID`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_penalty_team` FOREIGN KEY (`Team_ID`) REFERENCES `team` (`Team_ID`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_penalty_user` FOREIGN KEY (`User_ID`) REFERENCES `user` (`User_ID`) ON UPDATE CASCADE;

--
-- Constraints for table `personal_workout`
--
ALTER TABLE `personal_workout`
  ADD CONSTRAINT `fk_personal_workout_user` FOREIGN KEY (`User_ID`) REFERENCES `user` (`User_ID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `personal_workout_exercise`
--
ALTER TABLE `personal_workout_exercise`
  ADD CONSTRAINT `fk_personal_workout_exercise_exercise` FOREIGN KEY (`Exercise_ID`) REFERENCES `exercise` (`Exercise_ID`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_personal_workout_exercise_workout` FOREIGN KEY (`Personal_Workout_ID`) REFERENCES `personal_workout` (`Personal_Workout_ID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `team_member`
--
ALTER TABLE `team_member`
  ADD CONSTRAINT `fk_team_member_team` FOREIGN KEY (`Team_ID`) REFERENCES `team` (`Team_ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_team_member_user` FOREIGN KEY (`User_ID`) REFERENCES `user` (`User_ID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `team_workout`
--
ALTER TABLE `team_workout`
  ADD CONSTRAINT `fk_team_workout_booking` FOREIGN KEY (`Booking_ID`) REFERENCES `booking` (`Booking_ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_team_workout_team` FOREIGN KEY (`Team_ID`) REFERENCES `team` (`Team_ID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `team_workout_exercise`
--
ALTER TABLE `team_workout_exercise`
  ADD CONSTRAINT `fk_team_workout_exercise_exercise` FOREIGN KEY (`Exercise_ID`) REFERENCES `exercise` (`Exercise_ID`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_team_workout_exercise_workout` FOREIGN KEY (`Workout_ID`) REFERENCES `team_workout` (`Workout_ID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `university_student`
--
ALTER TABLE `university_student`
  ADD CONSTRAINT `fk_student_user` FOREIGN KEY (`User_ID`) REFERENCES `user` (`User_ID`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
