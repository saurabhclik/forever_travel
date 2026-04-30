<?php
include("../config.php");

if (isset($_POST['customer_id'])) {




    $stmt = $mysqli->prepare("SELECT * FROM   wallet_history  where customer_id=?");
    $stmt->bind_param("i",$_POST['customer_id']);
    $stmt->execute();
    $wallet_history = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    echo json_encode($wallet_history);
}
