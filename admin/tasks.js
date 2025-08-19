// Task Management JavaScript
let currentTasks = [];
let editingTaskId = null;

// DOM Elements
const tasksContainer = document.getElementById('tasksContainer');
const taskModal = document.getElementById('taskModal');
const deleteModal = document.getElementById('deleteModal');
const taskForm = document.getElementById('taskForm');
const createTaskBtn = document.getElementById('createTaskBtn');
const closeModal = document.getElementById('closeModal');
const cancelBtn = document.getElementById('cancelBtn');
const saveBtn = document.getElementById('saveBtn');
const confirmDelete = document.getElementById('confirmDelete');
const cancelDelete = document.getElementById('cancelDelete');

// Filter elements
const typeFilter = document.getElementById('typeFilter');
const statusFilter = document.getElementById('statusFilter');
const searchInput = document.getElementById('searchInput');

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    loadTasks();
    setupEventListeners();
});

function setupEventListeners() {
    // Modal controls
    createTaskBtn.addEventListener('click', () => openTaskModal());
    closeModal.addEventListener('click', () => closeTaskModal());
    cancelBtn.addEventListener('click', () => closeTaskModal());
    cancelDelete.addEventListener('click', () => closeDeleteModal());

    // Form submission
    taskForm.addEventListener('submit', handleTaskSubmit);
    
    // Delete confirmation
    confirmDelete.addEventListener('click', confirmDeleteTask);
    
    // Filters and search
    typeFilter.addEventListener('change', filterTasks);
    statusFilter.addEventListener('change', filterTasks);
    searchInput.addEventListener('input', debounce(filterTasks, 300));
    
    // Close modal on outside click
    taskModal.addEventListener('click', (e) => {
        if (e.target === taskModal) closeTaskModal();
    });
    deleteModal.addEventListener('click', (e) => {
        if (e.target === deleteModal) closeDeleteModal();
    });
}

// Load tasks from API
async function loadTasks() {
    try {
        const response = await fetch('../api/admin/list_tasks.php');
        const result = await response.json();
        
        if (result.success) {
            currentTasks = result.tasks;
            updateStats(result.stats);
            renderTasks(currentTasks);
        } else {
            showError('Failed to load tasks');
        }
    } catch (error) {
        console.error('Error loading tasks:', error);
        showError('Network error while loading tasks');
    }
}

// Update statistics
function updateStats(stats) {
    document.getElementById('totalTasks').textContent = stats.total_tasks;
    document.getElementById('activeTasks').textContent = stats.active_tasks;
    document.getElementById('videoTasks').textContent = stats.video_tasks;
    document.getElementById('socialTasks').textContent = stats.social_tasks;
}

// Render tasks
function renderTasks(tasks) {
    if (tasks.length === 0) {
        tasksContainer.innerHTML = `
            <div class="text-center py-12">
                <i class="fas fa-tasks text-4xl text-gray-300 mb-4"></i>
                <h3 class="text-lg font-medium text-gray-900 mb-2">No tasks found</h3>
                <p class="text-gray-500">Create your first task to get started</p>
            </div>
        `;
        return;
    }

    tasksContainer.innerHTML = `
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            ${tasks.map(task => renderTaskCard(task)).join('')}
        </div>
    `;
}

// Render individual task card
function renderTaskCard(task) {
    const typeIcon = {
        social: 'fa-share-alt',
        video: 'fa-video',
        referral: 'fa-users'
    }[task.type] || 'fa-tasks';

    const typeColor = {
        social: 'purple',
        video: 'red',
        referral: 'blue'
    }[task.type] || 'gray';

    const statusBadge = task.enabled 
        ? '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Active</span>'
        : '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">Inactive</span>';

    return `
        <div class="task-card bg-white rounded-lg border border-gray-200 p-6">
            <div class="flex items-start justify-between mb-4">
                <div class="flex items-center">
                    <div class="w-10 h-10 bg-${typeColor}-100 rounded-lg flex items-center justify-center mr-3">
                        <i class="fas ${typeIcon} text-${typeColor}-600"></i>
                    </div>
                    <div>
                        <h3 class="font-semibold text-gray-900">${escapeHtml(task.title)}</h3>
                        <p class="text-sm text-gray-500 capitalize">${task.type}</p>
                    </div>
                </div>
                ${statusBadge}
            </div>
            
            <p class="text-gray-600 text-sm mb-4 line-clamp-2">${escapeHtml(task.description)}</p>
            
            <div class="grid grid-cols-2 gap-4 mb-4">
                <div class="bg-gray-50 rounded-lg p-3 text-center">
                    <p class="text-sm text-gray-600">Points</p>
                    <p class="font-semibold text-primary">${task.reward}</p>
                </div>
                <div class="bg-gray-50 rounded-lg p-3 text-center">
                    <p class="text-sm text-gray-600">Target</p>
                    <p class="font-semibold text-gray-900">${task.target_count}</p>
                </div>
            </div>
            
            ${task.url ? `
                <div class="mb-4">
                    <p class="text-sm text-gray-600 mb-1">URL:</p>
                    <a href="${task.url}" target="_blank" class="text-sm text-blue-600 hover:text-blue-800 break-all">
                        ${escapeHtml(task.url)}
                    </a>
                </div>
            ` : ''}
            
            <div class="flex items-center justify-between pt-4 border-t border-gray-200">
                <div class="text-sm text-gray-500">
                    Created: ${task.formatted_date || new Date(task.created_at).toLocaleDateString()}
                </div>
                <div class="flex space-x-2">
                    <button onclick="editTask(${task.id})" 
                            class="text-blue-600 hover:text-blue-800 p-2 rounded-lg hover:bg-blue-50">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button onclick="deleteTask(${task.id}, '${escapeHtml(task.title)}')" 
                            class="text-red-600 hover:text-red-800 p-2 rounded-lg hover:bg-red-50">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
        </div>
    `;
}

// Filter tasks
function filterTasks() {
    const typeValue = typeFilter.value;
    const statusValue = statusFilter.value;
    const searchValue = searchInput.value.toLowerCase();
    
    let filtered = currentTasks.filter(task => {
        const matchesType = !typeValue || task.type === typeValue;
        const matchesStatus = !statusValue || 
            (statusValue === '1' && task.enabled) ||
            (statusValue === '0' && !task.enabled);
        const matchesSearch = !searchValue || 
            task.title.toLowerCase().includes(searchValue) ||
            task.description.toLowerCase().includes(searchValue);
        
        return matchesType && matchesStatus && matchesSearch;
    });
    
    renderTasks(filtered);
}

// Modal functions
function openTaskModal(task = null) {
    editingTaskId = task ? task.id : null;
    
    if (task) {
        document.getElementById('modalTitle').textContent = 'Edit Task';
        fillTaskForm(task);
    } else {
        document.getElementById('modalTitle').textContent = 'Create New Task';
        taskForm.reset();
        document.getElementById('taskId').value = '';
    }
    
    taskModal.classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeTaskModal() {
    taskModal.classList.add('hidden');
    document.body.style.overflow = 'auto';
    editingTaskId = null;
}

function closeDeleteModal() {
    deleteModal.classList.add('hidden');
    document.body.style.overflow = 'auto';
}

// Fill form with task data
function fillTaskForm(task) {
    document.getElementById('taskId').value = task.id;
    document.getElementById('taskTitle').value = task.title;
    document.getElementById('taskType').value = task.type;
    document.getElementById('taskDescription').value = task.description;
    document.getElementById('taskPoints').value = task.reward;
    document.getElementById('targetCount').value = task.target_count;
    document.getElementById('taskUrl').value = task.url || '';
    document.getElementById('isActive').checked = task.enabled;
}

// Handle task form submission
async function handleTaskSubmit(e) {
    e.preventDefault();
    
    const formData = new FormData(taskForm);
    const isEdit = editingTaskId !== null;
    
    saveBtn.disabled = true;
    saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Saving...';
    
    try {
        const endpoint = isEdit ? '../api/admin/update_task.php' : '../api/admin/create_task.php';
        const response = await fetch(endpoint, {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success || result.ok) {
            showSuccess(isEdit ? 'Task updated successfully' : 'Task created successfully');
            closeTaskModal();
            loadTasks(); // Reload tasks
        } else {
            showError(result.message || 'Failed to save task');
        }
    } catch (error) {
        console.error('Error saving task:', error);
        showError('Network error while saving task');
    } finally {
        saveBtn.disabled = false;
        saveBtn.innerHTML = '<i class="fas fa-save mr-2"></i>Save Task';
    }
}

// Edit task
function editTask(taskId) {
    const task = currentTasks.find(t => t.id === taskId);
    if (task) {
        openTaskModal(task);
    }
}

// Delete task
function deleteTask(taskId, taskTitle) {
    editingTaskId = taskId;
    deleteModal.querySelector('p').innerHTML = 
        `Are you sure you want to delete "<strong>${escapeHtml(taskTitle)}</strong>"? All associated user completions will also be removed.`;
    deleteModal.classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

// Confirm delete task
async function confirmDeleteTask() {
    if (!editingTaskId) return;
    
    confirmDelete.disabled = true;
    confirmDelete.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Deleting...';
    
    try {
        const formData = new FormData();
        formData.append('task_id', editingTaskId);
        formData.append('csrf', document.querySelector('input[name="csrf"]').value);
        
        const response = await fetch('../api/admin/delete_task.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success || result.ok) {
            showSuccess('Task deleted successfully');
            closeDeleteModal();
            loadTasks(); // Reload tasks
        } else {
            showError(result.message || 'Failed to delete task');
        }
    } catch (error) {
        console.error('Error deleting task:', error);
        showError('Network error while deleting task');
    } finally {
        confirmDelete.disabled = false;
        confirmDelete.innerHTML = '<i class="fas fa-trash mr-2"></i>Delete Task';
    }
}

// Utility functions
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

function showSuccess(message) {
    // Simple success notification
    const notification = document.createElement('div');
    notification.className = 'fixed top-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-50';
    notification.innerHTML = `<i class="fas fa-check mr-2"></i>${message}`;
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.remove();
    }, 3000);
}

function showError(message) {
    // Simple error notification
    const notification = document.createElement('div');
    notification.className = 'fixed top-4 right-4 bg-red-500 text-white px-6 py-3 rounded-lg shadow-lg z-50';
    notification.innerHTML = `<i class="fas fa-exclamation-triangle mr-2"></i>${message}`;
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.remove();
    }, 5000);
}
