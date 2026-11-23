<?php
// models/member/WorkoutModel.php

class WorkoutModel {
    private $pdo;
    private WorkoutExerciseModel $exerciseModel;

    public function __construct($dbConnection) {
        $this->pdo = $dbConnection;
        require_once __DIR__ . '/../common/WorkoutExerciseModel.php';
        $this->exerciseModel = new WorkoutExerciseModel($dbConnection);
    }

    public function getExerciseCatalog() {
        return $this->exerciseModel->getExerciseCatalog();
    }

    public function getPersonalWorkouts($user_id) {
        $stmt = $this->pdo->prepare("
            SELECT pw.Personal_Workout_ID, pw.Title, pw.Duration,
                   e.Exercise_ID, e.Exercise_Name, pwe.Sets, pwe.Reps
            FROM personal_workout pw
            LEFT JOIN personal_workout_exercise pwe
                ON pwe.Personal_Workout_ID = pw.Personal_Workout_ID
            LEFT JOIN exercise e ON e.Exercise_ID = pwe.Exercise_ID
            WHERE pw.User_ID = ?
            ORDER BY pw.Personal_Workout_ID DESC, e.Exercise_Name
        ");
        $stmt->execute([$user_id]);
        return $this->groupWorkoutRows($stmt->fetchAll(PDO::FETCH_ASSOC), 'Personal_Workout_ID');
    }

    public function getRecommendations($user_id) {
        $stmt = $this->pdo->prepare("
            SELECT cw.Common_Workout_ID, cw.Title, cw.Workout_Level, cw.Duration,
                   cw.Description, u.First_Name, u.Last_Name,
                   (sw.User_ID IS NOT NULL) AS Is_Saved,
                   e.Exercise_ID, e.Exercise_Name, cwe.Sets, cwe.Reps
            FROM common_workout cw
            INNER JOIN instructor i ON i.Instructor_ID = cw.Instructor_ID
            INNER JOIN `user` u ON u.User_ID = i.Instructor_ID
            LEFT JOIN member_saved_common_workout sw
                ON sw.Common_Workout_ID = cw.Common_Workout_ID AND sw.User_ID = ?
            LEFT JOIN common_workout_exercise cwe
                ON cwe.Common_Workout_ID = cw.Common_Workout_ID
            LEFT JOIN exercise e ON e.Exercise_ID = cwe.Exercise_ID
            ORDER BY cw.Title, e.Exercise_Name
        ");
        $stmt->execute([$user_id]);
        $workouts = $this->groupWorkoutRows($stmt->fetchAll(PDO::FETCH_ASSOC), 'Common_Workout_ID');
        foreach ($workouts as &$workout) {
            $workout['Instructor_Name'] = trim($workout['First_Name'] . ' ' . $workout['Last_Name']);
            $workout['Is_Saved'] = (bool)$workout['Is_Saved'];
            unset($workout['First_Name'], $workout['Last_Name']);
        }
        unset($workout);
        return $workouts;
    }

    public function createPersonalWorkout($user_id, array $routine, array $submittedExercises) {
        $this->pdo->beginTransaction();
        try {
            $exercises = $this->exerciseModel->resolveSubmittedExercises(
                $submittedExercises['id'],
                $submittedExercises['name'],
                $submittedExercises['sets'],
                $submittedExercises['reps']
            );
            $stmt = $this->pdo->prepare(
                'INSERT INTO personal_workout (User_ID, Title, Duration) VALUES (?, ?, ?)'
            );
            $stmt->execute([$user_id, $routine['title'], $routine['duration']]);
            $workoutId = (int)$this->pdo->lastInsertId();
            $this->insertPersonalExercises($workoutId, $exercises);
            $this->pdo->commit();
            return $workoutId;
        } catch (Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $e;
        }
    }

    public function updatePersonalWorkout($workout_id, $user_id, array $routine, array $submittedExercises) {
        $this->pdo->beginTransaction();
        try {
            $lock = $this->pdo->prepare(
                'SELECT Personal_Workout_ID FROM personal_workout WHERE Personal_Workout_ID = ? AND User_ID = ? FOR UPDATE'
            );
            $lock->execute([$workout_id, $user_id]);
            if ($lock->fetchColumn() === false) {
                throw new DomainException('Workout not found.');
            }
            $exercises = $this->exerciseModel->resolveSubmittedExercises(
                $submittedExercises['id'],
                $submittedExercises['name'],
                $submittedExercises['sets'],
                $submittedExercises['reps']
            );
            $update = $this->pdo->prepare(
                'UPDATE personal_workout SET Title = ?, Duration = ? WHERE Personal_Workout_ID = ? AND User_ID = ?'
            );
            $update->execute([$routine['title'], $routine['duration'], $workout_id, $user_id]);
            $delete = $this->pdo->prepare(
                'DELETE FROM personal_workout_exercise WHERE Personal_Workout_ID = ?'
            );
            $delete->execute([$workout_id]);
            $this->insertPersonalExercises($workout_id, $exercises);
            $this->pdo->commit();
            return true;
        } catch (Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $e;
        }
    }

    public function deletePersonalWorkout($workout_id, $user_id) {
        $stmt = $this->pdo->prepare(
            'DELETE FROM personal_workout WHERE Personal_Workout_ID = ? AND User_ID = ?'
        );
        $stmt->execute([$workout_id, $user_id]);
        return $stmt->rowCount() > 0;
    }

    public function saveRecommendation($user_id, $workout_id) {
        $exists = $this->pdo->prepare(
            'SELECT Common_Workout_ID FROM common_workout WHERE Common_Workout_ID = ?'
        );
        $exists->execute([$workout_id]);
        if ($exists->fetchColumn() === false) {
            throw new DomainException('Recommendation not found.');
        }
        $stmt = $this->pdo->prepare(
            'INSERT INTO member_saved_common_workout (User_ID, Common_Workout_ID) VALUES (?, ?) '
            . 'ON DUPLICATE KEY UPDATE Saved_At = Saved_At'
        );
        $stmt->execute([$user_id, $workout_id]);
        return true;
    }

    public function unsaveRecommendation($user_id, $workout_id) {
        $stmt = $this->pdo->prepare(
            'DELETE FROM member_saved_common_workout WHERE User_ID = ? AND Common_Workout_ID = ?'
        );
        $stmt->execute([$user_id, $workout_id]);
        return true;
    }

    public function copyRecommendation($user_id, $workout_id) {
        $this->pdo->beginTransaction();
        try {
            $stmt = $this->pdo->prepare(
                'SELECT Common_Workout_ID, Title, Duration FROM common_workout '
                . 'WHERE Common_Workout_ID = ? FOR UPDATE'
            );
            $stmt->execute([$workout_id]);
            $source = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$source) {
                throw new DomainException('Recommendation not found.');
            }

            $existingId = $this->findPersonalCopy($user_id, $workout_id);
            if ($existingId !== null) {
                $this->pdo->commit();
                return ['id' => $existingId, 'created' => false];
            }

            $exerciseStmt = $this->pdo->prepare(
                'SELECT Exercise_ID, Sets, Reps FROM common_workout_exercise '
                . 'WHERE Common_Workout_ID = ? ORDER BY Exercise_ID'
            );
            $exerciseStmt->execute([$workout_id]);
            $sourceExercises = $exerciseStmt->fetchAll(PDO::FETCH_ASSOC);

            $create = $this->pdo->prepare(
                'INSERT INTO personal_workout (User_ID, Source_Common_Workout_ID, Title, Duration) '
                . 'VALUES (?, ?, ?, ?)'
            );
            $create->execute([$user_id, $workout_id, $source['Title'], $source['Duration']]);
            $personalId = (int)$this->pdo->lastInsertId();
            $this->insertPersonalExercises($personalId, $sourceExercises);
            $this->pdo->commit();
            return ['id' => $personalId, 'created' => true];
        } catch (PDOException $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            if ($e->getCode() === '23000') {
                $existingId = $this->findPersonalCopy($user_id, $workout_id);
                if ($existingId !== null) {
                    return ['id' => $existingId, 'created' => false];
                }
            }
            throw $e;
        } catch (Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $e;
        }
    }

    private function findPersonalCopy($user_id, $workout_id): ?int {
        $stmt = $this->pdo->prepare(
            'SELECT Personal_Workout_ID FROM personal_workout '
            . 'WHERE User_ID = ? AND Source_Common_Workout_ID = ? FOR UPDATE'
        );
        $stmt->execute([$user_id, $workout_id]);
        $personalWorkoutId = $stmt->fetchColumn();
        return $personalWorkoutId === false ? null : (int)$personalWorkoutId;
    }

    private function groupWorkoutRows(array $rows, $idColumn) {
        $workouts = [];
        foreach ($rows as $row) {
            $workoutId = (int)$row[$idColumn];
            if (!isset($workouts[$workoutId])) {
                $workouts[$workoutId] = $row;
                $workouts[$workoutId]['Exercises'] = [];
            }
            if ($row['Exercise_ID'] !== null) {
                $workouts[$workoutId]['Exercises'][] = [
                    'Exercise_ID' => (int)$row['Exercise_ID'],
                    'Exercise_Name' => $row['Exercise_Name'],
                    'Sets' => (int)$row['Sets'],
                    'Reps' => (int)$row['Reps'],
                ];
            }
        }
        return array_values($workouts);
    }

    private function insertPersonalExercises($workout_id, array $exercises) {
        $stmt = $this->pdo->prepare(
            'INSERT INTO personal_workout_exercise (Personal_Workout_ID, Exercise_ID, Sets, Reps) VALUES (?, ?, ?, ?)'
        );
        foreach ($exercises as $exercise) {
            $stmt->execute([
                $workout_id,
                $exercise['Exercise_ID'],
                $exercise['Sets'],
                $exercise['Reps'],
            ]);
        }
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
