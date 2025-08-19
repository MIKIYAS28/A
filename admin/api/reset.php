<?php
require_once '../config.php'; // DB connection

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["error" => "Invalid request method"]);
    exit;
}

$email = trim($_POST['email'] ?? '');

// Only allow the specific admin email
if ($email !== 'mikiyasolana382@gmail.com') {
    echo json_encode(["error" => "Email not recognized"]);
    exit;
}

// Generate token
$token = bin2hex(random_bytes(32));
$expires = date('Y-m-d H:i:s', strtotime('+15 minutes'));

// Store token
$stmt = $pdo->prepare("INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, ?)");
$stmt->execute([$email, $token, $expires]);

// Send email with PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require '../vendor/autoload.php';

$mail = new PHPMailer(true);

try {
    $mail->setFrom('mikiyasolana382@gmail.com', 'Watch4UC Admin');
    $mail->addAddress($email);
    $mail->isHTML(true);
    $mail->Subject = 'Admin Password Reset';
    $resetLink = "https://yourdomain.com/admin_reset.php?token=" . urlencode($token);
    $mail->Body = "<p>Click the link below to reset your username and password:</p>
                   <p><a href='$resetLink'>$resetLink</a></p>
                   <p>This link expires in 15 minutes.</p>";

    $mail->send();
    echo json_encode(["success" => "Reset link sent to your email"]);
} catch (Exception $e) {
    echo json_encode(["error" => "Email sending failed: {$mail->ErrorInfo}"]);
}
