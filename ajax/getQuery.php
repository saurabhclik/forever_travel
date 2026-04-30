<?php
include("../config.php");

if (isset($_POST['customer_id'])) {
    $customer_id = $_POST['customer_id'];
$stmt = $mysqli->prepare("SELECT 
    customers.name, 
    customers.number, 
    query_mst.id,
    query_mst.destination,
    query_mst.status 
FROM query_mst 
JOIN customers ON customers.id = query_mst.customer_id 
WHERE customers.id = ? AND query_mst.status = 'Converted'");


    $stmt->bind_param("i", $customer_id);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) 
    {
        echo '<option value="'.$row['id'].'" data-id="'.$row['id'].'">'.$row['name'] . "(". $row['destination'] . ")" .'</option>';
    }
}


?>
