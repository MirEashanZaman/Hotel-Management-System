<?php
require_once '../config/db.php';
require_once '../config/auth.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Method not allowed']); exit;
}

$data     = json_decode(file_get_contents('php://input'), true);
$name     = trim($data['name']     ?? '');
$email    = trim($data['email']    ?? '');
$phone    = trim($data['phone']    ?? '');
$address  = trim($data['address']  ?? '');
$password = $data['password']      ?? '';

if (!$name || !$email || !$password) {
    echo json_encode(['error' => 'Name, email and password are required']); exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['error' => 'Invalid email address']); exit;
}

if (strlen($password) < 8) {
    echo json_encode(['error' => 'Password must be at least 8 characters']); exit;
}

$stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
if ($stmt->get_result()->num_rows > 0) {
    echo json_encode(['error' => 'This email is already registered']); exit;
}

$hash = password_hash($password, PASSWORD_DEFAULT);
$stmt = $conn->prepare("INSERT INTO users (name, email, password, role, phone, address) VALUES (?,?,'customer',?,?,?)");
$stmt = $conn->prepare("INSERT INTO users (name, email, password, role, phone, address) VALUES (?,?,?,'customer',?,?)");
$stmt->bind_param("sssss", $name, $email, $hash, $phone, $address);

if ($stmt->execute()) {
    $newId = $conn->insert_id;
    logActivity($conn, $newId, 'Register', "New customer registered: $email");
    echo json_encode(['success' => true, 'message' => 'Account created successfully']);
} else {
    echo json_encode(['error' => 'Registration failed. Please try again.']);
}
