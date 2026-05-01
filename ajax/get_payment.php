<?php
include "../config.php";

if(isset($_POST['query_id'])) {

    $query_id = $_POST['query_id'];

    $stmt = $mysqli->prepare("SELECT * FROM payment WHERE query_id=? ORDER BY id DESC");
    $stmt->bind_param("i", $query_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $i = 1;

    if($result->num_rows > 0){
        while($row = $result->fetch_assoc()){
            echo "<tr>
                <td>".$i++."</td>
                <td>".$row['amount']."</td>
                <td>".$row['date']."</td>
                <td>".$row['remark']."</td>
                <td>".$row['payment_type']."</td>
                <td>-</td>
            </tr>";
        }
    } else {
        echo "<tr><td colspan='6' class='text-center'>No Data Found</td></tr>";
    }
}
?>