<?php
// models/member/SettingsModel.php

class SettingsModel {
    private $pdo;

    public function __construct($dbConnection) {
        $this->pdo =$dbConnection;
    }

    public function getMemberDetails($user_id) {
        $stmt =$this->pdo->prepare("
            SELECT 
                u.User_ID, 
                u.First_Name, 
                u.Last_Name, 
                u.Email, 
                s.ReC, 
                s.Fagistration_Number, 
                s.NIculty, 
                s.Gender,
                s.Profile_Image, 
                s.Status AS student_status
            FROM `user` u
            LEFT JOIN `university_student` s ON u.User_ID = s.User_ID
            WHERE u.User_ID = :uid
            LIMIT 1
        ");
        $stmt->execute([':uid' =>$user_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updateProfileImage($user_id,$filename) {
        $stmt =$this->pdo->prepare("UPDATE `university_student` SET Profile_Image = :img WHERE User_ID = :uid");
        return $stmt->execute([':img' => $filename, ':uid' =>$user_id]);
    }

    public function getProfileImage($user_id) {
        $stmt =$this->pdo->prepare("SELECT Profile_Image FROM `university_student` WHERE User_ID = :uid LIMIT 1");
        $stmt->execute([':uid' =>$user_id]);
        return $stmt->fetchColumn();
    }

    public function getUserPasswordHash($user_id) {
        $stmt =$this->pdo->prepare("SELECT Password FROM `user` WHERE User_ID = :uid LIMIT 1");
        $stmt->execute([':uid' =>$user_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updateUserPassword($user_id,$hashed_password) {
        $stmt =$this->pdo->prepare("UPDATE `user` SET Password = :pwd WHERE User_ID = :uid");
        return $stmt->execute([':pwd' => $hashed_password, ':uid' =>$user_id]);
    }
}
?>