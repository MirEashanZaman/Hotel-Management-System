<?php
class ServiceController extends BaseController {
    private $serviceModel;
    private $bookingModel;

    public function __construct() {
        requireLogin();
        $this->serviceModel = new Service();
        $this->bookingModel = new Booking();
    }

    public function handleRequest() {
        $method = $_SERVER['REQUEST_METHOD'];
        $me = $this->me();

        if ($method === 'GET') {
            $type = $_GET['type'] ?? null;

            if ($type === 'requests') {
                $bookingId = $_GET['booking_id'] ?? null;
                if ($me['role'] === 'customer') {
                    $this->jsonResponse($this->serviceModel->getRequestsByCustomer($me['id']));
                } else {
                    $this->jsonResponse($this->serviceModel->getRequestsAll($bookingId));
                }
            } else {
                $this->jsonResponse($this->serviceModel->getServices());
            }
        }

        if ($method === 'POST') {
            $data = $this->getInput();
            $type = $data['type'] ?? 'request';

            if ($type === 'service') {
                requireRole('admin');
                $name     = trim($data['name'] ?? '');
                $desc     = trim($data['description'] ?? '');
                $price    = floatval($data['price'] ?? 0);
                $category = $data['category'] ?? 'other';

                $newId = $this->serviceModel->createService($name, $desc, $price, $category);
                if ($newId) {
                    $this->jsonResponse(['success' => true, 'id' => $newId]);
                } else {
                    $this->jsonResponse(['error' => 'Insert failed']);
                }
            } else {
                $bookingId = intval($data['booking_id'] ?? 0);
                $serviceId = intval($data['service_id'] ?? 0);
                $qty       = intval($data['quantity'] ?? 1);
                $notes     = trim($data['notes'] ?? '');

                if ($me['role'] === 'customer') {
                    $booking = $this->bookingModel->findById($bookingId);
                    if (!$booking || $booking['customer_id'] != $me['id']) {
                        $this->jsonResponse(['error' => 'Access denied'], 403);
                    }
                }

                $newId = $this->serviceModel->createRequest($bookingId, $serviceId, $qty, $notes);
                if ($newId) {
                    $this->jsonResponse(['success' => true, 'id' => $newId]);
                } else {
                    $this->jsonResponse(['error' => 'Request failed']);
                }
            }
        }

        if ($method === 'PUT') {
            requireRole(['admin', 'staff']);
            $data = $this->getInput();
            $id     = intval($data['id'] ?? 0);
            $status = $data['status'] ?? 'pending';

            if ($this->serviceModel->updateRequestStatus($id, $status)) {
                $this->jsonResponse(['success' => true]);
            } else {
                $this->jsonResponse(['error' => 'Update failed']);
            }
        }

        $this->jsonResponse(['error' => 'Method not allowed'], 405);
    }
}
