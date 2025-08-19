<?php
require_once __DIR__ . '/../config.php';
header('Content-Type: text/plain; charset=utf-8');

echo "Admin credentials diagnostic\n";
echo "========================\n\n";

try {
    // Detect which password column exists
    $cols = [];
    $r = $pdo->query("SHOW COLUMNS FROM users LIKE 'password'");
    if ($r && $r->fetch()) $cols[] = 'password';
    $r = $pdo->query("SHOW COLUMNS FROM users LIKE 'password_hash'");
    if ($r && $r->fetch()) $cols[] = 'password_hash';

    if (empty($cols)) {
        echo "No password columns ('password' or 'password_hash') found in users table.\n";
        exit;
    }

    // Build select list dynamically
    $selectCols = ['id','username','email','role'];
    foreach ($cols as $c) $selectCols[] = $c;
    $sql = 'SELECT ' . implode(', ', $selectCols) . " FROM users WHERE LOWER(role) = 'admin' ORDER BY id DESC LIMIT 1";

    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$admin) {
        echo "No admin user found in users table.\n";
        exit;
    }

    echo "Found admin row:\n";
    foreach ($admin as $k => $v) {
        echo sprintf("%s: %s\n", $k, $v === null ? '<NULL>' : $v);
    }

    echo "\nPassword verification checks:\n";
    $tests = [
        'Sura12,3#@!',
        '8suraman"50@$&8',
        'Sura12,3#@! ', // trailing space test
    ];

    foreach ($tests as $p) {
        $ok = false;
        foreach ($cols as $c) {
            if (!empty($admin[$c]) && password_verify($p, $admin[$c])) {
                $ok = true;
                break;
            }
        }
        echo sprintf("Test password: %s => %s\n", $p, $ok ? 'MATCH' : 'NO MATCH');
    }

    echo "\nNotes:\n";
    echo "- If none of the tests match, the stored hash doesn't correspond to the passwords tested.\n";
    echo "- The app contains mixed references to 'password' and 'password_hash'; pick one and make code + DB consistent.\n";
    echo "- Easiest fixes:\n";
    echo "  * Update create_admin_user.php to write into the 'password' column (or)\n";
    echo "  * Add a 'password_hash' column to the DB and populate it from 'password' if you prefer that name.\n";
    echo "- To recreate admin with a known password, run create_admin_user.php after adjusting it to match your DB column.\n";

} catch (Throwable $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
