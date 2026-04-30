<?php
include("../config.php");

if (isset($_POST['id'])) {

    $stmt = $mysqli->prepare("SELECT * from  query_imgs  where mst_id=?");
    $stmt->bind_param("i", $_POST['id']);
    $stmt->execute();
    $order_det = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    echo json_encode($order_det);
}
