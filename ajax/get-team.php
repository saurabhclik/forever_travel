<?php
include("../config.php");
$error = true;
$data = NULL;
$msg = '';


$token = $_COOKIE['token'];

$stmt = $mysqli->prepare("SELECT * FROM users WHERE token = ? and role='admin';");
$stmt->bind_param("s", $token);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
if (!empty($user)  && !empty($_POST['id'])){

   $stmt = $mysqli->prepare("SELECT u.*, p.name as parent_name FROM users u LEFT JOIN users p ON u.parent_id = p.id WHERE u.id = ?");
$stmt->bind_param("i", $_POST['id']);
$stmt->execute();
$feedback = $stmt->get_result()->fetch_assoc();

    $data=$feedback;

}else{
$error=true;
$msg="Unauthorized request";
}

$response = array('data' => $data, 'error' => $error, 'msg' => $msg);
echo json_encode($response);

?>