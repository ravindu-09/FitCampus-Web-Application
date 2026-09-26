<?php
// dal/admin/UserDAL.php

class UserDAL {
    private $pdo;

    public function __construct($pdoConnection) {
        $this->pdo =$pdoConnection;
    }

    // Fetch all teams for dropdowns
    public function getAllTeams() {
        $stmt =$this->pdo->query("SELECT Team_ID, Team_Name FROM team ORDER BY Team_Name ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Fetch all users with meta and team roster details
    public function getAllUsersWithRoster() {
        $this->pdo->query("SET SESSION group_concat_max_len = 10000;");

        $stmt =$this->pdo->query("
            SELECT 
                u.User_ID, u.First_Name, u.Last_Name, u.Email, u.Role,
                s.Registration_Number, s.Faculty, s.Profile_Image, s.Emergency_Contact,
                s.Life_Percentage, s.Date_of_Final_Exam, s.DOB, s.NIC, s.Gender,
                (
                    SELECT GROUP_CONCAT(CONCAT(t.Team_ID, '::', t.Team_Name, '::', tm.Role_In_Team) SEPARATOR '||')
                    FROM team_member tm
                    JOIN team t ON tm.Team_ID = t.Team_ID
                    WHERE tm.User_ID = u.User_ID
                ) AS Team_Data
            FROM `user` u
            LEFT JOIN `university_student` s ON u.User_ID = s.User_ID 
            ORDER BY u.User_ID DESC
        ");
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Update student life percentage and exam date meta
    public function updateStudentMeta($user_id, $life_pct,$exam_date) {
        $stmt =$this->pdo->prepare("UPDATE university_student SET Life_Percentage = ?, Date_of_Final_Exam = ? WHERE User_ID = ?");
        return $stmt->execute([$life_pct, $exam_date,$user_id]);
    }

    // Promote user to team captain
    public function promoteCaptain($user_id,$team_id) {
        $stmt =$this->pdo->prepare("
            INSERT INTO team_member (Team_ID, User_ID, Role_In_Team) 
            VALUES (?, ?, 'Captain') 
            ON DUPLICATE KEY UPDATE Role_In_Team = 'Captain'
        ");
        return $stmt->execute([$team_id,$user_id]);
    }

    // Demote captain back to regular member
    public function demoteCaptain($user_id,$team_id) {
        $stmt =$this->pdo->prepare("UPDATE team_member SET Role_In_Team = 'Member' WHERE User_ID = ? AND Team_ID = ?");
        return $stmt->execute([$user_id,$team_id]);
    }
}