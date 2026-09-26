<?php
// dal/admin/SettingDAL.php

class SettingDAL {
    private $pdo;

    public function __construct($pdoConnection) {
        $this->pdo =$pdoConnection;
    }

    // Fetch all facilities ordered by name
    public function getAllFacilities() {
        $stmt =$this->pdo->query("SELECT * FROM `facility` ORDER BY Facility_Name ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get user password hash by user ID
    public function getPasswordHash($user_id) {
        $stmt =$this->pdo->prepare("SELECT Password FROM `user` WHERE User_ID = :uid");
        $stmt->execute([':uid' =>$user_id]);
        return $stmt->fetchColumn();
    }

    // Update user password
    public function updatePassword($user_id,$hashed_password) {
        $stmt =$this->pdo->prepare("UPDATE `user` SET Password = ? WHERE User_ID = ?");
        return $stmt->execute([$hashed_password,$user_id]);
    }

    // Check if email already exists
    public function checkEmailExists($email) {
        $stmt =$this->pdo->prepare("SELECT User_ID FROM `user` WHERE Email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch();
    }

    // Register instructor transaction
    public function registerInstructor($fname,$lname, $email,$hash, $facility_id) {$this->pdo->beginTransaction();
        
        $stmt1 =$this->pdo->prepare("INSERT INTO `user` (First_Name, Last_Name, Email, Password, Role) VALUES (?, ?, ?, ?, 'Instructor')");
        $stmt1->execute([$fname,$lname, $email,$hash]);
        $instructor_id =$this->pdo->lastInsertId();
        
        $stmt2 =$this->pdo->prepare("INSERT INTO `instructor` (Instructor_ID, Facility_ID) VALUES (?, ?)");
        $stmt2->execute([$instructor_id, $facility_id]);$this->pdo->commit();
        return true;
    }

    // Insert new facility
    public function insertFacility($name, $loc,$cap, $open,$close) {
        $stmt =$this->pdo->prepare("INSERT INTO `facility` (Facility_Name, Location, Capacity, Open_Time, Close_Time) VALUES (?, ?, ?, ?, ?)");
        return $stmt->execute([$name, $loc, (int)$cap, $open,$close]);
    }

    // Update existing facility
    public function updateFacility($fac_id,$name, $loc,$cap, $open,$close) {
        $stmt =$this->pdo->prepare("UPDATE `facility` SET Facility_Name=?, Location=?, Capacity=?, Open_Time=?, Close_Time=? WHERE Facility_ID=?");
        return $stmt->execute([$name,$loc, (int)$cap,$open, $close,$fac_id]);
    }
}