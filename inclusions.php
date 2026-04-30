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
                // UPDATE existing record
                $stmt = $mysqli->prepare("UPDATE inclusions SET title = ?, name = ? WHERE id = ?");
                $stmt->bind_param("ssi", $_POST['title'], $_POST['name'], $_POST['id']);
                $stmt->execute();
                $stmt->close();

                $_SESSION['alert'] = [
                    'title' => 'Inclusion Successfully Updated',
                    'text' => 'Inclusion updated successfully.',
                    'icon' => 'success'
                ];
            } else {
                // INSERT new record
                $stmt = $mysqli->prepare("INSERT INTO inclusions (title, name) VALUES (?, ?)");
                $stmt->bind_param("ss", $_POST['title'], $_POST['name']);
                $stmt->execute();
                $stmt->close();

                $_SESSION['alert'] = [
                    'title' => 'Inclusion Successfully Saved',
                    'text' => 'Inclusion saved successfully.',
                    'icon' => 'success'
                ];
            }

            redirect("?");
        } else {
            $_SESSION['alert'] = [
                'title' => 'Inclusion Not Saved',
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
                        <h4 class="card-title">inclusions</h4>
                        <button type="button" class="btn btn-square btn-outline-danger addCompany"
                            data-bs-toggle="modal" data-bs-target=".bd-example-modal-lg">Add inclusion</button>

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
                                    $stmt = $mysqli->prepare('SELECT * FROM inclusions');
                                    $stmt->execute();
                                    $res = $stmt->get_result();
                                    $sr_no = 1;
                                    while($row = $res->fetch_assoc()):
                                ?>
                            <tr>
                                <td><?= $sr_no; ?></td>
                                <td><?= $row['title']  ?></td>
                                <td><?= $row['name']  ?></td>
                                <td><button class="btn btn-primary shadow btn-xs sharp me-1 edit"
                                                data-id="<?= $row['id'] ?>" data-name="<?= $row['name'] ?>"
                                        data-title="<?php echo $row['title']  ?>"
                                                ><i class="fa fa-pen"></i></button></td>
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
                <h5 class="modal-title">Add inclusions</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal">
                </button>
            </div>
            <div class="modal-body">
                <form class="needs-validation" id="companyform" novalidate method="POST">
                    <div class="row">
                        <div class="col-md-6">
                            <label for="name" class="form-label">Title</label>
                            <input type="text" class="form-control" id="title" name="title" value="" required>
                        </div>
                        <div class="col-md-6">
                            <label for="name" class="form-label">Name</label>
                            <input type="text" class="form-control" id="name" name="name" value="" required>
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
$(document).on("click", ".edit", function() {
    $("#id").val($(this).data("id"))
    $("#title").val($(this).data("title"))
    $("#name").val($(this).data("name"))
    $(".bd-example-modal-lg").modal("show");
});

$(document).on("click", ".addCompany", function() {

$("#companyform").find("input[type=text], textarea, select").val('');

});
</script>