<?php
 include "Layouts/Header.php";
include "Layouts/Sidebar.php";
if (isset($_POST['btnadd'])) {

    if (!empty($_POST['id'])) {
        try {
            $stmt = $mysqli->prepare("UPDATE status SET name = ?, status = ? WHERE id = ?");
            $stmt->bind_param("sii", $_POST['name'], $_POST['status'], $_POST['id']);

            $stmt->execute();
            $stmt->close();
            $_SESSION['alert'] = [
        'title' => 'Status Updated Successfully',
        'text' => "status updated",
        'icon' => 'success'
    ];
           
            redirect("status.php");
        } catch (Exception $e) {
            alert($e->getMessage(), "error", "error");
            redirect("status.php");
        }
    } else {
        try {

            if(empty($_POST['name'])){
                alert("Please fill all required fields.", "warning", "warning");
                redirect("?");
            }
            $stmt = $mysqli->prepare("INSERT INTO status (name, created_at) VALUES (?, NOW())");
            $stmt->bind_param("s",$_POST['name']);

         
            $stmt->execute();
            alert("Save Successfully", "success", "success");
               $_SESSION['alert'] = [
        'title' => 'Status Added Successfully',
        'text' => 'Status Added Successfully',
        'icon' => 'success'
    ];
            redirect("status.php");
        } catch (Exception $e) {
            alert($e->getMessage(), "error", "error");
            redirect("status.php");
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
                    <div class="card-header">
                        <h4 class="card-title">Status</h4>
                        <button type="button" class="btn btn-square btn-outline-danger" data-bs-toggle="modal"
                            data-bs-target=".bd-example-modal-lg">Add Status</button>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="example" class="display" style="min-width: 845px">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Status</th>
                                       
                                        <th>Created</th>
                                        <th>Updated</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                        $stmt = $mysqli->prepare("SELECT * from  status  order by id desc");
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
                                                data-id="<?= $row['id'] ?>" data-name="<?= $row['name'] ?>"
                                                data-active="<?= $row['status'] ?>"><i class="fa fa-pen"></i></button>
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
                    <h3 class="modal-title fs-5" id="exampleModalLabel"> <span class="btnname"> Add Status</span></h3>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">

                    </button>
                </div>
                <div class="modal-body">

                    <div class="row">
                        <input id="id" name="id" type="hidden">
                        <div class="col-md-12">
                            <label for="name" class="form-label">Status</label>
                            <input type="text" class="form-control" id="name" name="name" value="" required>
                        </div>
                        <!-- <div class="col-md-12">
                            <label for="" class="form-label">Active</label>
                            <select class="form-control" name="status   " id="status">
                                <option value="1">Yes</option>
                                <option value="0">No</option>
                            </select>
                        </div> -->
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
    $("#id").val($(this).data("id"))
    $("#name").val($(this).data("name"))
    // $("#status").val($(this).data("active"))
    $(".btnname").html("Update Status");
    $(".modal-title").html("Update Status");
    $(".bd-example-modal-lg").modal("show");
});
</script>