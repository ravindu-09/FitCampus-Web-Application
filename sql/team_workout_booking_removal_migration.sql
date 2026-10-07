-- Run once on local databases that still use the legacy TEAM_WORKOUT schema.
-- TEAM_WORKOUT is empty in the current project database, so no workout records
-- or booking associations need to be migrated.
ALTER TABLE `team_workout`
  DROP FOREIGN KEY `fk_team_workout_booking`,
  DROP INDEX `fk_team_workout_booking`,
  DROP COLUMN `Booking_ID`;
