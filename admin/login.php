 <?php
session_start();

// If already logged in as admin, redirect to dashboard
if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>

  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <meta http-equiv="Cache-Control" content="no-store, no-cache, must-revalidate">
  <meta http-equiv="Pragma" content="no-cache">
  <meta http-equiv="Expires" content="0">
  <title>SECURE LOGIN – AfroSystem</title>
  <style>
    /* ---------- RESET ---------- */
    *,*::before,*::after{box-sizing:border-box;margin:0;padding:0;font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,sans-serif}
    html,body{height:100%;background:#0e0e0e;overflow:hidden}

    /* ---------- MOTHERBOARD BACKGROUND ---------- */
    #pcb-bg{
      position:fixed;inset:0;z-index:-2;
      background:#0e0e0e url("data:image/svg+xml,%3Csvg width='100' height='100' xmlns='http://www.w3.org/2000/svg'%3E%3Cg stroke='%23181818' stroke-width='.5'%3E%3Cpath d='M0 50h100M50 0v100'/%3E%3Ccircle cx='20' cy='20' r='1.5' fill='%23202020'/%3E%3Ccircle cx='80' cy='80' r='1.5' fill='%23202020'/%3E%3C/g%3E%3C/svg%3E");
    }

    /* ---------- TRACES (animated from edges to center) ---------- */
    .trace{
      position:absolute;
      background:#00d2ff;
      filter:blur(1.5px);
      opacity:0;
      animation:drawIn 2s ease forwards;
    }
    @keyframes drawIn{
      0%{transform:scaleX(0) scaleY(0);opacity:0}
      50%{opacity:.8}
      100%{transform:scaleX(1) scaleY(1);opacity:.4}
    }

    /* ---------- WELCOME OVERLAY ---------- */
    #welcome{
      position:fixed;inset:0;display:grid;place-items:center;
      color:#fff;font-size:2.4rem;font-weight:700;letter-spacing:.08em;
      animation:fadeOut 1s 2.5s forwards;
      z-index:10;
    }
    @keyframes fadeOut{to{opacity:0;visibility:hidden}}

    /* ---------- GLASS PANEL ---------- */
    .glass-panel{
      position:absolute;top:50%;left:50%;
      transform:translate(-50%,-50%) scale(.9);
      width:360px;padding:2.8rem 2.2rem;
      background:rgba(10,10,10,.55);
      backdrop-filter:blur(12px) saturate(140%);
      border:2px solid rgba(0,210,255,.7);
      border-radius:12px;
      box-shadow:0 0 25px rgba(0,210,255,.25),inset 0 0 25px rgba(0,210,255,.05);
      opacity:0;
      animation:panelIn 1.5s 3.2s forwards;
    }
    @keyframes panelIn{to{transform:translate(-50%,-50%) scale(1);opacity:1}}

    /* ---------- TYPOGRAPHY & FORM ---------- */
    h1{margin-bottom:2rem;text-align:center;font-size:1.6rem;color:#fff;text-transform:uppercase;letter-spacing:.08em;text-shadow:0 0 8px #00d2ff}
    .field{position:relative;margin-bottom:1.6rem}
    .field label{position:absolute;top:-10px;left:8px;font-size:.9rem;color:#aaa;transition:.25s;background:transparent}
    .field input{width:100%;padding:.85rem .8rem .5rem 2.2rem;border:none;border-bottom:1px solid #444;background:transparent;color:#fff;font-size:1rem;outline:none}
    .field input:focus{border-color:#00d2ff}
    .line{position:absolute;left:0;bottom:0;height:2px;width:0;background:#00d2ff;transition:width .4s ease}
    .field input:focus + .line{width:100%}
    button{width:100%;padding:.9rem;border:none;border-radius:6px;background:linear-gradient(135deg,#0059ff 0%,#00d2ff 100%);color:#fff;font-weight:700;letter-spacing:.08em;cursor:pointer;transition:box-shadow .3s,transform .1s}
    button:hover{box-shadow:0 0 20px #00d2ff}
    button:active{transform:scale(.97)}
    .icon{position:absolute;left:.7rem;top:.9rem;font-size:1.1rem;pointer-events:none}

    /* ---------- RESPONSIVE ---------- */
    @media(max-width:480px){
      .glass-panel{width:90%;max-width:320px;padding:2rem 1.5rem}
      h1{font-size:1.35rem}
    }
  </style>
</head>
<body>

  <!-- animated traces -->
  <div class="trace" style="top:0;left:50%;width:2px;height:50%;transform-origin:top"></div>
  <div class="trace" style="bottom:0;left:50%;width:2px;height:50%;transform-origin:bottom"></div>
  <div class="trace" style="left:0;top:50%;height:2px;width:50%;transform-origin:left"></div>
  <div class="trace" style="right:0;top:50%;height:2px;width:50%;transform-origin:right"></div>

  <!-- welcome overlay -->
  <div id="welcome">Welcome Sura</div>

  <!-- login panel -->

      <!-- login panel -->
<main class="glass-panel">
  <h1>SECURE LOGIN</h1>
  <form action="api/login.php" method="post" id="loginForm">
    <div class="field">
      <span class="icon">👤</span>
 <input name="email" placeholder="E-mail" required>    
   <span class="line"></span>
    </div>
    <div class="field">
      <span class="icon">🔒</span>
  <input type="password" name="password" placeholder="Password" required>
      <span class="line"></span>
    </div>
    <button type="submit">LOG IN</button>
  </form>

  <!-- NEW: forgot link triggers modal -->
  <p style="text-align:center;margin-top:1.2rem;font-size:.85rem">
    <a href="#" id="forgotLink" style="color:#00d2ff;text-decoration:none">Forgot username or password?</a>
  </p>
</main>

<!-- NEW: reset modal (hidden by default) -->
<div id="resetModal" class="modal">
  <div class="modal-content glass-panel" style="width:340px">
    <span class="close">&times;</span>
    <h2 style="font-size:1.3rem;margin-bottom:1rem;text-align:center">Reset Credentials</h2>
    <form id="resetForm">
      <div class="field">
        <span class="icon">✉️</span>
        <input type="email" id="resetEmail" required placeholder="Enter admin e-mail"/>
        <span class="line"></span>
      </div>
      <button type="submit">Send Reset Link</button>
    </form>
    <p id="resetMsg" style="color:#0f0;margin-top:.8rem;text-align:center;visibility:hidden">✓ Check your inbox!</p>
  </div>
</div>

<!-- CSS for the modal overlay -->
<style>
.modal{
  position:fixed;inset:0;
  background:rgba(0,0,0,.7);
  display:flex;align-items:center;justify-content:center;
  z-index:100;
  opacity:0;visibility:hidden;
  transition:.3s;
}
.modal.show{opacity:1;visibility:visible}
.close{
  position:absolute;top:.6rem;right:1rem;
  font-size:1.5rem;color:#00d2ff;cursor:pointer;
}
</style>

  <script>
    // Modal / reset form handler (unchanged behavior)
    (() => {
      const modal   = document.getElementById('resetModal');
      const forgot  = document.getElementById('forgotLink');
      const close   = document.querySelector('.close');
      const form    = document.getElementById('resetForm');
      const msg     = document.getElementById('resetMsg');

      forgot.onclick = e => { e.preventDefault(); modal.classList.add('show'); };
      close.onclick  = () => modal.classList.remove('show');
      window.onclick = e => { if (e.target === modal) modal.classList.remove('show'); };

      form.addEventListener('submit', e => {
        e.preventDefault();
        const email = document.getElementById('resetEmail').value.trim();

        fetch('admin/api/reset.php', {
          method: 'POST',
          headers: {'Content-Type':'application/x-www-form-urlencoded'},
          body: 'email=' + encodeURIComponent(email)
        })
        .then(r => r.json())
        .then(data => {
          if (data.sent) {
            msg.style.visibility = 'visible';
            form.reset();
          } else {
            alert(data.error || 'Error sending reset link');
          }
        });
      });
    })();

    // Single, modern login handler: submit form via fetch and follow JSON redirect
    document.getElementById('loginForm').addEventListener('submit', async (e) => {
      e.preventDefault();
      const fd = new FormData(e.target);
      try {
        const res = await fetch('api/login.php', { method: 'POST', body: fd, credentials: 'same-origin' });
        // If server responds with a redirect header (rare for JSON endpoints), follow it
        if (res.redirected) {
          window.location.href = res.url;
          return;
        }
        const data = await res.json();
        if (data.success) {
          // Prefer server-provided redirect, fallback to admin index
          const dest = data.redirect || 'index.php';
          window.location.href = dest;
        } else {
          alert(data.error || data.message || 'Login failed. Please check your credentials.');
        }
      } catch (err) {
        console.error('Login error:', err);
        alert('Network or server error during login.');
      }
    });
  </script>
</body>
</html>