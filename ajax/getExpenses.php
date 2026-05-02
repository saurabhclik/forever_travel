<?php
include("../config.php");

$base_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http");
$base_url .= "://" . $_SERVER['HTTP_HOST'];
$base_url .= "/forever-travel";


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

    if ($result->num_rows > 0) {

    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $sno++ . "</td>";
        echo "<td>" . (!empty($row['ids']) ? $row['ids'] : '-') . "</td>";
        $file = htmlspecialchars($row['file']);
        $imagePath = $base_url . "/images/invoices/" . $file;

        echo 
        "<td>
            <a href='$imagePath' target='_blank'>
            <img src='$imagePath' style='width:50px;height:50px;object-fit:cover;'>
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
        echo "</tr>";
    }

} else {
    echo "<tr><td colspan='13' class='text-center'>No Expense Found</td></tr>";
}

}
?>
