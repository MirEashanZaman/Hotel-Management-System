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
        $stmt = $conn->prepare("SELECT * FROM rooms WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $room = $stmt->get_result()->fetch_assoc();
        echo json_encode($room ?: ['error' => 'Room not found']);
    } else {
        $status = $_GET['status'] ?? null;
        if ($status) {
            $stmt = $conn->prepare("SELECT * FROM rooms WHERE status = ? ORDER BY room_number");
            $stmt->bind_param("s", $status);
            $stmt->execute();
            echo json_encode($stmt->get_result()->fetch_all(MYSQLI_ASSOC));
        } else {
            $result = $conn->query("SELECT * FROM rooms ORDER BY room_number");
            echo json_encode($result->fetch_all(MYSQLI_ASSOC));
        }
    }
    exit;
}

if ($method === 'POST') {
    requireRole(['admin', 'staff']);
    $data = json_decode(file_get_contents('php://input'), true);
    $room_number = trim($data['room_number'] ?? '');
    $room_type   = $data['room_type'] ?? 'Single';
    $price       = floatval($data['price_per_night'] ?? 0);
    $capacity    = intval($data['capacity'] ?? 1);
    $description = trim($data['description'] ?? '');
    $amenities   = trim($data['amenities'] ?? '');
    $status      = $data['status'] ?? 'available';
    $floor       = intval($data['floor'] ?? 1);

    if (!$room_number || !$price) { echo json_encode(['error' => 'Room number and price required']); exit; }

    $stmt = $conn->prepare("INSERT INTO rooms (room_number, room_type, price_per_night, capacity, description, amenities, status, floor) VALUES (?,?,?,?,?,?,?,?)");
    $stmt->bind_param("ssdisssi", $room_number, $room_type, $price, $capacity, $description, $amenities, $status, $floor);
    if ($stmt->execute()) {
        logActivity($conn, $me['id'], 'Add Room', "Added room $room_number");
        echo json_encode(['success' => true, 'id' => $conn->insert_id]);
    } else {
        echo json_encode(['error' => 'Room number already exists or insert failed']);
    }
    exit;
}

if ($method === 'PUT') {
    requireRole(['admin', 'staff']);
    $data = json_decode(file_get_contents('php://input'), true);
    $id          = intval($data['id'] ?? 0);
    $room_type   = $data['room_type'] ?? 'Single';
    $price       = floatval($data['price_per_night'] ?? 0);
    $capacity    = intval($data['capacity'] ?? 1);
    $description = trim($data['description'] ?? '');
    $amenities   = trim($data['amenities'] ?? '');
    $status      = $data['status'] ?? 'available';
    $floor       = intval($data['floor'] ?? 1);

    $stmt = $conn->prepare("UPDATE rooms SET room_type=?, price_per_night=?, capacity=?, description=?, amenities=?, status=?, floor=? WHERE id=?");
    $stmt->bind_param("sdiisssi", $room_type, $price, $capacity, $description, $amenities, $status, $floor, $id);
    if ($stmt->execute()) {
        logActivity($conn, $me['id'], 'Update Room', "Updated room ID $id");
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['error' => 'Update failed']);
    }
    exit;
}

if ($method === 'DELETE') {
    requireRole('admin');
    $data = json_decode(file_get_contents('php://input'), true);
    $id = intval($data['id'] ?? 0);
    $stmt = $conn->prepare("DELETE FROM rooms WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        logActivity($conn, $me['id'], 'Delete Room', "Deleted room ID $id");
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['error' => 'Delete failed']);
    }
    exit;
}

echo json_encode(['error' => 'Method not allowed']);
