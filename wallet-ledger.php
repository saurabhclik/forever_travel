<?php
    include "Layouts/Header.php";
    include "Layouts/Sidebar.php";

    $credit_amount['credit_amount']= null ?? 0;
    $debit_amount['debit_amount']=null ?? 0;
    $users_wallet['wallet_amt']=null ?? 0;

   if(isset($_POST['btnadd']))
{
    // Check required fields
    if($_POST['customer_id'] == "" || $_POST['expense_category'] == "" || $_POST['expense_subcategory'] == "" || $_POST['amount'] == "" || $_POST['date'] == "" || $_POST['payment_status'] == "")
    {
        alert("Please fill all required fields.", "warning", "warning");
        redirect("?");
    }

    // If edit_id is set — update existing payment
    if(!empty($_POST['id']))  
    {
        $stmt = $mysqli->prepare("UPDATE wallet_payments SET customer_id = ?, expense_category = ?, expense_sub_category = ?, amount = ?, date = ?, payment_status = ? WHERE id = ?");
        $stmt->bind_param("isssssi", $_POST['customer_id'], $_POST['expense_category'], $_POST['expense_subcategory'], $_POST['amount'], $_POST['date'], $_POST['payment_status'], $_POST['id']);

        if($stmt->execute())
        {
            $_SESSION['alert'] = [
                'title' => 'Payment Updated',
                'text'  => 'Payment successfully updated.',
                'icon'  => 'success'
            ];
            redirect("?staff_id=".$_GET['staff_id']."&fromDt=".$_GET['fromDt']."&toDt=".$_GET['toDt']."");
        }
        else
        {
            $_SESSION['alert'] = [
                'title' => 'Error',
                'text'  => 'Failed to update payment.',
                'icon'  => 'error'
            ];
            redirect("?staff_id=".$_GET['staff_id']."&fromDt=".$_GET['fromDt']."&toDt=".$_GET['toDt']."");
        }
    }
    else  // Otherwise, it's an insert (your existing code)
    {
        $stmt = $mysqli->prepare("INSERT INTO wallet_payments(customer_id, expense_category, expense_sub_category, amount, date, payment_status, user_id) VALUES (?,?,?,?,?,?,?)");
        $stmt->bind_param("isssssi", $_POST['customer_id'], $_POST['expense_category'], $_POST['expense_subcategory'], $_POST['amount'], $_POST['date'], $_POST['payment_status'], $_SESSION['id']);

        if ($stmt->execute()) 
        {
            $stmt = $mysqli->prepare("UPDATE users SET wallet_amt = wallet_amt - ? WHERE id = ?");
            $stmt->bind_param("di", $_POST['amount'], $_SESSION['id']);
            $stmt->execute();

            $_SESSION['alert'] = [
                'title' => 'Payment Added',
                'text'  => 'Payment successfully added.',
                'icon'  => 'success'
            ];
            redirect("?staff_id=".$_GET['staff_id']."&fromDt=".$_GET['fromDt']."&toDt=".$_GET['toDt']."");
        } 
        else 
        {
            $_SESSION['alert'] = [
                'title' => 'Error',
                'text'  => 'Failed to add payment.',
                'icon'  => 'error'
            ];
            redirect("?staff_id=".$_GET['staff_id']."&fromDt=".$_GET['fromDt']."&toDt=".$_GET['toDt']."");
        }
    }
}



    if (isset($_GET['staff_id'])) 
    {
        $stmt = $mysqli->prepare("SELECT * from  `users`  where id=?");
        $stmt->bind_param("i", $_GET['staff_id']);
        $stmt->execute();
        $users_wallet = $stmt->get_result()->fetch_assoc();

        $stmt = $mysqli->prepare("select wallet_payments.*, customers.name as customer_name  , users.name as user_name , expense_category.name as expense_category_name , expense_subcategory.name as expense_subcategory from wallet_payments left join customers on wallet_payments.customer_id = customers.id left join users on wallet_payments.user_id = users.id left join expense_category on wallet_payments.expense_category = expense_category.id left join expense_subcategory on wallet_payments.expense_sub_category = expense_subcategory.id  where wallet_payments.user_id=? and date(date)>=date(?) and date(date)<=date(?)");
        $stmt->bind_param("iss",$_GET['staff_id'], $_GET['fromDt'], $_GET['toDt']);
        $stmt->execute();
        $expenses = $stmt->get_result();

        // $row = $expenses->fetch_assoc();
        // echo "<pre>";
        // print_r($row); exit;

        

        $stmt = $mysqli->prepare("SELECT sum(amount) as credit_amount from  wallet_history where customer_id=? and date(created_at)>=date(?) and date(created_at)<=date(?)");
        $stmt->bind_param("iss", $_GET['staff_id'], $_GET['fromDt'], $_GET['toDt']);
        $stmt->execute();
        $credit_amount = $stmt->get_result()->fetch_assoc();

        // $stmt = $mysqli->prepare("SELECT sum(amount) as debit_amount from  expenses a join customers b on a.ids=b.id  where a.user_id=? and date(a.created_at)>=date(?) and date(a.created_at)<=date(?)");
        // $stmt->bind_param("iss", $_GET['staff_id'], $_GET['fromDt'], $_GET['toDt']);
        $stmt = $mysqli->prepare("SELECT sum(amount) as debit_amount from  wallet_payments where user_id=? and date(date)>=date(?) and date(date)<=date(?)");
        $stmt->bind_param("iss", $_GET['staff_id'], $_GET['fromDt'], $_GET['toDt']);
        $stmt->execute();
        $debit_amount = $stmt->get_result()->fetch_assoc();

        $stmt = $mysqli->prepare("SELECT * from  wallet_history where customer_id=? and date(created_at)>=date(?) and date(created_at)<=date(?)");
        $stmt->bind_param("iss", $_GET['staff_id'], $_GET['fromDt'], $_GET['toDt']);
        $stmt->execute();
        $wallet_history = $stmt->get_result();
    }
?>

<div class="content-body">
    <div class="container-fluid">
        <!-- row -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Wallet Ledger</h4>
                        <form>
                            <div class="m-2 d-flex gap-2">
                                <div class="col-2">
                                    <select class="form-control" name="staff_id" id="staff_id" required>
                                        <option value="">Select</option>
                                        <?php
                                        if($_SESSION['user'] == 'admin' || $_SESSION['user'] == 'Team Manager'){

                                            $stmt = $mysqli->prepare("SELECT * from  users  where role='staff' or role= 'Team Manager'");
                                        }else{
                                            $stmt = $mysqli->prepare("SELECT * from  users  where id=?");
                                            $stmt->bind_param("i", $_SESSION['id']);
                                        }
                                         
                                            $stmt->execute();
                                            $res = $stmt->get_result();
                                            while ($row = $res->fetch_assoc()) 
                                            {
                                                echo '<option value="' . $row['id'] . '">' . $row['name'] . '</option>';
                                            }
                                        ?>
                                    </select>
                                </div>
                                <div class="col-3">
                                    <input type="date" class="form-control" name="fromDt" id="fromDt" required>
                                </div>
                                <div class="col-3">
                                    <input type="date" class="form-control" name="toDt" id="toDt" required>
                                </div>
                                <button type="submit" class="btn btn-primary">
                                    Search
                                </button>
                                <a href="wallet-ledger.php">
                                    <button type="button" class="btn btn-primary">
                                        <i class="fa fa-eraser"></i>
                                    </button>
                                </a>
                                <div class="col-md-3">
                                    <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                                        data-bs-target="#AddSelfExpense">
                                        Add Expenses
                                    </button>
                                </div>
                                <!-- Button trigger modal -->




                            </div>
                        </form>
                    </div>
                    <div class="row justify-content-center">
                        <div class="col-lg-12">
                            <div class="card border-0 overflow-hidden">
                                <div class="card-body p-0">
                                    <div class="row g-0">
                                        <div class="col-md-4 p-4 border-end border-light">
                                            <div class="d-flex align-items-center mb-3">
                                                <div class="bg-success bg-opacity-10 p-3 me-3">
                                                    <i class="fas fa-arrow-down text-success fs-4"></i>
                                                </div>
                                                <div>
                                                    <h6 class="card-subtitle text-muted mb-1">Total Credit</h6>
                                                    <h3 class="card-title text-success fw-bold mb-0">
                                                        <?= number_format($credit_amount['credit_amount'] ?? 0, 2) ?>
                                                    </h3>
                                                </div>
                                            </div>
                                            <div class="progress" style="height: 6px;">
                                                <!-- <div class="progress-bar bg-success" role="progressbar"
                                                style="width: <?= number_format($credit_amount['credit_amount'], 2) ?>%"
                                                aria-valuenow="<?= number_format($credit_amount['credit_amount'], 2) ?>"
                                                aria-valuemin="0" aria-valuemax="100"></div> -->
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-4 p-4 border-end border-light">
                                            <div class="d-flex align-items-center mb-3">
                                                <div class="bg-danger bg-opacity-10 p-3 rounded-3 me-3">
                                                    <i class="fas fa-arrow-up text-danger fs-4"></i>
                                                </div>
                                                <div>
                                                    <h6 class="card-subtitle text-muted mb-1">Total Debit</h6>
                                                    <h3 class="card-title text-danger fw-bold mb-0">
                                                        <?= number_format($debit_amount['debit_amount'] ?? 0, 2) ?>
                                                    </h3>
                                                </div>
                                            </div>
                                            <div class="progress" style="height: 6px;">
                                                <!-- <div class="progress-bar bg-danger" role="progressbar"
                                                    style="width:<?= number_format($debit_amount['debit_amount'], 2) ?>%"
                                                    aria-valuenow="<?= number_format($debit_amount['debit_amount'], 2) ?>"
                                                    aria-valuemin="0" aria-valuemax="100"></div> -->
                                            </div>
                                        </div>

                                        <div class="col-md-4 p-4">
                                            <div class="d-flex align-items-center mb-3">
                                                <div class="bg-primary bg-opacity-10 p-3 rounded-3 me-3">
                                                    <i class="fa fa-wallet text-danger fs-4"></i>
                                                </div>
                                                <div>
                                                    <h6 class="card-subtitle text-muted mb-1">Current Balance</h6>
                                                    <h3 class="card-title text-primary fw-bold mb-0">
                                                        <?= number_format($users_wallet['wallet_amt'], 2) ?>
                                                    </h3>
                                                </div>
                                            </div>
                                            <div class="progress" style="height: 6px;">
                                                <div class="progress-bar bg-primary" role="progressbar"
                                                    style="width: <?= number_format($users_wallet['wallet_amt'], 2) ?>%"
                                                    aria-valuenow="<?= number_format($users_wallet['wallet_amt'], 2) ?>"
                                                    aria-valuemin="0" aria-valuemax="100"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer bg-light py-3">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <small class="text-muted">Last updated:
                                            <?= isset($_GET['staff_id']) && !empty($_GET['staff_id']) ? date('M d, Y H:i', strtotime($users_wallet['created_at'])) : '' ?></small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="example" class="display" style="min-width: 845px">
                                <thead>
                                    <tr>
                                        <th>S.No</th>
                                        <th>Customer Name</th>
                                        <th>Expense Category</th>
                                        <th>Expense Sub Category</th>
                                        <th>Amount</th>
                                        <th>Created at</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $sno = 1;
                                    if(!empty($expenses))
                                    {
                                       while ($row = $expenses->fetch_assoc()) 
{
    echo '<tr>
        <td>' . $sno++ . '</td>
        <td>' . $row['customer_name'] . '</td>
        <td>' . $row['expense_category_name'] . '</td>
        <td>' . $row['expense_subcategory'] . '</td>
        <td>' . $row['amount'] . '</td>
        <td>' . $row['created_at'] . '</td>
        <td>
           <button 
    class="btn btn-outline-danger edit" 
    data-id="' . $row['id'] . '" 
    data-customer_id="' . $row['customer_id'] . '"
    data-expense_category="' . $row['expense_category'] . '" 
    data-expense_subcategory="' . $row['expense_sub_category'] . '" 
    data-amount="' . $row['amount'] . '" 
    data-payment_status="' . $row['payment_status'] . '" 
    data-date="' . $row['date'] . '"
>Edit</button>

        </td>
    </tr>';
}


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
                            <input type="number" name="number" id="number"
                                onkeypress="if(this.value.length==10) return false" class="form-control"
                                placeholder="Enter mobile number" required>

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

<div class="modal fade AddSelfExpense" id="AddSelfExpense" tabindex="-1" role="dialog" aria-hidden="true">
    <form class="needs-validation" novalidate method="POST">
        <div class="modal-dialog  modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="modal-title fs-5" id="exampleModalLabel"> <span class="btnname"> Add Payment</span></h3>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">

                    </button>
                </div>
                <div class="modal-body">

                    <div class="">
                        <input id="edit_id" name="id" type="hidden">
                         <label>Clients</label>
                        <select class="" id="customer_id" name="customer_id" value="" required>
                            <option value="">Select Client</option>
                            <?php
                                    $stmt = $mysqli->prepare("SELECT * from  customers  order by id desc");
                                    $stmt->execute();
                                    $res = $stmt->get_result();

                                    while ($row = $res->fetch_assoc()) 
                                    {
                                        echo '<option value="' . $row['id'] . '"   data-number="' . $row['number'] . '"    data-email="' . $row['email'] . '">' . $row['name'] . '</option>';
                                    }
                                ?>
                        </select>
                    </div>
                      
                         <div class=" mt-3 category">
                            <label>Expense Category</label>
                            <select name="expense_category" id="expense_category" class="form-control" required>
                                <option value="">Select</option>
                                <?php
                    $stmt = $mysqli->prepare("SELECT * FROM expense_category WHERE active=1");
                    $stmt->execute();
                    $category = $stmt->get_result();
                    while ($row = $category->fetch_assoc()) {
                        echo '<option value="' . $row['id'] . '">' . $row['name'] . '</option>';
                    }
                ?>
                            </select>
                        </div>

                        <div class=" mt-3 category" required>
                            <label>Expense Sub Category</label>
                            <select name="expense_subcategory" id="expense_subcategory" class="form-control" required>
                                <option value="">Select</option>
                            </select>
                        </div>
                        <div class=" mt-3 category">
                            <label>Amount</label>
                            <input type="number" name="amount" id="amount" class="form-control"
                                placeholder="Enter Amount" required>
                        </div>
                        <div class=" mt-3 category">
                            <label>Date</label>
                            <input type="date" name="date" id="date" class="form-control"
                            required>
                        </div>
                        <div class=" mt-3 category">
                            <label>Payment Status</label>
                            <select name="payment_status" class="form-control" id="payment_status" required>
                                <option value="Pending">Pending</option>
                                <option value="Complete">Complete</option>
                            </select>
                        </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary " data-bs-dismiss="modal">Close</button>
                    <button type="submit" class=" btn btn-square btn-outline-success" name="btnadd" id="btnadd"><span class="btnname"> Submit</span></button>
                </div>
            </div>
        </div>
    </form>
</div>
<?php include "Layouts/Footer.php"  ?>




<script>
    
   $('.AddSelfExpense').on('shown.bs.modal', function() {
  
    $('#customer_id').select2({
        dropdownParent: $('#AddSelfExpense')
    });
});

  $("#expense_category").on("change", function() {

    id = $(this).val();
    // alert(id);

        $.ajax({
            method: "POST",
            url: "ajax/get-expense-sub-category.php",
            data: {
                id: id
            },
            success: function(data) {
                $("#expense_subcategory").html(data);
            }
        });
    });
$(document).on('click', '.edit', function () {
    var id = $(this).data('id');
    var customer_id = $(this).data('customer_id');
    var expense_category = $(this).data('expense_category');
    var expense_subcategory = $(this).data('expense_subcategory');
    var amount = $(this).data('amount');
    var date = $(this).data('date');
    var payment_status = $(this).data('payment_status');

    $('#edit_id').val(id);
    $('#customer_id').val(customer_id);
    $('#expense_category').val(expense_category);
    $('#expense_subcategory').val(expense_subcategory);
    $('#amount').val(amount);
    $('#date').val(date);
    $('#payment_status').val(payment_status);


    $('#AddSelfExpense').modal('show');
});




</script>