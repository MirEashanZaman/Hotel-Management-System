<?php
require_once '../config/auth.php';
header('Content-Type: application/json');
if (isLoggedIn()) {
    echo json_encode(['loggedIn' => true, 'user' => currentUser()]);
} else {
    echo json_encode(['loggedIn' => false]);
}
