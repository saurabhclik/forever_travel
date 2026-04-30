<?php  include "Layouts/Header.php"; ?>
<?php  include "Layouts/Sidebar.php"; ?>
<?php
if (isset($_POST['btnadd'])) {

    if (!empty($_POST['id'])) {
        try {
            $stmt = $mysqli->prepare("UPDATE expense_subcategory set category_id=?,name=?,active=? WHERE id=?");
            $stmt->bind_param("ssii", $_POST['category_id'],$_POST['name'], $_POST['active'], $_POST['id']);
            $stmt->execute();
            $stmt->close();
            alert("Save Successfully", "success", "success");
                      redirect("expense_subcategory.php");
        } catch (Exception $e) {
            alert($e->getMessage(), "error", "error");
                    redirect("expense_subcategory.php");
        }
    } else {

        try {

            $stmt = $mysqli->prepare("INSERT into expense_subcategory (category_id,name,active)values (?,?,?)");
            $stmt->bind_param("ssi",$_POST['category_id'], $_POST['name'],  $_POST['active']);
            $stmt->execute();
            alert("Save Successfully", "success", "success");
                      redirect("expense_subcategory.php");
        } catch (Exception $e) {
            alert($e->getMessage(), "error", "error");
            redirect("expense_subcategory.php");
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
                            data-bs-target=".bd-example-modal-lg">Add Expence Category</button>

                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="example" class="display" style="min-width: 845px">
                                <thead>
                                    <tr>
                                        <th>S.No</th>
                                        <th>Sub Category Name</th>

                                        <th>Active</th>
                                        <th>Created</th>
                                        <th>Updated</th>

                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                        $stmt = $mysqli->prepare("SELECT * from  expense_subcategory  order by id desc");
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




                                        <td>
                                            <span class="badge <?php if ($row['active'] == 1)
                                                            echo "badge bg-success";
                                                        else
                                                            echo "badge bg-danger"; ?>"><?php if ($row['active'] == 1)
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
                                        <td><button class="btn btn-primary shadow btn-xs sharp me-1 edit" data-id="<?= $row['id'] ?>"
                                                data-name="<?= $row['name'] ?>"
                                                data-category_id="<?= $row['category_id'] ?>"
                                                data-active="<?= $row['active'] ?>"><i class="fa fa-pen"></i></button>
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
                    <h3 class="modal-title fs-5" id="exampleModalLabel"> <span class="btnname"> Add Sub Category</span></h3>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">

                    </button>
                </div>
                <div class="modal-body">

                    <div class="row">

                        <input id="id" name="id" type="hidden">
                        <div class="col-md-12">
                            <label for="name" class="form-label">Category</label>
                            <select class="form-control" id="category_id" name="category_id"  required>
                                <?php 
                                  $stmt = $mysqli->prepare("SELECT * from  expense_category where active=1");
                        $stmt->execute();
                        $res = $stmt->get_result();
                        $sno = 1;
                        while ($row = $res->fetch_assoc()) {
                            echo '<option value="'.$row['name'].'">'.$row['name'].'</option>';
                            
                        }
                                
                                ?>
                                
                            </select>
                        </div>

                       <div class="col-md-12">
                            <label for="name" class="form-label">Name</label>
                            <input class="form-control" id="name" name="name"  required>
                            
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
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-success" name="btnadd" id="btnadd"><span class="btnname"> Submit</span></button>
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
   $("#category_id").val($(this).data('category_id'))

        $("#active").val($(this).data('active'))

        $(".btnname").html('Update Expense Sub Category');
        $(".bd-example-modal-lg").modal('show');

    })
</script>