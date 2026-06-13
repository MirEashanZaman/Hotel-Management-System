<?php
abstract class BaseController {
    protected function jsonResponse($data, $statusCode = 200) {
        header('Content-Type: application/json');
        http_response_code($statusCode);
        echo json_encode($data);
        exit;
    }

    protected function getInput() {
        return json_decode(file_get_contents('php://input'), true) ?? [];
    }

    protected function me() {
        return currentUser();
    }

    protected function logActivity($userId, $action, $details = '') {
        $logModel = new Log();
        $logModel->write($userId, $action, $details);
    }
}
