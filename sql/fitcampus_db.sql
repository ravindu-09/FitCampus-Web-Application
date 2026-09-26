-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 26, 2026 at 08:06 PM
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `booking`
--

CREATE TABLE `booking` (
  `Booking_ID` int(10) UNSIGNED NOT NULL,
  `Team_ID` int(10) UNSIGNED NOT NULL,
  `Requested_By` int(10) UNSIGNED NOT NULL,
  `Facility_ID` int(10) UNSIGNED NOT NULL,
  `Team_Size` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `Approved_By` int(10) UNSIGNED DEFAULT NULL,
  `Booking_Time` datetime NOT NULL,
  `Reserve_Date` date NOT NULL,
  `Start_Time` time NOT NULL,
  `End_Time` time NOT NULL,
  `Status` enum('Pending','Approved','Rejected','Cancelled') NOT NULL DEFAULT 'Pending',
  `Exception_Reason` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `booking`
--

INSERT INTO `booking` (`Booking_ID`, `Team_ID`, `Requested_By`, `Facility_ID`, `Team_Size`, `Approved_By`, `Booking_Time`, `Reserve_Date`, `Start_Time`, `End_Time`, `Status`, `Exception_Reason`) VALUES
(40, 3, 21, 1, 1, NULL, '2026-09-24 16:23:50', '2026-09-24', '07:00:00', '09:00:00', 'Approved', ''),
(41, 3, 21, 1, 1, NULL, '2026-09-24 16:24:05', '2026-09-24', '08:00:00', '09:00:00', 'Approved', ''),
(42, 3, 21, 1, 1, NULL, '2026-09-24 16:25:19', '2026-09-26', '08:00:00', '10:00:00', 'Approved', ''),
(43, 3, 21, 1, 1, 4, '2026-09-24 16:25:30', '2026-09-26', '09:00:00', '10:00:00', 'Approved', 'hrdzhdh'),
(44, 3, 21, 2, 1, NULL, '2026-09-24 16:45:00', '2026-10-01', '14:00:00', '16:00:00', 'Approved', ''),
(45, 3, 21, 2, 1, 4, '2026-09-24 16:45:27', '2026-10-01', '15:00:00', '16:00:00', 'Approved', 'sfagag'),
(46, 3, 21, 2, 1, 4, '2026-09-24 16:48:01', '2026-10-02', '15:00:00', '16:00:00', 'Rejected', 'Maintenance'),
(48, 3, 21, 1, 1, NULL, '2026-09-24 17:40:26', '2026-10-07', '14:00:00', '16:00:00', 'Approved', ''),
(49, 3, 21, 1, 1, 4, '2026-09-24 17:40:58', '2026-10-07', '15:00:00', '16:00:00', 'Approved', 'fbhdndn'),
(50, 3, 21, 1, 1, NULL, '2026-09-24 17:41:23', '2026-10-10', '15:00:00', '16:00:00', 'Approved', ''),
(51, 1, 2, 1, 2, NULL, '2026-09-24 18:00:50', '2026-10-09', '15:00:00', '17:00:00', 'Approved', ''),
(52, 1, 2, 1, 2, NULL, '2026-09-24 18:02:07', '2026-10-07', '15:00:00', '16:00:00', 'Approved', ''),
(53, 5, 2, 1, 2, NULL, '2026-09-24 18:18:16', '2026-10-07', '17:00:00', '18:00:00', 'Approved', ''),
(54, 1, 2, 1, 2, NULL, '2026-09-25 10:17:41', '2026-09-25', '17:00:00', '19:00:00', 'Approved', ''),
(55, 1, 2, 1, 2, 4, '2026-09-25 10:20:02', '2026-09-25', '17:00:00', '18:00:00', 'Rejected', 'ane apita meka denna'),
(56, 1, 2, 2, 6, NULL, '2026-09-25 11:48:16', '2026-10-01', '15:00:00', '16:00:00', 'Cancelled', 'asfafagwsdgdvsv'),
(57, 1, 2, 2, 6, 4, '2026-09-25 12:00:13', '2026-10-01', '15:00:00', '16:00:00', 'Rejected', 'nope'),
(59, 6, 2, 2, 1, 4, '2026-09-25 12:41:28', '2026-10-01', '15:00:00', '16:00:00', 'Approved', 'dfhsfgjfhm'),
(60, 1, 2, 2, 6, 4, '2026-09-25 12:50:36', '2026-10-01', '15:00:00', '16:00:00', 'Approved', 'dsgshsdjdgj'),
(61, 1, 2, 2, 6, 4, '2026-09-25 12:55:19', '2026-10-01', '15:00:00', '16:00:00', 'Rejected', 'Maintenance'),
(62, 1, 2, 2, 6, NULL, '2026-09-25 13:11:59', '2026-10-01', '15:00:00', '16:00:00', 'Pending', 'wehawrhgb'),
(67, 1, 2, 2, 5, 4, '2026-09-26 01:58:58', '2026-09-25', '08:00:00', '09:00:00', 'Rejected', 'Maintenance'),
(68, 1, 2, 2, 5, 4, '2026-09-26 02:05:36', '2026-09-23', '10:00:00', '11:00:00', 'Approved', 'please we have special meet'),
(69, 2, 2, 1, 1, NULL, '2026-09-26 02:36:50', '2026-09-26', '08:00:00', '10:00:00', 'Approved', ''),
(71, 1, 2, 1, 5, NULL, '2026-09-26 03:14:26', '2026-09-24', '10:00:00', '11:00:00', 'Approved', ''),
(72, 1, 2, 1, 5, 4, '2026-09-26 03:15:36', '2026-09-23', '07:00:00', '08:00:00', 'Rejected', 'Sorry, cann\'t give access');

-- --------------------------------------------------------

--
-- Table structure for table `calorie_details`
--

CREATE TABLE `calorie_details` (
  `Detail_ID` int(10) UNSIGNED NOT NULL,
  `User_ID` int(10) UNSIGNED NOT NULL,
  `Date` date NOT NULL,
  `Type` enum('intake','burned') NOT NULL,
  `Item_Name` varchar(150) NOT NULL,
  `Category` varchar(50) DEFAULT NULL,
  `Portion_Or_Duration` varchar(50) DEFAULT NULL,
  `Calories` decimal(8,2) NOT NULL DEFAULT 0.00,
  `Carbs` decimal(6,2) DEFAULT 0.00,
  `Protein` decimal(6,2) DEFAULT 0.00,
  `Fat` decimal(6,2) DEFAULT 0.00,
  `Created_At` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `calorie_details`
--

INSERT INTO `calorie_details` (`Detail_ID`, `User_ID`, `Date`, `Type`, `Item_Name`, `Category`, `Portion_Or_Duration`, `Calories`, `Carbs`, `Protein`, `Fat`, `Created_At`) VALUES
(1, 2, '2026-09-26', 'intake', 'meat & eggs', 'Breakfast', '400g', 250.00, 50.00, 375.00, 150.00, '2026-09-26 03:19:10'),
(2, 11, '2026-09-17', 'intake', 'Fried rice', 'Dinner', '450g', 300.00, 150.00, 100.00, 200.00, '2026-09-26 14:11:44');

-- --------------------------------------------------------

--
-- Table structure for table `calorie_log`
--

CREATE TABLE `calorie_log` (
  `User_ID` int(10) UNSIGNED NOT NULL,
  `Date` date NOT NULL,
  `Calories_In` decimal(8,2) DEFAULT 0.00,
  `Calories_Out` decimal(8,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `calorie_log`
--

INSERT INTO `calorie_log` (`User_ID`, `Date`, `Calories_In`, `Calories_Out`) VALUES
(2, '2026-09-26', 250.00, 0.00),
(11, '2026-09-17', 300.00, 0.00);

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `facility`
--

INSERT INTO `facility` (`Facility_ID`, `Facility_Name`, `Location`, `Capacity`, `Open_Time`, `Close_Time`) VALUES
(1, 'Main Gym', 'Pavilion complex', 45, '06:00:00', '18:00:00'),
(2, 'Gym 2', 'Pavilion complex2', 10, '06:00:00', '18:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `facility_equipment`
--

CREATE TABLE `facility_equipment` (
  `Equipment_ID` int(10) UNSIGNED NOT NULL,
  `Facility_ID` int(10) UNSIGNED NOT NULL,
  `Equipment_Name` varchar(100) NOT NULL,
  `Quantity` int(10) UNSIGNED NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
(3, NULL),
(15, 1);

-- --------------------------------------------------------

--
-- Table structure for table `notification`
--

CREATE TABLE `notification` (
  `Notification_ID` int(10) UNSIGNED NOT NULL,
  `User_ID` int(10) UNSIGNED DEFAULT NULL,
  `Team_ID` int(10) UNSIGNED DEFAULT NULL,
  `Title` varchar(150) NOT NULL,
  `Message` text NOT NULL,
  `Created_At` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `notification`
--

INSERT INTO `notification` (`Notification_ID`, `User_ID`, `Team_ID`, `Title`, `Message`, `Created_At`) VALUES
(8, NULL, 1, 'Special Request Rejected', 'Your team\'s Special Request was Rejected. Reason: Maintenance', '2026-09-26 02:00:04'),
(9, 2, NULL, 'Special Request Approved', 'Your Special Request for a facility slot has been Approved.', '2026-09-26 02:06:05'),
(10, NULL, 1, 'Special Request Rejected', 'Your team \'UOC Track & Field\' special request for 2026-09-23 (07:00 AM - 08:00 AM) was Rejected. Reason: Sorry, cann\'t give access', '2026-09-26 03:16:19');

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
(1, 'UOC Track & Field', 'Athletics', 'Men'),
(2, 'UOC Basketball Men', 'Basketball', 'Men'),
(3, 'UOC Basketball Women', 'Basketball', 'Women'),
(4, 'UOC Swimming Team', 'Swimming', 'Mixed'),
(5, 'UOC Volleyball Men', 'Volleyball', 'Men'),
(6, 'UOC Cricket Men', 'Cricket', 'Men');

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
(1, 2, 'Captain'),
(1, 10, 'Member'),
(1, 11, 'Member'),
(1, 21, 'Member'),
(1, 22, 'Member'),
(2, 2, 'Captain'),
(3, 21, 'Captain'),
(4, 22, 'Captain'),
(5, 2, 'Member'),
(5, 21, 'Captain'),
(5, 22, 'Member'),
(6, 2, 'Member'),
(6, 21, 'Captain');

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
  `Registration_Photo` varchar(255) DEFAULT NULL,
  `Status` enum('active','pending','suspended') NOT NULL DEFAULT 'active',
  `Life_Percentage` tinyint(3) UNSIGNED NOT NULL DEFAULT 100,
  `Created_At` timestamp NOT NULL DEFAULT current_timestamp(),
  `Emergency_Contact` varchar(150) DEFAULT NULL,
  `Student_ID_Front` varchar(255) DEFAULT NULL,
  `Student_ID_Back` varchar(255) DEFAULT NULL,
  `Date_of_Final_Exam` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `university_student`
--

INSERT INTO `university_student` (`User_ID`, `Registration_Number`, `NIC`, `DOB`, `Faculty`, `QR_Token`, `Gender`, `Profile_Image`, `Registration_Photo`, `Status`, `Life_Percentage`, `Created_At`, `Emergency_Contact`, `Student_ID_Front`, `Student_ID_Back`, `Date_of_Final_Exam`) VALUES
(2, '2022CS002', '200187654321', '2001-08-20', 'Computing', NULL, NULL, NULL, NULL, 'active', 20, '2026-08-17 11:20:44', NULL, NULL, NULL, NULL),
(10, '2024cs107', '200334400460', '2003-12-09', 'School of Computing', NULL, 'male', 'avatar_10_1789592884.jpeg', 'avatar_6aab01406ecd0.jpg', 'active', 80, '2026-09-16 20:51:12', '0765414600', 'id_f_6aab01406de0c.jpg', 'id_b_6aab01406e54a.jpg', NULL),
(11, '20147stu4law', '200444400460', '2005-06-07', 'Faculty of Law', NULL, 'male', 'avatar_6ab40a5a0cd48.jpg', 'avatar_6ab40a5a0cd48.jpg', 'active', 100, '2026-09-23 17:20:26', '0112946861', 'id_f_6ab40a5a0bc0b.jpg', 'id_b_6ab40a5a0c5af.jpg', NULL),
(21, '2022CS021', '200112300021', '2001-01-10', 'Computing', NULL, NULL, 'default_avatar.png', NULL, 'active', 100, '2026-09-24 09:15:20', NULL, NULL, NULL, NULL),
(22, '2022CS022', '200112300022', '2001-02-15', 'Computing', NULL, NULL, 'default_avatar.png', NULL, 'active', 100, '2026-09-24 09:15:20', NULL, NULL, NULL, NULL),
(23, '2024/cs/083', '200744400460', '2004-07-20', 'Faculty of Arts', NULL, 'male', 'avatar_6ab62a0725a44.jpeg', 'avatar_6ab62a0725a44.jpeg', 'active', 100, '2026-09-25 08:00:07', '0112946861', 'id_f_6ab62a0725161.png', 'id_b_6ab62a0725613.png', NULL);

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
(2, 'Ravindu', 'Lochana', 'captain@stu.cmb.ac.lk', '$2y$10$YcUbwJRQy3fTsfCpHFntkuJOZoSNLb6Lt21ZOjlxAV69FIaBn.JZ6', 'Student'),
(3, 'Coach', 'Nuwan', 'instructor@cmb.ac.lk', '$2y$10$YcUbwJRQy3fTsfCpHFntkuJOZoSNLb6Lt21ZOjlxAV69FIaBn.JZ6', 'Instructor'),
(4, 'System', 'Admin', 'admin@cmb.ac.lk', '$2y$10$YcUbwJRQy3fTsfCpHFntkuJOZoSNLb6Lt21ZOjlxAV69FIaBn.JZ6', 'Admin'),
(10, 'Kulathunga', 'R.L.W.', 'ravindukulathunga8@gmail.com', '$2y$10$0h.CpOUvCaNo15P/3zE.FuCrACyTnu1K7EjwIxYGn.eJckBL3pU6O', 'Student'),
(11, 'sunil', 'kularathna', 'ravindulochana3002@gmail.com', '$2y$10$ipfW/W903EY24pPZ7x6SMORFtdHcVPxVwyRcF.kbhWuFSS5Ge5BAq', 'Student'),
(15, 'Jagath', 'Perera', 'chandimak188@gmail.com', '$2y$10$vZJPNK9XHq/pRqAxu1d7KuH7tizWkvEfqGtZ0akq1Cfj.Vp56SDji', 'Instructor'),
(21, 'Sadun', 'Kumara', 'sadun@stu.cmb.ac.lk', '$2y$10$YcUbwJRQy3fTsfCpHFntkuJOZoSNLb6Lt21ZOjlxAV69FIaBn.JZ6', 'Student'),
(22, 'Amal', 'Fernando', 'amal@stu.cmb.ac.lk', '$2y$10$YcUbwJRQy3fTsfCpHFntkuJOZoSNLb6Lt21ZOjlxAV69FIaBn.JZ6', 'Student'),
(23, 'Super', 'Nova', 'member@stu2.cmb.ac.lk', '$2y$10$vbDTKD6UKISZ5bdW9U8LY.74RiMBap/Y.07Z7RSf7b4WWLWp1v7de', 'Student');

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
-- Indexes for table `calorie_details`
--
ALTER TABLE `calorie_details`
  ADD PRIMARY KEY (`Detail_ID`),
  ADD KEY `fk_calorie_details_user` (`User_ID`),
  ADD KEY `idx_user_date` (`User_ID`,`Date`);

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
-- Indexes for table `notification`
--
ALTER TABLE `notification`
  ADD PRIMARY KEY (`Notification_ID`),
  ADD KEY `fk_notification_user` (`User_ID`),
  ADD KEY `fk_notification_team` (`Team_ID`);

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
  MODIFY `Booking_ID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=73;

--
-- AUTO_INCREMENT for table `calorie_details`
--
ALTER TABLE `calorie_details`
  MODIFY `Detail_ID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

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
  MODIFY `Facility_ID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

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
-- AUTO_INCREMENT for table `notification`
--
ALTER TABLE `notification`
  MODIFY `Notification_ID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

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
  MODIFY `Team_ID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `team_workout`
--
ALTER TABLE `team_workout`
  MODIFY `Workout_ID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `User_ID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

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
-- Constraints for table `calorie_details`
--
ALTER TABLE `calorie_details`
  ADD CONSTRAINT `fk_calorie_details_user` FOREIGN KEY (`User_ID`) REFERENCES `user` (`User_ID`) ON DELETE CASCADE ON UPDATE CASCADE;

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
-- Constraints for table `notification`
--
ALTER TABLE `notification`
  ADD CONSTRAINT `fk_notification_team` FOREIGN KEY (`Team_ID`) REFERENCES `team` (`Team_ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_notification_user` FOREIGN KEY (`User_ID`) REFERENCES `user` (`User_ID`) ON DELETE CASCADE ON UPDATE CASCADE;

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
