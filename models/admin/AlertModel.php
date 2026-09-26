<?php
// models/admin/AlertModel.php

class AlertModel {
    private $pdo;

    public function __construct($dbConnection) {
        $this->pdo = $dbConnection;
    }

    // System active notices saha alerts fetch karaganeema sadaha
    public function getSystemAlerts() {
        // Mheta adala database queries danna puluwan
        return [];
    }
}
?>