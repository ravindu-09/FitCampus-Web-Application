<?php
// models/common/SupportRequestModel.php

class SupportRequestModel {
    private $pdo;

    public function __construct($dbConnection) {
        $this->pdo = $dbConnection;
    }

    public function getUserProfile($userId) {
        $stmt = $this->pdo->prepare("
            SELECT u.User_ID, u.First_Name, u.Last_Name, u.Email, u.Role,
                   s.Registration_Number, s.Faculty
            FROM `user` u
            LEFT JOIN `university_student` s ON s.User_ID = u.User_ID
            WHERE u.User_ID = :user_id
            LIMIT 1
        ");
        $stmt->execute([':user_id' => $userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function createRequest($userId, $category, $description, $attachmentPath, $requestedEndDate) {
        $stmt = $this->pdo->prepare("
            INSERT INTO `feedback`
                (`User_ID`, `Category`, `Description`, `Attachment_Path`, `Requested_End_Date`)
            VALUES
                (:user_id, :category, :description, :attachment_path, :requested_end_date)
        ");
        $stmt->execute([
            ':user_id' => $userId,
            ':category' => $category,
            ':description' => $description,
            ':attachment_path' => $attachmentPath,
            ':requested_end_date' => $requestedEndDate
        ]);
        return (int)$this->pdo->lastInsertId();
    }

    public function getRequestsForUser($userId) {
        $stmt = $this->pdo->prepare("
            SELECT `Feedback_ID`, `Category`, `Description`, `Attachment_Path`,
                   `Requested_End_Date`, `Submitted_Date`, `Status`, `Response`, `Response_Date`
            FROM `feedback`
            WHERE `User_ID` = :user_id
            ORDER BY `Submitted_Date` DESC, `Feedback_ID` DESC
        ");
        $stmt->execute([':user_id' => $userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAllRequests() {
        return $this->pdo->query("
            SELECT f.`Feedback_ID`, f.`User_ID`, f.`Category`, f.`Description`,
                   f.`Attachment_Path`, f.`Requested_End_Date`, f.`Submitted_Date`,
                   f.`Status`, f.`Response`, f.`Response_Date`,
                   CONCAT(u.`First_Name`, ' ', u.`Last_Name`) AS `Full_Name`,
                   u.`Email`, s.`Registration_Number`, s.`Faculty`
            FROM `feedback` f
            INNER JOIN `user` u ON u.`User_ID` = f.`User_ID`
            LEFT JOIN `university_student` s ON s.`User_ID` = f.`User_ID`
            ORDER BY FIELD(f.`Status`, 'Pending', 'In Progress', 'Resolved', 'Rejected'),
                     f.`Submitted_Date` ASC, f.`Feedback_ID` ASC
        ")->fetchAll(PDO::FETCH_ASSOC);
    }

    public function respondToRequest($requestId, $adminId, $status, $response) {
        $stmt = $this->pdo->prepare("
            UPDATE `feedback`
            SET `Status` = :status,
                `Response` = :response,
                `Responded_Admin` = :admin_id,
                `Response_Date` = CURRENT_TIMESTAMP
            WHERE `Feedback_ID` = :request_id
        ");
        $stmt->execute([
            ':status' => $status,
            ':response' => $response,
            ':admin_id' => $adminId,
            ':request_id' => $requestId
        ]);
        return $stmt->rowCount() > 0;
    }

    public function getRequestAttachment($requestId) {
        $stmt = $this->pdo->prepare("
            SELECT `User_ID`, `Attachment_Path`
            FROM `feedback`
            WHERE `Feedback_ID` = :request_id
            LIMIT 1
        ");
        $stmt->execute([':request_id' => $requestId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>