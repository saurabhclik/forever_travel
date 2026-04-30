<?php
include("../config.php");

if (isset($_POST['customer_id'])) {




    $stmt = $mysqli->prepare("SELECT * FROM   order_mst  where customer_id=?");
    $stmt->bind_param("i",$_POST['customer_id']);
    $stmt->execute();
    $city = $stmt->get_result();
 
    while ($row = $city->fetch_assoc()) {
        echo '<option value="'.$row['id'].'">'.$row['invoice'].'</option>';
    }
 
}
