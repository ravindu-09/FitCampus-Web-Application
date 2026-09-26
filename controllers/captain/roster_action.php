<?php
// controllers/captain/roster_action.php
session_start();
require_once '../../includes/db_connection.php';
require_once '../../models/captain/RosterModel.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../views/auth/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$rosterModel = new RosterModel($pdo);

$selected_team_id = isset($_POST['team_id']) ? (int)$_POST['team_id'] : 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    // Verify if the logged-in user is a captain for this team
    if ($rosterModel->isCaptain($selected_team_id, $user_id)) {
        if ($action === 'add_member') {
            $reg_no = trim($_POST['reg_number'] ?? '');
            $captain_password = $_POST['captain_password'] ?? '';

            // 1. Verify Captain's password
            $hashed_password = $rosterModel->getUserPassword($user_id);

            if ($hashed_password && password_verify($captain_password, $hashed_password)) {
                // 2. Check if Student ID exists in the database
                $student = $rosterModel->getStudentByRegNumber($reg_no);

                if ($student) {
                    $new_member_id = $student['User_ID'];
                    
                    // 3. Check if already a member of the team
                    if (!$rosterModel->isTeamMember($selected_team_id, $new_member_id)) {
                        $rosterModel->addTeamMember($selected_team_id, $new_member_id);
                        $_SESSION['success_message'] = "Member added successfully!";
                    } else {
                        $_SESSION['error_message'] = "Student is already a member of this team.";
                    }
                } else {
                    $_SESSION['error_message'] = "Student with Registration Number '$reg_no' does not exist in the database.";
                }
            } else {
                $_SESSION['error_message'] = "Incorrect captain password. Verification failed.";
            }
        } elseif ($action === 'remove_member') {
            $remove_user_id = isset($_POST['remove_user_id']) ? (int)$_POST['remove_user_id'] : 0;
            // Prevent removing oneself
            if ($remove_user_id > 0 && $remove_user_id !== $user_id) {
                $rosterModel->removeTeamMember($selected_team_id, $remove_user_id);
                $_SESSION['success_message'] = "Member removed successfully!";
            } else {
                $_SESSION['error_message'] = "Invalid action or you cannot remove yourself.";
            }
        }
    } else {
        $_SESSION['error_message'] = "Unauthorized action for this team.";
    }
}

header("Location: ../../views/captain/roster.php?team_id=" . $selected_team_id);
exit();