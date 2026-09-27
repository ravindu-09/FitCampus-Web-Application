<?php
// models/instructor/KioskModel.php
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
            LIMIT 2
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getRecentAttendance($limit = 10) {
        $stmt = $this->pdo->prepare("
            SELECT a.Attendance_ID, a.Check_In_Time, us.Registration_Number,
                    u.First_Name, u.Last_Name, us.Profile_Image, us.Life_Percentage, a.Active_Status
            FROM attendance a
            JOIN user u ON a.User_ID = u.User_ID
            LEFT JOIN university_student us ON u.User_ID = us.User_ID
            ORDER BY a.Check_In_Time DESC
            LIMIT ?
        ");
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getStudentByRegNo($reg_no) {
        $stmt = $this->pdo->prepare("
            SELECT u.User_ID, u.First_Name, u.Last_Name, us.Registration_Number, us.Life_Percentage
            FROM university_student us
            JOIN user u ON us.User_ID = u.User_ID
            WHERE us.Registration_Number = ?
            LIMIT 1
        ");
        $stmt->execute([$reg_no]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}