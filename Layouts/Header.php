<?php
include('config.php');
if (isset($_COOKIE['token'])) {
  $stmt = $mysqli->prepare("SELECT * FROM `users` WHERE   token=?");
  $stmt->bind_param("s", $_COOKIE['token']);
  $stmt->execute();
  $user = $stmt->get_result()->fetch_assoc();
  $stmt->close();
  if (empty($user)) {

    redirect("login.php");
  } else {
    $_SESSION['token'] = $_COOKIE['token'];
  }
} else {

  redirect("login.php");
}

$stmt = $mysqli->prepare("SELECT * FROM `settings` WHERE   id=1");
$stmt->execute();
$setting = $stmt->get_result()->fetch_assoc();


if(isset($_POST['btnSave'])){

   $note = $_POST['note'];
   $date = date("Y-m-d");

   $stmt = $mysqli->prepare("INSERT INTO note (note , created_at) values( ? , $date)");
   $stmt->bind_param("s" , $note);
   $stmt->execute();
   $stmt->close();


}

?>
<!DOCTYPE html>
<html lang="en">
<!-- Mirrored from kamr.dexignlab.com/xhtml/index.html by HTTrack Website Copier/3.x [XR&CO'2014], Wed, 04 Jun 2025 10:42:29 GMT -->

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="keywords" content="" />
    <meta name="author" content="" />
    <meta name="robots" content="" />
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Kamr Hotel Admin Dashboard" />
    <meta property="og:title" content="Kamr Hotel Admin Dashboard" />
    <meta property="og:description" content="Kamr Hotel Admin Dashboard" />
    <meta property="og:image" content="social-image.png" />
    <meta name="format-detection" content="telephone=no">

    <!-- PAGE TITLE HERE -->
    <title>Forever Travellers Dashboard</title>


    <!-- FAVICONS ICON -->
    <link rel="shortcut icon" type="image/png" href="images/company_logo/logo1.png" />
    <link href="vendor/jquery-nice-select/css/nice-select.css" rel="stylesheet">
    <link rel="stylesheet" href="vendor/dotted-map/css/contrib/jquery.smallipop-0.3.0.min.css" type="text/css"
        media="all" />
    <link href="vendor/owl-carousel/owl.carousel.css" rel="stylesheet">
    <link href="vendor/bootstrap-select/dist/css/bootstrap-select.min.css" rel="stylesheet">
    <!-- Style css -->
    <link href="vendor/datatables/css/jquery.dataTables.min.css" rel="stylesheet">
    <link href="vendor/datepicker/css/bootstrap-datepicker.min.css" rel="stylesheet">
    <link href="vendor/swiper/css/swiper-bundle.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
    <!-- Include SweetAlert2 from CDN -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    <script src="https://cdn.ckeditor.com/4.22.1/standard/ckeditor.js"></script>

    <style>
    .tooltip-wrap {
        position: relative;
        display: inline-block;
        cursor: pointer;
    }

    .tooltip-wrap .custom-tooltip {
        visibility: hidden;
        width: 300px;
        background-color: #333;
        color: #fff;
        text-align: left;
        border-radius: 6px;
        padding: 12px 16px;
        position: fixed;
        z-index: 9999;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        opacity: 0;
        transition: opacity 0.3s ease;
        font-size: 14px;
        line-height: 1.5;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.3);
    }

    .tooltip-wrap .custom-tooltip::after {
        display: none;
        /* hide arrow since it's centered */
    }

    .tooltip-wrap:hover .custom-tooltip {
        visibility: visible;
        opacity: 1;
    }
@keyframes ring {
  0%   { transform: rotate(0); }
  10%  { transform: rotate(15deg); }
  20%  { transform: rotate(-10deg); }
  30%  { transform: rotate(10deg); }
  40%  { transform: rotate(-8deg); }
  50%  { transform: rotate(6deg); }
  60%  { transform: rotate(-4deg); }
  70%  { transform: rotate(2deg); }
  80%  { transform: rotate(-1deg); }
  100% { transform: rotate(0); }
}

.ringing {
  animation: ring 2s infinite;
  transform-origin: top center;
}


    </style>

</head>

<body>
    <div id="main-wrapper">

        <div class="header">
            <div class="header-content">
                <nav class="navbar navbar-expand">
                    <div class="collapse navbar-collapse justify-content-between">
                        <div class="header-left">
                            <div class="headaer-title">
                                <h1 class="font-w600 mb-0"> <?=$user["name"]?></h1>
                            </div>
                        </div>
                        <ul class="navbar-nav header-right">
                            <li>
                                <div class="nav-item ms-auto">
                                    <div class="input-group search-area2">
                                        <input type="text" class="form-control" placeholder="Search here">
                                        <span class="input-group-text"><a href="javascript:void(0)"><svg width="24"
                                                    height="24" viewBox="0 0 24 24" fill="none"
                                                    xmlns="http://www.w3.org/2000/svg">
                                                    <path
                                                        d="M9.65925 19.3102C11.8044 19.3102 13.8882 18.5945 15.5806 17.2764L21.9653 23.6611C22.4423 24.1218 23.2023 24.1086 23.663 23.6316C24.1123 23.1663 24.1123 22.4287 23.663 21.9635L17.2782 15.5787C20.5491 11.3682 19.7874 5.3033 15.5769 2.0324C11.3663 -1.23851 5.30149 -0.476829 2.03058 3.73371C-1.24033 7.94425 -0.478646 14.0091 3.73189 17.2801C5.42702 18.5969 7.51269 19.3113 9.65925 19.3102ZM4.52915 4.52727C7.36245 1.69391 11.9561 1.69386 14.7895 4.52717C17.6229 7.36047 17.6229 11.9542 14.7896 14.7875C11.9563 17.6209 7.36261 17.6209 4.52925 14.7876C4.5292 14.7876 4.5292 14.7876 4.52915 14.7875C1.69584 11.9749 1.67915 7.39791 4.49181 4.56461C4.50424 4.55213 4.51667 4.5397 4.52915 4.52727Z"
                                                        fill="#717579" />
                                                </svg>
                                            </a></span>
                                    </div>
                                </div>
                            </li>
                            <li class="nav-item dropdown notification_dropdown">

                                <?php 
                      
                            $status = 0;
                            $stmt = $mysqli->prepare("SELECT COUNT(*) AS total FROM notifications WHERE to_user_id = ? AND status = ?");
                            $stmt->bind_param("ii", $_SESSION['id'], $status);
                            $stmt->execute();
                            $stmt->bind_result($total);
                            $stmt->fetch();
                            $stmt->close();

                            $stmt = $mysqli->prepare("SELECT notifications.id, notifications.msg, notifications.from_user_id, notifications.to_user_id, notifications.created_at, users.name  FROM notifications JOIN users ON notifications.from_user_id = users.id WHERE notifications.to_user_id = ? AND notifications.status = ? ORDER BY notifications.id DESC");

                            $stmt->bind_param("ii", $_SESSION['id'],$status);
                            $stmt->execute();
                            $result = $stmt->get_result();
                            $stmt->close();
                            ?>
                                <a class="nav-link nav-action" href="javascript:void(0);" role="button"
                                    data-bs-toggle="dropdown">
                                    <svg id="notificationBell" width="28" height="28" viewBox="0 0 28 28" fill="none"   class="<?php echo ($total > 0) ? 'ringing' : ''; ?>"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <path
                                            d="M10.4524 25.6682C11.0605 27.0357 12.409 28 14.0005 28C15.592 28 16.9405 27.0357 17.5487 25.6682C16.4265 25.7231 15.2594 25.76 14.0005 25.76C12.7417 25.76 11.5746 25.723 10.4524 25.6682Z"
                                            fill="#737B8B" />
                                        <path
                                            d="M26.3532 19.74C24.877 17.8785 22.3996 14.2195 22.3996 10.64C22.3996 7.09073 20.1193 3.89758 16.7996 2.72382C16.7593 1.21406 15.5183 0 14.0007 0C12.482 0 11.2422 1.21406 11.2018 2.72382C7.88101 3.89758 5.6007 7.09073 5.6007 10.64C5.6007 14.2207 3.1244 17.8785 1.64712 19.74C1.15433 20.3616 1.00197 21.1825 1.24058 21.9363C1.47354 22.6721 2.05367 23.2422 2.79288 23.4595C4.08761 23.8415 6.20997 24.2715 9.44682 24.491C10.8479 24.5851 12.3543 24.64 14.0008 24.64C15.646 24.64 17.1525 24.5851 18.5535 24.491C21.7915 24.2715 23.9128 23.8415 25.2086 23.4595C25.9478 23.2422 26.5268 22.6722 26.7598 21.9363C26.9983 21.1825 26.8449 20.3616 26.3532 19.74Z"
                                            fill="#737B8B" />
                                    </svg>

                                    <span class="total_notification">
                                        <?php echo $total;  ?>
                                    </span>
                                </a>
                                <div class="dropdown-menu dropdown-menu-end">
                                    <div id="DZ_W_Notification1" class="widget-media dz-scroll p-3" style="">
                                        <ul class="timeline">
                                            <li>
                                                <div class="timeline-panel">
                                                    <div class="media-body">
                                                        <ul class="list-group">
                                                            <?php 
                                                    $count = 0;
                                                    while($row = $result->fetch_assoc()) :  
                                                        if ($count >= 3) break;
                                                    ?>
                                                            <li
                                                                class="list-group-item d-flex justify-content-between align-items-center">
                                                                <div>
                                                                    <strong>From:</strong>
                                                                    <?= htmlspecialchars($row['name']) ?><br>
                                                                    <small><?= htmlspecialchars($row['msg']) ?></small>
                                                                </div>
                                                                <span class="badge badge-primary badge-pill">New</span>
                                                            </li>

                                                            <?php 
                                                        $count++;
                                                    endwhile; 
                                                    ?>

                                                        </ul>
                                                    </div>
                                                </div>
                                            </li>

                                        </ul>
                                    </div>
                                    <a class="all-notification" href="notifications.php">See all notifications <i
                                            class="ti-arrow-end"></i></a>
                                </div>
                            </li>
                            <li class="nav-item dropdown notification_dropdown">
                                <a class="nav-link bell-link nav-action " href="javascript:void(0);">
                                    <svg width="28" height="28" viewBox="0 0 28 28" fill="none"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <path
                                            d="M14.8257 17.5282C14.563 17.6783 14.2627 17.7534 14 17.7534C13.7373 17.7534 13.437 17.6783 13.1743 17.5282L0 9.49598V20.193C0 22.4826 1.83914 24.3217 4.12869 24.3217H23.8713C26.1609 24.3217 28 22.4826 28 20.193V9.49598L14.8257 17.5282Z"
                                            fill="#737B8B" />
                                        <path
                                            d="M23.8713 3.67829H4.12863C2.17689 3.67829 0.525417 5.06703 0.112549 6.90617L13.9999 15.3887L27.8873 6.90617C27.4745 5.06703 25.823 3.67829 23.8713 3.67829Z"
                                            fill="#737B8B" />
                                    </svg>
                                    <span class="badge light text-white bg-primary rounded-circle"></span>
                                </a>
                            </li>
                            <!-- <li class="nav-item dropdown header-profile">
                                <div id="datepicker" class="input-group date dz-calender" data-date-format="mm-dd-yyyy">
                                    <span class="input-group-addon d-flex align-items-center">
                                        <svg width="18" height="18" viewBox="0 0 18 18" fill="none"
                                            xmlns="http://www.w3.org/2000/svg">
                                            <path
                                                d="M14.8337 3.16667H14.0003V1.50001C14.0003 1.27899 13.9125 1.06703 13.7563 0.910749C13.6 0.754469 13.388 0.666672 13.167 0.666672C12.946 0.666672 12.734 0.754469 12.5777 0.910749C12.4215 1.06703 12.3337 1.27899 12.3337 1.50001V3.16667H5.66699V1.50001C5.66699 1.27899 5.5792 1.06703 5.42292 0.910749C5.26663 0.754469 5.05467 0.666672 4.83366 0.666672C4.61265 0.666672 4.40068 0.754469 4.2444 0.910749C4.08812 1.06703 4.00033 1.27899 4.00033 1.50001V3.16667H3.16699C2.50395 3.16667 1.86807 3.43006 1.39923 3.8989C0.930384 4.36775 0.666992 5.00363 0.666992 5.66667V6.5H17.3337V5.66667C17.3337 5.00363 17.0703 4.36775 16.6014 3.8989C16.1326 3.43006 15.4967 3.16667 14.8337 3.16667Z"
                                                fill="#F66F4D" />
                                            <path
                                                d="M0.666992 14.8333C0.666992 15.4964 0.930384 16.1322 1.39923 16.6011C1.86807 17.0699 2.50395 17.3333 3.16699 17.3333H14.8337C15.4967 17.3333 16.1326 17.0699 16.6014 16.6011C17.0703 16.1322 17.3337 15.4964 17.3337 14.8333V8.16666H0.666992V14.8333Z"
                                                fill="#F66F4D" />
                                        </svg>
                                    </span>
                                    <input class="form-control" type="text" readonly />
                                </div>
                            </li> -->
                        </ul>
                    </div>
                </nav>
            </div>
        </div>



        <!--*******************
        Preloader start
    ********************-->
        <div id="preloader">
            <div class="lds-ripple">
                <div></div>
                <div></div>
            </div>
        </div>
        <!--*******************
        Preloader end
    ********************-->

        <!--**********************************
        Main wrapper start
    ***********************************-->



        <div class="nav-header">
            <a href="index.php" class="brand-logo"
                style="display: flex; align-items: center; justify-content: center; height: 100%;">
                <img src="<?php echo $setting['img_1'];  ?>" alt="logo"
                    style="object-fit: contain; height: 100%; width: auto; display: block;">
            </a>
            <div class="nav-control">
                <div class="hamburger">
                    <span class="line"></span><span class="line"></span><span class="line"></span>
                </div>
            </div>
        </div>

        <!--**********************************
            Nav header end
        ***********************************-->

        <!--**********************************
            Chat box start
        ***********************************-->
        <div class="chatbox">
            <div class="chatbox-close"></div>
            <div class="custom-tab-1">
                <ul class="nav nav-tabs">
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#notes">Notes</a>
                    </li>
                    <!-- <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#alerts">Alerts</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" data-bs-toggle="tab" href="#chat">Chat</a>
                    </li> -->
                </ul>
                <div class="tab-content">
                    <!-- <div class="tab-pane fade active show" id="chat" role="tabpanel">
                        <div class="card mb-sm-3 mb-md-0 contacts_card dz-chat-user-box">
                            <div class="card-header chat-list-header text-center">
                                <a href="javascript:void(0);"><svg xmlns="http://www.w3.org/2000/svg"
                                        xmlns:xlink="http://www.w3.org/1999/xlink" width="18px" height="18px"
                                        viewBox="0 0 24 24" version="1.1">
                                        <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                            <rect fill="#000000" x="4" y="11" width="16" height="2" rx="1" />
                                            <rect fill="#000000" opacity="0.3"
                                                transform="translate(12.000000, 12.000000) rotate(-270.000000) translate(-12.000000, -12.000000) "
                                                x="4" y="11" width="16" height="2" rx="1" />
                                        </g>
                                    </svg></a>
                                <div>
                                    <h6 class="mb-1">Chat List</h6>
                                    <p class="mb-0">Show All</p>
                                </div>
                                <a href="javascript:void(0);"><svg xmlns="http://www.w3.org/2000/svg"
                                        xmlns:xlink="http://www.w3.org/1999/xlink" width="18px" height="18px"
                                        viewBox="0 0 24 24" version="1.1">
                                        <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                            <rect x="0" y="0" width="24" height="24" />
                                            <circle fill="#000000" cx="5" cy="12" r="2" />
                                            <circle fill="#000000" cx="12" cy="12" r="2" />
                                            <circle fill="#000000" cx="19" cy="12" r="2" />
                                        </g>
                                    </svg></a>
                            </div>
                            <div class="card-body contacts_body p-0 dz-scroll  " id="DZ_W_Contacts_Body">
                                <ul class="contacts">
                                    <li class="name-first-letter">A</li>
                                    <li class="active dz-chat-user">
                                        <div class="d-flex bd-highlight">
                                            <div class="img_cont">
                                                <img src="images/avatar/1.jpg" class="rounded-circle user_img" alt="" />
                                                <span class="online_icon"></span>
                                            </div>
                                            <div class="user_info">
                                                <span>Archie Parker</span>
                                                <p>Kalid is online</p>
                                            </div>
                                        </div>
                                    </li>
                                    <li class="dz-chat-user">
                                        <div class="d-flex bd-highlight">
                                            <div class="img_cont">
                                                <img src="images/avatar/2.jpg" class="rounded-circle user_img" alt="" />
                                                <span class="online_icon offline"></span>
                                            </div>
                                            <div class="user_info">
                                                <span>Alfie Mason</span>
                                                <p>Taherah left 7 mins ago</p>
                                            </div>
                                        </div>
                                    </li>
                                    <li class="dz-chat-user">
                                        <div class="d-flex bd-highlight">
                                            <div class="img_cont">
                                                <img src="images/avatar/3.jpg" class="rounded-circle user_img" alt="" />
                                                <span class="online_icon"></span>
                                            </div>
                                            <div class="user_info">
                                                <span>AharlieKane</span>
                                                <p>Sami is online</p>
                                            </div>
                                        </div>
                                    </li>
                                    <li class="dz-chat-user">
                                        <div class="d-flex bd-highlight">
                                            <div class="img_cont">
                                                <img src="images/avatar/4.jpg" class="rounded-circle user_img" alt="" />
                                                <span class="online_icon offline"></span>
                                            </div>
                                            <div class="user_info">
                                                <span>Athan Jacoby</span>
                                                <p>Nargis left 30 mins ago</p>
                                            </div>
                                        </div>
                                    </li>
                                    <li class="name-first-letter">B</li>
                                    <li class="dz-chat-user">
                                        <div class="d-flex bd-highlight">
                                            <div class="img_cont">
                                                <img src="images/avatar/5.jpg" class="rounded-circle user_img" alt="" />
                                                <span class="online_icon offline"></span>
                                            </div>
                                            <div class="user_info">
                                                <span>Bashid Samim</span>
                                                <p>Rashid left 50 mins ago</p>
                                            </div>
                                        </div>
                                    </li>
                                    <li class="dz-chat-user">
                                        <div class="d-flex bd-highlight">
                                            <div class="img_cont">
                                                <img src="images/avatar/1.jpg" class="rounded-circle user_img" alt="" />
                                                <span class="online_icon"></span>
                                            </div>
                                            <div class="user_info">
                                                <span>Breddie Ronan</span>
                                                <p>Kalid is online</p>
                                            </div>
                                        </div>
                                    </li>
                                    <li class="dz-chat-user">
                                        <div class="d-flex bd-highlight">
                                            <div class="img_cont">
                                                <img src="images/avatar/2.jpg" class="rounded-circle user_img" alt="" />
                                                <span class="online_icon offline"></span>
                                            </div>
                                            <div class="user_info">
                                                <span>Ceorge Carson</span>
                                                <p>Taherah left 7 mins ago</p>
                                            </div>
                                        </div>
                                    </li>
                                    <li class="name-first-letter">D</li>
                                    <li class="dz-chat-user">
                                        <div class="d-flex bd-highlight">
                                            <div class="img_cont">
                                                <img src="images/avatar/3.jpg" class="rounded-circle user_img" alt="" />
                                                <span class="online_icon"></span>
                                            </div>
                                            <div class="user_info">
                                                <span>Darry Parker</span>
                                                <p>Sami is online</p>
                                            </div>
                                        </div>
                                    </li>
                                    <li class="dz-chat-user">
                                        <div class="d-flex bd-highlight">
                                            <div class="img_cont">
                                                <img src="images/avatar/4.jpg" class="rounded-circle user_img" alt="" />
                                                <span class="online_icon offline"></span>
                                            </div>
                                            <div class="user_info">
                                                <span>Denry Hunter</span>
                                                <p>Nargis left 30 mins ago</p>
                                            </div>
                                        </div>
                                    </li>
                                    <li class="name-first-letter">J</li>
                                    <li class="dz-chat-user">
                                        <div class="d-flex bd-highlight">
                                            <div class="img_cont">
                                                <img src="images/avatar/5.jpg" class="rounded-circle user_img" alt="" />
                                                <span class="online_icon offline"></span>
                                            </div>
                                            <div class="user_info">
                                                <span>Jack Ronan</span>
                                                <p>Rashid left 50 mins ago</p>
                                            </div>
                                        </div>
                                    </li>
                                    <li class="dz-chat-user">
                                        <div class="d-flex bd-highlight">
                                            <div class="img_cont">
                                                <img src="images/avatar/1.jpg" class="rounded-circle user_img" alt="" />
                                                <span class="online_icon"></span>
                                            </div>
                                            <div class="user_info">
                                                <span>Jacob Tucker</span>
                                                <p>Kalid is online</p>
                                            </div>
                                        </div>
                                    </li>
                                    <li class="dz-chat-user">
                                        <div class="d-flex bd-highlight">
                                            <div class="img_cont">
                                                <img src="images/avatar/2.jpg" class="rounded-circle user_img" alt="" />
                                                <span class="online_icon offline"></span>
                                            </div>
                                            <div class="user_info">
                                                <span>James Logan</span>
                                                <p>Taherah left 7 mins ago</p>
                                            </div>
                                        </div>
                                    </li>
                                    <li class="dz-chat-user">
                                        <div class="d-flex bd-highlight">
                                            <div class="img_cont">
                                                <img src="images/avatar/3.jpg" class="rounded-circle user_img" alt="" />
                                                <span class="online_icon"></span>
                                            </div>
                                            <div class="user_info">
                                                <span>Joshua Weston</span>
                                                <p>Sami is online</p>
                                            </div>
                                        </div>
                                    </li>
                                    <li class="name-first-letter">O</li>
                                    <li class="dz-chat-user">
                                        <div class="d-flex bd-highlight">
                                            <div class="img_cont">
                                                <img src="images/avatar/4.jpg" class="rounded-circle user_img" alt="" />
                                                <span class="online_icon offline"></span>
                                            </div>
                                            <div class="user_info">
                                                <span>Oliver Acker</span>
                                                <p>Nargis left 30 mins ago</p>
                                            </div>
                                        </div>
                                    </li>
                                    <li class="dz-chat-user">
                                        <div class="d-flex bd-highlight">
                                            <div class="img_cont">
                                                <img src="images/avatar/5.jpg" class="rounded-circle user_img" alt="" />
                                                <span class="online_icon offline"></span>
                                            </div>
                                            <div class="user_info">
                                                <span>Oscar Weston</span>
                                                <p>Rashid left 50 mins ago</p>
                                            </div>
                                        </div>
                                    </li>
                                </ul>
                            </div>
                        </div>
                        <div class="card chat dz-chat-history-box d-none">
                            <div class="card-header chat-list-header text-center">
                                <a href="javascript:void(0);" class="dz-chat-history-back">
                                    <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"
                                        width="18px" height="18px" viewBox="0 0 24 24" version="1.1">
                                        <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                            <polygon points="0 0 24 0 24 24 0 24" />
                                            <rect fill="#000000" opacity="0.3"
                                                transform="translate(15.000000, 12.000000) scale(-1, 1) rotate(-90.000000) translate(-15.000000, -12.000000) "
                                                x="14" y="7" width="2" height="10" rx="1" />
                                            <path
                                                d="M3.7071045,15.7071045 C3.3165802,16.0976288 2.68341522,16.0976288 2.29289093,15.7071045 C1.90236664,15.3165802 1.90236664,14.6834152 2.29289093,14.2928909 L8.29289093,8.29289093 C8.67146987,7.914312 9.28105631,7.90106637 9.67572234,8.26284357 L15.6757223,13.7628436 C16.0828413,14.136036 16.1103443,14.7686034 15.7371519,15.1757223 C15.3639594,15.5828413 14.7313921,15.6103443 14.3242731,15.2371519 L9.03007346,10.3841355 L3.7071045,15.7071045 Z"
                                                fill="#000000" fill-rule="nonzero"
                                                transform="translate(9.000001, 11.999997) scale(-1, -1) rotate(90.000000) translate(-9.000001, -11.999997) " />
                                        </g>
                                    </svg>
                                </a>
                                <div>
                                    <h6 class="mb-1">Chat with Khelesh</h6>
                                    <p class="mb-0 text-success">Online</p>
                                </div>
                                <div class="dropdown">
                                    <a href="javascript:void(0);" data-bs-toggle="dropdown" aria-expanded="false"><svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            xmlns:xlink="http://www.w3.org/1999/xlink" width="18px" height="18px"
                                            viewBox="0 0 24 24" version="1.1">
                                            <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                                <rect x="0" y="0" width="24" height="24" />
                                                <circle fill="#000000" cx="5" cy="12" r="2" />
                                                <circle fill="#000000" cx="12" cy="12" r="2" />
                                                <circle fill="#000000" cx="19" cy="12" r="2" />
                                            </g>
                                        </svg></a>
                                    <ul class="dropdown-menu dropdown-menu-start">
                                        <li class="dropdown-item"><i class="fa fa-user-circle text-primary me-2"></i>
                                            View profile</li>
                                        <li class="dropdown-item"><i class="fa fa-users text-primary me-2"></i> Add to
                                            friends</li>
                                        <li class="dropdown-item"><i class="fa fa-plus text-primary me-2"></i> Add to
                                            group</li>
                                        <li class="dropdown-item"><i class="fa fa-ban text-primary me-2"></i> Block
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <div class="card-body msg_card_body dz-scroll" id="DZ_W_Contacts_Body3">
                                <div class="d-flex justify-content-start mb-4">
                                    <div class="img_cont_msg">
                                        <img src="images/avatar/1.jpg" class="rounded-circle user_img_msg" alt="" />
                                    </div>
                                    <div class="msg_cotainer">
                                        Hi, how are you samim?
                                        <span class="msg_time">8:40 AM, Today</span>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-end mb-4">
                                    <div class="msg_cotainer_send">
                                        Hi Khalid i am good tnx how about you?
                                        <span class="msg_time_send">8:55 AM, Today</span>
                                    </div>
                                    <div class="img_cont_msg">
                                        <img src="images/avatar/2.jpg" class="rounded-circle user_img_msg" alt="" />
                                    </div>
                                </div>
                                <div class="d-flex justify-content-start mb-4">
                                    <div class="img_cont_msg">
                                        <img src="images/avatar/1.jpg" class="rounded-circle user_img_msg" alt="" />
                                    </div>
                                    <div class="msg_cotainer">
                                        I am good too, thank you for your chat template
                                        <span class="msg_time">9:00 AM, Today</span>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-end mb-4">
                                    <div class="msg_cotainer_send">
                                        You are welcome
                                        <span class="msg_time_send">9:05 AM, Today</span>
                                    </div>
                                    <div class="img_cont_msg">
                                        <img src="images/avatar/2.jpg" class="rounded-circle user_img_msg" alt="" />
                                    </div>
                                </div>
                                <div class="d-flex justify-content-start mb-4">
                                    <div class="img_cont_msg">
                                        <img src="images/avatar/1.jpg" class="rounded-circle user_img_msg" alt="" />
                                    </div>
                                    <div class="msg_cotainer">
                                        I am looking for your next templates
                                        <span class="msg_time">9:07 AM, Today</span>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-end mb-4">
                                    <div class="msg_cotainer_send">
                                        Ok, thank you have a good day
                                        <span class="msg_time_send">9:10 AM, Today</span>
                                    </div>
                                    <div class="img_cont_msg">
                                        <img src="images/avatar/2.jpg" class="rounded-circle user_img_msg" alt="" />
                                    </div>
                                </div>
                                <div class="d-flex justify-content-start mb-4">
                                    <div class="img_cont_msg">
                                        <img src="images/avatar/1.jpg" class="rounded-circle user_img_msg" alt="" />
                                    </div>
                                    <div class="msg_cotainer">
                                        Bye, see you
                                        <span class="msg_time">9:12 AM, Today</span>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-start mb-4">
                                    <div class="img_cont_msg">
                                        <img src="images/avatar/1.jpg" class="rounded-circle user_img_msg" alt="" />
                                    </div>
                                    <div class="msg_cotainer">
                                        Hi, how are you samim?
                                        <span class="msg_time">8:40 AM, Today</span>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-end mb-4">
                                    <div class="msg_cotainer_send">
                                        Hi Khalid i am good tnx how about you?
                                        <span class="msg_time_send">8:55 AM, Today</span>
                                    </div>
                                    <div class="img_cont_msg">
                                        <img src="images/avatar/2.jpg" class="rounded-circle user_img_msg" alt="" />
                                    </div>
                                </div>
                                <div class="d-flex justify-content-start mb-4">
                                    <div class="img_cont_msg">
                                        <img src="images/avatar/1.jpg" class="rounded-circle user_img_msg" alt="" />
                                    </div>
                                    <div class="msg_cotainer">
                                        I am good too, thank you for your chat template
                                        <span class="msg_time">9:00 AM, Today</span>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-end mb-4">
                                    <div class="msg_cotainer_send">
                                        You are welcome
                                        <span class="msg_time_send">9:05 AM, Today</span>
                                    </div>
                                    <div class="img_cont_msg">
                                        <img src="images/avatar/2.jpg" class="rounded-circle user_img_msg" alt="" />
                                    </div>
                                </div>
                                <div class="d-flex justify-content-start mb-4">
                                    <div class="img_cont_msg">
                                        <img src="images/avatar/1.jpg" class="rounded-circle user_img_msg" alt="" />
                                    </div>
                                    <div class="msg_cotainer">
                                        I am looking for your next templates
                                        <span class="msg_time">9:07 AM, Today</span>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-end mb-4">
                                    <div class="msg_cotainer_send">
                                        Ok, thank you have a good day
                                        <span class="msg_time_send">9:10 AM, Today</span>
                                    </div>
                                    <div class="img_cont_msg">
                                        <img src="images/avatar/2.jpg" class="rounded-circle user_img_msg" alt="" />
                                    </div>
                                </div>
                                <div class="d-flex justify-content-start mb-4">
                                    <div class="img_cont_msg">
                                        <img src="images/avatar/1.jpg" class="rounded-circle user_img_msg" alt="" />
                                    </div>
                                    <div class="msg_cotainer">
                                        Bye, see you
                                        <span class="msg_time">9:12 AM, Today</span>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer type_msg">
                                <div class="input-group">
                                    <textarea class="form-control overflow-hidden"
                                        placeholder="Type your message..."></textarea>
                                    <div class="input-group-append">
                                        <button type="button" class="btn btn-primary"><i
                                                class="fa fa-location-arrow"></i></button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div> -->
                    <!-- <div class="tab-pane fade" id="alerts" role="tabpanel">
                        <div class="card mb-sm-3 mb-md-0 contacts_card">
                            <div class="card-header chat-list-header text-center">
                                <a href="javascript:void(0);"><svg xmlns="http://www.w3.org/2000/svg"
                                        xmlns:xlink="http://www.w3.org/1999/xlink" width="18px" height="18px"
                                        viewBox="0 0 24 24" version="1.1">
                                        <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                            <rect x="0" y="0" width="24" height="24" />
                                            <circle fill="#000000" cx="5" cy="12" r="2" />
                                            <circle fill="#000000" cx="12" cy="12" r="2" />
                                            <circle fill="#000000" cx="19" cy="12" r="2" />
                                        </g>
                                    </svg></a>
                                <div>
                                    <h6 class="mb-1">Notications</h6>
                                    <p class="mb-0">Show All</p>
                                </div>
                                <a href="javascript:void(0);"><svg xmlns="http://www.w3.org/2000/svg"
                                        xmlns:xlink="http://www.w3.org/1999/xlink" width="18px" height="18px"
                                        viewBox="0 0 24 24" version="1.1">
                                        <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                            <rect x="0" y="0" width="24" height="24" />
                                            <path
                                                d="M14.2928932,16.7071068 C13.9023689,16.3165825 13.9023689,15.6834175 14.2928932,15.2928932 C14.6834175,14.9023689 15.3165825,14.9023689 15.7071068,15.2928932 L19.7071068,19.2928932 C20.0976311,19.6834175 20.0976311,20.3165825 19.7071068,20.7071068 C19.3165825,21.0976311 18.6834175,21.0976311 18.2928932,20.7071068 L14.2928932,16.7071068 Z"
                                                fill="#000000" fill-rule="nonzero" opacity="0.3" />
                                            <path
                                                d="M11,16 C13.7614237,16 16,13.7614237 16,11 C16,8.23857625 13.7614237,6 11,6 C8.23857625,6 6,8.23857625 6,11 C6,13.7614237 8.23857625,16 11,16 Z M11,18 C7.13400675,18 4,14.8659932 4,11 C4,7.13400675 7.13400675,4 11,4 C14.8659932,4 18,7.13400675 18,11 C18,14.8659932 14.8659932,18 11,18 Z"
                                                fill="#000000" fill-rule="nonzero" />
                                        </g>
                                    </svg></a>
                            </div>
                            <div class="card-body contacts_body p-0 dz-scroll" id="DZ_W_Contacts_Body1">
                                <ul class="contacts">
                                    <li class="name-first-letter">SEVER STATUS</li>
                                    <li class="active">
                                        <div class="d-flex bd-highlight">
                                            <div class="img_cont primary">KK</div>
                                            <div class="user_info">
                                                <span>David Nester Birthday</span>
                                                <p class="text-primary">Today</p>
                                            </div>
                                        </div>
                                    </li>
                                    <li class="name-first-letter">SOCIAL</li>
                                    <li>
                                        <div class="d-flex bd-highlight">
                                            <div class="img_cont success">RU</div>
                                            <div class="user_info">
                                                <span>Perfection Simplified</span>
                                                <p>Jame Smith commented on your status</p>
                                            </div>
                                        </div>
                                    </li>
                                    <li class="name-first-letter">SEVER STATUS</li>
                                    <li>
                                        <div class="d-flex bd-highlight">
                                            <div class="img_cont primary">AU</div>
                                            <div class="user_info">
                                                <span>AharlieKane</span>
                                                <p>Sami is online</p>
                                            </div>
                                        </div>
                                    </li>
                                    <li>
                                        <div class="d-flex bd-highlight">
                                            <div class="img_cont info">MO</div>
                                            <div class="user_info">
                                                <span>Athan Jacoby</span>
                                                <p>Nargis left 30 mins ago</p>
                                            </div>
                                        </div>
                                    </li>
                                </ul>
                            </div>
                            <div class="card-footer"></div>
                        </div>
                    </div> -->
                    <div class="tab-pane fade active show" id="notes">
                        <div class="card mb-sm-3 mb-md-0 note_card">
                            <div class="card-header chat-list-header text-center">
                                <!-- Button trigger modal -->
                                <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                                    data-bs-target="#exampleModal">
                                    Add Note
                                </button>

                                <!-- Modal -->
                                <div class="modal fade" id="exampleModal" tabindex="-1"
                                    aria-labelledby="exampleModalLabel" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="exampleModalLabel">Add Note</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                    aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <form action="" method="post" id="quoteForm">
                                                    <div class="row">
                                                        <div class="col-12">
                                                            <label for="">Note</label>
                                                            <input type="text" id="note" name="note"
                                                                placeholder="ADD NOTE" class="form-control">
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary"
                                                            data-bs-dismiss="modal">Close</button>
                                                        <button type="submit" name="btnSave"
                                                            class="btn btn-primary">Save
                                                            changes</button>
                                                    </div>

                                                </form>
                                            </div>

                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body contacts_body p-0 dz-scroll" id="DZ_W_Contacts_Body2">
                                <ul class="contacts">
                                    <?php  


                                    $user_id = $_SESSION['id'];
                                        $stmt = $mysqli->prepare("select * from note where user_id = ?");
                                        $stmt->bind_param("i" , $user_id);
                                        $stmt->execute();
                                        $res = $stmt->get_result();
                                        while($row = $res->fetch_assoc()):
                                     ?>
                                    <li class="active">
                                        <div class="d-flex bd-highlight">
                                            <div class="user_info">
                                                <div class="tooltip-wrap">
                                                    <span><?php echo htmlspecialchars($row['note']); ?></span>
                                                    <div class="custom-tooltip">
                                                        <?php echo htmlspecialchars($row['note']); ?></div>
                                                </div>
                                                <p><?php echo date('Y-m-d', strtotime($row['created_at'])) ?></p>
                                            </div>

                                            <div class="ms-auto">
                                                <!-- <a href="javascript:void(0);"
                                                    class="btn btn-primary btn-xs sharp me-1"><i
                                                        class="fas fa-pencil-alt"></i></a> -->
                                                <a href="javascript:void(0);" data-id="<?= $row['id'] ?>"
                                                    class="btn btn-danger btn-xs sharp deleteNote">
                                                    <i class="fa fa-trash"></i>
                                                </a>

                                            </div>
                                        </div>
                                    </li>
                                    <?php endwhile;  ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <script>
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        tooltipTriggerList.map(function(tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        })
        </script>