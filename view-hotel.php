<?php
    include "Layouts/Header.php";
    include "Layouts/Sidebar.php";

    if (!isset($_GET['id']) || !is_numeric($_GET['id'])) 
    {
        header("Location: hotels.php");
        exit();
    }

    $hotel_id = $_GET['id'];
    $hotel_query = "SELECT * FROM `hotels` WHERE `hotel_id` = ?";
    $stmt = $mysqli->prepare($hotel_query);
    $stmt->bind_param("i", $hotel_id);
    $stmt->execute();
    $hotel_result = $stmt->get_result();
    $hotel = $hotel_result->fetch_assoc();

    if (!$hotel) 
    {
        header("Location: hotels.php");
        exit();
    }

    $rooms = [];
    $amenities = [];
    $photos = [];
    $policies = [];
    $meals = [];

    $rooms_query = "SELECT * FROM `rooms` WHERE `hotel_id` = ?";
    $stmt = $mysqli->prepare($rooms_query);
    $stmt->bind_param("i", $hotel_id);
    $stmt->execute();
    $rooms_result = $stmt->get_result();
    while ($row = $rooms_result->fetch_assoc()) 
    {
        $rooms[] = $row;
    }

    $amenities_query = "SELECT * FROM `amenities` WHERE `hotel_id` = ?";
    $stmt = $mysqli->prepare($amenities_query);
    $stmt->bind_param("i", $hotel_id);
    $stmt->execute();
    $amenities_result = $stmt->get_result();
    while ($row = $amenities_result->fetch_assoc()) 
    {
        $amenities[] = $row;
    }

    $photos_query = "SELECT * FROM `photos` WHERE `hotel_id` = ?";
    $stmt = $mysqli->prepare($photos_query);
    $stmt->bind_param("i", $hotel_id);
    $stmt->execute();
    $photos_result = $stmt->get_result();
    while ($row = $photos_result->fetch_assoc()) 
    {
        $photos[] = $row;
    }

    $policies_query = "SELECT * FROM `policies` WHERE `hotel_id` = ?";
    $stmt = $mysqli->prepare($policies_query);
    $stmt->bind_param("i", $hotel_id);
    $stmt->execute();
    $policies_result = $stmt->get_result();
    $policies = $policies_result->fetch_assoc();

    $meals_query = "SELECT * FROM `meals` WHERE `hotel_id` = ?";
    $stmt = $mysqli->prepare($meals_query);
    $stmt->bind_param("i", $hotel_id);
    $stmt->execute();
    $meals_result = $stmt->get_result();
    while ($row = $meals_result->fetch_assoc()) 
    {
        $meals[] = $row;
    }
?>

<div class="content-body">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Hotel Details - <?= htmlspecialchars($hotel['hotel_name']) ?></h3>
                        <a href="hotels.php" class="btn btn-square btn-outline-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Hotels
                        </a>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="functions.php" enctype="multipart/form-data">
                            <input type="hidden" name="action" value="update_hotel">
                            <input type="hidden" name="hotel_id" value="<?= $hotel['hotel_id'] ?>">
                            
                            <ul class="nav nav-tabs" id="hotelTabs" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active" id="basic-tab" data-bs-toggle="tab" data-bs-target="#basic" type="button" role="tab">Basic Info</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="location-tab" data-bs-toggle="tab" data-bs-target="#location" type="button" role="tab">Location</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="contact-tab" data-bs-toggle="tab" data-bs-target="#contact" type="button" role="tab">Contact</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="room-tab" data-bs-toggle="tab" data-bs-target="#room" type="button" role="tab">Room Details</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="meals-tab" data-bs-toggle="tab" data-bs-target="#meals" type="button" role="tab">Meals</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="amenities-tab" data-bs-toggle="tab" data-bs-target="#amenities" type="button" role="tab">Amenities</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="policies-tab" data-bs-toggle="tab" data-bs-target="#policies" type="button" role="tab">Policies</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="media-tab" data-bs-toggle="tab" data-bs-target="#media" type="button" role="tab">Media</button>
                                </li>
                            </ul>

                            <div class="tab-content p-3 border border-top-0 rounded-bottom" id="hotelTabsContent">
                                <div class="tab-pane fade show active" id="basic" role="tabpanel">
                                    <div class="mb-3">
                                        <label for="hotel_name" class="form-label">Hotel Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="hotel_name" name="hotel_name" value="<?= htmlspecialchars($hotel['hotel_name']) ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="hotel_description" class="form-label">Description</label>
                                        <textarea class="form-control" id="hotel_description" name="hotel_description" rows="3"><?= htmlspecialchars($hotel['hotel_description']) ?></textarea>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="star_rating" class="form-label">Star Rating</label>
                                            <select class="form-select" id="star_rating" name="star_rating">
                                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                                    <option value="<?= $i ?>" <?= $hotel['star_rating'] == $i ? 'selected' : '' ?>>
                                                        <?= str_repeat('★', $i) ?>
                                                    </option>
                                                <?php endfor; ?>
                                            </select>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="status" class="form-label">Status</label>
                                            <select class="form-select" id="status" name="status" required>
                                                <option value="active" <?= (!isset($hotel['status']) || htmlspecialchars($hotel['status']) === 'Active') ? 'selected' : '' ?>>Active</option>
                                                <option value="inactive" <?= (isset($hotel['status']) && htmlspecialchars($hotel['status']) === 'Inactive') ? 'selected' : '' ?>>Inactive</option>
                                            </select>
                                        </div>

                                    </div>                   
                                </div>

                                <div class="tab-pane fade" id="location" role="tabpanel">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="address" class="form-label">Address</label>
                                            <input type="text" class="form-control" id="address" name="address" value="<?= htmlspecialchars($hotel['address']) ?>" required>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="city" class="form-label">City</label>
                                            <input type="text" class="form-control" id="city" name="city" value="<?= htmlspecialchars($hotel['city']) ?>" required>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="state" class="form-label">State</label>
                                            <select class="form-control" id="stateSelect" name="state" required>
                                                <option value="">Select State</option>
                                                <?php
                                                    $stmt = $mysqli->prepare("SELECT distinct state FROM `state_district`");
                                                    $stmt->execute();
                                                    $states = $stmt->get_result();
                                                    while ($row = $states->fetch_assoc()) 
                                                    {
                                                        $selected = ($row['state'] == $hotel['state']) ? 'selected' : '';
                                                        echo '<option value="' . htmlspecialchars($row['state']) . '" ' . $selected . '>' . htmlspecialchars($row['state']) . '</option>';
                                                    }
                                                ?>
                                            </select>
                                              <!-- State text input (hidden and disabled by default) -->
                                    <input type="text" class="form-control d-none" id="stateInput" name="state"
                                        placeholder="Enter State" disabled>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="country" class="form-label">Country</label>
                                            <select class="form-control" id="country" name="country" required>
                                                <option value="">Select Country</option>
                                                <?php
                                                    $stmt = $mysqli->prepare("SELECT * FROM countries");
                                                    $stmt->execute();
                                                    $countries = $stmt->get_result();
                                                    while ($row = $countries->fetch_assoc()) 
                                                    {
                                                        $selected = ($row['en_short_name'] == $hotel['country']) ? 'selected' : '';
                                                        echo '<option value="' . htmlspecialchars($row['en_short_name']) . '" ' . $selected . '>' . htmlspecialchars($row['en_short_name']) . '</option>';
                                                    }
                                                ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="zipcode" class="form-label">Postal/Zip Code</label>
                                            <input type="text" class="form-control" id="zipcode" name="zipcode" value="<?= htmlspecialchars($hotel['zipcode']) ?>">
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <label for="latitude" class="form-label">Latitude</label>
                                            <input type="text" class="form-control" id="latitude" name="latitude" value="<?= htmlspecialchars($hotel['latitude']) ?>">
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <label for="longitude" class="form-label">Longitude</label>
                                            <input type="text" class="form-control" id="longitude" name="longitude" value="<?= htmlspecialchars($hotel['longitude']) ?>">
                                        </div>
                                    </div>
                                </div>

                                <div class="tab-pane fade" id="contact" role="tabpanel">
                                    <div class="mb-3">
                                        <label for="phone_number" class="form-label">Phone Number</label>
                                        <input type="tel" class="form-control" id="phone_number" name="phone_number" value="<?= htmlspecialchars($hotel['phone_number']) ?>">
                                    </div>
                                    <div class="mb-3">
                                        <label for="email" class="form-label">Email</label>
                                        <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($hotel['email']) ?>">
                                    </div>
                                    <div class="mb-3">
                                        <label for="website_url" class="form-label">Website</label>
                                        <input type="url" class="form-control" id="website_url" name="website_url" value="<?= htmlspecialchars($hotel['website_url']) ?>">
                                    </div>
                                </div>

                                <div class="tab-pane fade" id="room" role="tabpanel">
                                    <div id="roomsContainer">
                                        <?php foreach ($rooms as $index => $room): ?>
                                            <div class="room-row mb-3">
                                                <input type="hidden" name="room_ids[]" value="<?= $room['room_id'] ?>">
                                                <div class="row align-items-end">
                                                    <div class="col-md-5 mb-3">
                                                        <label class="form-label">Room Type</label>
                                                        <input type="text" class="form-control" name="room_types[]" value="<?= htmlspecialchars($room['room_type']) ?>" required>
                                                    </div>
                                                    <div class="col-md-5 mb-3">
                                                        <label class="form-label">Room Price</label>
                                                        <input type="number" class="form-control" name="room_prices[]" value="<?= htmlspecialchars($room['room_price']) ?>" required>
                                                    </div>
                                                    <div class="col-md-2 mb-3">
                                                        <button type="button" class="btn btn-danger remove-room-btn">
                                                            <i class="fa fa-trash"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                    <button type="button" class="btn btn-primary" id="addRoomButton">Add Room</button>
                                </div>

                                <div class="tab-pane fade" id="meals" role="tabpanel">
                                    <div id="mealsContainer">
                                        <?php foreach ($meals as $index => $meal): ?>
                                            <div class="meal-row mb-3">
                                                <input type="hidden" name="meal_ids[]" value="<?= $meal['meal_id'] ?>">
                                                <div class="row align-items-end">
                                                    <div class="col-md-3 mb-3">
                                                        <label class="form-label">Meal Plan</label>
                                                        <select class="form-select" name="meal_plans[]">
                                                            <option value="None" <?= $meal['meal_type'] == 'None' ? 'selected' : '' ?>>None</option>
                                                            <option value="CP" <?= $meal['meal_type'] == 'CP' ? 'selected' : '' ?>>CP (Breakfast)</option>
                                                            <option value="MAP" <?= $meal['meal_type'] == 'MAP' ? 'selected' : '' ?>>MAP (Breakfast+Dinner)</option>
                                                            <option value="AP" <?= $meal['meal_type'] == 'AP' ? 'selected' : '' ?>>AP (All Meals)</option>
                                                            <option value="Custom" <?= $meal['meal_type'] == 'Custom' ? 'selected' : '' ?>>Custom</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-4 mb-3">
                                                        <label for="meal_price_1" class="form-label">Price</label>
                                                        <input type="number" class="form-control" id="meal_price_1" name="meal_prices[]" placeholder="Enter price" value="<?= htmlspecialchars($meal['meal_plan_price']) ?>">
                                                    </div>
                                                    <div class="col-md-4 mb-3">
                                                        <label class="form-label">Description</label>
                                                        <textarea class="form-control" name="meal_descriptions[]" rows="1"><?= htmlspecialchars($meal['description']) ?></textarea>
                                                    </div>
                                                    <div class="col-md-1 mb-3">
                                                        <button type="button" class="btn btn-danger remove-meal-btn">
                                                            <i class="fa fa-trash"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                    <button type="button" class="btn btn-primary" id="addMealButton">Add Meal</button>
                                </div>

                                <div class="tab-pane fade" id="amenities" role="tabpanel">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div id="amenitiesContainer">
                                                <?php foreach ($amenities as $index => $amenity): ?>
                                                    <div class="amenity-row mb-3">
                                                        <input type="hidden" name="amenity_ids[]" value="<?= $amenity['amenity_id'] ?>">
                                                        <div class="row align-items-end">
                                                            <div class="col-md-10 mb-3">
                                                                <label class="form-label">Amenity Name</label>
                                                                <input type="text" class="form-control" name="amenity_names[]" value="<?= htmlspecialchars($amenity['amenity_name']) ?>">
                                                            </div>
                                                            <div class="col-md-2 mb-3">
                                                                <button type="button" class="btn btn-danger remove-amenity-btn">
                                                                    <i class="fa fa-trash"></i>
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                            <button type="button" class="btn btn-primary" id="addAmenityButton">Add Amenity</button>
                                        </div>
                                    </div>
                                </div>

                                <div class="tab-pane fade" id="policies" role="tabpanel">
                                    <div class="mb-3">
                                        <label for="check_in_time" class="form-label">Check-In Time</label>
                                        <input type="time" class="form-control" id="check_in_time" name="check_in_time" value="<?= $policies ? htmlspecialchars($policies['check_in_time']) : '' ?>">
                                    </div>
                                    <div class="mb-3">
                                        <label for="check_out_time" class="form-label">Check-Out Time</label>
                                        <input type="time" class="form-control" id="check_out_time" name="check_out_time" value="<?= $policies ? htmlspecialchars($policies['check_out_time']) : '' ?>">
                                    </div>
                                    <div class="mb-3">
                                        <label for="cancellation_policy" class="form-label">Cancellation Policy</label>
                                        <textarea class="form-control" id="cancellation_policy" name="cancellation_policy" rows="3"><?= $policies ? htmlspecialchars($policies['cancellation_policy']) : '' ?></textarea>
                                    </div>
                                </div>

                                <div class="tab-pane fade" id="media" role="tabpanel">
                                    <div class="mb-3">
                                        <label class="form-label">Existing Photos</label>
                                        <div class="row">
                                            <?php foreach ($photos as $photo): ?>
                                                <div class="col-md-3 mb-3">
                                                    <div class="position-relative">
                                                        <img src="<?= htmlspecialchars($photo['photo_url']) ?>" class="img-thumbnail" style="height: 100px; width: 100%; object-fit: cover;">
                                                        <button type="button" class="btn btn-sm position-absolute top-10 end-0 m-1 delete-photo" data-photo-id="<?= $photo['photo_id'] ?>">
                                                           <i class="fa-solid fa-xmark text-danger fs-3 fw-bold" data-bs-toggle="tooltip"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label for="photos" class="form-label">Add New Photos</label>
                                        <input type="file" class="form-control" id="photos" name="photos[]" multiple>
                                    </div>
                                </div>
                            </div>

                            <div class="card-footer">
                                <button type="submit" class="btn btn-primary">Save Changes</button>                              
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php 
    include "Layouts/Footer.php";
?>

<script>
    $(document).ready(function() 
    {
        let roomCount = <?= count($rooms) ?>;
        $('#addRoomButton').on('click', function() 
        {
            roomCount++;
            const newRoom = `
                <div class="room-row mb-3">
                    <input type="hidden" name="room_ids[]" value="new">
                    <div class="row align-items-end">
                        <div class="col-md-5 mb-3">
                            <label class="form-label">Room Type</label>
                            <input type="text" class="form-control" name="room_types[]" required>
                        </div>
                        <div class="col-md-5 mb-3">
                            <label class="form-label">Room Price</label>
                            <input type="number" class="form-control" name="room_prices[]" required>
                        </div>
                        <div class="col-md-2 mb-3">
                            <button type="button" class="btn btn-danger remove-room-btn">
                                <i class="fa fa-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            `;
            $('#roomsContainer').append(newRoom);
        });

        $(document).on('click', '.remove-room-btn', function() 
        {
            $(this).closest('.room-row').remove();
        });

        let mealCount = <?= count($meals) ?>;
        $('#addMealButton').on('click', function() 
        {
            mealCount++;
            const newMeal = `
                <div class="meal-row mb-3">
                    <input type="hidden" name="meal_ids[]" value="new">
                    <div class="row align-items-end">
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Meal Plan</label>
                            <select class="form-select" name="meal_plans[]">
                                <option value="None">None</option>
                                <option value="CP">CP (Breakfast)</option>
                                <option value="MAP">MAP (Breakfast+Dinner)</option>
                                <option value="AP">AP (All Meals)</option>
                                <option value="Custom">Custom</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="meal_price_1" class="form-label">Price</label>
                            <input type="number" class="form-control" name="meal_prices[]" placeholder="Enter price">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="meal_descriptions[]" rows="1"></textarea>
                        </div>
                        <div class="col-md-1 mb-3">
                            <button type="button" class="btn btn-danger remove-meal-btn">
                                <i class="fa fa-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            `;
            $('#mealsContainer').append(newMeal);
        });

        $(document).on('click', '.remove-meal-btn', function() 
        {
            $(this).closest('.meal-row').remove();
        });

        let amenityCount = <?= count($amenities) ?>;
        $('#addAmenityButton').on('click', function() 
        {
            amenityCount++;
            const newAmenity = `
                <div class="amenity-row mb-3">
                    <input type="hidden" name="amenity_ids[]" value="new">
                    <div class="row align-items-end">
                        <div class="col-md-10 mb-3">
                            <label class="form-label">Amenity Name</label>
                            <input type="text" class="form-control" name="amenity_names[]">
                        </div>
                        <div class="col-md-2 mb-3">
                            <button type="button" class="btn btn-danger remove-amenity-btn">
                                <i class="fa fa-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            `;
            $('#amenitiesContainer').append(newAmenity);
        });

        $(document).on('click', '.remove-amenity-btn', function() 
        {
            $(this).closest('.amenity-row').remove();
        });

        $(document).on('click', '.delete-photo', function() 
        {
            const photoId = $(this).data('photo-id');
            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) 
                {
                    $.post('functions.php', 
                    {
                        action: 'delete_photo',
                        photo_id: photoId
                    }, 
                    function(response) 
                    {
                        if (response.success) 
                        {
                            Swal.fire(
                                'Deleted!',
                                'The photo has been deleted.',
                                'success'
                            ).then(() => {
                                location.reload();
                            });
                        } 
                        else 
                        {
                            Swal.fire(
                                'Error!',
                                'Error deleting photo: ' + response.message,
                                'error'
                            );
                        }
                    }, 'json');
                }
            });
        });
    });

    
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
