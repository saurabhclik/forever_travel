<?php
    include "Layouts/Header.php";
    include "Layouts/Sidebar.php";

    $sale_amount = "";
    $userFilter = isset($_GET['user_filter']) ? $_GET['user_filter'] : '';
    $currentStatus = isset($_GET['status']) ? $_GET['status'] : 'All';

    if (isset($_POST['btnadd'])) 
    {
        if (!empty($_POST['id'])) 
        {
            try 
            {
                $stmt = $mysqli->prepare("UPDATE `query_mst` set `customer_id`=?,`mobile`=?,`email`=?,`destination`=?,`travel_month`=?,`from_date`=?,`to_date`=?,`adult`=?,`child`=?,`service`=?,`user_id`=?,`infant`=?,`source`=?,`priority`=? ,`sale_amount` = ? WHERE id=?");
                $stmt->bind_param("isssssssssisssii",  $_POST['customer_id'], $_POST['mobile'], $_POST['email'], $_POST['destination'], $_POST['travel_month'], $_POST['from_date'], $_POST['to_date'], $_POST['adult'], $_POST['child'], $_POST['service'], $user['id'], $_POST['infant'], $_POST['source'], $_POST['priority'], $_POST['sale_amount'] , $_POST['id']);
                $stmt->execute();
                $stmt->close();
               
                alert("Save Successfully", "success", "success");

                if ($_POST['status'] == "Converted") 
                {
                    redirect("invoices.php");
                }
                redirect("?status=" . $_GET['status'] . "");
            } 
            catch (Exception $e) 
            {
                alert($e->getMessage(), "error", "error");
                redirect("?status=" . $_GET['status'] . "");
            }
        }
        else 
        {
            try 
            {
                $stmt = $mysqli->prepare("INSERT into query_mst (customer_id,mobile,email,destination,travel_month,from_date,to_date,adult,child,service,user_id,infant,source,priority)values (?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
                $stmt->bind_param("isssssssssisss", $_POST['customer_id'], $_POST['mobile'], $_POST['email'], $_POST['destination'], $_POST['travel_month'], $_POST['from_date'], $_POST['to_date'], $_POST['adult'], $_POST['child'], $_POST['service'], $user['id'], $_POST['infant'], $_POST['source'], $_POST['priority']);
                $stmt->execute();
                $mst_id = $mysqli->insert_id;
                $stmt->close();
             
                alert("Save Successfully", "success", "success");
                redirect("?status=" . $_GET['status'] . "");
            } 
            catch (Exception $e) 
            {
                alert($e->getMessage(), "error", "error");
                redirect("?status=" . $_GET['status'] . "");
            }
        }
    }

    if(isset($_POST['btnSaveStatus']))
    {
        // echo '<pre>'; print_r($_POST); exit;
        $stmt = $mysqli->prepare("UPDATE `query_mst` SET `status` = ?, `call_time` = ?, `call_date` = ?, `sale_amount` = ?, `remarks` = ? WHERE `id` = ?");
        $stmt->bind_param("sssssi", $_POST['status'], $_POST['call_time'], $_POST['call_date'], $_POST['sale_amount'], $_POST['remarks'], $_POST['id']);
        $stmt->execute();
        $stmt->close();

        $stmt = $mysqli->prepare("INSERT into `query_det` (`mst_id`,`remarks`,`call_time`,`call_date`,`user_id`,`status`)values (?,?,?,?,?,?)");
        $stmt->bind_param("isssis",   $_POST['id'], $_POST['remarks'], $_POST['call_time'], $_POST['call_date'], $user['id'], $_POST['status']);
        $stmt->execute();

        $_SESSION['alert'] = [
            'title' => 'Lead Updated Successfully',
            'text' => 'The lead details were Updated successfully.',
            'icon' => 'success'
        ];
        redirect("?status=" . $_GET['status'] . "");
    }

    if (isset($_POST['btnSavePayment'])) 
    {
        if (empty($_POST['payment_amount']) || empty($_POST['payment_mode']) || empty($_POST['payment_date']) || empty($_POST['payment_remark'])) {
            alert("Please fill all required fields.", "warning", "warning");
            redirect("?status=" . $_GET['status']);
        }

        if (isset($_POST['query_id']) && !empty($_POST['query_id'])) {
            $stmt = $mysqli->prepare("SELECT sale_amount,from_date FROM query_mst WHERE id = ?");
            $stmt->bind_param("i", $_POST['query_id']);
            $stmt->execute();
            $stmt->bind_result($sale_amount, $from_date);
            $stmt->fetch();
            $stmt->close();

            if (!$sale_amount) {
                alert("Invalid Query ID.", "error", "error");
                redirect("?status=" . $_GET['status']);
            }

            $stmt = $mysqli->prepare("SELECT COALESCE(SUM(amount), 0) FROM payment WHERE query_id = ?");
            $stmt->bind_param("i", $_POST['query_id']);
            $stmt->execute();
            $stmt->bind_result($total_paid);
            $stmt->fetch();
            $stmt->close();

            $new_payment = $_POST['payment_amount'];
            if (($total_paid + $new_payment) > $sale_amount) {
                alert("Payment exceeds the sale amount. Available balance: " . ($sale_amount - $total_paid), "error", "error");
                redirect("?status=" . $_GET['status']);
            }

            $stmt = $mysqli->prepare("INSERT INTO payment (amount, payment_type, ref_no, date, remark, query_id ,user_id) VALUES (?, ?, ?, ?, ?, ?,?)");
            $stmt->bind_param("sssssii", $_POST['payment_amount'], $_POST['payment_mode'], $_POST['payment_ref_no'], $_POST['payment_date'], $_POST['payment_remark'], $_POST['query_id'], $user['id']);
            $stmt->execute();
            $stmt->close();

            $stmt = $mysqli->prepare("SELECT COALESCE(SUM(amount), 0) FROM payment WHERE query_id = ?");
            $stmt->bind_param("i", $_POST['query_id']);
            $stmt->execute();
            $stmt->bind_result($total_paid_after_payment);
            $stmt->fetch();
            $stmt->close();

            if ($total_paid_after_payment == $sale_amount) {
                if (strtotime($from_date) <= strtotime(date('Y-m-d'))) {
                    $stmt = $mysqli->prepare("UPDATE query_mst SET status = 'Completed', payment_status = 'complete' WHERE id = ?");
                    $stmt->bind_param("i", $_POST['query_id']);
                    $stmt->execute();
                    $stmt->close();
                } elseif (strtotime($from_date) > strtotime(date('Y-m-d'))) {
                    $stmt = $mysqli->prepare("UPDATE query_mst SET payment_status = 'complete' WHERE id = ?");
                    $stmt->bind_param("i", $_POST['query_id']);
                    $stmt->execute();
                    $stmt->close();
                }
            } elseif ($total_paid_after_payment < $sale_amount) {
                $stmt = $mysqli->prepare("SELECT payment_status FROM query_mst WHERE id = ?");
                $stmt->bind_param("i", $_POST['query_id']);
                $stmt->execute();
                $stmt->bind_result($current_payment_status);
                $stmt->fetch();
                $stmt->close();

                if ($current_payment_status == 'complete') {
                    $stmt = $mysqli->prepare("UPDATE query_mst SET payment_status = 'pending' WHERE id = ?");
                    $stmt->bind_param("i", $_POST['query_id']);
                    $stmt->execute();
                    $stmt->close();
                }
            }

            $_SESSION['alert'] = [
                'title' => 'Payment Added Successfully',
                'text' => 'The payment has been recorded.',
                'icon' => 'success'
            ];
            redirect("?status=" . $_GET['status']);
        } else {
            alert("Query ID missing.", "error", "error");
            redirect("?status=" . $_GET['status']);
        }
    }

    if(isset($_POST['AddCustomerQuery']))
    {
        try
        {
            $customer_stmt = $mysqli->prepare("INSERT into `customers` (`name`,`number`,`email`,`address`,`city`,`state`,`pincode`,`country`,`user_id`,`gst_no`,`pan_number`,`number2`,`email2`,`birthday`,`anniversary`,`pre_name`)values (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
            $customer_stmt->bind_param("ssssssssisssssss", $_POST['name'],  $_POST['number'], $_POST['email'], $_POST['address'], $_POST['city'], $_POST['state'], $_POST['pincode'], $_POST['country'], $user['id'], $_POST['gst_no'], $_POST['pan_number'], $_POST['number2'], $_POST['email2'], $_POST['birthday'], $_POST['anniversary'], $_POST['pre']);
            $customer_stmt->execute();
            $customer_id = $customer_stmt->insert_id;

            $stmt = $mysqli->prepare("INSERT into query_mst (customer_id,mobile,email,destination,travel_month,from_date,to_date,adult,child,service,user_id,infant,source,priority)values (?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
            $stmt->bind_param("isssssssssisss", $customer_id, $_POST['number'], $_POST['email'], $_POST['destination'], $_POST['travel_month'], $_POST['from_date'], $_POST['to_date'], $_POST['adult'], $_POST['child'], $_POST['service'], $user['id'], $_POST['infant'], $_POST['source'], $_POST['priority']);
            $stmt->execute();
            $mst_id = $mysqli->insert_id;
            $stmt->close();

            $_SESSION['alert'] = [
                'title' => 'Lead Added Successfully',
                'text' => 'The customer and lead details were saved successfully.',
                'icon' => 'success'
            ];
            redirect("?status=" . $_GET['status']);
        }
        catch(Exception $e)
        {
            $_SESSION['alert'] = [
                'title' => 'Lead Not Added',
                'text' => 'An error occurred while saving the lead. Please try again.',
                'icon' => 'error'
            ];
            redirect("?status=" . $_GET['status']);
        }
    }

    if (isset($_POST['btnUpload']))
    {
        if (isset($_FILES['files']['name'])) 
        {
            $upload_dir = '../files/';
            $file_count = count($_FILES['files']['name']);
            for ($i = 0; $i < $file_count; $i++) 
            {
                $pic_name = $_FILES['files']['name'][$i];
                $pic_tmp_name = $_FILES['files']['tmp_name'][$i];

                $random_name = rand(1000, 10000000000000000) . '-' . $pic_name;
                $upload_name = $upload_dir . strtolower($random_name);
                $upload_name = preg_replace('/\s+/', '-', $upload_name);

                try 
                {
                    $res = move_uploaded_file($pic_tmp_name, $upload_name);
                    $stmt = $mysqli->prepare("INSERT into query_imgs(mst_id,image) VALUES(?,?)");
                    $stmt->bind_param("is", $_POST['id'], $upload_name);
                    $stmt->execute();
                    $stmt->close();
                    alert("Save Successfully", "success", "success");
                } 
                catch (Exception $e) 
                {
                    alert($e->getMessage(), "error", "error");
                }
            }
        }
        else 
        {
            alert("Image not Selected", "error", "error");
        }
        redirect("?status=" . $_GET['status'] . "");
    }

    if (isset($_POST['btnSave'])) 
    {
        try 
        {
            $stmt = $mysqli->prepare("SELECT * from customers where id=?");
            $stmt->bind_param('i', $_POST['ids']);
            $stmt->execute();
            $customers = $stmt->get_result()->fetch_assoc();

            $image_path = null;
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $target_dir = "./images/invoices/";
                if (!is_dir($target_dir)) {
                    mkdir($target_dir, 0777, true);
                }
                $image_name = time() . "_" . basename($_FILES["image"]["name"]);
                $target_file = $target_dir . $image_name;

                if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                    $image_path = $image_name;
                } else {
                    throw new Exception("Image upload failed to $target_file");
                }
            }

            if ($_POST['expense_type'] == 'labour') 
            {
                $_POST['vendor_id'] = $_POST['labour_id'];
            }

            $stmt = $mysqli->prepare("INSERT into expenses (expenses_for,ids,name,expense_category,expense_date,amount,file,payment_mode,note,ref_no,vendor_id,user_id,expense_type,query_id,expense_subcategory,build,paid_status)values (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
            $stmt->bind_param("sisssdssssiisisss", $_POST['expenses_for'], $_POST['ids'], $_POST['name'], $_POST['expense_category'], $_POST['expense_date'], $_POST['amount'], $image_path, $_POST['payment_mode'], $_POST['note'], $_POST['ref_no'], $_POST['vendor_id'], $user['id'], $_POST['expense_type'], $_POST['query_id'], $_POST['expense_subcategory'], $_POST['build'],$_POST['paid_status']);
            $stmt->execute();

            alert("Save Successfully", "success", "success");
            redirect("query?status=converted");
        } 
        catch (Exception $e) 
        {
            alert($e->getMessage(), "error", "error");
            redirect("query?status=converted");
        }
    }

    if (isset($_POST['ShareLeadAdd'])) 
    {
        if (empty($_POST['ShareUser'])) {
            alert("Please select users to share.", "warning", "warning");
            redirect("?status=" . $_GET['status'] . "");
        }

        $query_id = $_POST['query_id'];
        $stmt = $mysqli->prepare("SELECT user_id FROM query_mst WHERE id = ?");
        $stmt->bind_param("i", $query_id);
        $stmt->execute();
        $stmt->bind_result($existing_user_ids_string);
        $stmt->fetch();
        $stmt->close();

        $existing_user_ids = !empty($existing_user_ids_string) ? explode(',', $existing_user_ids_string) : [$_SESSION['id']];
        $new_user_ids = $_POST['ShareUser'];

        foreach ($new_user_ids as $uid) {
            if (!in_array($uid, $existing_user_ids)) {
                $existing_user_ids[] = $uid;
            }
        }

        $user_ids_string = implode(',', $existing_user_ids);

        try {
            $stmt = $mysqli->prepare("UPDATE query_mst SET user_id = ? WHERE id = ?");
            $stmt->bind_param("si", $user_ids_string, $query_id);
            $stmt->execute();
            alert("Lead shared successfully.", "success", "success");
            redirect("?status=" . $_GET['status'] . "");
        } catch (Exception $e) {
            alert("Error: " . $e->getMessage(), "error", "error");
            redirect("?status=" . $_GET['status'] . "");
        }
    }

    if (isset($_POST['leadAllocate'])) 
    {
        if (empty($_POST['lead_assign_user']) || empty($_POST['lead_id'])) 
        {
            alert("Please select a user and leads to transfer.", "warning", "warning");
            redirect("?status=" . $_GET['status']);
        }

        $Assign_user = $_POST['lead_assign_user'];
        $leadIds = $_POST['lead_id'];

        foreach ($leadIds as $leadId) 
        {
            $stmt = $mysqli->prepare("UPDATE `query_mst` SET `user_id` = ? WHERE `id` = ?");
            $stmt->bind_param("ii", $Assign_user, $leadId);
            $stmt->execute();
        }

        $_SESSION['alert'] = [
            'title' => 'Lead Transfer Successfully',
            'text' => count($leadIds) . ' leads successfully transferred.',
            'icon' => 'success'
        ];
        redirect("?status=" . $_GET['status']);
    }
?>

<style>
    #checkAll {
        appearance: none;
        -webkit-appearance: none;
        width: 20px;
        height: 20px;
        border: 2px solid #555;
        border-radius: 50%;
        outline: none;
        cursor: pointer;
        position: relative;
        transition: background 0.3s, border-color 0.3s;
    }


    #checkAll:checked {
        background-color: #4caf50;
        border-color: #4caf50;
    }


    #checkAll:checked::after {
        content: "✔";
        color: #fff;
        font-size: 24px;
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
    }

    .leadCheckbox {
        width: 15px;
        height: 15px;
        cursor: pointer;
        accent-color: #4caf50;
        margin: 5px;
    }

    .leadCheckbox:hover {
        box-shadow: 0 0 5px #4caf50;
    }

    .leadCheckbox:checked {
        accent-color: #4caf50;
    }
    
    .transfer-section {
        background: #f8f9fa;
        padding: 15px;
        border-radius: 8px;
        margin-bottom: 20px;
        border: 1px solid #e9ecef;
    }
    
    .selected-count-badge {
        background: #28a745;
        color: white;
        padding: 2px 8px;
        border-radius: 12px;
        font-size: 12px;
        margin-left: 5px;
    }
    
    .filter-section {
        background: #fff;
        padding: 15px;
        border-radius: 8px;
        margin-bottom: 20px;
        border: 1px solid #e9ecef;
    }

</style>

<div class="content-body">
    <div class="container-fluid">
        <!-- row -->
        <div class="row">
            <div class="col-12">
                 <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Query Management</h4>
                        <button type="button" class="btn btn-square btn-outline-danger" id="add-query">Add Query</button>
                    </div>
                    
                    <!-- User Filter Section -->
                    <div class="filter-section mx-3 mt-3">
                        <div class="row align-items-end">
                            <div class="col-md-3">
                                <label class="form-label fw-bold"><i class="fa fa-user"></i> Filter by User</label>
                                <select name="user_filter" id="user_filter" class="form-control select2">
                                    <option value="">-- All Users --</option>
                                    <?php
                                    if ($_SESSION['user'] == "admin") {
                                        $userQuery = "SELECT * FROM users WHERE status = '1' ORDER BY name";
                                        $userStmt = $mysqli->prepare($userQuery);
                                    } else if ($_SESSION['user'] == 'Team Manager') {
                                        $userQuery = "SELECT * FROM users WHERE status = '1' AND (id = ? OR parent_id = ?) ORDER BY name";
                                        $userStmt = $mysqli->prepare($userQuery);
                                        $userStmt->bind_param('ii', $_SESSION['id'], $_SESSION['id']);
                                    } else {
                                        $userQuery = "SELECT * FROM users WHERE status = '1' AND id = ? ORDER BY name";
                                        $userStmt = $mysqli->prepare($userQuery);
                                        $userStmt->bind_param('i', $_SESSION['id']);
                                    }
                                    $userStmt->execute();
                                    $userResult = $userStmt->get_result();
                                    while ($user = $userResult->fetch_assoc()) {
                                        $selected = ($userFilter == $user['id']) ? 'selected' : '';
                                        echo '<option value="' . $user['id'] . '" ' . $selected . '>' . htmlspecialchars($user['name']) . '</option>';
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button type="button" class="btn btn-primary btn-block" id="applyUserFilter">
                                    <i class="fa fa-search"></i> Apply Filter
                                </button>
                            </div>
                            <div class="col-md-2">
                                <a href="?status=<?= $currentStatus ?>" class="btn btn-secondary btn-block">
                                    <i class="fa fa-refresh"></i> Clear Filter
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Status Buttons -->
                    <div class="query-button m-4 flex-wrap">
                        <a href="?status=All<?= ($userFilter ? '&user_filter=' . $userFilter : '') ?>"
                            class="btn mt-md-0 mt-2 <?php if ($currentStatus == 'All') echo 'btn-success'; else echo 'btn-primary'; ?>">All</a>
                        <?php 
                            $stmt = $mysqli->prepare('SELECT * FROM status');
                            $stmt->execute();
                            $res = $stmt->get_result();
                            while ($row = $res->fetch_assoc()) 
                            { 
                        ?>
                            <a href="?status=<?php echo $row['name'] . ($userFilter ? '&user_filter=' . $userFilter : '') ?>"
                                class="btn mt-md-0 mt-2 <?php if ($currentStatus == $row['name']) echo 'btn-success'; else echo 'btn-primary'; ?>"><?= $row['name'] ?>
                            </a>
                        <?php 
                            }  
                        ?>
                    </div>
                    
                    <div class="card-body">
                        <form action="" method="Post" id="leadTransferForm">
                            <!-- Lead Transfer Section -->
                            <?php if($_SESSION['user'] == "admin" || $_SESSION['user'] == "Team Manager"): ?>
                            <div class="transfer-section">
                                <div class="row align-items-end">
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold">
                                            <i class="fa fa-exchange-alt"></i> Transfer Selected Leads To:
                                        </label>
                                        <select name="lead_assign_user" id="lead_assign_user" class="form-control select2" required>
                                            <option value="">-- Select User --</option>
                                            <?php
                                                if($_SESSION['user'] == "admin") {
                                                    $stmt_user = $mysqli->prepare("SELECT * FROM `users` WHERE `status` = '1' AND `role` != 'admin'");
                                                } else {
                                                    $stmt_user = $mysqli->prepare("SELECT * FROM `users` WHERE `status` = '1' AND (id = ? OR parent_id = ?) AND role != 'admin'");
                                                    $stmt_user->bind_param("ii", $_SESSION['id'], $_SESSION['id']);
                                                }
                                                $stmt_user->execute();
                                                $res_user = $stmt_user->get_result();
                                                while($row_user = $res_user->fetch_assoc()):
                                            ?>
                                            <option value="<?= $row_user['id'] ?>"><?= htmlspecialchars($row_user['name']) ?> (<?= ucfirst($row_user['role']) ?>)</option>
                                            <?php endwhile; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label fw-bold">Selected Leads:</label>
                                        <div>
                                            <span class="badge badge-primary" id="selectedLeadsCount">0</span>
                                            <span class="selected-count-badge">leads selected</span>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <button type="submit" name="leadAllocate" class="btn btn-success btn-block" id="transferLeadsBtn" disabled>
                                            <i class="fa fa-share"></i> Transfer Leads
                                        </button>
                                    </div>
                                    <div class="col-md-2">
                                        <button type="button" class="btn btn-secondary btn-block" id="clearSelectionBtn">
                                            <i class="fa fa-times"></i> Clear All
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>
                            
                            <div class="table-responsive">
                                <table id="example" class="display" style="min-width: 845px">
                                    <thead>
                                        <tr>
                                            <th>S.No
                                                <?php if($_SESSION['user'] == "admin" || $_SESSION['user'] == "Team Manager"): ?>
                                                <br>
                                                <input type="checkbox" id="checkAll">
                                                <label for="checkAll" class="ms-1 small">All</label>
                                                <?php endif; ?>
                                            </th>
                                            <th>User</th>
                                            <th>Customer Info</th>
                                            <th>Destination</th>
                                            <th>Travel Dates</th>
                                            <th>Persons</th>
                                            <th>Service</th>
                                            <th>Follow Up</th>
                                            <th>Remarks</th>
                                            <th>Created</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    <?php
                                    
                                        if ($_SESSION['user'] == "admin") 
                                        {
                                            if ($currentStatus == "All")
                                            {
                                                $query = "SELECT a.*, b.name as customer, b.number as mobile, b.email, c.name as user, d.name as destination_name
                                                    FROM query_mst a
                                                    LEFT JOIN destinations d ON a.destination = d.id
                                                    LEFT JOIN customers b ON a.customer_id = b.id
                                                    LEFT JOIN users c ON a.user_id = c.id
                                                    WHERE 1=1";
                                                $params = [];
                                                $types = "";
                                                
                                                if (!empty($userFilter)) {
                                                    $query .= " AND FIND_IN_SET(?, a.user_id)";
                                                    $params[] = $userFilter;
                                                    $types .= "i";
                                                }
                                                $query .= " ORDER BY a.pinned DESC, a.id DESC";
                                                
                                                $stmt = $mysqli->prepare($query);
                                                if (!empty($params)) {
                                                    $stmt->bind_param($types, ...$params);
                                                }
                                            } 
                                            else 
                                            {
                                                $query = "SELECT a.*, b.name as customer, b.number as mobile, b.email, c.name as user, d.name as destination_name
                                                    FROM query_mst a
                                                    LEFT JOIN customers b ON a.customer_id = b.id
                                                    LEFT JOIN destinations d ON a.destination = d.id
                                                    LEFT JOIN users c ON a.user_id = c.id
                                                    WHERE a.status=?";
                                                $params = [$currentStatus];
                                                $types = "s";
                                                
                                                if (!empty($userFilter)) {
                                                    $query .= " AND FIND_IN_SET(?, a.user_id)";
                                                    $params[] = $userFilter;
                                                    $types .= "i";
                                                }
                                                $query .= " ORDER BY a.pinned DESC, a.id DESC";
                                                
                                                $stmt = $mysqli->prepare($query);
                                                $stmt->bind_param($types, ...$params);
                                            }
                                        } 
                                        else if ($_SESSION['user'] == 'Team Manager') 
                                        {
                                            if ($currentStatus == "All") 
                                            {
                                                $query = "SELECT a.*, b.name as customer, b.number as mobile, b.email, c.name as user, d.name as destination_name
                                                    FROM query_mst a
                                                    LEFT JOIN customers b ON a.customer_id = b.id
                                                    LEFT JOIN destinations d ON a.destination = d.id
                                                    LEFT JOIN users c ON a.user_id = c.id
                                                    WHERE FIND_IN_SET(a.user_id, (
                                                        SELECT GROUP_CONCAT(id) FROM users WHERE id = ? OR parent_id = ?
                                                    ))";
                                                $params = [$_SESSION['id'], $_SESSION['id']];
                                                $types = "ii";
                                                
                                                if (!empty($userFilter)) {
                                                    $query .= " AND FIND_IN_SET(?, a.user_id)";
                                                    $params[] = $userFilter;
                                                    $types .= "i";
                                                }
                                                $query .= " ORDER BY a.pinned DESC, a.id DESC";
                                                
                                                $stmt = $mysqli->prepare($query);
                                                $stmt->bind_param($types, ...$params);
                                            } 
                                            else 
                                            {
                                                $query = "SELECT a.*, b.name as customer, b.number as mobile, b.email, c.name as user, d.name as destination_name
                                                    FROM query_mst a
                                                    LEFT JOIN customers b ON a.customer_id = b.id
                                                    LEFT JOIN destinations d ON a.destination = d.id
                                                    LEFT JOIN users c ON a.user_id = c.id
                                                    WHERE a.status=?
                                                    AND FIND_IN_SET(a.user_id, (
                                                        SELECT GROUP_CONCAT(id) FROM users WHERE id = ? OR parent_id = ?
                                                    ))";
                                                $params = [$currentStatus, $_SESSION['id'], $_SESSION['id']];
                                                $types = "sii";
                                                
                                                if (!empty($userFilter)) {
                                                    $query .= " AND FIND_IN_SET(?, a.user_id)";
                                                    $params[] = $userFilter;
                                                    $types .= "i";
                                                }
                                                $query .= " ORDER BY a.pinned DESC, a.id DESC";
                                                
                                                $stmt = $mysqli->prepare($query);
                                                $stmt->bind_param($types, ...$params);
                                            }
                                        } 
                                        else 
                                        {
                                            if ($currentStatus == "All") {
                                                $query = "SELECT a.*, b.name as customer, b.number as mobile, b.email, c.name as user, d.name as destination_name
                                                    FROM query_mst a
                                                    LEFT JOIN customers b ON a.customer_id = b.id
                                                    LEFT JOIN destinations d ON a.destination = d.id
                                                    LEFT JOIN users c ON a.user_id = c.id
                                                    WHERE FIND_IN_SET(?, a.user_id)";
                                                $params = [$_SESSION['id']];
                                                $types = "i";
                                                
                                                if (!empty($userFilter)) {
                                                    $query .= " AND FIND_IN_SET(?, a.user_id)";
                                                    $params[] = $userFilter;
                                                    $types .= "i";
                                                }
                                                $query .= " ORDER BY a.pinned DESC, a.id DESC";
                                                
                                                $stmt = $mysqli->prepare($query);
                                                $stmt->bind_param($types, ...$params);
                                            } 
                                            else 
                                            {
                                                $query = "SELECT a.*, b.name as customer, b.number as mobile, b.email, c.name as user, d.name as destination_name
                                                    FROM query_mst a
                                                    LEFT JOIN customers b ON a.customer_id = b.id
                                                    LEFT JOIN destinations d ON a.destination = d.id
                                                    LEFT JOIN users c ON a.user_id = c.id
                                                    WHERE a.status=?
                                                    AND FIND_IN_SET(?, a.user_id)";
                                                $params = [$currentStatus, $_SESSION['id']];
                                                $types = "si";
                                                
                                                if (!empty($userFilter)) {
                                                    $query .= " AND FIND_IN_SET(?, a.user_id)";
                                                    $params[] = $userFilter;
                                                    $types .= "i";
                                                }
                                                $query .= " ORDER BY a.pinned DESC, a.id DESC";
                                                
                                                $stmt = $mysqli->prepare($query);
                                                $stmt->bind_param($types, ...$params);
                                            }
                                        }

                                        if(isset($stmt)) {
                                            $stmt->execute();
                                            $res = $stmt->get_result();
                                            $sno = 1;
                                            while ($row = $res->fetch_assoc())
                                            {
                                    ?>
                                        <tr>
                                            <td>
                                                <?= $sno++; ?>
                                                <?php if($_SESSION['user'] == "admin" || $_SESSION['user'] == "Team Manager"): ?>
                                                <br>
                                                <input type="checkbox" class="leadCheckbox" value="<?= $row['id'] ?>" name="lead_id[]">
                                                <?php endif; ?>
                                                <?php if($row['pinned']): ?>
                                                <br>
                                                <span class="pin-query text-warning" style="cursor:pointer;" data-id="<?= $row['id'] ?>" data-value="0">📌</span>
                                                <?php endif; ?>
                                             </div>
                                            <td>
                                                <div class="d-flex align-items-center justify-content-between">
                                                    <span><?= htmlspecialchars($row['user'] ?? 'N/A'); ?></span>
                                                    <div class="dropdown">
                                                        <a href="#" role="button" id="dropdownMenuButton<?= $row['id'] ?>" data-bs-toggle="dropdown">
                                                            <i class="bi bi-three-dots-vertical" style="font-size: 18px;"></i>
                                                        </a>
                                                        <ul class="dropdown-menu">
                                                            <li>
                                                                <?php if($row['pinned'] == 0): ?>
                                                                <button type="button" class="dropdown-item pin-query" data-id="<?= $row['id'] ?>" data-value="1">📌 Pin</button>
                                                                <?php else: ?>
                                                                <button type="button" class="dropdown-item pin-query" data-id="<?= $row['id'] ?>" data-value="0">❌ Unpin</button>
                                                                <?php endif; ?>
                                                            </li>
                                                            <li><hr class="dropdown-divider"></li>
                                                            <li>
                                                                <button type="button" class="dropdown-item edit" data-id="<?= $row['id'] ?>">✏️ Edit Query</button>
                                                            </li>
                                                            <li>
                                                                <button type="button" class="dropdown-item update_status" data-bs-toggle="modal" data-bs-target="#status_change" data-id="<?= $row['id'] ?>">🔄 Update Status</button>
                                                            </li>
                                                            <li>
                                                                <button type="button" class="dropdown-item ShowRemarks" data-id="<?= $row['id'] ?>">💬 Show Remarks</button>
                                                            </li>
                                                            <li><hr class="dropdown-divider"></li>
                                                            <li>
                                                                <a href="add_quote.php?id=<?= $row['customer_id'] ?>&q_id=<?= $row['id']; ?>" class="dropdown-item">📄 Create Quote</a>
                                                            </li>
                                                            <li>
                                                                <a href="created_quote.php?q_id=<?= $row['id'] ?>&id=<?= $row['customer_id'] ?>" class="dropdown-item">📋 Created Quote</a>
                                                            </li>
                                                            <?php if($row['status'] == "Converted"): ?>
                                                            <li><hr class="dropdown-divider"></li>
                                                            <li>
                                                                <button type="button" class="dropdown-item addSaleAmount" data-sale_amount="<?= $row['sale_amount']; ?>" data-id="<?= $row['id']; ?>" data-bs-toggle="modal" data-bs-target="#AddSale">💰 Sale Amount</button>
                                                            </li>
                                                            <li>
                                                                <button type="button" class="dropdown-item add_expenses" data-id="<?= $row['id'] ?>" data-bs-toggle="modal" data-bs-target="#AddPayment" data-customer_id="<?= $row['customer_id'] ?>">📊 Expenses</button>
                                                            </li>
                                                            <?php endif; ?>
                                                            <?php if($currentStatus == "Converted"): ?>
                                                            <li>
                                                                <button type="button" class="dropdown-item payment_button" data-bs-toggle="modal" data-bs-target="#payment_collect" data-id="<?= $row['id'] ?>">💳 Payment Collect</button>
                                                            </li>
                                                            <?php endif; ?>
                                                            <li><hr class="dropdown-divider"></li>
                                                            <li>
                                                                <button type="button" class="dropdown-item shareLeadButton" data-bs-toggle="modal" data-id="<?= $row['id']; ?>" data-bs-target="#shareLead">👥 Share Lead</button>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                </div>
                                             </div>
                                            <td>
                                                <strong>Name:</strong> <?= htmlspecialchars($row['customer'] ?? 'N/A'); ?><br>
                                                <strong>Mobile:</strong> <?= htmlspecialchars($row['mobile'] ?? 'N/A'); ?><br>
                                                <strong>Email:</strong> <?= htmlspecialchars($row['email'] ?? 'N/A'); ?>
                                             </div>
                                            <td><?= htmlspecialchars($row['destination_name'] ?? 'N/A'); ?> </div>
                                            <td>
                                                <?= htmlspecialchars($row['travel_month'] ?? 'N/A'); ?><br>
                                                <strong>From:</strong> <?= $row['from_date'] ?? 'N/A'; ?><br>
                                                <strong>To:</strong> <?= $row['to_date'] ?? 'N/A'; ?>
                                             </div>
                                            <td>
                                                <strong>Adult:</strong> <?= $row['adult'] ?? 0; ?><br>
                                                <strong>Child:</strong> <?= $row['child'] ?? 0; ?><br>
                                                <strong>Infant:</strong> <?= $row['infant'] ?? 0; ?>
                                             </div>
                                            <td><?= htmlspecialchars($row['service'] ?? 'N/A'); ?> </div>
                                            <td>
                                                <?php if(!empty($row['call_time']) && $row['call_time'] != '00:00:00'): ?>
                                                <strong>Time:</strong> <?= $row['call_time']; ?><br>
                                                <?php endif; ?>
                                                <?php if(!empty($row['call_date']) && $row['call_date'] != '0000-00-00'): ?>
                                                <strong>Date:</strong> <?= $row['call_date']; ?>
                                                <?php endif; ?>
                                             </div>
                                            <td>
                                                <?php 
                                                    $remarks = isset($row['remarks']) && !empty($row['remarks']) ? $row['remarks'] : '----------'; 
                                                    if(strlen($remarks) > 30) {
                                                        $short_remark = substr($remarks, 0, 30) . '...';
                                                        echo '<span data-bs-toggle="tooltip" title="'.htmlspecialchars($remarks).'">'.htmlspecialchars($short_remark).'</span>';
                                                    } else {
                                                        echo htmlspecialchars($remarks);
                                                    }
                                                ?>
                                             </div>
                                            <td>
                                                <?php 
                                                    if (!empty($row['created_at'])) {
                                                        echo date("d M Y, h:i A", strtotime($row['created_at']));
                                                    } else {
                                                        echo 'N/A';
                                                    }
                                                ?>
                                             </div>
                                         </div>
                                        <?php
                                            }
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<div class="modal fade bd-example-modal-lg" id="queryModal" tabindex="-1" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <form class="needs-validation" novalidate method="POST">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel"> Add Query</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal">
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <input id="id" name="id" type="hidden">
                        <div class="col-md-12 existing-customer-div">
                            <label for="name" class="form-label">Select Client</label>
                            <select class="form-control" id="customer_id" name="customer_id" value="" required>
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
                        <div class="col-md-6">
                            <label for="name" class="form-label">Mobile</label>
                            <input type="number" class="form-control" id="mobile" name="mobile" value="" required>
                        </div>
                        <div class="col-md-6">
                            <label for="name" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" value="">
                        </div>
                   
                        <div class="col-md-6">
                            <label for="" class="form-label">Destination</label>
                              <select class="form-control" id="destination" name="destination" value="" required>
                                <option value="">Select Destination</option>
                                <?php
                                    $stmt = $mysqli->prepare("SELECT * from  destinations  order by id desc");
                                    $stmt->execute();
                                    $res = $stmt->get_result();

                                    while ($row = $res->fetch_assoc()) 
                                    {
                                        echo '<option value="' . $row['id'] . '">' . $row['name'] . '</option>';
                                    }
                                ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="name" class="form-label">Travel Month</label>
                            <select class="form-control" id="travel_month" name="travel_month">
                                <option value="">Select Month</option>
                                <option value="January">January</option>
                                <option value="February">February</option>
                                <option value="March">March</option>
                                <option value="April">April</option>
                                <option value="May">May</option>
                                <option value="June">June</option>
                                <option value="July">July</option>
                                <option value="August">August</option>
                                <option value="September">September</option>
                                <option value="October">October</option>
                                <option value="November">November</option>
                                <option value="December">December</option>
                            </select>

                        </div>
                        <div class="col-md-6">
                            <label for="name" class="form-label">From Date</label>
                            <input type="date" class="form-control" id="from_date" name="from_date" value="">
                        </div>
                        <div class="col-md-6">
                            <label for="name" class="form-label">To Date</label>
                            <input type="date" class="form-control" id="to_date" name="to_date" value="">
                        </div>
                        <div class="col-md-6">
                            <label for="name" class="form-label">Adult</label>
                            <input type="number" class="form-control" id="adult" name="adult" value="">
                        </div>
                        <div class="col-md-6">
                            <label for="name" class="form-label">Child</label>
                            <input type="number" class="form-control" id="child" name="child" value="">
                        </div>
                        <div class="col-md-6">
                            <label for="name" class="form-label">Infant</label>
                            <input type="number" class="form-control" id="infant" name="infant" value="">
                        </div>
                        <div class="col-md-6">
                            <label for="name" class="form-label">Lead Source</label>
                            <select class="form-control" id="source" name="source" value="">
                                <option value="">Select Source</option>
                                <?php
                                    $stmt = $mysqli->prepare("SELECT * from  source  ");
                                    $stmt->execute();
                                    $res = $stmt->get_result();

                                    while ($row = $res->fetch_assoc()) 
                                    {
                                        echo '<option value="' . $row['name'] . '">' . $row['name'] . '</option>';
                                    }
                                ?>
                            </select>
                        </div>


                        <div class="col-md-6">
                            <label for="name" class="form-label">Priority</label>
                            <select class="form-control" id="priority" name="priority" value="">
                                <option value="">Select Priority</option>
                                <?php
                                    $stmt = $mysqli->prepare("SELECT * from  priority  ");
                                    $stmt->execute();
                                    $res = $stmt->get_result();

                                    while ($row = $res->fetch_assoc()) 
                                    {
                                        echo '<option value="' . $row['name'] . '">' . $row['name'] . '</option>';
                                    }
                                ?>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label for="name" class="form-label">Service</label>
                            <select class="form-control" id="service" name="service" value="">
                                <option value="">Select Service</option>
                                <?php
                                    $stmt = $mysqli->prepare("SELECT * from  service  ");
                                    $stmt->execute();
                                    $res = $stmt->get_result();

                                    while ($row = $res->fetch_assoc()) 
                                    {
                                        echo '<option value="' . $row['name'] . '">' . $row['name'] . '</option>';
                                    }
                                ?>
                            </select>
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


<div class="modal fade status_change" id="status_change" tabindex="-1" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <form class="needs-validation" novalidate method="POST">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Update Query Status</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal">
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <input id="hidden_update_id" name="id" type="hidden">

                        <div class="col-md-6 status">
                            <label for="name" class="form-label">Status</label>
                            <select class="form-control" id="status" name="status" value="" required>
                                <option value="">Select Status</option>
                                <option value="New Query">New Query</option>
                                <option value="Follow Up">Follow Up</option>
                                <option value="Converted">Converted</option>
                                <option value="Lost">Lost</option>
                                <option value="Booked">Booked</option>
                            </select>
                        </div>
                        <div class="col-md-6 follow_up">
                            <label for="">Call Time</label>
                            <input type="time" name="call_time" id="call_time" class="form-control">
                        </div>
                        <div class="col-md-6 follow_up">
                            <label for="">Call Date</label>
                            <input type="date" name="call_date" id="call_date" class="form-control">
                        </div>
                        <div class="col-md-6 sale_amount">
                            <label for="">Sale Amount</label>
                            <input type="number" name="sale_amount" id="sale_amount" class="form-control">
                        </div>
                        <div class="col-md-12">
                            <label for="name" class="form-label">Remarks</label>
                            <textarea class="form-control" id="remarks" name="remarks" value="" required></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger light" data-bs-dismiss="modal">Close</button>
                    <button type="submit" name="btnSaveStatus" id="btnSaveStatus" class="btn btn-primary btnname">Save
                        changes</button>
                </div>
            </div>
        </div>
    </form>
</div>


<div class="modal fade payment_collect" id="payment_collect" tabindex="-1" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <form class="needs-validation" novalidate method="POST">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel"> ADD Query Payment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal">
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 ">
                            <label for="">Amount</label>
                            <input type="number" name="payment_amount" id="" class="form-control" required>
                        </div>
                        <div class="col-md-6 ">
                            <label for="">Payment Mode</label>
                            <select name="payment_mode" id="" class="form-control" required>
                                <option value="" disabled selected>Select Payment Type</option>
                                <option value="Cash">Cash</option>
                                <option value="UPI">UPI</option>
                                <option value="Card">Card</option>
                                <option value="Cheque">Cheque</option>
                            </select>
                        </div>
                        <div class="col-md-6 ">
                            <label for="">Ref. No.</label>
                            <input type="text" name="payment_ref_no" id="" class="form-control">
                        </div>
                        <div class="col-md-6 ">
                            <label for="">Date</label>
                            <input type="date" name="payment_date" id="" class="form-control" required>
                        </div>
                        <div class="col-md-6 ">
                            <label for="">Remark</label>
                            <input type="text" name="payment_remark" id="" class="form-control" required>
                        </div>


                    </div>
                </div>
                <div class="modal-footer">
                    <input type="hidden" name="query_id" id="hidden_query_id">
                    <button type="button" class="btn btn-danger light" data-bs-dismiss="modal">Close</button>
                    <button type="submit" name="btnSavePayment" id="btnSaveStatus" class="btn btn-primary btnname">Save
                        changes</button>
                </div>
            </div>
        </div>
    </form>
</div>

<form method="POST" enctype="multipart/form-data">
    <div class="modal fade" id="UploadModal">
        <div class="modal-dialog " role="document">

            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitleId">
                        Upload File
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="file_id">
                    <label for="">Upload Multiple File</label>
                    <input type="file" name="files[]" class="form-control" multiple>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        Close
                    </button>
                    <button type="submit" name="btnUpload" class="btn btn-primary">Save</button>
                </div>
            </div>

        </div>
    </div>
</form>

<div class="modal fade" id="ShowRemarks">
    <div class="modal-dialog modal-xl" role="document">

        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitleId">
                    Remarks
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <table class="table">
                    <thead>
                        <tr>
                            <th>S.No</th>
                            <th>Remarks</th>
                            <th>Call time</th>
                            <th>Call date</th>
                            <th>Status</th>
                            <th>User</th>
                            <th>Created at</th>
                        </tr>
                    </thead>
                    <tbody id="remarksList">

                    </tbody>

                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-bs-dismiss="modal">
                    Close
                </button>

            </div>
        </div>

    </div>
</div>

<div class="modal fade" id="ShowFile">
    <div class="modal-dialog modal-xl" role="document">

        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitleId">
                    File
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <table class="table">
                    <thead>
                        <tr>
                            <th>S.No</th>
                            <th>File</th>
                            <th>Approved</th>

                            <th>Created at</th>
                        </tr>
                    </thead>
                    <tbody id="fileList">

                    </tbody>

                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-bs-dismiss="modal">
                    Close
                </button>

            </div>
        </div>

    </div>
</div>

<?php include "Layouts/Footer.php";  ?>

<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<form action="" method="post" class="needs-validation" id="customerForm">
    <div class="modal fade" id="exampleModalToggle" aria-hidden="true" aria-labelledby="exampleModalToggleLabel"
        tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalToggleLabel">Add Customer</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <!-- <input id="id" name="id" type="hidden"> -->
                        <div class="col-md-3">
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
                        <div class="col-md-5">
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
                    <button type="button" class="btn btn-primary" id="nextBtn">Next: Add Query</button>

                </div>
            </div>
        </div>
    </div>

    <!-- Second Modal: Add Query -->
    <div class="modal fade" id="exampleModalToggle2" aria-hidden="true" aria-labelledby="exampleModalToggleLabel2"
        tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalToggleLabel2">Query Modal</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <!-- <input id="id" name="id" type="hidden"> -->
                        <div class="col-md-6">
                            <label for="name" class="form-label">Destination</label>
                            <select class="form-control" name="destination" value="" required>
                                <option value="">Select Destination</option>
                                <?php
                                    $stmt = $mysqli->prepare("SELECT * from  destinations  order by id desc");
                                    $stmt->execute();
                                    $res = $stmt->get_result();

                                    while ($row = $res->fetch_assoc()) 
                                    {
                                        echo '<option value="' . $row['id'] . '">' . $row['name'] . '</option>';
                                    }
                                ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="name" class="form-label">Travel Month</label>
                            <select class="form-control" id="travel_month" name="travel_month">
                                <option value="">Select Month</option>
                                <option value="January">January</option>
                                <option value="February">February</option>
                                <option value="March">March</option>
                                <option value="April">April</option>
                                <option value="May">May</option>
                                <option value="June">June</option>
                                <option value="July">July</option>
                                <option value="August">August</option>
                                <option value="September">September</option>
                                <option value="October">October</option>
                                <option value="November">November</option>
                                <option value="December">December</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="name" class="form-label">From Date</label>
                            <input type="date" class="form-control" id="from_date" name="from_date" value="">
                        </div>
                        <div class="col-md-6">
                            <label for="name" class="form-label">To Date</label>
                            <input type="date" class="form-control" id="to_date" name="to_date" value="">
                        </div>
                        <div class="col-md-6">
                            <label for="name" class="form-label">Adult</label>
                            <input type="number" class="form-control" id="adult" name="adult" value="">
                        </div>
                        <div class="col-md-6">
                            <label for="name" class="form-label">Child</label>
                            <input type="number" class="form-control" id="child" name="child" value="">
                        </div>
                        <div class="col-md-6">
                            <label for="name" class="form-label">Infant</label>
                            <input type="number" class="form-control" id="infant" name="infant" value="">
                        </div>
                        <div class="col-md-6">
                            <label for="name" class="form-label">Lead Source</label>
                            <select class="form-control" id="source" name="source" value="">
                                <option value="">Select Source</option>
                                <?php
                                        $stmt = $mysqli->prepare("SELECT * from  `source`  ");
                                        $stmt->execute();
                                        $res = $stmt->get_result();

                                        while ($row = $res->fetch_assoc()) 
                                        {
                                            echo '<option value="' . $row['name'] . '">' . $row['name'] . '</option>';
                                        }
                                    ?>
                            </select>
                        </div>


                        <div class="col-md-6">
                            <label for="name" class="form-label">Priority</label>
                            <select class="form-control" id="priority" name="priority" value="">
                                <option value="">Select Priority</option>
                                <?php
                                    $stmt = $mysqli->prepare("SELECT * from  `priority`  ");
                                    $stmt->execute();
                                    $res = $stmt->get_result();

                                    while ($row = $res->fetch_assoc()) 
                                    {
                                        echo '<option value="' . $row['name'] . '">' . $row['name'] . '</option>';
                                    }
                                ?>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label for="name" class="form-label">Service</label>
                            <select class="form-control" id="service" name="service" value="">
                                <option value="">Select Service</option>
                                <?php
                                        $stmt = $mysqli->prepare("SELECT * from  `service`  ");
                                        $stmt->execute();
                                        $res = $stmt->get_result();

                                        while ($row = $res->fetch_assoc()) 
                                        {
                                            echo '<option value="' . $row['name'] . '">' . $row['name'] . '</option>';
                                        }
                                    ?>
                            </select>
                        </div>


                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-primary" data-bs-target="#exampleModalToggle" data-bs-toggle="modal"
                        data-bs-dismiss="modal">Back</button>
                    <button type="submit" name="AddCustomerQuery"
                        class="btn btn-square btn-outline-success">Submit</button>
                </div>
            </div>
        </div>
    </div>
</form>


<div class="modal fade bd-example-modal-lg-expense" id="expense_add" tabindex="-1" role="dialog" aria-hidden="true">
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
                    <input type="hidden" id="invoice_id" name="query_id">
                    <input type="hidden" id="ids" name="ids">
                    <button type="button" class="btn btn-danger light" data-bs-dismiss="modal">Close</button>
                    <button type="submit" name="btnSave" id="btnadd" class="btn btn-primary btnname">Save
                        changes</button>
                </div>
            </div>
        </div>
    </form>
</div>



<!-- add payment modal  -->
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
                <button type="button" class="btn btn-primary" id="paymentCollect">
                    Collect Payment
                </button>

                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>

            </div>

        </div>
    </div>
</div>


<!-- Expense Details Modal -->
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
                <button type="button" class="btn btn-primary" id="add_expense_button">
                    Add Expense
                </button>

                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>

        </div>
    </div>
</div>


<!-- Modal for share lead -->
<div class="modal fade" id="shareLead" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Share Lead</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="" method="POST">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-12">
                            <select name="ShareUser[]" class="form-control" id="multiple_user" multiple>
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
                    </div>

                </div>

                <div class="modal-footer">
                    <input type="hidden" name="query_id" id="share_lead_hidden">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" name="ShareLeadAdd" class="btn btn-primary">Save changes</button>
                </div>
            </form>
            <div class="modal-body">
                <div id="sharedUsersList"></div>
            </div>
        </div>
    </div>
</div>



<script>
$(document).ready(function() {
    $('#add-query').on('click', function() {
        Swal.fire({
            title: 'Choose an Option',
            text: 'Is this for an Existing Customer or a New Customer?',
            icon: 'question',
            showCancelButton: true,
            showDenyButton: true,
            confirmButtonText: 'Existing Customer',
            denyButtonText: 'New Customer',
            cancelButtonText: 'Cancel',
            allowOutsideClick: false
        }).then((result) => {
            if (result.isConfirmed) {
                $('#queryModal').modal('show');
                $('.new-customer-div').addClass('d-none');
                $('.existing-customer-div').removeClass('d-none');
            } else if (result.isDenied) {
                $('.new-customer-div').removeClass('d-none');
                $('.existing-customer-div').addClass('d-none');
                $('#exampleModalToggle').modal('show');
            }
        }).catch((dismiss) => {
            if (dismiss === Swal.DismissReason.cancel) {
                // Do nothing on cancel
            }
        });
    });
});

$(document).on("click", ".edit", function() {
    $("#id").val($(this).data("id"))
    $(".btnname").html("Update Query");
    $.ajax({
        method: "POST",
        url: "ajax/get-query-details.php",
        data: {
            id: $(this).data("id")
        },
        success: function(data) {
            console.log(data);
            data = JSON.parse(data);
            $.each(data, function(i, o) {
                $('input[name=' + i + ']').val(o);
                $('select[name=' + i + ']').val(o);
                $('textarea[name=' + i + ']').val(o);
                if (o == "Follow Up") {
                    $(".follow_up").show(500)
                }
                if (o == "Completed") {
                    $(".status").hide()
                }
            })
            $("#remarks").val("")
            $("#exampleModalLabel").text('Update Query');
            $('#queryModal').modal('show');
        }
    });
});

$(document).ready(function() {
    $(".shareLeadButton").click(function() {

        $("#share_lead_hidden").val($(this).data('id'));
        queryId = $(this).data('id');


        $.ajax({
            url: 'ajax/getShareCount.php',
            type: 'POST',
            data: {
                query_id: queryId
            },
            success: function(response) {
                $("#sharedUsersList").html(response);
            },
            error: function() {
                alert("Error fetching shared count.");
            }
        });

    });
});




$('#queryModal').on('shown.bs.modal', function() {
    $('#customer_id').select2({
        dropdownParent: $('#queryModal')
    });
});
$('#shareLead').on('shown.bs.modal', function() {
    $('#multiple_user').select2({
        dropdownParent: $('#shareLead')
    });
});

$(".follow_up").hide()
$(".sale_amount").hide()

$("#expense_type").on("change", function() {
    const type = $(this).val();
    console.log(type);
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

$("#expense_type").on("change", function() {
    if (!$(this).val()) {
        $(".category").show(500);
    } else {
        $(".category").hide(500);
        $("#expense_category").val("");
        $("#expense_subcategory").val("");
    }
});

$(".status").show()







$(document).on("click", "#add_notice", function() {
    let id = $("#id").val();
    if (id != false) {
        $("#id").val("")
        $("#name").val("")

        $("#btnupdate").attr("name", "btnadd");
        $("#btnupdate").attr("id", "btnadd");
        $(".btnname").html("Add Query");
    }
    $("#exampleModal").modal("show");
});

$("#status").on("change", function() {
    if ($(this).val() == "Follow Up") {
        $(".follow_up").show(500)
    } else {
        $(".follow_up").hide(500)
    }
    if ($(this).val() == "Converted") {
        $(".sale_amount").show(300)
    } else {
        $(".sale_amount").hide(300)
    }
});

$(document).on("click", ".UploadFile", function() {
    $("#file_id").val($(this).data("id"))
    $("#UploadModal").modal("show")
});

$(document).on("click", ".ShowRemarks", function() {
    $.ajax({
        method: "POST",
        url: "ajax/get-remarks.php",
        data: {
            id: $(this).data("id")
        },
        success: function(data) {
            data = JSON.parse(data);
            var row = "";
            var sno = 1;
            data.forEach(element => {
                row += `<tr>
                     <td>${sno++}</td>
                     <td>${element.remarks}</td>
                     <td>${element.call_time}</td>
                     <td>${element.call_date}</td>
                     <td>${element.status}</td>
                     <td>${element.user}</td>
                     <td>${element.created_at}</td>
                       </tr>`;
            });
            $("#remarksList").html(row);

            $("#ShowRemarks").modal("show")
        }
    });

});

$(document).on("click", ".ShowImages", function() {
    $.ajax({
        method: "POST",
        url: "ajax/get-files.php",
        data: {
            id: $(this).data("id")
        },
        success: function(data) {
            data = JSON.parse(data);
            var row = "";
            var sno = 1;
            data.forEach(element => {
                var approved = "";
                if (element.approved == 1) {
                    approved = "Checked";
                }
                row += `<tr>
                     <td>${sno++}</td>
                     <td><a href="${element.image}" target="_blank">File </a></td>
                  
                     <td><input type="checkbox" class="approved" value="${element.id}" ${approved}></td>
                     <td>${element.created_at}</td>
                       </tr>`;
            });
            $("#fileList").html(row);

            $("#ShowFile").modal("show")
        }
    });


});

$("#customer_id").on("change", function() {
    $("#mobile").val($(this).find(":selected").data("number"))
    $("#email").val($(this).find(":selected").data("email"))
})
$(document).on("click", ".approved", function() {
    var approved = 0;
    var id = $(this).val();
    if ($(this).prop("checked")) {
        var approved = 1;

    } else {

        var approved = 0;
    }

    $.ajax({
        method: "POST",
        url: "ajax/approved-files.php",
        data: {
            id: id,
            approved: approved
        },
        success: function(data) {
            toastr.success("Save successfully");
        }
    });
})


document.getElementById('nextBtn').addEventListener('click', function() {
    const modal1Fields = document.querySelector('#exampleModalToggle .modal-body');
    const inputs = modal1Fields.querySelectorAll('input, select, textarea');
    let isValid = true;

    inputs.forEach(input => {
        if (!input.checkValidity()) {
            input.classList.add('is-invalid');
            isValid = false;
        } else {
            input.classList.remove('is-invalid');
        }
    });

    if (isValid) {

        const modal1 = bootstrap.Modal.getInstance(document.getElementById('exampleModalToggle'));
        modal1.hide();
        new bootstrap.Modal(document.getElementById('exampleModalToggle2')).show();
    }
});

$(document).ready(function() {

    // $("#add_expenses").on("click", function() {
    //     $("#modalId").modal("show");
    //     $("#invoice_id").val($(this).data("id"));
    //     $("#ids").val($(this).data("customer_id"));
    // });


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


$(document).on('click', '.pin-query', function() {
    var queryId = $(this).data('id');
    var newValue = $(this).data('value');
    $.ajax({
        url: 'ajax/update_pin_status.php',
        type: 'POST',
        data: {
            id: queryId,
            value: newValue
        },
        success: function(response) {
            location.reload(); // reload table to reflect changes
        }
    });
});

$(document).on("click", ".update_status", function() 
{
    $("#hidden_update_id").val($(this).data('id'));
})
$(document).on("click", "#payment_button", function() {
    $("#hidden_query_id").val($(this).data('id'));
})
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

$(document).on("click", "#paymentCollect", function() {
    $("#AddSale").modal('hide');
    $("#payment_collect").modal('show');
});


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

$(document).on("click", "#add_expenses", function() {
    $("#invoice_id").val($(this).data('id'));
    $("#ids").val($(this).data('customer_id'));
})

$(document).on('click', "#add_expense_button", function() {
    $('#AddPayment').modal('hide');
    $('#expense_add').modal('show');
})

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

function deleteUserFromQuery(queryId, userId) {
    if (confirm("Are you sure you want to remove this user from the lead?")) {
        $.ajax({
            url: 'ajax/delete_shared_user.php',
            type: 'POST',
            data: {
                query_id: queryId,
                user_id: userId
            },
            success: function(response) {
                alert(response);
                window.location.reload();
            },
            error: function() {
                alert("An error occurred while removing the user.");
            }
        });
    }
}

var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
    return new bootstrap.Tooltip(tooltipTriggerEl)
})

$(document).on("click", "#checkAll", function() {
    $(".leadCheckbox").prop('checked', $(this).prop('checked'));
});
$('#applyUserFilter').on('click', function() 
{
    var userFilter = $('#user_filter').val();
    var currentStatus = '<?= $currentStatus ?>';
    if (userFilter) 
    {
        window.location.href = '?status=' + currentStatus + '&user_filter=' + userFilter;
    } 
    else 
    {
        window.location.href = '?status=' + currentStatus;
    }
});

$(document).on('change', '.leadCheckbox', function() 
{
    updateSelectedCount();
});

function updateSelectedCount() 
{
    var selectedCount = $('.leadCheckbox:checked').length;
    $('#selectedLeadsCount').text(selectedCount);
    $('#transferLeadsBtn').prop('disabled', selectedCount === 0);
}

$('#clearSelectionBtn').on('click', function() 
{
    $('.leadCheckbox').prop('checked', false);
    // $('#checkAll').prop('checked', false);
    updateSelectedCount();
});

// $('#leadTransferForm').on('submit', function(e) 
// {
//     var selectedCount = $('.leadCheckbox:checked').length;
//     var selectedUser = $('#lead_assign_user').val();

//     if (selectedCount === 0) 
//     {
//         e.preventDefault();
//         Swal.fire('No Leads Selected', 'Please select at least one lead to transfer.', 'warning');
//         return false;
//     }

//     if (!selectedUser) 
//     {
//         e.preventDefault();
//         Swal.fire('No User Selected', 'Please select a user to transfer the leads to.', 'warning');
//         return false;
//     }

//     Swal.fire({
//         title: 'Confirm Transfer',
//         text: `Transfer ${selectedCount} lead(s) to the selected user?`,
//         icon: 'question',
//         showCancelButton: true,
//         confirmButtonText: 'Yes, Transfer!'
//     }).then((result) => {
//         if (result.isConfirmed) 
//         {
//             $('#leadTransferForm').off('submit').submit();
//         }
//     });

//     return false;
//     updateSelectedCount();
// });
</script>