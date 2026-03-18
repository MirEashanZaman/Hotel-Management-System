<?php
require_once '../config/db.php';
require_once '../config/auth.php';

header('Content-Type: application/json');
requireRole('admin');

$result = $conn->query("SELECT l.*, u.name as user_name, u.role FROM activity_logs l LEFT JOIN users u ON l.user_id = u.id ORDER BY l.logged_at DESC LIMIT 100");
echo json_encode($result->fetch_all(MYSQLI_ASSOC));
