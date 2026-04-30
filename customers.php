<?php
    include "Layouts/Header.php";
    include "Layouts/Sidebar.php";
    if (isset($_POST['btnadd'])) 
    {
        if (!empty($_POST['id'])) 
        {
            try 
            {
                $stmt = $mysqli->prepare("UPDATE customers set name=?,number=?,email=?,address=?,city=?,state=?,pincode=?,country=?,gst_no=?,pan_number=?,number2=?,email2=?,birthday=?,anniversary=?,pre_name=? WHERE id=?");
                $stmt->bind_param("sssssssssssssssi", $_POST['name'],  $_POST['number'], $_POST['email'], $_POST['address'], $_POST['city'], $_POST['state'], $_POST['pincode'], $_POST['country'], $_POST['gst_no'], $_POST['pan_number'],$_POST['number2'],$_POST['email2'],$_POST['birthday'],$_POST['anniversary'], $_POST['pre'],$_POST['id']);
                $stmt->execute();
                $stmt->close();
                alert("Save Successfully", "success", "success");
                redirect("customers.php");
            } 
            catch (Exception $e) 
            {
                alert($e->getMessage(), "error", "error");
                redirect("customers.php");
            }
        }
        else 
        {
            try 
            {
                $stmt = $mysqli->prepare("INSERT into `customers` (`name`,`number`,`email`,`address`,`city`,`state`,`pincode`,`country`,`user_id`,`gst_no`,`pan_number`,`number2`,`email2`,`birthday`,`anniversary`,`pre_name`)values (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
                $stmt->bind_param("ssssssssisssssss", $_POST['name'],  $_POST['number'], $_POST['email'], $_POST['address'], $_POST['city'], $_POST['state'], $_POST['pincode'], $_POST['country'], $user['id'], $_POST['gst_no'], $_POST['pan_number'],$_POST['number2'],$_POST['email2'],$_POST['birthday'],$_POST['anniversary'],$_POST['pre']);
                $stmt->execute();

            
                alert("Save Successfully", "success", "success");
                redirect("customers.php");
            } 
            catch (Exception $e) 
            {
                alert($e->getMessage(), "error", "error");
                redirect("customers.php");
            }
        }
    }

    if (isset($_POST['btnSubmit'])) 
    {
        $error = '';
        if (empty($_POST['transfer_user']) && $_POST['transfer_user'] == '')
            $error .= 'User field is required';
        if (empty($_POST['checked'])) 
        {
            $error .= 'Select at-least one lead from the table.';
        } 
        else 
        {
            if (count($_POST['checked']) <= 0) 
            {
                $error .= 'Select at-least one lead from the table.';
            }
        }

        if ($error == '') 
        {
            $lead_count = count($_POST['checked']);
            $checkedLeads = implode(',', $_POST['checked']);
            try 
            {
                for ($i = 0; $i < $lead_count; $i++) 
                {
                    $stmt = $mysqli->prepare("UPDATE customers SET user_id=? where id =?");
                    $stmt->bind_param("ii", $_POST['transfer_user'], $_POST['checked'][$i]);
                    $stmt->execute();
                }
                alert('allocated successfully', 'success', 'Allocated !!!');
                redirect('?');
            } 
            catch (\Throwable $th) 
            {
                alert($th->getMessage(), 'error', 'ERROR !!!');
                redirect('?');
            }
        } 
        else 
        {
            alert($error, 'error', 'ERROR !!!');
            redirect('?');
        }
    }
?>

<div class="content-body">
    <div class="container-fluid">
        <!--row-->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Customer</h4>
                        <button type="button" class="btn btn-square btn-outline-danger" data-bs-toggle="modal"
                            data-bs-target=".bd-example-modal-lg">Add Customer</button>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="example" class="display" style="min-width: 845px">
                                <thead>
                                    <tr>
                                        <th>S.No</th>
                                        <th> Name</th>
                                        <th>Number</th>
                                        <th>Email</th>
                                        <th>Address</th>
                                        <th>GST No</th>
                                        <th>Created</th>
                                        <th>Updated</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>

                                    <?php
                                    $stmt = $mysqli->prepare("SELECT a.*,b.name as user from  customers a join users b on a.user_id=b.id  order by a.id desc");
                                    $stmt->execute();
                                    $res = $stmt->get_result();
                                    $sno = 1;
                                    while ($row = $res->fetch_assoc()) 
                                    {
                                ?>
                                    <tr>
                                        <td>
                                            <?= $sno++; ?>
                                        </td>
                                        <td>
                                            <?= $row['name']; ?>
                                        </td>
                                        <td>
                                            <?= $row['number']; ?>
                                        </td>
                                        <td>
                                            <?= $row['email']; ?>
                                        </td>
                                        <td>
                                            <?= $row['address'] . ' ' . $row['city'] . ' ' . $row['state'] . ' ' . $row['pincode'] . ' ' . $row['country'] ?>
                                        </td>
                                        <td>
                                            <?= $row['gst_no']; ?>
                                        </td>
                                        <td>
                                            <?= $row['created_at']; ?>
                                        </td>
                                        <td>
                                            <?= $row['updated_at']; ?>
                                        </td>
                                        <td>
                                            <button type="button"
                                                class="btn btn-sm btn-primary  edit shadow btn-xs sharp"
                                                data-id="<?= $row['id'] ?>" data-name="<?= $row['name'] ?>"
                                                data-number="<?= $row['number'] ?>" data-email="<?= $row['email'] ?>"
                                                data-address="<?= $row['address'] ?>" data-city="<?= $row['city'] ?>"
                                                data-state="<?= $row['state'] ?>" data-pincode="<?= $row['pincode'] ?>"
                                                data-country="<?= $row['country'] ?>"
                                                data-gst_no="<?= $row['gst_no'] ?>"
                                                data-pan_number="<?= $row['pan_number'] ?>"
                                                data-number2="<?= $row['number2'] ?>"
                                                data-email2="<?= $row['email2'] ?>"
                                                data-birthday="<?= $row['birthday'] ?>"
                                                data-anniversary="<?= $row['anniversary'] ?>"
                                                data-pre="<?= $row['pre_name'] ?>"><i class="fa fa-pen"></i></button>
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

<div class="modal fade bd-example-modal-lg" id="exampleModalCustomer" tabindex="-1" role="dialog" aria-hidden="true">
    <form class="needs-validation" novalidate method="POST">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Add Customer</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal">
                    </button>
                </div>
                <div class="modal-body">


                    <div class="row">
                        <input id="id" name="id" type="hidden">
                        <div class="col-md-2">
                            <label for="">Select</label>
                            <select name="pre" id="pre" class="form-control ">
                                <option value="Mr.">Mr.</option>
                                <option value="Mrs.">Mrs.</option>
                                <option value="Miss.">Miss.</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label>Name</label>

                            <input type="text" name="name" id="name" required class="form-control"
                                placeholder="Enter name">

                        </div>
                        <div class="col-md-6">
                            <label>Number</label>
                            <input type="tel" name="number" id="number" class="form-control"
                                placeholder="Enter mobile number" pattern="\d{10}" minlength="10" maxlength="10"
                                required title="Please enter a 10-digit mobile number">


                        </div>
                        <div class="col-md-6 mt-2">
                            <label>Number 2</label>
                            <input type="number" name="number2" id="number2"
                                onkeypress="if(this.value.length==10) return false" class="form-control"
                                placeholder="Enter mobile number">

                        </div>
                        <div class="col-md-6 mt-2">
                            <label>Email</label>
                            <input type="email" name="email" id="email" class="form-control" placeholder="Enter email">

                        </div>
                        <div class="col-md-6 mt-2">
                            <label>Email 2</label>
                            <input type="email" name="email2" id="email2" class="form-control"
                                placeholder="Enter email">

                        </div>
                        <div class="col-md-3 mt-2">
                            <label>Birthday</label>
                            <input type="date" name="birthday" id="birthday" class="form-control"
                                placeholder="Enter email">

                        </div>
                        <div class="col-md-3 mt-2">
                            <label>Anniversary</label>
                            <input type="date" name="anniversary" id="anniversary" class="form-control"
                                placeholder="Enter email">

                        </div>
                        <div class="col-md-12 mt-2">
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
                            <label>GST No</label>
                            <input type="" name="gst_no" id="gst_no" class="form-control" placeholder="Enter Gst">

                        </div>
                        <div class="col-md-6 mt-2">
                            <label>Pan No</label>
                            <input type="" name="pan_number" id="pan_number" class="form-control"
                                placeholder="Enter Pan Number">

                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger light" data-bs-dismiss="modal">Close</button>
                    <button type="submit" name="btnadd" id="btnadd" class="btn btn-primary btnname">Save
                        changes</button>
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
    $("#email").val($(this).data("email"))
    $("#address").val($(this).data("address"))
    $("#state").val($(this).data("state"))
    $("#city").html('<option value=' + $(this).data("city") + '>' + $(this).data("city") + '</option>')
    $("#country").val($(this).data("country"))
    $("#pincode").val($(this).data("pincode"))
    $("#gst_no").val($(this).data('gst_no'))
    $("#pan_number").val($(this).data('pan_number'))
    $("#number2").val($(this).data('number2'))
    $("#email2").val($(this).data('email2'))
    $("#birthday").val($(this).data('birthday'))
    $("#anniversary").val($(this).data('anniversary'))
    $("#pre").val($(this).data('pre'))

    $(".btnname").html('Update Customer');
    $("#exampleModalCustomer").modal('show');

});
$(document).on("click", "#add_notice", function() {
    let id = $("#id").val();
    if (id != false) {
        $("#id").val("")
        $("input").val("")
        $("textarea").val("")

    }
    $(".btnname").html('Add Customer');
    $("#exampleModal").modal('show');
    $("#country").val('India')
})

function checkall() {
    console.log('')
    if ($('#all-check').is(':checked')) {
        $('.checked').prop('checked', true);
    } else {
        $('.checked').prop('checked', false);
    }
}
</script>