<?php

require_once __DIR__ . '/../common/LifePenaltyModel.php';

class LeaderboardModel
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function getFacultyOptions(): array
    {
        $stmt = $this->pdo->prepare(
            "SELECT DISTINCT s.Faculty FROM university_student s "
            . "INNER JOIN `user` u ON u.User_ID = s.User_ID "
            . "WHERE u.Role = 'Student' AND s.Status = 'active' "
            . 'AND s.Faculty IS NOT NULL AND TRIM(s.Faculty) <> \'\' '
            . 'ORDER BY s.Faculty'
        );
        $stmt->execute();
        return array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'Faculty');
    }

    public function getLeaderboardRankings(string $period = 'month', ?string $faculty = null): array
    {
        if ($period === 'all') {
            $sql = "SELECT u.User_ID, u.First_Name, u.Last_Name, s.Faculty, s.Profile_Image, "
                . 'SUM(vp.Points) AS Total_Points, COUNT(vp.Attendance_ID) AS Qualified_Visits '
                . 'FROM attendance_visit_points vp '
                . 'INNER JOIN attendance a ON a.Attendance_ID = vp.Attendance_ID '
                . 'INNER JOIN `user` u ON u.User_ID = a.User_ID AND u.Role = \'Student\' '
                . 'INNER JOIN university_student s ON s.User_ID = u.User_ID '
                . 'GROUP BY u.User_ID, u.First_Name, u.Last_Name, s.Faculty, s.Profile_Image '
                . 'ORDER BY Total_Points DESC, u.User_ID ASC';
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
        } else {
            $monthJoin = $period === 'month'
                ? ' AND vp.Awarded_At >= ? AND vp.Awarded_At < ? '
                : '';
            $facultyCondition = $period === 'faculty' ? ' AND s.Faculty = ?' : '';
            $sql = "SELECT u.User_ID, u.First_Name, u.Last_Name, s.Faculty, s.Profile_Image, "
                . 'COALESCE(SUM(vp.Points), 0) AS Total_Points, COUNT(vp.Attendance_ID) AS Qualified_Visits '
                . 'FROM `user` u INNER JOIN university_student s ON s.User_ID = u.User_ID '
                . 'LEFT JOIN attendance a ON a.User_ID = u.User_ID '
                . 'LEFT JOIN attendance_visit_points vp ON vp.Attendance_ID = a.Attendance_ID '
                . $monthJoin
                . 'WHERE u.Role = \'Student\' AND s.Status = \'active\''
                . $facultyCondition . ' '
                . 'GROUP BY u.User_ID, u.First_Name, u.Last_Name, s.Faculty, s.Profile_Image '
                . 'ORDER BY Total_Points DESC, u.User_ID ASC';
            $stmt = $this->pdo->prepare($sql);
            $parameters = $period === 'month' ? self::getMonthBounds() : [];
            if ($period === 'faculty') {
                $parameters[] = $faculty;
            }
            $stmt->execute($parameters);
        }

        return self::applyCompetitionRanks($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    public static function getMonthBounds(?DateTimeImmutable $now = null): array
    {
        $colombo = new DateTimeZone('Asia/Colombo');
        $current = ($now ?? new DateTimeImmutable('now', $colombo))->setTimezone($colombo);
        $start = $current->modify('first day of this month')->setTime(0, 0, 0);
        $end = $start->modify('first day of next month');

        return [$start->format('Y-m-d H:i:s'), $end->format('Y-m-d H:i:s')];
    }

    public function getMemberLifeSummary(int $userId): ?array
    {
        $stmt = $this->pdo->prepare(
            "SELECT s.Life_Percentage, s.Status, s.Faculty FROM university_student s "
            . "INNER JOIN `user` u ON u.User_ID = s.User_ID "
            . "WHERE s.User_ID = ? AND u.Role = 'Student' LIMIT 1"
        );
        $stmt->execute([$userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function getMemberPenalties(int $userId, int $limit = 10): array
    {
        $model = new LifePenaltyModel($this->pdo);
        return $model->getRecentPenaltiesForMember($userId, $limit);
    }

    public static function applyCompetitionRanks(array $rankings): array
    {
        $position = 0;
        $rank = 0;
        $lastPoints = null;
        foreach ($rankings as &$entry) {
            $position++;
            $points = (int)$entry['Total_Points'];
            if ($lastPoints === null || $points !== $lastPoints) {
                $rank = $position;
                $lastPoints = $points;
            }
            $entry['User_ID'] = (int)$entry['User_ID'];
            $entry['Total_Points'] = $points;
            $entry['Qualified_Visits'] = (int)$entry['Qualified_Visits'];
            $entry['Rank'] = $rank;
        }
        unset($entry);
        return $rankings;
    }
}
