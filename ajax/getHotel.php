
<?php
include("../config.php");

if (isset($_POST['quote_id'])) {
    $quote_id = $_POST['quote_id'];

    $stmt = $mysqli->prepare("SELECT hotel_quote.*, hotels.*
                              FROM hotel_quote
                              JOIN hotels ON hotel_quote.hotel_id = hotels.hotel_id
                              WHERE hotel_quote.quote_id = ?");
    $stmt->bind_param("i", $quote_id);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        echo '<option value="'.$row['id'].'" data-id="'.$row['id'].'">'.$row['hotel_name'].'</option>';
    }
}


?>
