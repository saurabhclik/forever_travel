<?php
include("../config.php");

if (isset($_POST['id'])) {
    $id = $_POST['id'];

    $stmt = $mysqli->prepare("DELETE FROM note WHERE id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        echo json_encode([
            'status' => 'success',
            'alert'  => [
                'title' => 'Deleted!',
                'text'  => 'Note has been deleted.',
                'icon'  => 'success'
            ]
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'alert'  => [
                'title' => 'Error!',
                'text'  => 'Failed to delete note.',
                'icon'  => 'error'
            ]
        ]);
    }

    $stmt->close();
}
?>
