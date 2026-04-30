
<?php
include("../config.php");

if (isset($_POST['hotel_id'])) {
    $hotel_id = $_POST['hotel_id'];

    $stmt = $mysqli->prepare("SELECT * from meals where hotel_id = ?");
    $stmt->bind_param("i", $hotel_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        echo '<option value="'.$row['meal_id'].'">'.$row['meal_type'].'</option>';
    }
}


?>