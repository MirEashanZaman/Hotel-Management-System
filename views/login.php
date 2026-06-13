<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Hotel Management System — Login</title>
  <link rel="stylesheet" href="css/login.css">
</head>

<body>
  <div class="bg-pattern"></div>
  <div class="bg-lines"></div>
  <div class="login-wrap">
    <div class="hotel-brand">
      <div class="hotel-emblem"><span>H</span></div>
      <h1>Hotel Management</h1>
      <p>System</p>
    </div>
    <div class="login-card">
      <h2>Welcome Back</h2>
      <p class="subtitle">Sign in to your account</p>
      <div class="form-group">
        <label>Email Address</label>
        <input type="email" id="email" placeholder="Enter Your Mail" autocomplete="email">
      </div>
      <div class="form-group">
        <label>Password</label>
        <input type="password" id="password" placeholder="Enter Your Password" autocomplete="current-password">
      </div>
      <button class="btn-login" id="loginBtn" onclick="doLogin()">Sign In</button>
      <button class="btn-signup" onclick="window.location.href='index.php?route=signup'">New Customer? Sign Up</button>
      <div class="error-msg" id="errorMsg"></div>
    </div>
  </div>
  <script>
    fetch('api/session.php').then(r => r.json()).then(d => {
      if (d.loggedIn) window.location.href = 'index.php?route=dashboard';
    });
    document.addEventListener('keydown', e => { if (e.key === 'Enter') doLogin(); });
    async function doLogin() {
      const btn = document.getElementById('loginBtn');
      const err = document.getElementById('errorMsg');
      const email = document.getElementById('email').value.trim();
      const password = document.getElementById('password').value;
      err.classList.remove('show');
      if (!email || !password) {
        err.textContent = 'Please enter email and password.';
        err.classList.add('show'); return;
      }
      btn.disabled = true;
      btn.innerHTML = '<span class="loading-spinner"></span>Signing In...';
      try {
        const res = await fetch('api/login.php', {
          method: 'POST', headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ email, password })
        });
        const data = await res.json();
        if (data.success) {
          window.location.href = 'index.php?route=dashboard';
        } else {
          err.textContent = data.error || 'Login failed.';
          err.classList.add('show');
          btn.disabled = false; btn.innerHTML = 'Sign In';
        }
      } catch {
        err.textContent = 'Server error. Please try again.';
        err.classList.add('show');
        btn.disabled = false; btn.innerHTML = 'Sign In';
      }
    }
  </script>
</body>

</html>
