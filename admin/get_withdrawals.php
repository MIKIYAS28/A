<?php
header('Content-Type: application/json');
require_once 'db_connection.php';

// Simple admin authentication
if (!isset($_SERVER['PHP_AUTH_USER']) || 
    $_SERVER['PHP_AUTH_USER'] !== 'admin' || 
    $_SERVER['PHP_AUTH_PW'] !== 'your_secure_password') {
    header('WWW-Authenticate: Basic realm="Admin Dashboard"');
    header('HTTP/1.0 401 Unauthorized');
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

try {
    $status = $_GET['status'] ?? 'all';
    
    $query = "SELECT 
                wr.id, 
                u.username, 
                u.email,
                wr.amount,
                wr.currency,
                wr.method,
                wr.bank_name,
                wr.account_number,
                wr.phone_number,
                wr.account_name,
                wr.status,
                wr.created_at
              FROM withdrawal_requests wr
              JOIN users u ON wr.user_id = u.id";
    
    if ($status !== 'all') {
        $query .= " WHERE wr.status = :status";
    }
    
    $query .= " ORDER BY wr.created_at DESC";
    
    $stmt = $pdo->prepare($query);
    
    if ($status !== 'all') {
        $stmt->bindParam(':status', $status);
    }
    
    $stmt->execute();
    $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode(['success' => true, 'data' => $requests]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Database error']);
}
?>