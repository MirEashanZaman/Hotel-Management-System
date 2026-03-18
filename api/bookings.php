<?php
require_once '../config/db.php';
require_once '../config/auth.php';

header('Content-Type: application/json');
requireLogin();

$method = $_SERVER['REQUEST_METHOD'];
$me = currentUser();

if ($method === 'GET') {
    $id = $_GET['id'] ?? null;
    $customerId = $_GET['customer_id'] ?? null;

    if ($id) {
        $stmt = $conn->prepare("
            SELECT b.*, r.room_number, r.room_type, r.price_per_night,
                   u.name as customer_name, u.email as customer_email, u.phone as customer_phone
            FROM bookings b
            JOIN rooms r ON b.room_id = r.id
            JOIN users u ON b.customer_id = u.id
            WHERE b.id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $booking = $stmt->get_result()->fetch_assoc();
        if (!$booking) { echo json_encode(['error' => 'Booking not found']); exit; }
        if ($me['role'] === 'customer' && $booking['customer_id'] != $me['id']) {
            echo json_encode(['error' => 'Access denied']); exit;
        }
        echo json_encode($booking);
    } else {
        if ($me['role'] === 'customer') {
            $stmt = $conn->prepare("
                SELECT b.*, r.room_number, r.room_type, r.price_per_night,
                       p.payment_status, p.payment_method, p.amount as paid_amount
                FROM bookings b
                JOIN rooms r ON b.room_id = r.id
                LEFT JOIN payments p ON p.booking_id = b.id
                WHERE b.customer_id = ? ORDER BY b.booked_at DESC");
            $stmt->bind_param("i", $me['id']);
            $stmt->execute();
            echo json_encode($stmt->get_result()->fetch_all(MYSQLI_ASSOC));
        } else {
            $where = '';
            if ($customerId) $where = "WHERE b.customer_id = " . intval($customerId);
            $result = $conn->query("
                SELECT b.*, r.room_number, r.room_type, r.price_per_night,
                       u.name as customer_name, u.email as customer_email
                FROM bookings b
                JOIN rooms r ON b.room_id = r.id
                JOIN users u ON b.customer_id = u.id
                $where ORDER BY b.booked_at DESC");
            echo json_encode($result->fetch_all(MYSQLI_ASSOC));
        }
    }
    exit;
}

if ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    if ($me['role'] === 'customer') {
        $customerId = $me['id'];
    } else {
        $customerId = intval($data['customer_id'] ?? $me['id']);
    }

    $roomId   = intval($data['room_id'] ?? 0);
    $checkIn  = $data['check_in'] ?? '';
    $checkOut = $data['check_out'] ?? '';
    $requests = trim($data['special_requests'] ?? '');

    if (!$roomId || !$checkIn || !$checkOut) {
        echo json_encode(['error' => 'Room, check-in and check-out required']); exit;
    }

    $ci = new DateTime($checkIn);
    $co = new DateTime($checkOut);
    if ($co <= $ci) { echo json_encode(['error' => 'Check-out must be after check-in']); exit; }
    $nights = $ci->diff($co)->days;

    $stmt = $conn->prepare("SELECT price_per_night, status FROM rooms WHERE id = ?");
    $stmt->bind_param("i", $roomId);
    $stmt->execute();
    $room = $stmt->get_result()->fetch_assoc();
    if (!$room) { echo json_encode(['error' => 'Room not found']); exit; }
    if ($room['status'] === 'maintenance') { echo json_encode(['error' => 'Room is under maintenance']); exit; }

    $stmt = $conn->prepare("
        SELECT id FROM bookings
        WHERE room_id = ? AND status NOT IN ('cancelled','checked_out')
        AND check_in < ? AND check_out > ?");
    $stmt->bind_param("iss", $roomId, $checkOut, $checkIn);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        echo json_encode(['error' => 'Room is not available for selected dates']); exit;
    }

    $total = $room['price_per_night'] * $nights;
    $stmt = $conn->prepare("INSERT INTO bookings (customer_id, room_id, check_in, check_out, total_price, status, special_requests) VALUES (?,?,?,?,?,'pending',?)");
    $stmt->bind_param("iissds", $customerId, $roomId, $checkIn, $checkOut, $total, $requests);
    if ($stmt->execute()) {
        $bookingId = $conn->insert_id;
        if ($checkIn === date('Y-m-d')) {
            $conn->query("UPDATE rooms SET status='occupied' WHERE id=$roomId");
        }
        logActivity($conn, $me['id'], 'Create Booking', "Booking #$bookingId for room $roomId");
        echo json_encode(['success' => true, 'id' => $bookingId, 'total_price' => $total]);
    } else {
        echo json_encode(['error' => 'Booking failed']);
    }
    exit;
}

if ($method === 'PUT') {
    $data = json_decode(file_get_contents('php://input'), true);
    $id = intval($data['id'] ?? 0);

    $stmt = $conn->prepare("SELECT * FROM bookings WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $booking = $stmt->get_result()->fetch_assoc();
    if (!$booking) { echo json_encode(['error' => 'Booking not found']); exit; }

    if ($me['role'] === 'customer' && $booking['customer_id'] != $me['id']) {
        echo json_encode(['error' => 'Access denied']); exit;
    }

    if ($me['role'] === 'customer') {
        $requests = trim($data['special_requests'] ?? $booking['special_requests']);
        $status   = $data['status'] ?? $booking['status'];
        if (!in_array($status, ['confirmed', 'cancelled'])) {
            echo json_encode(['error' => 'You can only cancel or keep the booking']); exit;
        }
        $stmt = $conn->prepare("UPDATE bookings SET special_requests=?, status=? WHERE id=?");
        $stmt->bind_param("ssi", $requests, $status, $id);
    } else {
        $status   = $data['status'] ?? $booking['status'];
        $requests = trim($data['special_requests'] ?? $booking['special_requests']);
        $stmt = $conn->prepare("UPDATE bookings SET status=?, special_requests=? WHERE id=?");
        $stmt->bind_param("ssi", $status, $requests, $id);

        if ($status === 'checked_in') {
            $conn->query("UPDATE rooms SET status='occupied' WHERE id=" . $booking['room_id']);
        } elseif (in_array($status, ['checked_out', 'cancelled'])) {
            $conn->query("UPDATE rooms SET status='available' WHERE id=" . $booking['room_id']);
        }
    }

    if ($stmt->execute()) {
        logActivity($conn, $me['id'], 'Update Booking', "Updated booking #$id to $status");
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
    $stmt = $conn->prepare("DELETE FROM bookings WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['error' => 'Delete failed']);
    }
    exit;
}

echo json_encode(['error' => 'Method not allowed']);
