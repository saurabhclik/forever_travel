<?php 



    include "Layouts/Header.php"; 
    include "Layouts/Sidebar.php"; 

   if(isset($_GET['id']) )
    {

        try{
            $id = intval($_GET['id']);
               $stmt = $mysqli->prepare("SELECT query_mst.*,customers.*,quote_master.created_at as quote_created_date , destinations.name as destination_name from query_mst join customers on customers.id = query_mst.customer_id left join destinations ON destinations.id = query_mst.destination join quote_master on query_mst.id = quote_master.query_id where query_mst.customer_id = ? and query_mst.id = ? ");
            $stmt->bind_param("ii", $id,$_GET['q_id']);
            $stmt->execute();
            $result = $stmt->get_result();
            $customer = $result->fetch_object();

            $start = new DateTime($customer->from_date);
            $end = new DateTime($customer->to_date);

            $interval = $start->diff($end);
            $days = $interval->days + 1;
            $nights = $days - 1;

            }
            catch (Exception $e)
            {
                alert($e->getMessage(), "error", "error");
                redirect("?");
            }

    }

    if(isset($_GET['q_id']))
    {
        $query_id = $_GET['q_id'];
        $stmt = $mysqli->prepare("Select * from quote_master where query_id = ?");
        $stmt->bind_param("i",$query_id);
        $stmt->execute();
        $quote_result = $stmt->get_result();
        $stmt->close();

    }



    $edit_mode = false;
    $quote_id = null;
    $quote = null;
    $place_description = [];
    $hotel_quotes = [];
    $transport_details = [];

    if (isset($_GET['quote_id']))
    {
        $edit_mode = true;
        $quote_id = intval($_GET['quote_id']);
        $hotel_quotes = [];
        $pakage_type = "";
        $comments = "";

        $stmt = $mysqli->prepare("
            SELECT hotel_quote.*, 
            quote_master.pakage_type,
            quote_master.comment,
            meals.meal_type, 
            meals.meal_plan, 
            rooms.room_type, 
            rooms.room_price
            FROM hotel_quote
            LEFT JOIN quote_master ON hotel_quote.quote_id = quote_master.id
            LEFT JOIN meals ON hotel_quote.meal_id = meals.meal_id
            LEFT JOIN rooms ON hotel_quote.room_id = rooms.room_id
            WHERE quote_id = ?
        ");

        $stmt->bind_param("i", $quote_id);
        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            // echo "<pre>";
            // print_r($row); exit;    
            $hotel_quote_id = $row['id'];
            $pakage_type = $row['pakage_type'];
            $comments = $row['comment'];
            $date_stmt = $mysqli->prepare("SELECT * FROM hotel_quote_dates WHERE hotel_quote_id = ?");
            $date_stmt->bind_param("i", $hotel_quote_id);
            $date_stmt->execute();
            $date_result = $date_stmt->get_result();

            $row['dates'] = [];
            while ($date_row = $date_result->fetch_assoc()) {
                $row['dates'][] = $date_row['date'];
                $row['palace_description'][] = [
                        'date' => $date_row['date'],
                        'title' => $date_row['title'],
                        'place' => $date_row['place'],
                        'description' => $date_row['description']
                    ];
            }
            $hotel_quotes[] = $row;
            $place_description[] = $row['palace_description'];

        }


        $stmt = $mysqli->prepare("SELECT * FROM conveyance WHERE quote_id = ?");
        $stmt->bind_param("i", $quote_id);
        $stmt->execute();
        $transport_details = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();




    }


    if (isset($_POST['BtnSubmit']) && isset($_GET['quote_id'])) 
    {
        
        $quote_id = intval($_GET['quote_id']);
        $customer_id = $_POST['customer_id']; 
        $pakage_type = $_POST['pakage_type'];
        $query_id = $_GET['q_id'];             
        $date = $_POST['date'];
        $comment = $_POST['note'];
        $created_at = date('Y-m-d H:i:s');
        $image = $_POST['destination_image'] ?? null;

        $mysqli->begin_transaction();

        try {
            // 1. Update quote_master
            $stmt = $mysqli->prepare("UPDATE quote_master SET customer_id=?, pakage_type=?, query_id=?, date=?, comment=?, created_at=? , image= ? WHERE id=?");
            $stmt->bind_param('isissssi', $customer_id, $pakage_type, $query_id, $date, $comment, $created_at, $image ,$quote_id);
            $stmt->execute();
            $stmt->close();

            // 2. Delete old hotel_quote and hotel_quote_dates
            $stmt = $mysqli->prepare("SELECT id FROM hotel_quote WHERE quote_id = ?");
            $stmt->bind_param("i", $quote_id);
            $stmt->execute();
            $result = $stmt->get_result();

            $old_hotel_ids = [];
            while ($row = $result->fetch_assoc()) {
                $old_hotel_ids[] = $row['id'];
            }
            $stmt->close();

            foreach ($old_hotel_ids as $id) {
                $mysqli->query("DELETE FROM hotel_quote_dates WHERE hotel_quote_id = $id");
            }

            $mysqli->query("DELETE FROM hotel_quote WHERE quote_id = $quote_id");

          
            foreach ($_POST['hotel_rows'] as $row) {
                $hotel_id = $row['hotel'];
                $meal_id = $row['meal'];
                $room_id = $row['room'];
                $rooms_count = $row['no_of_rooms'];
                $created_at = date('Y-m-d H:i:s');

                $stmt = $mysqli->prepare("SELECT room_price FROM rooms WHERE room_id = ?");
                $stmt->bind_param("i", $room_id);
                $stmt->execute();
                $stmt->bind_result($room_price);
                $stmt->fetch();
                $stmt->close();

                $stmt = $mysqli->prepare("INSERT INTO hotel_quote (quote_id, query_id, hotel_id, meal_id, room_id, no_of_rooms, price, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("iiiiiids", $quote_id, $query_id, $hotel_id, $meal_id, $room_id, $rooms_count, $room_price, $created_at);
                $stmt->execute();
                $hotel_quote_id = $stmt->insert_id;
                $stmt->close();

                if (!empty($row['dates'])) {
                    $stmt_dates = $mysqli->prepare("INSERT INTO hotel_quote_dates (hotel_quote_id, date) VALUES (?, ?)");
                    foreach ($row['dates'] as $date) {
                        $stmt_dates->bind_param("is", $hotel_quote_id, $date);
                        $stmt_dates->execute();
                    }
                    $stmt_dates->close();
                }
            }

            $mysqli->query("DELETE FROM conveyance WHERE quote_id = $quote_id");

            if (isset($_POST['transport_details'])) {
                foreach ($_POST['transport_details'] as $transport) {
                    $from_location     = $transport['transport_from'];
                    $to_location       = $transport['transport_to'];
                    $transport_mode    = $transport['transport_mode'];
                    $transport_name    = $transport['transport_name'];
                    $departure_time    = $transport['departure_time'];
                    $arrival_time      = $transport['arrival_time'];
                    $price             = $transport['price'];
                    $transport_comment = $transport['comment'];
                    $transport_class   = $transport['class'];
                    $travel_date = $transport['travel_date'];
                    $travel_person = $transport['travel_person'];
                    $travel_Baggage =  $transport['travel_Baggage']; 


                    $stmt = $mysqli->prepare("INSERT INTO conveyance (quote_id, from_location, to_location, transport_mode, transport_name, departure_time, arrival_time, price, comment ,transport_class,travel_date,travel_person,travel_baggage) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)");
                    $stmt->bind_param("issssssssssis", $quote_id, $from_location, $to_location, $transport_mode, $transport_name, $departure_time, $arrival_time, $price, $transport_comment,$transport_class,$travel_date,$travel_person,$travel_Baggage);
                    $stmt->execute();
                    $stmt->close();
                }
            }

            $mysqli->commit();

            $_SESSION['alert'] = [
                'title' => 'Quote Updated',
                'text' => 'Quote has been successfully updated.',
                'icon' => 'success'
            ];

            redirect("?id=" . $_GET['id'] . "&q_id=" . $_GET['q_id'] . "&quote_id=".$quote_id);

        } catch (Exception $e) {
            $mysqli->rollback();
            echo "Error: " . $e->getMessage();
        }
    }


    if(isset($_POST['submitBtn']) && isset($_GET['quote_id'])){

        $quote_id   = $_GET['quote_id'];
        $inclusions = $_POST['inclusions'] ?? [];
        $exclusions = $_POST['exclusions'] ?? [];
        $imp_notes  = $_POST['imp_notes']  ?? [];

        // Fetch Inclusions
        $inclusion_names = [];
        if(!empty($inclusions)){
            $in_ids = implode(',', array_map('intval', $inclusions));
            $result = $mysqli->query("SELECT name FROM inclusions WHERE id IN ($in_ids)");
            while($row = $result->fetch_assoc()){
                $inclusion_names[] = $row['name'];
            }
        }

        // Fetch Exclusions
        $exclusion_names = [];
        if(!empty($exclusions)){
            $ex_ids = implode(',', array_map('intval', $exclusions));
            $result = $mysqli->query("SELECT name FROM exclusions WHERE id IN ($ex_ids)");
            while($row = $result->fetch_assoc()){
                $exclusion_names[] = $row['name'];
            }
        }

        // Fetch Important Notes
        $imp_note_titles   = [];
        $imp_note_contents = [];
        if(!empty($imp_notes)){
            $imp_ids = implode(',', array_map('intval', $imp_notes));
            $result = $mysqli->query("SELECT category, content FROM important_notes WHERE id IN ($imp_ids)");
            while($row = $result->fetch_assoc()){
                $imp_note_titles[]   = $row['category'];
                $imp_note_contents[] = $row['content'];
            }
        }

        $inclusions_text    = implode(' | ', $inclusion_names);
        $exclusions_text    = implode(' | ', $exclusion_names);
        $imp_notes_text     = implode(' | ', $imp_note_titles);
        $imp_notes_content  = implode(' | ', $imp_note_contents);

        // Check if quote_id exists
        $check_stmt = $mysqli->prepare("SELECT id FROM quote_policy WHERE quote_id = ?");
        $check_stmt->bind_param("i", $quote_id);
        $check_stmt->execute();
        $check_stmt->store_result();

        if($check_stmt->num_rows > 0){
            // Exists — update
            $update_stmt = $mysqli->prepare("UPDATE quote_policy SET inclusions = ?, exclusions = ?, imp_note = ?, imp_note_content = ?, updated_at = NOW() WHERE quote_id = ?");
            $update_stmt->bind_param("ssssi", $inclusions_text, $exclusions_text, $imp_notes_text, $imp_notes_content, $quote_id);

            if($update_stmt->execute()){
                $_SESSION['alert'] = [
                    'title' => 'Quote Policy',
                    'text'  => 'Quote Policy has been successfully updated.',
                    'icon'  => 'success'
                ];
            } else {
                $_SESSION['alert'] = [
                    'title' => 'Quote Policy',
                    'text'  => $update_stmt->error,
                    'icon'  => 'error'
                ];
            }
            $update_stmt->close();

        } else {
            // Does not exist — insert
            $insert_stmt = $mysqli->prepare("INSERT INTO quote_policy (quote_id, inclusions, exclusions, imp_note, imp_note_content, created_at, updated_at)
                VALUES (?, ?, ?, ?, ?, NOW(), NOW())");

            $insert_stmt->bind_param("sssss", $quote_id, $inclusions_text, $exclusions_text, $imp_notes_text, $imp_notes_content);

            if($insert_stmt->execute()){
                $_SESSION['alert'] = [
                    'title' => 'Quote Policy',
                    'text'  => 'Quote Policy has been successfully added.',
                    'icon'  => 'success'
                ];
            } else {
                $_SESSION['alert'] = [
                    'title' => 'Quote Policy',
                    'text'  => $insert_stmt->error,
                    'icon'  => 'error'
                ];
            }
            $insert_stmt->close();
        }

        $check_stmt->close();
    }


  if(isset($_GET['quote_id']) && !empty($_GET['quote_id']))
  {
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



        $hotel_totals = [];
        $hotel_dates = []; 

        foreach ($hotel_quotes as $quote) {
            $hotel_id = $quote['hotel_id'];
            $no_of_rooms = $quote['no_of_rooms'];
            $price_per_room = $quote['price'];

        
            if (!isset($hotel_dates[$hotel_id])) {
                $hotel_dates[$hotel_id] = [];
            }

        
            foreach ($quote['dates'] as $date) {
                $hotel_dates[$hotel_id][$date] = true;
            }

        
            if (!isset($hotel_totals[$hotel_id])) {
                $hotel_totals[$hotel_id] = [
                    'price_per_room' => $price_per_room,
                    'no_of_rooms'    => $no_of_rooms,
                ];
            }
        }


        $hotel_name_map = [];
        $res = $mysqli->query("SELECT hotel_id, hotel_name FROM hotels");
        while ($row = $res->fetch_assoc()) {
            $hotel_name_map[$row['hotel_id']] = $row['hotel_name'];
        }

    if (isset($_POST['submitPlace']))
    {
        if (isset($_POST['plan_date_id']) && isset($_POST['place_id']) && isset($_POST['hotel_id'])) {

            $plan_date_ids = $_POST['plan_date_id'];
            $place_ids     = $_POST['place_id'];
            $hotel_ids     = $_POST['hotel_id'];


            $processed_plan_ids = [];

            for ($i = 0; $i < count($plan_date_ids); $i++) {
                $plan_date_id = $plan_date_ids[$i];
                $place_id     = $place_ids[$i];
                $hotel_id     = $hotel_ids[$i];


                $stmt1 = $mysqli->prepare("SELECT date, hotel_quote_id FROM hotel_quote_dates WHERE id = ?");
                $stmt1->bind_param("i", $plan_date_id);
                $stmt1->execute();
                $result1 = $stmt1->get_result();

                if ($result1->num_rows > 0) {
                    $plan_row = $result1->fetch_assoc();
                    $date = $plan_row['date'];
                    $hotel_quote_id = $plan_row['hotel_quote_id'];

                    $stmt2 = $mysqli->prepare("SELECT title, place, image, description FROM country_landmarks WHERE id = ?");
                    $stmt2->bind_param("i", $place_id);
                    $stmt2->execute();
                    $result2 = $stmt2->get_result();

                    if ($result2->num_rows > 0) {
                        $place_row = $result2->fetch_assoc();
                        $title       = $place_row['title'];
                        $place       = $place_row['place'];
                        $description = $place_row['description'];
                        $pic = $place_row['image'];

                        if (!in_array($plan_date_id, $processed_plan_ids)) {
                            $stmt3 = $mysqli->prepare("UPDATE hotel_quote_dates SET title = ?, place = ?, description = ?, image = ?, updated_at = NOW() WHERE id = ?");
                            $stmt3->bind_param("ssssi", $title, $place, $description, $pic , $plan_date_id);
                            $stmt3->execute();

                            $processed_plan_ids[] = $plan_date_id;

                        } else {
                            $stmt4 = $mysqli->prepare("INSERT INTO hotel_quote_dates (hotel_quote_id, date, title, place, description, image , created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())");
                            $stmt4->bind_param("isssss", $hotel_quote_id, $date, $title, $place, $description,$pic);
                            $stmt4->execute();
                        }
                    }
                }
            }


            $_SESSION['alert'] = [
                'title' => 'Your Dates Plan Updated',
                'text'  => 'Plan Updated Successfully',
                'icon'  => 'success'
            ];
            redirect("?id=" . $_GET['id'] . "&q_id=" . $_GET['q_id']. "&quote_id=" . $_GET['quote_id']);
        }
    }




?>

<style>
body {
    background-color: #f5f6fa;
}

.itinerary-card {
    border-radius: 1rem;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.05);
    margin-bottom: 20px;
}

.itinerary-card h5 {
    color: #333;
}

.description {
    white-space: pre-line;
}
</style>

<div class="content-body">
    <div class="container-fluid">
        <!-- row -->
        <div class="row">
            <div class="col-12">
                <div class="card">

                    <div class="card-header">
                        <div class="card-body">
                            <?php if ($customer): ?>
                            <div class="row">
                                <div class="col">
                                    <span class="card-title">
                                        <i class="fa-solid fa-hashtag"></i>
                                        <?= $customer->id  ?> ⦁
                                    </span>
                                    <h5 class="card-title d-inline">
                                        <?= $customer->name ?>
                                        ⦁
                                    </h5>
                                    <span class="card-title ">
                                        <?= $customer->destination_name ?>
                                    </span>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col">
                                    <h5 class="card-title d-inline">
                                        <i class="fa-solid fa-calendar"></i> &nbsp;
                                        <?= $customer->from_date ?>
                                        <i class="fa-solid fa-arrow-right"></i>
                                        <?php echo $customer->to_date;  ?>
                                    </h5>
                                    <span class="card-title ">
                                        &nbsp;
                                        Days: <?= $days ?>
                                        Night : <?= $nights  ?>

                                    </span>
                                    <span class="card-title">
                                        &nbsp;
                                        <i class="fa-solid fa-user"></i>
                                        Adult: <?=  $customer->adult  ?>
                                    </span>
                                    <span class="card-title">

                                        child: <?= $customer->child  ?>
                                    </span>
                                    <span class="card-title">
                                        Infant: <?= $customer->infant  ?>
                                    </span>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col">
                                    <h5 class="card-title d-inline">
                                        <?= $customer->name ?>
                                        ⦁
                                    </h5>

                                    <span class="card-title">
                                        <a href="tel: <?= $customer->number  ?>"><?= $customer->number  ?></a>
                                    </span>
                                    <span class="card-title">
                                        ⦁ Created Date :
                                        <?php echo date('Y-m-d',strtotime($customer->quote_created_date))  ?>
                                    </span>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="">
                            <ul class="nav nav-tabs" id="myTab" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active" id="home-tab" data-bs-toggle="tab"
                                        data-bs-target="#home-tab-pane" type="button" role="tab"
                                        aria-controls="home-tab-pane" aria-selected="true">Guest Details</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="profile-tab" data-bs-toggle="tab"
                                        data-bs-target="#profile-tab-pane" type="button" role="tab"
                                        aria-controls="profile-tab-pane" aria-selected="false">Destination &
                                        Duration</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="contact-tab" data-bs-toggle="tab"
                                        data-bs-target="#contact-tab-pane" type="button" role="tab"
                                        aria-controls="contact-tab-pane" aria-selected="false">Hotel Details</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="transport-tab" data-bs-toggle="tab"
                                        data-bs-target="#transport-tab-pane" type="button" role="tab"
                                        aria-controls="transport-tab-pane" aria-selected="false">Transport
                                        Details</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="place-tab" data-bs-toggle="tab"
                                        data-bs-target="#place-tab-pane" type="button" role="tab"
                                        aria-controls="place-tab-pane" aria-selected="false">Palace Visit</button>
                                </li>

                            </ul>

                            <!-- Tab panes -->
                            <form action="" method="post">
                                <div class="tab-content p-3 border border-top-0">
                                    <div class="tab-pane fade show active" id="home-tab-pane" role="tabpanel"
                                        aria-labelledby="home-tab" tabindex="0">
                                        <h4>Guest Details</h4>
                                        <div class="row">
                                            <div class="mb-3 col-3">
                                                <label for="name" class="form-label">Full Name</label>
                                                <input type="text" class="form-control" id="name" name="name"
                                                    placeholder="Enter your full name" value="<?=  $customer->name ?>"
                                                    readonly>
                                            </div>
                                            <div class="mb-3 col-3">
                                                <label for="" class="form-label">Number</label>
                                                <input type="number" class="form-control" id="number" name="number"
                                                    placeholder="Enter your number" value="<?=  $customer->number ?>"
                                                    readonly>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="tab-pane fade" id="profile-tab-pane" role="tabpanel"
                                        aria-labelledby="profile-tab" tabindex="0">
                                        <h4>Destination & Duration</h4>
                                        <div class="row">
                                            <div class="mb-3 col-3">
                                                <label for="" class="form-label">Destination</label>
                                                <input type="text" class="form-control" id="destination"
                                                    name="destination" placeholder="Enter your destination"
                                                    value="<?=  $customer->destination ?>" readonly>
                                            </div>
                                            <div class="mb-3 col-3">
                                                <label for="" class="form-label">Adult</label>
                                                <input type="number" class="form-control" id="adult" name="adult"
                                                    value="<?=  $customer->adult ?>" readonly>
                                            </div>
                                            <div class="mb-3 col-3">
                                                <label for="" class="form-label">Child</label>
                                                <input type="number" class="form-control" id="child" name="child"
                                                    value="<?=  $customer->child ?>" readonly>
                                            </div>
                                            <div class="mb-3 col-3">
                                                <label for="" class="form-label">Infant</label>
                                                <input type="number" class="form-control" id="infant" name="infant"
                                                    value="<?=  $customer->infant ?>" readonly>
                                            </div>

                                        </div>
                                        <div class="row">
                                            <div class="mb-3 col-3">
                                                <label for="" class="form-label">Start Date</label>
                                                <input type="date" class="form-control" id="from_date" name="from_date"
                                                    value="<?=  $customer->from_date ?>" disabled>
                                            </div>
                                            <div class="mb-3 col-3">
                                                <label for="" class="form-label">Days</label>
                                                <input type="text" class="form-control" id="days" name="days"
                                                    value="<?=  $days ?>" disabled>
                                            </div>
                                            <div class="mb-3 col-3">
                                                <label for="" class="form-label">Night</label>
                                                <input type="text" class="form-control" id="night" name="night"
                                                    value="<?=  $nights ?>" disabled>
                                            </div>
                                            <!-- <div class="mb-3 col-3">
                                                <label for="" class="form-label">Date</label>
                                                <input type="date" class="form-control" id="date" name="date"
                                                    value="<?php echo date('Y-m-d'); ?>">

                                            </div> -->
                                        </div>
                                        <hr>
                                        <h4>Comments or Notes</h4>
                                        <div class="row">
                                            <div class="mb-3 col-3">
                                                <label for="" class="form-label">Comments</label>
                                                <input type="text" class="form-control" id="note" name="note"
                                                    placeholder="Add Comments or Notes" value="<?= $comments;  ?>">
                                            </div>
                                            <div class="mb-3 col-3">
                                                <label for="" class="form-label">Image <span
                                                        style="color: red;">*</span></label>
                                                <select name="destination_image" class="form-control" required>
                                                    <?php 
                                                      $stmt = $mysqli->prepare("SELECT * from  destination_images  order by id desc");
                                                        $stmt->execute();
                                                        $res = $stmt->get_result();
                                                        $sno = 1;
                                                        while ($row = $res->fetch_assoc()) { ?>
                                                    <option value="<?= $row['image']  ?>"><?= $row['name'] ?></option>
                                                    <?php } ?>
                                                </select>
                                            </div>
                                        </div>

                                    </div>
                                    <div class="tab-pane fade" id="transport-tab-pane" role="tab-panel"
                                        aria-labelledby="transport-tab" tabindex="0">
                                        <h4>Transport Details</h4>
                                        <div id="transportFormRows">
                                            <?php foreach ($transport_details as $index => $transport): ?>

                                            <div class="row transportFormRow mb-3" data-index="<?= $index ?>">
                                                <div class="mb-3 col-3">
                                                    <label class="form-label">Transport Mode</label>
                                                    <select name="transport_details[<?= $index ?>][transport_mode]"
                                                        class="form-control">
                                                        <option value="" disabled>Select Mode</option>
                                                        <option value="flight"
                                                            <?= $transport['transport_mode'] === 'flight' ? 'selected' : '' ?>>
                                                            Flight</option>
                                                        <option value="train"
                                                            <?= $transport['transport_mode'] === 'train' ? 'selected' : '' ?>>
                                                            Train</option>
                                                        <option value="bus"
                                                            <?= $transport['transport_mode'] === 'bus' ? 'selected' : '' ?>>
                                                            Bus</option>
                                                        <option value="taxi"
                                                            <?= $transport['transport_mode'] === 'taxi' ? 'selected' : '' ?>>
                                                            Taxi</option>
                                                    </select>
                                                </div>

                                                <div class="mb-3 col-3">
                                                    <label class="form-label">Transport Name</label>
                                                    <input type="text" class="form-control"
                                                        name="transport_details[<?= $index ?>][transport_name]"
                                                        value="<?= htmlspecialchars($transport['transport_name']) ?>">
                                                </div>

                                                <div class="mb-3 col-3">
                                                    <label class="form-label">From</label>
                                                    <input type="text" class="form-control"
                                                        name="transport_details[<?= $index ?>][transport_from]"
                                                        value="<?= htmlspecialchars($transport['from_location']) ?>">
                                                </div>

                                                <div class="mb-3 col-3">
                                                    <label class="form-label">To</label>
                                                    <input type="text" class="form-control"
                                                        name="transport_details[<?= $index ?>][transport_to]"
                                                        value="<?= htmlspecialchars($transport['to_location']) ?>">
                                                </div>

                                                <div class="mb-3 col-3">
                                                    <label class="form-label">Departure Time</label>
                                                    <input type="time" class="form-control"
                                                        name="transport_details[<?= $index ?>][departure_time]"
                                                        value="<?= $transport['departure_time'] ?>">
                                                </div>

                                                <div class="mb-3 col-3">
                                                    <label class="form-label">Arrival Time</label>
                                                    <input type="time" class="form-control"
                                                        name="transport_details[<?= $index ?>][arrival_time]"
                                                        value="<?= $transport['arrival_time'] ?>">
                                                </div>

                                                <div class="mb-3 col-3">
                                                    <label class="form-label">Price</label>
                                                    <input type="number" class="form-control"
                                                        name="transport_details[<?= $index ?>][price]"
                                                        value="<?= $transport['price'] ?>">
                                                </div>

                                                <div class="mb-3 col-3">
                                                    <label class="form-label">Comments</label>
                                                    <input type="text" class="form-control"
                                                        name="transport_details[<?= $index ?>][comment]"
                                                        value="<?= htmlspecialchars($transport['comment']) ?>">
                                                </div>

                                                <div class="mb-3 col-3">
                                                    <label class="form-label">Class</label>
                                                    <input type="text" class="form-control"
                                                        name="transport_details[<?= $index ?>][class]"
                                                        value="<?= htmlspecialchars($transport['transport_class']) ?>">
                                                </div>

                                                <div class="mb-3 col-3">
                                                    <label class="form-label">Departure Date </label>
                                                    <input type="date" class="form-control"
                                                        name="transport_details[<?= $index ?>][travel_date]"
                                                        value="<?= htmlspecialchars($transport['travel_date']) ?>">
                                                </div>

                                                <div class="mb-3 col-3">
                                                    <label class="form-label">Arrival Date </label>
                                                    <input type="date" class="form-control"
                                                        name="transport_details[<?= $index ?>][arrival_date]"
                                                        value="<?= htmlspecialchars($transport['arrival_date']) ?>">
                                                </div>

                                                <div class="mb-3 col-3">
                                                    <label class="form-label">Travel Person</label>
                                                    <input type="number" class="form-control"
                                                        name="transport_details[<?= $index ?>][travel_person]"
                                                        value="<?= htmlspecialchars($transport['travel_person']) ?>">
                                                </div>
                                                <div class="mb-3 col-3">
                                                    <label class="form-label">Travel Baggage</label>
                                                    <input type="text" class="form-control"
                                                        name="transport_details[<?= $index ?>][travel_Baggage]"
                                                        value="<?= htmlspecialchars($transport['travel_baggage']) ?>">
                                                    <span>Note: Enter Weight in KG</span>
                                                </div>
                                            </div>


                                            <?php endforeach; ?>

                                            <div class="col-md-2 d-flex ms-auto">
                                                <button class="btn btn-success" type="submit"
                                                    name="BtnSubmit">Submit</button>
                                            </div>
                                        </div>

                                        <button type="button" id="addTransportBtn" class="btn btn-primary mb-3">+ Add
                                            Transport</button>
                                    </div>
                                    <div class="tab-pane fade" id="contact-tab-pane" role="tabpanel"
                                        aria-labelledby="contact-tab" tabindex="0">
                                        <h4>Hotel</h4>
                                        <div class="row">
                                            <span>
                                                <button type="button" class="btn btn-info showHotelForm"> <i
                                                        class="fa-solid fa-plus"></i> Add Hotel <span
                                                        class="btn-icon-end"><i class="fa fa-check"></i></span>
                                                </button>
                                            </span>
                                            <div class="mb-3 ">
                                                <label for="">Pakage Type</label>
                                                <input type="text" name="pakage_type" class="form-control" required
                                                    value="<?php echo $pakage_type; ?>">
                                            </div>
                                        </div>

                                        <br>
                                        <br>
                                        <div class="row hotelForm" style="display: none;">
                                            <div id="hotelFormRows">
                                                <?php foreach ($hotel_quotes as $index => $hotel): ?>
                                                <div class="row mb-3 hotelRow" data-index="<?= $index ?>">

                                                    <div class="mb-3 col-2">
                                                        <label class="form-label">Hotel</label>
                                                        <select name="hotel_rows[<?= $index ?>][hotel]"
                                                            class="form-control stay_hotel_id" required>
                                                            <option value="" disabled>Select Hotel</option>
                                                            <?php
                                            $stmt = $mysqli->prepare('SELECT * FROM hotels WHERE status = "Active"');
                                            $stmt->execute();
                                            $res = $stmt->get_result();
                                            while ($row = $res->fetch_assoc()) {
                                                $selected = ($row['hotel_id'] == $hotel['hotel_id']) ? 'selected' : '';
                                                echo "<option value='{$row['hotel_id']}' $selected>{$row['hotel_name']}</option>";
                                            }
                                            ?>
                                                        </select>
                                                    </div>

                                                    <div class="mb-3 col-3">
                                                        <label>Stay Nights</label>
                                                        <select name="hotel_rows[<?= $index ?>][dates][]"
                                                            class="form-control select2 stay_dates" required multiple>
                                                            <?php
                                                            $start = new DateTime($customer->from_date);
                                                            $end = new DateTime($customer->to_date);
                                                             $end->modify('+1 day');
                                                            
                                                            while ($start < $end) {
                                                                $value = $start->format('Y-m-d');
                                                                $display = $start->format('d-m-Y');
                                                                $selected = in_array($value, $hotel['dates']) ? 'selected' : '';
                                                                echo "<option value='$value' $selected>$display</option>";
                                                                $start->modify('+1 day');
                                                            }
                                                            ?>
                                                        </select>
                                                    </div>

                                                    <div class="mb-3 col-2">
                                                        <label>Meal Plan</label>
                                                        <select name="hotel_rows[<?= $index ?>][meal]"
                                                            class="form-control hotel_meal" required>
                                                            <option value="" disabled>Meal Type</option>
                                                            <?php
                                                            $stmt = $mysqli->prepare("SELECT * FROM meals");
                                                            $stmt->execute();
                                                            $res = $stmt->get_result();
                                                            while ($row = $res->fetch_assoc()) {
                                                                $selected = ($row['meal_id'] == $hotel['meal_id']) ? 'selected' : '';
                                                                echo "<option value='{$row['meal_id']}' $selected>{$row['meal_type']}</option>";
                                                            }
                                                            ?>
                                                        </select>
                                                    </div>

                                                    <div class="mb-3 col-2">
                                                        <label>Room Type</label>
                                                        <select name="hotel_rows[<?= $index ?>][room]"
                                                            class="form-control hotel_room" required>
                                                            <option value="" disabled>Room Type</option>
                                                            <?php
                                                                $stmt = $mysqli->prepare("SELECT * FROM rooms WHERE hotel_id = ?");
                                                                $stmt->bind_param("i", $hotel['hotel_id']);
                                                                $stmt->execute();
                                                                $res = $stmt->get_result();
                                                                while ($row = $res->fetch_assoc()) {
                                                                    $selected = ($row['room_id'] == $hotel['room_id']) ? 'selected' : '';
                                                                    echo "<option value='{$row['room_id']}' $selected>{$row['room_type']} ({$row['room_price']})</option>";
                                                                }
                                                                ?>
                                                        </select>
                                                    </div>

                                                    <div class="mb-3 col-2">
                                                        <label>No. of Rooms</label>
                                                        <input type="number"
                                                            name="hotel_rows[<?= $index ?>][no_of_rooms]"
                                                            class="form-control" value="<?= $hotel['no_of_rooms'] ?>"
                                                            min="1" required>
                                                    </div>

                                                    <div class="mb-3 col-1 d-flex align-items-end">
                                                        <button type="button"
                                                            class="btn btn-danger removeRow">X</button>
                                                    </div>

                                                </div>

                                                <?php endforeach; ?>
                                            </div>
                                            <div class="col-2">
                                                <button type="button" id="addMoreBtn" class="btn btn-primary">
                                                    + Hotel</button>
                                            </div>
                                        </div>


                                        <table class="table table-bordered table-striped mt-3">
                                            <thead>
                                                <tr>
                                                    <th>Hotel Name</th>
                                                    <th>No. of Rooms</th>
                                                    <th>Price per Room</th>
                                                    <th>Total Room Amount (₹)</th>

                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php $total_amount = 0; foreach ($hotel_dates as $hotel_id => $dates): 
                                                    $unique_days   = count($dates);
                                                    $no_of_rooms   = $hotel_totals[$hotel_id]['no_of_rooms'];
                                                    $price_per_room = $hotel_totals[$hotel_id]['price_per_room'];
                                                    $room_amount   = $no_of_rooms * $price_per_room * $unique_days;
                                                    $hotel_name    = isset($hotel_name_map[$hotel_id]) ? $hotel_name_map[$hotel_id] : 'Unknown Hotel';
                                                ?>
                                                <tr>
                                                    <td><?= $hotel_name ?></td>
                                                    <td><?= $no_of_rooms ?></td>
                                                    <td>₹ <?= $price_per_room ?></td>

                                                    <td><b>₹ <?= $room_amount ?></b></td>

                                                </tr>
                                                <?php $total_amount += $room_amount; endforeach; ?>
                                            </tbody>
                                            <tfoot>
                                                <tr>
                                                    <td colspan="3"></td>
                                                    <td>Total Amount: <b>₹ <?= $total_amount;  ?> </b></td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                    <div class="tab-pane fade" id="place-tab-pane" role="tabpanel"
                                        aria-labelledby="place-tab" tabindex="0">
                                        <h4>Place Visit</h4>


                                        <div class="row placeQuoteRow" style="">
                                            <div class="col-12">
                                                <div class="card">
                                                    <div class="card-body">
                                                        <div class="row ">

                                                            <div class="mb-3 col-3">
                                                                <label for="">Hotel</label>
                                                                <select name="hotel_id" id="hotel_id" name="hotel_name"
                                                                    class="form-control">

                                                                </select>
                                                            </div>


                                                            <div class="mb-3 col-3">
                                                                <label>Plan dates</label>
                                                                <select name="date" id="hotel_dates" name="hotel_date"
                                                                    class="form-control select2">

                                                                </select>
                                                            </div>
                                                            <div class="mb-3 col-3">
                                                                <label for="">Place</label>
                                                                <select name="place_name" id="place_id"
                                                                    class="form-control">
                                                                    <option value="" disabled selected>Place</option>
                                                                    <?php
                                    $stmt = $mysqli->prepare('SELECT * FROM country_landmarks');
                                    $stmt->execute();
                                    $res = $stmt->get_result();
                                    while ($row = $res->fetch_assoc()) {
                                      echo   "<option value='{$row['id']}'>{$row['place']}</option>";
                                    }
                                ?>
                                                                </select>
                                                            </div>

                                                            <div class="mb-3 col-3 d-flex align-items-end">
                                                                <button type="button" class="btn btn-primary"
                                                                    id="addToTableBtn">Add</button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                            </div>
                                        </div>


                                        <div class="card tablePalaces" style="display:none">
                                            <div class="card-body">
                                                <div class="row">
                                                    <form action="" method="post">
                                                        <table class="table table-bordered mt-3" id="quoteTable">
                                                            <thead>
                                                                <tr>
                                                                    <th>Hotel</th>
                                                                    <th>Plan Dates</th>
                                                                    <th>Place</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody></tbody>
                                                            <tfoot>
                                                                <div class="text-end">
                                                                    <button class="btn btn-success" type="submit"
                                                                        name="submitPlace">Submit</button>
                                                                </div>
                                                            </tfoot>
                                                        </table>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="table-responsive">
                                            <table id="example" class="display" style="min-width: 845px">
                                                <thead>
                                                    <tr>
                                                        <th>#</th>
                                                        <th>Date</th>
                                                        <th>Title</th>
                                                        <th>Place</th>
                                                        <th>Description</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php $sr_no=1; foreach($place_description as $group): ?>
                                                    <?php  foreach($group as $row): ?>
                                                    <tr>
                                                        <td><?= $sr_no; ?></td>
                                                        <td><?= $row['date'] ?></td>
                                                        <td><?= $row['title'] ?></td>
                                                        <td><?= $row['place'] ?></td>
                                                        <td><?= $row['description'] ?></td>
                                                    </tr>
                                                    <?php $sr_no++;  endforeach; ?>
                                                    <?php  endforeach; ?>

                                                </tbody>

                                            </table>
                                        </div>
                                    </div>
                                    <div class="text-end mt-5">
                                        <input type="hidden" name="customer_id" value="<?php echo $customer->id; ?>">
                                        <input type="hidden" name="id" value="<?php echo $_GET['id']; ?>">
                                        <input type="hidden" name="q_id" value="<?php echo $_GET['q_id']; ?>">

                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>


            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <ul class="nav nav-tabs" id="myTab" role="tablist">
                            <li id="policy_list" class="nav-item" role="presentation" style="">
                                <button class="nav-link" id="policy-tab" data-bs-toggle="tab"
                                    data-bs-target="#policy-tab-pane" type="button" role="tab"
                                    aria-controls="policy-tab-pane" aria-selected="false">Quotetaions
                                    policy
                                </button>
                            </li>
                        </ul>
                        <div class="tab-pane fade" id="policy-tab-pane" role="tabpanel" aria-labelledby="policy-tab"
                            tabindex="0" style="">
                            <form action="" method="post">
                                <div class="row align-items-center">
                                    <br>
                                    <br>
                                    <h4>Add Quotations policy</h4>
                                    <br>
                                    <div class="col-6 col-md-3">
                                        <label for="quotation_id">Select Inclusion</label>
                                        <select name="inclusions[]" id="" class="form-control select2" multiple>
                                            <?php  
                                                    $stmt = $mysqli->prepare("SELECT * FROM inclusions");
                                                    if ($stmt->execute()) {
                                                        $res = $stmt->get_result();
                                                        while($row = $res->fetch_assoc()):
                                                ?>
                                            <option value="<?php echo ($row['id']); ?>">
                                                <?php echo ($row['title']); ?>
                                            </option>
                                            <?php 
                                                        endwhile;
                                                    } else {
                                                        echo '<option value="">Error fetching data</option>';
                                                    }
                                                ?>
                                        </select>
                                    </div>


                                    <div class="col-6 col-md-3">
                                        <label for="quotation_id">Select Exclusions</label>
                                        <select name="exclusions[]" id="" class="form-control select2" multiple>
                                            <?php  
                                                    $stmt = $mysqli->prepare("SELECT * FROM exclusions");
                                                    if ($stmt->execute()) {
                                                        $res = $stmt->get_result();
                                                        while($row = $res->fetch_assoc()):
                                                ?>
                                            <option value="<?php echo ($row['id']); ?>">
                                                <?php echo ($row['title']); ?>
                                            </option>
                                            <?php 
                                                        endwhile;
                                                    } else {
                                                        echo '<option value="">Error fetching data</option>';
                                                    }
                                                ?>
                                        </select>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <label for="quotation_id">Select Imp Notes</label>
                                        <select name="imp_notes[]" id="" class="form-control select2" multiple>
                                            <?php  
                                                    $stmt = $mysqli->prepare("SELECT * FROM important_notes");
                                                    if ($stmt->execute()) {
                                                        $res = $stmt->get_result();
                                                        while($row = $res->fetch_assoc()):
                                                ?>
                                            <option value="<?php echo ($row['id']); ?>">
                                                <?php echo ($row['category']); ?>
                                            </option>
                                            <?php 
                                                        endwhile;
                                                    } else {
                                                        echo '<option value="">Error fetching data</option>';
                                                    }
                                                ?>
                                        </select>

                                    </div>
                                    <div class="col-3">
                                        <button type="submit" name="submitBtn" class="btn btn-success">Submit</button>
                                    </div>

                                </div>

                            </form>


                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">

                <?php if(isset($inclusions_array) && isset($exclusions_array) && isset($imp_notes_array) && isset($imp_notes_content_array)): ?>
                <div>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Inclusions</th>
                                <th>Exclusions</th>
                                <th>Important Notes</th>
                                <th>Important Note Content</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                                    $max_count = max(
                                        count($inclusions_array),
                                        count($exclusions_array),
                                        count($imp_notes_array),
                                        count($imp_notes_content_array)
                                    );
                                        $sr_no = 1;
                                        for($i = 0; $i < $max_count; $i++):
                                    ?>
                            <tr>
                                <td><?php echo $sr_no;  ?></td>
                                <td><?= $inclusions_array[$i] ?? '-' ?></td>
                                <td><?= $exclusions_array[$i] ?? '-' ?></td>
                                <td><?= $imp_notes_array[$i] ?? '-' ?></td>
                                <td><?= $imp_notes_content_array[$i] ?? '-' ?></td>
                            </tr>
                            <?php $sr_no++; endfor; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <p>No Quote Policy added available for this quote.</p>
                <?php endif; ?>
            </div>

        </div>

    </div>
</div>

<?php include "Layouts/Footer.php"  ?>
<script>
$(document).ready(function() {
    $(".showHotelForm").click(function() {
        $(".hotelForm").toggle();
    });
});


$(document).ready(function() {
    $("#quotetaion_id").on("click", function() {
        var quote_id = $(this).val();

        if (quote_id !== "") {
            $.ajax({
                url: 'ajax/getTable.php',
                type: 'POST',
                data: {
                    quote_id: quote_id
                },
                success: function(response) {

                    $(".conveyance_table tbody").html(response);
                }
            });
        } else {
            $(".conveyance_table tbody").html("");
        }
    });
});

var BASE_PATH = "<?= BASE_PATH; ?>";

$(document).ready(function() {
    $("#quotetaion_id").on("click", function() {
        var quote_id = $(this).val();

        if (quote_id !== "") {

            var query_id = "<?php echo $_GET['q_id']; ?>";
            var id = "<?php echo $_GET['id']; ?>";

            var newHref = "create_pdf.php?quote_id=" + quote_id + "&query_id=" + query_id + "&id=" + id;

            $("#generate_pdf").attr("href", newHref);
        }
    });
});


$(document).ready(function() {
    let transportIndex = 1;

    $("#addTransportBtn").click(function() {
        let newRow = $("#transportFormRows .transportFormRow:first").clone();

        newRow.attr("data-index", transportIndex);
        newRow.find("input, select").each(function() {
            let name = $(this).attr("name");
            if (name) {

                name = name.replace(/\[\d+\]/, "[" + transportIndex + "]");
                $(this).attr("name", name);
                $(this).val("");
            }
        });

        $("#transportFormRows").append(newRow);
        transportIndex++;
    });
});


$(document).ready(function() {
    let rowIndex = 1;

    $(".select2").select2();

    $("#addMoreBtn").click(function() {

        $("#hotelFormRows .row:first").find("select.select2").select2('destroy');
        let newRow = $("#hotelFormRows .row:first").clone();

        newRow.find("select").val("");
        newRow.find("input").val("");


        newRow.find("select, input").each(function() {
            let name = $(this).attr("name");
            if (name) {
                name = name.replace(/\[\d+\]/, "[0]");
                name = name.replace(/\[0\]/, "[" + rowIndex + "]");
                $(this).attr("name", name);
            }
        });

        $("#hotelFormRows").append(newRow);

        $(".select2").select2();
        rowIndex++;
    });

    $(document).on("click", ".removeRow", function() {
        let totalRows = $("#hotelFormRows .row").length;
        if ($(this).closest(".row").index() === 0) {
            alert("You cannot remove the first row.");
        } else {
            $(this).closest(".row").remove();
        }
    });
});

$(document).ready(function() {
    $(document).on("change", ".stay_hotel_id", function() {
        let hotel_id = $(this).val();
        let $row = $(this).closest('.hotelRow');
        let $mealDropdown = $row.find('.hotel_meal');
        let $roomDropdown = $row.find('.hotel_room');

        if (hotel_id) {
            // Fetch Meal Plan
            $.ajax({
                url: "ajax/getMeal.php",
                method: "POST",
                data: {
                    hotel_id: hotel_id
                },
                success: function(data) {
                    $mealDropdown.html(data);
                }
            });

            // Fetch Room Type (if needed)
            $.ajax({
                url: "ajax/getRoom.php", // Replace with your actual file
                method: "POST",
                data: {
                    hotel_id: hotel_id
                },
                success: function(data) {
                    $roomDropdown.html(data);
                }
            });
        }
    });
})

$(document).ready(function() {
    function handleQuotation() {
        var quote_id =<?php echo $quote_id; ?>;

        $("#generate_pdf").show();
        $("#view_quote").show();
        $("#policy_list").show();
        $("#policy-tab-pane").show();

        if (quote_id !== "") {

            var query_id = "<?php echo $_GET['q_id']; ?>";
            var id = "<?php echo $_GET['id']; ?>";

            var newHref = "create_pdf.php?quote_id=" + quote_id + "&query_id=" + query_id + "&id=" + id;
            $("#generate_pdf").attr("href", newHref);
            var newHref2 = "view_quotetaions.php?quote_id=" + quote_id + "&q_id=" + query_id + "&id=" + id;
            $("#view_quote").attr("href", newHref2);
        }

        if (quote_id) {
            $(".placeQuoteRow").show();

            $.ajax({
                url: "ajax/getHotel.php",
                method: "POST",
                data: {
                    quote_id: quote_id
                },
                success: function(data) {
                    $("#hotel_id").html(data);
                }
            });

        } else {
            $(".placeQuoteRow").hide();
        }
    }

    // On click
    $("#quotetaion_id").on("click", handleQuotation);

    // Also run on page load
    handleQuotation();
});



$(document).ready(function() {
    $("#hotel_id").on("click", function() {
        let hotel_quote_id = $(this).find(":selected").data('id');
        if (hotel_quote_id) {
            $.ajax({
                url: "ajax/getDates.php",
                method: "POST",
                data: {
                    hotel_quote_id: hotel_quote_id
                },
                success: function(data) {
                    console.log(data);
                    $("#hotel_dates").html(data);

                }
            });
        }
    })
})

$(document).ready(function() {
    $("#addToTableBtn").on("click", function() {
        var hotelId = $("#hotel_id").val();
        var planDateId = $("#hotel_dates").val();
        var planDateText = $("#hotel_dates option:selected").text();
        var placeId = $("#place_id").val();
        var placeText = $("#place_id option:selected").text();
        var hotelText = $("#hotel_id option:selected").text();

        if (!hotelId || !planDateId || !placeId) {
            alert("Please select all fields before adding.");
            return;
        }


        var hotelInput = $('<input type="text" readonly class="form-control mb-1">').val(hotelText);
        var hotelHidden = $('<input type="hidden">').attr("name", "hotel_id[]").val(hotelId);
        var hotelCell = $("<td></td>").append(hotelInput).append(hotelHidden);


        var dateInput = $('<input type="text" readonly class="form-control mb-1">').val(planDateText);
        var dateHidden = $('<input type="hidden">').attr("name", "plan_date_id[]").val(planDateId);
        var dateCell = $("<td></td>").append(dateInput).append(dateHidden);


        var placeInput = $('<input type="text" readonly class="form-control mb-1">').val(placeText);
        var placeHidden = $('<input type="hidden">').attr("name", "place_id[]").val(placeId);
        var placeCell = $("<td></td>").append(placeInput).append(placeHidden);

        var row = $("<tr></tr>");
        row.append(hotelCell);
        row.append(dateCell);
        row.append(placeCell);


        $("#quoteTable tbody").append(row);
        $(".tablePalaces").show();


        $("#hotel_id").val('');
        $("#hotel_dates").val(null).trigger('change');
        $("#place_id").val('');
    });

});
</script>