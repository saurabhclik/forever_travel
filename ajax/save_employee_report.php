<?php
include "../config.php";

$user_id = $_POST['user_id'] ?? 0;

// fields
$addon_sales = $_POST['add_on_sale'] ?? 0;
$review_quality = $_POST['review_quality'] ?? 0;
$task_accuracy = $_POST['task_accuracy'] ?? 0;
$attendance_missed = $_POST['attendance_days_missed'] ?? 0;
$training_missed = $_POST['trainings_missed'] ?? 0;
$knowledge_applied = $_POST['knowledge_applied'] ?? 0;
$process_accuracy = $_POST['process_accuracy'] ?? 0;
$collaboration = $_POST['collaboration'] ?? 0;
$ownership = $_POST['ownership'] ?? 0;
$values = $_POST['values'] ?? 0;

/* =========================
   UPDATE users table
========================= */

$update = $mysqli->prepare("
    UPDATE users SET 
        add_on_sale=?,
        review_quality=?,
        task_accuracy=?,
        attendance_days_missed=?,
        trainings_missed=?,
        knowledge_applied=?,
        process_accuracy=?,
        collaboration=?,
        ownership=?,
        values_data=?
    WHERE id=?
");

$update->bind_param(
    "iiiiiiiiiii",
    $addon_sales,
    $review_quality,
    $task_accuracy,
    $attendance_missed,
    $training_missed,
    $knowledge_applied,
    $process_accuracy,
    $collaboration,
    $ownership,
    $values,
    $user_id
);

$update->execute();

/* =========================
   INSERT into history table
========================= */

$insert = $mysqli->prepare("
    INSERT INTO employe_report 
    (user_id, add_on_sale, review_quality, task_accuracy, attendance_days_missed, trainings_missed, knowledge_applied, process_accuracy, collaboration, ownership, values_data, created_at)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
");

$insert->bind_param(
    "iiiiiiiiiii",
    $user_id,
    $addon_sales,
    $review_quality,
    $task_accuracy,
    $attendance_missed,
    $training_missed,
    $knowledge_applied,
    $process_accuracy,
    $collaboration,
    $ownership,
    $values
);

$insert->execute();

echo json_encode(["status" => "success"]);