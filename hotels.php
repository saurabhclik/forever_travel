<?php
    include "Layouts/Header.php";
    include "Layouts/Sidebar.php";

    $hotels_query = "SELECT * FROM `hotels` ORDER BY `hotel_id` DESC";
    $hotels_result = $mysqli->query($hotels_query);

?>

<div class="content-body">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                   <div class="card-header d-flex flex-wrap justify-content-between align-items-start gap-3">
    <!-- Title -->
    <h4 class="card-title m-0">Hotels</h4>

    <!-- Buttons -->
    <div class="d-flex gap-2">
        <a href="download_hotel_sample.php" class="btn btn-outline-primary">Download Sample</a>
        <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#addHotelModal">
            Add Hotel
        </button>
    </div>

    <!-- Upload Form -->
    <form method="POST" action="upload_hotel.php" enctype="multipart/form-data" class="d-flex align-items-end gap-2 flex-wrap">
        <div>
            <label for="hotel_csv_file" class="form-label m-0">Select CSV File</label>
            <input type="file" class="form-control" name="csv_file" id="hotel_csv_file" accept=".csv" required>
        </div>
        <button type="submit" name="submit" class="btn btn-success">Upload</button>
    </form>
</div>

                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="example" class="table" style="width:100%">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Hotel Name</th>
                                        <th>Star Rating</th>
                                        <th>Location</th>
                                        <th>Contact</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                            while ($row = $hotels_result->fetch_assoc()) 
                                            { 
                                        ?>
                                    <tr>
                                        <td>
                                            <?= htmlspecialchars($row['hotel_id']) ?>
                                        </td>
                                        <td>
                                            <?= htmlspecialchars($row['hotel_name']) ?>
                                        </td>
                                        <td>
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
                                        </td>
                                        <td>
                                            <?= htmlspecialchars($row['address']) ?>
                                        </td>
                                        <td>
                                            <?= htmlspecialchars($row['phone_number']) ?>
                                        </td>
                                        <td>
                                            <?= htmlspecialchars($row['status']) ?>
                                        </td>
                                        <td>
                                            <a href="view-hotel.php?id=<?= $row['hotel_id']; ?>"
                                                class="btn btn-sm btn-primary btn-sm Edit shadow btn-xs sharp">
                                                <i class="fa fa-eye"></i>
                                            </a>
                                            <!-- <button class="btn btn-sm btn-danger btn-sm Delete shadow btn-xs sharp">
                                                <i class="fa fa-trash"></i>
                                            </button> -->
                                        </td>
                                    </tr>
                                    <?php 
                                            } 
                                        ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="addHotelModal" tabindex="-1" aria-labelledby="addHotelModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addHotelModalLabel">Add New Hotel</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="functions.php" enctype="multipart/form-data">
                <input type="hidden" name="action" value="form_save">
                <input type="hidden" name="id" value="">
                <div class="modal-body">
                    <ul class="nav nav-tabs" id="hotelTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="basic-tab" data-bs-toggle="tab" data-bs-target="#basic"
                                type="button" role="tab">Basic Info</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="location-tab" data-bs-toggle="tab" data-bs-target="#location"
                                type="button" role="tab">Location</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="contact-tab" data-bs-toggle="tab" data-bs-target="#contact"
                                type="button" role="tab">Contact</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="room-tab" data-bs-toggle="tab" data-bs-target="#room"
                                type="button" role="tab">Room Details</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="meals-tab" data-bs-toggle="tab" data-bs-target="#meals"
                                type="button" role="tab">Meals</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="amenities-tab" data-bs-toggle="tab" data-bs-target="#amenities"
                                type="button" role="tab">Amenities</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="policies-tab" data-bs-toggle="tab" data-bs-target="#policies"
                                type="button" role="tab">Policies</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="media-tab" data-bs-toggle="tab" data-bs-target="#media"
                                type="button" role="tab">Media</button>
                        </li>
                    </ul>

                    <div class="tab-content p-3 border border-top-0 rounded-bottom" id="hotelTabsContent">
                        <div class="tab-pane fade show active" id="basic" role="tabpanel">
                            <div class="mb-3">
                                <label for="hotel_name" class="form-label">Hotel Name <span
                                        class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="hotel_name" name="hotel_name">
                            </div>
                            <div class="mb-3">
                                <label for="hotel_description" class="form-label">Description</label>
                                <textarea class="form-control" id="hotel_description" name="hotel_description"
                                    rows="3"></textarea>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="star_rating" class="form-label">Star Rating</label>
                                    <select class="form-select" id="star_rating" name="star_rating">
                                        <option value="1">★</option>
                                        <option value="2">★★</option>
                                        <option value="3">★★★</option>
                                        <option value="4">★★★★</option>
                                        <option value="5">★★★★★</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="status" class="form-label">Status</label>
                                    <select class="form-select" id="status" name="status">
                                        <option value="active">Active</option>
                                        <option value="inactive">Inactive</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="location" role="tabpanel">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="country" class="form-label">Country</label>
                                    <select class="form-control" id="country" name="country">
                                        <option value="" disabled selected>Select Country</option>
                                        <?php
                    $stmt = $mysqli->prepare("SELECT * FROM countries");
                    $stmt->execute();
                    $countries = $stmt->get_result();
                    while ($row = $countries->fetch_assoc()) {
                        echo '<option value="' . $row['en_short_name'] . '">' . $row['en_short_name'] . '</option>';
                    }
                ?>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="state" class="form-label">State</label>

                                    <!-- State select dropdown -->
                                    <select class="form-control" id="stateSelect" name="state">
                                        <option value="" disabled selected>Select State</option>
                                        <?php
                                            $stmt = $mysqli->prepare("SELECT DISTINCT state FROM `state_district`");
                                            $stmt->execute();
                                            $category = $stmt->get_result();
                                            while ($row = $category->fetch_assoc()) {
                                                echo '<option value="' . $row['state'] . '">' . $row['state'] . '</option>';
                                            }
                                        ?>
                                    </select>

                                    <!-- State text input (hidden and disabled by default) -->
                                    <input type="text" class="form-control d-none" id="stateInput" name="state"
                                        placeholder="Enter State" disabled>
                                </div>

                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="address" class="form-label">Address</label>
                                    <input type="text" class="form-control" id="address" name="address">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="city" class="form-label">City</label>
                                    <input type="text" class="form-control" id="city" name="city">
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="zipcode" class="form-label">Postal/Zip Code <span
                                            class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="zipcode" name="zipcode">
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label for="latitude" class="form-label">Latitude</label>
                                    <input type="text" class="form-control" id="latitude" name="latitude">
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label for="longitude" class="form-label">Longitude</label>
                                    <input type="text" class="form-control" id="longitude" name="longitude">
                                </div>
                            </div>
                        </div>


                        <div class="tab-pane fade" id="contact" role="tabpanel">
                            <div class="mb-3">
                                <label for="phone_number" class="form-label">Phone Number<span
                                        class="text-danger">*</span></label>
                                <input type="tel" class="form-control" id="phone_number" name="phone_number">
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email">
                            </div>
                            <div class="mb-3">
                                <label for="website_url" class="form-label">Website</label>
                                <input type="text" class="form-control" id="website_url" name="website_url">
                            </div>
                        </div>

                        <div class="tab-pane fade" id="room" role="tabpanel">
                            <div id="roomsContainer">
                                <div class="room-row mb-3">
                                    <div class="row align-items-end">
                                        <div class="col-md-5 mb-3">
                                            <label for="room_type_1" class="form-label">Room Type</label>
                                            <input type="text" class="form-control" id="room_type_1" name="room_types[]"
                                                placeholder="e.g., Single, Double, Suite">
                                        </div>
                                        <div class="col-md-5 mb-3">
                                            <label for="room_price_1" class="form-label">Room Price</label>
                                            <input type="number" class="form-control" id="room_price_1"
                                                name="room_prices[]" placeholder="Enter price per room">
                                        </div>
                                        <div class="col-md-2 mb-3">
                                            <button type="button" class="btn btn-danger remove-room-btn" disabled><i
                                                    class="fa fa-trash"></i></button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <button type="button" class="btn btn-primary" id="addRoomButton">Add Room </button>
                        </div>

                        <div class="tab-pane fade" id="amenities" role="tabpanel">
                            <div class="row">
                                <div class="col-md-6">
                                    <h6>General Amenities</h6>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" id="wifi" name="amenities[]"
                                            value="wifi">
                                        <label class="form-check-label" for="wifi">Free WiFi</label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" id="parking" name="amenities[]"
                                            value="parking">
                                        <label class="form-check-label" for="parking">Parking</label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" id="pool" name="amenities[]"
                                            value="pool">
                                        <label class="form-check-label" for="pool">Swimming Pool</label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" id="gym" name="amenities[]"
                                            value="gym">
                                        <label class="form-check-label" for="gym">Gym</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <h6>Room Amenities</h6>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" id="ac" name="amenities[]"
                                            value="ac">
                                        <label class="form-check-label" for="ac">Air Conditioning</label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" id="tv" name="amenities[]"
                                            value="tv">
                                        <label class="form-check-label" for="tv">TV</label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" id="minibar" name="amenities[]"
                                            value="minibar">
                                        <label class="form-check-label" for="minibar">Minibar</label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" id="safe" name="amenities[]"
                                            value="safe">
                                        <label class="form-check-label" for="safe">Safe</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="policies" role="tabpanel">
                            <div class="mb-3">
                                <label for="check_in_time" class="form-label">Check-In Time</label>
                                <input type="time" class="form-control" id="check_in_time" name="check_in_time">
                            </div>
                            <div class="mb-3">
                                <label for="check_out_time" class="form-label">Check-Out Time</label>
                                <input type="time" class="form-control" id="check_out_time" name="check_out_time">
                            </div>
                            <div class="mb-3">
                                <label for="cancellation_policy" class="form-label">Cancellation Policy</label>
                                <textarea class="form-control" id="cancellation_policy" name="cancellation_policy"
                                    rows="3"></textarea>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="media" role="tabpanel">
                            <div class="mb-3">
                                <label for="photos" class="form-label">Photos</label>
                                <input type="file" class="form-control" id="photos" name="photos[]" multiple>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="meals" role="tabpanel">
                            <div id="mealsContainer">
                                <div class="meal-row mb-3">
                                    <div class="row align-items-end">
                                        <div class="col-md-3 mb-3">
                                            <label for="meal_type_1" class="form-label">Meal Type</label>
                                            <select class="form-select" id="meal_type_1" name="meal_types[]">
                                                <option value="None">None</option>
                                                <option value="CP">CP (Breakfast)</option>
                                                <option value="MAP">MAP (Breakfast+Dinner)</option>
                                                <option value="AP">AP (All Meals)</option>
                                                <option value="Custom">Custom</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label for="meal_price_1" class="form-label">Price</label>
                                            <input type="number" class="form-control" id="meal_price_1"
                                                name="meal_prices[]" placeholder="Enter price">
                                        </div>


                                    </div>
                                    <div class="row">
                                        <div class="col-md-10 mb-3">
                                            <label for="meal_description_1" class="form-label">Description</label>
                                            <textarea class="form-control" id="meal_description_1"
                                                name="meal_descriptions[]" rows="1"></textarea>
                                        </div>
                                        <div class="col-md-1 mb-3">
                                            <button type="button" class="btn btn-danger remove-meal-btn" disabled><i
                                                    class="fa fa-trash"></i></button>
                                        </div>
                                    </div>

                                </div>
                            </div>
                            <button type="button" class="btn btn-primary" id="addMealButton">Add Meal</button>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" id="prevBtn">Previous</button>
                    <button type="button" class="btn btn-primary" id="nextBtn">Next</button>
                    <button type="submit" class="btn btn-success" id="submitBtn" style="display:none;">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php 
    include "Layouts/Footer.php";
?>
<script>
$(document).ready(function() {
    let roomCount = 1;
    let mealCount = 1;
    $('#addRoomButton').on('click', function() {
        roomCount++;
        const newRoom = `
                <div class="room-row mb-3">
                    <div class="row align-items-end">
                        <div class="col-md-5 mb-3">
                            <label for="room_type_${roomCount}" class="form-label">Room Type</label>
                            <input type="text" class="form-control" id="room_type_${roomCount}" name="room_types[]" placeholder="e.g., Single, Double, Suite">
                        </div>
                        <div class="col-md-5 mb-3">
                            <label for="room_price_${roomCount}" class="form-label">Room Price</label>
                            <input type="number" class="form-control" id="room_price_${roomCount}" name="room_prices[]" placeholder="Enter price per room">
                        </div>
                        <div class="col-md-2 mb-3">
                            <button type="button" class="btn btn-danger remove-room-btn"><i class="fa fa-trash"></i></button>
                        </div>
                    </div>
                </div>
            `;

        $('#roomsContainer').append(newRoom);
    });

    $('#roomsContainer').on('click', '.remove-room-btn', function() {
        $(this).closest('.room-row').remove();
    });

    $('#addMealButton').on('click', function() {
        mealCount++;
        const newMeal = `
                <div class="meal-row mb-3">
                    <div class="row align-items-end">
                        <div class="col-md-4 mb-3">
                            <label for="meal_type_${mealCount}" class="form-label">Meal Type</label>
                            <select class="form-select" id="meal_type_${mealCount}" name="meal_types[]">
                                <option value="None">None</option>
                                <option value="CP">CP (Breakfast)</option>
                                <option value="MAP">MAP (Breakfast+Dinner)</option>
                                <option value="AP">AP (All Meals)</option>
                                <option value="Custom">Custom</option>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="meal_price_${mealCount}" class="form-label">Price</label>
                            <input type="number" class="form-control" id="meal_price_${mealCount}" name="meal_prices[]" placeholder="Enter price">
                        </div>
                    </div>
                    <div class="row">
                                                <div class="col-md-10 mb-3">
                                                <label for="meal_description_1" class="form-label">Description</label>
                                                <textarea class="form-control" id="meal_description_${mealCount}" name="meal_descriptions[]" rows="1"></textarea>
                                            </div>   
                                            <div class="col-md-1 mb-3">
                                                <button type="button" class="btn btn-danger remove-meal-btn" disabled><i class="fa fa-trash"></i></button>
                                            </div> 
                                        </div>
                </div>
            `;

        $('#mealsContainer').append(newMeal);
        // CKEDITOR.replace(`meal_description_${mealCount}`);
    });

    $('#mealsContainer').on('click', '.remove-meal-btn', function() {
        $(this).closest('.meal-row').remove();
    });

    const tabs = [
        'basic', 'location', 'contact', 'room', 'meals',
        'amenities', 'policies', 'media'
    ];
    let currentTab = 0;

    const $prevBtn = $('#prevBtn');
    const $nextBtn = $('#nextBtn');
    const $submitBtn = $('#submitBtn');

    function updateButtons() {
        $prevBtn.toggle(currentTab !== 0);
        $nextBtn.toggle(currentTab !== tabs.length - 1);
        $submitBtn.toggle(currentTab === tabs.length - 1);
    }

    function showTab(index) {
        $('.tab-pane').removeClass('show active');
        $('.nav-link').removeClass('active');

        const tabId = tabs[index];
        $(`#${tabId}`).addClass('show active');
        $(`#${tabId}-tab`).addClass('active');

        currentTab = index;
        updateButtons();
    }

    $prevBtn.click(function() {
        if (currentTab > 0) {
            showTab(currentTab - 1);
        }
    });

    $nextBtn.click(function() {
        if (currentTab < tabs.length - 1) {
            showTab(currentTab + 1);
        }
    });

    updateButtons();
    $('.nav-link').click(function(e) {
        const tabId = $(this).data('bs-target').substring(1);
        const index = tabs.indexOf(tabId);
        if (index !== -1) {
            showTab(index);
        }
    });
});
CKEDITOR.replace('hotel_description');

// CKEDITOR.replace(`meal_description_1`);


$(document).ready(function() {
    $('#country').on('change', function() {
        var selectedCountry = $(this).val();

        if (selectedCountry === 'India') {
            // Show select, hide input
            $('#stateSelect').removeClass('d-none').prop('disabled', false);
            $('#stateInput').addClass('d-none').prop('disabled', true);
        } else {
            // Show input, hide select
            $('#stateInput').removeClass('d-none').prop('disabled', false);
            $('#stateSelect').addClass('d-none').prop('disabled', true);
        }
    });
});
</script>

