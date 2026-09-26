<?php
// bll/admin/SettingBLL.php
require_once '../../dal/admin/SettingDAL.php';

class SettingBLL {
    private $dal;
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
        $this->dal = new SettingDAL($pdo);
    }

    // Get facilities list for view rendering
    public function getFacilities() {
        try {
            return $this->dal->getAllFacilities();
        } catch (PDOException $e) {
            return [];
        }
    }

    // Verify admin password
    private function verifyAdminPassword($admin_id, $input_password) {
        $hash = $this->dal->getPasswordHash($admin_id);
        return $hash && password_verify($input_password, $hash);
    }

    // Change admin password logic
    public function changePassword($admin_id, $current_pwd, $new_pwd, $confirm_pwd) {
        if (empty($current_pwd) || empty($new_pwd) || empty($confirm_pwd)) {
            throw new Exception("All password fields are required.");
        }
        if ($new_pwd !== $confirm_pwd) {
            throw new Exception("New passwords do not match.");
        }
        if (strlen($new_pwd) < 8) {
            throw new Exception("New password must be at least 8 characters.");
        }
        if (!$this->verifyAdminPassword($admin_id, $current_pwd)) {
            throw new Exception("Incorrect current password.");
        }

        $hashed = password_hash($new_pwd, PASSWORD_BCRYPT);
        $this->dal->updatePassword($admin_id, $hashed);
        return "Admin password updated successfully.";
    }

    // Register new instructor logic
    public function registerInstructor($admin_id, $data) {
        $fname = trim($data['first_name'] ?? '');
        $lname = trim($data['last_name'] ?? '');
        $email = trim($data['email'] ?? '');
        $pwd = $data['default_password'] ?? '';
        $facility_id = !empty($data['facility_id']) ? $data['facility_id'] : null;
        $auth_password = $data['admin_auth_password'] ?? '';

        if (empty($fname) || empty($lname) || empty($email) || empty($pwd) || empty($facility_id) || empty($auth_password)) {
            throw new Exception("All instructor registration fields and admin authorization password are required.");
        }

        if (!$this->verifyAdminPassword($admin_id, $auth_password)) {
            throw new Exception("Authentication failed. Invalid admin password.");
        }

        if ($this->dal->checkEmailExists($email)) {
            throw new Exception("Email already registered.");
        }

        $hash = password_hash($pwd, PASSWORD_DEFAULT);
        $this->dal->registerInstructor($fname, $lname, $email, $hash, $facility_id);
        return "Instructor registered and assigned successfully.";
    }

    // Insert new facility logic
    public function insertFacility($admin_id, $data) {
        $name = trim($data['facility_name'] ?? '');
        $loc = trim($data['location'] ?? '');
        $cap = $data['capacity'] ?? '';
        $open = $data['open_time'] ?? '';
        $close = $data['close_time'] ?? '';
        $auth_password = $data['admin_auth_password'] ?? '';

        if (empty($name) || empty($loc) || $cap === '' || empty($open) || empty($close) || empty($auth_password)) {
            throw new Exception("All facility details and admin authorization password are required.");
        }

        if (!$this->verifyAdminPassword($admin_id, $auth_password)) {
            throw new Exception("Authentication failed. Invalid admin password.");
        }

        $this->dal->insertFacility($name, $loc, $cap, $open, $close);
        return "New facility added successfully.";
    }

    // Update existing facility logic
    public function updateFacility($admin_id, $data) {
        $fac_id = $data['facility_id'] ?? '';
        $name = trim($data['facility_name'] ?? '');
        $loc = trim($data['location'] ?? '');
        $cap = $data['capacity'] ?? '';
        $open = $data['open_time'] ?? '';
        $close = $data['close_time'] ?? '';
        $auth_password = $data['admin_auth_password'] ?? '';

        if (empty($fac_id) || empty($name) || empty($loc) || $cap === '' || empty($open) || empty($close) || empty($auth_password)) {
            throw new Exception("All facility update fields and admin authorization password are required.");
        }

        if (!$this->verifyAdminPassword($admin_id, $auth_password)) {
            throw new Exception("Authentication failed. Invalid admin password.");
        }

        $this->dal->updateFacility($fac_id, $name, $loc, $cap, $open, $close);
        return "Facility details updated successfully.";
    }
}