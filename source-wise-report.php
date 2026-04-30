<?php
    include "Layouts/Header.php";
    include "Layouts/Sidebar.php";

    $from = $_GET['from'] ?? '';
    $to = $_GET['to'] ?? '';
?>

<div class="content-body">
    <div class="container-fluid">
        <div class="row">
            <?php
                $dateCondition = "";
                if ($from != "" && $to != "") 
                {
                    $dateCondition = " AND DATE(q.created_at) BETWEEN '$from' AND '$to' ";
                }
                $statsQuery = "
                    SELECT 
                        COUNT(DISTINCT s.id) as total_sources,
                        COUNT(q.id) as total_queries,
                        SUM(CASE WHEN q.status = 'Converted' THEN 1 ELSE 0 END) as total_converted,
                        SUM(CASE WHEN q.status = 'Completed' THEN 1 ELSE 0 END) as total_completed,
                        SUM(CASE WHEN q.status = 'Follow Up' THEN 1 ELSE 0 END) as total_follow_up,
                        SUM(CASE WHEN q.status = 'New Query' THEN 1 ELSE 0 END) as total_new_queries,
                        SUM(CASE WHEN q.status = 'Lost' THEN 1 ELSE 0 END) as total_lost,
                        ROUND(
                            CASE 
                                WHEN COUNT(q.id) > 0 
                                THEN (SUM(CASE WHEN q.status = 'Converted' THEN 1 ELSE 0 END) * 100.0 / COUNT(q.id))
                                ELSE 0 
                            END, 1
                        ) as overall_conversion_rate,
                        ROUND(
                            CASE 
                                WHEN COUNT(q.id) > 0 
                                THEN (SUM(CASE WHEN q.status IN ('Completed', 'Converted') THEN 1 ELSE 0 END) * 100.0 / COUNT(q.id))
                                ELSE 0 
                            END, 1
                        ) as overall_success_rate
                    FROM source s
                    LEFT JOIN query_mst q ON s.name = q.source
                    WHERE s.active = 1 $dateCondition
                ";
                
                $statsResult = $mysqli->query($statsQuery);
                $stats = $statsResult->fetch_assoc();
                
                $totalSources = $stats['total_sources'] ?? 0;
                $totalQueries = $stats['total_queries'] ?? 0;
                $totalConverted = $stats['total_converted'] ?? 0;
                $totalCompleted = $stats['total_completed'] ?? 0;
                $totalFollowUp = $stats['total_follow_up'] ?? 0;
                $totalNewQueries = $stats['total_new_queries'] ?? 0;
                $totalLost = $stats['total_lost'] ?? 0;
                $overallConversionRate = $stats['overall_conversion_rate'] ?? 0;
                $overallSuccessRate = $stats['overall_success_rate'] ?? 0;
                $bestSourceQuery = "
                    SELECT 
                        s.name as source_name,
                        COUNT(q.id) as total_queries,
                        SUM(CASE WHEN q.status = 'Converted' THEN 1 ELSE 0 END) as converted,
                        ROUND(
                            CASE 
                                WHEN COUNT(q.id) > 0 
                                THEN (SUM(CASE WHEN q.status = 'Converted' THEN 1 ELSE 0 END) * 100.0 / COUNT(q.id))
                                ELSE 0 
                            END, 1
                        ) as conversion_rate
                    FROM source s
                    LEFT JOIN query_mst q ON s.name = q.source
                    WHERE s.active = 1 $dateCondition
                    GROUP BY s.id, s.name
                    HAVING total_queries > 0
                    ORDER BY conversion_rate DESC
                    LIMIT 1
                ";
                $bestResult = $mysqli->query($bestSourceQuery);
                $bestSource = $bestResult->fetch_assoc();
                $bestSourceName = $bestSource['source_name'] ?? 'N/A';
                $bestSourceRate = $bestSource['conversion_rate'] ?? 0;
            ?>
            
            <div class="col-xl-3 col-lg-4 col-sm-6">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="font-w600">Total Sources</h6>
                                <h2 class="mb-0"><?= number_format($totalSources) ?></h2>
                            </div>
                            <div class="text-primary">
                                <i class="fa fa-tag fa-2x"></i>
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
                                <h6 class="font-w600">Conversion Rate</h6>
                                <h2 class="mb-0"><?= $overallConversionRate ?>%</h2>
                            </div>
                            <div class="text-success">
                                <i class="fa fa-chart-line fa-2x"></i>
                            </div>
                        </div>
                        <div class="progress mt-3" style="height: 6px;">
                            <div class="progress-bar bg-success" style="width: <?= $overallConversionRate ?>%"></div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-xl-3 col-lg-4 col-sm-6">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="font-w600">Best Source</h6>
                                <h2 class="mb-0"><?= htmlspecialchars($bestSourceName) ?></h2>
                                <small class="text-muted"><?= $bestSourceRate ?>% conversion</small>
                            </div>
                            <div class="text-warning">
                                <i class="fa fa-trophy fa-2x"></i>
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
                        <h4 class="card-title">Source Performance Comparison</h4>
                    </div>
                    <div class="card-body">
                        <canvas id="sourcePerformanceChart" height="300"></canvas>
                    </div>
                </div>
            </div>
            
            <div class="col-xl-6 col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Query Status Distribution by Source</h4>
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
                        <h4 class="card-title">Source-wise Conversion Rates</h4>
                    </div>
                    <div class="card-body">
                        <canvas id="conversionRateChart" height="100"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Source Wise Report</h4>
                        <div class="card-header-toolbar">
                            <button class="btn btn-success btn-sm" id="export-excel">
                                <i class="fa fa-file-excel"></i> Export Excel
                            </button>
                            <button class="btn btn-danger btn-sm ReportbtnExportPDF">
                                <i class="fa fa-file-pdf"></i> Export PDF
                            </button>
                        </div>
                    </div>
                    <div class="row mb-3 px-3 mt-2">
                        <div class="col-md-3">
                            <label>From Date</label>
                            <input type="date" id="from_date" value="<?= htmlspecialchars($from) ?>" class="form-control">
                        </div>
                        <div class="col-md-3">
                            <label>To Date</label>
                            <input type="date" id="to_date" value="<?= htmlspecialchars($to) ?>" class="form-control">
                        </div>
                        <div class="col-md-3 gap-2 d-flex align-items-end">
                            <button class="btn btn-primary w-100" id="filter-date">
                                <i class="fa fa-filter"></i> Apply
                            </button>
                            <button class="btn btn-warning w-100" id="clear-filter">
                                <i class="fa fa-refresh"></i> Clear
                            </button>
                        </div>
                        <!-- <div class="col-md-3 gap-2 d-flex align-items-end">
                            <button class="btn btn-success w-100" id="export-excel-top">
                                <i class="fa fa-file-excel"></i> Export Excel
                            </button>
                            <button class="btn btn-danger w-100 ReportbtnExportPDF">
                                <i class="fa fa-file-pdf"></i> Export PDF
                            </button>
                        </div> -->
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="example" class="display" style="min-width: 845px">
                                <thead>
                                    <tr>
                                        <th>S.No</th>
                                        <th>Source Name</th>
                                        <th>Total Queries</th>
                                        <th>Converted</th>
                                        <th>Completed</th>
                                        <th>Follow Up</th>
                                        <th>New Queries</th>
                                        <th>Lost</th>
                                        <th>Conversion %</th>
                                        <th>Success %</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php
                                    $query = "
                                        SELECT 
                                            s.name as source_name,
                                            COUNT(q.id) AS total_queries,
                                            SUM(CASE WHEN q.status = 'Converted' THEN 1 ELSE 0 END) AS converted,
                                            SUM(CASE WHEN q.status = 'Completed' THEN 1 ELSE 0 END) AS completed,
                                            SUM(CASE WHEN q.status = 'Follow Up' THEN 1 ELSE 0 END) AS follow_up,
                                            SUM(CASE WHEN q.status = 'New Query' THEN 1 ELSE 0 END) AS new_queries,
                                            SUM(CASE WHEN q.status = 'Lost' THEN 1 ELSE 0 END) AS lost,
                                            ROUND(
                                                CASE 
                                                    WHEN COUNT(q.id) > 0 
                                                    THEN (SUM(CASE WHEN q.status = 'Converted' THEN 1 ELSE 0 END) * 100.0 / COUNT(q.id))
                                                    ELSE 0 
                                                END, 1
                                            ) AS conversion_percentage,
                                            ROUND(
                                                CASE 
                                                    WHEN COUNT(q.id) > 0 
                                                    THEN (SUM(CASE WHEN q.status IN ('Completed', 'Converted') THEN 1 ELSE 0 END) * 100.0 / COUNT(q.id))
                                                    ELSE 0 
                                                END, 1
                                            ) AS success_percentage
                                        FROM source s
                                        LEFT JOIN query_mst q 
                                            ON s.name = q.source
                                        WHERE s.active = 1
                                    ";
                                    if ($from != "" && $to != "") 
                                    {
                                        $query .= " AND DATE(q.created_at) BETWEEN '$from' AND '$to' ";
                                    }
                                    
                                    $query .= "
                                        GROUP BY s.id, s.name
                                        ORDER BY total_queries DESC
                                    ";

                                    $stmt = $mysqli->prepare($query);
                                    $stmt->execute();
                                    $res = $stmt->get_result();
                                    $sno = 1;
                                    $chartSources = [];
                                    $chartTotals = [];
                                    $chartConverted = [];
                                    $chartRates = [];
                                    $chartCompleted = [];
                                    $chartFollowUp = [];
                                    $chartNew = [];
                                    $chartLost = [];

                                    while ($row = $res->fetch_assoc()) 
                                    {
                                        $conversionClass = $row['conversion_percentage'] >= 30 ? 'success' : ($row['conversion_percentage'] >= 15 ? 'warning' : 'danger');
                                        $successClass = $row['success_percentage'] >= 50 ? 'success' : ($row['success_percentage'] >= 30 ? 'warning' : 'danger');
                                        if ($row['total_queries'] > 0) 
                                        {
                                            $chartSources[] = $row['source_name'];
                                            $chartTotals[] = $row['total_queries'];
                                            $chartConverted[] = $row['converted'];
                                            $chartRates[] = $row['conversion_percentage'];
                                            $chartCompleted[] = $row['completed'];
                                            $chartFollowUp[] = $row['follow_up'];
                                            $chartNew[] = $row['new_queries'];
                                            $chartLost[] = $row['lost'];
                                        }
                                        echo '
                                            <tr>
                                                <td>' . $sno++ . '</td>
                                                <td><strong>' . htmlspecialchars($row['source_name']) . '</strong></td>
                                                <td><span class="badge badge-primary">' . $row['total_queries'] . '</span></td>
                                                <td><span class="badge badge-success">' . $row['converted'] . '</span></td>
                                                <td><span class="badge badge-info">' . $row['completed'] . '</span></td>
                                                <td><span class="badge badge-warning">' . $row['follow_up'] . '</span></td>
                                                <td><span class="badge badge-secondary">' . $row['new_queries'] . '</span></td>
                                                <td><span class="badge badge-danger">' . $row['lost'] . '</span></td>
                                                <td><span class="badge badge-' . $conversionClass . '">' . $row['conversion_percentage'] . '%</span></td>
                                                <td><span class="badge badge-' . $successClass . '">' . $row['success_percentage'] . '%</span></td>
                                            </tr>';
                                    }
                                ?>
                                </tbody>
                                <tfoot>
                                    <tr class="fw-bold">
                                        <td colspan="2" class="text-end"><strong>Total:</strong></td>
                                        <td><strong><?= number_format($totalQueries) ?></strong></td>
                                        <td><strong><?= number_format($totalConverted) ?></strong></td>
                                        <td><strong><?= number_format($totalCompleted) ?></strong></td>
                                        <td><strong><?= number_format($totalFollowUp) ?></strong></td>
                                        <td><strong><?= number_format($totalNewQueries) ?></strong></td>
                                        <td><strong><?= number_format($totalLost) ?></strong></td>
                                        <td><strong><?= $overallConversionRate ?>%</strong></td>
                                        <td><strong><?= $overallSuccessRate ?>%</strong></td>
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

<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.28/jspdf.plugin.autotable.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
$(document).ready(function () 
{
    $("#filter-date, #export-excel-top").click(function () 
    {
        let from = $("#from_date").val();
        let to = $("#to_date").val();

        if (from === "" || to === "") 
        {
            alert("Please select both From and To dates");
            return;
        }

        window.location.href = "?from=" + from + "&to=" + to;
    });
    
    $("#clear-filter").click(function () 
    {
        window.location.href = "source-wise-report.php"; 
    });

    $("#export-excel").on("click", function () 
    {
        var table = document.getElementById("example");
        var wb = XLSX.utils.table_to_book(table, {
            sheet: "Source Wise Report"
        });
        XLSX.writeFile(wb, "source-wise-report.xlsx");
    });

    $(".ReportbtnExportPDF").on("click", function () 
    {
        var { jsPDF } = window.jspdf;
        var doc = new jsPDF('l', 'pt', 'a4');
        doc.setFontSize(16);
        doc.text("Source Wise Report", 40, 40);

        let now = new Date().toLocaleString();
        doc.setFontSize(10);
        doc.text("Generated On: " + now, 40, 60);

        let headers = [];
        $("#example thead th").each(function () 
        {
            headers.push($(this).text().trim());
        });

        let data = [];
        $("#example tbody tr").each(function ()
        {
            let row = [];
            $(this).find("td").each(function () 
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
        doc.save("source-wise-report.pdf");
    });
    var chartSources = <?= json_encode($chartSources) ?>;
    var chartTotals = <?= json_encode($chartTotals) ?>;
    var chartConverted = <?= json_encode($chartConverted) ?>;
    var chartRates = <?= json_encode($chartRates) ?>;
    var chartCompleted = <?= json_encode($chartCompleted) ?>;
    var chartFollowUp = <?= json_encode($chartFollowUp) ?>;
    var chartNew = <?= json_encode($chartNew) ?>;
    var chartLost = <?= json_encode($chartLost) ?>;
    if (document.getElementById('sourcePerformanceChart') && chartSources.length > 0) {
        const ctx1 = document.getElementById('sourcePerformanceChart').getContext('2d');
        new Chart(ctx1, {
            type: 'bar',
            data: {
                labels: chartSources,
                datasets: [
                    {
                        label: 'Total Queries',
                        data: chartTotals,
                        backgroundColor: '#3b7ddd',
                        borderColor: '#2c5aa6',
                        borderWidth: 1,
                        borderRadius: 5
                    },
                    {
                        label: 'Converted',
                        data: chartConverted,
                        backgroundColor: '#28a745',
                        borderColor: '#1e7e34',
                        borderWidth: 1,
                        borderRadius: 5
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
                                return `${context.dataset.label}: ${context.parsed.y}`;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });
    } 
    else 
    {
        document.getElementById('sourcePerformanceChart').innerHTML = '<div class="text-center text-muted mt-5">No data available for the selected period</div>';
    }
    if (document.getElementById('statusDistributionChart') && chartSources.length > 0) 
    {
        const ctx2 = document.getElementById('statusDistributionChart').getContext('2d');
        new Chart(ctx2, {
            type: 'bar',
            data: {
                labels: chartSources,
                datasets: [
                    {
                        label: 'Converted',
                        data: chartConverted,
                        backgroundColor: '#28a745',
                        borderColor: '#1e7e34',
                        borderWidth: 1
                    },
                    {
                        label: 'Completed',
                        data: chartCompleted,
                        backgroundColor: '#17a2b8',
                        borderColor: '#117a8b',
                        borderWidth: 1
                    },
                    {
                        label: 'Follow Up',
                        data: chartFollowUp,
                        backgroundColor: '#ffc107',
                        borderColor: '#e0a800',
                        borderWidth: 1
                    },
                    {
                        label: 'New Queries',
                        data: chartNew,
                        backgroundColor: '#6c757d',
                        borderColor: '#5a6268',
                        borderWidth: 1
                    },
                    {
                        label: 'Lost',
                        data: chartLost,
                        backgroundColor: '#dc3545',
                        borderColor: '#bd2130',
                        borderWidth: 1
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            boxWidth: 12,
                            font: { size: 10 }
                        }
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false
                    }
                },
                scales: {
                    x: {
                        stacked: true,
                    },
                    y: {
                        stacked: true,
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });
    } 
    else 
    {
        document.getElementById('statusDistributionChart').innerHTML = '<div class="text-center text-muted mt-5">No data available for the selected period</div>';
    }
    if (document.getElementById('conversionRateChart') && chartSources.length > 0) 
    {
        const ctx3 = document.getElementById('conversionRateChart').getContext('2d');
        const barColors = chartRates.map(rate => {
            if (rate >= 30) return '#28a745';
            if (rate >= 15) return '#ffc107';
            return '#dc3545';
        });
        
        new Chart(ctx3, {
            type: 'bar',
            data: {
                labels: chartSources,
                datasets: [{
                    label: 'Conversion Rate (%)',
                    data: chartRates,
                    backgroundColor: barColors,
                    borderColor: '#fff',
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
                                return `Conversion Rate: ${context.parsed.y}%`;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100,
                        ticks: {
                            callback: function(value) {
                                return value + '%';
                            }
                        }
                    }
                }
            }
        });
    } 
    else 
    {
        document.getElementById('conversionRateChart').innerHTML = '<div class="text-center text-muted mt-5">No data available for the selected period</div>';
    }
});
</script>