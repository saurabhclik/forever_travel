<?php

include('config.php');

if (isset($_POST['submit'])) {

    if (is_uploaded_file($_FILES['csv_file']['tmp_name'])) {

        mysqli_set_charset($mysqli, 'utf8mb4'); 

        $file = fopen($_FILES['csv_file']['tmp_name'], 'r');
        fgetcsv($file); // skip header

        $imported = 0;
        $skipped = 0;

        while (($row = fgetcsv($file)) !== FALSE) {
            if (count($row) >= 7) {

                $country     = trim(mysqli_real_escape_string($mysqli, $row[0]));
                $state       = trim(mysqli_real_escape_string($mysqli, $row[1]));
                $city        = trim(mysqli_real_escape_string($mysqli, $row[2]));
                $place       = trim(mysqli_real_escape_string($mysqli, $row[3]));
                $title       = trim(mysqli_real_escape_string($mysqli, $row[4]));
                $description = trim(mysqli_real_escape_string($mysqli, $row[5]));
                $note        = trim(mysqli_real_escape_string($mysqli, $row[7]));

                // check for duplicates
                $check_sql = $mysqli->prepare("SELECT id FROM country_landmarks WHERE country = ? AND state = ? AND city = ? AND place = ? AND title = ?");
                $check_sql->bind_param("sssss", $country, $state, $city, $place, $title);
                $check_sql->execute();
                $result = $check_sql->get_result();

                if ($result->num_rows === 0) {
                    $insert_sql = $mysqli->prepare("
                        INSERT INTO country_landmarks (country, state, city, place, title, description, note)
                        VALUES (?, ?, ?, ?, ?, ?, ?)
                    ");
                    $insert_sql->bind_param("sssssss", $country, $state, $city, $place, $title, $description, $note);
                    $insert_sql->execute();
                    $insert_sql->close();
                    $imported++;
                } else {
                    $skipped++;
                }

                $check_sql->close();
            }
        }

        fclose($file);

        $_SESSION['alert'] = [
            'title' => 'Import Finished',
            'text' => "$imported rows inserted, $skipped duplicates skipped.",
            'icon' => 'success'
        ];
        redirect("country_landmarks.php");

    } else {
        $_SESSION['alert'] = [
            'title' => 'Upload Failed',
            'text' => 'CSV file not uploaded.',
            'icon' => 'error'
        ];
        redirect("country_landmarks.php");
    }
}
?>

