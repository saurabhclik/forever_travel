<?php  
include "Layouts/Header.php"; 
include "Layouts/Sidebar.php"; 



if (isset($_POST['assign'])) {
    $role_id = $_POST['role_id'];
    $permissions = $_POST['permissions'] ?? [];

    $mysqli->query("DELETE FROM role_permissions WHERE role_id = $role_id");

    foreach ($permissions as $permission_id) {
        $stmt = $mysqli->prepare("INSERT INTO role_permissions (role_id, permission_id) VALUES (?, ?)");
        $stmt->bind_param("ii", $role_id, $permission_id);
        $stmt->execute();
        $stmt->close();
    }

    $_SESSION['alert'] = [
        'title' => 'Permissions Assigned!',
        'text'  => 'Permissions assigned successfully to the role.',
        'icon'  => 'success'
    ];
    redirect("assign_permission.php?role_id=$role_id");
}

$selected_role_id = $_GET['role_id'] ?? '';
$assigned_permissions = [];

if (!empty($selected_role_id)) {
    $res = $mysqli->query("SELECT permission_id FROM role_permissions WHERE role_id = $selected_role_id");
    while ($row = $res->fetch_assoc()) {
        $assigned_permissions[] = $row['permission_id'];
    }
}
?>

<div class="content-body">
    <div class="container-fluid">

        <!-- Card -->
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">Assign Permissions to Role</h4>
            </div>
            <div class="card-body">
                <form method="POST">
                    <div class="mb-3">
                        <label>Select Role:</label>
                        <select name="role_id" class="form-select" onchange="window.location.href='assign_permission.php?role_id=' + this.value" required>
                            <option value="">-- Select Role --</option>
                            <?php
                            $roles = $mysqli->query("SELECT * FROM roles");
                            while($role = $roles->fetch_assoc()){
                                $selected = ($selected_role_id == $role['id']) ? "selected" : "";
                                echo "<option value='{$role['id']}' $selected>{$role['role_name']}</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <?php if(!empty($selected_role_id)) { ?>
                    <div class="table-responsive">
                        <table class="table table-bordered" style="min-width: 845px">
                            <thead>
                                <tr>
                                    <th>S.No</th>
                                    <th>Permission Name</th>
                                    <th>Assign</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $i=1;
                                $permissions = $mysqli->query("SELECT * FROM permissions");
                                while($permission = $permissions->fetch_assoc()){
                                    $checked = (in_array($permission['id'], $assigned_permissions)) ? "checked" : "";
                                    echo "<tr>
                                            <td>{$i}</td>
                                            <td>{$permission['permission_name']}</td>
                                            <td>
                                                <input type='checkbox' name='permissions[]' value='{$permission['id']}' $checked>
                                            </td>
                                          </tr>";
                                    $i++;
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                    <button type="submit" name="assign" class="btn btn-primary">Assign Permissions</button>
                    <?php } ?>
                </form>
            </div>
        </div>

    </div>
</div>

<?php include "Layouts/Footer.php"; ?>


