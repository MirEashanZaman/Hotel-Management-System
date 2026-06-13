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
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600&display=swap');
        
        body {
            font-family: 'Montserrat', sans-serif;
            background: #0d0d0d;
            color: #e8e0d0;
            margin: 0;
            padding: 40px;
            font-size: 13px;
            line-height: 1.6;
        }

        .invoice-box {
            max-width: 800px;
            margin: auto;
            background: #111;
            border: 1px solid rgba(201, 168, 76, 0.2);
            padding: 40px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.5);
        }

        .header {
            display: flex;
            justify-content: space-between;
            border-bottom: 2px solid #c9a84c;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }

        .logo h1 {
            color: #c9a84c;
            font-size: 26px;
            margin: 0;
            font-family: Georgia, serif;
            letter-spacing: 1px;
        }

        .logo p {
            font-size: 9px;
            letter-spacing: 3px;
            text-transform: uppercase;
            color: #888;
            margin: 5px 0 0 0;
        }

        .info {
            text-align: right;
        }

        .info h2 {
            font-size: 20px;
            color: #c9a84c;
            margin: 0 0 5px 0;
            font-weight: 500;
        }

        .details-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            margin-bottom: 40px;
        }

        .details-block h3 {
            font-size: 11px;
            letter-spacing: 2px;
            text-transform: uppercase;
            color: #c9a84c;
            margin-bottom: 12px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            padding-bottom: 6px;
        }

        .details-block p {
            margin: 4px 0;
            color: #b0a898;
        }

        table.items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 40px;
        }

        table.items-table th, table.items-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }

        table.items-table th {
            color: #c9a84c;
            font-size: 10px;
            letter-spacing: 2px;
            text-transform: uppercase;
            border-bottom: 2px solid #c9a84c;
        }

        table.items-table td {
            color: #b0a898;
        }

        .totals {
            float: right;
            width: 300px;
            margin-bottom: 40px;
        }

        .totals-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }

        .totals-row.grand-total {
            border-bottom: none;
            border-top: 2px solid #c9a84c;
            font-size: 16px;
            font-weight: 600;
            color: #c9a84c;
            margin-top: 8px;
            padding-top: 12px;
        }

        .clear {
            clear: both;
        }

        .footer {
            border-top: 1px solid rgba(255, 255, 255, 0.05);
            padding-top: 20px;
            text-align: center;
            font-size: 11px;
            color: #5a5248;
            margin-top: 40px;
        }

        .actions {
            max-width: 800px;
            margin: 0 auto 20px auto;
            display: flex;
            justify-content: flex-end;
            gap: 12px;
        }

        .btn {
            padding: 10px 24px;
            background: transparent;
            border: 1px solid #c9a84c;
            color: #c9a84c;
            font-size: 11px;
            letter-spacing: 2px;
            text-transform: uppercase;
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
        }

        .btn:hover {
            background: #c9a84c;
            color: #0d0d0d;
        }

        .badge {
            display: inline-block;
            padding: 2px 8px;
            font-size: 9px;
            letter-spacing: 1px;
            text-transform: uppercase;
            border: 1px solid;
            font-weight: 500;
        }
        .badge-green { color: #4cc97a; border-color: rgba(76,201,122,0.4); }
        .badge-orange { color: #e0925a; border-color: rgba(224,146,90,0.4); }
        .badge-blue { color: #4c8ec9; border-color: rgba(76,142,201,0.4); }

        @media print {
            body {
                background: #fff;
                color: #000;
                padding: 0;
            }
            .invoice-box {
                box-shadow: none;
                border: none;
                background: #fff;
                padding: 0;
                color: #000;
                max-width: 100%;
            }
            .no-print {
                display: none !important;
            }
            table.items-table th {
                border-bottom: 2px solid #000;
                color: #000;
            }
            table.items-table td, .details-block p {
                color: #000;
            }
            .details-block h3 {
                color: #000;
                border-bottom: 1px solid #ddd;
            }
            .totals-row.grand-total {
                border-top: 2px solid #000;
                color: #000;
            }
            .logo h1, .info h2 {
                color: #000;
            }
            .header {
                border-bottom: 2px solid #000;
            }
        }
    </style>
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
                <p>Hotel & Resort</p>
            </div>
            <div class="info">
                <h2>INVOICE</h2>
                <p style="margin: 0; color: #b0a898;">Ref: #INV-<?= $booking['id'] ?>-<?= date('Y', strtotime($booking['booked_at'])) ?></p>
                <p style="margin: 4px 0 0 0; color: #b0a898;">Date: <?= date('d M Y') ?></p>
            </div>
        </div>

        <div class="details-grid">
            <div class="details-block">
                <h3>Billed To</h3>
                <p style="font-weight: 500; color: #e8e0d0;"><?= htmlspecialchars($booking['customer_name']) ?></p>
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
                    <th style="text-align: right;">Unit Price</th>
                    <th style="text-align: center;">Qty / Nights</th>
                    <th style="text-align: right;">Total</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Room Charge: Room <?= htmlspecialchars($booking['room_number']) ?> (<?= htmlspecialchars($booking['room_type']) ?>)</td>
                    <td style="text-align: right;">৳<?= number_format($booking['price_per_night']) ?></td>
                    <td style="text-align: center;"><?= $nights ?> night(s)</td>
                    <td style="text-align: right;">৳<?= number_format($booking['price_per_night'] * $nights) ?></td>
                </tr>
                <?php 
                $servicesTotal = 0;
                foreach ($services as $s): 
                    $sub = $s['price'] * $s['quantity'];
                    $servicesTotal += $sub;
                ?>
                <tr>
                    <td>Service: <?= htmlspecialchars($s['service_name']) ?> (<?= htmlspecialchars($s['category']) ?>)</td>
                    <td style="text-align: right;">৳<?= number_format($s['price']) ?></td>
                    <td style="text-align: center;"><?= $s['quantity'] ?></td>
                    <td style="text-align: right;">৳<?= number_format($sub) ?></td>
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
            
            <div style="margin-top: 20px; text-align: right;">
                <span style="font-size: 10px; letter-spacing: 1px; text-transform: uppercase; color: #888; display: block; margin-bottom: 5px;">Payment Status</span>
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
            <p style="margin-top: 5px; font-size: 10px;">Grand Palace Hotel & Resort · 123 Luxury Way, Coastal City · Tel: +880-123456</p>
        </div>
    </div>

</body>
</html>
