ALTER TABLE `goal`
    ADD COLUMN `Goal_Name` varchar(150) NOT NULL,
    ADD COLUMN `Start_Value` decimal(10,2) NOT NULL DEFAULT 0.00,
    ADD COLUMN `Current_Value` decimal(10,2) NOT NULL DEFAULT 0.00,
    ADD COLUMN `Unit` varchar(50) NOT NULL;

CREATE TABLE IF NOT EXISTS `goal_progress` (
    `Progress_ID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `Goal_ID` int(10) UNSIGNED NOT NULL,
    `Value` decimal(10,2) NOT NULL,
    `Update_Date` date NOT NULL,
    PRIMARY KEY (`Progress_ID`),
    KEY `idx_goal_progress_goal_date` (`Goal_ID`, `Update_Date`),
    CONSTRAINT `fk_goal_progress_goal`
        FOREIGN KEY (`Goal_ID`)
        REFERENCES `goal` (`Goal_ID`)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
