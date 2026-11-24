-- Adds auditable visit points and life-bar penalties.
-- Do not run automatically from application requests. Existing attendance and
-- penalty rows are left unchanged and are not backfilled into the points ledger.

DELIMITER //

DROP PROCEDURE IF EXISTS add_member_leaderboard_life_bar//
CREATE PROCEDURE add_member_leaderboard_life_bar()
BEGIN
    IF NOT EXISTS (
        SELECT 1
        FROM information_schema.STATISTICS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'attendance'
          AND INDEX_NAME = 'idx_attendance_user_active_checkout'
    ) THEN
        ALTER TABLE `attendance`
            ADD KEY `idx_attendance_user_active_checkout`
                (`User_ID`, `Active_Status`, `Check_Out_Time`);
    END IF;

    IF NOT EXISTS (
        SELECT 1 FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'penalty'
          AND COLUMN_NAME = 'Requested_Life_Deduction'
    ) THEN
        ALTER TABLE `penalty`
            ADD COLUMN `Requested_Life_Deduction` tinyint(3) UNSIGNED DEFAULT NULL
            AFTER `Severity`;
    END IF;

    IF NOT EXISTS (
        SELECT 1 FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'penalty'
          AND COLUMN_NAME = 'Life_Deduction'
    ) THEN
        ALTER TABLE `penalty`
            ADD COLUMN `Life_Deduction` tinyint(3) UNSIGNED DEFAULT NULL
            AFTER `Requested_Life_Deduction`;
    END IF;

    IF NOT EXISTS (
        SELECT 1 FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'penalty'
          AND COLUMN_NAME = 'Created_At'
    ) THEN
        ALTER TABLE `penalty`
            ADD COLUMN `Created_At` datetime DEFAULT NULL
            AFTER `Date`;
    END IF;

    IF NOT EXISTS (
        SELECT 1 FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'penalty'
          AND COLUMN_NAME = 'Issuer_User_ID'
    ) THEN
        ALTER TABLE `penalty`
            ADD COLUMN `Issuer_User_ID` int(10) UNSIGNED DEFAULT NULL
            AFTER `Instructor_ID`;
    END IF;

    IF NOT EXISTS (
        SELECT 1 FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'penalty'
          AND COLUMN_NAME = 'Issuer_Type'
    ) THEN
        ALTER TABLE `penalty`
            ADD COLUMN `Issuer_Type` enum('admin','instructor','system') DEFAULT NULL
            AFTER `Issuer_User_ID`;
    END IF;

    IF NOT EXISTS (
        SELECT 1 FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'penalty'
          AND COLUMN_NAME = 'Source_Attendance_ID'
    ) THEN
        ALTER TABLE `penalty`
            ADD COLUMN `Source_Attendance_ID` int(10) UNSIGNED DEFAULT NULL
            AFTER `Life_Deduction`;
    END IF;

    IF NOT EXISTS (
        SELECT 1 FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'penalty'
          AND COLUMN_NAME = 'Request_Key'
    ) THEN
        ALTER TABLE `penalty`
            ADD COLUMN `Request_Key` char(32)
                CHARACTER SET ascii COLLATE ascii_bin DEFAULT NULL
            AFTER `Source_Attendance_ID`;
    END IF;

    IF NOT EXISTS (
        SELECT 1 FROM information_schema.STATISTICS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'penalty'
          AND INDEX_NAME = 'uq_penalty_source_attendance'
    ) THEN
        ALTER TABLE `penalty`
            ADD UNIQUE KEY `uq_penalty_source_attendance` (`Source_Attendance_ID`);
    END IF;

    IF NOT EXISTS (
        SELECT 1 FROM information_schema.STATISTICS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'penalty'
          AND INDEX_NAME = 'uq_penalty_request_key'
    ) THEN
        ALTER TABLE `penalty`
            ADD UNIQUE KEY `uq_penalty_request_key` (`Request_Key`);
    END IF;

    IF NOT EXISTS (
        SELECT 1 FROM information_schema.STATISTICS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'penalty'
          AND INDEX_NAME = 'idx_penalty_user_created'
    ) THEN
        ALTER TABLE `penalty`
            ADD KEY `idx_penalty_user_created` (`User_ID`, `Created_At`);
    END IF;

    IF NOT EXISTS (
        SELECT 1 FROM information_schema.KEY_COLUMN_USAGE
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'penalty'
          AND CONSTRAINT_NAME = 'fk_penalty_issuer_user'
    ) THEN
        ALTER TABLE `penalty`
            ADD CONSTRAINT `fk_penalty_issuer_user`
            FOREIGN KEY (`Issuer_User_ID`) REFERENCES `user` (`User_ID`)
            ON DELETE RESTRICT ON UPDATE CASCADE;
    END IF;

    IF NOT EXISTS (
        SELECT 1 FROM information_schema.KEY_COLUMN_USAGE
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'penalty'
          AND CONSTRAINT_NAME = 'fk_penalty_source_attendance'
    ) THEN
        ALTER TABLE `penalty`
            ADD CONSTRAINT `fk_penalty_source_attendance`
            FOREIGN KEY (`Source_Attendance_ID`) REFERENCES `attendance` (`Attendance_ID`)
            ON DELETE RESTRICT ON UPDATE CASCADE;
    END IF;

    IF EXISTS (
        SELECT 1 FROM information_schema.TABLE_CONSTRAINTS
        WHERE CONSTRAINT_SCHEMA = DATABASE()
          AND TABLE_NAME = 'penalty'
          AND CONSTRAINT_NAME = 'chk_penalty_life_deduction'
          AND CONSTRAINT_TYPE = 'CHECK'
    ) THEN
        ALTER TABLE `penalty`
            DROP CONSTRAINT `chk_penalty_life_deduction`;
    END IF;

    IF EXISTS (
        SELECT 1 FROM information_schema.TABLE_CONSTRAINTS
        WHERE CONSTRAINT_SCHEMA = DATABASE()
          AND TABLE_NAME = 'penalty'
          AND CONSTRAINT_NAME = 'chk_penalty_requested_life_deduction'
          AND CONSTRAINT_TYPE = 'CHECK'
    ) THEN
        ALTER TABLE `penalty`
            DROP CONSTRAINT `chk_penalty_requested_life_deduction`;
    END IF;

    IF EXISTS (
        SELECT 1 FROM information_schema.TABLE_CONSTRAINTS
        WHERE CONSTRAINT_SCHEMA = DATABASE()
          AND TABLE_NAME = 'penalty'
          AND CONSTRAINT_NAME = 'chk_penalty_actual_not_above_requested'
          AND CONSTRAINT_TYPE = 'CHECK'
    ) THEN
        ALTER TABLE `penalty`
            DROP CONSTRAINT `chk_penalty_actual_not_above_requested`;
    END IF;

    ALTER TABLE `penalty`
        ADD CONSTRAINT `chk_penalty_requested_life_deduction`
        CHECK (`Requested_Life_Deduction` IS NULL OR `Requested_Life_Deduction` BETWEEN 1 AND 100),
        ADD CONSTRAINT `chk_penalty_life_deduction`
        CHECK (`Life_Deduction` IS NULL OR `Life_Deduction` BETWEEN 0 AND 100),
        ADD CONSTRAINT `chk_penalty_actual_not_above_requested`
        CHECK (`Life_Deduction` IS NULL OR `Requested_Life_Deduction` IS NULL OR `Life_Deduction` <= `Requested_Life_Deduction`);
END//

CALL add_member_leaderboard_life_bar()//
DROP PROCEDURE add_member_leaderboard_life_bar//

DELIMITER ;

CREATE TABLE IF NOT EXISTS `attendance_visit_points` (
    `Attendance_ID` int(10) UNSIGNED NOT NULL,
    `Points` tinyint(3) UNSIGNED NOT NULL,
    `Visit_Minutes` int(10) UNSIGNED NOT NULL,
    `Awarded_At` datetime NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY (`Attendance_ID`),
    CONSTRAINT `chk_visit_points_positive` CHECK (`Points` > 0),
    CONSTRAINT `chk_visit_points_minimum_duration` CHECK (`Visit_Minutes` >= 30),
    CONSTRAINT `fk_visit_points_attendance`
        FOREIGN KEY (`Attendance_ID`) REFERENCES `attendance` (`Attendance_ID`)
        ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
