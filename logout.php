<?php
include("config.php"); 
   $token="";
    $stmt = $mysqli->prepare("UPDATE `users` set token=?,last_login=now(),last_ip=? WHERE  token=?   ");
    $stmt->bind_param("sss",$token,$_SERVER['REMOTE_ADDR'],$_SESSION['admintoken']);
    $stmt->execute();
    $stmt->close();
    setcookie('token', $_SESSION['token'], time() - 86400 * 1);
    redirect("index.php");

?>
 