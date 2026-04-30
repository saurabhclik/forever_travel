<?php
include("../../config.php");
$error = true;
$data = NULL;
$msg = '';


$token = $_COOKIE['admintoken'];

$stmt = $mysqli->prepare("SELECT * FROM admin WHERE token = ? and role='admin';");
$stmt->bind_param("s", $token);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
if (!empty($user)  && !empty($_POST['mobile']) && !empty($_POST['id'])) {

    $stmt = $mysqli->prepare("SELECT  *from student_list where  id=?");
    $stmt->bind_param("i", $_POST['id']);
    $stmt->execute();
    $student_list = $stmt->get_result()->fetch_assoc();
    if (!empty($student_list)) {
        $stmt = $mysqli->prepare("SELECT  *from student_list where  mobilenumber=?");
        $stmt->bind_param("s", $_POST['mobile']);
        $stmt->execute();
        $email = $stmt->get_result()->fetch_assoc();
        if (empty($email)) {

            $stmt = $mysqli->prepare("UPDATE  student_list set mobilenumber=? where  id=?");
            $stmt->bind_param("si", $_POST['mobile'], $student_list['id']);
            $stmt->execute();

            $error = false;
            $msg = "Number successfully changed.";
           
        } else {
            $error = true;
            $msg = "Number  already exists.";
        }
    } else {
        $error = true;
        $msg = "Student Not Found";
    }
} else {
    $error = true;
    $msg = "Unauthorized request";
}

$response = array('data' => $data, 'error' => $error, 'msg' => $msg);
echo json_encode($response);
