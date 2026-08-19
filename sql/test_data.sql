-- all the test data for the database is inserted here

USE `fitcampus_db`;

-- ============================================================
-- FACILITIES
-- ============================================================

INSERT INTO `FACILITY`
(`Facility_Name`, `Location`, `Capacity`, `Open_Time`, `Close_Time`)
VALUES
('University Gym 1', 'Main Campus', 50, '06:00:00', '21:00:00'),
('University Gym 2', 'Sports Complex', 40, '06:00:00', '21:00:00');


-- ============================================================
-- USERS
-- ============================================================

INSERT INTO `USER`
(`First_Name`, `Last_Name`, `Email`, `Password`, `Role`)
VALUES
('Admin', 'User', 'admin@fitcampus.test', 'TEST_PASSWORD', 'Admin'),
('John', 'Instructor', 'john@fitcampus.test', 'TEST_PASSWORD', 'Instructor'),
('Alice', 'Perera', 'alice@fitcampus.test', 'TEST_PASSWORD', 'Student'),
('Bob', 'Fernando', 'bob@fitcampus.test', 'TEST_PASSWORD', 'Student'),
('Charlie', 'Silva', 'charlie@fitcampus.test', 'TEST_PASSWORD', 'Student'),
('Diana', 'Perera', 'diana@fitcampus.test', 'TEST_PASSWORD', 'Student'),
('Ethan', 'Fernando', 'ethan@fitcampus.test', 'TEST_PASSWORD', 'Student');


-- ============================================================
-- ADMIN
-- ============================================================

INSERT INTO `ADMIN`
(`Admin_ID`)
VALUES
(1);


-- ============================================================
-- INSTRUCTOR
-- ============================================================

INSERT INTO `INSTRUCTOR`
(`Instructor_ID`, `Facility_ID`)
VALUES
(2, 1);


-- ============================================================
-- UNIVERSITY STUDENTS
-- ============================================================

INSERT INTO `UNIVERSITY_STUDENT`
(`User_ID`, `Registration_Number`, `DOB`, `Faculty`,
 `QR_Token`, `Gender`, `Date_of_Final_Exam`)
VALUES
(3, 'UOC001', '2004-05-15', 'Computing',
 'QR-ALICE-001', 'Female', '2027-06-30'),

(4, 'UOC002', '2003-11-20', 'Science',
 'QR-BOB-002', 'Male', '2027-06-30'),

(5, 'UOC003', '2004-02-10', 'Computing',
 'QR-CHARLIE-003', 'Male', '2027-06-30'),

(6, 'UOC004', '2004-08-22', 'Management',
 'QR-DIANA-004', 'Female', '2027-06-30'),

(7, 'UOC005', '2003-12-05', 'Computing',
 'QR-ETHAN-005', 'Male', '2027-06-30');


-- ============================================================
-- EQUIPMENT
-- ============================================================

INSERT INTO `FACILITY_EQUIPMENT`
(`Facility_ID`, `Equipment_Name`, `Quantity`)
VALUES
(1, 'Treadmill', 5),
(1, 'Bench Press', 4),
(1, 'Dumbbell Set', 10),
(1, 'Squat Rack', 3),
(2, 'Treadmill', 4),
(2, 'Bench Press', 3),
(2, 'Dumbbell Set', 8);


-- ============================================================
-- EXERCISES
-- ============================================================

INSERT INTO `EXERCISE`
(`Exercise_Name`, `Description`, `Demo_Link`, `Type`)
VALUES
('Barbell Squat',
 'Compound lower-body exercise.',
 NULL,
 'Strength'),

('Bench Press',
 'Upper-body pushing exercise.',
 NULL,
 'Strength'),

('Push Up',
 'Bodyweight upper-body exercise.',
 NULL,
 'Bodyweight'),

('Plank',
 'Core stability exercise.',
 NULL,
 'Core'),

('Running',
 'Cardiovascular running exercise.',
 NULL,
 'Cardio');


-- ============================================================
-- TEAMS
-- ============================================================

INSERT INTO `TEAM`
(`Team_Name`, `Sport`, `Sport_Gender`)
VALUES
('University Basketball Team', 'Basketball', 'Mixed'),
('University Rugby Team', 'Rugby', 'Male');


-- ============================================================
-- TEAM MEMBERS
-- ============================================================

-- Alice is captain of Basketball
INSERT INTO `TEAM_MEMBER`
(`Team_ID`, `User_ID`, `Role_In_Team`)
VALUES
(1, 3, 'Captain'),
(1, 4, 'Member'),
(1, 5, 'Member'),

-- Alice is ALSO captain of Rugby
(2, 3, 'Captain'),
(2, 6, 'Member'),
(2, 7, 'Member');


-- ============================================================
-- COMMON WORKOUT
-- ============================================================

INSERT INTO `COMMON_WORKOUT`
(`Instructor_ID`, `Workout_Level`, `Duration`, `Title`)
VALUES
(2, 'Beginner', 45, 'Beginner Full Body'),
(2, 'Intermediate', 60, 'Intermediate Strength');


-- ============================================================
-- COMMON WORKOUT EXERCISES
-- ============================================================

INSERT INTO `COMMON_WORKOUT_EXERCISE`
(`Common_Workout_ID`, `Exercise_ID`, `Sets`, `Reps`)
VALUES
(1, 1, 3, 10),
(1, 3, 3, 12),
(1, 4, 3, 30),
(2, 1, 4, 8),
(2, 2, 4, 8);


-- ============================================================
-- PERSONAL WORKOUT
-- ============================================================

INSERT INTO `PERSONAL_WORKOUT`
(`User_ID`, `Duration`, `Title`)
VALUES
(3, 45, 'Alice Personal Workout'),
(4, 60, 'Bob Strength Workout');


-- ============================================================
-- PERSONAL WORKOUT EXERCISES
-- ============================================================

INSERT INTO `PERSONAL_WORKOUT_EXERCISE`
(`Personal_Workout_ID`, `Exercise_ID`, `Sets`, `Reps`)
VALUES
(1, 1, 3, 10),
(1, 4, 3, 30),
(2, 2, 4, 8);


-- ============================================================
-- BOOKING
-- ============================================================

INSERT INTO `BOOKING`
(`Team_ID`, `Requested_By`, `Facility_ID`,
 `Booking_Time`, `Reserve_Date`,
 `Start_Time`, `End_Time`,
 `Status`, `Exception_Reason`)
VALUES
(1, 3, 1,
 NOW(), CURDATE(),
 '16:00:00', '18:00:00',
 'Pending', NULL);


-- ============================================================
-- TEAM WORKOUT
-- ============================================================

INSERT INTO `TEAM_WORKOUT`
(`Team_ID`, `Booking_ID`, `Title`, `Description`, `Duration`)
VALUES
(1, 1, 'Basketball Strength Session',
 'Team strength and conditioning session.',
 90);


INSERT INTO `TEAM_WORKOUT_EXERCISE`
(`Workout_ID`, `Exercise_ID`, `Sets`, `Reps`)
VALUES
(1, 1, 3, 10),
(1, 2, 3, 10);


-- ============================================================
-- ATTENDANCE
-- ============================================================

-- Alice currently inside Gym 1
INSERT INTO `ATTENDANCE`
(`User_ID`, `Facility_ID`,
 `Check_In_Time`, `Check_Out_Time`,
 `Check_In_Method`, `Check_Out_Method`,
 `Active_Status`)
VALUES
(3, 1, NOW(), NULL, 'QR', NULL, TRUE);


-- Bob has already left
INSERT INTO `ATTENDANCE`
(`User_ID`, `Facility_ID`,
 `Check_In_Time`, `Check_Out_Time`,
 `Check_In_Method`, `Check_Out_Method`,
 `Active_Status`)
VALUES
(4, 1,
 DATE_SUB(NOW(), INTERVAL 2 HOUR),
 DATE_SUB(NOW(), INTERVAL 1 HOUR),
 'QR', 'QR', FALSE);


-- ============================================================
-- GOALS
-- ============================================================

INSERT INTO `GOAL`
(`User_ID`, `Goal_Type`, `Target`,
 `Start_Date`, `End_Date`, `Status`)
VALUES
(3, 'Weekly Gym Visits', 4, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 7 DAY), 'Active'),
(4, 'Weight Training Sessions', 3, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 7 DAY), 'Active');


-- ============================================================
-- CALORIE LOG
-- ============================================================

INSERT INTO `CALORIE_LOG`
(`User_ID`, `Date`, `Calories_In`, `Calories_Out`)
VALUES
(3, CURDATE(), 2100, 500),
(4, CURDATE(), 2400, 650);


-- ============================================================
-- GYM RULES
-- ============================================================

INSERT INTO `GYM_RULE`
(`Rule`, `Penalty_For_Violation`, `Instructor_ID`)
VALUES
('Return equipment after use.',
 'Temporary suspension of gym access.',
 2),

('Use appropriate safety equipment.',
 'Warning or temporary suspension.',
 2);


-- ============================================================
-- ANNOUNCEMENTS
-- ============================================================

INSERT INTO `ANNOUNCEMENT`
(`Admin_ID`, `Title`, `Description`)
VALUES
(1,
 'Welcome to FitCampus',
 'Welcome to the FitCampus university fitness management system.');


-- ============================================================
-- FEEDBACK
-- ============================================================

INSERT INTO `FEEDBACK`
(`User_ID`, `Category`, `Description`)
VALUES
(3,
 'Gym Facility',
 'The dumbbell area becomes crowded during peak hours.');


-- ============================================================
-- PENALTY
-- ============================================================

INSERT INTO `PENALTY`
(`User_ID`, `Team_ID`, `Instructor_ID`,
 `Description`, `Date`, `Severity`)
VALUES
(4, 1, 2,
 'Equipment was not returned after use.',
 CURDATE(),
 'Low');
