<?php
    include "Layouts/Header.php";
    include "Layouts/Sidebar.php";
    if (isset($_POST['btnsubmit'])) 
    {
        $error = Null;
        if (strlen($_POST['mobile']) != 10)
            $error = "Mobile number should be 10 digit";
        if (empty($error)) 
        {
            try 
            {
                $stmt = $mysqli->prepare("UPDATE  users set name=?,email=?,mobile=? where id=?");
                $stmt->bind_param("sssi",  $_POST['name'], $_POST['email'], $_POST['mobile'], $user['id']);
                $stmt->execute();
                alert("Update Successfully", "success", "success");
                redirect("profile.php");
            } 
            catch (Exception $e) 
            {
                alert($e->getMessage(), "error", "error");
                redirect("profile.php");
            }
        } 
        else 
        {
            alert($error, "error", "error");
            redirect("profile.php");
        }
    }

    if (isset($_POST['btnchange'])) 
    {
        $error = Null;
        if (empty($_POST['npassword']))
            $error = "Enter Current password";
        if ($_POST['npassword'] != $_POST['cnpassword'])
            $error = "Password current password Does not match.";
        $stmt = $mysqli->prepare("SELECT * from users where password=? and id=?");
        $stmt->bind_param("si",  $_POST['cpassword'], $user['id']);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();
        if (empty($res))
            $error = "Current password does not match";
        if (empty($error)) 
        {
            try 
            {
                $stmt = $mysqli->prepare("UPDATE  users set password=? where  id=?");
                $stmt->bind_param("si",  $_POST['cnpassword'], $user['id']);
                $stmt->execute();
                alert("Update Successfully", "success", "success");

                redirect("profile.php");
            } 
            catch (Exception $e) 
            {
                alert($e->getMessage(), "error", "error");
                redirect("profile.php");
            }
        } 
        else 
        {
            alert($error, "error", "error");
            redirect("profile.php");
        }
    }


    if (isset($_POST['BtnDelete'])) 
    {
        if (empty($_POST['password'])) 
        {
            alert("Enter Password", "error", "error");
            redirect("?");
        }
        $stmt = $mysqli->prepare("SELECT * from users where password=? and id=?");
        $stmt->bind_param("si",  $_POST['password'], $user['id']);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();
        if (empty($res)) 
        {
            alert("Incorrect Password.", "error", "error");
            redirect("?");
        } 
        else 
        {
            $stmt = $mysqli->prepare("DELETE from expenses where build='Not Billed'");
            //  $stmt->bind_param("si",  $_POST['password'], $user['id']);
            $stmt->execute();
            $stmt = $mysqli->prepare("DELETE from order_mst where is_billed='Not Billed'");
            //  $stmt->bind_param("si",  $_POST['password'], $user['id']);
            $stmt->execute();
            alert("Save Successfully.", "success", "success");
            redirect("?");
        }
    }
?>

<div class="content-body">
    <div class="container-fluid">
        <!-- row -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-12">
                                <div class="card mb-4">
                                    <div class="user-profile-header d-flex flex-column flex-sm-row text-sm-start text-center mb-4">
                                        <div class="flex-grow-1 mt-3">
                                            <div class="d-flex">
                                                <i class="fa fa-user-circle px-3" style="font-size:80px ;"></i>
                                                <div class="user-profile-info px-3">
                                                    <h4 cla>
                                                        <?= $user['name'] ?>
                                                    </h4>
                                                    <ul class="list-inline mb-0 d-flex align-items-center flex-wrap justify-content-sm-start justify-content-center gap-2">
                                                        <li class="list-inline-item fw-semibold">
                                                            <i class='bx bx-pen'></i>
                                                            <?= $user['role'] ?>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </div>
                                            <button type="button" class="btn btn-danger btn-sm float-end" data-bs-toggle="modal" data-bs-target="#modalId">SOS</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <ul class="nav nav-pills flex-column flex-sm-row mb-4">
                                    <li class="nav-item">
                                        <a class="nav-link active" href="profile.php">
                                            <i class='bx bx-user me-1'></i> Profile
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" href="team.php">
                                            <i class='bx bx-group me-1'></i> Teams
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-xl-4 col-lg-5 col-md-5">
                                <!-- About User -->
                                <div class="card mb-4">
                                    <div class="card-body">
                                        <small class="text-muted text-uppercase">About</small>
                                        <ul class="list-unstyled mb-4 mt-3">
                                            <li class="d-flex align-items-center mb-3"><i class="bx bx-user-circle"></i><span class="fw-semibold mx-2">Username:</span> <span><?= $user['username'] ?></span></li>
                                            <li class="d-flex align-items-center mb-3"><i class="bx bx-user"></i><span class="fw-semibold mx-2">Full Name:</span> <span><?= $user['name'] ?></span></li>
                                            <li class="d-flex align-items-center mb-3"><i class="bx bx-check"></i><span class="fw-semibold mx-2">Status:</span> <span>Active</span></li>
                                            <li class="d-flex align-items-center mb-3"><i class="bx bx-star"></i><span class="fw-semibold mx-2">Role:</span> <span><?= $user['role'] ?></span></li>
                                            <li class="d-flex align-items-center mb-3"><i class="bx bx-flag"></i><span class="fw-semibold mx-2">Country:</span> <span>India</span></li>
                                            <li class="d-flex align-items-center mb-3"><i class="bx bx-detail"></i><span class="fw-semibold mx-2">Language:</span> <span>English</span></li>
                                        </ul>
                                        <small class="text-muted text-uppercase">Contacts</small>
                                        <ul class="list-unstyled mb-4 mt-3">
                                            <li class="d-flex align-items-center mb-3"><i class="bx bx-phone"></i><span class="fw-semibold mx-2">Contact:</span> <span><?= $user['mobile'] ?></span></li>

                                            <li class="d-flex align-items-center mb-3"><i class="bx bx-envelope"></i><span class="fw-semibold mx-2">Email:</span> <span><?= $user['email'] ?></span></li>
                                        </ul>

                                    </div>
                                </div>

                            </div>
                            <div class="col-xl-8 col-lg-7 col-md-7">
                                <!-- Activity Timeline -->
                                <div class="card card-action mb-4">
                                    <div class="card-header align-items-center">
                                        <h5 class="card-action-title mb-0"><i class='bx bx-list-ul me-2'></i>Settings</h5>
                                    </div>
                                    <div class="card-body">
                                        <form class="needs-validation" novalidate method="POST" enctype="multipart/form-data">
                                            <div class="row">
                                                <div class="col-md-4">
                                                    <label>Name</label>
                                                    <input type="text" value="<?= $user['name'] ?>" class="form-control" name="name" id="name" required>
                                                </div>
                                                <div class="col-md-4">
                                                    <label>Number</label>
                                                    <input type="number" value="<?= $user['mobile'] ?>" class="form-control" name="mobile" id="mobile" required>
                                                </div>
                                                <div class="col-md-4">
                                                    <label>Email</label>
                                                    <input type="email" value="<?= $user['email'] ?>" class="form-control" name="email" id="email" required>
                                                </div>
                                                <div class="col-md-12 mt-3 text-center">
                                                    <button type="submit" class="btn btn-primary" name="btnsubmit" id="btnsubmit">Update</button>
                                                </div>
                                            </div>
                                        </form>
                                        <form class="needs-validation" novalidate method="POST" enctype="multipart/form-data">
                                            <div class="row mt-5">
                                                <div class="col-md-4">
                                                    <label>Current Password</label>
                                                    <input type="password" value="" class="form-control" name="cpassword" id="cpassword" required>
                                                </div>
                                                <div class="col-md-4">
                                                    <label>New Password</label>
                                                    <input type="password" class="form-control" name="npassword" id="npassword" required>
                                                </div>
                                                <div class="col-md-4">
                                                    <label>Retype New Password</label>
                                                    <input type="password" class="form-control" name="cnpassword" id="cnpassword" required>
                                                </div>
                                                <div class="col-md-12 mt-3 text-center">
                                                    <button type="submit" class="btn btn-primary" name="btnchange" id="btnchange">Update Password</button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal fade" id="modalId" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false" role="dialog" aria-labelledby="modalTitleId" aria-hidden="true">
                        <div class="modal-dialog " role="document">
                            <form method="POST">
                                <div class="modal-content">
                                    <div class="modal-header bg-danger">
                                        <h5 class="modal-title text-white" id="modalTitleId">
                                            Delete Not Billed
                                        </h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <h5>Are you sure you want to delete entry.</h5>
                                        <h5>This action can not be revoke. At any cost.</h5>
                                        <input type="password" name="password" class="form-control" placeholder="Enter Password" required>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                            Close
                                        </button>
                                        <button type="submit" name="BtnDelete" class="btn btn-danger">Yes, Delete</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include "Layouts/Footer.php"  ?>