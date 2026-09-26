<?php
// backend/common/get_notifications.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../../includes/db_connection.php';

$user_id = $_SESSION['user_id'] ?? 0;

$announcements = [];
$personal_notifications = [];

try {
    // 1. Fetch Common Announcements
    $stmt_ann = $pdo->query("
        SELECT Title, Description, Publish_Date 
        FROM announcement 
        ORDER BY Publish_Date DESC 
        LIMIT 20
    ");
    $announcements = $stmt_ann->fetchAll(PDO::FETCH_ASSOC);

    // 2. Fetch Personal & Team Notifications (Fixed PDO multiple named parameters issue)
    $stmt_notif = $pdo->prepare("
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
    $personal_notifications = $stmt_notif->fetchAll(PDO::FETCH_ASSOC);

} catch (\PDOException $e) {
    error_log("Notifications Fetch Error: " . $e->getMessage());
}
?>