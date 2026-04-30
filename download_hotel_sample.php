<?php

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="hotels_sample.csv"');

$output = fopen('php://output', 'w');

// Header row
fputcsv($output, [
    'hotel_name',
    'hotel_description',
    'star_rating',
    'address',
    'city',
    'state',
    'country',
    'zipcode',
    'latitude',
    'longitude',
    'phone_number',
    'email',
    'website_url',
    'status'
]);

fclose($output);
exit;
?>
