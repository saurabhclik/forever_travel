<?php include "Layouts/Header.php"; ?>

<?php include "Layouts/Sidebar.php"; ?>

<?php
$user_id = $_SESSION['id'] ?? '';
$today = date('Y-m-d');
if ($_SESSION['user'] == "admin") {
    $stmt = $mysqli->prepare("SELECT a.*, b.name as customer, c.name as user , destinations.name as destination_name
        FROM query_mst a 
        JOIN `customers` b ON a.customer_id = b.id 
        Left JOIN  `destinations` ON destinations.id = a.destination 
        JOIN users c ON a.user_id = c.id 
        WHERE a.status= 'Follow Up'  AND a.call_date = ?
        ORDER BY a.pinned DESC, a.id DESC");
    $stmt->bind_param('s', $today);
} else {
    $stmt = $mysqli->prepare("SELECT a.*, b.name as customer, c.name as user ,destinations.name as destination_name
        FROM query_mst a 
        JOIN customers b ON a.customer_id = b.id 
        JOIN users c ON a.user_id = c.id 
        JOIN destinations ON destinations.id = a.destination
        WHERE a.status= 'Follow Up' AND a.call_date = ? AND FIND_IN_SET(?, a.user_id)
        ORDER BY a.pinned DESC, a.id DESC");
    $stmt->bind_param('si', $today, $user_id);
}

$stmt->execute();
$data = $stmt->get_result();
// echo '<pre>'; print_r($res->fetch_all(MYSQLI_ASSOC)); echo '</pre>'; exit;
$sno = 1;
if ($data->num_rows > 0 && $_SESSION['showFollowupPopup']) {
    $showFollowupPopup = true;
} else {
    $showFollowupPopup = false;
}
// $showFollowupPopup = true;
unset($_SESSION['showFollowupPopup']);
?>

<?php
if (!empty($_GET['month'])) {
    $month = $_GET['month'];
    $year = $_GET['year'];
} else {
    $month = date('m');
    $year = date('Y');
}

$stmt = $mysqli->prepare("SELECT * from `query_mst` where month(from_date)=? and year(from_date)=? and status ='Converted'");
$stmt->bind_param("ss", $month, $year);
$stmt->execute();
$res = $stmt->get_result();
$travel_date = [];
while ($row = $res->fetch_assoc()) {
    $travel_date[] = $row['from_date'];
}

$ids = array_map('trim', explode(',', $_SESSION['child_ids']));

$conditions = [];
$params = [];
$types = '';

foreach ($ids as $id) {
    $conditions[] = "FIND_IN_SET(?, user_id)";
    $params[] = $id;
    $types .= "i";
}

$sql = "
        SELECT 
            COUNT(*) AS total,
            SUM(CASE WHEN status='New Query' THEN 1 ELSE 0 END) AS new_query,
            SUM(CASE WHEN status='Follow Up' THEN 1 ELSE 0 END) AS follow_up,
            SUM(CASE WHEN status='Completed' THEN 1 ELSE 0 END) AS completed,
            SUM(CASE WHEN status='Converted' THEN 1 ELSE 0 END) AS converted,
            SUM(CASE WHEN status='Lost' THEN 1 ELSE 0 END) AS lost
        FROM `query_mst`
        WHERE " . implode(" OR ", $conditions);

$stmt = $mysqli->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();
$dashboard = $result->fetch_assoc();
$topDestinationsQuery = "
        SELECT 
            d.name as destination_name,
            COUNT(q.id) as total_queries,
            SUM(q.sale_amount) as total_sales
        FROM `destinations` d
        JOIN query_mst q ON d.id = q.destination
        WHERE q.status = 'Converted'
        GROUP BY d.id, d.name
        ORDER BY total_sales DESC
        LIMIT 5
    ";
$topDestinationsResult = $mysqli->query($topDestinationsQuery);
$topDestinations = [];
while ($row = $topDestinationsResult->fetch_assoc()) {
    $topDestinations[] = $row;
}
$topServicesQuery = "
        SELECT 
            s.name AS service_name,
            COUNT(q.id) AS total_queries,
            SUM(q.sale_amount) AS total_sales
        FROM `service` s
        JOIN query_mst q ON s.name = q.service
        WHERE q.status = 'Converted'
        GROUP BY s.id, s.name
        ORDER BY total_sales DESC
        LIMIT 5
    ";
$topServicesResult = $mysqli->query($topServicesQuery);
$topServices = [];
while ($row = $topServicesResult->fetch_assoc()) {
    $topServices[] = $row;
}

$monthlySalesQuery = "
        SELECT 
            DATE_FORMAT(created_at, '%M') as month_name,
            MONTH(created_at) as month_num,
            SUM(sale_amount) as total_sales,
            COUNT(*) as total_queries
        FROM `query_mst`
        WHERE status = 'Converted' AND YEAR(created_at) = YEAR(CURDATE())
        GROUP BY MONTH(created_at)
        ORDER BY month_num ASC
    ";
$monthlySalesResult = $mysqli->query($monthlySalesQuery);
$monthlyLabels = [];
$monthlySales = [];
while ($row = $monthlySalesResult->fetch_assoc()) {
    $monthlyLabels[] = $row['month_name'];
    $monthlySales[] = $row['total_sales'];
}
$paymentMethodQuery = "
        SELECT 
            payment_type,
            COUNT(*) as count,
            SUM(amount) as total_amount
        FROM `payment`
        GROUP BY payment_type
    ";
$paymentMethodResult = $mysqli->query($paymentMethodQuery);
$paymentMethods = [];
while ($row = $paymentMethodResult->fetch_assoc()) {
    $paymentMethods[] = $row;
}

$user_id = $_SESSION['id'];

$user_id = $_SESSION['id'];

// ADD FROM HERE
$stmt = $mysqli->prepare("
    SELECT 
        u.id,
        u.name,
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
        AVG(TIMESTAMPDIFF(MINUTE, q.created_at, q.updated_at)) AS avg_response_time

    FROM users u
    LEFT JOIN query_mst q ON u.id = q.user_id
    WHERE u.id = ?
");

$stmt->bind_param("i", $user_id);
$stmt->execute();
$res = $stmt->get_result();
$row = $res->fetch_assoc();

if ($row) {

    $conversion_percentage = ($row['total_queries'] > 0)
        ? ($row['converted'] * 100 / $row['total_queries']) : 0;

    $b = ($conversion_percentage >= 40) ? 15 : (($conversion_percentage >= 30) ? 12 : (($conversion_percentage >= 20) ? 9 : (($conversion_percentage >= 10) ? 5 : 2)));

    $e = ($row['avg_response_time'] <= 15) ? 10 : (($row['avg_response_time'] <= 30) ? 8 : (($row['avg_response_time'] <= 60) ? 6 : (($row['avg_response_time'] <= 1440) ? 4 : 2)));

    $f = ($row['task_accuracy'] >= 80) ? 10 : (($row['task_accuracy'] >= 60) ? 8 : (($row['task_accuracy'] >= 40) ? 6 : 4));

    $g = ($row['add_on_sale'] >= 25000) ? 10 : (($row['add_on_sale'] >= 15000) ? 8 : (($row['add_on_sale'] >= 8000) ? 6 : (($row['add_on_sale'] >= 3000) ? 4 : 2)));

    $h = ($row['review_quality'] >= 4) ? 10 : (($row['review_quality'] == 3) ? 8 : (($row['review_quality'] == 2) ? 6 : (($row['review_quality'] == 1) ? 4 : 2)));

    $i = ($row['total_queries'] >= 100) ? 3 : (($row['total_queries'] >= 80) ? 2 : (($row['total_queries'] >= 50) ? 1 : 0));

    $j = ($row['attendance_days_missed'] <= 0) ? 2 : (($row['attendance_days_missed'] <= 2) ? 1 : 0);

    $results_marks = $b + $e + $f + $g + $h + $i + $j;

    $k = ($row['trainings_missed'] <= 0) ? 5 : (($row['trainings_missed'] == 1) ? 3 : 1);

    $l = $row['knowledge_applied'];

    $m = ($row['process_accuracy'] >= 100) ? 5 : (($row['process_accuracy'] >= 80) ? 4 : (($row['process_accuracy'] >= 60) ? 3 : (($row['process_accuracy'] >= 40) ? 2 : 1)));

    $skills_marks = $k + $l + $m;

    $attitude_marks = $row['collaboration'] + $row['ownership'] + $row['values_data'];

    $final_score = $results_marks + $skills_marks + $attitude_marks;

    // ✅ ZONE
    if ($final_score >= 85) {
        $alertClass = "success";
        $message = "🔥 Excellent {$row['name']}! Your performance is outstanding (GREEN Zone)";
    } elseif ($final_score >= 70) {
        $alertClass = "warning";
        $message = "⚠️ Good {$row['name']}, but you can improve (AMBER Zone)";
    } elseif ($final_score >= 50) {
        $alertClass = "secondary";
        $message = "📊 {$row['name']}, average performance (GREY Zone)";
    } else {
        $alertClass = "danger";
        $message = "🚨 {$row['name']}! Poor performance (RED Zone)";
    }
} else {
    $alertClass = "info";
    $message = "Welcome! No performance data found.";
}

?>

<style>
    table {
        width: 100%;
        border-collapse: collapse;
    }

    .th_calendar,
    .td_calendar {
        border: 1px solid #ddd;
        padding: 5px;
        text-align: center;
        width: 50px;
    }

    .th_calendar {
        background-color: #f4f4f4;
    }

    .td_calendar {
        height: 50px;

    }

    .today {
        background-color: #ffcccb;
    }

    .top-selling-item {
        border-bottom: 1px solid #eee;
        padding: 10px 0;
    }

    .top-selling-item:last-child {
        border-bottom: none;
    }

    .progress-bar-custom {
        height: 8px;
        border-radius: 4px;
        background-color: #e9ecef;
    }

    .progress-fill {
        height: 100%;
        border-radius: 4px;
        transition: width 0.5s ease;
    }

    .alert-success {
        background: #67db93;
        border-color: #edfaf2;
        color: white;
    }
</style>

<div class="content-body">
    <div class="container-fluid">
        <div class="alert alert-<?= $alertClass ?> alert-dismissible fade show" role="alert">
            <?= $message ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <div class="row">
            <div class="col-xl-12">
                <div class="col-xl-12 card h-auto">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <!-- <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6">
										<div class="income-data d-flex align-items-center justify-content-xl-start justify-content-between mb-xl-0 mb-3">
											<span class=" income-icon style-1 me-4">
											<svg width="30" height="30" viewBox="0 0 30 30" fill="none" xmlns="http://www.w3.org/2000/svg">
												<path d="M20.4764 0.97345C20.4255 0.974639 20.3747 0.978331 20.3241 0.984696C19.9555 1.02962 19.6167 1.20961 19.3732 1.48989C19.1297 1.77018 18.9988 2.13096 19.0057 2.50219V29.4991C19.0077 29.8041 19.1026 30.1012 19.2778 30.3509C19.453 30.6006 19.7001 30.7909 19.9862 30.8966C20.2723 31.0022 20.5838 31.0183 20.8792 30.9424C21.1746 30.8665 21.4398 30.7023 21.6395 30.4718L30.6425 19.9748C30.7704 19.8249 30.8676 19.6513 30.9284 19.4639C30.9893 19.2765 31.0126 19.079 30.9971 18.8825C30.9816 18.6861 30.9276 18.4946 30.8381 18.319C30.7486 18.1435 30.6254 17.9875 30.4755 17.8595C30.3257 17.7316 30.1521 17.6344 29.9647 17.5735C29.7773 17.5127 29.5797 17.4893 29.3833 17.5048C29.1869 17.5204 28.9954 17.5745 28.8199 17.664C28.6443 17.7535 28.4882 17.8766 28.3602 18.0265L21.994 25.4444V2.50219C21.9976 2.30152 21.9608 2.10206 21.8859 1.91585C21.811 1.72965 21.6995 1.56043 21.5579 1.41809C21.4164 1.27576 21.2478 1.16328 21.062 1.08729C20.8763 1.01131 20.6771 0.973336 20.4764 0.975699L20.4764 0.97345ZM11.453 1.00736C11.2441 1.01319 11.0388 1.0627 10.8501 1.15252C10.6614 1.24234 10.4935 1.37054 10.3573 1.52899L1.3661 12.026C1.22021 12.1722 1.10608 12.3469 1.03084 12.5392C0.955604 12.7315 0.920883 12.9374 0.928852 13.1437C0.936821 13.3501 0.98731 13.5526 1.07716 13.7385C1.167 13.9245 1.29427 14.0897 1.45099 14.2242C1.60771 14.3587 1.79051 14.4595 1.98794 14.52C2.18537 14.5806 2.39318 14.5997 2.59835 14.5763C2.80352 14.5528 3.00163 14.4871 3.18029 14.3835C3.35895 14.2799 3.51429 14.1407 3.6366 13.9743L10.0028 6.55623V29.4988C9.99838 29.6986 10.0339 29.8972 10.1073 30.0831C10.1807 30.2689 10.2905 30.4383 10.4302 30.5812C10.5699 30.724 10.7368 30.8374 10.921 30.9149C11.1052 30.9924 11.303 31.0324 11.5028 31.0324C11.7026 31.0324 11.9005 30.9924 12.0847 30.9149C12.2689 30.8374 12.4357 30.724 12.5754 30.5812C12.7152 30.4383 12.8249 30.2689 12.8983 30.0831C12.9717 29.8972 13.0072 29.6986 13.0028 29.4988V2.50167C13.0021 2.30093 12.9611 2.10237 12.8823 1.91775C12.8035 1.73314 12.6884 1.56607 12.5439 1.42674C12.3993 1.28741 12.2283 1.17853 12.041 1.1065C11.8536 1.03447 11.6536 1.00089 11.453 1.00753V1.00736Z" fill="#fff"/>
												</svg>
											</span>
											<div>
												<h2 class="font-w600 mb-0 income-value">$45,945</h2>
												<span class=" fs-6 font-w500">Total incomes</span>
											</div>
										</div>
									</div> -->
                            <!-- <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6">
										<div class="d-flex align-items-end justify-content-xl-start justify-content-between mb-xl-0 mb-3">
												<div id="NewCustomers"></div>
											<div class=" ms-3">
												<h6 class="fs-18 font-w600 mb-0 text-success">+2.4%</h6>
												<span class="fs-14 font-w400">Than last week</span>
											</div>
										</div>
									</div> -->
                            <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6">
                                <a href="query.php?status=New Query" style="text-decoration: none;">
                                    <div class="card trading mb-sm-0 mb-3">
                                        <div class="card-body">
                                            <div
                                                class="income-data d-flex justify-content-between align-items-center mb-sm-0 mb-2 ps-lg-0">
                                                <div>
                                                    <h3 class="font-w600 fs-2 mb-0 text-white">
                                                        <?= $dashboard['new_query'] ?>
                                                    </h3>
                                                    <span class="fs-6 font-w500 text-white">New Query</span>
                                                </div>
                                                <span class="income-icon style-2">
                                                    <svg width="34" height="24" viewBox="0 0 34 24" fill="none"
                                                        xmlns="http://www.w3.org/2000/svg">
                                                        <path
                                                            d="M33.5 22.5C33.5 22.8978 33.342 23.2793 33.0607 23.5606C32.7794 23.8419 32.3978 24 32 24H14C13.6022 24 13.2206 23.8419 12.9393 23.5606C12.658 23.2793 12.5 22.8978 12.5 22.5C12.5 20.113 13.4482 17.8238 15.136 16.136C16.8239 14.4482 19.1131 13.5 21.5 13.5H24.5C26.8869 13.5 29.1761 14.4482 30.864 16.136C32.5518 17.8238 33.5 20.113 33.5 22.5ZM23 0C21.8133 0 20.6533 0.351893 19.6666 1.01118C18.6799 1.67047 17.9108 2.60754 17.4567 3.7039C17.0026 4.80025 16.8838 6.00665 17.1153 7.17053C17.3468 8.33442 17.9182 9.40352 18.7574 10.2426C19.5965 11.0817 20.6656 11.6532 21.8295 11.8847C22.9933 12.1162 24.1997 11.9974 25.2961 11.5433C26.3925 11.0891 27.3295 10.3201 27.9888 9.33341C28.6481 8.34672 29 7.18668 29 5.99999C29 4.4087 28.3679 2.88257 27.2426 1.75736C26.1174 0.63214 24.5913 0 23 0ZM9.5 0C8.31331 0 7.15327 0.351893 6.16658 1.01118C5.17988 1.67047 4.41085 2.60754 3.95672 3.7039C3.5026 4.80025 3.38378 6.00665 3.61529 7.17053C3.8468 8.33442 4.41824 9.40352 5.25736 10.2426C6.09647 11.0817 7.16557 11.6532 8.32946 11.8847C9.49334 12.1162 10.6997 11.9974 11.7961 11.5433C12.8925 11.0891 13.8295 10.3201 14.4888 9.33341C15.1481 8.34672 15.5 7.18668 15.5 5.99999C15.5 4.4087 14.8679 2.88257 13.7426 1.75736C12.6174 0.63214 11.0913 0 9.5 0ZM9.5 22.5C9.49777 20.9244 9.80818 19.364 10.4133 17.9093C11.0183 16.4545 11.9061 15.1342 13.025 14.025C12.1093 13.6793 11.1388 13.5014 10.16 13.5H8.84C6.62931 13.504 4.5103 14.3839 2.94711 15.9471C1.38391 17.5103 0.503965 19.6293 0.5 21.84V22.5C0.5 22.8978 0.658035 23.2793 0.93934 23.5606C1.22064 23.8419 1.60218 24 2 24H9.77C9.59537 23.519 9.50406 23.0117 9.5 22.5Z"
                                                            fill="#FFFFFF" />
                                                    </svg>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                            </div>

                            <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6">
                                <a href="query.php?status=Follow Up" style="text-decoration: none;">
                                    <div class="card booking mb-0">
                                        <div class="card-body bg-warning">
                                            <div
                                                class="income-data d-flex justify-content-between align-items-center mb-sm-0 mb-2 ps-lg-0">
                                                <div>
                                                    <h3 class="font-w600 fs-2 mb-0 text-white">
                                                        <?php echo $dashboard['follow_up'] ?>
                                                    </h3>
                                                    <span class="fs-6 font-w500 text-white">Follow Up</span>
                                                </div>
                                                <span class="income-icon style-3">
                                                    <svg width="34" height="24" viewBox="0 0 48 48" fill="none"
                                                        xmlns="http://www.w3.org/2000/svg">
                                                        <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                                        <g id="SVGRepo_tracerCarrier" stroke-linecap="round"
                                                            stroke-linejoin="round"></g>
                                                        <g id="SVGRepo_iconCarrier">
                                                            <rect width="48" height="48" fill="white"
                                                                fill-opacity="0.01"></rect>
                                                            <path d="M6 5V30.0036H42V5" stroke="#000000"
                                                                stroke-width="4" stroke-linecap="round"
                                                                stroke-linejoin="round"></path>
                                                            <path d="M30 37L24 43L18 37" stroke="#000000"
                                                                stroke-width="4" stroke-linecap="round"
                                                                stroke-linejoin="round"></path>
                                                            <path d="M24 30V43" stroke="#000000" stroke-width="4"
                                                                stroke-linecap="round" stroke-linejoin="round"></path>
                                                            <path d="M27.9883 10.9786L32.9996 16.0001L27.9883 21.0903"
                                                                stroke="#000000" stroke-width="4" stroke-linecap="round"
                                                                stroke-linejoin="round"></path>
                                                            <path d="M15.001 16.001H33.0001" stroke="#000000"
                                                                stroke-width="4" stroke-linecap="round"></path>
                                                        </g>
                                                    </svg>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                            </div>

                            <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6">
                                <a href="query.php?status=Converted" style="text-decoration: none;">
                                    <div class="card booking mb-0">
                                        <div class="card-body bg-success">
                                            <div
                                                class="income-data d-flex justify-content-between align-items-center mb-sm-0 mb-2 ps-lg-0">
                                                <div>
                                                    <h3 class="font-w600 fs-2 mb-0 text-white">
                                                        <?php echo $dashboard['converted'] ?>
                                                    </h3>
                                                    <span class="fs-6 font-w500 text-white">Converted</span>
                                                </div>
                                                <span class="income-icon style-3">
                                                    <svg width="34" height="24" viewBox="0 0 24 24" fill="none"
                                                        xmlns="http://www.w3.org/2000/svg">
                                                        <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                                        <g id="SVGRepo_tracerCarrier" stroke-linecap="round"
                                                            stroke-linejoin="round"></g>
                                                        <g id="SVGRepo_iconCarrier">
                                                            <path
                                                                d="M12.0051 21.9843C17.5165 21.9843 21.9843 17.5165 21.9843 12.0051C21.9843 6.49372 17.5165 2.02588 12.0051 2.02588C6.49372 2.02588 2.02588 6.49372 2.02588 12.0051C2.02588 17.5165 6.49372 21.9843 12.0051 21.9843Z"
                                                                stroke="#292D32" stroke-width="1.5"
                                                                stroke-linecap="round" stroke-linejoin="round"></path>
                                                            <path d="M6.13721 4.02173L14.3002 12.2047L14.3202 7.66414"
                                                                stroke="#292D32" stroke-width="1.5"
                                                                stroke-linecap="round" stroke-linejoin="round"></path>
                                                            <path d="M17.8629 19.9784L9.69989 11.8054L9.67993 16.336"
                                                                stroke="#292D32" stroke-width="1.5"
                                                                stroke-linecap="round" stroke-linejoin="round"></path>
                                                        </g>
                                                    </svg>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                            </div>

                            <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6">
                                <a href="query.php?status=Completed" style="text-decoration: none;">
                                    <div class="card booking mb-0">
                                        <div class="card-body bg-light">
                                            <div
                                                class="income-data d-flex justify-content-between align-items-center mb-sm-0 mb-2 ps-lg-0">
                                                <div>
                                                    <h3 class="font-w600 fs-2 mb-0">
                                                        <?php echo $dashboard['completed'] ?>
                                                    </h3>
                                                    <span class="fs-6 font-w500">Completed</span>
                                                </div>
                                                <span class="income-icon style-3">
                                                    <svg width="34" height="24" viewBox="0 0 64 64" data-name="Layer 1"
                                                        id="Layer_1" xmlns="http://www.w3.org/2000/svg" fill="#000000">
                                                        <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                                        <g id="SVGRepo_tracerCarrier" stroke-linecap="round"
                                                            stroke-linejoin="round"></g>
                                                        <g id="SVGRepo_iconCarrier">
                                                            <defs>
                                                                <style>
                                                                    .cls-1 {
                                                                        fill: #0074ff;
                                                                    }

                                                                    .cls-2 {
                                                                        fill: #ffb300;
                                                                    }
                                                                </style>
                                                            </defs>
                                                            <path class="cls-1"
                                                                d="M28.75,55.5a23.5,23.5,0,1,1,14-42.38,2,2,0,0,1-2.38,3.21A19.51,19.51,0,1,0,48.25,32,19.65,19.65,0,0,0,48,28.93a2,2,0,1,1,4-.62A23.85,23.85,0,0,1,52.25,32,23.52,23.52,0,0,1,28.75,55.5Z">
                                                            </path>
                                                            <path class="cls-2"
                                                                d="M31.25,39.5a2,2,0,0,1-1.41-.59l-9.5-9.5a2,2,0,0,1,2.82-2.82l8.09,8.08L55.34,10.59a2,2,0,0,1,2.82,2.82l-25.5,25.5A2,2,0,0,1,31.25,39.5Z">
                                                            </path>
                                                        </g>
                                                    </svg>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                            </div>

                            <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6 mt-2">
                                <a href="query.php?status=Lost" style="text-decoration: none;">
                                    <div class="card booking mb-0">
                                        <div class="card-body" style="background: #FF6363">
                                            <div
                                                class="income-data d-flex justify-content-between align-items-center mb-sm-0 mb-2  mb-sm-0 mb-2 ps-lg-0 ">
                                                <div>
                                                    <h3 class="font-w600 fs-2 mb-0 " style="color: #000000">
                                                        <?php echo $dashboard['lost']  ?></h3>
                                                    <span class="fs-6 font-w500 " style="color: #000000">Lost</span>
                                                </div>
                                                <span class="income-icon style-3">
                                                    <svg width="34" height="24" viewBox="0 0 24 24" fill="none"
                                                        xmlns="http://www.w3.org/2000/svg">
                                                        <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                                        <g id="SVGRepo_tracerCarrier" stroke-linecap="round"
                                                            stroke-linejoin="round"></g>
                                                        <g id="SVGRepo_iconCarrier">
                                                            <path
                                                                d="M18 6L17.1991 18.0129C17.129 19.065 17.0939 19.5911 16.8667 19.99C16.6666 20.3412 16.3648 20.6235 16.0011 20.7998C15.588 21 15.0607 21 14.0062 21H9.99377C8.93927 21 8.41202 21 7.99889 20.7998C7.63517 20.6235 7.33339 20.3412 7.13332 19.99C6.90607 19.5911 6.871 19.065 6.80086 18.0129L6 6M4 6H20M16 6L15.7294 5.18807C15.4671 4.40125 15.3359 4.00784 15.0927 3.71698C14.8779 3.46013 14.6021 3.26132 14.2905 3.13878C13.9376 3 13.523 3 12.6936 3H11.3064C10.477 3 10.0624 3 9.70951 3.13878C9.39792 3.26132 9.12208 3.46013 8.90729 3.71698C8.66405 4.00784 8.53292 4.40125 8.27064 5.18807L8 6M14 10V17M10 10V17"
                                                                stroke="#000000" stroke-width="2" stroke-linecap="round"
                                                                stroke-linejoin="round"></path>
                                                        </g>
                                                    </svg>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                            </div>
                            <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6 mt-2">

                                <div class="card booking mb-0">
                                    <div class="card-body bg-success">
                                        <div
                                            class="income-data d-flex justify-content-between align-items-center mb-sm-0 mb-2  mb-sm-0 mb-2 ps-lg-0 ">
                                            <div>
                                                <h3 class="font-w600 fs-2 mb-0 text-white">
                                                    <?php
                                                    $ids = array_map('trim', explode(',', $_SESSION['child_ids']));
                                                    $conditions = [];
                                                    $params = [];
                                                    $types = '';

                                                    foreach ($ids as $id) {
                                                        $conditions[] = "FIND_IN_SET(?, user_id)";
                                                        $params[] = $id;
                                                        $types .= "i";
                                                    }

                                                    $sql = "
                                                            SELECT 
                                                                sum(sale_amount) AS total_sale_amount
                                                            FROM `query_mst`
                                                            WHERE " . implode(" OR ", $conditions);

                                                    $stmt = $mysqli->prepare($sql);
                                                    $stmt->bind_param($types, ...$params);
                                                    $stmt->execute();

                                                    $result = $stmt->get_result();
                                                    $totalSaleAmount = $result->fetch_assoc();
                                                    ?>
                                                    <?php echo $totalSaleAmount["total_sale_amount"]; ?>

                                                </h3>
                                                <span class="fs-6 font-w500 text-white ">Total Sale Amount</span>
                                            </div>
                                            <span class="income-icon style-3">
                                                <svg viewBox="0 0 1024 1024" class="icon" version="1.1"
                                                    xmlns="http://www.w3.org/2000/svg" fill="#000000">
                                                    <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                                    <g id="SVGRepo_tracerCarrier" stroke-linecap="round"
                                                        stroke-linejoin="round"></g>
                                                    <g id="SVGRepo_iconCarrier">
                                                        <path
                                                            d="M292.571429 906.971429H146.285714c-14.628571 0-21.942857-7.314286-21.942857-21.942858V533.942857c0-7.314286 14.628571-21.942857 21.942857-21.942857h146.285715c14.628571 0 21.942857 7.314286 21.942857 21.942857v351.085714c0 7.314286-7.314286 21.942857-21.942857 21.942858z"
                                                            fill="#F4B1B2"></path>
                                                        <path
                                                            d="M292.571429 928.914286H146.285714c-29.257143 0-43.885714-21.942857-43.885714-43.885715V533.942857c0-21.942857 21.942857-43.885714 43.885714-43.885714h146.285715c29.257143 0 43.885714 21.942857 43.885714 43.885714v351.085714c7.314286 21.942857-14.628571 43.885714-43.885714 43.885715zM153.6 877.714286h131.657143V541.257143H153.6V877.714286zM943.542857 928.914286H804.571429c-29.257143 0-51.2-21.942857-51.2-51.2V394.971429c0-29.257143 21.942857-51.2 51.2-51.2h138.971428c29.257143 0 51.2 21.942857 51.2 51.2V877.714286c0 29.257143-21.942857 51.2-51.2 51.2z m-7.314286-533.942857H804.571429V877.714286l131.657142-7.314286V394.971429z m7.314286 482.742857z"
                                                            fill="#D72822"></path>
                                                        <path
                                                            d="M621.714286 906.971429H490.057143c-14.628571 0-29.257143-14.628571-29.257143-29.257143V102.4c0-14.628571 14.628571-29.257143 29.257143-29.257143h124.342857c14.628571 0 29.257143 14.628571 29.257143 29.257143V877.714286c7.314286 14.628571-7.314286 29.257143-21.942857 29.257143z"
                                                            fill="#F4B1B2"></path>
                                                        <path
                                                            d="M621.714286 928.914286H490.057143c-29.257143 0-58.514286-21.942857-58.514286-58.514286V102.4c0-29.257143 21.942857-58.514286 58.514286-58.514286h124.342857c29.257143 0 58.514286 21.942857 58.514286 58.514286V877.714286c0 29.257143-21.942857 51.2-51.2 51.2zM490.057143 102.4V877.714286h131.657143V102.4H490.057143z"
                                                            fill="#D72822"></path>
                                                    </g>
                                                </svg>
                                            </span>
                                        </div>
                                    </div>
                                </div>

                            </div>

                            <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6 mt-3">

                                <div class="card trading mb-sm-0 mb-3">
                                    <div class="card-body">
                                        <div
                                            class="income-data d-flex justify-content-between align-items-center mb-sm-0 mb-2 ps-lg-0">
                                            <div>
                                                <h3 class="font-w600 fs-2 mb-0 text-white">
                                                    <h3 class="font-w600 fs-2 mb-0 text-white">
                                                        <?php
                                                        $stmt = $mysqli->prepare("SELECT SUM(amount) as total_expense_amount FROM expenses  WHERE user_id in (" . $_SESSION['child_ids'] . ")");
                                                        $stmt->execute();
                                                        $expense = $stmt->get_result()->fetch_assoc();
                                                        $totalExpenseAmount = $expense['total_expense_amount'];

                                                        ?>
                                                        <?php echo $totalExpenseAmount; ?>
                                                    </h3>
                                                </h3>
                                                <span class="fs-6 font-w500 text-white">Total Expense Sale</span>
                                            </div>
                                            <span class="income-icon style-3 mt-3">
                                                <svg width="34" height="32" viewBox="0 0 48 48" id="a"
                                                    xmlns="http://www.w3.org/2000/svg" fill="#347433" stroke="#347433">
                                                    <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                                    <g id="SVGRepo_tracerCarrier" stroke-linecap="round"
                                                        stroke-linejoin="round"></g>
                                                    <g id="SVGRepo_iconCarrier">
                                                        <defs>
                                                            <style>
                                                                .p {
                                                                    fill: none;
                                                                    stroke: #000000;
                                                                    stroke-linecap: round;
                                                                    stroke-linejoin: round;
                                                                }
                                                            </style>
                                                        </defs>
                                                        <g id="b">
                                                            <path id="c" class="p"
                                                                d="M16.5169,14.3442l7.7047-4.801,10.2741,8.6883v12.5665l-5.9671,4.836v-11.8175l-12.0117-9.4722Z">
                                                            </path>
                                                            <path id="d" class="p"
                                                                d="M26.0581,9.2578l5.8416-3.6121,10.4601,7.293-6.4328,4.9258">
                                                            </path>
                                                            <path id="e" class="p" d="M36.2041,28.6126l6.2959-5.1397">
                                                            </path>
                                                            <path id="f" class="p" d="M36.2041,25.9523l6.2959-5.1397">
                                                            </path>
                                                            <path id="g" class="p" d="M36.2041,23.292l6.2959-5.1397">
                                                            </path>
                                                            <path id="h" class="p" d="M36.2041,20.6317l6.2959-5.1397">
                                                            </path>
                                                            <path id="i" class="p"
                                                                d="M35.3139,14.172l2.7236-2.077-1.865-1.2474-1.4987,1.1314">
                                                            </path>
                                                            <path id="j" class="p"
                                                                d="M5.5,31.9538l13.5429,10.4006,7.4233-5.9106"></path>
                                                            <path id="k" class="p"
                                                                d="M5.5,29.2851l13.5429,10.4006,7.4233-5.9106"></path>
                                                            <path id="l" class="p"
                                                                d="M5.6039,26.6164l13.5429,10.4006,7.4233-5.9106">
                                                            </path>
                                                            <path id="m" class="p"
                                                                d="M5.5892,23.9478l13.5429,10.4006,7.4233-5.9106">
                                                            </path>
                                                            <path id="n" class="p"
                                                                d="M20.2345,23.7501c-.226,1.0274-1.6933,1.5535-3.2773,1.1753h0c-1.5841-.3783-2.685-1.5178-2.459-2.5451,.226-1.0274,1.6933-1.5535,3.2773-1.1753s2.685,1.5177,2.459,2.5451Z">
                                                            </path>
                                                            <path id="o" class="p"
                                                                d="M15.0514,15.826l-9.2955,5.5946,13.3311,10.1174,7.6392-6.0147">
                                                            </path>
                                                        </g>
                                                    </g>
                                                </svg>
                                            </span>
                                        </div>
                                    </div>
                                </div>

                            </div>

                            <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6 mt-3">
                                <div class="card trading mb-sm-0 mb-3">
                                    <div class="card-body">
                                        <div
                                            class="income-data d-flex justify-content-between align-items-center mb-sm-0 mb-2 ps-lg-0">
                                            <div>
                                                <h3 class="font-w600 fs-2 mb-0 text-white">
                                                    <h3 class="font-w600 fs-2 mb-0 text-white">
                                                        <?php
                                                        $child_ids = $_SESSION['child_ids'];
                                                        $stmt = $mysqli->prepare("SELECT
                                                                (SELECT COALESCE(SUM(amount), 0) FROM payment  WHERE user_id IN ($child_ids)) AS total_payments,
                                                                (SELECT COALESCE(SUM(amount), 0) FROM expenses WHERE user_id IN ($child_ids)) AS total_expenses,
                                                                (
                                                                    (SELECT COALESCE(SUM(amount), 0) FROM payment  WHERE user_id IN ($child_ids)) -
                                                                    (SELECT COALESCE(SUM(amount), 0) FROM expenses WHERE user_id IN ($child_ids))
                                                                ) AS total_profit
                                                            ");

                                                        $stmt->execute();
                                                        $result = $stmt->get_result()->fetch_assoc();
                                                        echo  $result['total_profit']
                                                        ?>
                                                    </h3>
                                                </h3>
                                                <span class="fs-6 font-w500 text-white">Total Profit</span>
                                            </div>
                                            <span class="income-icon style-3 mt-3">
                                                <svg width="34" height="32" viewBox="0 0 48 48" id="a"
                                                    xmlns="http://www.w3.org/2000/svg" fill="#347433" stroke="#347433">
                                                    <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                                    <g id="SVGRepo_tracerCarrier" stroke-linecap="round"
                                                        stroke-linejoin="round"></g>
                                                    <g id="SVGRepo_iconCarrier">
                                                        <defs>
                                                            <style>
                                                                .p {
                                                                    fill: none;
                                                                    stroke: #000000;
                                                                    stroke-linecap: round;
                                                                    stroke-linejoin: round;
                                                                }
                                                            </style>
                                                        </defs>
                                                        <g id="b">
                                                            <path id="c" class="p"
                                                                d="M16.5169,14.3442l7.7047-4.801,10.2741,8.6883v12.5665l-5.9671,4.836v-11.8175l-12.0117-9.4722Z">
                                                            </path>
                                                            <path id="d" class="p"
                                                                d="M26.0581,9.2578l5.8416-3.6121,10.4601,7.293-6.4328,4.9258">
                                                            </path>
                                                            <path id="e" class="p" d="M36.2041,28.6126l6.2959-5.1397">
                                                            </path>
                                                            <path id="f" class="p" d="M36.2041,25.9523l6.2959-5.1397">
                                                            </path>
                                                            <path id="g" class="p" d="M36.2041,23.292l6.2959-5.1397">
                                                            </path>
                                                            <path id="h" class="p" d="M36.2041,20.6317l6.2959-5.1397">
                                                            </path>
                                                            <path id="i" class="p"
                                                                d="M35.3139,14.172l2.7236-2.077-1.865-1.2474-1.4987,1.1314">
                                                            </path>
                                                            <path id="j" class="p"
                                                                d="M5.5,31.9538l13.5429,10.4006,7.4233-5.9106"></path>
                                                            <path id="k" class="p"
                                                                d="M5.5,29.2851l13.5429,10.4006,7.4233-5.9106"></path>
                                                            <path id="l" class="p"
                                                                d="M5.6039,26.6164l13.5429,10.4006,7.4233-5.9106">
                                                            </path>
                                                            <path id="m" class="p"
                                                                d="M5.5892,23.9478l13.5429,10.4006,7.4233-5.9106">
                                                            </path>
                                                            <path id="n" class="p"
                                                                d="M20.2345,23.7501c-.226,1.0274-1.6933,1.5535-3.2773,1.1753h0c-1.5841-.3783-2.685-1.5178-2.459-2.5451,.226-1.0274,1.6933-1.5535,3.2773-1.1753s2.685,1.5177,2.459,2.5451Z">
                                                            </path>
                                                            <path id="o" class="p"
                                                                d="M15.0514,15.826l-9.2955,5.5946,13.3311,10.1174,7.6392-6.0147">
                                                            </path>
                                                        </g>
                                                    </g>
                                                </svg>
                                            </span>

                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal fade" id="Event_Details_Modal">
                    <div class="modal-dialog modal-xl" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="modalTitleId">
                                    Travel Details
                                </h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Customer Name</th>
                                            <th>Destination</th>
                                            <th>Mobile</th>
                                            <th>Departure Date</th>
                                            <th>Service</th>
                                            <th>Sale Amount</th>
                                            <th>Payment Status</th>
                                        </tr>
                                    </thead>
                                    <tbody id="EventTable">
                                    </tbody>
                                </table>
                                <!-- <table class="mt-3 table">
                                    <thead>
                                        <tr>

                                            <td>Total Income</td>
                                            <td>Total Expense</td>
                                            <td>Vendor Expense</td>
                                            <td>Labour Expense</td>
                                            <td>TA/DA</td>
                                            <td>Mis.</td>
                                            <td>Profit</td>
                                        </tr>
                                    </thead>
                                    <tbody id="EventDataTable">
                                    </tbody>
                                </table> -->
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                    Close
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-xl-6">
                        <div class="card">
                            <form method="GET">
                                <div class="card-header d-flex justify-content-between">
                                    <div>
                                        <h5><i class="fa fa-calendar" aria-hidden="true"></i> Event Calendar</h5>
                                    </div>
                                    <div class="float-end d-flex">
                                        <div>
                                            <select name="month" id="month" class="form-control">
                                                <?php
                                                $currentMonth = date('n');
                                                for ($i = 1; $i <= 12; $i++) {
                                                    $selected = (isset($_GET['month']) ? $_GET['month'] == $i : $currentMonth == $i) ? 'selected' : '';
                                                    echo '<option value="' . $i . '" ' . $selected . '>' . $i . '</option>';
                                                }
                                                ?>
                                            </select>
                                        </div>
                                        <div class="mx-1">
                                            <select name="year" id="year" class="form-control">
                                                <?php
                                                $currentYear = date('Y');
                                                $endYear = date('Y', strtotime('+5 year'));
                                                for ($i = 2022; $i < $endYear; $i++) {
                                                    $selected = (isset($_GET['year']) ? $_GET['year'] == $i : $currentYear == $i) ? 'selected' : '';
                                                    echo '<option value="' . $i . '" ' . $selected . '>' . $i . '</option>';
                                                }
                                                ?>
                                            </select>
                                        </div>
                                        <div class="mx-1">
                                            <button type="submit" class="btn btn-primary">Search</button>
                                        </div>
                                    </div>
                                </div>
                            </form>

                            <div class="card-body  px-3">
                                <?php
                                $dateString = $year . '-' . $month . '-01';
                                echo '<h5 class="mx-4">' . date('F', strtotime($dateString)) . ', ' . date('Y', strtotime($dateString)) . '</h5>';

                                echo build_calendar($month, $year, $travel_date);
                                function build_calendar($month, $year, $travel_date)
                                {
                                    $daysOfWeek = array('Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday');
                                    $firstDayOfMonth = mktime(0, 0, 0, $month, 1, $year);
                                    $numberDays = date('t', $firstDayOfMonth);
                                    $dateComponents = getdate($firstDayOfMonth);
                                    $monthName = $dateComponents['month'];
                                    $dayOfWeek = $dateComponents['wday'];
                                    $calendar = "<table>";
                                    $calendar .= "<caption>$monthName $year</caption>";
                                    $calendar .= "<tr>";
                                    foreach ($daysOfWeek as $day) {
                                        $calendar .= "<th class='th_calendar'>$day</th>";
                                    }
                                    $calendar .= "</tr><tr>";
                                    if ($dayOfWeek > 0) {
                                        $calendar .= "<td class='td_calendar' colspan='$dayOfWeek'>&nbsp;</td>";
                                    }

                                    $currentDay = 1;
                                    $month = str_pad($month, 2, "0", STR_PAD_LEFT);
                                    $year = $year;
                                    $todayDate = date('Y-m-d');
                                    while ($currentDay <= $numberDays) {
                                        if ($dayOfWeek == 7) {
                                            $dayOfWeek = 0;
                                            $calendar .= "</tr><tr>";
                                        }

                                        $currentDayRel = str_pad($currentDay, 2, "0", STR_PAD_LEFT);
                                        $date = "$year-$month-$currentDayRel";

                                        $todayClass = ($date == $todayDate) ? 'today' : '';
                                        $todayText = ($date == $todayDate) ? 'Today' : '';
                                        $event = "";
                                        if (in_array($date, $travel_date)) {
                                            $event = 'bg-success';
                                        }
                                        $calendar .= "<td class=' td_calendar travel_date $todayClass $event' data-travel_date='$date'>$currentDay $todayText</td>";
                                        $currentDay++;
                                        $dayOfWeek++;
                                    }
                                    if ($dayOfWeek != 7) {
                                        $remainingDays = 7 - $dayOfWeek;
                                        $calendar .= "<td class='td_calendar' colspan='$remainingDays'>&nbsp;</td>";
                                    }
                                    $calendar .= "</tr>";
                                    $calendar .= "</table>";
                                    return $calendar;
                                }
                                ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3">
                        <div class="card">
                            <div class="card-header px-3">
                                <h4 class="card-title">Top Selling Destinations</h4>
                            </div>
                            <div class="card-body px-3">
                                <?php if (!empty($topDestinations)): ?>
                                    <?php $maxSales = max(array_column($topDestinations, 'total_sales')); ?>
                                    <?php foreach ($topDestinations as $index => $dest): ?>
                                        <div class="top-selling-item">
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <div>
                                                    <span class="badge badge-primary me-2">#<?= $index + 1 ?></span>
                                                    <strong><?= htmlspecialchars($dest['destination_name']) ?></strong>
                                                    <small class="text-muted">(<?= $dest['total_queries'] ?> queries)</small>
                                                </div>
                                                <div>
                                                    <strong class="text-success">₹<?= number_format($dest['total_sales'], 2) ?></strong>
                                                </div>
                                            </div>
                                            <div class="progress-bar-custom">
                                                <div class="progress-fill bg-success" style="width: <?= ($dest['total_sales'] / $maxSales) * 100 ?>%"></div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="text-center text-muted">No data available</div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3">
                        <div class="card">
                            <div class="card-header px-3">
                                <h4 class="card-title">Top Selling Services</h4>
                            </div>
                            <div class="card-body px-3">

                                <?php if (!empty($topServices)): ?>

                                    <?php $maxServiceSales = max(array_column($topServices, 'total_sales')); ?>

                                    <?php foreach ($topServices as $index => $service): ?>
                                        <div class="top-selling-item">
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <div>
                                                    <span class="badge badge-info me-2">#<?= $index + 1 ?></span>

                                                    <strong>
                                                        <?= htmlspecialchars($service['service_name'] ?? '') ?>
                                                    </strong>

                                                    <small class="text-muted">
                                                        (<?= $service['total_queries'] ?? 0 ?> bookings)
                                                    </small>
                                                </div>

                                                <div>
                                                    <strong class="text-warning">
                                                        ₹<?= number_format($service['total_sales'] ?? 0, 2) ?>
                                                    </strong>
                                                </div>
                                            </div>

                                            <div class="progress-bar-custom">
                                                <div class="progress-fill bg-info"
                                                    style="width: <?= $maxServiceSales > 0 ? ($service['total_sales'] / $maxServiceSales) * 100 : 0 ?>%">
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>

                                <?php else: ?>
                                    <div class="text-center text-muted">No data available</div>
                                <?php endif; ?>

                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-xl-12">
                        <div class="card">
                            <div class="card-header border-0 pb-0">
                                <h3>Expenses Summary</h3>
                            </div>
                            <div class="card-body">
                                <form action="" method="post">
                                    <div class="row">
                                        <div class="col-md-2">
                                            <select name="user_id" class="form-select">
                                                <option value="" selected disabled>Select User</option>
                                                <?php
                                                if ($_SESSION['user'] != 'admin') {
                                                    $user_ids = $_SESSION['child_ids'];
                                                    $stmt = $mysqli->prepare("SELECT * FROM users WHERE status = '1' AND FIND_IN_SET(id, ?)");
                                                    $stmt->bind_param("s", $user_ids);
                                                } else {
                                                    $stmt = $mysqli->prepare("SELECT * FROM users WHERE status = '1'");
                                                }

                                                $stmt->execute();
                                                $res = $stmt->get_result();

                                                while ($row = $res->fetch_assoc()):
                                                ?>
                                                    <option value="<?= $row['id'] ?>"
                                                        <?php if (isset($_GET['user']) && $_GET['user'] == $row['id']) echo 'selected'; ?>>
                                                        <?= htmlspecialchars($row['username']) ?>
                                                    </option>
                                                <?php endwhile; ?>
                                            </select>
                                        </div>
                                        <div class="col-md-2">
                                            <select name="customer_id" id="customer_id" class="form-select">
                                                <option value="" selected disabled>Select Customer</option>
                                                <?php
                                                $stmt = $mysqli->prepare("Select * from customers");
                                                $stmt->execute();
                                                $res = $stmt->get_result();
                                                while ($row = $res->fetch_assoc()):
                                                ?>
                                                    <option value="<?= $row['id'] ?>">
                                                        <?= $row['name'] ?>
                                                    </option>

                                                <?php endwhile; ?>
                                            </select>
                                        </div>
                                        <div class="col-md-2">
                                            <select name="status" id="" class="form-select">
                                                <option value="" selected disabled>Select Status</option>
                                                <option value="paid">Paid</option>
                                                <option value="unpaid">UnPaid</option>
                                            </select>
                                        </div>
                                        <div class="col-md-1">
                                            <button type="submit" class="btn btn-outline-success">Submit</button>
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
                                                <th>User</th>
                                                <th>File</th>
                                                <th>Customer</th>
                                                <!-- <th>Vendor</th> -->
                                                <th>Name</th>
                                                <th>Expense Category</th>
                                                <!-- <th>Expense Date</th> -->
                                                <th>Note</th>
                                                <!-- <th>Paymode</th>
                                                <th>Ref. No.</th> -->
                                                <th>Amount</th>
                                                <!-- <th>Billed</th>
                                                <th>Created at</th>
                                                <th>Action</th> -->
                                            </tr>
                                        </thead>

                                        <?php


                                        $user_id     = $_POST['user_id'] ?? '';
                                        $customer_id = $_POST['customer_id'] ?? '';
                                        $status      = $_POST['status'] ?? 'unpaid';

                                        // Base query
                                        $query = "SELECT a.*, b.name AS customer_name, c.name AS vendor_name, u.name AS user_name
                                        FROM expenses a
                                        LEFT JOIN customers b ON a.ids = b.id
                                        LEFT JOIN vendor c ON a.vendor_id = c.id
                                        JOIN users u ON a.user_id = u.id
                                        WHERE a.paid_status = ?";

                                        $params = [$status];
                                        $types  = "s";

                                        if ($_SESSION['user'] != 'admin') {
                                            $ids = array_map('trim', explode(',', $_SESSION['child_ids']));

                                            if (!empty($ids)) {
                                                $conditions = [];
                                                foreach ($ids as $id) {
                                                    $conditions[] = "FIND_IN_SET(?, a.user_id)";
                                                    $params[] = $id;
                                                    $types .= "i";
                                                }
                                                // Group OR conditions
                                                $query .= " AND (" . implode(" OR ", $conditions) . ")";
                                            }
                                        }

                                        // 🔹 Extra filters
                                        if (!empty($user_id)) {
                                            $query .= " AND a.user_id = ?";
                                            $params[] = $user_id;
                                            $types   .= "i";
                                        }

                                        if (!empty($customer_id)) {
                                            $query .= " AND a.ids = ?";
                                            $params[] = $customer_id;
                                            $types   .= "i";
                                        }

                                        $query .= " ORDER BY a.id DESC";

                                        // Prepare + Bind + Execute
                                        $stmt = $mysqli->prepare($query);
                                        $stmt->bind_param($types, ...$params);
                                        $stmt->execute();


                                        $res = $stmt->get_result();
                                        ?>
                                        <tbody>
                                            <?php
                                            $sno = 1;
                                            while ($row = $res->fetch_assoc()) {
                                                echo '<tr>
                                                    <td>' . $sno++ . '</td>
                                                    <td>' . $row['user_name'] . '</td>';
                                                if (empty($row['file'])) {
                                                    echo '<td>There is no file upload</td>';
                                                } else {
                                                    echo '<td><a href="' . BASE_PATH . 'images/invoices/' . $row['file'] . '" target="_blank">File</a></td>';
                                                }
                                                echo '<td>' . $row['customer_name'] . '</td>
                                                    <td>' . $row['name'] . '</td>
                                                    <td>' . $row['expense_category'] . '</td>
                                                    <td>' . $row['note'] . '</td>
                                                    <td>' . $row['amount'] . '</td>
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

                <div class="row">
                    <div class="col-xl-12">
                        <div class="card">
                            <div class="card-header border-0 pb-0">
                                <h3>Follow Up List</h3>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table " id="" style="min-width: 845px">
                                        <thead>
                                            <tr>
                                                <th>S.No</th>
                                                <th>User</th>
                                                <th>Customer Info </th>
                                                <th>Destination </th>
                                                <th>Travel Dates</th>
                                                <th>Person's </th>
                                                <th>Service </th>
                                                <th>Follow Up Time</th>
                                                <th>Remarks </th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                                while ($row = $data->fetch_assoc()) 
                                                    {
                                            ?>
                                                <tr>
                                                    <td>
                                                        <?= $sno++; ?>
                                                        <br>
                                                        <?php if ($row['pinned']): ?>
                                                            <i class="fas fa-star text-warning" title="Pinned"></i>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <div class="d-flex align-items-center justify-content-between">
                                                            <span><?= $row['user']; ?></span>
                                                        </div>
                                                    </td>

                                                    <td>
                                                        <strong> Name :</strong> <?= $row['customer']; ?>
                                                        <br>
                                                        <strong>Mobile : </strong> <?= $row['mobile']; ?>
                                                        <br>
                                                        <strong>Email :</strong> <?= $row['email']; ?>

                                                    </td>
                                                    <td><?= $row['destination_name']; ?></td>
                                                    <!-- <td><?= $row['travel_month']; ?></td> -->
                                                    <td>

                                                        <?= $row['travel_month']; ?>
                                                        <br>
                                                        <strong>From Date :</strong> <?= $row['from_date']; ?>
                                                        <br>
                                                        <strong>To Date :</strong> <?= $row['to_date']; ?>
                                                        <br>
                                                    </td>
                                                    <td>
                                                        <strong>Adult :</strong> <?= $row['adult']; ?>
                                                        <br>
                                                        <strong>Child :</strong><?= $row['child']; ?>
                                                    </td>
                                                    <td><?= $row['service']; ?></td>
                                                    <td>
                                                        <?php if ($row['call_time'] && $row['call_time'] != '00:00:00'): ?>
                                                            <strong>Call Time :</strong> <?= $row['call_time']; ?><br>
                                                        <?php endif; ?>

                                                        <?php if ($row['call_date'] && $row['call_date'] != '0000-00-00'): ?>
                                                            <strong>Call Date :</strong> <?= $row['call_date']; ?>
                                                        <?php endif; ?>
                                                    </td>

                                                    <td>
                                                        <?php
                                                        $remarks = $row['remarks'];
                                                        if (strlen($remarks) > 20) {
                                                            $short_remark = substr($remarks, 0, 20) . '...';
                                                            echo '<span data-bs-toggle="tooltip" data-bs-placement="top" title="' . htmlspecialchars($remarks) . '">' . $short_remark . '</span>';
                                                        } else {
                                                            echo $remarks;
                                                        }
                                                        ?>
                                                    </td>



                                                </tr>
                                            <?php
                                            }
                                            ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-xl-12">
                        <div class="card">
                            <div class="card-header border-0 pb-0">
                                <h3>Task List</h3>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table " style="min-width: 845px">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Title</th>
                                                <th>Due Date</th>
                                                <th>Priority</th>
                                                <th>Status</th>
                                                <th>Description</th>
                                                <th>File</th>
                                                <th>Remark</th>
                                                <th>User</th>
                                                <th>Date</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <?php
                                        $where = [];
                                        $params = [];
                                        $types  = "";
                                        if ($_SESSION['user'] != "admin") {
                                            $child_ids = implode(',', array_filter(explode(',', $_SESSION['child_ids']), 'is_numeric'));
                                            $where[] = "user_id IN ($child_ids)";
                                        } else {
                                            if (!empty($_GET['user'])) {
                                                $where[] = "user_id = ?";
                                                $params[] = $_GET['user'];
                                                $types   .= "i";
                                            }
                                        }

                                        $query = "SELECT * FROM `task`";
                                        if (!empty($where)) {
                                            $query .= " WHERE " . implode(" AND ", $where);
                                        }
                                        $query .= " ORDER BY id DESC";
                                        $stmt = $mysqli->prepare($query);
                                        if (!empty($params)) {
                                            $stmt->bind_param($types, ...$params);
                                        }
                                        $stmt->execute();
                                        $result = $stmt->get_result();
                                        ?>
                                        <tbody>
                                            <?php $sr_no = 1;
                                            while ($row = $result->fetch_assoc()):  ?>
                                                <tr>
                                                    <td><?= $sr_no; ?></td>
                                                    <td><?= $row['title'] ?></td>
                                                    <td><?= $row['due_date'] ?></td>
                                                    <td><?= $row['priority'] ?></td>
                                                    <td><?= $row['status'] ?></td>
                                                    <td><?= $row['description'] ?></td>
                                                    <td>
                                                        <?php if (!empty($row['file'])): ?>
                                                            <img src="<?= BASE_PATH ?>images/task/<?= $row['file'] ?>" height="60"
                                                                alt="img">
                                                        <?php else: ?>
                                                            No Image
                                                        <?php endif; ?>
                                                    </td>
                                                    <td><?= ($row['remarks']) ?></td>
                                                    <td>
                                                        <?php
                                                        $user_id = $row['user_id'];
                                                        $stmt_user = $mysqli->prepare("SELECT name FROM users WHERE id = ?");
                                                        $stmt_user->bind_param("i", $user_id);
                                                        $stmt_user->execute();
                                                        $stmt_user->bind_result($user_name);
                                                        $stmt_user->fetch();
                                                        $stmt_user->close();
                                                        echo ($user_name);
                                                        ?>
                                                    </td>
                                                    <td><?= date('Y-m-d', strtotime($row['created_at'])) ?></td>
                                                    <td>
                                                        <button type="button"
                                                            class="btn btn-primary shadow btn-xs sharp me-1 editTask"
                                                            data-id="<?= $row['id'] ?>" data-due_date="<?= $row['due_date'] ?>"
                                                            data-priority="<?= $row['priority'] ?>"
                                                            data-status="<?= $row['status'] ?>"
                                                            data-description="<?= $row['description'] ?>"
                                                            data-remark="<?= $row['remarks'] ?>"
                                                            data-user_id="<?= $row['user_id'] ?>"
                                                            data-title="<?= $row['title'] ?>"
                                                            data-repeat_interval="<?= $row['repeat_interval'] ?>"
                                                            data-repeat_count="<?= $row['repeat_count'] ?>">
                                                            <i class="fa fa-pen"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                            <?php $sr_no++;
                                            endwhile; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- <div class="row">
                    <div class="col-xl-3">
                        <div class="card">
                            <div class="card-header border-0 pb-0">
                                <h3>User List</h3>
                            </div>
                            <div class="card-body">
                                <div class="flex-column d-block nav-pills gap dz-scroll" id="Customerslist1">
                                    <?php
                                    $allowed_ids = $_SESSION['child_ids'];
                                    $stmt = $mysqli->prepare("SELECT 
                                            users.*, 
                                            (SELECT COUNT(*) FROM `query_mst` WHERE query_mst.user_id = users.id) AS total_queries
                                            FROM users
                                            WHERE status = '1' AND id IN ($allowed_ids)
                                            ORDER BY id DESC
                                        ");
                                    $stmt->execute();
                                    $res = $stmt->get_result();
                                    while ($row = $res->fetch_assoc()):
                                    ?>
                                        <div class="d-flex guest-list mb-4">
                                            <img src="<?php echo BASE_PATH; ?>images/user_image.png" alt="image">
                                            <div>
                                                <h4 class="m-0"><?= $row['name']  ?></h4>
                                                <span class="text-primary">User Leads :
                                                    <?php echo  $row['total_queries']  ?></span>
                                            </div>
                                        </div>
                                    <?php endwhile; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-9">
                        <div class="card">
                            <div class="card-header border-0 pb-0 flex-wrap">
                                <h3>Customer Reviews</h3>
                                <div class="d-flex">
                                    <select class="form-control default-select style-1 me-3 ms-0 border">
                                        <option>Sort by Newest</option>
                                        <option>Oldest</option>
                                        <option>Newest</option>
                                    </select>
                                    <a href="javascript:void(0);" class="btn btn-primary light text-nowrap">View
                                        more</a>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-xl-4 col-sm-5">
                                        <div class="nav review-tab flex-column d-block nav-pills gap dz-scroll mb-3"
                                            id="Customerslist2">
                                            <a href="#v-pills-bella" data-bs-toggle="pill" class="nav-link active show">
                                                <div class="d-flex guest-list">
                                                    <img src="images/review1.jpg" alt="image">
                                                    <div>
                                                        <h4 class="m-0">Bella Morgan</h4>
                                                        <span>24min ago</span>
                                                    </div>
                                                </div>
                                            </a>
                                            <a href="#v-pills-louis" data-bs-toggle="pill" class="nav-link">
                                                <div class="d-flex guest-list">
                                                    <img src="images/review2.jpg" alt="image">
                                                    <div>
                                                        <h4 class="m-0">Louis Pattinson</h4>
                                                        <span>24min ago</span>
                                                    </div>
                                                </div>
                                            </a>
                                            <a href="#v-pills-hans" data-bs-toggle="pill" class="nav-link">
                                                <div class="d-flex guest-list">
                                                    <img src="images/review3.jpg" alt="image">
                                                    <div>
                                                        <h4 class="m-0">Hans Takeshi</h4>
                                                        <span>24min ago</span>
                                                    </div>
                                                </div>
                                            </a>
                                            <a href="#v-pills-demian" data-bs-toggle="pill" class="nav-link">
                                                <div class="d-flex guest-list">
                                                    <img src="images/review4.jpg" alt="image">
                                                    <div>
                                                        <h4 class="m-0">Demian Sarumaha</h4>
                                                        <span>24min ago</span>
                                                    </div>
                                                </div>
                                            </a>
                                            <a href="#v-pills-morgan" data-bs-toggle="pill" class="nav-link">
                                                <div class="d-flex guest-list">
                                                    <img src="images/review1.jpg" alt="image">
                                                    <div>
                                                        <h4 class="m-0">Bella Morgan</h4>
                                                        <span>24min ago</span>
                                                    </div>
                                                </div>
                                            </a>
                                            <a href="#v-pills-morgan1" data-bs-toggle="pill" class="nav-link">
                                                <div class="d-flex guest-list">
                                                    <img src="images/review1.jpg" alt="image">
                                                    <div>
                                                        <h4 class="m-0">Bella Morgan</h4>
                                                        <span>24min ago</span>
                                                    </div>
                                                </div>
                                            </a>
                                        </div>
                                    </div>
                                    <div class="col-xl-8 col-sm-7">
                                        <div class="tab-content">
                                            <div id="v-pills-bella" class="tab-pane r-tab fade active show">
                                                <h3 class="font-w500">I love that room service</h3>
                                                <ul class="star-review">
                                                    <li><i class="fas fa-star"></i></li>
                                                    <li><i class="fas fa-star"></i></li>
                                                    <li><i class="fas fa-star"></i></li>
                                                    <li><i class="fas fa-star"></i></li>
                                                </ul>
                                                <p class="review-para mt-xl-4 mt-md-3 mt-sm-2 mt-2">
                                                    We were totally refreshed and rejuvenated for the whole of next year
                                                    and it was due to the relaxing stay at the hotel. The hotel is
                                                    absolutely marvelous! We liked absolutely everything, starting from
                                                    the breakfast through to the perfect room service including the
                                                    cleanliness and extra services such as
                                                </p>
                                                <div class="mb-sm-2">
                                                    <a href="javascript:void(0);"
                                                        class="btn btn-dark light border-0 mb-2 btn-sm ">Great
                                                        Service</a>
                                                    <a href="javascript:void(0);"
                                                        class="btn btn-dark light border-0 mb-2 btn-sm mx-xl-3 mx-md-0">Recomended</a>
                                                    <a href="javascript:void(0);"
                                                        class="btn btn-dark light border-0 mb-2 btn-sm ">Best Price</a>
                                                </div>
                                                <div
                                                    class="d-flex align-items-center justify-content-between flex-md-wrap flex-sm-wrap flex-wrap mt-3 mt-sm-5 pt-xl-5 pt-lg-0 mb-0 mb-sm-0">
                                                    <div class="d-flex guest-list mb-xl-0 mb-md-2 mb-sm-2 mb-2">
                                                        <img src="images/review1.jpg" alt="image">
                                                        <div>
                                                            <h4 class="m-0">Bella Morgan</h4>
                                                            <span>24min ago</span>
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <a href="javascript:void(0);"
                                                            class="btn btn-success border-0 me-sm-3 me-0">Accept
                                                            <svg class="ms-2" width="20" height="21" viewBox="0 0 20 21"
                                                                fill="none" xmlns="http://www.w3.org/2000/svg">
                                                                <path
                                                                    d="M10 0.5C4.50742 0.5 0 5.00742 0 10.5C0 15.9926 4.50742 20.5 10 20.5C15.4926 20.5 20 15.9926 20 10.5C20 5.00742 15.4926 0.5 10 0.5ZM8.7898 15.0484L4.4107 10.6694L6.06781 9.01227L8.86648 11.8109L14.485 6.70344L16.062 8.43723L8.7898 15.0484Z"
                                                                    fill="white" />
                                                            </svg>
                                                        </a>
                                                        <a href="javascript:void(0);"
                                                            class="btn btn-primary border-0">Reject
                                                            <svg class="ms-2" width="20" height="21" viewBox="0 0 20 21"
                                                                fill="none" xmlns="http://www.w3.org/2000/svg">
                                                                <path
                                                                    d="M10 0.5C4.50742 0.5 0 5.00742 0 10.5C0 15.9926 4.50742 20.5 10 20.5C15.4926 20.5 20 15.9926 20 10.5C20 5.00742 15.4926 0.5 10 0.5ZM14.9719 13.8148L13.3148 15.4719L10 12.1571L6.68523 15.4719L5.02812 13.8148L8.34289 10.5L5.02812 7.18523L6.68523 5.52812L10 8.84289L13.3148 5.52812L14.9719 7.18523L11.6571 10.5L14.9719 13.8148Z"
                                                                    fill="white" />
                                                            </svg>
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                            <div id="v-pills-louis" class="tab-pane r-tab fade">
                                                <h3>I love that room service</h3>
                                                <ul class="star-review">
                                                    <li><i class="fas fa-star"></i></li>
                                                    <li><i class="fas fa-star"></i></li>
                                                    <li><i class="fas fa-star"></i></li>
                                                    <li><i class="fas fa-star"></i></li>
                                                </ul>
                                                <p class="review-para mt-xl-4 mt-md-3 mt-sm-2 mt-2">
                                                    We were totally refreshed and rejuvenated for the whole of next year
                                                    and it was due to the relaxing stay at the hotel. The hotel is
                                                    absolutely marvelous! We liked absolutely everything, starting from
                                                    the breakfast through to the perfect room service including the
                                                    cleanliness and extra services such as
                                                </p>
                                                <div class="mb-sm-2">
                                                    <a href="javascript:void(0);"
                                                        class="btn btn-dark light border-0 mb-2 btn-sm">Great
                                                        Service</a>
                                                    <a href="javascript:void(0);"
                                                        class="btn btn-dark light border-0 mb-2 btn-sm mx-xl-3 mx-md-0">Recomended</a>
                                                    <a href="javascript:void(0);"
                                                        class="btn btn-dark light border-0 mb-2 btn-sm ">Best Price</a>
                                                </div>
                                                <div
                                                    class="d-flex align-items-center justify-content-between flex-md-wrap flex-sm-wrap flex-wrap mt-3 mt-sm-5 pt-xl-5 pt-lg-0 mb-3 mb-sm-0">
                                                    <div class="d-flex guest-list mb-xl-0 mb-md-2 mb-sm-2 mb-2">
                                                        <img src="images/review2.jpg" alt="image">
                                                        <div>
                                                            <h4 class="m-0">Louis Pattinson</h4>
                                                            <span>24min ago</span>
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <a href="javascript:void(0);"
                                                            class="btn btn-success border-0 me-sm-3 me-0">Accept
                                                            <svg class="ms-2" width="20" height="21" viewBox="0 0 20 21"
                                                                fill="none" xmlns="http://www.w3.org/2000/svg">
                                                                <path
                                                                    d="M10 0.5C4.50742 0.5 0 5.00742 0 10.5C0 15.9926 4.50742 20.5 10 20.5C15.4926 20.5 20 15.9926 20 10.5C20 5.00742 15.4926 0.5 10 0.5ZM8.7898 15.0484L4.4107 10.6694L6.06781 9.01227L8.86648 11.8109L14.485 6.70344L16.062 8.43723L8.7898 15.0484Z"
                                                                    fill="white" />
                                                            </svg>
                                                        </a>
                                                        <a href="javascript:void(0);"
                                                            class="btn btn-primary border-0">Reject
                                                            <svg class="ms-2" width="20" height="21" viewBox="0 0 20 21"
                                                                fill="none" xmlns="http://www.w3.org/2000/svg">
                                                                <path
                                                                    d="M10 0.5C4.50742 0.5 0 5.00742 0 10.5C0 15.9926 4.50742 20.5 10 20.5C15.4926 20.5 20 15.9926 20 10.5C20 5.00742 15.4926 0.5 10 0.5ZM14.9719 13.8148L13.3148 15.4719L10 12.1571L6.68523 15.4719L5.02812 13.8148L8.34289 10.5L5.02812 7.18523L6.68523 5.52812L10 8.84289L13.3148 5.52812L14.9719 7.18523L11.6571 10.5L14.9719 13.8148Z"
                                                                    fill="white" />
                                                            </svg>
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                            <div id="v-pills-hans" class="tab-pane r-tab fade">
                                                <h3>I love that room service</h3>
                                                <ul class="star-review">
                                                    <li><i class="fas fa-star"></i></li>
                                                    <li><i class="fas fa-star"></i></li>
                                                    <li><i class="fas fa-star"></i></li>
                                                    <li><i class="fas fa-star"></i></li>
                                                </ul>
                                                <p class="review-para mt-xl-4 mt-md-3 mt-sm-2 mt-2">
                                                    We were totally refreshed and rejuvenated for the whole of next year
                                                    and it was due to the relaxing stay at the hotel. The hotel is
                                                    absolutely marvelous! We liked absolutely everything, starting from
                                                    the breakfast through to the perfect room service including the
                                                    cleanliness and extra services such as
                                                </p>
                                                <div class="mb-sm-2">
                                                    <a href="javascript:void(0);"
                                                        class="btn btn-dark light border-0 mb-2 btn-sm">Great
                                                        Service</a>
                                                    <a href="javascript:void(0);"
                                                        class="btn btn-dark light border-0 mb-2 btn-sm mx-xl-3 mx-md-0">Recomended</a>
                                                    <a href="javascript:void(0);"
                                                        class="btn btn-dark light border-0 mb-2 btn-sm">Best Price</a>
                                                </div>
                                                <div
                                                    class="d-flex align-items-center justify-content-between flex-md-wrap flex-sm-wrap flex-wrap mt-3 mt-sm-5 pt-xl-5 pt-lg-0 mb-0 mb-sm-0">
                                                    <div class="d-flex guest-list mb-xl-0 mb-md-2 mb-sm-2 mb-2">
                                                        <img src="images/review3.jpg" alt="image">
                                                        <div>
                                                            <h4 class="m-0">Hans Takeshi</h4>
                                                            <span>24min ago</span>
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <a href="javascript:void(0);"
                                                            class="btn btn-success border-0 me-sm-3 me-0">Accept
                                                            <svg class="ms-2" width="20" height="21" viewBox="0 0 20 21"
                                                                fill="none" xmlns="http://www.w3.org/2000/svg">
                                                                <path
                                                                    d="M10 0.5C4.50742 0.5 0 5.00742 0 10.5C0 15.9926 4.50742 20.5 10 20.5C15.4926 20.5 20 15.9926 20 10.5C20 5.00742 15.4926 0.5 10 0.5ZM8.7898 15.0484L4.4107 10.6694L6.06781 9.01227L8.86648 11.8109L14.485 6.70344L16.062 8.43723L8.7898 15.0484Z"
                                                                    fill="white" />
                                                            </svg>
                                                        </a>
                                                        <a href="javascript:void(0);"
                                                            class="btn btn-primary border-0">Reject
                                                            <svg class="ms-2" width="20" height="21" viewBox="0 0 20 21"
                                                                fill="none" xmlns="http://www.w3.org/2000/svg">
                                                                <path
                                                                    d="M10 0.5C4.50742 0.5 0 5.00742 0 10.5C0 15.9926 4.50742 20.5 10 20.5C15.4926 20.5 20 15.9926 20 10.5C20 5.00742 15.4926 0.5 10 0.5ZM14.9719 13.8148L13.3148 15.4719L10 12.1571L6.68523 15.4719L5.02812 13.8148L8.34289 10.5L5.02812 7.18523L6.68523 5.52812L10 8.84289L13.3148 5.52812L14.9719 7.18523L11.6571 10.5L14.9719 13.8148Z"
                                                                    fill="white" />
                                                            </svg>
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                            <div id="v-pills-demian" class="tab-pane r-tab fade">
                                                <h3>I love that room service</h3>
                                                <ul class="star-review">
                                                    <li><i class="fas fa-star"></i></li>
                                                    <li><i class="fas fa-star"></i></li>
                                                    <li><i class="fas fa-star"></i></li>
                                                    <li><i class="fas fa-star"></i></li>
                                                </ul>
                                                <p class="review-para mt-xl-4 mt-md-3 mt-sm-2 mt-2">
                                                    We were totally refreshed and rejuvenated for the whole of next year
                                                    and it was due to the relaxing stay at the hotel. The hotel is
                                                    absolutely marvelous! We liked absolutely everything, starting from
                                                    the breakfast through to the perfect room service including the
                                                    cleanliness and extra services such as
                                                </p>
                                                <div class="mb-sm-2">
                                                    <a href="javascript:void(0);"
                                                        class="btn btn-dark light border-0 mb-2 btn-sm">Great
                                                        Service</a>
                                                    <a href="javascript:void(0);"
                                                        class="btn btn-dark light border-0 mb-2 btn-sm mx-xl-3 mx-md-0">Recomended</a>
                                                    <a href="javascript:void(0);"
                                                        class="btn btn-dark light border-0 mb-2 btn-sm">Best Price</a>
                                                </div>
                                                <div
                                                    class="d-flex align-items-center justify-content-between flex-md-wrap flex-sm-wrap flex-wrap mt-3 mt-sm-5 pt-xl-5 pt-lg-0 mb-0 mb-sm-0">
                                                    <div class="d-flex guest-list  mb-xl-0 mb-md-2 mb-sm-2 mb-2">
                                                        <img src="images/review4.jpg" alt="image">
                                                        <div>
                                                            <h4 class="m-0">Demian Sarumaha</h4>
                                                            <span>24min ago</span>
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <a href="javascript:void(0);"
                                                            class="btn btn-success border-0 me-sm-3 me-0">Accept
                                                            <svg class="ms-2" width="20" height="21" viewBox="0 0 20 21"
                                                                fill="none" xmlns="http://www.w3.org/2000/svg">
                                                                <path
                                                                    d="M10 0.5C4.50742 0.5 0 5.00742 0 10.5C0 15.9926 4.50742 20.5 10 20.5C15.4926 20.5 20 15.9926 20 10.5C20 5.00742 15.4926 0.5 10 0.5ZM8.7898 15.0484L4.4107 10.6694L6.06781 9.01227L8.86648 11.8109L14.485 6.70344L16.062 8.43723L8.7898 15.0484Z"
                                                                    fill="white" />
                                                            </svg>
                                                        </a>
                                                        <a href="javascript:void(0);"
                                                            class="btn btn-danger border-0">Reject
                                                            <svg class="ms-2" width="20" height="21" viewBox="0 0 20 21"
                                                                fill="none" xmlns="http://www.w3.org/2000/svg">
                                                                <path
                                                                    d="M10 0.5C4.50742 0.5 0 5.00742 0 10.5C0 15.9926 4.50742 20.5 10 20.5C15.4926 20.5 20 15.9926 20 10.5C20 5.00742 15.4926 0.5 10 0.5ZM14.9719 13.8148L13.3148 15.4719L10 12.1571L6.68523 15.4719L5.02812 13.8148L8.34289 10.5L5.02812 7.18523L6.68523 5.52812L10 8.84289L13.3148 5.52812L14.9719 7.18523L11.6571 10.5L14.9719 13.8148Z"
                                                                    fill="white" />
                                                            </svg>
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                            <div id="v-pills-morgan" class="tab-pane r-tab fade">
                                                <h3>I love that room service</h3>
                                                <ul class="star-review">
                                                    <li><i class="fas fa-star"></i></li>
                                                    <li><i class="fas fa-star"></i></li>
                                                    <li><i class="fas fa-star"></i></li>
                                                    <li><i class="fas fa-star"></i></li>
                                                </ul>
                                                <p class="review-para mt-xl-4 mt-md-3 mt-sm-2 mt-2">
                                                    We were totally refreshed and rejuvenated for the whole of next year
                                                    and it was due to the relaxing stay at the hotel. The hotel is
                                                    absolutely marvelous! We liked absolutely everything, starting from
                                                    the breakfast through to the perfect room service including the
                                                    cleanliness and extra services such as
                                                </p>
                                                <div>
                                                    <a href="javascript:void(0);"
                                                        class="btn btn-dark light border-0 mb-2 btn-sm">Great
                                                        Service</a>
                                                    <a href="javascript:void(0);"
                                                        class="btn btn-dark light border-0 mb-2 btn-sm mx-xl-3 mx-md-0">Recomended</a>
                                                    <a href="javascript:void(0);"
                                                        class="btn btn-dark light border-0 mb-2 btn-sm ">Best Price</a>
                                                </div>
                                                <div
                                                    class="d-flex align-items-center justify-content-between flex-md-wrap flex-sm-wrap flex-wrap mt-3 mt-sm-5 pt-xl-5 pt-lg-0 mb-0 mb-sm-0">
                                                    <div class="d-flex guest-list mb-xl-0 mb-md-2 mb-sm-2 mb-2">
                                                        <img src="images/review1.jpg" alt="image">
                                                        <div>
                                                            <h4 class="m-0">Bella Morgan</h4>
                                                            <span>24min ago</span>
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <a href="javascript:void(0);"
                                                            class="btn btn-success border-0 me-sm-3 me-0">Accept
                                                            <svg class="ms-2" width="20" height="21" viewBox="0 0 20 21"
                                                                fill="none" xmlns="http://www.w3.org/2000/svg">
                                                                <path
                                                                    d="M10 0.5C4.50742 0.5 0 5.00742 0 10.5C0 15.9926 4.50742 20.5 10 20.5C15.4926 20.5 20 15.9926 20 10.5C20 5.00742 15.4926 0.5 10 0.5ZM8.7898 15.0484L4.4107 10.6694L6.06781 9.01227L8.86648 11.8109L14.485 6.70344L16.062 8.43723L8.7898 15.0484Z"
                                                                    fill="white" />
                                                            </svg>
                                                        </a>
                                                        <a href="javascript:void(0);"
                                                            class="btn btn-primary border-0">Reject
                                                            <svg class="ms-2" width="20" height="21" viewBox="0 0 20 21"
                                                                fill="none" xmlns="http://www.w3.org/2000/svg">
                                                                <path
                                                                    d="M10 0.5C4.50742 0.5 0 5.00742 0 10.5C0 15.9926 4.50742 20.5 10 20.5C15.4926 20.5 20 15.9926 20 10.5C20 5.00742 15.4926 0.5 10 0.5ZM14.9719 13.8148L13.3148 15.4719L10 12.1571L6.68523 15.4719L5.02812 13.8148L8.34289 10.5L5.02812 7.18523L6.68523 5.52812L10 8.84289L13.3148 5.52812L14.9719 7.18523L11.6571 10.5L14.9719 13.8148Z"
                                                                    fill="white" />
                                                            </svg>
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                            <div id="v-pills-morgan1" class="tab-pane r-tab fade">
                                                <h3>I love that room service</h3>
                                                <ul class="star-review">
                                                    <li><i class="fas fa-star"></i></li>
                                                    <li><i class="fas fa-star"></i></li>
                                                    <li><i class="fas fa-star"></i></li>
                                                    <li><i class="fas fa-star"></i></li>
                                                </ul>
                                                <p class="review-para mt-xl-4 mt-md-3 mt-sm-2 mt-2">
                                                    We were totally refreshed and rejuvenated for the whole of next year
                                                    and it was due to the relaxing stay at the hotel. The hotel is
                                                    absolutely marvelous! We liked absolutely everything, starting from
                                                    the breakfast through to the perfect room service including the
                                                    cleanliness and extra services such as
                                                </p>
                                                <div class="mb-sm-2">
                                                    <a href="javascript:void(0);"
                                                        class="btn btn-dark light border-0 mb-2 btn-sm">Great
                                                        Service</a>
                                                    <a href="javascript:void(0);"
                                                        class="btn btn-dark light border-0 mb-2 btn-sm mx-xl-3 mx-md-0">Recomended</a>
                                                    <a href="javascript:void(0);"
                                                        class="btn btn-dark light border-0 mb-2 btn-sm">Best Price</a>
                                                </div>
                                                <div
                                                    class="d-flex align-items-center justify-content-between flex-md-wrap flex-sm-wrap flex-wrap mt-3 mt-sm-5 pt-xl-5 pt-lg-0 mb-0 mb-sm-0">
                                                    <div class="d-flex guest-list mb-xl-0 mb-md-2 mb-sm-2 mb-2">
                                                        <img src="images/review1.jpg" alt="image">
                                                        <div>
                                                            <h4 class="m-0">Bella Morgan</h4>
                                                            <span>24min ago</span>
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <a href="javascript:void(0);"
                                                            class="btn btn-success border-0 me-sm-3 me-0">Accept
                                                            <svg class="ms-2" width="20" height="21" viewBox="0 0 20 21"
                                                                fill="none" xmlns="http://www.w3.org/2000/svg">
                                                                <path
                                                                    d="M10 0.5C4.50742 0.5 0 5.00742 0 10.5C0 15.9926 4.50742 20.5 10 20.5C15.4926 20.5 20 15.9926 20 10.5C20 5.00742 15.4926 0.5 10 0.5ZM8.7898 15.0484L4.4107 10.6694L6.06781 9.01227L8.86648 11.8109L14.485 6.70344L16.062 8.43723L8.7898 15.0484Z"
                                                                    fill="white" />
                                                            </svg>
                                                        </a>
                                                        <a href="javascript:void(0);"
                                                            class="btn btn-primary border-0">Reject
                                                            <svg class="ms-2" width="20" height="21" viewBox="0 0 20 21"
                                                                fill="none" xmlns="http://www.w3.org/2000/svg">
                                                                <path
                                                                    d="M10 0.5C4.50742 0.5 0 5.00742 0 10.5C0 15.9926 4.50742 20.5 10 20.5C15.4926 20.5 20 15.9926 20 10.5C20 5.00742 15.4926 0.5 10 0.5ZM14.9719 13.8148L13.3148 15.4719L10 12.1571L6.68523 15.4719L5.02812 13.8148L8.34289 10.5L5.02812 7.18523L6.68523 5.52812L10 8.84289L13.3148 5.52812L14.9719 7.18523L11.6571 10.5L14.9719 13.8148Z"
                                                                    fill="white" />
                                                            </svg>
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div> -->
            </div>
        </div>
    </div>
</div>


<div class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Task</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal">
                </button>
            </div>
            <div class="modal-body">
                <form class="needs-validation" action="task.php" id="companyform" novalidate method="POST" enctype="multipart/form-data">
                    <div class="row">
                        <div class="col-md-6">
                            <label for="name" class="form-label">Title</label>
                            <input type="text" class="form-control" id="title" name="title" value="" required>
                        </div>
                        <div class="col-md-6">
                            <label for="name" class="form-label">Due Date</label>
                            <input type="date" class="form-control" id="due_date" name="due_date" value="" required>
                        </div>
                        <div class="col-md-6">
                            <label for="name" class="form-label">Priority</label>
                            <select name="priority" id="" class="form-control">
                                <option value="Low">Low</option>
                                <option value="Medium">Medium</option>
                                <option value="High">High</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="name" class="form-label">Status</label>
                            <select name="status" id="" class="form-control">
                                <option value="Pending">Pending</option>
                                <option value="InProgress">InProgress</option>
                                <option value="OnHold">OnHold</option>
                                <option value="Complete">Complete</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="name" class="form-label">Description</label>
                            <textarea name="descriptions" id="" class="form-control"></textarea>
                        </div>
                        <div class="col-md-6">
                            <label for="name" class="form-label">Remark</label>
                            <input type="text" class="form-control" id="remark" name="remark">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-6">
                            <label for="image">File</label>
                            <input type="file" name="image" class="form-control">
                        </div>
                        <?php if ($_SESSION['user'] == "admin"): ?>
                            <div class="col-6" id="select_users">
                                <label for="">Select Assign User</label>
                                <select name="user_ids[]" id="multiple_user" class="form-control" multiple>
                                    <?php
                                    $user_id = $row['user_id'];
                                    $stmt_user = $mysqli->prepare("SELECT * FROM users where status='1' ");
                                    $stmt_user->execute();
                                    $res = $stmt_user->get_result();
                                    while ($row = $res->fetch_assoc()):
                                    ?>
                                        <option value="<?= $row['id'] ?>"><?= $row['name'] ?></option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                        <?php endif;  ?>
                        <div class="col-6">
                            <label>Repeat Interval</label>
                            <select name="repeat_interval" id="repeat_interval" class="form-control">
                                <option value="">No Repeat</option>
                                <option value="daily">Daily</option>
                                <option value="weekly">Weekly</option>
                                <option value="monthly">Monthly</option>
                            </select>
                        </div>

                        <div class="col-6">
                            <label>Number of Repeats</label>
                            <input type="number" name="repeat_count" id="repeat_count" class="form-control" min="0"
                                value="0">
                            <small class="form-text text-muted">If value is 0 Task not Repeat</small>
                        </div>
                    </div>
            </div>
            <div class="modal-footer">
                <input id="id" name="id" type="hidden">
                <button type="button" class="btn btn-danger light" data-bs-dismiss="modal">Close</button>
                <button type="submit" name="BtnSubmit" class="btn btn-primary btnname">Save changes</button>
            </div>
            </form>
        </div>
    </div>
</div>

<style>
    #followupModal .modal-content {
        border: 0;
        border-radius: 24px;
        overflow: hidden;
        background: #f8fafc;
        font-family: 'Inter', sans-serif;
    }

    #followupModal .modal-header {
        background: linear-gradient(135deg, #F8857D, #ff9e97);
        padding: 22px 28px;
        border: 0;
    }

    #followupModal .modal-title {
        font-size: 24px;
        font-weight: 700;
        letter-spacing: .3px;
    }

    .followup-card {
        border: 0;
        border-radius: 20px;
        overflow: hidden;
        transition: all .25s ease;
        background: #fff;
        box-shadow: 0 8px 25px rgba(0, 0, 0, .05);
    }

    .followup-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 14px 35px rgba(248, 133, 125, .18);
    }

    .followup-top {
        padding: 18px 20px;
        border-bottom: 1px solid #f1f1f1;
        background: #fff;
    }

    .customer-name {
        font-size: 18px;
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 4px;
    }

    .followup-body {
        padding: 20px;
    }

    .info-label {
        font-size: 12px;
        font-weight: 600;
        color: #94a3b8;
        text-transform: uppercase;
        letter-spacing: .4px;
        margin-bottom: 3px;
    }

    .info-value {
        font-size: 15px;
        font-weight: 600;
        color: #334155;
    }

    .followup-info {
        padding: 12px;
        border-radius: 14px;
        background: #f8fafc;
        height: 100%;
    }

    .remark-box {
        background: #fff5f5;
        border-left: 4px solid #F8857D;
        border-radius: 12px;
        padding: 14px;
        color: #475569;
        font-size: 14px;
        line-height: 1.6;
    }

    .priority-badge {
        background: #F8857D;
        color: #fff;
        padding: 6px 12px;
        border-radius: 30px;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: .5px;
    }

    .empty-followup {
        text-align: center;
        padding: 70px 20px;
    }

    .empty-followup h4 {
        font-weight: 700;
        color: #334155;
        margin-bottom: 10px;
    }

    .empty-followup p {
        color: #94a3b8;
        margin: 0;
    }

    .btn-close {
        filter: brightness(0) invert(1);
        opacity: 1;
    }

    @media(max-width:768px) {
        .modal-dialog {
            margin: 12px;
        }

        #followupModal .modal-title {
            font-size: 20px;
        }
    }
</style>

<?php if ($showFollowupPopup == true): ?>

    <?php

    $today = date('Y-m-d');

    $stmtPopup = $mysqli->prepare("
SELECT 
    a.*,
    b.name as customer_name,
    d.name as destination_name

FROM query_mst a

LEFT JOIN customers b 
ON a.customer_id = b.id

LEFT JOIN destinations d
ON a.destination = d.name

WHERE a.status='Follow Up'
AND a.call_date = ?
AND FIND_IN_SET(?, a.user_id)

ORDER BY a.pinned DESC, a.id DESC
");

    $stmtPopup->bind_param("si", $today, $_SESSION['id']);
    $stmtPopup->execute();

    $resPopup = $stmtPopup->get_result();

    $totalCards = $resPopup->num_rows;

    $modalClass = "modal-xl";

    if ($totalCards == 1) {
        $modalClass = "modal-md";
    } elseif ($totalCards <= 3) {
        $modalClass = "modal-lg";
    }

    ?>

    <div class="modal fade"
        id="followupModal"
        tabindex="-1"
        aria-hidden="true">

        <div class="modal-dialog <?= $modalClass ?> modal-dialog-centered">

            <div class="modal-content border-0 rounded-4 shadow-lg overflow-hidden">

                <!-- Header -->
                <div class="modal-header border-0 py-3 px-4"
                    style="background:#F8857D;">

                    <div>

                        <h4 class="text-white fw-bold mb-0">
                            Today's Follow Ups
                        </h4>

                        <small class="text-white-50">
                            <?= $totalCards ?> Pending Follow Ups
                        </small>

                    </div>

                    <button type="button"
                        class="btn-close btn-close-white"
                        data-bs-dismiss="modal"></button>

                </div>

                <!-- Body -->
                <div class="modal-body bg-light p-3">

                    <div class="row g-3">

                        <?php if ($totalCards > 0): ?>

                            <?php while ($row = $resPopup->fetch_assoc()): ?>

                                <div class="<?= $totalCards == 1 ? 'col-12' : 'col-lg-6' ?>">

                                    <div class="card border-0 shadow-sm rounded-4 h-100">

                                        <div class="card-body p-3">

                                            <!-- Top -->
                                            <div class="d-flex justify-content-between align-items-start mb-3">

                                                <div>

                                                    <h5 class="fw-bold text-dark mb-1"
                                                        style="font-size:18px;">

                                                        <?= $row['customer_name'] ?>

                                                    </h5>

                                                    <span class="badge rounded-pill bg-light border text-dark px-3 py-2"
                                                        style="font-size:13px;">

                                                        <?= $row['destination_name'] ?>

                                                    </span>

                                                </div>

                                                <?php if ($row['pinned'] == 1): ?>

                                                    <span class="badge rounded-pill px-3 py-2"
                                                        style="background:#F8857D;font-size:12px;">

                                                        Priority

                                                    </span>

                                                <?php endif; ?>

                                            </div>

                                            <!-- Info -->
                                            <div class="row g-2">

                                                <div class="col-6">

                                                    <div class="bg-light border rounded-4 p-3">

                                                        <div class="text-black mb-1"
                                                            style="font-size:12px;">

                                                            Mobile

                                                        </div>

                                                        <div class="fw-semibold text-dark"
                                                            style="font-size:15px;">

                                                            <?= $row['mobile'] ?>

                                                        </div>

                                                    </div>

                                                </div>

                                                <div class="col-6">

                                                    <div class="bg-light border rounded-4 p-3">

                                                        <div class="text-black mb-1"
                                                            style="font-size:12px;">

                                                            Call Time

                                                        </div>

                                                        <div class="fw-semibold text-dark"
                                                            style="font-size:15px;">

                                                            <?= $row['call_time'] ?>

                                                        </div>

                                                    </div>

                                                </div>

                                            </div>

                                            <!-- Remarks -->
                                            <div class="mt-3 bg-white border rounded-4 p-3">

                                                <div class="fw-bold text-dark mb-2"
                                                    style="font-size:14px;">

                                                    Remarks

                                                </div>

                                                <div class="text-black"
                                                    style="font-size:14px; line-height:1.6;">

                                                    <?= strlen($row['remarks']) > 80
                                                        ? substr($row['remarks'], 0, 80) . '...'
                                                        : $row['remarks']; ?>

                                                </div>

                                            </div>

                                        </div>

                                    </div>

                                </div>

                            <?php endwhile; ?>

                        <?php else: ?>

                            <div class="col-12">

                                <div class="text-center py-5">

                                    <span class="badge rounded-pill px-4 py-2 mb-3"
                                        style="background:#F8857D;font-size:14px;">

                                        No Pending Follow Ups

                                    </span>

                                    <h4 class="fw-bold text-dark">
                                        You're all caught up
                                    </h4>

                                    <p class="text-black mb-0">
                                        No follow ups scheduled today.
                                    </p>

                                </div>

                            </div>

                        <?php endif; ?>

                    </div>

                </div>

            </div>

        </div>

    </div>

    <script>
        window.addEventListener('load', function() {

            sessionStorage.removeItem("followupShown");

            let modalElement = document.getElementById('followupModal');

            if (modalElement) {

                let followupModal = new bootstrap.Modal(modalElement);

                followupModal.show();

            }

        });
    </script>

<?php endif; ?>

<?php include "Layouts/Footer.php"  ?>

<script>
    $(document).on("click", ".travel_date", function() {
        var travel_date = $(this).data("travel_date");

        $.ajax({
            method: 'POST',
            url: 'ajax/get-travel-details.php',
            dataType: 'text',
            data: {
                travel_date: travel_date
            },
            beforeSend: function() {
                $('#wait').show();
            },
            success: function(data) {
                data = JSON.parse(data);

                var html = "";
                data.forEach(element => {
                    html += "<tr>";
                    html += "<td>" + element.id + "</td>";
                    html += "<td>" + element.customer_name + "</td>";
                    html += "<td>" + (element.destinations_name ? element.destinations_name :
                        "") + "</td>";

                    html += "<td>" + element.mobile + "</td>";
                    html += "<td>" + element.from_date + "</td>";
                    html += "<td>" + element.service + "</td>";
                    html += "<td>" + element.sale_amount + "</td>";
                    html += "<td>" + element.payment_status + "</td>";
                    html += "</tr>";
                });

                $("#EventTable").html(html);
                $("#Event_Details_Modal").modal("show");
            },
            complete: function() {
                $('#wait').hide();
            }
        });

    })
    $(document).on("click", ".view", function() {

        var id = $(this).data("id");
        $.ajax({
            method: 'POST',
            url: 'ajax/get-travel-view.php',
            dataType: 'text',
            data: {
                id: id
            },
            beforeSend: function(data) {
                $('#wait').show();
            },
            success: function(data) {
                data = JSON.parse(data);



                $("#EventDataTable").html("<tr><td>" + data.income.total_income + "</td><td>" + data
                    .expense.total_expense + "</td><td>" + data.expense.vendor_expense +
                    "</td><td>" + data.expense.labour + "</td><td>" + data.expense.tada +
                    "</td><td>" + data.expense.mis + "</td><td>" + (data.income.total_income - data
                        .expense.total_expense) + "</td></tr>");
                $("#Event_Details_Modal").modal("show")

            },
            complete: function(data) {
                $('#wait').hide();
            }
        });
    });


    $('.bd-example-modal-lg').on('shown.bs.modal', function() {
        $('#multiple_user').select2({
            dropdownParent: $('.bd-example-modal-lg')
        });
    });
    $(document).on("click", ".editTask", function() {
        $("#id").val($(this).data("id"));
        $("#title").val($(this).data("title"));
        $("#due_date").val($(this).data("due_date"));
        $("select[name='priority']").val($(this).data("priority"));
        $("select[name='status']").val($(this).data("status"));
        $("textarea[name='descriptions']").val($(this).data("description"));
        $("#remark").val($(this).data("remark"));
        $("#repeat_interval").val($(this).data("repeat_interval"));
        $("#repeat_count").val($(this).data("repeat_count"));
        $("#select_users").hide();
        $(".bd-example-modal-lg").modal("show");
    });

    $(document).on("click", ".addTask", function() {
        $("#companyform").find("input[type=text], input[type=hidden], input[type=file], textarea, select").val('');
        $(".bd-example-modal-lg").modal("show");
    });
</script>