<?php

class LifePenaltyModel
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function applyPenalty(
        int $userId,
        int $issuerUserId,
        string $issuerType,
        string $reason,
        string $severity,
        int $requestedDeduction,
        string $requestKey
    ): array {
        if (!in_array($issuerType, ['admin', 'instructor'], true)) {
            throw new InvalidArgumentException('Only administrators and instructors may issue penalties.');
        }
        if ($userId < 1 || $issuerUserId < 1) {
            throw new InvalidArgumentException('Select a valid member and issuer.');
        }
        if ($reason === '' || mb_strlen($reason) > 2000) {
            throw new InvalidArgumentException('Enter a reason of 1 to 2,000 characters.');
        }
        if ($severity === '' || mb_strlen($severity) > 30) {
            throw new InvalidArgumentException('Enter a severity label of 1 to 30 characters.');
        }
        if ($requestedDeduction < 1 || $requestedDeduction > 100) {
            throw new InvalidArgumentException('Penalty deductions must be between 1 and 100 Life points.');
        }
        if (!preg_match('/\A[a-f0-9]{32}\z/i', $requestKey)) {
            throw new InvalidArgumentException('The penalty request token is invalid.');
        }

        $requestKey = strtolower($requestKey);
        $this->pdo->beginTransaction();
        try {
            $existing = $this->findByRequestKey($requestKey, true);
            if ($existing) {
                $this->assertSamePenalty($existing, $userId, $reason, $severity, $requestedDeduction);
                $this->pdo->commit();
                return [
                    'penalty_id' => (int)$existing['Penalty_ID'],
                    'life_balance' => (int)$existing['Life_Percentage'],
                    'requested_life_deduction' => (int)$existing['Requested_Life_Deduction'],
                    'life_deduction' => (int)$existing['Life_Deduction'],
                    'replayed' => true,
                ];
            }

            $student = $this->pdo->prepare(
                "SELECT s.Life_Percentage FROM university_student s "
                . "INNER JOIN `user` u ON u.User_ID = s.User_ID "
                . "WHERE s.User_ID = ? AND u.Role = 'Student' FOR UPDATE"
            );
            $student->execute([$userId]);
            $currentLife = $student->fetchColumn();
            if ($currentLife === false) {
                throw new DomainException('Student account not found.');
            }
            $actualDeduction = self::calculateActualDeduction((int)$currentLife, $requestedDeduction);

            $instructorId = $issuerType === 'instructor' ? $issuerUserId : null;
            $insert = $this->pdo->prepare(
                'INSERT INTO penalty '
                . '(User_ID, Team_ID, Instructor_ID, Issuer_User_ID, Issuer_Type, Description, Date, Created_At, '
                . 'Severity, Requested_Life_Deduction, Life_Deduction, Source_Attendance_ID, Request_Key) '
                . 'VALUES (?, NULL, ?, ?, ?, ?, CURDATE(), NOW(), ?, ?, ?, NULL, ?)'
            );
            $insert->execute([
                $userId,
                $instructorId,
                $issuerUserId,
                $issuerType,
                $reason,
                $severity,
                $requestedDeduction,
                $actualDeduction,
                $requestKey,
            ]);
            $penaltyId = (int)$this->pdo->lastInsertId();

            $update = $this->pdo->prepare(
                'UPDATE university_student SET Life_Percentage = Life_Percentage - ? '
                . 'WHERE User_ID = ?'
            );
            $update->execute([$actualDeduction, $userId]);

            $balance = $this->pdo->prepare(
                'SELECT Life_Percentage FROM university_student WHERE User_ID = ?'
            );
            $balance->execute([$userId]);
            $lifeBalance = (int)$balance->fetchColumn();
            $this->pdo->commit();

            return [
                'penalty_id' => $penaltyId,
                'life_balance' => $lifeBalance,
                'requested_life_deduction' => $requestedDeduction,
                'life_deduction' => $actualDeduction,
                'replayed' => false,
            ];
        } catch (PDOException $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            if ($e->getCode() === '23000') {
                $existing = $this->findByRequestKey($requestKey);
                if ($existing) {
                    $this->assertSamePenalty($existing, $userId, $reason, $severity, $requestedDeduction);
                    return [
                        'penalty_id' => (int)$existing['Penalty_ID'],
                        'life_balance' => (int)$existing['Life_Percentage'],
                        'requested_life_deduction' => (int)$existing['Requested_Life_Deduction'],
                        'life_deduction' => (int)$existing['Life_Deduction'],
                        'replayed' => true,
                    ];
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

    public static function calculateActualDeduction(int $currentLife, int $requestedDeduction): int
    {
        if ($currentLife < 0 || $currentLife > 100 || $requestedDeduction < 1 || $requestedDeduction > 100) {
            throw new InvalidArgumentException('Life Bar and requested deduction must be within their valid ranges.');
        }

        return min($requestedDeduction, $currentLife);
    }

    public function getRecentPenaltiesForMember(int $userId, int $limit = 10): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT p.Penalty_ID, p.Description, p.Severity, p.Requested_Life_Deduction, '
            . 'p.Life_Deduction, p.Created_At, '
            . 'p.Issuer_Type, issuer.First_Name AS Issuer_First_Name, issuer.Last_Name AS Issuer_Last_Name '
            . 'FROM penalty p LEFT JOIN `user` issuer ON issuer.User_ID = p.Issuer_User_ID '
            . 'WHERE p.User_ID = ? AND p.Life_Deduction IS NOT NULL '
            . 'ORDER BY p.Created_At DESC, p.Penalty_ID DESC LIMIT ?'
        );
        $stmt->bindValue(1, $userId, PDO::PARAM_INT);
        $stmt->bindValue(2, max(1, min(50, $limit)), PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function findByRequestKey(string $requestKey, bool $forUpdate = false): array|false
    {
        $sql = 'SELECT p.Penalty_ID, p.User_ID, p.Description, p.Severity, '
            . 'COALESCE(p.Requested_Life_Deduction, p.Life_Deduction) AS Requested_Life_Deduction, p.Life_Deduction, '
            . 's.Life_Percentage FROM penalty p '
            . 'LEFT JOIN university_student s ON s.User_ID = p.User_ID '
            . 'WHERE p.Request_Key = ?';
        if ($forUpdate) {
            $sql .= ' FOR UPDATE';
        }
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$requestKey]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    private function assertSamePenalty(
        array $existing,
        int $userId,
        string $reason,
        string $severity,
        int $requestedDeduction
    ): void {
        if ((int)$existing['User_ID'] !== $userId
            || $existing['Description'] !== $reason
            || $existing['Severity'] !== $severity
            || (int)$existing['Requested_Life_Deduction'] !== $requestedDeduction) {
            throw new DomainException('This request token was already used for a different penalty.');
        }
    }
}
