<?php
include("../config.php");

if (isset($_POST['query_id'])) {
    $query_id = $_POST['query_id'];

    $stmt = $mysqli->prepare("SELECT user_id FROM query_mst WHERE id = ?");
    $stmt->bind_param("i", $query_id);
    $stmt->execute();
    $stmt->bind_result($user_ids_string);
    $stmt->fetch();
    $stmt->close();

    if (!empty($user_ids_string)) {
        $user_ids_array = explode(',', $user_ids_string);

        // Lead creator is always first ID
        $lead_creator_id = $user_ids_array[0];

        $placeholders = implode(',', array_fill(0, count($user_ids_array), '?'));
        $types = str_repeat('i', count($user_ids_array));

        $stmt2 = $mysqli->prepare("SELECT id, name FROM users WHERE id IN ($placeholders)");
        $stmt2->bind_param($types, ...$user_ids_array);
        $stmt2->execute();
        $result = $stmt2->get_result();

        if ($result->num_rows > 0) {
            echo '<table class="table table-bordered">';
            echo '<thead><tr><th>#</th><th>User Name</th>';

            // Show Action column only if session user is lead creator or admin
            $can_manage = ($_SESSION['id'] == $lead_creator_id || $_SESSION['user'] == 'admin');
            if ($can_manage) {
                echo '<th>Action</th>';
            }

            echo '</tr></thead><tbody>';
            $sr = 1;
            while ($row = $result->fetch_assoc()) {
                echo '<tr>';
                echo '<td>' . $sr . '</td>';
                echo '<td>' . htmlspecialchars($row['name']) . '</td>';

                if ($can_manage) {
                    echo '<td>';
                    // Show delete button only for others, not the creator row
                    if ($row['id'] != $lead_creator_id) {
                        echo '<button class="btn btn-danger btn-sm" onclick="deleteUserFromQuery(' . $query_id . ',' . $row['id'] . ')">Delete</button>';
                    }
                    echo '</td>';
                }

                echo '</tr>';
                $sr++;
            }
            echo '</tbody></table>';
        } else {
            echo "No users found.";
        }
    } else {
        echo "Not shared with anyone.";
    }
}
?>
