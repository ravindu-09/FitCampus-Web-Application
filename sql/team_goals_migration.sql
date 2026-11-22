CREATE TABLE IF NOT EXISTS `team_goal` (
  `Team_Goal_ID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `Team_ID` int(10) UNSIGNED NOT NULL,
  `Created_By` int(10) UNSIGNED NOT NULL,
  `Goal_Name` varchar(150) NOT NULL,
  `Description` text DEFAULT NULL,
  `Target_Value` decimal(12,2) NOT NULL,
  `Unit` varchar(50) NOT NULL,
  `Start_Date` date NOT NULL,
  `End_Date` date NOT NULL,
  `Status` varchar(30) NOT NULL DEFAULT 'Active',
  `Created_At` timestamp NOT NULL DEFAULT current_timestamp(),
  `Updated_At` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`Team_Goal_ID`),
  CONSTRAINT `chk_team_goal_target_positive` CHECK (`Target_Value` > 0),
  CONSTRAINT `chk_team_goal_dates` CHECK (`End_Date` >= `Start_Date`),
  KEY `idx_team_goal_team_dates` (`Team_ID`,`Start_Date`,`End_Date`),
  KEY `idx_team_goal_created_by` (`Created_By`),
  CONSTRAINT `fk_team_goal_team` FOREIGN KEY (`Team_ID`) REFERENCES `team` (`Team_ID`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_team_goal_creator` FOREIGN KEY (`Created_By`) REFERENCES `user` (`User_ID`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `team_goal_progress` (
  `Progress_ID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `Team_Goal_ID` int(10) UNSIGNED NOT NULL,
  `User_ID` int(10) UNSIGNED NOT NULL,
  `Contribution_Value` decimal(12,2) NOT NULL,
  `Progress_Date` date NOT NULL,
  `Created_At` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`Progress_ID`),
  KEY `idx_team_goal_progress_goal_date` (`Team_Goal_ID`,`Progress_Date`),
  KEY `idx_team_goal_progress_user` (`User_ID`),
  CONSTRAINT `chk_team_goal_progress_positive` CHECK (`Contribution_Value` > 0),
  CONSTRAINT `fk_team_goal_progress_goal` FOREIGN KEY (`Team_Goal_ID`) REFERENCES `team_goal` (`Team_Goal_ID`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_team_goal_progress_user` FOREIGN KEY (`User_ID`) REFERENCES `user` (`User_ID`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
