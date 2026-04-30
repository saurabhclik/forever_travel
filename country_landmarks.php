<?php
// if (isset($_POST['btnSubmit'])) {

//     print_r($_POST); exit;
// }
    include "Layouts/Header.php";
    include "Layouts/Sidebar.php";

if (isset($_POST['btnSubmit'])) {
    try {
        $country     = $_POST['country'];
        $state       = $_POST['state'];
        $city        = $_POST['city'];
        $palace      = $_POST['place'];
        $title       = $_POST['title'];
        $description = $_POST['description'];
        $note        = $_POST['note'];
        $active      = isset($_POST['active']) ? (int)$_POST['active'] : 0;

        $image_path = null;
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $target_dir = "./images/palace_images/";
            if (!is_dir($target_dir)) {
                mkdir($target_dir, 0777, true);
            }
            $image_name  = time() . "_" . basename($_FILES["image"]["name"]);
            $target_file = $target_dir . $image_name;

            if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                $image_path = $image_name;
            } else {
                throw new Exception("Image upload failed to $target_file");
            }
        }

        $stmt = $mysqli->prepare("INSERT INTO country_landmarks (country, state, city, place, title, description, image, note, active, created_at) 
                                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");

        $stmt->bind_param("ssssssssi", $country, $state, $city, $palace, $title, $description, $image_path, $note, $active);

        if (!$stmt->execute()) {
            throw new Exception("Database insert failed: " . $stmt->error);
        }

        $_SESSION['alert'] = [
            'title' => 'Details Saved',
            'text'  => 'Place details successfully saved.',
            'icon'  => 'success'
        ];

    } catch (Exception $e) {
        $_SESSION['alert'] = [
            'title' => 'Operation Failed',
            'text'  => $e->getMessage(),
            'icon'  => 'error'
        ];
    }

    redirect("?");
}


?>

<div class="content-body">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header ">
                        <h4 class="card-title m-0">Destination</h4>
                        <div class="col-3">
                            <div class="text-end mt-2">
                                <div class="d-flex justify-content-end gap-2">
                                <a href="download_country_landmark.php" class="btn btn-outline-primary"> Download Sample</a>
                                    <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal"
                                        data-bs-target="#addCountryDetails">
                                        Add Country LandMark
                                    </button>
                                </div>
                            </div>

                            <form method="POST" action="upload_country_landmark.php" enctype="multipart/form-data"
                                class="w-100 mt-3">
                                <div class="mb-3">
                                    <label for="csv_file" class="form-label">Select CSV File</label>
                                    <input type="file" class="form-control" name="csv_file" id="csv_file" accept=".csv"
                                        required>
                                </div>
                                <div class="d-grid">
                                    <button type="submit" name="submit" class="btn btn-success">Upload</button>
                                </div>
                            </form>

                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="example" class="table" style="width:100%">
                                <thead>
                                    <tr>
                                        <th>country</th>
                                       <th>state</th> 
                                        <th>city</th>
                                        <th>place</th>
                                        <th>title</th>
                                        <th>description</th>
                                        <th>image</th>
                                        <th>note</th>
                                    </tr>
                                </thead>
                               <?php $stmt = $mysqli->prepare("SELECT * from  country_landmarks  order by id desc");
                        $stmt->execute();
                        $res = $stmt->get_result();
                        $sno = 1;
                        while ($row = $res->fetch_assoc()):  ?>
                                        <tr>
                                            <td><?= $row['country'];  ?></td>
                                            <td><?= $row['state'];  ?></td>
                                            <td><?= $row['city'];  ?></td>
                                            <td><?= $row['place'];  ?></td>
                                            <td><?= $row['title'];  ?></td>
                                            <td><?= $row['description'];  ?></td>
                                            <td> <img src="<?php echo BASE_PATH; ?>/images/palace_images/<?= $row['image'];  ?>" alt="image" height="50"></td>
                                            <td><?= $row['note'];  ?></td>
                                        </tr>
                                <?php endwhile; ?>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="addCountryDetails" tabindex="-1" aria-labelledby="addHotelModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addHotelModalLabel">Add Palace</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="container mt-4">
                <form id="placeForm" method="post" enctype="multipart/form-data">
                    <div class="mb-3">
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

                    <div class="mb-3">
                        <label class="form-label">State</label>
                        <select id="stateSelect" name="state" class="form-control ">
                            <option value="" disabled selected>Select State</option>
                            <?php
                                    $stmt = $mysqli->prepare("SELECT  distinct state FROM `state_district`");
                                    $stmt->execute();
                                    $category = $stmt->get_result();
                                    while ($row = $category->fetch_assoc()) {
                                        echo '<option value="' . $row['state'] . '">' . $row['state'] . '</option>';
                                    }
                                ?>
                        </select>
                        <input type="text" id="stateInput" name="state" class="form-control mt-2 d-none"
                            placeholder="Enter State">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">City</label>
                        <select id="citySelect" name="city" class="form-control">
                            <option value="">----Select City----</option>
                        </select>
                        <input type="text" id="cityInput" name="city" class="form-control mt-2 d-none"
                            placeholder="Enter City">
                    </div>

                    <div class="mb-3">
                        <label for="place" class="form-label">Palace</label>
                        <input type="text" id="place" name="place" class="form-control">
                    </div>

                    <div class="mb-3">
                        <label for="title" class="form-label">Title</label>
                        <input type="text" id="title" name="title" class="form-control">
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea name="description" id="description" rows="10" cols="80">
                            Write your content here...
                        </textarea>


                    </div>

                    <div class="mb-3">
                        <label for="image" class="form-label">Image</label>
                        <input type="file" id="image" name="image" class="form-control">
                    </div>

                    <div class="mb-3">
                        <label for="note" class="form-label">Note</label>
                        <input type="text" id="note" name="note" class="form-control">
                    </div>

                    <div class="mb-3">
                        <label for="active" class="form-label">Active</label>
                        <select id="active" name="active" class="form-control">
                            <option value="1">Yes</option>
                            <option value="0">No</option>
                        </select>
                    </div>


                    <div class="modal-footer">
                        <button type="submit" class="btn btn-success" id="submitBtn" name="btnSubmit">Save</button>
                        <button type="button" class="btn btn-danger" data-bs-dismiss="modal"
                            aria-label="Close">Close</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>



<?php 
    include "Layouts/Footer.php";
?>
<script>
$(document).ready(function() {

    $('#country').change(function() {
        var selectedCountry = $(this).val();

        if (selectedCountry === 'India') {
            $('#stateSelect').removeClass('d-none');
            $('#citySelect').removeClass('d-none');
            $('#stateInput').addClass('d-none');
            $('#cityInput').addClass('d-none');
        } else if (selectedCountry) {
            $('#stateSelect').addClass('d-none');
            $('#citySelect').addClass('d-none');
            $('#stateInput').removeClass('d-none');
            $('#cityInput').removeClass('d-none');
        } else {
            $('#stateSelect').removeClass('d-none');
            $('#citySelect').removeClass('d-none');
            $('#stateInput').addClass('d-none');
            $('#cityInput').addClass('d-none');
        }
    });
});
$("#stateSelect").on("change", function() {
    $.ajax({
        url: "ajax/get-city.php",
        type: "POST",
        data: {
            state: $(this).val(),
        },
        success: function(result) {
            // Directly set the HTML response into citySelect
            $("#citySelect").html(result);
        },
        error: function(result) {
            console.log(result);
        }
    });
});
</script>