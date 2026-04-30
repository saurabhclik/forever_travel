<?php
    include "Layouts/Header.php";
    include "Layouts/Sidebar.php";

    $where = "";
    $params = [];
    $types = "";

    if (isset($_GET['assign_user']) && !empty($_GET['assign_user'])) 
    {
        $where .= " AND q.user_id = ? ";
        $params[] = $_GET['assign_user'];
        $types .= "i";
    }

    $startDate = isset($_GET['start_date']) ? $_GET['start_date'] : '';
    $endDate   = isset($_GET['end_date']) ? $_GET['end_date'] : '';

    if ($startDate && $endDate) 
    {
        $where .= " AND q.created_at BETWEEN ? AND ? ";
        $params[] = $startDate . " 00:00:00";
        $params[] = $endDate . " 23:59:59";
        $types .= "ss";
    }
    $statsQuery = "
        SELECT 
            COUNT(DISTINCT d.id) AS total_destinations,
            COUNT(q.id) AS total_queries,
            SUM(CASE WHEN q.status = 'New Query' THEN 1 ELSE 0 END) AS total_active,
            SUM(CASE WHEN q.status = 'Completed' THEN 1 ELSE 0 END) AS total_completed,
            SUM(CASE WHEN q.status = 'Converted' THEN 1 ELSE 0 END) AS total_converted,
            SUM(CASE WHEN q.status = 'Lost' THEN 1 ELSE 0 END) AS total_lost,
            SUM(CASE WHEN q.status = 'Follow Up' THEN 1 ELSE 0 END) AS total_followup
        FROM destinations d
        LEFT JOIN query_mst q ON q.destination = d.id
        WHERE 1=1 $where
    ";
    
    $statsStmt = $mysqli->prepare($statsQuery);
    if (!empty($params)) 
    {
        $statsStmt->bind_param($types, ...$params);
    }
    $statsStmt->execute();
    $stats = $statsStmt->get_result()->fetch_assoc();
    
    $totalDestinations = $stats['total_destinations'] ?? 0;
    $totalQueries = $stats['total_queries'] ?? 0;
    $totalActive = $stats['total_active'] ?? 0;
    $totalCompleted = $stats['total_completed'] ?? 0;
    $totalConverted = $stats['total_converted'] ?? 0;
    $totalLost = $stats['total_lost'] ?? 0;
    $totalFollowup = $stats['total_followup'] ?? 0;
    $completionRate = $totalQueries > 0 ? round(($totalCompleted / $totalQueries) * 100, 1) : 0;
    $conversionRate = $totalQueries > 0 ? round(($totalConverted / $totalQueries) * 100, 1) : 0;

    $sql = "
        SELECT 
            d.name AS destination_name, 
            d.id AS destination_id,
            COUNT(q.id) AS total_queries,
            SUM(CASE WHEN q.status = 'New Query' THEN 1 ELSE 0 END) AS active_queries,
            SUM(CASE WHEN q.status = 'Completed' THEN 1 ELSE 0 END) AS completed_queries,
            SUM(CASE WHEN q.status = 'Converted' THEN 1 ELSE 0 END) AS converted_queries,
            SUM(CASE WHEN q.status = 'Lost' THEN 1 ELSE 0 END) AS lost_queries,
            SUM(CASE WHEN q.status = 'Follow Up' THEN 1 ELSE 0 END) AS followup_queries,
            ROUND(
                CASE 
                    WHEN COUNT(q.id) > 0 
                    THEN (SUM(CASE WHEN q.status = 'Completed' THEN 1 ELSE 0 END) * 100.0 / COUNT(q.id))
                    ELSE 0 
                END, 1
            ) AS completion_percentage,
            ROUND(
                CASE 
                    WHEN COUNT(q.id) > 0 
                    THEN (SUM(CASE WHEN q.status = 'Converted' THEN 1 ELSE 0 END) * 100.0 / COUNT(q.id))
                    ELSE 0 
                END, 1
            ) AS conversion_percentage
        FROM destinations d
        LEFT JOIN query_mst q ON q.destination = d.id
        WHERE 1=1 $where
        GROUP BY d.id, d.name
        ORDER BY total_queries DESC
    ";

    $stmt = $mysqli->prepare($sql);
    if (!empty($params)) 
    {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $res = $stmt->get_result();
?>

<div class="content-body">
    <div class="container-fluid">
        <div class="row">
            <div class="col-xl-3 col-lg-4 col-sm-6">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="font-w600">Total Destinations</h6>
                                <h2 class="mb-0"><?= number_format($totalDestinations) ?></h2>
                            </div>
                            <div class="text-primary">
                                <i class="fa fa-map-marker-alt fa-2x"></i>
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
                            <div class="text-info">
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
                                <h6 class="font-w600">Completion Rate</h6>
                                <h2 class="mb-0"><?= $completionRate ?>%</h2>
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
                                <h6 class="font-w600">Conversion Rate</h6>
                                <h2 class="mb-0"><?= $conversionRate ?>%</h2>
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
                                <h6 class="font-w600">Active Queries</h6>
                                <h2 class="mb-0"><?= number_format($totalActive) ?></h2>
                            </div>
                            <div class="text-primary">
                                <i class="fa fa-spinner fa-2x"></i>
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
                                <h6 class="font-w600">Completed</h6>
                                <h2 class="mb-0"><?= number_format($totalCompleted) ?></h2>
                            </div>
                            <div class="text-success">
                                <i class="fa fa-check-double fa-2x"></i>
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
                                <h6 class="font-w600">Converted</h6>
                                <h2 class="mb-0"><?= number_format($totalConverted) ?></h2>
                            </div>
                            <div class="text-info">
                                <i class="fa fa-trophy fa-2x"></i>
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
                                <h6 class="font-w600">Lost</h6>
                                <h2 class="mb-0"><?= number_format($totalLost) ?></h2>
                            </div>
                            <div class="text-danger">
                                <i class="fa fa-times-circle fa-2x"></i>
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
                        <h4 class="card-title">Top 10 Destinations by Queries</h4>
                    </div>
                    <div class="card-body">
                        <canvas id="topDestinationsChart" height="300"></canvas>
                    </div>
                </div>
            </div>
            
            <div class="col-xl-6 col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Query Status Distribution</h4>
                    </div>
                    <div class="card-body">
                        <canvas id="statusDistributionChart" height="300"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Top 10 Destination Performance Metrics</h4>
                    </div>
                    <div class="card-body">
                        <canvas id="performanceChart" height="100"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Destination Report</h4>
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
                            <div class="row g-2 align-items-end">
                                <div class="col-md-3">
                                    <label>Assign User</label>
                                    <select name="assign_user" class="form-control select2">
                                        <option value="">--All Users--</option>
                                        <?php
                                        $stmt_user = $mysqli->prepare("SELECT * FROM users WHERE status='1'");
                                        $stmt_user->execute();
                                        $result_user = $stmt_user->get_result();
                                        while ($user = $result_user->fetch_assoc()):
                                            $selected = (isset($_GET['assign_user']) && $_GET['assign_user'] == $user['id']) ? "selected" : "";
                                        ?>
                                            <option value="<?= $user['id'] ?>" <?= $selected ?>><?= htmlspecialchars($user['name']) ?></option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>

                                <div class="col-md-2">
                                    <label>Start Date</label>
                                    <input type="date" name="start_date" class="form-control" 
                                        value="<?= htmlspecialchars($startDate) ?>">
                                </div>

                                <div class="col-md-2">
                                    <label>End Date</label>
                                    <input type="date" name="end_date" class="form-control" 
                                        value="<?= htmlspecialchars($endDate) ?>">
                                </div>

                                <div class="col-md-3">
                                    <button type="submit" class="btn btn-outline-success mt-2">
                                        <i class="fa fa-search"></i> Submit
                                    </button>
                                    <a href="destination_report.php" class="btn btn-outline-danger mt-2">
                                        <i class="fa fa-refresh"></i> Reset
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
                                        <th>Destination</th>
                                        <th>Active</th>
                                        <th>Follow Up</th>
                                        <th>Completed</th>
                                        <th>Converted</th>
                                        <th>Lost</th>
                                        <th>Total Leads</th>
                                        <th>Completion %</th>
                                        <th>Conversion %</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while($row = $res->fetch_assoc()) { 
                                        $completionClass = $row['completion_percentage'] >= 50 ? 'success' : ($row['completion_percentage'] >= 25 ? 'warning' : 'danger');
                                        $conversionClass = $row['conversion_percentage'] >= 30 ? 'success' : ($row['conversion_percentage'] >= 15 ? 'warning' : 'danger');
                                    ?>
                                    <tr>
                                        <td><strong><?= htmlspecialchars($row['destination_name']) ?></strong></td>
                                        <td><span class="badge badge-primary"><?= $row['active_queries'] ?></span></td>
                                        <td><span class="badge badge-warning"><?= $row['followup_queries'] ?></span></td>
                                        <td><span class="badge badge-info"><?= $row['completed_queries'] ?></span></td>
                                        <td><span class="badge badge-success"><?= $row['converted_queries'] ?></span></td>
                                        <td><span class="badge badge-danger"><?= $row['lost_queries'] ?></span></td>
                                        <td><span class="badge badge-secondary"><?= $row['total_queries'] ?></span></td>
                                        <td><span class="badge badge-<?= $completionClass ?>"><?= $row['completion_percentage'] ?>%</span></td>
                                        <td><span class="badge badge-<?= $conversionClass ?>"><?= $row['conversion_percentage'] ?>%</span></td>
                                    </tr>
                                    <?php } ?>
                                </tbody>
                                <tfoot>
                                    <tr class="fw-bold">
                                        <td class="text-end"><strong>Total:</strong></td>
                                        <td><strong><?= number_format($totalActive) ?></strong></td>
                                        <td><strong><?= number_format($totalFollowup) ?></strong></td>
                                        <td><strong><?= number_format($totalCompleted) ?></strong></td>
                                        <td><strong><?= number_format($totalConverted) ?></strong></td>
                                        <td><strong><?= number_format($totalLost) ?></strong></td>
                                        <td><strong><?= number_format($totalQueries) ?></strong></td>
                                        <td><strong><?= $completionRate ?>%</strong></td>
                                        <td><strong><?= $conversionRate ?>%</strong></td>
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
        $topQuery = "
            SELECT 
                d.name AS destination_name,
                COUNT(q.id) AS total_queries
            FROM destinations d
            LEFT JOIN query_mst q ON q.destination = d.id
            WHERE 1=1 $where
            GROUP BY d.id, d.name
            ORDER BY total_queries DESC
            LIMIT 10
        ";
        $topStmt = $mysqli->prepare($topQuery);
        if (!empty($params)) 
        {
            $topStmt->bind_param($types, ...$params);
        }
        $topStmt->execute();
        $topResult = $topStmt->get_result();
        $topNames = [];
        $topCounts = [];
        while ($row = $topResult->fetch_assoc()) 
        {
            $topNames[] = $row['destination_name'];
            $topCounts[] = $row['total_queries'];
        }
        $statusQuery = "
            SELECT 
                SUM(CASE WHEN q.status = 'New Query' THEN 1 ELSE 0 END) AS active,
                SUM(CASE WHEN q.status = 'Follow Up' THEN 1 ELSE 0 END) AS followup,
                SUM(CASE WHEN q.status = 'Completed' THEN 1 ELSE 0 END) AS completed,
                SUM(CASE WHEN q.status = 'Converted' THEN 1 ELSE 0 END) AS converted,
                SUM(CASE WHEN q.status = 'Lost' THEN 1 ELSE 0 END) AS lost
            FROM destinations d
            LEFT JOIN query_mst q ON q.destination = d.id
            WHERE 1=1 $where
        ";
        $statusStmt = $mysqli->prepare($statusQuery);
        if (!empty($params)) 
        {
            $statusStmt->bind_param($types, ...$params);
        }
        $statusStmt->execute();
        $statusData = $statusStmt->get_result()->fetch_assoc();
        $perfQuery = "
            SELECT 
                d.name AS destination_name,
                ROUND(
                    CASE 
                        WHEN COUNT(q.id) > 0 
                        THEN (SUM(CASE WHEN q.status IN ('Completed', 'Converted') THEN 1 ELSE 0 END) * 100.0 / COUNT(q.id))
                        ELSE 0 
                    END, 1
                ) AS success_rate
            FROM destinations d
            LEFT JOIN query_mst q ON q.destination = d.id
            WHERE 1=1 $where
            GROUP BY d.id, d.name
            HAVING COUNT(q.id) > 0
            ORDER BY success_rate DESC
            LIMIT 10
        ";
        $perfStmt = $mysqli->prepare($perfQuery);
        if (!empty($params)) 
        {
            $perfStmt->bind_param($types, ...$params);
        }
        $perfStmt->execute();
        $perfResult = $perfStmt->get_result();
        $perfNames = [];
        $perfRates = [];
        while ($row = $perfResult->fetch_assoc()) 
        {
            $perfNames[] = $row['destination_name'];
            $perfRates[] = $row['success_rate'];
        }
    ?>
    if (document.getElementById('topDestinationsChart')) 
    {
        const ctx1 = document.getElementById('topDestinationsChart').getContext('2d');
        new Chart(ctx1, {
            type: 'bar',
            data: {
                labels: <?= json_encode($topNames) ?>,
                datasets: [{
                    label: 'Number of Queries',
                    data: <?= json_encode($topCounts) ?>,
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
                                return `Queries: ${context.parsed.x}`;
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });
    }
    if (document.getElementById('statusDistributionChart')) 
    {
        const ctx2 = document.getElementById('statusDistributionChart').getContext('2d');
        new Chart(ctx2, {
            type: 'doughnut',
            data: {
                labels: ['Active', 'Follow Up', 'Completed', 'Converted', 'Lost'],
                datasets: [{
                    data: [
                        <?= $statusData['active'] ?? 0 ?>,
                        <?= $statusData['followup'] ?? 0 ?>,
                        <?= $statusData['completed'] ?? 0 ?>,
                        <?= $statusData['converted'] ?? 0 ?>,
                        <?= $statusData['lost'] ?? 0 ?>
                    ],
                    backgroundColor: ['#17a2b8', '#ffc107', '#007bff', '#28a745', '#dc3545'],
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
    if (document.getElementById('performanceChart')) 
    {
        const ctx3 = document.getElementById('performanceChart').getContext('2d');
        new Chart(ctx3, {
            type: 'bar',
            data: {
                labels: <?= json_encode($perfNames) ?>,
                datasets: [{
                    label: 'Success Rate (%)',
                    data: <?= json_encode($perfRates) ?>,
                    backgroundColor: '#28a745',
                    borderColor: '#1e7e34',
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
                                return `Success Rate: ${context.parsed.y}%`;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100,
                        ticks: {
                            callback: function(value) 
                            {
                                return value + '%';
                            }
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
            sheet: "Destination Report"
        });
        XLSX.writeFile(wb, "destination-report.xlsx");
    });
    $('.ReportbtnExportPDF').click(function() 
    {
        var { jsPDF } = window.jspdf;
        var doc = new jsPDF('l', 'pt', 'a4');
        
        doc.setFontSize(16);
        doc.text("Destination Report", 40, 40);
        
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
        
        doc.save("destination-report.pdf");
    });
});
</script>