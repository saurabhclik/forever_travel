<?php
 include "Layouts/Header.php";
include "Layouts/Sidebar.php";
if (isset($_POST['btnadd'])) {

    if (!empty($_POST['id'])) {
        try {
            $stmt = $mysqli->prepare("UPDATE category set name=?,active=?,hsn_code=? WHERE id=?");
            $stmt->bind_param("sisi", $_POST['name'], $_POST['active'],$_POST['hsn_code'], $_POST['id']);
            $stmt->execute();
            $stmt->close();
            alert("Save Successfully", "success", "success");
            redirect("category.php");
        } catch (Exception $e) {
            alert($e->getMessage(), "error", "error");
            redirect("category.php");
        }
    } else {

        try {
            if (empty($_POST['name'])) {
                alert("Please fill all required fields.", "warning", "warning");
                redirect("?");
            }
            $stmt = $mysqli->prepare("INSERT into category (name,active,hsn_code)values (?,?,?)");
            $stmt->bind_param("sis", $_POST['name'],  $_POST['active'],$_POST['hsn_code']);
            $stmt->execute();
            alert("Save Successfully", "success", "success");
            redirect("category.php");
        } catch (Exception $e) {
            alert($e->getMessage(), "error", "error");
            redirect("category.php");
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
                        <h4 class="card-title">Category</h4>
                        <button type="button" class="btn btn-square btn-outline-danger" data-bs-toggle="modal"
                            data-bs-target=".bd-example-modal-lg">Add Category</button>

                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="example" class="display" style="min-width: 845px">
                                <thead>
                                    <tr>
                                        <th>S.No</th>
                                        <th>HSN Code</th>
                                        <th>Category Name</th>

                                        <th>Active</th>
                                        <th>Created</th>
                                        <th>Updated</th>

                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                        $stmt = $mysqli->prepare("SELECT * from  category  order by id desc");
                        $stmt->execute();
                        $res = $stmt->get_result();
                        $sno = 1;
                        while ($row = $res->fetch_assoc()) {
                        ?>
                                    <tr>
                                        <td>
                                            <?= $sno++; ?>
                                        </td>
                                        <td>
                                            <?= $row['hsn_code']; ?>
                                        </td>
                                        <td>
                                            <?= $row['name']; ?>
                                        </td>




                                        <td>
                                            <span class="badge <?php if ($row['active'] == 1)
                                                            echo "badge light badge-success";
                                                        else
                                                            echo "badge light badge-warning"; ?>"><?php if ($row['active'] == 1)
                                                                                            echo "Yes";
                                                                                        else
                                                                                            echo "No"; ?></span>
                                        </td>
                                        <td>
                                            <?= $row['created_at']; ?>
                                        </td>
                                        <td>
                                            <?= $row['updated_at']; ?>
                                        </td>
                                        <td><button class="btn btn-primary shadow btn-xs sharp me-1 edit"
                                                data-id="<?= $row['id'] ?>" data-name="<?= $row['name'] ?>"
                                                data-active="<?= $row['active'] ?>"
                                                data-hsn_code="<?=$row['hsn_code']?>"><i class="fa fa-pen"></i></button>
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
                    <h3 class="modal-title fs-5" id="exampleModalLabel"> <span class="btnname"> Add Category</span></h3>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">

                    </button>
                </div>
                <div class="modal-body">

                    <div class="row">

                        <input id="id" name="id" type="hidden">
                        <div class="col-md-12">
                            <label for="name" class="form-label">Category Name</label>
                            <input type="text" class="form-control" id="name" name="name" value="" required>
                        </div>

                        <div class="col-md-12">
                            <label for="name" class="form-label">HSN Code</label>
                            <input type="text" class="form-control" id="hsn_code" name="hsn_code" value="" required>
                        </div>

                        <div class="col-md-12">
                            <label for="" class="form-label">Active</label>

                            <select class="form-control" name="active" id="active">
                                <option value="1">Yes</option>
                                <option value="0">No</option>
                            </select>
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

    $("#id").val($(this).data("id"))

    $("#name").val($(this).data("name"))

    $("#hsn_code").val($(this).data("hsn_code"))
    $("#active").val($(this).data("active"))

    $(".btnname").html("Update Category");
    $(".modal-title").html("Update Category");
    $(".bd-example-modal-lg").modal("show");

});
</script>