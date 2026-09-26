<?php
// models/auth/RegisterModel.php

class RegisterModel {
    private $pdo;

    public function __construct($dbConnection) {
        $this->pdo =$dbConnection;
    }

    // Check for existing user by Email or Registration Number
    public function checkDuplicateUser($email,$regNo) {
        $stmt =$this->pdo->prepare("
            SELECT u.User_ID 
            FROM `user` u 
            LEFT JOIN `university_student` s ON u.User_ID = s.User_ID 
            WHERE u.Email = :email OR s.Registration_Number = :reg_no 
            LIMIT 1
        ");
        $stmt->execute([':email' => $email, ':reg_no' =>$regNo]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Insert new student into database using transactions
    public function registerNewStudent($firstName, $lastName,$email, $hashedPwd,$studentData) {
        try {
            $this->pdo->beginTransaction();

            // 1. Insert into user table
            $stmt1 =$this->pdo->prepare("
                INSERT INTO `user` (`First_Name`, `Last_Name`, `Email`, `Password`, `Role`) 
                VALUES (:fname, :lname, :email, :pwd, 'Student')
            ");
            $stmt1->execute([
                ':fname' => $firstName,
                ':lname' => $lastName,
                ':email' => $email,
                ':pwd'   => $hashedPwd
            ]);

            $userId =$this->pdo->lastInsertId();

            // 2. Insert into university_student table
            $stmt2 =$this->pdo->prepare("
                INSERT INTO `university_student` 
                (`User_ID`, `Registration_Number`, `NIC`, `DOB`, `Faculty`, `Gender`, `Emergency_Contact`, `Profile_Image`, `Registration_Photo`, `Life_Percentage`, `Student_ID_Front`, `Student_ID_Back`, `Status`) 
                VALUES 
                (:id, :reg_no, :nic, :dob, :faculty, :gender, :emergency, :avatar, :reg_photo, 100, :id_f, :id_b, 'pending')
            ");
            
            $stmt2->execute([
                ':id'        => $userId,
                ':reg_no'    => $studentData['reg_no'],
                ':nic'       => $studentData['nic'],
                ':dob'       => $studentData['dob'],
                ':faculty'   => $studentData['faculty'],
                ':gender'    => $studentData['gender'],
                ':emergency' => $studentData['emergency_contact'],
                ':avatar'    => $studentData['avatar'],
                ':reg_photo' => $studentData['reg_photo'],
                ':id_f'      => $studentData['id_front'],
                ':id_b'      => $studentData['id_back']
            ]);

            $this->pdo->commit();
            return true;

        } catch (\PDOException $e) {
            if ($this->pdo->inTransaction()) {$this->pdo->rollBack();
            }
            error_log("Registration DB Error: " . $e->getMessage());
            return false;
        }
    }
}
?>