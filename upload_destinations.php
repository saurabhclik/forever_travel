<?php 

include('config.php');

if (isset($_POST['submit'])) {

    if (is_uploaded_file($_FILES['csv_file']['tmp_name'])) {
        $file = fopen($_FILES['csv_file']['tmp_name'], 'r');
        fgetcsv($file);
        while (($row = fgetcsv($file)) !== FALSE) {
            $country_name = mysqli_real_escape_string($mysqli, $row[0]);

            $check_sql = "SELECT id FROM destinations WHERE name = '$country_name'";
            $check_result = mysqli_query($mysqli, $check_sql);

            if (mysqli_num_rows($check_result) == 0) {
                $insert_sql = "INSERT INTO destinations (name) VALUES ('$country_name')";
                mysqli_query($mysqli, $insert_sql);
            }
        }
        fclose($file);
        
         redirect("destination.php");
    } else {
         redirect("destination.php");
    }
}



?>