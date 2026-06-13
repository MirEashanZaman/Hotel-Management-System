<?php

require_once 'config/db.php';

$errors = [];
$success = [];

$colCheck = $conn->query("SHOW COLUMNS FROM rooms LIKE 'image_url'")->fetch();
if (!$colCheck) {
    try {
        $conn->query("ALTER TABLE rooms ADD COLUMN image_url VARCHAR(255) DEFAULT NULL");
        $success[] = "✓ Added image_url column to rooms table.";
    } catch (PDOException $e) {
        $errors[] = "✗ Failed to add image_url column: " . $e->getMessage();
    }
}

$userColCheck = $conn->query("SHOW COLUMNS FROM users LIKE 'avatar_url'")->fetch();
if (!$userColCheck) {
    try {
        $conn->query("ALTER TABLE users ADD COLUMN avatar_url VARCHAR(255) DEFAULT NULL");
        $success[] = "✓ Added avatar_url column to users table.";
    } catch (PDOException $e) {
        $errors[] = "✗ Failed to add avatar_url column: " . $e->getMessage();
    }
}

$hash = password_hash('12345678', PASSWORD_DEFAULT);

$stmt = $conn->prepare("UPDATE users SET password = ? WHERE email IN ('eshan@admin.com','milton@gmail.com','tanjim@gmail.com')");
if ($stmt->execute([$hash]) && $stmt->rowCount() > 0) {
    $success[] = "✓ Passwords updated successfully for all 3 users.";
} else {
    $errors[] = "✗ Password update failed or users not found. Make sure you imported hotel.sql first.";
}

$stmt = $conn->query("SELECT name, email, role FROM users ORDER BY id");
$users = $stmt->fetchAll();

$tables = ['users','rooms','bookings','payments','services','service_requests','activity_logs'];
$missing = [];
foreach ($tables as $t) {
    $r = $conn->query("SHOW TABLES LIKE '$t'")->fetch();
    if (!$r) $missing[] = $t;
}
if (empty($missing)) {
    $success[] = "✓ All " . count($tables) . " tables present.";
} else {
    $errors[] = "✗ Missing tables: " . implode(', ', $missing);
}

$roomCount = $conn->query("SELECT COUNT(*) c FROM rooms")->fetch()['c'];
$success[] = "✓ $roomCount rooms in database.";

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Hotel Setup</title>
<link rel="stylesheet" href="css/setup.css">
</head>
<body>
<div class="box">
  <h1>Hotel Management System</h1>
  <p class="sub">Setup &amp; Initialization</p>

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
          <td class="password-muted">12345678</td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php endif; ?>

  <?php if (empty($errors)): ?>
    <a href="index.php" class="btn">Go to Login →</a>
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
