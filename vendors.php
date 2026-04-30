<?php  include "Layouts/Header.php"; ?>
<?php  include "Layouts/Sidebar.php"; ?>
<?php

if (isset($_POST['btnadd'])) {

    if (!empty($_POST['id'])) {
        try {
            $stmt = $mysqli->prepare("UPDATE vendor set name=?,number=?,email=?,address=?,city=?,state=?,pincode=?,country=?,pan_number=?,user_type=? WHERE id=?");
            $stmt->bind_param("ssssssssssi", $_POST['name'],  $_POST['number'], $_POST['email'], $_POST['address'], $_POST['city'], $_POST['state'], $_POST['pincode'], $_POST['country'], $_POST['pan_number'], $_POST['user_type'], $_POST['id']);
            $stmt->execute();
            $stmt->close();
            alert("Save Successfully", "success", "success");
            redirect("vendors.php");
        } catch (Exception $e) {
            alert($e->getMessage(), "error", "error");
            redirect("vendors.php");
        }
    } else {

        try {

            $stmt = $mysqli->prepare("INSERT into vendor (name,number,email,address,city,state,pincode,country,user_id,pan_number,user_type)values (?,?,?,?,?,?,?,?,?,?,?)");
            $stmt->bind_param("ssssssssiss", $_POST['name'],  $_POST['number'], $_POST['email'], $_POST['address'], $_POST['city'], $_POST['state'], $_POST['pincode'], $_POST['country'], $user['id'], $_POST['pan_number'], $_POST['user_type']);
            $stmt->execute();
            alert("Save Successfully", "success", "success");
            redirect("vendors.php");
        } catch (Exception $e) {
            alert($e->getMessage(), "error", "error");
            redirect("vendors.php");
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
                        <!-- <h4 class="card-title"></h4> -->
                        <div class="col-3 card-title">
                            <label for="">Select Type</label>
                            <form method="GET" id="formquery">

                                <select name="query" id="query" class="form-control">
                                    <option value="">All</option>
                                    <option value="vendor">Vendor</option>
                                    <option value="labour">Labour</option>
                                </select>
                            </form>
                        </div>
                        <button type="button" class="btn btn-square btn-outline-danger" data-bs-toggle="modal"
                            data-bs-target=".bd-example-modal-lg">Add Vendor</button>

                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="example" class="display" style="min-width: 845px">
                                <thead>
                                    <tr>
                                        <th>S.No</th>
                                        <th> Vendor/Labour</th>
                                        <th> Name</th>
                                        <th>Number</th>

                                        <th>Email</th>
                                        <th>Address</th>
                                        <th>Created</th>
                                        <th>Updated</th>

                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php


                        if (!empty($_GET['query'])) {
                            $stmt = $mysqli->prepare("SELECT * from  vendor  where user_type=? order by id desc");
                            $stmt->bind_param('s', $_GET['query']);
                            $stmt->execute();
                        } else {
                            $stmt = $mysqli->prepare("SELECT * from  vendor  order by id desc");
                            $stmt->execute();
                        }
                        $res = $stmt->get_result();
                        $sno = 1;
                        while ($row = $res->fetch_assoc()) {
                        ?>
                                    <tr>
                                        <td>
                                            <?= $sno++; ?>
                                        </td>
                                        <td><?= $row['user_type']; ?></td>
                                        <td><?= $row['name']; ?></td>
                                        <td><?= $row['number']; ?></td>
                                        <td><?= $row['email']; ?></td>
                                        <td><?= $row['address'] . ' ' . $row['city'] . ' ' . $row['state'] . ' ' . $row['pincode'] . ' ' . $row['country'] ?>
                                        </td>




                                        <td>
                                            <?= $row['created_at']; ?>
                                        </td>
                                        <td>
                                            <?= $row['updated_at']; ?>
                                        </td>
                                        <td><button class="btn btn-primary shadow btn-xs sharp me-1 edit"
                                                data-id="<?= $row['id'] ?>" data-name="<?= $row['name'] ?>"
                                                data-number="<?= $row['number'] ?>" data-email="<?= $row['email'] ?>"
                                                data-address="<?= $row['address'] ?>" data-city="<?= $row['city'] ?>"
                                                data-state="<?= $row['state'] ?>" data-pincode="<?= $row['pincode'] ?>"
                                                data-country="<?= $row['country'] ?>"
                                                data-pan_number="<?= $row['pan_number'] ?>"
                                                data-user_type="<?= $row['user_type'] ?>"><i
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
    <form class="needs-validation" novalidate method="POST">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="modal-title fs-5" id="exampleModalLabel"> <span class="btnname"> Vendor/Labour</span>
                    </h3>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">

                    </button>
                </div>
                <div class="modal-body">

                    <div class="row">

                        <input id="id" name="id" type="hidden">

                        <div class="col-md-6 mt-2">
                            <label>Select Vendor/Labour</label>

                            <select class="form-control" id="user_type" name="user_type" required>
                                <option value="" disabled selected>Select</option>
                                <option value="Vendor">Vendor</option>
                                <option value="Labour">Labour</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label>Name</label>
                            <input type="text" name="name" id="name" required class="form-control"
                                placeholder="Enter name">

                        </div>
                        <div class="col-md-6">
                            <label>Number</label>
                            <input type="number" name="number" id="number"
                                onkeypress="if(this.value.length==10) return false" required class="form-control"
                                placeholder="Enter mobile number">

                        </div>
                        <div class="col-md-6 mt-2">
                            <label>Email</label>
                            <input type="email" name="email" id="email" class="form-control" placeholder="Enter email">

                        </div>
                        <div class="col-md-6 mt-2">
                            <label>Address</label>
                            <textarea type="address" name="address" id="address" class="form-control"
                                placeholder="Enter full address"></textarea>

                        </div>
                        <div class="col-md-6 mt-2">
                            <label>State</label>

                            <select class="form-control" id="state" name="state">
                                <option value="" disabled selected>Select State</option>
                                <?php
                                $stmt = $mysqli->prepare("SELECT  distinct state FROM `state_district`");
                                //   $stmt->bind_param("s", $_COOKIE['token']);
                                $stmt->execute();
                                $category = $stmt->get_result();
                                while ($row = $category->fetch_assoc()) {
                                    echo '<option value="' . $row['state'] . '">' . $row['state'] . '</option>';
                                }

                                ?>
                            </select>
                        </div>
                        <div class="col-md-6 mt-2">
                            <label>City</label>

                            <select class="form-control" id="city" name="city">
                                <option value="" disabled selected>Select City</option>
                            </select>
                        </div>

                        <div class="col-md-6 mt-2">
                            <label>Pincode</label>
                            <input type="number" name="pincode" id="pincode" class="form-control"
                                placeholder="Enter Pincode">

                        </div>
                        <div class="col-md-6 mt-2">
                            <label>Country</label>

                            <select class="form-control" id="country" name="country">
                                <option value="" disabled selected>Select Country</option>
                                <?php
                                $stmt = $mysqli->prepare("SELECT * FROM countries");
                                //   $stmt->bind_param("s", $_COOKIE['token']);
                                $stmt->execute();
                                $countries = $stmt->get_result();
                                while ($row = $countries->fetch_assoc()) {
                                    echo '<option value="' . $row['en_short_name'] . '">' . $row['en_short_name'] . '</option>';
                                }

                                ?>
                            </select>
                        </div>
                        <div class="col-md-6 mt-2">
                            <label>Pan Number</label>
                            <input type="" name="pan_number" id="pan_number" class="form-control"
                                placeholder="Enter Pan Number">

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
    $("#number").val($(this).data('number'))
    $("#user_type").val($(this).data('user_type'))
    $("#email").val($(this).data("email"))
    $("#address").val($(this).data("address"))
    $("#state").val($(this).data("state"))
    $("#city").html('<option value=' + $(this).data("city") + '>' + $(this).data("city") + '</option>')
    $("#country").val($(this).data("country"))
    $("#pincode").val($(this).data("pincode"))
    $("#pan_number").val($(this).data("pan_number"))

    $(".btnname").html('Update Vendor/Labour');
    $(".bd-example-modal-lg").modal('show');

})
 $("#query").on("change", function() {
        $("#formquery").submit();
    });
    $("#query").val("<?php if (!empty($_GET['query'])) echo $_GET['query'] ?>")
</script>