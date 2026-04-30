<?php
include("../config.php");

if(isset($_POST['query_id'])){
    $query_id = $_POST['query_id'];

    // Fetch payments
    $stmt = $mysqli->prepare("SELECT * FROM payment WHERE query_id = ?");
    $stmt->bind_param("i", $query_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $i = 1;
    $totalAmount = 0;

    if($result->num_rows > 0){
        while($row = $result->fetch_assoc()){
            echo "<tr>";
            echo "<td>".$i++."</td>";
            echo "<td>".$row['amount']."</td>";
            echo "<td>".$row['date']."</td>";
            echo "<td>".$row['remark']."</td>";
            echo "<td>".$row['payment_type']."</td>";
            echo "<td><button class='btn btn-outline-warning btn-xs' onclick='editPaymentAmount(".$row['id'].", ".$row['amount'].")'>Edit Payment</button></td>";
            echo "</tr>";

            $totalAmount += $row['amount'];
        }
    } else {
        echo "<tr><td colspan='5' class='text-center'>No payments found.</td></tr>";
    }

    $stmt->close();

    // Fetch sale_amount from query_mst
    $stmt2 = $mysqli->prepare("SELECT sale_amount FROM query_mst WHERE id = ?");
    $stmt2->bind_param("i", $query_id);
    $stmt2->execute();
    $stmt2->bind_result($sale_amount);
    $stmt2->fetch();
    $stmt2->close();

    // Calculate pending amount
    $pendingAmount = $sale_amount - $totalAmount;

 
    // Display total and pending row
echo "<tr style='font-weight:bold; background:#f8f9fa;'>";
echo "<td colspan='1'>Total Paid</td>";
echo "<td>".$totalAmount."</td>";
echo "<td colspan='4'></td>";
echo "</tr>";

// pending amount row with Edit button
echo "<tr style='font-weight:bold; background:#fff3cd;'>";
echo "<td colspan='1'>Pending Amount</td>";
echo "<td>".$pendingAmount."</td>";
echo "<td colspan='3'>Sale Amount: <span id='saleAmount_".$query_id."'>".$sale_amount."</span></td>";
echo "<td><button class='btn btn-sm btn-primary' onclick='editSaleAmount(".$query_id.", ".$sale_amount.")'>Edit Sale</button></td>";
echo "</tr>";

}
?>
