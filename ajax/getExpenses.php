<?php
include("../config.php");

if (isset($_POST['query_id'])) {
    $query_id = $_POST['query_id'];

   $stmt = $mysqli->prepare("
    SELECT 
        e.*, 
        CASE 
            WHEN e.expense_type = 'vendor' THEN v.name
            WHEN e.expense_type = 'labour' THEN l.name
            ELSE 'N/A'
        END AS expense_person
    FROM expenses e
    LEFT JOIN vendor v ON e.vendor_id = v.id AND e.expense_type = 'vendor'
    LEFT JOIN labour l ON e.vendor_id = l.id AND e.expense_type = 'labour'
    WHERE e.query_id = ?
");
    $stmt->bind_param("i", $query_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $sno = 1;
while ($row = $result->fetch_assoc()) {
    echo "<tr>";
    echo "<td>" . $sno++ . "</td>";
    echo "<td>" . htmlspecialchars($row['ids']) . "</td>";
    echo "<td>
        <a href='images/invoices/" . htmlspecialchars($row['file']) . "' target='_blank'>
            <img src='images/invoices/" . htmlspecialchars($row['file']) . "' alt='Expense File' style='width: 50px; height: 50px; object-fit: cover;'>
        </a>
    </td>";
    echo "<td>" . htmlspecialchars($row['expense_person']) . "</td>";
    echo "<td>" . htmlspecialchars($row['name']) . "</td>";
    echo "<td>" . htmlspecialchars($row['expense_type']) . "</td>";
    echo "<td>" . htmlspecialchars($row['expense_date']) . "</td>";
    echo "<td>" . htmlspecialchars($row['note']) . "</td>";
    echo "<td>" . htmlspecialchars($row['payment_mode']) . "</td>";
    echo "<td>" . htmlspecialchars($row['ref_no']) . "</td>";
    echo "<td>" . htmlspecialchars($row['amount']) . "</td>";
    echo "<td>" . htmlspecialchars($row['build']) . "</td>";
    echo "<td>" . htmlspecialchars($row['paid_status']) . "</td>";

    // Action button
    echo "<td>
        <button class='btn btn-sm btn-primary update-paid-status' data-id='" . $row['id'] . "'>
            Mark as Paid
        </button>
    </td>";

    echo "</tr>";
}

}
?>
