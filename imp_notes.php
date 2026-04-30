<?php
    include "Layouts/Header.php";
    include "Layouts/Sidebar.php"; 

if (isset($_POST['BtnSubmit'])) {
    try {
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

        if (!empty($_POST['category']) && !empty($_POST['content'])) {
            if (!empty($_POST['id'])) {
                // UPDATE
                $stmt = $mysqli->prepare("UPDATE important_notes SET category = ?, content = ? WHERE id = ?");
                $stmt->bind_param("ssi", $_POST['category'], $_POST['content'], $_POST['id']);
            } else {
                // INSERT
                $stmt = $mysqli->prepare("INSERT INTO important_notes (category, content) VALUES (?, ?)");
                $stmt->bind_param("ss", $_POST['category'], $_POST['content']);
            }

            $stmt->execute();
            $stmt->close();

            $_SESSION['alert'] = [
                'title' => 'Note Saved',
                'text' => 'Important Note has been saved successfully.',
                'icon' => 'success'
            ];
        } else {
            $_SESSION['alert'] = [
                'title' => 'Note Not Saved',
                'text' => 'All fields are required.',
                'icon' => 'error'
            ];
        }
        redirect("?");
    } catch (Throwable $e) { // <-- Catch all possible errors
        $_SESSION['alert'] = [
            'title' => 'Error',
            'text' => 'Database error: ' . $e->getMessage(),
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
                    <div class="card-header d-flex justify-content-between">
                        <h4 class="card-title">Important Notes</h4>
                        <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal"
                            data-bs-target="#noteModal">Add
                            Note</button>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="notesTable" class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Category</th>
                                        <th>Content</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                    $stmt = $mysqli->prepare("SELECT * FROM important_notes ORDER BY category ASC");
                    $stmt->execute();
                    $res = $stmt->get_result();
                    $sr_no = 1;
                    while ($row = $res->fetch_assoc()):
                    ?>
                                    <tr>
                                        <td><?= $sr_no++ ?></td>
                                        <td><?= htmlspecialchars($row['category']) ?></td>
                                        <td><?= nl2br(htmlspecialchars($row['content'])) ?></td>
                                        <td>
                                            <button class="btn btn-sm btn-primary editNote" data-id="<?= $row['id'] ?>"
                                                data-category="<?= htmlspecialchars($row['category']) ?>"
                                                data-content="<?= htmlspecialchars($row['content']) ?>">
                                                <i class="fa fa-edit"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

        </div>



    </div>
</div>


<div class="modal fade" id="noteModal" tabindex="-1" aria-labelledby="noteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" id="noteForm">
                <div class="modal-header">
                    <h5 class="modal-title" id="noteModalLabel">Add Important Note</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="noteId">
                    <div class="mb-3">
                        <label for="category" class="form-label">Category</label>
                        <input type="text" name="category" id="category" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="content" class="form-label">Content</label>
                        <textarea name="content" id="content" rows="5" class="form-control" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" name="BtnSubmit" class="btn btn-primary">Save Note</button>
                </div>
            </form>
        </div>
    </div>
</div>


<?php include "Layouts/Footer.php"  ?>

<script>
$(document).on('click', '.editNote', function() {
    $('#noteId').val($(this).data('id'));
    $('#category').val($(this).data('category'));
    $('#content').val($(this).data('content'));
    $('#noteModalLabel').text('Edit Important Note');
    $('#noteModal').modal('show');
});

$('#noteModal').on('hidden.bs.modal', function() {
    $('#noteForm')[0].reset();
    $('#noteId').val('');
    $('#noteModalLabel').text('Add Important Note');
});
</script>