<?php
// models/member/TeamModel.php

class TeamModel {
    private $pdo;

    public function __construct($dbConnection) {
        $this->pdo = $dbConnection;
    }

    public function getTeamsByUser($user_id) {
        $stmt = $this->pdo->prepare("
            SELECT t.Team_ID, t.Team_Name, t.Sport, t.Sport_Gender
            FROM team t
            INNER JOIN team_member tm ON tm.Team_ID = t.Team_ID
            WHERE tm.User_ID = ?
            ORDER BY t.Team_Name
        ");
        $stmt->execute([$user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>