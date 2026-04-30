<?php
// Set headers to force download
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="destination.csv"');

// Open the output stream
$output = fopen('php://output', 'w');

// Write the single header row
fputcsv($output, ['name']); // replace with your actual header title

// Close the output stream
fclose($output);
exit;
?>
