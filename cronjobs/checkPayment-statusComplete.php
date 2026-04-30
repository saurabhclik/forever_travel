<?php
include("../config.php");
$q = $mysqli->query("SELECT id, sale_amount, from_date FROM query_mst WHERE status = 'Converted'");

while ($row = $q->fetch_assoc()) {
    $query_id = $row['id'];
    $sale_amount = $row['sale_amount'];
    $from_date = $row['from_date'];

   
    $stmt = $mysqli->prepare("SELECT COALESCE(SUM(amount), 0) FROM payment WHERE query_id = ?");
    $stmt->bind_param("i", $query_id);
    $stmt->execute();
    $stmt->bind_result($total_paid);
    $stmt->fetch();
    $stmt->close();

    if ($total_paid == $sale_amount && strtotime($from_date) <= strtotime(date('Y-m-d'))) {
        $stmt = $mysqli->prepare("UPDATE query_mst SET status = 'Completed' WHERE id = ?");
        $stmt->bind_param("i", $query_id);
        $stmt->execute();
        $stmt->close();
    }
}

echo "Cron completed successfully.";

?>
