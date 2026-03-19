<?php
require_once '../config/db.php';
require_once '../config/auth.php';

header('Content-Type: application/json');
requireLogin();

$method = $_SERVER['REQUEST_METHOD'];
$me = currentUser();

if ($method === 'GET') {
    $roomId    = $_GET['room_id']    ?? null;
    $bookingId = $_GET['booking_id'] ?? null;

    if ($bookingId) {
        $stmt = $conn->prepare("
            SELECT r.*, u.name as customer_name
            FROM reviews r
            JOIN users u ON r.customer_id = u.id
            WHERE r.booking_id = ?");
        $stmt->bind_param("i", $bookingId);
        $stmt->execute();
        echo json_encode($stmt->get_result()->fetch_all(MYSQLI_ASSOC));
    } elseif ($roomId) {
        $stmt = $conn->prepare("
            SELECT r.*, u.name as customer_name
            FROM reviews r
            JOIN users u ON r.customer_id = u.id
            WHERE r.room_id = ?
            ORDER BY r.created_at DESC");
        $stmt->bind_param("i", $roomId);
        $stmt->execute();
        echo json_encode($stmt->get_result()->fetch_all(MYSQLI_ASSOC));
    } else {
        $result = $conn->query("
            SELECT r.*, u.name as customer_name, ro.room_number, ro.room_type
            FROM reviews r
            JOIN users u ON r.customer_id = u.id
            JOIN rooms ro ON r.room_id = ro.id
            ORDER BY r.created_at DESC");
        echo json_encode($result->fetch_all(MYSQLI_ASSOC));
    }
    exit;
}

if ($method === 'POST') {
    if ($me['role'] !== 'customer') {
        echo json_encode(['error' => 'Only customers can post reviews']); exit;
    }

    $data      = json_decode(file_get_contents('php://input'), true);
    $bookingId = intval($data['booking_id'] ?? 0);
    $rating    = intval($data['rating']     ?? 0);
    $comment   = trim($data['comment']      ?? '');

    if (!$bookingId || !$rating) {
        echo json_encode(['error' => 'Booking and rating are required']); exit;
    }

    if ($rating < 1 || $rating > 5) {
        echo json_encode(['error' => 'Rating must be between 1 and 5']); exit;
    }

    $stmt = $conn->prepare("SELECT id, room_id, status FROM bookings WHERE id = ? AND customer_id = ?");
    $stmt->bind_param("ii", $bookingId, $me['id']);
    $stmt->execute();
    $booking = $stmt->get_result()->fetch_assoc();

    if (!$booking) {
        echo json_encode(['error' => 'Booking not found']); exit;
    }

    if ($booking['status'] !== 'checked_out') {
        echo json_encode(['error' => 'You can only review completed stays (checked out)']); exit;
    }

    $stmt = $conn->prepare("SELECT id FROM reviews WHERE booking_id = ? AND customer_id = ?");
    $stmt->bind_param("ii", $bookingId, $me['id']);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        echo json_encode(['error' => 'You have already reviewed this booking']); exit;
    }

    $roomId = $booking['room_id'];
    $stmt = $conn->prepare("INSERT INTO reviews (customer_id, booking_id, room_id, rating, comment) VALUES (?,?,?,?,?)");
    $stmt->bind_param("iiiis", $me['id'], $bookingId, $roomId, $rating, $comment);

    if ($stmt->execute()) {
        logActivity($conn, $me['id'], 'Review Posted', "Review for booking #$bookingId");
        echo json_encode(['success' => true, 'message' => 'Review posted successfully!']);
    } else {
        echo json_encode(['error' => 'Failed to post review']);
    }
    exit;
}

if ($method === 'DELETE') {
    $data = json_decode(file_get_contents('php://input'), true);
    $id   = intval($data['id'] ?? 0);

    if ($me['role'] === 'customer') {
        $stmt = $conn->prepare("DELETE FROM reviews WHERE id = ? AND customer_id = ?");
        $stmt->bind_param("ii", $id, $me['id']);
    } elseif ($me['role'] === 'admin') {
        $stmt = $conn->prepare("DELETE FROM reviews WHERE id = ?");
        $stmt->bind_param("i", $id);
    } else {
        echo json_encode(['error' => 'Access denied']); exit;
    }

    if ($stmt->execute() && $stmt->affected_rows > 0) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['error' => 'Delete failed or not authorized']);
    }
    exit;
}

echo json_encode(['error' => 'Method not allowed']);
