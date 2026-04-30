<?php
include "Layouts/Header.php";
include "Layouts/Sidebar.php"; 
if (isset($_POST['BtnSubmit'])) {
     $invoice_prefix = $_POST['invoice_prefix'];
    if (!empty($_POST['id'])) {
        try {
            $stmt = $mysqli->prepare("UPDATE company set name=?,address=?,number=?,email=?,bank_name=?,account_number=?,ifsc=? WHERE id=?");
            $stmt->bind_param("sssssssi", $_POST['name'],  $_POST['address'],$_POST['number'],$_POST['email'],$_POST['bank_name'],$_POST['account_number'],$_POST['ifsc'], $_POST['id']);
            $stmt->execute();
            $stmt->close();
            alert("Save Successfully", "success", "success");
            redirect("company.php");
        } catch (Exception $e) {
            alert($e->getMessage(), "error", "error");
            redirect("?");
        }
    } else {


try {
    // Check if any required field is empty
    if (empty($_POST['name'])) {
        alert("Please fill all required fields.", "warning", "warning");
        redirect("?");
    } else {
        $invoice_prefix = "";

        $stmt = $mysqli->prepare("INSERT INTO company (name, address, number, email, bank_name, account_number, ifsc, invoice_prefix) VALUES (?,?,?,?,?,?,?,?)");
        $stmt->bind_param("ssssssss", $_POST['name'], $_POST['address'], $_POST['number'], $_POST['email'], $_POST['bank_name'], $_POST['account_number'], $_POST['ifsc'], $invoice_prefix);
        $stmt->execute();

        alert("Saved Successfully", "success", "success");
        redirect("company.php");
    }
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
                        <h4 class="card-title">Company</h4>
                        <button type="button" class="btn btn-square btn-outline-danger addCompany" data-bs-toggle="modal"
                            data-bs-target=".bd-example-modal-lg">Add Company</button>
                           
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="example" class="display" style="min-width: 845px">
                                <thead>
                                    <tr>
                                        <th>S.No</th>
                                        <th>Name</th>
                                        <th>Address</th>
                                        <th>Email</th>
                                        <th>Phone</th>
                                        <th>Bank name</th>
                                        <th>Account Number</th>
                                        <th>IFSC</th>
                                        <th>Created</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                               
                                    <?php
                        $stmt = $mysqli->prepare("SELECT * from  company  order by id desc");
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
                                        <td><?= $row['address']; ?></td>
                                        <td><?= $row['email']; ?></td>
                                        <td><?= $row['number']; ?></td>
                                        <td><?= $row['bank_name']; ?></td>
                                        <td><?= $row['account_number']; ?></td>
                                        <td><?= $row['ifsc']; ?></td>



                                        <td>
                                            <?= $row['created_at']; ?>
                                        </td>

                                        <td><button class="btn btn-primary shadow btn-xs sharp me-1 edit" data-id="<?= $row['id'] ?>"
                                                data-name="<?= $row['name'] ?>" data-address="<?= $row['address'] ?>"
                                                data-number="<?= $row['number'] ?>" data-email="<?= $row['email'] ?>"
                                                data-bank_name="<?= $row['bank_name'] ?>"
                                                data-account_number="<?= $row['account_number'] ?>"
                                                data-ifsc="<?= $row['ifsc'] ?>"
                                                data-invoice_prefix="<?= $row['invoice_prefix'] ?>"><i
                                                    class="fa fa-pen"></i></button></td>
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
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Company</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal">
                </button>
            </div>
            <div class="modal-body">

                <form class="needs-validation" id="companyform" novalidate method="POST">
                    <div class="row">
                        <input id="id" name="id" type="hidden">
                        <div class="col-md-6">
                            <label for="name" class="form-label">Company Name</label>
                            <input type="text" class="form-control" id="name" name="name" value="" required>
                        </div>

                        <div class="col-md-6">
                            <label for="name" class="form-label">Address</label>
                            <input type="text" class="form-control" id="address" name="address" value="">
                        </div>
                        <div class="col-md-6">
                            <label for="name" class="form-label">Phone number</label>
                            <input type="text" class="form-control" id="number" name="number" value="">
                        </div>

                        <div class="col-md-6">
                            <label for="name" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" value="">
                        </div>

                        <div class="col-md-6">
                            <label for="name" class="form-label">Bank Name</label>
                            <input type="text" class="form-control" id="bank_name" name="bank_name" value="">
                        </div>


                        <div class="col-md-6">
                            <label for="name" class="form-label">Account Number</label>
                            <input type="text" class="form-control" id="account_number" name="account_number" value="">
                        </div>
                        <div class="col-md-6">
                            <label for="name" class="form-label">IFSC</label>
                            <input type="text" class="form-control" id="ifsc" name="ifsc" value="">
                        </div>
                        <div class="col-md-6">
                            <label for="name" class="form-label">Invoice Prefix</label>
                            <input type="text" class="form-control" id="invoice_prefix" name="invoice_prefix" >
                        </div>
                    </div>



            </div>
            <div class="modal-footer">
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
        $("#address").val($(this).data("address"))
        $("#number").val($(this).data("number"))
        $("#email").val($(this).data("email"))
        $("#bank_name").val($(this).data("bank_name"))
        $("#account_number").val($(this).data("account_number"))
        $("#ifsc").val($(this).data("ifsc"))
        $("#invoice_prefix").val($(this).data("invoice_prefix"))
        $(".btnname").html("Update Company");
        $(".modal-title").html("Update Company");
        $(".bd-example-modal-lg").modal("show");
    });

    $(document).on("click", ".addCompany", function() {

        $(".btnname").html("Add Company");
        $(".modal-title").html("Add Company");

        $('#companyform')[0].reset();
    });
</script>
