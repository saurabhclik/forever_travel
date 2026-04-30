<?php
include("../config.php");

header('Content-Type: application/json'); // Important for JSON response

if (isset($_POST['id'])) {
    $id = $_POST['id'];

    $stmt = $mysqli->prepare("UPDATE expenses SET paid_status = 'Paid' WHERE id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        echo json_encode([
            'status' => 'success',
            'title'  => 'Success!',
            'text'   => 'Paid status updated successfully!'
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'title'  => 'Error!',
            'text'   => 'Failed to update status.'
        ]);
    }
}
?>

