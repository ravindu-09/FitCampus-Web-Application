<?php
// views/admin/users.php
require_once '../../includes/db_connection.php';
require_once '../../includes/headers/header_admin.php';

try {
    $stmt = $pdo->query("
        SELECT 
            u.User_ID AS user_id, 
            CONCAT(u.First_Name, ' ', u.Last_Name) AS full_name, 
            u.Email AS email, 
            u.Role AS role, 
            s.Faculty AS faculty,
            (SELECT COUNT(*) FROM `TEAM_MEMBER` tm WHERE tm.User_ID = u.User_ID AND tm.Role_In_Team = 'Captain') AS is_captain
        FROM `USER` u
        LEFT JOIN `UNIVERSITY_STUDENT` s ON u.User_ID = s.User_ID 
        ORDER BY u.User_ID DESC
    ");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (\PDOException $e) {
    error_log("Users Roster Error: " . $e->getMessage());
    $users = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User &amp; Roster Management - FitCampus</title>
    
    <link rel="stylesheet" href="../../assets/css/base/main.css">
    <link rel="stylesheet" href="../../assets/css/base/components.css">
    <link rel="stylesheet" href="../../assets/css/base/glassmorphism.css">
    <link rel="stylesheet" href="../../assets/css/roles/admin.css">
</head>
<body class="admin-layout-body">

    <div class="admin-viewport-wrapper">
        <?php include_once '../../includes/sidebars/sidebar_admin.php'; ?>

        <main class="admin-main-canvas">
            <div class="section-header">
                <div>
                    <h2 class="section-title">Institutional User Directory</h2>
                    <p class="section-subtitle">Manage memberships, assign instructor status, and designate team captains</p>
                </div>
            </div>

            <div class="glass-card p-6">
                <div class="admin-table-container">
                    <table class="admin-data-table">
                        <thead>
                            <tr>
                                <th>Member Identity</th>
                                <th>Faculty</th>
                                <th>System Role</th>
                                <th class="text-right">Privileges</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $row): ?>
                                <tr>
                                    <td>
                                        <div class="user-cell-name"><?php echo htmlspecialchars($row['full_name'], ENT_QUOTES, 'UTF-8'); ?></div>
                                        <div class="user-cell-email"><?php echo htmlspecialchars($row['email'], ENT_QUOTES, 'UTF-8'); ?></div>
                                    </td>
                                    <td><?php echo htmlspecialchars($row['faculty'] ?? 'N/A', ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td>
                                        <span class="badge-tag"><?php echo strtoupper($row['role']); ?></span>
                                        <?php if ((int)$row['is_captain'] > 0): ?>
                                            <span class="badge-status badge-captain">CAPTAIN</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-right">
                                        <button class="btn btn-glass btn-sm">Edit Role</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <?php 
    include_once '../../includes/footers/footer_common.php'; 
    ?>