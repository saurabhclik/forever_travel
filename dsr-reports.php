<?php
    include "Layouts/Header.php";
    include "Layouts/Sidebar.php";
?>

<div class="content-body">
    <div class="container-fluid">
        <div class="row">
            <?php
                $where = " query_mst.status = 'Converted' ";
                $params = [];
                $types = "";
                if (!empty($_GET['fromDt'])) 
                {
                    $where .= " AND DATE(query_mst.created_at) >= ? ";
                    $params[] = $_GET['fromDt'];
                    $types .= "s";
                }

                if (!empty($_GET['toDt'])) 
                {
                    $where .= " AND DATE(query_mst.created_at) <= ? ";
                    $params[] = $_GET['toDt'];
                    $types .= "s";
                }
                $statsQuery = "SELECT 
                    COUNT(DISTINCT query_mst.id) as total_orders,
                    COALESCE(SUM(query_mst.sale_amount), 0) as total_sale,
                    COALESCE(SUM(expenses.amount), 0) as total_expense,
                    COALESCE(SUM(query_mst.sale_amount), 0) - COALESCE(SUM(expenses.amount), 0) as total_profit,
                    AVG(query_mst.sale_amount - COALESCE(expenses.amount, 0)) as avg_profit,
                    COUNT(DISTINCT customers.id) as unique_customers,
                    COUNT(DISTINCT users.id) as active_users
                    FROM query_mst
                    LEFT JOIN customers ON query_mst.customer_id = customers.id
                    LEFT JOIN users ON query_mst.user_id = users.id
                    LEFT JOIN expenses ON query_mst.id = expenses.query_id
                    WHERE $where";
                
                $statsStmt = $mysqli->prepare($statsQuery);
                if (!empty($params)) {
                    $statsStmt->bind_param($types, ...$params);
                }
                $statsStmt->execute();
                $stats = $statsStmt->get_result()->fetch_assoc();
                
                $totalOrders = $stats['total_orders'] ?? 0;
                $totalSale = $stats['total_sale'] ?? 0;
                $totalExpense = $stats['total_expense'] ?? 0;
                $totalProfit = $stats['total_profit'] ?? 0;
                $avgProfit = $stats['avg_profit'] ?? 0;
                $uniqueCustomers = $stats['unique_customers'] ?? 0;
                $activeUsers = $stats['active_users'] ?? 0;
                $profitMargin = $totalSale > 0 ? round(($totalProfit / $totalSale) * 100, 1) : 0;
            ?>
            
            <div class="col-xl-3 col-lg-4 col-sm-6">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="font-w600">Total Orders</h6>
                                <h2 class="mb-0"><?= number_format($totalOrders) ?></h2>
                            </div>
                            <div class="text-primary">
                                <i class="fa fa-shopping-cart fa-2x"></i>
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
                                <h6 class="font-w600">Total Sale</h6>
                                <h2 class="mb-0">₹<?= number_format($totalSale, 2) ?></h2>
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
                                <h6 class="font-w600">Total Expense</h6>
                                <h2 class="mb-0">₹<?= number_format($totalExpense, 2) ?></h2>
                            </div>
                            <div class="text-danger">
                                <i class="fa fa-arrow-down fa-2x"></i>
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
                                <h6 class="font-w600">Total Profit</h6>
                                <h2 class="mb-0">₹<?= number_format($totalProfit, 2) ?></h2>
                            </div>
                            <div class="text-warning">
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
                                <h6 class="font-w600">Profit Margin</h6>
                                <h2 class="mb-0"><?= $profitMargin ?>%</h2>
                            </div>
                            <div class="text-info">
                                <i class="fa fa-percent fa-2x"></i>
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
                                <h6 class="font-w600">Avg Profit/Order</h6>
                                <h2 class="mb-0">₹<?= number_format($avgProfit, 2) ?></h2>
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
                                <h6 class="font-w600">Unique Customers</h6>
                                <h2 class="mb-0"><?= number_format($uniqueCustomers) ?></h2>
                            </div>
                            <div class="text-primary">
                                <i class="fa fa-users fa-2x"></i>
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
                                <h6 class="font-w600">Active Users</h6>
                                <h2 class="mb-0"><?= number_format($activeUsers) ?></h2>
                            </div>
                            <div class="text-success">
                                <i class="fa fa-user-check fa-2x"></i>
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
                        <h4 class="card-title">Sale vs Expense vs Profit</h4>
                    </div>
                    <div class="card-body">
                        <canvas id="financialChart" height="300"></canvas>
                    </div>
                </div>
            </div>
            
            <div class="col-xl-6 col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Top 5 Services by Sale</h4>
                    </div>
                    <div class="card-body">
                        <canvas id="topServicesChart" height="300"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Daily Performance Trend</h4>
                    </div>
                    <div class="card-body">
                        <canvas id="dailyTrendChart" height="100"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">DSR Report</h4>
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
                        <form method="GET">
                            <div class="row align-items-end">
                                <div class="col-md-5">
                                    <div class="form-group">
                                        <label for="fromDt" class="form-label"><i class="fas fa-calendar me-2"></i>From Date</label>
                                        <input type="date" class="form-control" id="fromDt" name="fromDt" value="<?= $_GET['fromDt'] ?? '' ?>">
                                    </div>
                                </div>
                                <div class="col-md-5">
                                    <div class="form-group">
                                        <label for="toDt" class="form-label"><i class="fas fa-calendar me-2"></i>To Date</label>
                                        <input type="date" class="form-control" id="toDt" name="toDt" value="<?= $_GET['toDt'] ?? '' ?>">
                                    </div>
                                </div>
                                <div class="col-md-2 d-flex gap-2 align-items-end mb-3">
                                    <button class="btn btn-primary mt-2" type="submit">
                                        <i class="fas fa-search me-2"></i>Search
                                    </button>
                                    <a href="dsr-report.php" class="btn btn-primary border mt-2" title="Reset Filters">
                                        <i class="fas fa-redo"></i>
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
                                        <th>#</th>
                                        <th>Date</th>
                                        <th>Day</th>
                                        <th>Service</th>
                                        <th>Customer</th>
                                        <th>Customer Details</th>
                                        <th>Customer Address</th>
                                        <th>User</th>
                                        <th>Vendors</th>
                                        <th>Sale Amount</th>
                                        <th>Expense Amount</th>
                                        <th>Profit</th>
                                    </tr>
                                </thead>
                                <tbody>                              
                                <?php
                                    $sql = "SELECT 
                                        query_mst.*, 
                                        customers.name AS customer_name, 
                                        customers.number AS customer_mobile, 
                                        customers.email AS customer_email, 
                                        customers.address AS customer_address,
                                        users.name AS user_name,
                                        (SELECT COALESCE(SUM(amount), 0) FROM expenses WHERE query_id = query_mst.id) AS expense_amount,
                                        (query_mst.sale_amount - COALESCE((SELECT SUM(amount) FROM expenses WHERE query_id = query_mst.id), 0)) AS profit,
                                        (SELECT vendor.name 
                                         FROM expenses 
                                         LEFT JOIN vendor ON expenses.vendor_id = vendor.id
                                         WHERE expenses.query_id = query_mst.id 
                                         LIMIT 1) AS vendor_name
                                    FROM 
                                        query_mst
                                    LEFT JOIN customers ON query_mst.customer_id = customers.id
                                    LEFT JOIN users ON query_mst.user_id = users.id
                                    WHERE $where
                                    ORDER BY query_mst.id DESC";

                                    $stmt = $mysqli->prepare($sql);
                                    if (!empty($params)) 
                                    {
                                        $stmt->bind_param($types, ...$params);
                                    }
                                    $stmt->execute();
                                    $res = $stmt->get_result();
                                    $sno = 1;

                                    while ($row = $res->fetch_assoc()) 
                                    {
                                        $profitClass = $row['profit'] >= 0 ? 'text-success' : 'text-danger';
                                ?>
                                    <tr>
                                        <td><?= $sno ?></td>
                                        <td style="width: 150px !important;"><?php echo date("Y-m-d", strtotime($row['created_at'])) ?></td>
                                        <td><?php echo date("l", strtotime($row['created_at'])); ?></td>
                                        <td><?= htmlspecialchars($row['service'] ?? '') ?></td>
                                        <td><?= htmlspecialchars($row['customer_name'] ?? '') ?></td>
                                        <td>
                                            <small>
                                                <strong>Mobile:</strong> <?= htmlspecialchars($row['customer_mobile'] ?? '') ?><br>
                                                <strong>Email:</strong> <?= htmlspecialchars($row['customer_email'] ?? '') ?>
                                            </small>
                                        </td>
                                        <td><?= htmlspecialchars($row['customer_address'] ?? '') ?></td>
                                        <td><?= htmlspecialchars($row['user_name'] ?? '') ?></td>
                                        <td><?= htmlspecialchars($row['vendor_name'] ?? '') ?></td>
                                        <td class="text-success">₹<?= number_format($row['sale_amount'] ?? 0, 2) ?></td>
                                        <td class="text-danger">₹<?= number_format($row['expense_amount'] ?? 0, 2) ?></td>
                                        <td class="<?= $profitClass ?> fw-bold">₹<?= number_format($row['profit'] ?? 0, 2) ?></td>
                                    </tr>
                                    <?php
                                        $sno++;
                                    }
                                ?>
                                </tbody>
                                <tfoot>
                                    <tr class="fw-bold">
                                        <td colspan="8" class="text-end">Total:</td>
                                        <td class="text-success">₹<?= number_format($totalSale, 2) ?></td>
                                        <td class="text-danger">₹<?= number_format($totalExpense, 2) ?></td>
                                        <td class="text-warning">₹<?= number_format($totalProfit, 2) ?></td>
                                        <td></td>
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

<?php include "Layouts/Footer.php" ?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.28/jspdf.plugin.autotable.min.js"></script>

<script>
    
    $(document).ready(function() 
    {
        <?php
            $financialQuery = "SELECT 
                COALESCE(SUM(query_mst.sale_amount), 0) as total_sale,
                COALESCE(SUM(expenses.amount), 0) as total_expense,
                COALESCE(SUM(query_mst.sale_amount), 0) - COALESCE(SUM(expenses.amount), 0) as total_profit
                FROM query_mst
                LEFT JOIN expenses ON query_mst.id = expenses.query_id
                WHERE $where";
            
            $financialStmt = $mysqli->prepare($financialQuery);
            if (!empty($params)) 
            {
                $financialStmt->bind_param($types, ...$params);
            }
            $financialStmt->execute();
            $financial = $financialStmt->get_result()->fetch_assoc();
            $serviceQuery = "SELECT 
                service,
                COUNT(*) as count,
                COALESCE(SUM(sale_amount), 0) as total_sale
                FROM query_mst
                WHERE $where AND service IS NOT NULL AND service != ''
                GROUP BY service
                ORDER BY total_sale DESC
                LIMIT 5";
            
            $serviceStmt = $mysqli->prepare($serviceQuery);
            if (!empty($params)) 
            {
                $serviceStmt->bind_param($types, ...$params);
            }
            $serviceStmt->execute();
            $serviceResult = $serviceStmt->get_result();
            $serviceNames = [];
            $serviceSales = [];
            while ($row = $serviceResult->fetch_assoc()) 
            {
                $serviceNames[] = $row['service'];
                $serviceSales[] = $row['total_sale'];
            }
            $trendQuery = "SELECT 
                DATE(created_at) as date,
                COUNT(*) as order_count,
                COALESCE(SUM(sale_amount), 0) as daily_sale,
                COALESCE(SUM((SELECT COALESCE(SUM(amount), 0) FROM expenses WHERE query_id = query_mst.id)), 0) as daily_expense
                FROM query_mst
                WHERE $where
                GROUP BY DATE(created_at)
                ORDER BY date DESC
                LIMIT 7";
            
            $trendStmt = $mysqli->prepare($trendQuery);
            if (!empty($params)) 
            {
                $trendStmt->bind_param($types, ...$params);
            }
            $trendStmt->execute();
            $trendResult = $trendStmt->get_result();
            $trendDates = [];
            $trendSales = [];
            $trendExpenses = [];
            $trendProfits = [];
            while ($row = $trendResult->fetch_assoc()) 
            {
                array_unshift($trendDates, date('d M', strtotime($row['date'])));
                array_unshift($trendSales, $row['daily_sale']);
                array_unshift($trendExpenses, $row['daily_expense']);
                array_unshift($trendProfits, $row['daily_sale'] - $row['daily_expense']);
            }
        ?>
        const ctx1 = document.getElementById('financialChart').getContext('2d');
        new Chart(ctx1, {
            type: 'bar',
            data: {
                labels: ['Sale', 'Expense', 'Profit'],
                datasets: [{
                    label: 'Amount (₹)',
                    data: [
                        <?= $financial['total_sale'] ?? 0 ?>,
                        <?= $financial['total_expense'] ?? 0 ?>,
                        <?= $financial['total_profit'] ?? 0 ?>
                    ],
                    backgroundColor: ['#28a745', '#dc3545', '#ffc107'],
                    borderColor: ['#1e7e34', '#bd2130', '#e0a800'],
                    borderWidth: 1,
                    borderRadius: 5
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
                                return `₹${context.parsed.y.toLocaleString()}`;
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
                        }
                    }
                }
            }
        });
        if (document.getElementById('topServicesChart')) 
        {
            const ctx2 = document.getElementById('topServicesChart').getContext('2d');
            new Chart(ctx2, {
                type: 'bar',
                data: {
                    labels: <?= json_encode($serviceNames) ?>,
                    datasets: [{
                        label: 'Total Sale (₹)',
                        data: <?= json_encode($serviceSales) ?>,
                        backgroundColor: '#3b7ddd',
                        borderColor: '#2c5aa6',
                        borderWidth: 1,
                        borderRadius: 5
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    indexAxis: 'y',
                    plugins: {
                        legend: {
                            position: 'top',
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return `₹${context.parsed.x.toLocaleString()}`;
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return '₹' + value.toLocaleString();
                                }
                            }
                        }
                    }
                }
            });
        }
        const ctx3 = document.getElementById('dailyTrendChart').getContext('2d');
        new Chart(ctx3, {
            type: 'line',
            data: {
                labels: <?= json_encode($trendDates) ?>,
                datasets: [
                    {
                        label: 'Sale Amount',
                        data: <?= json_encode($trendSales) ?>,
                        borderColor: '#28a745',
                        backgroundColor: 'rgba(40, 167, 69, 0.1)',
                        tension: 0.4,
                        fill: true
                    },
                    {
                        label: 'Expense Amount',
                        data: <?= json_encode($trendExpenses) ?>,
                        borderColor: '#dc3545',
                        backgroundColor: 'rgba(220, 53, 69, 0.1)',
                        tension: 0.4,
                        fill: true
                    },
                    {
                        label: 'Profit',
                        data: <?= json_encode($trendProfits) ?>,
                        borderColor: '#ffc107',
                        backgroundColor: 'rgba(255, 193, 7, 0.1)',
                        tension: 0.4,
                        fill: true
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
                        callbacks: {
                            label: function(context) {
                                return `${context.dataset.label}: ₹${context.parsed.y.toLocaleString()}`;
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
                        }
                    }
                }
            }
        });
        $('#exportExcel').click(function() 
        {
            var table = document.getElementById('example');
            var wb = XLSX.utils.table_to_book(table, {
                sheet: "DSR Report"
            });
            XLSX.writeFile(wb, "dsr-report.xlsx");
        });
        $('.ReportbtnExportPDF').click(function() 
        {
            var { jsPDF } = window.jspdf;
            var doc = new jsPDF('l', 'pt', 'a4');
            
            doc.setFontSize(16);
            doc.text("DSR Report", 40, 40);
            
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
                $(this).find("td").each(function() {
                    row.push($(this).text().trim());
                });
                data.push(row);
            });
            
            doc.autoTable({
                head: [headers],
                body: data,
                startY: 80,
                styles: { fontSize: 7 },
                headStyles: { fillColor: [75, 108, 183] }
            });
            
            doc.save("dsr-report.pdf");
        });
    });
</script>