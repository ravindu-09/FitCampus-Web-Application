<?php
// models/admin/BookingModel.php

class BookingModel {
    private $pdo;

    public function __construct($dbConnection) {
        $this->pdo = $dbConnection;
    }

    // Fetch all facilities for the UI dropdown/tabs
    public function getAllFacilities() {
        $stmt = $this->pdo->query("SELECT Facility_ID, Facility_Name FROM facility ORDER BY Facility_ID ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Fetch facility capacity
    public function getFacilityCapacity($facility_id) {
        $stmt = $this->pdo->prepare("SELECT Capacity FROM facility WHERE Facility_ID = ?");
        $stmt->execute([$facility_id]);
        return $stmt->fetchColumn() ?: 50;
    }

    // Calculate total booked size for a specific slot
    public function getBookedSize($facility_id, $date, $time) {
        $stmt = $this->pdo->prepare("
            SELECT COALESCE(SUM(b.Team_Size), 0) as total_booked 
            FROM booking b 
            WHERE b.Facility_ID = ? AND b.Reserve_Date = ? AND ? >= b.Start_Time AND ? < b.End_Time AND b.Status = 'Approved'
        ");
        $stmt->execute([$facility_id, $date, $time, $time]);
        return $stmt->fetchColumn();
    }

    // Get exact details of teams booked in a slot
    public function getSlotDetails($facility_id, $date, $time) {
        $stmt = $this->pdo->prepare("
            SELECT b.*, t.Team_Name, u.First_Name, u.Last_Name, us.Registration_Number 
            FROM booking b
            JOIN team t ON b.Team_ID = t.Team_ID
            JOIN user u ON b.Requested_By = u.User_ID
            LEFT JOIN university_student us ON u.User_ID = us.User_ID
            WHERE b.Facility_ID = ? AND b.Reserve_Date = ? 
            AND (? >= b.Start_Time AND ? < b.End_Time)
            AND b.Status IN ('Approved', 'Pending')
            ORDER BY b.Status ASC, b.Start_Time ASC
        ");
        $stmt->execute([$facility_id, $date, $time, $time]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get all pending special requests
    public function getPendingRequests() {
        $stmt = $this->pdo->query("
            SELECT b.*, f.Facility_Name, t.Team_Name, u.First_Name, u.Last_Name 
            FROM booking b
            JOIN facility f ON b.Facility_ID = f.Facility_ID
            JOIN team t ON b.Team_ID = t.Team_ID
            JOIN user u ON b.Requested_By = u.User_ID
            WHERE b.Status = 'Pending'
            ORDER BY b.Booking_Time DESC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get booking basic info for conflict resolution
    public function getBookingById($booking_id) {
        $stmt = $this->pdo->prepare("SELECT Facility_ID, Reserve_Date, Start_Time, End_Time, Team_Size FROM booking WHERE Booking_ID = ?");
        $stmt->execute([$booking_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Get approved total size for conflict calculation
    public function getApprovedBookedSizeForConflict($facility_id, $date, $startTime) {
        $stmt = $this->pdo->prepare("
            SELECT COALESCE(SUM(Team_Size), 0) FROM booking 
            WHERE Facility_ID = ? AND Reserve_Date = ? AND Status = 'Approved'
            AND (? >= Start_Time AND ? < End_Time)
        ");
        $stmt->execute([$facility_id, $date, $startTime, $startTime]);
        return (int)$stmt->fetchColumn();
    }

    // Get occupants details for conflict modal
    public function getOccupantsForConflict($facility_id, $date, $startTime) {
        $stmt = $this->pdo->prepare("
            SELECT b.Booking_ID, t.Team_Name, b.Team_Size, b.Start_Time, b.End_Time 
            FROM booking b
            JOIN team t ON b.Team_ID = t.Team_ID
            WHERE b.Facility_ID = ? AND b.Reserve_Date = ? AND b.Status = 'Approved'
            AND (? >= b.Start_Time AND ? < b.End_Time)
        ");
        $stmt->execute([$facility_id, $date, $startTime, $startTime]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Process approval with overriding cancellations using Transactions
    public function approveBookingWithConflicts($booking_id, $admin_id, $cancel_ids, $cancel_reason) {
        try {
            $this->pdo->beginTransaction();

            $stmtNotiUser = $this->pdo->prepare("INSERT INTO notification (User_ID, Title, Message) VALUES (?, ?, ?)");
            $stmtNotiTeam = $this->pdo->prepare("INSERT INTO notification (Team_ID, Title, Message) VALUES (?, ?, ?)");

            if (!empty($cancel_ids)) {
                $inQuery = implode(',', array_fill(0, count($cancel_ids), '?'));
                $stmtCancel = $this->pdo->prepare("UPDATE booking SET Status = 'Cancelled', Exception_Reason = ? WHERE Booking_ID IN ($inQuery)");
                $params = array_merge([$cancel_reason], $cancel_ids);
                $stmtCancel->execute($params);

                $stmtTeams = $this->pdo->prepare("SELECT DISTINCT Team_ID FROM booking WHERE Booking_ID IN ($inQuery)");
                $stmtTeams->execute($cancel_ids);
                $cancelled_team_ids = $stmtTeams->fetchAll(PDO::FETCH_COLUMN);

                if (!empty($cancelled_team_ids)) {
                    foreach($cancelled_team_ids as $team_id) {
                        $msg = "Your team's booking was cancelled due to a facility override. Reason: " . $cancel_reason;
                        $stmtNotiTeam->execute([$team_id, 'Booking Cancelled', $msg]);
                    }
                }
            }

            $stmtApprove = $this->pdo->prepare("UPDATE booking SET Status = 'Approved', Approved_By = ? WHERE Booking_ID = ?");
            $stmtApprove->execute([$admin_id, $booking_id]);

            $stmtReq = $this->pdo->prepare("
                SELECT b.Requested_By, b.Team_ID, b.Reserve_Date, b.Start_Time, b.End_Time, t.Team_Name 
                FROM booking b
                JOIN team t ON b.Team_ID = t.Team_ID
                WHERE b.Booking_ID = ?
            ");
            $stmtReq->execute([$booking_id]);
            $req_data = $stmtReq->fetch(PDO::FETCH_ASSOC);

            if ($req_data) {
                $team_name = $req_data['Team_Name'];
                $reserve_date = $req_data['Reserve_Date'];
                $start_time = date('h:i A', strtotime($req_data['Start_Time']));
                $end_time = date('h:i A', strtotime($req_data['End_Time']));

                $msg = "Your Special Request for team '{$team_name}' on {$reserve_date} from {$start_time} to {$end_time} has been Approved.";
                $stmtNotiUser->execute([$req_data['Requested_By'], 'Special Request Approved', $msg]);
            }

            $this->pdo->commit();
            return ['success' => true];
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    // Process rejection using Transactions
    public function declineBooking($booking_id, $admin_id, $reject_reason) {
        try {
            $this->pdo->beginTransaction();

            $stmt = $this->pdo->prepare("UPDATE booking SET Status = 'Rejected', Approved_By = ?, Exception_Reason = ? WHERE Booking_ID = ?");
            $stmt->execute([$admin_id, $reject_reason, $booking_id]);

            $stmtNotiTeam = $this->pdo->prepare("INSERT INTO notification (Team_ID, Title, Message) VALUES (?, ?, ?)");

            $stmtTeam = $this->pdo->prepare("
                SELECT b.Team_ID, b.Reserve_Date, b.Start_Time, b.End_Time, t.Team_Name 
                FROM booking b
                JOIN team t ON b.Team_ID = t.Team_ID
                WHERE b.Booking_ID = ?
            ");
            $stmtTeam->execute([$booking_id]);
            $booking_info = $stmtTeam->fetch(PDO::FETCH_ASSOC);

            if ($booking_info) {
                $team_id = $booking_info['Team_ID'];
                $team_name = $booking_info['Team_Name'];
                $reserve_date = $booking_info['Reserve_Date'];
                $start_time = date('h:i A', strtotime($booking_info['Start_Time']));
                $end_time = date('h:i A', strtotime($booking_info['End_Time']));

                $msg = "Your team '{$team_name}' special request for {$reserve_date} ({$start_time} - {$end_time}) was Rejected. Reason: " . $reject_reason;
                $stmtNotiTeam->execute([$team_id, 'Special Request Rejected', $msg]);
            }

            $this->pdo->commit();
            return ['success' => true];
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}
?>