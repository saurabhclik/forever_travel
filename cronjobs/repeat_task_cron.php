<?php
include("../config.php");

$today = date('Y-m-d');


$result = $mysqli->query("SELECT * FROM task WHERE next_due_date = '$today' AND repeat_remaining > 0");

while($task = $result->fetch_assoc()) {

    $stmt = $mysqli->prepare("INSERT INTO task (title, due_date, user_id) VALUES (?, ?, ?)");
    $stmt->bind_param("ssi", $task['title'], $task['next_due_date'], $task['user_id']);
    $stmt->execute();
    $stmt->close();

    // अगली तारीख निकालिए
    $next_due_date = $task['next_due_date'];
    if ($task['repeat_interval'] == 'daily') {
        $next_due_date = date('Y-m-d', strtotime("+1 day", strtotime($next_due_date)));
    } elseif ($task['repeat_interval'] == 'weekly') {
        $next_due_date = date('Y-m-d', strtotime("+7 day", strtotime($next_due_date)));
    } elseif ($task['repeat_interval'] == 'monthly') {
        $next_due_date = date('Y-m-d', strtotime("+1 month", strtotime($next_due_date)));
    }

    // repeat_remaining कम करिए और next_due_date अपडेट करिए
    $stmt = $mysqli->prepare("UPDATE task SET repeat_remaining = repeat_remaining - 1, next_due_date = ? WHERE id = ?");
    $stmt->bind_param("si", $next_due_date, $task['id']);
    $stmt->execute();
    $stmt->close();
}
?>
