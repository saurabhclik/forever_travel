<?php
include("../config.php");

if (isset($_POST['quote'])) {
    $note = $_POST['quote'];
    
    $user_id = $_SESSION['id'];

       $stmt = $mysqli->prepare("INSERT INTO note (note , user_id , created_at) values( ? , ?, now())");
   $stmt->bind_param("si" , $note , $user_id );

    if ($stmt->execute()) {
        echo json_encode([
            'status' => 'success',
            'alert'  => [
                'title' => 'Note Successfully Saved',
                'text'  => 'Note successfully Saved.',
                'icon'  => 'success'
            ]
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'alert'  => [
                'title' => 'Error',
                'text'  => 'Failed to save Note.',
                'icon'  => 'error'
            ]
        ]);
    }

    $stmt->close();
}
?>
