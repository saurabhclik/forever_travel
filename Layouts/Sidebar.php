<div class="deznav">
    <div class="deznav-scroll">
        <!-- <div class=" dropdown header-bx"> 
			<a class="nav-link header-profile2 position-relative" href="index">
				<div class="header-img position-relative">
					<img src="images/header-img/pic-1.jpg" alt="header-img">
					<svg class="header-circle" width="130" height="130" viewBox="0 0 130 130" fill="none" xmlns="http://www.w3.org/2000/svg">
					<path d="M130 65C130 100.899 100.899 130 65 130C29.1015 130 0 100.899 0 65C0 29.1015 29.1015 0 65 0C100.899 0 130 29.1015 130 65ZM4.99306 65C4.99306 98.1409 31.8591 125.007 65 125.007C98.1409 125.007 125.007 98.1409 125.007 65C125.007 31.8591 98.1409 4.99306 65 4.99306C31.8591 4.99306 4.99306 31.8591 4.99306 65Z" fill="#FFD482"/>
					<path d="M65 2.49653C65 1.11774 66.1182 -0.00500592 67.496 0.0479365C76.3746 0.389105 85.0984 2.54751 93.1247 6.39966C101.902 10.6123 109.621 16.7428 115.711 24.3385C121.802 31.9341 126.108 40.8009 128.312 50.284C130.516 59.7671 130.562 69.6242 128.446 79.1274C126.33 88.6305 122.106 97.5369 116.087 105.189C110.067 112.841 102.406 119.043 93.6677 123.337C84.9299 127.631 75.3391 129.907 65.6037 129.997C56.7012 130.08 47.8858 128.333 39.7012 124.875C38.4312 124.338 37.895 122.847 38.48 121.598C39.065 120.35 40.5495 119.817 41.8213 120.35C49.3273 123.493 57.4027 125.08 65.5573 125.004C74.5449 124.921 83.399 122.819 91.4656 118.855C99.5322 114.891 106.605 109.166 112.162 102.102C117.72 95.0375 121.619 86.8153 123.572 78.0421C125.526 69.269 125.484 60.1691 123.449 51.4145C121.414 42.6598 117.438 34.4741 111.816 27.4619C106.193 20.4497 99.0674 14.7901 90.9643 10.9011C83.6123 7.3726 75.6263 5.38343 67.4958 5.04499C66.1182 4.98764 65 3.87533 65 2.49653Z" fill="var(--primary)"/>
					</svg> 
					<div class="header-edit position-absolute">
						<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
						<path d="M3.02526 12.5567L7.44727 16.9762L16.2481 8.17043L11.8261 3.75092L3.02526 12.5567Z" fill="#fff"/>
						<path d="M19.6341 3.01762L16.9827 0.366211C16.7401 0.123594 16.4227 0.00160156 16.1051 0H16.0919C15.7743 0.00160156 15.4573 0.123594 15.2153 0.366211L13.4453 2.13383L17.8665 6.55262L19.6342 4.785C19.8768 4.54238 19.9988 4.22539 20.0004 3.90781V3.89461C19.9987 3.57719 19.8767 3.2602 19.6341 3.01762Z" fill="#fff"/>
						<path d="M0 20L5.745 18.6738L1.32379 14.255L0 20Z" fill="#fff"/>
						</svg>
					</div>
				</div>
				<div class="header-content">
					<h2 class="font-w500">
						<?= isset($_SESSION['username']) && !empty($_SESSION['username']) ? $_SESSION['username'] : ''; ?>
					</h2>
					<span class="font-w400">
						<?=  isset($_SESSION['email']) && !empty($_SESSION['email']) ? $_SESSION['email'] : ''; ?>
					</span>
				</div>
			</a> 
		</div>
		 -->
        <ul class="metismenu" id="menu">
            <li>
                <a href="index.php" aria-expanded="false">
                    <i class="fa fa-user"></i>
                    <span class="nav-text">Dashboard</span>
                </a>
            </li>
            <?php if($_SESSION['user'] == 'admin') { ?>

            <li>
                <a class="has-arrow" href="javascript:void()" aria-expanded="false">
                    <i class="flaticon-025-dashboard"></i>
                    <span class="nav-text">Master</span>
                </a>
                <ul aria-expanded="false">
                    <li><a href="company.php">Company</a></li>
                    <!-- <li><a href="category">Category</a></li> -->
                    <!-- <li><a href="sub_category">Sub Category</a></li> -->
                    <!-- <li><a href="gst">Gst</a></li> -->
                    <!-- new dropdown make for masters -->
                    <li>
                        <a class="has-arrow" href="javascript:void()" aria-expanded="false">Query Master</a>
                        <ul aria-expanded="false">
                            <li><a href="status.php">Status</a></li>
                            <li><a href="source.php">Source</a></li>
                            <li><a href="priority.php">Priority</a></li>
                            <li><a href="service.php">Service</a></li>
                            <li><a href="destination.php">Destination</a></li>
                        </ul>
                    </li>
                    <li>
                        <a class="has-arrow" href="javascript:void()" aria-expanded="false">Quote Master</a>
                        <ul aria-expanded="false">
                            <li><a href="hotels.php">Hotels</a></li>
                            <li><a href="country_landmarks.php">Country Landmark</a></li>
                            <li><a href="inclusions.php">Inclusions</a></li>
                            <li><a href="exclusions.php">Exclusions</a></li>
                            <li><a href="imp_notes.php">Important Notes</a></li>
                            <li><a href="destination_images.php">Destination Image</a></li>
                        </ul>
                    </li>
                    <li>
                        <a class="has-arrow" href="javascript:void()" aria-expanded="false">Expense Master</a>
                        <ul aria-expanded="false">
                            <li><a href="expense_category.php">Expense Category</a></li>
                            <li><a href="expense_subcategory.php">Expense Sub Category</a></li>
                            <li><a href="vendors.php">Vendor</a></li>
                        </ul>
                    </li>
                    <!-- End New Master Dropdown -->

                </ul>
            </li>

            <li>
                <a class="has-arrow" href="javascript:void()" aria-expanded="false">
                    <i class="fa-solid fa-users"></i>
                    <span class="nav-text">User Management</span>
                </a>

                <ul aria-expanded="false">
                    <li><a href="team.php">Users</a></li>
                    <li><a href="Company-hierarchy.php">Company Hierarchy</a></li>
                    <!-- <li>
                        <a class="has-arrow" href="javascript:void()" aria-expanded="false">Role & Permissions</a>
                        <ul aria-expanded="false">
                            <li><a href="role">Role</a></li>
                            <li><a href="permission">Permission</a></li>
                            <li><a href="assign_permission">Assign Permission</a></li>
                        </ul>
                    </li> -->
                    
                    <!-- End New Master Dropdown -->

                </ul>
            </li>

            <!-- <li>
                <a href="team" aria-expanded="false">
                    <i class="fa fa-user"></i>
                    <span class="nav-text">User Management</span>
                </a>
            </li> -->
            <?php } ?>

            <li>
                <a href="customers.php" aria-expanded="false">
                    <i class="fa fa-user"></i>
                    <span class="nav-text">Customer</span>
                </a>
            </li>
            <li>
                <a href="query.php?status=New Query" aria-expanded="false">
                    <i class="flaticon-041-graph"></i>
                    <span class="nav-text">Query</span>
                </a>
            </li>
            <!-- <li>
				<a href="sale" aria-expanded="false">
					<i class="flaticon-086-star"></i>
					<span class="nav-text">Sale</span>
				</a>
			</li> -->
            <li>
                <a href="task.php" aria-expanded="false">
                    <i class="flaticon-043-menu"></i>
                    <span class="nav-text">Task</span>
                </a>
            </li>
            <!-- <li>
				<a href="expenses" aria-expanded="false">
					<i class="flaticon-045-heart"></i>
					<span class="nav-text">Expenses</span>
				</a>
			</li> -->
            <li>
                <a href="income_expense_summary.php" aria-expanded="false">
                    <i class="flaticon-045-heart"></i>
                    <span class="nav-text">Income | Expense Summary</span>
                </a>
            </li>
            <li>
                <a href="wallet-ledger.php" class="" aria-expanded="false">
                    <i class="flaticon-013-checkmark"></i>
                    <span class="nav-text">Wallet Ledger</span>
                </a>
            </li>
            <li>
                <a class="has-arrow " href="javascript:void()" aria-expanded="false">
                    <i class="flaticon-072-printer"></i>
                    <span class="nav-text">Reports</span>
                </a>
                <ul aria-expanded="false">
                    <!-- <li>
                        <a href="category-report">Category Report</a>
                    </li> -->
                    <!-- <li>
                        <a href="sub-category-report">Sub Category Report</a>
                    </li> -->
                    <li>
                        <a href="customer-report.php">Customer Report</a>
                    </li>
                    <!-- <li>
                        <a href="expense-report">Expense Report</a>
                    </li> -->
                    <li>
                        <a href="payment-ledger.php">Payment Ledger</a>
                    </li>
                    <!-- <li>
                        <a href="vendor-report">Vendor Report</a>
                    </li> -->
                    <!-- <li>
                        <a href="smart-report">Smart Report</a>
                    </li> -->
                  
                    <li>
                        <a href="employee-performance-report.php">Employee Performance Report</a>
                    </li>
                    <?php if($_SESSION['user'] == "admin"): ?>
                    <li>
                        <a href="dsr-reports.php">DSR Report</a>
                    </li>
                    <li>
                        <a href="destination_report.php">Destination Report</a>
                    </li>
                    <li>
                        <a href="target-reports.php">Target Report</a>
                    </li>
                    <li>
                        <a href="source-wise-report.php">Source Wise Report</a>
                    </li>
                    <li>
                        <a href="transaction-reports.php">Transaction Reports</a>
                    </li>
                        <?php endif; ?>
                </ul>
            </li>
            <li>
                <a href="settings.php" aria-expanded="false">
                    <i class="fa fa-gear"></i>
                    <span class="nav-text">Settings</span>
                </a>
            </li>
            <li>
                <a href="logout.php" aria-expanded="false">
               <i class="fa-solid fa-right-from-bracket"></i>
                    <span class="nav-text">Logout</span>
                </a>
            </li>

        </ul>

    </div>
</div>