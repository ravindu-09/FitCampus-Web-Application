<?php
// models/instructor/KioskModel.php
require_once __DIR__ . '/../common/VisitPointsModel.php';

class KioskModel {
    private $pdo;

    public function __construct($dbConnection) {
        $this->pdo = $dbConnection;
    }
    
    public function getLiveOccupancy() {
        $stmt = $this->pdo->query("
            SELECT f.Facility_ID, f.Facility_Name, f.Capacity,
                   (SELECT COUNT(*) FROM attendance a WHERE a.Facility_ID = f.Facility_ID AND a.Active_Status = 1) AS live_count
            FROM facility f
            ORDER BY f.Facility_ID ASC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getRecentAttendance($limit = 10) {
        $stmt = $this->pdo->prepare("
            SELECT a.Attendance_ID, a.User_ID, a.Check_In_Time, a.Check_Out_Time,
                    a.Active_Status, us.Registration_Number, us.Faculty,
                    u.First_Name, u.Last_Name, us.Profile_Image, us.Life_Percentage,
                    CASE WHEN a.Active_Status = 1 THEN 'Active' ELSE 'Complete' END AS Attendance_Status
            FROM attendance a
            JOIN user u ON a.User_ID = u.User_ID
            JOIN university_student us ON u.User_ID = us.User_ID
            ORDER BY a.Check_In_Time DESC
            LIMIT ?
        ");
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getFacilities(): array {
        return $this->pdo->query(
            'SELECT Facility_ID, Facility_Name FROM facility ORDER BY Facility_Name'
        )->fetchAll(PDO::FETCH_ASSOC);
    }

    public function recordManualCheckIn(string $registrationNumber, int $facilityId): array {
        $this->pdo->beginTransaction();
        try {
            $student = $this->lockStudentByRegistrationNumber($registrationNumber);
            if (!$student || $student['Status'] !== 'active') {
                throw new DomainException('An active student account was not found.');
            }

            $facility = $this->pdo->prepare(
                'SELECT Facility_ID, Facility_Name, '
                . '(TIME(NOW()) >= Open_Time AND TIME(NOW()) < Close_Time) AS Is_Open '
                . 'FROM facility WHERE Facility_ID = ? FOR UPDATE'
            );
            $facility->execute([$facilityId]);
            $facilityRecord = $facility->fetch(PDO::FETCH_ASSOC);
            if (!$facilityRecord) {
                throw new InvalidArgumentException('Select a valid facility.');
            }
            if (!(bool)$facilityRecord['Is_Open']) {
                throw new DomainException('Check-in is only available during facility opening hours.');
            }

            $active = $this->pdo->prepare(
                'SELECT Attendance_ID FROM attendance '
                . 'WHERE User_ID = ? AND Active_Status = 1 FOR UPDATE'
            );
            $active->execute([$student['User_ID']]);
            if ($active->fetchColumn() !== false) {
                throw new DomainException('This student already has an active gym visit.');
            }

            $insert = $this->pdo->prepare(
                "INSERT INTO attendance "
                . "(User_ID, Facility_ID, Check_In_Time, Check_In_Method, Active_Status) "
                . "VALUES (?, ?, NOW(), 'Manual', 1)"
            );
            $insert->execute([$student['User_ID'], $facilityId]);
            $attendanceId = (int)$this->pdo->lastInsertId();
            $this->pdo->commit();

            return [
                'attendance_id' => $attendanceId,
                'member_name' => trim($student['First_Name'] . ' ' . $student['Last_Name']),
                'facility_name' => $facilityRecord['Facility_Name'],
            ];
        } catch (Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $e;
        }
    }

    public function recordManualCheckOut(string $registrationNumber): array {
        $this->pdo->beginTransaction();
        try {
            // Both checkout paths lock the member row before the attendance row.
            $student = $this->lockStudentByRegistrationNumber($registrationNumber);
            if (!$student) {
                throw new DomainException('Student account was not found.');
            }

            $active = $this->pdo->prepare(
                'SELECT Attendance_ID FROM attendance '
                . 'WHERE User_ID = ? AND Active_Status = 1 AND Check_Out_Time IS NULL '
                . 'ORDER BY Check_In_Time DESC FOR UPDATE'
            );
            $active->execute([$student['User_ID']]);
            $attendanceIds = $active->fetchAll(PDO::FETCH_COLUMN);
            if (count($attendanceIds) !== 1) {
                throw new DomainException(
                    count($attendanceIds) === 0
                        ? 'No active gym visit was found for this student.'
                        : 'Multiple active visits were found. Contact an administrator.'
                );
            }

            $attendanceId = (int)$attendanceIds[0];
            $update = $this->pdo->prepare(
                "UPDATE attendance SET Check_Out_Time = NOW(), Check_Out_Method = 'Manual', Active_Status = 0 "
                . 'WHERE Attendance_ID = ? AND Active_Status = 1 AND Check_Out_Time IS NULL'
            );
            $update->execute([$attendanceId]);
            if ($update->rowCount() !== 1) {
                throw new DomainException('This gym visit has already been checked out.');
            }

            $visitPoints = new VisitPointsModel($this->pdo);
            $points = $visitPoints->awardCompletedAttendance($attendanceId);
            $duration = $this->pdo->prepare(
                'SELECT TIMESTAMPDIFF(MINUTE, Check_In_Time, Check_Out_Time) '
                . 'FROM attendance WHERE Attendance_ID = ?'
            );
            $duration->execute([$attendanceId]);
            $visitMinutes = (int)$duration->fetchColumn();
            $this->pdo->commit();

            return [
                'attendance_id' => $attendanceId,
                'member_name' => trim($student['First_Name'] . ' ' . $student['Last_Name']),
                'visit_minutes' => $visitMinutes,
                'points_awarded' => $points,
            ];
        } catch (Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $e;
        }
    }

    public function getStudentByRegNo($reg_no) {
        $stmt = $this->pdo->prepare("
            SELECT u.User_ID, u.First_Name, u.Last_Name, us.Registration_Number,
                   us.Life_Percentage, us.Status
            FROM university_student us
            JOIN user u ON us.User_ID = u.User_ID
            WHERE us.Registration_Number = ?
              AND u.Role = 'Student'
            LIMIT 1
        ");
        $stmt->execute([$reg_no]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    private function lockStudentByRegistrationNumber(string $registrationNumber): array|false {
        $stmt = $this->pdo->prepare(
            "SELECT u.User_ID, u.First_Name, u.Last_Name, us.Status "
            . "FROM university_student us INNER JOIN user u ON u.User_ID = us.User_ID "
            . "WHERE us.Registration_Number = ? AND u.Role = 'Student' LIMIT 1 FOR UPDATE"
        );
        $stmt->execute([$registrationNumber]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}