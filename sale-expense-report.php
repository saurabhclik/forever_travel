<?php
include "Layouts/Header.php";
include "Layouts/Sidebar.php";





?>


<div class="content-body">
    <div class="container-fluid">
        <!-- row -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Sale Expense Report</h4>


                    </div>
                    <div class="card-body table-responsive">
                        <table class="table" id="example">
                            <thead>
                                <tr>
                                    <th>S.No</th>
                                    <th>User</th>
                                    <th>Customer</th>
                                    <th>Sale Amt.</th>
                                    <th>Received Amt.</th>
                                    <th>Pending Amt.</th>
                                    <th>No. of Exp.</th>
                                    <th>Total Expense</th>
                                    <th>Paid</th>
                                    <th>Un Paid</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $sno = 1;
                                $stmt = $mysqli->prepare("SELECT
  a.id,
  a.sale_amount,
  b.name AS customer,
  c.name AS user,
  COALESCE(p.received, 0)              AS received,
  COALESCE(e.no_of_exp, 0)             AS no_of_exp,
  COALESCE(e.expense, 0)               AS expense,
  COALESCE(e.paid, 0)                  AS paid,
  COALESCE(e.unpaid, 0)                AS unpaid
FROM query_mst a
JOIN customers b ON a.customer_id = b.id
JOIN users     c ON a.user_id     = c.id 
LEFT JOIN (
  SELECT query_id, SUM(amount) AS received
  FROM payment 
  GROUP BY query_id
) p ON p.query_id = a.id
LEFT JOIN (
  SELECT 
    query_id,
    COUNT(*) AS no_of_exp, 
    SUM(amount) AS expense,
    SUM(CASE WHEN paid_status='paid'   THEN amount ELSE 0 END) AS paid,
    SUM(CASE WHEN paid_status='unpaid' THEN amount ELSE 0 END) AS unpaid
  FROM expenses
  GROUP BY query_id
) e ON e.query_id = a.id 
WHERE a.sale_amount > 0
ORDER BY a.id DESC;");
                                $stmt->execute();
                                $category = $stmt->get_result();
                                while ($row = $category->fetch_assoc()) {
                                    echo '
                                        <tr></tr>
                                            <td>' . $sno++ . '</td>
                                            <td>' . $row["user"] . '</td>
                                            <td>' . $row["customer"] . '</td>
                                            <td>' . $row["sale_amount"] . '</td>
                                            <td>' . $row["received"] . '</td>
                                            <td>' .  $row["sale_amount"] - $row["received"] . '</td>
                                            <td>' . $row["no_of_exp"] . '</td>
                                            <td>' . $row["expense"] . '</td>
                                    <td>' . $row["paid"] . '</td>
                                                 <td>' . $row["unpaid"] . '</td>';
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





<?php include "Layouts/Footer.php";  ?>