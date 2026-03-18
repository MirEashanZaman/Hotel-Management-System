<?php

require_once 'config/db.php';

$errors = [];
$success = [];

$hash = password_hash('12345678', PASSWORD_DEFAULT);

$wrongHash = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';
$stmt = $conn->prepare("UPDATE users SET password = ? WHERE email IN ('eshan@admin.com','milton@gmail.com','tanjim@gmail.com')");
$stmt->bind_param("s", $hash);
if ($stmt->execute() && $stmt->affected_rows > 0) {
    $success[] = "✓ Passwords updated successfully for all 3 users.";
} else {
    $errors[] = "✗ Password update failed or users not found. Make sure you imported hotel.sql first.";
}

$result = $conn->query("SELECT name, email, role FROM users ORDER BY id");
$users = $result->fetch_all(MYSQLI_ASSOC);

$tables = ['users','rooms','bookings','payments','services','service_requests','activity_logs'];
$missing = [];
foreach ($tables as $t) {
    $r = $conn->query("SHOW TABLES LIKE '$t'");
    if ($r->num_rows === 0) $missing[] = $t;
}
if (empty($missing)) {
    $success[] = "✓ All " . count($tables) . " tables present.";
} else {
    $errors[] = "✗ Missing tables: " . implode(', ', $missing);
}

$roomCount = $conn->query("SELECT COUNT(*) c FROM rooms")->fetch_assoc()['c'];
$success[] = "✓ $roomCount rooms in database.";

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Hotel Setup</title>
<style>
  @import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600&display=swap');
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
  body { background: #0d0d0d; color: #e8e0d0; font-family: 'Montserrat', sans-serif; display: flex; align-items: center; justify-content: center; min-height: 100vh; padding: 20px; }
  .box { background: #161616; border: 1px solid rgba(201,168,76,0.2); padding: 40px; max-width: 580px; width: 100%; }
  h1 { font-size: 22px; color: #c9a84c; margin-bottom: 6px; }
  p.sub { font-size: 11px; color: #5a5248; letter-spacing: 1px; margin-bottom: 28px; }
  .msg { padding: 10px 14px; font-size: 12px; margin-bottom: 10px; border: 1px solid; }
  .ok  { color: #4cc97a; border-color: rgba(76,201,122,0.3); background: rgba(76,201,122,0.07); }
  .err { color: #e05a5a; border-color: rgba(224,90,90,0.3);  background: rgba(224,90,90,0.07); }
  .section { margin-top: 24px; padding-top: 20px; border-top: 1px solid rgba(201,168,76,0.1); }
  .section h2 { font-size: 12px; letter-spacing: 2px; text-transform: uppercase; color: #c9a84c; margin-bottom: 14px; }
  table { width: 100%; border-collapse: collapse; }
  th, td { padding: 9px 12px; text-align: left; border-bottom: 1px solid rgba(255,255,255,0.05); font-size: 12px; }
  th { color: #c9a84c; font-size: 10px; letter-spacing: 2px; text-transform: uppercase; }
  td { color: #b0a898; }
  .badge { display: inline-block; padding: 2px 8px; font-size: 9px; letter-spacing: 1px; text-transform: uppercase; border: 1px solid; }
  .badge-gold  { color: #c9a84c; border-color: rgba(201,168,76,0.4); }
  .badge-blue  { color: #4c8ec9; border-color: rgba(76,142,201,0.4); }
  .badge-green { color: #4cc97a; border-color: rgba(76,201,122,0.4); }
  .btn { display: inline-block; margin-top: 24px; padding: 13px 28px; border: 1px solid #c9a84c; color: #c9a84c; text-decoration: none; font-size: 11px; letter-spacing: 3px; text-transform: uppercase; transition: all 0.2s; cursor: pointer; background: transparent; }
  .btn:hover { background: #c9a84c; color: #0d0d0d; }
  .warn { background: rgba(224,146,90,0.08); border: 1px solid rgba(224,146,90,0.3); color: #e0925a; padding: 12px 14px; font-size: 11px; margin-top: 20px; line-height: 1.7; }
</style>
</head>
<body>
<div class="box">
  <h1>Hotel Management System</h1>
  <p class="sub">Setup & Initialization</p>

  <?php foreach ($success as $s): ?>
    <div class="msg ok"><?= $s ?></div>
  <?php endforeach; ?>
  <?php foreach ($errors as $e): ?>
    <div class="msg err"><?= $e ?></div>
  <?php endforeach; ?>

  <?php if (!empty($users)): ?>
  <div class="section">
    <h2>Registered Users</h2>
    <table>
      <thead><tr><th>Name</th><th>Email</th><th>Role</th><th>Password</th></tr></thead>
      <tbody>
        <?php foreach ($users as $u): ?>
        <tr>
          <td><?= htmlspecialchars($u['name']) ?></td>
          <td><?= htmlspecialchars($u['email']) ?></td>
          <td>
            <span class="badge <?= $u['role']==='admin'?'badge-gold':($u['role']==='staff'?'badge-blue':'badge-green') ?>">
              <?= $u['role'] ?>
            </span>
          </td>
          <td style="color:#5a5248">12345678</td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php endif; ?>

  <?php if (empty($errors)): ?>
    <a href="index.html" class="btn">Go to Login →</a>
    <div class="warn">
      ⚠ <strong>Security Notice:</strong> Delete or restrict access to <code>setup.php</code> after setup is complete. This file resets all passwords to <code>12345678</code>.
    </div>
  <?php else: ?>
    <div class="warn">
      Please import <code>hotel.sql</code> into phpMyAdmin first, then refresh this page.
    </div>
  <?php endif; ?>
</div>
</body>
</html>
