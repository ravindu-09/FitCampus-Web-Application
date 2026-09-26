<?php
// models/member/CalorieModel.php

class CalorieModel {
    private $pdo;

    public function __construct($dbConnection) {
        $this->pdo =$dbConnection;
    }

    public function getDailySummary($user_id,$date) {
        $stmt =$this->pdo->prepare("SELECT Calories_In, Calories_Out FROM `calorie_log` WHERE User_ID = :uid AND Date = :dt LIMIT 1");
        $stmt->execute([':uid' => $user_id, ':dt' =>$date]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: ['Calories_In' => 0, 'Calories_Out' => 0];
    }

    public function getDailyDetails($user_id,$date) {
        $stmt =$this->pdo->prepare("SELECT * FROM `calorie_details` WHERE User_ID = :uid AND Date = :dt ORDER BY Detail_ID DESC");
        $stmt->execute([':uid' => $user_id, ':dt' =>$date]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function insertIntakeItem($user_id, $date,$item, $category,$portion, $calories,$carbs, $protein,$fat) {
        $ins =$this->pdo->prepare("INSERT INTO `calorie_details` (User_ID, Date, Type, Item_Name, Category, Portion_Or_Duration, Calories, Carbs, Protein, Fat) VALUES (:uid, :dt, 'intake', :item, :cat, :portion, :cal, :carbs, :protein, :fat)");
        $ins->execute([
            ':uid' => $user_id,
            ':dt' => $date,
            ':item' => $item,
            ':cat' => $category,
            ':portion' => $portion . 'g',
            ':cal' => $calories,
            ':carbs' => $carbs,
            ':protein' => $protein,
            ':fat' => $fat
        ]);

        $upd =$this->pdo->prepare("INSERT INTO `calorie_log` (User_ID, Date, Calories_In, Calories_Out) VALUES (:uid, :dt, :cal, 0) ON DUPLICATE KEY UPDATE Calories_In = Calories_In + :cal_up");
        $upd->execute([':uid' => $user_id, ':dt' =>$date, ':cal' => $calories, ':cal_up' =>$calories]);
    }

    public function insertBurnedItem($user_id, $date,$activity, $duration,$calories) {
        $ins =$this->pdo->prepare("INSERT INTO `calorie_details` (User_ID, Date, Type, Item_Name, Category, Portion_Or_Duration, Calories) VALUES (:uid, :dt, 'burned', :item, 'Workout', :duration, :cal)");
        $ins->execute([
            ':uid' => $user_id,
            ':dt' => $date,
            ':item' => $activity,
            ':duration' => $duration . ' min',
            ':cal' => $calories
        ]);

        $upd =$this->pdo->prepare("INSERT INTO `calorie_log` (User_ID, Date, Calories_In, Calories_Out) VALUES (:uid, :dt, 0, :cal) ON DUPLICATE KEY UPDATE Calories_Out = Calories_Out + :cal_up");
        $upd->execute([':uid' => $user_id, ':dt' =>$date, ':cal' => $calories, ':cal_up' =>$calories]);
    }

    public function resetDateLogs($user_id,$date) {
        $del =$this->pdo->prepare("DELETE FROM `calorie_details` WHERE User_ID = :uid AND Date = :dt");
        $del->execute([':uid' => $user_id, ':dt' =>$date]);

        $upd =$this->pdo->prepare("UPDATE `calorie_log` SET Calories_In = 0, Calories_Out = 0 WHERE User_ID = :uid AND Date = :dt");
        $upd->execute([':uid' => $user_id, ':dt' =>$date]);
    }
}
?>