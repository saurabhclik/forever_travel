<?php
include("../config.php");

if (isset($_POST['id'])) {
    $stmt = $mysqli->prepare("SELECT a.*,b.name as customer, c.name as company FROM order_mst a join customers b on a.customer_id=b.id join company c on a.company_id=c.id WHERE a.id = ? ;");
    $stmt->bind_param("s", $_POST['id']);
    $stmt->execute();
    $order_mst = $stmt->get_result()->fetch_assoc();



    $stmt_income = $mysqli->prepare("SELECT SUM(order_det.price*order_det.qty) AS total_income,customers.name
    FROM order_mst 
    INNER JOIN order_det ON order_mst.id = order_det.order_id join customers on  order_mst.customer_id=customers.id
    WHERE order_mst.id = ?");
    $stmt_income->bind_param("i", $order_mst['id']);
    $stmt_income->execute();
    $res_income = $stmt_income->get_result()->fetch_assoc();


    $stmt_income = $mysqli->prepare("SELECT SUM(amount) AS total_expense,
SUM(CASE WHEN expense_type='vendor' THEN amount ELSE 0 END)as 'vendor_expense',
SUM(CASE WHEN expense_type='labour' THEN amount ELSE 0 END)as 'labour',
SUM(CASE WHEN expense_type='TA/DA' THEN amount ELSE 0 END)as 'tada',
SUM(CASE WHEN expense_type=' ' THEN amount ELSE 0 END)as 'mis',b.name as name
FROM expenses a join customers b on a.ids=b.id WHERE a.query_id = ?");
    $stmt_income->bind_param("i", $order_mst['id']);
    $stmt_income->execute();
    $expense = $stmt_income->get_result()->fetch_assoc();

    echo  json_encode(array("order_mst" => $order_mst, "income" => $res_income, "expense" => $expense));
}
