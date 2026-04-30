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
                        <h4 class="card-title">Vendor Report</h4>
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