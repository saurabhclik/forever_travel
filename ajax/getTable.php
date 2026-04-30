<?php
include("../config.php");
if (isset($_POST['quote_id'])) {
    $quote_id = $_POST['quote_id'];

    $stmt = $mysqli->prepare("SELECT * FROM conveyance WHERE quote_id = ?");
    $stmt->bind_param("i", $quote_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>".$row['from_location']."</td>";
            echo "<td>".$row['to_location']."</td>";
            echo "<td>".$row['transport_mode']."</td>";
            echo "<td>".$row['transport_name']."</td>";
            echo "<td>".$row['departure_time']."</td>";
            echo "<td>".$row['arrival_time']."</td>";
            echo "<td>".$row['price']."</td>";
            echo "<td>".$row['transport_class']."</td>";
            echo "</tr>";
        }
    } else {
        echo "<tr><td colspan='8' class='text-center'>No records found</td></tr>";
    }
}
?>
