<?php
include("../config.php");

if (isset($_POST['category_id'])) {




    $stmt = $mysqli->prepare("SELECT * FROM   sub_category  where category_id=?");
    $stmt->bind_param("i", $_POST['category_id']);
    $stmt->execute();
    $city = $stmt->get_result();

    while ($row = $city->fetch_assoc()) {
        echo '<option value="' . $row['name'] . '">' . $row['name'] . '</option>';
    }
}
