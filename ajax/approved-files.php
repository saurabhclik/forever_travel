<?php
include("../config.php");

if (isset($_POST['id'])) {

    $stmt = $mysqli->prepare("UPDATE   query_imgs set approved=?  where id=?");
    $stmt->bind_param("ii", $_POST['approved'],$_POST['id']);
    $stmt->execute();
    return true;
}
