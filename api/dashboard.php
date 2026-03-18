<?php
require_once '../config/db.php';
require_once '../config/auth.php';

header('Content-Type: application/json');
requireLogin();
$me = currentUser();

$stats = [];

if ($me['role'] === 'admin') {
    $stats['total_rooms']      = $conn->query("SELECT COUNT(*) c FROM rooms")->fetch_assoc()['c'];
    $stats['available_rooms']  = $conn->query("SELECT COUNT(*) c FROM rooms WHERE status='available'")->fetch_assoc()['c'];
    $stats['occupied_rooms']   = $conn->query("SELECT COUNT(*) c FROM rooms WHERE status='occupied'")->fetch_assoc()['c'];
    $stats['maintenance_rooms']= $conn->query("SELECT COUNT(*) c FROM rooms WHERE status='maintenance'")->fetch_assoc()['c'];
    $stats['total_bookings']   = $conn->query("SELECT COUNT(*) c FROM bookings")->fetch_assoc()['c'];
    $stats['active_bookings']  = $conn->query("SELECT COUNT(*) c FROM bookings WHERE status IN ('confirmed','checked_in')")->fetch_assoc()['c'];
    $stats['total_revenue']    = $conn->query("SELECT COALESCE(SUM(amount),0) s FROM payments WHERE payment_status='paid'")->fetch_assoc()['s'];
    $stats['total_customers']  = $conn->query("SELECT COUNT(*) c FROM users WHERE role='customer'")->fetch_assoc()['c'];
    $stats['total_staff']      = $conn->query("SELECT COUNT(*) c FROM users WHERE role='staff'")->fetch_assoc()['c'];
    $stats['pending_payments'] = $conn->query("SELECT COUNT(*) c FROM payments WHERE payment_status='pending'")->fetch_assoc()['c'];
    $stats['pending_services'] = $conn->query("SELECT COUNT(*) c FROM service_requests WHERE status='pending'")->fetch_assoc()['c'];

    $result = $conn->query("SELECT b.id, b.check_in, b.check_out, b.status, b.total_price, u.name as customer_name, r.room_number FROM bookings b JOIN users u ON b.customer_id=u.id JOIN rooms r ON b.room_id=r.id ORDER BY b.booked_at DESC LIMIT 5");
    $stats['recent_bookings'] = $result->fetch_all(MYSQLI_ASSOC);

    $result = $conn->query("SELECT DATE_FORMAT(paid_at,'%Y-%m') as month, SUM(amount) as revenue FROM payments WHERE payment_status='paid' AND paid_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH) GROUP BY month ORDER BY month");
    $stats['monthly_revenue'] = $result->fetch_all(MYSQLI_ASSOC);

} elseif ($me['role'] === 'staff') {
    $stats['total_rooms']     = $conn->query("SELECT COUNT(*) c FROM rooms")->fetch_assoc()['c'];
    $stats['available_rooms'] = $conn->query("SELECT COUNT(*) c FROM rooms WHERE status='available'")->fetch_assoc()['c'];
    $stats['occupied_rooms']  = $conn->query("SELECT COUNT(*) c FROM rooms WHERE status='occupied'")->fetch_assoc()['c'];
    $stats['active_bookings'] = $conn->query("SELECT COUNT(*) c FROM bookings WHERE status IN ('confirmed','checked_in')")->fetch_assoc()['c'];
    $stats['total_customers'] = $conn->query("SELECT COUNT(*) c FROM users WHERE role='customer'")->fetch_assoc()['c'];
    $stats['pending_services']= $conn->query("SELECT COUNT(*) c FROM service_requests WHERE status='pending'")->fetch_assoc()['c'];

    $result = $conn->query("SELECT b.id, b.check_in, b.check_out, b.status, b.total_price, u.name as customer_name, r.room_number FROM bookings b JOIN users u ON b.customer_id=u.id JOIN rooms r ON b.room_id=r.id WHERE b.status IN ('confirmed','checked_in') ORDER BY b.check_in ASC LIMIT 5");
    $stats['recent_bookings'] = $result->fetch_all(MYSQLI_ASSOC);

} else {
    $uid = $me['id'];
    $stmt = $conn->prepare("SELECT COUNT(*) c FROM bookings WHERE customer_id=?"); $stmt->bind_param("i",$uid); $stmt->execute();
    $stats['total_bookings'] = $stmt->get_result()->fetch_assoc()['c'];

    $stmt = $conn->prepare("SELECT COUNT(*) c FROM bookings WHERE customer_id=? AND status IN ('confirmed','checked_in')"); $stmt->bind_param("i",$uid); $stmt->execute();
    $stats['active_bookings'] = $stmt->get_result()->fetch_assoc()['c'];

    $stmt = $conn->prepare("SELECT COALESCE(SUM(p.amount),0) s FROM payments p JOIN bookings b ON p.booking_id=b.id WHERE b.customer_id=? AND p.payment_status='paid'"); $stmt->bind_param("i",$uid); $stmt->execute();
    $stats['total_spent'] = $stmt->get_result()->fetch_assoc()['s'];

    $stmt = $conn->prepare("SELECT COUNT(*) c FROM service_requests sr JOIN bookings b ON sr.booking_id=b.id WHERE b.customer_id=?"); $stmt->bind_param("i",$uid); $stmt->execute();
    $stats['service_requests'] = $stmt->get_result()->fetch_assoc()['c'];

    $stmt = $conn->prepare("SELECT b.*, r.room_number, r.room_type FROM bookings b JOIN rooms r ON b.room_id=r.id WHERE b.customer_id=? ORDER BY b.booked_at DESC LIMIT 5"); $stmt->bind_param("i",$uid); $stmt->execute();
    $stats['recent_bookings'] = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

echo json_encode($stats);
