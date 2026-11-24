<?php

require_once __DIR__ . '/../common/WorkoutExerciseModel.php';

class InstructorWorkoutModel
{
    private PDO $pdo;
    private WorkoutExerciseModel $exerciseModel;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
        $this->exerciseModel = new WorkoutExerciseModel($pdo);
    }

    public function getExerciseCatalog(): array
    {
        return $this->exerciseModel->getExerciseCatalog();
    }

    public function getWorkouts(int $instructorId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT cw.Common_Workout_ID, cw.Title, cw.Workout_Level, cw.Duration, cw.Description, '
            . 'e.Exercise_ID, e.Exercise_Name, cwe.Sets, cwe.Reps '
            . 'FROM common_workout cw '
            . 'LEFT JOIN common_workout_exercise cwe ON cwe.Common_Workout_ID = cw.Common_Workout_ID '
            . 'LEFT JOIN exercise e ON e.Exercise_ID = cwe.Exercise_ID '
            . 'WHERE cw.Instructor_ID = ? ORDER BY cw.Common_Workout_ID DESC, e.Exercise_Name'
        );
        $stmt->execute([$instructorId]);
        $workouts = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $id = (int)$row['Common_Workout_ID'];
            if (!isset($workouts[$id])) {
                $workouts[$id] = $row;
                $workouts[$id]['Exercises'] = [];
            }
            if ($row['Exercise_ID'] !== null) {
                $workouts[$id]['Exercises'][] = [
                    'Exercise_ID' => (int)$row['Exercise_ID'],
                    'Exercise_Name' => $row['Exercise_Name'],
                    'Sets' => (int)$row['Sets'],
                    'Reps' => (int)$row['Reps'],
                ];
            }
        }
        return array_values($workouts);
    }

    public function createWorkout(int $instructorId, array $routine, array $submittedExercises): int
    {
        $this->pdo->beginTransaction();
        try {
            $exercises = $this->resolveExercises($submittedExercises);
            $stmt = $this->pdo->prepare(
                'INSERT INTO common_workout (Instructor_ID, Workout_Level, Duration, Title, Description) '
                . 'VALUES (?, ?, ?, ?, ?)'
            );
            $stmt->execute([
                $instructorId,
                $routine['level'],
                $routine['duration'],
                $routine['title'],
                $routine['description'],
            ]);
            $workoutId = (int)$this->pdo->lastInsertId();
            $this->insertExercises($workoutId, $exercises);
            $this->pdo->commit();
            return $workoutId;
        } catch (Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $e;
        }
    }

    public function updateWorkout(int $workoutId, int $instructorId, array $routine, array $submittedExercises): bool
    {
        $this->pdo->beginTransaction();
        try {
            $lock = $this->pdo->prepare(
                'SELECT Common_Workout_ID FROM common_workout '
                . 'WHERE Common_Workout_ID = ? AND Instructor_ID = ? FOR UPDATE'
            );
            $lock->execute([$workoutId, $instructorId]);
            if ($lock->fetchColumn() === false) {
                throw new DomainException('Recommendation not found.');
            }
            $exercises = $this->resolveExercises($submittedExercises);
            $stmt = $this->pdo->prepare(
                'UPDATE common_workout SET Workout_Level = ?, Duration = ?, Title = ?, Description = ? '
                . 'WHERE Common_Workout_ID = ? AND Instructor_ID = ?'
            );
            $stmt->execute([
                $routine['level'],
                $routine['duration'],
                $routine['title'],
                $routine['description'],
                $workoutId,
                $instructorId,
            ]);
            $delete = $this->pdo->prepare(
                'DELETE FROM common_workout_exercise WHERE Common_Workout_ID = ?'
            );
            $delete->execute([$workoutId]);
            $this->insertExercises($workoutId, $exercises);
            $this->pdo->commit();
            return true;
        } catch (Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $e;
        }
    }

    public function deleteWorkout(int $workoutId, int $instructorId): bool
    {
        $stmt = $this->pdo->prepare(
            'DELETE FROM common_workout WHERE Common_Workout_ID = ? AND Instructor_ID = ?'
        );
        $stmt->execute([$workoutId, $instructorId]);
        return $stmt->rowCount() > 0;
    }

    private function resolveExercises(array $submittedExercises): array
    {
        return $this->exerciseModel->resolveSubmittedExercises(
            $submittedExercises['id'],
            $submittedExercises['name'],
            $submittedExercises['sets'],
            $submittedExercises['reps']
        );
    }

    private function insertExercises(int $workoutId, array $exercises): void
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO common_workout_exercise (Common_Workout_ID, Exercise_ID, Sets, Reps) '
            . 'VALUES (?, ?, ?, ?)'
        );
        foreach ($exercises as $exercise) {
            $stmt->execute([
                $workoutId,
                $exercise['Exercise_ID'],
                $exercise['Sets'],
                $exercise['Reps'],
            ]);
        }
    }
}
