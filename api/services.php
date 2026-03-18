<?php
require_once '../config/db.php';
require_once '../config/auth.php';

header('Content-Type: application/json');
requireLogin();

$method = $_SERVER['REQUEST_METHOD'];
$me = currentUser();

if ($method === 'GET') {
    $type = $_GET['type'] ?? null; 

    if ($type === 'requests') {
        $bookingId = $_GET['booking_id'] ?? null;
        if ($me['role'] === 'customer') {
            $stmt = $conn->prepare("SELECT sr.*, s.name as service_name, s.price, s.category FROM service_requests sr JOIN services s ON sr.service_id = s.id JOIN bookings b ON sr.booking_id = b.id WHERE b.customer_id = ?");
            $stmt->bind_param("i", $me['id']);
            $stmt->execute();
            echo json_encode($stmt->get_result()->fetch_all(MYSQLI_ASSOC));
        } else {
            $where = $bookingId ? "WHERE sr.booking_id = " . intval($bookingId) : '';
            $result = $conn->query("SELECT sr.*, s.name as service_name, s.price, s.category, u.name as customer_name FROM service_requests sr JOIN services s ON sr.service_id = s.id JOIN bookings b ON sr.booking_id = b.id JOIN users u ON b.customer_id = u.id $where ORDER BY sr.requested_at DESC");
            echo json_encode($result->fetch_all(MYSQLI_ASSOC));
        }
    } else {
        $result = $conn->query("SELECT * FROM services ORDER BY category, name");
        echo json_encode($result->fetch_all(MYSQLI_ASSOC));
    }
    exit;
}

if ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $type = $data['type'] ?? 'request';

    if ($type === 'service') {
        requireRole('admin');
        $name     = trim($data['name'] ?? '');
        $desc     = trim($data['description'] ?? '');
        $price    = floatval($data['price'] ?? 0);
        $category = $data['category'] ?? 'other';
        $stmt = $conn->prepare("INSERT INTO services (name, description, price, category) VALUES (?,?,?,?)");
        $stmt->bind_param("ssds", $name, $desc, $price, $category);
        if ($stmt->execute()) { echo json_encode(['success' => true, 'id' => $conn->insert_id]); }
        else { echo json_encode(['error' => 'Insert failed']); }
    } else {
        $bookingId = intval($data['booking_id'] ?? 0);
        $serviceId = intval($data['service_id'] ?? 0);
        $qty       = intval($data['quantity'] ?? 1);
        $notes     = trim($data['notes'] ?? '');

        if ($me['role'] === 'customer') {
            $stmt = $conn->prepare("SELECT customer_id FROM bookings WHERE id = ?");
            $stmt->bind_param("i", $bookingId);
            $stmt->execute();
            $b = $stmt->get_result()->fetch_assoc();
            if (!$b || $b['customer_id'] != $me['id']) { echo json_encode(['error' => 'Access denied']); exit; }
        }

        $stmt = $conn->prepare("INSERT INTO service_requests (booking_id, service_id, quantity, notes) VALUES (?,?,?,?)");
        $stmt->bind_param("iiis", $bookingId, $serviceId, $qty, $notes);
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'id' => $conn->insert_id]);
        } else {
            echo json_encode(['error' => 'Request failed']);
        }
    }
    exit;
}

if ($method === 'PUT') {
    requireRole(['admin', 'staff']);
    $data = json_decode(file_get_contents('php://input'), true);
    $id     = intval($data['id'] ?? 0);
    $status = $data['status'] ?? 'pending';
    $stmt = $conn->prepare("UPDATE service_requests SET status=? WHERE id=?");
    $stmt->bind_param("si", $status, $id);
    if ($stmt->execute()) { echo json_encode(['success' => true]); }
    else { echo json_encode(['error' => 'Update failed']); }
    exit;
}

echo json_encode(['error' => 'Method not allowed']);
