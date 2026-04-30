<?php  include "Layouts/Header.php"; ?>
<?php  include "Layouts/Sidebar.php"; ?>
<?php

if (isset($_POST['btnadd'])) {

    if (!empty($_POST['id'])) {
        try {
            $stmt = $mysqli->prepare("UPDATE service set name=? WHERE id=?");
            $stmt->bind_param("si", $_POST['name'], $_POST['id']);
            $stmt->execute();
            $stmt->close();
            alert("Save Successfully", "success", "success");
            redirect("?");
        } catch (Exception $e) {
            alert($e->getMessage(), "error", "error");
            redirect("?");
        }
    } else {

        try {

            if(empty($_POST['name'])){
                 alert("Please fill all required fields.", "warning", "warning");
        redirect("?");
            }

            $stmt = $mysqli->prepare("INSERT into service (name)values (?)");
            $stmt->bind_param("s", $_POST['name']);
            $stmt->execute();
            alert("Save Successfully", "success", "success");
            redirect("?");
        } catch (Exception $e) {
            alert($e->getMessage(), "error", "error");
            redirect("?");
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
                        <h4 class="card-title"></h4>
                        <button type="button" class="btn btn-square btn-outline-danger" data-bs-toggle="modal"
                            data-bs-target=".bd-example-modal-lg">Add Service</button>

                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="example" class="display" style="min-width: 845px">
                                <thead>
                                    <tr>
                                        <th>S.No</th>
                                        <th>Name</th>


                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                        $stmt = $mysqli->prepare("SELECT * from  service  order by id desc");
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
                                            <?= $row['name']; ?>
                                        </td>





                                        <td><button class="btn btn-primary shadow btn-xs sharp me-1 edit" data-id="<?= $row['id'] ?>"
                                                data-name="<?= $row['name'] ?>"><i class="fa fa-pen"></i></button></td>
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
                    <h3 class="modal-title fs-5" id="exampleModalLabel"> <span class="btnname"> Add Service</span></h3>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">

                    </button>
                </div>
                <div class="modal-body">

                    <div class="row">

                        <input id="id" name="id" type="hidden">
                        <div class="col-md-12">
                            <label for="name" class="form-label">Name</label>
                            <input type="text" class="form-control" id="name" name="name" value="" required>
                        </div>



                    </div>


                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-success" name="btnadd" id="btnadd"><span class="btnname">
                            Submit</span></button>
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
 

        $(".btnname").html("Update service");
        $(".bd-example-modal-lg").modal("show");

    });
</script>