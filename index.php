<?php
require_once __DIR__ . '/bootstrap.php';

$route = $_GET['route'] ?? 'login';

switch ($route) {
    case 'signup':
        include __DIR__ . '/views/signup.php';
        break;
    case 'dashboard':
        include __DIR__ . '/views/dashboard.php';
        break;
    case 'payment-card':
        include __DIR__ . '/views/payment-card.php';
        break;
    case 'login':
    default:
        include __DIR__ . '/views/login.php';
        break;
}
