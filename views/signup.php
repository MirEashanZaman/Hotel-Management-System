<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Hotel Management System — Sign Up</title>
  <link rel="stylesheet" href="css/signup.css">
</head>

<body>
  <div class="bg-pattern"></div>
  <div class="bg-lines"></div>
  <div class="wrap">
    <div class="hotel-brand">
      <div class="hotel-emblem"><span>H</span></div>
      <h1>Hotel Management</h1>
      <p>System</p>
    </div>
    <div class="card">
      <h2>Sign Up</h2>
      <p class="sub">Register as a new customer</p>
      <div class="form-grid">
        <div class="form-group full">
          <label>Full Name</label>
          <input type="text" id="name" placeholder="Your full name">
        </div>
        <div class="form-group full">
          <label>Email Address</label>
          <input type="email" id="email" placeholder="your@email.com">
        </div>
        <div class="form-group">
          <label>Phone</label>
          <input type="tel" id="phone" placeholder="+880...">
        </div>
        <div class="form-group">
          <label>Address</label>
          <input type="text" id="address" placeholder="City, Country">
        </div>
        <div class="form-group">
          <label>Password</label>
          <input type="password" id="password" placeholder="Min 8 characters">
          <span class="password-hint">Minimum 8 characters</span>
        </div>
        <div class="form-group">
          <label>Confirm Password</label>
          <input type="password" id="confirm" placeholder="Repeat password">
        </div>
      </div>
      <button class="btn" id="regBtn" onclick="doSignup()">Sign Up</button>
      <div class="alert alert-error" id="errMsg"></div>
      <div class="alert alert-success" id="okMsg"></div>
      <button class="btn-back" onclick="window.location.href='index.php?route=login'">Back to Sign In</button>
    </div>
  </div>
  <script>
    fetch('api/session.php').then(r => r.json()).then(d => {
      if (d.loggedIn) window.location.href = 'index.php?route=dashboard';
    });
    async function doSignup() {
      const btn = document.getElementById('regBtn');
      const err = document.getElementById('errMsg');
      const ok = document.getElementById('okMsg');
      err.classList.remove('show');
      ok.classList.remove('show');
      const name = document.getElementById('name').value.trim();
      const email = document.getElementById('email').value.trim();
      const phone = document.getElementById('phone').value.trim();
      const address = document.getElementById('address').value.trim();
      const password = document.getElementById('password').value;
      const confirm = document.getElementById('confirm').value;
      if (!name || !email || !password || !confirm) { err.textContent = 'Name, email and password are required.'; err.classList.add('show'); return; }
      if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) { err.textContent = 'Please enter a valid email address.'; err.classList.add('show'); return; }
      if (password.length < 8) { err.textContent = 'Password must be at least 8 characters.'; err.classList.add('show'); return; }
      if (password !== confirm) { err.textContent = 'Passwords do not match.'; err.classList.add('show'); return; }
      btn.disabled = true;
      btn.innerHTML = '<span class="loading-spinner"></span>Creating Account...';
      try {
        const res = await fetch('api/signup.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ name, email, phone, address, password }) });
        const data = await res.json();
        if (data.success) {
          ok.textContent = 'Account created! Redirecting to login...';
          ok.classList.add('show');
          setTimeout(() => window.location.href = 'index.php?route=login', 2000);
        } else {
          err.textContent = data.error || 'Sign up failed.';
          err.classList.add('show');
          btn.disabled = false;
          btn.innerHTML = 'Sign Up';
        }
      } catch {
        err.textContent = 'Server error. Please try again.';
        err.classList.add('show');
        btn.disabled = false;
        btn.innerHTML = 'Sign Up';
      }
    }
  </script>
</body>

</html>
