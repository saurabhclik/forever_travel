<?php

 
ini_set("log_errors", 1);
ini_set("error_log", "error.log");
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
error_reporting(E_ALL);
ob_start();
define('BASE_PATH', (empty($_SERVER['HTTPS']) ? 'http' : 'https') . "://$_SERVER[HTTP_HOST]" . dirname($_SERVER['SCRIPT_NAME']) . '/');

session_start();
try {
    $mysqli = $_SERVER['SERVER_NAME'] == 'localhost' ? new mysqli("localhost", "root", "", "forever_travel") : new mysqli("localhost", "u128732477_SYEGO0A3J_forevertrave", "forevertraveEL@320", "u128732477_SYEGO0A3J_forevertravels");

    $mysqli->set_charset("utf8mb4");
    date_default_timezone_set('Asia/Calcutta');
    $now = new DateTime();
    $mins = $now->getOffset() / 60;
    $sgn = ($mins < 0 ? -1 : 1);
    $mins = abs($mins);
    $hrs = floor($mins / 60);
    $mins -= $hrs * 60;
    $offset = sprintf('%+d:%02d', $hrs * $sgn, $mins);
    $stmt = $mysqli->prepare("SET time_zone='$offset';");
    $stmt->execute();
    $stmt->close();
} catch (Exception $e) {
    error_log($e->getMessage());
    echo 'Error connecting to database';
}
function redirect($url)
{
    header('Location:' . $url);
    exit();
}
function token($length)
{

    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $randomString = '';

    for ($i = 0; $i < $length; $i++) {
        $index = rand(0, strlen($characters) - 1);
        $randomString .= $characters[$index];
    }

    return $randomString;
}

function alert($msg, $type, $title = "")
{
    $_SESSION['toastr']['msg_text'] = $msg;
    $_SESSION['toastr']['msg_type'] = $type;
    $_SESSION['toastr']['msg_title'] = $title;
}

function toaster()
{

    if (!empty($_SESSION['toastr']['msg_text'])) {
        switch ($_SESSION['toastr']['msg_type']) {
            case 'error':
                echo '<script>toastr.error("' . $_SESSION['toastr']['msg_text'] . '","' . $_SESSION['toastr']['msg_title'] . '");</script>';
                break;
            case 'success':
                echo '<script>toastr.success("' . $_SESSION['toastr']['msg_text'] . '","' . $_SESSION['toastr']['msg_title'] . '");</script>';
                break;
            case 'warning':
                echo '<script>toastr.warning("' . $_SESSION['toastr']['msg_text'] . '","' . $_SESSION['toastr']['msg_title'] . '");</script>';
                break;
            default:
                echo '<script>toastr.info("' . $_SESSION['toastr']['msg_text'] . '","' . $_SESSION['toastr']['msg_title'] . '");</script>';
                break;
        }
    }

    $_SESSION['toastr']['msg_text'] = "";
    $_SESSION['toastr']['msg_title'] = "";
    $_SESSION['toastr']['msg_type'] = "";
}
