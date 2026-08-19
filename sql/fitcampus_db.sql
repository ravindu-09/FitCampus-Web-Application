-- ============================================================
-- FITCAMPUS DATABASE
-- ============================================================

CREATE DATABASE IF NOT EXISTS `fitcampus_db`
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_general_ci;

USE `fitcampus_db`;


-- ============================================================
-- 1. USER
-- ============================================================

CREATE TABLE `USER` (
    `User_ID` INT UNSIGNED AUTO_INCREMENT,
    `First_Name` VARCHAR(50) NOT NULL,
    `Last_Name` VARCHAR(50) NOT NULL,
    `Email` VARCHAR(100) NOT NULL UNIQUE,
    `Password` VARCHAR(255) NOT NULL,
    `Role` ENUM('Student', 'Instructor', 'Admin') NOT NULL,

    PRIMARY KEY (`User_ID`)
) ENGINE=InnoDB;


-- ============================================================
-- 2. FACILITY
-- ============================================================

CREATE TABLE `FACILITY` (
    `Facility_ID` INT UNSIGNED AUTO_INCREMENT,
    `Facility_Name` VARCHAR(100) NOT NULL,
    `Location` VARCHAR(255),
    `Capacity` INT UNSIGNED NOT NULL,
    `Open_Time` TIME NOT NULL,
    `Close_Time` TIME NOT NULL,

    PRIMARY KEY (`Facility_ID`),

    CONSTRAINT `chk_facility_capacity`
        CHECK (`Capacity` > 0)
) ENGINE=InnoDB;


-- ============================================================
-- 3. ADMIN
-- ============================================================

CREATE TABLE `ADMIN` (
    `Admin_ID` INT UNSIGNED,

    PRIMARY KEY (`Admin_ID`),

    CONSTRAINT `fk_admin_user`
        FOREIGN KEY (`Admin_ID`)
        REFERENCES `USER` (`User_ID`)
        ON UPDATE CASCADE
        ON DELETE CASCADE
) ENGINE=InnoDB;


-- ============================================================
-- 4. INSTRUCTOR
-- ============================================================

CREATE TABLE `INSTRUCTOR` (
    `Instructor_ID` INT UNSIGNED,
    `Facility_ID` INT UNSIGNED,

    PRIMARY KEY (`Instructor_ID`),

    CONSTRAINT `fk_instructor_user`
        FOREIGN KEY (`Instructor_ID`)
        REFERENCES `USER` (`User_ID`)
        ON UPDATE CASCADE
        ON DELETE CASCADE,

    CONSTRAINT `fk_instructor_facility`
        FOREIGN KEY (`Facility_ID`)
        REFERENCES `FACILITY` (`Facility_ID`)
        ON UPDATE CASCADE
        ON DELETE SET NULL
) ENGINE=InnoDB;


-- ============================================================
-- 5. UNIVERSITY STUDENT
-- ============================================================

CREATE TABLE `UNIVERSITY_STUDENT` (
    `User_ID` INT UNSIGNED,
    `Registration_Number` VARCHAR(50) NOT NULL UNIQUE,
    `DOB` DATE NOT NULL,
    `Faculty` VARCHAR(100),
    `QR_Token` VARCHAR(255) UNIQUE,
    `Gender` VARCHAR(30),
    `Date_of_Final_Exam` DATE,

    PRIMARY KEY (`User_ID`),

    CONSTRAINT `fk_student_user`
        FOREIGN KEY (`User_ID`)
        REFERENCES `USER` (`User_ID`)
        ON UPDATE CASCADE
        ON DELETE CASCADE
) ENGINE=InnoDB;


-- ============================================================
-- 6. FACILITY EQUIPMENT
-- ============================================================

CREATE TABLE `FACILITY_EQUIPMENT` (
    `Equipment_ID` INT UNSIGNED AUTO_INCREMENT,
    `Facility_ID` INT UNSIGNED NOT NULL,
    `Equipment_Name` VARCHAR(100) NOT NULL,
    `Quantity` INT UNSIGNED NOT NULL DEFAULT 0,

    PRIMARY KEY (`Equipment_ID`),

    CONSTRAINT `fk_equipment_facility`
        FOREIGN KEY (`Facility_ID`)
        REFERENCES `FACILITY` (`Facility_ID`)
        ON UPDATE CASCADE
        ON DELETE CASCADE,

    CONSTRAINT `chk_equipment_quantity`
        CHECK (`Quantity` >= 0)
) ENGINE=InnoDB;


-- ============================================================
-- 7. EXERCISE
-- ============================================================

CREATE TABLE `EXERCISE` (
    `Exercise_ID` INT UNSIGNED AUTO_INCREMENT,
    `Exercise_Name` VARCHAR(100) NOT NULL UNIQUE,
    `Description` TEXT,
    `Demo_Link` VARCHAR(500),
    `Type` VARCHAR(50),

    PRIMARY KEY (`Exercise_ID`)
) ENGINE=InnoDB;


-- ============================================================
-- 8. TEAM
-- ============================================================

CREATE TABLE `TEAM` (
    `Team_ID` INT UNSIGNED AUTO_INCREMENT,
    `Team_Name` VARCHAR(100) NOT NULL,
    `Sport` VARCHAR(100) NOT NULL,
    `Sport_Gender` VARCHAR(30),

    PRIMARY KEY (`Team_ID`)
) ENGINE=InnoDB;


-- ============================================================
-- 9. TEAM MEMBER
-- ============================================================

CREATE TABLE `TEAM_MEMBER` (
    `Team_ID` INT UNSIGNED,
    `User_ID` INT UNSIGNED,
    `Role_In_Team` ENUM('Captain', 'Member') NOT NULL DEFAULT 'Member',

    PRIMARY KEY (`Team_ID`, `User_ID`),

    CONSTRAINT `fk_team_member_team`
        FOREIGN KEY (`Team_ID`)
        REFERENCES `TEAM` (`Team_ID`)
        ON UPDATE CASCADE
        ON DELETE CASCADE,

    CONSTRAINT `fk_team_member_user`
        FOREIGN KEY (`User_ID`)
        REFERENCES `USER` (`User_ID`)
        ON UPDATE CASCADE
        ON DELETE CASCADE
) ENGINE=InnoDB;


-- ============================================================
-- 10. BOOKING
-- ============================================================

CREATE TABLE `BOOKING` (
    `Booking_ID` INT UNSIGNED AUTO_INCREMENT,
    `Team_ID` INT UNSIGNED NOT NULL,
    `Requested_By` INT UNSIGNED NOT NULL,
    `Facility_ID` INT UNSIGNED NOT NULL,
    `Approved_By` INT UNSIGNED NULL,
    `Booking_Time` DATETIME NOT NULL,
    `Reserve_Date` DATE NOT NULL,
    `Start_Time` TIME NOT NULL,
    `End_Time` TIME NOT NULL,
    `Status` ENUM('Pending', 'Approved', 'Rejected', 'Cancelled') NOT NULL DEFAULT 'Pending',
    `Exception_Reason` TEXT,

    PRIMARY KEY (`Booking_ID`),

    CONSTRAINT `fk_booking_team`
        FOREIGN KEY (`Team_ID`)
        REFERENCES `TEAM` (`Team_ID`)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT `fk_booking_requested_by`
        FOREIGN KEY (`Requested_By`)
        REFERENCES `USER` (`User_ID`)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT `fk_booking_facility`
        FOREIGN KEY (`Facility_ID`)
        REFERENCES `FACILITY` (`Facility_ID`)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT `fk_booking_approved_by`
        FOREIGN KEY (`Approved_By`)
        REFERENCES `USER` (`User_ID`)
        ON UPDATE CASCADE
        ON DELETE SET NULL,

    CONSTRAINT `chk_booking_time`
        CHECK (`End_Time` > `Start_Time`)
) ENGINE=InnoDB;


-- ============================================================
-- 11. ATTENDANCE
-- ============================================================

CREATE TABLE `ATTENDANCE` (
    `Attendance_ID` INT UNSIGNED AUTO_INCREMENT,
    `User_ID` INT UNSIGNED NOT NULL,
    `Facility_ID` INT UNSIGNED NOT NULL,
    `Check_In_Time` DATETIME NOT NULL,
    `Check_Out_Time` DATETIME NULL,
    `Check_In_Method` VARCHAR(30),
    `Check_Out_Method` VARCHAR(30),
    `Active_Status` BOOLEAN NOT NULL DEFAULT TRUE,

    PRIMARY KEY (`Attendance_ID`),

    CONSTRAINT `fk_attendance_user`
        FOREIGN KEY (`User_ID`)
        REFERENCES `USER` (`User_ID`)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT `fk_attendance_facility`
        FOREIGN KEY (`Facility_ID`)
        REFERENCES `FACILITY` (`Facility_ID`)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
) ENGINE=InnoDB;


-- ============================================================
-- 12. COMMON WORKOUT
-- ============================================================

CREATE TABLE `COMMON_WORKOUT` (
    `Common_Workout_ID` INT UNSIGNED AUTO_INCREMENT,
    `Instructor_ID` INT UNSIGNED NOT NULL,
    `Workout_Level` VARCHAR(50),
    `Duration` INT UNSIGNED,
    `Title` VARCHAR(150) NOT NULL,

    PRIMARY KEY (`Common_Workout_ID`),

    CONSTRAINT `fk_common_workout_instructor`
        FOREIGN KEY (`Instructor_ID`)
        REFERENCES `INSTRUCTOR` (`Instructor_ID`)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
) ENGINE=InnoDB;


-- ============================================================
-- 13. COMMON WORKOUT EXERCISE
-- ============================================================

CREATE TABLE `COMMON_WORKOUT_EXERCISE` (
    `Common_Workout_ID` INT UNSIGNED,
    `Exercise_ID` INT UNSIGNED,
    `Sets` INT UNSIGNED,
    `Reps` INT UNSIGNED,

    PRIMARY KEY (`Common_Workout_ID`, `Exercise_ID`),

    CONSTRAINT `fk_common_workout_exercise_workout`
        FOREIGN KEY (`Common_Workout_ID`)
        REFERENCES `COMMON_WORKOUT` (`Common_Workout_ID`)
        ON UPDATE CASCADE
        ON DELETE CASCADE,

    CONSTRAINT `fk_common_workout_exercise_exercise`
        FOREIGN KEY (`Exercise_ID`)
        REFERENCES `EXERCISE` (`Exercise_ID`)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
) ENGINE=InnoDB;


-- ============================================================
-- 14. PERSONAL WORKOUT
-- ============================================================

CREATE TABLE `PERSONAL_WORKOUT` (
    `Personal_Workout_ID` INT UNSIGNED AUTO_INCREMENT,
    `User_ID` INT UNSIGNED NOT NULL,
    `Duration` INT UNSIGNED,
    `Title` VARCHAR(150) NOT NULL,

    PRIMARY KEY (`Personal_Workout_ID`),

    CONSTRAINT `fk_personal_workout_user`
        FOREIGN KEY (`User_ID`)
        REFERENCES `USER` (`User_ID`)
        ON UPDATE CASCADE
        ON DELETE CASCADE
) ENGINE=InnoDB;


-- ============================================================
-- 15. PERSONAL WORKOUT EXERCISE
-- ============================================================

CREATE TABLE `PERSONAL_WORKOUT_EXERCISE` (
    `Personal_Workout_ID` INT UNSIGNED,
    `Exercise_ID` INT UNSIGNED,
    `Sets` INT UNSIGNED,
    `Reps` INT UNSIGNED,

    PRIMARY KEY (`Personal_Workout_ID`, `Exercise_ID`),

    CONSTRAINT `fk_personal_workout_exercise_workout`
        FOREIGN KEY (`Personal_Workout_ID`)
        REFERENCES `PERSONAL_WORKOUT` (`Personal_Workout_ID`)
        ON UPDATE CASCADE
        ON DELETE CASCADE,

    CONSTRAINT `fk_personal_workout_exercise_exercise`
        FOREIGN KEY (`Exercise_ID`)
        REFERENCES `EXERCISE` (`Exercise_ID`)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
) ENGINE=InnoDB;


-- ============================================================
-- 16. TEAM WORKOUT
-- ============================================================

CREATE TABLE `TEAM_WORKOUT` (
    `Workout_ID` INT UNSIGNED AUTO_INCREMENT,
    `Team_ID` INT UNSIGNED NOT NULL,
    `Booking_ID` INT UNSIGNED NOT NULL,
    `Title` VARCHAR(150) NOT NULL,
    `Description` TEXT,
    `Duration` INT UNSIGNED,

    PRIMARY KEY (`Workout_ID`),

    CONSTRAINT `fk_team_workout_team`
        FOREIGN KEY (`Team_ID`)
        REFERENCES `TEAM` (`Team_ID`)
        ON UPDATE CASCADE
        ON DELETE CASCADE,

    CONSTRAINT `fk_team_workout_booking`
        FOREIGN KEY (`Booking_ID`)
        REFERENCES `BOOKING` (`Booking_ID`)
        ON UPDATE CASCADE
        ON DELETE CASCADE
) ENGINE=InnoDB;


-- ============================================================
-- 17. TEAM WORKOUT EXERCISE
-- ============================================================

CREATE TABLE `TEAM_WORKOUT_EXERCISE` (
    `Workout_ID` INT UNSIGNED,
    `Exercise_ID` INT UNSIGNED,
    `Sets` INT UNSIGNED,
    `Reps` INT UNSIGNED,

    PRIMARY KEY (`Workout_ID`, `Exercise_ID`),

    CONSTRAINT `fk_team_workout_exercise_workout`
        FOREIGN KEY (`Workout_ID`)
        REFERENCES `TEAM_WORKOUT` (`Workout_ID`)
        ON UPDATE CASCADE
        ON DELETE CASCADE,

    CONSTRAINT `fk_team_workout_exercise_exercise`
        FOREIGN KEY (`Exercise_ID`)
        REFERENCES `EXERCISE` (`Exercise_ID`)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
) ENGINE=InnoDB;


-- ============================================================
-- 18. GYM RULE
-- ============================================================

CREATE TABLE `GYM_RULE` (
    `Rule_No` INT UNSIGNED AUTO_INCREMENT,
    `Rule` TEXT NOT NULL,
    `Penalty_For_Violation` TEXT,
    `Instructor_ID` INT UNSIGNED NOT NULL,

    PRIMARY KEY (`Rule_No`),

    CONSTRAINT `fk_gym_rule_instructor`
        FOREIGN KEY (`Instructor_ID`)
        REFERENCES `INSTRUCTOR` (`Instructor_ID`)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
) ENGINE=InnoDB;


-- ============================================================
-- 19. GOAL
-- ============================================================

CREATE TABLE `GOAL` (
    `Goal_ID` INT UNSIGNED AUTO_INCREMENT,
    `User_ID` INT UNSIGNED NOT NULL,
    `Goal_Type` VARCHAR(100) NOT NULL,
    `Target` DECIMAL(10,2),
    `Start_Date` DATE,
    `End_Date` DATE,
    `Status` VARCHAR(30),

    PRIMARY KEY (`Goal_ID`),

    CONSTRAINT `fk_goal_user`
        FOREIGN KEY (`User_ID`)
        REFERENCES `USER` (`User_ID`)
        ON UPDATE CASCADE
        ON DELETE CASCADE,

    CONSTRAINT `chk_goal_dates`
        CHECK (`End_Date` IS NULL OR `Start_Date` IS NULL OR `End_Date` >= `Start_Date`)
) ENGINE=InnoDB;


-- ============================================================
-- 20. CALORIE LOG
-- ============================================================

CREATE TABLE `CALORIE_LOG` (
    `User_ID` INT UNSIGNED,
    `Date` DATE,
    `Calories_In` DECIMAL(8,2) DEFAULT 0,
    `Calories_Out` DECIMAL(8,2) DEFAULT 0,

    PRIMARY KEY (`User_ID`, `Date`),

    CONSTRAINT `fk_calorie_log_user`
        FOREIGN KEY (`User_ID`)
        REFERENCES `USER` (`User_ID`)
        ON UPDATE CASCADE
        ON DELETE CASCADE,

    CONSTRAINT `chk_calories_in`
        CHECK (`Calories_In` >= 0),

    CONSTRAINT `chk_calories_out`
        CHECK (`Calories_Out` >= 0)
) ENGINE=InnoDB;


-- ============================================================
-- 21. FEEDBACK
-- ============================================================

CREATE TABLE `FEEDBACK` (
    `Feedback_ID` INT UNSIGNED AUTO_INCREMENT,
    `User_ID` INT UNSIGNED NOT NULL,
    `Responded_Admin` INT UNSIGNED NULL,
    `Category` VARCHAR(50),
    `Description` TEXT NOT NULL,
    `Submitted_Date` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `Status` ENUM('Pending', 'In Progress', 'Resolved', 'Rejected') NOT NULL DEFAULT 'Pending',
    `Response_Date` DATETIME NULL,
    `Response` TEXT,

    PRIMARY KEY (`Feedback_ID`),

    CONSTRAINT `fk_feedback_user`
        FOREIGN KEY (`User_ID`)
        REFERENCES `USER` (`User_ID`)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT `fk_feedback_admin`
        FOREIGN KEY (`Responded_Admin`)
        REFERENCES `ADMIN` (`Admin_ID`)
        ON UPDATE CASCADE
        ON DELETE SET NULL
) ENGINE=InnoDB;


-- ============================================================
-- 22. ANNOUNCEMENT
-- ============================================================

CREATE TABLE `ANNOUNCEMENT` (
    `Announcement_ID` INT UNSIGNED AUTO_INCREMENT,
    `Admin_ID` INT UNSIGNED NOT NULL,
    `Title` VARCHAR(150) NOT NULL,
    `Description` TEXT NOT NULL,
    `Publish_Date` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (`Announcement_ID`),

    CONSTRAINT `fk_announcement_admin`
        FOREIGN KEY (`Admin_ID`)
        REFERENCES `ADMIN` (`Admin_ID`)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
) ENGINE=InnoDB;


-- ============================================================
-- 23. PENALTY
-- ============================================================

CREATE TABLE `PENALTY` (
    `Penalty_ID` INT UNSIGNED AUTO_INCREMENT,
    `User_ID` INT UNSIGNED NOT NULL,
    `Team_ID` INT UNSIGNED NULL,
    `Instructor_ID` INT UNSIGNED NULL,
    `Description` TEXT NOT NULL,
    `Date` DATE NOT NULL,
    `Severity` VARCHAR(30),

    PRIMARY KEY (`Penalty_ID`),

    CONSTRAINT `fk_penalty_user`
        FOREIGN KEY (`User_ID`)
        REFERENCES `USER` (`User_ID`)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT `fk_penalty_team`
        FOREIGN KEY (`Team_ID`)
        REFERENCES `TEAM` (`Team_ID`)
        ON UPDATE CASCADE
        ON DELETE SET NULL,

    CONSTRAINT `fk_penalty_instructor`
        FOREIGN KEY (`Instructor_ID`)
        REFERENCES `INSTRUCTOR` (`Instructor_ID`)
        ON UPDATE CASCADE
        ON DELETE SET NULL
) ENGINE=InnoDB;
