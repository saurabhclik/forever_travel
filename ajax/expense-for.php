<?php
include("../config.php");

if (isset($_POST['state'])) {
    if ($_POST['state'] == "Customer") {
        $stmt = $mysqli->prepare("SELECT * FROM   customers");
        // $stmt->bind_param("s",$_POST['state']);
        $stmt->execute();
        $city = $stmt->get_result();
        while ($row = $city->fetch_assoc()) {
            echo '<option value="' . $row['id'] . '">' . $row['name'] . '</option>';
        }
    } else {
        $stmt = $mysqli->prepare("SELECT * FROM   vendor");
        // $stmt->bind_param("s",$_POST['state']);
        $stmt->execute();
        $city = $stmt->get_result();
        while ($row = $city->fetch_assoc()) {
            echo '<option value="' . $row['id'] . '">' . $row['name'] . '</option>';
        }
    }
}
