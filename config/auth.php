<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Content-Type: application/json');
        http_response_code(401);
        echo json_encode(['error' => 'Not logged in', 'redirect' => true]);
        exit;
    }
}

function requireRole($roles) {
    requireLogin();
    if (!in_array($_SESSION['role'], (array)$roles)) {
        header('Content-Type: application/json');
        http_response_code(403);
        echo json_encode(['error' => 'Access denied']);
        exit;
    }
}

function currentUser() {
    return [
        'id'    => $_SESSION['user_id'] ?? null,
        'name'  => $_SESSION['name']    ?? null,
        'role'  => $_SESSION['role']    ?? null,
        'email' => $_SESSION['email']   ?? null,
    ];
}

function logActivity($conn, $userId, $action, $details = '') {
    $ip   = $_SERVER['REMOTE_ADDR'] ?? '';
    $stmt = $conn->prepare("INSERT INTO activity_logs (user_id, action, details, ip_address) VALUES (?,?,?,?)");
    $stmt->bind_param("isss", $userId, $action, $details, $ip);
    $stmt->execute();
}
