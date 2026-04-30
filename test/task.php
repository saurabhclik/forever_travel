<?php
    include "Layouts/Header.php";
    include "Layouts/Sidebar.php";

if(isset($_POST['btnAssign'])){
    $assign_user = $_POST['assign_user'];
    $task_ids = $_POST['task_id'];
    $assigned_by = $_SESSION['id']; 



    if(!empty($task_ids) && !empty($assign_user)){
        for($i = 0; $i < count($task_ids); $i++){
            $task_id = $task_ids[$i];


            $stmt = $mysqli->prepare("SELECT user_id FROM task WHERE id = ?");
            $stmt->bind_param("i", $task_id);
            $stmt->execute();
            $stmt->bind_result($from_user);
            $stmt->fetch();
            $stmt->close();


            $stmt = $mysqli->prepare("UPDATE task SET user_id = ? WHERE id = ?");
            $stmt->bind_param("ii", $assign_user, $task_id);
            $stmt->execute();
            $stmt->close();

            $stmt = $mysqli->prepare("INSERT INTO task_history (task_id, action, from_user, to_user, performed_by) VALUES (?, 'assigned', ?, ?, ?)");
            $stmt->bind_param("iiii", $task_id, $from_user, $assign_user, $assigned_by);
            $stmt->execute();
            $stmt->close();
        }

        $_SESSION['alert'] = [
            'title' => 'Task Assign',
            'text'  => 'Task Assign Successfully.',
            'icon'  => 'success'
        ];
    } else {
        $_SESSION['alert'] = [
            'title' => 'Task Not Assign',
            'text'  => 'Task Assign Failed.',
            'icon'  => 'error'
        ];
    }
}



// TASK CREATE / UPDATE
if (isset($_POST['BtnSubmit'])) {
    try {
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

        if (
            !empty($_POST['title']) &&
            !empty($_POST['due_date']) &&
            !empty($_POST['priority']) &&
            !empty($_POST['status'])
        ) {
            $title        = $_POST['title'];
            $due_date     = $_POST['due_date'];
            $priority     = $_POST['priority'];
            $status       = $_POST['status'];
            $descriptions = $_POST['descriptions'] ?? '';
            $remark       = $_POST['remark'] ?? '';
            $user_ids      = $_POST['user_ids'] ?? null;
            $repeat_interval = $_POST['repeat_interval'];
            $repeat_count = $_POST['repeat_count'];
            $msg = "Hey User, I have assigned a new task to you.( $title ) - $descriptions. Please check and update me once done.";



            $file_name = '';
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $target_dir = "images/task/";
                if (!is_dir($target_dir)) {
                    mkdir($target_dir, 0755, true);
                }
                $file_name = time() . '_' . basename($_FILES["image"]["name"]);
                $target_file = $target_dir . $file_name;
                move_uploaded_file($_FILES["image"]["tmp_name"], $target_file);
            }

            if (isset($_POST['id']) && !empty($_POST['id'])) 
            {
                // UPDATE task

                // echo "<pre>";
                // print_r($_POST); exit;

                $stmt = $mysqli->prepare("UPDATE task SET title = ?, due_date = ?, priority = ?, status = ?, description = ?, remarks = ?, file = ? , user_id = ? WHERE id = ?");
                $stmt->bind_param("sssssssii", $title, $due_date, $priority, $status, $descriptions, $remark, $file_name, $user_id , $_POST['id']);
                $stmt->execute();
                $stmt->close();



             
               if ($_POST['status'] == "Complete") {
                    $stmt = $mysqli->prepare("SELECT * FROM notifications WHERE task_id = ?");
                    $stmt->bind_param("i", $_POST['id']);
                    $stmt->execute();
                    $res = $stmt->get_result();
                    $row = $res->fetch_object();
                    $stmt->close();

                    if ($row) {
                        $msg = "Hey User, your task ( $title ) has been marked as complete.";
                        $stmt = $mysqli->prepare("INSERT INTO notifications (msg, from_user_id, to_user_id, task_id) VALUES (?, ?, ?, ?)");
                        $stmt->bind_param("siii", $msg, $_SESSION['id'], $row->from_user_id, $row->task_id);
                        $stmt->execute();
                        $stmt->close();
                    }
                }

                $_SESSION['alert'] = [
                    'title' => 'Task Updated',
                    'text'  => 'Task successfully updated.',
                    'icon'  => 'success'
                ];

            } 
            else
            {
         
            $perform_by = $_SESSION['id'];

            $user_ids = $_POST['user_ids'] ?? null;



            if (isset($user_ids) && !empty($user_ids) && is_array($user_ids)) {

            } else {
                $user_ids = [$_SESSION['id']];
            }

            for ($i = 0; $i < count($user_ids); $i++) {
                $user_id = $user_ids[$i];

        
                $stmt = $mysqli->prepare("INSERT INTO task (title, due_date, priority, status, description, remarks, file, repeat_interval , repeat_count , repeat_remaining, next_due_date, user_id) VALUES (?, ?, ?, ?, ?, ?, ?,?,?,?,?,?)");
                $stmt->bind_param("ssssssssissi", $title, $due_date, $priority, $status, $descriptions, $remark, $file_name, $repeat_interval, $repeat_count, $repeat_count, $due_date, $user_id);
                $stmt->execute();
                $task_id = $stmt->insert_id;
                $stmt->close();

                // Insert into task_history table
                $stmt = $mysqli->prepare("INSERT INTO task_history (task_id, action, to_user, performed_by) VALUES (?, 'created', ?, ?)");
                $stmt->bind_param("iii", $task_id, $user_id, $perform_by);
                $stmt->execute();
                $stmt->close();

                $stmt = $mysqli->prepare("INSERT INTO notifications (msg,from_user_id,to_user_id,task_id) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("siii", $msg , $_SESSION['id'], $user_id,$task_id);
                $stmt->execute();
                $stmt->close();
            }

            $_SESSION['alert'] = [
                'title' => 'Task Created',
                'text'  => 'Task successfully added.',
                'icon'  => 'success'
            ];


            }

            redirect("?");

        } else {
            $_SESSION['alert'] = [
                'title' => 'Missing Fields',
                'text'  => 'Please fill all required fields.',
                'icon'  => 'error'
            ];
            redirect("?");
        }

    } catch (mysqli_sql_exception $e) {
        $_SESSION['alert'] = [
            'title' => 'Database Error',
            'text'  => "Database error: " . $e->getMessage(),
            'icon'  => 'error'
        ];
        redirect("?mis");
    } catch (Throwable $e) {
        $_SESSION['alert'] = [
            'title' => 'Unexpected Error',
            'text'  => "Error: " . $e->getMessage(),
            'icon'  => 'error'
        ];
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
                        <h4 class="card-title">Task Management</h4>
                 
                        <button type="button" class="btn btn-square btn-outline-danger addTask" data-bs-toggle="modal"
                            data-bs-target=".bd-example-modal-lg">Add Task</button>
                


                    </div>
                    <div class="card-body">

                        <form method="GET" class="row g-2 mb-3">
                            <div class="col-md-2">
                                <select name="status" class="form-select">
                                    <option value="">-- All Status --</option>
                                    <option value="Pending"
                                        <?= (isset($_GET['status']) && $_GET['status'] == 'Pending') ? 'selected' : '' ?>>
                                        Pending</option>
                                    <option value="InProgress"
                                        <?= (isset($_GET['status']) && $_GET['status'] == 'InProgress') ? 'selected' : '' ?>>
                                        In Progress</option>
                                    <option value="Complete"
                                        <?= (isset($_GET['status']) && $_GET['status'] == 'Complete') ? 'selected' : '' ?>>
                                        Completed</option>
                                    <option value="OnHold"
                                        <?= (isset($_GET['status']) && $_GET['status'] == 'OnHold') ? 'selected' : '' ?>>
                                        OnHold</option>
                                </select>
                            </div>
                            <?php if($_SESSION['user'] == "admin") :?>
                            <div class="col-md-2">
                                <select name="user" class="form-select">
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
                            <?php endif;  ?>
                            <div class="col-md-2">
                                <select name="priority" class="form-select">
                                    <option value="">-- All Priority --</option>
                                    <option value="Low"
                                        <?= (isset($_GET['priority']) && $_GET['priority'] == 'Low') ? 'selected' : '' ?>>
                                        Low</option>
                                    <option value="Medium"
                                        <?= (isset($_GET['priority']) && $_GET['priority'] == 'Medium') ? 'selected' : '' ?>>
                                        Medium</option>
                                    <option value="High"
                                        <?= (isset($_GET['priority']) && $_GET['priority'] == 'High') ? 'selected' : '' ?>>
                                        High</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary">Filter</button>
                                <a href="task.php" class="btn btn-danger">Reset</a>

                            </div>




                        </form>

                    </div>
                    <form action="" method="POST">
                        <div class="card-body">

                            <?php if($_SESSION['user'] == "admin"): ?>
                            <h4>All Users</h4>
                            <br>
                            <div class="row">
                                <div class="col-3">
                                    <select name="assign_user" id="" class="form-control select2" required>
                                        <?php
                                    $user_id = $row['user_id'];
                                    $stmt_user = $mysqli->prepare("SELECT * FROM users where status = 1 ");
                                    $stmt_user->execute();
                                    $res = $stmt_user->get_result();
                                    while($row = $res->fetch_assoc()):
                                    ?>
                                        <option value="<?= $row['id'] ?>"><?= $row['name'] ?></option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                                <div class="col-2">
                                    <input type="submit" class="btn btn-success" name="btnAssign">
                                </div>


                            </div>
                            <?php endif; ?>

                            <div class="table-responsive">
                                <table id="example" class="display" style="min-width: 845px">
                                    <thead>
                                        <tr>
                                                 <?php if($_SESSION['user'] == "admin"): ?>
                                            <th><input type="checkbox" id="checkAll"></th>
                                            <?php endif; ?>
                                            <th>#</th>
                                            <th>Title</th>
                                            <th>Due Date</th>
                                            <th>Priority</th>
                                            <th>Status</th>
                                            <th>Description</th>
                                            <th>File</th>
                                            <th>Remark</th>
                                            <th>User</th>
                                            <th>Date</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php

$where = [];
$params = [];
$types  = "";

// If not admin, force filter by session user_id
if ($_SESSION['user'] != "admin") {
    $where[] = "user_id = ?";
    $params[] = $_SESSION['id'];
    $types   .= "i";
} else {
    // Only admin can filter by any user
    if (!empty($_GET['user'])) {
        $where[] = "user_id = ?";
        $params[] = $_GET['user'];
        $types   .= "i";
    }
}

// Other filters
if (!empty($_GET['status'])) {
    $where[] = "status = ?";
    $params[] = $_GET['status'];
    $types   .= "s";
}

if (!empty($_GET['priority'])) {
    $where[] = "priority = ?";
    $params[] = $_GET['priority'];
    $types   .= "s";
}

// Build query
$query = "SELECT * FROM task";
if (!empty($where)) {
    $query .= " WHERE " . implode(" AND ", $where);
}
$query .= " ORDER BY id DESC";

// Prepare statement
$stmt = $mysqli->prepare($query);

// Bind parameters if any
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

// Execute and fetch result
$stmt->execute();
$res = $stmt->get_result();


$sr_no = 1;
while ($row = $res->fetch_assoc()):
?>
                                        <tr>
                                                 <?php if($_SESSION['user'] == "admin"): ?>
                                            <td><input type="checkbox" class="leadCheckbox" value="<?= $row['id'] ?>"
                                                    name="task_id[]"></td>
                                                    <?php endif; ?>
                                            <td><?= $sr_no; ?></td>
                                            <td><?= $row['title'] ?></td>
                                            <td><?= $row['due_date'] ?></td>
                                            <td><?= $row['priority']?></td>
                                            <td><?= $row['status'] ?></td>
                                            <td><?= $row['description']?></td>
                                            <td>
                                                <?php if (!empty($row['file'])): ?>
                                                <img src="<?= BASE_PATH ?>images/task/<?= $row['file'] ?>" height="60"
                                                    alt="img">
                                                <?php else: ?>
                                                No Image
                                                <?php endif; ?>
                                            </td>
                                            <td><?= ($row['remarks']) ?></td>
                                            <td>
                                                <?php
            $user_id = $row['user_id'];
            $stmt_user = $mysqli->prepare("SELECT name FROM users WHERE id = ?");
            $stmt_user->bind_param("i", $user_id);
            $stmt_user->execute();
            $stmt_user->bind_result($user_name);
            $stmt_user->fetch();
            $stmt_user->close();
            echo ($user_name);
            ?>
                                            </td>
                                            <td><?= date('Y-m-d', strtotime($row['created_at'])) ?></td>
                                            <td>
                                                <button type="button"
                                                    class="btn btn-primary shadow btn-xs sharp me-1 editTask"
                                                    data-id="<?= $row['id'] ?>" data-due_date="<?= $row['due_date'] ?>"
                                                    data-priority="<?= $row['priority'] ?>"
                                                    data-status="<?= $row['status'] ?>"
                                                    data-description="<?= $row['description'] ?>"
                                                    data-remark="<?= $row['remarks'] ?>"
                                                    data-user_id="<?= $row['user_id'] ?>"
                                                    data-title="<?= $row['title'] ?>"
                                                    data-repeat_interval="<?= $row['repeat_interval'] ?>"
                                                    data-repeat_count="<?= $row['repeat_count'] ?>">
                                                    <i class="fa fa-pen"></i>
                                                </button>
                                            </td>
                                        </tr>
                                        <?php
$sr_no++;
endwhile;
?>
                                    </tbody>

                                </table>

                            </div>
                        </div>
                    </form>

                </div>
            </div>

        </div>



    </div>
</div>


<div class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Task</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal">
                </button>
            </div>
            <div class="modal-body">
                <form class="needs-validation" id="companyform" novalidate method="POST" enctype="multipart/form-data">
                    <div class="row">
                        <div class="col-md-6">
                            <label for="name" class="form-label">Title</label>
                            <input type="text" class="form-control" id="title" name="title" value="" required>
                        </div>
                        <div class="col-md-6">
                            <label for="name" class="form-label">Due Date</label>
                            <input type="date" class="form-control" id="due_date" name="due_date" value="" required>
                        </div>
                        <div class="col-md-6">
                            <label for="name" class="form-label">Priority</label>
                            <select name="priority" id="" class="form-control">
                                <option value="Low">Low</option>
                                <option value="Medium">Medium</option>
                                <option value="High">High</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="name" class="form-label">Status</label>
                            <select name="status" id="" class="form-control">
                                <option value="Pending">Pending</option>
                                <option value="InProgress">InProgress</option>
                                <option value="OnHold">OnHold</option>
                                <option value="Complete">Complete</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="name" class="form-label">Description</label>
                            <textarea name="descriptions" id="" class="form-control"></textarea>
                        </div>
                        <div class="col-md-6">
                            <label for="name" class="form-label">Remark</label>
                            <input type="text" class="form-control" id="remark" name="remark">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-6">
                            <label for="image">File</label>
                            <input type="file" name="image" class="form-control">
                        </div>
                             <?php if($_SESSION['user'] == "admin"): ?>
                        <div class="col-6" id="select_users">
                            <label for="">Select Assign User</label>
                            <select name="user_ids[]" id="multiple_user" class="form-control" multiple>
                                <?php
                                    $user_id = $row['user_id'];
                                    $stmt_user = $mysqli->prepare("SELECT * FROM users where status='1' ");
                                    $stmt_user->execute();
                                    $res = $stmt_user->get_result();
                                    while($row = $res->fetch_assoc()):
                                    ?>
                                <option value="<?= $row['id'] ?>"><?= $row['name'] ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <?php endif;  ?>
                        <div class="col-6">
                            <label>Repeat Interval</label>
                            <select name="repeat_interval" id="repeat_interval" class="form-control">
                                <option value="">No Repeat</option>
                                <option value="daily">Daily</option>
                                <option value="weekly">Weekly</option>
                                <option value="monthly">Monthly</option>
                            </select>
                        </div>

                        <div class="col-6">
                            <label>Number of Repeats</label>
                            <input type="number" name="repeat_count" id="repeat_count" class="form-control" min="0"
                                value="0">
                            <small class="form-text text-muted">If value is 0 Task not Repeat</small>
                        </div>
                    </div>
            </div>
            <div class="modal-footer">
                <input id="id" name="id" type="hidden">
                <button type="button" class="btn btn-danger light" data-bs-dismiss="modal">Close</button>
                <button type="submit" name="BtnSubmit" class="btn btn-primary btnname">Save changes</button>
            </div>
            </form>
        </div>
    </div>
</div>

<?php include "Layouts/Footer.php"  ?>

<script>
$('.bd-example-modal-lg').on('shown.bs.modal', function() {
    $('#multiple_user').select2({
        dropdownParent: $('.bd-example-modal-lg')
    });
});
$(document).on("click", ".editTask", function() {
    $("#id").val($(this).data("id"));
    $("#title").val($(this).data("title"));
    $("#due_date").val($(this).data("due_date"));
    $("select[name='priority']").val($(this).data("priority"));
    $("select[name='status']").val($(this).data("status"));
    $("textarea[name='descriptions']").val($(this).data("description"));
    $("#remark").val($(this).data("remark"));
    $("#repeat_interval").val($(this).data("repeat_interval"));
    $("#repeat_count").val($(this).data("repeat_count"));
    $("#select_users").hide();
    $(".bd-example-modal-lg").modal("show");
});

$(document).on("click", ".addTask", function() {
    // Clear all form fields for new entry
    $("#companyform").find("input[type=text], input[type=hidden], input[type=file], textarea, select").val('');
    $(".bd-example-modal-lg").modal("show");
});
$(document).on("click", "#checkAll", function() {
    $(".leadCheckbox").prop('checked', $(this).prop('checked'));
});
</script>