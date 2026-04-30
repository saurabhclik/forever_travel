<?php  
include "Layouts/Header.php"; 
include "Layouts/Sidebar.php"; 



if (isset($_POST['btnadd'])) {
    $name = $_POST['name'];
    $id = $_POST['id'];

    if (!empty($id)) {
        // Update existing role
        $stmt = $mysqli->prepare("UPDATE roles SET role_name = ? WHERE id = ?");
        $stmt->bind_param("si", $name, $id);
        if ($stmt->execute()) {
              $_SESSION['alert'] = [
        'title' => 'Role Updated Successfully',
        'text' => 'Role Updated Successfully',
        'icon' => 'success'];
            redirect("role.php");
        } else {
             $_SESSION['alert'] = [
        'title' => 'Role Not Update Successfully',
        'text' => 'Role Not Update Successfully',
        'icon' => 'error'];
            redirect("role.php");
        }
        $stmt->close();
    } else {
        // Insert new role

        if (empty($name)) {
           alert("Please fill all required fields.", "warning", "warning");
                redirect("?");
        }
        $stmt = $mysqli->prepare("INSERT INTO roles (role_name) VALUES (?)");
        $stmt->bind_param("s", $name);
        if ($stmt->execute()) {
          $_SESSION['alert'] = [
        'title' => 'Role Added Successfully',
        'text' => 'Role Added Successfully',
        'icon' => 'success'];
            redirect("role.php");
        } else {
            $_SESSION['alert'] = [
        'title' => 'Role Not Added Successfully',
        'text' => 'Role Not Added Successfully',
        'icon' => 'error'];
            redirect("role.php");   
        }
        $stmt->close();
    }
}
?>


<div class="content-body">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Role Management</h4>
                        <button type="button" class="btn btn-square btn-outline-danger" data-bs-toggle="modal"
                            data-bs-target=".bd-example-modal-lg">Add Role</button>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="example" class="display" style="min-width: 845px">
                                <thead>
                                    <tr>
                                        <th>S.No</th>
                                        <th>Name</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $i = 1;
                                    $stmt = $mysqli->prepare("SELECT * FROM roles ORDER BY id DESC");
                                    $stmt->execute();
                                    $result = $stmt->get_result();
                                    while ($row = $result->fetch_assoc()) {
                                    ?>
                                        <tr>
                                            <td><?= $i++ ?></td>
                                            <td><?= $row['role_name'] ?></td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-warning edit"
                                                    data-id="<?= $row['id'] ?>"
                                                    data-name="<?= $row['role_name'] ?>">Edit</button>
                                            </td>
                                        </tr>
                                    <?php } $stmt->close(); ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>



<?php include "Layouts/Footer.php"; ?>

<!-- Modal Start -->
<div class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog" aria-hidden="true">
    <form class="needs-validation" method="POST">
        <div class="modal-dialog modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="modal-title fs-5" id="exampleModalLabel">
                        <span class="btnname"> Add Role</span>
                    </h3>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <!-- Hidden id field for edit -->
                        <input id="id" name="id" type="hidden">
                        <div class="col-md-12">
                            <label for="name" class="form-label">Role Name</label>
                            <input type="text" class="form-control" id="name" name="name" value="" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <!-- Close button -->
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <!-- Submit button -->
                    <button type="submit" class="btn btn-success" name="btnadd" id="btnadd">
                        <span class="btnname"> Submit</span>
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>
<!-- Modal End -->

<script>
    $(document).on("click", ".edit", function() {
        $("#id").val($(this).data("id"))
        $("#name").val($(this).data("name"))
        $(".btnname").text("Update Role")
        $(".bd-example-modal-lg").modal("show")
    });
</script>
