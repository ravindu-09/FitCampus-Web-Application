<?php
// models/captain/BookingModel.php

class BookingModel {
    private $pdo;

    public function __construct($dbConnection) {
        $this->pdo = $dbConnection;
    }

    // Fetch all teams where the current user holds the 'Captain' role
    public function getCaptainTeams($user_id) {
        $stmt = $this->pdo->prepare("
            SELECT t.Team_ID, t.Team_Name, t.Sport, 
                   (SELECT COUNT(*) FROM team_member WHERE Team_ID = t.Team_ID) as Member_Count 
            FROM team t 
            JOIN team_member tm ON t.Team_ID = tm.Team_ID 
            WHERE tm.User_ID = ? AND tm.Role_In_Team = 'Captain'
        ");
        $stmt->execute([$user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Fetch all facilities to generate toggle buttons
    public function getAllFacilities() {
        $stmt = $this->pdo->query("SELECT Facility_ID, Facility_Name FROM facility ORDER BY Facility_ID ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Fetch the booking history specific to the logged-in captain
    public function getBookingHistory($user_id) {
        $stmt = $this->pdo->prepare("
            SELECT b.*, f.Facility_Name, t.Team_Name 
            FROM booking b
            JOIN facility f ON b.Facility_ID = f.Facility_ID
            JOIN team t ON b.Team_ID = t.Team_ID
            WHERE b.Requested_By = ?
            ORDER BY b.Reserve_Date DESC, b.Start_Time DESC
        ");
        $stmt->execute([$user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Fetch facility capacity
    public function getFacilityCapacity($facility_id) {
        $stmt = $this->pdo->prepare("SELECT Capacity FROM facility WHERE Facility_ID = ?");
        $stmt->execute([$facility_id]);
        return $stmt->fetchColumn() ?: 50;
    }

    // Check confirmed bookings for schedule grid
    public function getBookedTotal($facility_id, $current_date, $time) {
        $stmtBooked = $this->pdo->prepare("
            SELECT COALESCE(SUM(b.Team_Size), 0) as total_booked 
            FROM booking b 
            WHERE b.Facility_ID = ? AND b.Reserve_Date = ? AND ? >= b.Start_Time AND ? < b.End_Time AND b.Status = 'Approved'
        ");
        $stmtBooked->execute([$facility_id, $current_date, $time, $time]);
        return $stmtBooked->fetchColumn();
    }

    // Check pending requests for schedule grid
    public function getPendingCount($facility_id, $current_date, $time) {
        $stmtPending = $this->pdo->prepare("SELECT COUNT(*) FROM booking WHERE Facility_ID = ? AND Reserve_Date = ? AND ? >= Start_Time AND ? < End_Time AND Status = 'Pending'");
        $stmtPending->execute([$facility_id, $current_date, $time, $time]);
        return $stmtPending->fetchColumn();
    }

    // Check duplicate bookings
    public function checkDuplicateBooking($team_id, $date, $slot_time) {
        $stmtDup = $this->pdo->prepare("SELECT COUNT(*) FROM booking WHERE Team_ID = ? AND Reserve_Date = ? AND ? >= Start_Time AND ? < End_Time AND Status IN ('Approved', 'Pending')");
        $stmtDup->execute([$team_id, $date, $slot_time, $slot_time]);
        return $stmtDup->fetchColumn();
    }

    // Check weekly limit
    public function getWeeklyBookingCount($team_id, $week_start, $week_end) {
        $stmtLimit = $this->pdo->prepare("SELECT COUNT(*) FROM booking WHERE Team_ID = ? AND Status IN ('Approved', 'Pending') AND Reserve_Date BETWEEN ? AND ?");
        $stmtLimit->execute([$team_id, $week_start, $week_end]);
        return $stmtLimit->fetchColumn();
    }

    // Check slot capacity
    public function getSlotCapacityUsage($facility_id, $date, $slot_time) {
        $stmtCapCheck = $this->pdo->prepare("
            SELECT COALESCE(SUM(b.Team_Size), 0) 
            FROM booking b 
            WHERE b.Facility_ID = ? AND b.Reserve_Date = ? AND ? >= b.Start_Time AND ? < b.End_Time AND b.Status = 'Approved'
        ");
        $stmtCapCheck->execute([$facility_id, $date, $slot_time, $slot_time]);
        return $stmtCapCheck->fetchColumn();
    }

    // Insert new booking
    public function createBooking($team_id, $user_id, $facility_id, $team_size_input, $date, $time, $end_time, $status, $reason) {
        $stmt = $this->pdo->prepare("
            INSERT INTO booking (Team_ID, Requested_By, Facility_ID, Team_Size, Booking_Time, Reserve_Date, Start_Time, End_Time, Status, Exception_Reason) 
            VALUES (?, ?, ?, ?, NOW(), ?, ?, ?, ?, ?)
        ");
        return $stmt->execute([$team_id, $user_id, $facility_id, $team_size_input, $date, $time, $end_time, $status, $reason]);
    }

    public function getFacilityName($facility_id) {
        $stmtFacName = $this->pdo->prepare("SELECT Facility_Name FROM facility WHERE Facility_ID = ?");
        $stmtFacName->execute([$facility_id]);
        return $stmtFacName->fetchColumn();
    }

    public function getTeamName($team_id) {
        $stmtTeamName = $this->pdo->prepare("SELECT Team_Name FROM team WHERE Team_ID = ?");
        $stmtTeamName->execute([$team_id]);
        return $stmtTeamName->fetchColumn();
    }
}
?>