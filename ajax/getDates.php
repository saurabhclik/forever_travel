<?php
include("../config.php");

if (isset($_POST['hotel_quote_id'])) {
    $hotel_quote_id = $_POST['hotel_quote_id'];

    $stmt = $mysqli->prepare("SELECT DISTINCT date ,id
                            FROM hotel_quote_dates 
                            WHERE hotel_quote_id = ?
                            ");
    $stmt->bind_param("i", $hotel_quote_id);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        echo '<option value="'.$row['id'].'">'.$row['date'].'</option>';
    }
}
?>