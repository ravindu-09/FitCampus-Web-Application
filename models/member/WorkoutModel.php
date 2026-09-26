<?php
// models/member/WorkoutModel.php

class WorkoutModel {
    private $pdo;

    public function __construct($dbConnection) {
        $this->pdo = $dbConnection;
    }

    // Placeholder method to fetch user workout routines in future implementations
    public function getWorkoutsByUser($user_id) {
        return [];
    }
}
?>