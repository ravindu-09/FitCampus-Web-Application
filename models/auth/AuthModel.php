<?php
// models/auth/AuthModel.php

class AuthModel {
    private $pdo;

    public function __construct($dbConnection) {
        $this->pdo =$dbConnection;
    }

    public function getUserByIdentifier($identifier) {
        $stmt =$this->pdo->prepare("
            SELECT 
                u.User_ID, 
                u.First_Name,
                u.Last_Name,
                CONCAT(u.First_Name, ' ', u.Last_Name) AS full_name, 
                u.Email, 
                u.Password, 
                u.Role, 
                s.Registration_Number,
                s.Profile_Image,
                s.Life_Percentage,
                s.Status AS student_status,
                (SELECT COUNT(*) FROM `team_member` tm WHERE tm.User_ID = u.User_ID AND tm.Role_In_Team = 'Captain') AS is_captain
            FROM `user` u
            LEFT JOIN `university_student` s ON u.User_ID = s.User_ID
            WHERE u.Email = :id_email OR s.Registration_Number = :id_reg
            LIMIT 1
        ");
        $stmt->execute([
            ':id_email' => $identifier,
            ':id_reg'   => $identifier
        ]);
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>