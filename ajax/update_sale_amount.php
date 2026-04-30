<?php
include("../config.php");

if(isset($_POST['query_id']) && isset($_POST['sale_amount'])){
    $query_id = $_POST['query_id'];
    $sale_amount = $_POST['sale_amount'];

    $stmt = $mysqli->prepare("UPDATE query_mst SET sale_amount = ? WHERE id = ?");
    $stmt->bind_param("di", $sale_amount, $query_id);

    if($stmt->execute()){
        echo "Sale amount updated successfully.";
    } else {
        echo "Failed to update sale amount.";
    }

    $stmt->close();
}
?>
