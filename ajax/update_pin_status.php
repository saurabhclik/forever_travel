<?php
include("../config.php");
if(isset($_POST['id']) && isset($_POST['value'])) {
    $stmt = $mysqli->prepare("UPDATE query_mst SET pinned = ? WHERE id = ?");
    $stmt->bind_param("ii", $_POST['value'], $_POST['id']);
    $stmt->execute();
    $stmt->close();
    echo "success";
}
?>
