<?php
require_once __DIR__ . '/bootstrap.php';
requireLogin();

$bookingId = intval($_GET['id'] ?? 0);
if ($bookingId <= 0) {
    die("Invalid Booking ID");
}

$bookingModel = new Booking();
$booking = $bookingModel->findById($bookingId);

if (!$booking) {
    die("Booking not found");
}

// Security check: Customers can only see their own invoices
$me = currentUser();
if ($me['role'] === 'customer' && $booking['customer_id'] != $me['id']) {
    die("Access denied");
}

// Fetch payment details
$paymentModel = new Payment();
$payments = $paymentModel->getByBookingId($bookingId);
$payment = count($payments) ? $payments[0] : null;

// Fetch service requests
$serviceModel = new Service();
$services = $serviceModel->getRequestsAll($bookingId);

// Calculate nights
$ci = new DateTime($booking['check_in']);
$co = new DateTime($booking['check_out']);
$nights = $ci->diff($co)->days;
if ($nights <= 0) $nights = 1;

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice - Booking #<?= $booking['id'] ?></title>
    <link rel="stylesheet" href="css/invoice.css">
</head>
<body>

    <div class="actions no-print">
        <a href="index.php?route=dashboard" class="btn">← Back to Dashboard</a>
        <button class="btn" onclick="window.print()">Print / Save PDF</button>
    </div>

    <div class="invoice-box">
        <div class="header">
            <div class="logo">
                <h1>GRAND PALACE</h1>
                <p>Hotel &amp; Resort</p>
            </div>
            <div class="info">
                <h2>INVOICE</h2>
                <p class="info-text">Ref: #INV-<?= $booking['id'] ?>-<?= date('Y', strtotime($booking['booked_at'])) ?></p>
                <p class="info-text--spaced">Date: <?= date('d M Y') ?></p>
            </div>
        </div>

        <div class="details-grid">
            <div class="details-block">
                <h3>Billed To</h3>
                <p class="billed-name"><?= htmlspecialchars($booking['customer_name']) ?></p>
                <p><?= htmlspecialchars($booking['customer_email']) ?></p>
                <p><?= htmlspecialchars($booking['customer_phone'] ?: '—') ?></p>
                <p><?= htmlspecialchars($booking['address'] ?? '—') ?></p>
            </div>
            <div class="details-block">
                <h3>Reservation Details</h3>
                <p><strong>Room Number:</strong> <?= htmlspecialchars($booking['room_number']) ?> (<?= htmlspecialchars($booking['room_type']) ?>)</p>
                <p><strong>Nights:</strong> <?= $nights ?> night(s)</p>
                <p><strong>Check In:</strong> <?= date('d M Y', strtotime($booking['check_in'])) ?></p>
                <p><strong>Check Out:</strong> <?= date('d M Y', strtotime($booking['check_out'])) ?></p>
            </div>
        </div>

        <table class="items-table">
            <thead>
                <tr>
                    <th>Item Description</th>
                    <th class="text-right">Unit Price</th>
                    <th class="text-center">Qty / Nights</th>
                    <th class="text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Room Charge: Room <?= htmlspecialchars($booking['room_number']) ?> (<?= htmlspecialchars($booking['room_type']) ?>)</td>
                    <td class="text-right">৳<?= number_format($booking['price_per_night']) ?></td>
                    <td class="text-center"><?= $nights ?> night(s)</td>
                    <td class="text-right">৳<?= number_format($booking['price_per_night'] * $nights) ?></td>
                </tr>
                <?php 
                $servicesTotal = 0;
                foreach ($services as $s): 
                    $sub = $s['price'] * $s['quantity'];
                    $servicesTotal += $sub;
                ?>
                <tr>
                    <td>Service: <?= htmlspecialchars($s['service_name']) ?> (<?= htmlspecialchars($s['category']) ?>)</td>
                    <td class="text-right">৳<?= number_format($s['price']) ?></td>
                    <td class="text-center"><?= $s['quantity'] ?></td>
                    <td class="text-right">৳<?= number_format($sub) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="totals">
            <div class="totals-row">
                <span>Room Charges</span>
                <span>৳<?= number_format($booking['price_per_night'] * $nights) ?></span>
            </div>
            <?php if ($servicesTotal > 0): ?>
            <div class="totals-row">
                <span>Room Services</span>
                <span>৳<?= number_format($servicesTotal) ?></span>
            </div>
            <?php endif; ?>
            <div class="totals-row grand-total">
                <span>Grand Total</span>
                <span>৳<?= number_format($booking['total_price'] + $servicesTotal) ?></span>
            </div>
            
            <div class="payment-status-wrap">
                <span class="payment-status-label">Payment Status</span>
                <?php if ($payment && $payment['payment_status'] === 'paid'): ?>
                    <span class="badge badge-green">Paid (<?= strtoupper($payment['payment_method']) ?>)</span>
                <?php else: ?>
                    <span class="badge badge-orange">Pending Cash / Unpaid</span>
                <?php endif; ?>
            </div>
        </div>
        <div class="clear"></div>

        <div class="footer">
            <p>Thank you for choosing Grand Palace Hotel. We hope you enjoyed your stay!</p>
            <p class="footer-address">Grand Palace Hotel &amp; Resort · 123 Luxury Way, Coastal City · Tel: +880-123456</p>
        </div>
    </div>

</body>
</html>
