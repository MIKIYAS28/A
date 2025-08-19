<?php
session_start();
require '../db/db_connect.php'; // Adjust path as needed

if (!isset($_SESSION['admin_id'])) {
    header('HTTP/1.1 403 Forbidden');
    exit('Access denied');
}

$request_id = $_POST['request_id'];
$action = $_POST['action']; // 'approve' or 'reject'

// Get request details
$stmt = $conn->prepare("SELECT user_id FROM pubg_requests WHERE id = ?");
$stmt->bind_param("i", $request_id);
$stmt->execute();
$result = $stmt->get_result();
$request = $result->fetch_assoc();

if (!$request) {
    header('HTTP/1.1 404 Not Found');
    exit('Request not found');
}

// Update request status
$stmt = $conn->prepare("UPDATE pubg_requests SET status = ? WHERE id = ?");
$stmt->bind_param("si", $action, $request_id);
$stmt->execute();

// Create notification
$message = $action === 'approved' 
    ? "Your PUBG request has been approved! Enjoy your reward."
    : "Your PUBG request has been rejected. Please try again later.";

$stmt = $conn->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)");
$stmt->bind_param("is", $request['user_id'], $message);
$stmt->execute();

echo json_encode(['success' => true]);