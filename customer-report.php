<?php
    include "Layouts/Header.php";
    include "Layouts/Sidebar.php";
    $startDate = isset($_GET['start_date']) ? $_GET['start_date'] : '';
    $endDate = isset($_GET['end_date']) ? $_GET['end_date'] : '';
?>

<div class="content-body">
    <div class="container-fluid">
        <div class="row">
            <?php
                $statsWhere = "1=1";
                if ($startDate && $endDate) 
                {
                    $statsWhere .= " AND `created_at` BETWEEN '$startDate 00:00:00' AND '$endDate 23:59:59'";
                }
                $customerQuery = "SELECT COUNT(*) as `total_customers` FROM `customers` WHERE $statsWhere";
                $customerResult = $mysqli->query($customerQuery);
                $totalCustomers = $customerResult->fetch_assoc()['total_customers'] ?? 0;
                $revenueQuery = "
                    SELECT COALESCE(SUM(p.amount), 0) as `total_revenue` 
                    FROM `payment` p
                    LEFT JOIN `order_mst` om ON p.query_id = om.id
                    WHERE 1=1
                ";
                if ($startDate && $endDate) 
                {
                    $revenueQuery .= " AND p.date BETWEEN '$startDate' AND '$endDate'";
                }
                $revenueResult = $mysqli->query($revenueQuery);
                $totalRevenue = $revenueResult->fetch_assoc()['total_revenue'] ?? 0;
                $todayQuery = "SELECT COUNT(*) as today_count FROM customers WHERE DATE(created_at) = CURDATE()";
                $todayResult = $mysqli->query($todayQuery);
                $todayCustomers = $todayResult->fetch_assoc()['today_count'] ?? 0;
                $monthQuery = "SELECT COUNT(*) as month_count FROM customers WHERE MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE())";
                $monthResult = $mysqli->query($monthQuery);
                $monthCustomers = $monthResult->fetch_assoc()['month_count'] ?? 0;
            ?>
            
            <div class="col-xl-3 col-lg-4 col-sm-6">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="font-w600">Total Customers</h6>
                                <h2 class="mb-0"><?= number_format($totalCustomers) ?></h2>
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
                                <h6 class="font-w600">Total Revenue</h6>
                                <h2 class="mb-0">₹<?= number_format($totalRevenue, 2) ?></h2>
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
                                <h6 class="font-w600">Today's Customers</h6>
                                <h2 class="mb-0"><?= number_format($todayCustomers) ?></h2>
                            </div>
                            <div class="text-warning">
                                <i class="fa fa-calendar-day fa-2x"></i>
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
                                <h6 class="font-w600">This Month</h6>
                                <h2 class="mb-0"><?= number_format($monthCustomers) ?></h2>
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
            <div class="col-xl-6 col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Customer Growth Trend</h4>
                    </div>
                    <div class="card-body">
                        <canvas id="customerTrendChart" height="300"></canvas>
                    </div>
                </div>
            </div>
            
            <div class="col-xl-6 col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Top Cities by Customers</h4>
                    </div>
                    <div class="card-body">
                        <canvas id="topCitiesChart" height="300"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Revenue Trend</h4>
                    </div>
                    <div class="card-body">
                        <canvas id="revenueTrendChart" height="100"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Customer Report</h4>
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
                            <form method="get" class="row g-2" id="filterForm">
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
                                        <th>S.No</th>
                                        <th>Title</th>
                                        <th>Name</th>
                                        <th>Phone Number</th>
                                        <th>Alternate No</th>
                                        <th>Email</th>
                                        <th>City</th>
                                        <th>State</th>
                                        <th>GST No</th>
                                        <th>Date Created</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                        $query = "SELECT * FROM customers";
                                        $params = [];
                                        $types = "";

                                        if ($startDate && $endDate) 
                                        {
                                            $query .= " WHERE DATE(created_at) BETWEEN ? AND ?";
                                            $params[] = $startDate;
                                            $params[] = $endDate;
                                            $types .= "ss";
                                        }
                                        
                                        $query .= " ORDER BY id DESC";

                                        $stmt = $mysqli->prepare($query);
                                        if (!empty($params)) 
                                        {
                                            $stmt->bind_param($types, ...$params);
                                        }
                                        $stmt->execute();
                                        $res = $stmt->get_result();

                                        $sno = 1;
                                        while ($row = $res->fetch_assoc()) 
                                        {
                                            $title = isset($row['pre_name']) ? htmlspecialchars($row['pre_name']) : '';
                                            $name = isset($row['name']) ? htmlspecialchars($row['name']) : '';
                                            $fullName = $title ? $title . ' ' . $name : $name;
                                            
                                            echo '<tr>
                                                <td>' . $sno++ . '</td>
                                                <td>' . $title . '</td>
                                                <td><strong>' . htmlspecialchars($row['name'] ?? '') . '</strong></td>
                                                <td>' . (isset($row['number']) ? htmlspecialchars($row['number']) : '') . '</td>
                                                <td>' . (isset($row['number2']) ? htmlspecialchars($row['number2']) : '-') . '</td>
                                                <td>' . (isset($row['email']) ? htmlspecialchars($row['email']) : '-') . '</td>
                                                <td>' . (isset($row['city']) ? htmlspecialchars($row['city']) : '-') . '</td>
                                                <td>' . (isset($row['state']) ? htmlspecialchars($row['state']) : '-') . '</td>
                                                <td>' . (isset($row['gst_no']) && $row['gst_no'] != '0' ? htmlspecialchars($row['gst_no']) : '-') . '</td>
                                                <td>' . (isset($row['created_at']) ? date('d-m-Y', strtotime($row['created_at'])) : '') . '</td>
                                            </tr>';
                                        }
                                        
                                        if ($sno == 1) 
                                        {
                                            echo '<tr><td colspan="10" class="text-center text-muted">No customers found</td><tr>';
                                        }
                                    ?>
                                </tbody>
                                <tfoot>
                                    <tr class="bg-light fw-bold">
                                        <td colspan="9" class="text-end"><strong>Total Customers:</strong></td>
                                        <td><strong><?= number_format($totalCustomers) ?></strong></td>
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
        <?php
            $trendLabels = [];
            $trendCounts = [];
            $revenueLabels = [];
            $revenueAmounts = [];
            $cityLabels = [];
            $cityCounts = [];
            
            $dateCondition = "";
            if ($startDate && $endDate) 
            {
                $dateCondition = " AND DATE(created_at) BETWEEN '$startDate' AND '$endDate'";
            }
            $trendQuery = "
                SELECT DATE(created_at) as date, COUNT(*) as count 
                FROM `customers` 
                WHERE 1=1 $dateCondition 
                GROUP BY DATE(created_at) 
                ORDER BY date ASC 
                LIMIT 30
            ";
            $trendResult = $mysqli->query($trendQuery);
            while ($row = $trendResult->fetch_assoc()) 
            {
                $trendLabels[] = date('d M', strtotime($row['date']));
                $trendCounts[] = $row['count'];
            }
            $revenueQuery = "
                SELECT DATE(p.date) as date, COALESCE(SUM(p.amount), 0) as total 
                FROM payment p
                WHERE 1=1
            ";
            if ($startDate && $endDate) 
            {
                $revenueQuery .= " AND DATE(p.date) BETWEEN '$startDate' AND '$endDate'";
            }
            $revenueQuery .= " GROUP BY DATE(p.date) ORDER BY date ASC LIMIT 30";
            $revenueResult = $mysqli->query($revenueQuery);
            while ($row = $revenueResult->fetch_assoc()) 
            {
                $revenueLabels[] = date('d M', strtotime($row['date']));
                $revenueAmounts[] = $row['total'];
            }
            $cityQuery = "
                SELECT 
                    CASE 
                        WHEN city IS NULL OR city = '' OR city = '0' THEN 'Not Specified'
                        ELSE city 
                    END as city_name,
                    COUNT(*) as customer_count
                FROM `customers`
                WHERE 1=1 $dateCondition
                GROUP BY city_name
                ORDER BY customer_count DESC
                LIMIT 5
            ";
            $cityResult = $mysqli->query($cityQuery);
            while ($row = $cityResult->fetch_assoc()) 
            {
                $cityLabels[] = $row['city_name'];
                $cityCounts[] = $row['customer_count'];
            }
        ?>
        if (document.getElementById('customerTrendChart') && <?= count($trendLabels) ?> > 0) 
        {
            const ctx1 = document.getElementById('customerTrendChart').getContext('2d');
            new Chart(ctx1, {
                type: 'line',
                data: {
                    labels: <?= json_encode($trendLabels) ?>,
                    datasets: [{
                        label: 'New Customers',
                        data: <?= json_encode($trendCounts) ?>,
                        borderColor: '#3b7ddd',
                        backgroundColor: 'rgba(59, 125, 221, 0.1)',
                        tension: 0.4,
                        fill: true,
                        pointBackgroundColor: '#3b7ddd',
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
                                    return `New Customers: ${context.parsed.y}`;
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1
                            },
                            title: {
                                display: true,
                                text: 'Number of Customers'
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
        else if (document.getElementById('customerTrendChart')) 
        {
            document.getElementById('customerTrendChart').innerHTML = '<div class="text-center text-muted mt-5">No customer data available</div>';
        }
        if (document.getElementById('topCitiesChart') && <?= count($cityLabels) ?> > 0) 
        {
            const ctx2 = document.getElementById('topCitiesChart').getContext('2d');
            new Chart(ctx2, {
                type: 'bar',
                data: {
                    labels: <?= json_encode($cityLabels) ?>,
                    datasets: [{
                        label: 'Number of Customers',
                        data: <?= json_encode($cityCounts) ?>,
                        backgroundColor: ['#3b7ddd', '#28a745', '#ffc107', '#dc3545', '#17a2b8'],
                        borderColor: '#fff',
                        borderWidth: 2,
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
                                    return `Customers: ${context.parsed.y}`;
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1
                            },
                            title: {
                                display: true,
                                text: 'Number of Customers'
                            }
                        },
                        x: {
                            title: {
                                display: true,
                                text: 'City'
                            }
                        }
                    }
                }
            });
        } 
        else if (document.getElementById('topCitiesChart')) 
        {
            document.getElementById('topCitiesChart').innerHTML = '<div class="text-center text-muted mt-5">No city data available</div>';
        }
        if (document.getElementById('revenueTrendChart') && <?= count($revenueLabels) ?> > 0) 
        {
            const ctx3 = document.getElementById('revenueTrendChart').getContext('2d');
            new Chart(ctx3, {
                type: 'bar',
                data: {
                    labels: <?= json_encode($revenueLabels) ?>,
                    datasets: [{
                        label: 'Revenue (₹)',
                        data: <?= json_encode($revenueAmounts) ?>,
                        backgroundColor: '#28a745',
                        borderColor: '#1e7e34',
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
                                    return 'Revenue: ₹' + context.parsed.y.toLocaleString();
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) 
                                {
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
        else if (document.getElementById('revenueTrendChart')) 
        {
            document.getElementById('revenueTrendChart').innerHTML = '<div class="text-center text-muted mt-5">No revenue data available</div>';
        }
        $('#exportExcel').click(function() 
        {
            var table = document.getElementById('example');
            var wb = XLSX.utils.table_to_book(table, {
                sheet: "Customer Report"
            });
            XLSX.writeFile(wb, "customer-report.xlsx");
        });
        $('.ReportbtnExportPDF').click(function() 
        {
            var { jsPDF } = window.jspdf;
            var doc = new jsPDF('l', 'pt', 'a4');
            
            doc.setFontSize(16);
            doc.text("Customer Report", 40, 40);
            
            let now = new Date().toLocaleString();
            doc.setFontSize(10);
            doc.text("Generated On: " + now, 40, 60);
            doc.setFontSize(12);
            doc.text("Summary Statistics:", 40, 80);
            doc.setFontSize(10);
            doc.text(`Total Customers: <?= number_format($totalCustomers) ?>`, 40, 95);
            doc.text(`Total Revenue: ₹<?= number_format($totalRevenue, 2) ?>`, 200, 95);
            doc.text(`Today's Customers: <?= number_format($todayCustomers) ?>`, 360, 95);
            doc.text(`This Month: <?= number_format($monthCustomers) ?>`, 520, 95);
            
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
            
            doc.save("customer-report.pdf");
        });
    });
</script>
