<?php
    include "Layouts/Header.php";
    include "Layouts/Sidebar.php"; 

  if (isset($_POST['BtnSubmit'])) {
    try {
        global $mysqli;
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

        if (isset($_POST['name']) && !empty($_POST['name'])) {
            $name = $_POST['name'];
            $image_path = null;

            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $target_dir = "./images/destination_images/";
                if (!is_dir($target_dir)) {
                    mkdir($target_dir, 0777, true);
                }
                $image_name = time() . "_" . basename($_FILES["image"]["name"]);
                $target_file = $target_dir . $image_name;

                if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                    $image_path = $image_name;
                } else {
                    throw new Exception("Image upload failed to $target_file");
                }
            }

            if (isset($_POST['id']) && !empty($_POST['id'])) {
                $id = $_POST['id'];

                $stmt = $mysqli->prepare("UPDATE destination_images SET name = ?, image = ? WHERE id = ?");
                $stmt->bind_param("ssi", $name, $image_path, $id);
                $stmt->execute();
                $stmt->close();

                $_SESSION['alert'] = [
                    'title' => 'Image Successfully Updated',
                    'text'  => 'Image updated successfully.',
                    'icon'  => 'success'
                ];
            } else {
                $stmt = $mysqli->prepare("INSERT INTO destination_images (name, image) VALUES (?, ?)");
                $stmt->bind_param('ss', $name, $image_path);
                $stmt->execute();
                $stmt->close();

                $_SESSION['alert'] = [
                    'title' => 'Image Successfully Saved',
                    'text'  => 'Image saved successfully.',
                    'icon'  => 'success'
                ];
            }

            redirect("?");
            

        } else {
            $_SESSION['alert'] = [
                'title' => 'Image Not Saved',
                'text'  => 'All fields are required.',
                'icon'  => 'error'
            ];
      redirect("?");
           
        }

    } catch (mysqli_sql_exception $e) {
        $_SESSION['alert'] = [
            'title' => 'Database Error',
            'text'  => "Database error: " . $e->getMessage(),
            'icon'  => 'error'
        ];
       redirect("?");
     
    } catch (Throwable $e) {
        $_SESSION['alert'] = [
            'title' => 'Unexpected Error',
            'text'  => "Error: " . $e->getMessage(),
            'icon'  => 'error'
        ];
       redirect("?");
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
                        <h4 class="card-title">Destination Image</h4>
                        <button type="button" class="btn btn-square btn-outline-danger addDestinationImage"
                            data-bs-toggle="modal" data-bs-target=".bd-example-modal-lg">Add Image</button>

                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="example" class="display" style="min-width: 845px">
                                <thead>
                                    <tr>
                                        <th>#</th>

                                        <th>Name</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    
                                    $stmt = $mysqli->prepare("SELECT * FROM destination_images");
                                    $stmt->execute();
                                    $res = $stmt->get_result();
                                    $sr_no = 1;
                                    while($row = $res->fetch_assoc()):
                                    ?>
                                    <tr>
                                        <td><?php echo $sr_no;  ?></td>
                                        <td><?= $row['name'];  ?></td>
                                        <td><img src="<?php echo BASE_PATH;  ?>images/destination_images/<?= $row['image'];  ?>"
                                                alt="img" height="50"></td>
                                        <td><button class="btn btn-primary shadow btn-xs sharp me-1 edit"
                                                data-id="<?= $row['id'] ?>" data-name="<?= $row['name'] ?>">
                                                <i class="fa fa-pen"></i>
                                            </button></td>
                                    </tr>

                                    <?php $sr_no++; endwhile;  ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

        </div>



    </div>
</div>


<div class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Image</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal">
                </button>
            </div>
            <div class="modal-body">
                <form class="needs-validation" id="companyform" novalidate method="POST" enctype="multipart/form-data">
                    <div class="row">
                        <div class="col-md-6">
                            <label for="name" class="form-label">Name</label>
                            <input type="text" class="form-control" id="name" name="name" value="" required>
                        </div>
                        <div class="col-md-6">
                            <label for="image">Image</label>
                            <input type="file" class="form-control" id="image" name="image" required>
                            <div class="form-text text-danger">Note: Please select an image of size 4200 × 2848
                            </div>
                        </div>
                    </div>
            </div>
            <div class="modal-footer">
                <input id="id" name="id" type="hidden">
                <button type="button" class="btn btn-danger light" data-bs-dismiss="modal">Close</button>
                <button type="submit" name="BtnSubmit" class="btn btn-primary btnname">Save changes</button>
            </div>
            </form>
        </div>
    </div>
</div>

<?php include "Layouts/Footer.php"  ?>

<script>
$(document).on("click", ".edit", function() {
    $("#id").val($(this).data("id"))
    $("#name").val($(this).data("name"))
    $(".bd-example-modal-lg").modal("show");

});

$(document).on("click", ".addDestinationImage", function() {

    $("#companyform").find("input[type=text], textarea, select").val('');

});
</script>