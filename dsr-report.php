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
                        <h4 class="card-title">DSR Report</h4>
                    </div>
                    <div class="card-body border-bottom">
                        <form method="GET">
                            <div class="row align-items-end">
                                <div class="col-md-5">
                                    <div class="form-group">
                                        <label for="fromDt" class="form-label"><i class="fas fa-calendar me-2"></i>From Date</label>
                                        <input type="date" class="form-control" id="fromDt" name="fromDt" value="<?= $_GET['fromDt'] ?? '' ?>">
                                    </div>
                                </div>
                                <div class="col-md-5">
                                    <div class="form-group">
                                        <label for="toDt" class="form-label"><i class="fas fa-calendar me-2"></i>To Date</label>
                                        <input type="date" class="form-control" id="toDt" name="toDt" value="<?= $_GET['toDt'] ?? '' ?>">
                                    </div>
                                </div>
                                <div class="col-md-2 d-flex gap-2 align-items-end mb-3">
                                    <button class="btn btn-primary mt-2" type="submit">
                                        <i class="fas fa-search me-2"></i>Search
                                    </button>
                                    <a href="dsr-report.php" class="btn btn-primary border mt-2" title="Reset Filters">
                                        <i class="fas fa-redo"></i>
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="example" class="display" style="min-width: 845px">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Day</th>
                                        <th>Category</th>
                                        <th>Product Details</th>
                                        <th>Vendor</th>
                                        <th>Customer Name</th>
                                        <th>Contact Details</th>
                                        <th>Address</th>
                                        <th>Lead Gen</th>
                                        <th>Lead Con</th>
                                        <th>Gross Purchase</th>
                                        <th>Net Purchase</th>
                                        <th>Gross Sale Price</th>
                                        <th>Gross Profit</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>                              
                                <?php
                                    $stmt = $mysqli->prepare("SELECT a.*,c.name as category,b.sub_category,v.name as vendor,cm.name as customer,cm.number as customer_number,cm.address as customer_address,s.name as source,u.name as user,  (CASE WHEN ex.expense_type='vendor' THEN amount ELSE 0 END)as 'gross_purchase',(CASE WHEN ex.expense_type!='vendor' THEN amount ELSE 0 END)as 'net_purchase',sum(b.qty*b.price) as gross_sale from  order_mst a left join order_det b on a.id=b.order_id left join category c on b.category=c.id left join expenses ex on a.id=ex.query_id left join vendor v on ex.vendor_id=v.id left join customers cm on a.customer_id=cm.id left join source s on a.source=s.id left join users u on a.user_id=u.id");
                                    $stmt->execute();
                                    $res = $stmt->get_result();
                                    $sno = 1;
                                    while ($row = $res->fetch_assoc()) 
                                    {
                                        echo '
                                        <tr>
                                            <td>' . $row['invoice_date'] . '</td>
                                            <td>' . date('D', strtotime($row['invoice_date'])) . '</td>
                                            <td>' . $row['category'] . '</td>
                                            <td>' . $row['sub_category'] . '</td>
                                            <td>' . $row['vendor'] . '</td>
                                            <td>' . $row['customer'] . '</td>
                                            <td>' . $row['customer_number'] . '</td>
                                            <td>' . $row['customer_address'] . '</td>
                                            <td>' . $row['source'] . '</td>
                                            <td>' . $row['user'] . '</td>
                                            <td>' . $row['gross_purchase'] . '</td>
                                            <td>' . $row['net_purchase'] . '</td>
                                            <td>' . $row['gross_sale'] . '</td>
                                            <td>' . $row['gross_sale'] - $row['net_purchase'] - $row['gross_purchase'] . '                                     
                                            </td>
                                            <td>
                                            <button type="button" class="btn btn-success btn-sm payment_history " data-id="' . $row['id'] . '">Payments history</button></td>
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

<?php include "Layouts/Footer.php"  ?>

<div class="modal fade" id="view_payment" tabindex="-1" role="dialog" aria-labelledby="modalTitleId" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <form method="post">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitleId">Payment History</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>#</th>

                                <th>Amount Received</th>
                                <th>Payment Date</th>
                                <th>Payment Mode</th>
                                <th>Transaction ID</th>
                                <th>Payment Status</th>
                                <th>Description</th>
                            </tr>
                        </thead>
                        <tbody id="list_p">

                        </tbody>
                    </table>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Close</button>

                </div>
            </div>
        </form>
    </div>
</div>

<script>
    $(document).on("click",".payment_history", function() {

        $.ajax({
            method: 'POST',
            url: 'ajax/get-payment-history.php',
            dataType: 'text',
            data: {
                id: $(this).data("id")
            },
            beforeSend: function(data) {
                $('#wait').show();
            },
            success: function(data) {
                var html = "";
                data = JSON.parse(data)
                data.forEach(element => {
                    var checked = "";
                    if (element.service_tax_applicable == 1) {

                        checked = "checked";

                    }
                    html += '<tr><td>' + element.id + '</td>  <td>' + element.amount_received + '</td><td>' + element.payment_date + '</td><td>' + element.payment_mode + '</td><td>' + element.transaction_id + '</td><td>' + element.payment_status + '</td><td>' + element.description + '</td> </tr>';

                });



                $("#list_p").html(html)
            },
            complete: function(data) {
                $('#wait').hide();
            }
        });
        $("#view_payment").modal("show");
    })
</script>