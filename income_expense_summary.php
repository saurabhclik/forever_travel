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
                        <h4 class="card-title">Income Expense Summary</h4>

                    </div>
                    <div class="card-body">
                        <form action="">
                            <div class="row">


                                <div class="col-md-2">
                                    <label for="">Users</label>
                                    <select name="user" class="form-select">
                                        <option value="">Select User</option>
                                        <?php 
                                        $stmt = $mysqli->prepare("Select * from users where status = '1'");
                                        $stmt->execute();
                                        $res = $stmt->get_result();
                                        while($row = $res->fetch_assoc()):                                
                                        ?>
                                        <option value="<?= $row['id'] ?>"
                                            <?php if(isset($_GET['user']) && $_GET['user'] == $row['id']) echo 'selected'; ?>>
                                            <?= $row['username'] ?>
                                        </option>

                                        <?php endwhile; ?>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label for="">Customers</label>
                                    <select name="customer" class="form-control select2">
                                        <option value="">Select Customer</option>
                                        <?php 
                                        $stmt = $mysqli->prepare("Select * from customers");
                                        $stmt->execute();
                                        $res = $stmt->get_result();
                                        while($row = $res->fetch_assoc()):                                
                                        ?>
                                        <option value="<?= $row['id'] ?>"
                                            <?php if(isset($_GET['customer']) && $_GET['customer'] == $row['id']) echo 'selected'; ?>>
                                            <?= $row['name'] ?>
                                        </option>

                                        <?php endwhile; ?>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label for="">From Date</label>
                                    <input type="date" class="form-control" name="from_date"
                                        value="<?= isset($_GET['from_date']) ? $_GET['from_date'] : '' ?>">
                                </div>
                                <div class="col-md-2">
                                    <label for="">To Date</label>
                                    <input type="date" class="form-control" name="to_date"
                                        value="<?= isset($_GET['to_date']) ? $_GET['to_date'] : '' ?>">
                                </div>

                                <div class="col-md-1">
                                    <label for="">Submit</label>
                                    <br>
                                    <button type="submit" class="btn btn-success">Submit</button>
                                </div>
                                <div class="col-md-1">
                                    <label for="">Reset</label>
                                    <br>
                                    <a href="income_expense_summary.php" class="btn btn-danger">Reset</a>

                                </div>
                            </div>
                        </form>

                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="example" class="display" style="min-width: 845px">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Customer Lead</th>
                                        <th>User Name</th>
                                        <th>Sale Amount</th>
                                        <th>Expenses</th>
                                        <th>Payments</th>
                                        <th>Profit</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $where = "q.status = 'Converted'";
                                    $params = [];
                                    $types = "";

                                    // If user filter is set
                                    if (!empty($_GET['user'])) {
                                        $where .= " AND q.user_id = ?";
                                        $params[] = $_GET['user'];
                                        $types .= "i";
                                    }
                                    if ($_SESSION['user'] !== 'admin') {
                                        $where .= " AND q.user_id = ?";
                                        $params[] = $_SESSION['id'];
                                        $types .= "i";
                                    }

                                    // If customer filter is set
                                    if (!empty($_GET['customer'])) {
                                        $where .= " AND q.customer_id = ?";
                                        $params[] = $_GET['customer'];
                                        $types .= "i";
                                    }

                                    // If from_date is set
                                    if (!empty($_GET['from_date'])) {
                                        $where .= " AND DATE(q.created_at) >= ?";
                                        $params[] = $_GET['from_date'];
                                        $types .= "s";
                                    }

                                    // If to_date is set
                                    if (!empty($_GET['to_date'])) {
                                        $where .= " AND DATE(q.created_at) <= ?";
                                        $params[] = $_GET['to_date'];
                                        $types .= "s";
                                    }

                                    // Build the main query
                                    $query = "SELECT 
                                        q.*, 
                                        customers.name, 
                                        users.name AS user_name,
                                        COALESCE(e.total_expenses, 0) AS total_expenses, 
                                        COALESCE(p.total_payments, 0) AS total_payments, 
                                        destinations.name AS destination_name,
                                        (COALESCE(p.total_payments, 0) - COALESCE(e.total_expenses, 0)) AS profit 
                                    FROM query_mst q 
                                    LEFT JOIN (
                                        SELECT query_id, SUM(amount) AS total_expenses FROM expenses GROUP BY query_id
                                    ) e ON e.query_id = q.id 
                                    LEFT JOIN (
                                        SELECT query_id, SUM(amount) AS total_payments FROM payment GROUP BY query_id
                                    ) p ON p.query_id = q.id 
                                    LEFT JOIN customers ON customers.id = q.customer_id 
                                    LEFT JOIN destinations ON destinations.id = q.destination
                                    LEFT JOIN users ON users.id = q.user_id
                                
                                    WHERE $where
                                    ORDER BY q.id DESC";

                                    // Prepare and bind
                                    $stmt = $mysqli->prepare($query);

                                    // Bind params if any
                                    if (!empty($params)) {
                                        $stmt->bind_param($types, ...$params);
                                    }

                                    $stmt->execute();
                                    $res = $stmt->get_result();
                                    $sr_no = 1;
                                    while($row = $res->fetch_assoc()):      
                                     ?>

                                    <tr>
                                        <td><?= $sr_no;  ?></td>
                                        <td><?=$row['name'] ."---->". $row['destination_name'] ?></td>
                                        <td><?= $row['user_name']  ?></td>
                                        <td><?= $row['sale_amount']  ?></td>
                                        <td><?= $row['total_expenses'] ?></td>
                                        <td><?= $row['total_payments'] ?></td>
                                        <td><?= $row['profit'] ?></td>
                                        <td>
                                            <div class="dropdown">
                                                <a href="#" role="button" id="dropdownMenuLink"
                                                    data-bs-toggle="dropdown" aria-expanded="false">
                                                    <i class="bi bi-three-dots-vertical"></i>
                                                </a>

                                                <ul class="dropdown-menu" aria-labelledby="dropdownMenuLink">
                                                    <li> <button class="dropdown-item" id="addSaleAmount"
                                                            data-sale_amount="<?= $row['sale_amount']; ?>"
                                                            data-id="<?= $row['id']; ?>" data-bs-toggle="modal"
                                                            data-bs-target="#AddSale">
                                                            Payment History
                                                        </button></li>
                                                    <li>
                                                        <button type="button" class="dropdown-item"
                                                            data-id="<?= $row['id'] ?>" data-bs-toggle="modal"
                                                            data-bs-target="#AddPayment"
                                                            data-customer_id="<?= $row['customer_id'] ?>"
                                                            id="add_expenses">Expenses History</button>
                                                    </li>
                                                    <!-- <li>
                                                        <button type="button" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#addSaleExpense">
                                                        Add Sale | Expense 
                                                        </button>
                                                    </li> -->

                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php $sr_no++; endwhile; ?>
                                </tbody>

                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>



<div class="modal fade" id="AddSale" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <div class="modal-header d-flex align-items-center justify-content-between">
                <h5 class="modal-title" id="exampleModalLabel">Sale Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">

                <div class="row mb-3">
                    <div class="col-md-4">
                        <label for="saleAmountView" class="form-label">Sale Amount</label>
                        <input type="text" id="saleAmountView" class="form-control" disabled>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered align-middle">
                        <thead class="table-light">
                            <tr>
                                <th scope="col">#</th>
                                <th scope="col">Amount</th>
                                <th scope="col">Date</th>
                                <th scope="col">Remark</th>
                                <th scope="col">Payment Type</th>
                                <th scope="col">Action</th>
                            </tr>
                        </thead>
                        <tbody id="saleTableBody">

                        </tbody>
                    </table>
                </div>

            </div>

            <div class="modal-footer">
                <!-- <button type="button" class="btn btn-primary" id="paymentCollect">
                    Collect Payment
                </button> -->

                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>

            </div>

        </div>
    </div>
</div>

<div class="modal fade" id="AddPayment" tabindex="-1" aria-labelledby="expenseModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">

            <!-- Modal Header -->
            <div class="modal-header d-flex align-items-center justify-content-between">
                <h5 class="modal-title" id="expenseModalLabel">Expense Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <!-- Modal Body -->
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-bordered align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>User</th>
                                <th>File</th>
                                <th>Vendor</th>
                                <th>Name</th>
                                <th>Expense Type</th>
                                <th>Expense Date</th>
                                <th>Note</th>
                                <th>Paymode</th>
                                <th>Ref. No.</th>
                                <th>Amount</th>
                                <th>Billed</th>
                                <th>Paid Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="expenseTableBody">

                        </tbody>
                    </table>
                </div>
            </div>

            <div class="modal-footer">
                <!-- <button type="button" class="btn btn-primary" id="add_expense_button">
                    Add Expense
                </button> -->

                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>

        </div>
    </div>
</div>

<!-- Button trigger modal -->


<!-- Modal -->
<div class="modal fade" id="addSaleExpense" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Modal title</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        ...
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary">Save changes</button>
      </div>
    </div>
  </div>
</div>



<?php include "Layouts/Footer.php"  ?>

<script>
$(document).on("click", ".edit", function() {
    $("#id").val($(this).data("id"))

});
$(document).on("click", "#addSaleAmount", function() {
    $("#saleAmountView").val($(this).data('sale_amount'));
    var queryId = $(this).data("id");

    $("#saleTableBody").empty();

    $.ajax({
        url: "ajax/getPayments.php",
        type: "POST",
        data: {
            query_id: queryId
        },
        success: function(response) {
            $("#saleTableBody").html(response);
        },
        error: function() {
            alert("Error fetching sale data.");
        }
    });
    $("#hidden_query_id").val(queryId);
})
$(document).on("click", "#add_expenses", function() {

    var queryId = $(this).data("id");
    console.log(queryId);

    $("#expenseTableBody").empty();

    $.ajax({
        url: "ajax/getExpenses.php",
        type: "POST",
        data: {
            query_id: queryId
        },
        success: function(response) {
            $("#expenseTableBody").html(response);
        },
        error: function() {

        }
    });

})
$(document).on("click", ".update-paid-status", function() {
    var expenseId = $(this).data("id");

    Swal.fire({
        title: 'Are you sure?',
        text: "You want to mark this as Paid?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, Mark as Paid!'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: "ajax/updatePaidStatus.php",
                type: "POST",
                dataType: "json",
                data: {
                    id: expenseId
                },
                success: function(response) {
                    Swal.fire(
                        response.title,
                        response.text,
                        response.status
                    );

                    window.location.reload();

                }
            });
        }
    });
});
function editSaleAmount(queryId, currentAmount) {
    var newAmount = prompt("Enter new Sale Amount:", currentAmount);
    if (newAmount != null) {
        // Call AJAX to update sale amount
        $.ajax({
            url: 'ajax/update_sale_amount.php',
            type: 'POST',
            data: {
                query_id: queryId,
                sale_amount: newAmount
            },
            success: function(response) {
                alert(response);
                // optionally reload payments data again
                window.location.reload();

            }
        });
    }
}

function editPaymentAmount(paymentId, currentAmount) {
    var newAmount = prompt("Enter new Payment Amount:", currentAmount);
    // console.log(paymentId,currentAmount)
    if (newAmount != null) {
        $.ajax({
            url: 'ajax/update_sale_payment.php',
            type: 'POST',
            data: {
                payment_id: paymentId,
                payment_amount: newAmount
            },
            success: function(response) {
                alert(response);
                window.location.reload();
            },
            error: function() {
                alert("An error occurred while updating the payment amount.");
            }
        });
    }
}


</script>