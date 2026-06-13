<?php
class LogController extends BaseController {
    private $logModel;

    public function __construct() {
        requireRole('admin');
        $this->logModel = new Log();
    }

    public function handleRequest() {
        $method = $_SERVER['REQUEST_METHOD'];
        if ($method === 'GET') {
            $this->jsonResponse($this->logModel->getLogs(100));
        }
        $this->jsonResponse(['error' => 'Method not allowed'], 405);
    }
}
