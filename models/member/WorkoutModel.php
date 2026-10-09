<?php
// models/member/WorkoutModel.php

class WorkoutModel {
    private $pdo;

    public function __construct($dbConnection) {
        $this->pdo = $dbConnection;
    }

    public function getWorkoutsForTeamMember($team_id, $user_id) {
        $stmt = $this->pdo->prepare("
            SELECT tw.Workout_ID, tw.Title, tw.Description, tw.Duration,
                   e.Exercise_ID, e.Exercise_Name, twe.Sets, twe.Reps
            FROM team_workout tw
            INNER JOIN team_member tm
                ON tm.Team_ID = tw.Team_ID AND tm.User_ID = ?
            LEFT JOIN team_workout_exercise twe ON twe.Workout_ID = tw.Workout_ID
            LEFT JOIN exercise e ON e.Exercise_ID = twe.Exercise_ID
            WHERE tw.Team_ID = ?
            ORDER BY tw.Title, e.Exercise_Name
        ");
        $stmt->execute([$user_id, $team_id]);

        $workouts = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $workoutId = (int)$row['Workout_ID'];
            if (!isset($workouts[$workoutId])) {
                $workouts[$workoutId] = [
                    'Workout_ID' => $workoutId,
                    'Title' => $row['Title'],
                    'Description' => $row['Description'],
                    'Duration' => $row['Duration'],
                    'Exercises' => []
                ];
            }

            if ($row['Exercise_ID'] !== null) {
                $workouts[$workoutId]['Exercises'][] = [
                    'Exercise_Name' => $row['Exercise_Name'],
                    'Sets' => $row['Sets'],
                    'Reps' => $row['Reps']
                ];
            }
        }

        return array_values($workouts);
    }

    public function getAssignedWorkoutDetailsForMember($booking_id, $workout_id, $user_id) {
        $stmt = $this->pdo->prepare("
            SELECT b.Team_ID, tw.Workout_ID, tw.Title, tw.Description, tw.Duration,
                   e.Exercise_ID, e.Exercise_Name, twe.Sets, twe.Reps
            FROM booking b
            INNER JOIN team_member tm
                ON tm.Team_ID = b.Team_ID AND tm.User_ID = ?
            INNER JOIN booking_workout bw
                ON bw.Booking_ID = b.Booking_ID AND bw.Workout_ID = ?
            INNER JOIN team_workout tw
                ON tw.Workout_ID = bw.Workout_ID AND tw.Team_ID = b.Team_ID
            LEFT JOIN team_workout_exercise twe ON twe.Workout_ID = tw.Workout_ID
            LEFT JOIN exercise e ON e.Exercise_ID = twe.Exercise_ID
            WHERE b.Booking_ID = ?
              AND b.Status = 'Approved'
            ORDER BY e.Exercise_Name
        ");
        $stmt->execute([$user_id, $workout_id, $booking_id]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($rows)) {
            return null;
        }

        $workout = [
            'Team_ID' => (int)$rows[0]['Team_ID'],
            'Workout_ID' => (int)$rows[0]['Workout_ID'],
            'Title' => $rows[0]['Title'],
            'Description' => $rows[0]['Description'],
            'Duration' => $rows[0]['Duration'],
            'Exercises' => []
        ];

        foreach ($rows as $row) {
            if ($row['Exercise_ID'] !== null) {
                $workout['Exercises'][] = [
                    'Exercise_Name' => $row['Exercise_Name'],
                    'Sets' => $row['Sets'],
                    'Reps' => $row['Reps']
                ];
            }
        }

        return $workout;
    }
}
?>
