-- Create the booking-to-reusable-team-workout junction table.
CREATE TABLE IF NOT EXISTS `booking_workout` (
  `Booking_ID` int(10) UNSIGNED NOT NULL,
  `Workout_ID` int(10) UNSIGNED NOT NULL,
  PRIMARY KEY (`Booking_ID`,`Workout_ID`),
  KEY `fk_booking_workout_workout` (`Workout_ID`),
  CONSTRAINT `fk_booking_workout_booking` FOREIGN KEY (`Booking_ID`) REFERENCES `booking` (`Booking_ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_booking_workout_workout` FOREIGN KEY (`Workout_ID`) REFERENCES `team_workout` (`Workout_ID`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
