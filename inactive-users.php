<?php
    include "Layouts/Header.php";
    include "Layouts/Sidebar.php";
    
    if (isset($_POST['btnAdd'])) 
    {
        $error = Null;

        if (strlen($_POST['mobile']) != 10)
            $error = "Mobile number should be 10 digit";
        if (empty($error)) 
        {
            try 
            {

                $stmt = $mysqli->prepare("INSERT into  users (username,password,role,name,mobile,email,status,joining_date,state,city,address,parent_id)values (?,?,?,?,?,?,?,?,?,?,?,?)");
                $stmt->bind_param("sssssssssssi", $_POST['username'], $_POST['password'], $_POST['role'], $_POST['name'], $_POST['mobile'], $_POST['email'], $_POST['active'], $_POST['joining_date'], $_POST['state'], $_POST['city'], $_POST['address'],$_POST['parent_id']);
                $stmt->execute();
                alert("Team Added Successfully", "success", "success");

                redirect("team.php");
            } 
            catch (Exception $e) 
            {
                alert($e->getMessage(), "error", "error");
                redirect("team.php");
            }
        } 
        else 
        {
            alert($error, "error", "error");
            redirect("team.php");
        }
    }

    if (isset($_POST['btnUpdate']))
    {
        $error = Null;
        if (strlen($_POST['mobile']) != 10)
            $error = "Mobile number should be 10 digit";
        if (empty($error)) 
        {
            try 
            {
                $stmt = $mysqli->prepare("UPDATE  users  set password=?,role=?,name=?,mobile=?,email=?,status=?,joining_date=?,state=?,city=?,address=? ,parent_id = ? where id=?");
                $stmt->bind_param("ssssssssssii",  $_POST['password'], $_POST['role'], $_POST['name'], $_POST['mobile'], $_POST['email'], $_POST['active'], $_POST['joining_date'], $_POST['state'], $_POST['city'], $_POST['address'], $_POST['parent_id'] , $_POST['id']);
                $stmt->execute();
                alert("Team Update Successfully", "success", "success");

                redirect("team.php");
            } 
            catch (Exception $e) 
            {
                alert($e->getMessage(), "error", "error");
                redirect("team.php");
            }
        } 
        else 
        {
            alert($error, "error", "error");
            redirect("team.php");
        }
    }


    if (isset($_POST['BtnAddAmount'])) 
    {
        if (empty($_POST['amount'])) 
        {
            alert("Enter Amount", "error", "error");
            redirect("?");
        }
        try 
        {
            $stmt = $mysqli->prepare("INSERT into wallet_history (customer_id,amount,remarks)values (?,?,?)");
            $stmt->bind_param("ids", $_POST['customer_id'],  $_POST['amount'], $_POST['remarks']);
            $stmt->execute();

            $stmt = $mysqli->prepare("UPDATE users set wallet_amt=wallet_amt+? WHERE id=?");
            $stmt->bind_param("di", $_POST['amount'],  $_POST['customer_id']);
            $stmt->execute();
            $stmt->close();

            alert("Save Successfully", "success", "success");
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
                        <ul class="nav nav-pills flex-column flex-sm-row">
                            <li class="nav-item"><a class="nav-link " href="profile.php"><i class='bx bx-user me-1'></i> Profile</a></li>
                            <li class="nav-item"><a class="nav-link " href="team.php"><i class='bx bx-group me-1'></i> Active</a></li>
                              <li class="nav-item"><a class="nav-link active" href="javascript:void(0);"><i class='bx bx-group me-1'></i> In Active</a></li>
                        </ul>
                    </div>
                    <div class="card-body border-bottom">
                        <div class="card-header p-2" style="display: flex; justify-content: space-between;">
                            <div>
                                <h5>Team</h5>
                            </div>
                            <div>
                                <button class="btn btn-primary" type="button" id="add">Add Team</button>
                            </div>
                            
                        </div>               
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="example" class="display" style="min-width: 845px">
                                <thead>
                                    <tr>
                                        <th>S.No</th>
                                        <th>UserName</th>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Mobile</th>
                                        <th>Role</th>
                                        <th>Parent Name</th>
                                        <th>Address</th>
                                        <th>Joining Date</th>
                                        <th>Status</th>
                                        <th>Wallet Amount</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>                              
                                <?php
                                   $stmt = $mysqli->prepare("SELECT 
        u.*, 
        p.name AS parent_name 
    FROM users u
    LEFT JOIN users p ON u.parent_id = p.id
    WHERE u.role != 'admin' and u.status=0
");

                                    $stmt->execute();
                                    $staff = $stmt->get_result();
                                    $sno = 1;
                                    while ($row = $staff->fetch_assoc()) 
                                    {
                                        echo '<tr>
                                            <td>' . $sno++ . '</td>
                                            <td>' . $row['username'] . '</td>
                                            <td>' . $row['name'] . '</td>
                                            <td>' . $row['email'] . '</td>
                                            <td>' . $row['mobile'] . '</td>
                                            <td>' . $row['role'] . '</td>
                                            <td>' . $row['parent_name'] . '</td>
                                            <td>' . $row['address'] . '</td>
                                            <td>' . $row['joining_date'] . '</td>
                                            <td>';
                                        if ($row['status'] == 1) 
                                        {
                                            echo '<span class="badge bg-success">Active</span>';
                                        } 
                                        else 
                                        {
                                            echo '<span class="badge bg-danger">In Active</span>';
                                        }                              
                                        echo '
                                        <td>' . $row['wallet_amt'] . '</td>
                                        </td>
                                            <td><button class="btn btn-primary btn-sm edit" data-id="' . $row['id'] . '" data-role="'.$row['role'].'"><i class="fa fa-pen"></i></button>
                                            <button type="button" class="btn btn-dark btn-sm AddWalletAmt" data-id="' . $row['id'] . '"><i class="fa-solid fa-indian-rupee-sign"></i></button>

                                            <button class="btn btn-sm btn-info WalletHistory" type="button" data-id="' . $row['id'] . '"><i class="fa fa-eye" aria-hidden="true"></i></button>
                                            
                                            <button class="delete-user btn btn-sm btn-danger" type="button" data-id="'.$row['id'].'"><i class="fa fa-trash"></i></button>
                                            </td>
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

<div class="modal fade" id="TeamModel" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg ">
        <form class="needs-validation" novalidate method="POST" enctype="multipart/form-data">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="modal-title fs-5" id="title">Update Image</h3>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">

                    </button>
                </div>
                <div class="modal-body">
                    <input id="id" name="id" type="hidden">
                    <div class="row">
                        <div class="col-md-6">
                            <input type="hidden" class="form-control" name="id" id="id" required>
                            <label>Username <span class="text-danger" id="alert">* Username must be unique</span> </label>
                            <input type="text" class="form-control" name="username" id="username" placeholder="Enter username..." required>
                        </div>
                        <div class="col-md-6">
                            <label>Role</label>
                            <select class="form-control" name="role" id="role" placeholder="Enter name..." required>
                                <option value="">Select Role</option>
                                <option value="Team Manager">Team Manager</option>
                                <option value="Staff">Staff</option>
                            </select>
                        </div>
                        <div class="col-md-6 mt-3">
                            <label>Reporting Manager</label>
                          
                                    <select name="parent_id" id="parent_id" class="form-control " required>
                                        <?php
                                    $user_id = $row['user_id'];
                                  $stmt_user = $mysqli->prepare("SELECT * FROM users WHERE role = 'Team Manager' OR role = 'admin' and status ='1'");

                                    $stmt_user->execute();
                                    $res = $stmt_user->get_result();
                                    while($row = $res->fetch_assoc()):
                                    ?>
                                        <option value="<?= $row['id'] ?>"><?= $row['name'] ?></option>
                                        <?php endwhile; ?>
                                    </select>
                                
                        </div>
                        <div class="col-md-6 mt-3">
                            <label>Name</label>
                            <input type="text" class="form-control" name="name" id="id" placeholder="Enter name..." required>
                        </div>
                        <div class="col-md-6 mt-3">
                            <label>Email</label>
                            <input type="email" class="form-control" name="email" id="email" placeholder="Enter email..." required>
                        </div>
                        <div class="col-md-6 mt-3">
                            <label>Mobile</label>
                            <input type="number" class="form-control" name="mobile" id="mobile" placeholder="Enter mobile..." required>
                        </div>
                        <div class="col-md-6 mt-3">
                            <label>Joining Date</label>
                            <input type="date" class="form-control" name="joining_date" id="joining_date" placeholder="Enter mobile..." required>
                        </div>
                        <div class="col-md-6 mt-3">
                            <label>State</label>
                            <select class="form-control" name="state" id="state" placeholder="Enter name..." required>
                                <option value="">Select state</option>
                                <?php
                                $stmt = $mysqli->prepare("SELECT Distinct state from state_district");
                                $stmt->execute();
                                $res = $stmt->get_result();

                                while ($row = $res->fetch_assoc()) {
                                    echo '<option value="' . $row['state'] . '">' . $row['state'] . '</option>';
                                }
                                ?>
                            </select>
                        </div>
                        <div class="col-md-6 mt-3">
                            <label>City</label>
                            <select class="form-control" name="city" id="city" placeholder="Enter name..." required>
                                <option value="">Select city</option>
                            </select>
                        </div>
                        <div class="col-md-12 mt-3">
                            <label>Address</label>
                            <textarea type="text" class="form-control" name="address" id="address" placeholder="Enter address..." required></textarea>
                        </div>
                        <div class="col-md-6 mt-3">
                            <label>Password</label>
                            <input type="" class="form-control" name="password" id="password" placeholder="Enter password..." required>

                        </div>
                        <div class="col-md-6 mt-3">
                            <label>Active</label>
                            <select class="form-control" name="active" id="active" placeholder="Enter name..." required>
                                <option value="1">Active</option>
                                <option value="0">In Active</option>
                            </select>
                        </div>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-success" id="btn_save">Save</button>
                </div>
            </div>
        </form>
    </div>


</div>

<div class="modal fade" id="WalletModal">
    <div class="modal-dialog " role="document">
        <form method="POST" class="needs-validation" novalidate>
            <div class="modal-content">
                <div class="modal-header">
                    <input type="hidden" id="customer_id" name="customer_id">
                    <h5 class="modal-title" id="modalTitleId">
                        Wallet Amount
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body row">
                    <div class="col-md-12">
                        <label for="">Amount</label>
                        <input type="number" step="0.01" class="form-control" name="amount" required placeholder="Enter Amount">

                    </div>
                    <div class="col-md-12 mt-3">
                        <label for="">Remarks</label>
                        <textarea name="remarks" placeholder="Enter Remarks" class="form-control"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        Close
                    </button>
                    <button type="submit" name="BtnAddAmount" class="btn btn-primary">Save</button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="walletHistory">
    <div class="modal-dialog " role="document">
        <form method="POST" class="needs-validation" novalidate>
            <div class="modal-content">
                <div class="modal-header">
                    <input type="hidden" id="customer_id" name="customer_id">
                    <h5 class="modal-title" id="modalTitleId">
                        Wallet History
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>S.No</th>
                                <th>Amount</th>
                                <th>Remarks</th>
                                <th>Created at</th>
                            </tr>
                        </thead>
                        <tbody id="WalletHistoryList">

                        </tbody>

                    </table>


                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        Close
                    </button>

                </div>
            </div>
        </form>
    </div>
</div>

<?php include "Layouts/Footer.php"  ?>
<script>
    $("#add").on("click", function() 
    {
        $("#title").text("Add Team")
        $("#btn_save").attr("name", "btnAdd")
        $("#alert").text("Username must be unique")
        $("#username").removeAttr("disabled")
        $("input").val("");
        $("select").val("");
        $("textarea").val("");
        $("#TeamModel").modal("show");
    });

    $("#state").on("change", function() 
    {
        $.ajax({
            method: "POST",
            url: "ajax/get-city.php",
            data: {
                state: $(this).val()
            },
            success: function(data) 
            {
                $("#city").html(data)
            }
        });
    });

    $(document).on("click", ".edit", function() 
    {
        $("#title").text("Update Team")
        $("#btn_save").attr("name", "btnUpdate")
        $("#username").attr("disabled", "disabled")
        $("#alert").text("Username  can not changed")

        
        $.ajax({
            method: "POST",
            url: "ajax/get-team.php",
            data: {
                id: $(this).data("id")
            },
            success: function(data) 
            {
                data = JSON.parse(data)

                $("#parent_id").val(data.parent_id);

                //  var data = jQuery.parseJSON('');
                $.each(data.data, function(i, o) 
                {
                    $('input[name=' + i + ']').val(o);
                    $('textarea[name=' + i + ']').val(o);
                    $('select[name=' + i + ']').val(o);
                    if (i == "city") 
                    {
                        $("#city").html('<option value="' + o + '">' + o + '</option>');
                    }
                });

            }
        });
        $("#TeamModel").modal("show");
    });

    $(document).on("click", '.AddWalletAmt', function() 
    {
        $("#customer_id").val($(this).data("id"))
        $("#WalletModal").modal("show");
    })
    $(document).on("click", ".WalletHistory", function() 
    {
        $.ajax({
            method: "POST",
            url: "ajax/get-wallet-history.php",
            data: {
                customer_id: $(this).data("id")
            },
            success: function(data) 
            {
                data = JSON.parse(data);
                var html = "";
                var sno = 1;
                data.forEach(element => {
                    html += "<tr><td>" + sno++ + "</td><td>" + element.amount + "</td><td>" + element.remarks + "</td><td>" + element.created_at + "</td></tr>";

                });
                $("#WalletHistoryList").html(html);
                $("#walletHistory").modal("show");
            }
        });


    });
    $(document).on("click", ".delete-user", function() 
    {
        var id = $(this).attr('data-id');
        console.log(id);
        if(id)
        {
            Swal.fire({
            title: "Are you sure?",
            text: "You won't be able to revert this!",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            confirmButtonText: "Yes, delete it!"
            }).then((result) => {
                if (result.isConfirmed) 
                {
                    $.ajax({
                        url: 'functions.php',
                        type: 'POST',
                        dataType: 'json',
                        data: { 
                            id: id, 
                            action: "delete_user"
                        },                   
                        success: function(response) 
                        {
                            if (response.status === 200) 
                            {
                                Swal.fire("Deleted!", response.message, "success");
                                $(`.user-row[data-id='${id}']`).remove();
                            } 
                            else 
                            {
                                Swal.fire("Error!", response.message, "error");
                            }
                        },
                        error: function() 
                        {
                            Swal.fire("Error!", "Failed to delete user. Server error.", "error");
                        }
                    });
                }
            });
        }
        else
        {
            console.log('Error: id not found!');
        }
    });
</script>