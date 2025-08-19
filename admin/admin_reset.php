<?php
/* ---------- BACKEND STUB (KEEP OR REPLACE WITH YOUR REAL LOGIC) ---------- */
$token = $_GET['token'] ?? '';
$isValid = $token !== '';   // replace with real token check
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $postedToken = $_POST['token']      ?? '';
    $newUser     = trim($_POST['user']  ?? '');
    $newPass     = $_POST['pass']       ?? '';
    $confirmPass = $_POST['confirm']    ?? '';

    // --- Your actual reset logic would go here ---
    // e.g. verify $postedToken, update DB, etc.
    if ($postedToken === $token && $newUser && $newPass && $newPass === $confirmPass) {
        $success = true;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width,initial-scale=1.0"/>
  <title>Admin – Reset Credentials | Watch4UC</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
</head>
<body class="bg-slate-100 flex items-center justify-center min-h-screen p-4">
  <div class="w-full max-w-md">
    <?php if (!$success): ?>
      <?php if (!$isValid): ?>
        <!-- INVALID / MISSING TOKEN -->
        <div class="bg-white rounded-xl shadow-2xl p-8 text-center">
          <i class="fas fa-exclamation-triangle text-red-500 text-5xl mb-4"></i>
          <h1 class="text-2xl font-bold text-slate-700 mb-2">Invalid or Expired Link</h1>
          <p class="text-slate-500 mb-6">The reset link is missing or no longer valid.</p>
          <a href="login.php"
             class="inline-block bg-slate-700 text-white px-6 py-2 rounded hover:bg-slate-800 transition">
            Back to Login
          </a>
        </div>
      <?php else: ?>
        <!-- RESET FORM -->
        <form action="admin_reset.php" method="POST" id="resetForm"
              class="bg-white rounded-xl shadow-2xl p-8 space-y-6">
          <h1 class="text-2xl font-bold text-center text-slate-700">Reset Admin Credentials</h1>

          <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>"/>

          <!-- New Username -->
          <div>
            <label for="user" class="block text-sm font-medium text-slate-600 mb-1">New Username</label>
            <input id="user" name="user" type="text" required
                   class="w-full px-4 py-2 border border-slate-300 rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none"/>
          </div>

          <!-- New Password -->
          <div>
            <label for="pass" class="block text-sm font-medium text-slate-600 mb-1">New Password</label>
            <input id="pass" name="pass" type="password" required
                   class="w-full px-4 py-2 border border-slate-300 rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none"/>
          </div>

          <!-- Confirm Password -->
          <div>
            <label for="confirm" class="block text-sm font-medium text-slate-600 mb-1">Confirm Password</label>
            <input id="confirm" name="confirm" type="password" required
                   class="w-full px-4 py-2 border border-slate-300 rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none"/>
          </div>

          <!-- Error message placeholder -->
          <p id="errorMsg" class="text-sm text-red-600 hidden"></p>

          <!-- Submit -->
          <button type="submit"
                  class="w-full bg-blue-600 text-white font-semibold py-2 rounded hover:bg-blue-700 transition disabled:opacity-50"
                  id="submitBtn">
            Reset Credentials
          </button>
        </form>
      <?php endif; ?>
    <?php else: ?>
      <!-- SUCCESS STATE -->
      <div class="bg-white rounded-xl shadow-2xl p-8 text-center">
        <i class="fas fa-check-circle text-green-500 text-5xl mb-4"></i>
        <h1 class="text-2xl font-bold text-slate-700 mb-2">Credentials Updated!</h1>
        <p class="text-slate-500 mb-6">Your username and password have been changed successfully.</p>
        <a href="login.php"
           class="inline-block bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700 transition">
          Back to Login
        </a>
      </div>
    <?php endif; ?>
  </div>

  <!-- Simple client-side validation -->
  <script>
  document.getElementById('resetForm')?.addEventListener('submit', e => {
    const pass  = document.getElementById('pass').value;
    const conf  = document.getElementById('confirm').value;
    const msg   = document.getElementById('errorMsg');
    const btn   = document.getElementById('submitBtn');

    msg.classList.add('hidden');
    btn.disabled = false;

    if (!document.getElementById('user').value) {
      msg.textContent = 'Username is required.';
      msg.classList.remove('hidden');
      e.preventDefault();
      return;
    }
    if (pass !== conf) {
      msg.textContent = 'Passwords do not match.';
      msg.classList.remove('hidden');
      e.preventDefault();
      return;
    }
    btn.disabled = true;
    btn.textContent = 'Processing…';
  });
  </script>
</body>
</html>