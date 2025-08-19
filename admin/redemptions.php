<?php
// Check if session is already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

// Generate CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrfToken = $_SESSION['csrf_token'];
$adminUsername = $_SESSION['username'] ?? 'Admin';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Redemptions Dashboard - Admin Panel</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --primary: #4a90e2;
            --secondary: #357abd;
            --accent: #ff6b6b;
            --dark: #1a202c;
            --light: #f7fafc;
            --success: #48bb78;
            --warning: #ecc94b;
            --danger: #e53e3e;
            --gray: #718096;
            --gray-light: #e2e8f0;
            --sidebar-width: 260px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-color: #f5f7fa;
            color: var(--dark);
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar Styles */
        .sidebar {
            width: var(--sidebar-width);
            background: var(--dark);
            color: white;
            height: 100vh;
            position: fixed;
            padding: 20px 0;
            z-index: 100;
        }

        .logo {
            padding: 0 20px 20px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            margin-bottom: 20px;
        }

        .logo h1 {
            font-size: 24px;
            font-weight: 700;
            display: flex;
            align-items: center;
        }

        .logo i {
            margin-right: 10px;
            color: var(--primary);
        }

        .nav-links {
            padding: 0 15px;
        }

        .nav-links li {
            list-style: none;
            margin-bottom: 5px;
        }

        .nav-links a {
            color: #cbd5e0;
            text-decoration: none;
            padding: 12px 15px;
            border-radius: 6px;
            display: flex;
            align-items: center;
            transition: all 0.3s;
        }

        .nav-links a:hover, .nav-links a.active {
            background: rgba(74, 144, 226, 0.2);
            color: white;
        }

        .nav-links i {
            margin-right: 12px;
            font-size: 18px;
            width: 24px;
            text-align: center;
        }

        /* Main Content */
        .main-content {
            flex: 1;
            margin-left: var(--sidebar-width);
            padding: 20px;
        }

        /* Header */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: white;
            padding: 15px 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 25px;
        }

        .header-title h1 {
            font-size: 24px;
            font-weight: 600;
        }

        .header-title p {
            color: var(--gray);
            font-size: 14px;
        }

        .user-profile {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .user-profile img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
        }

        .user-info h4 {
            font-size: 15px;
            font-weight: 600;
        }

        .user-info p {
            font-size: 13px;
            color: var(--gray);
        }

        /* Stats Cards */
        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            display: flex;
            align-items: center;
        }

        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            font-size: 24px;
        }

        .stat-info h3 {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 5px;
        }

        .stat-info p {
            color: var(--gray);
            font-size: 14px;
        }

        .bg-blue { background: rgba(74, 144, 226, 0.1); color: var(--primary); }
        .bg-green { background: rgba(72, 187, 120, 0.1); color: var(--success); }
        .bg-orange { background: rgba(236, 201, 75, 0.1); color: var(--warning); }
        .bg-red { background: rgba(229, 62, 62, 0.1); color: var(--danger); }
        .bg-purple { background: rgba(159, 122, 234, 0.1); color: #9f7aea; }

        /* Charts Container */
        .charts-container {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 20px;
            margin-bottom: 30px;
        }

        .chart-card {
            background: white;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        .chart-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .chart-title h3 {
            font-size: 18px;
            font-weight: 600;
        }

        .chart-title p {
            color: var(--gray);
            font-size: 14px;
        }

        .chart-wrapper {
            height: 250px;
            position: relative;
        }

        /* Redemptions Container */
        .redemptions-container {
            background: white;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .section-title h2 {
            font-size: 20px;
            font-weight: 600;
        }

        .controls {
            display: flex;
            gap: 15px;
        }

        .btn {
            padding: 8px 15px;
            border-radius: 6px;
            font-weight: 500;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s;
            border: none;
        }

        .btn-primary { background: var(--primary); color: white; }
        .btn-success { background: var(--success); color: white; }
        .btn-outline { background: transparent; border: 1px solid var(--gray-light); color: var(--gray); }

        .btn:hover {
            opacity: 0.9;
            transform: translateY(-1px);
        }

        /* Filters */
        .filter-controls {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }

        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .filter-group label {
            font-size: 14px;
            color: var(--gray);
            font-weight: 500;
        }

        .filter-group select, .filter-group input {
            padding: 8px 12px;
            border-radius: 6px;
            border: 1px solid var(--gray-light);
            background: white;
            min-width: 150px;
        }

        /* Table Styles */
        .table-container {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead {
            background: #f8fafc;
            border-bottom: 2px solid var(--gray-light);
        }

        th, td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid var(--gray-light);
        }

        th {
            font-weight: 600;
            color: var(--gray);
            font-size: 14px;
        }

        .user-cell {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--primary);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
        }

        .reward-info {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .reward-icon {
            width: 40px;
            height: 40px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            background: var(--gray-light);
        }

        .status {
            display: inline-flex;
            align-items: center;
            padding: 5px 12px;
            border-radius: 50px;
            font-size: 13px;
            font-weight: 500;
        }

        .status-completed { background: rgba(72, 187, 120, 0.1); color: var(--success); }
        .status-processing { background: rgba(236, 201, 75, 0.1); color: var(--warning); }
        .status-failed { background: rgba(229, 62, 62, 0.1); color: var(--danger); }

        .points {
            font-weight: 600;
            color: var(--primary);
        }

        .action-btn {
            width: 32px;
            height: 32px;
            border-radius: 6px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            border: none;
            background: transparent;
            color: var(--gray);
            margin-right: 5px;
        }

        .action-btn:hover {
            background: var(--gray-light);
            color: var(--dark);
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }

        .modal-content {
            background: white;
            border-radius: 10px;
            width: 90%;
            max-width: 600px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }

        .modal-header {
            padding: 20px;
            background: var(--primary);
            color: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-radius: 10px 10px 0 0;
        }

        .modal-close {
            background: none;
            border: none;
            color: white;
            font-size: 24px;
            cursor: pointer;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
        }

        .modal-body {
            padding: 20px;
            max-height: 60vh;
            overflow-y: auto;
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid var(--gray-light);
        }

        .detail-row:last-child {
            border-bottom: none;
        }

        .detail-label {
            font-weight: 600;
            color: var(--gray);
        }

        .modal-footer {
            padding: 15px 20px;
            background: #f8fafc;
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            border-radius: 0 0 10px 10px;
        }

        /* Pagination */
        .pagination {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 25px;
            padding-top: 20px;
            border-top: 1px solid var(--gray-light);
        }

        .page-info {
            color: var(--gray);
            font-size: 14px;
        }

        .page-controls {
            display: flex;
            gap: 8px;
        }

        .page-btn {
            width: 36px;
            height: 36px;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: white;
            border: 1px solid var(--gray-light);
            cursor: pointer;
            transition: all 0.3s;
        }

        .page-btn:hover, .page-btn.active {
            background: var(--primary);
            color: white;
        }

        /* Popular Rewards */
        .popular-rewards {
            margin-top: 20px;
        }

        .reward-item {
            display: flex;
            align-items: center;
            padding: 15px;
            border-bottom: 1px solid var(--gray-light);
        }

        .reward-item:last-child {
            border-bottom: none;
        }

        .reward-details {
            flex: 1;
            margin-left: 15px;
        }

        .reward-name {
            font-weight: 600;
            margin-bottom: 5px;
        }

        .reward-stats {
            font-size: 13px;
            color: var(--gray);
        }

        .reward-count {
            font-weight: 600;
            color: var(--primary);
        }

        /* Responsive */
        @media (max-width: 1200px) {
            .charts-container {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            .stats-container {
                grid-template-columns: 1fr 1fr;
            }
            
            .filter-controls {
                flex-direction: column;
            }
            
            .section-header {
                flex-direction: column;
                gap: 15px;
                align-items: flex-start;
            }
            
            .charts-container {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 576px) {
            .stats-container {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="logo">
            <h1><i class="fas fa-play-circle"></i> <span>VideoDash</span></h1>
        </div>
        <ul class="nav-links">
            <li><a href="index.php"><i class="fas fa-home"></i> <span>Dashboard</span></a></li>
            <li><a href="add_video.php"><i class="fas fa-video"></i> <span>Videos</span></a></li>
            <li><a href="users.php"><i class="fas fa-users"></i> <span>Users</span></a></li>
            <li><a href="new-users.php"><i class="fas fa-user-plus"></i> <span>New Users</span></a></li>
            <li><a href="analytics.php"><i class="fas fa-chart-line"></i> <span>Analytics</span></a></li>
            <li><a href="redemptions.php" class="active"><i class="fas fa-gift"></i> <span>Redemptions</span></a></li>
            <li><a href="tasks.php"><i class="fas fa-tasks"></i> <span>Tasks</span></a></li>
            <li><a href="rewards.php"><i class="fas fa-coins"></i> <span>Rewards</span></a></li>
            <li><a href="withdrawal_requests.php"><i class="fas fa-money-bill-wave"></i> <span>Withdrawals</span></a></li>
            <li><a href="setting.php"><i class="fas fa-cog"></i> <span>Settings</span></a></li>
            <li><a href="security.php"><i class="fas fa-lock"></i> <span>Security</span></a></li>
            <li><a href="alerts.php"><i class="fas fa-exclamation-triangle"></i> <span>Alerts</span></a></li>
            <li><a href="database.php"><i class="fas fa-database"></i> <span>Database</span></a></li>
            <li><a href="integration.php"><i class="fas fa-plug"></i> <span>Integrations</span></a></li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Header -->
        <div class="header">
            <div class="header-title">
                <h1>Redemptions Dashboard</h1>
                <p>Monitor reward redemptions and user activity</p>
            </div>
            <div class="user-profile">
                <img src="https://randomuser.me/api/portraits/men/41.jpg" alt="Admin">
                <div class="user-info">
                    <h4><?= htmlspecialchars($adminUsername) ?></h4>
                    <p>Admin</p>
                </div>
            </div>
        </div>

        <!-- Stats Section -->
        <div class="stats-container">
            <div class="stat-card">
                <div class="stat-icon bg-blue">
                    <i class="fas fa-gift"></i>
                </div>
                <div class="stat-info">
                    <h3 id="totalRedemptions">1,247</h3>
                    <p>Total Redemptions</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon bg-green">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-info">
                    <h3 id="completedRedemptions">1,205</h3>
                    <p>Completed</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon bg-orange">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-info">
                    <h3 id="processingRedemptions">35</h3>
                    <p>Processing</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon bg-purple">
                    <i class="fas fa-coins"></i>
                </div>
                <div class="stat-info">
                    <h3 id="pointsRedeemed">2.4M</h3>
                    <p>Points Redeemed</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon bg-red">
                    <i class="fas fa-times-circle"></i>
                </div>
                <div class="stat-info">
                    <h3 id="failedRedemptions">7</h3>
                    <p>Failed</p>
                </div>
            </div>
        </div>

        <!-- Charts Section -->
        <div class="charts-container">
            <!-- Redemptions Over Time Chart -->
            <div class="chart-card">
                <div class="chart-header">
                    <div class="chart-title">
                        <h3>Redemptions Over Time</h3>
                        <p>Daily redemption trends</p>
                    </div>
                </div>
                <div class="chart-wrapper">
                    <canvas id="redemptionsChart"></canvas>
                </div>
            </div>
            
            <!-- Popular Rewards -->
            <div class="chart-card">
                <div class="chart-header">
                    <div class="chart-title">
                        <h3>Popular Rewards</h3>
                        <p>Most redeemed items</p>
                    </div>
                </div>
                <div class="popular-rewards">
                    <div class="reward-item">
                        <div class="reward-icon bg-blue">
                            <i class="fab fa-amazon"></i>
                        </div>
                        <div class="reward-details">
                            <div class="reward-name">Amazon Gift Card</div>
                            <div class="reward-stats">$25 • 500 points</div>
                        </div>
                        <div class="reward-count">342</div>
                    </div>
                    <div class="reward-item">
                        <div class="reward-icon bg-green">
                            <i class="fab fa-paypal"></i>
                        </div>
                        <div class="reward-details">
                            <div class="reward-name">PayPal Cash</div>
                            <div class="reward-stats">$10 • 200 points</div>
                        </div>
                        <div class="reward-count">298</div>
                    </div>
                    <div class="reward-item">
                        <div class="reward-icon bg-orange">
                            <i class="fas fa-mobile-alt"></i>
                        </div>
                        <div class="reward-details">
                            <div class="reward-name">Mobile Credits</div>
                            <div class="reward-stats">$5 • 100 points</div>
                        </div>
                        <div class="reward-count">156</div>
                    </div>
                    <div class="reward-item">
                        <div class="reward-icon bg-purple">
                            <i class="fab fa-google-play"></i>
                        </div>
                        <div class="reward-details">
                            <div class="reward-name">Google Play Card</div>
                            <div class="reward-stats">$15 • 300 points</div>
                        </div>
                        <div class="reward-count">87</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Redemptions Table Section -->
        <div class="redemptions-container">
            <div class="section-header">
                <div class="section-title">
                    <h2>Recent Redemptions</h2>
                    <p>Manage and track all reward redemptions</p>
                </div>
                <div class="controls">
                    <button class="btn btn-primary" id="addRewardBtn">
                        <i class="fas fa-plus"></i> Add Reward
                    </button>
                    <button class="btn btn-outline" id="exportBtn">
                        <i class="fas fa-download"></i> Export
                    </button>
                </div>
            </div>

            <!-- Filter Controls -->
            <div class="filter-controls">
                <div class="filter-group">
                    <label>Status</label>
                    <select id="statusFilter">
                        <option value="">All Status</option>
                        <option value="completed">Completed</option>
                        <option value="processing">Processing</option>
                        <option value="failed">Failed</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label>Reward Type</label>
                    <select id="rewardFilter">
                        <option value="">All Rewards</option>
                        <option value="gift-card">Gift Cards</option>
                        <option value="cash">Cash</option>
                        <option value="credits">Credits</option>
                        <option value="physical">Physical Items</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label>Date Range</label>
                    <select id="dateFilter">
                        <option value="">All Time</option>
                        <option value="today">Today</option>
                        <option value="week">This Week</option>
                        <option value="month">This Month</option>
                    </select>
                </div>
            </div>

            <!-- Redemptions Table -->
            <div class="table-container">
                <table id="redemptionsTable">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Reward</th>
                            <th>Points Used</th>
                            <th>Value</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="redemptionsTableBody">
                        <!-- Redemptions will be populated dynamically -->
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="pagination">
                <div class="page-info">
                    Showing <span id="startRange">1</span> to <span id="endRange">10</span> of <span id="totalRedemptions">125</span> redemptions
                </div>
                <div class="page-controls" id="pageControls">
                    <!-- Pagination will be generated dynamically -->
                </div>
            </div>
        </div>
    </div>

    <!-- Redemption Details Modal -->
    <div class="modal" id="redemptionModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Redemption Details</h3>
                <button class="modal-close" id="closeModal">&times;</button>
            </div>
            <div class="modal-body" id="modalBody">
                <!-- Redemption details will be populated dynamically -->
            </div>
            <div class="modal-footer">
                <button class="btn btn-outline" id="cancelBtn">Close</button>
            </div>
        </div>
    </div>

    <script>
        // Sample redemptions data
        let redemptions = [
            {
                id: 'RD001',
                userId: 'U001',
                userName: 'John Doe',
                userEmail: 'john.doe@example.com',
                rewardId: 'RW001',
                rewardName: 'Amazon Gift Card',
                rewardType: 'gift-card',
                rewardIcon: 'fab fa-amazon',
                pointsUsed: 500,
                value: 25.00,
                currency: 'USD',
                redemptionDate: '2024-01-15T10:30:00',
                status: 'completed',
                deliveryMethod: 'Email',
                deliveryDetails: 'john.doe@example.com',
                trackingInfo: 'AMZ-GC-001234',
                completedDate: '2024-01-15T10:45:00'
            },
            {
                id: 'RD002',
                userId: 'U002',
                userName: 'Jane Smith',
                userEmail: 'jane.smith@example.com',
                rewardId: 'RW002',
                rewardName: 'PayPal Cash',
                rewardType: 'cash',
                rewardIcon: 'fab fa-paypal',
                pointsUsed: 200,
                value: 10.00,
                currency: 'USD',
                redemptionDate: '2024-01-15T09:15:00',
                status: 'processing',
                deliveryMethod: 'PayPal Transfer',
                deliveryDetails: 'jane.smith@paypal.com',
                estimatedDelivery: '2024-01-16'
            },
            {
                id: 'RD003',
                userId: 'U003',
                userName: 'Mike Johnson',
                userEmail: 'mike.j@example.com',
                rewardId: 'RW003',
                rewardName: 'Mobile Credits',
                rewardType: 'credits',
                rewardIcon: 'fas fa-mobile-alt',
                pointsUsed: 100,
                value: 5.00,
                currency: 'USD',
                redemptionDate: '2024-01-14T16:45:00',
                status: 'completed',
                deliveryMethod: 'Direct Credit',
                deliveryDetails: '+1234567890',
                trackingInfo: 'MC-CR-005678',
                completedDate: '2024-01-14T17:00:00'
            },
            {
                id: 'RD004',
                userId: 'U004',
                userName: 'Sarah Williams',
                userEmail: 'sarah.w@example.com',
                rewardId: 'RW004',
                rewardName: 'Google Play Card',
                rewardType: 'gift-card',
                rewardIcon: 'fab fa-google-play',
                pointsUsed: 300,
                value: 15.00,
                currency: 'USD',
                redemptionDate: '2024-01-14T14:20:00',
                status: 'failed',
                deliveryMethod: 'Email',
                deliveryDetails: 'sarah.w@example.com',
                failureReason: 'Invalid email address',
                failedDate: '2024-01-14T15:00:00'
            },
            {
                id: 'RD005',
                userId: 'U005',
                userName: 'David Brown',
                userEmail: 'david.b@example.com',
                rewardId: 'RW001',
                rewardName: 'Amazon Gift Card',
                rewardType: 'gift-card',
                rewardIcon: 'fab fa-amazon',
                pointsUsed: 1000,
                value: 50.00,
                currency: 'USD',
                redemptionDate: '2024-01-13T11:10:00',
                status: 'completed',
                deliveryMethod: 'Email',
                deliveryDetails: 'david.b@example.com',
                trackingInfo: 'AMZ-GC-009876',
                completedDate: '2024-01-13T11:30:00'
            }
        ];

        // DOM Elements
        const redemptionsTableBody = document.getElementById('redemptionsTableBody');
        const redemptionModal = document.getElementById('redemptionModal');
        const closeModal = document.getElementById('closeModal');
        const statusFilter = document.getElementById('statusFilter');
        const rewardFilter = document.getElementById('rewardFilter');
        const dateFilter = document.getElementById('dateFilter');
        const exportBtn = document.getElementById('exportBtn');

        let currentPage = 1;
        const itemsPerPage = 10;

        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            updateStats();
            initCharts();
            renderRedemptionsTable();
            updatePagination();

            // Event listeners
            closeModal.addEventListener('click', () => {
                redemptionModal.style.display = 'none';
            });

            document.getElementById('cancelBtn').addEventListener('click', () => {
                redemptionModal.style.display = 'none';
            });

            // Filters
            statusFilter.addEventListener('change', filterRedemptions);
            rewardFilter.addEventListener('change', filterRedemptions);
            dateFilter.addEventListener('change', filterRedemptions);

            // Export
            exportBtn.addEventListener('click', exportRedemptions);
        });

        function updateStats() {
            const completed = redemptions.filter(r => r.status === 'completed').length;
            const processing = redemptions.filter(r => r.status === 'processing').length;
            const failed = redemptions.filter(r => r.status === 'failed').length;
            const totalPoints = redemptions.reduce((sum, r) => sum + r.pointsUsed, 0);

            document.getElementById('totalRedemptions').textContent = redemptions.length.toLocaleString();
            document.getElementById('completedRedemptions').textContent = completed.toLocaleString();
            document.getElementById('processingRedemptions').textContent = processing.toLocaleString();
            document.getElementById('failedRedemptions').textContent = failed.toLocaleString();
            document.getElementById('pointsRedeemed').textContent = (totalPoints / 1000).toFixed(1) + 'K';
        }

        function initCharts() {
            // Redemptions Over Time Chart
            const redemptionsCtx = document.getElementById('redemptionsChart').getContext('2d');
            new Chart(redemptionsCtx, {
                type: 'line',
                data: {
                    labels: ['Jan 8', 'Jan 9', 'Jan 10', 'Jan 11', 'Jan 12', 'Jan 13', 'Jan 14', 'Jan 15'],
                    datasets: [{
                        label: 'Redemptions',
                        data: [25, 32, 28, 45, 38, 52, 48, 65],
                        backgroundColor: 'rgba(74, 144, 226, 0.2)',
                        borderColor: 'rgba(74, 144, 226, 1)',
                        borderWidth: 2,
                        tension: 0.3,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                drawBorder: false
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            }
                        }
                    }
                }
            });
        }

        function renderRedemptionsTable() {
            const filteredRedemptions = filterRedemptionsList();
            const startIndex = (currentPage - 1) * itemsPerPage;
            const endIndex = Math.min(startIndex + itemsPerPage, filteredRedemptions.length);
            const paginatedRedemptions = filteredRedemptions.slice(startIndex, endIndex);

            // Update page info
            document.getElementById('startRange').textContent = filteredRedemptions.length > 0 ? startIndex + 1 : 0;
            document.getElementById('endRange').textContent = endIndex;
            document.getElementById('totalRedemptions').textContent = filteredRedemptions.length;

            redemptionsTableBody.innerHTML = '';

            if (paginatedRedemptions.length === 0) {
                redemptionsTableBody.innerHTML = `
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 40px;">
                            No redemptions found
                        </td>
                    </tr>
                `;
                return;
            }

            paginatedRedemptions.forEach(redemption => {
                const row = document.createElement('tr');
                const userInitials = redemption.userName.split(' ').map(n => n[0]).join('');
                
                row.innerHTML = `
                    <td>
                        <div class="user-cell">
                            <div class="user-avatar">${userInitials}</div>
                            <div>
                                <div style="font-weight: 600;">${redemption.userName}</div>
                                <div style="color: var(--gray); font-size: 13px;">${redemption.userEmail}</div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <div class="reward-info">
                            <div class="reward-icon bg-${getRewardColor(redemption.rewardType)}">
                                <i class="${redemption.rewardIcon}"></i>
                            </div>
                            <div>
                                <div style="font-weight: 600;">${redemption.rewardName}</div>
                                <div style="color: var(--gray); font-size: 13px;">${capitalizeFirstLetter(redemption.rewardType.replace('-', ' '))}</div>
                            </div>
                        </div>
                    </td>
                    <td class="points">${redemption.pointsUsed.toLocaleString()}</td>
                    <td>$${redemption.value.toFixed(2)}</td>
                    <td>${formatDate(redemption.redemptionDate)}</td>
                    <td><span class="status status-${redemption.status}">${capitalizeFirstLetter(redemption.status)}</span></td>
                    <td>
                        <button class="action-btn" onclick="viewRedemption('${redemption.id}')" title="View Details">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button class="action-btn" onclick="downloadReceipt('${redemption.id}')" title="Download Receipt">
                            <i class="fas fa-download"></i>
                        </button>
                    </td>
                `;
                
                redemptionsTableBody.appendChild(row);
            });
        }

        function filterRedemptionsList() {
            const statusValue = statusFilter.value;
            const rewardValue = rewardFilter.value;
            const dateValue = dateFilter.value;

            return redemptions.filter(redemption => {
                // Status filter
                const matchesStatus = !statusValue || redemption.status === statusValue;

                // Reward type filter
                const matchesReward = !rewardValue || redemption.rewardType === rewardValue;

                // Date filter
                let matchesDate = true;
                if (dateValue) {
                    const redemptionDate = new Date(redemption.redemptionDate);
                    const now = new Date();

                    if (dateValue === 'today') {
                        const today = new Date();
                        today.setHours(0, 0, 0, 0);
                        matchesDate = redemptionDate >= today;
                    } else if (dateValue === 'week') {
                        const weekAgo = new Date();
                        weekAgo.setDate(weekAgo.getDate() - 7);
                        matchesDate = redemptionDate >= weekAgo;
                    } else if (dateValue === 'month') {
                        const monthAgo = new Date();
                        monthAgo.setMonth(monthAgo.getMonth() - 1);
                        matchesDate = redemptionDate >= monthAgo;
                    }
                }

                return matchesStatus && matchesReward && matchesDate;
            });
        }

        function filterRedemptions() {
            currentPage = 1;
            renderRedemptionsTable();
            updatePagination();
        }

        function updatePagination() {
            const filteredRedemptions = filterRedemptionsList();
            const totalPages = Math.ceil(filteredRedemptions.length / itemsPerPage);
            
            let paginationHTML = `
                <div class="page-btn" onclick="changePage(${Math.max(1, currentPage - 1)})">
                    <i class="fas fa-chevron-left"></i>
                </div>
            `;
            
            for (let i = 1; i <= Math.min(totalPages, 5); i++) {
                paginationHTML += `
                    <div class="page-btn ${i === currentPage ? 'active' : ''}" onclick="changePage(${i})">
                        ${i}
                    </div>
                `;
            }
            
            if (totalPages > 5) {
                paginationHTML += `
                    <div class="page-btn">...</div>
                    <div class="page-btn" onclick="changePage(${totalPages})">${totalPages}</div>
                `;
            }
            
            paginationHTML += `
                <div class="page-btn" onclick="changePage(${Math.min(totalPages, currentPage + 1)})">
                    <i class="fas fa-chevron-right"></i>
                </div>
            `;
            
            document.getElementById('pageControls').innerHTML = paginationHTML;
        }

        function changePage(page) {
            currentPage = page;
            renderRedemptionsTable();
            updatePagination();
        }

        function viewRedemption(redemptionId) {
            const redemption = redemptions.find(r => r.id === redemptionId);
            if (!redemption) return;

            const modalBody = document.getElementById('modalBody');
            modalBody.innerHTML = `
                <div class="detail-row">
                    <span class="detail-label">Redemption ID:</span>
                    <span>${redemption.id}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">User:</span>
                    <span>${redemption.userName} (${redemption.userEmail})</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Reward:</span>
                    <span>${redemption.rewardName}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Points Used:</span>
                    <span style="font-weight: 600; color: var(--primary);">${redemption.pointsUsed.toLocaleString()}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Value:</span>
                    <span>$${redemption.value.toFixed(2)} ${redemption.currency}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Redemption Date:</span>
                    <span>${formatDateTime(redemption.redemptionDate)}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Status:</span>
                    <span class="status status-${redemption.status}">${capitalizeFirstLetter(redemption.status)}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Delivery Method:</span>
                    <span>${redemption.deliveryMethod}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Delivery Details:</span>
                    <span>${redemption.deliveryDetails}</span>
                </div>
                ${redemption.trackingInfo ? `
                    <div class="detail-row">
                        <span class="detail-label">Tracking Info:</span>
                        <span>${redemption.trackingInfo}</span>
                    </div>
                ` : ''}
                ${redemption.completedDate ? `
                    <div class="detail-row">
                        <span class="detail-label">Completed Date:</span>
                        <span>${formatDateTime(redemption.completedDate)}</span>
                    </div>
                ` : ''}
                ${redemption.estimatedDelivery ? `
                    <div class="detail-row">
                        <span class="detail-label">Estimated Delivery:</span>
                        <span>${formatDate(redemption.estimatedDelivery)}</span>
                    </div>
                ` : ''}
                ${redemption.failureReason ? `
                    <div class="detail-row">
                        <span class="detail-label">Failure Reason:</span>
                        <span style="color: var(--danger);">${redemption.failureReason}</span>
                    </div>
                ` : ''}
            `;

            redemptionModal.style.display = 'flex';
        }

        function downloadReceipt(redemptionId) {
            // In a real application, this would generate and download a receipt
            alert('Receipt downloaded successfully!');
        }

        function exportRedemptions() {
            // In a real application, this would generate and download a CSV/Excel file
            alert('Redemptions data exported successfully!');
        }

        function getRewardColor(type) {
            const colors = {
                'gift-card': 'blue',
                'cash': 'green',
                'credits': 'orange',
                'physical': 'purple'
            };
            return colors[type] || 'gray';
        }

        // Helper functions
        function formatDate(dateString) {
            const options = { year: 'numeric', month: 'short', day: 'numeric' };
            return new Date(dateString).toLocaleDateString('en-US', options);
        }

        function formatDateTime(dateString) {
            const options = { 
                year: 'numeric', 
                month: 'short', 
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            };
            return new Date(dateString).toLocaleDateString('en-US', options);
        }

        function capitalizeFirstLetter(string) {
            return string.charAt(0).toUpperCase() + string.slice(1);
        }
    </script>
</body>
</html>
