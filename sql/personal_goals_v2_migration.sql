-- Personal Goals v2 adds normalized metric definitions and dated performance
-- entries. Existing goal and goal_progress rows remain intact.

SET @goal_activity_sql = IF(
    EXISTS(
        SELECT 1
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'goal'
          AND COLUMN_NAME = 'Activity_Name'
    ),
    'SELECT 1',
    'ALTER TABLE `goal` ADD COLUMN `Activity_Name` varchar(150) DEFAULT NULL AFTER `Goal_Name`'
);
PREPARE goal_activity_stmt FROM @goal_activity_sql;
EXECUTE goal_activity_stmt;
DEALLOCATE PREPARE goal_activity_stmt;

CREATE TABLE IF NOT EXISTS `goal_metric` (
    `Goal_Metric_ID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `Goal_ID` int(10) UNSIGNED NOT NULL,
    `Metric_Key` varchar(40) NOT NULL,
    `Metric_Label` varchar(100) NOT NULL,
    `Unit` varchar(30) NOT NULL,
    `Target_Value` decimal(10,2) DEFAULT NULL,
    `Comparison_Direction` enum('at_least','at_most') NOT NULL,
    `Baseline_Value` decimal(10,2) DEFAULT NULL,
    `Display_Order` tinyint(3) UNSIGNED NOT NULL DEFAULT 1,
    PRIMARY KEY (`Goal_Metric_ID`),
    UNIQUE KEY `uq_goal_metric_id_goal` (`Goal_Metric_ID`,`Goal_ID`),
    UNIQUE KEY `uq_goal_metric_key` (`Goal_ID`,`Metric_Key`),
    KEY `idx_goal_metric_goal_order` (`Goal_ID`,`Display_Order`),
    CONSTRAINT `fk_goal_metric_goal`
        FOREIGN KEY (`Goal_ID`) REFERENCES `goal` (`Goal_ID`)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `goal_performance` (
    `Performance_ID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `Goal_ID` int(10) UNSIGNED NOT NULL,
    `Performance_Date` date NOT NULL,
    `Legacy_Progress_ID` int(10) UNSIGNED DEFAULT NULL,
    `Created_At` timestamp NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY (`Performance_ID`),
    UNIQUE KEY `uq_goal_performance_id_goal` (`Performance_ID`,`Goal_ID`),
    UNIQUE KEY `uq_goal_performance_legacy_progress` (`Legacy_Progress_ID`),
    KEY `idx_goal_performance_goal_date` (`Goal_ID`,`Performance_Date`,`Performance_ID`),
    CONSTRAINT `fk_goal_performance_goal`
        FOREIGN KEY (`Goal_ID`) REFERENCES `goal` (`Goal_ID`)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `goal_performance_value` (
    `Performance_Value_ID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `Performance_ID` int(10) UNSIGNED NOT NULL,
    `Goal_ID` int(10) UNSIGNED DEFAULT NULL,
    `Goal_Metric_ID` int(10) UNSIGNED NOT NULL,
    `Measured_Value` decimal(10,2) NOT NULL,
    PRIMARY KEY (`Performance_Value_ID`),
    UNIQUE KEY `uq_goal_performance_metric` (`Performance_ID`,`Goal_Metric_ID`),
    KEY `idx_goal_performance_value_performance_goal` (`Performance_ID`,`Goal_ID`),
    KEY `idx_goal_performance_value_metric_goal` (`Goal_Metric_ID`,`Goal_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Upgrade tables created by the earlier v2 migration before enforcing the
-- goal-scoped composite foreign keys. These checks make the upgrade rerunnable.
SET @goal_performance_index_sql = IF(
    EXISTS(
        SELECT 1 FROM INFORMATION_SCHEMA.STATISTICS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'goal_performance'
          AND INDEX_NAME = 'uq_goal_performance_id_goal'
    ),
    'SELECT 1',
    'ALTER TABLE `goal_performance` ADD UNIQUE KEY `uq_goal_performance_id_goal` (`Performance_ID`,`Goal_ID`)'
);
PREPARE goal_performance_index_stmt FROM @goal_performance_index_sql;
EXECUTE goal_performance_index_stmt;
DEALLOCATE PREPARE goal_performance_index_stmt;

SET @goal_metric_index_sql = IF(
    EXISTS(
        SELECT 1 FROM INFORMATION_SCHEMA.STATISTICS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'goal_metric'
          AND INDEX_NAME = 'uq_goal_metric_id_goal'
    ),
    'SELECT 1',
    'ALTER TABLE `goal_metric` ADD UNIQUE KEY `uq_goal_metric_id_goal` (`Goal_Metric_ID`,`Goal_ID`)'
);
PREPARE goal_metric_index_stmt FROM @goal_metric_index_sql;
EXECUTE goal_metric_index_stmt;
DEALLOCATE PREPARE goal_metric_index_stmt;

SET @performance_value_goal_column_sql = IF(
    EXISTS(
        SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'goal_performance_value'
          AND COLUMN_NAME = 'Goal_ID'
    ),
    'SELECT 1',
    'ALTER TABLE `goal_performance_value` ADD COLUMN `Goal_ID` int(10) UNSIGNED NULL AFTER `Performance_ID`'
);
PREPARE performance_value_goal_column_stmt FROM @performance_value_goal_column_sql;
EXECUTE performance_value_goal_column_stmt;
DEALLOCATE PREPARE performance_value_goal_column_stmt;

UPDATE `goal_performance_value` pv
INNER JOIN `goal_performance` p
    ON p.`Performance_ID` = pv.`Performance_ID`
INNER JOIN `goal_metric` m
    ON m.`Goal_Metric_ID` = pv.`Goal_Metric_ID`
SET pv.`Goal_ID` = p.`Goal_ID`
WHERE pv.`Goal_ID` IS NULL
  AND p.`Goal_ID` = m.`Goal_ID`;

-- Refuse to continue if any value cannot be tied to the same goal as both
-- parents. This intentionally reports a failure instead of repairing bad rows.
DROP PROCEDURE IF EXISTS `fitcampus_validate_goal_performance_values`;
DELIMITER $$
CREATE PROCEDURE `fitcampus_validate_goal_performance_values`()
BEGIN
    DECLARE invalid_row_count BIGINT DEFAULT 0;

    SELECT COUNT(*)
    INTO invalid_row_count
    FROM `goal_performance_value` pv
    LEFT JOIN `goal_performance` p
        ON p.`Performance_ID` = pv.`Performance_ID`
    LEFT JOIN `goal_metric` m
        ON m.`Goal_Metric_ID` = pv.`Goal_Metric_ID`
    WHERE p.`Performance_ID` IS NULL
       OR m.`Goal_Metric_ID` IS NULL
       OR p.`Goal_ID` <> m.`Goal_ID`
       OR pv.`Goal_ID` IS NULL
       OR pv.`Goal_ID` <> p.`Goal_ID`
       OR pv.`Goal_ID` <> m.`Goal_ID`;

    IF invalid_row_count > 0 THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Personal Goals v2 stopped: missing or cross-goal values. Inspect goal_performance_value parent links before rerunning.';
    END IF;
END$$
DELIMITER ;

CALL `fitcampus_validate_goal_performance_values`();
DROP PROCEDURE `fitcampus_validate_goal_performance_values`;

SET @performance_value_goal_not_null_sql = IF(
    EXISTS(
        SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'goal_performance_value'
          AND COLUMN_NAME = 'Goal_ID'
          AND IS_NULLABLE = 'YES'
    ),
    'ALTER TABLE `goal_performance_value` MODIFY COLUMN `Goal_ID` int(10) UNSIGNED NOT NULL',
    'SELECT 1'
);
PREPARE performance_value_goal_not_null_stmt FROM @performance_value_goal_not_null_sql;
EXECUTE performance_value_goal_not_null_stmt;
DEALLOCATE PREPARE performance_value_goal_not_null_stmt;

SET @performance_value_performance_index_sql = IF(
    EXISTS(
        SELECT 1 FROM INFORMATION_SCHEMA.STATISTICS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'goal_performance_value'
          AND INDEX_NAME = 'idx_goal_performance_value_performance_goal'
    ),
    'SELECT 1',
    'ALTER TABLE `goal_performance_value` ADD KEY `idx_goal_performance_value_performance_goal` (`Performance_ID`,`Goal_ID`)'
);
PREPARE performance_value_performance_index_stmt FROM @performance_value_performance_index_sql;
EXECUTE performance_value_performance_index_stmt;
DEALLOCATE PREPARE performance_value_performance_index_stmt;

SET @performance_value_metric_index_sql = IF(
    EXISTS(
        SELECT 1 FROM INFORMATION_SCHEMA.STATISTICS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'goal_performance_value'
          AND INDEX_NAME = 'idx_goal_performance_value_metric_goal'
    ),
    'SELECT 1',
    'ALTER TABLE `goal_performance_value` ADD KEY `idx_goal_performance_value_metric_goal` (`Goal_Metric_ID`,`Goal_ID`)'
);
PREPARE performance_value_metric_index_stmt FROM @performance_value_metric_index_sql;
EXECUTE performance_value_metric_index_stmt;
DEALLOCATE PREPARE performance_value_metric_index_stmt;

SET @old_performance_fk_sql = IF(
    EXISTS(
        SELECT 1 FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS
        WHERE CONSTRAINT_SCHEMA = DATABASE()
          AND TABLE_NAME = 'goal_performance_value'
          AND CONSTRAINT_NAME = 'fk_goal_performance_value_performance'
          AND CONSTRAINT_TYPE = 'FOREIGN KEY'
    ),
    'ALTER TABLE `goal_performance_value` DROP FOREIGN KEY `fk_goal_performance_value_performance`',
    'SELECT 1'
);
PREPARE old_performance_fk_stmt FROM @old_performance_fk_sql;
EXECUTE old_performance_fk_stmt;
DEALLOCATE PREPARE old_performance_fk_stmt;

SET @old_metric_fk_sql = IF(
    EXISTS(
        SELECT 1 FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS
        WHERE CONSTRAINT_SCHEMA = DATABASE()
          AND TABLE_NAME = 'goal_performance_value'
          AND CONSTRAINT_NAME = 'fk_goal_performance_value_metric'
          AND CONSTRAINT_TYPE = 'FOREIGN KEY'
    ),
    'ALTER TABLE `goal_performance_value` DROP FOREIGN KEY `fk_goal_performance_value_metric`',
    'SELECT 1'
);
PREPARE old_metric_fk_stmt FROM @old_metric_fk_sql;
EXECUTE old_metric_fk_stmt;
DEALLOCATE PREPARE old_metric_fk_stmt;

SET @performance_goal_fk_sql = IF(
    EXISTS(
        SELECT 1 FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS
        WHERE CONSTRAINT_SCHEMA = DATABASE()
          AND TABLE_NAME = 'goal_performance_value'
          AND CONSTRAINT_NAME = 'fk_goal_performance_value_performance_goal'
          AND CONSTRAINT_TYPE = 'FOREIGN KEY'
    ),
    'SELECT 1',
    'ALTER TABLE `goal_performance_value` ADD CONSTRAINT `fk_goal_performance_value_performance_goal` FOREIGN KEY (`Performance_ID`,`Goal_ID`) REFERENCES `goal_performance` (`Performance_ID`,`Goal_ID`) ON DELETE CASCADE ON UPDATE CASCADE'
);
PREPARE performance_goal_fk_stmt FROM @performance_goal_fk_sql;
EXECUTE performance_goal_fk_stmt;
DEALLOCATE PREPARE performance_goal_fk_stmt;

SET @metric_goal_fk_sql = IF(
    EXISTS(
        SELECT 1 FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS
        WHERE CONSTRAINT_SCHEMA = DATABASE()
          AND TABLE_NAME = 'goal_performance_value'
          AND CONSTRAINT_NAME = 'fk_goal_performance_value_metric_goal'
          AND CONSTRAINT_TYPE = 'FOREIGN KEY'
    ),
    'SELECT 1',
    'ALTER TABLE `goal_performance_value` ADD CONSTRAINT `fk_goal_performance_value_metric_goal` FOREIGN KEY (`Goal_Metric_ID`,`Goal_ID`) REFERENCES `goal_metric` (`Goal_Metric_ID`,`Goal_ID`) ON DELETE CASCADE ON UPDATE CASCADE'
);
PREPARE metric_goal_fk_stmt FROM @metric_goal_fk_sql;
EXECUTE metric_goal_fk_stmt;
DEALLOCATE PREPARE metric_goal_fk_stmt;

-- Use the goal's primary metric to preserve legacy single-value goal meaning.
INSERT IGNORE INTO `goal_metric`
    (`Goal_ID`, `Metric_Key`, `Metric_Label`, `Unit`, `Target_Value`,
     `Comparison_Direction`, `Baseline_Value`, `Display_Order`)
SELECT
    g.`Goal_ID`,
    IF(g.`Goal_Type` = 'Weight Loss', 'weight', 'primary'),
    IF(g.`Goal_Type` = 'Weight Loss', 'Body weight',
       COALESCE(NULLIF(g.`Activity_Name`, ''), NULLIF(g.`Goal_Name`, ''), g.`Goal_Type`)),
    COALESCE(NULLIF(g.`Unit`, ''), 'unit'),
    g.`Target`,
    IF(g.`Goal_Type` = 'Weight Loss', 'at_most', 'at_least'),
    IF(g.`Goal_Type` = 'Weight Loss', NULLIF(g.`Start_Value`, 0), NULL),
    1
FROM `goal` g;

-- Preserve every legacy history row, using its original ID as an idempotency key.
INSERT IGNORE INTO `goal_performance`
    (`Goal_ID`, `Performance_Date`, `Legacy_Progress_ID`)
SELECT gp.`Goal_ID`, gp.`Update_Date`, gp.`Progress_ID`
FROM `goal_progress` gp;

INSERT IGNORE INTO `goal_performance_value`
    (`Performance_ID`, `Goal_ID`, `Goal_Metric_ID`, `Measured_Value`)
SELECT
    p.`Performance_ID`,
    p.`Goal_ID`,
    m.`Goal_Metric_ID`,
    gp.`Value`
FROM `goal_progress` gp
INNER JOIN `goal_performance` p
    ON p.`Legacy_Progress_ID` = gp.`Progress_ID`
INNER JOIN `goal_metric` m
    ON m.`Goal_ID` = gp.`Goal_ID`
   AND m.`Metric_Key` = IF(
       (SELECT g.`Goal_Type` FROM `goal` g WHERE g.`Goal_ID` = gp.`Goal_ID`) = 'Weight Loss',
       'weight',
       'primary'
   )
WHERE gp.`Value` IS NOT NULL;

-- Activity labels for existing strength/endurance goals retain their old names.
UPDATE `goal`
SET `Activity_Name` = `Goal_Name`
WHERE `Activity_Name` IS NULL
  AND `Goal_Type` IN ('Strength', 'Endurance');
