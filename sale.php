<?php
    include "Layouts/Header.php";
    include "Layouts/Sidebar.php";
    if (isset($_POST['prod_list'])) 
    {
        $error = '';
        $p = json_decode($_POST['prod_list'], true);
        $p = array_filter($p, fn($value) => !is_null($value) && $value !== '');

        if (sizeOf($p) <= 0)
        $error = 'No product found';

        if ($error == '') 
        {
            $upload_dir = 'invoices/';
            $file_url = "";
            if (isset($_FILES['file']['name'])) 
            {
                $total_files = count($_FILES['file']['name']);
                for ($i = 0; $i < $total_files; $i++) 
                {
                    $pic_name = $_FILES["file"]["name"][$i];
                    $pic_tmp_name = $_FILES["file"]["tmp_name"][$i];
                    $error = $_FILES["file"]["error"][$i];

                    $random_name = rand(1000, 10000000000000000) . "-" . $pic_name;
                    $upload_name = $upload_dir . strtolower($random_name);
                    $upload_name = preg_replace('/\s+/', '-', $upload_name);
                    try 
                    {
                        $res = move_uploaded_file($pic_tmp_name, $upload_name);
                        $file[] = $upload_name;
                        $file_url = implode(", ", $file);
                    } 
                    catch (Exception $e) 
                    {
                        echo $e->getMessage();
                    }
                }
            }
            $status = "pending";
            $stmt = $mysqli->prepare("SELECT * from  company  order by id desc");
            $stmt->execute();
            $company = $stmt->get_result()->fetch_assoc();
            $company['invoice_prefix'] = $company['invoice_prefix'] . '_' . $company['invoice_no'];
            try 
            {
                $stmt = $mysqli->prepare("INSERT into order_mst (company_id,customer_id,invoice,due_date,service_tax,invoice_date,user_id,payment_status,gst_type,is_billed,file,travel_date,source,query_id)values(?,?,?,?,?,?,?,?,?,?,?,?,?,?);");
                $stmt->bind_param("iissdsisssssii", $_POST['company_id'], $_POST['customer_id'], $company['invoice_prefix'], $_POST['due_date'], $_POST['service_tax'], $_POST['invoice_date'], $user['id'], $status, $_POST['gst_type_mst'], $_POST['is_billed'], $file_url, $_POST['travel_date'], $_POST['source_id'], $_POST['query_id']);
                $stmt->execute();
                $order_id = $mysqli->insert_id;

                foreach ($p as $item) 
                {
                    if (empty($item['id'])) continue;
                    $stmt = $mysqli->prepare("INSERT into order_det(order_id,category,sub_category,description,qty,price,gst,commision,gst_type,user_id,purchase_rate,tcs) values(?,?,?,?,?,?,?,?,?,?,?,?);");
                    $stmt->bind_param("isssidddsidd", $order_id, $item['prod_category'], $item['prod_subcategory'], $item['description'], $item['qty'], $item['price'], $item['gst'], $item['commision'], $item['gst_type'], $user['id'], $item['purchase_rate'], $item['tcs']);
                    $stmt->execute();
                    $stmt->close();
                }
                $company['invoice_no'] = $company['invoice_no'] + 1;
                $stmt = $mysqli->prepare("UPDATE company set invoice_no=? WHERE id=?");
                $stmt->bind_param("si", $company['invoice_no'], $company['id']);
                $stmt->execute();
                $stmt->close();
                $stmt = $mysqli->prepare("UPDATE query_mst set status='Completed' WHERE id=?");
                $stmt->bind_param("i",  $_POST['query_id']);
                $stmt->execute();
                $stmt->close();
                alert('Save successfully.', 'success');
                redirect('invoices.php');
            } 
            catch (Exception $ex) 
            {
                alert($ex->getMessage(), 'error', 'ERROR');
                redirect('invoices.php');
            }
        } 
        else 
        {
            alert($error, 'error', 'ERROR');
            redirect('invoices.php');
        }
    }
?>

<div class="content-body">
    <div class="container-fluid">
        <!-- row -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-block">
                        <div class="d-flex justify-content-between">
                            <h4 class="card-title">Sale</h4>
                            <button type="button" class="btn btn-square btn-outline-danger" id="add_notice">Add Sale</button>
                        </div>
                        <div class="row">
                            <form method="get">
                                <div class="justify-content-center d-flex">
                                    <div class="col-3">
                                        <select class="form-control" id="company_id" name="company_id" required>
                                            <option value="" disabled selected>Select Company</option>
                                            <?php
                                                $stmt = $mysqli->prepare("SELECT  *  FROM `company`");
                                                $stmt->execute();
                                                $category = $stmt->get_result();
                                                while ($row = $category->fetch_assoc()) 
                                                {
                                                    echo '<option value="' . $row['id'] . '">' . $row['name'] . '</option>';
                                                }
                                            ?>
                                        </select>
                                    </div>
                                    <div class="ms-3">
                                        <button type="submit" class="btn btn-primary">Search</button>
                                        <a href="sale.php" class="text-white">
                                            <button type="button" class="btn-primary btn">
                                                <i class="fa-solid fa-eraser"></i>
                                            </button>
                                        </a>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                      <div class="card-body">
                        <div class="table-responsive">
                            <table id="example" class="display" style="min-width: 845px">
                                <thead>
                                      <tr>
                                        <th>S.No</th>
                                        <th>User</th>
                                        <th>Sale</th>
                                        <th>Customer Name</th>
                                        <th>Amount</th>
                                        <th>Sale Date</th>
                                        <th>Due Date</th>


                                        <th>Payment Status</th>
                                        <th>Is Billed</th>
                                        <th>File</th>

                                        <th>Action</th>
                                    </tr>
                                </thead>
                                  <tbody>
                                    <?php
                                if (!empty($_GET['company_id'])) {

                                    $stmt = $mysqli->prepare("SELECT a.*,b.name as customer,sum((c.price+c.commision)*c.qty) as amt,u.name as user,s.name as source from  order_mst  a join customers b on a.customer_id=b.id join order_det c on a.id=c.order_id join users u on a.user_id=u.id left join source s on a.source=s.id  where a.company_id=?   group by a.id order by a.id desc");
                                    $stmt->bind_param('i', $_GET['company_id']);
                                    $stmt->execute();
                                    $res = $stmt->get_result();
                                } else {
                                    $stmt = $mysqli->prepare("SELECT a.*,b.name as customer,sum((c.price+c.commision)*c.qty) as amt,u.name as user,s.name as source from  order_mst  a join customers b on a.customer_id=b.id join order_det c on a.id=c.order_id join users u on a.user_id=u.id  left join source s on a.source=s.id  group by a.id order by a.id desc");
                                    $stmt->execute();
                                    $res = $stmt->get_result();
                                }


                                $sno = 1;
                                while ($row = $res->fetch_assoc()) {
                                ?>
                                    <tr>
                                        <td><?= $sno++; ?></td>
                                        <td><?= $row['user']; ?></td>

                                        <td><?= $row['invoice']; ?></td>
                                        <td><?= $row['customer']; ?></td>
                                        <td><?= $row['amt'] ?></td>
                                        <td><?= $row['invoice_date']; ?></td>

                                        <td><?= $row['due_date']; ?></td>
                                        <td><?= $row['payment_status'] ?></td>
                                        <td><?= $row['is_billed'] ?></td>
                                        <?php
                                if ($row['user_id'] != 1) {
                                    $file = explode(', ', $row['file']);
                                    echo '<td>';

                                    foreach ($file as $key) {

                                ?>
                                        <a href="invoices.php/<?= $key ?>" target="_blank">File</a>
                                        <?php
                                    }
                                    echo '</td>';
                                } else {
                                    $file = explode(', ', $row['file']);
                                    echo '  <td>';
                                    foreach ($file as $key) {
                                    ?>
                                        <a href="<?= $key ?>" target="_blank">File</a>
                                        <?php
                                    }
                                    echo '</td>';
                                }
                                ?>

                                        <td class="d-flex gap-2 align-items-center">
                                            <a class="btn btn-sm btn-primary shadow btn-xs sharp"
                                                href="edit-invoice.php?id=<?= $row['id'] ?>"><i class="fa fa-pen"></i></a>
                                            <a class="btn btn-sm btn-success shadow btn-xs sharp"
                                                href="invoice-bill.php?id=<?= $row['id'] ?>"><i class="fa fa-eye"></i></a>

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

<div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <form class="needs-validation" novalidate method="POST" id="frmMain" enctype="multipart/form-data">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="modal-title fs-5" id="exampleModalLabel"> <span class="btnname"> Add Sale</span></h3>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                    </button>
                </div>
                <div class="modal-body">
                    <input id="id" name="id" type="hidden">
                    <div class="row">
                        <div class="col-md-4">
                            <label>File</label>
                            <input type="file" name="file[]" id="file" class="form-control" multiple>
                        </div>
                        <div class="mb-3 col-md-4 d-none">
                            <label for="source_id" class="form-label">Select Source</label>
                            <select class="form-control" id="source_id" name="source_id">
                                <option value="">Select Source</option>
                                <?php
                                    $stmt = $mysqli->prepare("SELECT * from `source` where active=1");
                                    $stmt->execute();
                                    $state = $stmt->get_result();
                                    while ($row = $state->fetch_assoc()) 
                                    {
                                        echo '<option value="' . $row['id'] . '">' . $row['name'] . '</option>';
                                    }
                                ?>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label>Select Billed/Not Billed</label>
                            <select class="form-control" id="is_billed" name="is_billed" required>
                                <option value="" disabled selected>Select </option>
                                <option value="Billed">Billed</option>
                                <option value="Not Billed">Not Billed</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label>Select Company</label>
                            <select class="form-control" id="company_id" name="company_id" required>
                                <option value="" disabled selected>Select Company</option>
                                <?php
                                    $stmt = $mysqli->prepare("SELECT  *  FROM `company`");
                                    $stmt->execute();
                                    $category = $stmt->get_result();
                                    while ($row = $category->fetch_assoc()) 
                                    {
                                        echo '<option value="' . $row['id'] . '">' . $row['name'] . '</option>';
                                    }
                                ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label>Select Customer</label>
                            <select class="form-control" id="customer_id" name="customer_id" required>
                                <option value="" disabled selected>Select Customer</option>
                                <?php
                                    $stmt = $mysqli->prepare("SELECT  *  FROM `customers`");
                                    //   $stmt->bind_param("s", $_COOKIE['token']);
                                    $stmt->execute();
                                    $category = $stmt->get_result();
                                    while ($row = $category->fetch_assoc()) 
                                    {
                                        echo '<option value="' . $row['id'] . '">' . $row['name'] . '</option>';
                                    }
                                ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label>Select Query</label>
                            <select class="form-control" id="query_id" name="query_id" required>
                                <option value="">Select Query</option>
                            </select>
                        </div>
                        <div class="col-md-4 mt-2">
                            <label>Sale Date</label>
                            <input type="date" name="invoice_date" id="invoice_date" class="form-control">
                        </div>
                        <div class="col-md-4 mt-2">
                            <label>Due Date</label>
                            <input type="date" name="due_date" id="due_date" class="form-control">
                        </div>
                        <div class="col-md-4 mt-2">
                            <label>Travel Date</label>
                            <input type="date" name="travel_date" id="travel_date" class="form-control">
                        </div>

                        <div class="col-md-4 mt-2 d-none">
                            <label>TCS</label>
                            <select step="0.01" name="service_tax" id="service_tax" class="form-control">
                                <option value="0">0</option>
                                <option value="5">5</option>
                                <option value="15">15</option>
                                <option value="20">20</option>
                            </select>
                        </div>

                        <div class="col-md-4 mt-2">
                            <label>GST Type</label>
                            <select step="0.01" name="gst_type_mst" id="gst_type_mst" class="form-control">
                                <option value="Outer GST">Outer GST</option>
                                <option value="Inner GST">Inner GST</option>
                            </select>
                        </div>


                        <div class="col-md-6 mt-2">
                        </div>
                        <h4 class="mt-2">Items</h4>
                        <hr>
                        <div class="row">
                            <div class="row">
                                <div class="mb-3 col-md-4">
                                    <label for="prod_category" class="form-label">Select Category</label>
                                    <select class="form-control" id="prod_category" name="prod_category" required>
                                        <option value="">Select category</option>
                                        <?php
                                            $stmt = $mysqli->prepare("SELECT * from `category`");
                                            $stmt->execute();
                                            $state = $stmt->get_result();
                                            while ($row = $state->fetch_assoc()) 
                                            {
                                                echo '<option value="' . $row['id'] . '">' . $row['name'] . ' (' . $row['hsn_code'] . ')</option>';
                                            }
                                        ?>
                                    </select>
                                </div>

                                <div class="mb-3 col-md-4">
                                    <label for="prod_subcategory" class="form-label">Select Sub Category</label>
                                    <select class="form-control" id="prod_subcategory" name="prod_subcategory" required>
                                        <option value="">Select subcategory</option>
                                    </select>
                                </div>

                                <div class="mb-3 col-md-4">
                                    <label for=" " class="form-label">Description</label>
                                    <input type="" name="description" id="description" class="form-control">
                                </div>
                                <div class="mb-3 col-md-2">
                                    <label for=" " class="form-label">Enter Qty</label>
                                    <input type="number" class="form-control" id="qty" name="qty">
                                </div>
                                <div class="mb-3 col-md-2">
                                    <label for=" " class="form-label">Price</label>
                                    <input type="number" class="form-control" step="0.01" id="price" name="price" id="price">
                                </div>
                                <div class="mb-3 col-md-2 d-none">
                                    <label for=" " class="form-label">Purchase Rate</label>
                                    <input type="number" class="form-control" value="0" step="0.01" name="purchase_rate" id="purchase_rate">
                                </div>
                                <div class="col-md-4 mt-2">
                                    <label>TCS</label>
                                    <select step="0.01" name="tcs" id="tcs" class="form-control">
                                        <option value="0">0</option>
                                        <option value="5">5</option>
                                        <option value="15">15</option>
                                        <option value="20">20</option>
                                    </select>
                                </div>
                                <div class="mb-3 col-md-2">
                                    <label for=" " class="form-label">Select GST</label>
                                    <select class="form-control" id="gst" name="gst">
                                        <option value="">Select GST</option>
                                        <?php
                                            $stmt = $mysqli->prepare("SELECT * from `gst`");
                                            $stmt->execute();
                                            $state = $stmt->get_result();
                                            while ($row = $state->fetch_assoc()) 
                                            {
                                                echo '<option value="' . $row['gst'] . '">' . $row['gst'] . '</option>';
                                            }
                                        ?>
                                    </select>
                                </div>
                                <div class="mb-3 col-md-2">
                                    <label for=" " class="form-label">GST Type</label>
                                    <select type="" class="form-control" id="gst_type" name="gst_type">
                                        <option value="include">Include</option>
                                        <option value="exclude">Exclude</option>
                                    </select>
                                </div>
                                <div class="mb-3 col-md-2">
                                    <label for=" " class="form-label">Commision Per Qty</label>
                                    <input type="number" class="form-control" step="0.01" id="commision" name="commision">
                                </div>
                                <div class="col-md-1">
                                    <label for=" " class="form-label">Add</label>
                                    <button class="btn btn-success" type="button" id="add_product" onclick="addItem()">Add</button>
                                </div>

                                <div class="table-responsive">
                                    <table class="table ">
                                        <thead>
                                            <tr>
                                                <th scope="col">Category</th>
                                                <th scope="col">SUb category</th>
                                                <th scope="col">Description</th>
                                                <th scope="col">Qty</th>
                                                <th scope="col">Price</th>
                                         
                                                <th scope="col">TCS</th>
                                                <th scope="col">GST</th>
                                                <th>Commision</th>
                                                <th scope="col">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody id="productList">
                                        </tbody>
                                    </table>
                                </div>
                                <input type="hidden" name="prod_list" id="prod_list" value="">
                                <hr>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-success" name="savelead" id="savelead"><span class="btnname"> Submit</span></button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<?php include "Layouts/Footer.php"  ?>

<script>
    $('#exampleModal').on('shown.bs.modal', function() 
    {
        $('select').select2({
            dropdownParent: $('#exampleModal')
        });
    });
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
        $(".btnname").html('Update Invoice');
        $("#exampleModal").modal('show');
    });
    $(document).on("click", "#add_notice", function() 
    {
        let id = $("#id").val();
        if (id != false) 
        {
            $("#id").val("")
            $("input").val("")
            $(".btnname").html('Add Invoice');
        }
        $("#exampleModal").modal('show');
    });
    var products = new Array();
    var id = 0;

    function addItem() 
    {
        id = id + 1;
        var prod_category = $('#prod_category').val()
        var prod_subcategory = $('#prod_subcategory').val()
        var description = $('#description').val();
        var qty = $('#qty').val()
        var price = $('#price').val()
        var gst = $('#gst').val()
        var commision = $('#commision').val()
        var gst_type = $('#gst_type').val()
        var tcs = $('#tcs').val()
        var purchase_rate = $('#purchase_rate').val()
        if (qty <= 0) 
        {
            alert('Qty should be more than zero.');
            return;
        }
        if (id <= 0)
        {
            alert('Select Category.');
            return;
        }
        var ex_p = products.filter((item, index) => item.id == id)
        console.log(products[id])
        if (products[id] != undefined) 
        {
            alert('This product already added.');
            return;
        }
        var row = '<tr class="prod' + id + '"><td>' + prod_category + '</td><td>' + prod_subcategory + '</td><td>' + description + '</td>' + '<td>' + qty + '</td><td>' + price + '</td><td>' + tcs + '</td> <td>' + gst + '</td><td>' + commision + '</td>' + '<td><button onclick="removeItem(' + id + ')" class="btn btn-sm btn-danger" type="button"><i class="fa fa-trash"></i> </button></td></tr>';
        $('#productList').append(row);
        var prod = 
        {
            id: id,
            qty: qty,
            prod_category: prod_category,
            prod_subcategory: prod_subcategory,
            description: description,
            price: price,
            gst: gst,
            commision: commision,
            gst_type: gst_type,
            tcs: tcs,
            purchase_rate: purchase_rate,

        }
        products[id] = prod;
        $('#qty').val('')
    }
    function removeItem(id) 
    {
        $('#productList').find('.prod' + id).remove();
        const index = products.indexOf(id)
        if (index > -1) products.splice(index, 1);
        delete products[id]
        console.log(products)
    }
    (function() 
    {
        'use strict';
        window.addEventListener('load', function() 
        {
            var forms = document.getElementsByClassName('needs-validation');
            var validation = Array.prototype.filter.call(forms, function(form) 
            {
                document.getElementById("savelead").addEventListener('click', function(event) {
                    if (form.checkValidity() === false) 
                    {
                        event.preventDefault();
                        event.stopPropagation();
                    } 
                    else 
                    {
                        $('#prod_list').val(JSON.stringify(products));
                        $('#frmMain').submit()
                    }
                    form.classList.add('was-validated');
                }, false);
            });
        }, false);
    })();
    $(document).ready(function() 
    {
        $('#prod_category').change(function() 
        {
            var categoryId = $(this).val();
            if (categoryId) 
            {
                $.ajax({
                    type: 'POST',
                    url: 'ajax/get_subcategories.php',
                    data: 'category_id=' + categoryId,
                    success: function(html) 
                    {
                        $('#prod_subcategory').html(html);
                    }
                });
            } 
            else 
            {
                $('#prod_subcategory').html('<option value="">Select subcategory</option>');
            }
        });
    });

    $("#customer_id").on("change", function() 
    {
        $.ajax({
            type: 'POST',
            url: 'ajax/get-query.php',
            data: {
                id: $(this).val()
            },
            success: function(html) 
            {
                data = JSON.parse(html)
                row = "";
                data.forEach(element => {
                    row += "<option value=" + element.id + ">  Query_" + element.id + " / " + element.destination + "</option>";
                });
                $("#query_id").html(row)
            }
        });
    })
</script>