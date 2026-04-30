<?php
    include "Layouts/Header.php";
    include "Layouts/Sidebar.php";
    if (isset($_POST['btnAddTarget'])) 
    {
        foreach ($_POST['user'] as $key => $value) 
        {
            $stmt = $mysqli->prepare("SELECT * from  target_report where user_id=? and month=? and year=?");
            $stmt->bind_param("iii", $key, $_POST['month'], $_POST['year']);
            $stmt->execute();
            $res = $stmt->get_result()->fetch_assoc();
            if (!$res) 
            {
                $stmt = $mysqli->prepare("INSERT into  target_report (user_id,month,year,target) values (?,?,?,?)");
                $stmt->bind_param("iiid", $key, $_POST['month'], $_POST['year'], $value);
                $stmt->execute();
                $stmt->close();
            }
        }
        alert("Save Successfully", "success");
        redirect("?");
    }
?>

<div class="content-body">
    <div class="container-fluid">
        <!-- row -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Target Report</h4>
                        <button type="button" class="btn btn-square btn-outline-danger float-end"  id="addTarget">
                            <i class="fa fa-print" aria-hidden="true"></i> Add Target</button>
                    </div>
                    <div class="card-body border-bottom">
                        <form method="GET">
                            <div class="row">
                                <div class="col-md-4">
                                    <select name="user_id" id="user_id" class="form-control" required>
                                        <option value="">Select User</option>
                                        <?php
                                            $stmt = $mysqli->prepare("SELECT * from  users where role!='admin'");
                                            $stmt->execute();
                                            $res = $stmt->get_result();
                                            $sno = 1;
                                            while ($row = $res->fetch_assoc()) 
                                            {
                                                echo '<option value="' . $row['id'] . '">' . $row['name'] . '</option>';
                                            }
                                        ?>
                                    </select>
                                </div>
                                <div class="col-md-3 d-flex gap-3">
                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="fa fa-search me-2"></i>Search
                                    </button>
                                    <a href="target-report.php" class="btn btn-primary">
                                        <i class="fa fa-refresh"></i>
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
                                        <th>S.No</th>
                                        <th>Month</th>
                                        <th>Target Amount</th>
                                        <th>Query</th>
                                        <th>Follow Up</th>
                                        <th>Confirmed</th>
                                        <th>Lost</th>
                                        <th>Completed</th>
                                        <th>Revenue</th>
                                        <th>Profit</th>
                                    </tr>
                                </thead>
                                <tbody>                              
                                <?php  
                                    $sno = 1;
                                    for ($i = 1; $i < 13; $i++) 
                                    {
                                        $stmt = $mysqli->prepare("SELECT a.*,count(*) as query, 
                                        SUM(CASE WHEN b.status = 'Follow Up' THEN 1 ELSE 0 END) AS follow_up,
                                        SUM(CASE WHEN b.status = 'Converted' THEN 1 ELSE 0 END) AS converted,
                                        SUM(CASE WHEN b.status = 'Lost' THEN 1 ELSE 0 END) AS lost,
                                        SUM(CASE WHEN b.status = 'Completed' THEN 1 ELSE 0 END) AS completed,
                                        d.qty*d.price as revenue,
                                        ex.amount as expense                         
                                        
                                        from target_report a join query_mst b join order_mst c on b.id=c.query_id join  order_det d on c.id=d.order_id and b.status='Completed' left join expenses ex on c.id=ex.query_id where a.user_id=? and a.month=?  and month(b.created_at)=? group by month(b.created_at)");
                                        $stmt->bind_param("iii", $_GET['user_id'], $i, $i);
                                        $stmt->execute();
                                        $res = $stmt->get_result();

                                            if ($res->num_rows > 0) 
                                            {
                                                while ($row = $res->fetch_assoc()) 
                                                {
                                                    echo '<tr>
                                                    <td>' . $sno++ . '</td>
                                                    <td>' . date("M", mktime(0, 0, 0, $i, 1)) . '</td>
                                                    <td>' . $row['target'] . '</td>
                                                    <td>' . $row['query'] . '</td>
                                                    <td>' . $row['follow_up'] . '</td>
                                                    <td>' . $row['converted'] . '</td>
                                                    <td>' . $row['lost'] . '</td>
                                                    <td>' . $row['completed'] . '</td>
                                                    <td>' . $row['revenue'] . '</td>
                                                    <td>' . $row['revenue'] - $row['expense'] . '</td>
                                                </tr>';
                                                }
                                            } 
                                            else 
                                            {
                                                echo '
                                                <tr>
                                                    <td>' . $sno++ . '</td>
                                                    <td>' . date("M", mktime(0, 0, 0, $i, 1)) . '</td>
                                                    <td>0</td>
                                                    <td>0</td>
                                                    <td>0</td>
                                                    <td>0</td>
                                                    <td>0</td>
                                                    <td>0</td>
                                                    <td>0</td>
                                                    <td>0</td>                         
                                                </tr>';
                                            }
                                        }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <form method="POST" class="needs-validation" novalidate>
                        <div class="modal fade" id="AddTarget">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="modalTitleId">
                                            Add Target
                                        </h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="row">
                                            <div class="col-6">
                                                <select name="month" id="month" class="form-control" required>
                                                    <option value="">--Select Month---</option>
                                                    <?php
                                                    $months = [
                                                        'January', 'February', 'March', 'April', 'May', 'June',
                                                        'July', 'August', 'September', 'October', 'November', 'December'
                                                    ];
                                                    $currentMonth = date('n');

                                                    for ($i = 1; $i < 13; $i++) {
                                                        $selected = ($i == $currentMonth) ? ' selected' : '';
                                                        echo '<option value="' . $i . '" ' . $selected . '>' . $months[$i - 1] . '</option>';
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                            <div class="col-6">
                                                <select name="year" id="year" class="form-control" required>
                                                    <?php
                                                        $year = date('Y');
                                                        for ($i = $year; $i > 2021; $i--) 
                                                        {
                                                            echo '<option value="' . $i . '"  >' . $i . '</option>';
                                                        }
                                                    ?>
                                                </select>
                                            </div>
                                        </div>
                                        <table class="table mt-4">
                                            <tr>
                                                <th>User</th>
                                                <th>Target</th>
                                            </tr>
                                            <?php
                                                $stmt = $mysqli->prepare("SELECT * from  users where role!='admin'");
                                                $stmt->execute();
                                                $res = $stmt->get_result();
                                                $sno = 1;
                                                while ($row = $res->fetch_assoc()) {
                                                    echo '<tr>
                                                            <td>' . $row['name'] . '</td>
                                                            <td><input type="number" min="0" value="0" class="form-control" name="user[' . $row['id'] . ']"></td>
                                                        </tr>';
                                                }
                                            ?>
                                        </table>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                            Close
                                        </button>
                                        <button type="submit" name="btnAddTarget" class="btn btn-primary">Save</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include "Layouts/Footer.php"  ?>

<script>
    $("#addTarget").on("click", function() 
    {
        $("#AddTarget").modal("show");
    });
    $("#user_id").val("<?php if (!empty($_GET['user_id'])) echo   $_GET['user_id'] ?>")
</script>