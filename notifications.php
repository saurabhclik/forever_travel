<?php  include "Layouts/Header.php"; ?>
<?php  include "Layouts/Sidebar.php"; ?>
<?php

$update_stmt = $mysqli->prepare("UPDATE notifications SET status = 1 WHERE to_user_id = ?");
$update_stmt->bind_param("i", $_SESSION['id']);
$update_stmt->execute();
$update_stmt->close();



$stmt = $mysqli->prepare("SELECT 
                        notifications.id, 
                        notifications.msg, 
                        notifications.from_user_id, 
                        notifications.to_user_id, 
                        notifications.created_at, 
                        from_user.name AS from_user_name,
                        to_user.name AS to_user_name
                    FROM 
                        notifications 
                    JOIN users AS from_user ON notifications.from_user_id = from_user.id 
                    JOIN users AS to_user ON notifications.to_user_id = to_user.id 
                    WHERE 
                        notifications.to_user_id = ? 
                    ORDER BY 
                        notifications.id DESC
                    ");

$stmt->bind_param("i", $_SESSION['id']);
$stmt->execute();
$result = $stmt->get_result();
$stmt->close();
?>





<div class="content-body">
    <div class="container-fluid">
        <!-- row -->


        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Notications</h4>

                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                          <table id="example" class="display" style="min-width: 845px">
    <thead>
        <tr>
            <th>S.No</th>
            <th>From</th>
            <th>To</th>
            <th>Message</th>
            <th>Created At</th>
     
        </tr>
    </thead>
    <tbody>
        <?php 
        $sr_no = 1;
        while($row = $result->fetch_assoc()):
        ?>
        <tr>
            <td><?= $sr_no++; ?></td>
            <td><?= htmlspecialchars($row['from_user_name']); ?></td>
            <td><?= htmlspecialchars($row['to_user_name']); ?></td>
            <td><?= htmlspecialchars($row['msg']); ?></td>
            <td><?= htmlspecialchars($row['created_at']); ?></td>
     
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


<?php include "Layouts/Footer.php"  ?>
<script>
  $(document).on("click", ".edit", function() {

        $("#id").val($(this).data("id"))


    });
</script>