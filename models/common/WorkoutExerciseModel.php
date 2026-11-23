<?php

class WorkoutExerciseModel
{
    private PDO $pdo;

    private const EXERCISE_NAME_LENGTH = 100;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function getExerciseCatalog(): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT Exercise_ID, Exercise_Name FROM exercise ORDER BY Exercise_Name'
        );
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function normalizeExerciseName(string $name): string
    {
        $normalized = preg_replace('/[\p{Z}\s]+/u', ' ', trim($name));
        if ($normalized === null) {
            throw new InvalidArgumentException('Enter a valid exercise name.');
        }
        $normalized = mb_strtoupper(trim($normalized), 'UTF-8');
        if ($normalized === '' || mb_strlen($normalized, 'UTF-8') > self::EXERCISE_NAME_LENGTH) {
            throw new InvalidArgumentException('Exercise names must contain 1 to 100 characters.');
        }
        return $normalized;
    }

    public function searchExercises(string $term): array
    {
        $normalizedTerm = preg_replace('/[\p{Z}\s]+/u', ' ', trim($term));
        if ($normalizedTerm === null) {
            throw new InvalidArgumentException('Enter a valid search term.');
        }
        $normalizedTerm = mb_strtoupper(trim($normalizedTerm), 'UTF-8');
        if (mb_strlen($normalizedTerm, 'UTF-8') < 2) {
            return ['exact' => null, 'suggestions' => []];
        }

        $exactStmt = $this->pdo->prepare(
            'SELECT Exercise_ID, Exercise_Name FROM exercise WHERE Exercise_Name_Normalized = ? LIMIT 1'
        );
        $exactStmt->execute([$normalizedTerm]);
        $exact = $exactStmt->fetch(PDO::FETCH_ASSOC) ?: null;

        $searchStmt = $this->pdo->prepare(
            'SELECT Exercise_ID, Exercise_Name FROM exercise '
            . 'WHERE LOCATE(?, Exercise_Name_Normalized) > 0 '
            . 'ORDER BY (Exercise_Name_Normalized = ?) DESC, Exercise_Name_Normalized LIMIT 8'
        );
        $searchStmt->execute([$normalizedTerm, $normalizedTerm]);
        return ['exact' => $exact, 'suggestions' => $searchStmt->fetchAll(PDO::FETCH_ASSOC)];
    }

    public function getOrCreateExercise(string $name): array
    {
        $normalizedName = self::normalizeExerciseName($name);
        $find = $this->pdo->prepare(
            'SELECT Exercise_ID, Exercise_Name FROM exercise WHERE Exercise_Name_Normalized = ? LIMIT 1'
        );
        $findCurrent = $this->pdo->prepare(
            'SELECT Exercise_ID, Exercise_Name FROM exercise WHERE Exercise_Name_Normalized = ? LIMIT 1 FOR UPDATE'
        );
        $find->execute([$normalizedName]);
        $existing = $find->fetch(PDO::FETCH_ASSOC);
        if ($existing) {
            return ['id' => (int)$existing['Exercise_ID'], 'name' => $existing['Exercise_Name'], 'created' => false];
        }

        try {
            $insert = $this->pdo->prepare('INSERT INTO exercise (Exercise_Name) VALUES (?)');
            $insert->execute([$normalizedName]);
            return [
                'id' => (int)$this->pdo->lastInsertId(),
                'name' => $normalizedName,
                'created' => true,
            ];
        } catch (PDOException $e) {
            if ($e->getCode() !== '23000') {
                throw $e;
            }
            $findCurrent->execute([$normalizedName]);
            $existing = $findCurrent->fetch(PDO::FETCH_ASSOC);
            if (!$existing) {
                throw $e;
            }
            return ['id' => (int)$existing['Exercise_ID'], 'name' => $existing['Exercise_Name'], 'created' => false];
        }
    }

    public function resolveSubmittedExercises(array $ids, array $names, array $sets, array $reps): array
    {
        $count = count($ids);
        if ($count < 1 || $count > 40 || count($names) !== $count
            || count($sets) !== $count || count($reps) !== $count) {
            throw new InvalidArgumentException('Add between 1 and 40 complete exercises.');
        }

        $findById = $this->pdo->prepare(
            'SELECT Exercise_ID, Exercise_Name FROM exercise WHERE Exercise_ID = ?'
        );
        $resolved = [];
        $seen = [];

        foreach ($ids as $index => $submittedId) {
            if (!is_scalar($submittedId) || !is_scalar($names[$index] ?? '')
                || !is_scalar($sets[$index] ?? null) || !is_scalar($reps[$index] ?? null)) {
                throw new InvalidArgumentException('Each exercise needs valid details.');
            }
            $setsValue = filter_var($sets[$index] ?? null, FILTER_VALIDATE_INT);
            $repsValue = filter_var($reps[$index] ?? null, FILTER_VALIDATE_INT);
            if ($setsValue === false || $setsValue < 1 || $setsValue > 1000
                || $repsValue === false || $repsValue < 1 || $repsValue > 1000) {
                throw new InvalidArgumentException('Each exercise needs valid positive sets and reps.');
            }

            if ((string)$submittedId === 'custom') {
                $exercise = $this->getOrCreateExercise((string)$names[$index]);
                $exerciseId = $exercise['id'];
            } else {
                $exerciseId = filter_var($submittedId, FILTER_VALIDATE_INT);
                if ($exerciseId === false || $exerciseId < 1) {
                    throw new InvalidArgumentException('Select a valid exercise.');
                }

                $findById->execute([$exerciseId]);
                $existing = $findById->fetch(PDO::FETCH_ASSOC);
                if (!$existing) {
                    throw new InvalidArgumentException('One of the selected exercises no longer exists.');
                }
                if (self::normalizeExerciseName((string)$names[$index]) !== self::normalizeExerciseName($existing['Exercise_Name'])) {
                    throw new InvalidArgumentException('The selected exercise does not match the submitted name.');
                }
            }

            if (isset($seen[$exerciseId])) {
                throw new InvalidArgumentException('An exercise can only appear once in a routine.');
            }
            $seen[$exerciseId] = true;
            $resolved[] = [
                'Exercise_ID' => $exerciseId,
                'Sets' => $setsValue,
                'Reps' => $repsValue,
            ];
        }

        return $resolved;
    }
}
