<?php
    include "Layouts/Header.php";
    include "Layouts/Sidebar.php";
    
    if (isset($_POST['btnAddTarget'])) 
    {
        foreach ($_POST['user'] as $key => $value) 
        {
            $stmt = $mysqli->prepare("SELECT * from  `target_report` where `user_id`=? and `month`=? and `year`=?");
            $stmt->bind_param("iii", $key, $_POST['month'], $_POST['year']);
            $stmt->execute();
            $res = $stmt->get_result()->fetch_assoc();
            if (!$res) 
            {
                $stmt = $mysqli->prepare("INSERT into  `target_report` (`user_id`,`month`,`year`,`target`) values (?,?,?,?)");
                $stmt->bind_param("iiid", $key, $_POST['month'], $_POST['year'], $value);
                $stmt->execute();
                $stmt->close();
            }
        }
        alert("Save Successfully", "success");
        redirect("?");
    }
?>

<div class="content-body">
    <div class="container-fluid">
        <div class="row">
            <?php
                $selectedUserId = isset($_GET['user_id']) ? $_GET['user_id'] : null;
                $selectedYear = isset($_GET['year']) ? $_GET['year'] : date('Y');
                
                if ($selectedUserId) 
                {
                    $statsQuery = "
                        SELECT 
                            COALESCE(SUM(tr.target), 0) as total_target,
                            COALESCE(SUM(qm.total_sale), 0) as total_sale,
                            COALESCE(SUM(ex.total_expenses), 0) as total_expense,
                            COALESCE(SUM(qm.total_sale), 0) - COALESCE(SUM(ex.total_expenses), 0) as total_profit,
                            AVG(CASE WHEN tr.target > 0 THEN (COALESCE(qm.total_sale, 0) / tr.target) * 100 ELSE 0 END) as avg_achievement
                        FROM target_report tr
                        LEFT JOIN (
                            SELECT 
                                user_id, 
                                MONTH(created_at) AS month, 
                                YEAR(created_at) AS year, 
                                SUM(sale_amount) AS total_sale
                            FROM query_mst
                            GROUP BY user_id, MONTH(created_at), YEAR(created_at)
                        ) qm ON qm.user_id = tr.user_id AND qm.month = tr.month AND qm.year = tr.year
                        LEFT JOIN (
                            SELECT 
                                user_id, 
                                MONTH(created_at) AS month, 
                                YEAR(created_at) AS year, 
                                SUM(amount) AS total_expenses
                            FROM expenses
                            GROUP BY user_id, MONTH(created_at), YEAR(created_at)
                        ) ex ON ex.user_id = tr.user_id AND ex.month = tr.month AND ex.year = tr.year
                        WHERE tr.user_id = ? AND tr.year = ?
                    ";
                    
                    $statsStmt = $mysqli->prepare($statsQuery);
                    $statsStmt->bind_param("ii", $selectedUserId, $selectedYear);
                    $statsStmt->execute();
                    $stats = $statsStmt->get_result()->fetch_assoc();
                    
                    $totalTarget = $stats['total_target'] ?? 0;
                    $totalSale = $stats['total_sale'] ?? 0;
                    $totalExpense = $stats['total_expense'] ?? 0;
                    $totalProfit = $stats['total_profit'] ?? 0;
                    $avgAchievement = round($stats['avg_achievement'] ?? 0, 1);
                    $userStmt = $mysqli->prepare("SELECT name FROM users WHERE id = ?");
                    $userStmt->bind_param("i", $selectedUserId);
                    $userStmt->execute();
                    $userResult = $userStmt->get_result()->fetch_assoc();
                    $userName = $userResult['name'] ?? 'Selected User';
                } 
                else 
                {
                    $totalTarget = 0;
                    $totalSale = 0;
                    $totalExpense = 0;
                    $totalProfit = 0;
                    $avgAchievement = 0;
                    $userName = 'No User Selected';
                }
            ?>
            
            <div class="col-xl-3 col-lg-4 col-sm-6">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="font-w600">Total Target</h6>
                                <h2 class="mb-0">₹<?= number_format($totalTarget, 2) ?></h2>
                            </div>
                            <div class="text-primary">
                                <i class="fa fa-bullseye fa-2x"></i>
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
                                <i class="fa fa-trophy fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-xl-4 col-lg-6 col-sm-12">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="font-w600">Average Achievement</h6>
                                <h2 class="mb-0"><?= $avgAchievement ?>%</h2>
                            </div>
                            <div class="text-info">
                                <i class="fa fa-percent fa-2x"></i>
                            </div>
                        </div>
                        <div class="progress mt-3" style="height: 8px;">
                            <div class="progress-bar bg-success" style="width: <?= $avgAchievement ?>%"></div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-xl-4 col-lg-6 col-sm-12">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="font-w600">Target Achievement Rate</h6>
                                <?php
                                    $achievementRate = $totalTarget > 0 ? round(($totalSale / $totalTarget) * 100, 1) : 0;
                                    $achievementClass = $achievementRate >= 100 ? 'success' : ($achievementRate >= 75 ? 'warning' : 'danger');
                                ?>
                                <h2 class="mb-0"><?= $achievementRate ?>%</h2>
                            </div>
                            <div class="text-<?= $achievementClass ?>">
                                <i class="fa fa-flag-checkered fa-2x"></i>
                            </div>
                        </div>
                        <div class="progress mt-3" style="height: 8px;">
                            <div class="progress-bar bg-<?= $achievementClass ?>" style="width: <?= $achievementRate ?>%"></div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-xl-4 col-lg-12 col-sm-12">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="font-w600">Profit Margin</h6>
                                <?php
                                    $profitMargin = $totalSale > 0 ? round(($totalProfit / $totalSale) * 100, 1) : 0;
                                    $marginClass = $profitMargin >= 20 ? 'success' : ($profitMargin >= 10 ? 'warning' : 'danger');
                                ?>
                                <h2 class="mb-0"><?= $profitMargin ?>%</h2>
                            </div>
                            <div class="text-<?= $marginClass ?>">
                                <i class="fa fa-chart-pie fa-2x"></i>
                            </div>
                        </div>
                        <div class="progress mt-3" style="height: 8px;">
                            <div class="progress-bar bg-<?= $marginClass ?>" style="width: <?= min($profitMargin, 100) ?>%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-xl-6 col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Monthly Target vs Achievement</h4>
                    </div>
                    <div class="card-body">
                        <canvas id="targetVsAchievementChart" height="300"></canvas>
                    </div>
                </div>
            </div>
            
            <div class="col-xl-6 col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Monthly Profit Trend</h4>
                    </div>
                    <div class="card-body">
                        <canvas id="profitTrendChart" height="300"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Monthly Achievement Rate</h4>
                    </div>
                    <div class="card-body">
                        <canvas id="achievementRateChart" height="100"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Target Report - <?= htmlspecialchars($userName) ?> (<?= $selectedYear ?>)</h4>
                        <div class="card-header-toolbar">
                            <button class="btn btn-success btn-sm" id="exportExcel">
                                <i class="fa fa-file-excel"></i> Export Excel
                            </button>
                            <button class="btn btn-danger btn-sm ReportbtnExportPDF">
                                <i class="fa fa-file-pdf"></i> Export PDF
                            </button>
                            <button type="button" class="btn btn-outline-danger btn-sm" id="addTarget">
                                <i class="fa fa-plus"></i> Add Target
                            </button>
                        </div>
                    </div>
                    <div class="card-body border-bottom">
                        <form method="GET">
                            <div class="row">
                                <div class="col-md-4">
                                    <select name="user_id" id="user_id" class="form-control" required>
                                        <option value="">Select User</option>
                                        <?php
                                            $stmt = $mysqli->prepare("SELECT * from  `users` where `role`!='admin'");
                                            $stmt->execute();
                                            $res = $stmt->get_result();
                                            while ($row = $res->fetch_assoc()) 
                                            {
                                                $selected = (isset($_GET['user_id']) && $_GET['user_id'] == $row['id']) ? 'selected' : '';
                                                echo '<option value="' . $row['id'] . '" ' . $selected . '>' . htmlspecialchars($row['name']) . '</option>';
                                            }
                                        ?>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <select name="year" id="year" class="form-control">
                                        <?php
                                            $startYear   = 2020;
                                            $currentYear = date('Y');
                                            $selectedYear = isset($_GET['year']) ? $_GET['year'] : date('Y');

                                            for ($year = $startYear; $year <= $currentYear + 1; $year++) 
                                            {
                                                $selected = ($year == $selectedYear) ? 'selected' : '';
                                                echo "<option value='{$year}' {$selected}>{$year}</option>";
                                            }
                                        ?>
                                    </select>
                                </div>
                                <div class="col-md-3 d-flex gap-3">
                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="fa fa-search me-2"></i>Search
                                    </button>
                                    <a href="target-reports.php" class="btn btn-primary">
                                        <i class="fa fa-refresh"></i>
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
                                        <th>User Name</th>
                                        <th>Month</th>
                                        <th>Target Amount</th>
                                        <th>Sale Amount</th>
                                        <th>Expense Amount</th>
                                        <th>Profit</th>
                                        <th>Achievement %</th>
                                    </tr>
                                </thead>
                                <tbody>                              
                                    <?php
                                        $sno = 1;
                                        $currentYear = date("Y");

                                        if (isset($_GET['user_id'])) 
                                        {
                                            $user_id = $_GET['user_id'];
                                            $year = isset($_GET['year']) ? intval($_GET['year']) : $currentYear;

                                            $stmtUser = $mysqli->prepare("SELECT `name` FROM `users` WHERE `id` = ?");
                                            $stmtUser->bind_param("i", $user_id);
                                            $stmtUser->execute();
                                            $stmtUser->bind_result($user_name);
                                            $stmtUser->fetch();
                                            $stmtUser->close();

                                            for ($i = 1; $i <= 12; $i++) 
                                            {
                                                $stmt = $mysqli->prepare("SELECT 
                                                    tr.target, 
                                                    COALESCE(qm.total_sale, 0) AS total_sale, 
                                                    COALESCE(ex.total_expenses, 0) AS total_expenses,
                                                    (COALESCE(qm.total_sale, 0) - COALESCE(ex.total_expenses, 0)) AS profit
                                                FROM 
                                                    target_report tr
                                                LEFT JOIN (
                                                    SELECT 
                                                        user_id, 
                                                        MONTH(created_at) AS month, 
                                                        YEAR(created_at) AS year, 
                                                        SUM(sale_amount) AS total_sale
                                                    FROM 
                                                        query_mst
                                                    GROUP BY 
                                                        user_id, MONTH(created_at), YEAR(created_at)
                                                ) qm ON qm.user_id = tr.user_id AND qm.month = tr.month AND qm.year = tr.year
                                                LEFT JOIN (
                                                    SELECT 
                                                        user_id, 
                                                        MONTH(created_at) AS month, 
                                                        YEAR(created_at) AS year, 
                                                        SUM(amount) AS total_expenses
                                                    FROM 
                                                        expenses
                                                    GROUP BY 
                                                        user_id, MONTH(created_at), YEAR(created_at)
                                                ) ex ON ex.user_id = tr.user_id AND ex.month = tr.month AND ex.year = tr.year
                                                WHERE 
                                                    tr.user_id = ? 
                                                    AND tr.month = ? 
                                                    AND tr.year = ?
                                                ");

                                                $stmt->bind_param("iii", $user_id, $i, $year);
                                                $stmt->execute();
                                                $res = $stmt->get_result();
                                                $row = $res->fetch_assoc();

                                                $target = $row ? $row['target'] : 0;
                                                $total_sale = $row ? $row['total_sale'] : 0;
                                                $total_expense = $row ? $row['total_expenses'] : 0;
                                                $total_profit = $row ? $row['profit'] : 0;
                                                $achievement = $target > 0 ? round(($total_sale / $target) * 100, 1) : 0;
                                                
                                                $achievementClass = $achievement >= 100 ? 'success' : ($achievement >= 75 ? 'warning' : 'danger');

                                                echo '<tr>
                                                    <td>' . $sno++ . '</td>
                                                    <td>' . htmlspecialchars($user_name) . '</td>
                                                    <td>' . date("M", mktime(0, 0, 0, $i, 1)) . '</td>
                                                    <td class="text-primary">₹' . number_format($target, 2) . '</td>
                                                    <td class="text-success">₹' . number_format($total_sale, 2) . '</td>
                                                    <td class="text-danger">₹' . number_format($total_expense, 2) . '</td>
                                                    <td class="text-warning">₹' . number_format($total_profit, 2) . '</td>
                                                    <td><span class="badge badge-' . $achievementClass . '">' . $achievement . '%</span></td>
                                                </tr>';
                                            }
                                        } 
                                        else 
                                        {
                                            for ($i = 1; $i <= 12; $i++) 
                                            {
                                                echo '<tr>
                                                    <td>' . $sno++ . '</td>
                                                    <td><span class="text-muted">Please select user</span></td>
                                                    <td>' . date("M", mktime(0, 0, 0, $i, 1)) . '</td>
                                                    <td class="text-muted">-</td>
                                                    <td class="text-muted">-</td>
                                                    <td class="text-muted">-</td>
                                                    <td class="text-muted">-</td>
                                                    <td class="text-muted">-</td>
                                                </tr>';
                                            }
                                        }
                                    ?>
                                </tbody>
                                <tfoot>
                                    <?php if (isset($_GET['user_id'])) { ?>
                                    <tr class="bg-light fw-bold">
                                        <td colspan="3" class="text-end"><strong>Total:</strong></td>
                                        <td class="text-primary"><strong>₹<?= number_format($totalTarget, 2) ?></strong></td>
                                        <td class="text-success"><strong>₹<?= number_format($totalSale, 2) ?></strong></td>
                                        <td class="text-danger"><strong>₹<?= number_format($totalExpense, 2) ?></strong></td>
                                        <td class="text-warning"><strong>₹<?= number_format($totalProfit, 2) ?></strong></td>
                                        <td><strong><?= $avgAchievement ?>%</strong></td>
                                    </tr>
                                    <?php } ?>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<form method="POST" class="needs-validation" novalidate>
    <div class="modal fade" id="AddTarget">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitleId">
                        <i class="fa fa-plus-circle"></i> Add Target
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-6">
                            <label class="form-label">Select Month</label>
                            <select name="month" id="month" class="form-control" required>
                                <option value="">--Select Month---</option>
                                <?php
                                $months = [
                                    'January', 'February', 'March', 'April', 'May', 'June',
                                    'July', 'August', 'September', 'October', 'November', 'December'
                                ];
                                $currentMonth = date('n');

                                for ($i = 1; $i < 13; $i++) {
                                    $selected = ($i == $currentMonth) ? ' selected' : '';
                                    echo '<option value="' . $i . '" ' . $selected . '>' . $months[$i - 1] . '</option>';
                                }
                                ?>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label">Select Year</label>
                            <select name="year" id="year" class="form-control" required>
                                <?php
                                    $year = date('Y');
                                    for ($i = $year; $i > 2021; $i--) 
                                    {
                                        echo '<option value="' . $i . '" ' . ($i == $year ? 'selected' : '') . '>' . $i . '</option>';
                                    }
                                ?>
                            </select>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th width="60%">User</th>
                                    <th width="40%">Target Amount (₹)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                    $stmt = $mysqli->prepare("SELECT * from users where role!='admin' ORDER BY name");
                                    $stmt->execute();
                                    $res = $stmt->get_result();
                                    while ($row = $res->fetch_assoc()) {
                                        echo '<tr>
                                                <td>' . htmlspecialchars($row['name']) . '</td>
                                                <td><input type="number" min="0" value="0" step="1000" class="form-control" name="user[' . $row['id'] . ']" required></td>
                                            </tr>';
                                    }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fa fa-times"></i> Close
                    </button>
                    <button type="submit" name="btnAddTarget" class="btn btn-primary">
                        <i class="fa fa-save"></i> Save Targets
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>

<?php include "Layouts/Footer.php" ?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.28/jspdf.plugin.autotable.min.js"></script>

<script>
    $("#addTarget").on("click", function() 
    {
        $("#AddTarget").modal("show");
    });
    $("#user_id").val("<?php if (!empty($_GET['user_id'])) echo $_GET['user_id'] ?>");
    
    $(document).ready(function() 
    {
        <?php
            if (isset($_GET['user_id'])) 
            {
                $user_id = $_GET['user_id'];
                $year = isset($_GET['year']) ? intval($_GET['year']) : date('Y');
                
                $monthlyData = [];
                for ($i = 1; $i <= 12; $i++) 
                {
                    $stmt = $mysqli->prepare("SELECT 
                        tr.target, 
                        COALESCE(qm.total_sale, 0) AS total_sale,
                        COALESCE(qm.total_sale, 0) - COALESCE(ex.total_expenses, 0) AS profit,
                        CASE WHEN tr.target > 0 THEN (COALESCE(qm.total_sale, 0) / tr.target) * 100 ELSE 0 END AS achievement
                    FROM target_report tr
                    LEFT JOIN (
                        SELECT user_id, MONTH(created_at) AS month, YEAR(created_at) AS year, SUM(sale_amount) AS total_sale
                        FROM query_mst GROUP BY user_id, MONTH(created_at), YEAR(created_at)
                    ) qm ON qm.user_id = tr.user_id AND qm.month = tr.month AND qm.year = tr.year
                    LEFT JOIN (
                        SELECT user_id, MONTH(created_at) AS month, YEAR(created_at) AS year, SUM(amount) AS total_expenses
                        FROM expenses GROUP BY user_id, MONTH(created_at), YEAR(created_at)
                    ) ex ON ex.user_id = tr.user_id AND ex.month = tr.month AND ex.year = tr.year
                    WHERE tr.user_id = ? AND tr.month = ? AND tr.year = ?");
                    
                    $stmt->bind_param("iii", $user_id, $i, $year);
                    $stmt->execute();
                    $row = $stmt->get_result()->fetch_assoc();
                    
                    $monthlyData[$i] = [
                        'target' => $row ? $row['target'] : 0,
                        'sale' => $row ? $row['total_sale'] : 0,
                        'profit' => $row ? $row['profit'] : 0,
                        'achievement' => $row ? round($row['achievement'], 1) : 0
                    ];
                }
            } 
            else 
            {
                $monthlyData = [];
                for ($i = 1; $i <= 12; $i++) 
                {
                    $monthlyData[$i] = ['target' => 0, 'sale' => 0, 'profit' => 0, 'achievement' => 0];
                }
            }
            
            $months = [];
            $targets = [];
            $sales = [];
            $profits = [];
            $achievements = [];
            for ($i = 1; $i <= 12; $i++) 
            {
                $months[] = date("M", mktime(0, 0, 0, $i, 1));
                $targets[] = $monthlyData[$i]['target'];
                $sales[] = $monthlyData[$i]['sale'];
                $profits[] = $monthlyData[$i]['profit'];
                $achievements[] = $monthlyData[$i]['achievement'];
            }
        ?>
        if (document.getElementById('targetVsAchievementChart')) 
        {
            const ctx1 = document.getElementById('targetVsAchievementChart').getContext('2d');
            new Chart(ctx1, {
                type: 'bar',
                data: {
                    labels: <?= json_encode($months) ?>,
                    datasets: [
                        {
                            label: 'Target Amount',
                            data: <?= json_encode($targets) ?>,
                            backgroundColor: '#3b7ddd',
                            borderColor: '#2c5aa6',
                            borderWidth: 1,
                            borderRadius: 5
                        },
                        {
                            label: 'Sale Amount',
                            data: <?= json_encode($sales) ?>,
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
                                label: function(context) 
                                {
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
        }
        if (document.getElementById('profitTrendChart')) 
        {
            const ctx2 = document.getElementById('profitTrendChart').getContext('2d');
            new Chart(ctx2, {
                type: 'line',
                data: {
                    labels: <?= json_encode($months) ?>,
                    datasets: [{
                        label: 'Profit Amount',
                        data: <?= json_encode($profits) ?>,
                        borderColor: '#ffc107',
                        backgroundColor: 'rgba(255, 193, 7, 0.1)',
                        tension: 0.4,
                        fill: true,
                        pointBackgroundColor: '#ffc107',
                        pointBorderColor: '#e0a800',
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
                                    return `Profit: ₹${context.parsed.y.toLocaleString()}`;
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
        }
        if (document.getElementById('achievementRateChart')) 
        {
            const ctx3 = document.getElementById('achievementRateChart').getContext('2d');
            new Chart(ctx3, {
                type: 'bar',
                data: {
                    labels: <?= json_encode($months) ?>,
                    datasets: [{
                        label: 'Achievement Rate (%)',
                        data: <?= json_encode($achievements) ?>,
                        backgroundColor: function(context) {
                            const value = context.dataset.data[context.dataIndex];
                            if (value >= 100) return '#28a745';
                            if (value >= 75) return '#ffc107';
                            return '#dc3545';
                        },
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
                                    return `Achievement: ${context.parsed.y}%`;
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 150,
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
        $('#exportExcel').click(function() 
        {
            var table = document.getElementById('example');
            var wb = XLSX.utils.table_to_book(table, {
                sheet: "Target Report"
            });
            XLSX.writeFile(wb, "target-report.xlsx");
        });
        $('.ReportbtnExportPDF').click(function() 
        {
            var { jsPDF } = window.jspdf;
            var doc = new jsPDF('l', 'pt', 'a4');
            
            doc.setFontSize(16);
            doc.text("Target Report - <?= htmlspecialchars($userName) ?> (<?= $selectedYear ?>)", 40, 40);
            
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
                styles: { fontSize: 8 },
                headStyles: { fillColor: [75, 108, 183] }
            });
            
            doc.save("target-report.pdf");
        });
    });
</script>
