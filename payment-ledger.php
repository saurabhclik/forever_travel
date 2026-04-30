<?php
    include "Layouts/Header.php";
    include "Layouts/Sidebar.php";
    
    if (isset($_POST['BtnSave'])) 
    {
        try 
        {
            $stmt = $mysqli->prepare("UPDATE `payment` SET `amount`=? WHERE `id`=?");
            $stmt->bind_param("di", $_POST['amount'], $_POST['id']);
            $stmt->execute();
            alert("Update Successfully", "success", "success");
            redirect("?");
        } 
        catch (Exception $e) 
        {
            alert($e->getMessage(), "error", "error");
            redirect("?");
        }
    }

    if (isset($_POST['BtnDelete'])) 
    {
        try 
        {
            $stmt = $mysqli->prepare("DELETE FROM `payment` WHERE `id`=?");
            $stmt->bind_param("i", $_POST['did']);
            $stmt->execute();
            alert("Delete Successfully", "success", "success");
            redirect("?");
        } 
        catch (Exception $e) 
        {
            alert($e->getMessage(), "error", "error");
            redirect("?");
        }
    }

    $startDate = isset($_GET['start_date']) ? $_GET['start_date'] : '';
    $endDate   = isset($_GET['end_date']) ? $_GET['end_date'] : '';
?>

<div class="content-body">
    <div class="container-fluid">
        <div class="row">
            <?php
                $statsWhere = "1=1";
                if ($startDate && $endDate) 
                {
                    $statsWhere .= " AND DATE(p.date) BETWEEN '$startDate' AND '$endDate'";
                }
                
                $statsQuery = "SELECT 
                    COUNT(*) as total_transactions,
                    COALESCE(SUM(p.amount), 0) as total_amount,
                    COUNT(DISTINCT p.query_id) as total_queries,
                    AVG(p.amount) as avg_payment,
                    COUNT(CASE WHEN p.payment_type = 'UPI' THEN 1 END) as upi_count,
                    COUNT(CASE WHEN p.payment_type = 'Card' THEN 1 END) as card_count,
                    COUNT(CASE WHEN p.payment_type = 'Cash' THEN 1 END) as cash_count,
                    COUNT(CASE WHEN p.payment_type = 'Bank Transfer' THEN 1 END) as bank_count
                    FROM `payment` p
                    WHERE $statsWhere";
                
                $statsResult = $mysqli->query($statsQuery);
                $stats = $statsResult->fetch_assoc();
                
                $totalAmount = $stats['total_amount'] ?? 0;
                $totalTransactions = $stats['total_transactions'] ?? 0;
                $totalQueries = $stats['total_queries'] ?? 0;
                $avgPayment = $stats['avg_payment'] ?? 0;
                $upiCount = $stats['upi_count'] ?? 0;
                $cardCount = $stats['card_count'] ?? 0;
                $cashCount = $stats['cash_count'] ?? 0;
                $bankCount = $stats['bank_count'] ?? 0;
            ?>
            
            <div class="col-xl-3 col-lg-4 col-sm-6">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="font-w600">Total Transactions</h6>
                                <h2 class="mb-0"><?= number_format($totalTransactions) ?></h2>
                            </div>
                            <div class="text-primary">
                                <i class="fa fa-receipt fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-xl-3 col-lg-4 col-sm-6">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="font-w600">Total Amount</h6>
                                <h2 class="mb-0">₹<?= number_format($totalAmount, 2) ?></h2>
                            </div>
                            <div class="text-success">
                                <i class="fa fa-rupee-sign fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-xl-3 col-lg-4 col-sm-6">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="font-w600">Total Queries</h6>
                                <h2 class="mb-0"><?= number_format($totalQueries) ?></h2>
                            </div>
                            <div class="text-warning">
                                <i class="fa fa-question-circle fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-xl-3 col-lg-4 col-sm-6">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="font-w600">Average Payment</h6>
                                <h2 class="mb-0">₹<?= number_format($avgPayment, 2) ?></h2>
                            </div>
                            <div class="text-info">
                                <i class="fa fa-chart-line fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-xl-3 col-lg-4 col-sm-6">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="font-w600">UPI Payments</h6>
                                <h2 class="mb-0"><?= number_format($upiCount) ?></h2>
                            </div>
                            <div class="text-primary">
                                <i class="fa fa-mobile-alt fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-xl-3 col-lg-4 col-sm-6">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="font-w600">Card Payments</h6>
                                <h2 class="mb-0"><?= number_format($cardCount) ?></h2>
                            </div>
                            <div class="text-info">
                                <i class="fa fa-credit-card fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-xl-3 col-lg-4 col-sm-6">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="font-w600">Cash Payments</h6>
                                <h2 class="mb-0"><?= number_format($cashCount) ?></h2>
                            </div>
                            <div class="text-success">
                                <i class="fa fa-money-bill fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-xl-3 col-lg-4 col-sm-6">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="font-w600">Bank Transfer</h6>
                                <h2 class="mb-0"><?= number_format($bankCount) ?></h2>
                            </div>
                            <div class="text-secondary">
                                <i class="fa fa-university fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-xl-6 col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Payment Trend</h4>
                    </div>
                    <div class="card-body">
                        <canvas id="paymentTrendChart" height="300"></canvas>
                    </div>
                </div>
            </div>
            
            <div class="col-xl-6 col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Payment Mode Distribution</h4>
                    </div>
                    <div class="card-body">
                        <canvas id="paymentModeChart" height="300"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Daily Collection</h4>
                    </div>
                    <div class="card-body">
                        <canvas id="dailyCollectionChart" height="100"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Payment Ledger</h4>
                        <div class="card-header-toolbar">
                            <button class="btn btn-success btn-sm" id="exportExcel">
                                <i class="fa fa-file-excel"></i> Export Excel
                            </button>
                            <button class="btn btn-danger btn-sm ReportbtnExportPDF">
                                <i class="fa fa-file-pdf"></i> Export PDF
                            </button>
                        </div>
                    </div>
                    <div class="row mb-3 mt-3 p-3">
                        <div class="col-md-9">
                            <form method="get" class="row g-2">
                                <div class="col-md-3">
                                    <input type="date" name="start_date" class="form-control" 
                                        placeholder="Start Date" value="<?= htmlspecialchars($startDate) ?>">
                                </div>
                                <div class="col-md-3">
                                    <input type="date" name="end_date" class="form-control" 
                                        placeholder="End Date" value="<?= htmlspecialchars($endDate) ?>">
                                </div>
                                <div class="col-md-3">
                                    <button type="submit" class="btn btn-primary">Apply Filter</button>
                                    <a href="<?= $_SERVER['PHP_SELF'] ?>" class="btn btn-secondary">Reset</a>
                                </div>
                            </form>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="example" class="display" style="min-width: 845px">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Query ID</th>
                                        <th>Customer Name</th>
                                        <th>Mobile</th>
                                        <th>Destination</th>
                                        <th>Service</th>
                                        <th>Amount</th>
                                        <th>Payment Type</th>
                                        <th>Reference No</th>
                                        <th>Payment Date</th>
                                        <th>Remark</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                        $query = "
                                            SELECT 
                                                p.*,
                                                c.name AS customer_name,
                                                c.number AS customer_mobile,
                                                q.destination,
                                                q.service,
                                                q.status as query_status
                                            FROM payment p
                                            LEFT JOIN query_mst q ON p.query_id = q.id
                                            LEFT JOIN customers c ON q.customer_id = c.id
                                        ";

                                        $params = [];
                                        $types = "";
                                        if ($startDate && $endDate) 
                                        {
                                            $query .= " WHERE DATE(p.date) BETWEEN ? AND ?";
                                            $params[] = $startDate;
                                            $params[] = $endDate;
                                            $types .= "ss";
                                        }
                                        
                                        $query .= " ORDER BY p.id DESC";

                                        $stmt = $mysqli->prepare($query);
                                        if (!empty($params)) 
                                        {
                                            $stmt->bind_param($types, ...$params);
                                        }
                                        $stmt->execute();
                                        $res = $stmt->get_result();

                                        while ($row = $res->fetch_assoc()) 
                                        {
                                            $paymentTypeBadge = '';
                                            if (isset($row['payment_type'])) 
                                            {
                                                if ($row['payment_type'] == 'UPI') 
                                                {
                                                    $paymentTypeBadge = '<span class="badge badge-primary">UPI</span>';
                                                }
                                                elseif ($row['payment_type'] == 'Card') 
                                                {
                                                    $paymentTypeBadge = '<span class="badge badge-info">Card</span>';
                                                }
                                                elseif ($row['payment_type'] == 'Cash') 
                                                {
                                                    $paymentTypeBadge = '<span class="badge badge-success">Cash</span>';
                                                }
                                                else 
                                                {
                                                    $paymentTypeBadge = '<span class="badge badge-secondary">' . htmlspecialchars($row['payment_type']) . '</span>';
                                                }
                                            }
                                            
                                            echo '<tr>
                                                <td>' . (isset($row['id']) ? htmlspecialchars($row['id']) : '') . '</td>
                                                <td>' . (isset($row['query_id']) ? htmlspecialchars($row['query_id']) : '') . '</td>
                                                <td><strong>' . (isset($row['customer_name']) ? htmlspecialchars($row['customer_name']) : 'N/A') . '</strong></td>
                                                <td>' . (isset($row['customer_mobile']) ? htmlspecialchars($row['customer_mobile']) : '-') . '</td>
                                                <td>' . (isset($row['destination']) ? htmlspecialchars($row['destination']) : '-') . '</td>
                                                <td>' . (isset($row['service']) ? htmlspecialchars($row['service']) : '-') . '</td>
                                                <td>₹' . (isset($row['amount']) ? number_format($row['amount'], 2) : '0.00') . '</td>
                                                <td>' . $paymentTypeBadge . '</td>
                                                <td>' . (isset($row['ref_no']) ? htmlspecialchars($row['ref_no']) : '-') . '</td>
                                                <td>' . (isset($row['date']) ? date('d-m-Y', strtotime($row['date'])) : '') . '</td>
                                                <td>' . (isset($row['remark']) ? htmlspecialchars($row['remark']) : '-') . '</td>
                                                <td class="d-flex gap-2 align-items-center">
                                                    <button type="button" class="btn btn-primary btn-sm Edit shadow btn-xs sharp" 
                                                        data-id="' . $row['id'] . '"
                                                        data-amount="' . $row['amount'] . '">
                                                        <i class="fa fa-pen"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-danger btn-sm Delete shadow btn-xs sharp" 
                                                        data-id="' . $row['id'] . '">
                                                        <i class="fa fa-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>';
                                        }
                                        
                                        if ($res->num_rows == 0) 
                                        {
                                            echo '<tr><td colspan="12" class="text-center text-muted">No payment records found</td><tr>';
                                        }
                                    ?>
                                </tbody>
                                <tfoot>
                                    <tr class="bg-light fw-bold">
                                        <td colspan="6" class="text-end"><strong>Total:</strong></td>
                                        <td colspan="6"><strong>₹<?= number_format($totalAmount, 2) ?></strong></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Update Payment Amount</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="post">
                <div class="modal-body">
                    <div class="form-group">
                        <label>Amount (₹)</label>
                        <input type="number" name="amount" id="amount" class="form-control" required step="0.01">
                        <input type="hidden" name="id" id="id">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" name="BtnSave" class="btn btn-primary">Save changes</button>
                </div>
            </form>
        </div>
    </div>
</div>
<div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Delete Payment</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="post">
                <div class="modal-body">
                    <p>Are you sure you want to delete this payment?</p>
                    <input type="hidden" name="did" id="did">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="BtnDelete" class="btn btn-danger">Delete</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include "Layouts/Footer.php"; ?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.28/jspdf.plugin.autotable.min.js"></script>

<script>
    $(document).on("click", ".Edit", function() 
    {
        $("#id").val($(this).data("id"));
        $("#amount").val($(this).data("amount"));
        $("#exampleModal").modal("show");
    });

    $(document).on("click", ".Delete", function() 
    {
        $("#did").val($(this).data("id"));
        $("#deleteModal").modal("show");
    });

    $(document).ready(function() 
    {
        <?php
            $trendLabels = [];
            $trendAmounts = [];
            $dailyLabels = [];
            $dailyAmounts = [];
            $modeLabels = [];
            $modeCounts = [];
            $modeColors = [];
            
            $dateCondition = "";
            if ($startDate && $endDate) 
            {
                $dateCondition = " AND DATE(date) BETWEEN '$startDate' AND '$endDate'";
            }
            $trendQuery = "
                SELECT DATE(date) as date, COALESCE(SUM(amount), 0) as total 
                FROM payment 
                WHERE 1=1 $dateCondition 
                GROUP BY DATE(date) 
                ORDER BY date ASC 
                LIMIT 30
            ";
            $trendResult = $mysqli->query($trendQuery);
            while ($row = $trendResult->fetch_assoc()) 
            {
                $trendLabels[] = date('d M', strtotime($row['date']));
                $trendAmounts[] = $row['total'];
            }
            $dailyQuery = "
                SELECT DATE(date) as date, COUNT(*) as count, COALESCE(SUM(amount), 0) as total 
                FROM payment 
                WHERE 1=1 $dateCondition 
                GROUP BY DATE(date) 
                ORDER BY date DESC 
                LIMIT 7
            ";
            $dailyResult = $mysqli->query($dailyQuery);
            while ($row = $dailyResult->fetch_assoc()) 
            {
                array_unshift($dailyLabels, date('d M', strtotime($row['date'])));
                array_unshift($dailyAmounts, $row['total']);
            }
            $modeQuery = "
                SELECT payment_type, COUNT(*) as count 
                FROM payment 
                WHERE payment_type IS NOT NULL AND payment_type != ''
            ";
            if ($startDate && $endDate) 
            {
                $modeQuery .= " AND DATE(date) BETWEEN '$startDate' AND '$endDate'";
            }
            $modeQuery .= " GROUP BY payment_type";
            $modeResult = $mysqli->query($modeQuery);
            $modeColorsArray = ['#3b7ddd', '#28a745', '#ffc107', '#dc3545', '#17a2b8', '#6f42c1'];
            $colorIndex = 0;
            while ($row = $modeResult->fetch_assoc()) 
            {
                $modeLabels[] = $row['payment_type'];
                $modeCounts[] = $row['count'];
                $modeColors[] = $modeColorsArray[$colorIndex % count($modeColorsArray)];
                $colorIndex++;
            }
        ?>
        if (document.getElementById('paymentTrendChart') && <?= count($trendLabels) ?> > 0) 
        {
            const ctx1 = document.getElementById('paymentTrendChart').getContext('2d');
            new Chart(ctx1, {
                type: 'line',
                data: {
                    labels: <?= json_encode($trendLabels) ?>,
                    datasets: [{
                        label: 'Payment Amount (₹)',
                        data: <?= json_encode($trendAmounts) ?>,
                        borderColor: '#28a745',
                        backgroundColor: 'rgba(40, 167, 69, 0.1)',
                        tension: 0.4,
                        fill: true,
                        pointBackgroundColor: '#28a745',
                        pointBorderColor: '#fff',
                        pointRadius: 4,
                        pointHoverRadius: 6
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: {
                            position: 'top',
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return 'Amount: ₹' + context.parsed.y.toLocaleString();
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return '₹' + value.toLocaleString();
                                }
                            },
                            title: {
                                display: true,
                                text: 'Amount (₹)'
                            }
                        },
                        x: {
                            title: {
                                display: true,
                                text: 'Date'
                            }
                        }
                    }
                }
            });
        } 
        else if (document.getElementById('paymentTrendChart')) 
        {
            document.getElementById('paymentTrendChart').innerHTML = '<div class="text-center text-muted mt-5">No payment data available</div>';
        }
        if (document.getElementById('paymentModeChart') && <?= count($modeLabels) ?> > 0) 
        {
            const ctx2 = document.getElementById('paymentModeChart').getContext('2d');
            new Chart(ctx2, {
                type: 'doughnut',
                data: {
                    labels: <?= json_encode($modeLabels) ?>,
                    datasets: [{
                        data: <?= json_encode($modeCounts) ?>,
                        backgroundColor: <?= json_encode($modeColors) ?>,
                        borderWidth: 2,
                        borderColor: '#fff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: {
                            position: 'bottom',
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) 
                                {
                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    const percentage = total > 0 ? ((context.parsed / total) * 100).toFixed(1) : 0;
                                    return `${context.label}: ${context.parsed} (${percentage}%)`;
                                }
                            }
                        }
                    }
                }
            });
        } 
        else if (document.getElementById('paymentModeChart')) 
        {
            document.getElementById('paymentModeChart').innerHTML = '<div class="text-center text-muted mt-5">No payment mode data available</div>';
        }
        if (document.getElementById('dailyCollectionChart') && <?= count($dailyLabels) ?> > 0) 
        {
            const ctx3 = document.getElementById('dailyCollectionChart').getContext('2d');
            new Chart(ctx3, {
                type: 'bar',
                data: {
                    labels: <?= json_encode($dailyLabels) ?>,
                    datasets: [{
                        label: 'Daily Collection (₹)',
                        data: <?= json_encode($dailyAmounts) ?>,
                        backgroundColor: '#3b7ddd',
                        borderColor: '#2c5aa6',
                        borderWidth: 1,
                        borderRadius: 5,
                        barPercentage: 0.7
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: {
                            position: 'top',
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return 'Collection: ₹' + context.parsed.y.toLocaleString();
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return '₹' + value.toLocaleString();
                                }
                            },
                            title: {
                                display: true,
                                text: 'Amount (₹)'
                            }
                        },
                        x: {
                            title: {
                                display: true,
                                text: 'Date'
                            }
                        }
                    }
                }
            });
        } 
        else if (document.getElementById('dailyCollectionChart')) 
        {
            document.getElementById('dailyCollectionChart').innerHTML = '<div class="text-center text-muted mt-5">No daily collection data available</div>';
        }
        $('#exportExcel').click(function() 
        {
            var table = document.getElementById('example');
            var wb = XLSX.utils.table_to_book(table, {
                sheet: "Payment Ledger"
            });
            XLSX.writeFile(wb, "payment-ledger.xlsx");
        });
        $('.ReportbtnExportPDF').click(function() 
        {
            var { jsPDF } = window.jspdf;
            var doc = new jsPDF('l', 'pt', 'a4');
            
            doc.setFontSize(16);
            doc.text("Payment Ledger Report", 40, 40);
            
            let now = new Date().toLocaleString();
            doc.setFontSize(10);
            doc.text("Generated On: " + now, 40, 60);

            doc.setFontSize(12);
            doc.text("Summary:", 40, 80);
            doc.setFontSize(10);
            doc.text(`Total Transactions: <?= number_format($totalTransactions) ?>`, 40, 95);
            doc.text(`Total Amount: ₹<?= number_format($totalAmount, 2) ?>`, 200, 95);
            doc.text(`Average Payment: ₹<?= number_format($avgPayment, 2) ?>`, 400, 95);
            
            let headers = [];
            $("#example thead th").each(function() 
            {
                headers.push($(this).text().trim());
            });
            
            let data = [];
            $("#example tbody tr").each(function() 
            {
                let row = [];
                $(this).find("td").each(function() 
                {
                    row.push($(this).text().trim());
                });
                data.push(row);
            });
            
            doc.autoTable({
                head: [headers],
                body: data,
                startY: 110,
                styles: { fontSize: 8 },
                headStyles: { fillColor: [75, 108, 183] }
            });
            
            doc.save("payment-ledger.pdf");
        });
    });
</script>