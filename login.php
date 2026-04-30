<?php
    include("config.php");
    if (isset($_COOKIE['token'])) 
    {
        $stmt = $mysqli->prepare("SELECT * FROM `users` WHERE token=?");
        $stmt->bind_param("s", $_COOKIE['token']);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if (($user)) 
        {
            switch ($user['role']) 
            {
                case 'admin':
                    alert('Successfully login.', 'success', "success");
                    redirect('index.php');
                    break;
                case 'Staff':
                    alert('Successfully login.', 'success', "success");
                    redirect('index.php');
                    break;
                case 'Team Manager':
                    alert('Successfully login.', 'success', "success");
                    redirect('index.php');
                    break;
                default:
                    alert('invalid user role.', 'error');
                    redirect('login.php');
                    break;
            }
        }
    }  
    if (isset($_POST['btnLogin'])) 
    {
        $stmt = $mysqli->prepare("SELECT * FROM `users` WHERE   username=? and `password`=? and status = '1'");
        $stmt->bind_param("ss", $_POST['username'], $_POST['password']);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();

        $stmt->close();
        if (empty($user))
        {
            $_SESSION['alert'] = [
                'title' => 'Login Failed',
                'text' => 'Invalid Email or Password!',
                'icon' => 'error'
            ];
            redirect('login.php');
        }
        else 
        {
            $_SESSION['user'] = $user['role'];
            $_SESSION['role_id'] = $user['role_id'];
            $_SESSION['id'] = $user['id'];
            $_SESSION['token'] = token(25);
            $_SESSION['last_login'] = $user['last_login'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['last_ip'] = $user['last_ip'];
            setcookie('token', $_SESSION['token'], time() + 86400 * 1);
            $stmt = $mysqli->prepare("UPDATE `users` set token=?,last_login=now(),last_ip=? WHERE  id=" . $user['id'] . "   ");
            $stmt->bind_param("ss", $_SESSION['token'], $_SERVER['REMOTE_ADDR']);
            $stmt->execute();
            $stmt->close();

            $child_ids = [];
    $iterable = [];
    array_push($iterable, $_SESSION['id']);

    while (sizeOf($iterable) > 0) {
      $iterable = implode(', ', $iterable);
      array_push($child_ids, $iterable);
      $stmt = $mysqli->prepare("SELECT id from users where parent_id in (" . $iterable . ")");
      $stmt->execute();
      $result = $stmt->get_result();
      $iterable = [];
      while ($row = $result->fetch_assoc())
        array_push($iterable, $row['id']);
    }
    $_SESSION['child_ids'] = implode(', ', $child_ids);

    // echo "<pre>";
    // print_r($_SESSION['child_ids']); exit;


            switch ($user['role']) 
            {
                case 'admin':
                    $_SESSION['alert'] = [
                        'title' => 'Login Success',
                        'text' => 'Welcome back!',
                        'icon' => 'success'
                    ];
                    redirect('index.php');
                    break;
                case 'Staff':
                 
                     $_SESSION['alert'] = [
                        'title' => 'Login Success',
                        'text' => 'Welcome back!',
                        'icon' => 'success'
                    ];
                    redirect('index.php');
                    break;
                case 'Team Manager':
                 
                     $_SESSION['alert'] = [
                        'title' => 'Login Success',
                        'text' => 'Welcome back!',
                        'icon' => 'success'
                    ];
                    redirect('index.php');
                    break;
                default:
                    alert('invalid user role.', 'error');
                    redirect('login.php');
                    break;
            }
        }
    }
    $stmt = $mysqli->prepare("SELECT * FROM `settings` WHERE   id=1");
    $stmt->execute();
    $setting = $stmt->get_result()->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en" class="h-100">
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
    <title>Forever Admin Dashboard</title>
    
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="shortcut icon" type="image/png" href="images/company_logo/logo1.png" />
    <link href="vendor/bootstrap-select/dist/css/bootstrap-select.min.css" rel="stylesheet">
    <link href="vendor/jquery-nice-select/css/nice-select.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">

</head>

<body class="vh-100">
    <div class="authincation h-100">
        <div class="container h-100">
            <div class="row justify-content-center h-100 align-items-center">
                <div class="col-md-6">
                    <div class="authincation-content">
                        <div class="row no-gutters">
                            <div class="col-xl-12">
                                <div class="auth-form">
                                    <div class="text-center mb-3">
                                      <img src="images/company_logo/logo1.png" alt="" height="100px">
                                    </div>
                                    <h4 class="text-center mb-4">Sign in your account</h4>
                                    <form action="" Method="post">
                                        <div class="mb-3">
                                            <label class="mb-1"><strong>User Name</strong></label>
                                            <input type="text" class="form-control" name="username">
                                        </div>
                                        <div class="mb-3">
                                            <label class="mb-1"><strong>Password</strong></label>
                                            <input type="password" class="form-control" name="password" value="">
                                        </div>
                                        <div class="form-row d-flex justify-content-between mt-4 mb-2">
                                            <div class="mb-3">
                                                <div class="form-check custom-checkbox ms-0">
                                                    <input type="checkbox" class="form-check-input"
                                                        id="basic_checkbox_1">
                                                    <label class="form-check-label" for="basic_checkbox_1">Remember my
                                                        preference</label>
                                                </div>
                                            </div>
                                            <div class="mb-3 mt-1">
                                                <a href="page-forgot-password.html">Forgot Password?</a>
                                            </div>
                                        </div>
                                        <div class="text-center">
                                            <button type="submit" name="btnLogin" class="btn btn-primary btn-block">Sign
                                                Me In</button>
                                        </div>
                                    </form>
                                    <div class="new-account mt-3">
                                        <p class="mb-0 mb-sm-3">Don't have an account? <a class="text-primary"
                                                href="page-register.html">Sign up</a></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="vendor/global/global.min.js"></script>
    <script src="js/custom.min.js"></script>
    <script src="js/deznav-init.js"></script>
    <script src="vendor/bootstrap-select/dist/js/bootstrap-select.min.js"></script>
    <script src="vendor/jquery-nice-select/js/jquery.nice-select.min.js"></script>
    <script src="js/demo.js"></script>
    <script src="js/styleSwitcher.js"></script>
    <?php
        if (isset($_SESSION['alert'])) 
        {
            $alert = $_SESSION['alert'];
            echo "
            <script>
                Swal.fire({
                    title: '{$alert['title']}',
                    text: '{$alert['text']}',
                    icon: '{$alert['icon']}',
                    confirmButtonText: 'OK'
                });
            </script>
            ";
            unset($_SESSION['alert']);
        }
    ?>
</body>
</html>