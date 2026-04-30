<?php
    include "Layouts/Header.php";
    include "Layouts/Sidebar.php";

    $stmt = $mysqli->prepare("SELECT a.*,b.name as customer from  order_mst a join customers b on a.customer_id=b.id where a.id=?");
    $stmt->bind_param('i', $_GET['id']);
    $stmt->execute();
    $invoice = $stmt->get_result()->fetch_assoc();

    if (isset($_POST['btnSave'])) 
    {
        try 
        {
            $stmt = $mysqli->prepare("SELECT * from  customers where id=?");
            $stmt->bind_param('i', $_POST['ids']);
            $stmt->execute();
            $customers = $stmt->get_result()->fetch_assoc();

              $image_path = null;
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $target_dir = "./images/invoices/";
            if (!is_dir($target_dir)) {
                mkdir($target_dir, 0777, true);
            }
            $image_name  = time() . "_" . basename($_FILES["image"]["name"]);
            $target_file = $target_dir . $image_name;

            if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                $image_path = $image_name;
            } else {
                throw new Exception("Image upload failed to $target_file");
            }
        }


            if ($_POST['expense_type'] == 'labour') 
            {
                $_POST['vendor_id'] = $_POST['`labour_id`'];
            }

            $stmt = $mysqli->prepare("INSERT into expenses (expenses_for,ids,name,expense_category,expense_date,amount,file,payment_mode,note,ref_no,vendor_id,user_id,expense_type,query_id,expense_subcategory,build,paid_status)values (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
            $stmt->bind_param("sisssdssssiisisss", $_POST['expenses_for'],  $_POST['ids'], $_POST['name'], $_POST['expense_category'], $_POST['expense_date'], $_POST['amount'], $image_path, $_POST['payment_mode'],  $_POST['note'], $_POST['ref_no'], $_POST['vendor_id'], $user['id'], $_POST['expense_type'], $_POST['query_id'], $_POST['expense_subcategory'], $_POST['build'],$_POST['paid_status']);
            $stmt->execute();

            alert("Save Successfully", "success", "success");
            redirect("expenses.php");
        } 
        catch (Exception $e) 
        {
            alert($e->getMessage(), "error", "error");
            redirect("expenses.php");
        }
    }

    // if (isset($_POST['btnadd'])) 
    // {
    //     try 
    //     {
    //         $stmt = $mysqli->prepare("INSERT into category (name,active)values (?,?)");
    //         $stmt->bind_param("si", $_POST['name'],  $_POST['active']);
    //         $stmt->execute();
    //         alert("Save Successfully", "success", "success");
    //         redirect("category");
    //     } 
    //     catch (Exception $e) 
    //     {
    //         alert($e->getMessage(), "error", "error");
    //         redirect("category");
    //     }
    // }

    if (isset($_POST['BtnAmountSave'])) 
    {
        try 
        {
            $stmt = $mysqli->prepare("UPDATE  expenses set amount=? where id=?");
            $stmt->bind_param("di",  $_POST['amount'], $_POST['id']);
            $stmt->execute();
            alert(" Update Successfully", "success", "success");

            redirect("?");
        } 
        catch (Exception $e) 
        {
            alert($e->getMessage(), "error", "error");
            redirect("?");
        }
    }

    if (isset($_POST['BtnDelete'])) 
    {
        try 
        {
            $stmt = $mysqli->prepare("SELECT * from  expenses where id=?");
            $stmt->bind_param('i', $_POST['did']);
            $stmt->execute();
            $expenses = $stmt->get_result()->fetch_assoc();

            $stmt = $mysqli->prepare("UPDATE  users set wallet_amt=wallet_amt+? where id=?");
            $stmt->bind_param("di",  $expenses['amount'], $expenses['ids']);
            $stmt->execute();


            $stmt = $mysqli->prepare("DELETE from expenses  where id=?");
            $stmt->bind_param("i", $_POST['did']);
            $stmt->execute();
            alert(" Delete Successfully", "success", "success");
            redirect("?");
        } 
        catch (Exception $e) 
        {
            alert($e->getMessage(), "error", "error");
            redirect("?");
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
                        <h4 class="card-title">Expenses</h4>
                        <button type="button" class="btn btn-square btn-outline-danger" data-bs-toggle="modal"
                            data-bs-target=".bd-example-modal-lg" id="add_expenses">Add Expenses</button>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="example" class="display" style="min-width: 845px">
                                <thead>
                                    <tr>
                                        <th>S.No</th>
                                        <th>User</th>
                                        <th>File</th>
                                        <th>Customer</th>
                                        <th>Vendor</th>
                                        <th>Name</th>
                                        <th>Expense Category</th>
                                        <th>Expense Date</th>
                                        <th>Note</th>
                                        <th>Paymode</th>
                                        <th>Ref. No.</th>
                                        <th>Amount</th>
                                        <th>Billed</th>
                                        <th>Created at</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                        $stmt = $mysqli->prepare("SELECT * from  expenses  order by id desc");
                                        $stmt->execute();
                                        $res = $stmt->get_result();
                                        $sno = 1;
                                        while ($row = $res->fetch_assoc()) 
                                        {
                                            $stmt = $mysqli->prepare("SELECT a.*,b.name as ids,c.name as vendor_name,u.name as user from  expenses a left join customers b on a.ids=b.id left join vendor c on a.vendor_id=c.id join users u on a.user_id=u.id where a.id=? order by a.id desc");
                                            $stmt->bind_param("i", $row['id']);
                                            $stmt->execute();
                                            $expenses = $stmt->get_result();
                                            while ($row1 = $expenses->fetch_assoc()) 
                                            {
                                                echo '<tr>
                                                    <td>' . $sno++ . '</td>
                                                    <td>' . $row1['user'] . '</td>';

                                                if (empty($row['file'])) 
                                                {
                                                    echo '             <td>There is no file upload</td>';
                                                } 
                                                else 
                                                {
                                                  echo '<td><a href="' . BASE_PATH . 'images/invoices/' . $row['file'] . '" target="_blank">File</a></td>';

                                                }

                                                echo  '<td>' . $row1['ids'] . '</td>
                                                    <td>' . $row1['vendor_name'] . '</td>
                                                    <td>' . $row1['name'] . '</td>
                                                    <td>' . $row1['expense_category'] . '</td>
                                                    <td>' . $row1['expense_date'] . '</td>
                                                    <td>' . $row1['note'] . '</td>
                                                        <td>' . $row1['payment_mode'] . '</td>
                                                        
                                                            <td>' . $row1['ref_no'] . '</td>
                                                    <td>' . $row1['amount'] . '</td>
                                                    <td>' . $row1['build'] . '</td>
                                                        <td>' . $row['created_at'] . '</td>
                                                    <td class="d-flex align-items-center gap-2 my-auto"><button type="button" class="btn btn-primary btn-sm Edit shadow btn-xs sharp" 
                                                    data-id="' . $row['id'] . '"
                                                        data-amount="' . $row['amount'] . '"
                                                    
                                                    ><i class="fa fa-pen"></i></button>
                                                    
                                                    <button type="button" class="btn btn-danger btn-sm Delete shadow btn-xs sharp" 
                                                    data-id="' . $row['id'] . '"
                                                    
                                                    
                                                    ><i class="fa fa-trash"></i></button> 
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

<div class="modal fade bd-example-modal-lg" id="modalId" tabindex="-1" role="dialog" aria-hidden="true">
    <form class="needs-validation" novalidate method="post" enctype="multipart/form-data">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitleId">Expenses</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-6">
                            <label>File</label>
                            <input type="file" name="image" class="form-control">
                        </div>
                        <div class="col-6">
                            <label>Select Billed/Not Billed</label>
                            <select name="build" id="build" class="form-control">
                                <option value="">Select</option>
                                <option value="Billed">Billed</option>
                                <option value="Not Billed">Not Billed</option>
                            </select>
                        </div>

                        <div class="col-6 mt-3">
                            <label>Name</label>
                            <input type="text" name="name" id="name" class="form-control">
                        </div>
                        <div class="col-6 mt-3">
                            <label>Note</label>
                            <textarea name="note" id="note" class="form-control"></textarea>
                        </div>

                        <div class="col-6 mt-3">
                            <label>Expense Type</label>
                            <select class="form-control" id="expense_type" name="expense_type">
                                <option value="">Select Expense Type</option>
                                <option value="vendor">Vendor</option>
                                <option value="labour">Labour</option>
                                <option value="TA/DA">TA/DA</option>
                                <option value="">Miscellaneous</option>
                            </select>
                        </div>
                        <div class="col-6 mt-3 vendor">
                            <label>Vendor</label>
                            <select class="form-control" id="vendor_id" name="vendor_id">
                                <option value="">Select Vendor</option>
                                <?php
                    $stmt = $mysqli->prepare("SELECT * FROM vendor WHERE user_type='vendor'");
                    $stmt->execute();
                    $category = $stmt->get_result();
                    while ($row = $category->fetch_assoc()) {
                        echo '<option value="' . $row['id'] . '">' . $row['name'] . '</option>';
                    }
                ?>
                            </select>
                        </div>

                        <div class="col-6 mt-3 labour">
                            <label>Labour</label>
                            <select class="form-control" id="labour_id" name="labour_id">
                                <option value="">Select Labour</option>
                                <?php
                    $stmt = $mysqli->prepare("SELECT * FROM vendor WHERE user_type='labour'");
                    $stmt->execute();
                    $category = $stmt->get_result();
                    while ($row = $category->fetch_assoc()) {
                        echo '<option value="' . $row['id'] . '">' . $row['name'] . '</option>';
                    }
                ?>
                            </select>
                        </div>
                        <div class="col-6 mt-3 category">
                            <label>Expense Category</label>
                            <select name="expense_category" id="expense_category" class="form-control">
                                <option value="">Select</option>
                                <?php
                    $stmt = $mysqli->prepare("SELECT * FROM expense_category WHERE active=1");
                    $stmt->execute();
                    $category = $stmt->get_result();
                    while ($row = $category->fetch_assoc()) {
                        echo '<option value="' . $row['name'] . '">' . $row['name'] . '</option>';
                    }
                ?>
                            </select>
                        </div>

                        <div class="col-6 mt-3 category">
                            <label>Expense Sub Category</label>
                            <select name="expense_subcategory" id="expense_subcategory" class="form-control">
                                <option value="">Select</option>
                            </select>
                        </div>

                        <div class="col-6 mt-3">
                            <label>Expense Date</label>
                            <input type="date" name="expense_date" id="expense_date" class="form-control">
                        </div>
                        <div class="col-6 mt-3">
                            <label>Expense Amount</label>
                            <input type="number" step="0.01" name="amount" id="amount" class="form-control" required>
                        </div>

                        <div class="col-6 mt-3">
                            <label>Customer</label>
                            <select class="form-control" id="ids" name="ids" required>
                                <option value="">Select Customer</option>
                                <?php
                    $stmt = $mysqli->prepare("SELECT * FROM customers");
                    $stmt->execute();
                    $category = $stmt->get_result();
                    while ($row = $category->fetch_assoc()) {
                        echo '<option value="' . $row['id'] . '">' . $row['name'] . '</option>';
                    }
                ?>
                            </select>
                        </div>

                        <div class="col-6 mt-3">
                            <label>Select Query</label>
                            <select class="form-control" id="invoice_id" name="query_id" required>
                                <option value="">Select Sale</option>
                            </select>
                        </div>

                        <div class="col-6 mt-3">
                            <label>Payment Mode</label>
                            <select name="payment_mode" id="payment_mode" class="form-control">
                                <option value="Bank Transfer">Bank Transfer</option>
                                <option value="Cheque">Cheque</option>
                                <option value="Cash">Cash</option>
                            </select>
                        </div>

                        <div class="col-6 mt-3">
                            <label>Reference Number</label>
                            <input type="text" name="ref_no" id="ref_no" class="form-control">
                        </div>

                        <div class="col-6 mt-3">
                            <label>Paid Status</label>
                            <select name="paid_status" id="paid_status" class="form-control">
                                <option value="">Select</option>
                                <option value="paid">Paid</option>
                                <option value="unpaid">Un Paid</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-danger light" data-bs-dismiss="modal">Close</button>
                    <button type="submit" name="btnSave" id="btnadd" class="btn btn-primary btnname">Save
                        changes</button>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
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
                    <button type="submit" class="btn btn-success" name="btnadd" id="btnadd"><span class="btnname">
                            Submit</span></button>
                </div>
            </div>
        </div>
    </form>
</div> -->

<div class="modal fade" id="EditModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form method="post">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="exampleModalLabel">Edit</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md">
                            <label>
                                Amount
                            </label>
                            <input type="number" step="0.01" name="amount" id="expense_amount" class="form-control">
                            <input type="hidden" name="id" id="expense_id">
                            <input type="hidden" name="old_amt" id="old_amt">
                        </div>

                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" name="BtnAmountSave" class="btn btn-primary">Save changes</button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form method="post">
            <div class="modal-content">
                <div class="modal-header bg-danger">
                    <h1 class="modal-title fs-5 text-white" id="exampleModalLabel">Delete</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md">
                            <h4>Are you sure you want to delete this?</h4>

                            <input type="hidden" name="did" id="did">
                        </div>

                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-success" data-bs-dismiss="modal">Close</button>
                    <button type="submit" name="BtnDelete" class="btn btn-danger">Yes</button>
                </div>
            </div>
        </form>
    </div>
</div>

<?php include "Layouts/Footer.php"  ?>

<script>
$(document).ready(function() {

    //  $('#modalId').on('shown.bs.modal', function() {
    //     $('select').select2({
    //         dropdownParent: $('#modalId')
    //     });
    // });



    $("#add_expenses").on("click", function() {
        $("#modalId").modal("show");
    });

    $("#expenses_for").on("change", function() {
        $.ajax({
            method: "POST",
            url: "ajax/expense-for.php",
            data: {
                state: $(this).val()
            },
            success: function(data) {
                $("#ids").html(data);
            }
        });
    });

    // this code for sale 
    // $("#ids").on("change", function() {
    //     $.ajax({
    //         method: "POST",
    //         url: "ajax/get-invoices.php",
    //         data: {
    //             customer_id: $(this).val()
    //         },
    //         success: function(data) {
    //             $("#invoice_id").html(data);
    //         }
    //     });
    // });


    $("#ids").on("change", function() {
        $.ajax({
            method: "POST",
            url: "ajax/getQuery.php",
            data: {
                customer_id: $(this).val()
            },
            success: function(data) {
                console.log(data)
                $("#invoice_id").html(data);
            }
        });
    });

    $(document).on("click", "#add_notice", function() {
        let id = $("#id").val();
        if (id) {
            $("#id").val("");
            $("#title").val("");
            $("#description").html("");
            $("#sequence").val("");
            $("#active").val("");
            $("#btnupdate").attr('name', 'btnadd');
            $("#btnupdate").attr('id', 'btnadd');
            $(".btnname").html('Add Category');
        }
        $("#exampleModal").modal('show');
    });

    $(".vendor").hide();
    $(".labour").hide();

    $("#expense_type").on("change", function() {
        const type = $(this).val();
        if (type === "vendor") {
            $(".vendor").show(500);
            $(".labour").hide(500);
        } else if (type === "labour") {
            $(".labour").show(500);
            $(".vendor").hide(500);
        } else {
            $(".vendor").hide(500);
            $(".labour").hide(500);
        }
    });

    $(document).on("click", ".Edit", function() {
        $("#expense_id").val($(this).data("id"));
        $("#expense_amount").val($(this).data("amount"));
        $("#old_amt").val($(this).data("amount"));
        $("#EditModal").modal("show");
    });

    $(document).on("click", ".Delete", function() {
        $("#did").val($(this).data("id"));
        $("#deleteModal").modal("show");
    });

    $("#expense_category").on("change", function() {
        $.ajax({
            method: "POST",
            url: "ajax/get-expense-sub-category.php",
            data: {
                name: $(this).val()
            },
            success: function(data) {
                $("#expense_subcategory").html(data);
            }
        });
    });

    $(".category").hide();

    $("#expense_type").on("change", function() {
        if (!$(this).val()) {
            $(".category").show(500);
        } else {
            $(".category").hide(500);
            $("#expense_category").val("");
            $("#expense_subcategory").val("");
        }
    });
});
</script>