<?php
require_once __DIR__ . '/bootstrap.php';

$route = $_GET['route'] ?? 'home';

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
        include __DIR__ . '/views/login.php';
        break;
    case 'home':
    default:
        include __DIR__ . '/views/home.php';
        break;
}
