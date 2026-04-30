<?php
include("../config.php");

if (isset($_POST['name'])) {




    $stmt = $mysqli->prepare("SELECT * FROM   expense_subcategory  where category_id=?");
    $stmt->bind_param("s",$_POST['name']);
    $stmt->execute();
    $city = $stmt->get_result();
 
    while ($row = $city->fetch_assoc()) {
        echo '<option value="'.$row['name'].'">'.$row['name'].'</option>';
    }
 
}
