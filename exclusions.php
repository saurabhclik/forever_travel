<?php
    include "Layouts/Header.php";
    include "Layouts/Sidebar.php"; 

    if (isset($_POST['BtnSubmit'])) {
    try {
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

        if (
            isset($_POST['title']) && !empty($_POST['title']) &&
            isset($_POST['name']) && !empty($_POST['name'])
        ) {
            if (isset($_POST['id']) && !empty($_POST['id'])) {
                
                $stmt = $mysqli->prepare("UPDATE exclusions SET title = ?, name = ? WHERE id = ?");
                $stmt->bind_param("ssi", $_POST['title'], $_POST['name'], $_POST['id']);
                $stmt->execute();
                $stmt->close();

                $_SESSION['alert'] = [
                    'title' => 'Exclusion Successfully Updated',
                    'text' => 'Exclusion updated successfully.',
                    'icon' => 'success'
                ];
            } else {
            
                $stmt = $mysqli->prepare("INSERT INTO exclusions (title, name) VALUES (?, ?)");
                $stmt->bind_param("ss", $_POST['title'], $_POST['name']);
                $stmt->execute();
                $stmt->close();

                $_SESSION['alert'] = [
                    'title' => 'Exclusion Successfully Saved',
                    'text' => 'Exclusion saved successfully.',
                    'icon' => 'success'
                ];
            }

            redirect("?");
        } else {
            $_SESSION['alert'] = [
                'title' => 'Exclusion Not Saved',
                'text' => 'All fields are required.',
                'icon' => 'error'
            ];
            redirect("?");
        }
    } catch (mysqli_sql_exception $e) {
        $_SESSION['alert'] = [
            'title' => 'Database Error',
            'text' => "Database error: " . $e->getMessage(),
            'icon' => 'error'
        ];
        redirect("?");
    } catch (Throwable $e) {
        $_SESSION['alert'] = [
            'title' => 'Unexpected Error',
            'text' => "Error: " . $e->getMessage(),
            'icon' => 'error'
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
                        <h4 class="card-title">Exclusions</h4>
                        <button type="button" class="btn btn-square btn-outline-danger addCompany"
                            data-bs-toggle="modal" data-bs-target=".bd-example-modal-lg">Add Exclusion</button>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="example" class="display" style="min-width: 845px">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Title</th>
                                        <th>Name</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $stmt = $mysqli->prepare('SELECT * FROM exclusions');
                                    $stmt->execute();
                                    $res = $stmt->get_result();
                                    $sr_no = 1;
                                    while($row = $res->fetch_assoc()):
                                    ?>
                                    <tr>
                                        <td><?= $sr_no; ?></td>
                                        <td><?= $row['title']; ?></td>
                                        <td><?= $row['name']; ?></td>
                                        <td>
                                            <button class="btn btn-primary shadow btn-xs sharp me-1 edit"
                                                data-id="<?= $row['id'] ?>" 
                                                data-name="<?= $row['name'] ?>"
                                                data-title="<?= $row['title'] ?>">
                                                <i class="fa fa-pen"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <?php $sr_no++; endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Exclusion</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form class="needs-validation" id="exclusionForm" novalidate method="POST">
                    <div class="row">
                        <div class="col-md-6">
                            <label for="title" class="form-label">Title</label>
                            <input type="text" class="form-control" id="title" name="title" required>
                        </div>
                        <div class="col-md-6">
                            <label for="name" class="form-label">Name</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                    </div>
            </div>
            <div class="modal-footer">
                <input id="id" name="id" type="hidden">
                <button type="button" class="btn btn-danger light" data-bs-dismiss="modal">Close</button>
                <button type="submit" name="BtnSubmit" class="btn btn-primary">Save changes</button>
            </div>
                </form>
        </div>
    </div>
</div>


<?php include "Layouts/Footer.php"  ?>

<!-- <script>
$(document).on("click", ".edit", function() {
    $("#id").val($(this).data("id"))
    $("#title").val($(this).data("title"))
    $("#name").val($(this).data("name"))
    $(".bd-example-modal-lg").modal("show");
});

$(document).on("click", ".addCompany", function() {

$("#companyform").find("input[type=text], textarea, select").val('');

});
</script> -->

<script>
    $(document).on('click', '.edit', function () {
        const id = $(this).data('id');
        const title = $(this).data('title');
        const name = $(this).data('name');

        $('#id').val(id);
        $('#title').val(title);
        $('#name').val(name);

        $('.modal-title').text('Edit Exclusion');
        $('.btnname').text('Update');
        $('.bd-example-modal-lg').modal('show');
    });

    // Optional: Reset modal on close
    $('.bd-example-modal-lg').on('hidden.bs.modal', function () {
        $('#exclusionForm')[0].reset();
        $('#id').val('');
        $('.modal-title').text('Add Exclusion');
        $('.btnname').text('Save changes');
    });
</script>