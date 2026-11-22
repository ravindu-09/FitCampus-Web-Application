<?php
// models/member/GoalModel.php

class GoalModel
{
    private $pdo;

    public function __construct($dbConnection)
    {
        $this->pdo = $dbConnection;
    }

    public function getGoalsByUser($userId)
    {
        $goalStmt = $this->pdo->prepare(
            "SELECT Goal_ID, User_ID, Goal_Name, Activity_Name, Goal_Type,
                    Target, Start_Value, Current_Value, Unit, Start_Date,
                    End_Date, Status
             FROM goal
             WHERE User_ID = ?
             ORDER BY Goal_ID DESC"
        );
        $goalStmt->execute([$userId]);
        $goals = $goalStmt->fetchAll(PDO::FETCH_ASSOC);

        if (!$goals) {
            return [];
        }

        $goalsById = [];
        foreach ($goals as &$goal) {
            $goal['Metrics'] = [];
            $goal['History'] = [];
            $goal['Latest_Performance'] = null;
            $goal['Best_Performance'] = null;
            $goalsById[(int) $goal['Goal_ID']] = &$goal;
        }
        unset($goal);

        $metricStmt = $this->pdo->prepare(
            "SELECT gm.Goal_Metric_ID, gm.Goal_ID, gm.Metric_Key,
                    gm.Metric_Label, gm.Unit, gm.Target_Value,
                    gm.Comparison_Direction, gm.Baseline_Value,
                    gm.Display_Order
             FROM goal_metric gm
             INNER JOIN goal g ON g.Goal_ID = gm.Goal_ID
             WHERE g.User_ID = ?
             ORDER BY gm.Goal_ID, gm.Display_Order, gm.Goal_Metric_ID"
        );
        $metricStmt->execute([$userId]);
        $metricsById = [];
        foreach ($metricStmt->fetchAll(PDO::FETCH_ASSOC) as $metric) {
            $goalId = (int) $metric['Goal_ID'];
            $metric['Goal_Metric_ID'] = (int) $metric['Goal_Metric_ID'];
            $metric['Target_Value'] = $metric['Target_Value'] !== null
                ? (float) $metric['Target_Value']
                : null;
            $metric['Baseline_Value'] = $metric['Baseline_Value'] !== null
                ? (float) $metric['Baseline_Value']
                : null;
            $metric['Latest_Value'] = null;
            $goalsById[$goalId]['Metrics'][] = $metric;
            $metricsById[$metric['Goal_Metric_ID']] = $metric;
        }

        $historyStmt = $this->pdo->prepare(
            "SELECT p.Performance_ID, p.Goal_ID, p.Performance_Date,
                    v.Goal_Metric_ID, v.Measured_Value
             FROM goal_performance p
             INNER JOIN goal g ON g.Goal_ID = p.Goal_ID
             LEFT JOIN goal_performance_value v
                ON v.Performance_ID = p.Performance_ID
               AND v.Goal_ID = p.Goal_ID
             WHERE g.User_ID = ?
             ORDER BY p.Goal_ID, p.Performance_Date, p.Performance_ID,
                      v.Goal_Metric_ID"
        );
        $historyStmt->execute([$userId]);
        $performanceIndexes = [];
        foreach ($historyStmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $goalId = (int) $row['Goal_ID'];
            $performanceId = (int) $row['Performance_ID'];

            if (!isset($performanceIndexes[$goalId][$performanceId])) {
                $performanceIndexes[$goalId][$performanceId] =
                    count($goalsById[$goalId]['History']);
                $goalsById[$goalId]['History'][] = [
                    'Performance_ID' => $performanceId,
                    'Performance_Date' => $row['Performance_Date'],
                    'Values' => []
                ];
            }

            if ($row['Goal_Metric_ID'] !== null) {
                $metricId = (int) $row['Goal_Metric_ID'];
                if (isset($metricsById[$metricId])) {
                    $metric = $metricsById[$metricId];
                    $value = (float) $row['Measured_Value'];
                    $historyIndex = $performanceIndexes[$goalId][$performanceId];
                    $goalsById[$goalId]['History'][$historyIndex]['Values'][$metric['Metric_Key']] = $value;
                    $goalsById[$goalId]['History'][$historyIndex]['Value_Labels'][$metric['Metric_Key']] = [
                        'label' => $metric['Metric_Label'],
                        'unit' => $metric['Unit'],
                        'value' => $value
                    ];
                }
            }
        }

        foreach ($goals as &$goal) {
            $goalId = (int) $goal['Goal_ID'];
            $metrics = &$goal['Metrics'];
            foreach ($metrics as &$metric) {
                $metric['Latest_Value'] = null;
            }
            unset($metric);

            if ($goal['History']) {
                $goal['Latest_Performance'] = $goal['History'][count($goal['History']) - 1];
                foreach ($metrics as &$metric) {
                    $metric['Latest_Value'] =
                        $goal['Latest_Performance']['Values'][$metric['Metric_Key']] ?? null;
                }
                unset($metric);
            } else {
                $goal['Latest_Performance'] = [
                    'Performance_ID' => null,
                    'Performance_Date' => null,
                    'Values' => []
                ];
                if ($metrics) {
                    $primaryKey = $this->primaryMetricKey($goal);
                    foreach ($metrics as &$metric) {
                        if ($metric['Metric_Key'] === $primaryKey && $goal['Current_Value'] !== null) {
                            $metric['Latest_Value'] = (float) $goal['Current_Value'];
                            $goal['Latest_Performance']['Values'][$primaryKey] =
                                (float) $goal['Current_Value'];
                        }
                    }
                    unset($metric);
                }
            }

            if ($goal['Goal_Type'] === 'Strength') {
                $goal['Best_Performance'] = $this->findBestStrengthPerformance(
                    $goal['History'],
                    $metrics
                );
            }

            $hasCompletedEntry = false;
            foreach ($goal['History'] as $entry) {
                if ($this->performanceMeetsTargets($entry['Values'], $metrics)) {
                    $hasCompletedEntry = true;
                    break;
                }
            }
            if (!$hasCompletedEntry && !$goal['History'] && $metrics) {
                $hasCompletedEntry = $this->performanceMeetsTargets(
                    $goal['Latest_Performance']['Values'],
                    $metrics
                );
            }

            $isCompleted = strcasecmp((string) $goal['Status'], 'Completed') === 0
                || $hasCompletedEntry;
            $goal['Is_Completed'] = $isCompleted;
            $goal['Display_Status'] = $isCompleted
                ? 'Completed'
                : (!empty($goal['End_Date']) && $goal['End_Date'] < date('Y-m-d')
                    ? 'Overdue'
                    : 'Active');
            unset($metrics);
        }
        unset($goal);

        return $goals;
    }

    public function getGoalByIdAndUser($goalId, $userId)
    {
        $stmt = $this->pdo->prepare(
            "SELECT Goal_ID, User_ID, Goal_Name, Activity_Name, Goal_Type,
                    Target, Start_Value, Current_Value, Unit, Start_Date,
                    End_Date, Status
             FROM goal
             WHERE Goal_ID = ? AND User_ID = ?
             LIMIT 1"
        );
        $stmt->execute([$goalId, $userId]);
        $goal = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($goal) {
            $goal['Metrics'] = $this->getMetricsByGoalAndUser($goalId, $userId);
        }

        return $goal;
    }

    public function createGoal($userId, array $goal)
    {
        try {
            $this->pdo->beginTransaction();

            $stmt = $this->pdo->prepare(
                "INSERT INTO goal
                    (User_ID, Goal_Name, Activity_Name, Goal_Type, Target,
                     Start_Value, Current_Value, Unit, Start_Date, End_Date, Status)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Active')"
            );
            $primaryMetric = $goal['metrics'][0];
            $stmt->execute([
                $userId,
                $goal['goal_name'],
                $goal['activity_name'],
                $goal['goal_type'],
                $primaryMetric['target'],
                $goal['start_value'],
                $goal['current_value'],
                $primaryMetric['unit'],
                $goal['start_date'],
                $goal['end_date']
            ]);
            $goalId = (int) $this->pdo->lastInsertId();

            $metricStmt = $this->pdo->prepare(
                "INSERT INTO goal_metric
                    (Goal_ID, Metric_Key, Metric_Label, Unit, Target_Value,
                     Comparison_Direction, Baseline_Value, Display_Order)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
            );
            $metricIds = [];
            foreach ($goal['metrics'] as $index => $metric) {
                $metricStmt->execute([
                    $goalId,
                    $metric['key'],
                    $metric['label'],
                    $metric['unit'],
                    $metric['target'],
                    $metric['direction'],
                    $metric['baseline'] ?? null,
                    $index + 1
                ]);
                $metricIds[$metric['key']] = (int) $this->pdo->lastInsertId();
            }

            if (!empty($goal['initial_values'])) {
                $this->insertPerformance(
                    $goalId,
                    $goal['start_date'],
                    $goal['initial_values'],
                    $metricIds
                );
            }

            $this->pdo->commit();
            return true;
        } catch (PDOException $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $e;
        } catch (RuntimeException $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $e;
        }
    }

    public function updateGoalProgress($goalId, $userId, array $values, $updateDate)
    {
        try {
            $this->pdo->beginTransaction();

            $goalStmt = $this->pdo->prepare(
                "SELECT Goal_ID, Goal_Type, Status
                 FROM goal
                 WHERE Goal_ID = ? AND User_ID = ?
                 LIMIT 1 FOR UPDATE"
            );
            $goalStmt->execute([$goalId, $userId]);
            $goal = $goalStmt->fetch(PDO::FETCH_ASSOC);
            if (!$goal) {
                $this->pdo->rollBack();
                return false;
            }

            $metrics = $this->getMetricsByGoalAndUser($goalId, $userId);
            if (!$metrics || count($values) !== count($metrics)) {
                $this->pdo->rollBack();
                return false;
            }

            $metricIds = [];
            foreach ($metrics as $metric) {
                $key = $metric['Metric_Key'];
                if (!array_key_exists($key, $values) || !is_numeric($values[$key])) {
                    $this->pdo->rollBack();
                    return false;
                }
                $value = (float) $values[$key];
                if (!is_finite($value) || $value < 0 || $value > 99999999.99) {
                    $this->pdo->rollBack();
                    return false;
                }
                $values[$key] = $value;
                $metricIds[$key] = (int) $metric['Goal_Metric_ID'];
            }

            $previouslyCompleted = strcasecmp((string) $goal['Status'], 'Completed') === 0
                || $this->hasCompletedPerformance($goalId, $userId, $metrics);
            $performanceId = $this->insertPerformance(
                $goalId,
                $updateDate,
                $values,
                $metricIds
            );
            $completed = $this->performanceMeetsTargets($values, $metrics)
                || $previouslyCompleted;
            $primaryKey = $this->primaryMetricKey($goal);
            $currentValue = $values[$primaryKey] ?? reset($values);

            $updateStmt = $this->pdo->prepare(
                "UPDATE goal
                 SET Current_Value = ?, Status = ?
                 WHERE Goal_ID = ? AND User_ID = ?"
            );
            $updateStmt->execute([
                $currentValue,
                $completed ? 'Completed' : 'Active',
                $goalId,
                $userId
            ]);

            $this->pdo->commit();
            return $performanceId > 0;
        } catch (PDOException $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $e;
        } catch (RuntimeException $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $e;
        }
    }

    public function deleteGoal($goalId, $userId)
    {
        $stmt = $this->pdo->prepare(
            "DELETE FROM goal WHERE Goal_ID = ? AND User_ID = ?"
        );
        $stmt->execute([$goalId, $userId]);
        return $stmt->rowCount() > 0;
    }

    private function getMetricsByGoalAndUser($goalId, $userId)
    {
        $stmt = $this->pdo->prepare(
            "SELECT gm.Goal_Metric_ID, gm.Goal_ID, gm.Metric_Key,
                    gm.Metric_Label, gm.Unit, gm.Target_Value,
                    gm.Comparison_Direction, gm.Baseline_Value,
                    gm.Display_Order
             FROM goal_metric gm
             INNER JOIN goal g ON g.Goal_ID = gm.Goal_ID
             WHERE gm.Goal_ID = ? AND g.User_ID = ?
             ORDER BY gm.Display_Order, gm.Goal_Metric_ID"
        );
        $stmt->execute([$goalId, $userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function insertPerformance($goalId, $performanceDate, array $values, array $metricIds)
    {
        $stmt = $this->pdo->prepare(
            "INSERT INTO goal_performance (Goal_ID, Performance_Date)
             VALUES (?, ?)"
        );
        $stmt->execute([$goalId, $performanceDate]);
        $performanceId = (int) $this->pdo->lastInsertId();

        $valueStmt = $this->pdo->prepare(
            "INSERT INTO goal_performance_value
                (Performance_ID, Goal_ID, Goal_Metric_ID, Measured_Value)
             VALUES (?, ?, ?, ?)"
        );
        foreach ($values as $key => $value) {
            if (!isset($metricIds[$key])) {
                throw new RuntimeException('Performance metric does not belong to this goal.');
            }
            $valueStmt->execute([
                $performanceId,
                $goalId,
                $metricIds[$key],
                $value
            ]);
        }

        return $performanceId;
    }

    private function performanceMeetsTargets(array $values, array $metrics)
    {
        if (!$metrics) {
            return false;
        }
        foreach ($metrics as $metric) {
            $key = $metric['Metric_Key'];
            $target = $metric['Target_Value'];
            if ($target === null || !array_key_exists($key, $values)) {
                return false;
            }
            $value = (float) $values[$key];
            if (
                ($metric['Comparison_Direction'] === 'at_least' && $value < (float) $target)
                || ($metric['Comparison_Direction'] === 'at_most' && $value > (float) $target)
            ) {
                return false;
            }
        }
        return true;
    }

    private function hasCompletedPerformance($goalId, $userId, array $metrics)
    {
        $stmt = $this->pdo->prepare(
            "SELECT p.Performance_ID, gm.Metric_Key, v.Measured_Value
             FROM goal_performance p
             INNER JOIN goal g ON g.Goal_ID = p.Goal_ID
             INNER JOIN goal_performance_value v
                ON v.Performance_ID = p.Performance_ID
               AND v.Goal_ID = p.Goal_ID
             INNER JOIN goal_metric gm
                ON gm.Goal_Metric_ID = v.Goal_Metric_ID
               AND gm.Goal_ID = v.Goal_ID
             WHERE p.Goal_ID = ? AND g.User_ID = ?
             ORDER BY p.Performance_ID, gm.Display_Order"
        );
        $stmt->execute([$goalId, $userId]);

        $valuesByPerformance = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $performanceId = (int) $row['Performance_ID'];
            $valuesByPerformance[$performanceId][$row['Metric_Key']] =
                (float) $row['Measured_Value'];
        }
        foreach ($valuesByPerformance as $values) {
            if ($this->performanceMeetsTargets($values, $metrics)) {
                return true;
            }
        }
        return false;
    }

    private function primaryMetricKey(array $goal)
    {
        if (($goal['Goal_Type'] ?? '') === 'Endurance') {
            return 'distance';
        }
        if (($goal['Goal_Type'] ?? '') === 'Strength') {
            return 'weight';
        }
        return 'weight';
    }

    private function findBestStrengthPerformance(array $history, array $metrics)
    {
        $weightKey = null;
        $repetitionKey = null;
        foreach ($metrics as $metric) {
            if ($metric['Metric_Key'] === 'weight') {
                $weightKey = 'weight';
            } elseif ($metric['Metric_Key'] === 'repetitions') {
                $repetitionKey = 'repetitions';
            }
        }
        if ($weightKey === null) {
            return null;
        }

        $best = null;
        foreach ($history as $entry) {
            if (!isset($entry['Values'][$weightKey])) {
                continue;
            }
            if (
                $best === null
                || $entry['Values'][$weightKey] > $best['Values'][$weightKey]
            ) {
                $best = $entry;
            }
        }
        if ($best !== null && $repetitionKey !== null) {
            $best['Repetitions'] = $best['Values'][$repetitionKey] ?? null;
        }
        return $best;
    }
}
?>
