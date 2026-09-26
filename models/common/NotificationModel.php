<?php
// models/common/NotificationModel.php

class NotificationModel {
    private $pdo;

    public function __construct($dbConnection) {
        $this->pdo = $dbConnection;
    }

    // Fetch Common Announcements
    public function getAnnouncements() {
        $stmt_ann = $this->pdo->query("
            SELECT Title, Description, Publish_Date 
            FROM announcement 
            ORDER BY Publish_Date DESC 
            LIMIT 20
        ");
        return $stmt_ann->fetchAll(PDO::FETCH_ASSOC);
    }

    // Fetch Personal & Team Notifications
    public function getPersonalNotifications($user_id) {
        $stmt_notif = $this->pdo->prepare("
            SELECT Notification_ID, Title, Message, Created_At 
            FROM notification 
            WHERE User_ID = :uid1 
               OR Team_ID IN (SELECT Team_ID FROM team_member WHERE User_ID = :uid2)
            ORDER BY Created_At DESC 
            LIMIT 30
        ");
        $stmt_notif->execute([
            ':uid1' => $user_id,
            ':uid2' => $user_id
        ]);
        return $stmt_notif->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>