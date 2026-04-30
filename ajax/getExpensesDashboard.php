<?php

include("../config.php");

$where = " WHERE 1 ";
if(!empty($_POST['user_id'])){
    $where .= " AND a.user_id = " . intval($_POST['user_id']);
}
if(!empty($_POST['customer_id'])){
    $where .= " AND a.ids = " . intval($_POST['customer_id']);
}
if(!empty($_POST['status'])){
    $where .= " AND a.status = '" . $mysqli->real_escape_string($_POST['status']) . "'";
}

$query = "SELECT a.*, b.name as customer, u.name as user 
          FROM expenses a 
          LEFT JOIN customers b ON a.ids = b.id 
          JOIN users u ON a.user_id = u.id 
          $where 
          ORDER BY a.id DESC";

$result = $mysqli->query($query);

$sno = 1;
if($result->num_rows > 0){
    while($row = $result->fetch_assoc()){
        $fileLink = empty($row['file']) 
            ? 'There is no file upload' 
            : '<a href="'.BASE_PATH.'images/invoices/'.$row['file'].'" target="_blank">File</a>';

        echo '<tr>
                <td>'.$sno++.'</td>
                <td>'.$row['user'].'</td>
                <td>'.$fileLink.'</td>
                <td>'.$row['customer'].'</td>
                <td>'.$row['name'].'</td>
                <td>'.$row['expense_category'].'</td>
                <td>'.$row['note'].'</td>
                <td>'.$row['amount'].'</td>
              </tr>';
    }
}else{
    echo '<tr><td colspan="8">No expenses found.</td></tr>';
}
?>
