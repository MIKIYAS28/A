<?php
// Check if session is already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../config.php';

// Check if user is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.html');
    exit;
}

$adminUsername = $_SESSION['username'] ?? 'Admin';

// Generate CSRF token
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrfToken = $_SESSION['csrf_token'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Task Management - UC FORGE Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com/3.4.16"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Pacifico&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        primary: '#FFD700',
                        secondary: '#357ABD'
                    }
                }
            }
        };
    </script>
    <style>
        .task-card {
            transition: all 0.3s ease;
        }
        .task-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }
        .modal {
            backdrop-filter: blur(4px);
        }
    </style>
</head>
<body class="min-h-screen bg-gray-50">
    <!-- Sidebar -->
    <aside class="w-72 h-screen bg-white fixed left-0 top-0 shadow-md flex flex-col z-50 border-r border-gray-200">
        <!-- Logo -->
        <div class="px-6 py-6 border-b border-gray-200">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 flex items-center justify-center bg-primary bg-opacity-10 rounded-full p-2">
                    <img src="../image.png" alt="Logo" class="object-contain" />
                </div>
                <h1 class="font-['Pacifico'] text-2xl text-primary">UC FORGE</h1>
            </div>
        </div>
        
        <!-- Navigation Menu -->
        <nav class="flex-1 overflow-y-auto py-4">
            <ul>
                <li><a href="index.html" class="sidebar-item flex items-center px-6 py-3 text-gray-700 hover:bg-gray-50">
                    <i class="fas fa-home w-6 h-6 mr-3 text-gray-400"></i>
                    <span>Dashboard</span>
                </a></li>
                <li><a href="#" class="sidebar-item flex items-center px-6 py-3 text-gray-700 hover:bg-gray-50">
                    <i class="fas fa-users w-6 h-6 mr-3 text-gray-400"></i>
                    <span>Users</span>
                </a></li>
                <li><a href="tasks.php" class="sidebar-item active flex items-center px-6 py-3 text-primary bg-primary bg-opacity-10">
                    <i class="fas fa-tasks w-6 h-6 mr-3"></i>
                    <span class="font-medium">Task Management</span>
                </a></li>
                <li><a href="#" class="sidebar-item flex items-center px-6 py-3 text-gray-700 hover:bg-gray-50">
                    <i class="fas fa-money-bill-wave w-6 h-6 mr-3 text-gray-400"></i>
                    <span>Withdrawal Requests</span>
                </a></li>
                <li><a href="#" class="sidebar-item flex items-center px-6 py-3 text-gray-700 hover:bg-gray-50">
                    <i class="fas fa-gift w-6 h-6 mr-3 text-gray-400"></i>
                    <span>UC Redemptions</span>
                </a></li>
            </ul>
        </nav>
        
        <!-- Admin Profile -->
        <div class="px-6 py-4 border-t border-gray-200">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-primary flex items-center justify-center">
                    <i class="fas fa-user-shield text-white"></i>
                </div>
                <div class="flex-1">
                    <p class="font-medium text-gray-900"><?= htmlspecialchars($adminUsername) ?></p>
                    <p class="text-sm text-gray-500">Administrator</p>
                </div>
                <a href="#" class="text-gray-400 hover:text-red-500">
                    <i class="fas fa-sign-out-alt"></i>
                </a>
            </div>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="flex-1 ml-72 p-8">
        <!-- Header -->
        <div class="bg-gradient-to-r from-primary to-secondary rounded-lg p-6 mb-8 text-white shadow-lg">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold">Task Management</h1>
                    <p class="text-sm opacity-90 mt-1">Create and manage Social, Video, and Referral tasks</p>
                </div>
                <button id="createTaskBtn" class="bg-white text-gray-800 px-6 py-3 rounded-lg font-medium hover:bg-gray-100 transition-colors flex items-center gap-2">
                    <i class="fas fa-plus"></i>
                    Create New Task
                </button>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-lg p-6 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">Total Tasks</p>
                        <p id="totalTasks" class="text-2xl font-bold text-gray-900">0</p>
                    </div>
                    <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-tasks text-blue-600"></i>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-lg p-6 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">Active Tasks</p>
                        <p id="activeTasks" class="text-2xl font-bold text-green-600">0</p>
                    </div>
                    <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-check-circle text-green-600"></i>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-lg p-6 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">Video Tasks</p>
                        <p id="videoTasks" class="text-2xl font-bold text-red-600">0</p>
                    </div>
                    <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-video text-red-600"></i>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-lg p-6 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">Social Tasks</p>
                        <p id="socialTasks" class="text-2xl font-bold text-purple-600">0</p>
                    </div>
                    <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-share-alt text-purple-600"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Task Filters -->
        <div class="bg-white rounded-lg p-6 mb-8 shadow-sm">
            <div class="flex flex-wrap items-center gap-4">
                <div>
                    <label class="text-sm font-medium text-gray-700">Filter by Type:</label>
                    <select id="typeFilter" class="ml-2 border border-gray-300 rounded-lg px-3 py-2">
                        <option value="">All Types</option>
                        <option value="social">Social</option>
                        <option value="video">Video</option>
                        <option value="referral">Referral</option>
                    </select>
                </div>
                
                <div>
                    <label class="text-sm font-medium text-gray-700">Filter by Status:</label>
                    <select id="statusFilter" class="ml-2 border border-gray-300 rounded-lg px-3 py-2">
                        <option value="">All Status</option>
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
                    </select>
                </div>
                
                <div class="flex-1">
                    <div class="relative">
                        <input type="text" id="searchInput" placeholder="Search tasks..." 
                               class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary">
                        <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tasks List -->
        <div class="bg-white rounded-lg shadow-sm">
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-xl font-bold text-gray-900">All Tasks</h2>
            </div>
            
            <div id="tasksContainer" class="p-6">
                <div id="loadingTasks" class="text-center py-8">
                    <i class="fas fa-spinner fa-spin text-2xl text-gray-400 mb-4"></i>
                    <p class="text-gray-500">Loading tasks...</p>
                </div>
            </div>
        </div>
    </main>

    <!-- Create/Edit Task Modal -->
    <div id="taskModal" class="modal fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center">
        <div class="bg-white rounded-lg max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto">
            <div class="p-6 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h3 id="modalTitle" class="text-xl font-bold text-gray-900">Create New Task</h3>
                    <button id="closeModal" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
            </div>
            
            <form id="taskForm" class="p-6">
                <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrfToken) ?>">
                <input type="hidden" id="taskId" name="task_id" value="">
                
                <div class="grid grid-cols-1 gap-6">
                    <!-- Basic Info -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="taskTitle" class="block text-sm font-medium text-gray-700 mb-2">Task Title</label>
                            <input type="text" id="taskTitle" name="title" required
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary">
                        </div>
                        
                        <div>
                            <label for="taskType" class="block text-sm font-medium text-gray-700 mb-2">Task Type</label>
                            <select id="taskType" name="type" required
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary">
                                <option value="">Select Type</option>
                                <option value="social">Social Media</option>
                                <option value="video">Video</option>
                                <option value="referral">Referral</option>
                            </select>
                        </div>
                    </div>
                    
                    <div>
                        <label for="taskDescription" class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                        <textarea id="taskDescription" name="description" rows="3" required
                                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary"></textarea>
                    </div>
                    
                    <!-- Points and URL -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="taskPoints" class="block text-sm font-medium text-gray-700 mb-2">Points Reward</label>
                            <input type="number" id="taskPoints" name="reward" min="1" required
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary">
                        </div>
                        
                        <div>
                            <label for="targetCount" class="block text-sm font-medium text-gray-700 mb-2">Target Count</label>
                            <input type="number" id="targetCount" name="target_count" min="1" value="1"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary">
                        </div>
                    </div>
                    
                    <div>
                        <label for="taskUrl" class="block text-sm font-medium text-gray-700 mb-2">URL (Optional)</label>
                        <input type="url" id="taskUrl" name="url" placeholder="https://example.com"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary">
                    </div>
                    
                    <!-- Status -->
                    <div>
                        <label class="flex items-center">
                            <input type="checkbox" id="isActive" name="enabled" checked class="text-primary focus:ring-primary">
                            <span class="ml-2 text-sm font-medium text-gray-700">Task is Active</span>
                        </label>
                    </div>
                </div>
                
                <div class="flex justify-end gap-4 mt-8">
                    <button type="button" id="cancelBtn" class="px-6 py-3 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                        Cancel
                    </button>
                    <button type="submit" id="saveBtn" class="px-6 py-3 bg-primary text-white rounded-lg hover:bg-opacity-90">
                        <i class="fas fa-save mr-2"></i>
                        Save Task
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="modal fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center">
        <div class="bg-white rounded-lg max-w-md w-full mx-4">
            <div class="p-6">
                <div class="flex items-center mb-4">
                    <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center mr-4">
                        <i class="fas fa-trash text-red-600"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-gray-900">Delete Task</h3>
                        <p class="text-gray-600">This action cannot be undone</p>
                    </div>
                </div>
                
                <p class="text-gray-700 mb-6">Are you sure you want to delete this task? All associated user completions will also be removed.</p>
                
                <div class="flex justify-end gap-4">
                    <button id="cancelDelete" class="px-6 py-3 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                        Cancel
                    </button>
                    <button id="confirmDelete" class="px-6 py-3 bg-red-600 text-white rounded-lg hover:bg-red-700">
                        <i class="fas fa-trash mr-2"></i>
                        Delete Task
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="tasks.js"></script>
</body>
</html>
