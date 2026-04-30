<?php
include("../config.php");

// if (isset($_POST['travel_date'])) {
//     $stmt = $mysqli->prepare("SELECT a.*,b.name as customer, c.name as company FROM order_mst a join customers b on a.customer_id=b.id join company c on a.company_id=c.id WHERE travel_date = ? ;");
//     $stmt->bind_param("s", $_POST['travel_date']);
//     $stmt->execute();
//     $order_mst = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

//     echo  json_encode($order_mst);
// }

if(isset($_POST['travel_date'])){
    $stmt = $mysqli->prepare("SELECT query_mst.*, destinations.name as destinations_name , customers.name as customer_name  from query_mst left join customers on query_mst.customer_id = customers.id left join destinations on query_mst.destination = destinations.id where query_mst.from_date = ? and query_mst.status = 'Converted'");
    $stmt->bind_param("s", $_POST['travel_date']);
    $stmt->execute();
    $order_mst = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    echo json_encode($order_mst);
}
