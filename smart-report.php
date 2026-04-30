<?php
    include "Layouts/Header.php";
    include "Layouts/Sidebar.php";
?>

<div class="content-body">
    <div class="container-fluid">
        <!-- row -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Customer Wise Sale Report</h4>
                        <button type="button" class="btn btn-square btn-outline-danger"  onclick="printsave()">
                            <i class="fa fa-print" aria-hidden="true"></i> Print</button>
                    </div>
                    <div class="card-body border-bottom">
                        <form class="row g-3 align-items-end">
                            <div class="col-md-5">
                                <div class="form-group">
                                    <label for="customer_id" class="form-label fw-bold">Customer Name</label>
                                    <select class="form-select select2" id="customer_id" name="customer_id" required>
                                        <option value="" disabled selected>Select Customer</option>
                                        <?php
                                            $stmt = $mysqli->prepare("SELECT * FROM `customers`");
                                            $stmt->execute();
                                            $category = $stmt->get_result();
                                            while ($row = $category->fetch_assoc()) {
                                                echo '<option value="' . $row['id'] . '">' . $row['name'] . '</option>';
                                            }
                                        ?>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="col-md-5">
                                <div class="form-group">
                                    <label for="invoice_id" class="form-label fw-bold">Customer Event</label>
                                    <select class="form-select select2" id="invoice_id" name="invoice_id">
                                        <option value="" disabled selected>Select Event</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-2 d-flex gap-3">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fa fa-search me-2"></i>Search
                                </button>
                                <a href="smart-report.php" class="btn btn-primary">
                                    <i class="fa fa-refresh"></i>
                                </a>
                            </div>
                        </form>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="example" class="display" style="min-width: 845px">
                                <thead>
                                    <tr>
                                        <th>S.No</th>
                                        <th>Name</th>
                                        <th>Phone Number</th>
                                        <th>Email</th>                            
                                        <th>Date Created</th>
                                    </tr>
                                </thead>
                                <tbody>                              
                                <?php
                                    $stmt = $mysqli->prepare("SELECT a.*  from  vendor a ");
                                    $stmt->execute();
                                    $res = $stmt->get_result();
                                    $sno = 1;
                                    while ($row = $res->fetch_assoc()) {
                                        echo '<tr>
                                                <td>' . $sno++ . '</td>
                                                <td>' . $row['name'] . '</td>
                                                <td>' . $row['number'] . '</td>
                                                <td>' . $row['email'] . '</td>
                                            
                                                <td>' . $row['created_at'] . '</td>
                                            </tr>';
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

<div class="modal fade bd-example-modal-lg" id="exampleModal" tabindex="-1" role="dialog" aria-hidden="true">
    <form class="needs-validation" novalidate method="POST">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"  id="exampleModalLabel">Add Customer</h5>
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

                            <input type="text" name="name" id="name" required class="form-control" placeholder="Enter name">

                        </div>
                        <div class="col-md-6">
                            <label>Number</label>
                            <input type="number" name="number" id="number" onkeypress="if(this.value.length==10) return false" class="form-control" placeholder="Enter mobile number" required>

                        </div>
                        <div class="col-md-6 mt-2">
                            <label>Number 2</label>
                            <input type="number" name="number2" id="number2" onkeypress="if(this.value.length==10) return false" class="form-control" placeholder="Enter mobile number" >

                        </div>
                        <div class="col-md-6 mt-2">
                            <label>Email</label>
                            <input type="email" name="email" id="email" class="form-control" placeholder="Enter email">

                        </div>
                        <div class="col-md-6 mt-2">
                            <label>Email 2</label>
                            <input type="email" name="email2" id="email2" class="form-control" placeholder="Enter email">

                        </div>
                        <div class="col-md-3 mt-2">
                            <label>Birthday</label>
                            <input type="date" name="birthday" id="birthday" class="form-control" placeholder="Enter email">

                        </div>
                        <div class="col-md-3 mt-2">
                            <label>Anniversary</label>
                            <input type="date" name="anniversary" id="anniversary" class="form-control" placeholder="Enter email">

                        </div>
                        <div class="col-md-12 mt-2">
                            <label>Address</label>
                            <textarea type="address" name="address" id="address" class="form-control" placeholder="Enter full address"></textarea>

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
                            <input type="number" name="pincode" id="pincode" class="form-control" placeholder="Enter Pincode">

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
                            <input type="" name="pan_number" id="pan_number" class="form-control" placeholder="Enter Pan Number">

                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger light" data-bs-dismiss="modal">Close</button>
                    <button type="submit"  name="btnadd" id="btnadd" class="btn btn-primary btnname">Save changes</button>
                </div>
            </div>
        </div>
    </form>
</div>

<?php include "Layouts/Footer.php"  ?>

<script>
    $(document).on("click", ".edit", function() 
    {
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
        $("#exampleModal").modal('show');

    });
    $(document).on("click", "#add_notice", function() 
    {
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

    function checkall() 
    {
        console.log('')
        if ($('#all-check').is(':checked')) 
        {
            $('.checked').prop('checked', true);
        } 
        else 
        {
            $('.checked').prop('checked', false);
        }
    }
</script>