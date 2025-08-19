<?php
session_start();
require_once __DIR__ . '/../../config.php';

$errors = [];
$out    = [];

// 1) DB connect
try {
  $out[] = "PDO connected ✅";
} catch (Throwable $e) {
  $errors[] = "PDO failed: " . $e->getMessage();
}

// 2) Fetch admin row
try {
  $stmt = $pdo->prepare("SELECT id, username, email, role, password FROM users WHERE LOWER(username)='surafel' OR LOWER(email)='mikiyasolana382@gmail.com' ORDER BY id DESC LIMIT 1");
  $stmt->execute();
  $u = $stmt->fetch(PDO::FETCH_ASSOC);
  if ($u) {
    $out[] = "Admin row found: id={$u['id']}, username={$u['username']}, email={$u['email']}, role={$u['role']}";
    $ok = password_verify("Sura12,3#@!", $u['password']);
    $out[] = "password_verify('Sura12,3#@!') => " . ($ok ? "MATCH ✅" : "NO MATCH ❌");
  } else {
    $errors[] = "No admin row found for username/email.";
  }
} catch (Throwable $e) {
  $errors[] = "Query error: " . $e->getMessage();
}

// 3) Show results
header('Content-Type: text/plain; charset=utf-8');
echo "=== Admin Login Diagnostic ===\n";
foreach ($out as $line)    echo "• $line\n";
foreach ($errors as $line) echo "✗ $line\n";
