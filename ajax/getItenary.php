<?php
include("../config.php");

if (isset($_POST['quote_id'])) {
    $quote_id = $_POST['quote_id'];

   $stmt = $mysqli->prepare("SELECT 
    hotel_quote.*, 
    hotel_quote_dates.title, 
    hotel_quote_dates.date, 
    hotel_quote_dates.description AS place_description,  
    hotel_quote_dates.image AS place_image,  
    hotel_quote_dates.place, 
    hotels.hotel_name AS hotel_name, 
    hotels.hotel_description AS hotel_description, 
    meals.meal_type AS meal_name, 
    meals.description AS meal_description, 
    rooms.room_type AS room_type, 
    rooms.room_price AS room_price,
    GROUP_CONCAT(amenities.amenity_name SEPARATOR ', ') AS amenities
FROM hotel_quote
JOIN hotels ON hotel_quote.hotel_id = hotels.hotel_id
JOIN meals ON hotel_quote.meal_id = meals.meal_id
JOIN rooms ON hotel_quote.room_id = rooms.room_id
JOIN hotel_quote_dates ON hotel_quote.id = hotel_quote_dates.hotel_quote_id
LEFT JOIN amenities ON hotel_quote.hotel_id = amenities.hotel_id
WHERE hotel_quote.quote_id = ?
GROUP BY hotel_quote.id, hotel_quote_dates.id
");

    
    $stmt->bind_param("i", $quote_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $data = [];
    while($row = $result->fetch_assoc()){
        $data[] = $row;
    }

    echo json_encode($data);
}
?>
