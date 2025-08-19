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
    <title>Admin Dashboard - YouTube Video Management</title>
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
            --header-height: 70px;
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
            overflow-x: hidden;
        }

        /* Sidebar Styles */
        .sidebar {
            width: var(--sidebar-width);
            background: var(--dark);
            color: white;
            height: 100vh;
            position: fixed;
            padding: 20px 0;
            transition: all 0.3s;
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

        /* Main Content Styles */
        .main-content {
            flex: 1;
            margin-left: var(--sidebar-width);
            padding: 20px;
        }

        /* Header Styles */
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

        .header-actions {
            display: flex;
            gap: 15px;
        }

        .search-box {
            position: relative;
        }

        .search-box input {
            padding: 10px 15px 10px 40px;
            border-radius: 50px;
            border: 1px solid var(--gray-light);
            width: 250px;
            transition: all 0.3s;
        }

        .search-box input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(74, 144, 226, 0.2);
        }

        .search-box i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--gray);
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
            object-fit: cover;
        }

        .user-info h4 {
            font-size: 15px;
            font-weight: 600;
        }

        .user-info p {
            font-size: 13px;
            color: var(--gray);
        }

        /* Stats Section */
        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
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

        .bg-blue {
            background: rgba(74, 144, 226, 0.1);
            color: var(--primary);
        }

        .bg-green {
            background: rgba(72, 187, 120, 0.1);
            color: var(--success);
        }

        .bg-orange {
            background: rgba(236, 201, 75, 0.1);
            color: var(--warning);
        }

        .bg-red {
            background: rgba(229, 62, 62, 0.1);
            color: var(--danger);
        }

        /* Charts Section */
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

        .chart-controls {
            display: flex;
            gap: 10px;
        }

        .chart-btn {
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 13px;
            background: var(--gray-light);
            border: none;
            cursor: pointer;
        }

        .chart-btn.active {
            background: var(--primary);
            color: white;
        }

        .chart-wrapper {
            height: 250px;
            position: relative;
        }

        /* Video Table Section */
        .videos-container {
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

        .btn-primary {
            background: var(--primary);
            color: white;
        }

        .btn-primary:hover {
            background: var(--secondary);
        }

        .btn-outline {
            background: transparent;
            border: 1px solid var(--gray-light);
            color: var(--gray);
        }

        .btn-outline:hover {
            background: var(--gray-light);
        }

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

        .filter-group input {
            padding-left: 35px;
        }

        .search-control {
            position: relative;
        }

        .search-control i {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--gray);
        }

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

        th {
            padding: 12px 15px;
            text-align: left;
            font-weight: 600;
            color: var(--gray);
            font-size: 14px;
        }

        td {
            padding: 15px;
            border-bottom: 1px solid var(--gray-light);
            font-size: 14px;
        }

        .video-cell {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .video-thumbnail {
            width: 80px;
            height: 45px;
            border-radius: 4px;
            background: var(--gray-light);
            overflow: hidden;
            position: relative;
        }

        .video-thumbnail img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .video-info {
            display: flex;
            flex-direction: column;
        }

        .video-title {
            font-weight: 600;
            margin-bottom: 3px;
            display: -webkit-box;
            -webkit-line-clamp: 1;
            -webkit-box-orient: vertical;
            overflow: hidden;
            line-clamp: 1;
        }

        .video-category {
            color: var(--gray);
            font-size: 13px;
        }

        .status {
            display: inline-flex;
            align-items: center;
            padding: 5px 12px;
            border-radius: 50px;
            font-size: 13px;
            font-weight: 500;
        }

        .status-active {
            background: rgba(72, 187, 120, 0.1);
            color: var(--success);
        }

        .status-inactive {
            background: rgba(229, 62, 62, 0.1);
            color: var(--danger);
        }

        .status-pending {
            background: rgba(236, 201, 75, 0.1);
            color: var(--warning);
        }
        
        .verification-code {
            font-family: monospace;
            background-color: rgba(74, 144, 226, 0.1);
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 13px;
            color: var(--primary);
            display: inline-block;
        }

        .action-btn {
            width: 32px;
            height: 32px;
            border-radius: 6px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s;
            border: none;
            background: transparent;
            color: var(--gray);
        }

        .action-btn:hover {
            background: var(--gray-light);
            color: var(--dark);
        }

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
            border-color: var(--primary);
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
            align-items: center;
            justify-content: center;
        }

        .modal-content {
            background: white;
            border-radius: 10px;
            width: 500px;
            max-width: 90%;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }

        .modal-header {
            padding: 20px;
            border-bottom: 1px solid var(--gray-light);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-header h2 {
            font-size: 20px;
            font-weight: 600;
        }

        .close-btn {
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            color: var(--gray);
        }

        .modal-body {
            padding: 20px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--dark);
        }

        .form-group input, .form-group select, .form-group textarea {
            width: 100%;
            padding: 10px 15px;
            border: 1px solid var(--gray-light);
            border-radius: 6px;
            font-size: 15px;
        }

        .form-group input:focus, .form-group select:focus, .form-group textarea:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(74, 144, 226, 0.2);
        }

        .required {
            color: var(--danger);
        }

        .modal-footer {
            padding: 15px 20px;
            border-top: 1px solid var(--gray-light);
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }

        /* Preview Section */
        .preview-container {
            background: #f8fafc;
            border-radius: 8px;
            padding: 15px;
            margin-top: 20px;
        }
        
        .preview-player {
            width: 100%;
            height: 200px;
            background: #000;
            border-radius: 6px;
            overflow: hidden;
            margin-bottom: 10px;
        }
        
        .preview-thumbnail {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .preview-title {
            font-weight: 600;
            margin-bottom: 5px;
            font-size: 16px;
        }
        
        .preview-link {
            color: var(--primary);
            text-decoration: none;
            font-size: 14px;
            display: block;
            margin-top: 5px;
        }
        
        .preview-link:hover {
            text-decoration: underline;
        }
        
        .error-message {
            color: var(--danger);
            font-size: 13px;
            margin-top: 5px;
            display: none;
        }
        
        .loading-indicator {
            display: none;
            text-align: center;
            padding: 10px;
            color: var(--gray);
        }

        /* Responsive Design */
        @media (max-width: 1200px) {
            .charts-container {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 992px) {
            .sidebar {
                width: 80px;
            }
            
            .logo h1 span, .nav-links a span {
                display: none;
            }
            
            .logo h1 {
                justify-content: center;
            }
            
            .nav-links a {
                justify-content: center;
                padding: 12px;
            }
            
            .nav-links i {
                margin-right: 0;
                font-size: 20px;
            }
            
            .main-content {
                margin-left: 80px;
            }
        }

        @media (max-width: 768px) {
            .header {
                flex-direction: column;
                gap: 15px;
                align-items: flex-start;
            }
            
            .search-box input {
                width: 100%;
            }
            
            .stats-container {
                grid-template-columns: 1fr 1fr;
            }
            
            .filter-controls {
                flex-direction: column;
            }
            
            .controls {
                flex-wrap: wrap;
            }
            
            .charts-container {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 576px) {
            .stats-container {
                grid-template-columns: 1fr;
            }
            
            .page-controls {
                display: none;
            }
            
            .pagination {
                justify-content: center;
            }
            
            .modal-content {
                width: 95%;
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
            <li><a href="add_video.php" class="active"><i class="fas fa-video"></i> <span>Videos</span></a></li>
            <li><a href="users.php"><i class="fas fa-users"></i> <span>Users</span></a></li>
            <li><a href="new-users.php"><i class="fas fa-user-plus"></i> <span>New Users</span></a></li>
            <li><a href="analytics.php"><i class="fas fa-chart-line"></i> <span>Analytics</span></a></li>
            <li><a href="redemptions.php"><i class="fas fa-gift"></i> <span>Redemptions</span></a></li>
            <li><a href="tasks.php"><i class="fas fa-tasks"></i> <span>Tasks</span></a></li>
            <li><a href="rewards.php"><i class="fas fa-coins"></i> <span>Rewards</span></a></li>
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
                <h1>YouTube Video Management</h1>
                <p>Manage and analyze your YouTube video content</p>
            </div>
            <div class="header-actions">
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" placeholder="Search videos..." id="searchInput">
                </div>
                <div class="user-profile">
                    <img src="https://randomuser.me/api/portraits/men/41.jpg" alt="Admin">
                    <div class="user-info">
                        <h4><?= htmlspecialchars($adminUsername) ?></h4>
                        <p>Video Manager</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats Section -->
        <div class="stats-container">
            <div class="stat-card">
                <div class="stat-icon bg-blue">
                    <i class="fas fa-video"></i>
                </div>
                <div class="stat-info">
                    <h3 id="totalVideos">1,248</h3>
                    <p>Total Videos</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon bg-green">
                    <i class="fas fa-eye"></i>
                </div>
                <div class="stat-info">
                    <h3 id="totalViews">2.4M</h3>
                    <p>Total Views</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon bg-orange">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-info">
                    <h3 id="watchTime">1,850</h3>
                    <p>Watch Time (hrs)</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon bg-red">
                    <i class="fas fa-coins"></i>
                </div>
                <div class="stat-info">
                    <h3 id="pointsAwarded">24,850</h3>
                    <p>Points Awarded</p>
                </div>
            </div>
        </div>

        <!-- Charts Section -->
        <div class="charts-container">
            <div class="chart-card">
                <div class="chart-header">
                    <div class="chart-title">
                        <h3>Views Over Time</h3>
                        <p>Last 30 days performance</p>
                    </div>
                    <div class="chart-controls">
                        <button class="chart-btn active">30 Days</button>
                        <button class="chart-btn">90 Days</button>
                        <button class="chart-btn">1 Year</button>
                    </div>
                </div>
                <div class="chart-wrapper">
                    <canvas id="viewsChart"></canvas>
                </div>
            </div>
            
            <div class="chart-card">
                <div class="chart-header">
                    <div class="chart-title">
                        <h3>Category Distribution</h3>
                        <p>By view count</p>
                    </div>
                </div>
                <div class="chart-wrapper">
                    <canvas id="categoryChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Videos Table Section -->
        <div class="videos-container">
            <div class="section-header">
                <div class="section-title">
                    <h2>YouTube Videos</h2>
                    <p>Manage your YouTube video content library</p>
                </div>
                <div class="controls">
                    <button class="btn btn-outline" id="exportBtn">
                        <i class="fas fa-download"></i> Export
                    </button>
                    <button class="btn btn-primary" id="addVideoBtn">
                        <i class="fas fa-plus"></i> Add YouTube Video
                    </button>
                </div>
            </div>

            <!-- Filter Controls -->
            <div class="filter-controls">
                <div class="filter-group">
                    <label>Status</label>
                    <select id="statusFilter">
                        <option>All Status</option>
                        <option>Active</option>
                        <option>Inactive</option>
                        <option>Pending</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label>Category</label>
                    <select id="categoryFilter">
                        <option>All Categories</option>
                        <option>Entertainment</option>
                        <option>Education</option>
                        <option>Gaming</option>
                        <option>Technology</option>
                        <option>Science</option>
                        <option>Lifestyle</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label>Upload Date</label>
                    <select id="dateFilter">
                        <option>All Time</option>
                        <option>Today</option>
                        <option>This Week</option>
                        <option>This Month</option>
                    </select>
                </div>
                <div class="filter-group search-control">
                    <label>Search Videos</label>
                    <i class="fas fa-search"></i>
                    <input type="text" placeholder="Search by title..." id="titleSearch">
                </div>
            </div>

            <!-- Videos Table -->
            <div class="table-container">
                <table id="videosTable">
                    <thead>
                        <tr>
                            <th>Video</th>
                            <th>Category</th>
                            <th>Views</th>
                            <th>Watch Time</th>
                            <th>Points</th>
                            <th>Verification Code</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="videosTableBody">
                        <!-- Videos will be populated dynamically -->
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="pagination">
                <div class="page-info" id="pageInfo">
                    Showing 1 to 5 of 1,248 videos
                </div>
                <div class="page-controls">
                    <div class="page-btn" id="prevPage"><i class="fas fa-chevron-left"></i></div>
                    <div class="page-btn active">1</div>
                    <div class="page-btn">2</div>
                    <div class="page-btn">3</div>
                    <div class="page-btn">4</div>
                    <div class="page-btn"><i class="fas fa-ellipsis-h"></i></div>
                    <div class="page-btn">25</div>
                    <div class="page-btn" id="nextPage"><i class="fas fa-chevron-right"></i></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Video Modal -->
    <div class="modal" id="videoModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modalTitle">Add YouTube Video</h2>
                <button class="close-btn" id="closeModal">&times;</button>
            </div>
            <div class="modal-body">
                <form id="videoForm">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                    <div class="form-group">
                        <label for="youtubeUrl">YouTube Video URL <span class="required">*</span></label>
                        <input type="text" id="youtubeUrl" name="youtube_url" placeholder="https://www.youtube.com/watch?v=..." required>
                        <div class="error-message" id="urlError">Please enter a valid YouTube URL</div>
                    </div>
                    
                    <div class="loading-indicator" id="loadingIndicator">
                        <i class="fas fa-spinner fa-spin"></i> Fetching video details...
                    </div>
                    
                    <div class="preview-container" id="previewContainer" style="display: none;">
                        <div class="preview-player">
                            <iframe id="videoPlayer" width="100%" height="100%" 
                                    src="" frameborder="0" 
                                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                                    allowfullscreen></iframe>
                        </div>
                        <div class="preview-title" id="previewTitle">Video Title</div>
                        <a href="#" class="preview-link" id="watchLink" target="_blank">Watch on YouTube</a>
                    </div>
                    
                    <div class="form-group">
                        <label for="videoTitle">Video Title <span class="required">*</span></label>
                        <input type="text" id="videoTitle" name="title" placeholder="Enter video title" required>
                        <div class="error-message" id="titleError">Please enter a title for the video</div>
                    </div>
                    <div class="form-group">
                        <label for="videoCategory">Category</label>
                        <select id="videoCategory" name="category">
                            <option value="Entertainment">Entertainment</option>
                            <option value="Education">Education</option>
                            <option value="Gaming">Gaming</option>
                            <option value="Technology">Technology</option>
                            <option value="Science">Science</option>
                            <option value="Lifestyle">Lifestyle</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="videoStatus">Status</label>
                        <select id="videoStatus" name="status">
                            <option value="Active">Active</option>
                            <option value="Inactive">Inactive</option>
                            <option value="Pending">Pending</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="pointsAward">Points Awarded</label>
                        <input type="number" id="pointsAward" name="points" min="0" value="100">
                    </div>
                    <div class="form-group">
                        <label for="verification_code">Verification Code/Phrase <span class="required">*</span></label>
                        <input type="text" id="verification_code" name="verification_code" placeholder="e.g. 1234 or ABCD" required>
                        <div class="error-message" id="codeError">Please enter a verification code/phrase</div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-outline" id="cancelBtn">Cancel</button>
                <button class="btn btn-primary" id="saveVideoBtn">Add Video</button>
            </div>
        </div>
    </div>

    <script>
        // Sample video data - CHANGED KEY TO 'youtube_videos'
        let videos = JSON.parse(localStorage.getItem('youtube_videos')) || [
            {
                id: 1,
                title: "How to Master Digital Marketing in 2025",
                category: "Education",
                views: 15248,
                watchTime: 420,
                points: 1850,
                status: "Active",
                thumbnail: "https://i.ytimg.com/vi/dQw4w9WgXcQ/hqdefault.jpg",
                youtubeId: "dQw4w9WgXcQ",
                verificationCode: "ABCD1234"
            },
            {
                id: 2,
                title: "Top Gaming Strategies for Beginners",
                category: "Gaming",
                views: 28745,
                watchTime: 780,
                points: 3250,
                status: "Active",
                thumbnail: "https://i.ytimg.com/vi/6ZfuNTqbHE8/hqdefault.jpg",
                youtubeId: "6ZfuNTqbHE8",
                verificationCode: "EFGH5678"
            },
            {
                id: 3,
                title: "Quick and Healthy Breakfast Ideas",
                category: "Lifestyle",
                views: 9521,
                watchTime: 150,
                points: 850,
                status: "Active",
                thumbnail: "https://i.ytimg.com/vi/PkZNo7MFNFg/hqdefault.jpg",
                youtubeId: "PkZNo7MFNFg",
                verificationCode: "IJKL9012"
            },
            {
                id: 4,
                title: "Bohemian Rhapsody - Official Trailer",
                category: "Entertainment",
                views: 125842,
                watchTime: 3450,
                points: 8420,
                status: "Active",
                thumbnail: "https://i.ytimg.com/vi/fJ9rUzIMcZQ/hqdefault.jpg",
                youtubeId: "fJ9rUzIMcZQ",
                verificationCode: "MNOP3456"
            },
            {
                id: 5,
                title: "Introduction to Quantum Computing",
                category: "Science",
                views: 42369,
                watchTime: 920,
                points: 2150,
                status: "Inactive",
                thumbnail: "https://i.ytimg.com/vi/R_f1uCWKZQs/hqdefault.jpg",
                youtubeId: "R_f1uCWKZQs",
                verificationCode: "QRST7890"
            }
        ];

        // Save videos to localStorage - CHANGED KEY TO 'youtube_videos'
        function saveVideosToStorage() {
            localStorage.setItem('youtube_videos', JSON.stringify(videos));
        }

        // DOM Elements
        const videosTableBody = document.getElementById('videosTableBody');
        const videoModal = document.getElementById('videoModal');
        const addVideoBtn = document.getElementById('addVideoBtn');
        const closeModal = document.getElementById('closeModal');
        const cancelBtn = document.getElementById('cancelBtn');
        const saveVideoBtn = document.getElementById('saveVideoBtn');
        const youtubeUrlInput = document.getElementById('youtubeUrl');
        const videoTitleInput = document.getElementById('videoTitle');
        const previewContainer = document.getElementById('previewContainer');
        const previewTitle = document.getElementById('previewTitle');
        const videoPlayer = document.getElementById('videoPlayer');
        const watchLink = document.getElementById('watchLink');
        const loadingIndicator = document.getElementById('loadingIndicator');
        const urlError = document.getElementById('urlError');
        const titleError = document.getElementById('titleError');
        const codeError = document.getElementById('codeError');
        const searchInput = document.getElementById('searchInput');
        const statusFilter = document.getElementById('statusFilter');
        const categoryFilter = document.getElementById('categoryFilter');
        const dateFilter = document.getElementById('dateFilter');
        const titleSearch = document.getElementById('titleSearch');
        const exportBtn = document.getElementById('exportBtn');

        // Initialize charts and render table
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize charts
            initCharts();
            
            // Render videos table
            renderVideosTable(videos);
            
            // Modal functionality
            addVideoBtn.addEventListener('click', () => {
                document.getElementById('modalTitle').textContent = 'Add YouTube Video';
                document.getElementById('saveVideoBtn').textContent = 'Add Video';
                resetForm();
                videoModal.style.display = 'flex';
            });

            closeModal.addEventListener('click', () => {
                videoModal.style.display = 'none';
            });

            cancelBtn.addEventListener('click', () => {
                videoModal.style.display = 'none';
            });

            // Update preview when URL changes
            youtubeUrlInput.addEventListener('input', updatePreview);
            
            // Update preview title when input changes
            videoTitleInput.addEventListener('input', () => {
                previewTitle.textContent = videoTitleInput.value || "YouTube Video";
                if (videoTitleInput.value) {
                    titleError.style.display = 'none';
                }
            });
            
            // Update preview code when input changes
            document.getElementById('verification_code').addEventListener('input', () => {
                if (document.getElementById('verification_code').value) {
                    codeError.style.display = 'none';
                }
            });

            // Save video functionality
            saveVideoBtn.addEventListener('click', saveVideo);
            
            // Export functionality
            exportBtn.addEventListener('click', () => {
                alert('Video data exported successfully!');
            });

            // Search functionality
            searchInput.addEventListener('input', filterVideos);
            statusFilter.addEventListener('change', filterVideos);
            categoryFilter.addEventListener('change', filterVideos);
            dateFilter.addEventListener('change', filterVideos);
            titleSearch.addEventListener('input', filterVideos);
        });

        // Function to initialize charts
        function initCharts() {
            // Views Over Time Chart
            const viewsCtx = document.getElementById('viewsChart').getContext('2d');
            new Chart(viewsCtx, {
                type: 'line',
                data: {
                    labels: ['1', '5', '10', '15', '20', '25', '30'],
                    datasets: [{
                        label: 'Daily Views',
                        data: [1200, 1900, 1500, 2200, 1800, 2500, 3000],
                        borderColor: '#4a90e2',
                        backgroundColor: 'rgba(74, 144, 226, 0.1)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.4
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

            // Category Distribution Chart
            const categoryCtx = document.getElementById('categoryChart').getContext('2d');
            new Chart(categoryCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Entertainment', 'Education', 'Gaming', 'Technology', 'Science', 'Lifestyle'],
                    datasets: [{
                        data: [35, 20, 15, 12, 10, 8],
                        backgroundColor: [
                            '#4a90e2',
                            '#48bb78',
                            '#ecc94b',
                            '#9f7aea',
                            '#ed64a6',
                            '#667eea'
                        ],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'right',
                        }
                    },
                    cutout: '70%'
                }
            });
        }

        // Function to extract YouTube video ID from URL
        function extractVideoId(url) {
            const regExp = /^.*(youtu\.be\/|v\/|u\/\w\/|embed\/|watch\?v=|\&v=)([^#\&\?]*).*/;
            const match = url.match(regExp);
            return (match && match[2].length === 11) ? match[2] : null;
        }

        // Function to update preview
        function updatePreview() {
            const url = youtubeUrlInput.value;
            const videoId = extractVideoId(url);
            
            if (videoId) {
                // Show loading indicator
                loadingIndicator.style.display = 'block';
                previewContainer.style.display = 'block';
                urlError.style.display = 'none';
                
                // Update player
                videoPlayer.src = `https://www.youtube.com/embed/${videoId}`;
                
                // Update watch link
                watchLink.href = `https://www.youtube.com/watch?v=${videoId}`;
                
                // Set placeholder title
                if (!videoTitleInput.value) {
                    videoTitleInput.value = "YouTube Video";
                    previewTitle.textContent = "YouTube Video";
                }
                
                // Hide loading indicator after a short delay to simulate fetch
                setTimeout(() => {
                    loadingIndicator.style.display = 'none';
                }, 800);
            } else {
                previewContainer.style.display = 'none';
                if (url.length > 0) {
                    urlError.style.display = 'block';
                } else {
                    urlError.style.display = 'none';
                }
            }
        }

        // Function to save video
        function saveVideo() {
            if (!validateForm()) return;
            
            const formData = new FormData(document.getElementById('videoForm'));
            const title = videoTitleInput.value;
            const category = document.getElementById('videoCategory').value;
            const status = document.getElementById('videoStatus').value;
            const points = parseInt(document.getElementById('pointsAward').value);
            const youtubeUrl = youtubeUrlInput.value;
            const videoId = extractVideoId(youtubeUrl);
            const verificationCode = document.getElementById('verification_code').value;
            
            // Send to backend via AJAX
            fetch('api/add_video.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Add new video to local storage as well
                    const newVideo = {
                        id: videos.length + 1,
                        title: title,
                        category: category,
                        views: Math.floor(Math.random() * 50000),
                        watchTime: Math.floor(Math.random() * 100),
                        points: points,
                        status: status,
                        thumbnail: `https://i.ytimg.com/vi/${videoId}/hqdefault.jpg`,
                        youtubeId: videoId,
                        verificationCode: verificationCode
                    };
                    
                    videos.unshift(newVideo);
                    renderVideosTable(videos);
                    updateStats();
                    saveVideosToStorage();
                    
                    videoModal.style.display = 'none';
                    alert('Video added successfully!');
                } else {
                    alert('Error adding video: ' + (data.error || 'Unknown error'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error adding video. Please try again.');
            });
        }

        // Function to validate form
        function validateForm() {
            let isValid = true;
            
            // Validate URL
            const url = youtubeUrlInput.value;
            const videoId = extractVideoId(url);
            if (!videoId) {
                urlError.style.display = 'block';
                isValid = false;
            } else {
                urlError.style.display = 'none';
            }
            
            // Validate title
            if (!videoTitleInput.value.trim()) {
                titleError.style.display = 'block';
                isValid = false;
            } else {
                titleError.style.display = 'none';
            }
            
            // Validate verification code
            const verificationCode = document.getElementById('verification_code').value;
            if (!verificationCode.trim()) {
                codeError.style.display = 'block';
                isValid = false;
            } else {
                codeError.style.display = 'none';
            }
            
            return isValid;
        }

        // Function to reset form
        function resetForm() {
            document.getElementById('videoForm').reset();
            previewContainer.style.display = 'none';
            videoPlayer.src = '';
            urlError.style.display = 'none';
            titleError.style.display = 'none';
            codeError.style.display = 'none';
            loadingIndicator.style.display = 'none';
        }

        // Function to render videos table
        function renderVideosTable(videosArray) {
            videosTableBody.innerHTML = '';
            
            videosArray.forEach(video => {
                const row = document.createElement('tr');
                
                // Add mouseover effect for better UX
                row.addEventListener('mouseenter', () => {
                    row.style.backgroundColor = '#f8fafc';
                });
                
                row.addEventListener('mouseleave', () => {
                    row.style.backgroundColor = '';
                });
                
                row.innerHTML = `
                    <td>
                        <div class="video-cell">
                            <div class="video-thumbnail">
                                <img src="${video.thumbnail}" alt="${video.title}">
                            </div>
                            <div class="video-info">
                                <div class="video-title">${video.title}</div>
                                <div class="video-category">${video.category}</div>
                            </div>
                        </div>
                    </td>
                    <td>${video.category}</td>
                    <td>${video.views.toLocaleString()}</td>
                    <td>${video.watchTime} hrs</td>
                    <td>${video.points.toLocaleString()}</td>
                    <td><span class="verification-code">${video.verificationCode}</span></td>
                    <td><span class="status status-${video.status.toLowerCase()}">${video.status}</span></td>
                    <td>
                        <button class="action-btn" onclick="editVideo(${video.id})"><i class="fas fa-edit"></i></button>
                        <button class="action-btn" onclick="deleteVideo(${video.id})"><i class="fas fa-trash"></i></button>
                        <button class="action-btn" onclick="viewAnalytics(${video.id})"><i class="fas fa-chart-line"></i></button>
                    </td>
                `;
                
                videosTableBody.appendChild(row);
            });
            
            updateStats();
        }

        // Function to update stats
        function updateStats() {
            const totalVideos = videos.length;
            const totalViews = videos.reduce((sum, video) => sum + video.views, 0);
            const watchTime = videos.reduce((sum, video) => sum + video.watchTime, 0);
            const pointsAwarded = videos.reduce((sum, video) => sum + video.points, 0);
            
            document.getElementById('totalVideos').textContent = totalVideos.toLocaleString();
            document.getElementById('totalViews').textContent = (totalViews / 1000).toFixed(1) + 'K';
            document.getElementById('watchTime').textContent = watchTime.toLocaleString();
            document.getElementById('pointsAwarded').textContent = pointsAwarded.toLocaleString();
            document.getElementById('pageInfo').textContent = `Showing 1 to ${Math.min(5, videos.length)} of ${videos.length} videos`;
        }

        // Function to filter videos
        function filterVideos() {
            const searchTerm = searchInput.value.toLowerCase();
            const statusValue = statusFilter.value;
            const categoryValue = categoryFilter.value;
            const titleTerm = titleSearch.value.toLowerCase();
            
            const filtered = videos.filter(video => {
                const matchesSearch = video.title.toLowerCase().includes(searchTerm);
                const matchesStatus = statusValue === 'All Status' || video.status === statusValue;
                const matchesCategory = categoryValue === 'All Categories' || video.category === categoryValue;
                const matchesTitle = video.title.toLowerCase().includes(titleTerm);
                
                return matchesSearch && matchesStatus && matchesCategory && matchesTitle;
            });
            
            renderVideosTable(filtered);
        }

        // Function to edit video
        function editVideo(id) {
            const video = videos.find(v => v.id === id);
            if (!video) return;
            
            document.getElementById('modalTitle').textContent = 'Edit Video';
            document.getElementById('saveVideoBtn').textContent = 'Update Video';
            
            youtubeUrlInput.value = `https://www.youtube.com/watch?v=${video.youtubeId}`;
            videoTitleInput.value = video.title;
            document.getElementById('videoCategory').value = video.category;
            document.getElementById('videoStatus').value = video.status;
            document.getElementById('pointsAward').value = video.points;
            document.getElementById('verification_code').value = video.verificationCode;
            
            previewContainer.style.display = 'block';
            videoPlayer.src = `https://www.youtube.com/embed/${video.youtubeId}`;
            previewTitle.textContent = video.title;
            watchLink.href = `https://www.youtube.com/watch?v=${video.youtubeId}`;
            
            // Store the video id in the button for update
            saveVideoBtn.dataset.id = id;
            videoModal.style.display = 'flex';
            
            // Update save button functionality for editing
            saveVideoBtn.onclick = function() {
                if (!validateForm()) return;
                
                const title = videoTitleInput.value;
                const category = document.getElementById('videoCategory').value;
                const status = document.getElementById('videoStatus').value;
                const points = parseInt(document.getElementById('pointsAward').value);
                const youtubeUrl = youtubeUrlInput.value;
                const videoId = extractVideoId(youtubeUrl);
                const verificationCode = document.getElementById('verification_code').value;
                
                // Update video
                const videoIndex = videos.findIndex(v => v.id === id);
                if (videoIndex !== -1) {
                    videos[videoIndex] = {
                        ...videos[videoIndex],
                        title: title,
                        category: category,
                        status: status,
                        points: points,
                        thumbnail: `https://i.ytimg.com/vi/${videoId}/hqdefault.jpg`,
                        youtubeId: videoId,
                        verificationCode: verificationCode
                    };
                    
                    renderVideosTable(videos);
                    saveVideosToStorage();
                    videoModal.style.display = 'none';
                    alert('Video updated successfully!');
                }
            };
        }

        // Function to delete video
        function deleteVideo(id) {
            if (confirm('Are you sure you want to delete this video?')) {
                const index = videos.findIndex(v => v.id === id);
                if (index !== -1) {
                    videos.splice(index, 1);
                    renderVideosTable(videos);
                    saveVideosToStorage();
                    alert('Video deleted successfully!');
                }
            }
        }

        // Function to view analytics
        function viewAnalytics(id) {
            const video = videos.find(v => v.id === id);
            if (video) {
                alert(`Opening analytics for: ${video.title}`);
            }
        }
    </script>
</body>
</html>
