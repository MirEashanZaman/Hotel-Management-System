<?php
require_once __DIR__ . '/../bootstrap.php';
requireLogin();

$checkIn = $_GET['check_in'] ?? '';
$checkOut = $_GET['check_out'] ?? '';

if (empty($checkIn) || empty($checkOut)) {
    header('Content-Type: application/json');
    echo json_encode([]);
    exit;
}

global $conn;
$stmt = $conn->prepare("
    SELECT DISTINCT room_id FROM bookings
    WHERE status NOT IN ('cancelled', 'checked_out')
      AND check_in < ? AND check_out > ?
");
$stmt->execute([$checkOut, $checkIn]);
$occupied = $stmt->fetchAll(PDO::FETCH_COLUMN);

header('Content-Type: application/json');
echo json_encode($occupied);
exit;
