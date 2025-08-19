<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Redemption Requests</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
        
        /* Analytics Modal Styles */
        .analytics-modal .modal-content {
            width: 800px;
        }
        
        .analytics-header {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
            gap: 15px;
        }
        
        .analytics-header img {
            width: 120px;
            height: 68px;
            border-radius: 4px;
        }
        
        .analytics-header h3 {
            font-size: 18px;
            margin-bottom: 5px;
        }
        
        .analytics-header p {
            color: var(--gray);
            font-size: 14px;
        }
        
        .metrics-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .metric-card {
            background: #f8fafc;
            border-radius: 8px;
            padding: 15px;
            text-align: center;
        }
        
        .metric-card .value {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 5px;
            color: var(--primary);
        }
        
        .metric-card .label {
            font-size: 13px;
            color: var(--gray);
        }
        
        .analytics-charts {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .analytics-chart {
            background: white;
            border-radius: 8px;
            padding: 15px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }
        
        .analytics-chart h4 {
            font-size: 15px;
            margin-bottom: 15px;
            color: var(--gray);
        }
        
        .chart-container {
            height: 200px;
            position: relative;
        }
        
        .audience-stats {
            background: white;
            border-radius: 8px;
            padding: 15px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }
        
        .audience-stats h4 {
            font-size: 15px;
            margin-bottom: 15px;
            color: var(--gray);
        }
        
        .audience-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
        }
        
        .audience-item {
            text-align: center;
        }
        
        .audience-item .value {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 5px;
        }
        
        .audience-item .label {
            font-size: 13px;
            color: var(--gray);
        }
        
        .progress-bar {
            height: 6px;
            background: var(--gray-light);
            border-radius: 3px;
            margin-top: 5px;
            overflow: hidden;
        }
        
        .progress {
            height: 100%;
            background: var(--primary);
            border-radius: 3px;
        }

        /* Responsive Design */
        @media (max-width: 1200px) {
            .charts-container {
                grid-template-columns: 1fr;
            }
            
            .analytics-modal .modal-content {
                width: 700px;
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
            
            .analytics-modal .modal-content {
                width: 600px;
            }
            
            .metrics-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .analytics-charts {
                grid-template-columns: 1fr;
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
            
            .analytics-modal .modal-content {
                width: 95%;
            }
            
            .audience-grid {
                grid-template-columns: repeat(2, 1fr);
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
            
            .metrics-grid {
                grid-template-columns: 1fr;
            }
            
            .audience-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar (same as add video.html) -->
    <div class="sidebar">
        <!-- ... existing sidebar code ... -->
        <ul class="nav-links">
            <!-- Add new link -->
            <li><a href="admin_redemption.html" class="active"><i class="fas fa-gift"></i> <span>Redemption Requests</span></a></li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="header">
            <div class="header-title">
                <h1>Redemption Requests</h1>
                <p>Approve or reject user UC redemption requests</p>
            </div>
        </div>

        <!-- Requests Table -->
        <div class="videos-container">
            <div class="section-header">
                <h2>Pending Requests</h2>
            </div>
            
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>User ID</th>
                            <th>PUBG Username</th>
                            <th>PUBG ID</th>
                            <th>Points</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="requestsTable">
                        <!-- Requests will load here -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
<!-- After requests table -->
<div class="videos-container mt-8">
    <div class="section-header">
        <h2>Send Announcement</h2>
    </div>
    
    <div class="form-group">
        <label>Announcement Message</label>
        <textarea id="announcementMessage" rows="4" class="w-full"></textarea>
    </div>
    
    <div class="form-group">
        <label>Announcement Type</label>
        <select id="announcementType">
            <option value="info">Information</option>
            <option value="warning">Warning</option>
            <option value="urgent">Urgent</option>
        </select>
    </div>
    
    <button class="btn btn-primary" onclick="sendAnnouncement()">
        <i class="fas fa-bullhorn"></i> Send Announcement
    </button>
</div>

<script>
function sendAnnouncement() {
    const message = document.getElementById('announcementMessage').value;
    const type = document.getElementById('announcementType').value;
    
    if(!message) return;
    
    const announcement = {
        id: Date.now(),
        message,
        type,
        date: new Date().toISOString()
    };
    
    // Save to all users
    const users = JSON.parse(localStorage.getItem('users')) || {};
    Object.keys(users).forEach(userId => {
        addUserNotification(userId, `ANNOUNCEMENT: ${message}`, type);
    });
    
    alert('Announcement sent to all users!');
}
</script>
    <script>
        // Load and display requests
        function loadRequests() {
            const requests = JSON.parse(localStorage.getItem('redemptionRequests') || [];
            const table = document.getElementById('requestsTable');
            table.innerHTML = '';
            
            requests.forEach(request => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${request.userId}</td>
                    <td>${request.pubgUsername}</td>
                    <td>${request.pubgId}</td>
                    <td>${request.points}</td>
                    <td>${new Date(request.date).toLocaleString()}</td>
                    <td><span class="status status-${request.status}">${request.status}</span></td>
                    <td>
                        ${request.status === 'pending' ? `
                            <button class="btn btn-success" onclick="updateRequest(${request.id}, 'approved')">
                                <i class="fas fa-check"></i> Approve
                            </button>
                            <button class="btn btn-danger" onclick="updateRequest(${request.id}, 'rejected')">
                                <i class="fas fa-times"></i> Reject
                            </button>
                        ` : ''}
                    </td>
                `;
                table.appendChild(row);
            });
        }
        
        // Update request status
        function updateRequest(id, status) {
            const requests = JSON.parse(localStorage.getItem('redemptionRequests'));
            const request = requests.find(r => r.id === id);
            
            if(request) {
                request.status = status;
                localStorage.setItem('redemptionRequests', JSON.stringify(requests));
                
                // Add notification for user
                addUserNotification(
                    request.userId,
                    `Your UC redemption request has been ${status}`,
                    status === 'approved' ? 'success' : 'error'
                );
                
                loadRequests();
            }
        }
        
        // Add notification to user's account
        function addUserNotification(userId, message, type) {
            const notifications = JSON.parse(localStorage.getItem('userNotifications') || {};
            if(!notifications[userId]) notifications[userId] = [];
            
            notifications[userId].push({
                id: Date.now(),
                message,
                type,
                date: new Date().toISOString(),
                read: false
            });
            
            localStorage.setItem('userNotifications', JSON.stringify(notifications));
        }
        
        // Initialize
        loadRequests();
    </script>
</body>
</html>