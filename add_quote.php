<?php
include "Layouts/Header.php";
include "Layouts/Sidebar.php"; 

    if (isset($_POST['BtnSubmit']))
    {
        
        $query_id = $_GET['q_id'];
        $customer_id = $_POST['customer_id'];
        $comment = $_POST['note'] ?? ''; 
        $created_at = date('Y-m-d H:i:s');
        $date = $_POST['date'];
        $pakage_type = $_POST['pakage_type'];
        $image = $_POST['destination_image'];

        
        try 
        
        {
            $mysqli->begin_transaction();
            
            $master_quote = $mysqli->prepare("INSERT INTO quote_master (customer_id, pakage_type , query_id, date, comment,image, created_at) VALUES (? ,?, ?, ?, ?, ?, ?)");
            $master_quote->bind_param('isissss', $customer_id, $pakage_type , $query_id, $date, $comment, $image, $created_at);
            $master_quote->execute();
            $quote_id = $master_quote->insert_id;
            $master_quote->close();

            // echo "<pre>";
            // print_r($_POST); exit;
            
            $hotel_rows = $_POST['hotel_rows'];
            
           foreach ($hotel_rows as $row) 
        {
            $hotel_id    = $row['hotel'];
            $meal_id     = $row['meal'];
            $room_id     = $row['room'];
            $rooms_count = $row['no_of_rooms'];
            $created_at  = date('Y-m-d H:i:s');

            // Get room price
            $stmt = $mysqli->prepare("SELECT room_price FROM rooms WHERE room_id = ?");
            $stmt->bind_param("i", $room_id);
            $stmt->execute();
            $stmt->bind_result($room_price);
            $stmt->fetch();
            $stmt->close();

            // Get meal price
            $stmt = $mysqli->prepare("SELECT meal_plan_price FROM meals WHERE meal_id = ?");
            $stmt->bind_param("i", $meal_id);
            $stmt->execute();
            $stmt->bind_result($meal_plan_price);
            $stmt->fetch();
            $stmt->close();

            // Example total price calculation
            

            // Insert hotel quote with total price (you can store meal_price separately too if needed)
            $stmt = $mysqli->prepare("INSERT INTO hotel_quote (quote_id, query_id, hotel_id, meal_id, room_id, no_of_rooms, price, meal_prize ,created_at) VALUES (?, ?, ?, ?, ?, ?, ?,?, ?)");
            $stmt->bind_param("iiiiiidss", $quote_id, $query_id, $hotel_id, $meal_id, $room_id, $rooms_count, $room_price, $meal_plan_price ,$created_at);
            $stmt->execute();
            $hotel_quote_id = $stmt->insert_id;
            $stmt->close();

            // Insert hotel quote dates
            if (!empty($row['dates'])) {
                $stmt_dates = $mysqli->prepare("INSERT INTO hotel_quote_dates (hotel_quote_id, date) VALUES (?, ?)");
                foreach ($row['dates'] as $date) {
                    $stmt_dates->bind_param("is", $hotel_quote_id, $date);
                    $stmt_dates->execute();
                }
                $stmt_dates->close();
            }
        }


            if (isset($_POST['transport_details'])) 
            {
                foreach ($_POST['transport_details'] as $transport) 
                {
                    $from_location    = $transport['transport_from'];
                    $to_location      = $transport['transport_to'];
                    $transport_mode   = $transport['transport_mode'];
                    $transport_name   = $transport['transport_name'];
                    $departure_time   = $transport['departure_time'];
                    $arrival_time     = $transport['arrival_time'];
                    $arrival_date     = $transport['arrival_date'];
                    $price            = $transport['price'];
                    $transport_comment= $transport['comment'];
                    $transport_class =  $transport['class']; 
                    $travel_date =  $transport['travel_date']; 
                    $travel_person =  $transport['travel_person']; 
                    $travel_Baggage =  $transport['travel_Baggage']; 


                    $stmt = $mysqli->prepare("INSERT INTO conveyance (quote_id, from_location, to_location, transport_mode, transport_name, departure_time, arrival_time, price, comment ,transport_class,travel_date,travel_person,travel_baggage,arrival_date) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
                    $stmt->bind_param("issssssssssiss", $quote_id, $from_location, $to_location, $transport_mode, $transport_name, $departure_time, $arrival_time, $price, $transport_comment,$transport_class,$travel_date,$travel_person,$travel_Baggage,$arrival_date);
                    $stmt->execute();
                    $stmt->close();
                }
            }
                $mysqli->commit();
                $_SESSION['alert'] = [
                'title' => 'Quote Successfully Saved',
                'text' => 'Quote Save',
                'icon' => 'success'
                ];
                redirect("?id=".$_GET['id']."&q_id=".$_GET['q_id']);
        }
        catch (Throwable $e)
        {
            $mysqli->rollback();
            alert($e->getMessage(), "error", "error");
            redirect("?id=".$_GET['id']."&q_id=".$_GET['q_id']);
        }

    }
        if(isset($_GET['id']) && isset($_GET['q_id']))
        {

        try{
            $id = intval($_GET['id']);
           $stmt = $mysqli->prepare("SELECT query_mst.*, customers.*, destinations.name as destination_name 
    FROM query_mst
    JOIN customers ON customers.id = query_mst.customer_id
    LEFT JOIN destinations ON destinations.id = query_mst.destination
    WHERE query_mst.customer_id = ? AND query_mst.id = ?");

            $stmt->bind_param("ii", $id , $_GET['q_id']);
            $stmt->execute();
            $result = $stmt->get_result();
            $customer = $result->fetch_object();

            $start = new DateTime($customer->from_date);
            $end = new DateTime($customer->to_date);

            $interval = $start->diff($end);
            $days = $interval->days + 1; 
            $nights = $days - 1;


            // echo "<pre>";
            // print_r($customer); exit;

            }
            catch (Exception $e)
            {
                alert($e->getMessage(), "error", "error");
              redirect("?id=" . $_GET['id'] . "&q_id=" . $_GET['q_id']);
            }

        }

        if (isset($_GET['q_id'])) {
            $q_id = $_GET['q_id'];
            $stmt = $mysqli->prepare("SELECT * FROM quote_master WHERE query_id = ?");
            $stmt->bind_param("i", $q_id); 
            $stmt->execute();
            $quote_result = $stmt->get_result();
        
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
            redirect("?id=" . $_GET['id'] . "&q_id=" . $_GET['q_id']);
        }
    }




?>
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
                                        <?= $customer->from_date; ?>
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
                                <!-- <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="quote-tab" data-bs-toggle="tab"
                                        data-bs-target="#quote-tab-pane" type="button" role="tab"
                                        aria-controls="quote-tab-pane" aria-selected="false">Place Visits</button>
                                </li> -->

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
                                                    placeholder="Enter your full name" value="<?=  $customer->name ?>" readonly>
                                            </div>
                                            <div class="mb-3 col-3">
                                                <label for="" class="form-label">Number</label>
                                                <input type="number" class="form-control" id="number" name="number"
                                                    placeholder="Enter your number" value="<?=  $customer->number ?>" readonly>
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
                                                    placeholder="Add Comments or Notes" >
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
                                            <div class="row transportFormRow mb-3" data-index="0">
                                                <div class="mb-3 col-3">
                                                    <label class="form-label">Transport Mode <span
                                                            style="color: red;">*</span></label>
                                                    <select name="transport_details[0][transport_mode]"
                                                        class="form-control" required>
                                                        <option value="" selected disabled>Select Mode</option>
                                                        <option value="flight">Flight</option>
                                                        <option value="train">Train</option>
                                                        <option value="bus">Bus</option>
                                                        <option value="taxi">Taxi</option>
                                                    </select>
                                                </div>

                                                <div class="mb-3 col-3">
                                                    <label class="form-label">Transport Name <span
                                                            style="color: red;">*</span></label>
                                                    <input type="text" class="form-control"
                                                        name="transport_details[0][transport_name]" required>
                                                </div>

                                                <div class="mb-3 col-3">
                                                    <label class="form-label">From <span
                                                            style="color: red;">*</span></label>
                                                    <input type="text" class="form-control"
                                                        name="transport_details[0][transport_from]" required>
                                                </div>

                                                <div class="mb-3 col-3">
                                                    <label class="form-label">To <span
                                                            style="color: red;">*</span></label>
                                                    <input type="text" class="form-control"
                                                        name="transport_details[0][transport_to]" required>
                                                </div>

                                                <div class="mb-3 col-3">
                                                    <label class="form-label">Departure Date <span
                                                            style="color: red;">*</span></label>
                                                    <input type="date" class="form-control"
                                                        name="transport_details[0][travel_date]" required>
                                                </div>



                                                <div class="mb-3 col-3">
                                                    <label class="form-label">Departure Time <span
                                                            style="color: red;">*</span></label>
                                                    <input type="time" class="form-control"
                                                        name="transport_details[0][departure_time]" required>
                                                </div>
                                                 <div class="mb-3 col-3">
                                                    <label class="form-label">Arrival Date <span
                                                            style="color: red;">*</span></label>
                                                    <input type="date" class="form-control"
                                                        name="transport_details[0][arrival_date]" required>
                                                </div>
                                                <div class="mb-3 col-3">
                                                    <label class="form-label">Arrival Time <span
                                                            style="color: red;">*</span></label>
                                                    <input type="time" class="form-control"
                                                        name="transport_details[0][arrival_time]" required>
                                                </div>

                                                <div class="mb-3 col-3">
                                                    <label class="form-label">Price <span
                                                            style="color: red;">*</span></label>
                                                    <input type="number" class="form-control"
                                                        name="transport_details[0][price]" required>
                                                </div>

                                               
                                                <div class="mb-3 col-3">
                                                    <label class="form-label">Class <span
                                                            style="color: red;">*</span></label>
                                                    <input type="text" class="form-control"
                                                        name="transport_details[0][class]" required>
                                                </div>
                                                
                                               
                                                <div class="mb-3 col-3">
                                                    <label class="form-label">Travel Person <span
                                                            style="color: red;">*</span></label>
                                                    <input type="number" class="form-control"
                                                        name="transport_details[0][travel_person]" required>
                                                </div>
                                                <div class="mb-3 col-3">
                                                    <label class="form-label">Travel Baggage <span
                                                            style="color: red;">*</span></label>
                                                    <input type="text" class="form-control"
                                                        name="transport_details[0][travel_Baggage]" required>
                                                    <span>Note: Enter Weight in KG</span>
                                                </div>
                                                 <div class="mb-3 col-3">
                                                    <label class="form-label">Comments</label>
                                                    <input type="text" class="form-control"
                                                        name="transport_details[0][comment]">
                                                </div>
                                            </div>
                                        </div>

                                        <button type="button" id="addTransportBtn" class="btn btn-primary mb-3">+ Add
                                            Transport</button>
                                        <div class="row">
                                            <div class="col-md-2 d-flex ms-auto">
                                                <button class="btn btn-success" type="submit"
                                                    name="BtnSubmit">Submit</button>
                                            </div>

                                        </div>
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
                                                <label for="">Pakage Type <span style="color: red;">*</span></label>
                                                <input type="text" name="pakage_type" class="form-control" required>
                                            </div>


                                        </div>


                                        <br>
                                        <br>
                                        <div class="row hotelForm" style="display: none;">
                                            <div id="hotelFormRows">

                                                <div id="hotelContainer">
                                                    <div class="row mb-3 hotelRow">

                                                        <div class="mb-3 col-2">
                                                            <label class="form-label">Hotel <span
                                                                    style="color: red;">*</span></label>
                                                            <select name="hotel_rows[0][hotel]"
                                                                class="form-control stay_hotel_id" required>
                                                                <option value="" disabled selected>Select Hotel
                                                                </option>
                                                                <?php
                                        $stmt = $mysqli->prepare('SELECT * FROM hotels WHERE status = "Active"');
                                        $stmt->execute();
                                        $res = $stmt->get_result();
                                        while ($row = $res->fetch_assoc()) {
                                            echo "<option value='{$row['hotel_id']}'>{$row['hotel_name']}</option>";
                                        }
                                        ?>
                                                            </select>
                                                        </div>

                                                        <div class="mb-3 col-3">
                                                            <label>Stay Nights <span
                                                                    style="color: red;">*</span></label>
                                                            <select name="hotel_rows[0][dates][]"
                                                                class="form-control select2" required multiple>
                                                                <?php
                                        $start = new DateTime($customer->from_date);
                                        $end = new DateTime($customer->to_date);
                                        $end->modify('+1 day');
                                    
                                        while ($start < $end) {
                                            $value = $start->format('Y-m-d');
                                            $display = $start->format('d-m-Y');
                                            echo "<option value='$value'>$display</option>";
                                            $start->modify('+1 day');
                                        }
                                        ?>
                                                            </select>
                                                        </div>

                                                        <div class="mb-3 col-2">
                                                            <label>Meal Plan <span style="color: red;">*</span></label>
                                                            <select name="hotel_rows[0][meal]"
                                                                class="form-control hotel_meal" required>
                                                                <option value="" disabled selected>Meal Type</option>
                                                            </select>
                                                        </div>

                                                        <div class="mb-3 col-2">
                                                            <label>Room Type <span style="color: red;">*</span></label>
                                                            <select name="hotel_rows[0][room]"
                                                                class="form-control hotel_room" required>
                                                                <option value="" disabled selected>Room Type</option>
                                                            </select>
                                                        </div>

                                                        <div class="mb-3 col-2">
                                                            <label>No. of Rooms <span
                                                                    style="color: red;">*</span></label>
                                                            <input type="number" name="hotel_rows[0][no_of_rooms]"
                                                                class="form-control" min="1" required>
                                                        </div>

                                                        <div class="mb-3 col-1 d-flex align-items-end">
                                                            <button type="button"
                                                                class="btn btn-danger removeRow">X</button>
                                                        </div>

                                                    </div>
                                                </div>

                                            </div>
                                            <div class="col-2">
                                                <button type="button" id="addMoreBtn" class="btn btn-primary">
                                                    + Hotel</button>
                                            </div>

                                        </div>


                                    </div>
                                    <!-- <div class="tab-pane fade" id="quote-tab-pane" role="tabpanel"
                                        aria-labelledby="quote-tab" tabindex="0">
                                        <a href="" class="btn btn-square btn-outline-danger float-end" id="generate_pdf"
                                            style="display:none;">
                                            Create PDF</a>


                                        <h4>My Quotetaions</h4>
                                        <div class="row align-items-center">
                                            <div class="col-6 col-md-3">
                                                <select name="quotes" id="quotetaion_id" class="form-control">
                                                    <?php while($row = $quote_result->fetch_assoc()): ?>
                                                    <option value="<?php echo $row['id']; ?>">
                                                        <?php echo $row['pakage_type']; ?>
                                                    </option>
                                                    <?php endwhile; ?>
                                                </select>
                                            </div>

                                            <div class="col-auto">
                                                <a href="#" class="btn btn-square btn-outline-success" id="view_quote"
                                                    style="display:none;">
                                                    Edit Quotations
                                                </a>
                                            </div>
                                        </div>



                                    </div> -->


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

        <!-- <div class="row placeQuoteRow" style="display:none">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="row ">

                            <div class="mb-3 col-3">
                                <label for="">Hotel</label>
                                <select name="hotel_id" id="hotel_id" name="hotel_name" class="form-control">

                                </select>
                            </div>


                            <div class="mb-3 col-3">
                                <label>Plan dates</label>
                                <select name="date" id="hotel_dates" name="hotel_date" class="form-control select2">

                                </select>
                            </div>
                            <div class="mb-3 col-3">
                                <label for="">Place</label>
                                <select name="place_name" id="place_id" class="form-control">
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
                                <button type="button" class="btn btn-primary" id="addToTableBtn">Add</button>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div> -->


        <!-- <div class="card tablePalaces" style="display:none">
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
                                    <button class="btn btn-success" type="submit" name="submitPlace">Submit</button>
                                </div>
                            </tfoot>
                        </table>
                    </form>
                </div>
            </div>
        </div> -->
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
    $("#quotetaion_id").on("click", function() {
        var quote_id = $(this).val();

        $("#generate_pdf").show();
        $("#view_quote").show();
        $("#policy_list").show();
        $("#policy-tab-pane").show();

        if (quote_id !== "") {

            var query_id = "<?php echo $_GET['q_id']; ?>";
            var id = "<?php echo $_GET['id']; ?>";

            var newHref = "create_pdf.php?quote_id=" + quote_id + "&query_id=" + query_id + "&id=" + id;
            $("#generate_pdf").attr("href", newHref);
            var newHref2 = "view_quotetaions.php?quote_id=" + quote_id + "&q_id=" + query_id + "&id=" +
                id;
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
    });
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
