<?php
// controllers/admin/users_page_controller.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Create CSRF Token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// 1. Connect to Database & Model HERE (Application Tier)
require_once '../../includes/db_connection.php';
require_once '../../models/admin/UserModel.php'; 

$userModel = new UserModel($pdo);

// 2. Fetch Data using Model
try {
    $teamsList = $userModel->getAllTeams();
} catch (\PDOException $e) {
    $teamsList = []; 
}

try {
    $users = $userModel->getAllUsersWithRosters();

    // Parse Team Data for JSON output
    foreach ($users as &$row) {
        $parsed_teams = [];
        $team_ids = [];
        $is_captain = false;

        if (!empty($row['Team_Data'])) {
            $t_list = explode('||', $row['Team_Data']);
            foreach ($t_list as $t) {
                $parts = explode('::', $t);
                if (count($parts) === 3) {
                    $parsed_teams[] = ['id' => $parts[0], 'name' => $parts[1], 'role' => $parts[2]];
                    $team_ids[] = $parts[0];
                    if ($parts[2] === 'Captain') $is_captain = true;
                }
            }
        }
        
        $row['parsed_teams'] = $parsed_teams;
        $row['team_ids'] = implode(',', $team_ids); 
        $row['is_captain'] = $is_captain;
    
        unset($row);
    }

} catch (\PDOException $e) {
    error_log("Users Roster Error: " . $e->getMessage());
    $users = [];
}
?>