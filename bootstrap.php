<?php
require_once __DIR__ . '/autoload.php';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/auth.php';
require_once __DIR__ . '/config/mail.php';

// Auto-checkout expired bookings on every request
(new Booking())->autoCheckout();
