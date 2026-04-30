<?php
include "Layouts/Header.php";
include "Layouts/Sidebar.php";
if (isset($_POST['btnadd'])) {

    if (!empty($_POST['id'])) {
        try {
            $stmt = $mysqli->prepare("UPDATE destinations SET name = ? WHERE id = ?");
            $stmt->bind_param("si", $_POST['name'], $_POST['id']);

            $stmt->execute();
            $stmt->close();
            $_SESSION['alert'] = [
        'title' => 'destination Updated Successfully',
        'text' => "destination updated",
        'icon' => 'success'
    ];
           
            redirect("destination.php");
        } catch (Exception $e) {
            alert($e->getMessage(), "error", "error");
            redirect("destination.php");
        }
    } else {
        try {

            if(empty($_POST['name'])){
                alert("Please fill all required fields.", "warning", "warning");
                redirect("?");
            }
            $stmt = $mysqli->prepare("INSERT INTO destinations (name, created_at) VALUES (?, NOW())");
            $stmt->bind_param("s",$_POST['name']);

         
            $stmt->execute();
         
               $_SESSION['alert'] = [
        'title' => 'destination Added Successfully',
        'text' => 'destination Added Successfully',
        'icon' => 'success'
    ];
            redirect("destination.php");
        } catch (Exception $e) {
            alert($e->getMessage(), "error", "error");
            redirect("destination.php");
        }
    }
}



?>

<div class="content-body">
    <div class="container-fluid">
        <!-- row -->
        <div class="row">
            <div class="col-12">

                <div class="card">

                    <div class="card-header d-flex flex-wrap justify-content-between align-items-start gap-3">
    <!-- Title -->
    <h4 class="card-title m-0">Destination</h4>

    <!-- Buttons -->
    <div class="d-flex gap-2">
        <a href="download_destination_sample.php" class="btn btn-outline-primary">Download Sample</a>
        <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target=".bd-example-modal-lg">
            Add Destination
        </button>
    </div>

    <!-- Upload Form -->
    <form method="POST" action="upload_destinations.php" enctype="multipart/form-data" class="d-flex align-items-end gap-2 flex-wrap">
        <div>
            <label for="csv_file" class="form-label m-0">Select CSV File</label>
            <input type="file" class="form-control" name="csv_file" id="csv_file" accept=".csv" required>
        </div>
        <button type="submit" name="submit" class="btn btn-success">Upload</button>
    </form>
</div>

                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="example" class="display" style="min-width: 845px">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Name</th>
                                        <th>Created</th>
                                        <th>Updated</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                        $stmt = $mysqli->prepare("SELECT * from  destinations  order by id desc");
                        $stmt->execute();
                        $res = $stmt->get_result();
                        $sno = 1;
                        while ($row = $res->fetch_assoc()) {
                        ?>
                                    <tr>
                                        <td>
                                            <?= $sno++; ?>

                                        <td>
                                            <?= $row['name']; ?>
                                        </td>

                                        <td>
                                            <?= $row['created_at']; ?>
                                        </td>
                                        <td>
                                            <?= $row['updated_at']; ?>
                                        </td>
                                        <td><button class="btn btn-primary shadow btn-xs sharp me-1 edit"
                                                data-id="<?= $row['id'] ?>" data-name="<?= $row['name'] ?>"><i
                                                    class="fa fa-pen"></i></button>
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


<div class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog" aria-hidden="true">
    <form class="needs-validation" novalidate method="POST">
        <div class="modal-dialog  modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="modal-title fs-5" id="exampleModalLabel"> <span class="btnname"> Add Destination</span>
                    </h3>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">

                    </button>
                </div>
                <div class="modal-body">

                    <div class="row">
                        <input id="id" name="id" type="hidden">
                        <div class="col-md-12">
                            <label for="name" class="form-label">Destination Name</label>
                            <input type="text" class="form-control" id="name" name="name" value="" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary " data-bs-dismiss="modal">Close</button>
                    <button type="submit" class=" btn btn-square btn-outline-success" name="btnadd" id="btnadd"><span
                            class="btnname"> Submit</span></button>
                </div>
            </div>
        </div>
    </form>
</div>



<?php include "Layouts/Footer.php"  ?>

<script>
$(document).on("click", ".edit", function() {
    $("#id").val($(this).data("id"));
    $("#name").val($(this).data("name"));
    $(".bd-example-modal-lg").modal('show');
});
</script>