<?php
    include "Layouts/Header.php";
    include "Layouts/Sidebar.php";
?>

<div class="content-body">
    <div class="container-fluid">
        <div class="row">
            <?php
                $status_filter = isset($_GET['status']) && !empty($_GET['status']) ? $_GET['status'] : '';
                $from_date = isset($_GET['fromDt']) && !empty($_GET['fromDt']) ? $_GET['fromDt'] : '';
                $to_date = isset($_GET['toDt']) && !empty($_GET['toDt']) ? $_GET['toDt'] : '';
                $statsWhere = "1=1";
                $statsParams = [];
                $statsTypes = "";
                if (!empty($status_filter)) 
                {
                    $statsWhere .= " AND q.payment_status = ?";
                    $statsParams[] = $status_filter;
                    $statsTypes .= "s";
                }
                
                if (!empty($from_date) && !empty($to_date)) 
                {
                    $statsWhere .= " AND DATE(q.created_at) BETWEEN ? AND ?";
                    $statsParams[] = $from_date;
                    $statsParams[] = $to_date;
                    $statsTypes .= "ss";
                }
                $statsQuery = "
                    SELECT 
                        COUNT(DISTINCT q.id) as total_transactions,
                        COALESCE(SUM(q.sale_amount), 0) as total_sale_amount,
                        COALESCE(SUM(p.amount), 0) as total_payment_received,
                        COALESCE(SUM(q.sale_amount) - SUM(p.amount), 0) as pending_amount,
                        COUNT(CASE WHEN p.payment_type = 'cash' THEN 1 END) as cash_count,
                        COUNT(CASE WHEN p.payment_type = 'card' THEN 1 END) as card_count,
                        COUNT(CASE WHEN p.payment_type = 'online' THEN 1 END) as online_count,
                        COUNT(CASE WHEN p.payment_type = 'bank_transfer' THEN 1 END) as bank_count,
                        ROUND(AVG(p.amount), 2) as avg_payment,
                        COUNT(CASE WHEN q.payment_status = 'paid' THEN 1 END) as paid_count,
                        COUNT(CASE WHEN q.payment_status = 'pending' THEN 1 END) as pending_count,
                        COUNT(CASE WHEN q.payment_status = 'partial' THEN 1 END) as partial_count
                    FROM query_mst q
                    INNER JOIN customers c ON q.customer_id = c.id
                    INNER JOIN payment p ON p.query_id = q.id
                    WHERE $statsWhere
                ";
                
                $statsStmt = $mysqli->prepare($statsQuery);
                if (!empty($statsParams)) 
                {
                    $statsStmt->bind_param($statsTypes, ...$statsParams);
                }
                $statsStmt->execute();
                $stats = $statsStmt->get_result()->fetch_assoc();
                
                $totalTransactions = $stats['total_transactions'] ?? 0;
                $totalSaleAmount = $stats['total_sale_amount'] ?? 0;
                $totalPaymentReceived = $stats['total_payment_received'] ?? 0;
                $pendingAmount = $stats['pending_amount'] ?? 0;
                $cashCount = $stats['cash_count'] ?? 0;
                $cardCount = $stats['card_count'] ?? 0;
                $onlineCount = $stats['online_count'] ?? 0;
                $bankCount = $stats['bank_count'] ?? 0;
                $avgPayment = $stats['avg_payment'] ?? 0;
                $paidCount = $stats['paid_count'] ?? 0;
                $pendingCountStatus = $stats['pending_count'] ?? 0;
                $partialCount = $stats['partial_count'] ?? 0;
                
                $collectionRate = $totalSaleAmount > 0 ? round(($totalPaymentReceived / $totalSaleAmount) * 100, 1) : 0;
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
                                <i class="fa fa-exchange-alt fa-2x"></i>
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
                                <h6 class="font-w600">Total Sale Amount</h6>
                                <h2 class="mb-0">₹<?= number_format($totalSaleAmount, 2) ?></h2>
                            </div>
                            <div class="text-success">
                                <i class="fa fa-chart-line fa-2x"></i>
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
                                <h6 class="font-w600">Payment Received</h6>
                                <h2 class="mb-0">₹<?= number_format($totalPaymentReceived, 2) ?></h2>
                            </div>
                            <div class="text-info">
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
                                <h6 class="font-w600">Collection Rate</h6>
                                <h2 class="mb-0"><?= $collectionRate ?>%</h2>
                            </div>
                            <div class="text-warning">
                                <i class="fa fa-percent fa-2x"></i>
                            </div>
                        </div>
                        <div class="progress mt-3" style="height: 6px;">
                            <div class="progress-bar bg-success" style="width: <?= $collectionRate ?>%"></div>
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
                                <h6 class="font-w600">Pending Amount</h6>
                                <h2 class="mb-0">₹<?= number_format($pendingAmount, 2) ?></h2>
                            </div>
                            <div class="text-danger">
                                <i class="fa fa-clock fa-2x"></i>
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
                            <div class="text-secondary">
                                <i class="fa fa-chart-bar fa-2x"></i>
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
                                <h6 class="font-w600">Paid</h6>
                                <h2 class="mb-0"><?= number_format($paidCount) ?></h2>
                            </div>
                            <div class="text-success">
                                <i class="fa fa-check-circle fa-2x"></i>
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
                                <h6 class="font-w600">Pending</h6>
                                <h2 class="mb-0"><?= number_format($pendingCountStatus) ?></h2>
                            </div>
                            <div class="text-warning">
                                <i class="fa fa-hourglass-half fa-2x"></i>
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
                        <h4 class="card-title">Payment Method Distribution</h4>
                    </div>
                    <div class="card-body">
                        <canvas id="paymentMethodChart" height="300"></canvas>
                    </div>
                </div>
            </div>
            
            <div class="col-xl-6 col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Payment Status Overview</h4>
                    </div>
                    <div class="card-body">
                        <canvas id="paymentStatusChart" height="300"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Transaction Trend (Last 7 Days)</h4>
                    </div>
                    <div class="card-body">
                        <canvas id="transactionTrendChart" height="100"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Transaction Reports</h4>
                        <div class="card-header-toolbar">
                            <button class="btn btn-success btn-sm" id="exportExcel">
                                <i class="fa fa-file-excel"></i> Export Excel
                            </button>
                            <button class="btn btn-danger btn-sm ReportbtnExportPDF">
                                <i class="fa fa-file-pdf"></i> Export PDF
                            </button>
                        </div>
                    </div>
                    <div class="card-body border-bottom">
                        <form method="GET" class="mb-0">
                            <div class="row g-2 align-items-end">
                                <div class="col-md-3">
                                    <label class="form-label">Payment Status</label>
                                    <select name="status" id="status" class="form-control">
                                        <option value="">-- All Status --</option>
                                        <?php 
                                            $existing_status = $mysqli->prepare("SELECT DISTINCT `payment_status` FROM `query_mst` WHERE `payment_status` IS NOT NULL AND `payment_status` != ''");
                                            $existing_status->execute();
                                            $status_result = $existing_status->get_result();
                                            while ($row = $status_result->fetch_assoc()) 
                                            {
                                                $selected = ($status_filter == $row['payment_status']) ? 'selected' : '';
                                                echo '<option value="' . htmlspecialchars($row['payment_status']) . '" ' . $selected . '>' . ucfirst(htmlspecialchars($row['payment_status'])) . '</option>';
                                            }
                                        ?>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">From Date</label>
                                    <input type="date" class="form-control" name="fromDt" id="fromDt" value="<?= htmlspecialchars($from_date) ?>">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">To Date</label>
                                    <input type="date" class="form-control" name="toDt" id="toDt" value="<?= htmlspecialchars($to_date) ?>">
                                </div>
                                <div class="col-md-3">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fa fa-search"></i> Search
                                    </button>
                                    <a href="transaction-reports.php" class="btn btn-secondary">
                                        <i class="fa fa-eraser"></i> Reset
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="example" class="display" style="min-width: 845px">
                                <thead>
                                    <tr>
                                        <th>S.No</th>
                                        <th>Name</th>
                                        <th>Phone Number</th>
                                        <th>Email</th>
                                        <th>Sale Amount</th>
                                        <th>Payment Received</th>
                                        <th>Payment Type</th>
                                        <th>Payment Status</th>
                                        <th>Source</th>
                                        <th>Priority</th>
                                    </tr>
                                </thead>
                                <tbody>                              
                                <?php
                                    $mainQuery = "
                                        SELECT 
                                            c.name as `name`, 
                                            q.mobile as `number`, 
                                            q.email as `email`, 
                                            q.status, 
                                            q.source, 
                                            q.priority, 
                                            q.sale_amount, 
                                            q.payment_status as `payment_status`,
                                            p.amount as payment, 
                                            p.remark, 
                                            p.payment_type 
                                        FROM query_mst q 
                                        INNER JOIN customers c ON q.customer_id = c.id 
                                        INNER JOIN payment p ON p.query_id = q.id
                                        WHERE 1=1
                                    ";
                                    
                                    $params = [];
                                    $types = "";
                                    
                                    if (!empty($status_filter)) 
                                    {
                                        $mainQuery .= " AND q.payment_status = ?";
                                        $params[] = $status_filter;
                                        $types .= "s";
                                    }
                                    
                                    if (!empty($from_date) && !empty($to_date)) 
                                    {
                                        $mainQuery .= " AND DATE(q.created_at) BETWEEN ? AND ?";
                                        $params[] = $from_date;
                                        $params[] = $to_date;
                                        $types .= "ss";
                                    }
                                    
                                    $mainQuery .= " ORDER BY q.id DESC";
                                    
                                    $stmt = $mysqli->prepare($mainQuery);
                                    if (!empty($params)) 
                                    {
                                        $stmt->bind_param($types, ...$params);
                                    }
                                    $stmt->execute();
                                    $res = $stmt->get_result();
                                    $sno = 1;
                                    $paymentTypes = [];
                                    $paymentTypeAmounts = [];
                                    $statusData = [];
                                    
                                    while ($row = $res->fetch_assoc()) 
                                    {
                                        $statusBadge = '';
                                        if ($row['payment_status'] == 'paid') 
                                        {
                                            $statusBadge = '<span class="badge badge-success">Paid</span>';
                                        } 
                                        elseif ($row['payment_status'] == 'pending') 
                                        {
                                            $statusBadge = '<span class="badge badge-warning">Pending</span>';
                                        } 
                                        elseif ($row['payment_status'] == 'partial') 
                                        {
                                            $statusBadge = '<span class="badge badge-info">Partial</span>';
                                        } 
                                        else 
                                        {
                                            $statusBadge = '<span class="badge badge-secondary">' . htmlspecialchars($row['payment_status']) . '</span>';
                                        }
                                        
                                        $priorityBadge = '';
                                        if ($row['priority'] == 'HIGH') 
                                        {
                                            $priorityBadge = '<span class="badge badge-danger">High</span>';
                                        } 
                                        elseif ($row['priority'] == 'MEDIUM') 
                                        {
                                            $priorityBadge = '<span class="badge badge-warning">Medium</span>';
                                        } 
                                        else 
                                        {
                                            $priorityBadge = '<span class="badge badge-info">Normal</span>';
                                        }
                                        $paymentType = strtolower($row['payment_type']);
                                        if (!isset($paymentTypes[$paymentType])) 
                                        {
                                            $paymentTypes[$paymentType] = 0;
                                            $paymentTypeAmounts[$paymentType] = 0;
                                        }
                                        $paymentTypes[$paymentType]++;
                                        $paymentTypeAmounts[$paymentType] += $row['payment'];
                                        
                                        $statusKey = $row['payment_status'];
                                        if (!isset($statusData[$statusKey])) 
                                        {
                                            $statusData[$statusKey] = 0;
                                        }
                                        $statusData[$statusKey]++;
                                        ?> 
                                        <tr>
                                            <td>
                                                <?php echo $sno++; ?></td>
                                            <td>
                                                <strong><?php echo htmlspecialchars($row['name']); ?></strong>
                                            </td>
                                            <td>
                                                <?php echo htmlspecialchars($row['number']); ?>
                                            </td>
                                            <td>
                                                <?php echo htmlspecialchars($row['email']); ?>
                                            </td>
                                            <td class="text-success">
                                                ₹<?php echo number_format($row['sale_amount'], 2); ?>
                                            </td>
                                            <td class="text-primary">
                                                ₹<?php echo number_format($row['payment'], 2); ?>
                                            </td>
                                            <td>
                                                <?php echo ucfirst(htmlspecialchars($row['payment_type'])); ?>
                                            </td>
                                            <td>
                                                <?php echo $statusBadge; ?>
                                            </td>
                                            <td>
                                                <?php echo htmlspecialchars($row['source']); ?>
                                            </td>
                                            <td>
                                                <?php echo $priorityBadge; ?>
                                            </td>
                                        </tr>
                                    <?php
                                    }
                                    
                                    if ($sno == 1) 
                                    {
                                        echo '<tr><td colspan="10" class="text-center text-muted">No transactions found</td></tr>';
                                    }
                                ?>
                                </tbody>
                                <tfoot>
                                    <tr class="fw-bold">
                                        <td colspan="4" class="text-end"><strong>Total:</strong></td>
                                        <td class="text-success"><strong>₹<?= number_format($totalSaleAmount, 2) ?></strong></td>
                                        <td class="text-primary"><strong>₹<?= number_format($totalPaymentReceived, 2) ?></strong></td>
                                        <td colspan="4"></td>
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

<?php include "Layouts/Footer.php"; ?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.28/jspdf.plugin.autotable.min.js"></script>

<script>
$(document).ready(function() 
{
    var paymentTypes = <?php 
        $types = [];
        $counts = [];
        foreach ($paymentTypes as $type => $count) 
        {
            $types[] = ucfirst($type);
            $counts[] = $count;
        }
        echo json_encode(['labels' => $types, 'data' => $counts]);
    ?>;
    
    var statusTypes = <?php 
        $statusLabels = [];
        $statusCounts = [];
        $statusColors = [];
        foreach ($statusData as $status => $count) 
        {
            $statusLabels[] = ucfirst($status);
            $statusCounts[] = $count;
            if ($status == 'paid') 
            {
                $statusColors[] = '#28a745';
            } 
            elseif ($status == 'pending') 
            {
                $statusColors[] = '#ffc107';
            } 
            elseif ($status == 'partial') 
            {
                $statusColors[] = '#17a2b8';
            } 
            else 
            {
                $statusColors[] = '#6c757d';
            }
        }
        echo json_encode(['labels' => $statusLabels, 'data' => $statusCounts, 'colors' => $statusColors]);
    ?>;
    if (document.getElementById('paymentMethodChart') && paymentTypes.data.length > 0) 
    {
        const ctx1 = document.getElementById('paymentMethodChart').getContext('2d');
        new Chart(ctx1, {
            type: 'doughnut',
            data: {
                labels: paymentTypes.labels,
                datasets: [{
                    data: paymentTypes.data,
                    backgroundColor: ['#3b7ddd', '#28a745', '#ffc107', '#dc3545', '#17a2b8'],
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
    else 
    {
        if (document.getElementById('paymentMethodChart')) 
        {
            document.getElementById('paymentMethodChart').innerHTML = '<div class="text-center text-muted mt-5">No data available</div>';
        }
    }
    if (document.getElementById('paymentStatusChart') && statusTypes.data.length > 0) 
    {
        const ctx2 = document.getElementById('paymentStatusChart').getContext('2d');
        new Chart(ctx2, {
            type: 'pie',
            data: {
                labels: statusTypes.labels,
                datasets: [{
                    data: statusTypes.data,
                    backgroundColor: statusTypes.colors,
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
                            label: function(context) {
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
    else 
    {
        if (document.getElementById('paymentStatusChart')) 
        {
            document.getElementById('paymentStatusChart').innerHTML = '<div class="text-center text-muted mt-5">No data available</div>';
        }
    }
    <?php
        $trendWhere = "1=1";
        $trendParams = [];
        $trendTypes = "";
        
        if (!empty($status_filter)) 
        {
            $trendWhere .= " AND q.payment_status = ?";
            $trendParams[] = $status_filter;
            $trendTypes .= "s";
        }
        
        $trendQuery = "
            SELECT 
                DATE(q.created_at) as date,
                COUNT(DISTINCT q.id) as transaction_count,
                COALESCE(SUM(p.amount), 0) as total_amount
            FROM query_mst q
            INNER JOIN payment p ON p.query_id = q.id
            WHERE $trendWhere AND DATE(q.created_at) >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
            GROUP BY DATE(q.created_at)
            ORDER BY date ASC
        ";
        
        $trendStmt = $mysqli->prepare($trendQuery);
        if (!empty($trendParams)) 
        {
            $trendStmt->bind_param($trendTypes, ...$trendParams);
        }
        $trendStmt->execute();
        $trendResult = $trendStmt->get_result();
        
        $trendDates = [];
        $trendCounts = [];
        $trendAmounts = [];
        while ($row = $trendResult->fetch_assoc()) 
        {
            $trendDates[] = date('d M', strtotime($row['date']));
            $trendCounts[] = $row['transaction_count'];
            $trendAmounts[] = $row['total_amount'];
        }
    ?>
    
    if (document.getElementById('transactionTrendChart') && <?= count($trendDates) ?> > 0) 
    {
        const ctx3 = document.getElementById('transactionTrendChart').getContext('2d');
        new Chart(ctx3, {
            type: 'line',
            data: {
                labels: <?= json_encode($trendDates) ?>,
                datasets: [
                    {
                        label: 'Transaction Count',
                        data: <?= json_encode($trendCounts) ?>,
                        borderColor: '#3b7ddd',
                        backgroundColor: 'rgba(59, 125, 221, 0.1)',
                        tension: 0.4,
                        fill: true,
                        yAxisID: 'y'
                    },
                    {
                        label: 'Amount (₹)',
                        data: <?= json_encode($trendAmounts) ?>,
                        borderColor: '#28a745',
                        backgroundColor: 'rgba(40, 167, 69, 0.1)',
                        tension: 0.4,
                        fill: true,
                        yAxisID: 'y1'
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        position: 'left',
                        title: {
                            display: true,
                            text: 'Number of Transactions'
                        },
                        ticks: {
                            stepSize: 1
                        }
                    },
                    y1: {
                        beginAtZero: true,
                        position: 'right',
                        title: {
                            display: true,
                            text: 'Amount (₹)'
                        },
                        ticks: {
                            callback: function(value) 
                            {
                                return '₹' + value.toLocaleString();
                            }
                        },
                        grid: {
                            drawOnChartArea: false
                        }
                    }
                }
            }
        });
    }
    $('#exportExcel').click(function() 
    {
        var table = document.getElementById('example');
        var wb = XLSX.utils.table_to_book(table, {
            sheet: "Transaction Reports"
        });
        XLSX.writeFile(wb, "transaction-reports.xlsx");
    });
    $('.ReportbtnExportPDF').click(function() 
    {
        var { jsPDF } = window.jspdf;
        var doc = new jsPDF('l', 'pt', 'a4');
        
        doc.setFontSize(16);
        doc.text("Transaction Reports", 40, 40);
        
        let now = new Date().toLocaleString();
        doc.setFontSize(10);
        doc.text("Generated On: " + now, 40, 60);
        
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
            startY: 80,
            styles: { fontSize: 8 },
            headStyles: { fillColor: [75, 108, 183] }
        });
        
        doc.save("transaction-reports.pdf");
    });
});
</script>