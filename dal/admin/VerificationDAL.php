<?php
// dal/admin/VerificationDAL.php

class VerificationDAL {
    private $pdo;

    public function __construct($pdoConnection) {
        $this->pdo =$pdoConnection;
    }

    // Fetch all pending applicants with profile and ID details
    public function getPendingApplicants() {
        $stmt =$this->pdo->query("
            SELECT 
                u.User_ID AS user_id, 
                CONCAT(u.First_Name, ' ', u.Last_Name) AS full_name, 
                u.Email AS email, 
                s.Registration_Number AS reg_no, 
                s.Faculty AS faculty, 
                s.Emergency_Contact AS emergency_contact, 
                s.DOB as dob,
                s.Gender as gender,
                s.NIC as nic,
                COALESCE(s.Registration_Photo, s.Profile_Image) AS profile_image, 
                s.Student_ID_Front AS id_front_image, 
                s.Student_ID_Back AS id_back_image, 
                s.Created_At AS created_at 
            FROM `user` u
            INNER JOIN `university_student` s ON u.User_ID = s.User_ID 
            WHERE s.Status = 'pending' 
            ORDER BY s.Created_At ASC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Count pending review applicants
    public function getPendingCount() {
        return count($this->getPendingApplicants());
    }

    // Count approved active members
    public function getApprovedCount() {
        return $this->pdo->query("SELECT COUNT(*) FROM `university_student` WHERE `Status` = 'active'")->fetchColumn() ?: 0;
    }

    // Count total student members
    public function getTotalMembersCount() {
        return $this->pdo->query("SELECT COUNT(*) FROM `university_student`")->fetchColumn() ?: 0;
    }

    // Fetch target applicant details by user ID
    public function getApplicantById($target_user_id) {
        $stmt =$this->pdo->prepare("
            SELECT 
                u.User_ID, 
                CONCAT(u.First_Name, ' ', u.Last_Name) AS full_name, 
                u.Email, 
                s.Profile_Image AS profile_image, 
                s.Registration_Photo AS reg_photo,
                s.Student_ID_Front AS id_front_image, 
                s.Student_ID_Back AS id_back_image 
            FROM `user` u
            INNER JOIN `university_student` s ON u.User_ID = s.User_ID 
            WHERE u.User_ID = :id AND s.Status = 'pending' 
            LIMIT 1
        ");
        $stmt->execute([':id' =>$target_user_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Update student status to active (approve)
    public function updateStudentStatusActive($target_user_id) {
        $update_stmt =$this->pdo->prepare("UPDATE `university_student` SET `Status` = 'active' WHERE `User_ID` = :id");
        return $update_stmt->execute([':id' =>$target_user_id]);
    }

    // Delete rejected user from database
    public function deleteRejectedUser($target_user_id) {
        $delete_stmt =$this->pdo->prepare("DELETE FROM `user` WHERE `User_ID` = :id");
        return $delete_stmt->execute([':id' =>$target_user_id]);
    }
}