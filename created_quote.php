<?php
include "Layouts/Header.php";
include "Layouts/Sidebar.php"; 

if(isset($_GET['q_id']) && !empty($_GET['q_id']))
{
    $stmt = $mysqli->prepare("SELECT * FROM quote_master WHERE query_id = ? AND customer_id = ?");
    $stmt->bind_param("ii",$_GET['q_id'],$_GET['id']);
    $stmt->execute();
    $res = $stmt->get_result();

}



?>

<div class="content-body">
    <div class="container-fluid">
        <!-- row -->


        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Created Quote</h4>
                    </div>
                    <div class="card-body">
                        <div class="">
                            <ul class="nav nav-tabs" id="myTab" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active" id="quote-tab" data-bs-toggle="tab"
                                        data-bs-target="#quote-tab-pane" type="button" role="tab"
                                        aria-controls="quote-tab-pane" aria-selected="false">Created Quote</button>
                                </li>

                            </ul>

                            <!-- Tab panes -->
                            <form action="" method="post">
                                <div class="tab-content p-3 border border-top-0">

                                    <div class="tab-pane fade show active " id="quote-tab-pane" role="tabpanel"
                                        aria-labelledby="quote-tab" tabindex="0">
                                        <h4>My Quotations</h4>

                                        <div class="table-responsive mt-3">
                                            <table class="table table-bordered table-striped">
                                                <thead>
                                                    <tr>
                                                        <th>#</th>
                                                        <th>Package Type</th>
                                                     
                                                        <th>Created Date</th>
                                                        <th>Action</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php 
                $i = 1;
                if($res->num_rows > 0):
                    while($row = $res->fetch_assoc()): ?>
                                                    <tr>
                                                        <td><?php echo $i++; ?></td>
                                                        <td><?php echo $row['pakage_type']; ?></td>
                                                      
                                                        <td><?php echo date("d-m-Y", strtotime($row['created_at'])); ?>
                                                        </td>
                                                        <td>
                                                            <a href="create_pdf.php?quote_id=<?php echo $row['id']; ?>&query_id=<?php echo $_GET['q_id']; ?>&id=<?php echo $_GET['id']; ?>"
                                                                class="btn btn-sm btn-danger">PDF</a>
                                                            <a href="view_quotetaions.php?quote_id=<?php echo $row['id']; ?>&q_id=<?php echo $_GET['q_id']; ?>&id=<?php echo $_GET['id']; ?>"
                                                                class="btn btn-sm btn-success">Edit</a>
                                                        </td>
                                                    </tr>
                                                    <?php endwhile; else: ?>
                                                    <tr>
                                                        <td colspan="5" class="text-center">No quotations found.</td>
                                                    </tr>
                                                    <?php endif; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>



                                    <!-- <div class="text-end mt-5">


                                        <button class="btn btn-success" type="submit" name="BtnSubmit">Submit</button>
                                    </div> -->

                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

        </div>



    </div>
</div>




<?php include "Layouts/Footer.php"  ?>

<script>
$(document).ready(function() {
    $("#quotetaion_id").on("click", function() {
        var quote_id = $(this).val();

        $("#generate_pdf").show();
        $("#view_quote").show();
        // $("#policy_list").show();
        // $("#policy-tab-pane").show();

        if (quote_id !== "") {

            var query_id = "<?php echo $_GET['q_id']; ?>";
            var id = "<?php echo $_GET['id']; ?>";

            var newHref = "create_pdf.php?quote_id=" + quote_id + "&query_id=" + query_id + "&id=" + id;
            $("#generate_pdf").attr("href", newHref);
            var newHref2 = "view_quotetaions.php?quote_id=" + quote_id + "&q_id=" + query_id + "&id=" +
                id;
            $("#view_quote").attr("href", newHref2);

        }


    });
});
</script>