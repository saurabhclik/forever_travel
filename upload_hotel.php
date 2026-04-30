<?php

include('config.php');

// Enable MySQLi exceptions and show errors for debugging
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
ini_set('display_errors', 1);
error_reporting(E_ALL);



if (isset($_POST['submit'])) {
    try {
        if (!is_uploaded_file($_FILES['csv_file']['tmp_name'])) {
            throw new Exception('CSV file not uploaded.');
        }

        mysqli_set_charset($mysqli, 'utf8mb4');

        $file = fopen($_FILES['csv_file']['tmp_name'], 'r');
        if (!$file) {
            throw new Exception('Failed to open uploaded CSV file.');
        }

        $header = fgetcsv($file); // Skip and capture the header
        $imported = 0;
        $skipped = 0;
       

        while (($row = fgetcsv($file)) !== FALSE) {

            $hotel_name        = trim(mysqli_real_escape_string($mysqli, $row[0]));
            $hotel_description = trim(mysqli_real_escape_string($mysqli, $row[1]));
            $star_rating       = trim(mysqli_real_escape_string($mysqli, $row[2]));
            $address           = trim(mysqli_real_escape_string($mysqli, $row[3]));
            $city              = trim(mysqli_real_escape_string($mysqli, $row[4]));
            $state             = trim(mysqli_real_escape_string($mysqli, $row[5]));
            $country           = trim(mysqli_real_escape_string($mysqli, $row[6]));
            $zipcode           = trim(mysqli_real_escape_string($mysqli, $row[7]));
            $latitude          = trim(mysqli_real_escape_string($mysqli, $row[8]));
            $longitude         = trim(mysqli_real_escape_string($mysqli, $row[9]));
            $phone_number      = trim(mysqli_real_escape_string($mysqli, $row[10]));
            $email             = trim(mysqli_real_escape_string($mysqli, $row[11]));
            $website_url       = trim(mysqli_real_escape_string($mysqli, $row[12]));
            $status            = isset($row[13]) ? trim(mysqli_real_escape_string($mysqli, $row[13])) : 'Active';

           
           
            $check_sql = $mysqli->prepare("
                SELECT hotel_id FROM hotels 
                WHERE hotel_name = ? AND address = ?
            ");
            $check_sql->bind_param("ss", $hotel_name, $address);
            $check_sql->execute();
            $result = $check_sql->get_result();

            if ($result->num_rows === 0) {
                $insert_sql = $mysqli->prepare("
                    INSERT INTO hotels (
                        hotel_name, hotel_description, star_rating, address, city, state, country,
                        zipcode, latitude, longitude, phone_number, email, website_url, status
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $insert_sql->bind_param(
                    "ssisssssssssss",
                    $hotel_name, $hotel_description, $star_rating, $address,
                    $city, $state, $country, $zipcode, $latitude, $longitude,
                    $phone_number, $email, $website_url, $status
                );
                $insert_sql->execute();
                $insert_sql->close();
                $imported++;
                
            } else {
                $skipped++;
            }

            $check_sql->close();
        }

        fclose($file);

        $_SESSION['alert'] = [
            'title' => 'Import Finished',
            'text' => "$imported rows inserted, $skipped duplicates skipped.",
            'icon' => 'success'
        ];
        redirect('hotels.php');
       
    } catch (Exception $e) {
        $_SESSION['alert'] = [
            'title' => 'Import Error',
            'text' => 'Error: ' . $e->getMessage(),
            'icon' => 'error'
        ];
        redirect('hotels.php');
    }
}
?>
