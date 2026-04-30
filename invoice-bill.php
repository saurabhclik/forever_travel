<?php
    include "Layouts/Header.php";
    include "Layouts/Sidebar.php";
    $stmt = $mysqli->prepare("SELECT a.*,b.name as customer,b.address,b.city,b.state,b.pincode,b.gst_no as gst,cm.name as company,cm.address as company_address,cm.number as company_number,cm.email as company_email from  order_mst a join customers b on a.customer_id=b.id join company cm on a.company_id=cm.id where a.id=?");
    $stmt->bind_param('i', $_GET['id']);
    $stmt->execute();
    $invoice = $stmt->get_result()->fetch_assoc();
    $stmt = $mysqli->prepare("SELECT * from `settings` where id=1");
    $stmt->execute();
    $settings = $stmt->get_result()->fetch_assoc();

    if (isset($_POST['btnSubmit'])) 
    {
        $active = 1;
        $stmt = $mysqli->prepare("UPDATE `order_mst` set customer_invoice=? where id=?");
        $stmt->bind_param('ii', $active, $invoice['id']);
        $stmt->execute();
        $s = 0;
        $stmt = $mysqli->prepare("UPDATE `order_det` set service_tax_applicable=? where order_id=?");
        $stmt->bind_param('ii', $s, $invoice['id']);
        $stmt->execute();
        foreach ($_POST['service_tax'] as $key => $value) 
        {
            $stmt = $mysqli->prepare("UPDATE `order_det` set service_tax_applicable=? where id=?");
            $stmt->bind_param('ii', $active, $value);
            $stmt->execute();
        }
        alert("save successfully", "success", "success");
        redirect("invoice-bill.php?id=" . $invoice['id'] . "");
    }

    if (isset($_POST['btnSvaePayment'])) 
    {
        $_POST['payment'] = 0;
        try 
        {
            $stmt = $mysqli->prepare("INSERT INTO `payments` (order_id,payment,amount_received,payment_date,payment_mode,transaction_id,description,payment_status,user_id) values (?,?,?,?,?,?,?,?,?)");
            $stmt->bind_param("isssssssi", $invoice['id'], $_POST['payment'], $_POST['amount_received'], $_POST['payment_date'], $_POST['payment_mode'], $_POST['transaction_id'], $_POST['description'], $_POST['payment_status'], $user['id']);
            $stmt->execute();
            $stmt = $mysqli->prepare("UPDATE order_mst set payment_status=? where id=? ");
            $stmt->bind_param("si", $_POST['payment_status'], $invoice['id']);
            $stmt->execute();
            alert('Save successfully.', 'success');
            redirect("invoice-bill.php?id=" . $invoice['id'] . "");
        } 
        catch (Exception $ex) 
        {
            alert($ex->getMessage(), 'error', 'ERROR');
            redirect("invoice-bill.php?id=" . $invoice['id'] . "");
        }
    }
?>
<style>
    @media print 
    {
        .noPrint 
        {
            display: none;
            margin-top: 0px;
        }
    }

    body 
    {
        margin-top: 10px;
        color: #484b51;
    }

    .text-secondary-d1 
    {
        color: #728299 !important;
    }

    .page-header 
    {
        margin: 0 0 1rem;
        padding-bottom: 1rem;
        padding-top: .5rem;
        border-bottom: 1px dotted #e2e2e2;
        display: -ms-flexbox;
        display: flex;
        -ms-flex-pack: justify;
        justify-content: space-between;
        -ms-flex-align: center;
        align-items: center;
    }

    .page-title 
    {
        padding: 0;
        margin: 0;
        font-size: 1.75rem;
        font-weight: 300;
    }

    .brc-default-l1 
    {
        border-color: #dce9f0 !important;
    }

    .ml-n1,
    .mx-n1 
    {
        margin-left: -.25rem !important;
    }

    .mr-n1,
    .mx-n1 
    {
        margin-right: -.25rem !important;
    }

    .mb-4,
    .my-4 
    {
        margin-bottom: 1.5rem !important;
    }

    hr 
    {
        margin-top: 1rem;
        margin-bottom: 1rem;
        border: 0;
        border-top: 1px solid rgba(0, 0, 0, .1);
    }

    .text-grey-m2 
    {
        color: #888a8d !important;
    }

    .text-success-m2 
    {
        color: #86bd68 !important;
    }

    .font-bolder,
    .text-600 
    {
        font-weight: 600 !important;
    }

    .text-110 
    {
        font-size: 100% !important;
    }

    .text-blue 
    {
        color: #478fcc !important;
    }

    .pb-25,
    .py-25 
    {
        padding-bottom: .75rem !important;
    }

    .pt-25,
    .py-25 
    {
        padding-top: .75rem !important;
    }

    .bgc-default-tp1 
    {
        background-color: rgba(121, 169, 197, .92) !important;
    }

    .bgc-default-l4,
    .bgc-h-default-l4:hover 
    {
        background-color: #f3f8fa !important;
    }

    .page-header .page-tools 
    {
        -ms-flex-item-align: end;
        align-self: flex-end;
    }

    .btn-light 
    {
        color: #757984;
        background-color: #f5f6f9;
        border-color: #dddfe4;
    }

    .w-2 
    {
        width: 1rem;
    }

    .text-120 
    {
        font-size: 110% !important;
    }

    .text-primary-m1 
    {
        color: #4087d4 !important;
    }

    .text-danger-m1 
    {
        color: #dd4949 !important;
    }

    .text-blue-m2 
    {
        color: #68a3d5 !important;
    }

    .text-150 
    {
        font-size: 150% !important;
    }

    .text-60 
    {
        font-size: 60% !important;
    }

    .text-grey-m1 
    {
        color: #7b7d81 !important;
    }

    .align-bottom 
    {
        vertical-align: bottom !important;
    }

    #itemlist td 
    {
        padding: 6px 15px;
    }
</style>

<script>
    function printcontent() 
    {
        var printContents = document.getElementById('print').innerHTML;
        var originalContents = document.body.innerHTML;
        document.body.innerHTML = printContents;
        window.print();
        document.body.innerHTML = originalContents;
    }

    function printcustomercopy() 
    {
        var printContents = document.getElementById('printcustomer').innerHTML;
        var originalContents = document.body.innerHTML;
        document.body.innerHTML = printContents;
        window.print();
        document.body.innerHTML = originalContents;
    }
</script>

<div class="content-body">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="m-2" style="display: flex; justify-content:space-between;">
                            <div>
                                <h4>Sale</h4>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="bg-dark text-white p-2">
                                    Admin Copy
                                    <button type="button" class="btn btn-sm btn-success float-end" onclick="printcontent()">
                                        <i class="fa fa-print"></i>
                                        Print
                                    </button>
                                    <button type="button" class="btn btn-success btn-sm" id="payment">Payments</button>
                                    <button type="button" class="btn btn-warning btn-sm" id="payment_history">Payments history</button>
                                    <a href="edit-invoice?id=<?= $invoice['id'] ?>" class="btn btn-sm btn-warning float-end mx-3">
                                        <i class="fa fa-pen"></i>
                                        Edit
                                    </a>
                                </div>
                                <div class="" id="print">
                                    <div>
                                        <div class="mt-3" id style="display: flex; justify-content: space-between;">
                                            <div>
                                                <div>
                                                    <?= $invoice['company'] ?><br>
                                                    <?= $invoice['company_address'] ?><br><?= $invoice['company_number'] ?> <br>
                                                    <?= $invoice['company_email'] ?><br>
                                                    GST NO. TESTGST321654 <br>
                                                    <hr>
                                                </div>
                                                <div> To, <br>
                                                    <?= $invoice['customer'] ?><br>
                                                    <?= $invoice['address'] ?> <br>
                                                    <?= $invoice['city'] ?>
                                                    <?= $invoice['state'] ?><br>
                                                    <?= $invoice['pincode'] ?> <br>
                                                    <?= $invoice['gst'] ?>
                                                </div>
                                            </div>
                                            <div>
                                                <div>
                                                    Sale No. <?= $invoice['invoice'] ?><br>
                                                    Sale Date. <?= $invoice['invoice_date'] ?>
                                                </div>
                                                <div class="">
                                                    <img src="<?= $setting['img_1'] ?>" width="120px">
                                                </div>

                                            </div>
                                        </div>
                                        <div class="mt-3">
                                        </div>
                                        <div class="mt-3 table-responsive">
                                            <table class="table ">
                                                <thead>
                                                    <tr>
                                                        <th>S.No</th>
                                                        <th>Category</th>
                                                        <th>Description</th>
                                                        <th>Qty</th>
                                                        <th>Price</th>
                                                        <th>GST Type</th>
                                                        <th>GST</th>
                                                        <th>TCS</th>
                                                        <th>Total</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php

                                                    $stmt = $mysqli->prepare("SELECT * from  payments  where order_id=?");
                                                    $stmt->bind_param('i', $_GET['id']);
                                                    $stmt->execute();
                                                    $payments = $stmt->get_result();
                                                    $paid_amt = 0;
                                                    while ($row = $payments->fetch_assoc()) {
                                                        $paid_amt += $row['amount_received'];
                                                    }


                                                    $stmt = $mysqli->prepare("SELECT a.*,b.hsn_code  from  order_det a left join category b on a.category=b.name where a.order_id=? group by a.id");
                                                    $stmt->bind_param('i', $_GET['id']);
                                                    $stmt->execute();
                                                    $res = $stmt->get_result();
                                                    $sno = 1;
                                                    $total = 0;
                                                    $gst = 0;
                                                    $c_total_gst = 0;
                                                    $commision = 0;
                                                    $commision_total = 0;
                                                    $comision_price = 0;
                                                    $comision_gst = 0;
                                                    $total_com_gst_price = 0;
                                                    $total_price = 0;
                                                    $total_price_with_qty = 0;
                                                    $tp = 0;
                                                    $gst_price_view = "";
                                                    $tcs = 0;
                                                    $tcs_total = 0;
                                                    $tcs_commission = 0;
                                                    $tcs_commission_total = 0;

                                                    while ($row = $res->fetch_assoc()) {
                                                        $c_gst = 0;
                                                        $total += $row['price'] * $row['qty'];
                                                    
                                                        $gst += ($row['price'] * $row['qty']) * ($row['gst'] / 100);
                                                        $price = 0;
                                                        $tcs=$row['price'] * $row['qty']/100*$row['tcs'];
                                                        $tcs_total += $row['price'] * $row['qty']/100*$row['tcs'];
                                                        $tcs_commission=$row['commision'] * $row['qty']/100*$row['tcs'];
                                                        $tcs_commission_total += $row['commision'] * $row['qty']/100*$row['tcs'];

                                                        $total_price += $comision_price + $price;

                                                        $gst_price = "";

                                                        $c_gst = $row['qty'] * ($row['commision'] - ($row['commision'] / (1 + ($row['gst']) / 100)));
                                                        $c_total_gst += $c_gst;
                                                        $commision += $row['qty'] * $row['commision'];
                                                        $gst_type = "";


                                                        if ($row['gst_type'] == "include") {
                                                            $include_price = (1 + ($row['gst']) / 100);
                                                            $price = ($row['price'] / $include_price);


                                                            $commision_total += $row['qty'] * ($row['commision'] / (1 + ($row['gst']) / 100));


                                                            $comision_price = number_format(($row['commision'] / (1 + ($row['gst']) / 100)), 2);
                                                            $comision_gst = number_format(($row['commision'] - ($row['commision'] / (1 + ($row['gst']) / 100))), 2);


                                                            $total_com_gst_price = ($comision_gst + $comision_price) * $row['qty'];

                                                            $tp = $row['price'] * $row['qty'];
                                                            $total_price_with_qty += $tp + $total_com_gst_price;



                                                            if ($invoice['gst_type'] == "Outer GST") {
                                                                $gst_type = 'IGST: ' . ($row['gst']) . "%";
                                                                $gst_price = number_format(($row['gst'] / 100 * $price), 2);

                                                                $gst_price_view = number_format(($comision_gst), 2);
                                                            } else {
                                                                $gst_type = 'SGST: ' . number_format($row['gst'] / 2, 2) . '% <br> CGST: ' . number_format($row['gst'] / 2, 2) . '%';

                                                                $gst_price = number_format(($row['gst'] / 100 * $price) / 2, 2) . "<br>" . number_format(($row['gst'] / 100 * $price / 2), 2);


                                                                $gst_price_view = number_format(($comision_gst) / 2, 2) . "<br>" . number_format(($comision_gst / 2), 2);
                                                            }
                                                        } else {
                                                            $price = $row['price'];
                                                            $comision_price = (($row['commision']));
                                                            $comision_gst = (($row['commision'] * (($row['gst']) / 100)));

                                                            $total_com_gst_price = ($comision_gst + $comision_price) * $row['qty'];

                                                            $tp = ($row['price'] * $row['qty'] + ($row['price'] * $row['qty']) * ($row['gst'] / 100));
                                                            $total_price_with_qty += $tp + $total_com_gst_price;
                                                            $gst_price = ($row['gst'] / 100 * $price);


                                                            if ($invoice['gst_type'] == "Outer GST") {
                                                                $gst_type = 'IGST: ' . number_format($row['gst'], 2) . "%";
                                                                $gst_price = number_format(($row['gst'] / 100 * $price), 2);

                                                                $gst_price_view = number_format(($comision_gst), 2);
                                                            } else {
                                                                $gst_type = 'SGST: ' . number_format($row['gst'] / 2, 2) . '% <br> CGST: ' . number_format($row['gst'] / 2, 2) . '%';
                                                                $gst_price = number_format(($row['gst'] / 100 * $price) / 2, 2) . " <br>" .
                                                                    number_format(($row['gst'] / 100 * $price / 2), 2);

                                                                $gst_price_view = number_format(($comision_gst) / 2, 2) . "<br>" . number_format(($comision_gst / 2), 2);
                                                            }
                                                        }




                                                        echo '<tr>
                                                        <td>' . $sno++ . '</td>
                                                        <td>' . $row['category'] . ' (HSN : ' . $row['hsn_code'] . ')</td>
                                                        <td ">' . $row['description'] . '</td>
                                                        <td>' . $row['qty'] . '</td> 
                                                        <td>' . number_format($price, 2) . '</td>
                                                        <td>' . $gst_type . '</td>
                                                        <td>' . ($gst_price) . '</td>
                                                    
                                                        <td>' . $tcs . '</td>
                                                        <td>' . $tp+$tcs . '</td>
                                                        </tr>';
                                                        if ($comision_price > 0) {




                                                            echo  '<tr>
                                                            <td></td>
                                                            <td></td>
                                                    <td colspan="" class="text-center">COMMISSION</td>
                                                    <td>' . $row['qty'] . '</td>
                                                    <td>' . ($comision_price) . '</td>
                                                


                                                <td>' . $gst_type . '</td>
                                                <td>' . ($gst_price_view) . '</td>





                                                <td>' . $tcs_commission . '</td>
                                                <td>' . $total_com_gst_price+$tcs_commission . '</td>
                                                </tr>
                                                        
                                                        ';
                                                        }
                                                    }
                                                    ?>



                                                    <tr>
                                                        <td colspan="6">
                                                            Subtotal
                                                        </td>

                                                        <td></td>
                                                        <td></td>
                                                        <td><?= number_format($total_price_with_qty+$tcs_total+$tcs_commission_total, 2) ?></td>
                                                    </tr>




                                                
                                                    <tr>
                                                        <td colspan="7">Grand Total</td>
                                                        <td></td>
                                                        <td><?= number_format($total_price_with_qty+$tcs_total+$tcs_commission_total + $invoice['service_tax'] / 100 * ($total_price_with_qty), 2) ?></td>
                                                    </tr>
                                                    </tr>
                                                    <tr>
                                                        <td colspan="7">Paid Amount</td>
                                                        <td></td>
                                                        <td><?= number_format($paid_amt, 2) ?></td>
                                                    </tr>

                                                    <td colspan="7">Due Amount</td>
                                                    <td></td>
                                                    <td class="text-danger"><?= number_format(($total_price_with_qty+$tcs_total+$tcs_commission_total + $invoice['service_tax'] / 100 * ($total_price_with_qty)) - $paid_amt, 2) ?></td>
                                                    </tr>
                                                    <tr>
                                                        <td colspan="8" class="text-center">MAKE ALL PAYMENTS TO "Clikzop Expertz"</td>
                                                    </tr>
                                                    <tr>
                                                        <td colspan="8">Bank Details

                                                            HDFC Bank

                                                            Account Name:
                                                            Clikzop Expertz

                                                            Ac No.- 50200036546546

                                                            Ifsc Code- HDFC0004031

                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td colspan="8" class="text-center">THANK YOU FOR YOUR BUSINESS!</td>
                                                    </tr>

                                                </tbody>

                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="bg-secondary text-white p-2">
                                    Customer Copy
                                    <button type="button" class="btn btn-sm btn-dark float-end" onclick="printcustomercopy()">
                                        <i class="fa fa-print"></i>
                                        Print
                                    </button>

                                </div>



                                <div class="" id="printcustomer">

                                    <div>
                                        <div class="mt-3" id style="display: flex; justify-content: space-between;">
                                            <div>
                                                <div>

                                                    <?= $invoice['company'] ?><br>
                                                    <?= $invoice['company_address'] ?><br><?= $invoice['company_number'] ?> <br>
                                                    <?= $invoice['company_email'] ?><br>
                                                    GST NO. TESTGST3210223 <br>

                                                    <hr>

                                                </div>
                                                <div> To, <br>
                                                    <?= $invoice['customer'] ?><br>
                                                    <?= $invoice['address'] ?> <br>
                                                    <?= $invoice['city'] ?>
                                                    <?= $invoice['state'] ?><br>
                                                    <?= $invoice['pincode'] ?><br>
                                                    <?= $invoice['gst'] ?>
                                                </div>
                                            </div>
                                            <div>
                                                <div>
                                                    Sale No. <?= $invoice['invoice'] ?><br>
                                                    Sale Date. <?= $invoice['invoice_date'] ?>
                                                </div>
                                                <div class="">
                                                    <img src="<?= $setting['img_1'] ?>" width="120px">
                                                </div>

                                            </div>

                                        </div>

                                        <div class="mt-3">


                                        </div>
                                        <div class="mt-3">
                                            <table class="table">
                                                <thead>
                                                    <tr>
                                                        <th>S.No</th>
                                                        <th>Category</th>
                                                        <th>Description</th>
                                                        <th>Qty</th>
                                                        <th>Price</th>
                                                        <th>TOtal</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php

                                                    $stmt = $mysqli->prepare("SELECT * from  order_det  where order_id=?");
                                                    $stmt->bind_param('i', $_GET['id']);
                                                    $stmt->execute();
                                                    $res = $stmt->get_result();
                                                    $sno = 1;
                                                    $total = 0;
                                                    $price = 0;
                                                    $commision = 0;
                                                    $comision = 0;
                                                    $gst = 0;
                                                    $total_price_with_qty = 0;
                                                    $tp = 0;
                                                    $tcs_total = 0;
                                                    $tcs_commission = 0;
                                                    $tcs_commission_total = 0;
                                                    while ($row = $res->fetch_assoc()) {
                                                        $c_gst = 0;
                                                        $total += $row['price'] * $row['qty'];
                                                        $gst += ($row['price'] * $row['qty']) * ($row['gst'] / 100);
                                                        $price = 0;

                                                        $total_price += $comision_price + $price;

                                                        $gst_price = "";
                                                        $gst_price_n = "";

                                                        $c_gst = $row['qty'] * ($row['commision'] - ($row['commision'] / (1 + ($row['gst']) / 100)));
                                                        $c_total_gst += $c_gst;
                                                        $commision += $row['qty'] * $row['commision'];
                                                        $gst_type = "";

                                                        $tcs=$row['price'] * $row['qty']/100*$row['tcs'];
                                                        $tcs_total += $row['price'] * $row['qty']/100*$row['tcs'];
                                                        $tcs_commission=$row['commision'] * $row['qty']/100*$row['tcs'];
                                                        $tcs_commission_total += $row['commision'] * $row['qty']/100*$row['tcs'];


                                                        if ($row['gst_type'] == "include") {
                                                            $include_price = (1 + ($row['gst']) / 100);
                                                            $price = ($row['price'] / $include_price);


                                                            $commision_total += $row['qty'] * ($row['commision'] / (1 + ($row['gst']) / 100));


                                                            $comision_price = number_format(($row['commision'] / (1 + ($row['gst']) / 100)), 2);
                                                            $comision_gst = number_format(($row['commision'] - ($row['commision'] / (1 + ($row['gst']) / 100))), 2);


                                                            $total_com_gst_price = ($comision_gst + $comision_price) * $row['qty'];

                                                            $tp = $row['price'] * $row['qty'];
                                                            $total_price_with_qty += $tp + $total_com_gst_price;

                                                            $gst_price_n = ($row['gst'] / 100 * $price);

                                                            if ($invoice['gst_type'] == "Outer GST") {
                                                                $gst_type = 'IGST: ' . ($row['gst']) . "%";
                                                                $gst_price = (($row['gst'] / 100 * $price));
                                                            } else {
                                                                $gst_type = 'SGST: ' . ($row['gst'] / 2) . '% <br> CGST: ' . ($row['gst'] / 2) . '%';

                                                                $gst_price = (($row['gst'] / 100 * $price) / 2) . "<br>" . (($row['gst'] / 100 * $price / 2));
                                                            }
                                                        } else {
                                                            $price = $row['price'];
                                                            $comision_price = (($row['commision']));
                                                            $comision_gst = (($row['commision'] * (($row['gst']) / 100)));

                                                            $total_com_gst_price = ($comision_gst + $comision_price) * $row['qty'];

                                                            $tp = ($row['price'] * $row['qty'] + ($row['price'] * $row['qty']) * ($row['gst'] / 100));
                                                            $total_price_with_qty += $tp + $total_com_gst_price;
                                                            $gst_price_n = ($row['gst'] / 100 * $price);

                                                            if ($invoice['gst_type'] == "Outer GST") {
                                                                $gst_type = 'IGST: ' . ($row['gst']) . "%";
                                                                $gst_price = (($row['gst'] / 100 * $price));
                                                            } else {
                                                                $gst_type = 'SGST: ' . ($row['gst'] / 2) . '% <br> CGST: ' . ($row['gst'] / 2) . '%';
                                                                $gst_price = (($row['gst'] / 100 * $price) / 2) . " <br>" . (($row['gst'] / 100 * $price / 2));
                                                            }
                                                        }


                                                        echo '<tr>
                                                        <td>' . $sno++ . '</td>
                                                            <td>' . $row['category'] . '</td>
                                                        <td>' . $row['description'] . '</td>
                                                        <td> ' . $row['qty'] . ' </td>
                                                        <td> ' . $price + $gst_price_n + $comision_price + $comision_gst +$tcs_total. ' </td>
                                                        <td>' . ($price + $gst_price_n + $comision_price + $comision_gst+$tcs_total) * $row['qty'] . '</td>
                                                        </tr>';
                                                    }

                                                    ?>


                                                    <tr>
                                                        <td colspan="4">Subtotal(Incl Gst)</td>
                                                        <td></td>
                                                        <td><?= number_format($total_price_with_qty+$tcs_total+$tcs_commission_total, 2)  ?></td>
                                                    </tr>
                                                    
                                                    <tr>
                                                        <td colspan="4">Grand Total</td>
                                                        <td></td>
                                                        <td><?= number_format($total_price_with_qty +$tcs_total+$tcs_commission_total+ $invoice['service_tax'] / 100 * ($total_price_with_qty), 2) ?></td>
                                                    </tr>
                                                    <tr>
                                                        <td colspan="6" class="text-center">MAKE ALL PAYMENTS TO "Clikzop Expertz"</td>
                                                    </tr>
                                                    <tr>
                                                        <td colspan="6">Bank Details

                                                            HDFC Bank

                                                            Account Name:
                                                            Clikzop Expertz

                                                            Ac No.- 50200036546546

                                                            Ifsc Code- HDFC0004031

                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td colspan="6" class="text-center">THANK YOU FOR YOUR BUSINESS!</td>
                                                    </tr>

                                                </tbody>

                                            </table>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalId" tabindex="-1" role="dialog" aria-labelledby="modalTitleId" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form method="post">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitleId">Add Service Tax on Product</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Description</th>
                                <th>Qty</th>
                                <th>Price</th>
                            </tr>
                        </thead>
                        <tbody id="list">
                        </tbody>
                    </table>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" name="btnSubmit" class="btn btn-primary">Save</button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="modalId_p" tabindex="-1" role="dialog" aria-labelledby="modalTitleId" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form method="POST">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitleId">Payment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12 mt-2">
                            <label>Amount Received</label>
                            <input type="number" step="0.01" name="amount_received" id="amount_received" class="form-control">
                        </div>
                        <div class="col-md-12 mt-2">
                            <label>Payment Date</label>
                            <input type="date" name="payment_date" id="payment_date" class="form-control">
                        </div>
                        <div class="col-md-12 mt-2">
                            <label>Payment Mode</label>
                            <select name="payment_mode" id="payment_mode" class="form-control">
                                <option value="">Select</option>
                                <option value="Cash">Cash</option>
                                <option value="Cheque">Cheque</option>
                                <option value="RTGS">RTGS</option>
                                <option value="UPI">UPI</option>
                                <option value="CARD">CARD</option>
                            </select>
                        </div>
                        <div class="col-md-12 mt-2">
                            <label>Transaction ID</label>
                            <input type="text" name="transaction_id" id="transaction_id" class="form-control">
                        </div>
                        <div class="col-md-12 mt-2">
                            <label>Payment Status</label>
                            <select name="payment_status" id="transaction_id" class="form-control" required>
                                <option value="">Select Status</option>
                                <option value="Partial">Partial</option>
                                <option value="Complete">Complete</option>
                            </select>
                        </div>
                        <div class="col-md-12 mt-2">
                            <label>Description</label>
                            <textarea name="description" id="description" class="form-control"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary" name="btnSvaePayment">Save</button>
                </div>
            </div>
        </form>
    </div>
</div>

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

<?php include "Layouts/Footer.php"  ?>

<script>
    $("#customer_id").val("<?= $invoice['customer_id'] ?>")
    var id = "<?= $_GET['id'] ?>";
    $("#payment").on("click", function() 
    {
        $("#modalId_p").modal("show")
    })

    $("#Create_customer_copy").on("click", function() 
    {
        $.ajax({
            method: 'POST',
            url: 'ajax/get-order-details.php',
            dataType: 'text',
            data: {
                id: id
            },
            beforeSend: function(data) 
            {
                $('#wait').show();
            },
            success: function(data) 
            {
                var html = "";
                data = JSON.parse(data)
                data.forEach(element => {
                    var checked = "";
                    if (element.service_tax_applicable == 1) 
                    {
                        checked = "checked";
                    }
                    html += '<tr><td><input type="checkbox" name="service_tax[]" value="' + element.id + '" ' + checked + '></td> <td>' + element.description + '</td><td>' + element.qty + '</td><td>' + element.price + '</td> </tr>';
                });
                $("#list").html(html)
            },
            complete: function(data) 
            {
                $('#wait').hide();
            }
        });
        $("#modalId").modal("show");
    })

    var id = "<?= $_GET['id'] ?>";
    $("#payment_history").on("click", function() 
    {
        $.ajax({
            method: 'POST',
            url: 'ajax/get-payment-history.php',
            dataType: 'text',
            data: {
                id: id
            },
            beforeSend: function(data) 
            {
                $('#wait').show();
            },
            success: function(data) 
            {
                var html = "";
                data = JSON.parse(data)
                data.forEach(element => {
                    var checked = "";
                    if (element.service_tax_applicable == 1) 
                    {
                        checked = "checked";
                    }
                    html += '<tr><td>' + element.id + '</td>  <td>' + element.amount_received + '</td><td>' + element.payment_date + '</td><td>' + element.payment_mode + '</td><td>' + element.transaction_id + '</td><td>' + element.payment_status + '</td><td>' + element.description + '</td> </tr>';
                });
                $("#list_p").html(html)
            },
            complete: function(data) 
            {
                $('#wait').hide();
            }
        });
        $("#view_payment").modal("show");
    })
</script>