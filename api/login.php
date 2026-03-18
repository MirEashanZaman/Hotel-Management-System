<?php
require_once '../config/db.php';
require_once '../config/auth.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Method not allowed']); exit;
}

$data     = json_decode(file_get_contents('php://input'), true);
$email    = trim($data['email']    ?? '');
$password = trim($data['password'] ?? '');

if (!$email || !$password) {
    echo json_encode(['error' => 'Email and password required']); exit;
}

$stmt = $conn->prepare("SELECT id, name, email, password, role FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if (!$user) {
    echo json_encode(['error' => 'Invalid email or password']); exit;
}

$stored   = $user['password'];
$verified = false;

if (strpos($stored, 'sha256:') === 0) {
    $hash = substr($stored, 7);
    if (hash('sha256', $password) === $hash) {
        $verified = true;
        $bcrypt = password_hash($password, PASSWORD_DEFAULT);
        $upd = $conn->prepare("UPDATE users SET password=? WHERE id=?");
        $upd->bind_param("si", $bcrypt, $user['id']);
        $upd->execute();
    }
}
elseif (strpos($stored, 'NEEDS_HASH:') === 0) {
    $plain = substr($stored, 11);
    if ($password === $plain) {
        $verified = true;
        $bcrypt = password_hash($password, PASSWORD_DEFAULT);
        $upd = $conn->prepare("UPDATE users SET password=? WHERE id=?");
        $upd->bind_param("si", $bcrypt, $user['id']);
        $upd->execute();
    }
}
elseif (strpos($stored, '$2y$') === 0 || strpos($stored, '$2a$') === 0) {
    $verified = password_verify($password, $stored);
}
else {
    $verified = ($password === $stored);
    if ($verified) {
        $bcrypt = password_hash($password, PASSWORD_DEFAULT);
        $upd = $conn->prepare("UPDATE users SET password=? WHERE id=?");
        $upd->bind_param("si", $bcrypt, $user['id']);
        $upd->execute();
    }
}

if (!$verified) {
    echo json_encode(['error' => 'Invalid email or password']); exit;
}

$_SESSION['user_id'] = $user['id'];
$_SESSION['name']    = $user['name'];
$_SESSION['role']    = $user['role'];
$_SESSION['email']   = $user['email'];

logActivity($conn, $user['id'], 'Login', 'User logged in from ' . ($_SERVER['REMOTE_ADDR'] ?? ''));

echo json_encode([
    'success' => true,
    'user' => [
        'id'    => $user['id'],
        'name'  => $user['name'],
        'role'  => $user['role'],
        'email' => $user['email']
    ]
]);
