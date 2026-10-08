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
}
?>