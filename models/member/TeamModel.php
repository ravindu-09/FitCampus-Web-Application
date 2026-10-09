<?php
// models/member/TeamModel.php

class TeamModel {
    private $pdo;

    public function __construct($dbConnection) {
        $this->pdo = $dbConnection;
    }

    public function getTeamsByUser($user_id) {
        $stmt = $this->pdo->prepare("
            SELECT t.Team_ID, t.Team_Name, t.Sport, t.Sport_Gender
            FROM team t
            INNER JOIN team_member tm ON tm.Team_ID = t.Team_ID
            WHERE tm.User_ID = ?
            ORDER BY t.Team_Name
        ");
        $stmt->execute([$user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getUpcomingApprovedBookingsForTeam($team_id, $user_id) {
        $stmt = $this->pdo->prepare("
            SELECT b.Booking_ID, b.Reserve_Date, b.Start_Time, b.End_Time,
                   f.Facility_Name, tw.Workout_ID, tw.Title AS Workout_Title
            FROM booking b
            INNER JOIN team_member tm
                ON tm.Team_ID = b.Team_ID AND tm.User_ID = ?
            INNER JOIN facility f ON f.Facility_ID = b.Facility_ID
            LEFT JOIN booking_workout bw ON bw.Booking_ID = b.Booking_ID
            LEFT JOIN team_workout tw
                ON tw.Workout_ID = bw.Workout_ID AND tw.Team_ID = b.Team_ID
            WHERE b.Team_ID = ?
              AND b.Status = 'Approved'
              AND TIMESTAMP(b.Reserve_Date, b.Start_Time) >= CURRENT_TIMESTAMP
              AND TIMESTAMP(b.Reserve_Date, b.Start_Time) < DATE_ADD(CURRENT_TIMESTAMP, INTERVAL 14 DAY)
            ORDER BY b.Reserve_Date ASC, b.Start_Time ASC, b.Booking_ID ASC
        ");
        $stmt->execute([$user_id, $team_id]);

        $sessions = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $bookingId = (int)$row['Booking_ID'];
            if (!isset($sessions[$bookingId])) {
                $sessions[$bookingId] = [
                    'Booking_ID' => $bookingId,
                    'Reserve_Date' => $row['Reserve_Date'],
                    'Start_Time' => $row['Start_Time'],
                    'End_Time' => $row['End_Time'],
                    'Facility_Name' => $row['Facility_Name'],
                    'Assigned_Workouts' => []
                ];
            }

            if ($row['Workout_ID'] !== null) {
                $sessions[$bookingId]['Assigned_Workouts'][] = [
                    'Workout_ID' => (int)$row['Workout_ID'],
                    'Title' => $row['Workout_Title']
                ];
            }
        }

        return array_values($sessions);
    }
}
?>
