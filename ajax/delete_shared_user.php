<?php
include("../config.php");


if (isset($_POST['query_id']) && isset($_POST['user_id'])) {
    $query_id = $_POST['query_id'];
    $user_id_to_delete = $_POST['user_id'];

    // Fetch existing user_ids
    $stmt = $mysqli->prepare("SELECT user_id FROM query_mst WHERE id = ?");
    $stmt->bind_param("i", $query_id);
    $stmt->execute();
    $stmt->bind_result($user_ids_string);
    $stmt->fetch();
    $stmt->close();

    if (!empty($user_ids_string)) {
        $user_ids_array = explode(',', $user_ids_string);
        $lead_creator_id = $user_ids_array[0];

        // Permission check — only creator or admin can delete
        if ($_SESSION['id'] == $lead_creator_id || $_SESSION['user'] == 'admin') {
            // Prevent deletion of creator
            if ($user_id_to_delete != $lead_creator_id) {
                // Remove user from array
                $updated_user_ids_array = array_diff($user_ids_array, [$user_id_to_delete]);

                // Implode to string again
                $updated_user_ids_string = implode(',', $updated_user_ids_array);

                // Update database
                $stmt2 = $mysqli->prepare("UPDATE query_mst SET user_id = ? WHERE id = ?");
                $stmt2->bind_param("si", $updated_user_ids_string, $query_id);
                $stmt2->execute();
                $stmt2->close();

                echo "success";
            } else {
                echo "cannot_delete_creator";
            }
        } else {
            echo "unauthorized";
        }
    } else {
        echo "no_users_found";
    }
} else {
    echo "invalid_request";
}
?>
