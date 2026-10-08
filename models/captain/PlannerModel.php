<?php
// models/captain/PlannerModel.php

class PlannerModel {
    private $pdo;

    public function __construct($dbConnection) {
        $this->pdo = $dbConnection;
    }

    public function getCaptainTeams($user_id) {
        $stmt = $this->pdo->prepare("
            SELECT t.Team_ID, t.Team_Name, t.Sport
            FROM team t
            INNER JOIN team_member tm ON tm.Team_ID = t.Team_ID
            WHERE tm.User_ID = ? AND tm.Role_In_Team = 'Captain'
            ORDER BY t.Team_Name
        ");
        $stmt->execute([$user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTeamWorkoutsByUser($user_id) {
        $stmt = $this->pdo->prepare("
            SELECT tw.Workout_ID, tw.Team_ID, t.Team_Name, tw.Title,
                   tw.Description, tw.Duration, e.Exercise_ID,
                   e.Exercise_Name, twe.Sets, twe.Reps
            FROM team_workout tw
            INNER JOIN team t ON t.Team_ID = tw.Team_ID
            INNER JOIN team_member captain
                ON captain.Team_ID = tw.Team_ID
                AND captain.User_ID = ?
                AND captain.Role_In_Team = 'Captain'
            LEFT JOIN team_workout_exercise twe ON twe.Workout_ID = tw.Workout_ID
            LEFT JOIN exercise e ON e.Exercise_ID = twe.Exercise_ID
            ORDER BY t.Team_Name, tw.Title, e.Exercise_Name
        ");
        $stmt->execute([$user_id]);

        $workouts = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $workoutId = (int)$row['Workout_ID'];
            if (!isset($workouts[$workoutId])) {
                $workouts[$workoutId] = [
                    'Workout_ID' => $workoutId,
                    'Team_ID' => (int)$row['Team_ID'],
                    'Team_Name' => $row['Team_Name'],
                    'Title' => $row['Title'],
                    'Description' => $row['Description'],
                    'Duration' => $row['Duration'],
                    'Exercises' => []
                ];
            }

            if ($row['Exercise_ID'] !== null) {
                $workouts[$workoutId]['Exercises'][] = [
                    'Exercise_ID' => (int)$row['Exercise_ID'],
                    'Exercise_Name' => $row['Exercise_Name'],
                    'Sets' => $row['Sets'],
                    'Reps' => $row['Reps']
                ];
            }
        }

        return array_values($workouts);
    }

    public function createWorkout($team_id, $user_id, $title, $description, $duration, array $exercises) {
        $this->pdo->beginTransaction();
        try {
            if (!$this->lockCaptainTeam($team_id, $user_id)) {
                throw new DomainException('You are not a captain of the selected team.');
            }

            $stmt = $this->pdo->prepare(
                'INSERT INTO team_workout (Team_ID, Title, Description, Duration) VALUES (?, ?, ?, ?)'
            );
            $stmt->execute([$team_id, $title, $description, $duration]);
            $workout_id = (int)$this->pdo->lastInsertId();
            $this->replaceExercises($workout_id, $exercises);
            $this->pdo->commit();
            return $workout_id;
        } catch (Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $e;
        }
    }

    public function updateWorkout($workout_id, $team_id, $user_id, $title, $description, $duration, array $exercises) {
        $this->pdo->beginTransaction();
        try {
            if (!$this->lockOwnedWorkout($workout_id, $team_id, $user_id)) {
                throw new DomainException('Workout not found for a team you captain.');
            }

            $stmt = $this->pdo->prepare(
                'UPDATE team_workout SET Title = ?, Description = ?, Duration = ? WHERE Workout_ID = ? AND Team_ID = ?'
            );
            $stmt->execute([$title, $description, $duration, $workout_id, $team_id]);

            $delete = $this->pdo->prepare('DELETE FROM team_workout_exercise WHERE Workout_ID = ?');
            $delete->execute([$workout_id]);
            $this->insertExercises($workout_id, $exercises);

            $this->pdo->commit();
            return true;
        } catch (Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $e;
        }
    }

    public function deleteWorkout($workout_id, $team_id, $user_id) {
        $this->pdo->beginTransaction();
        try {
            if (!$this->lockOwnedWorkout($workout_id, $team_id, $user_id)) {
                throw new DomainException('Workout not found for a team you captain.');
            }

            $stmt = $this->pdo->prepare(
                'DELETE FROM team_workout WHERE Workout_ID = ? AND Team_ID = ?'
            );
            $stmt->execute([$workout_id, $team_id]);
            $this->pdo->commit();
            return true;
        } catch (Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $e;
        }
    }

    private function lockCaptainTeam($team_id, $user_id) {
        $stmt = $this->pdo->prepare(
            "SELECT Team_ID FROM team_member WHERE Team_ID = ? AND User_ID = ? AND Role_In_Team = 'Captain' FOR UPDATE"
        );
        $stmt->execute([$team_id, $user_id]);
        return $stmt->fetchColumn() !== false;
    }

    private function lockOwnedWorkout($workout_id, $team_id, $user_id) {
        $stmt = $this->pdo->prepare(
            "SELECT tw.Workout_ID
             FROM team_workout tw
             INNER JOIN team_member tm ON tm.Team_ID = tw.Team_ID
             WHERE tw.Workout_ID = ? AND tw.Team_ID = ?
               AND tm.User_ID = ? AND tm.Role_In_Team = 'Captain'
             FOR UPDATE"
        );
        $stmt->execute([$workout_id, $team_id, $user_id]);
        return $stmt->fetchColumn() !== false;
    }

    private function replaceExercises($workout_id, array $exercises) {
        $this->insertExercises($workout_id, $exercises);
    }

    private function insertExercises($workout_id, array $exercises) {
        $findExercise = $this->pdo->prepare(
            'SELECT Exercise_ID FROM exercise WHERE Exercise_Name = ? LIMIT 1 FOR UPDATE'
        );
        $createExercise = $this->pdo->prepare(
            'INSERT INTO exercise (Exercise_Name) VALUES (?)'
        );
        $linkExercise = $this->pdo->prepare(
            'INSERT INTO team_workout_exercise (Workout_ID, Exercise_ID, Sets, Reps) VALUES (?, ?, ?, ?)'
        );
        $linkedExerciseIds = [];

        foreach ($exercises as $exercise) {
            $exerciseName = preg_replace('/\s+/', ' ', trim($exercise['name']));
            $findExercise->execute([$exerciseName]);
            $exercise_id = $findExercise->fetchColumn();
            if ($exercise_id === false) {
                $createExercise->execute([$exerciseName]);
                $exercise_id = (int)$this->pdo->lastInsertId();
            } else {
                $exercise_id = (int)$exercise_id;
            }

            if (isset($linkedExerciseIds[$exercise_id])) {
                throw new InvalidArgumentException('An exercise can only appear once in a workout.');
            }
            $linkedExerciseIds[$exercise_id] = true;
            $linkExercise->execute([
                $workout_id,
                $exercise_id,
                $exercise['sets'],
                $exercise['reps']
            ]);
        }
    }
}
?>