<?php
require_once '../config/db.php';
require_once '../config/auth.php';

header('Content-Type: application/json');
requireLogin();

$method = $_SERVER['REQUEST_METHOD'];
$me = currentUser();

if ($method === 'GET') {
    $id = $_GET['id'] ?? null;
    if ($id) {
        if ($me['role'] === 'customer' && $id != $me['id']) {
            echo json_encode(['error' => 'Access denied']); exit;
        }
        $stmt = $conn->prepare("SELECT id, name, email, role, phone, address, created_at FROM users WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();
        if (!$user) { echo json_encode(['error' => 'User not found']); exit; }
        if ($me['role'] === 'staff' && $user['role'] === 'admin') {
            echo json_encode(['error' => 'Access denied']); exit;
        }
        echo json_encode($user);
    } else {
        if ($me['role'] === 'customer') {
            $stmt = $conn->prepare("SELECT id, name, email, role, phone, address, created_at FROM users WHERE id = ?");
            $stmt->bind_param("i", $me['id']);
            $stmt->execute();
            echo json_encode([$stmt->get_result()->fetch_assoc()]);
        } elseif ($me['role'] === 'staff') {
            $result = $conn->query("SELECT id, name, email, role, phone, address, created_at FROM users WHERE role = 'customer'");
            echo json_encode($result->fetch_all(MYSQLI_ASSOC));
        } else {
            $result = $conn->query("SELECT id, name, email, role, phone, address, created_at FROM users ORDER BY role, name");
            echo json_encode($result->fetch_all(MYSQLI_ASSOC));
        }
    }
    exit;
}

if ($method === 'POST') {
    if ($me['role'] === 'customer') {
        echo json_encode(['error' => 'Access denied']); exit;
    }

    $data     = json_decode(file_get_contents('php://input'), true);
    $name     = trim($data['name']     ?? '');
    $email    = trim($data['email']    ?? '');
    $phone    = trim($data['phone']    ?? '');
    $address  = trim($data['address']  ?? '');
    $password = $data['password']      ?? '';
    $role     = $data['role']          ?? 'customer';

    if ($me['role'] === 'staff' && $role !== 'customer') {
        echo json_encode(['error' => 'Staff can only add customers']); exit;
    }
    if ($role === 'admin') {
        echo json_encode(['error' => 'Cannot create admin accounts']); exit;
    }

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
    $stmt = $conn->prepare("INSERT INTO users (name, email, password, role, phone, address) VALUES (?,?,?,?,?,?)");
    $stmt->bind_param("ssssss", $name, $email, $hash, $role, $phone, $address);

    if ($stmt->execute()) {
        $newId = $conn->insert_id;
        logActivity($conn, $me['id'], 'Add User', "Added $role: $email");
        echo json_encode(['success' => true, 'id' => $newId]);
    } else {
        echo json_encode(['error' => 'Failed to create user']);
    }
    exit;
}

if ($method === 'PUT') {
    $data     = json_decode(file_get_contents('php://input'), true);
    $targetId = intval($data['id'] ?? 0);

    if (!$targetId) { echo json_encode(['error' => 'User ID required']); exit; }

    $stmt = $conn->prepare("SELECT role FROM users WHERE id = ?");
    $stmt->bind_param("i", $targetId);
    $stmt->execute();
    $target = $stmt->get_result()->fetch_assoc();
    if (!$target) { echo json_encode(['error' => 'User not found']); exit; }

    if ($me['role'] === 'customer' && $targetId != $me['id']) {
        echo json_encode(['error' => 'Access denied']); exit;
    }
    if ($me['role'] === 'staff' && $target['role'] !== 'customer' && $targetId != $me['id']) {
        echo json_encode(['error' => 'Staff can only edit customers or their own profile']); exit;
    }
    if ($me['role'] === 'staff' && $target['role'] === 'admin') {
        echo json_encode(['error' => 'Access denied']); exit;
    }

    $name    = trim($data['name']    ?? '');
    $phone   = trim($data['phone']   ?? '');
    $address = trim($data['address'] ?? '');
    $newPass = trim($data['password'] ?? '');

    if ($newPass) {
        if (strlen($newPass) < 8) {
            echo json_encode(['error' => 'Password must be at least 8 characters']); exit;
        }
        $hashed = password_hash($newPass, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET name=?, phone=?, address=?, password=? WHERE id=?");
        $stmt->bind_param("ssssi", $name, $phone, $address, $hashed, $targetId);
    } else {
        $stmt = $conn->prepare("UPDATE users SET name=?, phone=?, address=? WHERE id=?");
        $stmt->bind_param("sssi", $name, $phone, $address, $targetId);
    }

    if ($stmt->execute()) {
        logActivity($conn, $me['id'], 'Update User', "Updated user ID $targetId");
        echo json_encode(['success' => true, 'message' => 'User updated']);
    } else {
        echo json_encode(['error' => 'Update failed']);
    }
    exit;
}

if ($method === 'DELETE') {
    requireRole('admin');
    $data     = json_decode(file_get_contents('php://input'), true);
    $targetId = intval($data['id'] ?? 0);
    if ($targetId == $me['id']) { echo json_encode(['error' => 'Cannot delete yourself']); exit; }
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $targetId);
    if ($stmt->execute()) {
        logActivity($conn, $me['id'], 'Delete User', "Deleted user ID $targetId");
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['error' => 'Delete failed']);
    }
    exit;
}

echo json_encode(['error' => 'Method not allowed']);
