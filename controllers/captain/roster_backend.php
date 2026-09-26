<?php
// views/captain/roster_backend.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

$user_id =$_SESSION['user_id'];
require_once '../../includes/db_connection.php';

$error_message = '';$success_message = '';

// Get team_id from GET or POST request
$selected_team_id = isset($_GET['team_id']) ? (int)$_GET['team_id'] : (isset($_POST['team_id']) ? (int)$_POST['team_id'] : 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action =$_POST['action'] ?? '';
    
    // Verify if the logged-in user is a captain for this team
    $stmtCheckCap =$pdo->prepare("SELECT COUNT(*) FROM team_member WHERE Team_ID = ? AND User_ID = ? AND Role_In_Team = 'Captain'");
    $stmtCheckCap->execute([$selected_team_id,$user_id]);
    $is_valid_captain =$stmtCheckCap->fetchColumn() > 0;

    if ($is_valid_captain) {
        if ($action === 'add_member') {
            $reg_no = trim($_POST['reg_number'] ?? '');
            $captain_password =$_POST['captain_password'] ?? '';

            // 1. Verify Captain's password
            $stmtUser =$pdo->prepare("SELECT Password FROM `user` WHERE User_ID = ?");
            $stmtUser->execute([$user_id]);
            $hashed_password =$stmtUser->fetchColumn();

            if ($hashed_password && password_verify($captain_password,$hashed_password)) {
                // 2. Check if Student ID exists in the database (university_student)
                $stmtStudent =$pdo->prepare("SELECT User_ID FROM university_student WHERE Registration_Number = ?");
                $stmtStudent->execute([$reg_no]);
                $student =$stmtStudent->fetch(PDO::FETCH_ASSOC);

                if ($student) {
                    $new_member_id =$student['User_ID'];
                    
                    // 3. Check if already a member of the team
                    $stmtExists =$pdo->prepare("SELECT COUNT(*) FROM team_member WHERE Team_ID = ? AND User_ID = ?");
                    $stmtExists->execute([$selected_team_id,$new_member_id]);
                    if ($stmtExists->fetchColumn() == 0) {
                        $stmtAdd =$pdo->prepare("INSERT INTO team_member (Team_ID, User_ID, Role_In_Team) VALUES (?, ?, 'Member')");
                        $stmtAdd->execute([$selected_team_id, $new_member_id]);$success_message = "Member added successfully!";
                    } else {
                        $error_message = "Student is already a member of this team.";
                    }
                } else {
                    $error_message = "Student with Registration Number '$reg_no' does not exist in the database.";
                }
            } else {
                $error_message = "Incorrect captain password. Verification failed.";
            }
        } elseif ($action === 'remove_member') {$remove_user_id = isset($_POST['remove_user_id']) ? (int)$_POST['remove_user_id'] : 0;
            // Prevent removing oneself
            if ($remove_user_id > 0 && $remove_user_id !==$user_id) {
                $stmtDel =$pdo->prepare("DELETE FROM team_member WHERE Team_ID = ? AND User_ID = ?");
                $stmtDel->execute([$selected_team_id, $remove_user_id]);$success_message = "Member removed successfully!";
            } else {
                $error_message = "Invalid action or you cannot remove yourself.";
            }
        }
    } else {
        $error_message = "Unauthorized action for this team.";
    }
}

// GET CAPTAIN'S TEAMS
$captain_teams = [];
try {
    $stmt =$pdo->prepare("
        SELECT t.Team_ID, t.Team_Name, t.Sport, t.Sport_Gender,
               (SELECT COUNT(*) FROM team_member WHERE Team_ID = t.Team_ID) as Member_Count 
        FROM team t 
        JOIN team_member tm ON t.Team_ID = tm.Team_ID 
        WHERE tm.User_ID = ? AND tm.Role_In_Team = 'Captain'
    ");
    $stmt->execute([$user_id]);
    $captain_teams =$stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {$captain_teams = [];
}

// Default to the first team if no team is selected
if ($selected_team_id == 0 && !empty($captain_teams)) {
    $selected_team_id =$captain_teams[0]['Team_ID'];
}

// Fetch details and members for the selected team
$team_members = [];$selected_team_name = "";
$selected_team_sport = "";
$selected_team_gender = "";

if ($selected_team_id > 0) {
    try {
        $stmtTeam =$pdo->prepare("SELECT Team_Name, Sport, Sport_Gender FROM team WHERE Team_ID = ?");
        $stmtTeam->execute([$selected_team_id]);
        $team_info =$stmtTeam->fetch(PDO::FETCH_ASSOC);
        
        if ($team_info) {
            $selected_team_name =$team_info['Team_Name'];
            $selected_team_sport =$team_info['Sport'];
            $selected_team_gender =$team_info['Sport_Gender'];
        }

        $stmtMembers =$pdo->prepare("
            SELECT u.User_ID, u.First_Name, u.Last_Name, u.Email, tm.Role_In_Team, 
                   us.Registration_Number, us.Faculty, us.Gender 
            FROM team_member tm
            JOIN `user` u ON tm.User_ID = u.User_ID
            LEFT JOIN university_student us ON u.User_ID = us.User_ID
            WHERE tm.Team_ID = ?
        ");
        $stmtMembers->execute([$selected_team_id]);
        $team_members =$stmtMembers->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {$team_members = [];
    }
}
?>