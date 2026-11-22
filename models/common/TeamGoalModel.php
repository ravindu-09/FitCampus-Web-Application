<?php

class TeamGoalModel
{
    private $pdo;

    public function __construct($dbConnection)
    {
        $this->pdo = $dbConnection;
    }

    public function getCaptainTeams($userId)
    {
        $stmt = $this->pdo->prepare(
            "SELECT t.Team_ID, t.Team_Name, t.Sport
             FROM team t
             INNER JOIN team_member tm ON tm.Team_ID = t.Team_ID
             WHERE tm.User_ID = ? AND tm.Role_In_Team = 'Captain'
             ORDER BY t.Team_Name"
        );
        $stmt->execute([$userId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getGoalsForCaptain($teamId, $userId)
    {
        $stmt = $this->pdo->prepare(
            "SELECT tg.Team_Goal_ID, tg.Team_ID, tg.Created_By, tg.Goal_Name,
                    tg.Description, tg.Target_Value, tg.Unit, tg.Start_Date,
                    tg.End_Date, tg.Status, tg.Created_At, tg.Updated_At,
                    COALESCE(SUM(tgp.Contribution_Value), 0) AS Total_Progress,
                    LEAST(100, GREATEST(0, ROUND(
                        COALESCE(SUM(tgp.Contribution_Value), 0) / tg.Target_Value * 100
                    ))) AS Progress_Percentage,
                    COUNT(tgp.Progress_ID) AS Contribution_Count,
                    CASE
                        WHEN tg.Status = 'Active' AND tg.End_Date < CURRENT_DATE THEN 'Expired'
                        ELSE tg.Status
                    END AS Effective_Status
             FROM team_goal tg
             LEFT JOIN team_goal_progress tgp
                ON tgp.Team_Goal_ID = tg.Team_Goal_ID
             WHERE tg.Team_ID = ?
               AND EXISTS (
                    SELECT 1
                    FROM team_member captain
                    WHERE captain.Team_ID = tg.Team_ID
                      AND captain.User_ID = ?
                      AND captain.Role_In_Team = 'Captain'
               )
             GROUP BY tg.Team_Goal_ID, tg.Team_ID, tg.Created_By, tg.Goal_Name,
                      tg.Description, tg.Target_Value, tg.Unit, tg.Start_Date,
                      tg.End_Date, tg.Status, tg.Created_At, tg.Updated_At
             ORDER BY tg.Start_Date DESC, tg.Team_Goal_ID DESC"
        );
        $stmt->execute([$teamId, $userId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getGoalsForMember($teamId, $userId)
    {
        $stmt = $this->pdo->prepare(
            "SELECT tg.Team_Goal_ID, tg.Team_ID, tg.Goal_Name, tg.Description,
                    tg.Target_Value, tg.Unit, tg.Start_Date, tg.End_Date,
                    tg.Status, COALESCE(SUM(tgp.Contribution_Value), 0) AS Total_Progress,
                    LEAST(100, GREATEST(0, ROUND(
                        COALESCE(SUM(tgp.Contribution_Value), 0) / tg.Target_Value * 100
                    ))) AS Progress_Percentage,
                    COALESCE(SUM(CASE WHEN tgp.User_ID = ? THEN tgp.Contribution_Value ELSE 0 END), 0) AS My_Contribution,
                    COUNT(tgp.Progress_ID) AS Contribution_Count,
                    CASE
                        WHEN tg.Status = 'Active' AND tg.End_Date < CURRENT_DATE THEN 'Expired'
                        ELSE tg.Status
                    END AS Effective_Status
             FROM team_goal tg
             LEFT JOIN team_goal_progress tgp
                ON tgp.Team_Goal_ID = tg.Team_Goal_ID
             WHERE tg.Team_ID = ?
               AND EXISTS (
                    SELECT 1
                    FROM team_member member
                    WHERE member.Team_ID = tg.Team_ID
                      AND member.User_ID = ?
               )
             GROUP BY tg.Team_Goal_ID, tg.Team_ID, tg.Goal_Name, tg.Description,
                      tg.Target_Value, tg.Unit, tg.Start_Date, tg.End_Date, tg.Status
             ORDER BY tg.Start_Date DESC, tg.Team_Goal_ID DESC"
        );
        $stmt->execute([$userId, $teamId, $userId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getContributionsForCaptain($goalId, $teamId, $userId)
    {
        $stmt = $this->pdo->prepare(
            "SELECT tgp.Progress_ID, tgp.User_ID, tgp.Contribution_Value,
                    tgp.Progress_Date, tgp.Created_At,
                    CONCAT(u.First_Name, ' ', u.Last_Name) AS Contributor_Name
             FROM team_goal_progress tgp
             INNER JOIN team_goal tg ON tg.Team_Goal_ID = tgp.Team_Goal_ID
             INNER JOIN `user` u ON u.User_ID = tgp.User_ID
             WHERE tg.Team_Goal_ID = ? AND tg.Team_ID = ?
               AND EXISTS (
                    SELECT 1
                    FROM team_member captain
                    WHERE captain.Team_ID = tg.Team_ID
                      AND captain.User_ID = ?
                      AND captain.Role_In_Team = 'Captain'
               )
             ORDER BY tgp.Progress_Date DESC, tgp.Progress_ID DESC"
        );
        $stmt->execute([$goalId, $teamId, $userId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getContributionsForMember($goalId, $teamId, $userId)
    {
        $stmt = $this->pdo->prepare(
            "SELECT tgp.Progress_ID, tgp.User_ID, tgp.Contribution_Value,
                    tgp.Progress_Date,
                    CONCAT(u.First_Name, ' ', u.Last_Name) AS Contributor_Name
             FROM team_goal_progress tgp
             INNER JOIN team_goal tg ON tg.Team_Goal_ID = tgp.Team_Goal_ID
             INNER JOIN `user` u ON u.User_ID = tgp.User_ID
             WHERE tg.Team_Goal_ID = ? AND tg.Team_ID = ?
               AND EXISTS (
                    SELECT 1
                    FROM team_member member
                    WHERE member.Team_ID = tg.Team_ID
                      AND member.User_ID = ?
               )
             ORDER BY tgp.Progress_Date DESC, tgp.Progress_ID DESC"
        );
        $stmt->execute([$goalId, $teamId, $userId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getContributorRankingsForCaptain($teamId, $userId)
    {
        return $this->getContributorRankings($teamId, $userId, true);
    }

    public function getContributorRankingsForMember($teamId, $userId)
    {
        return $this->getContributorRankings($teamId, $userId, false);
    }

    private function getContributorRankings($teamId, $userId, $requireCaptain)
    {
        $captainCheck = $requireCaptain ? " AND viewer.Role_In_Team = 'Captain'" : '';
        $stmt = $this->pdo->prepare(
            "SELECT tg.Team_Goal_ID, tgp.User_ID,
                    CONCAT(u.First_Name, ' ', u.Last_Name) AS Contributor_Name,
                    SUM(tgp.Contribution_Value) AS Total_Contribution
             FROM team_goal_progress tgp
             INNER JOIN team_goal tg ON tg.Team_Goal_ID = tgp.Team_Goal_ID
             INNER JOIN `user` u ON u.User_ID = tgp.User_ID
             WHERE tg.Team_ID = ?
               AND EXISTS (
                    SELECT 1
                    FROM team_member contributor
                    WHERE contributor.Team_ID = tg.Team_ID
                      AND contributor.User_ID = tgp.User_ID
               )
               AND EXISTS (
                    SELECT 1
                    FROM team_member viewer
                    WHERE viewer.Team_ID = tg.Team_ID
                      AND viewer.User_ID = ?{$captainCheck}
               )
             GROUP BY tg.Team_Goal_ID, tgp.User_ID, u.First_Name, u.Last_Name
             ORDER BY tg.Team_Goal_ID, Total_Contribution DESC,
                      Contributor_Name ASC, tgp.User_ID ASC"
        );
        $stmt->execute([$teamId, $userId]);

        $rankingsByGoal = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $ranking) {
            $goalId = (int)$ranking['Team_Goal_ID'];
            $ranking['User_ID'] = (int)$ranking['User_ID'];
            $rankingsByGoal[$goalId][] = $ranking;
        }

        return $rankingsByGoal;
    }

    public function createGoal($teamId, $userId, array $goal)
    {
        $this->pdo->beginTransaction();
        try {
            $this->assertCaptain($teamId, $userId);
            $stmt = $this->pdo->prepare(
                "INSERT INTO team_goal
                    (Team_ID, Created_By, Goal_Name, Description, Target_Value,
                     Unit, Start_Date, End_Date, Status)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'Active')"
            );
            $stmt->execute([
                $teamId,
                $userId,
                $goal['name'],
                $goal['description'],
                $goal['target'],
                $goal['unit'],
                $goal['start_date'],
                $goal['end_date']
            ]);
            $goalId = (int)$this->pdo->lastInsertId();
            $this->pdo->commit();

            return $goalId;
        } catch (Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $e;
        }
    }

    public function updateGoal($goalId, $teamId, $userId, array $goal)
    {
        $this->pdo->beginTransaction();
        try {
            $this->assertCaptain($teamId, $userId);
            $this->assertEditableGoal($goalId, $teamId);
            $history = $this->pdo->prepare(
                "SELECT COUNT(*) AS Contribution_Count,
                        SUM(CASE WHEN Progress_Date < ? OR Progress_Date > ? THEN 1 ELSE 0 END) AS Outside_New_Period
                 FROM team_goal_progress
                 WHERE Team_Goal_ID = ?"
            );
            $history->execute([$goal['start_date'], $goal['end_date'], $goalId]);
            $historySummary = $history->fetch(PDO::FETCH_ASSOC);
            if ((int)$historySummary['Contribution_Count'] > 0
                && ($historySummary['Outside_New_Period'] > 0
                    || $this->goalUnitChanged($goalId, $goal['unit']))) {
                throw new DomainException('Dates or unit cannot exclude or reinterpret existing contributions.');
            }

            $stmt = $this->pdo->prepare(
                "UPDATE team_goal
                 SET Goal_Name = ?, Description = ?, Target_Value = ?,
                     Unit = ?, Start_Date = ?, End_Date = ?, Updated_At = CURRENT_TIMESTAMP
                 WHERE Team_Goal_ID = ? AND Team_ID = ? AND Status = 'Active'"
            );
            $stmt->execute([
                $goal['name'],
                $goal['description'],
                $goal['target'],
                $goal['unit'],
                $goal['start_date'],
                $goal['end_date'],
                $goalId,
                $teamId
            ]);
            $this->pdo->commit();

            return true;
        } catch (Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $e;
        }
    }

    public function changeGoalStatus($goalId, $teamId, $userId, $status)
    {
        if (!in_array($status, ['Completed', 'Cancelled'], true)) {
            throw new InvalidArgumentException('Choose a valid goal status.');
        }

        $this->pdo->beginTransaction();
        try {
            $this->assertCaptain($teamId, $userId);
            $this->assertEditableGoal($goalId, $teamId);
            $stmt = $this->pdo->prepare(
                "UPDATE team_goal
                 SET Status = ?, Updated_At = CURRENT_TIMESTAMP
                 WHERE Team_Goal_ID = ? AND Team_ID = ? AND Status = 'Active'"
            );
            $stmt->execute([$status, $goalId, $teamId]);
            if ($stmt->rowCount() !== 1) {
                throw new DomainException('That active team goal could not be updated.');
            }
            $this->pdo->commit();

            return true;
        } catch (Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $e;
        }
    }

    public function addContribution($goalId, $teamId, $userId, $value, $date)
    {
        if (!is_numeric($value) || !is_finite((float)$value) || (float)$value <= 0
            || (float)$value > 9999999999.99 || $date > date('Y-m-d')) {
            throw new InvalidArgumentException('Enter a positive contribution dated no later than today.');
        }

        $this->pdo->beginTransaction();
        try {
            $stmt = $this->pdo->prepare(
                "SELECT tg.Team_Goal_ID, tg.Status, tg.Start_Date, tg.End_Date
                 FROM team_goal tg
                 INNER JOIN team_member tm
                    ON tm.Team_ID = tg.Team_ID AND tm.User_ID = ?
                 WHERE tg.Team_Goal_ID = ? AND tg.Team_ID = ?
                 FOR UPDATE"
            );
            $stmt->execute([$userId, $goalId, $teamId]);
            $goal = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$goal) {
                throw new DomainException('That goal is not available to your team.');
            }
            if ($goal['Status'] !== 'Active' || $goal['End_Date'] < date('Y-m-d')) {
                throw new DomainException('Contributions are only accepted for active goals.');
            }
            if ($date < $goal['Start_Date'] || $date > $goal['End_Date']) {
                throw new InvalidArgumentException('Contribution date must be within the goal period.');
            }

            $insert = $this->pdo->prepare(
                "INSERT INTO team_goal_progress
                    (Team_Goal_ID, User_ID, Contribution_Value, Progress_Date)
                 VALUES (?, ?, ?, ?)"
            );
            $insert->execute([$goalId, $userId, $value, $date]);
            $this->pdo->commit();

            return true;
        } catch (Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $e;
        }
    }

    private function assertCaptain($teamId, $userId)
    {
        $stmt = $this->pdo->prepare(
            "SELECT Team_ID
             FROM team_member
             WHERE Team_ID = ? AND User_ID = ? AND Role_In_Team = 'Captain'
             FOR UPDATE"
        );
        $stmt->execute([$teamId, $userId]);
        if ($stmt->fetchColumn() === false) {
            throw new DomainException('You are not a captain of the selected team.');
        }
    }

    private function assertEditableGoal($goalId, $teamId)
    {
        $stmt = $this->pdo->prepare(
            "SELECT Team_Goal_ID
             FROM team_goal
             WHERE Team_Goal_ID = ? AND Team_ID = ? AND Status = 'Active'
               AND End_Date >= CURRENT_DATE
             FOR UPDATE"
        );
        $stmt->execute([$goalId, $teamId]);
        if ($stmt->fetchColumn() === false) {
            throw new DomainException('Active team goal not found.');
        }
    }

    private function goalUnitChanged($goalId, $unit)
    {
        $stmt = $this->pdo->prepare(
            "SELECT Unit FROM team_goal WHERE Team_Goal_ID = ?"
        );
        $stmt->execute([$goalId]);

        return $stmt->fetchColumn() !== $unit;
    }
}
