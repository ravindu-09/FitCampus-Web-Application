-- Compatible with MariaDB 10.4.28: REGEXP_REPLACE, STORED generated columns,
-- utf8mb4_bin collation, and indexes on stored generated columns are supported.
-- Adds instructor instructions, canonical exercise uniqueness, member bookmarks,
-- and a nullable source reference for independently copied recommendations.
-- Existing exercise IDs and workout-to-exercise relationships are preserved.

-- Diagnostic: any returned group must be resolved manually before proceeding.
SELECT
    UPPER(TRIM(REGEXP_REPLACE(`Exercise_Name`, '[[:space:]]+', ' '))) COLLATE utf8mb4_bin AS `Normalized_Name`,
    GROUP_CONCAT(`Exercise_ID` ORDER BY `Exercise_ID`) AS `Exercise_IDs`,
    COUNT(*) AS `Duplicate_Count`
FROM `exercise`
GROUP BY UPPER(TRIM(REGEXP_REPLACE(`Exercise_Name`, '[[:space:]]+', ' '))) COLLATE utf8mb4_bin
HAVING COUNT(*) > 1;

DELIMITER //

DROP PROCEDURE IF EXISTS add_personal_workout_catalog_support//
CREATE PROCEDURE add_personal_workout_catalog_support()
BEGIN
    IF EXISTS (
        SELECT 1
        FROM `exercise`
        GROUP BY UPPER(TRIM(REGEXP_REPLACE(`Exercise_Name`, '[[:space:]]+', ' '))) COLLATE utf8mb4_bin
        HAVING COUNT(*) > 1
    ) THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Duplicate normalized exercise names exist. Review the preceding diagnostic query and manually resolve IDs and workout links before rerunning.';
    END IF;

    UPDATE `exercise`
    SET `Exercise_Name` = UPPER(TRIM(REGEXP_REPLACE(`Exercise_Name`, '[[:space:]]+', ' ')));

    IF EXISTS (
        SELECT 1
        FROM information_schema.STATISTICS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'exercise'
          AND INDEX_NAME = 'uq_exercise_name'
    ) THEN
        ALTER TABLE `exercise` DROP INDEX `uq_exercise_name`;
    END IF;

    IF NOT EXISTS (
        SELECT 1
        FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'exercise'
          AND COLUMN_NAME = 'Exercise_Name_Normalized'
    ) THEN
        ALTER TABLE `exercise`
            ADD COLUMN `Exercise_Name_Normalized` varchar(100)
                CHARACTER SET utf8mb4 COLLATE utf8mb4_bin
                GENERATED ALWAYS AS (
                    UPPER(TRIM(REGEXP_REPLACE(`Exercise_Name`, '[[:space:]]+', ' ')))
                ) STORED
                AFTER `Exercise_Name`;
    END IF;

    IF NOT EXISTS (
        SELECT 1
        FROM information_schema.STATISTICS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'exercise'
          AND INDEX_NAME = 'uq_exercise_name_normalized'
    ) THEN
        ALTER TABLE `exercise`
            ADD UNIQUE KEY `uq_exercise_name_normalized` (`Exercise_Name_Normalized`);
    END IF;

    IF NOT EXISTS (
        SELECT 1
        FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'common_workout'
          AND COLUMN_NAME = 'Description'
    ) THEN
        ALTER TABLE `common_workout`
            ADD COLUMN `Description` text DEFAULT NULL AFTER `Title`;
    END IF;

    IF NOT EXISTS (
        SELECT 1
        FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'personal_workout'
          AND COLUMN_NAME = 'Source_Common_Workout_ID'
    ) THEN
        ALTER TABLE `personal_workout`
            ADD COLUMN `Source_Common_Workout_ID` int(10) UNSIGNED DEFAULT NULL
            AFTER `User_ID`;
    END IF;

    IF NOT EXISTS (
        SELECT 1
        FROM information_schema.STATISTICS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'personal_workout'
          AND INDEX_NAME = 'uq_personal_workout_user_source_common'
    ) THEN
        ALTER TABLE `personal_workout`
            ADD UNIQUE KEY `uq_personal_workout_user_source_common`
                (`User_ID`, `Source_Common_Workout_ID`);
    END IF;

    IF NOT EXISTS (
        SELECT 1
        FROM information_schema.KEY_COLUMN_USAGE
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'personal_workout'
          AND CONSTRAINT_NAME = 'fk_personal_workout_source_common'
    ) THEN
        ALTER TABLE `personal_workout`
            ADD CONSTRAINT `fk_personal_workout_source_common`
            FOREIGN KEY (`Source_Common_Workout_ID`)
            REFERENCES `common_workout` (`Common_Workout_ID`)
            ON DELETE SET NULL ON UPDATE CASCADE;
    END IF;
END//

CALL add_personal_workout_catalog_support()//
DROP PROCEDURE add_personal_workout_catalog_support//

DELIMITER ;

CREATE TABLE IF NOT EXISTS `member_saved_common_workout` (
    `User_ID` int(10) UNSIGNED NOT NULL,
    `Common_Workout_ID` int(10) UNSIGNED NOT NULL,
    `Saved_At` timestamp NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY (`User_ID`, `Common_Workout_ID`),
    KEY `idx_saved_common_workout_workout` (`Common_Workout_ID`),
    CONSTRAINT `fk_saved_common_workout_user`
        FOREIGN KEY (`User_ID`) REFERENCES `user` (`User_ID`)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_saved_common_workout_workout`
        FOREIGN KEY (`Common_Workout_ID`) REFERENCES `common_workout` (`Common_Workout_ID`)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
