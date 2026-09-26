<?php
// models/captain/PlannerModel.php

class PlannerModel {
    private $pdo;

    public function __construct($dbConnection) {
        $this->pdo = $dbConnection;
    }

    // Placeholder method to fetch team workouts in future implementations
    public function getTeamWorkoutsByUser($user_id) {
        return [];
    }
}
?>