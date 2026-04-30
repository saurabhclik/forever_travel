<?php
include("../config.php");

if (isset($_POST['id'])) {

    $stmt = $mysqli->prepare("SELECT a.*,b.name as customer,c.name as user from  query_mst a join customers b on a.customer_id=b.id join users c on a.user_id=c.id where a.customer_id=? and a.status='Converted'");
    $stmt->bind_param("i", $_POST['id']);
    $stmt->execute();
    $order_det = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    echo json_encode($order_det);
}
