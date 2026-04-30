<?php
include("../config.php");

if(isset($_POST['payment_id']) && isset($_POST['payment_amount'])){
    $payment_id = $_POST['payment_id'];
    $payment_amount = $_POST['payment_amount'];

    $stmt = $mysqli->prepare("UPDATE payment SET amount = ? WHERE id = ?");
    $stmt->bind_param("di", $payment_amount, $payment_id);

    if($stmt->execute()){
        echo "Payment amount updated successfully.";
    } else {
        echo "Failed to update payment amount.";
    }

    $stmt->close();
}
?>
