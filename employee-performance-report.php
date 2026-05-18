<?php
include "Layouts/Header.php";
include "Layouts/Sidebar.php";

$from = $_GET['from'] ?? '';
$to = $_GET['to'] ?? '';
?>

<style>
    .save-field {
    border: none !important;
    border-bottom: 1px solid #ccc !important;
    border-radius: 0 !important;
    background: transparent !important;
    text-align: center;
    padding: 4px 6px;
    font-size: 13px;
    box-shadow: none !important;
}

/* Focus effect */
.save-field:focus {
    border-bottom: 2px solid #28a745 !important;
    outline: none;
    background: #fff;
}

/* Remove red error border */
.save-field:invalid {
    border-bottom: 1px solid #ccc !important;
    box-shadow: none;
}

/* Remove arrows - Chrome, Safari */
input[type=number]::-webkit-inner-spin-button,
input[type=number]::-webkit-outer-spin-button {
    -webkit-appearance: none;
    margin: 0;
}

/* Remove arrows - Firefox */
input[type=number] {
    -moz-appearance: textfield;
}
    </style>

<div class="content-body">
    <div class="container-fluid">
        <div class="row">
            <?php
                $statsQuery = "
                    SELECT 
                        COUNT(DISTINCT u.id) as total_employees,
                        COUNT(q.id) as total_queries,
                        SUM(CASE WHEN q.status = 'Converted' THEN 1 ELSE 0 END) as total_converted,
                        SUM(CASE WHEN q.status = 'Completed' THEN 1 ELSE 0 END) as total_completed,
                        SUM(CASE WHEN q.status = 'Follow Up' THEN 1 ELSE 0 END) as total_follow_up,
                        SUM(CASE WHEN q.status = 'New Query' THEN 1 ELSE 0 END) as total_new_queries,
                        SUM(CASE WHEN q.status = 'Lost' THEN 1 ELSE 0 END) as total_lost,
                        ROUND(AVG(CASE WHEN q.status = 'Converted' THEN 1 ELSE 0 END) * 100, 1) as conversion_rate
                    FROM users u
                    LEFT JOIN query_mst q ON u.id = q.user_id
                    WHERE u.role != 'admin'
                ";
                
                if ($from != "" && $to != "") 
                {
                    $statsQuery .= " AND DATE(q.created_at) BETWEEN '$from' AND '$to' ";
                }
                
                $statsResult = $mysqli->query($statsQuery);
                $stats = $statsResult->fetch_assoc();
                
                $totalEmployees = $stats['total_employees'] ?? 0;
                $totalQueries = $stats['total_queries'] ?? 0;
                $totalConverted = $stats['total_converted'] ?? 0;
                $totalCompleted = $stats['total_completed'] ?? 0;
                $totalFollowUp = $stats['total_follow_up'] ?? 0;
                $totalNewQueries = $stats['total_new_queries'] ?? 0;
                $totalLost = $stats['total_lost'] ?? 0;
                $conversionRate = $stats['conversion_rate'] ?? 0;
            ?>
            
            <div class="col-xl-3 col-lg-4 col-sm-6">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="font-w600">Total Employees</h6>
                                <h2 class="mb-0"><?= number_format($totalEmployees) ?></h2>
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
                                <h2 class="mb-0"><?= $conversionRate ?>%</h2>
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
                                <h6 class="font-w600">Converted Queries</h6>
                                <h2 class="mb-0"><?= number_format($totalConverted) ?></h2>
                            </div>
                            <div class="text-warning">
                                <i class="fa fa-check-circle fa-2x"></i>
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
                        <h4 class="card-title">Query Status Distribution</h4>
                    </div>
                    <div class="card-body">
                        <canvas id="queryStatusChart" height="300"></canvas>
                    </div>
                </div>
            </div>
            
            <div class="col-xl-6 col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Top 5 Performers</h4>
                    </div>
                    <div class="card-body">
                        <canvas id="topPerformersChart" height="300"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Query Trend</h4>
                    </div>
                    <div class="card-body">
                        <canvas id="queryTrendChart" height="100"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Employee Performance Report</h4>
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
                                        <th>Employee Name</th>
                                        <th>Role</th>
                                        <th>Total Queries</th>
                                        <th>Converted</th>
                                        <th>Completed</th>
                                        <th>Follow Up</th>
                                        <th>New Queries</th>
                                        <th>Lost</th>
                                        <th>Conversion %</th>
                                        <th>Response Time(mins)</th>
                                        <th>Last Update</th>
                                        <th>Joining Date</th>
                                        <th>Status</th>
                                        <th>Total Leads</th>
                                        <th>Add-on Sales ₹</th>
                                        <th>Review Quality (1-5)</th>
                                        <th>Task Accuracy %</th>
                                        <th>Attendance Days Missed</th>
                                        <th>Trainings Missed</th>
                                        <th>Knowledge Applied (1-10)</th>
                                        <th>Process Accuracy %</th>
                                        <th>Collaboration (1-7)</th>
                                        <th>Ownership (1-8)</th>
                                        <th>Values (1-5)</th>
                                        <th>Results Marks</th>
                                        <th>Skills Marks</th>
                                        <th>Attitude Marks</th>
                                        <th>Final Score</th>
                                        <th>Zone</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php
                                    $query = "
                                    SELECT 
                                        u.id,
                                        u.name,
                                        u.role,
                                        u.status,
                                        u.joining_date,
                                        u.add_on_sale,
                                        u.review_quality,
                                        u.task_accuracy,
                                        u.attendance_days_missed,
                                        u.trainings_missed,
                                        u.knowledge_applied,
                                        u.process_accuracy,
                                        u.collaboration,
                                        u.ownership,
                                        u.values_data,

                                        COUNT(q.id) AS total_queries,

                                        SUM(CASE WHEN q.status = 'Converted' THEN 1 ELSE 0 END) AS converted,
                                        SUM(CASE WHEN q.status = 'Completed' THEN 1 ELSE 0 END) AS completed,
                                        SUM(CASE WHEN q.status = 'Follow Up' THEN 1 ELSE 0 END) AS follow_up,
                                        SUM(CASE WHEN q.status = 'New Query' THEN 1 ELSE 0 END) AS new_queries,
                                        SUM(CASE WHEN q.status = 'Lost' THEN 1 ELSE 0 END) AS lost,

                                        MAX(q.updated_at) AS last_update,

                                        AVG(TIMESTAMPDIFF(MINUTE, q.created_at, q.updated_at)) AS avg_response_time,

                                        ROUND(
                                            CASE 
                                                WHEN COUNT(q.id) > 0 
                                                THEN (SUM(CASE WHEN q.status = 'Converted' THEN 1 ELSE 0 END) * 100.0 / COUNT(q.id))
                                                ELSE 0 
                                            END, 1
                                        ) AS conversion_percentage

                                    FROM users u
                                    LEFT JOIN query_mst q 
                                        ON u.id = q.user_id
                                    WHERE u.role != 'admin'
                                    ";
                                    if($_SESSION['user'] != 'admin')
                                    {
                                        $login_user_id = $_SESSION['id'];

                                        $query .= " AND u.id = '$login_user_id' ";
                                    }

                                    if (!empty($from) && !empty($to)) 
                                    {
                                        $query .= " AND DATE(q.created_at) BETWEEN '$from' AND '$to' ";
                                    }
                                    $query .= "
                                    GROUP BY 
                                        u.id,
                                        u.name,
                                        u.role,
                                        u.status,
                                        u.joining_date,
                                         u.add_on_sale,
                                        u.review_quality,
                                        u.task_accuracy,
                                        u.attendance_days_missed,
                                        u.trainings_missed,
                                        u.knowledge_applied,
                                        u.process_accuracy,
                                        u.collaboration,
                                        u.ownership,
                                        u.values_data

                                    ORDER BY 
                                        conversion_percentage DESC,
                                        total_queries DESC
                                    ";

                                    $stmt = $mysqli->prepare($query);
                                    $stmt->execute();
                                    $res = $stmt->get_result();

                                    $sno = 1;

                                    while ($row = $res->fetch_assoc()) 
                                    {
                                        $status_badge = ($row['status'] == 1)
                                            ? '<span class="badge badge-success">Active</span>'
                                            : '<span class="badge badge-danger">Inactive</span>';

                                        $joining_date = $row['joining_date']
                                            ? date('d M Y', strtotime($row['joining_date']))
                                            : 'Not Set';

                                        $conversionClass = $row['conversion_percentage'] >= 50 
                                            ? 'badge-success' 
                                            : ($row['conversion_percentage'] >= 25 ? 'badge-warning' : 'badge-danger');

                                        $avg_time = ($row['avg_response_time'])
                                            ? round($row['avg_response_time'], 2) . ' min'
                                            : '0 min';

                                        $last_update = $row['last_update']
                                            ? date('d M Y H:i', strtotime($row['last_update']))
                                            : 'No Update';

                                        $total_leads = $row['total_queries'] - $row['lost'];   

                                        // B2 - Conversion %
                                        $b = ($row['conversion_percentage'] >= 40) ? 15 :
                                            (($row['conversion_percentage'] >= 30) ? 12 :
                                            (($row['conversion_percentage'] >= 20) ? 9 :
                                            (($row['conversion_percentage'] >= 10) ? 5 : 2)));

                                        // E2 - Response Time
                                        $e = ($row['avg_response_time'] <= 15) ? 10 :
                                            (($row['avg_response_time'] <= 30) ? 8 :
                                            (($row['avg_response_time'] <= 60) ? 6 :
                                            (($row['avg_response_time'] <= 1440) ? 4 : 2)));

                                        // F2 - Task Accuracy
                                        $f = ($row['task_accuracy'] >= 80) ? 10 :
                                            (($row['task_accuracy'] >= 60) ? 8 :
                                            (($row['task_accuracy'] >= 40) ? 6 : 4));

                                        // G2 - Add-on Sale
                                        $g = ($row['add_on_sale'] >= 25000) ? 10 :
                                            (($row['add_on_sale'] >= 15000) ? 8 :
                                            (($row['add_on_sale'] >= 8000) ? 6 :
                                            (($row['add_on_sale'] >= 3000) ? 4 : 2)));

                                        // H2 - Review Quality
                                        $h = ($row['review_quality'] >= 4) ? 10 :
                                            (($row['review_quality'] == 3) ? 8 :
                                            (($row['review_quality'] == 2) ? 6 :
                                            (($row['review_quality'] == 1) ? 4 : 2)));

                                        // I2 - Total Queries
                                        $i = ($row['total_queries'] >= 100) ? 3 :
                                            (($row['total_queries'] >= 80) ? 2 :
                                            (($row['total_queries'] >= 50) ? 1 : 0));

                                        // J2 - Attendance Missed
                                        $j = ($row['attendance_days_missed'] <= 0) ? 2 :
                                            (($row['attendance_days_missed'] <= 2) ? 1 : 0);

                                        // FINAL RESULTS MARKS
                                        $results_marks = $b + $e + $f + $g + $h + $i + $j;

                                        // K2 - Trainings Missed
                                        $k = ($row['trainings_missed'] <= 0) ? 5 :
                                            (($row['trainings_missed'] == 1) ? 3 : 1);

                                        // L2 - Knowledge Applied
                                        $l = $row['knowledge_applied'];

                                        // M2 - Process Accuracy
                                        $m = ($row['process_accuracy'] >= 100) ? 5 :
                                            (($row['process_accuracy'] >= 80) ? 4 :
                                            (($row['process_accuracy'] >= 60) ? 3 :
                                            (($row['process_accuracy'] >= 40) ? 2 : 1)));

                                        $skills_marks = $k + $l + $m;

                                        $n = $row['collaboration'];
                                        $o = $row['ownership'];
                                        $p = $row['values_data'];

                                        $attitude_marks = $n + $o + $p;

                                        $final_score = $results_marks + $skills_marks + $attitude_marks;

                                        if ($final_score >= 85) {
                                            $zone = '<span class="badge badge-success">GREEN</span>';
                                        } elseif ($final_score >= 70) {
                                            $zone = '<span class="badge badge-warning">AMBER</span>';
                                        } elseif ($final_score >= 50) {
                                            $zone = '<span class="badge badge-secondary">GREY</span>';
                                        } else {
                                            $zone = '<span class="badge badge-danger">RED</span>';
                                        }


                                        echo '

                                        <tr data-user-id="' . $row['id'] . '">
                                            <td>' . $sno++ . '</td>
                                            <td><strong>' . htmlspecialchars($row['name']) . '</strong></td>
                                            <td>' . htmlspecialchars($row['role']) . '</td>

                                            <td><span class="badge badge-primary">' . $row['total_queries'] . '</span></td>
                                            <td><span class="badge badge-success">' . $row['converted'] . '</span></td>
                                            <td><span class="badge badge-info">' . $row['completed'] . '</span></td>
                                            <td><span class="badge badge-warning">' . $row['follow_up'] . '</span></td>
                                            <td><span class="badge badge-secondary">' . $row['new_queries'] . '</span></td>
                                            <td><span class="badge badge-danger">' . $row['lost'] . '</span></td>

                                            <td><span class="badge ' . $conversionClass . '">' . $row['conversion_percentage'] . '%</span></td>

                                            <td>' . $avg_time . '</td>
                                            <td>' . $last_update . '</td>
                                            <td>' . $joining_date . '</td>
                                            <td>' . $status_badge . '</td>

                                           <td><span class="badge badge-dark">'.$total_leads.'</span></td>

                                            <td><input type="number" class="form-control save-field" name="add_on_sale" value="'.$row['add_on_sale'].'"  '.($_SESSION['user'] != 'admin' ? 'readonly' : '').'></td>
                                            <td><input type="number" class="form-control save-field" name="review_quality" value="'.$row['review_quality'].'" '.($_SESSION['user'] != 'admin' ? 'readonly' : '').'></td>
                                            <td><input type="number" class="form-control save-field" name="task_accuracy" value="'.$row['task_accuracy'].'" '.($_SESSION['user'] != 'admin' ? 'readonly' : '').'></td>
                                            <td><input type="number" class="form-control save-field" name="attendance_days_missed" value="'.$row['attendance_days_missed'].'" '.($_SESSION['user'] != 'admin' ? 'readonly' : '').'></td>
                                            <td><input type="number" class="form-control save-field" name="trainings_missed" value="'.$row['trainings_missed'].'" '.($_SESSION['user'] != 'admin' ? 'readonly' : '').'></td>
                                            <td><input type="number" class="form-control save-field" name="knowledge_applied" value="'.$row['knowledge_applied'].'" '.($_SESSION['user'] != 'admin' ? 'readonly' : '').'></td>
                                            <td><input type="number" class="form-control save-field" name="process_accuracy" value="'.$row['process_accuracy'].'" '.($_SESSION['user'] != 'admin' ? 'readonly' : '').'></td>
                                            <td><input type="number" class="form-control save-field" name="collaboration" value="'.$row['collaboration'].'"  '.($_SESSION['user'] != 'admin' ? 'readonly' : '').'></td>
                                            <td><input type="number" class="form-control save-field" name="ownership" value="'.$row['ownership'].'" '.($_SESSION['user'] != 'admin' ? 'readonly' : '').'></td>
                                            <td><input type="number" class="form-control save-field" name="values_data" value="'.$row['values_data'].'" '.($_SESSION['user'] != 'admin' ? 'readonly' : '').'></td>
                                            <td><strong>'.$results_marks.'</strong></td>
                                            <td><strong>'.$skills_marks.'</strong></td>
                                            <td><strong>'.$attitude_marks.'</strong></td>
                                            <td><strong>'.$final_score.'</strong></td>
                                            <td>'.$zone.'</td>

                                        </tr>';

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
            window.location.href = "employee-performance-report.php"; 
        });

        $("#export-excel").on("click", function () 
        {
            var table = document.getElementById("example");
            var wb = XLSX.utils.table_to_book(table, {
                sheet: "Employee Performance"
            });
            XLSX.writeFile(wb, "employee-performance-report.xlsx");
        });

        $(".ReportbtnExportPDF").on("click", function () 
        {
            var { jsPDF } = window.jspdf;
            var doc = new jsPDF('l', 'pt', 'a4');
            doc.setFontSize(16);
            doc.text("Employee Performance Report", 40, 40);

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
            doc.save("employee-performance-report.pdf");
        });
        <?php
            $statusQuery = "
                SELECT 
                    SUM(CASE WHEN q.status = 'Converted' THEN 1 ELSE 0 END) as converted,
                    SUM(CASE WHEN q.status = 'Completed' THEN 1 ELSE 0 END) as completed,
                    SUM(CASE WHEN q.status = 'Follow Up' THEN 1 ELSE 0 END) as follow_up,
                    SUM(CASE WHEN q.status = 'New Query' THEN 1 ELSE 0 END) as new_queries,
                    SUM(CASE WHEN q.status = 'Lost' THEN 1 ELSE 0 END) as lost
                FROM users u
                LEFT JOIN query_mst q ON u.id = q.user_id
                WHERE u.role != 'admin'
            ";
            if ($from != "" && $to != "") 
            {
                $statusQuery .= " AND DATE(q.created_at) BETWEEN '$from' AND '$to' ";
            }
            $statusResult = $mysqli->query($statusQuery);
            $statusData = $statusResult->fetch_assoc();
            $topQuery = "
                SELECT 
                    u.name,
                    COUNT(q.id) AS total_queries,
                    SUM(CASE WHEN q.status = 'Converted' THEN 1 ELSE 0 END) AS converted
                FROM users u
                LEFT JOIN query_mst q ON u.id = q.user_id
                WHERE u.role != 'admin'
            ";
            if ($from != "" && $to != "") 
            {
                $topQuery .= " AND DATE(q.created_at) BETWEEN '$from' AND '$to' ";
            }
            $topQuery .= "
                GROUP BY u.id, u.name
                ORDER BY converted DESC
                LIMIT 5
            ";
            $topResult = $mysqli->query($topQuery);
            $topNames = [];
            $topConversions = [];
            while ($row = $topResult->fetch_assoc()) 
            {
                $topNames[] = $row['name'];
                $topConversions[] = $row['converted'];
            }
            $trendQuery = "
                SELECT 
                    DATE_FORMAT(q.created_at, '%Y-%m') as month,
                    COUNT(*) as total,
                    SUM(CASE WHEN q.status = 'Converted' THEN 1 ELSE 0 END) as converted
                FROM users u
                LEFT JOIN query_mst q ON u.id = q.user_id
                WHERE u.role != 'admin' AND q.created_at IS NOT NULL
            ";
            if ($from != "" && $to != "") 
            {
                $trendQuery .= " AND DATE(q.created_at) BETWEEN '$from' AND '$to' ";
            }
            $trendQuery .= "
                GROUP BY DATE_FORMAT(q.created_at, '%Y-%m')
                ORDER BY month ASC
                LIMIT 6
            ";
            $trendResult = $mysqli->query($trendQuery);
            $trendMonths = [];
            $trendTotals = [];
            $trendConversions = [];
            while ($row = $trendResult->fetch_assoc()) 
            {
                $trendMonths[] = date('M Y', strtotime($row['month'] . '-01'));
                $trendTotals[] = $row['total'];
                $trendConversions[] = $row['converted'];
            }
        ?>
        const ctx1 = document.getElementById('queryStatusChart').getContext('2d');
        new Chart(ctx1, {
            type: 'doughnut',
            data: {
                labels: ['Converted', 'Completed', 'Follow Up', 'New Queries', 'Lost'],
                datasets: [{
                    data: [
                        <?= $statusData['converted'] ?? 0 ?>,
                        <?= $statusData['completed'] ?? 0 ?>,
                        <?= $statusData['follow_up'] ?? 0 ?>,
                        <?= $statusData['new_queries'] ?? 0 ?>,
                        <?= $statusData['lost'] ?? 0 ?>
                    ],
                    backgroundColor: ['#28a745', '#17a2b8', '#ffc107', '#6c757d', '#dc3545'],
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
        const ctx2 = document.getElementById('topPerformersChart').getContext('2d');
        new Chart(ctx2, {
            type: 'bar',
            data: {
                labels: <?= json_encode($topNames) ?>,
                datasets: [{
                    label: 'Converted Queries',
                    data: <?= json_encode($topConversions) ?>,
                    backgroundColor: '#28a745',
                    borderColor: '#1e7e34',
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
                                return `Converted: ${context.parsed.x}`;
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
        const ctx3 = document.getElementById('queryTrendChart').getContext('2d');
        new Chart(ctx3, {
            type: 'line',
            data: {
                labels: <?= json_encode($trendMonths) ?>,
                datasets: [
                    {
                        label: 'Total Queries',
                        data: <?= json_encode($trendTotals) ?>,
                        borderColor: '#3b7ddd',
                        backgroundColor: 'rgba(59, 125, 221, 0.1)',
                        tension: 0.4,
                        fill: true
                    },
                    {
                        label: 'Converted',
                        data: <?= json_encode($trendConversions) ?>,
                        borderColor: '#28a745',
                        backgroundColor: 'rgba(40, 167, 69, 0.1)',
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
    });
</script>

<script>
   $(document).on("blur", ".save-field", function () {

    let row = $(this).closest("tr");
    let user_id = row.data("user-id");

    let data = {
        user_id: user_id,
        add_on_sale: row.find('[name="add_on_sale"]').val(),
        review_quality: row.find('[name="review_quality"]').val(),
        task_accuracy: row.find('[name="task_accuracy"]').val(),
        attendance_days_missed: row.find('[name="attendance_days_missed"]').val(),
        trainings_missed: row.find('[name="trainings_missed"]').val(),
        knowledge_applied: row.find('[name="knowledge_applied"]').val(),
        process_accuracy: row.find('[name="process_accuracy"]').val(),
        collaboration: row.find('[name="collaboration"]').val(),
        ownership: row.find('[name="ownership"]').val(),
        values: row.find('[name="values_data"]').val()
    };

    $.ajax({
        url: "ajax/save_employee_report.php",
        method: "POST",
        data: data,
        success: function (res) {
            console.log("Saved:", res);
        }
    });
    });
</script>