<?php

require_once __DIR__ . '/../models/common/LifePenaltyModel.php';
require_once __DIR__ . '/../models/common/VisitPointsModel.php';
require_once __DIR__ . '/../models/member/LeaderboardModel.php';

function verify(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

function source(string $relativePath): string
{
    $contents = file_get_contents(__DIR__ . '/../' . $relativePath);
    if ($contents === false) {
        throw new RuntimeException('Unable to read verification input: ' . $relativePath);
    }
    return $contents;
}

verify(!VisitPointsModel::qualifiesDuration(29), 'A 29-minute visit must not qualify.');
verify(VisitPointsModel::qualifiesDuration(30), 'A 30-minute visit must qualify.');
verify(!VisitPointsModel::qualifiesDuration(null), 'A missing duration must not qualify.');

verify(
    LifePenaltyModel::calculateActualDeduction(1, 100) === 1,
    'A 100-point request against a 1-point balance must remove exactly 1.'
);
verify(
    LifePenaltyModel::calculateActualDeduction(0, 2) === 0,
    'A penalty against a zero balance must record a zero actual deduction.'
);
verify(
    LifePenaltyModel::calculateActualDeduction(1, 2) === 1,
    'An automatic two-point missed-sign-out request must remove only the remaining one point.'
);

$marchBoundary = new DateTimeImmutable('2026-03-31 23:59:59', new DateTimeZone('Asia/Colombo'));
$aprilBoundary = new DateTimeImmutable('2026-04-01 00:00:00', new DateTimeZone('Asia/Colombo'));
verify(
    LeaderboardModel::getMonthBounds($marchBoundary) === ['2026-03-01 00:00:00', '2026-04-01 00:00:00'],
    'The final instant of March must use March leaderboard boundaries.'
);
verify(
    LeaderboardModel::getMonthBounds($aprilBoundary) === ['2026-04-01 00:00:00', '2026-05-01 00:00:00'],
    'The first instant of April must use April leaderboard boundaries.'
);

$ranked = LeaderboardModel::applyCompetitionRanks([
    ['User_ID' => 3, 'Total_Points' => 20, 'Qualified_Visits' => 2],
    ['User_ID' => 8, 'Total_Points' => 20, 'Qualified_Visits' => 2],
    ['User_ID' => 12, 'Total_Points' => 10, 'Qualified_Visits' => 1],
]);
verify(
    array_column($ranked, 'Rank') === [1, 1, 3],
    'Equal points must receive shared competition ranks.'
);

$freshSchema = source('sql/fitcampus_db.sql');
$migration = source('sql/member_leaderboard_life_bar_migration.sql');
$pointsModel = source('models/common/VisitPointsModel.php');
$kioskModel = source('models/instructor/KioskModel.php');
$penaltyModel = source('models/common/LifePenaltyModel.php');
$leaderboardModel = source('models/member/LeaderboardModel.php');
$kioskAction = source('controllers/instructor/kiosk_action.php');
$penaltyAction = source('controllers/common/penalty_action.php');
$header = source('includes/headers/header_member.php');
$connection = source('includes/db_connection.php');
$allTimeStart = strpos($leaderboardModel, "if (\$period === 'all')");
$allTimeEnd = $allTimeStart === false ? false : strpos($leaderboardModel, '} else {', $allTimeStart);
$allTimeQuery = ($allTimeStart !== false && $allTimeEnd !== false)
    ? substr($leaderboardModel, $allTimeStart, $allTimeEnd - $allTimeStart)
    : '';

verify(
    str_contains($freshSchema, 'idx_attendance_user_active_checkout')
        && str_contains($migration, 'idx_attendance_user_active_checkout'),
    'The attendance lookup index must be present in both schema paths.'
);
verify(
    str_contains($freshSchema, 'Requested_Life_Deduction')
        && str_contains($freshSchema, 'BETWEEN 0 AND 100')
        && str_contains($freshSchema, 'chk_penalty_actual_not_above_requested')
        && str_contains($migration, 'Requested_Life_Deduction')
        && str_contains($migration, 'BETWEEN 0 AND 100')
        && str_contains($migration, 'BETWEEN 1 AND 100')
        && str_contains($migration, 'chk_penalty_actual_not_above_requested'),
    'Fresh schema and migration must distinguish requested and actual deductions.'
);
verify(
    str_contains($freshSchema, 'PRIMARY KEY (`Attendance_ID`)')
        && str_contains($migration, 'PRIMARY KEY (`Attendance_ID`)'),
    'Attendance must uniquely key its visit-points ledger entry.'
);
verify(
    str_contains($pointsModel, "Check_Out_Method = 'Auto_Close'")
        && str_contains($pointsModel, 'WHERE Attendance_ID = ? AND Active_Status = 1 AND Check_Out_Time IS NULL')
        && str_contains($pointsModel, 'Source_Attendance_ID')
        && str_contains($pointsModel, 'awardCompletedAttendance($attendanceId)'),
    'Facility close must conditionally finalize, audit once, and use the shared points ledger.'
);
verify(
    str_contains($kioskModel, '$student = $this->lockStudentByRegistrationNumber($registrationNumber)')
        && str_contains($kioskModel, 'WHERE User_ID = ? AND Active_Status = 1 AND Check_Out_Time IS NULL')
        && str_contains($kioskModel, 'ORDER BY Check_In_Time DESC FOR UPDATE')
        && str_contains($kioskModel, 'if ($update->rowCount() !== 1)'),
    'Manual checkout must lock the member before the active attendance row.'
);
verify(
    strpos($pointsModel, 'SELECT User_ID FROM attendance WHERE Attendance_ID = ?')
        < strpos($pointsModel, 'SELECT Life_Percentage FROM university_student WHERE User_ID = ? FOR UPDATE')
        && strpos($pointsModel, 'SELECT Life_Percentage FROM university_student WHERE User_ID = ? FOR UPDATE')
        < strpos($pointsModel, 'WHERE a.Attendance_ID = ? FOR UPDATE'),
    'Facility close must use the same member-then-attendance lock order as manual checkout.'
);
verify(
    str_contains($penaltyModel, 'Requested_Life_Deduction, Life_Deduction')
        && str_contains($penaltyModel, 'calculateActualDeduction')
        && str_contains($penaltyModel, 'Requested_Life_Deduction\'] !== $requestedDeduction')
        && !str_contains($penaltyModel, 'attendance_visit_points'),
    'Penalty writes and request replay checks must distinguish the requested deduction.'
);
verify(
    str_contains($leaderboardModel, 'AND s.Faculty = ?')
        && str_contains($leaderboardModel, 'if ($period === \'all\')')
        && str_contains($leaderboardModel, 's.Status = \\\'active\\\'')
        && !str_contains($allTimeQuery, 's.Status')
        && str_contains($leaderboardModel, 'getMonthBounds')
        && str_contains($leaderboardModel, 'ORDER BY Total_Points DESC, u.User_ID ASC'),
    'Leaderboard period, faculty, eligibility, month, and ordering rules must be present.'
);
verify(
    str_contains($kioskAction, '($_SESSION[\'role\'] ?? \'\') !== \'instructor\'')
        && str_contains($kioskAction, 'hash_equals')
        && str_contains($penaltyAction, '[\'admin\', \'instructor\']')
        && str_contains($penaltyAction, 'hash_equals')
        && str_contains($kioskAction, '401')
        && str_contains($penaltyAction, '401'),
    'Attendance and penalty actions must retain their role and CSRF guards.'
);
verify(
    str_contains($header, 'SELECT s.Life_Percentage')
        && !str_contains($header, '$_SESSION[\'life_percentage\']')
        && str_contains($connection, 'SET time_zone = \'+05:30\'')
        && str_contains($connection, 'date_default_timezone_set(\'Asia/Colombo\')'),
    'Member header and application connections must use the current balance and Colombo time.'
);

echo "Leaderboard/Life Bar verification passed (pure logic and source-level guards; no database used).\n";
