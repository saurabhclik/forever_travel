<?php
include "config.php";
$location = [];
$transport = [];
$duration = "";

   if(isset($_GET['id']) )
    {
        try{
            $id = intval($_GET['id']);
            $stmt = $mysqli->prepare("
            SELECT
            customers.*,
            query_mst.destination,
            query_mst.from_date,
            query_mst.to_date,
            query_mst.adult,
            query_mst.child,
            query_mst.infant,
            query_mst.service,
            query_mst.status,
            query_mst.priority
            , destinations.name AS destination_name
            FROM customers
            JOIN query_mst ON customers.id = query_mst.customer_id
            JOIN destinations on query_mst.destination = destinations.id
            WHERE customers.id = ?
            ");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            $customer = $result->fetch_object();

            $start = new DateTime($customer->from_date);
            $end = new DateTime($customer->to_date);

            $interval = $start->diff($end);
            $days = $interval->days;
            $nights = $days - 1;
            }
            catch (Exception $e)
            {
                alert($e->getMessage(), "error", "error");
                redirect("?");
            }

    }

    if(isset($_GET['query_id']) && !empty($_GET['query_id'] ))
    {
        $query_id = $_GET['query_id'];
        $quote_id = $_GET['quote_id'];
        $stmt = $mysqli->prepare("SELECT * FROM quote_master WHERE query_id = ? AND id = ?");
        $stmt->bind_param("ii", $query_id, $quote_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $quote_result = $result->fetch_object();
        $stmt->close();


        $stmt = $mysqli->prepare("SELECT * FROM query_mst WHERE id = ?");
        $stmt->bind_param("i", $query_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $query_detail = $result->fetch_object();
        $stmt->close();

    }

    if(isset($_GET['quote_id']) && !empty($_GET['quote_id']))
    {
        if (isset($_GET['quote_id']))
        {
            $quote_id = $_GET['quote_id'];

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
            ORDER BY hotel_quote_dates.date ASC");

            $stmt->bind_param("i", $quote_id);
            $stmt->execute();
            $result = $stmt->get_result();

            $data = [];
            $location = [];

            $unique_hotel_ids = [];
            $total_rooms = 0;

            while ($row = $result->fetch_assoc())
            {
              $data[] = $row;
              $location[] = $row['place'];
              if (!in_array($row['hotel_id'], $unique_hotel_ids)) {
                  $total_rooms += $row['no_of_rooms'];
                  $unique_hotel_ids[] = $row['hotel_id'];
              }
            }
            // echo "<pre>";
            // print_r($data);
            // exit;
        }


        
    $quote_id = $_GET['quote_id'];

    $stmt = $mysqli->prepare("SELECT * FROM conveyance WHERE quote_id = ?");
    $stmt->bind_param("i", $quote_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) 
    {
        while($row = $result->fetch_assoc()) 
        {
            $departure = strtotime($row['departure_time']);
            $arrival   = strtotime($row['arrival_time']);

    
            if ($arrival < $departure) {
                $arrival = strtotime('+1 day', $arrival);
            }

            $duration = $arrival - $departure;
            $hours = floor($duration / 3600);
            $minutes = floor(($duration % 3600) / 60);
            $row['duration'] = sprintf('%02d:%02d', $hours, $minutes);

            $transport[] = $row;    
        }

    }


            // echo "<pre>";
            // print_r($transport);
            $stmt->close();

            $stmt = $mysqli->prepare("SELECT 
            hotel_quote.hotel_id, 
            hotel_quote.room_id,
            rooms.*,
            hotels.*,
            GROUP_CONCAT(DISTINCT photos.photo_url SEPARATOR ', ') AS hotel_images,
            GROUP_CONCAT(DISTINCT amenities.amenity_name SEPARATOR ', ') AS amenities
        FROM hotel_quote 
        left JOIN rooms ON hotel_quote.room_id = rooms.room_id
        JOIN hotels ON hotel_quote.hotel_id = hotels.hotel_id  
        LEFT JOIN photos ON hotel_quote.hotel_id = photos.hotel_id  
        LEFT JOIN amenities ON hotel_quote.hotel_id = amenities.hotel_id
        WHERE quote_id = ?
        GROUP BY hotel_quote.hotel_id
        ");

    $stmt->bind_param("i", $quote_id);
    $stmt->execute();
    $result2 = $stmt->get_result();

    if($result->num_rows > 0){
      while($row2 = $result2->fetch_assoc()){
        $hotel[] = $row2;

       }
    }

    // echo "<pre>";
    // print_r($hotel); exit;
    $stmt->close();

    }

    $inclusions_array = [] ?? null;
    $exclusions_array = [] ?? null;
    $imp_notes_array = [] ?? null;
    $imp_notes_content_array = [] ?? null;


      if(isset($_GET['quote_id']) && !empty($_GET['quote_id'])){

        $quote_id = $_GET['quote_id'];

        $stmt = $mysqli->prepare("SELECT inclusions, exclusions, imp_note, imp_note_content FROM quote_policy WHERE quote_id = ?");
        $stmt->bind_param("i",$quote_id);
        $stmt->execute();
        $res = $stmt->get_result();

        if($res->num_rows > 0){
            $row = $res->fetch_assoc();
            $inclusions_array        = !empty($row['inclusions']) ? explode(' | ', $row['inclusions']) : [];
            $exclusions_array        = !empty($row['exclusions']) ? explode(' | ', $row['exclusions']) : [];
            $imp_notes_array         = !empty($row['imp_note']) ? explode(' | ', $row['imp_note']) : [];
            $imp_notes_content_array = !empty($row['imp_note_content']) ? explode(' | ', $row['imp_note_content']) : [];
        }

    

        }


?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title> Trip Pdf</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&family=Material+Icons"
        rel="stylesheet" />

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css"
        integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
    /* Reset and basics */
    * {
        box-sizing: border-box;
    }

    body {
        font-family: 'Poppins', sans-serif;
        margin: 0;
        padding: 0;
        /* background: #f9fafb; */
        color: #1f2937;
        line-height: 1.6;
    }

    /* a {
        color: #3b82f6;
        text-decoration: none;
    } */

    /* a:hover {
        text-decoration: underline;
    } */

    /* Container */
    .container {
        max-width: 960px;
        margin: 2rem auto 4rem;
        padding: 0 1rem;
    }

    h1,
    h2,
    h3,
    h4 {
        color: #111827;
        margin-bottom: 0.5rem;
    }

    h1 {
        font-weight: 800;
        font-size: 2.5rem;
        margin-bottom: 1rem;
    }

    h2 {
        font-weight: 700;
        border-bottom: 2px solid #3b82f6;
        padding-bottom: 0.25rem;
        margin-top: 2.5rem;
    }

    h3 {
        font-weight: 600;
        margin-top: 1.5rem;
    }

    h4 {
        font-weight: 600;
        margin: 2px;
        color: #2563eb;
    }

    /* Section styles */
    section {
        margin-top: 2rem;
        background: white;
        padding: 1.5rem 2rem;
        border-radius: 12px;
        box-shadow: 0 6px 12px rgba(0, 0, 0, 0.05);
    }

    .subsection {
        margin-top: 1rem;
        border-top: 1px solid #e5e7eb;
        padding-top: 1rem;
    }

    /* Flight card */
    .flight-card {
        border: 1px solid #d1d5db;
        border-radius: 8px;
        padding: 1rem;
        margin-bottom: 1rem;
        background: #f3f4f6;
        display: flex;
        flex-wrap: wrap;
        gap: 1rem;
        align-items: center;
    }

    .flight-info {
        flex: 2 1 320px;
    }

    .airport {
        font-weight: 600;
        font-size: 1.1rem;
        color: #2563eb;
    }

    .flight-meta {
        font-size: 0.9rem;
        color: #4b5563;
    }

    .flight-duration {
        font-weight: 600;
        background: #dbeafe;
        padding: 0.4rem 0.7rem;
        border-radius: 6px;
        color: #1d4ed8;
        text-align: center;
        min-width: 95px;
    }

    /* Hotel card */
    .hotel-card {
        display: flex;
        gap: 1.5rem;
        padding: 1rem;
        border-radius: 12px;
        background: #fff;
        margin-bottom: 1rem;
    }

    .hotel-info-inline {
        font-size: 14px;
        color: #555;
    }

    .hotel-info-inline span {
        display: inline;
    }


    .hotel-image {
        min-width: 300px;
        min-height: 120px;
        border-radius: 12px;
        overflow: hidden;
        flex-shrink: 0;
        box-shadow: 0 0 8px rgba(99, 102, 241, 0.3);
        height: 500px;
    }

    .hotel-image img {
        width: 300px;
        height: 100%;
        object-fit: cover;
    }

    /* .hotel-details {
        flex-grow: 1;
        display: flex;
        flex-direction: column;
    } */

    .hotel-name {
        font-size: 1.25rem;
        font-weight: 700;
        color: #4338ca;
        margin-bottom: 0.25rem;
    }

    .hotel-address {
        font-size: 0.9rem;
        color: #6b7280;
        margin: 2px;
        color: black;
    }

    .hotel-rating {
        color: #f59e0b;
        font-weight: 600;
        margin: 2px;
    }

    .hotel-info-list {
        font-size: 0.9rem;
        color: #4b5563;
        list-style-type: disc;
        margin-left: 1.25rem;
    }

    /* Itinerary day toggle */
    .day-toggle {
        background: #4338ca;
        color: white;
        cursor: pointer;
        padding: 0.6rem 1rem;
        border-radius: 10px;
        border: none;
        font-weight: 600;
        margin-bottom: 0.75rem;
        display: flex;
        align-items: center;
        gap: 0.4rem;
        font-size: 1rem;
        user-select: none;
    }

    .day-toggle .material-icons {
        transition: transform 0.3s ease;
    }

    .day-toggle[aria-expanded="true"] .material-icons {
        transform: rotate(90deg);
    }

    .itinerary-content {
        padding-left: 1rem;
        border-left: 3px solid #4338ca;
        margin-bottom: 1.5rem;
    }

    .itinerary-time {
        font-weight: 600;
        margin-bottom: 0.3rem;
        color: #2563eb;
    }

    .itinerary-desc {
        margin-bottom: 0.6rem;
        white-space: pre-line;
    }

    .meal-included {
        background: #d1fae5;
        color: #065f46;
        padding: 0.15rem 0.5rem;
        border-radius: 4px;
        font-size: 0.8rem;
        font-weight: 700;
        display: inline-block;
        margin-right: 0.5rem;
        vertical-align: middle;
    }

    /* Lists */
    ul.inclusion-list,
    ul.exclusion-list {
        list-style-type: disc;
        margin-left: 1.4rem;
        color: #374151;
    }

    /* Responsive */
    @media (max-width: 640px) {
        .hotel-card {
            flex-direction: column;
            gap: 1rem;
        }

        .hotel-image {
            width: 100%;
            min-width: auto;
            min-height: 180px;
        }
    }

    /* Button styles */
    .btn {
        background: #4338ca;
        border: none;
        padding: 0.6rem 1.2rem;
        color: white;
        font-weight: 600;
        border-radius: 10px;
        cursor: pointer;
        transition: background 0.2s ease;
    }

    .btn:hover,
    .btn:focus {
        background: #3730a3;
        outline: none;
    }

    .card {
        border: 1px solid #d1d5db;
        border-radius: 8px;
        margin-bottom: 1rem;
        background: #ffffff;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .card-header {
        background: #f3f4f6;
        padding: 1rem;
        font-weight: 600;
        font-size: 1.2rem;
        border-bottom: 1px solid #e5e7eb;
    }

    .card-body {
        padding: 1rem;
    }

    .itinerary-time {
        font-weight: 600;
        color: #2563eb;
    }

    .first-slide {
        width: 100%;
        height: auto;
        margin-bottom: 20px;
        /* Space below the image */
    }


    .header-image-container {
        position: relative;
        text-align: center;
        color: white;
        /* Change text color for better visibility */
    }

    .header-image-container img {
        width: 100%;
        /* Make the image responsive */
        height: auto;
        /* Maintain aspect ratio */
        opacity: 0.8;
    }

    .header-content {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        text-align: center;
        background: rgba(0, 0, 0, 0.5);
        /* Optional: Add a background for better readability */
        padding: 20px;
        /* Optional: Add some padding */
        border-radius: 10px;
        /* Optional: Rounded corners */
    }

    .price-card {
        background: white;
        border-radius: 8px;
        width: 320px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        padding: 20px 25px;
        box-sizing: border-box;
    }

    .price-card h2 {
        margin: 0 0 10px 0;
        font-weight: 700;
        font-size: 18px;
        color: #222;
    }

    .info-line {
        font-size: 12px;
        margin: 3px 0;
        color: #555;
    }

    .info-line strong {
        font-weight: 700;
        color: #111;
    }

    .price-row {
        display: flex;
        justify-content: space-between;
        margin-top: 15px;
        border-bottom: 1px solid #ddd;
        padding-bottom: 8px;
        font-size: 14px;
        color: #222;
    }

    .price-row .label {
        font-weight: 400;
    }

    .price-row .amount {
        font-weight: 400;
    }

    .total-price-row {
        display: flex;
        justify-content: space-between;
        margin-top: 12px;
        font-size: 20px;
        font-weight: 700;
        color: #000;
    }

    .total-subtext {
        font-size: 10px;
        color: #999;
        margin-top: 2px;
        letter-spacing: 1px;
    }

    .footer-text {
        text-align: center;
        font-size: 11px;
        color: #111;
        margin-top: 15px;
        font-weight: 600;
        max-width: 350px;
        line-height: 1.2;
    }

    .footer-sub {
        font-weight: 400;
        margin-top: 2px;
    }

    .prizeBreakDownSection {
        margin: 0;
        font-family: Arial, sans-serif;
        background: #d3b58a url('https://storage.googleapis.com/workspace-0f70711f-8b4e-4d94-86f1-2a93ccde5887/image/7b712593-0588-4bb2-954a-331c29f9a1c3.png') no-repeat center center fixed;
        background-size: cover;
        display: flex;
        height: 100vh;
        justify-content: center;
        align-items: center;
        flex-direction: column;
    }


 

    /* Responsive image with fallback background */
    .hero-image {
        width: 100%;
        height: 500px;
        background: url('<?php echo BASE_PATH; ?>images/destination_images/<?php echo $quote_result->image; ?>') center center/cover no-repeat;

        position: relative;
        display: flex;
        flex-direction: column;
        justify-content: flex-end;
        padding: 40px 20px 80px 20px;
        color: #007c86;
    }

    .hero-image img {
        display: none;
    }

    .overlay-text {
        max-width: 90%;
        background: rgba(255 255 255 / 0.65);
        padding: 30px 20px;
        border-radius: 12px;
        backdrop-filter: saturate(180%) blur(10px);
        -webkit-backdrop-filter: saturate(180%) blur(10px);
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
    }

    .overlay-text h1 {
        margin: 0 0 10px 0;
        font-size: 6vw;
        font-weight: 700;
        letter-spacing: 0.02em;
        line-height: 1.1;
        color: #007c86;
    }

    .overlay-text h2 {
        margin: 0 0 8px 0;
        font-weight: 600;
        font-size: 1.4rem;
        color: #121212;
    }

    .overlay-text p {
        margin: 0;
        font-size: 1rem;
        letter-spacing: 0.04em;
        color: #121212;
    }

    /* Bottom location banner */
    .location-bar {
        width: 100%;
        background: #ffffffdd;
        text-align: center;
        font-weight: 600;
        font-size: 0.95rem;
        padding: 12px 10px;
        color: #000000cc;
        letter-spacing: 0.05em;
        position: relative;
        bottom: 0;
        user-select: none;
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .hero-image {
            padding: 30px 15px 60px 15px;
        }

        .overlay-text h1 {
            font-size: 12vw;
        }

        .overlay-text h2 {
            font-size: 1.25rem;
        }

        .overlay-text p {
            font-size: 0.9rem;
        }

        .location-bar {
            font-size: 0.85rem;
            padding: 10px 8px;
        }
    }

@media print {
  section {
    page-break-after: auto;
    break-after: auto;
  }
  section:first-of-type {
    page-break-before: auto;
    break-before: auto;
  }
  .card, .flight-card {
    page-break-inside: avoid;
    break-inside: avoid;
  }
}


    </style>

</head>

<body>
    <div class="container " role="main">
     
        <!-- <div class="header-image-container">
            <img src="<?php echo BASE_PATH;  ?>images/destination_images/<?php echo $quote_result->image ?>"
                alt="First Slide" class="first-slide" />
            <div class="header-content">
                <h1> Trip Itinerary</h1>
                <p><strong>Duration:</strong> <?= $days ?> Days / <?= $nights ?> Nights</p>
                <p><strong>Locations:</strong> <?php echo implode(" | ", $location); ?></p>
            </div>
        </div> -->
      
            <section class="hero-image" role="img"
                aria-label="Aerial view of turquoise blue sea surrounded by lush green tropical hills with traditional Bali water villas with pointed thatched roofs along the shore">

                <div class="overlay-text" aria-describedby="duration-info location-info">
                    <h1><?= $customer->destination_name ?></h1>
                    <h2>TRIP ITINERARY</h2>
                    <p id="duration-info"> <?= $days ?> Days / <?= $nights ?> Nights</p>
                </div>
            </section>
            <div class="location-bar" id="location-info">
               <?php echo implode(" | ", $location); ?>
            </div>
        

        <section id="flights" aria-label="Flight Details">
            <h2>Flights</h2>
            <?php foreach($transport as $row): ?>
            <article class="flight-card" aria-label="Flight from Delhi to Ho Chi Minh City">
                <div class="flight-info">
                    <div><span class="material-icons" aria-hidden="true"
                            style="vertical-align:middle;">flight_takeoff</span> <span
                            class="airport"><?= $row['from_location']  ?></span> → <span
                            class="airport"><?= $row['to_location']  ?></span></div>
                    <div class="flight-meta"><?= $row['transport_name'] ?> | Departure Date :
                        <?php echo $row['travel_date'] ?> | Departure Time :
                        <?= $row['departure_time']  ?> | Arrival Date : <?php echo $row['arrival_date']  ?> |
                        Arrival Time : <?= $row['arrival_time']  ?> | Baggage <?php echo $row['travel_baggage']  ?>
                    </div>
                </div>
                <div class="flight-duration" aria-label="Flight duration">
                    Duration <?php echo $row['duration']  ?>
                </div>
            </article>
            <?php endforeach; ?>
        </section>

        <section aria-label="Hotel Details">
            <h2>Hotels</h2>
            <?php foreach($hotel as $row): ?>
              
            <article class="hotel-card" aria-label=" <?php echo $row['hotel_name'];  ?> ">
                <div class="hotel-image">
                    <img src="<?php echo BASE_PATH; ?><?php echo $row['hotel_images'] ?>"
                        onerror="this.onerror=null;this.src='https://placehold.co/180x120/gray/ffffff?text=Image+Unavailable';"
                        alt="Image" />
                </div>
                <div class="hotel-details">
                    <h3 class="hotel-name"> <?php echo $row['hotel_name'];  ?></h3>
                    <p class="hotel-address text-dark">
                        Address : <?php echo $row['address'];  ?> | Country : <?php echo $row['country']  ?> | State
                        :<?php echo $row['state']  ?> | City : <?php echo $row['city']  ?>
                    </p>

                    <h4>Hotel Description</h4>
                    <p class="hotel-rating" aria-label="Rating 8.6 out of 10">
                        Room Type: <?= $row['room_type'] ?><br>
                        <?php
                            $stars = intval($row['star_rating']); 
                            for ($i = 0; $i < $stars; $i++) 
                            {
                                echo '<i class="fa fa-star" style="color: gold;"></i>';
                            }
                            for ($i = $stars; $i < 5; $i++) 
                            {
                                echo '<i class="fa fa-star-o" style="color: gold;"></i>'; 
                            }
                            ?>
                    </p>
                    <p><?= $row['hotel_description'];  ?></p>
                    <h4>Amenities</h4>
                    <div class="hotel-info-inline">
                        <?php 
                $amenities = explode(',', $row['amenities']);
                $amenity_count = count($amenities);
                foreach($amenities as $index => $amenity){
                    echo "<span>".trim($amenity)."</span>";
                    // Add dot separator if not the last item
                    if($index < $amenity_count - 1){
                        echo " &bull; ";
                    }
                }
            ?>
                    </div>

                </div>
            </article>
           

            <?php endforeach;  ?>
        </section>

        <section id="itinerary" aria-label="Travel Itinerary">
            <h2>Itinerary</h2>
            <?php
                $day = 1;
                $previous_date = '';

                foreach($data as $row):
                    if ($row['date'] !== $previous_date):
                ?>
            <div class="card mb-3">
                <div class="card-header">
                    Day <?= $day ?> - Arrival <?= $row['title'] ?> -
                    <?= date("D, d M Y", strtotime($row['date'])) ?>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-3">
                            <img src="<?= BASE_PATH ?>/images/palace_images/<?= $row['place_image'] ?>" alt="image"
                                style="width:100%; height:250px; object-fit:cover; border-radius:20px;">




                        </div>
                        <div class="col-9">
                            <p><?= $row['place_description'] ?>.</p>
                            <p>
                                <span class="material-icons" aria-hidden="true"
                                    style="vertical-align:middle;">hotel</span>
                                <strong>Accommodation:</strong> Hotel: <?= $row['hotel_name'] ?>, Room Type:
                                <?= $row['room_type'] ?>
                            </p>
                            <p>
                                <span class="material-icons" aria-hidden="true"
                                    style="vertical-align:middle;">restaurant</span>
                                <strong>Meal Type: <span style="color: blue;">Included At Hotel</span> </strong>
                                <?= $row['meal_name'] ?> - <?= $row['meal_description'] ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            <?php
                $previous_date = $row['date'];
                $day++;
                else:
            ?>
            <div class="card mb-3">
                <div class="card-header">
                    Same Day - Arrival <?= $row['title'] ?> - <?= date("D, d M Y", strtotime($row['date'])) ?>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-3">
                            <img src="<?= BASE_PATH ?>/images/palace_images/<?= $row['place_image'] ?>" alt="image"
                                height="200">
                        </div>
                        <div class="col-9">
                            <p><?= $row['place_description'] ?>.</p>
                            <p>
                                <span class="material-icons" aria-hidden="true"
                                    style="vertical-align:middle;">hotel</span>
                                <strong>Accommodation:</strong> Hotel: <?= $row['hotel_name'] ?>, Room Type:
                                <?= $row['room_type'] ?>
                            </p>
                            <p>
                                <span class="material-icons" aria-hidden="true"
                                    style="vertical-align:middle;">restaurant</span>
                                <strong>Meal:</strong> <?= $row['meal_description'] ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            <?php
                endif;
            endforeach;
            ?>
        </section>




        <section id="inclusions" aria-label="Inclusions">
            <h2>Inclusions</h2>
            <ul class="inclusion-list">
                <?php
                    $max_count = 
                        count($inclusions_array);
                
                    $sr_no = 1;
                    for($i = 0; $i < $max_count; $i++):
                    ?>
                            <li><?= $inclusions_array[$i] ?? '-' ?></li>
                            <?php endfor; ?>
                        </ul>
                    </section>

                    <section id="exclusions" aria-label="Exclusions">
                        <h2>Exclusions</h2>
                        <ul class="exclusion-list">
                            <?php
                    $count_exclusions = count($exclusions_array);
                    for($i = 0; $i < $count_exclusions; $i++):
                ?>
                <li><?= $exclusions_array[$i] ?? '-' ?></li>
                <?php endfor; ?>
            </ul>
        </section>


        <section id="notes" aria-label="Important Notes">
            <h2>Important Notes</h2>
                        <?php
                $notes_count = max(count($imp_notes_array), count($imp_notes_content_array));
                if($notes_count > 0):
                    for($i = 0; $i < $notes_count; $i++):
                ?>
                        <p>
                            <strong><?= $imp_notes_array[$i] ?? '-' ?>:</strong>
                            <?= $imp_notes_content_array[$i] ?? '-' ?>
                        </p>
                        <?php
                    endfor;
                else:
                ?>
                        <p>No important notes available.</p>
                        <?php endif; ?>
        </section>
     

    </div>

   

</body>

</html>