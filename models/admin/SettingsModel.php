<?php
// models/admin/SettingsModel.php

class SettingsModel {
    private $pdo;

    public function __construct($dbConnection) {
        $this->pdo =$dbConnection;
    }

    // Fetch all facilities for the View
    public function getAllFacilities() {
        $stmt =$this->pdo->query("SELECT * FROM `facility` ORDER BY Facility_Name ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get admin password hash for verification
    public function getAdminPasswordHash($admin_id) {
        $stmt =$this->pdo->prepare("SELECT Password FROM `user` WHERE User_ID = :uid");
        $stmt->execute([':uid' =>$admin_id]);
        return $stmt->fetchColumn();
    }

    // Update admin password
    public function updateAdminPassword($admin_id,$hashedPassword) {
        $stmt =$this->pdo->prepare("UPDATE `user` SET Password = ? WHERE User_ID = ?");
        return $stmt->execute([$hashedPassword,$admin_id]);
    }

    // Check if email already exists
    public function checkEmailExists($email) {
        $check =$this->pdo->prepare("SELECT User_ID FROM `user` WHERE Email = ?");
        $check->execute([$email]);
        return $check->fetch();
    }

    // Register a new instructor using a transaction
    public function registerInstructor($fname, $lname,$email, $hashedPwd,$facility_id) {
        try {
            $this->pdo->beginTransaction();
            
            $stmt1 =$this->pdo->prepare("INSERT INTO `user` (First_Name, Last_Name, Email, Password, Role) VALUES (?, ?, ?, ?, 'Instructor')");
            $stmt1->execute([$fname,$lname, $email,$hashedPwd]);
            $instructor_id =$this->pdo->lastInsertId();
            
            $stmt2 =$this->pdo->prepare("INSERT INTO `instructor` (Instructor_ID, Facility_ID) VALUES (?, ?)");
            $stmt2->execute([$instructor_id, $facility_id]);$this->pdo->commit();
            return true;
        } catch (Exception $e) {$this->pdo->rollBack();
            throw $e;
        }
    }

    // Insert a new facility
    public function insertFacility($name, $loc,$cap, $open,$close) {
        $stmt =$this->pdo->prepare("INSERT INTO `facility` (Facility_Name, Location, Capacity, Open_Time, Close_Time) VALUES (?, ?, ?, ?, ?)");
        return $stmt->execute([$name, $loc, (int)$cap, $open,$close]);
    }

    // Update an existing facility
    public function updateFacility($fac_id,$name, $loc,$cap, $open,$close) {
        $stmt =$this->pdo->prepare("UPDATE `facility` SET Facility_Name=?, Location=?, Capacity=?, Open_Time=?, Close_Time=? WHERE Facility_ID=?");
        return $stmt->execute([$name,$loc, (int)$cap,$open, $close,$fac_id]);
    }
}
?>