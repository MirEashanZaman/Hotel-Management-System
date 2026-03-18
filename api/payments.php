<?php
require_once '../config/db.php';
require_once '../config/auth.php';

header('Content-Type: application/json');
requireLogin();

$method = $_SERVER['REQUEST_METHOD'];
$me = currentUser();

if ($method === 'GET') {
    $bookingId = $_GET['booking_id'] ?? null;
    if ($bookingId) {
        $stmt = $conn->prepare("SELECT p.*, b.customer_id FROM payments p JOIN bookings b ON p.booking_id = b.id WHERE p.booking_id = ?");
        $stmt->bind_param("i", $bookingId);
        $stmt->execute();
        $payments = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        if ($me['role'] === 'customer' && count($payments) && $payments[0]['customer_id'] != $me['id']) {
            echo json_encode(['error' => 'Access denied']); exit;
        }
        echo json_encode($payments);
    } else {
        if ($me['role'] === 'customer') {
            $stmt = $conn->prepare("
                SELECT p.*, r.room_number, b.check_in, b.check_out, b.status as booking_status
                FROM payments p
                JOIN bookings b ON p.booking_id = b.id
                JOIN rooms r ON b.room_id = r.id
                WHERE b.customer_id = ?
                ORDER BY p.created_at DESC");
            $stmt->bind_param("i", $me['id']);
            $stmt->execute();
            echo json_encode($stmt->get_result()->fetch_all(MYSQLI_ASSOC));
        } else {
            $result = $conn->query("
                SELECT p.*, b.customer_id, b.status as booking_status,
                       u.name as customer_name, r.room_number
                FROM payments p
                JOIN bookings b ON p.booking_id = b.id
                JOIN users u ON b.customer_id = u.id
                JOIN rooms r ON b.room_id = r.id
                ORDER BY p.created_at DESC");
            echo json_encode($result->fetch_all(MYSQLI_ASSOC));
        }
    }
    exit;
}

if ($method === 'POST') {
    $data          = json_decode(file_get_contents('php://input'), true);
    $bookingId     = intval($data['booking_id']     ?? 0);
    $amount        = floatval($data['amount']        ?? 0);
    $paymentMethod = $data['payment_method']         ?? 'cash';
    $txId          = trim($data['transaction_id']    ?? '');

    $stmt = $conn->prepare("SELECT customer_id, total_price, status FROM bookings WHERE id = ?");
    $stmt->bind_param("i", $bookingId);
    $stmt->execute();
    $booking = $stmt->get_result()->fetch_assoc();
    if (!$booking) { echo json_encode(['error' => 'Booking not found']); exit; }

    if ($me['role'] === 'customer' && $booking['customer_id'] != $me['id']) {
        echo json_encode(['error' => 'Access denied']); exit;
    }

    if (!$amount) $amount = $booking['total_price'];

    $paymentStatus = ($paymentMethod === 'card') ? 'paid' : 'pending';
    $paidAt        = ($paymentMethod === 'card') ? date('Y-m-d H:i:s') : null;

    $stmt = $conn->prepare("SELECT id, payment_status FROM payments WHERE booking_id = ?");
    $stmt->bind_param("i", $bookingId);
    $stmt->execute();
    $existing = $stmt->get_result()->fetch_assoc();

    if ($existing) {
        $stmt = $conn->prepare("UPDATE payments SET payment_method=?, payment_status=?, transaction_id=?, paid_at=? WHERE booking_id=?");
        $stmt->bind_param("ssssi", $paymentMethod, $paymentStatus, $txId, $paidAt, $bookingId);
        $stmt->execute();
    } else {
        $stmt = $conn->prepare("INSERT INTO payments (booking_id, amount, payment_method, payment_status, transaction_id, paid_at) VALUES (?,?,?,?,?,?)");
        $stmt->bind_param("idssss", $bookingId, $amount, $paymentMethod, $paymentStatus, $txId, $paidAt);
        $stmt->execute();
    }

    if ($paymentMethod === 'card') {
        $conn->query("UPDATE bookings SET status='confirmed' WHERE id=$bookingId");
        logActivity($conn, $me['id'], 'Payment', "Booking #$bookingId paid by card — auto confirmed");
        echo json_encode(['success' => true, 'status' => 'confirmed', 'message' => 'Card payment successful. Booking confirmed!']);
    } else {
        $conn->query("UPDATE bookings SET status='pending' WHERE id=$bookingId");
        logActivity($conn, $me['id'], 'Payment', "Booking #$bookingId cash payment pending — awaiting staff confirmation");
        echo json_encode(['success' => true, 'status' => 'pending', 'message' => 'Cash payment registered. Please pay at the front desk. Your booking will be confirmed by staff.']);
    }
    exit;
}

if ($method === 'PUT') {
    requireRole(['admin', 'staff']);

    $data      = json_decode(file_get_contents('php://input'), true);
    $id        = intval($data['id'] ?? 0);
    $paidAt    = date('Y-m-d H:i:s');
    $status    = 'paid';

    $stmt = $conn->prepare("SELECT booking_id, payment_method FROM payments WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $payment = $stmt->get_result()->fetch_assoc();
    if (!$payment) { echo json_encode(['error' => 'Payment not found']); exit; }

    $stmt = $conn->prepare("UPDATE payments SET payment_status='paid', paid_at=? WHERE id=?");
    $stmt->bind_param("si", $paidAt, $id);

    if ($stmt->execute()) {
        $bookingId = $payment['booking_id'];
        $conn->query("UPDATE bookings SET status='confirmed' WHERE id=$bookingId");
        logActivity($conn, $me['id'], 'Confirm Cash Payment', "Staff confirmed cash payment #$id — booking #$bookingId is now confirmed");
        echo json_encode(['success' => true, 'message' => 'Cash payment confirmed. Booking is now confirmed.']);
    } else {
        echo json_encode(['error' => 'Update failed']);
    }
    exit;
}

echo json_encode(['error' => 'Method not allowed']);
