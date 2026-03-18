<?php
require_once '../config/auth.php';
require_once '../config/db.php';
header('Content-Type: application/json');
if (isLoggedIn()) {
    logActivity($conn, $_SESSION['user_id'], 'Logout', 'User logged out');
}
session_destroy();
echo json_encode(['success' => true]);
