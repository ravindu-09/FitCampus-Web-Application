<?php
// models/captain/RosterModel.php

class RosterModel {
    private $pdo;

    public function __construct($dbConnection) {
        $this->pdo =$dbConnection;
    }

    // Verify if the user is a captain for the team
    public function isCaptain($team_id,$user_id) {
        $stmt =$this->pdo->prepare("SELECT COUNT(*) FROM team_member WHERE Team_ID = ? AND User_ID = ? AND Role_In_Team = 'Captain'");
        $stmt->execute([$team_id,$user_id]);
        return $stmt->fetchColumn() > 0;
    }

    // Get user password hash
    public function getUserPassword($user_id) {
        $stmt =$this->pdo->prepare("SELECT Password FROM `user` WHERE User_ID = ?");
        $stmt->execute([$user_id]);
        return $stmt->fetchColumn();
    }

    // Find student by registration number
    public function getStudentByRegNumber($reg_no) {
        $stmt =$this->pdo->prepare("SELECT User_ID FROM university_student WHERE Registration_Number = ?");
        $stmt->execute([$reg_no]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Check if user is already in team
    public function isTeamMember($team_id,$user_id) {
        $stmt =$this->pdo->prepare("SELECT COUNT(*) FROM team_member WHERE Team_ID = ? AND User_ID = ?");
        $stmt->execute([$team_id,$user_id]);
        return $stmt->fetchColumn() > 0;
    }

    // Add member to team
    public function addTeamMember($team_id,$user_id) {
        $stmt =$this->pdo->prepare("INSERT INTO team_member (Team_ID, User_ID, Role_In_Team) VALUES (?, ?, 'Member')");
        return $stmt->execute([$team_id,$user_id]);
    }

    // Remove member from team
    public function removeTeamMember($team_id,$user_id) {
        $stmt =$this->pdo->prepare("DELETE FROM team_member WHERE Team_ID = ? AND User_ID = ?");
        return $stmt->execute([$team_id,$user_id]);
    }

    // Get all teams where user is captain
    public function getCaptainTeams($user_id) {
        $stmt =$this->pdo->prepare("
            SELECT t.Team_ID, t.Team_Name, t.Sport, t.Sport_Gender,
                   (SELECT COUNT(*) FROM team_member WHERE Team_ID = t.Team_ID) as Member_Count 
            FROM team t 
            JOIN team_member tm ON t.Team_ID = tm.Team_ID 
            WHERE tm.User_ID = ? AND tm.Role_In_Team = 'Captain'
        ");
        $stmt->execute([$user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get team info
    public function getTeamInfo($team_id) {
        $stmt =$this->pdo->prepare("SELECT Team_Name, Sport, Sport_Gender FROM team WHERE Team_ID = ?");
        $stmt->execute([$team_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Get team members list
    public function getTeamMembers($team_id) {
        $stmt =$this->pdo->prepare("
            SELECT u.User_ID, u.First_Name, u.Last_Name, u.Email, tm.Role_In_Team, 
                   us.Registration_Number, us.Faculty, us.Gender 
            FROM team_member tm
            JOIN `user` u ON tm.User_ID = u.User_ID
            LEFT JOIN university_student us ON u.User_ID = us.User_ID
            WHERE tm.Team_ID = ?
        ");
        $stmt->execute([$team_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>