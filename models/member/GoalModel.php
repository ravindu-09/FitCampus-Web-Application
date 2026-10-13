<?php
// models/member/GoalModel.php

class GoalModel
{
    private $pdo;

    public function __construct($dbConnection)
    {
        $this->pdo = $dbConnection;
    }

    // Get all goals belonging to the logged-in user
    public function getGoalsByUser($user_id)
    {
        $sql = "SELECT
                    Goal_ID,
                    User_ID,
                    Goal_Name,
                    Goal_Type,
                    Target,
                    Start_Value,
                    Current_Value,
                    Unit,
                    Start_Date,
                    End_Date,
                    Status
                FROM goal
                WHERE User_ID = ?
                ORDER BY Goal_ID DESC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$user_id]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get one goal belonging to the logged-in user
    public function getGoalByIdAndUser($goal_id, $user_id)
    {
        $sql = "SELECT
                    Goal_ID,
                    User_ID,
                    Goal_Name,
                    Goal_Type,
                    Target,
                    Start_Value,
                    Current_Value,
                    Unit,
                    Start_Date,
                    End_Date,
                    Status
                FROM goal
                WHERE Goal_ID = ?
                  AND User_ID = ?
                LIMIT 1";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            $goal_id,
            $user_id
        ]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Create a new goal
    public function createGoal(
        $user_id,
        $goal_name,
        $goal_type,
        $target,
        $start_value,
        $current_value,
        $unit,
        $start_date,
        $end_date,
        $status
    ) {
        $sql = "INSERT INTO goal
                    (
                        User_ID,
                        Goal_Name,
                        Goal_Type,
                        Target,
                        Start_Value,
                        Current_Value,
                        Unit,
                        Start_Date,
                        End_Date,
                        Status
                    )
                VALUES
                    (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute([
            $user_id,
            $goal_name,
            $goal_type,
            $target,
            $start_value,
            $current_value,
            $unit,
            $start_date,
            $end_date,
            $status
        ]);
    }

    // Save progress, update latest value,
    // and automatically update goal status
    public function updateGoalProgress(
        $goal_id,
        $user_id,
        $value,
        $update_date
    ) {
        try {
            $this->pdo->beginTransaction();

            // Get the goal first and verify ownership
            $goal = $this->getGoalByIdAndUser(
                $goal_id,
                $user_id
            );

            if (!$goal) {
                $this->pdo->rollBack();
                return false;
            }

            $goal_type = $goal['Goal_Type'];
            $target = (float) $goal['Target'];

            // Default status
            $status = 'Active';

            // Weight Loss goals decrease towards the target
            if ($goal_type === 'Weight Loss') {
                if ($value <= $target) {
                    $status = 'Completed';
                }
            } else {
                // Strength / Endurance increase towards the target
                if ($value >= $target) {
                    $status = 'Completed';
                }
            }

            // Update current value and status
            $sql = "UPDATE goal
                    SET Current_Value = ?,
                        Status = ?
                    WHERE Goal_ID = ?
                      AND User_ID = ?";

            $stmt = $this->pdo->prepare($sql);

            $stmt->execute([
                $value,
                $status,
                $goal_id,
                $user_id
            ]);

            // Store progress history
            $historySql = "INSERT INTO goal_progress
                                (
                                    Goal_ID,
                                    Value,
                                    Update_Date
                                )
                           VALUES
                                (?, ?, ?)";

            $historyStmt = $this->pdo->prepare(
                $historySql
            );

            $historyStmt->execute([
                $goal_id,
                $value,
                $update_date
            ]);

            $this->pdo->commit();

            return true;

        } catch (PDOException $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            throw $e;
        }
    }
    // Delete a goal belonging to the logged-in user
    public function deleteGoal($goal_id, $user_id)
    {
        $sql = "DELETE FROM goal
                WHERE Goal_ID = ?
                AND User_ID = ?";

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            $goal_id,
            $user_id
        ]);

        return $stmt->rowCount() > 0;
    }

    // Get progress history for one goal
    public function getGoalProgressHistory(
        $goal_id,
        $user_id
    ) {
        $sql = "SELECT
                    gp.Progress_ID,
                    gp.Goal_ID,
                    gp.Value,
                    gp.Update_Date
                FROM goal_progress gp
                INNER JOIN goal g
                    ON gp.Goal_ID = g.Goal_ID
                WHERE gp.Goal_ID = ?
                  AND g.User_ID = ?
                ORDER BY gp.Update_Date DESC,
                         gp.Progress_ID DESC";

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            $goal_id,
            $user_id
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>