<?php
include("../config.php");

if (isset($_POST['state'])) {




    $stmt = $mysqli->prepare("SELECT * FROM   state_district  where state=?");
    $stmt->bind_param("s",$_POST['state']);
    $stmt->execute();
    $city = $stmt->get_result();
 
    while ($row = $city->fetch_assoc()) {
        echo '<option value="'.$row['District'].'">'.$row['District'].'</option>';
    }
 
}
