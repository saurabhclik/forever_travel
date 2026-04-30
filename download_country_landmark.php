<?php

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="country_landmark.csv"');


$output = fopen('php://output', 'w');


fputcsv($output, ['country','state','city','place','title','description','note']); 


fclose($output);
exit;
?>
