<?php

require_once __DIR__ . '/LifePenaltyModel.php';

class VisitPointsModel
{
    public const POINTS_PER_VISIT = 10;
    public const MINIMUM_VISIT_MINUTES = 30;
    public const MISSED_SIGNOUT_DEDUCTION = 2;

    public static function qualifiesDuration(?int $visitMinutes): bool
    {
        return $visitMinutes !== null && $visitMinutes >= self::MINIMUM_VISIT_MINUTES;
    }

    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function awardCompletedAttendance(int $attendanceId): int
    {
        if (!$this->pdo->inTransaction()) {
            throw new LogicException('Visit points must be awarded inside an attendance transaction.');
        }

        $attendance = $this->pdo->prepare(
            'SELECT Check_In_Time, Check_Out_Time, Check_In_Method, Check_Out_Method, Active_Status, '
            . 'TIMESTAMPDIFF(MINUTE, Check_In_Time, Check_Out_Time) AS Visit_Minutes '
            . 'FROM attendance WHERE Attendance_ID = ? FOR UPDATE'
        );
        $attendance->execute([$attendanceId]);
        $visit = $attendance->fetch(PDO::FETCH_ASSOC);
        if (!$visit
            || (int)$visit['Active_Status'] !== 0
            || $visit['Check_Out_Time'] === null
            || trim((string)$visit['Check_In_Method']) === ''
            || trim((string)$visit['Check_Out_Method']) === ''
            || $visit['Visit_Minutes'] === null
            || !self::qualifiesDuration((int)$visit['Visit_Minutes'])) {
            return 0;
        }

        $existing = $this->pdo->prepare(
            'SELECT Attendance_ID FROM attendance_visit_points WHERE Attendance_ID = ? FOR UPDATE'
        );
        $existing->execute([$attendanceId]);
        if ($existing->fetchColumn() !== false) {
            return 0;
        }

        $insert = $this->pdo->prepare(
            'INSERT INTO attendance_visit_points (Attendance_ID, Points, Visit_Minutes) VALUES (?, ?, ?)'
        );
        $insert->execute([
            $attendanceId,
            self::POINTS_PER_VISIT,
            (int)$visit['Visit_Minutes'],
        ]);
        return self::POINTS_PER_VISIT;
    }

    public function finalizeFacilityClosures(): array
    {
        $due = $this->pdo->query(
            'SELECT a.Attendance_ID '
            . 'FROM attendance a '
            . 'INNER JOIN facility f ON f.Facility_ID = a.Facility_ID '
            . 'WHERE a.Active_Status = 1 AND a.Check_Out_Time IS NULL '
            . 'AND TIME(a.Check_In_Time) < f.Close_Time '
            . 'AND TIMESTAMP(DATE(a.Check_In_Time), f.Close_Time) <= NOW() '
            . 'ORDER BY a.Attendance_ID'
        )->fetchAll(PDO::FETCH_COLUMN);

        $results = ['closed' => 0, 'penalties' => 0, 'points' => 0];
        foreach ($due as $attendanceId) {
            $result = $this->finalizeAttendance((int)$attendanceId);
            foreach ($results as $key => $_) {
                $results[$key] += $result[$key];
            }
        }
        return $results;
    }

    private function finalizeAttendance(int $attendanceId): array
    {
        $this->pdo->beginTransaction();
        try {
            $ownerLookup = $this->pdo->prepare(
                'SELECT User_ID FROM attendance WHERE Attendance_ID = ?'
            );
            $ownerLookup->execute([$attendanceId]);
            $userId = $ownerLookup->fetchColumn();
            if ($userId === false) {
                $this->pdo->commit();
                return ['closed' => 0, 'penalties' => 0, 'points' => 0];
            }

            $lifeStatement = $this->pdo->prepare(
                'SELECT Life_Percentage FROM university_student WHERE User_ID = ? FOR UPDATE'
            );
            $lifeStatement->execute([$userId]);
            $currentLife = $lifeStatement->fetchColumn();
            if ($currentLife === false) {
                throw new DomainException('Student Life Bar record was not found.');
            }

            $stmt = $this->pdo->prepare(
                'SELECT a.User_ID, a.Facility_ID, a.Check_In_Time, a.Active_Status, a.Check_Out_Time, '
                . 'f.Close_Time, TIMESTAMP(DATE(a.Check_In_Time), f.Close_Time) AS Scheduled_Close, NOW() AS Current_Time '
                . 'FROM attendance a INNER JOIN facility f ON f.Facility_ID = a.Facility_ID '
                . 'WHERE a.Attendance_ID = ? FOR UPDATE'
            );
            $stmt->execute([$attendanceId]);
            $attendance = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$attendance
                || (int)$attendance['User_ID'] !== (int)$userId
                || (int)$attendance['Active_Status'] !== 1
                || $attendance['Check_Out_Time'] !== null
                || strtotime($attendance['Scheduled_Close']) > strtotime($attendance['Current_Time'])
                || strtotime($attendance['Check_In_Time']) >= strtotime($attendance['Scheduled_Close'])) {
                $this->pdo->commit();
                return ['closed' => 0, 'penalties' => 0, 'points' => 0];
            }

            $alreadyPenalized = $this->pdo->prepare(
                'SELECT Penalty_ID FROM penalty WHERE Source_Attendance_ID = ? FOR UPDATE'
            );
            $alreadyPenalized->execute([$attendanceId]);
            if ($alreadyPenalized->fetchColumn() !== false) {
                throw new LogicException('Open attendance already has a missed-signout penalty.');
            }

            $requestedDeduction = self::MISSED_SIGNOUT_DEDUCTION;
            $actualDeduction = LifePenaltyModel::calculateActualDeduction(
                (int)$currentLife,
                $requestedDeduction
            );

            $close = $this->pdo->prepare(
                "UPDATE attendance SET Check_Out_Time = ?, Check_Out_Method = 'Auto_Close', Active_Status = 0 "
                . 'WHERE Attendance_ID = ? AND Active_Status = 1 AND Check_Out_Time IS NULL'
            );
            $close->execute([$attendance['Scheduled_Close'], $attendanceId]);
            if ($close->rowCount() !== 1) {
                $this->pdo->commit();
                return ['closed' => 0, 'penalties' => 0, 'points' => 0];
            }

            $penalty = $this->pdo->prepare(
                'INSERT INTO penalty '
                . '(User_ID, Team_ID, Instructor_ID, Issuer_User_ID, Issuer_Type, Description, Date, Created_At, '
                . 'Severity, Requested_Life_Deduction, Life_Deduction, Source_Attendance_ID) '
                . "VALUES (?, NULL, NULL, NULL, 'system', ?, DATE(?), NOW(), 'Missed sign-out', ?, ?, ?)"
            );
            $penalty->execute([
                $attendance['User_ID'],
                'Attendance remained open at the scheduled facility close.',
                $attendance['Scheduled_Close'],
                $requestedDeduction,
                $actualDeduction,
                $attendanceId,
            ]);

            $life = $this->pdo->prepare(
                'UPDATE university_student '
                . 'SET Life_Percentage = Life_Percentage - ? WHERE User_ID = ?'
            );
            $life->execute([$actualDeduction, $attendance['User_ID']]);

            $points = $this->awardCompletedAttendance($attendanceId);
            $this->pdo->commit();

            return ['closed' => 1, 'penalties' => 1, 'points' => $points];
        } catch (Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $e;
        }
    }
}
