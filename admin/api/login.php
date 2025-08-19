<?php
// TOP of api/login.php – add these two lines
ini_set('display_errors', 1);
error_reporting(E_ALL);
// Removed accidental JavaScript line
// /Watch4UC/admin/api/login.php
// Enable error display for development
require_once __DIR__ . '/../../config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["success" => false, "message" => "Invalid request"]);
    exit;
}


// Accept either "username" or "email" or "identity"
$identity = trim($_POST['identity'] ?? ($_POST['username'] ?? ($_POST['email'] ?? '')));
$password = $_POST['password'] ?? '';

if ($identity === '' || $password === '') {
    echo json_encode(["success" => false, "message" => "Username/email and password required"]);
    exit;
}

try {
    // Look up by username or email (case-insensitive), must be admin
    $sql = "
        SELECT id, username, email, password, role
        FROM users
        WHERE (LOWER(username) = LOWER(:ident) OR LOWER(email) = LOWER(:ident))
          AND LOWER(role) = 'admin'
        LIMIT 1
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':ident' => $identity]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$admin) {
        echo json_encode(["success" => false, "message" => "Login failed. Please check your credentials."]);
        exit;
    }

    if (!password_verify($password, $admin['password'])) {
        echo json_encode(["success" => false, "message" => "Login failed. Please check your credentials."]);
        exit;
    }

    // Success: set admin session
    // Regenerate session id on successful login for security
    if (function_exists('session_regenerate_id')) {
        session_regenerate_id(true);
    }
    $_SESSION['admin_id']       = (int)$admin['id'];
    $_SESSION['admin_username'] = $admin['username'];
    $_SESSION['role']           = 'admin';

    echo json_encode(["success" => true, "message" => "Login successful", "redirect" => 'index.php']);
} catch (Throwable $e) {
    // For dev you can log: error_log("ADMIN LOGIN: ".$e->getMessage());
    echo json_encode(["success" => false, "message" => "Database error occurred"]);

}

