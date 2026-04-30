<?php  include "Layouts/Header.php"; ?>
<?php  include "Layouts/Sidebar.php"; ?>

<?php

if (isset($_POST['btnadd'])) {

    if (!empty($_POST['id'])) {
        try {
            $stmt = $mysqli->prepare("UPDATE gst set gst=? WHERE id=?");
            $stmt->bind_param("di", $_POST['name'], $_POST['id']);
            $stmt->execute();
            $stmt->close();
            alert("Save Successfully", "success", "success");
            redirect("gst.php");
        } catch (Exception $e) {
            alert($e->getMessage(), "error", "error");
            redirect("gst.php");
        }
    } else {

        try {

            $stmt = $mysqli->prepare("INSERT into gst (gst)values (?)");
            $stmt->bind_param("d", $_POST['name']);
            $stmt->execute();
            alert("Save Successfully", "success", "success");
            redirect("gst.php");
        } catch (Exception $e) {
            alert($e->getMessage(), "error", "error");
            redirect("gst.php");
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
                            data-bs-target=".bd-example-modal-lg">Add GST</button>

                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="example" class="display" style="min-width: 845px">
                                <thead>
                                    <tr>
                                        <th>S.No</th>
                                        <th>GST</th>
                                        <th>Created</th>
                                        <th>Updated</th>

                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                        $stmt = $mysqli->prepare("SELECT * from  gst  order by gst asc");
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
                                            <?= $row['gst']; ?>
                                        </td>



                                        <td>
                                            <?= $row['created_at']; ?>
                                        </td>
                                        <td>
                                            <?= $row['updated_at']; ?>
                                        </td>
                                        <td><button class="btn btn-primary shadow btn-xs sharp me-1 edit" data-id="<?= $row['id'] ?>"
                                                data-name="<?= $row['gst'] ?>"><i class="fa fa-pen"></i></button></td>
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
                    <h3 class="modal-title fs-5" id="exampleModalLabel"> <span class="btnname"> Add GST</span></h3>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">

                    </button>
                </div>
                <div class="modal-body">

                    <div class="row">

                        <input id="id" name="id" type="hidden">
                        <div class="col-md-12">
                            <label for="name" class="form-label">GST</label>
                            <input type="number" step="0.01" class="form-control" id="name" name="name" value=""
                                required>
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

    $("#id").val($(this).data('id'))
    $("#name").val($(this).data('name'))
    $(".btnname").html('Update GST');
    $(".bd-example-modal-lg").modal('show');

})
</script>