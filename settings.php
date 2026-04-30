<?php
    include "Layouts/Header.php";
    include "Layouts/Sidebar.php";
    if (isset($_POST['btnimg_1'])) 
    {
        $upload_dir = 'logo/';
        $file_url = "";
        if (isset($_FILES['file']['name'])) 
        {
            $pic_name = $_FILES["file"]["name"];
            $pic_tmp_name = $_FILES["file"]["tmp_name"];
            $error = $_FILES["file"]["error"]; 
            {
                $random_name = rand(1000, 10000000000000000) . "-" . $pic_name;
                $upload_name = $upload_dir . strtolower($random_name);
                $upload_name = preg_replace('/\s+/', '-', $upload_name);
                try 
                {
                    $res = move_uploaded_file($pic_tmp_name, $upload_name);
                    $file_url = $upload_name;
                } 
                catch (Exception $e) 
                {
                    echo $e->getMessage();
                }
            }
        } 
        else 
        {
            echo '<script>alert("Image not move");</script>';
            return;
        }
        try 
        {
            $stmt = $mysqli->prepare("UPDATE settings set img_1=? WHERE id=1");
            $stmt->bind_param("s", $file_url);
            $stmt->execute();
            $stmt->close();
            alert("Save Successfully", "success", "success");
            redirect("settings.php");
        } 
        catch (Exception $e) 
        {
            alert($e->getMessage(), "error", "error");
            redirect("settings.php");
        }
    }
?>

<div class="content-body">
    <div class="container-fluid">
        <!-- row -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body border-bottom">
                        <form method="post" enctype="multipart/form-data">
                            <div class="row">
                                <div class="col-md-6">
                                    <input type="file" name="file" class="form-control">
                                </div>
                                <div class="col-md-6">
                                    <button type="submit" name="btnimg_1" class="btn btn-primary">Save</button>
                                </div>

                            </div>
                        </form>                  
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="example" class="display" style="min-width: 845px">
                                <thead>
                                    <tr>
                                        <th>Image 1</th>
                                    </tr>
                                </thead>
                                <tbody>                              
                                <?php
                                    $stmt = $mysqli->prepare("SELECT * from  settings ");
                                    $stmt->execute();
                                    $res = $stmt->get_result();
                                    $sno = 1;
                                    while ($row = $res->fetch_assoc()) 
                                    {
                                ?>
                                    <tr>
                                        <td>
                                            <img src=" <?= $row['img_1']; ?>" width="85px">
                                        </td>                               
                                    </tr>
                                <?php
                                    }
                                ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include "Layouts/Footer.php"  ?>