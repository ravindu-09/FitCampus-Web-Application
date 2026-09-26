<?php
// models/member/DashboardModel.php

class DashboardModel {
    private $pdo;

    public function __construct($dbConnection) {
        $this->pdo =$dbConnection;
    }

    // 1. Fetch User Attendance / Check-In Status
    public function getAttendanceStatus($user_id) {
        $stmt =$this->pdo->prepare("
            SELECT a.Check_In_Time, a.Active_Status, f.Facility_Name, f.Location
            FROM `attendance` a
            JOIN `facility` f ON a.Facility_ID = f.Facility_ID
            WHERE a.User_ID = :uid
            ORDER BY a.Check_In_Time DESC
            LIMIT 1
        ");
        $stmt->execute([':uid' =>$user_id]);
        return $stmt->fetch();
    }

    // 2. Fetch Facility Live Capacity & Load Calculation
    public function getLiveFacilities() {
        $stmt =$this->pdo->query("
            SELECT f.Facility_ID, f.Facility_Name, f.Capacity,
                   (SELECT COUNT(*) 
                    FROM `attendance` a 
                    WHERE a.Facility_ID = f.Facility_ID AND a.Active_Status = 1) AS live_count
            FROM `facility` f
            ORDER BY f.Facility_ID ASC
            LIMIT 2
        ");
        return $stmt->fetchAll();
    }

    // 3. Fetch Gym Rules & Regulations
    public function getGymRules() {
        $stmt =$this->pdo->query("
            SELECT Rule_No, Rule, Penalty_For_Violation 
            FROM `gym_rule` 
            ORDER BY Rule_No ASC 
            LIMIT 6
        ");
        return $stmt->fetchAll();
    }

    // 4. Fetch Latest Announcements & Motivation
    public function getAnnouncements() {
        $stmt =$this->pdo->query("
            SELECT Title, Description, Publish_Date 
            FROM `announcement` 
            ORDER BY Publish_Date DESC 
            LIMIT 2
        ");
        return $stmt->fetchAll();
    }
}
?>